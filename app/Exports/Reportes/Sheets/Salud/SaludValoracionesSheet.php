<?php

namespace App\Exports\Reportes\Sheets\Salud;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SaludValoracionesSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Valoraciones';
    }

    public function headings(): array
    {
        return ['Código AM', 'Nombre', 'Nivel Dependencia', 'Riesgo Caída', 'Índice Barthel', 'Fecha'];
    }

    public function collection()
    {
        return $this->service->valoracionesLista(500)->map(fn($r) => [
            $r->cod_am,
            $r->nombre,
            $r->nivel_dependencia,
            $r->riesgo_caida,
            $r->indice_barthel,
            $r->fecha_valoracion,
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
