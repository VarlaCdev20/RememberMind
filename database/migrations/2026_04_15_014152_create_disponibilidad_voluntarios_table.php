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
        Schema::create('disponibilidad_voluntarios', function (Blueprint $table) {
            $table->increments('cod_hor_vol');

            $table->string('dia_semana', 100);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
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
        Schema::table('disponibilidad_voluntarios', function (Blueprint $table) {
            $table->dropForeign(['cod_vol']);
        });

        Schema::dropIfExists('disponibilidad_voluntarios');
    }
};