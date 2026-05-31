<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('valoracion_funcional_adulto', function (Blueprint $table) {
            // Estado del registro: distingue la valoración vigente de históricas y anuladas.
            // Sin este campo no hay forma de saber cuál es la evaluación funcional actual del paciente.
            $table->string('estado', 20)->default('VIGENTE')->after('nivel_dependencia');
            // Valores: VIGENTE | HISTORICA | ANULADA

            // Riesgo de caída: resultado clínico que combina autonomía, dispositivos y sensoriales.
            // No es calculable automáticamente; lo determina el profesional evaluador.
            $table->string('riesgo_caida', 20)->nullable()->after('estado');
            // Valores: BAJO | MEDIO | ALTO

            // Índice de Barthel (0–100): escala estándar de independencia en AVD.
            // Cada ítem tiene peso diferente; se registra el puntaje final del evaluador.
            $table->unsignedSmallInteger('indice_barthel')->nullable()->after('riesgo_caida');

            // Control de anulación no destructiva (patrón del proyecto: evaluaciones_geriatricas)
            $table->text('motivo_anulacion')->nullable()->after('observacion');
            $table->string('anulado_por', 20)->nullable()->after('motivo_anulacion');
            $table->foreign('anulado_por')
                ->references('cod_usu')->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->timestamp('fecha_anulacion')->nullable()->after('anulado_por');

            // Índice para filtrar por adulto + estado sin full scan
            $table->index(['cod_am', 'estado'], 'idx_valfunc_am_estado');
        });
    }

    public function down(): void
    {
        Schema::table('valoracion_funcional_adulto', function (Blueprint $table) {
            $table->dropIndex('idx_valfunc_am_estado');
            $table->dropForeign(['anulado_por']);
            $table->dropColumn([
                'estado', 'riesgo_caida', 'indice_barthel',
                'motivo_anulacion', 'anulado_por', 'fecha_anulacion',
            ]);
        });
    }
};
