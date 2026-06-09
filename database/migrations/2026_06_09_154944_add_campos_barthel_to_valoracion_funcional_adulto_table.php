<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('valoracion_funcional_adulto', function (Blueprint $table) {
            if (! Schema::hasColumn('valoracion_funcional_adulto', 'riesgo_caida')) {
                $table->string('riesgo_caida', 30)->nullable()->after('nivel_dependencia');
            }

            if (! Schema::hasColumn('valoracion_funcional_adulto', 'indice_barthel')) {
                $table->unsignedSmallInteger('indice_barthel')->nullable()->after('riesgo_caida');
            }

            if (! Schema::hasColumn('valoracion_funcional_adulto', 'estado')) {
                $table->string('estado', 30)->default('VIGENTE')->after('indice_barthel');
            }

            if (! Schema::hasColumn('valoracion_funcional_adulto', 'motivo_anulacion')) {
                $table->text('motivo_anulacion')->nullable()->after('observacion');
            }

            if (! Schema::hasColumn('valoracion_funcional_adulto', 'anulado_por')) {
                $table->string('anulado_por', 20)->nullable()->after('motivo_anulacion');
            }

            if (! Schema::hasColumn('valoracion_funcional_adulto', 'fecha_anulacion')) {
                $table->timestamp('fecha_anulacion')->nullable()->after('anulado_por');
            }
        });
    }

    public function down(): void
    {
        Schema::table('valoracion_funcional_adulto', function (Blueprint $table) {
            if (Schema::hasColumn('valoracion_funcional_adulto', 'fecha_anulacion')) {
                $table->dropColumn('fecha_anulacion');
            }

            if (Schema::hasColumn('valoracion_funcional_adulto', 'anulado_por')) {
                $table->dropColumn('anulado_por');
            }

            if (Schema::hasColumn('valoracion_funcional_adulto', 'motivo_anulacion')) {
                $table->dropColumn('motivo_anulacion');
            }

            if (Schema::hasColumn('valoracion_funcional_adulto', 'estado')) {
                $table->dropColumn('estado');
            }

            if (Schema::hasColumn('valoracion_funcional_adulto', 'indice_barthel')) {
                $table->dropColumn('indice_barthel');
            }

            if (Schema::hasColumn('valoracion_funcional_adulto', 'riesgo_caida')) {
                $table->dropColumn('riesgo_caida');
            }
        });
    }
};