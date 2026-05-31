<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Corregir existentes
        \DB::table('cargos_administrativos')->where('nombre', 'SECRETARIA')->update([
            'nombre' => 'Secretaría administrativa',
            'descripcion' => 'Gestión de agenda, correspondencia y soporte administrativo general.'
        ]);
        
        \DB::table('cargos_administrativos')->where('nombre', 'RECEPCIONISTA')->update([
            'nombre' => 'Registro institucional',
            'descripcion' => 'Encargado del registro y gestión de expedientes.'
        ]);
        
        \DB::table('cargos_administrativos')->where('nombre', 'COORDINADORA')->update([
            'nombre' => 'Coordinación administrativa',
            'descripcion' => 'Coordinación de áreas y personal.'
        ]);
        
        \DB::table('cargos_administrativos')->where('nombre', 'AUXILIAR ADMINISTRATIVO')->update([
            'nombre' => 'Apoyo administrativo',
            'descripcion' => 'Auxiliar en procesos y soporte administrativo.'
        ]);

        // 2. Insertar nuevos si no existen
        $nuevos = [
            ['nombre' => 'Dirección administrativa', 'descripcion' => 'Responsable de la dirección y administración general.'],
            ['nombre' => 'Responsable administrativo', 'descripcion' => 'Gestión y control de áreas de soporte administrativo.'],
            ['nombre' => 'Coordinación de programas', 'descripcion' => 'Programas de atención social y servicios residenciales.'],
            ['nombre' => 'Gestión documental', 'descripcion' => 'Administración y archivo de expedientes digitales y físicos.']
        ];

        foreach ($nuevos as $n) {
            \DB::table('cargos_administrativos')->updateOrInsert(
                ['nombre' => $n['nombre']],
                ['descripcion' => $n['descripcion'], 'created_at' => now(), 'updated_at' => now(), 'estado' => 'ACTIVO']
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo
    }
};
