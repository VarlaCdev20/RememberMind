<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tareas_plan_cuidado')) {
            return;
        }

        Schema::create('tareas_plan_cuidado', function (Blueprint $table) {
            $table->id('cod_tarea');

            $table->unsignedBigInteger('cod_plan');
            $table->string('cod_am', 10);
            $table->unsignedInteger('cod_turno');            // turno en que se realiza
            $table->string('responsable_id', 20)->nullable(); // FK users

            $table->string('area', 30);
            // SIGNOS, MEDICACION, MOVILIDAD, COGNITIVO, ALIMENTACION, HIDRATACION,
            // HIGIENE, SUEÑO, SEGURIDAD, EMOCIONAL, FAMILIAR, REEVALUACION

            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->string('frecuencia', 50)->nullable();    // DIARIA, CADA_8H, SEMANAL, etc.
            $table->date('fecha_programada');
            $table->time('hora_programada')->nullable();
            $table->string('prioridad', 20)->default('NORMAL');
            // BAJA, NORMAL, ALTA, URGENTE
            $table->string('estado', 20)->default('PENDIENTE');
            // PENDIENTE, EN_PROCESO, REALIZADA, OMITIDA, REPROGRAMADA, VENCIDA, TRANSFERIDA, ANULADA

            $table->timestamp('fecha_realizada')->nullable();
            $table->text('resultado')->nullable();
            $table->text('observacion')->nullable();
            $table->text('motivo_omision')->nullable();
            $table->unsignedInteger('transferida_a_turno_id')->nullable(); // FK turnos_enfermeria
            $table->string('registrado_por', 20)->nullable(); // FK users

            $table->timestamps();

            $table->foreign('cod_plan')
                ->references('cod_plan')
                ->on('planes_cuidado')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('cod_turno')
                ->references('cod_turno')
                ->on('turnos_enfermeria')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('responsable_id')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('transferida_a_turno_id')
                ->references('cod_turno')
                ->on('turnos_enfermeria')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('registrado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['cod_am', 'fecha_programada']);
            $table->index(['cod_am', 'estado']);
            $table->index(['cod_turno', 'fecha_programada']);
            $table->index(['area', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas_plan_cuidado');
    }
};
