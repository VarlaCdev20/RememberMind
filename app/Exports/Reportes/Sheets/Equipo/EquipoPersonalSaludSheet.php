<?php

namespace App\Exports\Reportes\Sheets\Equipo;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquipoPersonalSaludSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Personal Salud';
    }

    public function headings(): array
    {
        return ['Código', 'Nombre', 'Especialidad', 'Matrícula', 'Estado Laboral', 'Fecha Ingreso'];
    }

    public function collection()
    {
        return $this->service->personalSaludLista(500)->map(fn($r) => [
            // No se consultan los antiguos cod_per_sal/estado_laboral/fecha_ing.
            $r->cod_personal,
            $r->nombre_completo,
            $r->especialidad ?? '—',
            $r->matricula_profesional ?? '—',
            $r->estado,
            $r->fecha_ingreso,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '7A68B0']]],
        ];
    }
}
