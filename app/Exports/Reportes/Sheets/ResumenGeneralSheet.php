<?php

namespace App\Exports\Reportes\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResumenGeneralSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected array $datos) {}

    public function title(): string
    {
        return 'Resumen General';
    }

    public function headings(): array
    {
        return ['Indicador', 'Valor', 'Descripción'];
    }

    public function array(): array
    {
        $kpis = $this->datos['kpis'] ?? [];
        $filas = [];

        foreach ($kpis as $kpi) {
            $filas[] = [
                $kpi['titulo'] ?? '',
                $kpi['valor'] ?? 0,
                $kpi['subtitulo'] ?? '',
            ];
        }

        $salud = $this->datos['salud'] ?? [];
        $filas[] = ['', '', ''];
        $filas[] = ['Fichas médicas activas',     $salud['fichasActivas'] ?? 0,         'Con ficha activa'];
        $filas[] = ['Sin ficha médica',           $salud['adultosSinFicha'] ?? 0,       'Adultos activos sin ficha'];
        $filas[] = ['Medicaciones activas',       $salud['medicacionesActivas'] ?? 0,   'En seguimiento'];
        $filas[] = ['Atenciones del mes',         $salud['atencionesMes'] ?? 0,         'Mes en curso'];
        $filas[] = ['Valoraciones recientes',     $salud['valoracionesRecientes'] ?? 0, 'Últimos 30 días'];
        $filas[] = ['Alta dependencia funcional', $salud['altaDependencia'] ?? 0,       'Valoración más reciente por adulto'];

        $fam = $this->datos['redFamiliar'] ?? [];
        $filas[] = ['', '', ''];
        $filas[] = ['Total familiares',          $fam['totalFamiliares'] ?? 0,    'Familiares registrados'];
        $filas[] = ['Adultos con familiar',      $fam['adultosConFamiliar'] ?? 0, 'Con al menos un vínculo'];
        $filas[] = ['Adultos sin familiar',      $fam['adultosSinFamiliar'] ?? 0, 'Sin vínculo familiar activo'];
        $filas[] = ['Responsables familiares',   $fam['responsables'] ?? 0,       'Marcados como responsables'];

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
