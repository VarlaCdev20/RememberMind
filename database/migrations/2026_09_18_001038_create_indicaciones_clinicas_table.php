<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicaciones_clinicas', function (Blueprint $table) {
            $table->string('cod_indicacion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo_indicacion', 40);
            $table->text('descripcion');
            $table->string('prioridad', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicaciones_clinicas');
    }
};
