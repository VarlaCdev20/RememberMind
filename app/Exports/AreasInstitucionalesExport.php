<?php

namespace App\Exports;

use App\Models\AreaInstitucional;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AreasInstitucionalesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return AreaInstitucional::with(['responsable', 'usuarios'])->orderBy('nombre')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Área',
            'Tipo de Área',
            'Responsable del Área',
            'Estado',
            'Personal Activo',
            'Personal Inactivo',
            'Última Actualización'
        ];
    }

    /**
     * @param mixed $area
     * @return array
     */
    public function map($area): array
    {
        return [
            $area->nombre,
            $area->tipo_area,
            $area->responsable ? $area->responsable->name : 'Sin Responsable',
            $area->estado,
            $area->usuarios->where('estado', 1)->count(),
            $area->usuarios->where('estado', '!=', 1)->count(),
            $area->updated_at ? $area->updated_at->format('d/m/Y H:i') : 'Sin fecha',
        ];
    }
}
