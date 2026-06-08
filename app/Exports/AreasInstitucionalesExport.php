<?php

namespace App\Exports;

use App\Models\AreaInstitucional;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AreasInstitucionalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('areas_institucionales')) {
            return collect();
        }

        return AreaInstitucional::with(['responsable', 'usuarios'])->orderBy('nombre')->get();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Áreas Institucionales';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Área',
            'Tipo de área',
            'Responsable',
            'Estado',
            'Total usuarios',
            'Usuarios activos',
            'Usuarios inactivos',
            'Última actualización',
            'Observaciones'
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
            $area->usuarios->count(),
            $area->usuarios->filter(fn($u) => in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
            $area->usuarios->filter(fn($u) => !in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
            $area->updated_at ? \Carbon\Carbon::parse($area->updated_at)->format('d/m/Y H:i') : 'Sin registro',
            $area->observaciones ?: 'Sin observaciones',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Activar autofiltros para todas las columnas de la cabecera
        $sheet->setAutoFilter('A1:I1');

        return [
            // Cabecera: Negrita, texto blanco, fondo azul profundo (#2F3E5C)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2F3E5C'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }
}
