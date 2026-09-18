<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admisiones', function (Blueprint $table) {
            $table->string('cod_admision', 20)->primary();
            $table->string('cod_preadmision', 20)->nullable();
            $table->string('cod_residente', 20);
            $table->string('cod_usuario_registro', 20);
            $table->dateTime('fecha_hora_admision');
            $table->string('tipo_ingreso', 50)->nullable();
            $table->string('procedencia', 120)->nullable();
            $table->text('motivo_ingreso');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_preadmision')->references('cod_preadmision')->on('preadmisiones')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admisiones');
    }
};
