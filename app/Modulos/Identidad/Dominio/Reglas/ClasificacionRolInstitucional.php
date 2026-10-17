<?php

namespace App\Modulos\Identidad\Dominio\Reglas;

final class ClasificacionRolInstitucional
{
    public const ROLES = ['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA',
        'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'];

    public static function paraRol(string $rol): ?array
    {
        $datos = match ($rol) {
            'ENFERMEROS' => ['salud', 'ENFERMERO', 'Enfermero(a)', null],
            'MEDICO GENERAL/GERIATRA' => ['salud', 'MEDICO_GENERAL', 'Médico General/Geriatra', null],
            'PSICOLOGO/A' => ['salud', 'PSICOLOGO', 'Psicólogo(a)', null],
            'PEDAGOGO' => ['salud', 'PEDAGOGO', 'Pedagogo(a)', null],
            'NUTRICIONISTA' => ['salud', 'NUTRICIONISTA', 'Nutricionista', null],
            'FISIOTERAPEUTA' => ['salud', 'FISIOTERAPEUTA', 'Fisioterapeuta', null],
            'SUPERADMINISTRADOR' => ['admin', 'SUPERADMINISTRADOR', 'Superadministrador(a)', null],
            'GERENTE' => ['admin', 'GERENTE', 'Gerente', null],
            'ADMINISTRADOR' => ['admin', 'ADMINISTRADOR', 'Administrador(a)', null],
            default => null,
        };
        if ($datos === null) {
            return null;
        }

        return ['rol' => $rol, 'tipo_personal' => $datos[0], 'tipo_label' => $datos[0] === 'salud' ? 'Personal de Salud' : 'Personal Administrativo',
            'rol_operativo' => $datos[1], 'rol_label' => $datos[2], 'cod_area_referencia' => $datos[3]];
    }
}
