<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controles_cognitivos', function (Blueprint $table) {
            $table->string('cod_control_cognitivo', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            foreach (['orientacion_persona', 'orientacion_lugar', 'orientacion_tiempo', 'memoria_reciente', 'memoria_remota', 'atencion', 'comprension', 'lenguaje'] as $column) {
                $table->string($column, 30)->nullable();
            }
            foreach (['sigue_instrucciones', 'repite_preguntas', 'olvida_indicaciones', 'reconoce_personas', 'reconoce_entorno', 'confusion', 'cambio_cognitivo'] as $column) {
                $table->boolean($column)->nullable();
            }
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controles_cognitivos');
    }
};
