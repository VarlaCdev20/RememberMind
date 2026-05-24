<?php

namespace App\Exports\Reportes\Sheets\Equipo;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquipoResumenSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Resumen Equipo';
    }

    public function headings(): array
    {
        return ['Categoría', 'Total'];
    }

    public function array(): array
    {
        $r = $this->service->equipoResumen();
        return [
            ['Personal de Salud',        $r['personal_salud']],
            ['Personal Administrativo',  $r['personal_admin']],
            ['Voluntarios Activos',      $r['voluntarios']],
            ['Total del Equipo',         $r['total']],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '7A68B0']]],
        ];
    }
}
