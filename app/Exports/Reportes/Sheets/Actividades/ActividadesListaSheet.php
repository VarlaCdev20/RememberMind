<?php

namespace App\Exports\Reportes\Sheets\Actividades;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActividadesListaSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Listado';
    }

    public function headings(): array
    {
        return ['Código', 'Adulto Mayor', 'Tipo Actividad', 'Fecha', 'Hora', 'Estado', 'Observación'];
    }

    public function collection()
    {
        return $this->service->actividadesLista(500)->map(fn($r) => [
            $r->cod_act_adul,
            $r->adulto,
            $r->nombre_tipo_act ?? '—',
            $r->fecha,
            $r->hora,
            $r->estado,
            $r->obs,
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
