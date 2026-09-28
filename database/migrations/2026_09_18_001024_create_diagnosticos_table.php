<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosticos', function (Blueprint $table) {
            $table->string('cod_diagnostico', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_personal', 20);
            $table->string('codigo_clinico', 30)->nullable();
            $table->string('nombre', 160);
            $table->string('tipo', 50)->nullable();
            $table->string('certeza', 30)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosticos');
    }
};
