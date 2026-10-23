<?php

declare(strict_types=1);

namespace MotorExperto\adaptadores\entrada;

use DateTimeImmutable;
use DomainException;
use MotorExperto\dominio\conocimiento\AdmisibilidadCognitivaInstitucional;
use MotorExperto\dominio\conocimiento\AdmisibilidadFuenteCOGMEM;
use MotorExperto\dominio\conocimiento\MapeadorSemantico;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\instrumentos\CorteDocumentoInstrumental;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;

/** Traduce respuestas originales identificadas a hechos; nunca usa z, clasificación ni texto libre como diagnóstico. */
final class ConstructorMemoriaInstrumental
{
    public function construir(PaqueteConocimiento $p, ContextoEjecucion $contexto, array $capturas, array $vinculos, bool $conservarEpisodios = false): array
    {
        if (($capturas['disponible'] ?? null) !== true || $contexto->version !== $p->version) {
            throw new DomainException('Se requieren fuentes autorizadas y la misma versión de conocimiento.');
        }
        $m = new MemoriaTrabajo($contexto);
        $excluidas = [];
        foreach ($vinculos as $mapeo => $vinculo) {
            if (! is_string($mapeo) || ! is_array($vinculo) || array_diff(array_keys($vinculo), ['cod_instrumento', 'codigo_instrumento', 'version_instrumento', 'cod_pregunta', 'campo', 'componente', 'perfil_interpretativo'])) {
                throw new DomainException('Vínculo instrumental fuera del contrato.');
            }
            foreach (['cod_instrumento', 'codigo_instrumento', 'version_instrumento', 'cod_pregunta', 'campo'] as $campo) {
                if (! is_string($vinculo[$campo] ?? null) || $vinculo[$campo] === '') {
                    throw new DomainException('El vínculo requiere identidad, versión, pregunta y campo originales.');
                }
            }
            $mapa = $p->fila('mapeos_variables_fuente', $mapeo);
            $fuente = $p->fila('fuentes_datos_expertas', $mapa['cod_fuente_dato_experta']);
            foreach (['campo_pk_raiz' => 'cod_aplicacion', 'campo_residente' => 'cod_residente', 'campo_temporal' => 'fecha_hora', 'campo_personal' => 'cod_personal'] as $campoFuente => $esperado) {
                if (($fuente[$campoFuente] ?? null) !== $esperado) {
                    throw new DomainException('La fuente no declara las columnas originales de aplicación, residente, fecha y autor.');
                }
            }
            if ($fuente['tabla_raiz'] !== 'aplicaciones_instrumento'
                || ($p->contratosExtraccion[$mapa['tipo_extraccion']] ?? null) !== 'SELECTOR_COMPONENTE'
                || $mapa['clave_selector'] !== $vinculo['cod_pregunta']
                || ! in_array($vinculo['campo'], ['valor_logico', 'valor_texto'], true)) {
                throw new DomainException('El mapeo exige selector de pregunta y un literal lógico/textual original; no admite umbrales implícitos.');
            }
            foreach ($capturas['capturas'] as $a) {
                if ($a['cod_residente'] !== $contexto->residente) {
                    throw new DomainException('La captura pertenece a otro residente.');
                }
                if ($a['cod_instrumento'] !== $vinculo['cod_instrumento'] || $a['codigo_instrumento'] !== $vinculo['codigo_instrumento']
                    || $a['version_instrumento'] !== $vinculo['version_instrumento']) {
                    continue;
                }
                if (! is_int($a['edad_en_aplicacion'] ?? null) || $a['edad_en_aplicacion'] < 60) {
                    $excluidas[] = ['aplicacion' => $a['cod_aplicacion'], 'mapeo' => $mapeo,
                        'motivos' => ['FUERA_DE_COHORTE_60_O_MAS_O_EDAD_DESCONOCIDA']];

                    continue;
                }
                // La falta de perfil numérico no invalida un literal original. Ningún otro rechazo se omite.
                $motivos = array_values(array_diff($a['motivos'], ['SIN_PERFIL_EXPLICITO']));
                if (($a['procedencia']['cod_documento_clinico'] ?? null) !== null
                    && ! CorteDocumentoInstrumental::admite($a['documento_original'] ?? null, $contexto->fechaCorte)) {
                    $motivos[] = 'DOCUMENTO_FUERA_DEL_CORTE_O_SIN_FECHA';
                }
                if ($motivos !== []) {
                    $excluidas[] = ['aplicacion' => $a['cod_aplicacion'], 'mapeo' => $mapeo, 'motivos' => $motivos];

                    continue;
                }
                $filas = array_values(array_filter($a['respuestas_originales'], static fn ($r) => $r['cod_pregunta'] === $vinculo['cod_pregunta']));
                if (count($filas) > 1) {
                    throw new DomainException('Más de una respuesta original para la misma pregunta y aplicación.');
                }
                $r = $filas[0] ?? null;
                $pregunta = $r['pregunta_actual'] ?? $a['preguntas_declarativas_originales'][$vinculo['cod_pregunta']] ?? null;
                if ($pregunta !== null && ($pregunta['cod_instrumento'] ?? null) !== $a['cod_instrumento']) {
                    throw new DomainException('La pregunta declarada pertenece a otro instrumento.');
                }
                $campo = $vinculo['campo'];
                $original = $r[$campo] ?? null;
                // La pareja opción/literal conserva el registro nominal; no representa dos mediciones.
                $esNominalObservacional = $r !== null && $campo === 'valor_texto' && ($a['instrumento_actual']['tipo'] ?? null) === 'OBSERVACIONAL'
                    && in_array($pregunta['tipo_respuesta'] ?? null, ['OPCION', 'OPCION_UNICA'], true);
                $tipoCorrecto = $r === null || ($campo === 'valor_logico' ? in_array($r['tipo_respuesta'], ['LOGICO', 'BOOLEANO'], true)
                    : (in_array($r['tipo_respuesta'], ['TEXTO', 'TEXTO_CORTO', 'TEXTO_LARGO'], true)
                        || ($esNominalObservacional && $r['tipo_respuesta'] === $pregunta['tipo_respuesta'])));
                $otros = array_diff(['cod_opcion', 'valor_numero', 'valor_logico', 'valor_texto'], [$campo]);
                if ($esNominalObservacional) {
                    $otros = array_diff($otros, ['cod_opcion']);
                    $opcion = $r['opcion_actual'] ?? null;
                    if ($r !== null && (! is_array($opcion) || ! is_string($r['cod_opcion'] ?? null)
                        || ($opcion['cod_opcion'] ?? null) !== $r['cod_opcion']
                        || ($r['cod_pregunta'] ?? null) !== $vinculo['cod_pregunta']
                        || ($pregunta['cod_pregunta'] ?? null) !== $vinculo['cod_pregunta']
                        || ($opcion['cod_pregunta'] ?? null) !== $vinculo['cod_pregunta']
                        || ! is_string($opcion['valor'] ?? null)
                        || ($original !== null && $original !== $opcion['valor']))) {
                        throw new DomainException('La opción original y el literal observacional son incompatibles.');
                    }
                }
                $ambiguo = $r !== null && count(array_filter($otros, static fn ($c) => ($r[$c] ?? null) !== null && $r[$c] !== '')) > 0;
                if (! $tipoCorrecto || $ambiguo || ($r !== null && (! $r['coherente'] || $r['cod_aplicacion'] !== $a['cod_aplicacion']))) {
                    throw new DomainException('Respuesta incompatible con su pregunta, campo o aplicación.');
                }
                $tipado = $campo === 'valor_logico' ? match (true) {
                    $original === true || $original === 1 || $original === '1' => true,
                    $original === false || $original === 0 || $original === '0' => false,
                    default => null,
                } : (is_string($original) ? $original : null);
                $variable = $p->fila('variables_expertas', $mapa['cod_variable_experta']);
                $esMemoriaInstrumental = $p->red->nodo($variable['cod_nodo_semantico'])['codigo_semantico'] === 'VAR-MEM-INST-01';
                $atencion = $conservarEpisodios ? ($a['aplicacion_actual']['cod_atencion'] ?? null) : null;
                if ($atencion !== null && (! is_string($atencion) || $atencion === ''
                    || ($a['atencion_original']['cod_atencion'] ?? null) !== $atencion
                    || ($a['atencion_original']['cod_residente'] ?? null) !== $contexto->residente)) {
                    throw new DomainException('La atención original no acredita el episodio del mismo residente.');
                }
                $unidad = $atencion === null ? $a['cod_aplicacion'] : 'atencion:'.$atencion;
                $mediciones = [];
                $perfil = $vinculo['perfil_interpretativo'] ?? null;
                foreach (is_array($perfil) && is_array($perfil['mediciones_requeridas'] ?? null) ? $perfil['mediciones_requeridas'] : [] as $nombre => $selector) {
                    if (! is_array($selector)) {
                        continue;
                    }
                    $originales = array_values(array_filter($a['respuestas_originales'], static fn ($r) => $r['cod_pregunta'] === ($selector['cod_pregunta'] ?? null)));
                    if (count($originales) !== 1) {
                        continue;
                    }
                    $medida = $originales[0];
                    if (($medida['coherente'] ?? false) !== true || $medida['cod_aplicacion'] !== $a['cod_aplicacion']
                        || ($medida['pregunta_actual']['cod_instrumento'] ?? null) !== $a['cod_instrumento']
                        || ($medida['pregunta_actual']['cod_pregunta'] ?? null) !== $medida['cod_pregunta']
                        || ! in_array($medida['pregunta_actual']['tipo_respuesta'] ?? null, ['NUMERO', 'NUMERICO'], true)
                        || $medida['tipo_respuesta'] !== $medida['pregunta_actual']['tipo_respuesta']
                        || count(array_filter(['valor_logico', 'valor_texto', 'cod_opcion'], static fn ($campo) => ($medida[$campo] ?? null) !== null && $medida[$campo] !== '')) > 0
                        || ($selector['campo'] ?? null) !== 'valor_numero') {
                        continue;
                    }
                    $mediciones[$nombre] = ['cod_aplicacion' => $a['cod_aplicacion'], 'cod_pregunta' => $medida['cod_pregunta'],
                        'cod_respuesta' => $medida['cod_respuesta'], 'campo' => 'valor_numero', 'valor_original' => $medida['valor_numero'] ?? null];
                }
                $f = new FuenteBrutaCOGMEM($fuente['cod_fuente_dato_experta'], 'aplicaciones_instrumento', $vinculo['cod_pregunta'],
                    $a['cod_aplicacion'], $a['cod_residente'], new DateTimeImmutable($a['fecha_hora']), $original, $tipado,
                    $a['cod_personal'], true, $a['estado_original'] === 'COMPLETA', atencion: $atencion, contexto: [
                        'unidad_soporte' => $unidad, 'cod_respuesta' => $r['cod_respuesta'] ?? null,
                        'componente_instrumental_verificado' => $esMemoriaInstrumental && $r !== null
                            && ($a['instrumento_actual']['tipo'] ?? null) !== 'OBSERVACIONAL',
                        'cod_pregunta' => $vinculo['cod_pregunta'], 'campo_original' => $campo,
                        'procedencia' => $a['procedencia'], 'instrumento' => $a['instrumento_actual'],
                        'pregunta_original' => $pregunta, 'fuente_edad' => $a['fuente_edad'],
                    ] + ($esNominalObservacional ? ['naturaleza_fuente' => 'OBSERVACION_CLINICA',
                        'opcion_original' => $r['opcion_actual'] ?? null] : [])
                        + ($conservarEpisodios ? ['unidad_conjuncion' => $a['cod_aplicacion'],
                            'componente_declarado' => $vinculo['componente'] ?? null, 'perfil_interpretativo' => $perfil,
                            'mediciones_interpretacion' => $mediciones] : []));
                $e = (new MapeadorSemantico)->mapear($p, $contexto, $f, $mapeo);
                $e = (new AdmisibilidadCognitivaInstitucional)->validar($p, $e);
                $criterios = [];
                foreach ($p->tabla('criterios_dominios_resultado') as $criterio) {
                    $id = $criterio['cod_nodo_criterio'];
                    if ($p->variableParticipa($e->variable, $id)) {
                        $criterios[] = $id;
                        if ($p->red->nodo($id)['codigo_semantico'] === 'COG-MEM') {
                            $e = (new AdmisibilidadFuenteCOGMEM)->validar($p, $e);
                        }
                    }
                }
                $m->incorporar($e);
                foreach ($criterios as $criterio) {
                    $m->vincular($criterio, $e->id, $esNominalObservacional ? 'OBSERVACION_NOMINAL_ORIGINAL' : 'RESPUESTA_INSTRUMENTAL_ORIGINAL');
                }
            }
        }

        return ['memoria' => $m, 'fuentes_excluidas' => $excluidas];
    }
}
