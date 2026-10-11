<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos_pedagogicos', function (Blueprint $table) {
            $table->string('cod_seguimiento_pedagogico', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_actividad', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('atencion', 40)->nullable();
            $table->string('comprension_instrucciones', 40)->nullable();
            $table->string('ejecucion_tarea', 40)->nullable();
            $table->string('reconocimiento', 40)->nullable();
            $table->string('orientacion', 40)->nullable();
            $table->string('participacion', 40)->nullable();
            $table->string('interaccion', 40)->nullable();
            $table->boolean('cambio_desempeno')->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_actividad')->references('cod_actividad')->on('actividades')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos_pedagogicos');
    }
};
