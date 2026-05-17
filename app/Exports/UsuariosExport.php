<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsuariosExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::with(['area', 'roles'])->orderBy('ap_paterno')->orderBy('ap_materno')->orderBy('nombres')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nombre Completo',
            'Correo Electrónico',
            'Área Institucional',
            'Rol Principal',
            'Estado',
            'Fecha de Registro'
        ];
    }

    /**
     * @param mixed $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->area ? $user->area->nombre : 'Sin Área',
            $user->getRoleNames()->first() ?? 'Sin Rol',
            $user->estado == 1 ? 'Activo' : 'Inactivo',
            $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'Sin fecha',
        ];
    }
}
