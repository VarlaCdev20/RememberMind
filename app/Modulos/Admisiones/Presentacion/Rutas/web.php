<?php

use App\Modulos\Admisiones\Presentacion\Http\Controllers\AdmisionController;
use App\Modulos\Admisiones\Presentacion\Http\Controllers\PreadmisionController;
use App\Modulos\Admisiones\Presentacion\Livewire\Paginas\PreadmisionesPanel;
use App\Modulos\Admisiones\Presentacion\Livewire\Paginas\PreadmisionWizard;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_043' => static function (): void {
        Route::get('/preadmisiones', PreadmisionesPanel::class)
            ->name('preadmisiones');
    },
    'ruta_044' => static function (): void {
        Route::get('/preadmisiones/rechazadas', PreadmisionesPanel::class)
            ->name('preadmisiones.rechazadas');
    },
    'ruta_045' => static function (): void {
        Route::get('/preadmision', PreadmisionWizard::class)
            ->middleware('permission:admisiones.crear')
            ->name('preadmision');
    },
    'ruta_203' => static function (): void {
        Route::get('/preadmisiones', [PreadmisionController::class, 'index'])->middleware('permission:preadmisiones.ver')->name('preadmisiones.index');
    },
    'ruta_204' => static function (): void {
        Route::post('/preadmisiones', [PreadmisionController::class, 'store'])->middleware('permission:preadmisiones.crear')->name('preadmisiones.store');
    },
    'ruta_205' => static function (): void {
        Route::patch('/preadmisiones/{preadmision}/revision', [PreadmisionController::class, 'revisar'])->middleware('permission:preadmisiones.revisar')->name('preadmisiones.revisar');
    },
    'ruta_206' => static function (): void {
        Route::post('/preadmisiones/{preadmision}/admision', [AdmisionController::class, 'store'])->middleware('permission:admisiones.formalizar')->name('admisiones.store');
    },
];
