<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_prescripcion', function (Blueprint $table) {
            $table->string('cod_horario_prescripcion', 20)->primary();
            $table->string('cod_prescripcion', 20);
            $table->time('hora_programada');
            $table->decimal('dosis_programada', 10, 3)->nullable();
            $table->string('dias_semana', 50)->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_prescripcion')->references('cod_prescripcion')->on('prescripciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_prescripcion');
    }
};
