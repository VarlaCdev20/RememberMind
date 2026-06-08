<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obs_adulto', function (Blueprint $table) {
            $table->string('cod_obs_adul', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('cod_est_adul', 20)->nullable();
            $table->string('creado_por', 20);
            $table->text('observacion');
            $table->date('fecha');
            $table->string('categoria', 100)->default('GENERAL');
            $table->string('nivel_riesgo', 50)->default('BAJO');
            $table->string('estado', 50)->default('ACTIVO');
            $table->timestamps();

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

            $table->foreign('creado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obs_adulto');
    }
};