<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_personal', function (Blueprint $table) {
            $table->string('cod_asignacion_personal', 20)->primary();
            $table->string('cod_jornada', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_area', 20);
            $table->string('funcion', 80)->nullable();
            $table->string('tipo_asignacion', 30);
            $table->dateTime('fecha_asignacion');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_area')->references('cod_area')->on('areas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_personal');
    }
};
