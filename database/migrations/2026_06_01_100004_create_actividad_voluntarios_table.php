<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('actividad_voluntarios')) {
            return;
        }

        Schema::create('actividad_voluntarios', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('cod_act_adul');
            $table->unsignedInteger('cod_vol');

            $table->string('rol_apoyo')->nullable();
            // Ej: Animador, Asistente, Coordinador

            $table->string('estado', 20)->default('ASIGNADO');
            // ASIGNADO | ASISTIO | FALTO | CANCELADO

            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cod_act_adul')
                ->references('cod_act_adul')
                ->on('actividades_adulto')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_vol')
                ->references('cod_vol')
                ->on('voluntarios')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Un voluntario no puede estar asignado dos veces a la misma actividad
            $table->unique(['cod_act_adul', 'cod_vol'], 'uq_voluntario_actividad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad_voluntarios');
    }
};
