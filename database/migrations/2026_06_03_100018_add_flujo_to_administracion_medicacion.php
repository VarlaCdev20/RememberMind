<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('administracion_medicacion', function (Blueprint $table) {
            if (! Schema::hasColumn('administracion_medicacion', 'cod_turno')) {
                $table->unsignedInteger('cod_turno')->nullable()->after('registrado_por');
                $table->foreign('cod_turno')
                    ->references('cod_turno')
                    ->on('turnos_enfermeria')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
            if (! Schema::hasColumn('administracion_medicacion', 'cod_tarea')) {
                $table->unsignedBigInteger('cod_tarea')->nullable()->after('cod_turno');
                $table->foreign('cod_tarea')
                    ->references('cod_tarea')
                    ->on('tareas_plan_cuidado')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
            if (! Schema::hasColumn('administracion_medicacion', 'estado')) {
                $table->string('estado', 20)->default('PENDIENTE')->after('observacion');
                // PENDIENTE, ADMINISTRADA, OMITIDA, REPROGRAMADA, RECHAZADA, SUSPENDIDA
            }
            if (! Schema::hasColumn('administracion_medicacion', 'reaccion_adversa')) {
                $table->boolean('reaccion_adversa')->default(false)->after('estado');
            }
            if (! Schema::hasColumn('administracion_medicacion', 'descripcion_reaccion')) {
                $table->text('descripcion_reaccion')->nullable()->after('reaccion_adversa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('administracion_medicacion', function (Blueprint $table) {
            $table->dropColumnIfExists('descripcion_reaccion');
            $table->dropColumnIfExists('reaccion_adversa');
            $table->dropColumnIfExists('estado');
            if (Schema::hasColumn('administracion_medicacion', 'cod_tarea')) {
                $table->dropForeign(['cod_tarea']);
                $table->dropColumn('cod_tarea');
            }
            if (Schema::hasColumn('administracion_medicacion', 'cod_turno')) {
                $table->dropForeign(['cod_turno']);
                $table->dropColumn('cod_turno');
            }
        });
    }
};
