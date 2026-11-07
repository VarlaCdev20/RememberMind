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
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class DocumentacionPersonalPanel extends Component
{
    use WithFileUploads, WithPagination;

    public string $busqueda = '';

    #[Url(except: 'archivos')]
    public string $vista = 'archivos';

    #[Locked] public ?string $carpetaUsuario = null;

    public string $estadoArchivo = '';

    public string $personaArchivo = '';

    public string $rolArchivo = '';

    public string $formatoArchivo = '';

    #[Locked]
    public ?string $documentoSeleccionado = null;

    public bool $vistaPreviaAbierta = false;

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
        $this->resetPage('archivosPage');
        $this->resetPage('carpetasPage');
    }

    public function updatedEstadoArchivo(): void
    {
        $this->resetPage('archivosPage');
        $this->resetPage('carpetasPage');
    }

    public function updatedPersonaArchivo(): void
    {
        $this->resetPage('archivosPage');
        $this->resetPage('carpetasPage');
    }

    public function updatedRolArchivo(): void
    {
        $this->personaArchivo = '';
        $this->resetPage('archivosPage');
        $this->resetPage('carpetasPage');
    }

    public function updatedFormatoArchivo(): void
    {
        $this->resetPage('archivosPage');
        $this->resetPage('carpetasPage');
    }

    public function cambiarVista(string $vista): void
    {
        abort_unless(in_array($vista, ['archivos', 'solicitudes'], true), 422);
        $this->vista = $vista;
        $this->carpetaUsuario = null;
        $this->busqueda = '';
        $this->resetPage();
        $this->resetPage('archivosPage');
        $this->resetPage('carpetasPage');
    }

    public function limpiarFiltros(): void
    {
        $this->reset('busqueda', 'estadoArchivo', 'personaArchivo', 'rolArchivo', 'formatoArchivo');
        $this->updatedBusqueda();
    }

    public function abrirCarpetaUsuario(string $codigo): void
    {
        abort_unless(app(ConsultarDocumentacionPersonal::class)->archivos(auth()->user())->where('cod_usuario', $codigo)->exists(), 404);
        $this->carpetaUsuario = $codigo;
        $this->documentoSeleccionado = null;
        $this->vistaPreviaAbierta = false;
        $this->resetPage('archivosPage');
        $this->dispatch('personal-carpeta-abierta');
    }

    public function cerrarCarpetaUsuario(): void
    {
        app(ConsultarDocumentacionPersonal::class)->archivos(auth()->user());
        $codigo = $this->carpetaUsuario;
        $this->carpetaUsuario = null;
        $this->documentoSeleccionado = null;
        $this->vistaPreviaAbierta = false;
        $this->resetPage('archivosPage');
        $this->dispatch('personal-carpeta-cerrada', usuario: $codigo);
    }

    public function verArchivo(string $codigo): void
    {
        $documento = app(ConsultarDocumentacionPersonal::class)->archivos(auth()->user())
            ->when($this->carpetaUsuario, fn ($q) => $q->where('cod_usuario', $this->carpetaUsuario))->findOrFail($codigo);
        abort_unless(app(DocumentoLaboralPolicy::class)->view(auth()->user(), $documento), 403);
        $this->documentoSeleccionado = $documento->getKey();
        $this->vistaPreviaAbierta = true;
    }

    public function cerrarVistaPrevia(): void
    {
        $this->dispatch('personal-documento-cerrado', documento: $this->documentoSeleccionado);
        $this->vistaPreviaAbierta = false;
        $this->documentoSeleccionado = null;
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
        $this->vista = 'solicitudes';
        $this->mensaje = 'Solicitud emitida y destinatarios conservados.';
        $this->resetPage();
        $this->dispatch('documentacion-personal-actualizada');
    }

    public function verDetalle(string $codigo): void
    {
        $this->consulta()->findOrFail($codigo);
        $this->vista = 'solicitudes';
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
        abort_unless(in_array($this->vista, ['archivos', 'solicitudes'], true), 422);
        abort_unless(in_array($this->estadoArchivo, ['', 'CARGADO', 'PENDIENTE', 'VALIDADO', 'OBSERVADO', 'VENCIDO', 'ANULADO', 'ANULADA', 'REEMPLAZADO'], true), 422);
        abort_unless(in_array($this->formatoArchivo, ['', 'PDF', 'IMAGEN', 'OTROS'], true), 422);
        $personasArchivo = collect();
        $rolesArchivo = collect();
        if ($this->vista === 'archivos') {
            $personasArchivo = \App\Models\User::query()
                ->whereIn('cod_usuario', $lectura->archivos($actor)->reorder()->select('cod_usuario'))
                ->with(['personal', 'roles'])->get()
                ->sortBy(fn ($u) => trim($u->personal->nombres.' '.$u->personal->apellido_paterno));
            $rolesArchivo = $personasArchivo->flatMap(fn ($u) => $u->roles->pluck('name'))->unique()->sort()->values();
            abort_unless($this->rolArchivo === '' || $rolesArchivo->contains($this->rolArchivo), 422);
            if ($this->rolArchivo !== '') {
                $personasArchivo = $personasArchivo->filter(fn ($u) => $u->hasRole($this->rolArchivo));
            }
        }
        $hayFiltrosArchivo = trim($this->busqueda) !== '' || $this->estadoArchivo !== '' || $this->personaArchivo !== '' || $this->rolArchivo !== '' || $this->formatoArchivo !== '';
        $consultaArchivos = $this->vista === 'archivos' ? $lectura->archivos($actor)
            ->when($this->estadoArchivo !== '', fn ($q) => $q->where('estado', $this->estadoArchivo))
            ->when($this->personaArchivo !== '', fn ($q) => $q->where('cod_usuario', $this->personaArchivo))
            ->when($this->rolArchivo !== '', fn ($q) => $q->whereHas('usuario.roles', fn ($r) => $r->where('name', $this->rolArchivo)))
            ->when($this->formatoArchivo !== '', function ($q): void {
                $extensiones = ['jpg', 'jpeg', 'png', 'webp'];
                if ($this->formatoArchivo === 'PDF') {
                    $q->whereLike('ruta_archivo', '%.pdf');
                } elseif ($this->formatoArchivo === 'IMAGEN') {
                    $q->where(function ($f) use ($extensiones): void {
                        foreach ($extensiones as $extension) {
                            $f->orWhereLike('ruta_archivo', '%.'.$extension);
                        }
                    });
                } else {
                    foreach (['pdf', ...$extensiones] as $extension) {
                        $q->whereNotLike('ruta_archivo', '%.'.$extension);
                    }
                }
            })
            ->when(trim($this->busqueda) !== '', function ($q): void {
                $palabras = array_slice(preg_split('/\s+/u', mb_substr(trim($this->busqueda), 0, 160)), 0, 12);
                foreach ($palabras as $palabra) {
                    $q->where(fn ($f) => $f->whereLike('nombre', '%'.$palabra.'%')
                        ->orWhereHas('usuario.personal', fn ($p) => $p->whereLike('nombres', '%'.$palabra.'%')->orWhereLike('apellido_paterno', '%'.$palabra.'%')->orWhereLike('apellido_materno', '%'.$palabra.'%')));
                }
            }) : null;
        $carpetaActual = null;
        if ($this->vista === 'archivos' && $this->carpetaUsuario) {
            abort_unless($lectura->archivos($actor)->where('cod_usuario', $this->carpetaUsuario)->exists(), 404);
            $carpetaActual = \App\Models\User::with('personal')->findOrFail($this->carpetaUsuario);
        }
        $carpetasUsuarios = null;
        if ($consultaArchivos && ! $carpetaActual) {
            $totales = (clone $consultaArchivos)->reorder()->select('cod_usuario')
                ->selectRaw('COUNT(*) as total_documentos')->groupBy('cod_usuario');
            $carpetasUsuarios = \App\Models\User::query()->joinSub($totales, 'archivos_autorizados', 'usuarios.cod_usuario', '=', 'archivos_autorizados.cod_usuario')
                ->join('personal as titular_carpeta', 'titular_carpeta.cod_usuario', '=', 'usuarios.cod_usuario')
                ->select('usuarios.*', 'archivos_autorizados.total_documentos')->with('personal')
                ->orderBy('titular_carpeta.apellido_paterno')->orderBy('titular_carpeta.nombres')->orderBy('usuarios.cod_usuario')
                ->paginate(12, pageName: 'carpetasPage');
        }
        $archivos = $consultaArchivos ? $consultaArchivos->when($this->carpetaUsuario, fn ($q) => $q->where('cod_usuario', $this->carpetaUsuario))
            ->paginate(12, pageName: 'archivosPage') : null;
        $permisosArchivos = $archivos ? app(DocumentoLaboralPolicy::class)->permisosDocumentos($actor, $archivos->getCollection()) : [];
        $documentoVisible = $this->vistaPreviaAbierta ? $lectura->archivos($actor)->findOrFail($this->documentoSeleccionado) : null;
        $archivoDisponible = false;
        if ($documentoVisible) {
            abort_unless(app(DocumentoLaboralPolicy::class)->view($actor, $documentoVisible), 403);
            $disk = str_starts_with($documentoVisible->ruta_archivo, 'documentos_personal_privados/') ? 'local' : 'public';
            $archivoDisponible = Storage::disk($disk)->exists($documentoVisible->ruta_archivo);
        }
        $destinatarios = $disponible && $this->vista === 'solicitudes' ? $this->consulta()->when(trim($this->busqueda) !== '', fn ($q) => $q->whereHas('solicitud', fn ($s) => $s->whereLike('nombre_documento', '%'.trim($this->busqueda).'%')))->paginate(12) : null;
        $detalle = $disponible && $this->detalleAbierto ? $this->consulta()->findOrFail($this->destinatarioSeleccionado) : null;
        if ($destinatarios || $detalle) {
            app(\App\Modulos\Identidad\Aplicacion\Consultas\PlazoExpedienteDocumental::class)->preparar(
                ($destinatarios?->getCollection() ?? collect())->concat($detalle ? [$detalle] : []));
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

        return view('identidad::livewire.identidad.documentacion-personal-panel', compact('destinatarios', 'detalle', 'elegibles', 'tipos', 'gestor', 'disponible', 'lectura', 'permisosDocumentales', 'archivos', 'permisosArchivos', 'documentoVisible', 'archivoDisponible', 'personasArchivo', 'rolesArchivo', 'hayFiltrosArchivo', 'carpetasUsuarios', 'carpetaActual'))
            ->layout('layouts.sistema');
    }
}
