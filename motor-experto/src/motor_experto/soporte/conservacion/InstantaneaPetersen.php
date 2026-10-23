<?php

declare(strict_types=1);

namespace MotorExperto\soporte\conservacion;

use DateTimeImmutable;
use DomainException;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\petersen\EvaluarPetersenInstitucional;
use MotorExperto\dominio\petersen\FuentePetersenInstitucional;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen;

/** Captura en memoria de la frontera autorizada. No guarda historia ni vuelve a autorizar fuentes. */
final readonly class InstantaneaPetersen
{
    public const CONTRATO = 'PETERSEN-ORIGINALES-1';

    public const CONTRATO_MEMBRESIAS = 'PETERSEN-ORIGINALES-2';

    private function __construct(public array $contenido, public string $huella) {}

    public static function crear(ContextoEjecucion $contexto, array $valoraciones, ?PaqueteConocimiento $paquete,
        ?array $cabecera, string $usuario, array $adquisicion, array $documentos = [], array $procedencia = [], array $membresias = [],
        ?array $soportesOriginales = null): self
    {
        $juicios = [];
        foreach ($valoraciones as $v) {
            if (! $v instanceof ValoracionOriginalPetersen) {
                throw new DomainException('La preparación requiere valoraciones originales tipadas.');
            }
            $juicios[] = ['elemento' => $v->elemento, 'estado' => $v->estado, 'justificacion' => $v->justificacion,
                'origen' => $v->origen->referencia(), 'soportes' => array_map(fn ($s) => $s->referencia(), $v->soportes),
                'comprobaciones' => $v->comprobaciones, 'dominios_revisados' => $v->dominiosRevisados];
        }
        if (($paquete === null) !== ($cabecera === null) || $usuario === '' || strlen($usuario) > 20) {
            throw new DomainException('La preparación requiere actor y cabecera del gobierno utilizado, o su ausencia explícita.');
        }
        $datos = ['contrato' => $soportesOriginales === null ? self::CONTRATO : self::CONTRATO_MEMBRESIAS,
            'contexto' => ['evaluacion' => $contexto->evaluacion, 'residente' => $contexto->residente, 'version' => $contexto->version,
                'usuario_ejecucion' => $usuario, 'fecha_corte' => CodecInstantanea::fecha('corte', $contexto->fechaCorte)],
            'valoraciones' => $juicios, 'gobierno' => $paquete === null ? null : ['cabecera' => $cabecera, 'conocimiento' => $paquete->declaracion()],
            'adquisicion' => $adquisicion, 'constancias_documentales' => $documentos, 'procedencia_valoraciones' => $procedencia,
            'membresias_soportes' => $membresias,
            'limites' => ['revalida_permisos_historicos' => false, 'revalida_bytes_documentales' => false,
                'reproduce_adquisicion_sql' => false, 'persistido' => false]];
        if ($soportesOriginales !== null) {
            $datos['soportes_originales'] = $soportesOriginales;
        }
        $datos['salida'] = self::ejecutar($datos);
        $datos = CapturaInstrumentalCanonica::ordenar($datos);

        return new self($datos, CodecInstantanea::huella($datos));
    }

    public static function verificar(array $contenido, string $huella): self
    {
        $original = self::verificarIntegridad($contenido, $huella);
        $contenido = $original->contenido;
        if (CodecInstantanea::huella(self::ejecutar($contenido)) !== CodecInstantanea::huella($contenido['salida'])) {
            throw new DomainException('La salida Petersen no se reproduce con sus originales y gobierno capturados.');
        }

        return $original;
    }

    /** Forma y huella del original, sin inferencia; la huella esperada debe proceder de conservación confiable. */
    public static function verificarIntegridad(array $contenido, string $huella): self
    {
        $contenido = IntegridadPreparacion::comprobar($contenido, $huella);
        self::campos($contenido, self::camposContenido($contenido, true));
        IntegridadPreparacion::contexto($contenido['contexto']);
        if (! in_array($contenido['contrato'], [self::CONTRATO, self::CONTRATO_MEMBRESIAS], true) || ! is_array($contenido['salida']) || ! is_array($contenido['adquisicion'])
            || ! is_array($contenido['limites']) || ($contenido['gobierno'] !== null && ! is_array($contenido['gobierno']))) {
            throw new DomainException('La preparación Petersen requiere su contrato, datos y salida originales.');
        }
        foreach (['valoraciones', 'constancias_documentales', 'procedencia_valoraciones', 'membresias_soportes'] as $coleccion) {
            if (! is_array($contenido[$coleccion]) || ! array_is_list($contenido[$coleccion])) {
                throw new DomainException('La preparación Petersen requiere sus colecciones originales ordenadas.');
            }
        }
        foreach ($contenido['valoraciones'] as $valoracion) {
            if (! is_array($valoracion)) {
                throw new DomainException('La valoración Petersen conservada tiene una forma incompatible.');
            }
            self::campos($valoracion, ['elemento', 'estado', 'justificacion', 'origen', 'soportes', 'comprobaciones', 'dominios_revisados']);
            if (! is_array($valoracion['origen']) || ! is_array($valoracion['soportes']) || ! array_is_list($valoracion['soportes'])
                || ! is_array($valoracion['comprobaciones']) || ! is_array($valoracion['dominios_revisados'])) {
                throw new DomainException('La valoración Petersen requiere sus referencias y colecciones originales.');
            }
            foreach ($valoracion['soportes'] as $soporte) {
                if (! is_array($soporte) || ! is_string($soporte['tipo'] ?? null) || ! is_string($soporte['registro'] ?? null)
                    || ! is_array($soporte['datos_originales'] ?? null)) {
                    throw new DomainException('El soporte Petersen conservado tiene una forma incompatible.');
                }
            }
        }
        if ($contenido['contrato'] === self::CONTRATO_MEMBRESIAS) {
            MembresiasOriginalesPetersen::comprobar($contenido);
        }
        self::campos($contenido['salida'], ['contrato', 'evaluacion', 'residente', 'version', 'fecha_corte', 'patron', 'estado', 'impedimentos',
            'dominios', 'requisitos', 'gobierno', 'valoraciones_excluidas', 'revision_profesional', 'persistido', 'adquisicion']);
        foreach (['evaluacion', 'residente', 'version'] as $campo) {
            if ($contenido['salida'][$campo] !== $contenido['contexto'][$campo]) {
                throw new DomainException('La salida Petersen conservada pertenece a otro contexto.');
            }
        }
        if ($contenido['salida']['persistido'] !== false) {
            throw new DomainException('La preparación Petersen no acredita conservación institucional.');
        }
        self::campos($contenido['adquisicion'], ['valoraciones_al_corte', 'exclusiones', 'fuentes_originales_mutables', 'conservacion_completa']);
        if (! is_int($contenido['adquisicion']['valoraciones_al_corte']) || $contenido['adquisicion']['valoraciones_al_corte'] < 0
            || ! is_array($contenido['adquisicion']['exclusiones']) || ! array_is_list($contenido['adquisicion']['exclusiones'])
            || $contenido['adquisicion']['fuentes_originales_mutables'] !== true || $contenido['adquisicion']['conservacion_completa'] !== false
            || $contenido['limites'] !== CapturaInstrumentalCanonica::ordenar(['revalida_permisos_historicos' => false,
                'revalida_bytes_documentales' => false, 'reproduce_adquisicion_sql' => false, 'persistido' => false])) {
            throw new DomainException('La preparación Petersen no conserva sus límites de adquisición originales.');
        }
        if ($contenido['gobierno'] !== null) {
            self::campos($contenido['gobierno'], ['cabecera', 'conocimiento']);
            if (! is_array($contenido['gobierno']['cabecera']) || ! is_array($contenido['gobierno']['conocimiento'])) {
                throw new DomainException('El gobierno Petersen requiere sus originales completos.');
            }
            $k = $contenido['gobierno']['conocimiento'];
            self::campos($k, ['version', 'tablas', 'contratos_relaciones', 'solo_pruebas_tecnicas', 'tipo_regla_resultado', 'contratos_extraccion']);
            if (($contenido['gobierno']['cabecera']['cod_version_modelo'] ?? null) !== $k['version']
                || ! is_string($k['version']) || ! is_array($k['tablas']) || ! is_array($k['contratos_relaciones'])
                || ! is_bool($k['solo_pruebas_tecnicas']) || ! is_string($k['tipo_regla_resultado']) || ! is_array($k['contratos_extraccion'])) {
                throw new DomainException('El gobierno Petersen conservado no corresponde a su versión original.');
            }
        }

        return new self($contenido, $huella);
    }

    private static function ejecutar(array $d): array
    {
        self::campos($d, self::camposContenido($d, false), ['salida']);
        if (! in_array($d['contrato'], [self::CONTRATO, self::CONTRATO_MEMBRESIAS], true) || ! array_is_list($d['valoraciones']) || ! array_is_list($d['constancias_documentales'])
            || ! array_is_list($d['procedencia_valoraciones'])
            || CapturaInstrumentalCanonica::ordenar($d['limites']) !== CapturaInstrumentalCanonica::ordenar([
                'revalida_permisos_historicos' => false, 'revalida_bytes_documentales' => false, 'reproduce_adquisicion_sql' => false, 'persistido' => false])) {
            throw new DomainException('Contrato o límites de preparación Petersen incompatibles.');
        }
        if ($d['contrato'] === self::CONTRATO_MEMBRESIAS) {
            MembresiasOriginalesPetersen::comprobar($d);
        }
        self::campos($d['contexto'], ['evaluacion', 'residente', 'version', 'usuario_ejecucion', 'fecha_corte']);
        if (! is_string($d['contexto']['usuario_ejecucion']) || $d['contexto']['usuario_ejecucion'] === '' || strlen($d['contexto']['usuario_ejecucion']) > 20) {
            throw new DomainException('La cuenta de preparación debe estar identificada.');
        }
        $c = new ContextoEjecucion($d['contexto']['evaluacion'], $d['contexto']['residente'], $d['contexto']['version'],
            CodecInstantanea::leerFecha('corte', (object) $d['contexto']['fecha_corte']));
        self::campos($d['adquisicion'], ['valoraciones_al_corte', 'exclusiones', 'fuentes_originales_mutables', 'conservacion_completa']);
        if (! is_int($d['adquisicion']['valoraciones_al_corte']) || $d['adquisicion']['valoraciones_al_corte'] < count($d['valoraciones'])
            || ! array_is_list($d['adquisicion']['exclusiones']) || $d['adquisicion']['fuentes_originales_mutables'] !== true
            || $d['adquisicion']['conservacion_completa'] !== false) {
            throw new DomainException('La adquisición no representa una preparación en memoria.');
        }
        $procedencia = [];
        foreach ($d['procedencia_valoraciones'] as $original) {
            self::campos($original, ['cod_valoracion_petersen', 'cod_residente', 'cod_personal', 'cod_usuario_registro', 'elemento', 'registrado_en', 'fecha_hora', 'tipo_registro',
                'cod_valoracion_anterior', 'cod_documento_clinico', 'localizador_fuente']);
            if ($original['cod_residente'] !== $c->residente || self::fecha($original['registrado_en']) > $c->fechaCorte
                || self::fecha($original['fecha_hora']) > $c->fechaCorte || isset($procedencia[$original['cod_valoracion_petersen']])) {
                throw new DomainException('La procedencia de la valoración pertenece a otro residente o corte.');
            }
            $procedencia[$original['cod_valoracion_petersen']] = $original;
        }
        if (count($procedencia) !== $d['adquisicion']['valoraciones_al_corte']) {
            throw new DomainException('La preparación debe conservar la procedencia de todas las valoraciones adquiridas.');
        }
        if (! array_is_list($d['membresias_soportes']) || count($d['membresias_soportes']) !== count($procedencia)) {
            throw new DomainException('La preparación debe fijar las colecciones de soportes de todos los juicios adquiridos.');
        }
        $vistas = $soportesVistos = [];
        foreach ($d['membresias_soportes'] as $membresia) {
            self::campos($membresia, ['valoracion', 'soportes']);
            if (! isset($procedencia[$membresia['valoracion']]) || isset($vistas[$membresia['valoracion']]) || ! array_is_list($membresia['soportes'])) {
                throw new DomainException('La membresía de soportes no pertenece a la adquisición.');
            }
            $vistas[$membresia['valoracion']] = count($membresia['soportes']);
            foreach ($membresia['soportes'] as $codigo) {
                if (! is_string($codigo) || $codigo === '' || strlen($codigo) > 20 || isset($soportesVistos[$codigo])) {
                    throw new DomainException('La membresía repite un soporte o tiene una identidad inválida.');
                }
                $soportesVistos[$codigo] = true;
            }
        }
        foreach ($d['adquisicion']['exclusiones'] as $exclusion) {
            self::campos($exclusion, ['valoracion', 'elemento', 'motivo']);
            if (($procedencia[$exclusion['valoracion']]['elemento'] ?? null) !== $exclusion['elemento']) {
                throw new DomainException('Una exclusión pertenece a otra valoración.');
            }
        }
        foreach ($d['constancias_documentales'] as $documento) {
            self::campos($documento, ['valoracion', 'papel', 'cod_documento_clinico', 'cod_residente', 'cod_personal', 'hash_archivo', 'fecha_hora', 'estado', 'localizador_fuente', 'verificado']);
            if (($documento['cod_residente'] !== null && $documento['cod_residente'] !== $c->residente) || ! is_bool($documento['verificado'])
                || ! in_array($documento['papel'], ['ORIGINAL', 'SOPORTE'], true)
                || ! isset($procedencia[$documento['valoracion']])
                || ($documento['papel'] === 'ORIGINAL' && ($procedencia[$documento['valoracion']]['cod_documento_clinico'] !== $documento['cod_documento_clinico']
                    || $procedencia[$documento['valoracion']]['localizador_fuente'] !== $documento['localizador_fuente']))
                || ($documento['verificado'] && (! is_string($documento['hash_archivo']) || ! preg_match('/^[a-f0-9]{64}$/D', $documento['hash_archivo'])))) {
                throw new DomainException('La constancia documental pertenece a otro contexto o carece de huella.');
            }
            $usada = array_values(array_filter($d['valoraciones'], fn ($v) => $v['origen']['registro'] === $documento['valoracion']));
            if ($usada !== [] && (! $documento['verificado'] || $documento['fecha_hora'] === null || self::fecha($documento['fecha_hora']) > $c->fechaCorte)) {
                throw new DomainException('Una valoración utiliza un documento no verificable o posterior al corte.');
            }
            if ($usada !== [] && $documento['papel'] === 'SOPORTE') {
                $soportes = array_filter($usada[0]['soportes'], fn ($s) => $s['tabla'] === 'documentos_clinicos' && $s['registro'] === $documento['cod_documento_clinico']
                    && $s['autor'] === $documento['cod_personal'] && $s['fecha'] === $documento['fecha_hora']);
                if (count($soportes) !== 1) {
                    throw new DomainException('La constancia documental no identifica su soporte original.');
                }
            }
        }
        $paquete = null;
        if ($d['gobierno'] !== null) {
            self::campos($d['gobierno'], ['cabecera', 'conocimiento']);
            $k = $d['gobierno']['conocimiento'];
            self::campos($k, ['version', 'tablas', 'contratos_relaciones', 'solo_pruebas_tecnicas', 'tipo_regla_resultado', 'contratos_extraccion']);
            $paquete = new PaqueteConocimiento($k['version'], $k['tablas'], $k['contratos_relaciones'], $k['solo_pruebas_tecnicas'], $k['tipo_regla_resultado'], $k['contratos_extraccion']);
            $cabecera = $d['gobierno']['cabecera'];
            self::campos($cabecera, ['cod_version_modelo', 'codigo_version', 'nombre', 'descripcion', 'estado', 'fecha_hora_creacion',
                'fecha_hora_vigencia', 'fecha_hora_retiro', 'motivo_cambio', 'cod_version_anterior', 'cod_usuario_creacion', 'observacion']);
            if ($cabecera['cod_version_modelo'] !== $paquete->version || $cabecera['estado'] !== 'ACTIVO'
                || $cabecera['fecha_hora_vigencia'] === null || self::fecha($cabecera['fecha_hora_vigencia']) > $c->fechaCorte
                || ($cabecera['fecha_hora_retiro'] !== null && self::fecha($cabecera['fecha_hora_retiro']) <= $c->fechaCorte)) {
                throw new DomainException('El gobierno capturado no corresponde a la versión y corte.');
            }
        }
        $valoraciones = [];
        foreach ($d['valoraciones'] as $v) {
            self::campos($v, ['elemento', 'estado', 'justificacion', 'origen', 'soportes', 'comprobaciones', 'dominios_revisados']);
            if (! array_is_list($v['soportes'])) {
                throw new DomainException('Los soportes requieren una lista explícita.');
            }
            $original = $procedencia[$v['origen']['registro']] ?? null;
            if ($original === null || $original['cod_personal'] !== $v['origen']['autor'] || $original['fecha_hora'] !== $v['origen']['fecha']
                || $original['elemento'] !== $v['elemento'] || $original['tipo_registro'] === 'ANULACION') {
                throw new DomainException('La valoración no corresponde a su procedencia original.');
            }
            if ($vistas[$v['origen']['registro']] !== count($v['soportes']) + count($v['dominios_revisados'])) {
                throw new DomainException('Los soportes usados no coinciden con la colección adquirida del juicio.');
            }
            $valoraciones[] = new ValoracionOriginalPetersen($v['elemento'], $v['estado'], $v['justificacion'], self::fuente($v['origen']),
                array_map(self::fuente(...), $v['soportes']), $v['comprobaciones'], $v['dominios_revisados']);
        }
        $salida = (new EvaluarPetersenInstitucional)->evaluar($c, $valoraciones, $paquete, exigirGobierno: true);
        $salida['gobierno']['codigo_version'] = $d['gobierno']['cabecera']['codigo_version'] ?? null;
        $salida['adquisicion'] = $d['adquisicion'];

        return CapturaInstrumentalCanonica::ordenar($salida);
    }

    /** La V1 histórica no se completa con datos que nunca adquirió. */
    private static function camposContenido(array $d, bool $salida): array
    {
        return ['contrato', 'contexto', 'valoraciones', 'gobierno', 'adquisicion', 'constancias_documentales',
            'procedencia_valoraciones', 'membresias_soportes', 'limites',
            ...(($d['contrato'] ?? null) === self::CONTRATO_MEMBRESIAS ? ['soportes_originales'] : []),
            ...($salida ? ['salida'] : [])];
    }

    private static function fuente(array $f): FuentePetersenInstitucional
    {
        self::campos($f, ['tabla', 'registro', 'residente', 'autor', 'fecha', 'episodio', 'tipo', 'vigente', 'datos_originales']);

        return new FuentePetersenInstitucional($f['tabla'], $f['registro'], $f['residente'], $f['autor'], self::fecha($f['fecha']),
            $f['episodio'], $f['tipo'], $f['vigente'], $f['datos_originales']);
    }

    private static function fecha(string $fecha): DateTimeImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}[+-]\d{2}:\d{2}$/D', $fecha)) {
            throw new DomainException('La fecha original requiere microsegundos y zona explícita.');
        }

        return new DateTimeImmutable($fecha);
    }

    private static function campos(array $d, array $obligatorios, array $opcionales = []): void
    {
        if (array_diff(array_keys($d), [...$obligatorios, ...$opcionales]) || array_diff($obligatorios, array_keys($d))) {
            throw new DomainException('Campos fuera del contrato de preparación Petersen.');
        }
    }
}
