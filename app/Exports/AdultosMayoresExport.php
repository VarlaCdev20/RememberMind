<?php

namespace App\Exports;

use App\Models\AdultoMayor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class AdultosMayoresExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return AdultoMayor::with('estado')->orderBy('ap_paterno')->orderBy('ap_materno')->orderBy('nombres')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nombre Completo',
            'RUT / Identificación',
            'Género',
            'Edad',
            'Fecha de Nacimiento',
            'Estado Residencial',
            'Fecha de Ingreso'
        ];
    }

    /**
     * @param mixed $am
     * @return array
     */
    public function map($am): array
    {
        $edad = $am->fecha_nac ? Carbon::parse($am->fecha_nac)->age : 'Desconocida';
        $nacimiento = $am->fecha_nac ? Carbon::parse($am->fecha_nac)->format('d/m/Y') : 'Sin fecha';
        
        return [
            $am->nombres . ' ' . $am->ap_paterno . ' ' . $am->ap_materno,
            $am->rut ?? 'No Registrado',
            $am->genero ?? 'No Definido',
            $edad,
            $nacimiento,
            $am->estado ? $am->estado->estado : 'Sin Estado',
            $am->created_at ? $am->created_at->format('d/m/Y') : 'Sin fecha',
        ];
    }
}
