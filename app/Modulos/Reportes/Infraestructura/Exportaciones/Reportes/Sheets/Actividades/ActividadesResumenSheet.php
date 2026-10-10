<?php

namespace App\Modulos\Reportes\Infraestructura\Exportaciones\Reportes\Sheets\Actividades;

use App\Modulos\Reportes\Aplicacion\Consultas\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActividadesResumenSheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Resumen';
    }

    public function headings(): array
    {
        return ['Indicador', 'Valor'];
    }

    public function array(): array
    {
        $r = $this->service->actividadesResumen();

        return [
            ['Total Registradas', $r['total']],
            ['Completadas',       $r['completadas']],
            ['Pendientes',        $r['pendientes']],
            ['Canceladas',        $r['canceladas']],
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
