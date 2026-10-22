<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\RestablecerEsquemaPostgres;
use Tests\TestCase;

final class PreparacionPostgresRepetidaTest extends TestCase
{
    public static function transacciones(): array
    {
        return [['laravel'], ['pdo']];
    }

    #[DataProvider('transacciones')]
    public function test_no_restablece_una_transaccion_abierta_ni_la_revierte(string $tipo): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Guarda del runner PostgreSQL.');
        }
        $db = DB::connection();
        $tipo === 'pdo' ? $db->getPdo()->beginTransaction() : $db->beginTransaction();
        try {
            try {
                RestablecerEsquemaPostgres::antesDeFresh(null);
                self::fail('Una transacción abierta debe impedir el restablecimiento.');
            } catch (\RuntimeException $e) {
                self::assertStringContainsString('transacciones abiertas', $e->getMessage());
                self::assertTrue($db->getPdo()->inTransaction());
            }
        } finally {
            $tipo === 'pdo' ? $db->getPdo()->rollBack() : $db->rollBack();
        }
    }

    public function test_rechaza_search_path_de_sesion_distinto_sin_modificarlo(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Guarda del runner PostgreSQL.');
        }
        DB::statement('SET search_path TO pg_catalog');
        try {
            try {
                RestablecerEsquemaPostgres::antesDeFresh(null);
                self::fail('No debe restablecer un esquema distinto.');
            } catch (\RuntimeException $e) {
                self::assertStringContainsString('identidad SQL', $e->getMessage());
                self::assertSame('pg_catalog', DB::selectOne('SELECT current_schema() AS s', [], false)->s);
            }
        } finally {
            DB::statement('SET search_path TO public');
        }
    }

    public function test_dos_fresh_consecutivos_preservan_funciones_y_triggers_petersen(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('La persistencia de funciones independientes requiere PostgreSQL desechable.');
        }
        try {
            foreach ([1, 2] as $ciclo) {
                $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
                self::assertSame(2, (int) DB::selectOne("SELECT count(*) AS n FROM pg_proc p JOIN pg_namespace n ON n.oid=p.pronamespace
                    WHERE n.nspname='public' AND p.proname IN ('vpo_inmutable','svp_inmutable')")->n);
                self::assertSame(2, (int) DB::selectOne("SELECT count(*) AS n FROM pg_trigger WHERE NOT tgisinternal AND tgname IN ('vpo_inmutable','svp_inmutable')")->n);
                self::assertSame(0, DB::table('evaluaciones_expertas')->count());
                self::assertSame(0, DB::table('valoraciones_petersen_originales')->count());
            }
        } finally {
            RefreshDatabaseState::$migrated = false;
        }
    }
}
