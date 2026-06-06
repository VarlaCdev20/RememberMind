<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Obtener todos los usuarios con sus roles
        $usuarios = User::with('roles')->get();

        foreach ($usuarios as $user) {
            $oldRoles = $user->roles->pluck('name')->toArray();
            
            foreach ($oldRoles as $oldRole) {
                switch ($oldRole) {
                    case 'admin':
                        $user->assignRole('SUPERADMINISTRADOR');
                        $user->removeRole('admin');
                        Log::info("Migrado usuario {$user->cod_usu} de admin a SUPERADMINISTRADOR");
                        break;

                    case 'personal_admin':
                        $user->assignRole('ADMINISTRADOR');
                        $user->removeRole('personal_admin');
                        Log::info("Migrado usuario {$user->cod_usu} de personal_admin a ADMINISTRADOR");
                        break;

                    case 'personal_salud':
                        // Determinar si es médico o enfermero (asumimos enfermero por defecto si no hay registro específico)
                        $esMedico = \DB::table('personal_salud')
                                       ->where('cod_per_sal', $user->cod_usu) // o cod_usu según esquema
                                       ->orWhere('nombres', $user->nombres)
                                       ->value('tipo');

                        if ($esMedico === 'MEDICO' || $esMedico === 'MÉDICO') {
                            $user->assignRole('MEDICO GENERAL/GERIATRA');
                            Log::info("Migrado usuario {$user->cod_usu} de personal_salud a MEDICO GENERAL/GERIATRA");
                        } else {
                            $user->assignRole('ENFERMEROS');
                            Log::info("Migrado usuario {$user->cod_usu} de personal_salud a ENFERMEROS");
                        }
                        $user->removeRole('personal_salud');
                        break;

                    case 'voluntario':
                        $user->assignRole('VOLUNTARIO');
                        $user->removeRole('voluntario');
                        Log::info("Migrado usuario {$user->cod_usu} de voluntario a VOLUNTARIO");
                        break;

                    case 'familiar':
                        $user->assignRole('FAMILIAR');
                        $user->removeRole('familiar');
                        Log::info("Migrado usuario {$user->cod_usu} de familiar a FAMILIAR");
                        break;
                }
            }
        }
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $usuarios = User::with('roles')->get();

        foreach ($usuarios as $user) {
            $roles = $user->roles->pluck('name')->toArray();
            
            foreach ($roles as $role) {
                switch ($role) {
                    case 'SUPERADMINISTRADOR':
                        $user->assignRole('admin');
                        $user->removeRole('SUPERADMINISTRADOR');
                        break;
                    case 'ADMINISTRADOR':
                        $user->assignRole('personal_admin');
                        $user->removeRole('ADMINISTRADOR');
                        break;
                    case 'ENFERMEROS':
                    case 'MEDICO GENERAL/GERIATRA':
                        $user->assignRole('personal_salud');
                        $user->removeRole($role);
                        break;
                    case 'VOLUNTARIO':
                        $user->assignRole('voluntario');
                        $user->removeRole('VOLUNTARIO');
                        break;
                    case 'FAMILIAR':
                        $user->assignRole('familiar');
                        $user->removeRole('FAMILIAR');
                        break;
                }
            }
        }
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
