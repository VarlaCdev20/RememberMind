<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opciones_pregunta', function (Blueprint $table) {
            $table->string('cod_opcion', 20)->primary();
            $table->string('cod_pregunta', 20);
            $table->string('nombre', 160);
            $table->string('valor', 120)->nullable();
            $table->decimal('puntaje', 8, 2)->nullable();
            $table->smallInteger('orden');
            $table->string('estado', 20);
            $table->foreign('cod_pregunta')->references('cod_pregunta')->on('preguntas_instrumento')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opciones_pregunta');
    }
};
