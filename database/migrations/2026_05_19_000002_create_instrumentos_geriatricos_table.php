<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('instrumentos_geriatricos', function (Blueprint $table) {
            $table->string('cod_instrumento', 30)->primary(); // INS_KATZ, INS_MMSE
            $table->string('cod_area', 20);
            $table->string('nombre', 150);
            $table->string('siglas', 20)->nullable();
            $table->string('tipo_resultado', 30)->default('CUANTITATIVO'); // CUANTITATIVO, CUALITATIVO, MIXTO, TIEMPO, FRACCION_VISUAL
            $table->decimal('puntaje_maximo', 8, 2)->nullable();
            $table->decimal('punto_corte_normal', 8, 2)->nullable();
            $table->decimal('punto_corte_riesgo', 8, 2)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('estado', 20)->default('ACTIVO'); // ACTIVO, INACTIVO
            
            $table->foreign('cod_area')->references('cod_area')->on('areas_geriatricas')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrumentos_geriatricos');
    }
};
