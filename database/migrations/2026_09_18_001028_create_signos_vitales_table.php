<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signos_vitales', function (Blueprint $table) {
            $table->string('cod_signo', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->decimal('presion_sistolica', 5, 2)->nullable();
            $table->decimal('presion_diastolica', 5, 2)->nullable();
            $table->decimal('frecuencia_cardiaca', 6, 2)->nullable();
            $table->decimal('frecuencia_respiratoria', 6, 2)->nullable();
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->decimal('saturacion_oxigeno', 5, 2)->nullable();
            $table->decimal('glucemia', 8, 2)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signos_vitales');
    }
};
