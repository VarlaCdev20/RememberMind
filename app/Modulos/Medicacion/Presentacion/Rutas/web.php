<?php

use App\Modulos\Medicacion\Presentacion\Http\Controllers\AdultoMayorAdministracionMedicacionController;
use App\Modulos\Medicacion\Presentacion\Http\Controllers\AdultoMayorMedicacionController;
use App\Modulos\Medicacion\Presentacion\Http\Controllers\MedicacionController;
use App\Modulos\Medicacion\Presentacion\Livewire\Paginas\SaludAdministracionMedicacionPanel;
use App\Modulos\Medicacion\Presentacion\Livewire\Paginas\SaludMedicacionPanel;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_088' => static function (): void {
        Route::post('/medicacion', [AdultoMayorMedicacionController::class, 'store'])->middleware('permission:prescripciones.crear')->name('medicacion.store');
    },
    'ruta_089' => static function (): void {
        Route::put('/medicacion/{medicacion}', [AdultoMayorMedicacionController::class, 'update'])->middleware('permission:prescripciones.editar')->name('medicacion.update');
    },
    'ruta_090' => static function (): void {
        Route::patch('/medicacion/{medicacion}/suspender', [AdultoMayorMedicacionController::class, 'suspender'])->middleware('permission:prescripciones.suspender')->name('medicacion.suspender');
    },
    'ruta_091' => static function (): void {
        Route::patch('/medicacion/{medicacion}/finalizar', [AdultoMayorMedicacionController::class, 'finalizar'])->middleware('permission:prescripciones.editar')->name('medicacion.finalizar');
    },
    'ruta_092' => static function (): void {
        Route::patch('/medicacion/{medicacion}/archivar', [AdultoMayorMedicacionController::class, 'archivar'])->middleware('permission:prescripciones.editar')->name('medicacion.archivar');
    },
    'ruta_093' => static function (): void {
        Route::patch('/medicacion/{medicacion}/restaurar', [AdultoMayorMedicacionController::class, 'restore'])->middleware('permission:prescripciones.editar')->name('medicacion.restore');
    },
    'ruta_094' => static function (): void {
        Route::post('/administracion-medicacion', [AdultoMayorAdministracionMedicacionController::class, 'store'])
            ->middleware('permission:administraciones_medicacion.crear')
            ->name('administracion-medicacion.store');
    },
    'ruta_116' => static function (): void {
        Route::get('/{adulto}/medicacion', SaludMedicacionPanel::class)->name('medicacion');
    },
    'ruta_117' => static function (): void {
        Route::get('/{adulto}/administracion', SaludAdministracionMedicacionPanel::class)->name('administracion');
    },
    'ruta_133' => static function (): void {
        Route::get('/medicacion', SaludAdministracionMedicacionPanel::class)
            ->middleware('permission:enfermeria.ver_dashboard|administraciones_medicacion.ver|prescripciones.ver')
            ->name('medicacion');
    },
    'ruta_152' => static function (): void {
        Route::get('/medicacion', SaludAdministracionMedicacionPanel::class)
            ->middleware('permission:enfermeria.ver_dashboard|administraciones_medicacion.ver|prescripciones.ver')
            ->name('medicacion');
    },
    'ruta_236' => static function (): void {
        Route::get('/residentes/{residente}/medicacion', [MedicacionController::class, 'index'])->middleware('permission:prescripciones.ver')->name('medicacion.index');
    },
    'ruta_237' => static function (): void {
        Route::post('/residentes/{residente}/prescripciones', [MedicacionController::class, 'prescribir'])->middleware('permission:prescripciones.crear')->name('prescripciones.store');
    },
    'ruta_238' => static function (): void {
        Route::patch('/prescripciones/{prescripcion}/suspender', [MedicacionController::class, 'suspender'])->middleware('permission:prescripciones.suspender')->name('prescripciones.suspender');
    },
    'ruta_239' => static function (): void {
        Route::post('/prescripciones/{prescripcion}/administraciones', [MedicacionController::class, 'administrar'])->middleware('permission:administraciones_medicacion.crear')->name('administraciones.store');
    },
];
