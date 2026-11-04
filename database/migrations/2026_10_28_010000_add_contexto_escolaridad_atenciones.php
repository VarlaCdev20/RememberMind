<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Extensión escalar por episodio adoptada el 28/10/2026, sin nuevas tablas ni FK. */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $motor = DB::getDriverName();
            if (! in_array($motor, ['sqlite', 'pgsql'], true)) {
                throw new LogicException('Motor no admitido para esta extensión.');
            }
            $entero = $motor === 'sqlite' ? "typeof(escolaridad_anios)='integer' AND " : '';
            DB::statement("ALTER TABLE atenciones ADD COLUMN escolaridad_anios SMALLINT NULL
                CONSTRAINT atenciones_escolaridad_rango CHECK(escolaridad_anios IS NULL OR ({$entero}escolaridad_anios BETWEEN 0 AND 32767))");
            foreach (['puede_leer', 'puede_escribir'] as $campo) {
                $control = $motor === 'sqlite' ? " CONSTRAINT atenciones_{$campo}_logico CHECK({$campo} IS NULL OR {$campo} IN (0,1))" : '';
                DB::statement("ALTER TABLE atenciones ADD COLUMN {$campo} BOOLEAN NULL{$control}");
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SET LOCAL lock_timeout = '10s'");
                DB::statement('LOCK TABLE atenciones IN ACCESS EXCLUSIVE MODE');
            } else {
                DB::statement('UPDATE atenciones SET escolaridad_anios=escolaridad_anios WHERE 1=0');
            }
            if (DB::table('atenciones')->whereNotNull('escolaridad_anios')->orWhereNotNull('puede_leer')->orWhereNotNull('puede_escribir')->exists()) {
                throw new LogicException('El contexto clínico conservado no se elimina por rollback.');
            }
            foreach (['puede_escribir', 'puede_leer', 'escolaridad_anios'] as $campo) {
                DB::statement('ALTER TABLE atenciones DROP COLUMN '.$campo);
            }
        });
    }
};
