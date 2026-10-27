<?php

use App\Http\Middleware\AutorizarResidenteVinculado;
use Illuminate\Support\Facades\Route;

// Inert module registries are invoked in the original order and group context.
$rutas = [];
$rutas['Compartido'] = require base_path('app/Compartido/Presentacion/Rutas/web.php');
$rutas['Reportes'] = require base_path('app/Modulos/Reportes/Presentacion/Rutas/web.php');
$rutas['Identidad'] = require base_path('app/Modulos/Identidad/Presentacion/Rutas/web.php');
$rutas['Institucion'] = require base_path('app/Modulos/Institucion/Presentacion/Rutas/web.php');
$rutas['Residentes'] = require base_path('app/Modulos/Residentes/Presentacion/Rutas/web.php');
$rutas['ApoyoDecision'] = require base_path('app/Modulos/ApoyoDecision/Presentacion/Rutas/web.php');
$rutas['Alertas'] = require base_path('app/Modulos/Alertas/Presentacion/Rutas/web.php');
$rutas['Admisiones'] = require base_path('app/Modulos/Admisiones/Presentacion/Rutas/web.php');
$rutas['Social'] = require base_path('app/Modulos/Social/Presentacion/Rutas/web.php');
$rutas['Clinica'] = require base_path('app/Modulos/Clinica/Presentacion/Rutas/web.php');
$rutas['Valoraciones'] = require base_path('app/Modulos/Valoraciones/Presentacion/Rutas/web.php');
$rutas['Medicacion'] = require base_path('app/Modulos/Medicacion/Presentacion/Rutas/web.php');
$rutas['Cuidados'] = require base_path('app/Modulos/Cuidados/Presentacion/Rutas/web.php');

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

$rutas['Compartido']['ruta_001']();

Route::middleware(['auth:sanctum','cuenta.activa'])->group(function () {
    Route::get('/cambiar-contrasena-inicial', [\App\Modulos\Identidad\Presentacion\Http\Controllers\CambiarPasswordInicialController::class,'show'])->name('password.inicial');
    Route::post('/cambiar-contrasena-inicial', [\App\Modulos\Identidad\Presentacion\Http\Controllers\CambiarPasswordInicialController::class,'guardar'])
        ->middleware('throttle:6,1')->name('password.inicial.guardar');
});

