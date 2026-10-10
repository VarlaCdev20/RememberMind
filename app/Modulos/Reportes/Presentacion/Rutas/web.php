<?php

use App\Modulos\Reportes\Presentacion\Http\Controllers\AreaReporteController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\BitacoraController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\DashboardController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\FichaPacienteReporteController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\ReporteActividadesController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\ReporteAdultosController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\ReporteBitacoraController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\ReporteEquipoController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\ReporteFamiliaresController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\ReporteInstitucionalController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\ReporteSaludController;
use App\Modulos\Reportes\Presentacion\Http\Controllers\ReporteV2Controller;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\ReporteEnfermeria;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\ReportesActividadesPanel;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\ReportesInstitucionalesPanel;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_002' => static function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    },
    'ruta_037' => static function (): void {
        Route::get('/general/pdf', [AreaReporteController::class, 'generalPdf'])->name('general.pdf');
    },
    'ruta_038' => static function (): void {
        Route::get('/general/excel', [AreaReporteController::class, 'generalExcel'])->name('general.excel');
    },
    'ruta_039' => static function (): void {
        Route::get('/general/csv', [AreaReporteController::class, 'generalCsv'])->name('general.csv');
    },
    'ruta_040' => static function (): void {
        Route::get('/{area}/pdf', [AreaReporteController::class, 'areaPdf'])->name('area.pdf');
    },
    'ruta_041' => static function (): void {
        Route::get('/{area}/excel', [AreaReporteController::class, 'areaExcel'])->name('area.excel');
    },
    'ruta_102' => static function (): void {
        Route::get('reporte-institucional', ReportesInstitucionalesPanel::class)
            ->middleware('permission:reportes.ver')
            ->name('adultos-mayores.reporte-institucional');
    },
    'ruta_141' => static function (): void {
        Route::get('/reportes', ReportesActividadesPanel::class)->name('reportes');
    },
    'ruta_150' => static function (): void {
        Route::get('/pacientes/{adulto}/pdf', [FichaPacienteReporteController::class, 'pdf'])
            ->middleware('permission:enfermeria.ver_ficha_paciente')
            ->name('pacientes.ficha.pdf');
    },
    'ruta_155' => static function (): void {
        Route::get('/reportes', ReporteEnfermeria::class)
            ->middleware('permission:enfermeria.ver_dashboard')
            ->name('reportes');
    },
    'ruta_180' => static function (): void {
        Route::get('/institucional', [ReporteInstitucionalController::class, 'preview'])
            ->middleware('permission:reportes.institucional')
            ->name('institucional.preview');
    },
    'ruta_181' => static function (): void {
        Route::get('/institucional/pdf', [ReporteInstitucionalController::class, 'pdf'])
            ->middleware('permission:reportes.institucional')
            ->name('institucional.pdf');
    },
    'ruta_182' => static function (): void {
        Route::get('/institucional/excel', [ReporteInstitucionalController::class, 'excel'])
            ->middleware('permission:reportes.institucional')
            ->name('institucional.excel');
    },
    'ruta_183' => static function (): void {
        Route::get('/adultos', [ReporteAdultosController::class, 'preview'])
            ->middleware('permission:reportes.ver')
            ->name('adultos.preview');
    },
    'ruta_184' => static function (): void {
        Route::get('/adultos/pdf', [ReporteAdultosController::class, 'pdf'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('adultos.pdf');
    },
    'ruta_185' => static function (): void {
        Route::get('/adultos/excel', [ReporteAdultosController::class, 'excel'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('adultos.excel');
    },
    'ruta_186' => static function (): void {
        Route::get('/salud', [ReporteSaludController::class, 'preview'])
            ->middleware('permission:reportes.ver')
            ->name('salud.preview');
    },
    'ruta_187' => static function (): void {
        Route::get('/salud/pdf', [ReporteSaludController::class, 'pdf'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('salud.pdf');
    },
    'ruta_188' => static function (): void {
        Route::get('/salud/excel', [ReporteSaludController::class, 'excel'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('salud.excel');
    },
    'ruta_189' => static function (): void {
        Route::get('/familiares', [ReporteFamiliaresController::class, 'preview'])
            ->middleware('permission:reportes.ver')
            ->name('familiares.preview');
    },
    'ruta_190' => static function (): void {
        Route::get('/familiares/pdf', [ReporteFamiliaresController::class, 'pdf'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('familiares.pdf');
    },
    'ruta_191' => static function (): void {
        Route::get('/familiares/excel', [ReporteFamiliaresController::class, 'excel'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('familiares.excel');
    },
    'ruta_192' => static function (): void {
        Route::get('/equipo', [ReporteEquipoController::class, 'preview'])
            ->middleware('permission:reportes.ver')
            ->name('equipo.preview');
    },
    'ruta_193' => static function (): void {
        Route::get('/equipo/pdf', [ReporteEquipoController::class, 'pdf'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('equipo.pdf');
    },
    'ruta_194' => static function (): void {
        Route::get('/equipo/excel', [ReporteEquipoController::class, 'excel'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('equipo.excel');
    },
    'ruta_195' => static function (): void {
        Route::get('/actividades', [ReporteActividadesController::class, 'preview'])
            ->middleware('permission:reportes.ver')
            ->name('actividades.preview');
    },
    'ruta_196' => static function (): void {
        Route::get('/actividades/pdf', [ReporteActividadesController::class, 'pdf'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('actividades.pdf');
    },
    'ruta_197' => static function (): void {
        Route::get('/actividades/excel', [ReporteActividadesController::class, 'excel'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('actividades.excel');
    },
    'ruta_198' => static function (): void {
        Route::get('/bitacora', [ReporteBitacoraController::class, 'preview'])
            ->middleware('permission:reportes.ver')
            ->name('bitacora.preview');
    },
    'ruta_199' => static function (): void {
        Route::get('/bitacora/pdf', [ReporteBitacoraController::class, 'pdf'])
            ->middleware('permission:reportes.exportar_pdf')
            ->name('bitacora.pdf');
    },
    'ruta_200' => static function (): void {
        Route::get('bitacora', [BitacoraController::class, 'index'])
            ->middleware('permission:bitacora.ver')
            ->name('bitacora.index');
    },
    'ruta_254' => static function (): void {
        Route::get('/residentes/{residente}/reporte.pdf', [ReporteV2Controller::class, 'residentePdf'])->middleware('permission:residentes.ver')->name('reportes.residente');
    },
    'ruta_255' => static function (): void {
        Route::get('/reportes/residentes.csv', [ReporteV2Controller::class, 'residentesCsv'])->middleware('permission:residentes.ver')->name('reportes.residentes');
    },
];
