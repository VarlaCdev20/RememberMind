<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 1 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versiones_modelo_experto', function (Blueprint $table): void {
            $table->string('cod_version_modelo', 20);
            $table->string('codigo_version', 30);
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->dateTime('fecha_hora_vigencia')->nullable();
            $table->dateTime('fecha_hora_retiro')->nullable();
            $table->text('motivo_cambio')->nullable();
            $table->string('cod_version_anterior', 20)->nullable();
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_version_modelo'], 'se01_pk');
            $table->unique(['codigo_version'], 'se01_u1');
            $table->index(['estado'], 'se01_i1');
            $table->index(['cod_version_anterior'], 'se01_i2');
            $table->index(['cod_usuario_creacion'], 'se01_i3');
            $table->foreign(['cod_version_anterior'], 'se01_f1')
                ->references(['cod_version_modelo'])->on('versiones_modelo_experto')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se01_f2')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versiones_modelo_experto');
    }
};
