<?php

namespace App\Modulos\Social\Presentacion\Livewire\Paginas;

use App\Compartido\Infraestructura\Notificaciones\CorreoNoConfigurado;
use App\Models\Contacto;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarAccesoFamiliar;
use App\Modulos\Social\Aplicacion\Acciones\ConservarVersionVinculo;
use App\Modulos\Social\Aplicacion\Acciones\GestionarRedApoyo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class RedApoyoPanel extends Component
{
    use WithPagination;

    public string $vista = 'residentes';

    public string $buscar = '';

    public string $residenteSeleccionado = '';

    public bool $modalContacto = false;

    public bool $modalVinculo = false;

    public bool $modalVigencia = false;

    #[Locked]
    public ?string $contactoEditar = null;

    #[Locked]
    public ?string $vinculoEditar = null;

    #[Locked]
    public ?string $vigenciaEditar = null;

    #[Locked]
    public string $vigenciaResidente = '';

    #[Locked]
    public string $nuevoEstado = '';

    public string $motivo = '';

    public array $contacto = [];

    public string $contactoVinculo = '';

    public string $residenteVinculo = '';

    public string $parentesco = '';

    public bool $responsablePrincipal = false;

    public bool $contactoEmergencia = false;

    public string $observacion = '';

    #[Locked]
    public string $mensaje = '';

    public bool $modalCuenta = false;

    #[Locked]
    public ?string $cuentaContacto = null;

    public string $correoAcceso = '';

    #[Locked]
    public bool $editarCorreoAcceso = false;

    public function mount(): void
    {
        $this->lectura();
        $this->residenteSeleccionado = (string) request('residente', request('adulto', ''));
    }

    private function lectura(): void
    {
        app(GestionarRedApoyo::class)->autorizar(auth()->user(), 'residentes_contactos.ver', false);
        Gate::authorize('viewAny', Residente::class);
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }

    public function cambiarVista(string $vista): void
    {
        if ($vista === 'personas') {
            app(GestionarRedApoyo::class)->autorizar(auth()->user(), 'contactos.ver', false);
        }
        abort_unless(in_array($vista, ['personas', 'residentes'], true), 422);
        $this->vista = $vista;
        $this->resetPage();
    }

    public function limpiar(): void
    {
        $this->buscar = '';
        $this->resetPage();
    }

    public function nuevoContacto(): void
    {
        app(GestionarRedApoyo::class)->autorizar(auth()->user(), 'contactos.gestionar');
        $this->contactoEditar = null;
        $this->contacto = array_fill_keys(['nombres', 'apellido_paterno', 'apellido_materno', 'numero_documento', 'telefono', 'celular', 'correo', 'direccion', 'observacion'], '');
        $this->modalContacto = true;
        $this->resetValidation();
    }

    public function editarContacto(string $id): void
    {
        app(GestionarRedApoyo::class)->autorizar(auth()->user(), 'contactos.gestionar');
        $contacto = Contacto::findOrFail($id);
        $this->contactoEditar = $id;
        $this->contacto = $contacto->only(['nombres', 'apellido_paterno', 'apellido_materno', 'numero_documento', 'telefono', 'celular', 'correo', 'direccion', 'observacion']);
        $this->modalContacto = true;
        $this->resetValidation();
    }

    public function guardarContacto(): void
    {
        app(GestionarRedApoyo::class)->guardarContacto(auth()->user(), $this->contacto, $this->contactoEditar);
        $this->modalContacto = false;
        $this->mensaje = 'Contacto guardado. Sus vínculos y su cuenta de acceso no se modificaron.';
    }

    public function cerrarContacto(): void
    {
        $this->modalContacto = false;
        $this->resetValidation();
    }

    public function nuevoVinculo(?string $contactoId = null): void
    {
        app(GestionarRedApoyo::class)->autorizar(auth()->user(), 'residentes_contactos.gestionar');
        $this->vinculoEditar = null;
        $this->contactoVinculo = $contactoId ?? '';
        $this->residenteVinculo = $this->residenteSeleccionado;
        $this->parentesco = '';
        $this->observacion = '';
        $this->responsablePrincipal = false;
        $this->contactoEmergencia = false;
        $this->modalVinculo = true;
        $this->resetValidation();
    }

    public function editarVinculo(string $id): void
    {
        app(GestionarRedApoyo::class)->autorizar(auth()->user(), 'residentes_contactos.gestionar');
        $v = ResidenteContacto::with('residente')->findOrFail($id);
        Gate::authorize('view', $v->residente);
        $this->vinculoEditar = $id;
        $this->residenteVinculo = $v->cod_residente;
        $this->contactoVinculo = $v->cod_contacto;
        $this->parentesco = $v->parentesco;
        $this->observacion = $v->observacion ?? '';
        $this->responsablePrincipal = $v->responsable_principal;
        $this->contactoEmergencia = $v->contacto_emergencia;
        $this->modalVinculo = true;
        $this->resetValidation();
    }

    public function guardarVinculo(): void
    {
        $this->validate(['residenteVinculo' => 'required|string', 'contactoVinculo' => 'required|string']);
        app(GestionarRedApoyo::class)->vincular(auth()->user(), $this->residenteVinculo, $this->contactoVinculo, [
            'parentesco' => $this->parentesco, 'responsable_principal' => $this->responsablePrincipal,
            'contacto_emergencia' => $this->contactoEmergencia, 'observacion' => $this->observacion,
        ], $this->vinculoEditar);
        $this->modalVinculo = false;
        $this->mensaje = 'Vínculo guardado. No se concedieron permisos de información ni de salida.';
    }

    public function cerrarVinculo(): void
    {
        $this->modalVinculo = false;
        $this->resetValidation();
    }

    public function abrirVigencia(string $id): void
    {
        app(GestionarRedApoyo::class)->autorizar(auth()->user(), 'residentes_contactos.gestionar');
        $v = ResidenteContacto::with('residente')->findOrFail($id);
        Gate::authorize('view', $v->residente);
        $this->vigenciaEditar = $id;
        $this->vigenciaResidente = $v->cod_residente;
        $this->nuevoEstado = $v->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $this->motivo = '';
        $this->modalVigencia = true;
        $this->resetValidation();
    }

    public function guardarVigencia(): void
    {
        app(GestionarRedApoyo::class)->cambiarVigencia(auth()->user(), $this->vigenciaResidente, $this->vigenciaEditar ?? '', $this->nuevoEstado, $this->motivo);
        $this->modalVigencia = false;
        $this->mensaje = 'Vigencia actualizada. Se conservan la cuenta y los registros anteriores.';
    }

    public function cerrarVigencia(): void
    {
        $this->modalVigencia = false;
        $this->resetValidation();
    }

    public function abrirCuenta(string $contactoId): void
    {
        $persona = Contacto::findOrFail($contactoId);
        app(GestionarAccesoFamiliar::class)->autorizar(auth()->user(), $persona);
        $this->cuentaContacto = $contactoId;
        $this->editarCorreoAcceso = (bool) $persona->cod_usuario;
        $this->correoAcceso = $persona->usuario?->correo ?? '';
        $this->modalCuenta = true;
        $this->resetValidation();
    }

    public function crearCuenta(): void
    {
        if ($this->editarCorreoAcceso) {
            app(GestionarAccesoFamiliar::class)->actualizarCorreo(auth()->user(), $this->cuentaContacto ?? '', $this->correoAcceso);
            $this->modalCuenta = false;
            $this->mensaje = 'Correo de acceso actualizado. Los datos de contacto y los vínculos no cambiaron.';

            return;
        }
        try {
            app(GestionarAccesoFamiliar::class)->crear(auth()->user(), $this->cuentaContacto ?? '', $this->correoAcceso);
        } catch (CorreoNoConfigurado $error) {
            $this->addError('correo', CorreoNoConfigurado::AVISO);

            return;
        }
        $this->modalCuenta = false;
        $this->mensaje = 'Cuenta Familiar creada. El servicio de correo recibió el enlace para que su titular elija la contraseña.';
    }

    public function cerrarCuenta(): void
    {
        $this->modalCuenta = false;
        $this->resetValidation();
    }

    public function recuperarCuenta(string $contactoId): void
    {
        try {
            app(GestionarAccesoFamiliar::class)->recuperar(auth()->user(), $contactoId);
        } catch (CorreoNoConfigurado $error) {
            $this->addError('acceso', CorreoNoConfigurado::AVISO);

            return;
        }
        $this->mensaje = 'El servicio de correo recibió la solicitud de enlace. No se modificaron la contraseña ni los vínculos.';
    }

    public function cambiarAcceso(string $contactoId, string $estadoEsperado): void
    {
        app(GestionarAccesoFamiliar::class)->cambiarEstado(auth()->user(), $contactoId, $estadoEsperado);
        $this->mensaje = 'Acceso actualizado. Los vínculos y las autorizaciones se conservaron.';
    }

    public function render()
    {
        $this->lectura();
        $actor = auth()->user();
        if ($this->vista === 'personas') {
            app(GestionarRedApoyo::class)->autorizar($actor, 'contactos.ver', false);
        }
        $residente = $this->residenteSeleccionado ? Residente::findOrFail($this->residenteSeleccionado) : null;
        if ($residente) {
            Gate::authorize('view', $residente);
        }
        $residentes = Residente::where('estado', 'ADMITIDO')->when($this->vista === 'residentes' && trim($this->buscar) !== '', fn ($q) => $q->where(fn ($n) => $n->whereLike('nombres', '%'.trim($this->buscar).'%')->orWhereLike('apellido_paterno', '%'.trim($this->buscar).'%')->orWhereLike('apellido_materno', '%'.trim($this->buscar).'%')))
            ->orderBy('apellido_paterno')->orderBy('nombres')->limit(40)->get(['cod_residente', 'nombres', 'apellido_paterno', 'apellido_materno', 'estado']);
        $contactos = Contacto::query()->when(! $actor->can('contactos.ver'), fn ($q) => $q->whereHas('vinculos', fn ($v) => $v->where('cod_residente', $residente?->getKey() ?? '')))
            ->when($this->vista === 'personas' && trim($this->buscar) !== '', fn ($q) => $q->where(fn ($n) => $n->whereLike('nombres', '%'.trim($this->buscar).'%')->orWhereLike('apellido_paterno', '%'.trim($this->buscar).'%')->orWhereLike('numero_documento', '%'.trim($this->buscar).'%')))
            ->with(['vinculos' => fn ($q) => $q->when(! $actor->can('contactos.ver'), fn ($v) => $v->where('cod_residente', $residente?->getKey() ?? ''))->with('residente')])->orderBy('apellido_paterno')->orderBy('nombres')->paginate(12);
        if ($actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']) && $actor->can('contactos.gestionar')) {
            $contactos->getCollection()->load('usuario');
        } else {
            $contactos->getCollection()->each(fn ($c) => $c->setRelation('usuario', null));
        }
        $vinculos = $residente ? ResidenteContacto::where('cod_residente', $residente->getKey())->with('contacto')->orderBy('parentesco')->get() : collect();
        foreach ($contactos as $c) {
            foreach ($c->vinculos as $v) {
                Gate::authorize('view', $v->residente);
            }
        }
        $historiaDisponible = app(ConservarVersionVinculo::class)->disponible();
        $personasElegibles = $this->modalVinculo && $actor->can('contactos.ver') ? Contacto::where('estado', 'ACTIVO')->orderBy('apellido_paterno')->get(['cod_contacto', 'nombres', 'apellido_paterno', 'apellido_materno']) : collect();
        $historiaVinculo = collect();
        if ($this->modalVinculo && $this->vinculoEditar && app(ConservarVersionVinculo::class)->disponible()) {
            $fuente = ResidenteContacto::with('residente')->findOrFail($this->vinculoEditar);
            Gate::authorize('view', $fuente->residente);
            $historiaVinculo = DB::table('versiones_vinculo_contacto')->where('cod_residente_contacto', $fuente->getKey())->orderBy('numero_version')->get();
        }

        return view('social::livewire.residentes.red-apoyo-panel', compact('contactos', 'residentes', 'residente', 'vinculos', 'personasElegibles', 'historiaVinculo', 'historiaDisponible'))->layout('layouts.sistema');
    }
}
