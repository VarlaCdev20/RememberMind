<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 12 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condiciones_regla_experta', function (Blueprint $table): void {
            $table->string('cod_condicion_regla', 20);
            $table->string('cod_regla_experta', 20);
            $table->string('cod_variable_experta', 20);
            $table->string('cod_valor_semantico', 20);
            $table->primary(['cod_condicion_regla'], 'se12_pk');
            $table->unique(['cod_regla_experta', 'cod_variable_experta', 'cod_valor_semantico'], 'se12_u1');
            $table->index(['cod_variable_experta', 'cod_valor_semantico'], 'se12_i1');
            $table->foreign(['cod_regla_experta'], 'se12_f1')
                ->references(['cod_regla_experta'])->on('reglas_expertas')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_variable_experta'], 'se12_f2')
                ->references(['cod_variable_experta'])->on('variables_expertas')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_valor_semantico'], 'se12_f3')
                ->references(['cod_valor_semantico'])->on('valores_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condiciones_regla_experta');
    }
};
