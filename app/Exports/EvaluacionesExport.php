<?php

namespace App\Exports;

use App\Models\EvaluacionCognitiva;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class EvaluacionesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return EvaluacionCognitiva::with(['adultoMayor', 'tipoEvaluacion', 'user'])
            ->orderBy('fecha_eval', 'desc')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Residente',
            'Evaluación / Test',
            'Puntaje Obtenido',
            'Puntaje Máximo',
            'Interpretación / Resultado',
            'Nivel de Riesgo',
            'Evaluador Clínico',
            'Fecha de Evaluación',
            'Estado'
        ];
    }

    /**
     * @param mixed $ev
     * @return array
     */
    public function map($ev): array
    {
        $residente = $ev->adultoMayor ? $ev->adultoMayor->nombres . ' ' . $ev->adultoMayor->ap_paterno : 'No Registrado';
        $test = $ev->tipoEvaluacion ? $ev->tipoEvaluacion->nombre : 'Sin Definir';
        $evaluador = $ev->user?->name ?? 'No Asignado';
        
        return [
            $residente,
            $test,
            (float) $ev->puntaje_total,
            (float) $ev->puntaje_maximo,
            $ev->resultado_interpretacion ?? 'Sin Interpretación',
            $ev->nivel_riesgo ?? 'Sin Riesgo',
            $evaluador,
            $ev->fecha_eval ? Carbon::parse($ev->fecha_eval)->format('d/m/Y') : 'Sin fecha',
            $ev->estado_eval ?? 'ACTIVO',
        ];
    }
}
