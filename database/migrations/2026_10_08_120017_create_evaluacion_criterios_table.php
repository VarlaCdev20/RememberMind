<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 17 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluacion_criterios', function (Blueprint $table): void {
            $table->string('cod_evaluacion_criterio', 20);
            $table->string('cod_evaluacion_experta', 20);
            $table->string('cod_nodo_criterio', 20);
            $table->string('estado_evaluabilidad', 30);
            $table->string('codigo_modificador_interpretacion', 30)->nullable();
            $table->text('motivo_evaluabilidad')->nullable();
            $table->dateTime('fecha_hora_determinacion');
            $table->primary(['cod_evaluacion_criterio'], 'se17_pk');
            $table->unique(['cod_evaluacion_experta', 'cod_nodo_criterio'], 'se17_u1');
            $table->index(['cod_nodo_criterio'], 'se17_i1');
            $table->index(['estado_evaluabilidad'], 'se17_i2');
            $table->foreign(['cod_evaluacion_experta'], 'se17_f1')
                ->references(['cod_evaluacion_experta'])->on('evaluaciones_expertas')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_nodo_criterio'], 'se17_f2')
                ->references(['cod_nodo_semantico'])->on('nodos_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluacion_criterios');
    }
};
