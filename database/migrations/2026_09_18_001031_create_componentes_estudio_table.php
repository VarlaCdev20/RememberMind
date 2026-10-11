<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('componentes_estudio', function (Blueprint $table) {
            $table->string('cod_componente', 20)->primary();
            $table->string('cod_tipo_estudio', 20);
            $table->string('nombre', 120);
            $table->string('unidad_referencia', 40)->nullable();
            $table->string('tipo_resultado', 30);
            $table->smallInteger('orden');
            $table->string('estado', 20);
            $table->foreign('cod_tipo_estudio')->references('cod_tipo_estudio')->on('tipos_estudio_clinico')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('componentes_estudio');
    }
};
