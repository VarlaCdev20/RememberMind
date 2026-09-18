<?php

use App\Http\Controllers\Admisiones\AdmisionController;
use App\Http\Controllers\Admisiones\PreadmisionController;
use App\Http\Controllers\Reportes\DashboardController;
use App\Http\Controllers\Residentes\ResidenteController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('inicio');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/residentes', [ResidenteController::class, 'index'])
            ->middleware('can:viewAny,App\\Models\\Residente')->name('residentes.index');
        Route::get('/residentes/{residente}', [ResidenteController::class, 'show'])
            ->middleware('can:view,residente')->name('residentes.show');

        Route::get('/preadmisiones', [PreadmisionController::class, 'index'])
            ->middleware('permission:preadmisiones.ver')->name('preadmisiones.index');
        Route::post('/preadmisiones', [PreadmisionController::class, 'store'])
            ->middleware('permission:preadmisiones.crear')->name('preadmisiones.store');
        Route::patch('/preadmisiones/{preadmision}/revision', [PreadmisionController::class, 'revisar'])
            ->middleware('permission:preadmisiones.revisar')->name('preadmisiones.revisar');
        Route::post('/preadmisiones/{preadmision}/admision', [AdmisionController::class, 'store'])
            ->middleware('permission:admisiones.formalizar')->name('admisiones.store');
    });
});
