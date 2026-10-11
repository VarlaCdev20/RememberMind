<?php

namespace App\Modulos\Identidad\Dominio\Reglas;

final class ClasificacionRolInstitucional
{
    public const ROLES = ['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA',
        'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'];

    public static function paraRol(string $rol): ?array
    {
        $datos = match ($rol) {
            'ENFERMEROS' => ['salud', 'ENFERMERO', 'Enfermero(a)', 'ARE_0004'],
            'MEDICO GENERAL/GERIATRA' => ['salud', 'MEDICO_GENERAL', 'Médico General/Geriatra', 'ARE_0004'],
            'PSICOLOGO/A' => ['salud', 'PSICOLOGO', 'Psicólogo(a)', 'ARE_0005'],
            'PEDAGOGO' => ['salud', 'PEDAGOGO', 'Pedagogo(a)', 'ARE_0005'],
            'NUTRICIONISTA' => ['salud', 'NUTRICIONISTA', 'Nutricionista', 'ARE_0004'],
            'FISIOTERAPEUTA' => ['salud', 'FISIOTERAPEUTA', 'Fisioterapeuta', 'ARE_0004'],
            'SUPERADMINISTRADOR' => ['admin', 'SUPERADMINISTRADOR', 'Superadministrador(a)', 'ARE_0009'],
            'GERENTE' => ['admin', 'GERENTE', 'Gerente', null],
            'ADMINISTRADOR' => ['admin', 'ADMINISTRADOR', 'Administrador(a)', 'ARE_0003'],
            default => null,
        };
        if ($datos === null) {
            return null;
        }

        return ['rol' => $rol, 'tipo_personal' => $datos[0], 'tipo_label' => $datos[0] === 'salud' ? 'Personal de Salud' : 'Personal Administrativo',
            'rol_operativo' => $datos[1], 'rol_label' => $datos[2], 'cod_area_referencia' => $datos[3]];
    }
}
