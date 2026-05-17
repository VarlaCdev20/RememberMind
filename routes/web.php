<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\AdultoMayorController;

Route::get('/', function () {
    return view('welcome');
});

// ──────────────────────────────────────────────
// RUTAS AUTENTICADAS
// ──────────────────────────────────────────────
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {

            // ── Usuarios ─────────────────────────
            Route::resource('usuarios', UsuarioController::class)->middleware('permission:usuarios.ver');

            Route::patch('usuarios/{usuario}/desactivar', [UsuarioController::class, 'desactivar'])
                ->middleware('permission:usuarios.cambiar_estado')
                ->name('usuarios.desactivar');

            Route::patch('usuarios/{usuario}/activar', [UsuarioController::class, 'activar'])
                ->middleware('permission:usuarios.cambiar_estado')
                ->name('usuarios.activar');

            // ── Roles y Permisos ─────────────────
            Route::view('/roles-permisos', 'admin.roles-permisos.index')
                ->middleware('permission:roles.ver')
                ->name('roles-permisos.index');

            // ── Adultos Mayores ──────────────────
            Route::resource('adultos-mayores', AdultoMayorController::class)
                ->middleware('permission:adultos.ver')
                ->parameters([
                    'adultos-mayores' => 'adulto_mayor'
                ]);

            Route::patch('adultos-mayores/{adulto_mayor}/archivar', [AdultoMayorController::class, 'archivar'])
                ->middleware('permission:adultos.archivar')
                ->name('adultos-mayores.archivar');

            Route::patch('adultos-mayores/{adulto_mayor}/restaurar', [AdultoMayorController::class, 'restaurar'])
                ->middleware('permission:adultos.restaurar')
                ->name('adultos-mayores.restaurar');

            Route::patch('adultos-mayores/{adulto_mayor}/estado', [AdultoMayorController::class, 'cambiarEstado'])
                ->middleware('permission:adultos.cambiar_estado')
                ->name('adultos-mayores.estado');

            // ── Submódulos Adultos Mayores ────────
            Route::prefix('adultos-mayores/{adulto_mayor}')
                ->middleware('permission:adultos.ver')
                ->name('adultos-mayores.')
                ->group(function () {
                // Familiares
                Route::get('familiares', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorFamiliarController::class, 'index'])->name('familiares.index');
                Route::post('familiares', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorFamiliarController::class, 'store'])->name('familiares.store');
                Route::patch('familiares/{familiar}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorFamiliarController::class, 'update'])->name('familiares.update');
                Route::delete('familiares/{familiar}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorFamiliarController::class, 'destroy'])->name('familiares.destroy');
                Route::patch('familiares/{familiar}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorFamiliarController::class, 'restore'])->name('familiares.restore');

                // Observaciones
                Route::get('observaciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorObservacionController::class, 'index'])->name('observaciones.index');
                Route::post('observaciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorObservacionController::class, 'store'])->name('observaciones.store');
                Route::patch('observaciones/{observacion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorObservacionController::class, 'update'])->name('observaciones.update');
                Route::delete('observaciones/{observacion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorObservacionController::class, 'destroy'])->name('observaciones.destroy');
                Route::patch('observaciones/{observacion}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorObservacionController::class, 'restore'])->name('observaciones.restore');

                // Atenciones
                Route::get('atenciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorAtencionController::class, 'index'])->name('atenciones.index');
                Route::post('atenciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorAtencionController::class, 'store'])->name('atenciones.store');
                Route::patch('atenciones/{atencion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorAtencionController::class, 'update'])->name('atenciones.update');
                Route::delete('atenciones/{atencion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorAtencionController::class, 'destroy'])->name('atenciones.destroy');
                Route::patch('atenciones/{atencion}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorAtencionController::class, 'restore'])->name('atenciones.restore');

                // Evaluaciones Cognitivas
                Route::get('evaluaciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorEvaluacionController::class, 'index'])->name('evaluaciones.index');
                Route::post('evaluaciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorEvaluacionController::class, 'store'])->name('evaluaciones.store');
                Route::get('evaluaciones/{evaluacion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorEvaluacionController::class, 'show'])->name('evaluaciones.show');
                Route::delete('evaluaciones/{evaluacion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorEvaluacionController::class, 'destroy'])->name('evaluaciones.destroy');
                Route::patch('evaluaciones/{evaluacion}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorEvaluacionController::class, 'restore'])->name('evaluaciones.restore');

                // Actividades
                Route::post('actividades', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorActividadController::class, 'store'])->name('actividades.store');
                Route::patch('actividades/{actividad}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorActividadController::class, 'update'])->name('actividades.update');
                Route::delete('actividades/{actividad}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorActividadController::class, 'destroy'])->name('actividades.destroy');
                Route::patch('actividades/{actividad}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorActividadController::class, 'restore'])->name('actividades.restore');

                // Documentos
                Route::post('documentos', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorDocumentoController::class, 'store'])->name('documentos.store');
                Route::patch('documentos/{documento}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorDocumentoController::class, 'update'])->name('documentos.update');
                Route::delete('documentos/{documento}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorDocumentoController::class, 'destroy'])->name('documentos.destroy');
                Route::patch('documentos/{documento}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorDocumentoController::class, 'restore'])->name('documentos.restore');

                // FASE 3: Módulos Médicos y Administrativos
                // Ficha Médica
                Route::post('ficha-medica', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorFichaMedicaController::class, 'store'])->name('ficha-medica.store');
                Route::put('ficha-medica/{ficha}', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorFichaMedicaController::class, 'update'])->name('ficha-medica.update');
                Route::patch('ficha-medica/{ficha}/archivar', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorFichaMedicaController::class, 'archivar'])->name('ficha-medica.archivar');
                Route::patch('ficha-medica/{ficha}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorFichaMedicaController::class, 'restore'])->name('ficha-medica.restore');

                // Medicación
                Route::post('medicacion', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'store'])->name('medicacion.store');
                Route::put('medicacion/{medicacion}', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'update'])->name('medicacion.update');
                Route::patch('medicacion/{medicacion}/suspender', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'suspender'])->name('medicacion.suspender');
                Route::patch('medicacion/{medicacion}/finalizar', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'finalizar'])->name('medicacion.finalizar');
                Route::patch('medicacion/{medicacion}/archivar', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'archivar'])->name('medicacion.archivar');
                Route::patch('medicacion/{medicacion}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'restore'])->name('medicacion.restore');

                // Administración de Medicación
                Route::post('administracion-medicacion', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorAdministracionMedicacionController::class, 'store'])->name('administracion-medicacion.store');

                // Signos Vitales
                Route::post('signos-vitales', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorSignosVitalesController::class, 'store'])->name('signos-vitales.store');
                Route::put('signos-vitales/{signo}', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorSignosVitalesController::class, 'update'])->name('signos-vitales.update');

                // Valoración Funcional
                Route::post('valoracion-funcional', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorValoracionFuncionalController::class, 'store'])->name('valoracion-funcional.store');
                Route::put('valoracion-funcional/{valoracion}', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorValoracionFuncionalController::class, 'update'])->name('valoracion-funcional.update');

                // Reporte individual (anidado bajo adulto_mayor)
                Route::get('reporte-individual', [AdultoMayorController::class, 'reporteIndividual'])->name('reporte-individual');
                
                // Reportes específicos (médico, medicación, vitales, etc.)
                Route::get('reportes/{tipo}', [AdultoMayorController::class, 'reporteEspecifico'])->name('reportes.especifico');
            });

            // ── Reportes protegidos ──────────────
            Route::get('reporte-general', [AdultoMayorController::class, 'reporteGeneral'])
                ->middleware('permission:reportes.ver')
                ->name('adultos-mayores.reporte-general');
            Route::get('reporte-institucional', [AdultoMayorController::class, 'reporteInstitucional'])
                ->middleware('permission:reportes.ver')
                ->name('adultos-mayores.reporte-institucional');
            Route::get('reporte-bienestar', [AdultoMayorController::class, 'reporteBienestar'])
                ->middleware('permission:reportes.ver')
                ->name('adultos-mayores.reporte-bienestar');

            // ── Bitácora ─────────────────────────
            Route::get('bitacora', [\App\Http\Controllers\Admin\BitacoraController::class, 'index'])
                ->middleware('permission:bitacora.ver')
                ->name('bitacora.index');
        });
});