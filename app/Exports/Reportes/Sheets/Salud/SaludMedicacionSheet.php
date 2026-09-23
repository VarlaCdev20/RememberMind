<?php

namespace App\Exports\Reportes\Sheets\Salud;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SaludMedicacionSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Medicaciones';
    }

    public function headings(): array
    {
        return ['Código AM', 'Nombre', 'Medicamento', 'Dosis', 'Frecuencia', 'Estado', 'Fecha Inicio', 'Fecha Fin'];
    }

    public function collection()
    {
        return $this->service->medicacionLista(500)->map(fn($r) => [
            $r->cod_am,
            $r->nombre,
            $r->nombre_medicamento,
            $r->dosis,
            $r->frecuencia,
            $r->estado,
            $r->fecha_inicio,
            $r->fecha_fin,
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
