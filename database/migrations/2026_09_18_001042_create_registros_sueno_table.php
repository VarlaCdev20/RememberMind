<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_sueno', function (Blueprint $table) {
            $table->string('cod_registro_sueno', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->date('fecha');
            $table->decimal('horas_sueno', 4, 2)->nullable();
            $table->smallInteger('despertares')->nullable();
            $table->boolean('insomnio')->nullable();
            $table->boolean('somnolencia_diurna')->nullable();
            $table->boolean('agitacion_nocturna')->nullable();
            $table->string('calidad', 30)->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_sueno');
    }
};
