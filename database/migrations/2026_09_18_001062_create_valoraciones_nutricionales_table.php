<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones_nutricionales', function (Blueprint $table) {
            $table->string('cod_valoracion_nutricional', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_medicion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado_nutricional', 60)->nullable();
            $table->string('apetito', 40)->nullable();
            $table->string('deglucion', 40)->nullable();
            $table->string('riesgo_desnutricion', 40)->nullable();
            $table->string('necesidad_asistencia', 40)->nullable();
            $table->decimal('requerimiento_hidrico', 10, 2)->nullable();
            $table->text('restricciones_alimentarias')->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recomendacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_medicion')->references('cod_medicion')->on('mediciones_antropometricas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones_nutricionales');
    }
};
