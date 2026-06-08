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

class UsuariosSinTurnoExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::where(function($q) {
                $q->where('estado', 'ACTIVO')
                  ->orWhere('estado', 1)
                  ->orWhere('estado', '1');
            })
            ->whereDoesntHave('asignacionesTurno', function ($query) {
                $query->where('estado', 'ACTIVA');
            })
            ->with(['roles'])
            ->get();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Personal Sin Turno';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nombre Completo',
            'Área Institucional Principal',
            'Correo Electrónico',
            'Teléfono',
            'Rol',
            'Último Acceso'
        ];
    }

    /**
     * @param mixed $user
     * @return array
     */
    public function map($user): array
    {
        $rol = $user->roles->pluck('name')->implode(', ') ?: 'Sin Rol';

        return [
            $user->name,
            $user->areaInstitucional ? $user->areaInstitucional->nombre : 'Sin Área Asignada',
            $user->correo ?: $user->email,
            $user->telefono ?: 'No registrado',
            strtoupper($rol),
            $user->ultimo_acceso ? \Carbon\Carbon::parse($user->ultimo_acceso)->format('d/m/Y H:i') : 'Nunca',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->setAutoFilter('A1:F1');

        return [
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