/*
|--------------------------------------------------------------------------
| RUTAS AUTENTICADAS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    'cuenta.activa',
    config('jetstream.auth_session'),
    'verified',
])->group(function () use ($rutas) {

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
    $rutas['Reportes']['ruta_002']();
    $rutas['Identidad']['ruta_003']();
    $rutas['Identidad']['ruta_004']();

    /*
    |--------------------------------------------------------------------------
    | ÁREA ADMINISTRATIVA / INSTITUCIONAL
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () use ($rutas) {

            /*
            |------------------------------------------------------------------
            | PANEL DE ADMINISTRACIÓN
            |------------------------------------------------------------------
            */
            Route::prefix('administracion')
                ->name('administracion.')
                ->middleware('permission:admisiones.ver_dashboard')
                ->group(function () use ($rutas) {
                    $rutas['Institucion']['ruta_005']();

                    $rutas['Institucion']['ruta_006']();

                    $rutas['Residentes']['ruta_007']();
                    $rutas['Institucion']['ruta_008']();

                    foreach ([
                        'admisiones', 'residentes', 'habitaciones', 'ocupacion',
                        'jornadas', 'asignaciones', 'contactos', 'documentacion',
                        'consentimientos', 'seguros', 'actividades', 'visitas',
                        'alertas', 'incidentes', 'reportes',
                    ] as $modulo) {
                        $rutas['Institucion']['ruta_009']($modulo);
                    }
                });

            $rutas['Institucion']['ruta_010']();

            /*
            |------------------------------------------------------------------
            | IDENTIDAD, USUARIOS Y PERSONAL
            |------------------------------------------------------------------
            */

            Route::prefix('usuarios')
                ->name('usuarios.')
                ->group(function () use ($rutas) {
                    $rutas['Identidad']['ruta_011']();

                    $rutas['Identidad']['ruta_012']();

                    $rutas['Identidad']['ruta_013']();

                    $rutas['Identidad']['ruta_014']();

                    $rutas['Identidad']['ruta_015']();

                    $rutas['Identidad']['ruta_016']();

                    $rutas['Identidad']['ruta_017']();

                    $rutas['Identidad']['ruta_018']();

                    $rutas['Identidad']['ruta_019']();

                    $rutas['Identidad']['ruta_020']();

                    $rutas['Identidad']['ruta_021']();

                    $rutas['Identidad']['ruta_022']();

                    $rutas['Identidad']['ruta_023']();

                    $rutas['Identidad']['ruta_024']();
                });

            Route::prefix('usuarios/{user}/documentos')
                ->name('usuarios.documentos.')
                ->group(function () use ($rutas) {
                    $rutas['Identidad']['ruta_025']();
                    $rutas['Identidad']['ruta_026']();
                    $rutas['Identidad']['ruta_027']();
                    $rutas['Identidad']['ruta_028']();
                    $rutas['Identidad']['ruta_029']();
                    $rutas['Identidad']['ruta_030']();
                });

            $rutas['Identidad']['ruta_031']();

            $rutas['Identidad']['ruta_032']();

            $rutas['ApoyoDecision']['ruta_033']();

            $rutas['Institucion']['ruta_034']();

            $rutas['Institucion']['ruta_035']();
            $rutas['Institucion']['galeria']();

            $rutas['Institucion']['ruta_036']();

            /*
            |------------------------------------------------------------------
            | REPORTES DE ÁREAS INSTITUCIONALES
            |------------------------------------------------------------------
            */
            Route::prefix('areas-institucionales/reportes')
                ->name('areas-institucionales.reportes.')
                ->middleware('permission:areas.reportes')
                ->group(function () use ($rutas) {
                    $rutas['Reportes']['ruta_037']();
                    $rutas['Reportes']['ruta_038']();
                    $rutas['Reportes']['ruta_039']();
                    $rutas['Reportes']['ruta_040']();
                    $rutas['Reportes']['ruta_041']();
                });

            /*
            |------------------------------------------------------------------
            | ADMISIONES / PREADMISIÓN
            |------------------------------------------------------------------
            */

            // Debe declararse antes de adultos-mayores/{adulto_mayor}
            // para evitar colisiones con el binding del resource.
            $rutas['Alertas']['ruta_042']();

            Route::prefix('admisiones')
                ->name('admisiones.')
                ->middleware('permission:admisiones.ver_dashboard')
                ->group(function () use ($rutas) {
                    $rutas['Admisiones']['ruta_043']();

                    $rutas['Admisiones']['ruta_044']();

                    $rutas['Admisiones']['ruta_045']();
                });

            /*
            |------------------------------------------------------------------
            | RESIDENTES / ADULTOS MAYORES
            |------------------------------------------------------------------
            */

            $rutas['Residentes']['ruta_046']();

            $rutas['Residentes']['ruta_047']();

            $rutas['Residentes']['ruta_048']();

            $rutas['Residentes']['ruta_049']();

            $rutas['Residentes']['ruta_050']();

            $rutas['Residentes']['ruta_051']();

            /*
            |------------------------------------------------------------------
            | SUBMÓDULOS DEL RESIDENTE
            |------------------------------------------------------------------
            |
            | El grupo exige residentes.ver.
            | Cada operación de escritura agrega además su permiso específico.
            |
            | Pendiente de validar en el seeder:
            | - atenciones.crear / atenciones.editar / atenciones.anular
            | - valoraciones_funcionales.crear / valoraciones_funcionales.editar
            | - administraciones_medicacion.crear
            |
            */
            Route::prefix('adultos-mayores/{adulto_mayor}')
                ->middleware(['permission:residentes.ver', AutorizarResidenteVinculado::class])
                ->name('adultos-mayores.')
                ->group(function () use ($rutas) {

                    // Familiares
                    $rutas['Social']['ruta_052']();
                    $rutas['Social']['ruta_053']();
                    $rutas['Social']['ruta_054']();
                    $rutas['Social']['ruta_055']();
                    $rutas['Social']['ruta_056']();

                    // Observaciones
                    $rutas['Clinica']['ruta_057']();
                    $rutas['Clinica']['ruta_058']();
                    $rutas['Clinica']['ruta_059']();
                    $rutas['Clinica']['ruta_060']();
                    $rutas['Clinica']['ruta_061']();

                    // Atenciones / Consultas base
                    $rutas['Clinica']['ruta_062']();
                    $rutas['Clinica']['ruta_063']();
                    $rutas['Clinica']['ruta_064']();
                    $rutas['Clinica']['ruta_065']();
                    $rutas['Clinica']['ruta_066']();

                    // Evaluaciones cognitivas
                    $rutas['Valoraciones']['ruta_067']();
                    $rutas['Valoraciones']['ruta_068']();
                    $rutas['Valoraciones']['ruta_069']();
                    $rutas['Valoraciones']['ruta_070']();
                    $rutas['Valoraciones']['ruta_071']();

                    // Evaluaciones geriátricas integrales
                    $rutas['Residentes']['ruta_072']();

                    $rutas['Residentes']['ruta_073']();

                    // Actividades
                    $rutas['Social']['ruta_074']();
                    $rutas['Social']['ruta_075']();
                    $rutas['Social']['ruta_076']();
                    $rutas['Social']['ruta_077']();

                    // Documentos
                    $rutas['Residentes']['ruta_078']();
                    $rutas['Residentes']['ruta_079']();
                    $rutas['Residentes']['ruta_080']();
                    $rutas['Residentes']['ruta_081']();
                    $rutas['Residentes']['ruta_082']();
                    $rutas['Residentes']['ruta_083']();

                    // Ficha médica
                    $rutas['Clinica']['ruta_084']();
                    $rutas['Clinica']['ruta_085']();
                    $rutas['Clinica']['ruta_086']();
                    $rutas['Clinica']['ruta_087']();

                    // Medicación / prescripción
                    $rutas['Medicacion']['ruta_088']();
                    $rutas['Medicacion']['ruta_089']();
                    $rutas['Medicacion']['ruta_090']();
                    $rutas['Medicacion']['ruta_091']();
                    $rutas['Medicacion']['ruta_092']();
                    $rutas['Medicacion']['ruta_093']();

                    // Administración de medicación
                    $rutas['Medicacion']['ruta_094']();

                    // Signos vitales
                    $rutas['Clinica']['ruta_095']();
                    $rutas['Clinica']['ruta_096']();

                    // Valoración funcional
                    $rutas['Valoraciones']['ruta_097']();
                    $rutas['Valoraciones']['ruta_098']();

                    // Reportes individuales
                    $rutas['Residentes']['ruta_099']();

                    $rutas['Residentes']['ruta_100']();
                });

            /*
            |------------------------------------------------------------------
            | REPORTES DE RESIDENTES
            |------------------------------------------------------------------
            */
            $rutas['Residentes']['ruta_101']();

            $rutas['Reportes']['ruta_102']();

            $rutas['Residentes']['ruta_103']();

            /*
            |------------------------------------------------------------------
            | SALUD Y SEGUIMIENTO GENERAL
            |------------------------------------------------------------------
            */
            Route::prefix('salud-seguimiento')
                ->name('salud-seguimiento.')
                ->middleware('permission:salud.ver')
                ->group(function () use ($rutas) {
                    // Índices globales
                    $rutas['Clinica']['ruta_104']();
                    $rutas['Clinica']['ruta_105']();
                    $rutas['Clinica']['ruta_106']();
                    $rutas['Clinica']['ruta_107']();
                    $rutas['Clinica']['ruta_108']();
                    $rutas['Clinica']['ruta_109']();
                    $rutas['Clinica']['ruta_110']();
                    $rutas['Clinica']['ruta_111']();
                    $rutas['Clinica']['ruta_112']();
                    $rutas['Clinica']['ruta_113']();

                    // Paneles individuales
                    $rutas['Clinica']['ruta_114']();
                    $rutas['Clinica']['ruta_115']();
                    $rutas['Medicacion']['ruta_116']();
                    $rutas['Medicacion']['ruta_117']();
                    $rutas['Clinica']['ruta_118']();
                    $rutas['Valoraciones']['ruta_119']();
                    $rutas['Valoraciones']['ruta_120']();
                });

            /*
            |------------------------------------------------------------------
            | FAMILIA Y SOCIAL
            |------------------------------------------------------------------
            */
            Route::prefix('familia-social')
                ->name('familia-social.')
                ->middleware('permission:residentes_contactos.ver')
                ->group(function () use ($rutas) {
                    $rutas['Social']['ruta_121']();
                    $rutas['Social']['ruta_122']();
                    $rutas['Social']['ruta_123']();

                    $rutas['Social']['ruta_124']();

                    $rutas['Social']['ruta_125']();
                });

            /*
            |------------------------------------------------------------------
            | INFRAESTRUCTURA Y FLUJO CLÍNICO
            |------------------------------------------------------------------
            */

            Route::prefix('habitaciones')
                ->name('habitaciones.')
                ->middleware('permission:habitaciones.ver')
                ->group(function () use ($rutas) {
                    $rutas['Institucion']['ruta_126']();
                });

            Route::prefix('turnos-enfermeria')
                ->name('turnos-enfermeria.')
                ->middleware('permission:turnos.ver')
                ->group(function () use ($rutas) {
                    $rutas['Institucion']['ruta_127']();
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
                ->group(function () use ($rutas) {
                    $rutas['Valoraciones']['ruta_128']();

                    $rutas['Valoraciones']['ruta_129']();
                });

            Route::prefix('asignacion-turno')
                ->name('asignacion-turno.')
                ->middleware('permission:turnos.ver')
                ->group(function () use ($rutas) {
                    $rutas['Institucion']['ruta_130']();
                });

            Route::prefix('plan-cuidado')
                ->name('plan-cuidado.')
                ->middleware('permission:planes_cuidado.ver')
                ->group(function () use ($rutas) {
                    $rutas['Cuidados']['ruta_131']();
                    $rutas['Cuidados']['ruta_132']();

                    $rutas['Medicacion']['ruta_133']();
                });

            Route::prefix('seguimiento-diario')
                ->name('seguimiento-diario.')
                ->middleware('permission:atenciones.ver')
                ->group(function () use ($rutas) {
                    $rutas['Cuidados']['ruta_134']();
                });

            Route::prefix('alertas-clinicas')
                ->name('alertas-clinicas.')
                ->middleware('permission:alertas.ver')
                ->group(function () use ($rutas) {
                    $rutas['Alertas']['ruta_135']();
                });

            Route::prefix('pase-turno')
                ->name('pase-turno.')
                ->middleware('permission:pases_turno.ver')
                ->group(function () use ($rutas) {
                    $rutas['Cuidados']['ruta_136']();
                });

            /*
            |------------------------------------------------------------------
            | ACTIVIDADES
            |------------------------------------------------------------------
            */

            Route::prefix('actividades')
                ->name('actividades.')
                ->middleware('permission:actividades.ver')
                ->group(function () use ($rutas) {
                    $rutas['Social']['ruta_137']();
                    $rutas['Social']['ruta_138']();
                    $rutas['Social']['ruta_139']();
                    $rutas['Social']['ruta_140']();
                    $rutas['Reportes']['ruta_141']();
                });

            /*
            |------------------------------------------------------------------
            | ENFERMERÍA
            |------------------------------------------------------------------
            */
            Route::prefix('enfermeria')
                ->name('enfermeria.')
                ->group(function () use ($rutas) {
                    $rutas['Cuidados']['ruta_142']();

                    $rutas['Cuidados']['ruta_143']();

                    $rutas['Cuidados']['ruta_144']();

                    $rutas['Cuidados']['ruta_145']();

                    $rutas['Alertas']['ruta_146']();

                    $rutas['Cuidados']['ruta_147']();

                    $rutas['Clinica']['ruta_148']();

                    $rutas['ApoyoDecision']['ruta_149']();

                    $rutas['Reportes']['ruta_150']();

                    $rutas['Cuidados']['ruta_151']();

                    $rutas['Medicacion']['ruta_152']();

                    $rutas['Alertas']['ruta_153']();

                    $rutas['Cuidados']['ruta_154']();

                    $rutas['Reportes']['ruta_155']();
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
                ->group(function () use ($rutas) {

                    // Inicio
                    $rutas['Clinica']['ruta_156']();

                    // Residentes: todos los residentes del centro.
                    $rutas['Clinica']['ruta_157']();

                    $rutas['Clinica']['ruta_158']();

                    $rutas['ApoyoDecision']['ruta_159']();

                    // Compatibilidad con rutas/vistas existentes.
                    $rutas['Clinica']['ruta_160']();

                    $rutas['Clinica']['ruta_161']();

                    // Consultas / Evoluciones.
                    // Por ahora reutiliza el panel de seguimiento; cuando exista el
                    // componente específico de consulta médica se reemplaza aquí.
                    $rutas['Clinica']['ruta_162']();

                    // Valoraciones de admisión.
                    $rutas['Valoraciones']['ruta_163']();

                    // Se conserva esta entrada para compatibilidad con el dashboard
                    // actual hasta revisar la lógica interna de DashboardMedico.
                    $rutas['Clinica']['ruta_164']();

                    // Interconsultas.
                    $rutas['Clinica']['ruta_165']();

                    // Cognición y riesgo: reutiliza el listado de evaluaciones
                    // geriátricas existente sin duplicar lógica.
                    $rutas['Clinica']['ruta_166']();

                    // Monitor clínico de signos vitales.
                    $rutas['Clinica']['ruta_167']();

                    // Medicación: listado global para revisión médica.
                    $rutas['Clinica']['ruta_168']();

                    // Alertas clínicas.
                    $rutas['Alertas']['ruta_169']();

                    // Reportes clínicos del área médica.
                    $rutas['Clinica']['ruta_170']();

                    // Ficha médica y clínica integrada por residente (unificada con FichaPaciente).
                    $rutas['Clinica']['ruta_171']();

                    // Compatibilidad con enlaces existentes que todavía usan /paciente/.
                    $rutas['Clinica']['ruta_172']();
                });

            /*
            |------------------------------------------------------------------
            | PSICOLOGÍA
            |------------------------------------------------------------------
            */
            Route::prefix('psicologia')
                ->name('psicologia.')
                ->group(function () use ($rutas) {
                    $rutas['ApoyoDecision']['revision_psicologia']();
                    $rutas['Valoraciones']['ruta_173']();
                    $rutas['Valoraciones']['ruta_174']();
                    $rutas['Valoraciones']['ruta_175']();

                    $rutas['Valoraciones']['ruta_176']();

                    $rutas['Valoraciones']['ruta_177']();

                    $rutas['Valoraciones']['ruta_178']();

                    $rutas['Valoraciones']['ruta_179']();
                    $rutas['Valoraciones']['ruta_psicologia_ficha']();
                    $rutas['Valoraciones']['ruta_psicologia_actividad_general']();
                });

            /*
            |------------------------------------------------------------------
            | REPORTES INSTITUCIONALES
            |------------------------------------------------------------------
            */
            Route::prefix('reportes')
                ->name('reportes.')
                ->group(function () use ($rutas) {

                    // Institucional
                    $rutas['Reportes']['ruta_180']();

                    $rutas['Reportes']['ruta_181']();

                    $rutas['Reportes']['ruta_182']();

                    // Adultos mayores
                    $rutas['Reportes']['ruta_183']();

                    $rutas['Reportes']['ruta_184']();

                    $rutas['Reportes']['ruta_185']();

                    // Salud y seguimiento
                    $rutas['Reportes']['ruta_186']();

                    $rutas['Reportes']['ruta_187']();

                    $rutas['Reportes']['ruta_188']();

                    // Familiares
                    $rutas['Reportes']['ruta_189']();

                    $rutas['Reportes']['ruta_190']();

                    $rutas['Reportes']['ruta_191']();

                    // Equipo institucional
                    $rutas['Reportes']['ruta_192']();

                    $rutas['Reportes']['ruta_193']();

                    $rutas['Reportes']['ruta_194']();

                    // Actividades
                    $rutas['Reportes']['ruta_195']();

                    $rutas['Reportes']['ruta_196']();

                    $rutas['Reportes']['ruta_197']();

                    // Bitácora (sin Excel por seguridad)
                    $rutas['Reportes']['ruta_198']();

                    $rutas['Reportes']['ruta_199']();
                });

            /*
            |------------------------------------------------------------------
            | BITÁCORA
            |------------------------------------------------------------------
            */
            $rutas['Reportes']['ruta_200']();
        });
});

