<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_voluntarios', function (Blueprint $table) {
            $table->string('cod_asis_vol', 20)->primary();
            $table->string('cod_vol', 20);
            $table->string('cod_am', 20)->nullable();
            $table->date('fecha');
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->text('actividad_realizada')->nullable();
            $table->text('novedades_observaciones')->nullable();
            $table->string('estado', 50)->default('PRESENTE');
            $table->timestamps();

            $table->foreign('cod_vol')
                ->references('cod_vol')
                ->on('voluntarios')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_voluntarios');
    }
};