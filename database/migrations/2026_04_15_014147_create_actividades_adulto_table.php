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
        Schema::create('actividades_adulto', function (Blueprint $table) {
            $table->increments('cod_act_adul');

            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->text('obs')->nullable();
            $table->string('estado', 100);

            $table->unsignedInteger('cod_tipo_act');
            $table->unsignedInteger('cod_am');

            $table->foreign('cod_tipo_act')
                ->references('cod_tipo_act')
                ->on('tipo_actividades_adulto')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividades_adulto', function (Blueprint $table) {
            $table->dropForeign(['cod_tipo_act']);
            $table->dropForeign(['cod_am']);
        });

        Schema::dropIfExists('actividades_adulto');
    }
};