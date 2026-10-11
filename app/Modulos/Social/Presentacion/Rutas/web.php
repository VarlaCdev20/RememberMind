<?php

use App\Modulos\Social\Presentacion\Http\Controllers\ActividadController;
use App\Modulos\Social\Presentacion\Http\Controllers\AdultoMayorActividadController;
use App\Modulos\Social\Presentacion\Http\Controllers\AdultoMayorFamiliarController;
use App\Modulos\Social\Presentacion\Http\Controllers\ResumenFamiliaSocialController;
use App\Modulos\Social\Presentacion\Livewire\Paginas\ActividadesPanel;
use App\Modulos\Social\Presentacion\Livewire\Paginas\AsistenciaPanel;
use App\Modulos\Social\Presentacion\Livewire\Paginas\ParticipacionPanel;
use App\Modulos\Social\Presentacion\Livewire\Paginas\RedApoyoPanel;
use App\Modulos\Social\Presentacion\Livewire\Paginas\TiposActividadPanel;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_052' => static function (): void {
        Route::get('/familiares', [AdultoMayorFamiliarController::class, 'index'])->middleware('permission:residentes_contactos.ver')->name('familiares.index');
    },
    'ruta_053' => static function (): void {
        Route::post('/familiares', [AdultoMayorFamiliarController::class, 'store'])->middleware('permission:residentes_contactos.gestionar')->name('familiares.store');
    },
    'ruta_054' => static function (): void {
        Route::patch('/familiares/{familiar}', [AdultoMayorFamiliarController::class, 'update'])->middleware('permission:residentes_contactos.gestionar')->name('familiares.update');
    },
    'ruta_055' => static function (): void {
        Route::delete('/familiares/{familiar}', [AdultoMayorFamiliarController::class, 'destroy'])->middleware('permission:residentes_contactos.gestionar')->name('familiares.destroy');
    },
    'ruta_056' => static function (): void {
        Route::patch('/familiares/{familiar}/restaurar', [AdultoMayorFamiliarController::class, 'restore'])->middleware('permission:residentes_contactos.gestionar')->name('familiares.restore');
    },
    'ruta_074' => static function (): void {
        Route::post('/actividades', [AdultoMayorActividadController::class, 'store'])->middleware('permission:actividades.gestionar')->name('actividades.store');
    },
    'ruta_075' => static function (): void {
        Route::patch('/actividades/{actividad}', [AdultoMayorActividadController::class, 'update'])->middleware('permission:actividades.gestionar')->name('actividades.update');
    },
    'ruta_076' => static function (): void {
        Route::delete('/actividades/{actividad}', [AdultoMayorActividadController::class, 'destroy'])->middleware('permission:actividades.gestionar')->name('actividades.destroy');
    },
    'ruta_077' => static function (): void {
        Route::patch('/actividades/{actividad}/restaurar', [AdultoMayorActividadController::class, 'restore'])->middleware('permission:actividades.gestionar')->name('actividades.restore');
    },
    'ruta_122' => static function (): void {
        Route::get('/resumen', ResumenFamiliaSocialController::class)->name('resumen');
    },
    'ruta_123' => static function (): void {
        Route::get('/red-apoyo', RedApoyoPanel::class)->name('red-apoyo');
    },
    'ruta_137' => static function (): void {
        Route::get('/', ActividadesPanel::class)->name('index');
    },
    'ruta_138' => static function (): void {
        Route::get('/tipos', TiposActividadPanel::class)->name('tipos');
    },
    'ruta_139' => static function (): void {
        Route::get('/participacion', ParticipacionPanel::class)->name('participacion');
    },
    'ruta_140' => static function (): void {
        Route::get('/asistencia', AsistenciaPanel::class)->name('asistencia');
    },
    'ruta_243' => static function (): void {
        Route::post('/actividades', [ActividadController::class, 'store'])->middleware('permission:actividades.gestionar')->name('actividades.store');
    },
    'ruta_244' => static function (): void {
        Route::post('/actividades/{actividad}/participantes', [ActividadController::class, 'participante'])->middleware('permission:actividades.gestionar')->name('actividades.participantes');
    },
    'ruta_121' => static function (): void {
        Route::redirect('/', '/admin/familia-social/resumen')->name('index');
    },
    'ruta_124' => static function (): void {
        Route::view('/visitas', 'pages.familia-social.base', [
            'titulo' => 'Visitas',
            'descripcion' => 'Visitas familiares, sociales y acompañamiento presencial.',
            'icono' => 'ph-hand-heart',
        ])->name('visitas');
    },
    'ruta_125' => static function (): void {
        Route::view('/ficha-social', 'pages.familia-social.base', [
            'titulo' => 'Ficha social',
            'descripcion' => 'Situacion social, convivencia, red de apoyo real y observaciones sociales.',
            'icono' => 'ph-clipboard-text',
        ])->name('ficha-social');
    },
];
