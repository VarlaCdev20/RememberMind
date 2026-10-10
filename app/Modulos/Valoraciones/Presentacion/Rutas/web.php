<?php

use App\Modulos\Valoraciones\Presentacion\Http\Controllers\AdultoMayorEvaluacionController;
use App\Modulos\Valoraciones\Presentacion\Http\Controllers\AdultoMayorValoracionFuncionalController;
use App\Modulos\Valoraciones\Presentacion\Http\Controllers\InstrumentoController;
use App\Modulos\Valoraciones\Presentacion\Http\Controllers\ValoracionProfesionalController;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\DashboardPsicologo;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\EvaluacionesAreaPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\SaludEvaluacionesGeriatricasPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\SaludValoracionPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\ValoracionEnfermeriaPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\ValoracionMedicaPanel;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_067' => static function (): void {
        Route::get('/evaluaciones', [AdultoMayorEvaluacionController::class, 'index'])->middleware('permission:aplicaciones_instrumento.ver')->name('evaluaciones.index');
    },
    'ruta_068' => static function (): void {
        Route::post('/evaluaciones', [AdultoMayorEvaluacionController::class, 'store'])->middleware('permission:aplicaciones_instrumento.crear')->name('evaluaciones.store');
    },
    'ruta_069' => static function (): void {
        Route::get('/evaluaciones/{evaluacion}', [AdultoMayorEvaluacionController::class, 'show'])->middleware('permission:aplicaciones_instrumento.ver')->name('evaluaciones.show');
    },
    'ruta_070' => static function (): void {
        Route::delete('/evaluaciones/{evaluacion}', [AdultoMayorEvaluacionController::class, 'destroy'])->middleware('permission:aplicaciones_instrumento.anular')->name('evaluaciones.destroy');
    },
    'ruta_071' => static function (): void {
        Route::patch('/evaluaciones/{evaluacion}/restaurar', [AdultoMayorEvaluacionController::class, 'restore'])->middleware('permission:aplicaciones_instrumento.editar')->name('evaluaciones.restore');
    },
    'ruta_097' => static function (): void {
        Route::post('/valoracion-funcional', [AdultoMayorValoracionFuncionalController::class, 'store'])->middleware('permission:valoraciones_funcionales.crear')->name('valoracion-funcional.store');
    },
    'ruta_098' => static function (): void {
        Route::put('/valoracion-funcional/{valoracion}', [AdultoMayorValoracionFuncionalController::class, 'update'])->middleware('permission:valoraciones_funcionales.editar')->name('valoracion-funcional.update');
    },
    'ruta_119' => static function (): void {
        Route::get('/{adulto}/valoracion', SaludValoracionPanel::class)->name('valoracion');
    },
    'ruta_120' => static function (): void {
        Route::get('/{adulto}/evaluaciones-geriatricas', SaludEvaluacionesGeriatricasPanel::class)->name('evaluaciones-geriatricas');
    },
    'ruta_128' => static function (): void {
        Route::get('/valoracion-enfermeria', ValoracionEnfermeriaPanel::class)
            ->middleware('permission:valoracion_enfermeria.ver')
            ->name('valoracion-enfermeria');
    },
    'ruta_129' => static function (): void {
        Route::get('/valoracion-medica', ValoracionMedicaPanel::class)
            ->middleware('permission:valoracion_medica.ver')
            ->name('valoracion-medica');
    },
    'ruta_163' => static function (): void {
        Route::get('/valoraciones-medicas', ValoracionMedicaPanel::class)
            ->name('valoraciones');
    },
    'ruta_173' => static function (): void {
        Route::get('/dashboard', DashboardPsicologo::class)->name('dashboard');
    },
    'ruta_174' => static function (): void {
        Route::get('/evaluaciones-asignadas', DashboardPsicologo::class)->name('evaluaciones');
    },
    'ruta_175' => static function (): void {
        Route::get('/evaluacion/cognitiva', EvaluacionesAreaPanel::class)
            ->defaults('codArea', 'ARE_COG')
            ->name('evaluacion.cognitiva');
    },
    'ruta_176' => static function (): void {
        Route::get('/evaluacion/afectiva', EvaluacionesAreaPanel::class)
            ->defaults('codArea', 'ARE_AFE')
            ->name('evaluacion.afectiva');
    },
    'ruta_177' => static function (): void {
        Route::get('/evaluacion/funcionamiento', EvaluacionesAreaPanel::class)
            ->defaults('codArea', 'ARE_FUN')
            ->name('evaluacion.funcionamiento');
    },
    'ruta_178' => static function (): void {
        Route::get('/evaluacion/nutricional', EvaluacionesAreaPanel::class)
            ->defaults('codArea', 'ARE_NUT')
            ->name('evaluacion.nutricional');
    },
    'ruta_179' => static function (): void {
        Route::get('/evaluacion/entorno', EvaluacionesAreaPanel::class)
            ->defaults('codArea', 'ARE_SOC')
            ->name('evaluacion.entorno');
    },
    'ruta_240' => static function (): void {
        Route::get('/instrumentos', [InstrumentoController::class, 'index'])->middleware('permission:instrumentos.ver')->name('instrumentos.index');
    },
    'ruta_241' => static function (): void {
        Route::post('/instrumentos/{instrumento}/residentes/{residente}', [InstrumentoController::class, 'aplicar'])->middleware('permission:aplicaciones_instrumento.crear')->name('instrumentos.aplicar');
    },
    'ruta_242' => static function (): void {
        Route::post('/residentes/{residente}/valoraciones/{tipo}', [ValoracionProfesionalController::class, 'registrar'])
            ->middleware('permission:valoraciones_psicologicas.crear|valoraciones_nutricionales.crear|valoraciones_funcionales.crear|seguimientos_pedagogicos.crear')
            ->name('valoraciones.store');
    },
];
