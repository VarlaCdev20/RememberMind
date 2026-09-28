<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->string('cod_actividad', 20)->primary();
            $table->string('cod_area', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo', 60);
            $table->string('nombre', 160);
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_hora');
            $table->unsignedSmallInteger('duracion_minutos')->nullable();
            $table->string('lugar', 120)->nullable();
            $table->unsignedSmallInteger('cupo')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_area')->references('cod_area')->on('areas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
