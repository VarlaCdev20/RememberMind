<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Contraste de la referencia amplia F02; no acredita reducción a 110 tablas. */
final class ReferenciaF02PostgresDesechableTest extends TestCase
{
    public function test_el_dto_compuesto_completo_vuelve_de_sql_y_reproduce_sus_huellas_en_el_esquema_referencia(): void
    {
        if (PHP_OS_FAMILY !== 'Windows' || getenv('RM_TEST_PROFILE') !== 'postgres'
            || ! preg_match('/^remembermind_test_[a-f0-9]{8}_testing$/D', (string) getenv('DB_DATABASE'))) {
            self::markTestSkipped('Requiere PostgreSQL propio y desechable del runner.');
        }

        $raiz = dirname(__DIR__, 2);
        $nombre = 'remembermind-orion-f02-'.bin2hex(random_bytes(8));
        // sys_get_temp_dir() puede devolver la ruta corta 8.3 de Windows y romper el aislamiento textual del replay.
        $temporal = rtrim((string) getenv('LOCALAPPDATA'), '/\\').DIRECTORY_SEPARATOR.'Temp';
        self::assertDirectoryExists($temporal);
        $salida = $temporal.DIRECTORY_SEPARATOR.$nombre;
        $proceso = new Process([PHP_BINARY, $raiz.'/prototipos/orion-conservacion-0_2/ensayo.php', $salida], $raiz);
        $proceso->setTimeout(180);

        try {
            $proceso->mustRun();
            $resultado = json_decode(file_get_contents($salida.'/resultados.json'), true, flags: JSON_THROW_ON_ERROR);
            self::assertSame(0, $resultado['fallos']);
            $casos = array_column($resultado['pruebas'], null, 'caso');
            self::assertSame('PASS', $casos['guardar_recuperar_replay_dto_completo']['estado']);
            self::assertTrue($casos['guardar_recuperar_replay_dto_completo']['evidencia']['igualdad_estricta']);
            self::assertSame('PASS', $casos['fallo_intermedio_revierte_todas_las_filas']['estado']);
        } finally {
            $base = realpath($temporal);
            $real = is_dir($salida) ? realpath($salida) : false;
            if ($base !== false && $real !== false && dirname($real) === $base && basename($real) === $nombre) {
                (new Filesystem)->deleteDirectory($real);
            }
        }
    }
}
