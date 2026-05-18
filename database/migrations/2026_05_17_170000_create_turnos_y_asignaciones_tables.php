<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. turnos_institucionales
        Schema::create('turnos_institucionales', function (Blueprint $table) {
            $table->string('cod_turno', 20)->primary(); // Format: TUR_0001
            $table->string('nombre', 100)->unique();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('color', 20)->nullable();
            $table->string('estado', 20)->default('ACTIVO'); // ACTIVO, INACTIVO
            $table->text('observaciones')->nullable();
            
            $table->string('creado_por', 20)->nullable();
            $table->string('actualizado_por', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('creado_por')->references('cod_usu')->on('users')->onDelete('set null');
            $table->foreign('actualizado_por')->references('cod_usu')->on('users')->onDelete('set null');
        });

        // 2. asignaciones_turno
        Schema::create('asignaciones_turno', function (Blueprint $table) {
            $table->string('cod_asignacion', 20)->primary(); // Format: AST_0001
            $table->string('cod_usu', 20); // FK users
            $table->string('cod_area', 20); // FK areas_institucionales
            $table->string('cod_turno', 20); // FK turnos_institucionales
            $table->json('dias_semana'); // ["LUNES", "MIERCOLES"]
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('estado', 20)->default('ACTIVA'); // ACTIVA, INACTIVA, FINALIZADA
            $table->string('tipo_asignacion', 50)->nullable(); // REGULAR, TEMPORAL, APOYO, VOLUNTARIADO, COBERTURA
            $table->text('observaciones')->nullable();

            $table->string('creado_por', 20)->nullable();
            $table->string('actualizado_por', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cod_usu')->references('cod_usu')->on('users')->onDelete('cascade');
            $table->foreign('cod_area')->references('cod_area')->on('areas_institucionales')->onDelete('cascade');
            $table->foreign('cod_turno')->references('cod_turno')->on('turnos_institucionales')->onDelete('cascade');
            
            $table->foreign('creado_por')->references('cod_usu')->on('users')->onDelete('set null');
            $table->foreign('actualizado_por')->references('cod_usu')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_turno');
        Schema::dropIfExists('turnos_institucionales');
    }
};
