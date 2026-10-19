<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Cinco columnas aprobadas por la propietaria el 18/10/2026; una decisión por revisión. */
return new class extends Migration
{
    public function up(): void
    {
        $this->transicion(fn () => $this->ampliar());
    }

    private function ampliar(): void
    {
        Schema::table('reglas_expertas', function (Blueprint $t): void {
            $t->string('cod_regla_anterior', 20)->nullable();
            $t->string('estado_revision', 20)->nullable();
            $t->string('cod_usuario_revision', 20)->nullable();
            $t->dateTime('fecha_hora_revision')->nullable();
            $t->text('justificacion_revision')->nullable();
            $t->index('cod_regla_anterior', 're_revision_anterior_i');
            $t->index('cod_usuario_revision', 're_revision_usuario_i');
            $t->foreign('cod_regla_anterior', 're_revision_anterior_f')->references('cod_regla_experta')->on('reglas_expertas')->restrictOnDelete();
            $t->foreign('cod_usuario_revision', 're_revision_usuario_f')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
        $check = "((estado_revision IS NULL OR estado_revision = 'PENDIENTE') AND cod_usuario_revision IS NULL AND fecha_hora_revision IS NULL AND justificacion_revision IS NULL) OR (estado_revision IN ('APROBADA','RECHAZADA') AND cod_usuario_revision IS NOT NULL AND fecha_hora_revision IS NOT NULL AND fecha_hora_revision >= fecha_hora_creacion AND justificacion_revision IS NOT NULL AND length(trim(justificacion_revision)) > 0)";
        $check = 'COALESCE(('.$check.'), FALSE)';
        $previous = '(cod_regla_anterior IS NULL OR cod_regla_anterior <> cod_regla_experta)';
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE reglas_expertas ADD CONSTRAINT re_revision_firma_ck CHECK ('.$check.')');
            DB::statement('ALTER TABLE reglas_expertas ADD CONSTRAINT re_revision_anterior_ck CHECK '.$previous);
            DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION conservar_revision_regla() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    IF OLD.cod_regla_anterior IS DISTINCT FROM NEW.cod_regla_anterior
       OR OLD.cod_regla_experta IS DISTINCT FROM NEW.cod_regla_experta
       OR (OLD.estado_revision IN ('APROBADA','RECHAZADA') AND
           ROW(OLD.estado_revision, OLD.cod_usuario_revision, OLD.fecha_hora_revision, OLD.justificacion_revision)
           IS DISTINCT FROM
           ROW(NEW.estado_revision, NEW.cod_usuario_revision, NEW.fecha_hora_revision, NEW.justificacion_revision)) THEN
        RAISE EXCEPTION 'La revision y su antecedente se conservan; cree una sucesora';
    END IF;
    RETURN NEW;
END;
$$;
CREATE TRIGGER re_revision_historia BEFORE UPDATE ON reglas_expertas
FOR EACH ROW EXECUTE FUNCTION conservar_revision_regla();
SQL);
        } elseif (DB::getDriverName() === 'sqlite') {
            foreach (['INSERT', 'UPDATE'] as $event) {
                $predicate = preg_replace('/\b(estado_revision|cod_usuario_revision|fecha_hora_revision|fecha_hora_creacion|justificacion_revision|cod_regla_anterior|cod_regla_experta)\b/', 'NEW.$1', '('.$check.') AND '.$previous);
                DB::unprepared('CREATE TRIGGER re_revision_'.strtolower($event).' BEFORE '.$event.' ON reglas_expertas WHEN NOT ('.$predicate.") BEGIN SELECT RAISE(ABORT, 'Revision de regla incompatible'); END");
            }
            DB::unprepared(<<<'SQL'
CREATE TRIGGER re_revision_historia BEFORE UPDATE ON reglas_expertas
WHEN OLD.cod_regla_anterior IS NOT NEW.cod_regla_anterior
  OR OLD.cod_regla_experta IS NOT NEW.cod_regla_experta
  OR (OLD.estado_revision IN ('APROBADA','RECHAZADA') AND
      (OLD.estado_revision IS NOT NEW.estado_revision OR OLD.cod_usuario_revision IS NOT NEW.cod_usuario_revision
       OR OLD.fecha_hora_revision IS NOT NEW.fecha_hora_revision OR OLD.justificacion_revision IS NOT NEW.justificacion_revision))
BEGIN SELECT RAISE(ABORT, 'La revision y su antecedente se conservan; cree una sucesora'); END
SQL);
        } else {
            throw new LogicException('Motor no soportado por el contrato de revisión de reglas.');
        }
    }

    public function down(): void
    {
        $this->transicion(fn () => $this->retirar());
    }

    private function retirar(): void
    {
        if (DB::table('reglas_expertas')->where(function ($q): void {
            foreach (['cod_regla_anterior', 'estado_revision', 'cod_usuario_revision', 'fecha_hora_revision', 'justificacion_revision'] as $column) {
                $q->orWhereNotNull($column);
            }
        })->exists()) {
            throw new LogicException('No se puede retirar el contrato mientras conserva historia de revisión o sustitución.');
        }
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER re_revision_historia ON reglas_expertas');
            DB::statement('DROP FUNCTION conservar_revision_regla()');
            DB::statement('ALTER TABLE reglas_expertas DROP CONSTRAINT re_revision_firma_ck');
            DB::statement('ALTER TABLE reglas_expertas DROP CONSTRAINT re_revision_anterior_ck');
        } else {
            DB::unprepared('DROP TRIGGER IF EXISTS re_revision_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS re_revision_update');
            DB::unprepared('DROP TRIGGER IF EXISTS re_revision_historia');
        }
        Schema::table('reglas_expertas', function (Blueprint $t): void {
            $t->dropForeign(DB::getDriverName() === 'sqlite' ? ['cod_regla_anterior'] : 're_revision_anterior_f');
            $t->dropForeign(DB::getDriverName() === 'sqlite' ? ['cod_usuario_revision'] : 're_revision_usuario_f');
            $t->dropIndex('re_revision_anterior_i');
            $t->dropIndex('re_revision_usuario_i');
            $t->dropColumn(['cod_regla_anterior', 'estado_revision', 'cod_usuario_revision', 'fecha_hora_revision', 'justificacion_revision']);
        });
    }

    /** SQLite reconstruye la tabla al retirar FK; se conservan datos, referencias y triggers ajenos. */
    private function transicion(Closure $operacion): void
    {
        DB::transaction(function () use ($operacion): void {
            $sqlite = DB::getDriverName() === 'sqlite';
            $triggers = $sqlite ? DB::select("SELECT name, sql FROM sqlite_master WHERE type = 'trigger' AND tbl_name = 'reglas_expertas'") : [];
            $anterior = $sqlite ? (int) DB::scalar('PRAGMA defer_foreign_keys') : 0;
            if ($sqlite) {
                DB::statement('PRAGMA defer_foreign_keys = ON');
            }
            try {
                $operacion();
                foreach ($triggers as $trigger) {
                    if (! str_starts_with($trigger->name, 're_revision_')
                        && ! DB::scalar("SELECT count(*) FROM sqlite_master WHERE type = 'trigger' AND name = ?", [$trigger->name])) {
                        DB::unprepared($trigger->sql);
                    }
                }
                if ($sqlite && DB::select('PRAGMA foreign_key_check') !== []) {
                    throw new LogicException('Referencias inconsistentes durante la transición de revisión de reglas.');
                }
            } finally {
                if ($sqlite) {
                    DB::statement('PRAGMA defer_foreign_keys = '.($anterior === 1 ? 'ON' : 'OFF'));
                }
            }
        });
    }
};
