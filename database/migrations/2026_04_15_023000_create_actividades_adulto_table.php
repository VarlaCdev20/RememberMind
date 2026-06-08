<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades_adulto', function (Blueprint $table) {
            $table->string('cod_act_adul', 20)->primary();
            $table->string('cod_am', 20);
            $table->string('cod_tipo_act', 20);
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->text('observacion')->nullable();
            $table->string('estado', 50)->default('COMPLETADA');
            $table->string('registrado_por', 20)->nullable();
            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_tipo_act')
                ->references('cod_tipo_act')
                ->on('tipo_actividades_adulto')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('registrado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades_adulto');
    }
};