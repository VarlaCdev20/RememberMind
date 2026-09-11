<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE adulto_mayor ADD CONSTRAINT ck_adulto_estado_operativo CHECK (estado_operativo IN ('EN_CENTRO','SALIDA_TEMPORAL','HOSPITALIZADO','EGRESADO','FALLECIDO'))");
        DB::statement("ALTER TABLE medicacion_adulto ADD CONSTRAINT ck_medicacion_prn_condicion CHECK (NOT es_prn OR (condicion_prn IS NOT NULL AND btrim(condicion_prn) <> ''))");
        DB::statement('ALTER TABLE medicacion_adulto ADD CONSTRAINT ck_medicacion_intervalo CHECK (intervalo_horas IS NULL OR intervalo_horas BETWEEN 1 AND 24)');
        DB::statement('ALTER TABLE administracion_medicacion ADD CONSTRAINT ck_administracion_intensidad CHECK (intensidad_previa IS NULL OR intensidad_previa BETWEEN 0 AND 10)');
        DB::statement('ALTER TABLE administracion_medicacion ADD CONSTRAINT ck_administracion_hora_real CHECK (NOT administrado OR hora_real IS NOT NULL)');
        DB::statement('ALTER TABLE registros_cuidados ADD CONSTRAINT ck_cuidado_porcentaje CHECK (porcentaje IS NULL OR porcentaje IN (0,25,50,75,100))');
        DB::statement('ALTER TABLE registros_cuidados ADD CONSTRAINT ck_cuidado_cantidad CHECK (cantidad_ml IS NULL OR cantidad_ml > 0)');
        DB::statement('ALTER TABLE registros_cuidados ADD CONSTRAINT ck_cuidado_dolor CHECK (dolor IS NULL OR dolor BETWEEN 0 AND 10)');
        DB::statement("ALTER TABLE registros_cuidados ADD CONSTRAINT ck_cuidado_estado CHECK (estado IN ('FIRMADO','RECTIFICADO','ANULADO'))");
        DB::statement('ALTER TABLE incidentes_residente ADD CONSTRAINT ck_incidente_dolor CHECK (dolor IS NULL OR dolor BETWEEN 0 AND 10)');
        DB::statement("ALTER TABLE incidentes_residente ADD CONSTRAINT ck_incidente_estado CHECK (estado IN ('ABIERTO','EN_SEGUIMIENTO','CERRADO','ANULADO'))");
        DB::statement("ALTER TABLE lesiones_residente ADD CONSTRAINT ck_lesion_estado CHECK (estado IN ('ACTIVA','CERRADA','ANULADA'))");
        DB::statement('ALTER TABLE seguimientos_lesion ADD CONSTRAINT ck_seguimiento_lesion_dolor CHECK (dolor IS NULL OR dolor BETWEEN 0 AND 10)');
        DB::statement('ALTER TABLE seguimientos_lesion ADD CONSTRAINT ck_seguimiento_lesion_dimensiones CHECK ((largo_cm IS NULL OR largo_cm >= 0) AND (ancho_cm IS NULL OR ancho_cm >= 0) AND (profundidad_cm IS NULL OR profundidad_cm >= 0))');
        DB::statement('CREATE UNIQUE INDEX uq_recepcion_turno_usuario_dia ON recepciones_turno (cod_turno, cod_usuario, (fecha_hora_recepcion::date))');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS uq_recepcion_turno_usuario_dia');
        foreach ([
            'seguimientos_lesion' => ['ck_seguimiento_lesion_dolor', 'ck_seguimiento_lesion_dimensiones'],
            'lesiones_residente' => ['ck_lesion_estado'],
            'incidentes_residente' => ['ck_incidente_dolor', 'ck_incidente_estado'],
            'registros_cuidados' => ['ck_cuidado_porcentaje', 'ck_cuidado_cantidad', 'ck_cuidado_dolor', 'ck_cuidado_estado'],
            'administracion_medicacion' => ['ck_administracion_intensidad', 'ck_administracion_hora_real'],
            'medicacion_adulto' => ['ck_medicacion_prn_condicion', 'ck_medicacion_intervalo'],
            'adulto_mayor' => ['ck_adulto_estado_operativo'],
        ] as $tabla => $restricciones) {
            foreach ($restricciones as $restriccion) {
                DB::statement("ALTER TABLE {$tabla} DROP CONSTRAINT IF EXISTS {$restriccion}");
            }
        }
    }
};
