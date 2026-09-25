<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $atencionChildren = [
        'aplicaciones_instrumento' => 'fk_aplicacion_atencion_residente',
        'controles_cognitivos' => 'fk_control_atencion_residente',
        'derivaciones' => 'fk_derivacion_atencion_residente',
        'diagnosticos' => 'fk_diagnostico_atencion_residente',
        'documentos_clinicos' => 'fk_documento_clinico_atencion_residente',
        'estudios_clinicos' => 'fk_estudio_atencion_residente',
        'indicaciones_clinicas' => 'fk_indicacion_atencion_residente',
        'notas_clinicas' => 'fk_nota_atencion_residente',
        'prescripciones' => 'fk_prescripcion_atencion_residente',
        'registros_conductuales' => 'fk_conductual_atencion_residente',
        'registros_movilidad' => 'fk_movilidad_atencion_residente',
        'signos_vitales' => 'fk_signo_atencion_residente',
        'valoraciones_dolor' => 'fk_dolor_atencion_residente',
        'valoraciones_funcionales' => 'fk_funcional_atencion_residente',
        'valoraciones_nutricionales' => 'fk_nutricional_atencion_residente',
        'valoraciones_psicologicas' => 'fk_psicologica_atencion_residente',
    ];

    /** @var array<string, string> */
    private array $checks = [
        'ocupaciones_cama.ck_ocupacion_fechas' => '(fecha_hora_liberacion IS NULL OR fecha_hora_liberacion >= fecha_hora_asignacion)',
        'prescripciones.ck_prescripcion_dosis' => '(dosis IS NULL OR dosis > 0)',
        'prescripciones.ck_prescripcion_fechas' => '(fecha_hora_suspension IS NULL OR fecha_hora_suspension >= fecha_hora_prescripcion)',
        'horarios_prescripcion.ck_horario_dosis' => '(dosis_programada IS NULL OR dosis_programada > 0)',
        'administraciones_medicacion.ck_administracion_dosis' => '(dosis_administrada IS NULL OR dosis_administrada > 0)',
        'aplicaciones_instrumento.ck_aplicacion_puntajes' => '(puntaje_total IS NULL OR puntaje_total >= 0) AND (puntaje_maximo IS NULL OR puntaje_maximo >= 0) AND (puntaje_total IS NULL OR puntaje_maximo IS NULL OR puntaje_total <= puntaje_maximo)',
        'preadmisiones.ck_preadmision_fechas' => '(fecha_revision IS NULL OR fecha_revision >= fecha_solicitud)',
        'planes_cuidado.ck_plan_fechas' => '(fecha_hora_cierre IS NULL OR fecha_hora_cierre >= fecha_hora_apertura)',
        'valoraciones_enfermeria_preadmision.ck_val_enf_dolor' => '(intensidad_dolor IS NULL OR intensidad_dolor BETWEEN 0 AND 10) AND (NOT hay_dolor OR intensidad_dolor BETWEEN 1 AND 10)',
        'valoraciones_enfermeria_preadmision.ck_val_enf_pa' => '(pa_sistolica IS NULL OR pa_sistolica BETWEEN 1 AND 400) AND (pa_diastolica IS NULL OR pa_diastolica BETWEEN 1 AND 400) AND (pa_sistolica IS NULL OR pa_diastolica IS NULL OR pa_sistolica > pa_diastolica)',
        'valoraciones_enfermeria_preadmision.ck_val_enf_fc' => '(frecuencia_cardiaca IS NULL OR frecuencia_cardiaca BETWEEN 1 AND 300)',
        'valoraciones_enfermeria_preadmision.ck_val_enf_fr' => '(frecuencia_respiratoria IS NULL OR frecuencia_respiratoria BETWEEN 1 AND 100)',
        'valoraciones_enfermeria_preadmision.ck_val_enf_temperatura' => '(temperatura IS NULL OR temperatura BETWEEN 25 AND 45)',
        'valoraciones_enfermeria_preadmision.ck_val_enf_saturacion' => '(saturacion_oxigeno IS NULL OR saturacion_oxigeno BETWEEN 0 AND 100)',
        'valoraciones_enfermeria_preadmision.ck_val_enf_peso' => '(peso IS NULL OR peso BETWEEN 20 AND 300)',
        'valoraciones_enfermeria_preadmision.ck_val_enf_talla' => '(talla IS NULL OR talla BETWEEN 0.5 AND 240)',
    ];

    public function up(): void
    {
        $this->assertCurrentDataIsConsistent();
        $this->createBusinessGrainIndexes();

        if (DB::getDriverName() === 'pgsql') {
            $this->createPostgresForeignKeys();
            $this->createPostgresExecutionTrigger();
            $this->createPostgresChecks();

            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            $this->createSqliteConsistencyTriggers();
            $this->createSqliteCheckTriggers();
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            foreach ($this->atencionChildren as $table => $constraint) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraint}");
            }
            DB::statement('ALTER TABLE administraciones_medicacion DROP CONSTRAINT IF EXISTS fk_administracion_prescripcion_residente');
            DB::statement('DROP TRIGGER IF EXISTS trg_ejecucion_residente_plan ON ejecuciones_cuidado');
            DB::statement('DROP FUNCTION IF EXISTS validar_ejecucion_residente_plan()');

            foreach (array_keys($this->checks) as $qualifiedName) {
                [$table, $constraint] = explode('.', $qualifiedName, 2);
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraint}");
            }
        }

        if (DB::getDriverName() === 'sqlite') {
            foreach (array_keys($this->atencionChildren) as $table) {
                DB::statement("DROP TRIGGER IF EXISTS trg_{$table}_atencion_residente_ins");
                DB::statement("DROP TRIGGER IF EXISTS trg_{$table}_atencion_residente_upd");
            }
            DB::statement('DROP TRIGGER IF EXISTS trg_administracion_prescripcion_residente_ins');
            DB::statement('DROP TRIGGER IF EXISTS trg_administracion_prescripcion_residente_upd');
            DB::statement('DROP TRIGGER IF EXISTS trg_ejecucion_residente_plan_ins');
            DB::statement('DROP TRIGGER IF EXISTS trg_ejecucion_residente_plan_upd');

            foreach (array_keys($this->checks) as $qualifiedName) {
                [$table, $constraint] = explode('.', $qualifiedName, 2);
                DB::statement("DROP TRIGGER IF EXISTS trg_{$constraint}_ins");
                DB::statement("DROP TRIGGER IF EXISTS trg_{$constraint}_upd");
            }
        }

        foreach ([
            'uq_ocupacion_residente_activa',
            'uq_ocupacion_cama_activa',
            'uq_asignacion_personal_activa',
            'uq_asignacion_residente_activa',
            'uq_horario_prescripcion_activo',
            'uq_administracion_programada_dia',
            'uq_atencion_residente_par',
            'uq_prescripcion_residente_par',
        ] as $index) {
            DB::statement("DROP INDEX IF EXISTS {$index}");
        }
    }

    private function assertCurrentDataIsConsistent(): void
    {
        foreach (array_keys($this->atencionChildren) as $table) {
            $mismatches = DB::table("{$table} as hijo")
                ->join('atenciones as atencion', 'atencion.cod_atencion', '=', 'hijo.cod_atencion')
                ->whereColumn('hijo.cod_residente', '!=', 'atencion.cod_residente')
                ->count();

            if ($mismatches > 0) {
                throw new RuntimeException("{$table} contiene {$mismatches} relaciones atención/residente inconsistentes.");
            }
        }

        $administracionesInvalidas = DB::table('administraciones_medicacion as administracion')
            ->join('prescripciones as prescripcion', 'prescripcion.cod_prescripcion', '=', 'administracion.cod_prescripcion')
            ->whereColumn('administracion.cod_residente', '!=', 'prescripcion.cod_residente')
            ->count();
        if ($administracionesInvalidas > 0) {
            throw new RuntimeException('Existen administraciones de medicación asociadas a otro residente.');
        }

        $ejecucionesInvalidas = DB::table('ejecuciones_cuidado as ejecucion')
            ->join('intervenciones_cuidado as intervencion', 'intervencion.cod_intervencion', '=', 'ejecucion.cod_intervencion')
            ->join('planes_cuidado as plan', 'plan.cod_plan', '=', 'intervencion.cod_plan')
            ->whereColumn('ejecucion.cod_residente', '!=', 'plan.cod_residente')
            ->count();
        if ($ejecucionesInvalidas > 0) {
            throw new RuntimeException('Existen ejecuciones de cuidado asociadas a otro residente.');
        }
    }

    private function createBusinessGrainIndexes(): void
    {
        DB::statement("CREATE UNIQUE INDEX uq_ocupacion_residente_activa ON ocupaciones_cama (cod_residente) WHERE estado IN ('ACTIVA', 'ACTIVO')");
        DB::statement("CREATE UNIQUE INDEX uq_ocupacion_cama_activa ON ocupaciones_cama (cod_cama) WHERE estado IN ('ACTIVA', 'ACTIVO')");
        DB::statement("CREATE UNIQUE INDEX uq_asignacion_personal_activa ON asignaciones_personal (cod_jornada, cod_personal, cod_area) WHERE estado IN ('ACTIVA', 'ACTIVO')");
        DB::statement("CREATE UNIQUE INDEX uq_asignacion_residente_activa ON asignaciones_residente_jornada (cod_residente, cod_jornada, cod_personal) WHERE estado IN ('ACTIVA', 'ACTIVO')");
        DB::statement("CREATE UNIQUE INDEX uq_horario_prescripcion_activo ON horarios_prescripcion (cod_prescripcion, hora_programada, COALESCE(dias_semana, '')) WHERE estado IN ('ACTIVA', 'ACTIVO')");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX uq_administracion_programada_dia ON administraciones_medicacion (cod_prescripcion, cod_horario_prescripcion, (CAST(fecha_hora_programada AS date))) WHERE cod_horario_prescripcion IS NOT NULL AND fecha_hora_programada IS NOT NULL');
        } else {
            DB::statement('CREATE UNIQUE INDEX uq_administracion_programada_dia ON administraciones_medicacion (cod_prescripcion, cod_horario_prescripcion, date(fecha_hora_programada)) WHERE cod_horario_prescripcion IS NOT NULL AND fecha_hora_programada IS NOT NULL');
        }

        DB::statement('CREATE UNIQUE INDEX uq_atencion_residente_par ON atenciones (cod_atencion, cod_residente)');
        DB::statement('CREATE UNIQUE INDEX uq_prescripcion_residente_par ON prescripciones (cod_prescripcion, cod_residente)');
    }

    private function createPostgresForeignKeys(): void
    {
        foreach ($this->atencionChildren as $table => $constraint) {
            DB::statement(
                "ALTER TABLE {$table} ADD CONSTRAINT {$constraint} "
                .'FOREIGN KEY (cod_atencion, cod_residente) '
                .'REFERENCES atenciones (cod_atencion, cod_residente) '
                .'ON DELETE RESTRICT NOT VALID',
            );
            DB::statement("ALTER TABLE {$table} VALIDATE CONSTRAINT {$constraint}");
        }

        DB::statement(
            'ALTER TABLE administraciones_medicacion '
            .'ADD CONSTRAINT fk_administracion_prescripcion_residente '
            .'FOREIGN KEY (cod_prescripcion, cod_residente) '
            .'REFERENCES prescripciones (cod_prescripcion, cod_residente) '
            .'ON DELETE RESTRICT NOT VALID',
        );
        DB::statement('ALTER TABLE administraciones_medicacion VALIDATE CONSTRAINT fk_administracion_prescripcion_residente');
    }

    private function createPostgresExecutionTrigger(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION validar_ejecucion_residente_plan()
            RETURNS trigger AS $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM intervenciones_cuidado intervencion
                    INNER JOIN planes_cuidado plan ON plan.cod_plan = intervencion.cod_plan
                    WHERE intervencion.cod_intervencion = NEW.cod_intervencion
                      AND plan.cod_residente = NEW.cod_residente
                ) THEN
                    RAISE EXCEPTION 'La ejecución de cuidado no corresponde al residente del plan.'
                        USING ERRCODE = '23503';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_ejecucion_residente_plan
            BEFORE INSERT OR UPDATE OF cod_intervencion, cod_residente
            ON ejecuciones_cuidado
            FOR EACH ROW EXECUTE FUNCTION validar_ejecucion_residente_plan();
            SQL);
    }

    private function createPostgresChecks(): void
    {
        foreach ($this->checks as $qualifiedName => $expression) {
            [$table, $constraint] = explode('.', $qualifiedName, 2);
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$constraint} CHECK ({$expression}) NOT VALID");
            DB::statement("ALTER TABLE {$table} VALIDATE CONSTRAINT {$constraint}");
        }
    }

    private function createSqliteConsistencyTriggers(): void
    {
        foreach (array_keys($this->atencionChildren) as $table) {
            $condition = 'NEW.cod_atencion IS NOT NULL AND NOT EXISTS (SELECT 1 FROM atenciones WHERE cod_atencion = NEW.cod_atencion AND cod_residente = NEW.cod_residente)';
            $this->createSqliteAbortTrigger($table, "trg_{$table}_atencion_residente", $condition, 'La atención no corresponde al residente.');
        }

        $this->createSqliteAbortTrigger(
            'administraciones_medicacion',
            'trg_administracion_prescripcion_residente',
            'NOT EXISTS (SELECT 1 FROM prescripciones WHERE cod_prescripcion = NEW.cod_prescripcion AND cod_residente = NEW.cod_residente)',
            'La prescripción no corresponde al residente.',
        );
        $this->createSqliteAbortTrigger(
            'ejecuciones_cuidado',
            'trg_ejecucion_residente_plan',
            'NOT EXISTS (SELECT 1 FROM intervenciones_cuidado intervencion INNER JOIN planes_cuidado plan ON plan.cod_plan = intervencion.cod_plan WHERE intervencion.cod_intervencion = NEW.cod_intervencion AND plan.cod_residente = NEW.cod_residente)',
            'La ejecución de cuidado no corresponde al residente del plan.',
        );
    }

    private function createSqliteCheckTriggers(): void
    {
        foreach ($this->checks as $qualifiedName => $expression) {
            [$table, $constraint] = explode('.', $qualifiedName, 2);
            $this->createSqliteAbortTrigger(
                $table,
                "trg_{$constraint}",
                'NOT ('.$this->sqliteNewExpression($expression).')',
                "Restricción de integridad {$constraint} incumplida.",
            );
        }
    }

    private function sqliteNewExpression(string $expression): string
    {
        $columns = [
            'fecha_hora_liberacion', 'fecha_hora_asignacion', 'dosis',
            'fecha_hora_suspension', 'fecha_hora_prescripcion', 'dosis_programada',
            'dosis_administrada', 'puntaje_total', 'puntaje_maximo',
            'fecha_revision', 'fecha_solicitud', 'fecha_hora_cierre',
            'fecha_hora_apertura', 'intensidad_dolor', 'hay_dolor',
            'pa_sistolica', 'pa_diastolica', 'frecuencia_cardiaca',
            'frecuencia_respiratoria', 'temperatura', 'saturacion_oxigeno',
            'peso', 'talla',
        ];

        usort($columns, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($columns as $column) {
            $expression = preg_replace(
                '/(?<![.])\\b'.preg_quote($column, '/').'\\b/',
                'NEW.'.$column,
                $expression,
            );
        }

        return $expression;
    }

    private function createSqliteAbortTrigger(
        string $table,
        string $baseName,
        string $condition,
        string $message,
    ): void {
        foreach (['INSERT' => 'ins', 'UPDATE' => 'upd'] as $operation => $suffix) {
            DB::unprepared(
                "CREATE TRIGGER {$baseName}_{$suffix} BEFORE {$operation} ON {$table} "
                ."FOR EACH ROW WHEN {$condition} "
                ."BEGIN SELECT RAISE(ABORT, '{$message}'); END;",
            );
        }
    }
};
