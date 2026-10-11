<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocupaciones_cama', function (Blueprint $table) {
            $table->string('cod_ocupacion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_cama', 20);
            $table->string('cod_admision', 20);
            $table->string('cod_usuario_registro', 20);
            $table->dateTime('fecha_hora_asignacion');
            $table->dateTime('fecha_hora_liberacion')->nullable();
            $table->text('motivo_liberacion')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_cama', 'estado']);
            $table->index(['cod_residente', 'estado']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_cama')->references('cod_cama')->on('camas')->restrictOnDelete();
            $table->foreign('cod_admision')->references('cod_admision')->on('admisiones')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocupaciones_cama');
    }
};
