<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programaciones_cuidado', function (Blueprint $table) {
            $table->string('cod_programacion', 20)->primary();
            $table->string('cod_intervencion', 20);
            $table->string('cod_turno', 20)->nullable();
            $table->string('frecuencia', 60);
            $table->string('dias_semana', 50)->nullable();
            $table->time('hora_programada')->nullable();
            $table->date('fecha_activacion');
            $table->date('fecha_desactivacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_intervencion')->references('cod_intervencion')->on('intervenciones_cuidado')->restrictOnDelete();
            $table->foreign('cod_turno')->references('cod_turno')->on('turnos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programaciones_cuidado');
    }
};
