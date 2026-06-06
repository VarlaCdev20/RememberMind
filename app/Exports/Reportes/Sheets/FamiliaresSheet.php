<?php

namespace App\Exports\Reportes\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FamiliaresSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected array $datos) {}

    public function title(): string
    {
        return 'Red Familiar';
    }

    public function headings(): array
    {
        return ['Indicador', 'Valor'];
    }

    public function array(): array
    {
        $f = $this->datos['redFamiliar'] ?? [];
        $filas = [
            ['Total familiares registrados', $f['totalFamiliares'] ?? 0],
            ['Adultos con familiar vinculado', $f['adultosConFamiliar'] ?? 0],
            ['Adultos activos sin familiar', $f['adultosSinFamiliar'] ?? 0],
            ['Responsables familiares', $f['responsables'] ?? 0],
            ['', ''],
        ];

        $parentescos = $f['parentescos'] ?? [];
        if (!empty($parentescos)) {
            $filas[] = ['PARENTESCOS PRINCIPALES', ''];
            foreach ($parentescos as $p) {
                $filas[] = [$p['parentesco'] ?? 'Sin parentesco', $p['total'] ?? 0];
            }
        }

        return $filas;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D4843A']]],
        ];
    }
}
