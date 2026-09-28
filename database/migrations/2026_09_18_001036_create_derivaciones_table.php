<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('derivaciones', function (Blueprint $table) {
            $table->string('cod_derivacion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_area_solicitante', 20);
            $table->string('cod_area_receptora', 20);
            $table->string('cod_personal_solicitante', 20);
            $table->string('cod_personal_receptor', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->text('motivo');
            $table->string('prioridad', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->text('respuesta')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_area_solicitante')->references('cod_area')->on('areas')->restrictOnDelete();
            $table->foreign('cod_area_receptora')->references('cod_area')->on('areas')->restrictOnDelete();
            $table->foreign('cod_personal_solicitante')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_personal_receptor')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('derivaciones');
    }
};
