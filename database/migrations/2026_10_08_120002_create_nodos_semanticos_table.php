<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 2 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nodos_semanticos', function (Blueprint $table): void {
            $table->string('cod_nodo_semantico', 20);
            $table->string('cod_version_modelo', 20);
            $table->string('codigo_semantico', 50);
            $table->string('nombre', 160);
            $table->string('tipo_nodo', 40);
            $table->text('definicion');
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_nodo_semantico'], 'se02_pk');
            $table->unique(['cod_version_modelo', 'codigo_semantico'], 'se02_u1');
            $table->unique(['cod_nodo_semantico', 'cod_version_modelo'], 'se02_u2');
            $table->index(['tipo_nodo'], 'se02_i1');
            $table->index(['cod_usuario_creacion'], 'se02_i2');
            $table->foreign(['cod_version_modelo'], 'se02_f1')
                ->references(['cod_version_modelo'])->on('versiones_modelo_experto')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se02_f2')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodos_semanticos');
    }
};
