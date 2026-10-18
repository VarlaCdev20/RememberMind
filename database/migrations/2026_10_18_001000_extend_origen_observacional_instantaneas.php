<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Dos FK de origen aprobadas por la propietaria; ninguna tabla nueva ni transformación de historia. */
return new class extends Migration
{
    private const TABLA = 'hechos_instantanea_experta';

    private const ORIGEN = "(tipo_origen = 'D137' AND cod_evidencia_evaluacion IS NOT NULL) OR (tipo_origen IN ('REFERENCIA_EXTERNA_SINTETICA','REFERENCIA_CRITERIO_SINTETICA','REFERENCIA_IADL_SINTETICA','REVISION_TECNICA_SINTETICA') AND cod_evidencia_evaluacion IS NULL)";

    private const EPISODIO = "(tipo_origen = 'D137' AND ((cod_atencion IS NOT NULL AND cod_actividad IS NULL) OR (cod_atencion IS NULL AND cod_actividad IS NOT NULL))) OR (tipo_origen <> 'D137' AND cod_atencion IS NULL AND cod_actividad IS NULL)";

    public function up(): void
    {
        $this->transicion(function (): void {
            Schema::table(self::TABLA, function (Blueprint $t): void {
                $t->string('cod_control_cognitivo', 20)->nullable();
                $t->string('cod_personal_autor', 20)->nullable();
                $t->index('cod_control_cognitivo', 'obs_control_ix');
                $t->index('cod_personal_autor', 'obs_autor_ix');
                $t->foreign('cod_control_cognitivo', 'obs_control_fk')->references('cod_control_cognitivo')->on('controles_cognitivos')->restrictOnDelete();
                $t->foreign('cod_personal_autor', 'obs_autor_fk')->references('cod_personal')->on('personal')->restrictOnDelete();
            });
            $observacion = "tipo_origen = 'CONTROL_COGNITIVO' AND cod_control_cognitivo IS NOT NULL AND cod_personal_autor IS NOT NULL AND cod_evidencia_evaluacion IS NOT NULL AND cod_actividad IS NULL";
            $historico = "tipo_origen <> 'CONTROL_COGNITIVO' AND cod_control_cognitivo IS NULL AND cod_personal_autor IS NULL";
            $this->check('snap_origen', '(('.self::ORIGEN.') AND ('.$historico.')) OR ('.$observacion.')');
            $this->check('snap_episodio_original', '(('.self::EPISODIO.') AND ('.$historico.')) OR ('.$observacion.')');
        });
    }

    public function down(): void
    {
        if (DB::table(self::TABLA)->where('tipo_origen', 'CONTROL_COGNITIVO')->exists()) {
            throw new RuntimeException('No se retira el origen observacional con instantáneas conservadas.');
        }
        $this->transicion(function (): void {
            $this->retirarChecks();
            Schema::table(self::TABLA, function (Blueprint $t): void {
                $t->dropForeign(DB::getDriverName() === 'sqlite' ? ['cod_control_cognitivo'] : 'obs_control_fk');
                $t->dropForeign(DB::getDriverName() === 'sqlite' ? ['cod_personal_autor'] : 'obs_autor_fk');
                $t->dropIndex('obs_control_ix');
                $t->dropIndex('obs_autor_ix');
                $t->dropColumn(['cod_control_cognitivo', 'cod_personal_autor']);
            });
            $this->check('snap_origen', self::ORIGEN);
            $this->check('snap_episodio_original', self::EPISODIO);
        });
    }

    private function retirarChecks(): void
    {
        foreach (['snap_origen', 'snap_episodio_original'] as $nombre) {
            if (DB::getDriverName() === 'sqlite') {
                foreach (['insert', 'update'] as $evento) {
                    DB::unprepared("DROP TRIGGER IF EXISTS {$nombre}_{$evento}");
                }
            } else {
                DB::statement('ALTER TABLE '.self::TABLA.' DROP CONSTRAINT IF EXISTS '.$nombre);
            }
        }
    }

    private function check(string $nombre, string $expr): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $expr = preg_replace_callback("/'[^']*'|\\b[a-z][a-z0-9_]*\\b/", static fn (array $m): string => str_starts_with($m[0], "'") || ! str_contains($m[0], '_') ? $m[0] : 'NEW.'.$m[0], $expr);
            foreach (['insert', 'update'] as $evento) {
                DB::unprepared("DROP TRIGGER IF EXISTS {$nombre}_{$evento}");
                DB::unprepared('CREATE TRIGGER '.$nombre.'_'.$evento.' BEFORE '.strtoupper($evento).' ON '.self::TABLA." WHEN COALESCE(({$expr}),0) = 0 BEGIN SELECT RAISE(ABORT, '{$nombre}'); END");
            }
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE '.self::TABLA.' DROP CONSTRAINT IF EXISTS '.$nombre);
            DB::statement('ALTER TABLE '.self::TABLA.' ADD CONSTRAINT '.$nombre.' CHECK (('.$expr.') IS TRUE)');
        } else {
            throw new RuntimeException('Motor no soportado para conservación observacional.');
        }
    }

    /** Conserva también los triggers escalares existentes cuando SQLite reconstruye la tabla. */
    private function transicion(Closure $operacion): void
    {
        DB::transaction(function () use ($operacion): void {
            $sqlite = DB::getDriverName() === 'sqlite';
            $triggers = $sqlite ? DB::select("SELECT name, sql FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ?", [self::TABLA]) : [];
            $anterior = $sqlite ? (int) DB::scalar('PRAGMA defer_foreign_keys') : 0;
            if ($sqlite) {
                DB::statement('PRAGMA defer_foreign_keys = ON');
            }
            try {
                $operacion();
                foreach ($triggers as $trigger) {
                    if (! str_starts_with($trigger->name, 'snap_origen_') && ! str_starts_with($trigger->name, 'snap_episodio_original_')
                        && ! DB::scalar("SELECT count(*) FROM sqlite_master WHERE type = 'trigger' AND name = ?", [$trigger->name])) {
                        DB::unprepared($trigger->sql);
                    }
                }
                if ($sqlite && DB::select('PRAGMA foreign_key_check') !== []) {
                    throw new RuntimeException('Referencias inconsistentes durante la ampliación observacional.');
                }
            } finally {
                if ($sqlite) {
                    DB::statement('PRAGMA defer_foreign_keys = '.($anterior === 1 ? 'ON' : 'OFF'));
                }
            }
        });
    }
};
