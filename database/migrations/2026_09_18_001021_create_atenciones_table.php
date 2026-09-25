<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atenciones', function (Blueprint $table) {
            $table->string('cod_atencion', 20)->primary();
            $table->string('cod_residente', 20);
            $table->string('cod_area', 20);
            $table->string('cod_personal', 20);
            $table->string('tipo_atencion', 60);
            $table->text('motivo')->nullable();
            $table->dateTime('fecha_hora');
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->index(['cod_residente', 'fecha_hora']);
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_area')->references('cod_area')->on('areas')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atenciones');
    }
};
