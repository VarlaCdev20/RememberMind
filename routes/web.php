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
use App\Livewire\Cuidados\AgendaEnfermeria;
use App\Livewire\Cuidados\AsignacionTurnoPanel;
use App\Livewire\Cuidados\DashboardTurno;
use App\Livewire\Cuidados\FichaPaciente;
use App\Livewire\Cuidados\MisPacientes;
use App\Livewire\Cuidados\PaseTurnoPanel;
use App\Livewire\Cuidados\PlanCuidadoPanel;
use App\Livewire\Cuidados\RegistrosEnfermeria;
use App\Livewire\Cuidados\ReporteEnfermeria;
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

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('pages.welcome');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| RUTAS AUTENTICADAS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | RESOLUCIÓN DEL DASHBOARD DESPUÉS DEL LOGIN
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    | - El médico debe evaluarse ANTES que Enfermería.
    | - Esto corrige el caso en el que un médico con permisos compartidos
    |   terminaba inicialmente en el dashboard de Enfermería.
    | - Los perfiles no contemplados todavía conservan el DashboardController
    |   general como fallback para no romper áreas existentes.
    |
    */
    Route::get('/dashboard', function () {
        $usuario = auth()->user();

        abort_unless($usuario, 401);

        // Administración siempre tiene prioridad sobre los paneles operativos.
        if ($usuario->hasAnyRole([
            'SUPERADMINISTRADOR',
            'ADMINISTRADOR',
            'superadmin',
            'admin',
        ])) {
            return redirect()->route('admin.administracion.dashboard');
        }

        // Médico / Geriatría: se resuelve antes que Enfermería.
        // Se utiliza un permiso existente del proyecto para no inventar
        // una dependencia nueva antes de actualizar el seeder de permisos.
        if ($usuario->can('valoracion_medica.ver')) {
            return redirect()->route('admin.medico.dashboard');
        }

        // Enfermería.
        if ($usuario->can('enfermeria.ver_dashboard')) {
            return redirect()->route('admin.enfermeria.dashboard');
        }

        // Fallback para Psicología, Fisioterapia, Nutrición, Voluntariado,
        // Familiar u otros perfiles mientras su resolución se centraliza.
        return app(DashboardController::class)->index();
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ÁREA ADMINISTRATIVA / INSTITUCIONAL
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {

            /*
            |------------------------------------------------------------------
            | PANEL DE ADMINISTRACIÓN
            |------------------------------------------------------------------
            */
            Route::prefix('administracion')
                ->name('administracion.')
                ->group(function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])
                        ->name('dashboard');

                    Route::get('/', [DashboardController::class, 'index'])
                        ->name('index');
                });

            Route::get('vistas-extra', function () {
                abort_unless(auth()->user()?->hasRole('SUPERADMINISTRADOR'), 403);

                return view('pages.admin.vistas-extra');
            })->name('vistas-extra');

            /*
            |------------------------------------------------------------------
            | IDENTIDAD, USUARIOS Y PERSONAL
            |------------------------------------------------------------------
            */

            Route::prefix('usuarios')
                ->name('usuarios.')
                ->group(function () {
                    Route::get('/', [UsuarioController::class, 'index'])
                        ->middleware('permission:usuarios.ver')
                        ->name('index');

                    Route::get('/create', [UsuarioController::class, 'create'])
                        ->middleware('permission:usuarios.crear')
                        ->name('create');

                    Route::post('/', [UsuarioController::class, 'store'])
                        ->middleware('permission:usuarios.crear')
                        ->name('store');

                    Route::get('/{usuario}', [UsuarioController::class, 'show'])
                        ->middleware('permission:usuarios.ver')
                        ->name('show');

                    Route::get('/{usuario}/edit', [UsuarioController::class, 'edit'])
                        ->middleware('permission:usuarios.editar')
                        ->name('edit');

                    Route::match(['put', 'patch'], '/{usuario}', [UsuarioController::class, 'update'])
                        ->middleware('permission:usuarios.editar')
                        ->name('update');

                    Route::delete('/{usuario}', [UsuarioController::class, 'destroy'])
                        ->middleware('permission:usuarios.editar')
                        ->name('destroy');

                    Route::patch('/{usuario}/desactivar', [UsuarioController::class, 'desactivar'])
                        ->middleware('permission:usuarios.cambiar_estado')
                        ->name('desactivar');

                    Route::patch('/{usuario}/activar', [UsuarioController::class, 'activar'])
                        ->middleware('permission:usuarios.cambiar_estado')
                        ->name('activar');

                    Route::get('/{usuario}/ficha/pdf', [UsuarioController::class, 'fichaPdf'])
                        ->middleware('permission:usuarios.reportes.pdf')
                        ->name('ficha.pdf');

                    Route::get('/{usuario}/documentacion/pdf', [UsuarioController::class, 'documentacionPdf'])
                        ->middleware('permission:usuarios.reportes.pdf')
                        ->name('documentacion.pdf');

                    Route::get('/{usuario}/solicitud-documental/pdf', [UsuarioController::class, 'solicitudDocumentalPdf'])
                        ->middleware('permission:usuarios.ver')
                        ->name('solicitud-documental.pdf');

                    Route::get('/{usuario}/horarios/pdf', [UsuarioController::class, 'horariosPdf'])
                        ->middleware('permission:usuarios.reportes.pdf')
                        ->name('horarios.pdf');

                    Route::post('/{usuario}/ficha/enviar-correo', [UsuarioController::class, 'enviarFichaCorreo'])
                        ->middleware('permission:usuarios.ver')
                        ->name('ficha.enviar-correo');
                });

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

            Route::get('/personal-institucional', PersonalInstitucionalPanel::class)
                ->middleware('permission:personal_institucional.ver')
                ->name('personal-institucional');

            Route::view('/roles-permisos', 'pages.roles-permisos.index')
                ->middleware('permission:roles.ver')
                ->name('roles-permisos.index');

            Route::view('/areas-institucionales', 'pages.areas-institucionales.index')
                ->middleware('permission:areas.ver')
                ->name('areas-institucionales.index');

            Route::view('/turnos-asignaciones', 'pages.turnos-asignaciones.index')
                ->middleware('permission:turnos.ver')
                ->name('turnos-asignaciones.index');

            /*
            |------------------------------------------------------------------
            | REPORTES DE ÁREAS INSTITUCIONALES
            |------------------------------------------------------------------
            */
            Route::prefix('areas-institucionales/reportes')
                ->name('areas-institucionales.reportes.')
                ->middleware('permission:areas.reportes')
                ->group(function () {
                    Route::get('/general/pdf', [AreaReporteController::class, 'generalPdf'])->name('general.pdf');
                    Route::get('/general/excel', [AreaReporteController::class, 'generalExcel'])->name('general.excel');
                    Route::get('/general/csv', [AreaReporteController::class, 'generalCsv'])->name('general.csv');
                    Route::get('/{area}/pdf', [AreaReporteController::class, 'areaPdf'])->name('area.pdf');
                    Route::get('/{area}/excel', [AreaReporteController::class, 'areaExcel'])->name('area.excel');
                });

            /*
            |------------------------------------------------------------------
            | ADMISIONES / PREADMISIÓN
            |------------------------------------------------------------------
            */

            // Debe declararse antes de adultos-mayores/{adulto_mayor}
            // para evitar colisiones con el binding del resource.
            Route::get('adultos-mayores/alertas-pendientes', AlertasPendientesPanel::class)
                ->middleware('permission:adultos.ver')
                ->name('adultos-mayores.alertas-pendientes');

            Route::prefix('admisiones')
                ->name('admisiones.')
                ->middleware('permission:admisiones.ver_dashboard')
                ->group(function () {
                    Route::get('/preadmisiones', PreadmisionesPanel::class)
                        ->name('preadmisiones');

                    Route::get('/preadmisiones/rechazadas', PreadmisionesPanel::class)
                        ->name('preadmisiones.rechazadas');

                    Route::get('/preadmision', PreadmisionWizard::class)
                        ->middleware('permission:admisiones.crear')
                        ->name('preadmision');
                });

            /*
            |------------------------------------------------------------------
            | RESIDENTES / ADULTOS MAYORES
            |------------------------------------------------------------------
            */

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

            /*
            |------------------------------------------------------------------
            | SUBMÓDULOS DEL RESIDENTE
            |------------------------------------------------------------------
            |
            | El grupo exige adultos.ver.
            | Cada operación de escritura agrega además su permiso específico.
            |
            | Pendiente de validar en el seeder:
            | - ficha_medica.crear / ficha_medica.editar / ficha_medica.archivar
            | - valoracion_funcional.crear / valoracion_funcional.editar
            | - administracion_medicacion.registrar
            |
            */
            Route::prefix('adultos-mayores/{adulto_mayor}')
                ->middleware('permission:adultos.ver')
                ->name('adultos-mayores.')
                ->group(function () {

                    // Familiares
                    Route::get('/familiares', [AdultoMayorFamiliarController::class, 'index'])->name('familiares.index');
                    Route::post('/familiares', [AdultoMayorFamiliarController::class, 'store'])->middleware('permission:familiares.crear')->name('familiares.store');
                    Route::patch('/familiares/{familiar}', [AdultoMayorFamiliarController::class, 'update'])->middleware('permission:familiares.editar')->name('familiares.update');
                    Route::delete('/familiares/{familiar}', [AdultoMayorFamiliarController::class, 'destroy'])->middleware('permission:familiares.anular')->name('familiares.destroy');
                    Route::patch('/familiares/{familiar}/restaurar', [AdultoMayorFamiliarController::class, 'restore'])->middleware('permission:familiares.editar')->name('familiares.restore');

                    // Observaciones
                    Route::get('/observaciones', [AdultoMayorObservacionController::class, 'index'])->name('observaciones.index');
                    Route::post('/observaciones', [AdultoMayorObservacionController::class, 'store'])->middleware('permission:observaciones.crear')->name('observaciones.store');
                    Route::patch('/observaciones/{observacion}', [AdultoMayorObservacionController::class, 'update'])->middleware('permission:observaciones.editar')->name('observaciones.update');
                    Route::delete('/observaciones/{observacion}', [AdultoMayorObservacionController::class, 'destroy'])->middleware('permission:observaciones.anular')->name('observaciones.destroy');
                    Route::patch('/observaciones/{observacion}/restaurar', [AdultoMayorObservacionController::class, 'restore'])->middleware('permission:observaciones.editar')->name('observaciones.restore');

                    // Atenciones / Consultas base
                    Route::get('/atenciones', [AdultoMayorAtencionController::class, 'index'])->name('atenciones.index');
                    Route::post('/atenciones', [AdultoMayorAtencionController::class, 'store'])->middleware('permission:atenciones.crear')->name('atenciones.store');
                    Route::patch('/atenciones/{atencion}', [AdultoMayorAtencionController::class, 'update'])->middleware('permission:atenciones.editar')->name('atenciones.update');
                    Route::delete('/atenciones/{atencion}', [AdultoMayorAtencionController::class, 'destroy'])->middleware('permission:atenciones.anular')->name('atenciones.destroy');
                    Route::patch('/atenciones/{atencion}/restaurar', [AdultoMayorAtencionController::class, 'restore'])->middleware('permission:atenciones.editar')->name('atenciones.restore');

                    // Evaluaciones cognitivas
                    Route::get('/evaluaciones', [AdultoMayorEvaluacionController::class, 'index'])->name('evaluaciones.index');
                    Route::post('/evaluaciones', [AdultoMayorEvaluacionController::class, 'store'])->middleware('permission:evaluaciones.crear')->name('evaluaciones.store');
                    Route::get('/evaluaciones/{evaluacion}', [AdultoMayorEvaluacionController::class, 'show'])->name('evaluaciones.show');
                    Route::delete('/evaluaciones/{evaluacion}', [AdultoMayorEvaluacionController::class, 'destroy'])->middleware('permission:evaluaciones.anular')->name('evaluaciones.destroy');
                    Route::patch('/evaluaciones/{evaluacion}/restaurar', [AdultoMayorEvaluacionController::class, 'restore'])->middleware('permission:evaluaciones.editar')->name('evaluaciones.restore');

                    // Evaluaciones geriátricas integrales
                    Route::delete('/evaluaciones-geriatricas/{evaluacion}/anular', [AdultoMayorController::class, 'anularEvaluacionGeriatrica'])
                        ->middleware('permission:evaluaciones.anular')
                        ->name('evaluaciones-geriatricas.anular');

                    Route::get('/evaluaciones-geriatricas/{evaluacion}/pdf', [AdultoMayorController::class, 'pdfEvaluacionGeriatrica'])
                        ->middleware('permission:reportes.individual')
                        ->name('evaluaciones-geriatricas.pdf');

                    // Actividades
                    Route::post('/actividades', [AdultoMayorActividadController::class, 'store'])->middleware('permission:actividades.crear')->name('actividades.store');
                    Route::patch('/actividades/{actividad}', [AdultoMayorActividadController::class, 'update'])->middleware('permission:actividades.editar')->name('actividades.update');
                    Route::delete('/actividades/{actividad}', [AdultoMayorActividadController::class, 'destroy'])->middleware('permission:actividades.anular')->name('actividades.destroy');
                    Route::patch('/actividades/{actividad}/restaurar', [AdultoMayorActividadController::class, 'restore'])->middleware('permission:actividades.editar')->name('actividades.restore');

                    // Documentos
                    Route::get('/documentos', [AdultoMayorDocumentoController::class, 'index'])->name('documentos.index');
                    Route::get('/documentos/{documento}/archivo', [AdultoMayorDocumentoController::class, 'archivo'])->name('documentos.archivo');
                    Route::post('/documentos', [AdultoMayorDocumentoController::class, 'store'])->middleware('permission:documentos.subir')->name('documentos.store');
                    Route::patch('/documentos/{documento}', [AdultoMayorDocumentoController::class, 'update'])->middleware('permission:documentos.subir')->name('documentos.update');
                    Route::delete('/documentos/{documento}', [AdultoMayorDocumentoController::class, 'destroy'])->middleware('permission:documentos.archivar')->name('documentos.destroy');
                    Route::patch('/documentos/{documento}/restaurar', [AdultoMayorDocumentoController::class, 'restore'])->middleware('permission:documentos.archivar')->name('documentos.restore');

                    // Ficha médica
                    Route::post('/ficha-medica', [AdultoMayorFichaMedicaController::class, 'store'])->middleware('permission:ficha_medica.crear')->name('ficha-medica.store');
                    Route::put('/ficha-medica/{ficha}', [AdultoMayorFichaMedicaController::class, 'update'])->middleware('permission:ficha_medica.editar')->name('ficha-medica.update');
                    Route::patch('/ficha-medica/{ficha}/archivar', [AdultoMayorFichaMedicaController::class, 'archivar'])->middleware('permission:ficha_medica.archivar')->name('ficha-medica.archivar');
                    Route::patch('/ficha-medica/{ficha}/restaurar', [AdultoMayorFichaMedicaController::class, 'restore'])->middleware('permission:ficha_medica.archivar')->name('ficha-medica.restore');

                    // Medicación / prescripción
                    Route::post('/medicacion', [AdultoMayorMedicacionController::class, 'store'])->middleware('permission:medicacion.crear')->name('medicacion.store');
                    Route::put('/medicacion/{medicacion}', [AdultoMayorMedicacionController::class, 'update'])->middleware('permission:medicacion.editar')->name('medicacion.update');
                    Route::patch('/medicacion/{medicacion}/suspender', [AdultoMayorMedicacionController::class, 'suspender'])->middleware('permission:medicacion.suspender')->name('medicacion.suspender');
                    Route::patch('/medicacion/{medicacion}/finalizar', [AdultoMayorMedicacionController::class, 'finalizar'])->middleware('permission:medicacion.editar')->name('medicacion.finalizar');
                    Route::patch('/medicacion/{medicacion}/archivar', [AdultoMayorMedicacionController::class, 'archivar'])->middleware('permission:medicacion.editar')->name('medicacion.archivar');
                    Route::patch('/medicacion/{medicacion}/restaurar', [AdultoMayorMedicacionController::class, 'restore'])->middleware('permission:medicacion.editar')->name('medicacion.restore');

                    // Administración de medicación
                    Route::post('/administracion-medicacion', [AdultoMayorAdministracionMedicacionController::class, 'store'])
                        ->middleware('permission:administracion_medicacion.registrar')
                        ->name('administracion-medicacion.store');

                    // Signos vitales
                    Route::post('/signos-vitales', [AdultoMayorSignosVitalesController::class, 'store'])->middleware('permission:signos_vitales.crear')->name('signos-vitales.store');
                    Route::put('/signos-vitales/{signo}', [AdultoMayorSignosVitalesController::class, 'update'])->middleware('permission:signos_vitales.editar')->name('signos-vitales.update');

                    // Valoración funcional
                    Route::post('/valoracion-funcional', [AdultoMayorValoracionFuncionalController::class, 'store'])->middleware('permission:valoracion_funcional.crear')->name('valoracion-funcional.store');
                    Route::put('/valoracion-funcional/{valoracion}', [AdultoMayorValoracionFuncionalController::class, 'update'])->middleware('permission:valoracion_funcional.editar')->name('valoracion-funcional.update');

                    // Reportes individuales
                    Route::get('/reporte-individual', [AdultoMayorController::class, 'reporteIndividual'])
                        ->middleware('permission:reportes.individual')
                        ->name('reporte-individual');

                    Route::get('/reportes/{tipo}', [AdultoMayorController::class, 'reporteEspecifico'])
                        ->middleware('permission:reportes.individual')
                        ->name('reportes.especifico');
                });

            /*
            |------------------------------------------------------------------
            | REPORTES DE RESIDENTES
            |------------------------------------------------------------------
            */
            Route::get('reporte-general', [AdultoMayorController::class, 'reporteGeneral'])
                ->middleware('permission:reportes.ver')
                ->name('adultos-mayores.reporte-general');

            Route::get('reporte-institucional', ReportesInstitucionalesPanel::class)
                ->middleware('permission:reportes.ver')
                ->name('adultos-mayores.reporte-institucional');

            Route::get('reporte-bienestar', [AdultoMayorController::class, 'reporteBienestar'])
                ->middleware('permission:reportes.ver')
                ->name('adultos-mayores.reporte-bienestar');

            /*
            |------------------------------------------------------------------
            | SALUD Y SEGUIMIENTO GENERAL
            |------------------------------------------------------------------
            */
            Route::prefix('salud-seguimiento')
                ->name('salud-seguimiento.')
                ->middleware('permission:salud.ver')
                ->group(function () {
                    // Índices globales
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

                    // Paneles individuales
                    Route::get('/{adulto}/resumen', SaludResumenPanel::class)->name('resumen');
                    Route::get('/{adulto}/ficha', SaludFichaPanel::class)->name('ficha');
                    Route::get('/{adulto}/medicacion', SaludMedicacionPanel::class)->name('medicacion');
                    Route::get('/{adulto}/administracion', SaludAdministracionMedicacionPanel::class)->name('administracion');
                    Route::get('/{adulto}/signos', SaludSignosPanel::class)->name('signos');
                    Route::get('/{adulto}/valoracion', SaludValoracionPanel::class)->name('valoracion');
                    Route::get('/{adulto}/evaluaciones-geriatricas', SaludEvaluacionesGeriatricasPanel::class)->name('evaluaciones-geriatricas');
                });

            /*
            |------------------------------------------------------------------
            | FAMILIA Y SOCIAL
            |------------------------------------------------------------------
            */
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

            /*
            |------------------------------------------------------------------
            | INFRAESTRUCTURA Y FLUJO CLÍNICO
            |------------------------------------------------------------------
            */

            Route::prefix('habitaciones')
                ->name('habitaciones.')
                ->middleware('permission:habitaciones.ver')
                ->group(function () {
                    Route::get('/', HabitacionesPanel::class)->name('index');
                });

            Route::prefix('turnos-enfermeria')
                ->name('turnos-enfermeria.')
                ->middleware('permission:turnos_enfermeria.ver')
                ->group(function () {
                    Route::get('/', TurnosEnfermeriaPanel::class)->name('index');
                });

            /*
            | Admisión clínica
            |
            | CORRECCIÓN IMPORTANTE:
            | Antes el prefijo completo exigía valoracion_enfermeria.ver,
            | por lo que un médico necesitaba también el permiso de Enfermería.
            | Ahora cada valoración exige únicamente su permiso correspondiente.
            */
            Route::prefix('admision')
                ->name('admision.')
                ->group(function () {
                    Route::get('/valoracion-enfermeria', ValoracionEnfermeriaPanel::class)
                        ->middleware('permission:valoracion_enfermeria.ver')
                        ->name('valoracion-enfermeria');

                    Route::get('/valoracion-medica', ValoracionMedicaPanel::class)
                        ->middleware('permission:valoracion_medica.ver')
                        ->name('valoracion-medica');
                });

            Route::prefix('asignacion-turno')
                ->name('asignacion-turno.')
                ->middleware('permission:turnos.ver')
                ->group(function () {
                    Route::get('/', AsignacionTurnoPanel::class)->name('index');
                });

            Route::prefix('plan-cuidado')
                ->name('plan-cuidado.')
                ->middleware('permission:plan_cuidado.ver')
                ->group(function () {
                    Route::get('/', PlanCuidadoPanel::class)->name('index');
                    Route::get('/tareas', TareasPlanPanel::class)
                        ->middleware('permission:tareas.ver')
                        ->name('tareas');
                });

            Route::prefix('seguimiento-diario')
                ->name('seguimiento-diario.')
                ->middleware('permission:seguimiento.ver')
                ->group(function () {
                    Route::get('/', SeguimientoDiarioPanel::class)->name('index');
                });

            Route::prefix('alertas-clinicas')
                ->name('alertas-clinicas.')
                ->middleware('permission:alertas.ver')
                ->group(function () {
                    Route::get('/', AlertasPanel::class)->name('index');
                });

            Route::prefix('pase-turno')
                ->name('pase-turno.')
                ->middleware('permission:pase_turno.ver')
                ->group(function () {
                    Route::get('/', PaseTurnoPanel::class)->name('index');
                });

            /*
            |------------------------------------------------------------------
            | ACTIVIDADES Y VOLUNTARIADO
            |------------------------------------------------------------------
            */

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

            /*
            |------------------------------------------------------------------
            | ENFERMERÍA
            |------------------------------------------------------------------
            */
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

            /*
            |------------------------------------------------------------------
            | MÉDICO GENERAL / GERIATRÍA
            |------------------------------------------------------------------
            |
            | Todos los residentes activos pertenecen al universo clínico del
            | médico general del geriátrico; no se utiliza asignación médico-
            | residente como en Enfermería.
            |
            | Se utiliza valoracion_medica.ver como puerta de acceso existente.
            | Cuando agregues un permiso dedicado (p.ej. medico.ver_dashboard),
            | conviene sustituir este middleware por el nuevo permiso.
            |
            */
            Route::prefix('medico')
                ->name('medico.')
                ->middleware('permission:valoracion_medica.ver')
                ->group(function () {

                    // Inicio
                    Route::get('/dashboard', DashboardMedico::class)
                        ->name('dashboard');

                    // Residentes: todos los residentes del centro.
                    Route::get('/residentes', PacientesSeguimientoPanel::class)
                        ->name('residentes');

                    // Compatibilidad con rutas/vistas existentes.
                    Route::get('/pacientes-seguimiento', PacientesSeguimientoPanel::class)
                        ->name('pacientes.observacion');

                    Route::get('/historial-clinico', PacientesSeguimientoPanel::class)
                        ->name('pacientes.historial');

                    // Consultas / Evoluciones.
                    // Por ahora reutiliza el panel de seguimiento; cuando exista el
                    // componente específico de consulta médica se reemplaza aquí.
                    Route::get('/consultas-evoluciones', PacientesSeguimientoPanel::class)
                        ->name('consultas');

                    // Valoraciones de admisión.
                    Route::get('/valoraciones-medicas', ValoracionMedicaPanel::class)
                        ->name('valoraciones');

                    // Se conserva esta entrada para compatibilidad con el dashboard
                    // actual hasta revisar la lógica interna de DashboardMedico.
                    Route::get('/decisiones-admision', DashboardMedico::class)
                        ->name('decisiones');

                    // Interconsultas.
                    Route::get('/interconsultas', PacientesSeguimientoPanel::class)
                        ->name('interconsultas');

                    // Cognición y riesgo: reutiliza el listado de evaluaciones
                    // geriátricas existente sin duplicar lógica.
                    Route::get('/cognicion-riesgo', SaludSeguimientoListPanel::class)
                        ->defaults('seccion', 'evaluaciones')
                        ->name('cognicion-riesgo');

                    // Monitor clínico de signos vitales.
                    Route::get('/signos-vitales', SignosVitalesPanel::class)
                        ->name('signos-vitales');

                    // Medicación: listado global para revisión médica.
                    Route::get('/medicacion', SaludSeguimientoListPanel::class)
                        ->defaults('seccion', 'medicacion')
                        ->name('medicacion');

                    // Alertas clínicas.
                    Route::get('/alertas', AlertasPanel::class)
                        ->name('alertas');

                    // Reportes clínicos del área médica.
                    Route::get('/reportes', SaludSeguimientoListPanel::class)
                        ->defaults('seccion', 'reportes')
                        ->name('reportes');

                    // Ficha clínica integrada por residente.
                    Route::get('/residente/{adulto}', FichaClinicaIntegradaPanel::class)
                        ->name('residente.ficha');

                    // Compatibilidad con enlaces existentes que todavía usan /paciente/.
                    Route::get('/paciente/{adulto}', FichaClinicaIntegradaPanel::class)
                        ->name('paciente.ficha');
                });

            /*
            |------------------------------------------------------------------
            | PSICOLOGÍA
            |------------------------------------------------------------------
            */
            Route::prefix('psicologia')
                ->name('psicologia.')
                ->group(function () {
                    Route::get('/dashboard', DashboardPsicologo::class)->name('dashboard');
                    Route::get('/evaluaciones-asignadas', DashboardPsicologo::class)->name('evaluaciones');
                    Route::get('/seguimiento-emocional', [DashboardController::class, 'index'])->name('seguimiento');
                    Route::get('/alertas-conductuales', [DashboardController::class, 'index'])->name('alertas');

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

                    Route::get('/pacientes-derivados', [DashboardController::class, 'index'])
                        ->name('pacientes.derivados');

                    Route::get('/historial-psicologico', [DashboardController::class, 'index'])
                        ->name('pacientes.historial');

                    Route::get('/reportes-psicologicos', [DashboardController::class, 'index'])
                        ->name('reportes');
                });

            /*
            |------------------------------------------------------------------
            | FISIOTERAPIA
            |------------------------------------------------------------------
            */
            Route::prefix('fisioterapia')
                ->name('fisioterapia.')
                ->group(function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
                    Route::get('/pacientes-derivados', [DashboardController::class, 'index'])->name('pacientes.derivados');
                    Route::get('/valoracion-funcional', [DashboardController::class, 'index'])->name('valoracion');
                    Route::get('/plan-funcional', [DashboardController::class, 'index'])->name('plan');
                    Route::get('/evolucion-fisica', [DashboardController::class, 'index'])->name('evolucion');
                    Route::get('/riesgo-caida', [DashboardController::class, 'index'])->name('riesgo.caida');
                    Route::get('/alertas-funcionales', [DashboardController::class, 'index'])->name('alertas');
                    Route::get('/reportes-fisioterapia', [DashboardController::class, 'index'])->name('reportes');
                });

            /*
            |------------------------------------------------------------------
            | NUTRICIÓN
            |------------------------------------------------------------------
            */
            Route::prefix('nutricion')
                ->name('nutricion.')
                ->group(function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
                    Route::get('/pacientes-derivados', [DashboardController::class, 'index'])->name('pacientes.derivados');
                    Route::get('/valoracion-nutricional', [DashboardController::class, 'index'])->name('valoracion');
                    Route::get('/plan-alimentario', [DashboardController::class, 'index'])->name('plan');
                    Route::get('/seguimiento-nutricional', [DashboardController::class, 'index'])->name('seguimiento');
                    Route::get('/peso-imc', [DashboardController::class, 'index'])->name('control.peso');
                    Route::get('/hidratacion', [DashboardController::class, 'index'])->name('control.hidratacion');
                    Route::get('/alertas-nutricionales', [DashboardController::class, 'index'])->name('alertas');
                    Route::get('/reportes-nutricionales', [DashboardController::class, 'index'])->name('reportes');
                });

            /*
            |------------------------------------------------------------------
            | VOLUNTARIO
            |------------------------------------------------------------------
            */
            Route::prefix('voluntario')
                ->name('voluntario.')
                ->group(function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
                    Route::get('/mis-actividades', [DashboardController::class, 'index'])->name('actividades');
                    Route::get('/asistencia', [DashboardController::class, 'index'])->name('asistencia');
                    Route::get('/disponibilidad', [DashboardController::class, 'index'])->name('disponibilidad');
                    Route::get('/adultos-asignados', [DashboardController::class, 'index'])->name('adultos.asignados');
                    Route::get('/reportes-actividades', [DashboardController::class, 'index'])->name('reportes');
                });

            /*
            |------------------------------------------------------------------
            | PORTAL FAMILIAR
            |------------------------------------------------------------------
            */
            Route::prefix('portal-familiar')
                ->name('familiar.')
                ->group(function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
                    Route::get('/resumen-estado', [DashboardController::class, 'index'])->name('resumen');
                    Route::get('/actividades', [DashboardController::class, 'index'])->name('actividades');
                    Route::get('/visitas', [DashboardController::class, 'index'])->name('visitas');
                    Route::get('/estado-cuenta', [DashboardController::class, 'index'])->name('pagos');
                    Route::get('/documentos', [DashboardController::class, 'index'])->name('documentos');
                });

            /*
            |------------------------------------------------------------------
            | REPORTES INSTITUCIONALES
            |------------------------------------------------------------------
            */
            Route::prefix('reportes')
                ->name('reportes.')
                ->group(function () {

                    // Institucional
                    Route::get('/institucional', [ReporteInstitucionalController::class, 'preview'])
                        ->middleware('permission:reportes.institucional')
                        ->name('institucional.preview');

                    Route::get('/institucional/pdf', [ReporteInstitucionalController::class, 'pdf'])
                        ->middleware('permission:reportes.institucional')
                        ->name('institucional.pdf');

                    Route::get('/institucional/excel', [ReporteInstitucionalController::class, 'excel'])
                        ->middleware('permission:reportes.institucional')
                        ->name('institucional.excel');

                    // Adultos mayores
                    Route::get('/adultos', [ReporteAdultosController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('adultos.preview');

                    Route::get('/adultos/pdf', [ReporteAdultosController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('adultos.pdf');

                    Route::get('/adultos/excel', [ReporteAdultosController::class, 'excel'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('adultos.excel');

                    // Salud y seguimiento
                    Route::get('/salud', [ReporteSaludController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('salud.preview');

                    Route::get('/salud/pdf', [ReporteSaludController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('salud.pdf');

                    Route::get('/salud/excel', [ReporteSaludController::class, 'excel'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('salud.excel');

                    // Familiares
                    Route::get('/familiares', [ReporteFamiliaresController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('familiares.preview');

                    Route::get('/familiares/pdf', [ReporteFamiliaresController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('familiares.pdf');

                    Route::get('/familiares/excel', [ReporteFamiliaresController::class, 'excel'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('familiares.excel');

                    // Equipo institucional
                    Route::get('/equipo', [ReporteEquipoController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('equipo.preview');

                    Route::get('/equipo/pdf', [ReporteEquipoController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('equipo.pdf');

                    Route::get('/equipo/excel', [ReporteEquipoController::class, 'excel'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('equipo.excel');

                    // Actividades
                    Route::get('/actividades', [ReporteActividadesController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('actividades.preview');

                    Route::get('/actividades/pdf', [ReporteActividadesController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('actividades.pdf');

                    Route::get('/actividades/excel', [ReporteActividadesController::class, 'excel'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('actividades.excel');

                    // Bitácora (sin Excel por seguridad)
                    Route::get('/bitacora', [ReporteBitacoraController::class, 'preview'])
                        ->middleware('permission:reportes.ver')
                        ->name('bitacora.preview');

                    Route::get('/bitacora/pdf', [ReporteBitacoraController::class, 'pdf'])
                        ->middleware('permission:reportes.exportar_pdf')
                        ->name('bitacora.pdf');
                });

            /*
            |------------------------------------------------------------------
            | BITÁCORA
            |------------------------------------------------------------------
            */
            Route::get('bitacora', [BitacoraController::class, 'index'])
                ->middleware('permission:bitacora.ver')
                ->name('bitacora.index');
        });
});
