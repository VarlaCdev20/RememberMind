<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\AdultoMayorController;
use App\Http\Controllers\Admin\Reportes\ReporteInstitucionalController;
use App\Http\Controllers\Admin\Reportes\ReporteAdultosController;
use App\Http\Controllers\Admin\Reportes\ReporteSaludController;
use App\Http\Controllers\Admin\Reportes\ReporteFamiliaresController;
use App\Http\Controllers\Admin\Reportes\ReporteEquipoController;
use App\Http\Controllers\Admin\Reportes\ReporteActividadesController;
use App\Http\Controllers\Admin\Reportes\ReporteBitacoraController;
use App\Http\Controllers\Admin\FamiliaSocial\ResumenFamiliaSocialController;

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

            Route::get('usuarios/{usuario}/ficha/pdf', [UsuarioController::class, 'fichaPdf'])
                ->middleware('permission:usuarios.reportes.pdf')
                ->name('usuarios.ficha.pdf');

            Route::get('usuarios/{usuario}/documentacion/pdf', [UsuarioController::class, 'documentacionPdf'])
                ->middleware('permission:usuarios.reportes.pdf')
                ->name('usuarios.documentacion.pdf');

            Route::prefix('usuarios/{user}/documentos')
                ->name('usuarios.documentos.')
                ->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\Usuarios\DocumentosUsuarioController::class, 'preview'])->name('preview');
                    Route::get('/pdf', [\App\Http\Controllers\Admin\Usuarios\DocumentosUsuarioController::class, 'paquetePdf'])->name('pdf');
                    Route::post('/enviar', [\App\Http\Controllers\Admin\Usuarios\DocumentosUsuarioController::class, 'enviarPaqueteCorreo'])->name('enviar');
                    Route::get('/{documento}/ver', [\App\Http\Controllers\Admin\Usuarios\DocumentosUsuarioController::class, 'verDocumento'])->name('ver');
                    Route::get('/{documento}/pdf', [\App\Http\Controllers\Admin\Usuarios\DocumentosUsuarioController::class, 'pdfDocumento'])->name('documento-pdf');
                    Route::get('/{documento}/imprimir', [\App\Http\Controllers\Admin\Usuarios\DocumentosUsuarioController::class, 'imprimirDocumento'])->name('imprimir');
                });

            Route::get('usuarios/{usuario}/solicitud-documental/pdf', [UsuarioController::class, 'solicitudDocumentalPdf'])
                ->middleware('permission:usuarios.ver')
                ->name('usuarios.solicitud-documental.pdf');

            Route::get('usuarios/{usuario}/horarios/pdf', [UsuarioController::class, 'horariosPdf'])
                ->middleware('permission:usuarios.reportes.pdf')
                ->name('usuarios.horarios.pdf');

            Route::post('usuarios/{usuario}/ficha/enviar-correo', [UsuarioController::class, 'enviarFichaCorreo'])
                ->middleware('permission:usuarios.ver')
                ->name('usuarios.ficha.enviar-correo');

            // ── Personal Institucional ─────────────
            Route::get('/personal-institucional', \App\Livewire\Admin\PersonalInstitucional\PersonalInstitucionalPanel::class)
                ->middleware('permission:personal_institucional.ver')
                ->name('personal-institucional');

            // ── Roles y Permisos ─────────────────
            Route::view('/roles-permisos', 'admin.roles-permisos.index')
                ->middleware('permission:roles.ver')
                ->name('roles-permisos.index');

            // ── Áreas Institucionales ─────────────
            Route::view('/areas-institucionales', 'admin.areas-institucionales.index')
                ->middleware('permission:areas.ver')
                ->name('areas-institucionales.index');

            // ── Turnos y Asignaciones ─────────────
            Route::view('/turnos-asignaciones', 'admin.turnos-asignaciones.index')
                ->middleware('permission:turnos.ver')
                ->name('turnos-asignaciones.index');

            // ── Reportes de Áreas Institucionales ─
            Route::prefix('areas-institucionales/reportes')
                ->name('areas-institucionales.reportes.')
                ->middleware('permission:areas.reportes')
                ->group(function () {
                    Route::get('general/pdf', [\App\Http\Controllers\Admin\AreasInstitucionales\AreaReporteController::class, 'generalPdf'])
                        ->name('general.pdf');
                    Route::get('general/excel', [\App\Http\Controllers\Admin\AreasInstitucionales\AreaReporteController::class, 'generalExcel'])
                        ->name('general.excel');
                    Route::get('general/csv', [\App\Http\Controllers\Admin\AreasInstitucionales\AreaReporteController::class, 'generalCsv'])
                        ->name('general.csv');
                    Route::get('{area}/pdf', [\App\Http\Controllers\Admin\AreasInstitucionales\AreaReporteController::class, 'areaPdf'])
                        ->name('area.pdf');
                    Route::get('{area}/excel', [\App\Http\Controllers\Admin\AreasInstitucionales\AreaReporteController::class, 'areaExcel'])
                        ->name('area.excel');
                });

            // ── Alertas y Pendientes (Debe definirse antes del resource para evitar colisiones) ──
            Route::get('adultos-mayores/alertas-pendientes', \App\Livewire\Admin\AdultosMayores\AlertasPendientesPanel::class)
                ->middleware('permission:adultos.ver')
                ->name('adultos-mayores.alertas-pendientes');

            // ── Admisiones ───────────────────────
            Route::prefix('admisiones')
                ->name('admisiones.')
                ->middleware('permission:admisiones.ver_dashboard')
                ->group(function () {
                    Route::get('/preadmisiones', \App\Livewire\Admin\Admisiones\PreadmisionesPanel::class)->name('preadmisiones');
                    Route::get('/preadmision', \App\Livewire\Admin\Admisiones\PreadmisionWizard::class)->name('preadmision');
                });

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
            // El grupo exige adultos.ver. Cada ruta de mutación agrega su permiso específico.
            // Permisos sin seeder (pendientes): ficha_medica.crear/editar, valoracion_funcional.crear/editar, administracion_medicacion.registrar
            Route::prefix('adultos-mayores/{adulto_mayor}')
                ->middleware('permission:adultos.ver')
                ->name('adultos-mayores.')
                ->group(function () {
                // Familiares
                Route::get('familiares', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorFamiliarController::class, 'index'])->name('familiares.index');
                Route::post('familiares', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorFamiliarController::class, 'store'])->middleware('permission:familiares.crear')->name('familiares.store');
                Route::patch('familiares/{familiar}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorFamiliarController::class, 'update'])->middleware('permission:familiares.editar')->name('familiares.update');
                Route::delete('familiares/{familiar}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorFamiliarController::class, 'destroy'])->middleware('permission:familiares.anular')->name('familiares.destroy');
                Route::patch('familiares/{familiar}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorFamiliarController::class, 'restore'])->middleware('permission:familiares.editar')->name('familiares.restore');

                // Observaciones
                Route::get('observaciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorObservacionController::class, 'index'])->name('observaciones.index');
                Route::post('observaciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorObservacionController::class, 'store'])->middleware('permission:observaciones.crear')->name('observaciones.store');
                Route::patch('observaciones/{observacion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorObservacionController::class, 'update'])->middleware('permission:observaciones.editar')->name('observaciones.update');
                Route::delete('observaciones/{observacion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorObservacionController::class, 'destroy'])->middleware('permission:observaciones.anular')->name('observaciones.destroy');
                Route::patch('observaciones/{observacion}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorObservacionController::class, 'restore'])->middleware('permission:observaciones.editar')->name('observaciones.restore');

                // Atenciones
                Route::get('atenciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorAtencionController::class, 'index'])->name('atenciones.index');
                Route::post('atenciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorAtencionController::class, 'store'])->middleware('permission:atenciones.crear')->name('atenciones.store');
                Route::patch('atenciones/{atencion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorAtencionController::class, 'update'])->middleware('permission:atenciones.editar')->name('atenciones.update');
                Route::delete('atenciones/{atencion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorAtencionController::class, 'destroy'])->middleware('permission:atenciones.anular')->name('atenciones.destroy');
                Route::patch('atenciones/{atencion}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorAtencionController::class, 'restore'])->middleware('permission:atenciones.editar')->name('atenciones.restore');

                // Evaluaciones Cognitivas
                Route::get('evaluaciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorEvaluacionController::class, 'index'])->name('evaluaciones.index');
                Route::post('evaluaciones', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorEvaluacionController::class, 'store'])->middleware('permission:evaluaciones.crear')->name('evaluaciones.store');
                Route::get('evaluaciones/{evaluacion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorEvaluacionController::class, 'show'])->name('evaluaciones.show');
                Route::delete('evaluaciones/{evaluacion}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorEvaluacionController::class, 'destroy'])->middleware('permission:evaluaciones.anular')->name('evaluaciones.destroy');
                Route::patch('evaluaciones/{evaluacion}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorEvaluacionController::class, 'restore'])->middleware('permission:evaluaciones.editar')->name('evaluaciones.restore');

                // Evaluaciones Geriátricas Integrales (Fase 2)
                Route::delete('evaluaciones-geriatricas/{evaluacion}/anular', [AdultoMayorController::class, 'anularEvaluacionGeriatrica'])->middleware('permission:evaluaciones.anular')->name('evaluaciones-geriatricas.anular');
                Route::get('evaluaciones-geriatricas/{evaluacion}/pdf', [AdultoMayorController::class, 'pdfEvaluacionGeriatrica'])->middleware('permission:reportes.individual')->name('evaluaciones-geriatricas.pdf');

                // Actividades
                Route::post('actividades', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorActividadController::class, 'store'])->middleware('permission:actividades.crear')->name('actividades.store');
                Route::patch('actividades/{actividad}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorActividadController::class, 'update'])->middleware('permission:actividades.editar')->name('actividades.update');
                Route::delete('actividades/{actividad}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorActividadController::class, 'destroy'])->middleware('permission:actividades.anular')->name('actividades.destroy');
                Route::patch('actividades/{actividad}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorActividadController::class, 'restore'])->middleware('permission:actividades.editar')->name('actividades.restore');

                // Documentos
                Route::get('documentos', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorDocumentoController::class, 'index'])->name('documentos.index');
                Route::post('documentos', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorDocumentoController::class, 'store'])->middleware('permission:documentos.subir')->name('documentos.store');
                Route::patch('documentos/{documento}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorDocumentoController::class, 'update'])->middleware('permission:documentos.subir')->name('documentos.update');
                Route::delete('documentos/{documento}', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorDocumentoController::class, 'destroy'])->middleware('permission:documentos.archivar')->name('documentos.destroy');
                Route::patch('documentos/{documento}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\AdultoMayorDocumentoController::class, 'restore'])->middleware('permission:documentos.archivar')->name('documentos.restore');

                // FASE 3: Módulos Médicos y Administrativos
                // Ficha Médica
                Route::post('ficha-medica', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorFichaMedicaController::class, 'store'])->middleware('permission:ficha_medica.crear')->name('ficha-medica.store');
                Route::put('ficha-medica/{ficha}', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorFichaMedicaController::class, 'update'])->middleware('permission:ficha_medica.editar')->name('ficha-medica.update');
                Route::patch('ficha-medica/{ficha}/archivar', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorFichaMedicaController::class, 'archivar'])->middleware('permission:ficha_medica.archivar')->name('ficha-medica.archivar');
                Route::patch('ficha-medica/{ficha}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorFichaMedicaController::class, 'restore'])->middleware('permission:ficha_medica.archivar')->name('ficha-medica.restore');

                // Medicación
                Route::post('medicacion', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'store'])->middleware('permission:medicacion.crear')->name('medicacion.store');
                Route::put('medicacion/{medicacion}', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'update'])->middleware('permission:medicacion.editar')->name('medicacion.update');
                Route::patch('medicacion/{medicacion}/suspender', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'suspender'])->middleware('permission:medicacion.suspender')->name('medicacion.suspender');
                Route::patch('medicacion/{medicacion}/finalizar', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'finalizar'])->middleware('permission:medicacion.editar')->name('medicacion.finalizar');
                Route::patch('medicacion/{medicacion}/archivar', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'archivar'])->middleware('permission:medicacion.editar')->name('medicacion.archivar');
                Route::patch('medicacion/{medicacion}/restaurar', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorMedicacionController::class, 'restore'])->middleware('permission:medicacion.editar')->name('medicacion.restore');

                // Administración de Medicación
                Route::post('administracion-medicacion', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorAdministracionMedicacionController::class, 'store'])->middleware('permission:administracion_medicacion.registrar')->name('administracion-medicacion.store');

                // Signos Vitales
                Route::post('signos-vitales', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorSignosVitalesController::class, 'store'])->middleware('permission:signos_vitales.crear')->name('signos-vitales.store');
                Route::put('signos-vitales/{signo}', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorSignosVitalesController::class, 'update'])->middleware('permission:signos_vitales.editar')->name('signos-vitales.update');

                // Valoración Funcional
                Route::post('valoracion-funcional', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorValoracionFuncionalController::class, 'store'])->middleware('permission:valoracion_funcional.crear')->name('valoracion-funcional.store');
                Route::put('valoracion-funcional/{valoracion}', [\App\Http\Controllers\Admin\AdultosMayores\Salud\AdultoMayorValoracionFuncionalController::class, 'update'])->middleware('permission:valoracion_funcional.editar')->name('valoracion-funcional.update');

                // Reporte individual (anidado bajo adulto_mayor)
                Route::get('reporte-individual', [AdultoMayorController::class, 'reporteIndividual'])->middleware('permission:reportes.individual')->name('reporte-individual');

                // Reportes específicos (médico, medicación, vitales, etc.)
                Route::get('reportes/{tipo}', [AdultoMayorController::class, 'reporteEspecifico'])->middleware('permission:reportes.individual')->name('reportes.especifico');
            });

            // ── Reportes protegidos ──────────────
            Route::get('reporte-general', [AdultoMayorController::class, 'reporteGeneral'])
                ->middleware('permission:reportes.ver')
                ->name('adultos-mayores.reporte-general');
            Route::get('reporte-institucional', \App\Livewire\Admin\AdultosMayores\ReportesInstitucionalesPanel::class)
                ->middleware('permission:reportes.ver')
                ->name('adultos-mayores.reporte-institucional');
            Route::get('reporte-bienestar', [AdultoMayorController::class, 'reporteBienestar'])
                ->middleware('permission:reportes.ver')
                ->name('adultos-mayores.reporte-bienestar');


            // ── Salud y Seguimiento ──────────────────
            Route::prefix('salud-seguimiento')
                ->name('salud-seguimiento.')
                ->middleware('permission:salud.ver')
                ->group(function () {
                    // Global Indexes for Sidebar
                    Route::get('/', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('index');
                    Route::get('/resumenes', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('resumen.index');
                    Route::get('/fichas', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('ficha.index');
                    Route::get('/medicaciones', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('medicacion.index');
                    Route::get('/administraciones', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('administracion.index');
                    Route::get('/signos-vitales', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('signos.index');
                    Route::get('/valoraciones', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('valoracion.index');
                    Route::get('/evaluaciones-geriatricas', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('evaluaciones-geriatricas.index');
                    Route::get('/alertas', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('alertas');
                    Route::get('/reportes', \App\Livewire\Admin\SaludSeguimiento\SaludSeguimientoListPanel::class)->name('reportes');

                    // Individual Panels
                    Route::get('/{adulto}/resumen', \App\Livewire\Admin\SaludSeguimiento\SaludResumenPanel::class)->name('resumen');
                    Route::get('/{adulto}/ficha', \App\Livewire\Admin\SaludSeguimiento\SaludFichaPanel::class)->name('ficha');
                    Route::get('/{adulto}/medicacion', \App\Livewire\Admin\SaludSeguimiento\SaludMedicacionPanel::class)->name('medicacion');
                    Route::get('/{adulto}/administracion', \App\Livewire\Admin\SaludSeguimiento\SaludAdministracionMedicacionPanel::class)->name('administracion');
                    Route::get('/{adulto}/signos', \App\Livewire\Admin\SaludSeguimiento\SaludSignosPanel::class)->name('signos');
                    Route::get('/{adulto}/valoracion', \App\Livewire\Admin\SaludSeguimiento\SaludValoracionPanel::class)->name('valoracion');
                    Route::get('/{adulto}/evaluaciones-geriatricas', \App\Livewire\Admin\SaludSeguimiento\SaludEvaluacionesGeriatricasPanel::class)->name('evaluaciones-geriatricas');
                });

            // Familia y Social
            Route::prefix('familia-social')
                ->name('familia-social.')
                ->middleware('permission:familiares.ver')
                ->group(function () {
                    Route::redirect('/', '/admin/familia-social/resumen')->name('index');
                    Route::get('/resumen', ResumenFamiliaSocialController::class)->name('resumen');
                    Route::get('/red-apoyo', \App\Livewire\Admin\FamiliaSocial\RedApoyoPanel::class)->name('red-apoyo');
                    Route::view('/visitas', 'admin.familia-social.base', [
                        'titulo' => 'Visitas',
                        'descripcion' => 'Visitas familiares, sociales y acompañamiento presencial.',
                        'icono' => 'ph-hand-heart',
                    ])->name('visitas');
                    Route::view('/ficha-social', 'admin.familia-social.base', [
                        'titulo' => 'Ficha social',
                        'descripcion' => 'Situacion social, convivencia, red de apoyo real y observaciones sociales.',
                        'icono' => 'ph-clipboard-text',
                    ])->name('ficha-social');
                });

            // ── FLUJO CLÍNICO / ENFERMERÍA (Fase 6) ──────────────────────────────

            // Infraestructura: Habitaciones y Camas
            Route::prefix('habitaciones')
                ->name('habitaciones.')
                ->middleware('permission:habitaciones.ver')
                ->group(function () {
                    Route::get('/', \App\Livewire\Admin\Enfermeria\HabitacionesPanel::class)->name('index');
                });

            // Turnos de Enfermería
            Route::prefix('turnos-enfermeria')
                ->name('turnos-enfermeria.')
                ->middleware('permission:turnos_enfermeria.ver')
                ->group(function () {
                    Route::get('/', \App\Livewire\Admin\Enfermeria\TurnosEnfermeriaPanel::class)->name('index');
                });

            // Admisión clínica — Valoraciones
            Route::prefix('admision')
                ->name('admision.')
                ->middleware('permission:valoracion_enfermeria.ver')
                ->group(function () {
                    Route::get('/valoracion-enfermeria', \App\Livewire\Admin\Enfermeria\ValoracionEnfermeriaPanel::class)->name('valoracion-enfermeria');
                    Route::get('/valoracion-medica', \App\Livewire\Admin\Enfermeria\ValoracionMedicaPanel::class)
                        ->middleware('permission:valoracion_medica.ver')
                        ->name('valoracion-medica');
                });

            // Asignación de Turno
            Route::prefix('asignacion-turno')
                ->name('asignacion-turno.')
                ->middleware('permission:turnos.ver')
                ->group(function () {
                    Route::get('/', fn () => redirect()->route('admin.turnos-asignaciones.index'))->name('index');
                });

            // Plan de Cuidado y Tareas
            Route::prefix('plan-cuidado')
                ->name('plan-cuidado.')
                ->middleware('permission:plan_cuidado.ver')
                ->group(function () {
                    Route::get('/', \App\Livewire\Admin\Enfermeria\PlanCuidadoPanel::class)->name('index');
                    Route::get('/tareas', \App\Livewire\Admin\Enfermeria\TareasPlanPanel::class)
                        ->middleware('permission:tareas.ver')
                        ->name('tareas');
                });

            // Seguimiento Diario
            Route::prefix('seguimiento-diario')
                ->name('seguimiento-diario.')
                ->middleware('permission:seguimiento.ver')
                ->group(function () {
                    Route::get('/', \App\Livewire\Admin\Enfermeria\SeguimientoDiarioPanel::class)->name('index');
                });

            // Alertas y Acciones
            Route::prefix('alertas-clinicas')
                ->name('alertas-clinicas.')
                ->middleware('permission:alertas.ver')
                ->group(function () {
                    Route::get('/', \App\Livewire\Admin\Enfermeria\AlertasPanel::class)->name('index');
                });

            // Pase de Turno
            Route::prefix('pase-turno')
                ->name('pase-turno.')
                ->middleware('permission:pase_turno.ver')
                ->group(function () {
                    Route::get('/', \App\Livewire\Admin\Enfermeria\PaseTurnoPanel::class)->name('index');
                });

            // ── Actividades ─────────────────────────
            Route::prefix('actividades')
                ->name('actividades.')
                ->middleware('permission:actividades.ver')
                ->group(function () {
                    Route::get('/', \App\Livewire\Admin\Actividades\ActividadesPanel::class)->name('index');
                    Route::get('/tipos', \App\Livewire\Admin\Actividades\TiposActividadPanel::class)->name('tipos');
                    Route::get('/participacion', \App\Livewire\Admin\Actividades\ParticipacionPanel::class)->name('participacion');
                    Route::get('/asistencia', \App\Livewire\Admin\Actividades\AsistenciaPanel::class)->name('asistencia');
                    Route::get('/reportes', \App\Livewire\Admin\Actividades\ReportesActividadesPanel::class)->name('reportes');
                });

            // Voluntariado
            Route::prefix('voluntariado')
                ->name('voluntariado.')
                ->middleware('permission:voluntarios.ver')
                ->group(function () {
                    Route::view('/', 'admin.voluntarios.index')->name('index');
                    Route::view('/voluntarios', 'admin.voluntarios.voluntarios')->name('voluntarios.index');
                    Route::view('/disponibilidad', 'admin.voluntarios.disponibilidad')->name('disponibilidad.index');
                    Route::view('/asignaciones', 'admin.voluntarios.asignaciones')->name('asignaciones.index');
                    Route::view('/asistencia', 'admin.voluntarios.asistencia')->name('asistencia.index');
                    Route::view('/reportes', 'admin.voluntarios.index')->name('reportes.index');
                });

            // ── Enfermería ─────────────────────────
            Route::prefix('enfermeria')
                ->name('enfermeria.')
                ->group(function () {
                    Route::get('/dashboard', \App\Livewire\Admin\Enfermeria\DashboardTurno::class)
                        ->middleware('permission:enfermeria.ver_dashboard')
                        ->name('dashboard');
                    
                    Route::get('/pacientes', \App\Livewire\Admin\Enfermeria\MisPacientes::class)
                        ->middleware('permission:enfermeria.ver_pacientes_asignados')
                        ->name('pacientes');
                    
                    Route::get('/pacientes/{adulto}', \App\Livewire\Admin\Enfermeria\FichaPaciente::class)
                        ->middleware('permission:enfermeria.ver_ficha_paciente')
                        ->name('pacientes.ficha');
                        
                    Route::get('/pacientes/{adulto}/pdf', [\App\Http\Controllers\Admin\Enfermeria\FichaPacienteReporteController::class, 'pdf'])
                        ->middleware('permission:enfermeria.ver_ficha_paciente')
                        ->name('pacientes.ficha.pdf');
                    
                    Route::get('/tareas', \App\Livewire\Admin\Enfermeria\TareasPlanPanel::class)
                        ->middleware('permission:enfermeria.ver_dashboard')
                        ->name('tareas');
                    
                    Route::get('/alertas', \App\Livewire\Admin\Enfermeria\AlertasPanel::class)
                        ->middleware('permission:enfermeria.ver_dashboard')
                        ->name('alertas');
                        
                    Route::get('/pase-turno', \App\Livewire\Admin\Enfermeria\PaseTurnoPanel::class)
                        ->middleware('permission:enfermeria.ver_dashboard')
                        ->name('pase-turno');
                        
                    Route::get('/actividades', \App\Livewire\Admin\Enfermeria\DashboardTurno::class)
                        ->middleware('permission:enfermeria.ver_dashboard')
                        ->name('actividades');
                        
                    Route::get('/reportes', \App\Livewire\Admin\Enfermeria\DashboardTurno::class)
                        ->middleware('permission:enfermeria.ver_dashboard')
                        ->name('reportes');
                });

            // ── Médico General ─────────────────────────
            Route::prefix('medico')
                ->name('medico.')
                ->group(function () {
                    Route::get('/dashboard', \App\Livewire\Admin\Medico\DashboardMedico::class)
                        ->name('dashboard');
                        
                    // Admisiones Médicas
                    Route::get('/valoraciones-medicas', \App\Livewire\Admin\Medico\DashboardMedico::class)
                        ->name('valoraciones');
                    Route::get('/decisiones-admision', \App\Livewire\Admin\Medico\DashboardMedico::class)
                        ->name('decisiones');
                        
                    // Pacientes
                    Route::get('/pacientes-observacion', \App\Livewire\Admin\Medico\DashboardMedico::class)
                        ->name('pacientes.observacion');
                    Route::get('/historial-clinico', \App\Livewire\Admin\Medico\DashboardMedico::class)
                        ->name('pacientes.historial');
                });

            // ── Psicología ─────────────────────────
            Route::prefix('psicologia')
                ->name('psicologia.')
                ->group(function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])
                        ->name('dashboard');
                        
                    // Psicología
                    Route::get('/evaluaciones-asignadas', [DashboardController::class, 'index'])
                        ->name('evaluaciones');
                    Route::get('/seguimiento-emocional', [DashboardController::class, 'index'])
                        ->name('seguimiento');
                    Route::get('/alertas-conductuales', [DashboardController::class, 'index'])
                        ->name('alertas');
                        
                    // Pacientes
                    Route::get('/pacientes-derivados', [DashboardController::class, 'index'])
                        ->name('pacientes.derivados');
                    Route::get('/historial-psicologico', [DashboardController::class, 'index'])
                        ->name('pacientes.historial');
                        
                    // Reportes
                    Route::get('/reportes-psicologicos', [DashboardController::class, 'index'])
                        ->name('reportes');
                });

            // ── Fisioterapia ─────────────────────────
            Route::prefix('fisioterapia')
                ->name('fisioterapia.')
                ->group(function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])
                        ->name('dashboard');
                        
                    // Fisioterapia
                    Route::get('/pacientes-derivados', [DashboardController::class, 'index'])
                        ->name('pacientes.derivados');
                    Route::get('/valoracion-funcional', [DashboardController::class, 'index'])
                        ->name('valoracion');
                    Route::get('/plan-funcional', [DashboardController::class, 'index'])
                        ->name('plan');
                    Route::get('/evolucion-fisica', [DashboardController::class, 'index'])
                        ->name('evolucion');
                        
                    // Riesgos
                    Route::get('/riesgo-caida', [DashboardController::class, 'index'])
                        ->name('riesgo.caida');
                    Route::get('/alertas-funcionales', [DashboardController::class, 'index'])
                        ->name('alertas');
                        
                    // Reportes
                    Route::get('/reportes-fisioterapia', [DashboardController::class, 'index'])
                        ->name('reportes');
                });

            // ── Nutrición ─────────────────────────
            Route::prefix('nutricion')
                ->name('nutricion.')
                ->group(function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])
                        ->name('dashboard');
                        
                    // Nutrición
                    Route::get('/pacientes-derivados', [DashboardController::class, 'index'])
                        ->name('pacientes.derivados');
                    Route::get('/valoracion-nutricional', [DashboardController::class, 'index'])
                        ->name('valoracion');
                    Route::get('/plan-alimentario', [DashboardController::class, 'index'])
                        ->name('plan');
                    Route::get('/seguimiento-nutricional', [DashboardController::class, 'index'])
                        ->name('seguimiento');
                        
                    // Control
                    Route::get('/peso-imc', [DashboardController::class, 'index'])
                        ->name('control.peso');
                    Route::get('/hidratacion', [DashboardController::class, 'index'])
                        ->name('control.hidratacion');
                    Route::get('/alertas-nutricionales', [DashboardController::class, 'index'])
                        ->name('alertas');
                        
                    // Reportes
                    Route::get('/reportes-nutricionales', [DashboardController::class, 'index'])
                        ->name('reportes');
                });

            // ── Voluntario ─────────────────────────
            Route::prefix('voluntario')
                ->name('voluntario.')
                ->group(function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])
                        ->name('dashboard');
                        
                    // Voluntariado
                    Route::get('/mis-actividades', [DashboardController::class, 'index'])
                        ->name('actividades');
                    Route::get('/asistencia', [DashboardController::class, 'index'])
                        ->name('asistencia');
                    Route::get('/disponibilidad', [DashboardController::class, 'index'])
                        ->name('disponibilidad');
                        
                    // Adultos Mayores
                    Route::get('/adultos-asignados', [DashboardController::class, 'index'])
                        ->name('adultos.asignados');
                        
                    // Reportes
                    Route::get('/reportes-actividades', [DashboardController::class, 'index'])
                        ->name('reportes');
                });

            // ── Portal Familiar ─────────────────────────
            Route::prefix('portal-familiar')
                ->name('familiar.')
                ->group(function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])
                        ->name('dashboard');
                        
                    // Mi Familiar
                    Route::get('/resumen-estado', [DashboardController::class, 'index'])
                        ->name('resumen');
                    Route::get('/actividades', [DashboardController::class, 'index'])
                        ->name('actividades');
                    Route::get('/visitas', [DashboardController::class, 'index'])
                        ->name('visitas');
                        
                    // Pagos y Documentos
                    Route::get('/estado-cuenta', [DashboardController::class, 'index'])
                        ->name('pagos');
                    Route::get('/documentos', [DashboardController::class, 'index'])
                        ->name('documentos');
                });

            // ── Reportes Institucionales ─────────
            Route::prefix('reportes')
                ->name('reportes.')
                ->group(function () {
                    Route::get('institucional', [ReporteInstitucionalController::class, 'preview'])
                        ->middleware('permission:reportes.institucional')
                        ->name('institucional.preview');

                    Route::get('institucional/pdf', [ReporteInstitucionalController::class, 'pdf'])
                        ->middleware('permission:reportes.institucional')
                        ->name('institucional.pdf');

                    Route::get('institucional/excel', [ReporteInstitucionalController::class, 'excel'])
                        ->middleware('permission:reportes.institucional')
                        ->name('institucional.excel');

                    // ── Adultos Mayores ──────────────
                    Route::get('adultos', [ReporteAdultosController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('adultos.preview');
                    Route::get('adultos/pdf', [ReporteAdultosController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('adultos.pdf');
                    Route::get('adultos/excel', [ReporteAdultosController::class, 'excel'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('adultos.excel');

                    // ── Salud y Seguimiento ──────────
                    Route::get('salud', [ReporteSaludController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('salud.preview');
                    Route::get('salud/pdf', [ReporteSaludController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('salud.pdf');
                    Route::get('salud/excel', [ReporteSaludController::class, 'excel'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('salud.excel');

                    // ── Familiares ───────────────────
                    Route::get('familiares', [ReporteFamiliaresController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('familiares.preview');
                    Route::get('familiares/pdf', [ReporteFamiliaresController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('familiares.pdf');
                    Route::get('familiares/excel', [ReporteFamiliaresController::class, 'excel'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('familiares.excel');

                    // ── Equipo Institucional ─────────
                    Route::get('equipo', [ReporteEquipoController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('equipo.preview');
                    Route::get('equipo/pdf', [ReporteEquipoController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('equipo.pdf');
                    Route::get('equipo/excel', [ReporteEquipoController::class, 'excel'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('equipo.excel');

                    // ── Actividades ──────────────────
                    Route::get('actividades', [ReporteActividadesController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('actividades.preview');
                    Route::get('actividades/pdf', [ReporteActividadesController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('actividades.pdf');
                    Route::get('actividades/excel', [ReporteActividadesController::class, 'excel'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('actividades.excel');

                    // ── Bitácora (sin Excel por seguridad) ──
                    Route::get('bitacora', [ReporteBitacoraController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('bitacora.preview');
                    Route::get('bitacora/pdf', [ReporteBitacoraController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('bitacora.pdf');
                });

            // ── Bitácora ─────────────────────────
            Route::get('bitacora', [\App\Http\Controllers\Admin\BitacoraController::class, 'index'])
                ->middleware('permission:bitacora.ver')
                ->name('bitacora.index');
        });
});
