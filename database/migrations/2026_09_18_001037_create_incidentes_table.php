<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidentes', function (Blueprint $table) {
            $table->string('cod_incidente', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->string('tipo_incidente', 60);
            $table->string('gravedad', 30)->nullable();
            $table->string('lugar', 120)->nullable();
            $table->dateTime('fecha_hora');
            $table->text('descripcion');
            $table->text('medida_inmediata')->nullable();
            $table->boolean('requiere_medico');
            $table->boolean('requiere_derivacion');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidentes');
    }
};
