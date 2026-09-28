<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_alerta', function (Blueprint $table) {
            $table->string('cod_evento_alerta', 20)->primary();
            $table->string('cod_alerta', 20);
            $table->string('cod_usuario', 20);
            $table->string('tipo_evento', 50);
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30)->nullable();
            $table->dateTime('fecha_hora');
            $table->text('descripcion')->nullable();
            $table->foreign('cod_alerta')->references('cod_alerta')->on('alertas')->restrictOnDelete();
            $table->foreign('cod_usuario')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_alerta');
    }
};
