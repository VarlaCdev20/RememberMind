<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas_instrumento', function (Blueprint $table) {
            $table->string('cod_pregunta', 20)->primary();
            $table->string('cod_instrumento', 20);
            $table->string('codigo', 30);
            $table->text('enunciado');
            $table->string('dominio', 80)->nullable();
            $table->string('tipo_respuesta', 30);
            $table->decimal('puntaje_maximo', 8, 2)->nullable();
            $table->smallInteger('orden');
            $table->string('estado', 20);
            $table->foreign('cod_instrumento')->references('cod_instrumento')->on('instrumentos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_instrumento');
    }
};
