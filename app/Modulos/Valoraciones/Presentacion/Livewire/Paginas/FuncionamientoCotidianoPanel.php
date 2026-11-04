<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\Atencion;
use App\Models\ControlFuncionalIadl as Iadl;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\FuentesValoracionOperativa;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarControlFuncionalIadl;
use App\Modulos\Valoraciones\Presentacion\Soporte\OpcionesFuncionamientoCotidiano as Opciones;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

final class FuncionamientoCotidianoPanel extends Component
{
    use WithPagination;

    #[Locked]
    public string $codResidente;

    #[Locked]
    public ?string $codigo = null;

    #[Locked]
    public ?string $anterior = null;

    #[Locked]
    public ?string $huella = null;

    #[Locked]
    public bool $abierto = false;

    #[Locked]
    public bool $revision = false;

    #[Locked]
    public bool $confirmarDescarte = false;

    #[Locked]
    public ?string $mensaje = null;

    public array $form = [];

    #[Locked]
    public array $formInicial = [];

    public function mount(Residente $residente): void
    {
        $this->codResidente = $residente->getKey();
        $this->contexto();
    }

    private function contexto(): array
    {
        $usuario = User::query()->findOrFail(auth()->id());
        auth()->setUser($usuario);
        $residente = FuentesValoracionOperativa::residentes(Residente::query())->findOrFail($this->codResidente);
        Gate::forUser($usuario)->authorize('consultar', [Iadl::class, $residente]);

        return [$usuario, $residente];
    }

    public function nuevo(): void
    {
        [$usuario, $residente] = $this->contexto();
        Gate::forUser($usuario)->authorize('registrar', [Iadl::class, $residente]);
        if ($this->abierto) {
            $this->addError('registro', 'Conserva o cierra la captura actual antes de abrir otra.');

            return;
        }
        $this->form = array_fill_keys(array_keys(Opciones::CAMPOS), '') + ['fecha_hora' => now()->format('Y-m-d\TH:i'), 'cod_atencion' => '', 'observacion' => ''];
        $this->anterior = $this->codigo = $this->huella = $this->mensaje = null;
        $this->formInicial = $this->form;
        $this->abierto = true;
        $this->revision = $this->confirmarDescarte = false;
        $this->resetValidation();
    }

    public function rectificar(string $id): void
    {
        [$usuario, $residente] = $this->contexto();
        Gate::forUser($usuario)->authorize('rectificar', [Iadl::class, $residente]);
        if ($this->abierto) {
            $this->addError('registro', 'Conserva o cierra la captura actual antes de rectificar otro registro.');

            return;
        }
        $fila = Iadl::query()->where('cod_residente', $this->codResidente)->findOrFail($id);
        if (Iadl::query()->where('cod_control_anterior', $id)->exists()) {
            $this->addError('registro', 'El original ya tiene una rectificación. Selecciona el registro vigente.');

            return;
        }
        $this->form = $fila->only(array_keys(Opciones::CAMPOS)) + ['fecha_hora' => $fila->fecha_hora->format('Y-m-d\TH:i'),
            'cod_atencion' => $fila->cod_atencion ?? '', 'observacion' => ''];
        $this->anterior = $id;
        $this->codigo = $this->huella = $this->mensaje = null;
        $this->formInicial = $this->form;
        $this->abierto = true;
        $this->revision = $this->confirmarDescarte = false;
        $this->resetValidation();
    }

    public function updatedForm(): void
    {
        $this->revision = false;
        $this->huella = null;
    }

    public function revisar(): void
    {
        [$usuario, $residente] = $this->contexto();
        abort_unless($this->abierto, 422);
        try {
            app(RegistrarControlFuncionalIadl::class)->validar($usuario, $residente, $this->form, $this->anterior);
        } catch (ValidationException $e) {
            $this->errores($e);

            return;
        }
        $this->resetValidation();
        $this->codigo ??= app(CodigosEntidad::class)->siguiente('controles_funcionales_iadl');
        $this->huella = hash('sha256', json_encode($this->form, JSON_THROW_ON_ERROR));
        $this->revision = true;
    }

