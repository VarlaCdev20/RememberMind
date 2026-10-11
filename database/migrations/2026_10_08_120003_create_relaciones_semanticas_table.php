<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 3 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relaciones_semanticas', function (Blueprint $table): void {
            $table->string('cod_relacion_semantica', 20);
            $table->string('cod_version_modelo', 20);
            $table->string('cod_nodo_origen', 20);
            $table->string('tipo_relacion', 50);
            $table->string('modalidad_relacion', 30)->nullable();
            $table->string('cod_nodo_destino', 20);
            $table->text('significado');
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_relacion_semantica'], 'se03_pk');
            $table->unique(['cod_version_modelo', 'cod_nodo_origen', 'tipo_relacion', 'cod_nodo_destino'], 'se03_u1');
            $table->index(['cod_nodo_destino'], 'se03_i1');
            $table->index(['tipo_relacion'], 'se03_i2');
            $table->index(['cod_usuario_creacion'], 'se03_i3');
            $table->foreign(['cod_version_modelo'], 'se03_f1')
                ->references(['cod_version_modelo'])->on('versiones_modelo_experto')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_nodo_origen'], 'se03_f2')
                ->references(['cod_nodo_semantico'])->on('nodos_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_nodo_destino'], 'se03_f3')
                ->references(['cod_nodo_semantico'])->on('nodos_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se03_f4')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_nodo_origen', 'cod_version_modelo'], 'se03_f5')
                ->references(['cod_nodo_semantico', 'cod_version_modelo'])->on('nodos_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_nodo_destino', 'cod_version_modelo'], 'se03_f6')
                ->references(['cod_nodo_semantico', 'cod_version_modelo'])->on('nodos_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relaciones_semanticas');
    }
};
