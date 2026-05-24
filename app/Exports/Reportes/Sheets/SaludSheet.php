<?php

namespace App\Exports\Reportes\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SaludSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected array $datos) {}

    public function title(): string
    {
        return 'Salud y Seguimiento';
    }

    public function headings(): array
    {
        return ['Métrica', 'Valor', 'Período'];
    }

    public function array(): array
    {
        $s = $this->datos['salud'] ?? [];

        return [
            ['Fichas médicas activas',          $s['fichasActivas'] ?? 0,         'Activas'],
            ['Adultos sin ficha médica',         $s['adultosSinFicha'] ?? 0,       'Activos sin ficha'],
            ['Medicaciones activas',             $s['medicacionesActivas'] ?? 0,   'En seguimiento'],
            ['Atenciones médicas',               $s['atencionesMes'] ?? 0,         'Mes en curso'],
            ['Valoraciones funcionales',         $s['valoracionesRecientes'] ?? 0, 'Últimos 30 días'],
            ['Alta dependencia funcional',       $s['altaDependencia'] ?? 0,       'Valoración más reciente por adulto'],
            ['Signos vitales registrados',       $s['signosVitales7d'] ?? 0,       'Últimos 7 días'],
            ['Evaluaciones cognitivas',          $s['evalCognitivas30d'] ?? 0,     'Últimos 30 días'],
            ['Riesgo de caída alto',             $s['riesgoCaidaAlto'] ?? 0,       'Valoración más reciente por adulto'],
            ['Administraciones de medicación',  $s['adminMedicacionHoy'] ?? 0,    'Hoy'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2A9D8F']]],
        ];
    }
}
