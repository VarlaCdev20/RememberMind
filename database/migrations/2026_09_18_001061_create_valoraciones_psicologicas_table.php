<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones_psicologicas', function (Blueprint $table) {
            $table->string('cod_valoracion_psicologica', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_atencion', 20);
            $table->dateTime('fecha_hora');
            $table->string('estado_animo', 50)->nullable();
            $table->string('afecto', 50)->nullable();
            $table->string('ansiedad', 50)->nullable();
            $table->string('apatia', 50)->nullable();
            $table->text('percepcion')->nullable();
            $table->text('conducta')->nullable();
            $table->text('comunicacion')->nullable();
            $table->text('interaccion_social')->nullable();
            $table->text('impresion_cognitiva')->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recomendacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones_psicologicas');
    }
};
