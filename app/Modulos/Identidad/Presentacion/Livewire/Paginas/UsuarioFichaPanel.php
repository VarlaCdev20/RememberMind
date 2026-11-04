<?php

namespace App\Modulos\Identidad\Presentacion\Livewire\Paginas;

use App\Models\Documento;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\DocumentacionUsuarioService;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarSeguridadCuenta;
use App\Modulos\Identidad\Aplicacion\Consultas\UsuarioFichaService;
use App\Modulos\Identidad\Presentacion\Policies\DocumentoLaboralPolicy;
use App\Modulos\Reportes\Infraestructura\Exportaciones\ReportExportService;
use App\Modulos\Reportes\Infraestructura\Exportaciones\ReportFileNameService;
use App\Modulos\Reportes\Infraestructura\Exportaciones\UsuarioExpedienteExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UsuarioFichaPanel extends Component
{
    use WithFileUploads, WithPagination;

    public User $usuario;

    public string $activeTab = 'datos_personales';

    // ── Documentación ──
    public $selectedTipoDoc = null;

    #[Locked]
    public ?array $referenciaDocumento = null;

    public $archivoSubida;

    public $fechaEmision;

    public $fechaVencimiento;

    public $observacionesArchivo;

    public $selectedDocId = null;

    public $motivoRechazo;

    public bool $mostrarSubidaModal = false;

    public bool $mostrarObservacionModal = false;

    public bool $mostrarAnulacionModal = false;

    // ── Historial ──
    public $filtroAccion = '';

    public $filtroFechaDesde = '';

    public $filtroFechaHasta = '';

    protected $paginationTheme = 'tailwind';

    public function mount(User $usuario)
    {
        $this->usuario = $usuario;
        $this->activeTab = request()->query('tab', 'datos_personales');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // ══════════════════════════════════════════════
    // DOCUMENTACIÓN
    // ══════════════════════════════════════════════

    public function abrirSubida(string $codTipoDoc): void
    {
        abort_unless(
            auth()->user()?->can('documentos.subir') || auth()->user()?->can('documentos.gestionar'),
            403
        );

        $tipo = app(DocumentacionUsuarioService::class)->tipoDisponible($this->usuario, $codTipoDoc);
        $this->referenciaDocumento = app(DocumentacionUsuarioService::class)->referenciaVigente($this->usuario, $codTipoDoc, auth()->user());
        $this->selectedTipoDoc = $tipo;
        $this->archivoSubida = null;
        $this->fechaEmision = null;
        $this->fechaVencimiento = null;
        $this->observacionesArchivo = null;
        $this->mostrarSubidaModal = true;
    }

    public function subirArchivo(): void
    {
        abort_unless(
            auth()->user()?->can('documentos.subir') || auth()->user()?->can('documentos.gestionar'),
            403
        );

        $rules = [
            'archivoSubida' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB máx
            'fechaEmision' => ['nullable', 'date', 'before_or_equal:today'],
        ];

        if ($this->selectedTipoDoc?->requiere_vencimiento) {
            $rules['fechaVencimiento'] = ['required', 'date', 'after:fechaEmision'];
        } else {
            $rules['fechaVencimiento'] = ['nullable', 'date', 'after:fechaEmision'];
        }

        $this->validate($rules, [
            'archivoSubida.required' => 'Debe seleccionar un archivo.',
            'archivoSubida.mimes' => 'El formato debe ser PDF, JPG, JPEG o PNG.',
            'archivoSubida.max' => 'El tamaño máximo es de 10 MB.',
            'fechaVencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fechaVencimiento.after' => 'La fecha de vencimiento debe ser posterior a la emisión.',
            'fechaEmision.before_or_equal' => 'La fecha de emisión no puede ser futura.',
        ]);

        try {
            if ($this->referenciaDocumento === null) {
                throw ValidationException::withMessages(['file' => 'Abre la carga desde el documento que deseas actualizar.']);
            }
            $service = app(DocumentacionUsuarioService::class);
            $service->subirDocumento(
                $this->usuario,
                $this->selectedTipoDoc->cod_tipo_doc,
                $this->archivoSubida,
                $this->fechaEmision,
                $this->fechaVencimiento,
                $this->observacionesArchivo,
                auth()->user(),
                $this->referenciaDocumento
            );

            $this->mostrarSubidaModal = false;
            $this->reset(['selectedTipoDoc', 'referenciaDocumento', 'archivoSubida', 'fechaEmision', 'fechaVencimiento', 'observacionesArchivo']);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Documento cargado',
                'text' => 'El documento ha sido cargado satisfactoriamente y está pendiente de validación.',
            ]);
        } catch (ValidationException $e) {
            $mapa = ['file' => 'archivoSubida', 'observaciones' => 'observacionesArchivo'];
            foreach ($e->errors() as $campo => $mensajes) {
                foreach ($mensajes as $mensaje) {
                    $this->addError($mapa[$campo] ?? $campo, $mensaje);
                }
            }
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de subida',
                'text' => 'No se pudo guardar el archivo. Intente nuevamente.',
            ]);
        }
    }

    public function validarDoc(string $codDoc): void
    {
        if (! auth()->user()->can('documentos.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permisos para validar documentos.',
            ]);

            return;
        }

        $documento = Documento::where('cod_usuario', $this->usuario->cod_usuario)->findOrFail($codDoc);
        app(DocumentacionUsuarioService::class)->validarDocumento($documento, auth()->user(), $this->usuario->getKey());

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Documento validado',
            'text' => 'El documento ha sido marcado como válido.',
        ]);
    }

    public function abrirObservarDoc(string $codDoc): void
    {
        abort_unless(auth()->user()?->can('documentos.gestionar'), 403);

        $this->selectedDocId = $codDoc;
        $this->motivoRechazo = '';
        $this->mostrarObservacionModal = true;
    }

    public function observarDoc(): void
    {
        abort_unless(auth()->user()?->can('documentos.gestionar'), 403);

        $this->validate([
            'motivoRechazo' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'motivoRechazo.required' => 'El motivo es obligatorio.',
            'motivoRechazo.min' => 'Indique un motivo más descriptivo.',
        ]);

        $documento = Documento::where('cod_usuario', $this->usuario->cod_usuario)->findOrFail($this->selectedDocId);
        app(DocumentacionUsuarioService::class)->observarDocumento($documento, $this->motivoRechazo, auth()->user(), $this->usuario->getKey());

        $this->mostrarObservacionModal = false;
        $this->reset(['selectedDocId', 'motivoRechazo']);

        $this->dispatch('swal', [
            'icon' => 'warning',
            'title' => 'Documento observado',
            'text' => 'Se ha enviado la observación al expediente del usuario.',
        ]);
    }

    public function abrirAnularDoc(string $codDoc): void
    {
        abort_unless(
            auth()->user()?->can('documentos.archivar') || auth()->user()?->can('documentos.gestionar'),
            403
        );

        $this->selectedDocId = $codDoc;
        $this->motivoRechazo = '';
        $this->mostrarAnulacionModal = true;
    }

    public function anularDoc(): void
    {
        abort_unless(
            auth()->user()?->can('documentos.archivar') || auth()->user()?->can('documentos.gestionar'),
            403
        );

        $this->validate([
            'motivoRechazo' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'motivoRechazo.required' => 'El motivo de la anulación es obligatorio.',
        ]);

        $documento = Documento::where('cod_usuario', $this->usuario->cod_usuario)->findOrFail($this->selectedDocId);
        app(DocumentacionUsuarioService::class)->anularDocumento($documento, $this->motivoRechazo, auth()->user(), $this->usuario->getKey());

        $this->mostrarAnulacionModal = false;
        $this->reset(['selectedDocId', 'motivoRechazo']);

        $this->dispatch('swal', [
            'icon' => 'error',
            'title' => 'Documento anulado',
            'text' => 'El documento ha sido anulado permanentemente.',
        ]);
    }

    public function descargarDoc(string $codDoc)
    {
        abort_unless(auth()->user()?->can('documentos.ver'), 403);

        $documento = Documento::where('cod_usuario', $this->usuario->cod_usuario)->findOrFail($codDoc);
        abort_unless(app(DocumentoLaboralPolicy::class)->view(auth()->user(), $documento), 403);

        // La ruta vuelve a autorizar y resuelve el disco privado/público del archivo original.
        return redirect()->route('admin.personal.documento.archivo', $documento);
    }

    // ══════════════════════════════════════════════
    // SEGURIDAD Y ACCESO
    // ══════════════════════════════════════════════

    public function restablecerPassword(): void
    {
        if (! auth()->user()->can('usuarios.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para restablecer contraseñas.',
            ]);

            return;
        }

        $tempPassword = app(GestionarSeguridadCuenta::class)->restablecerPassword(auth()->user(), $this->usuario);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Contraseña restablecida',
            'text' => "Se ha generado una clave temporal: {$tempPassword}",
        ]);
    }

    public function toggleAcceso(): void
    {
        if (! auth()->user()->can('usuarios.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para bloquear accesos.',
            ]);

            return;
        }

        if ($this->usuario->cod_usuario === auth()->id()) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción denegada',
                'text' => 'No puedes bloquear tu propia cuenta.',
            ]);

            return;
        }

        $nuevoAcceso = app(GestionarSeguridadCuenta::class)->toggleAcceso(auth()->user(), $this->usuario);
        $this->usuario->refresh();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Acceso actualizado',
            'text' => "El acceso al sistema ahora se encuentra {$nuevoAcceso}.",
        ]);
    }

    // ══════════════════════════════════════════════
    // REPORTES Y EXPORTACIÓN
    // ══════════════════════════════════════════════

    public function generarExpedientePdf()
    {
        if (! auth()->user()->can('usuarios.reportes.pdf')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para exportar el expediente del usuario.',
            ]);

            return null;
        }

        try {
            $fichaService = app(UsuarioFichaService::class);
            $docService = app(DocumentacionUsuarioService::class);

            $expediente = $fichaService->obtenerExpedienteCompleto($this->usuario);
            $checklist = $docService->obtenerChecklistUsuario($this->usuario);

            $viewData = [
                'usuario' => $this->usuario,
                'rol' => $expediente['rol'],
                'nombre_rol' => $expediente['nombre_rol'],
                'area' => $expediente['area'],
                'horarios' => $expediente['horarios'],
                'avance_documental' => $expediente['avance_documental'],
                'checklist' => $checklist,
                'fecha' => now()->format('d/m/Y H:i'),
                'usuario_solicitante' => auth()->user()->name,
            ];

            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate('expediente_'.$this->usuario->cod_usuario, 'pdf');

            $exportService = app(ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($this->usuario)
                ->event('reportes')
                ->log('Generó el expediente institucional en formato PDF.');

            return $exportService->exportPdf('pdf.exports.usuarios.expediente_ficha', $viewData, $filename);
        } catch (\Throwable $e) {
            Log::error('Error al exportar PDF de expediente: '.$e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el expediente en PDF: '.$e->getMessage(),
            ]);

            return null;
        }
    }

    public function generarExpedienteExcel()
    {
        if (! auth()->user()->can('usuarios.reportes.excel')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para exportar el expediente a Excel.',
            ]);

            return null;
        }

        try {
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate('expediente_'.$this->usuario->cod_usuario, 'xlsx');

            $exportService = app(ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($this->usuario)
                ->event('reportes')
                ->log('Generó el expediente institucional en formato Excel.');

            return $exportService->exportExcel(new UsuarioExpedienteExport($this->usuario), $filename);
        } catch (\Throwable $e) {
            Log::error('Error al exportar Excel de expediente: '.$e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el expediente en Excel: '.$e->getMessage(),
            ]);

            return null;
        }
    }

    // ══════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════

    public function render()
    {
        $fichaService = app(UsuarioFichaService::class);
        $docService = app(DocumentacionUsuarioService::class);

        $expediente = $fichaService->obtenerExpedienteCompleto($this->usuario);
        $checklist = $docService->obtenerChecklistUsuario($this->usuario);

        $filtrosHistorial = [
            'accion' => $this->filtroAccion,
            'fecha_desde' => $this->filtroFechaDesde,
            'fecha_hasta' => $this->filtroFechaHasta,
        ];

        $historialActividad = $fichaService->obtenerHistorialActividad($this->usuario, $filtrosHistorial);

        return view('livewire.identidad.usuario-ficha-panel', [
            'rol' => $expediente['rol'],
            'nombre_rol' => $expediente['nombre_rol'],
            'area' => $expediente['area'],
            'horarios' => $expediente['horarios'],
            'historial_horarios' => $expediente['historial_horarios'],
            'avance_documental' => $expediente['avance_documental'],
            'checklist' => $checklist,
            'historial_actividad' => $historialActividad,
        ]);
    }
}
