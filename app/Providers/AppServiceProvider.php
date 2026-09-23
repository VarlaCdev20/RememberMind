<?php

namespace App\Providers;

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

        \Illuminate\Support\Facades\Gate::before(function ($user, string $ability) {
            // SUPERADMINISTRADOR tiene lectura transversal, pero las acciones
            // clínicas de escritura siguen exigiendo el rol profesional competente.
            $esLectura = in_array($ability, ['view', 'viewAny'], true)
                || str_ends_with($ability, '.ver');

            return $user->hasRole('SUPERADMINISTRADOR') && $esLectura ? true : null;
        });

        \Illuminate\Support\Facades\Route::bind('adulto_mayor', function ($value) {
            return \App\Models\AdultoMayor::where('cod_residente', $value)->firstOrFail();
        });
        \Illuminate\Support\Facades\Route::bind('adulto', function ($value) {
            return \App\Models\AdultoMayor::where('cod_residente', $value)->firstOrFail();
        });
        \Illuminate\Support\Facades\Route::bind('residente', function ($value) {
            return \App\Models\Residente::where('cod_residente', $value)->firstOrFail();
        });
    }
}
