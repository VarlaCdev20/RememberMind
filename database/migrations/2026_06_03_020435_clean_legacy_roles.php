<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Eliminar roles antiguos si existen
        Role::whereIn('name', ['personal_salud', 'personal_admin'])->delete();

        // 2. Renombrar a mayúsculas
        $voluntario = Role::where('name', 'voluntario')->first();
        if ($voluntario) {
            $voluntario->update(['name' => 'VOLUNTARIO']);
        } else {
            Role::firstOrCreate(['name' => 'VOLUNTARIO']);
        }

        $familiar = Role::where('name', 'familiar')->first();
        if ($familiar) {
            $familiar->update(['name' => 'FAMILIAR']);
        } else {
            Role::firstOrCreate(['name' => 'FAMILIAR']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
