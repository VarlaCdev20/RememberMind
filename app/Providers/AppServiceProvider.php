<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Carbon\Carbon::setLocale('es');
        setlocale(LC_TIME, 'es_ES.utf8', 'es_ES', 'es');

        foreach ([\App\Models\SignosVitalesAdulto::class, \App\Models\AdministracionMedicacion::class,
            \App\Models\TareaPlanCuidado::class, \App\Models\SeguimientoDiario::class] as $modelo) {
            $modelo::saved(fn ($registro) => app(\App\Services\Alertas\DeteccionAlertasService::class)->detectar($registro->cod_am));
        }

        Gate::before(function ($user, $ability) {
            return $user->hasRole('SUPERADMINISTRADOR') ? true : null;
        });
    }
}