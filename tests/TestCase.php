<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $application = parent::createApplication();
        $connection = $application['config']->get('database.default');
        if (! $application->environment('testing') || $connection !== 'sqlite'
            || $application['config']->get('database.connections.sqlite.database') !== ':memory:'
            || $application['config']->get('database.connections.sqlite.url')) {
            throw new \RuntimeException('Se rechaza una base no desechable antes de preparar las pruebas.');
        }

        return $application;
    }

    protected function rutaVistaArchivo(string $vista): string
    {
        $nombre = str_replace('/', '.', preg_replace('/\.blade\.php$/', '', $vista));

        return view()->getFinder()->find($nombre);
    }
}
