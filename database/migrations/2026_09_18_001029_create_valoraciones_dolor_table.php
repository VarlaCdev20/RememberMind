<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones_dolor', function (Blueprint $table) {
            $table->string('cod_valoracion_dolor', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->unsignedTinyInteger('intensidad')->nullable();
            $table->string('ubicacion', 120)->nullable();
            $table->string('tipo_dolor', 60)->nullable();
            $table->string('duracion', 80)->nullable();
            $table->text('desencadenante')->nullable();
            $table->text('intervencion')->nullable();
            $table->text('respuesta')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones_dolor');
    }
};
