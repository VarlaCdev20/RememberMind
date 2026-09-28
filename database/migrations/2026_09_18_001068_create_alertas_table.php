<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->string('cod_alerta', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal_responsable', 20)->nullable();
            $table->string('tipo', 60);
            $table->string('prioridad', 20);
            $table->string('modulo', 60)->nullable();
            $table->string('cod_registro', 20)->nullable();
            $table->string('titulo', 180);
            $table->text('descripcion');
            $table->dateTime('fecha_hora');
            $table->dateTime('fecha_hora_limite')->nullable();
            $table->string('generacion', 30);
            $table->string('estado', 20);
            $table->index(['cod_residente', 'estado', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal_responsable')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
