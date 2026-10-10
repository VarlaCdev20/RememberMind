<?php

namespace App\Exports;

use App\Backend\Modulos\Reportes\Servicios\AreasReportDataService;
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
        return app(AreasReportDataService::class)->getGeneralReportData()['areas'];
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
            'Responsable',
            'Estado',
            'Total usuarios',
            'Usuarios activos',
            'Usuarios inactivos',
            'Descripción'
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
            $area->responsable ? $area->responsable->name : 'Sin Responsable',
            $area->estado,
            $area->usuarios->count(),
            $area->usuarios->filter(fn($u) => in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
            $area->usuarios->filter(fn($u) => !in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
            $area->descripcion ?: 'Sin descripción',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Activar autofiltros para todas las columnas de la cabecera
        $sheet->setAutoFilter('A1:G1');

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
