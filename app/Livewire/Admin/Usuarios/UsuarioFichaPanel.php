<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use App\Models\DocumentoUsuario;
use App\Models\TipoDocumentoUsuario;
use App\Services\Usuarios\UsuarioFichaService;
use App\Services\Usuarios\DocumentacionUsuarioService;
use App\Exports\UsuarioExpedienteExport;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UsuarioFichaPanel extends Component
{
    use WithFileUploads, WithPagination;

    public User $usuario;
    public string $activeTab = 'datos_personales';

    // ── Documentación ──
    public $selectedTipoDoc = null;
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
        $tipo = TipoDocumentoUsuario::findOrFail($codTipoDoc);
        $this->selectedTipoDoc = $tipo;
        $this->archivoSubida = null;
        $this->fechaEmision = null;
        $this->fechaVencimiento = null;
        $this->observacionesArchivo = null;
        $this->mostrarSubidaModal = true;
    }

    public function subirArchivo(): void
    {
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
            $service = app(DocumentacionUsuarioService::class);
            $service->subirDocumento(
                $this->usuario,
                $this->selectedTipoDoc->cod_tipo_doc,
                $this->archivoSubida,
                $this->fechaEmision,
                $this->fechaVencimiento,
                $this->observacionesArchivo,
                auth()->user()
            );

            $this->mostrarSubidaModal = false;
            $this->reset(['selectedTipoDoc', 'archivoSubida', 'fechaEmision', 'fechaVencimiento', 'observacionesArchivo']);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Documento cargado',
                'text' => 'El documento ha sido cargado satisfactoriamente y está pendiente de validación.'
            ]);
        } catch (\Throwable $e) {
            Log::error("Error al subir documento de usuario: " . $e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de subida',
                'text' => 'No se pudo guardar el archivo: ' . $e->getMessage()
            ]);
        }
    }

    public function validarDoc(string $codDoc): void
    {
        if (!auth()->user()->can('documentos_usuarios.validar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permisos para validar documentos.'
            ]);
            return;
        }

        $documento = DocumentoUsuario::findOrFail($codDoc);
        app(DocumentacionUsuarioService::class)->validarDocumento($documento, auth()->user());

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Documento validado',
            'text' => 'El documento ha sido marcado como válido.'
        ]);
    }

    public function abrirObservarDoc(string $codDoc): void
    {
        $this->selectedDocId = $codDoc;
        $this->motivoRechazo = '';
        $this->mostrarObservacionModal = true;
    }

    public function observarDoc(): void
    {
        $this->validate([
            'motivoRechazo' => ['required', 'string', 'min:5', 'max:255']
        ], [
            'motivoRechazo.required' => 'El motivo es obligatorio.',
            'motivoRechazo.min' => 'Indique un motivo más descriptivo.'
        ]);

        $documento = DocumentoUsuario::findOrFail($this->selectedDocId);
        app(DocumentacionUsuarioService::class)->observarDocumento($documento, $this->motivoRechazo, auth()->user());

        $this->mostrarObservacionModal = false;
        $this->reset(['selectedDocId', 'motivoRechazo']);

        $this->dispatch('swal', [
            'icon' => 'warning',
            'title' => 'Documento observado',
            'text' => 'Se ha enviado la observación al expediente del usuario.'
        ]);
    }

    public function abrirAnularDoc(string $codDoc): void
    {
        $this->selectedDocId = $codDoc;
        $this->motivoRechazo = '';
        $this->mostrarAnulacionModal = true;
    }

    public function anularDoc(): void
    {
        $this->validate([
            'motivoRechazo' => ['required', 'string', 'min:5', 'max:255']
        ], [
            'motivoRechazo.required' => 'El motivo de la anulación es obligatorio.'
        ]);

        $documento = DocumentoUsuario::findOrFail($this->selectedDocId);
        app(DocumentacionUsuarioService::class)->anularDocumento($documento, $this->motivoRechazo, auth()->user());

        $this->mostrarAnulacionModal = false;
        $this->reset(['selectedDocId', 'motivoRechazo']);

        $this->dispatch('swal', [
            'icon' => 'error',
            'title' => 'Documento anulado',
            'text' => 'El documento ha sido anulado permanentemente.'
        ]);
    }

    public function descargarDoc(string $codDoc)
    {
        $documento = DocumentoUsuario::findOrFail($codDoc);
        
        if (!Storage::disk('public')->exists($documento->archivo)) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Archivo no encontrado',
                'text' => 'El archivo no existe físicamente en el servidor.'
            ]);
            return null;
        }

        return Storage::disk('public')->download($documento->archivo, $documento->nombre_documento . '.' . $documento->extension);
    }

    // ══════════════════════════════════════════════
    // SEGURIDAD Y ACCESO
    // ══════════════════════════════════════════════

    public function restablecerPassword(): void
    {
        if (!auth()->user()->can('usuarios.acceso.restablecer_password')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para restablecer contraseñas.'
            ]);
            return;
        }

        $nombres = strtoupper(preg_replace('/\s+/', '', $this->usuario->nombres ?? 'USU'));
        $apPaterno = strtoupper(preg_replace('/\s+/', '', $this->usuario->ap_paterno ?? 'RM'));
        $documento = preg_replace('/\D/', '', $this->usuario->numero_documento ?? '');
        $tempPassword = substr($nombres, 0, 3) . substr($apPaterno, 0, 3) . ($documento ?: now()->format('His'));

        $this->usuario->update([
            'password' => \Illuminate\Support\Facades\Hash::make($tempPassword)
        ]);

        activity('Usuarios')
            ->causedBy(auth()->user())
            ->performedOn($this->usuario)
            ->event('seguridad')
            ->log("Restableció la contraseña del usuario {$this->usuario->name} a una clave temporal.");

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Contraseña restablecida',
            'text' => "Se ha generado una clave temporal: {$tempPassword}"
        ]);
    }

    public function toggleAcceso(): void
    {
        if (!auth()->user()->can('usuarios.acceso.bloquear')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para bloquear accesos.'
            ]);
            return;
        }

        if ($this->usuario->cod_usu === auth()->id()) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción denegada',
                'text' => 'No puedes bloquear tu propia cuenta.'
            ]);
            return;
        }

        $nuevoAcceso = $this->usuario->acceso_sistema === 'HABILITADO' ? 'BLOQUEADO' : 'HABILITADO';
        $this->usuario->update(['acceso_sistema' => $nuevoAcceso]);

        activity('Usuarios')
            ->causedBy(auth()->user())
            ->performedOn($this->usuario)
            ->event('seguridad')
            ->log(($nuevoAcceso === 'HABILITADO' ? 'Habilitó' : 'Bloqueó') . " el acceso de {$this->usuario->name} al sistema.");

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Acceso actualizado',
            'text' => "El acceso al sistema ahora se encuentra {$nuevoAcceso}."
        ]);
    }

    // ══════════════════════════════════════════════
    // REPORTES Y EXPORTACIÓN
    // ══════════════════════════════════════════════

    public function generarExpedientePdf()
    {
        if (!auth()->user()->can('usuarios.reportes.pdf')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para exportar el expediente del usuario.'
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

            $fileNameService = app(\App\Services\Reports\ReportFileNameService::class);
            $filename = $fileNameService->generate('expediente_' . $this->usuario->cod_usu, 'pdf');

            $exportService = app(\App\Services\Reports\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($this->usuario)
                ->event('reportes')
                ->log("Generó el expediente institucional en formato PDF.");

            return $exportService->exportPdf('reports.usuarios.expediente_ficha', $viewData, $filename);
        } catch (\Throwable $e) {
            Log::error("Error al exportar PDF de expediente: " . $e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el expediente en PDF: ' . $e->getMessage()
            ]);
            return null;
        }
    }

    public function generarExpedienteExcel()
    {
        if (!auth()->user()->can('usuarios.reportes.excel')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para exportar el expediente a Excel.'
            ]);
            return null;
        }

        try {
            $fileNameService = app(\App\Services\Reports\ReportFileNameService::class);
            $filename = $fileNameService->generate('expediente_' . $this->usuario->cod_usu, 'xlsx');

            $exportService = app(\App\Services\Reports\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($this->usuario)
                ->event('reportes')
                ->log("Generó el expediente institucional en formato Excel.");

            return $exportService->exportExcel(new UsuarioExpedienteExport($this->usuario), $filename);
        } catch (\Throwable $e) {
            Log::error("Error al exportar Excel de expediente: " . $e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el expediente en Excel: ' . $e->getMessage()
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

        return view('livewire.admin.usuarios.usuario-ficha-panel', [
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
