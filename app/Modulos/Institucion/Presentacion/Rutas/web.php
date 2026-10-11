<?php

use App\Modulos\Institucion\Presentacion\Http\Controllers\AreasAtencionController;
use App\Modulos\Institucion\Presentacion\Http\Controllers\BuscarAdministracionController;
use App\Modulos\Institucion\Presentacion\Http\Controllers\CentroCoordinacionController;
use App\Modulos\Institucion\Presentacion\Http\Controllers\InfraestructuraController;
use App\Modulos\Institucion\Presentacion\Http\Controllers\InstitucionalController;
use App\Modulos\Institucion\Presentacion\Http\Controllers\OperacionController;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\AsignacionTurnoPanel;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\TurnosEnfermeriaPanel;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_130' => static function (): void {
        Route::get('/', AsignacionTurnoPanel::class)->name('index');
    },

    'ruta_127' => static function (): void {
        Route::get('/', TurnosEnfermeriaPanel::class)->name('index');
    },

    'ruta_005' => static function (): void {
        Route::get('/dashboard', CentroCoordinacionController::class)
            ->name('dashboard');
    },
    'ruta_006' => static function (): void {
        Route::get('/', CentroCoordinacionController::class)
            ->name('index');
    },
    'ruta_008' => static function (): void {
        Route::get('/buscar', BuscarAdministracionController::class)
            ->name('buscar');
    },
    'ruta_009' => static function (string $modulo): void {
        Route::get('/'.$modulo, [OperacionController::class, 'index'])
            ->defaults('modulo', $modulo)->name($modulo);
    },
    'ruta_010' => static function (): void {
        Route::get('vistas-extra', function () {
            abort_unless(auth()->user()?->hasRole('SUPERADMINISTRADOR'), 403);

            return view('pages.admin.vistas-extra');
        })->name('vistas-extra');
    },
    'ruta_034' => static function (): void {
        Route::get('/areas-atencion', [AreasAtencionController::class, 'index'])
            ->middleware('permission:areas.ver')
            ->name('areas-atencion.index');
    },
    'ruta_207' => static function (): void {
        Route::get('/infraestructura', [InfraestructuraController::class, 'index'])->middleware('permission:habitaciones.ver')->name('infraestructura.index');
    },
    'ruta_208' => static function (): void {
        Route::post('/habitaciones', [InfraestructuraController::class, 'habitacion'])->middleware('permission:habitaciones.gestionar')->name('habitaciones.store');
    },
    'ruta_209' => static function (): void {
        Route::post('/habitaciones/{habitacion}/camas', [InfraestructuraController::class, 'cama'])->middleware('permission:camas.gestionar')->name('camas.store');
    },
    'ruta_210' => static function (): void {
        Route::get('/usuarios', [InstitucionalController::class, 'usuarios'])->middleware('permission:usuarios.ver')->name('usuarios');
    },
    'ruta_211' => static function (): void {
        Route::post('/usuarios', [InstitucionalController::class, 'guardarUsuario'])->middleware('permission:usuarios.gestionar')->name('usuarios.store');
    },
    'ruta_212' => static function (): void {
        Route::post('/personal', [InstitucionalController::class, 'guardarPersonal'])->middleware('permission:personal.gestionar')->name('personal.store');
    },
    'ruta_213' => static function (): void {
        Route::post('/areas', [InstitucionalController::class, 'guardarArea'])->middleware('permission:areas.gestionar')->name('areas.store');
    },
    'ruta_214' => static function (): void {
        Route::post('/turnos', [InstitucionalController::class, 'guardarTurno'])->middleware('permission:turnos.gestionar')->name('turnos.store');
    },
    'ruta_215' => static function (): void {
        Route::post('/jornadas', [InstitucionalController::class, 'abrirJornada'])->middleware('permission:jornadas.gestionar')->name('jornadas.store');
    },
    'ruta_216' => static function (): void {
        Route::post('/jornadas/{jornada}/personal', [InstitucionalController::class, 'asignarPersonal'])->middleware('permission:asignaciones_personal.gestionar')->name('jornadas.personal');
    },
    'ruta_035' => static function (): void {
        Route::view('/areas-institucionales', 'pages.areas-institucionales.index')
            ->middleware('permission:areas.ver')
            ->name('areas-institucionales.index');
    },
    'ruta_036' => static function (): void {
        Route::view('/turnos-asignaciones', 'pages.turnos-asignaciones.index')
            ->middleware('permission:turnos.ver')
            ->name('turnos-asignaciones.index');
    },
    'ruta_126' => static function (): void {
        Route::get('/', fn () => redirect()->route('admin.administracion.habitaciones'))->name('index');
    },
];
