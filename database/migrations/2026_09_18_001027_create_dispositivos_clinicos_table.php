<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispositivos_clinicos', function (Blueprint $table) {
            $table->string('cod_dispositivo', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo', 60);
            $table->text('descripcion')->nullable();
            $table->string('ubicacion', 120)->nullable();
            $table->dateTime('fecha_colocacion')->nullable();
            $table->dateTime('fecha_retiro')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispositivos_clinicos');
    }
};
