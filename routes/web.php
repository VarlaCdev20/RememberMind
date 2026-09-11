<?php

use App\Http\Controllers\Admin\AdultosMayores\AdultoMayorActividadController;
use App\Http\Controllers\Admin\Reportes\ReporteActividadesController;
use App\Http\Controllers\Clinica\AdultoMayorFichaMedicaController;
use App\Http\Controllers\Clinica\AdultoMayorSignosVitalesController;
use App\Http\Controllers\Documentos\AdultoMayorDocumentoController;
use App\Http\Controllers\Documentos\DocumentosUsuarioController;
use App\Http\Controllers\Identidad\UsuarioController;
use App\Http\Controllers\Medicacion\AdultoMayorAdministracionMedicacionController;
use App\Http\Controllers\Medicacion\AdultoMayorMedicacionController;
use App\Http\Controllers\Reportes\AreaReporteController;
use App\Http\Controllers\Reportes\BitacoraController;
use App\Http\Controllers\Reportes\DashboardController;
use App\Http\Controllers\Reportes\FichaPacienteReporteController;
use App\Http\Controllers\Reportes\ReporteAdultosController;
use App\Http\Controllers\Reportes\ReporteBitacoraController;
use App\Http\Controllers\Reportes\ReporteEquipoController;
use App\Http\Controllers\Reportes\ReporteFamiliaresController;
use App\Http\Controllers\Reportes\ReporteInstitucionalController;
use App\Http\Controllers\Reportes\ReporteSaludController;
use App\Http\Controllers\Residentes\AdultoMayorAtencionController;
use App\Http\Controllers\Residentes\AdultoMayorController;
use App\Http\Controllers\Residentes\AdultoMayorFamiliarController;
use App\Http\Controllers\Residentes\AdultoMayorObservacionController;
use App\Http\Controllers\Residentes\ResumenFamiliaSocialController;
use App\Http\Controllers\Valoraciones\AdultoMayorEvaluacionController;
use App\Http\Controllers\Valoraciones\AdultoMayorValoracionFuncionalController;
use App\Livewire\Admin\Actividades\ActividadesPanel;
use App\Livewire\Admin\Actividades\AsistenciaPanel;
use App\Livewire\Admin\Actividades\ParticipacionPanel;
use App\Livewire\Admin\Actividades\ReportesActividadesPanel;
use App\Livewire\Admin\Actividades\TiposActividadPanel;
use App\Livewire\Admisiones\HabitacionesPanel;
use App\Livewire\Admisiones\PreadmisionesPanel;
use App\Livewire\Admisiones\PreadmisionWizard;
use App\Livewire\Alertas\AlertasPanel;
use App\Livewire\Alertas\AlertasPendientesPanel;
use App\Livewire\Clinica\DashboardMedico;
use App\Livewire\Clinica\FichaClinicaIntegradaPanel;
use App\Livewire\Clinica\PacientesSeguimientoPanel;
use App\Livewire\Clinica\SaludFichaPanel;
use App\Livewire\Clinica\SaludResumenPanel;
use App\Livewire\Clinica\SaludSeguimientoListPanel;
use App\Livewire\Clinica\SaludSignosPanel;
use App\Livewire\Clinica\SignosVitalesPanel;
use App\Livewire\Cuidados\AsignacionTurnoPanel;
use App\Livewire\Cuidados\DashboardTurno;
use App\Livewire\Cuidados\AgendaEnfermeria;
use App\Livewire\Cuidados\RegistrosEnfermeria;
use App\Livewire\Cuidados\ReporteEnfermeria;
use App\Livewire\Cuidados\FichaPaciente;
use App\Livewire\Cuidados\MisPacientes;
use App\Livewire\Cuidados\PaseTurnoPanel;
use App\Livewire\Cuidados\PlanCuidadoPanel;
use App\Livewire\Cuidados\SeguimientoDiarioPanel;
use App\Livewire\Cuidados\TareasPlanPanel;
use App\Livewire\Cuidados\TurnosEnfermeriaPanel;
use App\Livewire\Identidad\PersonalInstitucionalPanel;
use App\Livewire\Medicacion\SaludAdministracionMedicacionPanel;
use App\Livewire\Medicacion\SaludMedicacionPanel;
use App\Livewire\Reportes\ReportesInstitucionalesPanel;
use App\Livewire\Residentes\RedApoyoPanel;
use App\Livewire\Valoraciones\DashboardPsicologo;
use App\Livewire\Valoraciones\EvaluacionesAreaPanel;
use App\Livewire\Valoraciones\SaludEvaluacionesGeriatricasPanel;
use App\Livewire\Valoraciones\SaludValoracionPanel;
use App\Livewire\Valoraciones\ValoracionEnfermeriaPanel;
use App\Livewire\Valoraciones\ValoracionMedicaPanel;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.welcome');
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

            // ── Área de Administración (Supervisión / Panel Completo de Administración) ──
            Route::prefix('administracion')->name('administracion.')->group(function () {
                Route::get('/dashboard', [DashboardController::class, 'index'])
                    ->name('dashboard');
                Route::get('/', [DashboardController::class, 'index'])
                    ->name('index');
            });

            Route::get('vistas-extra', function () {
                abort_unless(auth()->user()?->hasRole('SUPERADMINISTRADOR'), 403);

                return view('pages.admin.vistas-extra');
            })->name('vistas-extra');

            // ── Usuarios ─────────────────────────
            Route::get('usuarios', [UsuarioController::class, 'index'])
                ->middleware('permission:usuarios.ver')
                ->name('usuarios.index');

            Route::get('usuarios/create', [UsuarioController::class, 'create'])
                ->middleware('permission:usuarios.crear')
                ->name('usuarios.create');

            Route::post('usuarios', [UsuarioController::class, 'store'])
                ->middleware('permission:usuarios.crear')
                ->name('usuarios.store');

            Route::get('usuarios/{usuario}', [UsuarioController::class, 'show'])
                ->middleware('permission:usuarios.ver')
                ->name('usuarios.show');

            Route::get('usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])
                ->middleware('permission:usuarios.editar')
                ->name('usuarios.edit');

            Route::match(['put', 'patch'], 'usuarios/{usuario}', [UsuarioController::class, 'update'])
                ->middleware('permission:usuarios.editar')
                ->name('usuarios.update');

            Route::delete('usuarios/{usuario}', [UsuarioController::class, 'destroy'])
                ->middleware('permission:usuarios.editar')
                ->name('usuarios.destroy');

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
                    Route::get('/', [DocumentosUsuarioController::class, 'preview'])->name('preview');
                    Route::get('/pdf', [DocumentosUsuarioController::class, 'paquetePdf'])->name('pdf');
                    Route::post('/enviar', [DocumentosUsuarioController::class, 'enviarPaqueteCorreo'])->name('enviar');
                    Route::get('/{documento}/ver', [DocumentosUsuarioController::class, 'verDocumento'])->name('ver');
                    Route::get('/{documento}/pdf', [DocumentosUsuarioController::class, 'pdfDocumento'])->name('documento-pdf');
                    Route::get('/{documento}/imprimir', [DocumentosUsuarioController::class, 'imprimirDocumento'])->name('imprimir');
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
            Route::get('/personal-institucional', PersonalInstitucionalPanel::class)
                ->middleware('permission:personal_institucional.ver')
                ->name('personal-institucional');

            // ── Roles y Permisos ─────────────────
            Route::view('/roles-permisos', 'pages.roles-permisos.index')
                ->middleware('permission:roles.ver')
                ->name('roles-permisos.index');

            // ── Áreas Institucionales ─────────────
            Route::view('/areas-institucionales', 'pages.areas-institucionales.index')
                ->middleware('permission:areas.ver')
                ->name('areas-institucionales.index');

            // ── Turnos y Asignaciones ─────────────
            Route::view('/turnos-asignaciones', 'pages.turnos-asignaciones.index')
                ->middleware('permission:turnos.ver')
                ->name('turnos-asignaciones.index');

            // ── Reportes de Áreas Institucionales ─
            Route::prefix('areas-institucionales/reportes')
                ->name('areas-institucionales.reportes.')
                ->middleware('permission:areas.reportes')
                ->group(function () {
                    Route::get('general/pdf', [AreaReporteController::class, 'generalPdf'])
                        ->name('general.pdf');
                    Route::get('general/excel', [AreaReporteController::class, 'generalExcel'])
                        ->name('general.excel');
                    Route::get('general/csv', [AreaReporteController::class, 'generalCsv'])
                        ->name('general.csv');
                    Route::get('{area}/pdf', [AreaReporteController::class, 'areaPdf'])
                        ->name('area.pdf');
                    Route::get('{area}/excel', [AreaReporteController::class, 'areaExcel'])
                        ->name('area.excel');
                });

            // ── Alertas y Pendientes (Debe definirse antes del resource para evitar colisiones) ──
            Route::get('adultos-mayores/alertas-pendientes', AlertasPendientesPanel::class)
                ->middleware('permission:adultos.ver')
                ->name('adultos-mayores.alertas-pendientes');

            // ── Admisiones ───────────────────────
            Route::prefix('admisiones')
                ->name('admisiones.')
                ->middleware('permission:admisiones.ver_dashboard')
                ->group(function () {
                    Route::get('/preadmisiones', PreadmisionesPanel::class)->name('preadmisiones');
                    Route::get('/preadmisiones/rechazadas', PreadmisionesPanel::class)->name('preadmisiones.rechazadas');
                    Route::get('/preadmision', PreadmisionWizard::class)
                        ->middleware('permission:admisiones.crear')
                        ->name('preadmision');
                });

            // ── Adultos Mayores ──────────────────
            Route::resource('adultos-mayores', AdultoMayorController::class)
                ->only(['index', 'show'])
                ->middleware('permission:adultos.ver')
                ->parameters([
                    'adultos-mayores' => 'adulto_mayor',
                ]);

            Route::resource('adultos-mayores', AdultoMayorController::class)
                ->only(['edit', 'update'])
                ->middleware('permission:adultos.editar')
                ->parameters([
                    'adultos-mayores' => 'adulto_mayor',
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
                    Route::get('familiares', [AdultoMayorFamiliarController::class, 'index'])->name('familiares.index');
                    Route::post('familiares', [AdultoMayorFamiliarController::class, 'store'])->middleware('permission:familiares.crear')->name('familiares.store');
                    Route::patch('familiares/{familiar}', [AdultoMayorFamiliarController::class, 'update'])->middleware('permission:familiares.editar')->name('familiares.update');
                    Route::delete('familiares/{familiar}', [AdultoMayorFamiliarController::class, 'destroy'])->middleware('permission:familiares.anular')->name('familiares.destroy');
                    Route::patch('familiares/{familiar}/restaurar', [AdultoMayorFamiliarController::class, 'restore'])->middleware('permission:familiares.editar')->name('familiares.restore');

                    // Observaciones
                    Route::get('observaciones', [AdultoMayorObservacionController::class, 'index'])->name('observaciones.index');
                    Route::post('observaciones', [AdultoMayorObservacionController::class, 'store'])->middleware('permission:observaciones.crear')->name('observaciones.store');
                    Route::patch('observaciones/{observacion}', [AdultoMayorObservacionController::class, 'update'])->middleware('permission:observaciones.editar')->name('observaciones.update');
                    Route::delete('observaciones/{observacion}', [AdultoMayorObservacionController::class, 'destroy'])->middleware('permission:observaciones.anular')->name('observaciones.destroy');
                    Route::patch('observaciones/{observacion}/restaurar', [AdultoMayorObservacionController::class, 'restore'])->middleware('permission:observaciones.editar')->name('observaciones.restore');

                    // Atenciones
                    Route::get('atenciones', [AdultoMayorAtencionController::class, 'index'])->name('atenciones.index');
                    Route::post('atenciones', [AdultoMayorAtencionController::class, 'store'])->middleware('permission:atenciones.crear')->name('atenciones.store');
                    Route::patch('atenciones/{atencion}', [AdultoMayorAtencionController::class, 'update'])->middleware('permission:atenciones.editar')->name('atenciones.update');
                    Route::delete('atenciones/{atencion}', [AdultoMayorAtencionController::class, 'destroy'])->middleware('permission:atenciones.anular')->name('atenciones.destroy');
                    Route::patch('atenciones/{atencion}/restaurar', [AdultoMayorAtencionController::class, 'restore'])->middleware('permission:atenciones.editar')->name('atenciones.restore');

                    // Evaluaciones Cognitivas
                    Route::get('evaluaciones', [AdultoMayorEvaluacionController::class, 'index'])->name('evaluaciones.index');
                    Route::post('evaluaciones', [AdultoMayorEvaluacionController::class, 'store'])->middleware('permission:evaluaciones.crear')->name('evaluaciones.store');
                    Route::get('evaluaciones/{evaluacion}', [AdultoMayorEvaluacionController::class, 'show'])->name('evaluaciones.show');
                    Route::delete('evaluaciones/{evaluacion}', [AdultoMayorEvaluacionController::class, 'destroy'])->middleware('permission:evaluaciones.anular')->name('evaluaciones.destroy');
                    Route::patch('evaluaciones/{evaluacion}/restaurar', [AdultoMayorEvaluacionController::class, 'restore'])->middleware('permission:evaluaciones.editar')->name('evaluaciones.restore');

                    // Evaluaciones Geriátricas Integrales (Fase 2)
                    Route::delete('evaluaciones-geriatricas/{evaluacion}/anular', [AdultoMayorController::class, 'anularEvaluacionGeriatrica'])->middleware('permission:evaluaciones.anular')->name('evaluaciones-geriatricas.anular');
                    Route::get('evaluaciones-geriatricas/{evaluacion}/pdf', [AdultoMayorController::class, 'pdfEvaluacionGeriatrica'])->middleware('permission:reportes.individual')->name('evaluaciones-geriatricas.pdf');

                    // Actividades
                    Route::post('actividades', [AdultoMayorActividadController::class, 'store'])->middleware('permission:actividades.crear')->name('actividades.store');
                    Route::patch('actividades/{actividad}', [AdultoMayorActividadController::class, 'update'])->middleware('permission:actividades.editar')->name('actividades.update');
                    Route::delete('actividades/{actividad}', [AdultoMayorActividadController::class, 'destroy'])->middleware('permission:actividades.anular')->name('actividades.destroy');
                    Route::patch('actividades/{actividad}/restaurar', [AdultoMayorActividadController::class, 'restore'])->middleware('permission:actividades.editar')->name('actividades.restore');

                    // Documentos
                    Route::get('documentos', [AdultoMayorDocumentoController::class, 'index'])->name('documentos.index');
                    Route::get('documentos/{documento}/archivo', [AdultoMayorDocumentoController::class, 'archivo'])->name('documentos.archivo');
                    Route::post('documentos', [AdultoMayorDocumentoController::class, 'store'])->middleware('permission:documentos.subir')->name('documentos.store');
                    Route::patch('documentos/{documento}', [AdultoMayorDocumentoController::class, 'update'])->middleware('permission:documentos.subir')->name('documentos.update');
                    Route::delete('documentos/{documento}', [AdultoMayorDocumentoController::class, 'destroy'])->middleware('permission:documentos.archivar')->name('documentos.destroy');
                    Route::patch('documentos/{documento}/restaurar', [AdultoMayorDocumentoController::class, 'restore'])->middleware('permission:documentos.archivar')->name('documentos.restore');

                    // FASE 3: Módulos Médicos y Administrativos
                    // Ficha Médica
                    Route::post('ficha-medica', [AdultoMayorFichaMedicaController::class, 'store'])->middleware('permission:ficha_medica.crear')->name('ficha-medica.store');
                    Route::put('ficha-medica/{ficha}', [AdultoMayorFichaMedicaController::class, 'update'])->middleware('permission:ficha_medica.editar')->name('ficha-medica.update');
                    Route::patch('ficha-medica/{ficha}/archivar', [AdultoMayorFichaMedicaController::class, 'archivar'])->middleware('permission:ficha_medica.archivar')->name('ficha-medica.archivar');
                    Route::patch('ficha-medica/{ficha}/restaurar', [AdultoMayorFichaMedicaController::class, 'restore'])->middleware('permission:ficha_medica.archivar')->name('ficha-medica.restore');

                    // Medicación
                    Route::post('medicacion', [AdultoMayorMedicacionController::class, 'store'])->middleware('permission:medicacion.crear')->name('medicacion.store');
                    Route::put('medicacion/{medicacion}', [AdultoMayorMedicacionController::class, 'update'])->middleware('permission:medicacion.editar')->name('medicacion.update');
                    Route::patch('medicacion/{medicacion}/suspender', [AdultoMayorMedicacionController::class, 'suspender'])->middleware('permission:medicacion.suspender')->name('medicacion.suspender');
                    Route::patch('medicacion/{medicacion}/finalizar', [AdultoMayorMedicacionController::class, 'finalizar'])->middleware('permission:medicacion.editar')->name('medicacion.finalizar');
                    Route::patch('medicacion/{medicacion}/archivar', [AdultoMayorMedicacionController::class, 'archivar'])->middleware('permission:medicacion.editar')->name('medicacion.archivar');
                    Route::patch('medicacion/{medicacion}/restaurar', [AdultoMayorMedicacionController::class, 'restore'])->middleware('permission:medicacion.editar')->name('medicacion.restore');

                    // Administración de Medicación
                    Route::post('administracion-medicacion', [AdultoMayorAdministracionMedicacionController::class, 'store'])->middleware('permission:administracion_medicacion.registrar')->name('administracion-medicacion.store');

                    // Signos Vitales
                    Route::post('signos-vitales', [AdultoMayorSignosVitalesController::class, 'store'])->middleware('permission:signos_vitales.crear')->name('signos-vitales.store');
                    Route::put('signos-vitales/{signo}', [AdultoMayorSignosVitalesController::class, 'update'])->middleware('permission:signos_vitales.editar')->name('signos-vitales.update');

                    // Valoración Funcional
                    Route::post('valoracion-funcional', [AdultoMayorValoracionFuncionalController::class, 'store'])->middleware('permission:valoracion_funcional.crear')->name('valoracion-funcional.store');
                    Route::put('valoracion-funcional/{valoracion}', [AdultoMayorValoracionFuncionalController::class, 'update'])->middleware('permission:valoracion_funcional.editar')->name('valoracion-funcional.update');

                    // Reporte individual (anidado bajo adulto_mayor)
                    Route::get('reporte-individual', [AdultoMayorController::class, 'reporteIndividual'])->middleware('permission:reportes.individual')->name('reporte-individual');

                    // Reportes específicos (médico, medicación, vitales, etc.)
                    Route::get('reportes/{tipo}', [AdultoMayorController::class, 'reporteEspecifico'])->middleware('permission:reportes.individual')->name('reportes.especifico');
                });

            // ── Reportes protegidos ──────────────
            Route::get('reporte-general', [AdultoMayorController::class, 'reporteGeneral'])
                ->middleware('permission:reportes.ver')
                ->name('adultos-mayores.reporte-general');
            Route::get('reporte-institucional', ReportesInstitucionalesPanel::class)
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
                    Route::get('/', SaludSeguimientoListPanel::class)->name('index');
                    Route::get('/resumenes', SaludSeguimientoListPanel::class)->defaults('seccion', 'resumen')->name('resumen.index');
                    Route::get('/fichas', SaludSeguimientoListPanel::class)->defaults('seccion', 'ficha')->name('ficha.index');
                    Route::get('/medicaciones', SaludSeguimientoListPanel::class)->defaults('seccion', 'medicacion')->name('medicacion.index');
                    Route::get('/administraciones', SaludSeguimientoListPanel::class)->defaults('seccion', 'administracion')->name('administracion.index');
                    Route::get('/signos-vitales', SaludSeguimientoListPanel::class)->defaults('seccion', 'signos')->name('signos.index');
                    Route::get('/valoraciones', SaludSeguimientoListPanel::class)->defaults('seccion', 'valoracion')->name('valoracion.index');
                    Route::get('/evaluaciones-geriatricas', SaludSeguimientoListPanel::class)->defaults('seccion', 'evaluaciones')->name('evaluaciones-geriatricas.index');
                    Route::get('/alertas', SaludSeguimientoListPanel::class)->defaults('seccion', 'alertas')->name('alertas');
                    Route::get('/reportes', SaludSeguimientoListPanel::class)->defaults('seccion', 'reportes')->name('reportes');

                    // Individual Panels
                    Route::get('/{adulto}/resumen', SaludResumenPanel::class)->name('resumen');
                    Route::get('/{adulto}/ficha', SaludFichaPanel::class)->name('ficha');
                    Route::get('/{adulto}/medicacion', SaludMedicacionPanel::class)->name('medicacion');
                    Route::get('/{adulto}/administracion', SaludAdministracionMedicacionPanel::class)->name('administracion');
                    Route::get('/{adulto}/signos', SaludSignosPanel::class)->name('signos');
                    Route::get('/{adulto}/valoracion', SaludValoracionPanel::class)->name('valoracion');
                    Route::get('/{adulto}/evaluaciones-geriatricas', SaludEvaluacionesGeriatricasPanel::class)->name('evaluaciones-geriatricas');
                });

            // Familia y Social
            Route::prefix('familia-social')
                ->name('familia-social.')
                ->middleware('permission:familiares.ver')
                ->group(function () {
                    Route::redirect('/', '/admin/familia-social/resumen')->name('index');
                    Route::get('/resumen', ResumenFamiliaSocialController::class)->name('resumen');
                    Route::get('/red-apoyo', RedApoyoPanel::class)->name('red-apoyo');
                    Route::view('/visitas', 'pages.familia-social.base', [
                        'titulo' => 'Visitas',
                        'descripcion' => 'Visitas familiares, sociales y acompañamiento presencial.',
                        'icono' => 'ph-hand-heart',
                    ])->name('visitas');
                    Route::view('/ficha-social', 'pages.familia-social.base', [
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
                    Route::get('/', HabitacionesPanel::class)->name('index');
                });

            // Turnos de Enfermería
            Route::prefix('turnos-enfermeria')
                ->name('turnos-enfermeria.')
                ->middleware('permission:turnos_enfermeria.ver')
                ->group(function () {
                    Route::get('/', TurnosEnfermeriaPanel::class)->name('index');
                });

            // Admisión clínica — Valoraciones
            Route::prefix('admision')
                ->name('admision.')
                ->middleware('permission:valoracion_enfermeria.ver')
                ->group(function () {
                    Route::get('/valoracion-enfermeria', ValoracionEnfermeriaPanel::class)->name('valoracion-enfermeria');
                    Route::get('/valoracion-medica', ValoracionMedicaPanel::class)
                        ->middleware('permission:valoracion_medica.ver')
                        ->name('valoracion-medica');
                });

            // Asignación de Turno
            Route::prefix('asignacion-turno')
                ->name('asignacion-turno.')
                ->middleware('permission:turnos.ver')
                ->group(function () {
                    Route::get('/', AsignacionTurnoPanel::class)->name('index');
                });

            // Plan de Cuidado y Tareas
            Route::prefix('plan-cuidado')
                ->name('plan-cuidado.')
                ->middleware('permission:plan_cuidado.ver')
                ->group(function () {
                    Route::get('/', PlanCuidadoPanel::class)->name('index');
                    Route::get('/tareas', TareasPlanPanel::class)
                        ->middleware('permission:tareas.ver')
                        ->name('tareas');
                });

            // Seguimiento Diario
            Route::prefix('seguimiento-diario')
                ->name('seguimiento-diario.')
                ->middleware('permission:seguimiento.ver')
                ->group(function () {
                    Route::get('/', SeguimientoDiarioPanel::class)->name('index');
                });

            // Alertas y Acciones
            Route::prefix('alertas-clinicas')
                ->name('alertas-clinicas.')
                ->middleware('permission:alertas.ver')
                ->group(function () {
                    Route::get('/', AlertasPanel::class)->name('index');
                });

            // Pase de Turno
            Route::prefix('pase-turno')
                ->name('pase-turno.')
                ->middleware('permission:pase_turno.ver')
                ->group(function () {
                    Route::get('/', PaseTurnoPanel::class)->name('index');
                });

            // ── Actividades ─────────────────────────
            Route::prefix('actividades')
                ->name('actividades.')
                ->middleware('permission:actividades.ver')
                ->group(function () {
                    Route::get('/', ActividadesPanel::class)->name('index');
                    Route::get('/tipos', TiposActividadPanel::class)->name('tipos');
                    Route::get('/participacion', ParticipacionPanel::class)->name('participacion');
                    Route::get('/asistencia', AsistenciaPanel::class)->name('asistencia');
                    Route::get('/reportes', ReportesActividadesPanel::class)->name('reportes');
                });

            // Voluntariado
            Route::prefix('voluntariado')
                ->name('voluntariado.')
                ->middleware('permission:voluntarios.ver')
                ->group(function () {
                    Route::view('/', 'pages.voluntariado.index')->name('index');
                    Route::view('/voluntarios', 'pages.voluntariado.voluntarios')->name('voluntarios.index');
                    Route::view('/disponibilidad', 'pages.voluntariado.disponibilidad')->name('disponibilidad.index');
                    Route::view('/asignaciones', 'pages.voluntariado.asignaciones')->name('asignaciones.index');
                    Route::view('/asistencia', 'pages.voluntariado.asistencia')->name('asistencia.index');
                    Route::view('/reportes', 'pages.voluntariado.index')->name('reportes.index');
                });

            // ── Enfermería ─────────────────────────
            Route::prefix('enfermeria')
                ->name('enfermeria.')
                ->group(function () {
                    Route::get('/dashboard', DashboardTurno::class)
                        ->middleware('permission:enfermeria.ver_dashboard')
                        ->name('dashboard');

                    Route::get('/agenda', AgendaEnfermeria::class)
                        ->middleware('permission:enfermeria.ver_dashboard')
                        ->name('agenda');

                    Route::get('/registros', RegistrosEnfermeria::class)
                        ->middleware('permission:seguimiento.ver')
                        ->name('registros');

                    Route::get('/pacientes', MisPacientes::class)
                        ->middleware('permission:enfermeria.ver_pacientes_asignados')
                        ->name('pacientes');

                    Route::get('/pacientes/{adulto}', FichaPaciente::class)
                        ->middleware('permission:enfermeria.ver_ficha_paciente')
                        ->name('pacientes.ficha');

                    Route::get('/pacientes/{adulto}/pdf', [FichaPacienteReporteController::class, 'pdf'])
                        ->middleware('permission:enfermeria.ver_ficha_paciente')
                        ->name('pacientes.ficha.pdf');

                    Route::get('/tareas', TareasPlanPanel::class)
                        ->middleware('permission:tareas.ver')
                        ->name('tareas');

                    Route::get('/alertas', AlertasPanel::class)
                        ->middleware('permission:alertas.ver|alertas.gestionar|salud.alertas.ver|salud.alertas.gestionar')
                        ->name('alertas');

                    Route::get('/pase-turno', PaseTurnoPanel::class)
                        ->middleware('permission:pase_turno.ver')
                        ->name('pase-turno');

                    Route::get('/reportes', ReporteEnfermeria::class)
                        ->middleware('permission:enfermeria.ver_dashboard')
                        ->name('reportes');
                });

            // ── Médico General ─────────────────────────
            Route::prefix('medico')
                ->name('medico.')
                ->group(function () {
                    // Dashboard: estadísticas, KPIs, gráficos, cola de valoraciones
                    Route::get('/dashboard', DashboardMedico::class)
                        ->name('dashboard');

                    // Admisiones: valoraciones y decisiones se muestran en el dashboard
                    // con filtro de sección (querystring ?seccion=X)
                    Route::get('/valoraciones-medicas', DashboardMedico::class)
                        ->name('valoraciones');
                    Route::get('/decisiones-admision', DashboardMedico::class)
                        ->name('decisiones');

                    // Seguimiento: lista de pacientes con tabs
                    // Tab 'activos' = seguimiento general
                    Route::get('/pacientes-seguimiento', PacientesSeguimientoPanel::class)
                        ->name('pacientes.observacion');

                    // Tab 'historial' = todos los pacientes (sin filtro de estado)
                    Route::get('/historial-clinico', PacientesSeguimientoPanel::class)
                        ->name('pacientes.historial');

                    // Tab 'interconsultas' = pacientes con notas INTERCONSULTA activas
                    Route::get('/interconsultas', PacientesSeguimientoPanel::class)
                        ->name('interconsultas');

                    // Monitor de signos vitales: todos los pacientes activos con alertas PA/FC/SpO2/Temp/Glucosa
                    Route::get('/signos-vitales', SignosVitalesPanel::class)
                        ->name('signos-vitales');

                    // Ficha clínica integrada por paciente (6 tabs: resumen, notas, signos, medicación, funcional, geriátrico)
                    Route::get('/paciente/{adulto}', FichaClinicaIntegradaPanel::class)
                        ->name('paciente.ficha');
                });

            // ── Psicología ─────────────────────────
            Route::prefix('psicologia')
                ->name('psicologia.')
                ->group(function () {
                    Route::get('/dashboard', DashboardPsicologo::class)
                        ->name('dashboard');

                    // Evaluaciones asignadas → dashboard principal
                    Route::get('/evaluaciones-asignadas', DashboardPsicologo::class)
                        ->name('evaluaciones');
                    Route::get('/seguimiento-emocional', [DashboardController::class, 'index'])
                        ->name('seguimiento');
                    Route::get('/alertas-conductuales', [DashboardController::class, 'index'])
                        ->name('alertas');

                    // Evaluaciones por área geriátrica
                    Route::get('/evaluacion/cognitiva', EvaluacionesAreaPanel::class)
                        ->defaults('codArea', 'ARE_COG')
                        ->name('evaluacion.cognitiva');
                    Route::get('/evaluacion/afectiva', EvaluacionesAreaPanel::class)
                        ->defaults('codArea', 'ARE_AFE')
                        ->name('evaluacion.afectiva');
                    Route::get('/evaluacion/funcionamiento', EvaluacionesAreaPanel::class)
                        ->defaults('codArea', 'ARE_FUN')
                        ->name('evaluacion.funcionamiento');
                    Route::get('/evaluacion/nutricional', EvaluacionesAreaPanel::class)
                        ->defaults('codArea', 'ARE_NUT')
                        ->name('evaluacion.nutricional');
                    Route::get('/evaluacion/entorno', EvaluacionesAreaPanel::class)
                        ->defaults('codArea', 'ARE_SOC')
                        ->name('evaluacion.entorno');

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
            Route::get('bitacora', [BitacoraController::class, 'index'])
                ->middleware('permission:bitacora.ver')
                ->name('bitacora.index');
        });
});
