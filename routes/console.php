<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $detector = app(\App\Services\Alertas\DeteccionAlertasService::class);
    $detector->detectar();
    $detector->detectarPreventivas();
})->name('vigilancia-asistencial')->everyFiveMinutes()->withoutOverlapping();
