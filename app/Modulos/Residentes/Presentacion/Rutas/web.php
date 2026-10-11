<?php

use App\Modulos\Residentes\Presentacion\Http\Controllers\AdultoMayorController;
use App\Modulos\Residentes\Presentacion\Http\Controllers\AdultoMayorDocumentoController;
use App\Modulos\Residentes\Presentacion\Http\Controllers\DocumentoController;
use App\Modulos\Residentes\Presentacion\Http\Controllers\RelacionResidenteController;
use App\Modulos\Residentes\Presentacion\Http\Controllers\ResidenteAdministrativoController;
use App\Modulos\Residentes\Presentacion\Http\Controllers\ResidenteController;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_007' => static function (): void {
        Route::get('/residentes/{residente}', [ResidenteAdministrativoController::class, 'show'])
            ->middleware('permission:residentes.ver')->name('residentes.show');
    },
    'ruta_046' => static function (): void {
        Route::resource('adultos-mayores', AdultoMayorController::class)
            ->only(['index', 'show'])
            ->middleware('permission:residentes.ver')
            ->parameters([
                'adultos-mayores' => 'adulto_mayor',
            ]);
    },
    'ruta_047' => static function (): void {
        Route::resource('adultos-mayores', AdultoMayorController::class)
            ->only(['edit'])
            ->middleware('permission:residentes.ver')
            ->parameters([
                'adultos-mayores' => 'adulto_mayor',
            ]);
    },
    'ruta_048' => static function (): void {
        Route::resource('adultos-mayores', AdultoMayorController::class)
            ->only(['update'])
            ->middleware('permission:residentes.gestionar')
            ->parameters([
                'adultos-mayores' => 'adulto_mayor',
            ]);
    },
    'ruta_049' => static function (): void {
        Route::patch('adultos-mayores/{adulto_mayor}/archivar', [AdultoMayorController::class, 'archivar'])
            ->middleware('permission:residentes.gestionar')
            ->name('adultos-mayores.archivar');
    },
    'ruta_050' => static function (): void {
        Route::patch('adultos-mayores/{adulto_mayor}/restaurar', [AdultoMayorController::class, 'restaurar'])
            ->middleware('permission:residentes.gestionar')
            ->name('adultos-mayores.restaurar');
    },
    'ruta_051' => static function (): void {
        Route::patch('adultos-mayores/{adulto_mayor}/estado', [AdultoMayorController::class, 'cambiarEstado'])
            ->middleware('permission:residentes.gestionar')
            ->name('adultos-mayores.estado');
    },
    'ruta_072' => static function (): void {
        Route::delete('/evaluaciones-geriatricas/{evaluacion}/anular', [AdultoMayorController::class, 'anularEvaluacionGeriatrica'])
            ->middleware('permission:aplicaciones_instrumento.anular')
            ->name('evaluaciones-geriatricas.anular');
    },
    'ruta_073' => static function (): void {
        Route::get('/evaluaciones-geriatricas/{evaluacion}/pdf', [AdultoMayorController::class, 'pdfEvaluacionGeriatrica'])
            ->middleware('permission:reportes.individual')
            ->name('evaluaciones-geriatricas.pdf');
    },
    'ruta_078' => static function (): void {
        Route::get('/documentos', [AdultoMayorDocumentoController::class, 'index'])->middleware('permission:documentos.ver')->name('documentos.index');
    },
    'ruta_079' => static function (): void {
        Route::get('/documentos/{documento}/archivo', [AdultoMayorDocumentoController::class, 'archivo'])->middleware('permission:documentos.ver')->name('documentos.archivo');
    },
    'ruta_080' => static function (): void {
        Route::post('/documentos', [AdultoMayorDocumentoController::class, 'store'])->middleware('permission:documentos.gestionar')->name('documentos.store');
    },
    'ruta_081' => static function (): void {
        Route::patch('/documentos/{documento}', [AdultoMayorDocumentoController::class, 'update'])->middleware('permission:documentos.gestionar')->name('documentos.update');
    },
    'ruta_082' => static function (): void {
        Route::delete('/documentos/{documento}', [AdultoMayorDocumentoController::class, 'destroy'])->middleware('permission:documentos.gestionar')->name('documentos.destroy');
    },
    'ruta_083' => static function (): void {
        Route::patch('/documentos/{documento}/restaurar', [AdultoMayorDocumentoController::class, 'restore'])->middleware('permission:documentos.gestionar')->name('documentos.restore');
    },
    'ruta_099' => static function (): void {
        Route::get('/reporte-individual', [AdultoMayorController::class, 'reporteIndividual'])
            ->middleware('permission:reportes.individual')
            ->name('reporte-individual');
    },
    'ruta_100' => static function (): void {
        Route::get('/reportes/{tipo}', [AdultoMayorController::class, 'reporteEspecifico'])
            ->middleware('permission:reportes.individual')
            ->name('reportes.especifico');
    },
    'ruta_101' => static function (): void {
        Route::get('reporte-general', [AdultoMayorController::class, 'reporteGeneral'])
            ->middleware('permission:reportes.ver')
            ->name('adultos-mayores.reporte-general');
    },
    'ruta_103' => static function (): void {
        Route::get('reporte-bienestar', [AdultoMayorController::class, 'reporteBienestar'])
            ->middleware('permission:reportes.ver')
            ->name('adultos-mayores.reporte-bienestar');
    },
    'ruta_201' => static function (): void {
        Route::get('/residentes', [ResidenteController::class, 'index'])->middleware('can:viewAny,App\\Models\\Residente')->name('residentes.index');
    },
    'ruta_202' => static function (): void {
        Route::get('/residentes/{residente}', [ResidenteController::class, 'show'])->middleware('can:view,residente')->name('residentes.show');
    },
    'ruta_248' => static function (): void {
        Route::post('/residentes/{residente}/documentos', [DocumentoController::class, 'store'])->middleware('permission:documentos.gestionar')->name('documentos.store');
    },
    'ruta_249' => static function (): void {
        Route::get('/residentes/{residente}/relaciones', [RelacionResidenteController::class, 'index'])->middleware('permission:residentes_contactos.ver')->name('residentes.relaciones');
    },
    'ruta_250' => static function (): void {
        Route::post('/residentes/{residente}/contactos', [RelacionResidenteController::class, 'vincularContacto'])->middleware('permission:contactos.gestionar')->name('residentes.contactos.store');
    },
    'ruta_251' => static function (): void {
        Route::post('/residentes/{residente}/visitas', [RelacionResidenteController::class, 'registrarVisita'])->middleware('permission:visitas.gestionar')->name('residentes.visitas.store');
    },
    'ruta_252' => static function (): void {
        Route::post('/residentes/{residente}/consentimientos', [RelacionResidenteController::class, 'registrarConsentimiento'])->middleware('permission:consentimientos.gestionar')->name('residentes.consentimientos.store');
    },
    'ruta_253' => static function (): void {
        Route::get('/documentos/{documento}/descargar', [DocumentoController::class, 'descargar'])->middleware('permission:documentos.ver')->name('documentos.descargar');
    },
];
