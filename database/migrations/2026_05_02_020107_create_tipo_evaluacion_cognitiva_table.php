<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_evaluacion_cognitiva', function (Blueprint $table) {
            $table->id('cod_tipo_eval');

            $table->string('nombre', 80);
            $table->text('descripcion')->nullable();

            $table->decimal('puntaje_maximo', 5, 2)->default(30);
            $table->decimal('punto_corte_normal', 5, 2)->nullable();
            $table->decimal('punto_corte_riesgo', 5, 2)->nullable();

            $table->string('estado', 20)->default('ACTIVO');

            $table->timestamps();

            $table->unique('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_evaluacion_cognitiva');
    }
};