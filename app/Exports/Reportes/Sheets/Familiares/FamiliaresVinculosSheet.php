<?php

namespace App\Exports\Reportes\Sheets\Familiares;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FamiliaresVinculosSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Vínculos';
    }

    public function headings(): array
    {
        return ['Código AM', 'Adulto Mayor', 'Familiar', 'Parentesco', 'Responsable', 'Estado'];
    }

    public function collection()
    {
        return $this->service->vinculosLista(500)->map(fn($r) => [
            $r->cod_am,
            $r->adulto,
            $r->familiar,
            $r->parentesco_vinculo,
            $r->es_responsable ? 'Sí' : 'No',
            $r->estado,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D4843A']]],
        ];
    }
}
