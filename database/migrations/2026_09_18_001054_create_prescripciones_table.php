<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescripciones', function (Blueprint $table) {
            $table->string('cod_prescripcion', 20)->primary();
            $table->string('cod_med_adulto', 30)->nullable();
            $table->string('cod_residente', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_medicamento', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_personal_suspension', 20)->nullable();
            $table->decimal('dosis', 10, 3)->nullable();
            $table->string('unidad_dosis', 30)->nullable();
            $table->string('via_administracion', 60);
            $table->string('frecuencia', 80)->nullable();
            $table->text('indicacion')->nullable();
            $table->boolean('segun_necesidad');
            $table->dateTime('fecha_hora_prescripcion');
            $table->dateTime('fecha_hora_suspension')->nullable();
            $table->text('motivo_suspension')->nullable();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'estado']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_medicamento')->references('cod_medicamento')->on('medicamentos')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_personal_suspension')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescripciones');
    }
};
