<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_clinicas', function (Blueprint $table) {
            $table->string('cod_nota', 20);
            $table->string('cod_atencion', 20);
            $table->string('cod_residente', 20);
            $table->string('cod_personal', 20);
            $table->string('cod_nota_anterior', 20)->nullable();
            $table->string('tipo_nota', 50);
            $table->text('contenido');
            $table->dateTime('fecha_hora');
            $table->text('motivo_correccion')->nullable();
            $table->string('estado', 20);
            $table->primary('cod_nota');
            $table->foreign('cod_atencion')->references('cod_atencion')->on('atenciones')->restrictOnDelete();
            $table->foreign('cod_residente')->references('cod_residente')->on('residentes')->restrictOnDelete();
            $table->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
        });

        Schema::table('notas_clinicas', function (Blueprint $table) {
            $table->foreign('cod_nota_anterior')->references('cod_nota')->on('notas_clinicas')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_clinicas');
    }
};
