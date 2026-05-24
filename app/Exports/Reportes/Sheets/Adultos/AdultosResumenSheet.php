<?php

namespace App\Exports\Reportes\Sheets\Adultos;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdultosResumenSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Resumen';
    }

    public function headings(): array
    {
        return ['Indicador', 'Valor', 'Observación'];
    }

    public function array(): array
    {
        $resumen = $this->service->adultosResumen();
        $edad    = $this->service->adultosEdad();
        $estado  = $this->service->adultosEstado();
        $genero  = $this->service->adultosGenero();

        $filas = [
            // — Totales —
            ['Total Registrados',          $resumen['total'],        'Incluye activos y archivados'],
            ['Activos',                    $resumen['activos'],      'Sin fecha de archivado'],
            ['Archivados',                 $resumen['archivados'],   'Con fecha de archivado'],
            ['Nuevos Este Mes',            $resumen['nuevos_mes'],   now()->format('m/Y')],
            ['', '', ''],
            // — Edad —
            ['Edad Promedio',              ($edad['promedio'] ?? 0) . ' años', 'Calculado sobre registros con fecha de nac.'],
            ['', '', ''],
            // — Familiar —
            ['Con Familiar Vinculado',     $resumen['con_familiar'],  'Al menos un vínculo activo en familiar_adulto'],
            ['Sin Familiar Vinculado',     $resumen['sin_familiar'],  'Sin ningún vínculo activo'],
            ['', '', ''],
            // — Ficha médica —
            ['Con Ficha Médica',           $resumen['con_ficha'],     'Al menos una ficha médica no eliminada'],
            ['Sin Ficha Médica',           $resumen['sin_ficha'],     'Sin ficha médica registrada'],
            ['', '', ''],
        ];

        // — Distribución por estado —
        $filas[] = ['DISTRIBUCIÓN POR ESTADO', '', ''];
        foreach ($estado['labels'] as $i => $label) {
            $filas[] = [$label, $estado['data'][$i] ?? 0, ''];
        }
        $filas[] = ['', '', ''];

        // — Distribución por género —
        $filas[] = ['DISTRIBUCIÓN POR GÉNERO', '', ''];
        foreach ($genero['labels'] as $i => $label) {
            $filas[] = [$label, $genero['data'][$i] ?? 0, ''];
        }
        $filas[] = ['', '', ''];

        // — Distribución por rango de edad —
        $filas[] = ['DISTRIBUCIÓN POR RANGO DE EDAD', '', ''];
        foreach ($edad['labels'] as $i => $label) {
            $filas[] = [$label . ' años', $edad['data'][$i] ?? 0, ''];
        }

        return $filas;
    }

    public function columnWidths(): array
    {
        return ['A' => 38, 'B' => 14, 'C' => 42];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2F3E5C']],
            ],
        ];
    }
}
