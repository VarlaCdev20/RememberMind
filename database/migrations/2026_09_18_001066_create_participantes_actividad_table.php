<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participantes_actividad', function (Blueprint $table) {
            $table->string('cod_participante', 20)->primary();
            $table->string('cod_actividad', 20);
            $table->string('cod_residente', 20);
            $table->string('asistencia', 30)->nullable();
            $table->string('nivel_participacion', 40)->nullable();
            $table->string('desempeno', 40)->nullable();
            $table->text('observacion')->nullable();
            $table->unique(['cod_actividad', 'cod_residente']);
            $table->foreign('cod_actividad')->references('cod_actividad')->on('actividades')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participantes_actividad');
    }
};
