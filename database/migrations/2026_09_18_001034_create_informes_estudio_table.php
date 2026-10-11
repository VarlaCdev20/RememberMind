<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informes_estudio', function (Blueprint $table) {
            $table->string('cod_informe_estudio', 20)->primary();
            $table->string('cod_estudio', 20);
            $table->string('cod_personal', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->text('hallazgos')->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recomendacion')->nullable();
            $table->string('origen', 20);
            $table->string('profesional_externo', 160)->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_estudio')->references('cod_estudio')->on('estudios_clinicos')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes_estudio');
    }
};
