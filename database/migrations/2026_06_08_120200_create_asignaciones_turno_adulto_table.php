<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asignaciones_turno_adulto')) {
            return;
        }

        Schema::create('asignaciones_turno_adulto', function (Blueprint $table) {
            $table->string('cod_asig_turno', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('cod_turno', 20);
            $table->string('cod_usu_enfermero', 20);
            $table->string('cod_habitacion', 20)->nullable();
            $table->string('cod_cama', 20)->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('nivel_supervision', 30)->default('ESTANDAR');
            $table->string('estado', 30)->default('ACTIVA');
            $table->text('motivo_asignacion')->nullable();
            $table->string('asignado_por', 20)->nullable();
            $table->timestamps();

            $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('cod_turno')->references('cod_turno')->on('turnos_enfermeria')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('cod_usu_enfermero')->references('cod_usu')->on('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('cod_habitacion')->references('cod_habitacion')->on('habitaciones')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('cod_cama')->references('cod_cama')->on('camas')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('asignado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();

            $table->index(['cod_am', 'estado']);
            $table->index(['cod_usu_enfermero', 'cod_turno', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_turno_adulto');
    }
};
