<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asistencia_voluntarios', function (Blueprint $table) {
            $table->increments('cod_asis_vol');

            $table->date('fecha');
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->string('estado', 100);
            $table->text('actividad_realizada')->nullable();
            $table->text('observaciones')->nullable();

            $table->unsignedInteger('cod_vol');

            $table->foreign('cod_vol')
                ->references('cod_vol')
                ->on('voluntarios')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencia_voluntarios', function (Blueprint $table) {
            $table->dropForeign(['cod_vol']);
        });

        Schema::dropIfExists('asistencia_voluntarios');
    }
};