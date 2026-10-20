<?php

declare(strict_types=1);

namespace MotorExperto\dominio\conocimiento\versiones;

use DomainException;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorReglas;

/** Gobierno declarativo del contrato E–H ya adoptado; los estados provienen del control de originales. */
final class PaquetePetersenInstitucional
{
    public const CODIGO = 'ORION-PETERSEN-1';

    public const FUNDAMENTO = 'Gobierno del contrato PETERSEN-INSTITUCIONAL-1, E–H adoptado el 19/10/2026. Cuatro patrones orientativos desde originales admisibles; sin equivalencia observación/dominio ni acreditación automática de C03. Revisión profesional posterior pendiente.';

    public const CRITERIO = 'DCL-PETERSEN';

    private const VARIABLES = ['C01' => 'REQUISITO', 'C02' => 'REQUISITO', 'C03' => 'REQUISITO',
        'C04' => 'REQUISITO', 'C05' => 'REQUISITO', 'PERFIL' => 'PERFIL', 'MEMORIA' => 'MEMORIA', 'CANTIDAD' => 'CANTIDAD'];

    private const VALORES = ['REQUISITO' => ['S', 'N', 'U', 'I', 'X'], 'PERFIL' => ['INTERPRETABLE', 'INCOMPLETO'],
        'MEMORIA' => ['A', 'P', 'U'], 'CANTIDAD' => ['NINGUNO', 'UNICO', 'MULTIPLE'], 'RESULTADO' => ['PET-01', 'PET-02', 'PET-03', 'PET-04']];

    public static function contratos(): array
    {
        return ['relaciones' => [['tipo_relacion' => 'APORTA_EVIDENCIA', 'tipo_origen' => 'VARIABLE',
            'tipo_destino' => 'CRITERIO', 'modalidades' => [null], 'habilita_participacion' => true]],
            'extracciones' => [], 'tipo_regla_resultado' => 'RESULTADO'];
    }

    private static function id(string $tipo, string $codigo): string
    {
        return $tipo.'_'.substr(hash('sha256', self::CODIGO.'|'.$codigo), 0, 16);
    }

    public static function condiciones(string $patron): array
    {
        if (! in_array($patron, self::VALORES['RESULTADO'], true)) {
            throw new DomainException('Patrón ajeno al contrato Petersen adoptado.');
        }

        return ['C01' => 'S', 'C02' => 'S', 'C03' => 'S', 'C04' => 'S', 'C05' => 'S', 'PERFIL' => 'INTERPRETABLE',
            'MEMORIA' => in_array($patron, ['PET-01', 'PET-02'], true) ? 'A' : 'P',
            'CANTIDAD' => in_array($patron, ['PET-01', 'PET-03'], true) ? 'UNICO' : 'MULTIPLE'];
    }

