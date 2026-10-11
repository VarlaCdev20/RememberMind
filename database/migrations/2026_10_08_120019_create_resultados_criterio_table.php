<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 19 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultados_criterio', function (Blueprint $table): void {
            $table->string('cod_resultado_criterio', 20);
            $table->string('cod_evaluacion_criterio', 20);
            $table->string('cod_valor_semantico', 20);
            $table->dateTime('fecha_hora_determinacion');
            $table->primary(['cod_resultado_criterio'], 'se19_pk');
            $table->unique(['cod_evaluacion_criterio'], 'se19_u1');
            $table->index(['cod_valor_semantico'], 'se19_i1');
            $table->foreign(['cod_evaluacion_criterio'], 'se19_f1')
                ->references(['cod_evaluacion_criterio'])->on('evaluacion_criterios')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_valor_semantico'], 'se19_f2')
                ->references(['cod_valor_semantico'])->on('valores_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados_criterio');
    }
};
