<?php

namespace App\Exports;

use App\Models\AsignacionTurno;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TurnosAsignacionesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return AsignacionTurno::with(['usuario', 'area', 'turno'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Asignaciones de Turno';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Personal / Usuario',
            'Área Operativa',
            'Turno Institucional',
            'Horario',
            'Días Semanales',
            'Fecha Inicio',
            'Fecha Fin',
            'Estado Asignación',
            'Tipo Asignación',
            'Observaciones'
        ];
    }

    /**
     * @param mixed $asig
     * @return array
     */
    public function map($asig): array
    {
        $dias = is_array($asig->dias_semana) ? implode(', ', $asig->dias_semana) : '';
        
        $horario = 'Flexible';
        if ($asig->turno && $asig->turno->hora_inicio && $asig->turno->hora_fin) {
            $horario = substr($asig->turno->hora_inicio, 0, 5) . ' - ' . substr($asig->turno->hora_fin, 0, 5);
        }

        return [
            $asig->usuario ? $asig->usuario->name : 'N/D',
            $asig->area ? $asig->area->nombre : 'N/D',
            $asig->turno ? $asig->turno->nombre : 'N/D',
            $horario,
            $dias,
            $asig->fecha_inicio ? \Carbon\Carbon::parse($asig->fecha_inicio)->format('d/m/Y') : 'N/D',
            $asig->fecha_fin ? \Carbon\Carbon::parse($asig->fecha_fin)->format('d/m/Y') : 'PRESENTE',
            $asig->estado,
            $asig->tipo_asignacion ?: 'REGULAR',
            $asig->observaciones ?: 'Sin observaciones',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->setAutoFilter('A1:J1');

        return [
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
