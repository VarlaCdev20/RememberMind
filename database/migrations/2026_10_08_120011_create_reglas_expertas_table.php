<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 11 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reglas_expertas', function (Blueprint $table): void {
            $table->string('cod_regla_experta', 20);
            $table->string('cod_version_modelo', 20);
            $table->string('codigo_regla', 50);
            $table->string('nombre', 160);
            $table->string('tipo_regla', 30);
            $table->text('descripcion');
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_regla_experta'], 'se11_pk');
            $table->unique(['cod_version_modelo', 'codigo_regla'], 'se11_u1');
            $table->index(['cod_version_modelo', 'tipo_regla', 'estado'], 'se11_i1');
            $table->index(['cod_usuario_creacion'], 'se11_i2');
            $table->foreign(['cod_version_modelo'], 'se11_f1')
                ->references(['cod_version_modelo'])->on('versiones_modelo_experto')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se11_f2')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglas_expertas');
    }
};
