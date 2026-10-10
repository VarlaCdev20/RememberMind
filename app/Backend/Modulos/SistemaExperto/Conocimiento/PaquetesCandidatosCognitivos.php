<?php

namespace App\Backend\Modulos\SistemaExperto\Conocimiento;

/** Manifiestos documentales D-108/D-135 en memoria. No son PaqueteConocimiento ejecutable. */
final class PaquetesCandidatosCognitivos
{
    public function todos(): array
    {
        $criterios = [
            'COG-ATE' => ['Atención', 10, ['ATE-S01' => 'Atención sostenida', 'ATE-S02' => 'Atención selectiva', 'ATE-S03' => 'Atención dividida']],
            'COG-EJE' => ['Función ejecutiva', 18, ['EJE-S01' => 'Control inhibitorio / inhibición', 'EJE-S02' => 'Flexibilidad cognitiva / set shifting',
                'EJE-S03' => 'Actualización / manipulación en memoria de trabajo', 'EJE-S04' => 'Planificación y resolución de problemas', 'EJE-S05' => 'Razonamiento / abstracción']],
            'COG-LEN' => ['Lenguaje', 24, ['LEN-S01' => 'Denominación / acceso léxico', 'LEN-S02' => 'Fluidez verbal / generación léxica',
                'LEN-S03' => 'Comprensión del lenguaje', 'LEN-S04' => 'Repetición', 'LEN-S05' => 'Producción verbal / discurso']],
            'COG-VIS' => ['Función visuoespacial', 31, ['VIS-S01' => 'Percepción / relaciones visuoespaciales', 'VIS-S02' => 'Visuoconstrucción']],
        ];
        $manifiestos = [];
        foreach ($criterios as $codigo => [$nombre, $fila, $subcomponentes]) {
            $manifiestos[] = ['codigo' => $codigo, 'nombre' => $nombre, 'version_documental' => 'O.R.I.O.N. D-108/D-135 · 2026-10-08',
                'estado_documental' => 'DEFINIDO/CONGELADO — V1', 'estado_tecnico' => 'INACTIVO',
                'motivo' => 'Formalización específica pendiente: variables, mapeos, compuertas, dominios de resultado, reglas y resolvedor.',
                'fuente' => '2_Conceptos · fila '.$fila, 'subcomponentes_candidatos' => $subcomponentes,
                'reglas' => [], 'mapeos' => [], 'compuertas' => [], 'dominios_resultado' => []];
        }

        return $manifiestos;
    }
}
