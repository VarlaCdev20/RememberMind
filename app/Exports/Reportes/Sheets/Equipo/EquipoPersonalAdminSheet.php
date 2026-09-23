<?php

namespace App\Exports\Reportes\Sheets\Equipo;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquipoPersonalAdminSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Personal Admin';
    }

    public function headings(): array
    {
        return ['Código', 'Nombre', 'Cargo', 'Área', 'Estado Laboral', 'Fecha Ingreso'];
    }

    public function collection()
    {
        return $this->service->personalAdminLista(500)->map(fn($r) => [
            // Campos físicos/derivados de personal y asignaciones_personal V2.
            $r->cod_personal,
            $r->nombre_completo,
            $r->rol_nombre,
            $r->area_nombre ?? '—',
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
