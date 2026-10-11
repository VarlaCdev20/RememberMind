<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;

class EntornoPruebasSeguroTest extends TestCase
{
    public function test_base_operativa_usuario_global_y_host_remoto_no_son_desechables(): void
    {
        $db = 'remembermind_test_aabbccdd_testing';
        $this->assertTrue(EntornoPruebas::postgresDesechable($db, $db.'_runner', '127.0.0.1'));
        $this->assertFalse(EntornoPruebas::postgresDesechable('remembermind_dev', 'postgres', '127.0.0.1'));
        $this->assertFalse(EntornoPruebas::postgresDesechable($db, 'postgres', '127.0.0.1'));
        $this->assertFalse(EntornoPruebas::postgresDesechable($db, $db.'_runner', 'servidor-remoto'));
    }

    public function test_bootstrap_rechaza_destino_operativo_antes_del_autoload(): void
    {
        $proceso = new Process([PHP_BINARY, '-r',
            'try { require $argv[1]; echo "ACEPTADO"; } catch (RuntimeException $e) { echo class_exists("Illuminate\\Foundation\\Application", false) ? "AUTOLOAD" : "RECHAZADO_ANTES_AUTOLOAD"; }',
            dirname(__DIR__).'/bootstrap.php'], env: ['APP_ENV' => 'testing', 'RM_TEST_PROFILE' => 'postgres',
                'DB_CONNECTION' => 'pgsql', 'DB_DATABASE' => 'remembermind_dev', 'DB_USERNAME' => 'postgres',
                'DB_HOST' => '127.0.0.1', 'DB_PORT' => '5432', 'DB_PASSWORD' => 'sintetica', 'DB_URL' => '']);
        $proceso->mustRun();
        $this->assertSame('RECHAZADO_ANTES_AUTOLOAD', $proceso->getOutput());
    }
}
