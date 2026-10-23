<?php

use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\CapturaPetersenPanel;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ConocimientoPanel;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\GobiernoReglasPanel;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResidentesSistemaExperto;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_033' => static function (): void {
        Route::get('/sistema-experto/residentes', ResidentesSistemaExperto::class)
            ->middleware(['role:MEDICO GENERAL/GERIATRA|PSICOLOGO/A|SUPERADMINISTRADOR', 'can:viewAny,App\\Models\\Residente'])
            ->name('sistema-experto.residentes');
        Route::get('/sistema-experto/residentes/{residente}/petersen', CapturaPetersenPanel::class)
            ->middleware('can:consultarOriginales,App\\Models\\ValoracionPetersenOriginal,residente')
            ->name('sistema-experto.petersen-original');
        Route::get('/sistema-experto/reglas', GobiernoReglasPanel::class)
            ->middleware('can:consultarGobierno,App\\Models\\VersionModeloExperto')
            ->name('sistema-experto.reglas');
        Route::get('/sistema-experto', ConocimientoPanel::class)
            ->middleware(['role:SUPERADMINISTRADOR', 'can:consultar,App\Models\VersionModeloExperto'])
            ->name('sistema-experto.index');
    },
    'ruta_149' => static function (): void {
        Route::get('/pacientes/{residente}/resultados-experto', ResultadosExpertoResidente::class)
            ->middleware(['permission:enfermeria.ver_ficha_paciente', 'can:consultarResultados,App\\Models\\EvaluacionExperta,residente'])
            ->name('pacientes.resultados-experto');
    },
    'ruta_159' => static function (): void {
        Route::get('/residente/{residente}/evaluacion-cognitiva/resultados-experto', ResultadosExpertoResidente::class)
            ->middleware('can:consultarResultados,App\\Models\\EvaluacionExperta,residente')
            ->name('residente.resultados-experto');
    },
    'revision_psicologia' => static function (): void {
        Route::get('/residente/{residente}/resultados-experto', ResultadosExpertoResidente::class)
            ->middleware(['permission:evaluaciones_expertas.revisar_psicologia', 'can:consultarResultados,App\\Models\\EvaluacionExperta,residente'])
            ->name('residente.resultados-experto');
    },
];
