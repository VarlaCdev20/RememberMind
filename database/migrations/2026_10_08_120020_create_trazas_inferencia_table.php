<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 20 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trazas_inferencia', function (Blueprint $table): void {
            $table->string('cod_traza_inferencia', 20);
            $table->string('cod_evaluacion_criterio', 20);
            $table->string('estado_inferencia', 30);
            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin')->nullable();
            $table->primary(['cod_traza_inferencia'], 'se20_pk');
            $table->unique(['cod_evaluacion_criterio'], 'se20_u1');
            $table->index(['estado_inferencia'], 'se20_i1');
            $table->foreign(['cod_evaluacion_criterio'], 'se20_f1')
                ->references(['cod_evaluacion_criterio'])->on('evaluacion_criterios')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trazas_inferencia');
    }
};
