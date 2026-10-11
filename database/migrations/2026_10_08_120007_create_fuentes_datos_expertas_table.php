<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 7 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuentes_datos_expertas', function (Blueprint $table): void {
            $table->string('cod_fuente_dato_experta', 20);
            $table->string('cod_version_modelo', 20);
            $table->string('codigo_fuente', 50);
            $table->string('nombre', 160);
            $table->string('tipo_fuente', 30);
            $table->string('tabla_raiz', 80);
            $table->string('campo_pk_raiz', 80);
            $table->string('campo_residente', 80)->nullable();
            $table->string('campo_temporal', 80)->nullable();
            $table->string('campo_personal', 80)->nullable();
            $table->string('campo_estado', 80)->nullable();
            $table->string('clave_adaptador', 80)->nullable();
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_fuente_dato_experta'], 'se07_pk');
            $table->unique(['cod_version_modelo', 'codigo_fuente'], 'se07_u1');
            $table->unique(['cod_fuente_dato_experta', 'cod_version_modelo'], 'se07_u2');
            $table->index(['tabla_raiz'], 'se07_i1');
            $table->index(['cod_usuario_creacion'], 'se07_i2');
            $table->foreign(['cod_version_modelo'], 'se07_f1')
                ->references(['cod_version_modelo'])->on('versiones_modelo_experto')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se07_f2')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuentes_datos_expertas');
    }
};