    public function volver(): void
    {
        $this->revision = $this->confirmarDescarte = false;
    }

    public function guardar(): void
    {
        [$usuario, $residente] = $this->contexto();
        if (! $this->abierto || ! $this->revision || $this->codigo === null || $this->huella === null
            || ! hash_equals($this->huella, hash('sha256', json_encode($this->form, JSON_THROW_ON_ERROR)))) {
            $this->addError('registro', 'Revisa y confirma la observación antes de guardarla.');

            return;
        }
        try {
            app(RegistrarControlFuncionalIadl::class)->registrar($usuario, $residente, $this->form, $this->codigo, $this->anterior);
        } catch (ValidationException $e) {
            $this->errores($e);

            return;
        }
        $this->mensaje = $this->anterior === null ? 'Observación de funcionamiento cotidiano registrada.' : 'Rectificación registrada. El original se conserva en el historial.';
        $this->limpiar();
        $this->resetPage();
        $this->dispatch('funcionamiento-captura-cerrada');
    }

    public function cerrar(): void
    {
        $this->contexto();
        if ($this->form === $this->formInicial) {
            $this->limpiar();
            $this->dispatch('funcionamiento-captura-cerrada');

            return;
        }
        $this->confirmarDescarte = true;
    }

    public function descartar(): void
    {
        $this->contexto();
        $this->limpiar();
        $this->dispatch('funcionamiento-captura-cerrada');
    }

    private function limpiar(): void
    {
        $this->abierto = $this->revision = $this->confirmarDescarte = false;
        $this->form = $this->formInicial = [];
        $this->codigo = $this->anterior = $this->huella = null;
        $this->resetValidation();
    }

    private function errores(ValidationException $e): void
    {
        $this->revision = false;
        $this->huella = null;
        foreach ($e->errors() as $campo => $mensajes) {
            $this->addError($campo === 'registro' ? $campo : 'form.'.$campo, $mensajes[0]);
        }
    }

    public function render()
    {
        [$usuario, $residente] = $this->contexto();
        $registros = Iadl::query()->where('cod_residente', $this->codResidente)->with('personal')
            ->withExists(['sucesora'])->orderByDesc('registrado_en')->orderByDesc('cod_control_iadl')->paginate(10);
        $atenciones = $this->abierto ? Atencion::query()->where('cod_residente', $this->codResidente)
            ->orderByDesc('fecha_hora')->limit(100)->get(['cod_atencion', 'fecha_hora', 'tipo_atencion']) : collect();
        if ($this->abierto && ($this->form['cod_atencion'] ?? '') !== '' && ! $atenciones->contains('cod_atencion', $this->form['cod_atencion'])) {
            $seleccionada = Atencion::query()->where('cod_residente', $this->codResidente)->find($this->form['cod_atencion']);
            if ($seleccionada !== null) {
                $atenciones->push($seleccionada);
            }
        }
        $atencionRevisada = $atenciones->firstWhere('cod_atencion', $this->form['cod_atencion'] ?? null);
        $profesional = $usuario->personal()->where('estado', 'ACTIVO')->first();
        $puedeCrear = Gate::forUser($usuario)->allows('registrar', [Iadl::class, $residente]);
        $puedeRectificar = Gate::forUser($usuario)->allows('rectificar', [Iadl::class, $residente]);
        $campos = Opciones::CAMPOS;
        $opciones = Opciones::OPCIONES;
        $retorno = $usuario->hasRole('PSICOLOGO/A') ? route('admin.psicologia.residente.ficha', ['residente' => $residente, 'tab' => 'funcionamiento'])
            : route('admin.sistema-experto.petersen-original', $residente);

        return view('valoraciones::livewire.valoraciones.funcionamiento-cotidiano-panel', compact('residente', 'registros', 'atenciones', 'profesional', 'puedeCrear', 'puedeRectificar', 'campos', 'opciones', 'retorno', 'atencionRevisada'))->layout('layouts.sistema');
    }
}
