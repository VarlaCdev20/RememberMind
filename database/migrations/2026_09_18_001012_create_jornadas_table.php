<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jornadas', function (Blueprint $table) {
            $table->string('cod_jornada', 20)->primary();
            $table->string('cod_turno', 20);
            $table->string('cod_usuario_apertura', 20)->nullable();
            $table->string('cod_usuario_cierre', 20)->nullable();
            $table->date('fecha_jornada');
            $table->string('estado', 20);
            $table->foreign('cod_turno')->references('cod_turno')->on('turnos')->restrictOnDelete();
            $table->foreign('cod_usuario_apertura')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
            $table->foreign('cod_usuario_cierre')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jornadas');
    }
};
