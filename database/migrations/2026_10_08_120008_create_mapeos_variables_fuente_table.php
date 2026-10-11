<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 8 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapeos_variables_fuente', function (Blueprint $table): void {
            $table->string('cod_mapeo_variable_fuente', 20);
            $table->string('cod_version_modelo', 20);
            $table->string('cod_variable_experta', 20);
            $table->string('cod_fuente_dato_experta', 20);
            $table->string('tipo_extraccion', 30);
            $table->string('campo_valor', 80)->nullable();
            $table->string('clave_selector', 80)->nullable();
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_mapeo_variable_fuente'], 'se08_pk');
            $table->index(['cod_version_modelo'], 'se08_i1');
            $table->index(['cod_variable_experta', 'cod_fuente_dato_experta'], 'se08_i2');
            $table->index(['cod_fuente_dato_experta'], 'se08_i3');
            $table->index(['cod_usuario_creacion'], 'se08_i4');
            $table->foreign(['cod_version_modelo'], 'se08_f1')
                ->references(['cod_version_modelo'])->on('versiones_modelo_experto')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_variable_experta'], 'se08_f2')
                ->references(['cod_variable_experta'])->on('variables_expertas')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_fuente_dato_experta'], 'se08_f3')
                ->references(['cod_fuente_dato_experta'])->on('fuentes_datos_expertas')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se08_f4')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_variable_experta', 'cod_version_modelo'], 'se08_f5')
                ->references(['cod_variable_experta', 'cod_version_modelo'])->on('variables_expertas')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_fuente_dato_experta', 'cod_version_modelo'], 'se08_f6')
                ->references(['cod_fuente_dato_experta', 'cod_version_modelo'])->on('fuentes_datos_expertas')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapeos_variables_fuente');
    }
};
