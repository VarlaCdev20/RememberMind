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
            $r->cod_per_adm,
            $r->nombre,
            $r->cargo,
            $r->area_admin,
            $r->estado_laboral,
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
