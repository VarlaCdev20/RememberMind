<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 15 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidencias_evaluacion', function (Blueprint $table): void {
            $table->string('cod_evidencia_evaluacion', 20);
            $table->string('cod_evaluacion_experta', 20);
            $table->string('cod_mapeo_variable_fuente', 20);
            $table->string('cod_registro_fuente', 20);
            $table->string('estado_representacion', 30);
            $table->string('cod_valor_semantico', 20)->nullable();
            $table->string('estado_admisibilidad', 30);
            $table->text('motivo_admisibilidad')->nullable();
            $table->dateTime('fecha_hora_incorporacion');
            $table->primary(['cod_evidencia_evaluacion'], 'se15_pk');
            $table->unique(['cod_evaluacion_experta', 'cod_mapeo_variable_fuente', 'cod_registro_fuente'], 'se15_u1');
            $table->index(['cod_mapeo_variable_fuente'], 'se15_i1');
            $table->index(['cod_valor_semantico'], 'se15_i2');
            $table->index(['cod_evaluacion_experta', 'estado_admisibilidad'], 'se15_i3');
            $table->foreign(['cod_evaluacion_experta'], 'se15_f1')
                ->references(['cod_evaluacion_experta'])->on('evaluaciones_expertas')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_mapeo_variable_fuente'], 'se15_f2')
                ->references(['cod_mapeo_variable_fuente'])->on('mapeos_variables_fuente')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_valor_semantico'], 'se15_f3')
                ->references(['cod_valor_semantico'])->on('valores_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias_evaluacion');
    }
};
