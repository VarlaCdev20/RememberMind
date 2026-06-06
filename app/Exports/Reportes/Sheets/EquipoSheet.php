<?php

namespace App\Exports\Reportes\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquipoSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    public function __construct(protected array $datos) {}

    public function title(): string
    {
        return 'Equipo Institucional';
    }

    public function headings(): array
    {
        return ['Área', 'Total', 'Activos', 'Detalle'];
    }

    public function array(): array
    {
        $e   = $this->datos['equipo'] ?? [];
        $ps  = $e['personal_salud'] ?? [];
        $pa  = $e['personal_admin'] ?? [];
        $vol = $e['voluntarios'] ?? [];

        $filas = [
            ['Personal de Salud',        $ps['total'] ?? 0,  $ps['activos'] ?? 0,  'Especialidades: ' . ($e['especialidades_total'] ?? 0)],
            ['Personal Administrativo',  $pa['total'] ?? 0,  $pa['activos'] ?? 0,  ''],
            ['Voluntarios',              $vol['total'] ?? 0, $vol['activos'] ?? 0,  'Asignados: ' . ($vol['asignados'] ?? 0)],
            ['', '', '', ''],
        ];

        $especialidades = $ps['especialidades'] ?? [];
        if (!empty($especialidades)) {
            $filas[] = ['ESPECIALIDADES DE SALUD', '', '', ''];
            foreach ($especialidades as $esp) {
                $filas[] = [$esp['nombre'] ?? 'Sin nombre', $esp['total'] ?? 0, '', ''];
            }
        }

        $cargos = $pa['cargos'] ?? [];
        if (!empty($cargos)) {
            $filas[] = ['', '', '', ''];
            $filas[] = ['CARGOS ADMINISTRATIVOS', '', '', ''];
            foreach ($cargos as $c) {
                $filas[] = [$c['nombre'] ?? 'Sin cargo', $c['total'] ?? 0, '', ''];
            }
        }

        $areas = $vol['areas'] ?? [];
        if (!empty($areas)) {
            $filas[] = ['', '', '', ''];
            $filas[] = ['ÁREAS DE VOLUNTARIOS', '', '', ''];
            foreach ($areas as $a) {
                $filas[] = [$a['area'] ?? 'Sin área', $a['total'] ?? 0, '', ''];
            }
        }

        return $filas;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '9B8AC7']]],
        ];
    }
}
