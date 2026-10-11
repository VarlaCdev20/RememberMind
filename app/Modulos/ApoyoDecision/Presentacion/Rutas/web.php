<?php

use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ConocimientoPanel;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_033' => static function (): void {
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
];
