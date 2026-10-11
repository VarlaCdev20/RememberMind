<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 9 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapeos_valores_fuente', function (Blueprint $table): void {
            $table->string('cod_mapeo_valor_fuente', 20);
            $table->string('cod_mapeo_variable_fuente', 20);
            $table->string('cod_valor_semantico', 20);
            $table->string('tipo_valor_fuente', 30);
            $table->text('valor_fuente_exacto');
            $table->string('estado_aprobacion', 40);
            $table->dateTime('fecha_hora_vigencia_desde')->nullable();
            $table->dateTime('fecha_hora_vigencia_hasta')->nullable();
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_mapeo_valor_fuente'], 'se09_pk');
            $table->index(['cod_mapeo_variable_fuente', 'estado'], 'se09_i1');
            $table->index(['cod_valor_semantico'], 'se09_i2');
            $table->index(['cod_usuario_creacion'], 'se09_i3');
            $table->foreign(['cod_mapeo_variable_fuente'], 'se09_f1')
                ->references(['cod_mapeo_variable_fuente'])->on('mapeos_variables_fuente')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_valor_semantico'], 'se09_f2')
                ->references(['cod_valor_semantico'])->on('valores_semanticos')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se09_f3')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapeos_valores_fuente');
    }
};
