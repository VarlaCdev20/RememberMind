<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heridas', function (Blueprint $table) {
            $table->string('cod_herida', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo_herida', 60);
            $table->string('ubicacion', 120);
            $table->string('causa', 120)->nullable();
            $table->string('clasificacion', 60)->nullable();
            $table->dateTime('fecha_hora_identificacion');
            $table->dateTime('fecha_hora_cierre')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heridas');
    }
};
