<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejecuciones_cuidado', function (Blueprint $table) {
            $table->string('cod_ejecucion', 20)->primary();
            $table->string('cod_intervencion', 20);
            $table->string('cod_residente', 20);
            $table->string('cod_jornada', 20);
            $table->string('cod_personal', 20);
            $table->dateTime('fecha_hora_programada')->nullable();
            $table->dateTime('fecha_hora_ejecucion')->nullable();
            $table->string('resultado', 60)->nullable();
            $table->text('motivo_omision')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_intervencion')->references('cod_intervencion')->on('intervenciones_cuidado')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejecuciones_cuidado');
    }
};
