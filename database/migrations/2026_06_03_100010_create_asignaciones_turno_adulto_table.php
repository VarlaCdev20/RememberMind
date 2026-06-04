<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asignaciones_turno_adulto')) {
            return;
        }

        Schema::create('asignaciones_turno_adulto', function (Blueprint $table) {
            $table->id('cod_asig_turno');

            $table->string('cod_am', 10);
            $table->unsignedInteger('cod_turno');             // FK turnos_enfermeria
            $table->string('cod_usu_enfermero', 20);         // FK users (enfermero asignado)
            $table->unsignedBigInteger('cod_habitacion')->nullable();
            $table->unsignedBigInteger('cod_cama')->nullable();

            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            $table->string('nivel_supervision', 30)->default('ESTANDAR');
            // MINIMO, ESTANDAR, INTENSIVO, CRITICO
            $table->string('estado', 20)->default('ACTIVA');
            // ACTIVA, FINALIZADA, REEMPLAZADA, ANULADA
            $table->text('motivo_asignacion');
            $table->string('asignado_por', 20)->nullable();   // FK users

            $table->timestamps();
            $table->softDeletes();

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

            $table->foreign('cod_usu_enfermero')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('cod_habitacion')
                ->references('cod_habitacion')
                ->on('habitaciones')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('cod_cama')
                ->references('cod_cama')
                ->on('camas')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('asignado_por')
                ->references('cod_usu')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['cod_am', 'estado']);
            $table->index(['cod_usu_enfermero', 'estado']);
            $table->index(['cod_turno', 'estado']);
            $table->index('fecha_inicio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_turno_adulto');
    }
};
