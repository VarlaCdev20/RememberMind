<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Referencia amplia: seis DTO completos en SQLite :memory:; no acredita un diseño <=110. */
final class ReferenciaF02SqliteDesechableTest extends TestCase
{
    public function test_las_dos_ramas_y_el_replay_completo_vuelven_de_sqlite_desechable(): void
    {
        $raiz = dirname(__DIR__, 2);
        $proceso = new Process([PHP_BINARY, $raiz.'/prototipos/orion-conservacion-0_2/ensayar-sqlite-completo.php'], $raiz);
        $proceso->setTimeout(90);
        $proceso->mustRun();

        self::assertStringContainsString('PASS F02 SQLite con fusión B01: 6 casos, 38 tablas físicas', $proceso->getOutput());
        self::assertStringContainsString('39 modelos y 51 colecciones', $proceso->getOutput());
        self::assertStringContainsString('NO cumple el máximo 110', $proceso->getOutput());
    }

    public function test_ensayo_integrado_con_la_cabecera_institucional_en_base_desechable(): void
    {
        $raiz = dirname(__DIR__, 2);
        $proceso = new Process([PHP_BINARY, $raiz.'/prototipos/orion-conservacion-0_2/ensayar-sqlite-completo.php', '--base-institucional'], $raiz);
        $proceso->setTimeout(120);
        $proceso->mustRun();

        self::assertStringContainsString('PASS F02 SQLite sobre 104 tablas instaladas: 6 casos, 157 tablas físicas', $proceso->getOutput());
        self::assertStringContainsString('39 modelos y 51 colecciones', $proceso->getOutput());
        self::assertStringContainsString('NO cumple el máximo 110', $proceso->getOutput());
    }
}
