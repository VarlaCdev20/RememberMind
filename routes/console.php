<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:monitorear-documentos-pendientes')
    ->daily()
    ->description('Monitorear documentos pendientes y expirar los que excedan 48 horas');
