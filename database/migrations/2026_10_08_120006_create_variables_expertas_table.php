<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 6 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variables_expertas', function (Blueprint $table): void {
            $table->string('cod_variable_experta', 20);
            $table->string('cod_version_modelo', 20);
            $table->string('cod_nodo_semantico', 20);
            $table->string('cod_nodo_propietario_primario', 20)->nullable();
            $table->string('cod_dominio_valores', 20)->nullable();
            $table->string('tipo_semantico', 30);
            $table->string('papel_inferencial', 40);
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_variable_experta'], 'se06_pk');
            $table->unique(['cod_nodo_semantico'], 'se06_u1');
            $table->unique(['cod_variable_experta', 'cod_version_modelo'], 'se06_u2');
            $table->index(['cod_version_modelo'], 'se06_i1');
            $table->index(['cod_nodo_propietario_primario'], 'se06_i2');
            $table->index(['cod_dominio_valores'], 'se06_i3');
            $table->index(['cod_usuario_creacion'], 'se06_i4');
            $table->foreign(['cod_version_modelo'], 'se06_f1')
                ->references(['cod_version_modelo'])->on('versiones_modelo_experto')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_nodo_semantico'], 'se06_f2')
                ->references(['cod_nodo_semantico'])->on('nodos_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_nodo_propietario_primario'], 'se06_f3')
                ->references(['cod_nodo_semantico'])->on('nodos_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_dominio_valores'], 'se06_f4')
                ->references(['cod_dominio_valores'])->on('dominios_valores_expertos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se06_f5')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_nodo_semantico', 'cod_version_modelo'], 'se06_f6')
                ->references(['cod_nodo_semantico', 'cod_version_modelo'])->on('nodos_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_nodo_propietario_primario', 'cod_version_modelo'], 'se06_f7')
                ->references(['cod_nodo_semantico', 'cod_version_modelo'])->on('nodos_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_dominio_valores', 'cod_version_modelo'], 'se06_f8')
                ->references(['cod_dominio_valores', 'cod_version_modelo'])->on('dominios_valores_expertos')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variables_expertas');
    }
};
