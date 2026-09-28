<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administraciones_medicacion', function (Blueprint $table) {
            $table->string('cod_administracion', 20)->primary();
            $table->string('cod_prescripcion', 20);
            $table->string('cod_horario_prescripcion', 20)->nullable();
            $table->string('cod_residente', 20);
            $table->string('cod_jornada', 20);
            $table->string('cod_personal', 20);
            $table->dateTime('fecha_hora_programada')->nullable();
            $table->dateTime('fecha_hora_administracion')->nullable();
            $table->string('resultado', 40);
            $table->decimal('dosis_administrada', 10, 3)->nullable();
            $table->text('motivo_omision')->nullable();
            $table->text('efecto_observado')->nullable();
            $table->text('reaccion_adversa')->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_residente', 'fecha_hora_programada']);
            $table->foreign('cod_prescripcion')->references('cod_prescripcion')->on('prescripciones')->restrictOnDelete();
            $table->foreign('cod_horario_prescripcion')->references('cod_horario_prescripcion')->on('horarios_prescripcion')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administraciones_medicacion');
    }
};
