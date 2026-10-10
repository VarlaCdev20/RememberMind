<?php

use App\Modulos\Clinica\Presentacion\Http\Controllers\AdultoMayorAtencionController;
use App\Modulos\Clinica\Presentacion\Http\Controllers\AdultoMayorFichaMedicaController;
use App\Modulos\Clinica\Presentacion\Http\Controllers\AdultoMayorObservacionController;
use App\Modulos\Clinica\Presentacion\Http\Controllers\AdultoMayorSignosVitalesController;
use App\Modulos\Clinica\Presentacion\Http\Controllers\EstudioClinicoController;
use App\Modulos\Clinica\Presentacion\Http\Controllers\ExpedienteClinicoController;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\DashboardMedico;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\FichaPaciente;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\ObjetivosSignosVitalesPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\PacientesSeguimientoPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludFichaPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludResumenPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludSeguimientoListPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludSignosPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SignosVitalesPanel;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_057' => static function (): void {
        Route::get('/observaciones', [AdultoMayorObservacionController::class, 'index'])->middleware('permission:notas_clinicas.ver')->name('observaciones.index');
    },
    'ruta_058' => static function (): void {
        Route::post('/observaciones', [AdultoMayorObservacionController::class, 'store'])->middleware('permission:notas_clinicas.crear')->name('observaciones.store');
    },
    'ruta_059' => static function (): void {
        Route::patch('/observaciones/{observacion}', [AdultoMayorObservacionController::class, 'update'])->middleware('permission:notas_clinicas.editar')->name('observaciones.update');
    },
    'ruta_060' => static function (): void {
        Route::delete('/observaciones/{observacion}', [AdultoMayorObservacionController::class, 'destroy'])->middleware('permission:notas_clinicas.anular')->name('observaciones.destroy');
    },
    'ruta_061' => static function (): void {
        Route::patch('/observaciones/{observacion}/restaurar', [AdultoMayorObservacionController::class, 'restore'])->middleware('permission:notas_clinicas.editar')->name('observaciones.restore');
    },
    'ruta_062' => static function (): void {
        Route::get('/atenciones', [AdultoMayorAtencionController::class, 'index'])->middleware('permission:atenciones.ver')->name('atenciones.index');
    },
    'ruta_063' => static function (): void {
        Route::post('/atenciones', [AdultoMayorAtencionController::class, 'store'])->middleware('permission:atenciones.crear')->name('atenciones.store');
    },
    'ruta_064' => static function (): void {
        Route::patch('/atenciones/{atencion}', [AdultoMayorAtencionController::class, 'update'])->middleware('permission:atenciones.editar')->name('atenciones.update');
    },
    'ruta_065' => static function (): void {
        Route::delete('/atenciones/{atencion}', [AdultoMayorAtencionController::class, 'destroy'])->middleware('permission:atenciones.anular')->name('atenciones.destroy');
    },
    'ruta_066' => static function (): void {
        Route::patch('/atenciones/{atencion}/restaurar', [AdultoMayorAtencionController::class, 'restore'])->middleware('permission:atenciones.editar')->name('atenciones.restore');
    },
    'ruta_084' => static function (): void {
        Route::post('/ficha-medica', [AdultoMayorFichaMedicaController::class, 'store'])->middleware('permission:atenciones.crear')->name('ficha-medica.store');
    },
    'ruta_085' => static function (): void {
        Route::put('/ficha-medica/{ficha}', [AdultoMayorFichaMedicaController::class, 'update'])->middleware('permission:atenciones.editar')->name('ficha-medica.update');
    },
    'ruta_086' => static function (): void {
        Route::patch('/ficha-medica/{ficha}/archivar', [AdultoMayorFichaMedicaController::class, 'archivar'])->middleware('permission:atenciones.anular')->name('ficha-medica.archivar');
    },
    'ruta_087' => static function (): void {
        Route::patch('/ficha-medica/{ficha}/restaurar', [AdultoMayorFichaMedicaController::class, 'restore'])->middleware('permission:atenciones.editar')->name('ficha-medica.restore');
    },
    'ruta_095' => static function (): void {
        Route::post('/signos-vitales', [AdultoMayorSignosVitalesController::class, 'store'])->middleware('permission:signos_vitales.crear')->name('signos-vitales.store');
    },
    'ruta_096' => static function (): void {
        Route::put('/signos-vitales/{signo}', [AdultoMayorSignosVitalesController::class, 'update'])->middleware('permission:signos_vitales.editar')->name('signos-vitales.update');
    },
    'ruta_104' => static function (): void {
        Route::get('/', SaludSeguimientoListPanel::class)->name('index');
    },
    'ruta_105' => static function (): void {
        Route::get('/resumenes', SaludSeguimientoListPanel::class)->defaults('seccion', 'resumen')->name('resumen.index');
    },
    'ruta_106' => static function (): void {
        Route::get('/fichas', SaludSeguimientoListPanel::class)->defaults('seccion', 'ficha')->name('ficha.index');
    },
    'ruta_107' => static function (): void {
        Route::get('/medicaciones', SaludSeguimientoListPanel::class)->defaults('seccion', 'medicacion')->name('medicacion.index');
    },
    'ruta_108' => static function (): void {
        Route::get('/administraciones', SaludSeguimientoListPanel::class)->defaults('seccion', 'administracion')->name('administracion.index');
    },
    'ruta_109' => static function (): void {
        Route::get('/signos-vitales', SaludSeguimientoListPanel::class)->defaults('seccion', 'signos')->name('signos.index');
    },
    'ruta_110' => static function (): void {
        Route::get('/valoraciones', SaludSeguimientoListPanel::class)->defaults('seccion', 'valoracion')->name('valoracion.index');
    },
    'ruta_111' => static function (): void {
        Route::get('/evaluaciones-geriatricas', SaludSeguimientoListPanel::class)->defaults('seccion', 'evaluaciones')->name('evaluaciones-geriatricas.index');
    },
    'ruta_112' => static function (): void {
        Route::get('/alertas', SaludSeguimientoListPanel::class)->defaults('seccion', 'alertas')->name('alertas');
    },
    'ruta_113' => static function (): void {
        Route::get('/reportes', SaludSeguimientoListPanel::class)->defaults('seccion', 'reportes')->name('reportes');
    },
    'ruta_114' => static function (): void {
        Route::get('/{adulto}/resumen', SaludResumenPanel::class)->name('resumen');
    },
    'ruta_115' => static function (): void {
        Route::get('/{adulto}/ficha', SaludFichaPanel::class)->name('ficha');
    },
    'ruta_118' => static function (): void {
        Route::get('/{adulto}/signos', SaludSignosPanel::class)->name('signos');
    },
    'ruta_148' => static function (): void {
        Route::get('/pacientes/{adulto}', FichaPaciente::class)
            ->middleware('permission:enfermeria.ver_ficha_paciente')
            ->name('pacientes.ficha');
    },
    'ruta_156' => static function (): void {
        Route::get('/dashboard', DashboardMedico::class)
            ->name('dashboard');
    },
    'ruta_157' => static function (): void {
        Route::get('/residentes', PacientesSeguimientoPanel::class)
            ->name('residentes');
    },
    'ruta_158' => static function (): void {
        Route::get('/residente/{residente}/objetivos-signos-vitales', ObjetivosSignosVitalesPanel::class)
            ->middleware('permission:objetivos_signos_vitales.ver')
            ->name('residente.objetivos-signos-vitales');
    },
    'ruta_160' => static function (): void {
        Route::get('/pacientes-seguimiento', PacientesSeguimientoPanel::class)
            ->name('pacientes.observacion');
    },
    'ruta_161' => static function (): void {
        Route::get('/historial-clinico', PacientesSeguimientoPanel::class)
            ->name('pacientes.historial');
    },
    'ruta_162' => static function (): void {
        Route::get('/consultas-evoluciones', PacientesSeguimientoPanel::class)
            ->name('consultas');
    },
    'ruta_164' => static function (): void {
        Route::get('/decisiones-admision', DashboardMedico::class)
            ->name('decisiones');
    },
    'ruta_165' => static function (): void {
        Route::get('/interconsultas', PacientesSeguimientoPanel::class)
            ->name('interconsultas');
    },
    'ruta_166' => static function (): void {
        Route::get('/cognicion-riesgo', SaludSeguimientoListPanel::class)
            ->defaults('seccion', 'evaluaciones')
            ->name('cognicion-riesgo');
    },
    'ruta_167' => static function (): void {
        Route::get('/signos-vitales', SignosVitalesPanel::class)
            ->name('signos-vitales');
    },
    'ruta_168' => static function (): void {
        Route::get('/medicacion', SaludSeguimientoListPanel::class)
            ->defaults('seccion', 'medicacion')
            ->name('medicacion');
    },
    'ruta_170' => static function (): void {
        Route::get('/reportes', SaludSeguimientoListPanel::class)
            ->defaults('seccion', 'reportes')
            ->name('reportes');
    },
    'ruta_171' => static function (): void {
        Route::get('/residente/{adulto}', FichaPaciente::class)
            ->name('residente.ficha');
    },
    'ruta_172' => static function (): void {
        Route::get('/paciente/{adulto}', FichaPaciente::class)
            ->name('paciente.ficha');
    },
    'ruta_217' => static function (): void {
        Route::get('/residentes/{residente}/expediente', [ExpedienteClinicoController::class, 'index'])->middleware('permission:atenciones.ver')->name('expediente.index');
    },
    'ruta_218' => static function (): void {
        Route::post('/residentes/{residente}/atenciones', [ExpedienteClinicoController::class, 'crearAtencion'])->middleware('permission:atenciones.crear')->name('atenciones.store');
    },
    'ruta_219' => static function (): void {
        Route::post('/residentes/{residente}/clinica/{tipo}', [ExpedienteClinicoController::class, 'registrar'])
            ->middleware('permission:notas_clinicas.crear|antecedentes_clinicos.crear|diagnosticos.crear|alergias.crear|signos_vitales.crear|valoraciones_dolor.crear|mediciones_antropometricas.crear|incidentes.crear|indicaciones_clinicas.crear')
            ->name('clinica.store');
    },
    'ruta_220' => static function (): void {
        Route::get('/residentes/{residente}/estudios', [EstudioClinicoController::class, 'index'])->middleware('permission:estudios_clinicos.ver')->name('estudios.index');
    },
    'ruta_221' => static function (): void {
        Route::post('/residentes/{residente}/estudios', [EstudioClinicoController::class, 'solicitar'])->middleware('permission:estudios_clinicos.crear')->name('estudios.store');
    },
    'ruta_222' => static function (): void {
        Route::post('/estudios/{estudio}/resultados', [EstudioClinicoController::class, 'resultados'])->middleware('permission:resultados_estudio.crear')->name('estudios.resultados');
    },
    'ruta_223' => static function (): void {
        Route::post('/estudios/{estudio}/informes', [EstudioClinicoController::class, 'informar'])->middleware('permission:informes_estudio.crear')->name('estudios.informes');
    },
    'ruta_224' => static function (): void {
        Route::post('/residentes/{residente}/documentos-clinicos', [EstudioClinicoController::class, 'documento'])->middleware('permission:documentos_clinicos.crear')->name('documentos-clinicos.store');
    },
    'ruta_225' => static function (): void {
        Route::get('/documentos-clinicos/{documentoClinico}/descargar', [EstudioClinicoController::class, 'descargar'])->middleware('permission:documentos_clinicos.ver')->name('documentos-clinicos.descargar');
    },
    'ruta_226' => static function (): void {
        Route::post('/residentes/{residente}/derivaciones', [EstudioClinicoController::class, 'derivar'])->middleware('permission:derivaciones.crear')->name('derivaciones.store');
    },
];
