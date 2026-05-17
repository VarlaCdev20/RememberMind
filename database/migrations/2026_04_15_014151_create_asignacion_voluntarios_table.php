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
        Schema::create('asignacion_voluntarios', function (Blueprint $table) {
            $table->increments('cod_asig_vol');

            $table->date('fecha_asig');
            $table->date('fecha_fin')->nullable();
            $table->string('estado', 100);
            $table->text('obser')->nullable();

            $table->unsignedInteger('cod_am');
            $table->unsignedInteger('cod_vol');

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
        Schema::table('asignacion_voluntarios', function (Blueprint $table) {
            $table->dropForeign(['cod_am']);
            $table->dropForeign(['cod_vol']);
        });

        Schema::dropIfExists('asignacion_voluntarios');
    }
};