/*
|--------------------------------------------------------------------------
| ENDPOINTS OPERATIVOS BDD V2
|--------------------------------------------------------------------------
|
| La interfaz histórica se conserva en las rutas anteriores. Estos endpoints
| exponen las operaciones V2 que esa interfaz y las integraciones consumen,
| sin reemplazar ninguna pantalla existente.
|
*/
Route::middleware(['auth:sanctum', 'cuenta.activa', config('jetstream.auth_session'), 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () use ($rutas): void {
        $rutas['Residentes']['ruta_201']();
        $rutas['Residentes']['ruta_202']();

        $rutas['Admisiones']['ruta_203']();
        $rutas['Admisiones']['ruta_204']();
        $rutas['Admisiones']['ruta_205']();
        $rutas['Admisiones']['ruta_206']();
        $rutas['Institucion']['ruta_207']();
        $rutas['Institucion']['ruta_208']();
        $rutas['Institucion']['ruta_209']();

        Route::prefix('institucional')->name('institucional.')->group(function () use ($rutas): void {
            $rutas['Institucion']['ruta_210']();
            $rutas['Institucion']['ruta_211']();
            $rutas['Institucion']['ruta_212']();
            $rutas['Institucion']['ruta_213']();
            $rutas['Institucion']['ruta_214']();
            $rutas['Institucion']['ruta_215']();
            $rutas['Institucion']['ruta_216']();
        });

        $rutas['Clinica']['ruta_217']();
        $rutas['Clinica']['ruta_218']();
        $rutas['Clinica']['ruta_219']();
        $rutas['Clinica']['ruta_220']();
        $rutas['Clinica']['ruta_221']();
        $rutas['Clinica']['ruta_222']();
        $rutas['Clinica']['ruta_223']();
        $rutas['Clinica']['ruta_224']();
        $rutas['Clinica']['ruta_225']();
        $rutas['Clinica']['ruta_226']();

        $rutas['Cuidados']['ruta_227']();
        $rutas['Cuidados']['ruta_228']();
        $rutas['Cuidados']['ruta_229']();
        $rutas['Cuidados']['ruta_230']();
        $rutas['Cuidados']['ruta_231']();
        $rutas['Cuidados']['ruta_232']();
        $rutas['Cuidados']['ruta_233']();
        $rutas['Cuidados']['ruta_234']();
        $rutas['Cuidados']['ruta_235']();

        $rutas['Medicacion']['ruta_236']();
        $rutas['Medicacion']['ruta_237']();
        $rutas['Medicacion']['ruta_238']();
        $rutas['Medicacion']['ruta_239']();

        $rutas['Valoraciones']['ruta_240']();
        $rutas['Valoraciones']['ruta_241']();
        $rutas['Valoraciones']['ruta_242']();
        $rutas['Social']['ruta_243']();
        $rutas['Social']['ruta_244']();

        $rutas['Alertas']['ruta_245']();
        $rutas['Alertas']['ruta_246']();
        $rutas['Alertas']['ruta_247']();

        $rutas['Residentes']['ruta_248']();
        $rutas['Residentes']['ruta_249']();
        $rutas['Residentes']['ruta_250']();
        $rutas['Residentes']['ruta_251']();
        $rutas['Residentes']['ruta_252']();
        $rutas['Residentes']['ruta_253']();
        $rutas['Reportes']['ruta_254']();
        $rutas['Reportes']['ruta_255']();
    });
