<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 13 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consecuencias_regla_experta', function (Blueprint $table): void {
            $table->string('cod_consecuencia_regla', 20);
            $table->string('cod_regla_experta', 20);
            $table->string('cod_criterio_dominio_resultado', 20);
            $table->string('cod_valor_semantico', 20);
            $table->primary(['cod_consecuencia_regla'], 'se13_pk');
            $table->unique(['cod_regla_experta', 'cod_criterio_dominio_resultado'], 'se13_u1');
            $table->index(['cod_criterio_dominio_resultado'], 'se13_i1');
            $table->index(['cod_valor_semantico'], 'se13_i2');
            $table->foreign(['cod_regla_experta'], 'se13_f1')
                ->references(['cod_regla_experta'])->on('reglas_expertas')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_criterio_dominio_resultado'], 'se13_f2')
                ->references(['cod_criterio_dominio_resultado'])->on('criterios_dominios_resultado')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_valor_semantico'], 'se13_f3')
                ->references(['cod_valor_semantico'])->on('valores_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consecuencias_regla_experta');
    }
};
