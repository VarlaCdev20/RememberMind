<?php

namespace App\Exports\Reportes\Sheets\Salud;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SaludFichasSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Fichas Médicas';
    }

    public function headings(): array
    {
        return ['Código AM', 'Nombre', 'Estado', 'Hipertensión', 'Diabetes', 'Prob. Cardiacos', 'Fecha Registro'];
    }

    public function collection()
    {
        return $this->service->fichasLista(500)->map(fn($r) => [
            $r->cod_am,
            $r->nombre,
            $r->estado,
            $r->hipertension ? 'Sí' : 'No',
            $r->diabetes      ? 'Sí' : 'No',
            $r->problemas_cardiacos ? 'Sí' : 'No',
            $r->created_at,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2A9D8F']]],
        ];
    }
}
