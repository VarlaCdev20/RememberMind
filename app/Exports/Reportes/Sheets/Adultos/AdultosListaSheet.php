<?php

namespace App\Exports\Reportes\Sheets\Adultos;

use App\Services\Reportes\ReporteDataService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdultosListaSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(protected ReporteDataService $service) {}

    public function title(): string
    {
        return 'Listado';
    }

    public function headings(): array
    {
        return [
            'Código',
            'Nombre Completo',
            'CI',
            'Género',
            'Edad',
            'Fecha Nac.',
            'Estado Civil',
            'Nivel Educativo',
            'Tipo Ingreso',
            'Fecha Ingreso',
            'Permanencia',
            'Estado',
            'Con Familiar',
            'Con Ficha Médica',
        ];
    }

    public function collection()
    {
        return $this->service->adultosListaCompleta(1000)->map(fn($r) => [
            $r->cod_am,
            $r->nombre_completo,
            $r->ci,
            $r->genero,
            $r->edad !== null ? (int) $r->edad : '',
            $r->fecha_nac,
            $r->estado_civil ?? '',
            $r->nivel_educat ?? '',
            $r->tipo_ing ?? '',
            $r->fecha_ing,
            $r->permanencia ?? '',
            $r->nombre_estado ?? '',
            $r->tiene_familiar ? 'Sí' : 'No',
            $r->tiene_ficha    ? 'Sí' : 'No',
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, 'B' => 32, 'C' => 12, 'D' => 10,
            'E' => 7,  'F' => 14, 'G' => 16, 'H' => 18,
            'I' => 16, 'J' => 14, 'K' => 14, 'L' => 16,
            'M' => 14, 'N' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2F3E5C']],
            ],
        ];
    }
}