    public function construir(string $version, string $autor, string $fecha): PaqueteConocimiento
    {
        $t = array_fill_keys(array_keys(PaqueteConocimiento::CLAVES), []);
        $sello = ['estado' => 'ACTIVO', 'fecha_hora_creacion' => $fecha, 'cod_usuario_creacion' => $autor, 'observacion' => self::FUNDAMENTO];
        $t['nodos_semanticos'][] = $sello + ['cod_nodo_semantico' => self::id('N', self::CRITERIO), 'cod_version_modelo' => $version,
            'codigo_semantico' => self::CRITERIO, 'nombre' => 'Patrón orientativo de Petersen', 'tipo_nodo' => 'CRITERIO', 'definicion' => self::FUNDAMENTO];
        foreach (self::VALORES as $dominio => $valores) {
            $t['dominios_valores_expertos'][] = $sello + ['cod_dominio_valores' => self::id('D', $dominio), 'cod_version_modelo' => $version,
                'codigo_dominio' => 'PET-'.$dominio, 'nombre' => $dominio, 'descripcion' => self::FUNDAMENTO, 'tipo_dominio' => $dominio === 'RESULTADO' ? 'RESULTADO' : 'INSTRUMENTAL'];
            foreach ($valores as $valor) {
                $t['valores_semanticos'][] = $sello + ['cod_valor_semantico' => self::id('V', $dominio.'|'.$valor),
                    'cod_dominio_valores' => self::id('D', $dominio), 'codigo_valor' => $valor, 'nombre' => $valor,
                    'definicion_semantica' => self::FUNDAMENTO, 'estado_aprobacion' => 'APROBADO'];
            }
        }
        $t['criterios_dominios_resultado'][] = $sello + ['cod_criterio_dominio_resultado' => self::id('C', self::CRITERIO),
            'cod_nodo_criterio' => self::id('N', self::CRITERIO), 'cod_dominio_valores' => self::id('D', 'RESULTADO')];
        foreach (self::VARIABLES as $codigo => $dominio) {
            $t['nodos_semanticos'][] = $sello + ['cod_nodo_semantico' => self::id('N', $codigo), 'cod_version_modelo' => $version,
                'codigo_semantico' => 'PET-VAR-'.$codigo, 'nombre' => match ($codigo) {
                    'PERFIL' => 'Cinco dominios interpretables', 'MEMORIA' => 'Estado original de Memoria',
                    'CANTIDAD' => 'Número de dominios alterados', default => 'Requisito '.$codigo,
                }, 'tipo_nodo' => 'VARIABLE', 'definicion' => 'Estado derivado después del control de originales; no es una clasificación esperada.'];
            $t['variables_expertas'][] = $sello + ['cod_variable_experta' => self::id('E', $codigo), 'cod_version_modelo' => $version,
                'cod_nodo_semantico' => self::id('N', $codigo), 'cod_nodo_propietario_primario' => self::id('N', self::CRITERIO),
                'cod_dominio_valores' => self::id('D', $dominio), 'tipo_semantico' => 'INSTRUMENTAL', 'papel_inferencial' => 'PRIMARIO'];
            $t['relaciones_semanticas'][] = $sello + ['cod_relacion_semantica' => self::id('R', $codigo), 'cod_version_modelo' => $version,
                'cod_nodo_origen' => self::id('N', $codigo), 'cod_nodo_destino' => self::id('N', self::CRITERIO),
                'tipo_relacion' => 'APORTA_EVIDENCIA', 'modalidad_relacion' => null, 'significado' => self::FUNDAMENTO];
        }
        foreach (self::VALORES['RESULTADO'] as $patron) {
            $id = self::id('G', $patron);
            $t['reglas_expertas'][] = $sello + ['cod_regla_experta' => $id, 'cod_version_modelo' => $version, 'codigo_regla' => $patron,
                'nombre' => match ($patron) {
                    'PET-01' => 'Amnésico de dominio único', 'PET-02' => 'Amnésico multidominio',
                    'PET-03' => 'No amnésico de dominio único', 'PET-04' => 'No amnésico multidominio',
                }, 'tipo_regla' => 'RESULTADO', 'descripcion' => self::FUNDAMENTO, 'cod_regla_anterior' => null,
                'estado_revision' => 'PENDIENTE', 'cod_usuario_revision' => null, 'fecha_hora_revision' => null, 'justificacion_revision' => null];
            foreach (self::condiciones($patron) as $variable => $valor) {
                $t['condiciones_regla_experta'][] = ['cod_condicion_regla' => self::id('K', $patron.'|'.$variable),
                    'cod_regla_experta' => $id, 'cod_variable_experta' => self::id('E', $variable),
                    'cod_valor_semantico' => self::id('V', self::VARIABLES[$variable].'|'.$valor)];
            }
            $t['consecuencias_regla_experta'][] = ['cod_consecuencia_regla' => self::id('S', $patron), 'cod_regla_experta' => $id,
                'cod_criterio_dominio_resultado' => self::id('C', self::CRITERIO), 'cod_valor_semantico' => self::id('V', 'RESULTADO|'.$patron)];
        }
        $c = self::contratos();

        return new PaqueteConocimiento($version, $t, $c['relaciones'], false, $c['tipo_regla_resultado'], []);
    }

