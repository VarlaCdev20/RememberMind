<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// O.R.I.O.N. D-123 / D-137: 4.16, 4.19. Orden físico 4 de 23.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dominios_valores_expertos', function (Blueprint $table): void {
            $table->string('cod_dominio_valores', 20);
            $table->string('cod_version_modelo', 20);
            $table->string('codigo_dominio', 50);
            $table->string('nombre', 160);
            $table->text('descripcion');
            $table->string('tipo_dominio', 30);
            $table->string('estado', 20);
            $table->dateTime('fecha_hora_creacion');
            $table->string('cod_usuario_creacion', 20);
            $table->text('observacion')->nullable();
            $table->primary(['cod_dominio_valores'], 'se04_pk');
            $table->unique(['cod_version_modelo', 'codigo_dominio'], 'se04_u1');
            $table->unique(['cod_dominio_valores', 'cod_version_modelo'], 'se04_u2');
            $table->index(['cod_usuario_creacion'], 'se04_i1');
            $table->foreign(['cod_version_modelo'], 'se04_f1')
                ->references(['cod_version_modelo'])->on('versiones_modelo_experto')
                ->restrictOnDelete()->noActionOnUpdate();
            $table->foreign(['cod_usuario_creacion'], 'se04_f2')
                ->references(['cod_usuario'])->on('usuarios')
                ->restrictOnDelete()->noActionOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dominios_valores_expertos');
    }
};
