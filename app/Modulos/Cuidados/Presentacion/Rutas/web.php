<?php

use App\Modulos\Cuidados\Presentacion\Http\Controllers\CuidadoController;
use App\Modulos\Cuidados\Presentacion\Http\Controllers\FotografiaHeridaController;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\AgendaEnfermeria;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\CentralCuidados;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\DashboardTurno;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\MisPacientes;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\PaseTurnoPanel;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\PlanCuidadoPanel;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\RegistrosEnfermeria;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\SeguimientoDiarioPanel;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\TareasPlanPanel;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_131' => static function (): void {
        Route::get('/', PlanCuidadoPanel::class)->name('index');
    },
    'ruta_132' => static function (): void {
        Route::get('/tareas', TareasPlanPanel::class)
            ->middleware('permission:ejecuciones_cuidado.ver')
            ->name('tareas');
    },
    'ruta_134' => static function (): void {
        Route::get('/', SeguimientoDiarioPanel::class)->name('index');
    },
    'ruta_136' => static function (): void {
        Route::get('/', PaseTurnoPanel::class)->name('index');
    },
    'ruta_142' => static function (): void {
        Route::get('/dashboard', DashboardTurno::class)
            ->middleware('permission:enfermeria.ver_dashboard')
            ->name('dashboard');
    },
    'ruta_143' => static function (): void {
        Route::get('/agenda', AgendaEnfermeria::class)
            ->middleware('permission:enfermeria.ver_dashboard')
            ->name('agenda');
    },
    'ruta_144' => static function (): void {
        Route::get('/cuidados', CentralCuidados::class)
            ->middleware('permission:enfermeria.ver_dashboard')
            ->name('cuidados');
        Route::get('/registros', RegistrosEnfermeria::class)
            ->middleware('permission:atenciones.ver')
            ->name('registros');
    },
    'ruta_145' => static function (): void {
        Route::get('/seguimiento', SeguimientoDiarioPanel::class)
            ->middleware('permission:atenciones.ver')
            ->name('seguimiento');
    },
    'ruta_147' => static function (): void {
        Route::get('/pacientes', MisPacientes::class)
            ->middleware('permission:enfermeria.ver_pacientes_asignados')
            ->name('pacientes');
    },
    'ruta_151' => static function (): void {
        Route::get('/tareas', TareasPlanPanel::class)
            ->middleware('permission:ejecuciones_cuidado.ver')
            ->name('tareas');
    },
    'ruta_154' => static function (): void {
        Route::get('/pase-turno', PaseTurnoPanel::class)
            ->middleware('permission:pases_turno.ver')
            ->name('pase-turno');
    },
    'ruta_227' => static function (): void {
        Route::get('/residentes/{residente}/cuidados', [CuidadoController::class, 'index'])->middleware('permission:planes_cuidado.ver')->name('cuidados.index');
    },
    'ruta_228' => static function (): void {
        Route::post('/residentes/{residente}/cuidados/{tipo}', [CuidadoController::class, 'registrar'])
            ->middleware('permission:controles_cognitivos.crear|registros_conductuales.crear|registros_sueno.crear|registros_ingesta.crear|registros_hidratacion.crear|registros_eliminacion.crear|registros_movilidad.crear|heridas.crear')
            ->name('cuidados.store');
    },
    'ruta_229' => static function (): void {
        Route::post('/residentes/{residente}/planes', [CuidadoController::class, 'crearPlan'])->middleware('permission:planes_cuidado.crear')->name('planes.store');
    },
    'ruta_230' => static function (): void {
        Route::post('/residentes/{residente}/asignaciones-jornada', [CuidadoController::class, 'asignarJornada'])->middleware('permission:asignaciones_residente_jornada.gestionar')->name('cuidados.asignaciones.store');
    },
    'ruta_231' => static function (): void {
        Route::post('/residentes/{residente}/pases-turno', [CuidadoController::class, 'registrarPase'])->middleware('permission:pases_turno.crear')->name('cuidados.pases.store');
    },
    'ruta_232' => static function (): void {
        Route::get('/residentes/{residente}/fotografias-heridas/{documentoClinico}', FotografiaHeridaController::class)
            ->name('cuidados.fotografias.ver');
        Route::post('/heridas/{herida}/curaciones', [CuidadoController::class, 'curarHerida'])->middleware('permission:curaciones_herida.crear')->name('cuidados.curaciones.store');
    },
    'ruta_233' => static function (): void {
        Route::post('/planes/{plan}/intervenciones', [CuidadoController::class, 'crearIntervencion'])->middleware('permission:planes_cuidado.crear')->name('intervenciones.store');
    },
    'ruta_234' => static function (): void {
        Route::post('/intervenciones/{intervencion}/programaciones', [CuidadoController::class, 'programar'])->middleware('permission:planes_cuidado.crear')->name('programaciones.store');
    },
    'ruta_235' => static function (): void {
        Route::post('/intervenciones/{intervencion}/ejecuciones', [CuidadoController::class, 'ejecutar'])->middleware('permission:ejecuciones_cuidado.crear')->name('ejecuciones.store');
    },
];
