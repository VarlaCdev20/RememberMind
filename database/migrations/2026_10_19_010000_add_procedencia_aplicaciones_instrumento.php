<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Cinco columnas aprobadas el 19/10/2026; no se completan metadatos de registros anteriores. */
return new class extends Migration
{
    public function up(): void
    {
        $this->transicion(fn () => $this->ampliar());
    }

    private function ampliar(): void
    {
        Schema::table('aplicaciones_instrumento', function (Blueprint $t): void {
            $t->string('cod_usuario_registro', 20)->nullable();
            $t->dateTime('registrado_en', 6)->nullable();
            $t->string('cod_documento_clinico', 20)->nullable();
            $t->string('localizador_fuente', 120)->nullable();
            $t->string('cod_aplicacion_anterior', 20)->nullable();
            foreach (['cod_usuario_registro' => ['usuarios', 'cod_usuario'], 'cod_documento_clinico' => ['documentos_clinicos', 'cod_documento_clinico'],
                'cod_aplicacion_anterior' => ['aplicaciones_instrumento', 'cod_aplicacion']] as $columna => [$tabla, $pk]) {
                $t->index($columna, 'ai_fuente_'.$columna.'_i');
                $t->foreign($columna, 'ai_fuente_'.$columna.'_f')->references($pk)->on($tabla)->restrictOnDelete();
            }
        });
        DB::statement('CREATE UNIQUE INDEX ai_fuente_original_u ON aplicaciones_instrumento (cod_documento_clinico, localizador_fuente) WHERE cod_documento_clinico IS NOT NULL AND cod_aplicacion_anterior IS NULL');
        DB::statement('CREATE UNIQUE INDEX ai_fuente_sucesora_u ON aplicaciones_instrumento (cod_aplicacion_anterior) WHERE cod_aplicacion_anterior IS NOT NULL');
        $check = '((cod_usuario_registro IS NULL AND registrado_en IS NULL) OR (cod_usuario_registro IS NOT NULL AND registrado_en IS NOT NULL))'
            .' AND ((cod_documento_clinico IS NULL AND localizador_fuente IS NULL) OR (cod_documento_clinico IS NOT NULL AND localizador_fuente IS NOT NULL AND length(trim(localizador_fuente)) > 0))'
            .' AND (cod_aplicacion_anterior IS NULL OR cod_aplicacion_anterior <> cod_aplicacion)';
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE aplicaciones_instrumento ADD CONSTRAINT ai_fuente_ck CHECK ('.$check.')');
        } elseif (DB::getDriverName() === 'sqlite') {
            $predicate = preg_replace('/\b(cod_usuario_registro|registrado_en|cod_documento_clinico|localizador_fuente|cod_aplicacion_anterior|cod_aplicacion)\b/', 'NEW.$1', $check);
            foreach (['INSERT', 'UPDATE'] as $evento) {
                DB::unprepared('CREATE TRIGGER ai_fuente_'.strtolower($evento).' BEFORE '.$evento.' ON aplicaciones_instrumento WHEN NOT ('.$predicate.") BEGIN SELECT RAISE(ABORT, 'Procedencia instrumental incompatible'); END");
            }
        } else {
            throw new LogicException('Motor no soportado por el contrato de fuentes originales.');
        }
    }

    public function down(): void
    {
        $this->transicion(fn () => $this->retirar());
    }

    private function retirar(): void
    {
        if (DB::table('aplicaciones_instrumento')->whereNotNull('cod_usuario_registro')->orWhereNotNull('registrado_en')
            ->orWhereNotNull('cod_documento_clinico')->orWhereNotNull('localizador_fuente')->orWhereNotNull('cod_aplicacion_anterior')->exists()) {
            throw new LogicException('No se retira procedencia mientras conserva registros; requiere una reversión sin pérdida.');
        }
        DB::statement('DROP INDEX ai_fuente_original_u');
        DB::statement('DROP INDEX ai_fuente_sucesora_u');
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE aplicaciones_instrumento DROP CONSTRAINT ai_fuente_ck');
        } else {
            DB::unprepared('DROP TRIGGER ai_fuente_insert');
            DB::unprepared('DROP TRIGGER ai_fuente_update');
        }
        Schema::table('aplicaciones_instrumento', function (Blueprint $t): void {
            foreach (['cod_usuario_registro', 'cod_documento_clinico', 'cod_aplicacion_anterior'] as $c) {
                $t->dropForeign(DB::getDriverName() === 'sqlite' ? [$c] : 'ai_fuente_'.$c.'_f');
                $t->dropIndex('ai_fuente_'.$c.'_i');
            }
            $t->dropColumn(['cod_usuario_registro', 'registrado_en', 'cod_documento_clinico', 'localizador_fuente', 'cod_aplicacion_anterior']);
        });
    }

    /** SQLite reconstruye la tabla al cambiar FK; no pierde respuestas, triggers previos ni referencias. */
    private function transicion(Closure $operacion): void
    {
        DB::transaction(function () use ($operacion): void {
            $sqlite = DB::getDriverName() === 'sqlite';
            $triggers = $sqlite ? DB::select("SELECT name, sql FROM sqlite_master WHERE type = 'trigger' AND tbl_name = 'aplicaciones_instrumento'") : [];
            $anterior = $sqlite ? (int) DB::scalar('PRAGMA defer_foreign_keys') : 0;
            if ($sqlite) {
                DB::statement('PRAGMA defer_foreign_keys = ON');
            }
            try {
                $operacion();
                foreach ($triggers as $trigger) {
                    if (! str_starts_with($trigger->name, 'ai_fuente_')
                        && ! DB::scalar("SELECT count(*) FROM sqlite_master WHERE type = 'trigger' AND name = ?", [$trigger->name])) {
                        DB::unprepared($trigger->sql);
                    }
                }
                if ($sqlite && DB::select('PRAGMA foreign_key_check') !== []) {
                    throw new LogicException('Referencias inconsistentes durante la transición de procedencia.');
                }
            } finally {
                if ($sqlite) {
                    DB::statement('PRAGMA defer_foreign_keys = '.($anterior === 1 ? 'ON' : 'OFF'));
                }
            }
        });
    }
};
