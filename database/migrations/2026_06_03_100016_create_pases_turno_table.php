<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pases_turno')) {
            return;
        }

        Schema::create('pases_turno', function (Blueprint $table) {
            $table->id('cod_pase');

            $table->string('cod_am', 10);
            $table->unsignedInteger('turno_saliente_id');
            $table->unsignedInteger('turno_entrante_id');
            $table->string('enfermero_saliente_id', 20)->nullable();
            $table->string('enfermero_entrante_id', 20)->nullable();

            $table->date('fecha');
            $table->string('estado_general_cierre', 30)->nullable();
            // ESTABLE, DETERIORO, MEJORADO, CRITICO
            $table->text('resumen_turno');

            $table->json('tareas_realizadas_json')->nullable();
            $table->json('tareas_pendientes_json')->nullable();
            $table->json('alertas_activas_json')->nullable();

            $table->text('recomendacion_siguiente_turno')->nullable();
            $table->boolean('requiere_vigilancia_especial')->default(false);
            $table->text('motivo_vigilancia')->nullable();

            $table->string('estado', 20)->default('GENERADO');
            // GENERADO, RECIBIDO, OBSERVADO, ANULADO
            $table->timestamp('fecha_recibido')->nullable();

            $table->timestamps();

            $table->foreign('cod_am')
                ->references('cod_am')
                ->on('adulto_mayor')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('turno_saliente_id')
                ->references('cod_turno')
                ->on('turnos_enfermeria')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('turno_entrante_id')
                ->references('cod_turno')
                ->on('turnos_enfermeria')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('enfermero_saliente_id')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('enfermero_entrante_id')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['cod_am', 'fecha']);
            $table->index(['cod_am', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pases_turno');
    }
};