    /** Identidades SQL regeneradas admitidas; semántica cerrada para impedir quitar requisitos desde el gestor. */
    public function validar(PaqueteConocimiento $p): void
    {
        $codigos = array_column($p->tabla('reglas_expertas'), 'codigo_regla');
        sort($codigos);
        if (count($p->tabla('reglas_expertas')) !== 4 || count($p->tabla('variables_expertas')) !== 8
            || count($p->tabla('criterios_dominios_resultado')) !== 1 || $p->tipoReglaResultado !== 'RESULTADO'
            || $p->tabla('mapeos_variables_fuente') !== [] || $p->tabla('fuentes_datos_expertas') !== [] || $codigos !== self::VALORES['RESULTADO']) {
            throw new DomainException('El gobierno Petersen debe conservar las cuatro reglas y sus estados derivados adoptados.');
        }
        foreach (['nodos_semanticos', 'dominios_valores_expertos', 'valores_semanticos', 'criterios_dominios_resultado'] as $tabla) {
            foreach ($p->tabla($tabla) as $dependencia) {
                if ($dependencia['estado'] !== 'ACTIVO' || ($tabla === 'valores_semanticos' && $dependencia['estado_aprobacion'] !== 'APROBADO')) {
                    throw new DomainException('Una dependencia del gobierno Petersen no está habilitada.');
                }
            }
        }
        $criterio = $p->tabla('criterios_dominios_resultado')[0];
        if ($p->red->nodo($criterio['cod_nodo_criterio'])['codigo_semantico'] !== self::CRITERIO
            || $p->fila('dominios_valores_expertos', $criterio['cod_dominio_valores'])['codigo_dominio'] !== 'PET-RESULTADO') {
            throw new DomainException('Criterio ajeno al contrato Petersen.');
        }
        foreach ($p->tabla('variables_expertas') as $v) {
            $codigo = substr($p->red->nodo($v['cod_nodo_semantico'])['codigo_semantico'], 8);
            if (! isset(self::VARIABLES[$codigo]) || ! $p->variableParticipa($v['cod_variable_experta'], $criterio['cod_nodo_criterio'])
                || $v['estado'] !== 'ACTIVO' || $v['cod_nodo_propietario_primario'] !== $criterio['cod_nodo_criterio']
                || $p->fila('dominios_valores_expertos', $v['cod_dominio_valores'])['codigo_dominio'] !== 'PET-'.self::VARIABLES[$codigo]) {
                throw new DomainException('Variable Petersen sin contrato de derivación o participación.');
            }
        }
        foreach ($p->tabla('reglas_expertas') as $r) {
            $esperadas = self::condiciones($r['codigo_regla']);
            $actuales = [];
            foreach (array_filter($p->tabla('condiciones_regla_experta'), fn ($c) => $c['cod_regla_experta'] === $r['cod_regla_experta']) as $c) {
                $v = $p->fila('variables_expertas', $c['cod_variable_experta']);
                $codigo = substr($p->red->nodo($v['cod_nodo_semantico'])['codigo_semantico'], 8);
                if (isset($actuales[$codigo])) {
                    throw new DomainException('Una regla Petersen repite una condición.');
                }
                $valor = $p->fila('valores_semanticos', $c['cod_valor_semantico']);
                if ($valor['estado'] !== 'ACTIVO' || $valor['estado_aprobacion'] !== 'APROBADO') {
                    throw new DomainException('Condición Petersen sin valor habilitado.');
                }
                $actuales[$codigo] = $valor['codigo_valor'];
            }
            ksort($esperadas);
            ksort($actuales);
            $salidas = array_values(array_filter($p->tabla('consecuencias_regla_experta'), fn ($c) => $c['cod_regla_experta'] === $r['cod_regla_experta']));
            if ($actuales !== $esperadas || $r['tipo_regla'] !== 'RESULTADO' || count($salidas) !== 1
                || $salidas[0]['cod_criterio_dominio_resultado'] !== $criterio['cod_criterio_dominio_resultado']
                || $p->fila('valores_semanticos', $salidas[0]['cod_valor_semantico'])['codigo_valor'] !== $r['codigo_regla']) {
                throw new DomainException('Las condiciones y consecuencias Petersen conservan el contrato E–H; cambiarlo requiere decisión institucional.');
            }
        }
    }

    public function evaluar(PaqueteConocimiento $p, array $estados): array
    {
        $this->validar($p);
        $hechos = [];
        foreach ($p->tabla('variables_expertas') as $v) {
            $codigo = substr($p->red->nodo($v['cod_nodo_semantico'])['codigo_semantico'], 8);
            foreach ($p->tabla('valores_semanticos') as $valor) {
                if ($valor['cod_dominio_valores'] === $v['cod_dominio_valores'] && $valor['codigo_valor'] === ($estados[$codigo] ?? null)) {
                    $hechos[] = ['id' => 'DERIVADO:'.$codigo, 'variable' => $v['cod_variable_experta'], 'valor' => $valor['cod_valor_semantico']];
                }
            }
        }
        $reglas = $activadas = [];
        foreach ($p->tabla('reglas_expertas') as $r) {
            $condiciones = (new EvaluadorReglas)->evaluarCondiciones(array_values(array_filter($p->tabla('condiciones_regla_experta'),
                fn ($c) => $c['cod_regla_experta'] === $r['cod_regla_experta'])), $hechos);
            $habilitada = $r['estado'] === 'ACTIVO' && ($r['estado_revision'] ?? null) !== 'RECHAZADA';
            $cumple = $habilitada && ! in_array('NO_CUMPLE', array_column($condiciones, 'estado'), true);
            if ($cumple) {
                $activadas[] = $r['codigo_regla'];
            }
            $reglas[] = ['regla' => $r['cod_regla_experta'], 'codigo' => $r['codigo_regla'], 'estado' => $r['estado'],
                'estado_revision' => $r['estado_revision'] ?? null, 'habilitada' => $habilitada, 'cumple' => $cumple, 'condiciones' => $condiciones];
        }
        if (count($activadas) > 1) {
            throw new DomainException('Gobierno Petersen ambiguo: más de un patrón activado.');
        }

        return ['version' => $p->version, 'patron' => $activadas[0] ?? null, 'estados_derivados' => $estados, 'reglas' => $reglas, 'persistido' => false];
    }
}
