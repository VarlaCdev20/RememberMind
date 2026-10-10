<?php

namespace App\Modulos\Reportes\Infraestructura\Exportaciones;

use App\Modulos\Reportes\Aplicacion\Consultas\AreasReportDataService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AreasInstitucionalesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * @return Collection
     */
    public function collection()
    {
        return app(AreasReportDataService::class)->getGeneralReportData()['areas'];
    }

    public function title(): string
    {
        return 'Áreas Institucionales';
    }

    public function headings(): array
    {
        return [
            'Área',
            'Responsable',
            'Estado',
            'Total usuarios',
            'Usuarios activos',
            'Usuarios inactivos',
            'Descripción',
        ];
    }

    /**
     * @param  mixed  $area
     */
    public function map($area): array
    {
        return [
            $area->nombre,
            $area->responsable ? $area->responsable->name : 'Sin Responsable',
            $area->estado,
            $area->usuarios->count(),
            $area->usuarios->filter(fn ($u) => in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
            $area->usuarios->filter(fn ($u) => ! in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
            $area->descripcion ?: 'Sin descripción',
        ];
    }

    /**
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
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2F3E5C'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
