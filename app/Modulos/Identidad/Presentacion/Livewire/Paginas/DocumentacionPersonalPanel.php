<?php

namespace App\Modulos\Identidad\Presentacion\Livewire\Paginas;

use App\Models\Personal;
use App\Modulos\Identidad\Aplicacion\Acciones\AutorizarGestionPersonal;
use App\Modulos\Identidad\Aplicacion\Acciones\DocumentacionUsuarioService;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarSolicitudesDocumentales;
use App\Modulos\Identidad\Aplicacion\Acciones\RegistrarArchivoSolicitudDocumental;
use App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal;
use App\Modulos\Identidad\Aplicacion\Consultas\ConsultarDocumentacionPersonal;
use App\Modulos\Identidad\Dominio\Reglas\DocumentosPersonalInstitucional;
use App\Modulos\Identidad\Presentacion\Policies\DocumentoLaboralPolicy;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class DocumentacionPersonalPanel extends Component
{
    use WithFileUploads, WithPagination;

    public string $busqueda = '';

    public bool $formulario = false;

    public string $rol = 'ADMINISTRADOR';

    public string $tipoDocumento = 'OTROS';

    public string $nombreDocumento = '';

    public string $indicaciones = '';

    public string $motivo = '';

    public string $origenPlazo = 'SOLICITUD_POSTERIOR';

    public string $fechaLimite = '';

    public array $personas = [];

    #[Locked]
    public string $claveSolicitud = '';

    #[Locked]
    public ?string $destinatarioSeleccionado = null;

    public bool $detalleAbierto = false;

    public $archivo;

    public string $fechaEmision = '';

    public string $motivoRevision = '';

    public string $motivoProrroga = '';

    #[Locked]
    public string $mensaje = '';

    private function consulta()
    {
        return app(ConsultarDocumentacionPersonal::class)->consulta(auth()->user());
    }

    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatedRol(): void
    {
        $this->personas = [];
        $this->tipoDocumento = 'OTROS';
    }

    public function nuevaSolicitud(): void
    {
        $actor = app(AutorizarGestionPersonal::class)->autorizar(auth()->user());
        abort_unless($actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']), 403);
        app(CapacidadDocumentalPersonal::class)->exigir();
        $this->reset(['personas', 'nombreDocumento', 'indicaciones', 'motivo', 'fechaLimite']);
        $this->claveSolicitud = (string) Str::uuid();
        $this->formulario = true;
        $this->resetValidation();
    }

    public function cerrarSolicitud(): void
    {
        $this->formulario = false;
        $this->resetValidation();
    }

    public function emitirSolicitud(): void
    {
        app(GestionarSolicitudesDocumentales::class)->emitir(auth()->user(), [
            'rol' => $this->rol, 'tipo_documento' => $this->tipoDocumento, 'nombre_documento' => $this->nombreDocumento,
            'indicaciones' => $this->indicaciones ?: null, 'motivo' => $this->motivo, 'personas' => $this->personas,
            'origen_plazo' => $this->origenPlazo, 'fecha_limite' => $this->fechaLimite ?: null, 'clave_solicitud' => $this->claveSolicitud,
        ]);
        $this->formulario = false;
        $this->mensaje = 'Solicitud emitida y destinatarios conservados.';
        $this->resetPage();
        $this->dispatch('documentacion-personal-actualizada');
    }

    public function verDetalle(string $codigo): void
    {
        $this->consulta()->findOrFail($codigo);
        $this->destinatarioSeleccionado = $codigo;
        $this->detalleAbierto = true;
        $this->reset('archivo', 'fechaEmision', 'motivoRevision', 'motivoProrroga');
        $this->resetValidation();
    }

    public function cerrarDetalle(): void
    {
        $this->detalleAbierto = false;
        $this->destinatarioSeleccionado = null;
        $this->reset('archivo');
        $this->resetValidation();
    }

    public function guardarEntrega(): void
    {
        $this->consulta()->findOrFail($this->destinatarioSeleccionado);
        $this->validate(['archivo' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120', 'fechaEmision' => 'nullable|date_format:Y-m-d|before_or_equal:today']);
        app(RegistrarArchivoSolicitudDocumental::class)->ejecutar(auth()->user(), $this->destinatarioSeleccionado, $this->archivo, $this->fechaEmision ?: null);
        $this->reset('archivo', 'fechaEmision');
        $this->mensaje = 'Entrega guardada. Falta la revisión del documento.';
        $this->dispatch('documentacion-personal-actualizada');
    }

    public function revisar(string $documento, string $decision): void
    {
        $destino = $this->consulta()->findOrFail($this->destinatarioSeleccionado);
        $archivo = $destino->entregas->firstWhere('cod_documento', $documento)?->documento;
        abort_unless($archivo, 404);
        $servicio = app(DocumentacionUsuarioService::class);
        match ($decision) {
            'VALIDAR' => $servicio->validarDocumento($archivo, auth()->user(), $destino->personal->cod_usuario),
            'OBSERVAR' => $servicio->observarDocumento($archivo, $this->motivoRevision, auth()->user(), $destino->personal->cod_usuario),
            'ANULAR' => $servicio->anularDocumento($archivo, $this->motivoRevision, auth()->user(), $destino->personal->cod_usuario),
            default => abort(422),
        };
        $this->motivoRevision = '';
        $this->mensaje = 'Revisión documental guardada; se conserva el historial.';
        $this->dispatch('documentacion-personal-actualizada');
    }

    public function prorrogar(): void
    {
        $this->consulta()->findOrFail($this->destinatarioSeleccionado);
        app(GestionarSolicitudesDocumentales::class)->prorrogar(auth()->user(), $this->destinatarioSeleccionado, $this->motivoProrroga);
        $this->motivoProrroga = '';
        $this->mensaje = 'Única prórroga guardada: siete días sobre el límite original.';
        $this->dispatch('documentacion-personal-actualizada');
    }

    public function render()
    {
        $actor = auth()->user()?->fresh();
        abort_unless($actor?->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR'), 403);
        $lectura = app(ConsultarDocumentacionPersonal::class);
        $gestor = $lectura->esGestor($actor);
        abort_unless($gestor || $actor->personal, 403);
        $disponible = app(CapacidadDocumentalPersonal::class)->disponible();
        $destinatarios = $disponible ? $this->consulta()->when(trim($this->busqueda) !== '', fn ($q) => $q->whereHas('solicitud', fn ($s) => $s->whereLike('nombre_documento', '%'.trim($this->busqueda).'%')))->paginate(12) : null;
        $detalle = $disponible && $this->detalleAbierto ? $this->consulta()->findOrFail($this->destinatarioSeleccionado) : null;
        if ($disponible) {
            app(\App\Modulos\Identidad\Aplicacion\Consultas\PlazoExpedienteDocumental::class)->preparar(
                $destinatarios->getCollection()->concat($detalle ? [$detalle] : []));
        }
        $permisosDocumentales = $detalle
            ? app(DocumentoLaboralPolicy::class)->permisosDocumentos($actor, $detalle->entregas->pluck('documento')->filter())
            : [];
        $elegibles = collect();
        $tipos = [];
        if ($this->formulario) {
            app(AutorizarGestionPersonal::class)->autorizar($actor);
            abort_unless(in_array($this->rol, AutorizarGestionPersonal::ROLES_TRABAJADOR, true), 422);
            $elegibles = Personal::whereHas('usuario', fn ($u) => $u->role($this->rol)->whereDoesntHave('roles', fn ($r) => $r->whereNotIn('name', AutorizarGestionPersonal::ROLES_TRABAJADOR)))
                ->orderBy('apellido_paterno')->orderBy('nombres')->get();
            $tipos = DocumentosPersonalInstitucional::paraRol($this->rol);
        }

        return view('identidad::livewire.identidad.documentacion-personal-panel', compact('destinatarios', 'detalle', 'elegibles', 'tipos', 'gestor', 'disponible', 'lectura', 'permisosDocumentales'))
            ->layout('layouts.sistema');
    }
}
