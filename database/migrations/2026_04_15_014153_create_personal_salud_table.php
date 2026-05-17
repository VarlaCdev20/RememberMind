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
        Schema::create('personal_salud', function (Blueprint $table) {
            $table->increments('cod_per_sal');

            $table->date('fecha_ing');
            $table->integer('anios_exp')->nullable();
            $table->string('matricula_prof', 50)->nullable();
            $table->string('estado_laboral', 100);
            $table->text('observaciones')->nullable();

            $table->string('cod_usu', 20);
            $table->unsignedInteger('cod_esp')->nullable();

            $table->foreign('cod_usu')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_esp')
                ->references('cod_esp')
                ->on('especialidades')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_salud', function (Blueprint $table) {
            $table->dropForeign(['cod_usu']);
            $table->dropForeign(['cod_esp']);
        });

        Schema::dropIfExists('personal_salud');
    }
};