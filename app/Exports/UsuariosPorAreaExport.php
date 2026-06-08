<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsuariosPorAreaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $codArea;

    public function __construct($codArea)
    {
        $this->codArea = $codArea;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::with(['roles'])
            ->where('cod_area', $this->codArea)
            ->orderBy('nombres')
            ->get();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Usuarios del área';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Usuario',
            'Rol',
            'Cargo o especialidad',
            'Estado',
            'Correo',
            'Teléfono',
            'Último acceso'
        ];
    }

    /**
     * @param mixed $user
     * @return array
     */
    public function map($user): array
    {
        $rolName = $user->getRoleNames()->first() ?? 'Sin Rol';
        $rolLimpio = strtoupper(str_replace('_', ' ', $rolName));
        $cargoEspecialidad = $rolLimpio !== 'SIN ROL' ? $rolLimpio : 'Sin Asignar';

        return [
            $user->name,
            $rolLimpio,
            $cargoEspecialidad,
            in_array($user->estado, ['ACTIVO', 1, '1']) ? 'Activo' : 'Inactivo',
            $user->correo ?: 'Sin registrar',
            ($user->codigo_telefono ? $user->codigo_telefono . ' ' : '') . ($user->telefono ?: 'Sin registrar'),
            $user->ultimo_acceso ? \Carbon\Carbon::parse($user->ultimo_acceso)->format('d/m/Y H:i') : 'Nunca',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Activar autofiltros para todas las columnas de la cabecera
        $sheet->setAutoFilter('A1:G1');

        return [
            // Cabecera: Negrita, texto blanco, fondo azul profundo (#2F3E5C)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2F3E5C'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }
}
