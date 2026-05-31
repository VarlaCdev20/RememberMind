<?php

namespace App\Exports;

use App\Models\Voluntario;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class VoluntariadoExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Voluntario::with('usuario')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nombre del Voluntario',
            'Correo Electrónico',
            'Área de Apoyo',
            'Estado',
            'Fecha de Ingreso',
            'Observaciones'
        ];
    }

    /**
     * @param mixed $vol
     * @return array
     */
    public function map($vol): array
    {
        $nombre = $vol->usuario ? $vol->usuario->name : 'No Registrado';
        $email = $vol->usuario ? $vol->usuario->email : 'Sin Correo';
        
        return [
            $nombre,
            $email,
            $vol->area_apoyo ?? 'General',
            $vol->estado ?? 'Activo',
            $vol->fecha_ing ? Carbon::parse($vol->fecha_ing)->format('d/m/Y') : 'Sin fecha',
            $vol->observaciones ?? '',
        ];
    }
}
