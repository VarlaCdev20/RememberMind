<?php

namespace App\Exports\Reportes\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BitacoraSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected array $datos) {}

    public function title(): string
    {
        return 'Bitácora';
    }

    public function headings(): array
    {
        return ['Fecha', 'Usuario', 'Acción', 'Módulo'];
    }

    public function array(): array
    {
        $filas = [];

        foreach ($this->datos['bitacora'] ?? [] as $log) {
            $filas[] = [
                $log['fecha']   ?? '-',
                $log['usuario'] ?? 'Sistema',
                $log['accion']  ?? '-',
                $log['modulo']  ?? 'General',
            ];
        }

        return $filas;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2F3E5C']]],
        ];
    }
}
