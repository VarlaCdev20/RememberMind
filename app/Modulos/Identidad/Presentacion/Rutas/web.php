<?php

use App\Models\User;
use App\Modulos\Identidad\Presentacion\Http\Controllers\DocumentosUsuarioController;
use App\Modulos\Identidad\Presentacion\Http\Controllers\RolePreviewController;
use App\Modulos\Identidad\Presentacion\Http\Controllers\UsuarioController;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\PersonalInstitucionalPanel;
use Illuminate\Support\Facades\Route;

// Registered by routes/web.php; do not execute while loading this file.
return [
    'ruta_003' => static function (): void {
        Route::post('/ver-como-rol', [RolePreviewController::class, 'store'])->name('role-preview.store');
    },
    'ruta_004' => static function (): void {
        Route::delete('/ver-como-rol', [RolePreviewController::class, 'destroy'])->name('role-preview.destroy');
    },
    'ruta_011' => static function (): void {
        Route::get('/', [UsuarioController::class, 'index'])
            ->middleware('permission:usuarios.ver')
            ->name('index');
    },
    'ruta_012' => static function (): void {
        Route::get('/create', [UsuarioController::class, 'create'])
            ->middleware('permission:usuarios.crear')
            ->name('create');
    },
    'ruta_013' => static function (): void {
        Route::post('/', [UsuarioController::class, 'store'])
            ->middleware('permission:usuarios.crear')
            ->name('store');
    },
    'ruta_014' => static function (): void {
        Route::get('/{usuario}', [UsuarioController::class, 'show'])
            ->middleware('permission:usuarios.ver')
            ->name('show');
    },
    'ruta_015' => static function (): void {
        Route::get('/{usuario}/edit', [UsuarioController::class, 'edit'])
            ->middleware('permission:usuarios.editar')
            ->name('edit');
    },
    'ruta_016' => static function (): void {
        Route::match(['put', 'patch'], '/{usuario}', [UsuarioController::class, 'update'])
            ->middleware('permission:usuarios.editar')
            ->name('update');
    },
    'ruta_017' => static function (): void {
        Route::delete('/{usuario}', [UsuarioController::class, 'destroy'])
            ->middleware('permission:usuarios.editar')
            ->name('destroy');
    },
    'ruta_018' => static function (): void {
        Route::patch('/{usuario}/desactivar', [UsuarioController::class, 'desactivar'])
            ->middleware('permission:usuarios.cambiar_estado')
            ->name('desactivar');
    },
    'ruta_019' => static function (): void {
        Route::patch('/{usuario}/activar', [UsuarioController::class, 'activar'])
            ->middleware('permission:usuarios.cambiar_estado')
            ->name('activar');
    },
    'ruta_020' => static function (): void {
        Route::get('/{usuario}/ficha/pdf', [UsuarioController::class, 'fichaPdf'])
            ->middleware('permission:usuarios.reportes.pdf')
            ->name('ficha.pdf');
    },
    'ruta_021' => static function (): void {
        Route::get('/{usuario}/documentacion/pdf', [UsuarioController::class, 'documentacionPdf'])
            ->middleware('permission:usuarios.reportes.pdf')
            ->name('documentacion.pdf');
    },
    'ruta_022' => static function (): void {
        Route::get('/{usuario}/solicitud-documental/pdf', [UsuarioController::class, 'solicitudDocumentalPdf'])
            ->middleware('permission:usuarios.ver')
            ->name('solicitud-documental.pdf');
    },
    'ruta_023' => static function (): void {
        Route::get('/{usuario}/horarios/pdf', [UsuarioController::class, 'horariosPdf'])
            ->middleware('permission:usuarios.reportes.pdf')
            ->name('horarios.pdf');
    },
    'ruta_024' => static function (): void {
        Route::post('/{usuario}/ficha/enviar-correo', [UsuarioController::class, 'enviarFichaCorreo'])
            ->middleware('permission:usuarios.ver')
            ->name('ficha.enviar-correo');
    },
    'ruta_025' => static function (): void {
        Route::get('/', [DocumentosUsuarioController::class, 'preview'])->name('preview');
    },
    'ruta_026' => static function (): void {
        Route::get('/pdf', [DocumentosUsuarioController::class, 'paquetePdf'])->name('pdf');
    },
    'ruta_027' => static function (): void {
        Route::post('/enviar', [DocumentosUsuarioController::class, 'enviarPaqueteCorreo'])
            ->middleware('permission:usuarios.editar|usuarios.crear')->name('enviar');
    },
    'ruta_028' => static function (): void {
        Route::get('/{documento}/ver', [DocumentosUsuarioController::class, 'verDocumento'])->name('ver');
    },
    'ruta_029' => static function (): void {
        Route::get('/{documento}/pdf', [DocumentosUsuarioController::class, 'pdfDocumento'])->name('documento-pdf');
    },
    'ruta_030' => static function (): void {
        Route::get('/{documento}/imprimir', [DocumentosUsuarioController::class, 'imprimirDocumento'])->name('imprimir');
    },
    'ruta_031' => static function (): void {
        Route::get('/personal-institucional', PersonalInstitucionalPanel::class)
            ->middleware('permission:personal_institucional.ver')
            ->name('personal-institucional');
    },
    'ruta_256' => static function (): void {
        Route::get('/local-login-enfermeria', function () {
            $u = User::where('correo', 'enfermeria@remembermind.com')->first();
            auth()->login($u);
            request()->session()->regenerate();

            return redirect('/admin/enfermeria/pacientes');
        });
    },
    'ruta_032' => static function (): void {
        Route::view('/roles-permisos', 'pages.roles-permisos.index')
            ->middleware('permission:roles.ver')
            ->name('roles-permisos.index');
    },
];
