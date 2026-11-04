<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\RestablecerEsquemaPostgres;
use Tests\TestCase;

final class GuardasPreparacionBaseTest extends TestCase
{
    public function test_alias_sqlite_persistente_se_rechaza_antes_de_abrir_archivo(): void
    {
        $ruta = sys_get_temp_dir().'/remembermind-no-abrir-'.bin2hex(random_bytes(8)).'.sqlite';
        config(['database.connections.alias_no_desechable' => ['driver' => 'sqlite', 'database' => $ruta, 'prefix' => '']]);
        try {
            RestablecerEsquemaPostgres::antesDeFresh('alias_no_desechable');
            self::fail('No debe permitir un fresh de un archivo.');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('no desechable', $e->getMessage());
            self::assertFileDoesNotExist($ruta);
        }
    }

    public function test_rechaza_conexion_con_lectura_y_escritura_separadas_sin_conectarse(): void
    {
        config(['database.connections.alias_dividido' => ['driver' => 'pgsql', 'host' => '127.0.0.1',
            'database' => 'remembermind_test_aabbccdd_testing', 'username' => 'remembermind_test_aabbccdd_testing_runner',
            'password' => 'sintetico', 'search_path' => 'public', 'read' => ['database' => 'remembermind_dev'], 'prefix' => '']]);
        $this->expectException(\RuntimeException::class);
        RestablecerEsquemaPostgres::antesDeFresh('alias_dividido');
    }
}
