<?php

namespace App\Exports\Reportes\Sheets\Actividades;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActividadesListaSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Listado Actividades';
    }

    public function headings(): array
    {
        return [
            'Código',
            'Nombre Actividad',
            'Tipo',
            'Categoría',
            'Adulto Mayor (si directo)',
            'Fecha',
            'Hora Inicio',
            'Hora Fin',
            'Lugar',
            'Responsable',
            'Estado',
            'Participantes',
            'Asistieron',
            'Faltaron',
            'Justificados',
            'Con Seguimiento',
            'Resultado General',
            'Nivel Cumplimiento',
            'Evaluación Final',
            'Incidencias',
            'Recomendaciones',
            'Observaciones',
        ];
    }

    public function collection()
    {
        return $this->service->actividadesListaEnriquecida(500)->map(fn($r) => [
            $r->cod_act_adul,
            $r->nombre ?? '—',
            $r->tipo_actividad ?? '—',
            $r->categoria ?? '—',
            $r->adulto ?? '—',
            $r->fecha ? \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') : '—',
            $r->hora ? substr($r->hora, 0, 5) : '—',
            $r->hora_fin ? substr($r->hora_fin, 0, 5) : '—',
            $r->lugar ?? '—',
            $r->responsable_id ?? '—',
            $r->estado ?? '—',
            (int) ($r->total_participantes ?? 0),
            (int) ($r->asistieron ?? 0),
            (int) ($r->faltaron ?? 0),
            (int) ($r->justificados ?? 0),
            (int) ($r->seguimiento ?? 0),
            $r->resultado_general ?? '—',
            $r->nivel_cumplimiento ?? '—',
            $r->evaluacion_final ?? '—',
            $r->incidencias ?? '—',
            $r->recomendaciones ?? '—',
            $r->obs ?? '—',
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '5A8A70']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8, 'B' => 28, 'C' => 22, 'D' => 16, 'E' => 28,
            'F' => 12, 'G' => 10, 'H' => 10, 'I' => 20, 'J' => 16,
            'K' => 14, 'L' => 12, 'M' => 10, 'N' => 10, 'O' => 12,
            'P' => 14, 'Q' => 18, 'R' => 16, 'S' => 30, 'T' => 30,
            'U' => 30, 'V' => 30,
        ];
    }
}
