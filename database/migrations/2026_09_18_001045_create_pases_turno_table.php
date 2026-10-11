<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pases_turno', function (Blueprint $table) {
            $table->string('cod_pase', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_jornada_saliente', 20);
            $table->string('cod_jornada_entrante', 20);
            $table->string('cod_personal_saliente', 20);
            $table->string('cod_personal_entrante', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->text('estado_general')->nullable();
            $table->text('resumen');
            $table->text('pendientes')->nullable();
            $table->text('vigilancia')->nullable();
            $table->text('recomendacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_jornada_saliente')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_jornada_entrante')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_personal_saliente')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_personal_entrante')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pases_turno');
    }
};
