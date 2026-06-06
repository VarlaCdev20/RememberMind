<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signos_vitales_adulto', function (Blueprint $table) {
            if (!Schema::hasColumn('signos_vitales_adulto', 'presion_sistolica')) {
                $table->integer('presion_sistolica')->nullable()->after('presion_arterial');
            }
            if (!Schema::hasColumn('signos_vitales_adulto', 'presion_diastolica')) {
                $table->integer('presion_diastolica')->nullable()->after('presion_sistolica');
            }
            if (!Schema::hasColumn('signos_vitales_adulto', 'frecuencia_respiratoria')) {
                $table->integer('frecuencia_respiratoria')->nullable()->after('frecuencia_cardiaca');
            }
            if (!Schema::hasColumn('signos_vitales_adulto', 'estado')) {
                $table->string('estado', 20)->default('VIGENTE')->after('observacion');
            }
            if (!Schema::hasColumn('signos_vitales_adulto', 'motivo_anulacion')) {
                $table->text('motivo_anulacion')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('signos_vitales_adulto', 'anulado_por')) {
                $table->string('anulado_por', 20)->nullable()->after('motivo_anulacion');
                $table->foreign('anulado_por')
                    ->references('cod_usu')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            }
            if (!Schema::hasColumn('signos_vitales_adulto', 'fecha_anulacion')) {
                $table->timestamp('fecha_anulacion')->nullable()->after('anulado_por');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signos_vitales_adulto', function (Blueprint $table) {
            if (Schema::hasColumn('signos_vitales_adulto', 'anulado_por')) {
                $table->dropForeign(['anulado_por']);
                $table->dropColumn('anulado_por');
            }
            foreach (['presion_sistolica', 'presion_diastolica', 'frecuencia_respiratoria',
                      'estado', 'motivo_anulacion', 'fecha_anulacion'] as $col) {
                if (Schema::hasColumn('signos_vitales_adulto', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
