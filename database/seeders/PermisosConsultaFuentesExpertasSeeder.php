<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modulos\ApoyoDecision\Aplicacion\Consultas\CapturasInstrumentalesResidente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Reparación aditiva de lecturas aprobadas; no sustituye la matriz ni concede firma clínica. */
final class PermisosConsultaFuentesExpertasSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $lecturas = array_merge(CapturasInstrumentalesResidente::PERMISOS_LECTURA,
                ['documentos_clinicos.ver', 'valoraciones_petersen.ver', 'controles_funcionales_iadl.ver', 'reglas_expertas.ver']);
            foreach ($lecturas as $permiso) {
                Permission::findOrCreate($permiso, 'web');
            }
            $medicina = Role::findByName('MEDICO GENERAL/GERIATRA', 'web');
            $superadmin = Role::findByName('SUPERADMINISTRADOR', 'web');
            $cambios = [];
            foreach ([$medicina, $superadmin] as $rol) {
                $permisos = $rol->name === 'SUPERADMINISTRADOR'
                    ? Permission::query()->where('guard_name', 'web')->where('name', 'like', '%.ver')->pluck('name')->all()
                    : $lecturas;
                $faltantes = array_values(array_diff($permisos, $rol->permissions()->pluck('name')->all()));
                if ($faltantes !== []) {
                    $rol->givePermissionTo($faltantes);
                    $cambios[$rol->name] = $faltantes;
                }
            }
            if ($cambios !== []) {
                activity('accesos')->withProperties(['lecturas_agregadas' => $cambios])
                    ->log('Reparación aprobada de consulta experta y lectura de superadministración');
            }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
