<?php

use App\Modulos\Alertas\Presentacion\Http\Controllers\AlertaController;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPendientesPanel;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\IncidentesPanel;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_042' => static function (): void {
        Route::get('adultos-mayores/alertas-pendientes', AlertasPendientesPanel::class)
            ->middleware('permission:residentes.ver')
            ->name('adultos-mayores.alertas-pendientes');
    },
    'ruta_135' => static function (): void {
        Route::get('/', AlertasPanel::class)->name('index');
    },
    'ruta_146' => static function (): void {
        Route::get('/incidentes', IncidentesPanel::class)
            ->middleware('permission:enfermeria.ver_dashboard|incidentes.ver|atenciones.ver')
            ->name('incidentes');
    },
    'ruta_153' => static function (): void {
        Route::get('/alertas', AlertasPanel::class)
            ->middleware('permission:alertas.ver|alertas.gestionar')
            ->name('alertas');
    },
    'ruta_169' => static function (): void {
        Route::get('/alertas', AlertasPanel::class)
            ->name('alertas');
    },
    'ruta_245' => static function (): void {
        Route::get('/alertas', [AlertaController::class, 'index'])->middleware('permission:alertas.ver')->name('alertas.index');
    },
    'ruta_246' => static function (): void {
        Route::post('/residentes/{residente}/alertas', [AlertaController::class, 'store'])->middleware('permission:alertas.gestionar')->name('alertas.store');
    },
    'ruta_247' => static function (): void {
        Route::patch('/alertas/{alerta}/estado', [AlertaController::class, 'cambiarEstado'])
            ->middleware('permission:alertas.gestionar|alertas.reconocer|alertas.asignar|alertas.seguimiento|alertas.cerrar')
            ->name('alertas.estado');
    },
];
