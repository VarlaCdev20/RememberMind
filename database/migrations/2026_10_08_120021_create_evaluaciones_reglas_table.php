<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 21 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_reglas', function (Blueprint $table): void {
            $table->string('cod_evaluacion_regla', 20);
            $table->string('cod_traza_inferencia', 20);
            $table->string('cod_regla_experta', 20);
            $table->string('estado_regla', 20);
            $table->dateTime('fecha_hora_evaluacion');
            $table->primary(['cod_evaluacion_regla'], 'se21_pk');
            $table->unique(['cod_traza_inferencia', 'cod_regla_experta'], 'se21_u1');
            $table->index(['cod_regla_experta'], 'se21_i1');
            $table->index(['estado_regla'], 'se21_i2');
            $table->foreign(['cod_traza_inferencia'], 'se21_f1')
                ->references(['cod_traza_inferencia'])->on('trazas_inferencia')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_regla_experta'], 'se21_f2')
                ->references(['cod_regla_experta'])->on('reglas_expertas')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_reglas');
    }
};
