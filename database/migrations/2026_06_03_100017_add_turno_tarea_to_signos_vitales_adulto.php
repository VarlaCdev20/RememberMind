<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signos_vitales_adulto', function (Blueprint $table) {
            if (! Schema::hasColumn('signos_vitales_adulto', 'intensidad_dolor')) {
                $table->unsignedTinyInteger('intensidad_dolor')->nullable()->after('dolor');
                // Escala 0-10 numérica (dolor es texto descriptivo existente)
            }
            if (! Schema::hasColumn('signos_vitales_adulto', 'cod_turno')) {
                $table->unsignedInteger('cod_turno')->nullable()->after('registrado_por');
                $table->foreign('cod_turno')
                    ->references('cod_turno')
                    ->on('turnos_enfermeria')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
            if (! Schema::hasColumn('signos_vitales_adulto', 'cod_tarea')) {
                $table->unsignedBigInteger('cod_tarea')->nullable()->after('cod_turno');
                $table->foreign('cod_tarea')
                    ->references('cod_tarea')
                    ->on('tareas_plan_cuidado')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signos_vitales_adulto', function (Blueprint $table) {
            if (Schema::hasColumn('signos_vitales_adulto', 'cod_tarea')) {
                $table->dropForeign(['cod_tarea']);
                $table->dropColumn('cod_tarea');
            }
            if (Schema::hasColumn('signos_vitales_adulto', 'cod_turno')) {
                $table->dropForeign(['cod_turno']);
                $table->dropColumn('cod_turno');
            }
            $table->dropColumnIfExists('intensidad_dolor');
        });
    }
};
