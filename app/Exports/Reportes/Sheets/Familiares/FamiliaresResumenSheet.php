<?php

namespace App\Exports\Reportes\Sheets\Familiares;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FamiliaresResumenSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Resumen Familiares';
    }

    public function headings(): array
    {
        return ['Indicador', 'Valor'];
    }

    public function array(): array
    {
        $r = $this->service->familiaresResumen();
        return [
            ['Familiares Registrados',       $r['total_familiares']],
            ['Vínculos Activos',             $r['vinculos_activos']],
            ['Adultos sin Familiar',         $r['adultos_sin_familiar']],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D4843A']]],
        ];
    }
}
