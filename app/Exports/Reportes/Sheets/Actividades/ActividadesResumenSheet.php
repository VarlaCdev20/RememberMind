<?php

namespace App\Exports\Reportes\Sheets\Actividades;

use App\Services\Reportes\ReporteDataService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActividadesResumenSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
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

        // KPIs de participación desde actividad_participantes
        $part = ['total' => 0, 'asistencias' => 0, 'faltas' => 0, 'justificados' => 0, 'seguimiento' => 0];
        if (Schema::hasTable('actividad_participantes')) {
            $row = DB::table('actividad_participantes')->whereNull('deleted_at')
                ->selectRaw("
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado_asistencia = 'ASISTIO'      THEN 1 ELSE 0 END) AS asistencias,
                    SUM(CASE WHEN estado_asistencia = 'FALTO'        THEN 1 ELSE 0 END) AS faltas,
                    SUM(CASE WHEN estado_asistencia = 'JUSTIFICADO'  THEN 1 ELSE 0 END) AS justificados,
                    SUM(CASE WHEN requiere_seguimiento = true         THEN 1 ELSE 0 END) AS seguimiento
                ")->first();
            $part = [
                'total'       => (int) ($row->total ?? 0),
                'asistencias' => (int) ($row->asistencias ?? 0),
                'faltas'      => (int) ($row->faltas ?? 0),
                'justificados'=> (int) ($row->justificados ?? 0),
                'seguimiento' => (int) ($row->seguimiento ?? 0),
            ];
        }

        $tasa = $part['total'] > 0 ? round(($part['asistencias'] / $part['total']) * 100, 1) : 0;

        return [
            ['— ACTIVIDADES —', ''],
            ['Total registradas',             $r['total']],
            ['Realizadas / Completadas',      $r['completadas']],
            ['Programadas / Pendientes',      $r['pendientes']],
            ['Canceladas / Anuladas',         $r['canceladas']],
            ['', ''],
            ['— PARTICIPACIÓN —', ''],
            ['Total inscripciones',           $part['total']],
            ['Asistencias registradas',       $part['asistencias']],
            ['Faltas registradas',            $part['faltas']],
            ['Justificados',                  $part['justificados']],
            ['Tasa de asistencia (%)',         $tasa . '%'],
            ['Requieren seguimiento',         $part['seguimiento']],
            ['', ''],
            ['Generado',                     now()->format('d/m/Y H:i')],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1  => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                   'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '5A8A70']]],
            2  => ['font' => ['bold' => true, 'color' => ['rgb' => '5A8A70']]],
            8  => ['font' => ['bold' => true, 'color' => ['rgb' => '5A8A70']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 20];
    }
}
