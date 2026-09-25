<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudios_clinicos', function (Blueprint $table) {
            $table->string('cod_estudio', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_tipo_estudio', 20);
            $table->string('cod_personal', 20);
            $table->text('motivo')->nullable();
            $table->string('prioridad', 20)->nullable();
            $table->dateTime('fecha_solicitud');
            $table->dateTime('fecha_realizacion')->nullable();
            $table->string('centro_medico', 160)->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'fecha_solicitud']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_tipo_estudio')->references('cod_tipo_estudio')->on('tipos_estudio_clinico')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudios_clinicos');
    }
};
