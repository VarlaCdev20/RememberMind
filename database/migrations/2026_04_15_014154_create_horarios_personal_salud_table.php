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
        Schema::create('horarios_personal_salud', function (Blueprint $table) {
            $table->increments('cod_hor_per_sal');

            $table->string('dia_semana', 100);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->string('turno', 100);
            $table->string('estado', 100);
            $table->text('observaciones')->nullable();

            $table->unsignedInteger('cod_per_sal');

            $table->foreign('cod_per_sal')
                ->references('cod_per_sal')
                ->on('personal_salud')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horarios_personal_salud', function (Blueprint $table) {
            $table->dropForeign(['cod_per_sal']);
        });

        Schema::dropIfExists('horarios_personal_salud');
    }
};