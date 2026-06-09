<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('areas_institucionales')->updateOrInsert(
            ['cod_area' => 'ARE_0009'],
            [
                'nombre'             => 'ADMINISTRACIÓN DEL SISTEMA',
                'slug'               => 'administracion-sistema',
                'tipo_area'          => 'Administrativa',
                'descripcion'        => 'Gestión global del sistema, configuración y superadministración institucional.',
                'color'              => '#1E293B',
                'icono'              => 'ph-gear',
                'estado'             => 'ACTIVA',
                'orden'              => 9,
                'roles_sugeridos'    => json_encode(['SUPERADMINISTRADOR']),
                'modulos_relacionados' => json_encode([]),
                'updated_at'         => now(),
                'created_at'         => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('areas_institucionales')->where('cod_area', 'ARE_0009')->delete();
    }
};
