<?php

namespace App\Exports\Reportes\Sheets\Equipo;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquipoVoluntariosSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Voluntarios';
    }

    public function headings(): array
    {
        return ['Código', 'Nombre', 'Área Apoyo', 'Área Preferente', 'Estado', 'Fecha Ingreso'];
    }

    public function collection()
    {
        return $this->service->voluntariosLista(500)->map(fn($r) => [
            $r->cod_vol,
            $r->nombre,
            $r->area_apoyo,
            $r->area_apoyo_preferente,
            $r->estado,
            $r->fecha_ing,
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
