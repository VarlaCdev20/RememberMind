<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // FASE A: Auditoria previa de datos existentes (Zero Fabrication y No Conversion Automatica de Ambiguos)
        $this->auditarDatosExistentes();

        // FASE B: Endurecimiento de restricciones CHECK en PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            // Actualizar restriccion en valoraciones_enfermeria_preadmision (rango canonico 50.0 a 240.0 cm)
            DB::statement('ALTER TABLE valoraciones_enfermeria_preadmision DROP CONSTRAINT IF EXISTS ck_val_enf_talla');
            DB::statement('ALTER TABLE valoraciones_enfermeria_preadmision ADD CONSTRAINT ck_val_enf_talla CHECK (talla IS NULL OR (talla >= 50.0 AND talla <= 240.0)) NOT VALID');
            DB::statement('ALTER TABLE valoraciones_enfermeria_preadmision VALIDATE CONSTRAINT ck_val_enf_talla');

            // Establecer restriccion en mediciones_antropometricas (rango canonico 50.0 a 240.0 cm)
            DB::statement('ALTER TABLE mediciones_antropometricas DROP CONSTRAINT IF EXISTS ck_med_ant_talla');
            DB::statement('ALTER TABLE mediciones_antropometricas ADD CONSTRAINT ck_med_ant_talla CHECK (talla IS NULL OR (talla >= 50.0 AND talla <= 240.0)) NOT VALID');
            DB::statement('ALTER TABLE mediciones_antropometricas VALIDATE CONSTRAINT ck_med_ant_talla');

            return;
        }

        // FASE C: Emulacion de restricciones CHECK mediante triggers para SQLite (suite de pruebas)
        if (DB::getDriverName() === 'sqlite') {
            $this->createSqliteCheckTriggers();
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE mediciones_antropometricas DROP CONSTRAINT IF EXISTS ck_med_ant_talla');
            DB::statement('ALTER TABLE valoraciones_enfermeria_preadmision DROP CONSTRAINT IF EXISTS ck_val_enf_talla');
            DB::statement('ALTER TABLE valoraciones_enfermeria_preadmision ADD CONSTRAINT ck_val_enf_talla CHECK (talla IS NULL OR (talla >= 0.5 AND talla <= 240.0))');

            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS trg_ck_med_ant_talla_ins');
            DB::statement('DROP TRIGGER IF EXISTS trg_ck_med_ant_talla_upd');
            DB::statement('DROP TRIGGER IF EXISTS trg_ck_val_enf_talla_ins');
            DB::statement('DROP TRIGGER IF EXISTS trg_ck_val_enf_talla_upd');

            // Restaurar trigger anterior para valoraciones_enfermeria_preadmision
            $condition = 'NOT (NEW.talla IS NULL OR (NEW.talla >= 0.5 AND NEW.talla <= 240.0))';
            $msg = 'Restriccion de integridad ck_val_enf_talla incumplida.';
            DB::unprepared("CREATE TRIGGER trg_ck_val_enf_talla_ins BEFORE INSERT ON valoraciones_enfermeria_preadmision FOR EACH ROW WHEN {$condition} BEGIN SELECT RAISE(ABORT, '{$msg}'); END;");
            DB::unprepared("CREATE TRIGGER trg_ck_val_enf_talla_upd BEFORE UPDATE ON valoraciones_enfermeria_preadmision FOR EACH ROW WHEN {$condition} BEGIN SELECT RAISE(ABORT, '{$msg}'); END;");
        }
    }

    private function auditarDatosExistentes(): void
    {
        // Verificar si existen valores de talla fuera del rango canonico o ambiguos
        $fueraRangoPreadmision = DB::table('valoraciones_enfermeria_preadmision')
            ->whereNotNull('talla')
            ->where(function ($q) {
                $q->where('talla', '<', 50.0)
                    ->orWhere('talla', '>', 240.0);
            })
            ->count();

        if ($fueraRangoPreadmision > 0) {
            throw new RuntimeException(
                "Existen {$fueraRangoPreadmision} registros en 'valoraciones_enfermeria_preadmision' con talla fuera del rango canonico (50.0 - 240.0 cm). Conforme a la politica de no conversion automatica de datos ambiguos, se detiene la migracion para regularizacion manual auditada."
            );
        }

        $fueraRangoAntropometria = DB::table('mediciones_antropometricas')
            ->whereNotNull('talla')
            ->where(function ($q) {
                $q->where('talla', '<', 50.0)
                    ->orWhere('talla', '>', 240.0);
            })
            ->count();

        if ($fueraRangoAntropometria > 0) {
            throw new RuntimeException(
                "Existen {$fueraRangoAntropometria} registros en 'mediciones_antropometricas' con talla fuera del rango canonico (50.0 - 240.0 cm). Conforme a la politica de no conversion automatica de datos ambiguos, se detiene la migracion para regularizacion manual auditada."
            );
        }
    }

    private function createSqliteCheckTriggers(): void
    {
        // 1. valoraciones_enfermeria_preadmision
        DB::statement('DROP TRIGGER IF EXISTS trg_ck_val_enf_talla_ins');
        DB::statement('DROP TRIGGER IF EXISTS trg_ck_val_enf_talla_upd');

        $condVal = 'NOT (NEW.talla IS NULL OR (NEW.talla >= 50.0 AND NEW.talla <= 240.0))';
        $msgVal = 'Restriccion de integridad ck_val_enf_talla incumplida.';
        DB::unprepared("CREATE TRIGGER trg_ck_val_enf_talla_ins BEFORE INSERT ON valoraciones_enfermeria_preadmision FOR EACH ROW WHEN {$condVal} BEGIN SELECT RAISE(ABORT, '{$msgVal}'); END;");
        DB::unprepared("CREATE TRIGGER trg_ck_val_enf_talla_upd BEFORE UPDATE ON valoraciones_enfermeria_preadmision FOR EACH ROW WHEN {$condVal} BEGIN SELECT RAISE(ABORT, '{$msgVal}'); END;");

        // 2. mediciones_antropometricas
        DB::statement('DROP TRIGGER IF EXISTS trg_ck_med_ant_talla_ins');
        DB::statement('DROP TRIGGER IF EXISTS trg_ck_med_ant_talla_upd');

        $condMed = 'NOT (NEW.talla IS NULL OR (NEW.talla >= 50.0 AND NEW.talla <= 240.0))';
        $msgMed = 'Restriccion de integridad ck_med_ant_talla incumplida.';
        DB::unprepared("CREATE TRIGGER trg_ck_med_ant_talla_ins BEFORE INSERT ON mediciones_antropometricas FOR EACH ROW WHEN {$condMed} BEGIN SELECT RAISE(ABORT, '{$msgMed}'); END;");
        DB::unprepared("CREATE TRIGGER trg_ck_med_ant_talla_upd BEFORE UPDATE ON mediciones_antropometricas FOR EACH ROW WHEN {$condMed} BEGIN SELECT RAISE(ABORT, '{$msgMed}'); END;");
    }
};
