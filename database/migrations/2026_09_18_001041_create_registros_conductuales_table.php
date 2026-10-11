<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_conductuales', function (Blueprint $table) {
            $table->string('cod_registro_conductual', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_jornada', 20)->nullable();
            $table->string('cod_atencion', 20)->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado_animo', 40)->nullable();
            foreach (['apatia', 'agitacion', 'agresividad', 'ansiedad', 'aislamiento', 'deambulacion'] as $column) {
                $table->boolean($column)->nullable();
            }
            $table->string('participacion', 30)->nullable();
            $table->boolean('cambio_conducta')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('intervencion')->nullable();
            $table->text('respuesta')->nullable();
            $table->string('estado', 20);
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_jornada')->references('cod_jornada')->on('jornadas')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_conductuales');
    }
};
