<?php

declare(strict_types=1);

namespace MotorExperto\dominio\explicacion;

use DomainException;
use MotorExperto\investigacion\dcl\CatalogoPerfilCincoDominios;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental as Catalogo;

/**
 * Contrato de presentación del laboratorio: consolida salidas y procedencia ya calculadas.
 * No decide evaluabilidad, interpreta instrumentos ni convierte hallazgos en estados DCL.
 * Los estados añadidos son proyecciones técnicas en memoria, nunca catálogos persistidos.
 */
final class ConstructorResultadoExperimental
{
    public function construir(array $perfil, array $contexto, ?array $referencias): array
    {
        if (($perfil['uso'] ?? null) !== 'PRUEBA_TECNICA_NO_CLINICA'
            || ($contexto['uso'] ?? null) !== 'TEST_ONLY' || ($contexto['aislado'] ?? null) !== true
            || ($perfil['evaluacion'] ?? null) !== ($contexto['evaluacion'] ?? null)
            || ($perfil['residente_ficticio'] ?? null) !== ($contexto['residente_ficticio'] ?? null)
            || ($perfil['fecha_corte'] ?? null) !== ($contexto['fecha_corte'] ?? null)) {
            throw new DomainException('Contrato de resultado ajeno al laboratorio o a su evaluación.');
        }
        $admitidas = $excluidas = $trazas = $informes = [];
        $contradicciones = $contextosInsuficientes = [];
        $fuentes = $perfil['fuentes_examinadas'];
        $fuentes['referencias_dcl'] = $referencias;
        foreach (['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'] as $dominio) {
            $salida = $perfil['perfil'][$dominio]['salida'];
            $r = $salida['resultado'];
            array_push($admitidas, ...$r['evidencias_admitidas']);
            array_push($excluidas, ...$r['excluidas']);
            $trazas[$dominio] = $r['traza'];
            $informes[$dominio] = $salida['informe'];
            if ($r['estado_tecnico'] === 'EVIDENCIA_CONTRADICTORIA') {
                $contradicciones[] = $dominio;
            }
            if (! str_ends_with($r['evaluabilidad'], '-2')) {
                $contextosInsuficientes[] = $dominio;
            }
        }
        $capturasPorRegla = [];
        $hechos = $rechazosG08 = [];
        $controlesTareas = [];
        $admitidasPorId = array_fill_keys(array_column($admitidas, 'id'), true);
        $excluidasPorId = array_column($excluidas, 'motivo', 'id');
        foreach ($fuentes['g09'] as $captura) {
            $capturasPorRegla[$captura['regla']][] = $captura;
            $original = $captura['captura_original'];
            $app = $original['aplicacion'];
            $controlesTareas[$app['cod_aplicacion']] = $captura['controles_tarea'] ?? null;
            $valores = [];
            foreach ($original['respuestas'] as $respuesta) {
                $valores[] = ['registro' => $respuesta['cod_respuesta'], 'pregunta' => $respuesta['cod_pregunta'],
                    'campo' => 'valor_texto', 'valor' => $respuesta['valor_texto'], 'tipo' => get_debug_type($respuesta['valor_texto'])];
            }
            $ids = $captura['evidencias_generadas'];
            $esAdmitida = (bool) array_intersect_key($admitidasPorId, array_flip($ids));
            $motivos = $captura['motivos_no_admision'];
            foreach ($ids as $id) {
                if (isset($excluidasPorId[$id])) {
                    $motivos[] = $excluidasPorId[$id];
                }
            }
            $semanticos = [];
            foreach ($perfil['perfil'][$captura['criterio']]['salida']['resultado']['fuentes_examinadas'] as $e) {
                if (in_array($e['id'], $ids, true)) {
                    $semanticos[] = $e['valor_semantico_test_only'];
                }
            }
            $hechos[] = ['id' => 'SIM-H-'.substr(hash('sha256', serialize([$perfil['evaluacion'], $captura['regla'], $app['cod_aplicacion']])), 0, 14),
                'caso' => $perfil['residente_ficticio'], 'evaluacion' => $perfil['evaluacion'],
                'fuente' => 'aplicaciones_instrumento', 'registro_original' => $app['cod_aplicacion'],
                'valores_originales' => $valores, 'valor_semantico' => $semanticos[0] ?? null,
                'fecha_original' => $app['fecha_hora'], 'fecha_corte' => $perfil['fecha_corte'],
                'autor' => $app['cod_personal'] ?? null, 'procedencia' => 'CAPTURA_SINTETICA_DECLARADA',
                'estado_operacional' => $app['estado'], 'version_mapeo' => $captura['perfil_instrumental']['version_instrumento'],
                'perfil_mapeo' => $captura['perfil_instrumental']['codigo'], 'criterio' => $captura['criterio'],
                'regla' => $captura['regla'], 'rol' => 'INSTRUMENTAL_TEST_ONLY',
                'episodio' => $app['cod_aplicacion'], 'evidencias' => $ids, 'admitida' => $esAdmitida,
                'motivos' => array_values(array_unique($motivos))];
            if ($captura['motivos_no_admision']) {
                $rechazosG08[] = ['regla' => $captura['regla'], 'aplicacion' => $app['cod_aplicacion'],
                    'motivos' => $captura['motivos_no_admision']];
            }
        }
        $reglas = $subcomponentes = $noSolicitadas = [];
        // D-108 y catálogo conceptual vigente: no son cuatro reglas instrumentales de Memoria.
        foreach (CatalogoPerfilCincoDominios::subcomponentes() as $codigo => $meta) {
            if ($meta['dominio'] !== 'COG-MEM') {
                continue;
            }
            $subcomponentes[$codigo] = ['dominio' => 'COG-MEM', 'nombre' => $meta['nombre'], 'evaluado' => false,
                'resultado' => null, 'reglas' => [], 'motivo_no_emision' => 'CONTRATO_DE_TAREA_PENDIENTE'];
        }
        foreach (Catalogo::todas() as $id => $meta) {
            $filas = array_values(array_filter($trazas[$meta['criterio']], static fn ($t) => $t['regla'] === $id));
            $estado = 'NO_EVALUADA';
            foreach ($filas as $fila) {
                if ($fila['cumple']) {
                    $estado = 'CUMPLE';
                    break;
                }
                if (! in_array(null, $fila['condiciones'], true)) {
                    $estado = 'NO_CUMPLE';
                }
            }
            $solicitada = isset($capturasPorRegla[$id]);
            $g = $perfil['perfil'][$meta['criterio']]['salida']['resultado'];
            $motivo = null;
            if (! $solicitada) {
                $noSolicitadas[] = $id;
                $motivo = 'TAREA_NO_SOLICITADA';
            } elseif ($g['estado_tecnico'] === 'EVIDENCIA_CONTRADICTORIA') {
                $motivo = 'CONTRADICCION_DOCUMENTADA';
            } elseif (! $filas) {
                $tieneAdmitidas = array_filter($g['evidencias_admitidas'], static fn ($e) => $e['regla'] === $id);
                $motivo = $tieneAdmitidas ? 'COMPUERTA_NO_HABILITA' : 'FUENTE_NO_ADMISIBLE';
            } elseif ($estado !== 'CUMPLE') {
                $motivo = $estado === 'NO_CUMPLE' ? 'CONDICION_POSITIVA_NO_CUMPLIDA' : 'CONDICION_DESCONOCIDA';
            }
            $reglas[$id] = ['dominio' => $meta['criterio'], 'subcomponente' => $meta['componente'],
                'version' => Catalogo::VERSION, 'solicitada' => $solicitada, 'evaluada' => (bool) $filas,
                'estado' => $estado, 'condiciones_y_soportes' => $filas, 'motivo_no_emision' => $motivo];
            $subcomponentes[$meta['componente']] = ['dominio' => $meta['criterio'], 'nombre' => $meta['descripcion'],
                'evaluado' => (bool) $filas, 'resultado' => array_intersect_key($g['consecuencias_candidatas'], [$meta['consecuencia'] => true]),
                'reglas' => [$id], 'motivo_no_emision' => $motivo];
        }
        $memoria = $perfil['perfil']['COG-MEM']['salida'];
        $confusores = [];
        foreach (['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'] as $dominio) {
            $particular = $contexto['contextos_dominios'][$dominio] ?? [];
            $confusores[$dominio] = ['confusor_invalidante' => array_key_exists('confusor_invalidante', $particular)
                ? $particular['confusor_invalidante'] : ($contexto['confusor_invalidante'] ?? null)];
        }
        $confusores['COG-MEM'] = $contexto['contexto_memoria'];
        $reglasFuente = $reglas;
        $declarativas = [];
        $usaDeclaracion = false;
        foreach (['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'] as $dominio) {
            $d = $perfil['perfil'][$dominio]['salida']['resultado']['inferencia_declarativa'] ?? null;
            if ($d === null) {
                continue;
            }
            $usaDeclaracion = true;
            foreach ($d['reglas'] as $codigo => $regla) {
                $fuente = $reglasFuente[$codigo] ?? null;
                $sinMedicion = $fuente !== null && (! $fuente['evaluada'] || $fuente['estado'] === 'NO_EVALUADA');
                $declarativas[$codigo] = ['dominio' => $dominio, 'version' => $d['version_conocimiento'],
                    'estado' => $sinMedicion ? 'NO_EVALUADA' : $regla['estado'], 'estado_nucleo' => $regla['estado'],
                    'evaluada' => ! $sinMedicion, 'condiciones_y_soportes' => $regla['condiciones'],
                    'soportes_originales' => $regla['soportes_originales'], 'consecuencia' => $regla['consecuencia'],
                    'soportes_efectivos' => $regla['soportes_efectivos'], 'grupos_soporte' => $regla['grupos_soporte'],
                    'unidad_soporte' => $regla['unidad_soporte'],
                    'alcance' => $regla['alcance'], 'evaluaciones_unidad' => $regla['evaluaciones_unidad'],
                    'nombre' => $regla['nombre'], 'fundamento' => $regla['fundamento']];
            }
            foreach (Catalogo::todas() as $meta) {
                if ($meta['criterio'] !== $dominio) {
                    continue;
                }
                $coinciden = array_filter($d['reglas'], static fn ($r) => $r['consecuencia'] === $meta['consecuencia']);
                $cumplen = array_filter($coinciden, static fn ($r) => $r['estado'] === 'CUMPLE');
                $subcomponentes[$meta['componente']]['reglas'] = array_keys($coinciden);
                $subcomponentes[$meta['componente']]['evaluado'] = (bool) $coinciden;
                $noHabilitado = in_array($dominio, $contextosInsuficientes, true);
                $motivoFuente = $subcomponentes[$meta['componente']]['motivo_no_emision'];
                $subcomponentes[$meta['componente']]['motivo_no_emision'] = in_array($dominio, $contradicciones, true)
                    ? 'CONTRADICCION_DOCUMENTADA' : ($noHabilitado ? 'COMPUERTA_NO_HABILITA'
                        : ($cumplen ? null : (! $coinciden ? 'SIN_REGLA_ELEGIBLE'
                            : (in_array($motivoFuente, ['TAREA_NO_SOLICITADA', 'FUENTE_NO_ADMISIBLE', 'CONDICION_DESCONOCIDA'], true)
                                ? $motivoFuente : 'CONDICION_POSITIVA_NO_CUMPLIDA'))));
            }
        }
        if ($usaDeclaracion) {
            $reglas = $declarativas;
        }

        return ['caso' => $perfil['residente_ficticio'], 'evaluacion' => $perfil['evaluacion'], 'fecha_corte' => $perfil['fecha_corte'],
            'catalogo_conceptual' => CatalogoPerfilCincoDominios::construir(),
            'manifiesto_versiones' => $perfil['versiones'], 'fuentes_examinadas' => $fuentes, 'hechos_instrumentales' => $hechos,
            'evidencias_admitidas' => ['g09' => $admitidas, 'memoria' => array_values(array_filter($fuentes['memoria'],
                static fn ($e) => in_array($e['admisibilidad'], ['ADMISIBLE', 'ADMISIBLE_CON_ADVERTENCIA'], true)))],
            'evidencias_excluidas' => ['g09' => $excluidas, 'g08' => $rechazosG08, 'memoria' => $memoria['traza']['sin_emision'],
                'auditoria_dcl' => $perfil['puente_dcl']['auditoria'] ?? null],
            'evidencias_faltantes' => ['tareas_no_solicitadas' => $noSolicitadas, 'contextos_no_habilitados' => $contextosInsuficientes,
                'soportes_dcl' => $perfil['clasificacion_dcl']['traza']['sin_soporte'],
                'dominios_dcl_no_interpretables' => array_keys(array_filter($perfil['clasificacion_dcl']['traza']['dominios'],
                    static fn ($estado) => in_array($estado, ['U', 'I'], true))),
                'requisitos_dcl_desconocidos' => array_keys(array_filter($perfil['clasificacion_dcl']['traza']['requisitos'],
                    static fn ($estado) => $estado === 'U'))],
            'perfil_cinco_dominios' => $perfil['perfil'], 'subcomponentes' => $subcomponentes,
            'reglas_examinadas' => ['g09' => $reglas, 'condiciones_fuente_g09' => $reglasFuente, 'memoria' => $memoria['traza']['reglas']],
            'contradicciones' => $contradicciones, 'confusores' => $confusores,
            'controles_y_confusores_tareas' => $controlesTareas,
            'requisitos_dcl' => $perfil['clasificacion_dcl']['traza']['requisitos'], 'petersen' => $perfil['clasificacion_dcl'],
            'resumen' => ['g09' => $informes, 'memoria' => ['evaluabilidad' => $memoria['evaluabilidad'], 'resultado' => $memoria['resultado']],
                'petersen' => $perfil['informe_dcl']],
            'traza_tecnica' => ['g09' => $trazas, 'admisibilidad_tareas' => $controlesTareas,
                'memoria' => $memoria['traza'], 'petersen' => $perfil['clasificacion_dcl']['traza']]];
    }
}
