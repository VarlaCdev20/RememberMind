<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_ingesta', function (Blueprint $table) {
            $table->string('cod_ingesta', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20);
            $table->dateTime('fecha_hora');
            $table->string('tipo_comida', 40);
            $table->decimal('porcentaje_consumido', 5, 2)->nullable();
            $table->string('apetito', 30)->nullable();
            $table->string('tolerancia', 30)->nullable();
            $table->boolean('dificultad_deglucion')->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_ingesta');
    }
};
