<?php

namespace App\Exports\Reportes\Sheets\Salud;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SaludResumenSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Resumen Salud';
    }

    public function headings(): array
    {
        return ['Indicador', 'Valor'];
    }

    public function array(): array
    {
        $r = $this->service->saludResumen();
        return [
            ['Fichas Médicas',          $r['fichas']],
            ['Medicaciones Activas',    $r['medicaciones']],
            ['Valoraciones Vigentes',   $r['valoraciones']],
            ['Total Atenciones',        $r['atenciones']],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2A9D8F']]],
        ];
    }
}
