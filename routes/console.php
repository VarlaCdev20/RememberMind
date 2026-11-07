<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('alertas:verificar-vigilancia')
    ->name('vigilancia-asistencial')->everyFiveMinutes()->withoutOverlapping()
    ->when(fn () => filled(config('vigilancia_alertas.desde')));
