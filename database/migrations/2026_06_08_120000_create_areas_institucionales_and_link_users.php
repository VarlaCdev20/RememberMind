<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('areas_institucionales')) {
            Schema::create('areas_institucionales', function (Blueprint $table) {
                $table->string('cod_area', 20)->primary();
                $table->string('nombre', 100)->unique();
                $table->string('slug', 120)->unique();
                $table->string('tipo_area', 50)->default('Administrativa');
                $table->text('descripcion')->nullable();
                $table->string('responsable_id', 20)->nullable();
                $table->json('roles_sugeridos')->nullable();
                $table->json('modulos_relacionados')->nullable();
                $table->string('color', 20)->nullable();
                $table->string('icono', 60)->default('ph-buildings');
                $table->string('estado', 30)->default('ACTIVA');
                $table->integer('orden')->default(0);
                $table->text('observaciones')->nullable();
                $table->string('imagen_area', 255)->nullable();
                $table->string('creado_por', 20)->nullable();
                $table->string('actualizado_por', 20)->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        $areas = [
            ['cod_area' => 'ARE_0001', 'nombre' => 'DIRECCION GENERAL', 'slug' => 'direccion-general', 'tipo_area' => 'Administrativa', 'descripcion' => 'Direccion general y toma de decisiones estrategicas.', 'color' => '#2F3E5C', 'orden' => 1],
            ['cod_area' => 'ARE_0002', 'nombre' => 'COORDINACION DE PROGRAMAS Y SERVICIOS', 'slug' => 'coordinacion-programas-servicios', 'tipo_area' => 'Administrativa', 'descripcion' => 'Coordinacion de programas asistenciales y servicios institucionales.', 'color' => '#5E6599', 'orden' => 2],
            ['cod_area' => 'ARE_0003', 'nombre' => 'AREA ADMINISTRATIVA Y REGISTRO INSTITUCIONAL', 'slug' => 'area-administrativa-registro-institucional', 'tipo_area' => 'Administrativa', 'descripcion' => 'Gestion administrativa, contable y registro de usuarios.', 'color' => '#967B66', 'orden' => 3],
            ['cod_area' => 'ARE_0004', 'nombre' => 'AREA DE ATENCION MEDICA', 'slug' => 'area-atencion-medica', 'tipo_area' => 'Salud', 'descripcion' => 'Atencion medica general, geriatria, enfermeria y fisioterapia.', 'color' => '#63775B', 'orden' => 4],
            ['cod_area' => 'ARE_0005', 'nombre' => 'AREA DE PSICOLOGIA Y SEGUIMIENTO COGNITIVO', 'slug' => 'area-psicologia-seguimiento-cognitivo', 'tipo_area' => 'Salud', 'descripcion' => 'Apoyo psicologico, evaluaciones cognitivas y actividades pedagogicas.', 'color' => '#9B8B7E', 'orden' => 5],
            ['cod_area' => 'ARE_0008', 'nombre' => 'VOLUNTARIADO Y RELACIONES INSTITUCIONALES', 'slug' => 'voluntariado-relaciones-institucionales', 'tipo_area' => 'Social', 'descripcion' => 'Gestion de voluntariado, apoyo social y relaciones institucionales.', 'color' => '#63775B', 'orden' => 8],
        ];

        foreach ($areas as $area) {
            DB::table('areas_institucionales')->updateOrInsert(
                ['cod_area' => $area['cod_area']],
                array_merge($area, [
                    'estado' => 'ACTIVA',
                    'icono' => 'ph-buildings',
                    'roles_sugeridos' => json_encode([]),
                    'modulos_relacionados' => json_encode([]),
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }

        if (! Schema::hasColumn('users', 'cod_area')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('cod_area', 20)->nullable()->after('observaciones');
                $table->foreign('cod_area')
                    ->references('cod_area')
                    ->on('areas_institucionales')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'cod_area')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['cod_area']);
                $table->dropColumn('cod_area');
            });
        }

        Schema::dropIfExists('areas_institucionales');
    }
};
