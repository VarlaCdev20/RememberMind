<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 10 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criterios_dominios_resultado', function (Blueprint $table): void {
            $table->string('cod_criterio_dominio_resultado', 20);
            $table->string('cod_nodo_criterio', 20);
            $table->string('cod_dominio_valores', 20);
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_criterio_dominio_resultado'], 'se10_pk');
            $table->unique(['cod_nodo_criterio'], 'se10_u1');
            $table->index(['cod_dominio_valores'], 'se10_i1');
            $table->index(['estado'], 'se10_i2');
            $table->index(['cod_usuario_creacion'], 'se10_i3');
            $table->foreign(['cod_nodo_criterio'], 'se10_f1')
                ->references(['cod_nodo_semantico'])->on('nodos_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_dominio_valores'], 'se10_f2')
                ->references(['cod_dominio_valores'])->on('dominios_valores_expertos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se10_f3')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criterios_dominios_resultado');
    }
};
