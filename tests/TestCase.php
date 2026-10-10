<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function rutaVistaArchivo(string $vista): string
    {
        $nombre = str_replace('/', '.', preg_replace('/\.blade\.php$/', '', $vista));

        return view()->getFinder()->find($nombre);
    }
}
