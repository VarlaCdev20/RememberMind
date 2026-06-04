<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NormalizeRolesSeeder extends Seeder
{
    public function run()
    {
        // Renombrar 'MEDICO GENERAL/GERIATRA' a 'MEDICO GENERAL' si existe
        /** @var Role|null $medico */
        $medico = Role::where('name', 'MEDICO GENERAL/GERIATRA')->first();
        if ($medico instanceof Role) {
            $medico->name = 'MEDICO GENERAL';
            $medico->save();
        }

        // Asegurar roles válidos
        $validRoles = [
            'SUPERADMINISTRADOR',
            'ADMINISTRADOR',
            'MEDICO GENERAL',
            'ENFERMEROS',
            'PSICOLOGO/A',
            'NUTRICIONISTA',
            'FISIOTERAPEUTA',
            'PEDAGOGO',
            'VOLUNTARIO',
            'FAMILIAR'
        ];

        foreach ($validRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Reasignar usuarios de 'personal_salud'
        $oldSalud = Role::where('name', 'personal_salud')->first();
        if ($oldSalud) {
            $users = User::role('personal_salud')->get();
            foreach ($users as $user) {
                $ps = DB::table('personal_salud')->where('cod_usu', $user->cod_usu)->first();
                if ($ps) {
                    $newRole = match(strtoupper($ps->tipo_personal_salud)) {
                        'MEDICO_GENERAL', 'MEDICO GENERAL' => 'MEDICO GENERAL',
                        'ENFERMERO' => 'ENFERMEROS',
                        'PSICOLOGO' => 'PSICOLOGO/A',
                        'FISIOTERAPEUTA' => 'FISIOTERAPEUTA',
                        'NUTRICIONISTA' => 'NUTRICIONISTA',
                        default => 'MEDICO GENERAL'
                    };
                    $user->assignRole($newRole);
                } else {
                    $user->assignRole('MEDICO GENERAL');
                }
                $user->removeRole('personal_salud');
            }
        }

        // Reasignar 'admin' y 'personal_admin' a 'ADMINISTRADOR'
        $oldAdmins = Role::whereIn('name', ['admin', 'personal_admin'])->get();
        foreach ($oldAdmins as $oldAdmin) {
            $users = User::role($oldAdmin->name)->get();
            foreach ($users as $user) {
                $user->assignRole('ADMINISTRADOR');
                $user->removeRole($oldAdmin->name);
            }
        }

        // Reasignar lowercase 'voluntario' y 'familiar' a UPPERCASE
        $lowerVoluntario = Role::where('name', 'voluntario')->first();
        if ($lowerVoluntario) {
            $users = User::role('voluntario')->get();
            foreach ($users as $user) {
                $user->assignRole('VOLUNTARIO');
                $user->removeRole('voluntario');
            }
        }

        $lowerFamiliar = Role::where('name', 'familiar')->first();
        if ($lowerFamiliar) {
            $users = User::role('familiar')->get();
            foreach ($users as $user) {
                $user->assignRole('FAMILIAR');
                $user->removeRole('familiar');
            }
        }

        // Eliminar roles obsoletos y limpiar permisos huérfanos asociados
        $rolesToDelete = ['personal_salud', 'admin', 'personal_admin', 'voluntario', 'familiar'];
        foreach ($rolesToDelete as $roleName) {
            /** @var Role|null $r */
            $r = Role::where('name', $roleName)->first();
            if ($r instanceof Role) {
                $r->syncPermissions([]); // Limpiar pivot
                $r->delete();
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
