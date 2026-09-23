<?php

use App\Http\Controllers\Actividades\ActividadController;
use App\Http\Controllers\Admisiones\AdmisionController;
use App\Http\Controllers\Admisiones\InfraestructuraController;
use App\Http\Controllers\Admisiones\PreadmisionController;
use App\Http\Controllers\Alertas\AlertaController;
use App\Http\Controllers\Clinica\ExpedienteClinicoController;
use App\Http\Controllers\Clinica\EstudioClinicoController;
use App\Http\Controllers\Cuidados\CuidadoController;
use App\Http\Controllers\Documentos\DocumentoController;
use App\Http\Controllers\Identidad\InstitucionalController;
use App\Http\Controllers\Instrumentos\InstrumentoController;
use App\Http\Controllers\Medicacion\MedicacionController;
use App\Http\Controllers\Reportes\DashboardController;
use App\Http\Controllers\Reportes\ReporteV2Controller;
use App\Http\Controllers\Residentes\ResidenteController;
use App\Http\Controllers\Residentes\RelacionResidenteController;
use App\Http\Controllers\Valoraciones\ValoracionProfesionalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('inicio');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        // Compatibilidad con el destino por rol usado por LoginResponse.
        // Mantiene el acceso de enfermería sobre el dashboard V2 mientras
        // se reconstruyen sus vistas especializadas sin recuperar tablas legacy.
        Route::get('/enfermeria/dashboard', [DashboardController::class, 'index'])
            ->name('enfermeria.dashboard');

        Route::get('/residentes', [ResidenteController::class, 'index'])->middleware('can:viewAny,App\\Models\\Residente')->name('residentes.index');
        Route::get('/residentes/{residente}', [ResidenteController::class, 'show'])->middleware('can:view,residente')->name('residentes.show');

        Route::get('/preadmisiones', [PreadmisionController::class, 'index'])->middleware('permission:preadmisiones.ver')->name('preadmisiones.index');
        Route::post('/preadmisiones', [PreadmisionController::class, 'store'])->middleware('permission:preadmisiones.crear')->name('preadmisiones.store');
        Route::patch('/preadmisiones/{preadmision}/revision', [PreadmisionController::class, 'revisar'])->middleware('permission:preadmisiones.revisar')->name('preadmisiones.revisar');
        Route::post('/preadmisiones/{preadmision}/admision', [AdmisionController::class, 'store'])->middleware('permission:admisiones.formalizar')->name('admisiones.store');
        Route::get('/infraestructura', [InfraestructuraController::class, 'index'])->middleware('permission:habitaciones.ver')->name('infraestructura.index');
        Route::post('/habitaciones', [InfraestructuraController::class, 'habitacion'])->middleware('permission:habitaciones.gestionar')->name('habitaciones.store');
        Route::post('/habitaciones/{habitacion}/camas', [InfraestructuraController::class, 'cama'])->middleware('permission:camas.gestionar')->name('camas.store');

        Route::prefix('institucional')->name('institucional.')->middleware('role:SUPERADMINISTRADOR|ADMINISTRADOR')->group(function (): void {
            Route::get('/usuarios', [InstitucionalController::class, 'usuarios'])->middleware('permission:usuarios.ver')->name('usuarios');
            Route::post('/usuarios', [InstitucionalController::class, 'guardarUsuario'])->middleware('permission:usuarios.gestionar')->name('usuarios.store');
            Route::post('/personal', [InstitucionalController::class, 'guardarPersonal'])->middleware('permission:personal.gestionar')->name('personal.store');
            Route::post('/areas', [InstitucionalController::class, 'guardarArea'])->middleware('permission:areas.gestionar')->name('areas.store');
            Route::post('/turnos', [InstitucionalController::class, 'guardarTurno'])->middleware('permission:turnos.gestionar')->name('turnos.store');
            Route::post('/jornadas', [InstitucionalController::class, 'abrirJornada'])->middleware('permission:jornadas.gestionar')->name('jornadas.store');
            Route::post('/jornadas/{jornada}/personal', [InstitucionalController::class, 'asignarPersonal'])->middleware('permission:jornadas.gestionar')->name('jornadas.personal');
        });

        Route::get('/residentes/{residente}/expediente', [ExpedienteClinicoController::class, 'index'])->middleware('permission:atenciones.ver')->name('expediente.index');
        Route::post('/residentes/{residente}/atenciones', [ExpedienteClinicoController::class, 'crearAtencion'])->middleware('permission:atenciones.crear')->name('atenciones.store');
        Route::post('/residentes/{residente}/clinica/{tipo}', [ExpedienteClinicoController::class, 'registrar'])->name('clinica.store');
        Route::get('/residentes/{residente}/estudios', [EstudioClinicoController::class, 'index'])->middleware('permission:estudios_clinicos.ver')->name('estudios.index');
        Route::post('/residentes/{residente}/estudios', [EstudioClinicoController::class, 'solicitar'])->middleware('permission:estudios_clinicos.crear')->name('estudios.store');
        Route::post('/estudios/{estudio}/resultados', [EstudioClinicoController::class, 'resultados'])->middleware('permission:resultados_estudio.crear')->name('estudios.resultados');
        Route::post('/estudios/{estudio}/informes', [EstudioClinicoController::class, 'informar'])->middleware('permission:informes_estudio.crear')->name('estudios.informes');
        Route::post('/residentes/{residente}/documentos-clinicos', [EstudioClinicoController::class, 'documento'])->middleware('permission:documentos_clinicos.crear')->name('documentos-clinicos.store');
        Route::get('/documentos-clinicos/{documentoClinico}/descargar', [EstudioClinicoController::class, 'descargar'])->middleware('permission:documentos_clinicos.ver')->name('documentos-clinicos.descargar');
        Route::post('/residentes/{residente}/derivaciones', [EstudioClinicoController::class, 'derivar'])->middleware('permission:derivaciones.crear')->name('derivaciones.store');

        Route::get('/residentes/{residente}/cuidados', [CuidadoController::class, 'index'])->middleware('permission:planes_cuidado.ver')->name('cuidados.index');
        Route::post('/residentes/{residente}/cuidados/{tipo}', [CuidadoController::class, 'registrar'])->name('cuidados.store');
        Route::post('/residentes/{residente}/planes', [CuidadoController::class, 'crearPlan'])->middleware('permission:planes_cuidado.crear')->name('planes.store');
        Route::post('/residentes/{residente}/asignaciones-jornada', [CuidadoController::class, 'asignarJornada'])->middleware('permission:asignaciones_residente_jornada.gestionar')->name('cuidados.asignaciones.store');
        Route::post('/residentes/{residente}/pases-turno', [CuidadoController::class, 'registrarPase'])->middleware('permission:pases_turno.crear')->name('cuidados.pases.store');
        Route::post('/heridas/{herida}/curaciones', [CuidadoController::class, 'curarHerida'])->middleware('permission:curaciones_herida.crear')->name('cuidados.curaciones.store');
        Route::post('/planes/{plan}/intervenciones', [CuidadoController::class, 'crearIntervencion'])->middleware('permission:planes_cuidado.crear')->name('intervenciones.store');
        Route::post('/intervenciones/{intervencion}/programaciones', [CuidadoController::class, 'programar'])->middleware('permission:planes_cuidado.crear')->name('programaciones.store');
        Route::post('/intervenciones/{intervencion}/ejecuciones', [CuidadoController::class, 'ejecutar'])->middleware('permission:ejecuciones_cuidado.crear')->name('ejecuciones.store');

        Route::get('/residentes/{residente}/medicacion', [MedicacionController::class, 'index'])->middleware('permission:prescripciones.ver')->name('medicacion.index');
        Route::post('/residentes/{residente}/prescripciones', [MedicacionController::class, 'prescribir'])->middleware('permission:prescripciones.crear')->name('prescripciones.store');
        Route::patch('/prescripciones/{prescripcion}/suspender', [MedicacionController::class, 'suspender'])->middleware('permission:prescripciones.suspender')->name('prescripciones.suspender');
        Route::post('/prescripciones/{prescripcion}/administraciones', [MedicacionController::class, 'administrar'])->middleware('permission:administraciones_medicacion.crear')->name('administraciones.store');

        Route::get('/instrumentos', [InstrumentoController::class, 'index'])->middleware('permission:instrumentos.ver')->name('instrumentos.index');
        Route::post('/instrumentos/{instrumento}/residentes/{residente}', [InstrumentoController::class, 'aplicar'])->middleware('permission:aplicaciones_instrumento.crear')->name('instrumentos.aplicar');
        Route::post('/residentes/{residente}/valoraciones/{tipo}', [ValoracionProfesionalController::class, 'registrar'])->name('valoraciones.store');

        Route::get('/actividades', [ActividadController::class, 'index'])->middleware('permission:actividades.ver')->name('actividades.index');
        Route::post('/actividades', [ActividadController::class, 'store'])->middleware('permission:actividades.gestionar')->name('actividades.store');
        Route::post('/actividades/{actividad}/participantes', [ActividadController::class, 'participante'])->middleware('permission:actividades.gestionar')->name('actividades.participantes');

        Route::get('/alertas', [AlertaController::class, 'index'])->middleware('permission:alertas.ver')->name('alertas.index');
        Route::post('/residentes/{residente}/alertas', [AlertaController::class, 'store'])->middleware('permission:alertas.gestionar')->name('alertas.store');
        Route::patch('/alertas/{alerta}/estado', [AlertaController::class, 'cambiarEstado'])->middleware('permission:alertas.gestionar')->name('alertas.estado');

        Route::post('/residentes/{residente}/documentos', [DocumentoController::class, 'store'])->middleware('permission:documentos.gestionar')->name('documentos.store');
        Route::get('/residentes/{residente}/relaciones', [RelacionResidenteController::class, 'index'])->middleware('permission:residentes_contactos.ver')->name('residentes.relaciones');
        Route::post('/residentes/{residente}/contactos', [RelacionResidenteController::class, 'vincularContacto'])->middleware('permission:contactos.gestionar')->name('residentes.contactos.store');
        Route::post('/residentes/{residente}/visitas', [RelacionResidenteController::class, 'registrarVisita'])->middleware('permission:visitas.gestionar')->name('residentes.visitas.store');
        Route::post('/residentes/{residente}/consentimientos', [RelacionResidenteController::class, 'registrarConsentimiento'])->middleware('permission:consentimientos.gestionar')->name('residentes.consentimientos.store');
        Route::get('/documentos/{documento}/descargar', [DocumentoController::class, 'descargar'])->middleware('permission:documentos.ver')->name('documentos.descargar');
        Route::get('/residentes/{residente}/reporte.pdf', [ReporteV2Controller::class, 'residentePdf'])->middleware('permission:residentes.ver')->name('reportes.residente');
        Route::get('/reportes/residentes.csv', [ReporteV2Controller::class, 'residentesCsv'])->middleware('permission:residentes.ver')->name('reportes.residentes');
    });
});
