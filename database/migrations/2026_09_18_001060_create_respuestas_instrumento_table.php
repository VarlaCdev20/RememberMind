<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respuestas_instrumento', function (Blueprint $table) {
            $table->string('cod_respuesta', 20)->primary();
            $table->string('cod_aplicacion', 20);
            $table->string('cod_pregunta', 20);
            $table->string('cod_opcion', 20)->nullable();
            $table->decimal('valor_numero', 12, 4)->nullable();
            $table->text('valor_texto')->nullable();
            $table->boolean('valor_logico')->nullable();
            $table->decimal('puntaje', 8, 2)->nullable();
            $table->text('observacion')->nullable();
            $table->unique(['cod_aplicacion', 'cod_pregunta']);
            $table->foreign('cod_aplicacion')->references('cod_aplicacion')->on('aplicaciones_instrumento')->restrictOnDelete();
            $table->foreign('cod_pregunta')->references('cod_pregunta')->on('preguntas_instrumento')->restrictOnDelete();
            $table->foreign('cod_opcion')->references('cod_opcion')->on('opciones_pregunta')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas_instrumento');
    }
};
