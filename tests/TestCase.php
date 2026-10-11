<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Tests\Support\EntornoPruebas;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $application = parent::createApplication();
        $connection = $application['config']->get('database.default');
        $config = $application['config']->get('database.connections.'.$connection, []);
        $segura = $connection === 'sqlite' && ($config['database'] ?? null) === ':memory:';
        if (getenv('RM_TEST_PROFILE') === 'postgres' && $connection === 'pgsql') {
            $segura = EntornoPruebas::postgresDesechable((string) ($config['database'] ?? ''), (string) ($config['username'] ?? ''), (string) ($config['host'] ?? ''));
        }
        if (! $application->environment('testing') || ! $segura || ! empty($config['url'])) {
            throw new \RuntimeException('Se rechaza una base no desechable antes de preparar las pruebas.');
        }
        if ($connection === 'pgsql') {
            $identidad = DB::selectOne('select current_database() as database, current_user as username, (select case when rolsuper or rolcreatedb then 1 else 0 end from pg_roles where rolname = current_user) as privileged');
            if ($identidad->database !== $config['database'] || $identidad->username !== $config['username'] || (int) $identidad->privileged !== 0) {
                throw new \RuntimeException('El runner debe ser propio de la base de pruebas y carecer de privilegios globales.');
            }
        }

        return $application;
    }

    protected function rutaVistaArchivo(string $vista): string
    {
        $nombre = str_replace('/', '.', preg_replace('/\.blade\.php$/', '', $vista));

        return view()->getFinder()->find($nombre);
    }
}
