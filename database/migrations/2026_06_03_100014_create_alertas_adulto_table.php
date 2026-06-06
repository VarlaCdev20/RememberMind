<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('alertas_adulto')) {
            return;
        }

        Schema::create('alertas_adulto', function (Blueprint $table) {
            $table->id('cod_alerta');

            $table->string('cod_am', 10);
            $table->unsignedInteger('cod_turno')->nullable();

            $table->string('origen', 30);
            // SIGNOS, MEDICACION, SEGUIMIENTO, PLAN, INCIDENTE, SOLICITUD_MEDICA, MANUAL

            $table->string('tipo_alerta', 50);
            // Libre: PRESION_ALTA, GLUCOSA_ALTA, TAREA_OMITIDA, REACCION_ADVERSA,
            //        CAIDA, SOLICITUD_MEDICA, COMPORTAMIENTO, etc.

            $table->string('nivel', 20)->default('MEDIO');
            // BAJO, MEDIO, ALTO, CRITICO

            $table->text('motivo');
            $table->string('responsable_id', 20)->nullable(); // FK users (responsable asignado)
            $table->string('estado', 20)->default('ABIERTA');
            // ABIERTA, EN_ATENCION, CERRADA, ANULADA

            $table->text('accion_tomada')->nullable();
            $table->timestamp('fecha_atencion')->nullable();
            $table->string('atendido_por', 20)->nullable();   // FK users

            $table->timestamp('fecha_cierre')->nullable();
            $table->string('cerrado_por', 20)->nullable();    // FK users
            $table->text('observacion_cierre')->nullable();

            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('cod_turno')
                ->references('cod_turno')
                ->on('turnos_enfermeria')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('responsable_id')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('atendido_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('cerrado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['cod_am', 'estado']);
            $table->index(['nivel', 'estado']);
            $table->index(['origen', 'estado']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_adulto');
    }
};
