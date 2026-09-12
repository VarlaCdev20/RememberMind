<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signos_vitales_adulto', function (Blueprint $table) {
            $table->string('rectifica_a', 20)->nullable();
            $table->text('motivo_rectificacion')->nullable();
            $table->foreign('rectifica_a')->references('cod_signo')->on('signos_vitales_adulto')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table('registros_cuidados', function (Blueprint $table) {
            $table->string('relacionado_a', 20)->nullable();
            $table->string('fase_dolor', 20)->nullable();
            $table->foreign('relacionado_a')->references('cod_registro_cuidado')->on('registros_cuidados')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table('incidentes_residente', function (Blueprint $table) {
            $table->string('responsable_id', 20)->nullable();
            $table->text('seguimiento')->nullable();
            $table->timestamp('fecha_seguimiento')->nullable();
            $table->text('evaluacion_final')->nullable();
            $table->text('resultado_cierre')->nullable();
            $table->timestamp('fecha_cierre')->nullable();
            $table->string('cerrado_por', 20)->nullable();
            $table->foreign('responsable_id')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('cerrado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table('lesiones_residente', function (Blueprint $table) {
            $table->text('resultado_cierre')->nullable();
            $table->text('motivo_cierre')->nullable();
            $table->string('cerrado_por', 20)->nullable();
            $table->foreign('cerrado_por')->references('cod_usu')->on('users')->cascadeOnUpdate()->nullOnDelete();
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE signos_vitales_adulto ADD CONSTRAINT ck_signos_rectificacion CHECK ((rectifica_a IS NULL AND motivo_rectificacion IS NULL) OR (rectifica_a IS NOT NULL AND motivo_rectificacion IS NOT NULL AND btrim(motivo_rectificacion) <> ''))");
            DB::statement("ALTER TABLE registros_cuidados ADD CONSTRAINT ck_cuidado_fase_dolor CHECK (fase_dolor IS NULL OR fase_dolor IN ('VALORACION','INTERVENCION','REEVALUACION'))");
            DB::statement("ALTER TABLE incidentes_residente ADD CONSTRAINT ck_incidente_cierre_completo CHECK (estado <> 'CERRADO' OR (evaluacion_final IS NOT NULL AND btrim(evaluacion_final) <> '' AND resultado_cierre IS NOT NULL AND btrim(resultado_cierre) <> '' AND fecha_cierre IS NOT NULL AND cerrado_por IS NOT NULL))");
            DB::statement("ALTER TABLE lesiones_residente ADD CONSTRAINT ck_lesion_cierre_completo CHECK (estado <> 'CERRADA' OR (resultado_cierre IS NOT NULL AND btrim(resultado_cierre) <> '' AND motivo_cierre IS NOT NULL AND btrim(motivo_cierre) <> '' AND fecha_cierre IS NOT NULL AND cerrado_por IS NOT NULL))");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE lesiones_residente DROP CONSTRAINT IF EXISTS ck_lesion_cierre_completo');
            DB::statement('ALTER TABLE incidentes_residente DROP CONSTRAINT IF EXISTS ck_incidente_cierre_completo');
            DB::statement('ALTER TABLE registros_cuidados DROP CONSTRAINT IF EXISTS ck_cuidado_fase_dolor');
            DB::statement('ALTER TABLE signos_vitales_adulto DROP CONSTRAINT IF EXISTS ck_signos_rectificacion');
        }
        Schema::table('lesiones_residente', function (Blueprint $table) {
            $table->dropForeign(['cerrado_por']);
            $table->dropColumn(['resultado_cierre', 'motivo_cierre', 'cerrado_por']);
        });
        Schema::table('incidentes_residente', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            $table->dropForeign(['cerrado_por']);
            $table->dropColumn(['responsable_id', 'seguimiento', 'fecha_seguimiento', 'evaluacion_final', 'resultado_cierre', 'fecha_cierre', 'cerrado_por']);
        });
        Schema::table('registros_cuidados', function (Blueprint $table) {
            $table->dropForeign(['relacionado_a']);
            $table->dropColumn(['relacionado_a', 'fase_dolor']);
        });
        Schema::table('signos_vitales_adulto', function (Blueprint $table) {
            $table->dropForeign(['rectifica_a']);
            $table->dropColumn(['rectifica_a', 'motivo_rectificacion']);
        });
    }
};
