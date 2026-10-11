<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_residente_jornada', function (Blueprint $table) {
            $table->string('cod_asignacion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_jornada', 20);
            $table->string('cod_personal', 20);
            $table->string('nivel_supervision', 30)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_residente_jornada');
    }
};
