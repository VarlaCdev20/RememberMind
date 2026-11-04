<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// El diagnóstico falla explícitamente hasta aprobar autoría; no simula una sesión web.
Schedule::command('alertas:verificar-vigilancia')
    ->name('vigilancia-asistencial')->everyFiveMinutes()->withoutOverlapping();
