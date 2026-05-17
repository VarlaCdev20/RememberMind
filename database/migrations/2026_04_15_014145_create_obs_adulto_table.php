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
        Schema::create('obs_adulto', function (Blueprint $table) {
            $table->increments('cod_obs_adul');

            $table->date('fecha');
            $table->string('tipo_obs', 100);
            $table->text('descripcion')->nullable();

            $table->unsignedInteger('cod_am');
            $table->unsignedInteger('cod_est_adul')->nullable();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_est_adul')
                ->references('cod_est_adul')
                ->on('estado_adulto')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obs_adulto', function (Blueprint $table) {
            $table->dropForeign(['cod_am']);
            $table->dropForeign(['cod_est_adul']);
        });

        Schema::dropIfExists('obs_adulto');
    }
};