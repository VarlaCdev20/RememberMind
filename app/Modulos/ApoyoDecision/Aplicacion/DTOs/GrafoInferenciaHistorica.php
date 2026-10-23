<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

/** Proyección de una lectura D-137 validada. No consulta fuentes actuales ni ejecuta reglas. */
final class GrafoInferenciaHistorica
{
    public function presentar(array $lectura, bool $permisoInstrumental): array
    {
        $grafo = ['nodos' => [], 'aristas' => [], 'reglas' => [], 'aviso' => null,
            'evaluacion' => $lectura['evaluacion']['id'] ?? null, 'version' => $lectura['evaluacion']['version'] ?? null];
        $detalle = $lectura['detalle'];
        if (($lectura['prueba_tecnica'] ?? false) || isset($lectura['simulacion_local'])) {
            $grafo['aviso'] = 'Esta ejecución utiliza otro contrato de conservación. No se presenta como traza clínica D-137.';

            return $grafo;
        }
        if (! $grafo['evaluacion'] || ! $detalle['traza']) {
            $grafo['aviso'] = 'No hay una traza histórica conservada para esta área.';

            return $grafo;
        }
        $fallo = (bool) $detalle['integridad'];
        $nodos = [];
        $agregar = function (string $id, string $tipo, string $nombre, int $columna, array $datos = [], string $estado = '') use (&$nodos, $grafo): void {
            $estado = $estado ?: 'Referencia conservada';
            $nodos[$id] = compact('id', 'tipo', 'nombre', 'columna', 'datos', 'estado')
                + ['version' => $grafo['version'], 'evaluacion' => $grafo['evaluacion']];
        };
        $aristas = [];
        $conectar = function (string $origen, string $destino, string $relacion) use (&$aristas): void {
            $aristas[$origen.'>'.$destino.':'.$relacion] = compact('origen', 'destino', 'relacion');
        };
        foreach ($detalle['evidencias'] as $id => $e) {
            $restringida = $e['tabla'] === 'aplicaciones_instrumento' && ! $permisoInstrumental;
            $agregar('fuente:'.$id, 'Fuente', $restringida ? 'Fuente instrumental restringida' : $e['fuente'], 0,
                $restringida ? ['Acceso' => 'Requiere permiso instrumental.'] : [
                    'Registro' => $e['registro'], 'Tipo' => $e['tabla'], 'Campo' => $e['campo'] ?? 'No conservado en esta evaluación',
                    'Valor original' => 'No conservado en esta evaluación', 'Fecha original' => 'No conservado en esta evaluación',
                    'Profesional original' => 'No conservado en esta evaluación',
                    'Advertencia' => 'El dato fuente actual se consulta por separado; no sustituye al histórico.',
                ]);
            $agregar('mapeo:'.$id, 'Mapeo', $restringida ? 'Mapeo restringido' : 'Mapeo semántico', 1,
                $restringida ? [] : ['Identidad' => $e['mapeo'], 'Variable' => $e['variable'],
                    'Transformación original' => 'No conservado en esta evaluación']);
            $agregar('evidencia:'.$id, 'Evidencia', $restringida ? 'Evidencia instrumental restringida' : $e['nombre'], 2,
                $restringida ? ['Acceso' => 'Requiere permiso instrumental.'] : [
                    'Identidad' => $id, 'Valor semántico' => $e['valor'] ?? 'No registrado',
                    'Admisibilidad' => $e['admisibilidad'], 'Representación' => $e['representacion'],
                    'Rol' => $e['rol'], 'Participación' => $e['participacion'], 'Uso' => $e['papel'],
                    'Incorporación a evaluación' => $e['fecha'] ?? 'No conservado en esta evaluación',
                ], $restringida ? 'Acceso restringido' : $e['admisibilidad']);
            $conectar('fuente:'.$id, 'mapeo:'.$id, 'referencia conservada');
            $conectar('mapeo:'.$id, 'evidencia:'.$id, 'representada por');
        }
        foreach ($detalle['reglas'] as $r) {
            $rid = 'regla:'.$r['id'];
            // Sin permiso no se serializan los requisitos instrumentales, tampoco en NO_CUMPLE sin soportes.
            $reglaRestringida = ! $permisoInstrumental;
            $agregar($rid, 'Regla', $r['nombre'], 4, [
                'Código' => $r['codigo'] ?? $r['id'], 'Tipo' => $r['tipo'] ?? 'No conservado en esta evaluación',
                'Descripción' => $r['descripcion'] ?? 'No conservado en esta evaluación',
                'Construcción' => 'SI todas las condiciones exigidas están respaldadas, ENTONCES aporta la consecuencia declarada.',
                'Estado registrado' => $r['estado'],
            ], $fallo ? 'Integridad no verificable' : $r['estado']);
            $filas = [];
            foreach ($r['condiciones'] as $c) {
                $cid = 'condicion:'.$r['id'].':'.$c['id'];
                $restringida = $reglaRestringida;
                $exigida = $restringida ? 'Condición instrumental restringida' : ($c['variable_nombre'] ?? $c['variable']).' = '.($c['valor_nombre'] ?? $c['valor']);
                $agregar($cid, 'Condición', $exigida, 3, $restringida ? ['Acceso' => 'Requiere permiso instrumental.'] : [
                    'Variable requerida' => $c['variable_nombre'] ?? $c['variable'], 'Valor exigido' => $c['valor_nombre'] ?? $c['valor'],
                    'Soporte' => $c['soportes'] ? count($c['soportes']).' evidencia(s) conservada(s)' : 'Sin evidencia de soporte',
                ], $fallo ? 'Integridad no verificable' : $c['estado']);
                foreach ($c['soportes'] as $sid) {
                    if (isset($nodos['evidencia:'.$sid])) {
                        $conectar('evidencia:'.$sid, $cid, 'soporte conservado');
                    }
                }
                $conectar($cid, $rid, 'condición conjuntiva');
                $filas[] = ['id' => $cid, 'exigida' => $exigida, 'estado' => $fallo ? 'Integridad no verificable' : $c['estado'],
                    'datos' => $restringida ? [] : array_values(array_map(fn ($id) => $detalle['evidencias'][$id], $c['soportes']))];
            }
            foreach ($r['consecuencias'] as $i => $valor) {
                $coid = 'consecuencia:'.$r['id'].':'.$i;
                $agregar($coid, 'Consecuencia', LenguajeResultadosExperto::RESULTADOS[$valor]['titulo'] ?? $valor, 5,
                    ['Aplicación' => $r['estado'] === 'CUMPLE' ? 'Aportada según traza' : 'Declarada; no aportada'],
                    $fallo ? 'Integridad no verificable' : ($r['estado'] === 'CUMPLE' ? 'Aportada' : 'No aportada'));
                $conectar($rid, $coid, $r['estado'] === 'CUMPLE' ? 'aporta según traza' : 'declara; no ejecutada');
                if (! $fallo && $r['estado'] === 'CUMPLE' && $detalle['resultado']) {
                    $conectar($coid, 'resultado', 'considerada por el resolvedor');
                }
            }
            $grafo['reglas'][] = ['id' => $rid, 'nombre' => $r['nombre'], 'estado' => $r['estado'], 'filas' => $filas,
                'consecuencias' => array_map(fn ($v) => LenguajeResultadosExperto::RESULTADOS[$v]['titulo'] ?? $v, $r['consecuencias'])];
        }
        foreach ($detalle['evidencias'] as $e) {
            foreach ($e['relaciones'] ?? [] as $relacion) {
                if (isset($nodos['evidencia:'.$relacion['origen']], $nodos['evidencia:'.$relacion['destino']])) {
                    $conectar('evidencia:'.$relacion['origen'], 'evidencia:'.$relacion['destino'], $relacion['tipo']);
                }
            }
        }
        $agregar('resultado', 'Resultado', $detalle['estado'], 6,
            ['Interpretación' => $detalle['interpretacion'], 'Publicación' => $detalle['autorizado'] ? 'Según contrato de versión' : 'No habilitada'],
            $fallo ? 'Integridad no verificable' : $detalle['estado']);
        $grafo['nodos'] = array_values($nodos);
        usort($grafo['nodos'], fn ($a, $b) => [$a['columna'], $a['id']] <=> [$b['columna'], $b['id']]);
        $grafo['aristas'] = array_values($aristas);
        $grafo['aviso'] = $fallo ? 'Error de integridad: las rutas se muestran para inspección, sin acreditar una conclusión válida.'
            : 'Traza histórica guardada. Las consecuencias no aportadas no se conectan al resultado. No se reejecuta el motor.';

        return $grafo;
    }
}
