<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 5 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valores_semanticos', function (Blueprint $table): void {
            $table->string('cod_valor_semantico', 20);
            $table->string('cod_dominio_valores', 20);
            $table->string('codigo_valor', 60);
            $table->string('nombre', 160);
            $table->text('definicion_semantica');
            $table->string('estado_aprobacion', 40);
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_valor_semantico'], 'se05_pk');
            $table->unique(['cod_dominio_valores', 'codigo_valor'], 'se05_u1');
            $table->index(['estado_aprobacion'], 'se05_i1');
            $table->index(['cod_usuario_creacion'], 'se05_i2');
            $table->foreign(['cod_dominio_valores'], 'se05_f1')
                ->references(['cod_dominio_valores'])->on('dominios_valores_expertos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se05_f2')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valores_semanticos');
    }
};
