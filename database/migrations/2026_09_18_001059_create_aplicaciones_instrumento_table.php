<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aplicaciones_instrumento', function (Blueprint $table) {
            $table->string('cod_aplicacion', 20)->primary();
            $table->string('cod_instrumento', 20);
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->decimal('puntaje_total', 8, 2)->nullable();
            $table->decimal('puntaje_maximo', 8, 2)->nullable();
            $table->string('clasificacion', 80)->nullable();
            $table->text('interpretacion')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_instrumento')->references('cod_instrumento')->on('instrumentos')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aplicaciones_instrumento');
    }
};
