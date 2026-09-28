<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones_funcionales', function (Blueprint $table) {
            $table->string('cod_valoracion_funcional', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_atencion', 20);
            $table->dateTime('fecha_hora');
            foreach (['marcha', 'equilibrio', 'traslado', 'fuerza_funcional', 'resistencia'] as $column) {
                $table->string($column, 40)->nullable();
            }
            foreach (['alimentacion_autonoma', 'bano_autonomo', 'vestido_autonomo', 'higiene_autonoma', 'continencia', 'movilidad_autonoma'] as $column) {
                $table->string($column, 30)->nullable();
            }
            $table->boolean('necesita_supervision')->nullable();
            $table->string('nivel_dependencia', 40)->nullable();
            $table->text('conclusion')->nullable();
            $table->text('recomendacion')->nullable();
            $table->string('estado', 20);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valoraciones_funcionales');
    }
};
