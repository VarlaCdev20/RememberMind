<?php

namespace App\Exports;

use Spatie\Activitylog\Models\Activity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class BitacoraExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Activity::with('causer')->orderBy('created_at', 'desc')->take(2000)->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Módulo / Sección',
            'Descripción del Suceso',
            'Ejecutado Por',
            'Tipo de Registro',
            'Fecha y Hora'
        ];
    }

    /**
     * @param mixed $activity
     * @return array
     */
    public function map($activity): array
    {
        $usuario = $activity->causer ? $activity->causer->name : 'Sistema';
        
        return [
            $activity->log_name ? ucfirst($activity->log_name) : 'General',
            $activity->description,
            $usuario,
            $activity->event ? strtoupper($activity->event) : 'MODIFICACIÓN',
            $activity->created_at ? Carbon::parse($activity->created_at)->format('d/m/Y H:i:s') : 'Sin fecha',
        ];
    }
}
