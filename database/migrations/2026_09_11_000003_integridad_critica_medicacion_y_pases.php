<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') return;

        // Conserva el hecho histórico sin inventar una causa clínica que no fue documentada.
        DB::table('administracion_medicacion')
            ->where('administrado', false)
            ->where(fn ($q) => $q->whereNull('motivo_omision')->orWhere('motivo_omision', ''))
            ->update(['motivo_omision' => 'Registro legado: motivo no documentado en el sistema anterior.']);

        DB::statement("ALTER TABLE medicacion_adulto ADD CONSTRAINT ck_medicacion_prn_intervalo_requerido CHECK (NOT es_prn OR intervalo_horas IS NOT NULL)");
        DB::statement("ALTER TABLE medicacion_adulto ADD CONSTRAINT ck_medicacion_orden_completa CHECK (estado NOT IN ('ACTIVO','ACTIVA','VIGENTE') OR (dosis IS NOT NULL AND btrim(dosis) <> '' AND via_administracion IS NOT NULL AND btrim(via_administracion) <> ''))");
        DB::statement("ALTER TABLE administracion_medicacion ADD CONSTRAINT ck_administracion_omision_motivo CHECK (administrado OR (motivo_omision IS NOT NULL AND btrim(motivo_omision) <> ''))");
        DB::statement("ALTER TABLE administracion_medicacion ADD CONSTRAINT ck_administracion_prn_datos CHECK (motivo_prn IS NULL OR (btrim(motivo_prn) <> '' AND valoracion_previa IS NOT NULL AND btrim(valoracion_previa) <> '' AND intensidad_previa IS NOT NULL AND requiere_reevaluacion))");
        DB::statement("CREATE UNIQUE INDEX uq_pase_residente_turno_fecha ON pases_turno (cod_am, turno_saliente_id, fecha)");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') return;
        DB::statement('DROP INDEX IF EXISTS uq_pase_residente_turno_fecha');
        foreach (['ck_administracion_prn_datos', 'ck_administracion_omision_motivo'] as $constraint) {
            DB::statement("ALTER TABLE administracion_medicacion DROP CONSTRAINT IF EXISTS {$constraint}");
        }
        foreach (['ck_medicacion_orden_completa', 'ck_medicacion_prn_intervalo_requerido'] as $constraint) {
            DB::statement("ALTER TABLE medicacion_adulto DROP CONSTRAINT IF EXISTS {$constraint}");
        }
    }
};
