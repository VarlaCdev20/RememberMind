<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas;

use App\Models\ReglaExperta;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarReglasExpertas;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

/** Interacción de gobierno; las decisiones, permisos y transacciones pertenecen al backend. */
final class GobiernoReglasPanel extends Component
{
    use WithPagination;

    public string $versionSeleccionada = '';

    public string $codigoVersion = '';

    public string $motivoCambio = '';

    public bool $agregarRegla = false;

    public array $nueva = ['codigo' => '', 'nombre' => '', 'descripcion' => '', 'fundamento' => '',
        'condiciones' => [['variable' => '', 'valor' => '']], 'criterio_dominio' => '', 'valor_consecuencia' => ''];

    public string $decision = 'APROBADA';

    public string $justificacion = '';

    public array $entradaPrueba = [];

    #[Locked]
    public ?string $reglaPrueba = null;

    #[Locked]
    public ?array $resultadoPrueba = null;

    #[Locked]
    public ?string $reglaArchivo = null;

    #[Locked]
    public ?string $reglaRevision = null;

    #[Locked]
    public ?string $mensaje = null;

    public function mount(): void
    {
        $this->autorizar();
    }

    public function registrarInstitucional(): void
    {
        $actor = $this->autorizar('gestionarReglas');
        $this->resetErrorBag('gobierno');
        try {
            $this->versionSeleccionada = app(RegistrarConocimientoInstitucional::class)->ejecutar($actor);
            $this->updatedVersionSeleccionada();
            $this->mensaje = 'Base institucional registrada: cinco dominios, diecinueve componentes y diecisiete reglas. Las revisiones profesionales permanecen pendientes.';
        } catch (DomainException $e) {
            $this->addError('gobierno', $e->getMessage());
        }
    }

    public function updatedVersionSeleccionada(): void
    {
        $this->autorizar();
        $this->seleccion();
        $this->reglaArchivo = $this->reglaRevision = null;
        $this->reglaPrueba = $this->resultadoPrueba = null;
        $this->resetPage();
    }

    public function prepararArchivo(string $id): void
    {
        $this->autorizar('gestionarReglas');
        $this->regla($id);
        $this->reglaArchivo = $id;
        $this->resetValidation();
    }

    public function prepararRevision(string $id): void
    {
        $this->autorizar('revisarReglas');
        $this->regla($id);
        $this->reglaRevision = $id;
        $this->justificacion = '';
        $this->resetValidation();
    }

    public function prepararPrueba(string $id): void
    {
        $this->autorizar('gestionarReglas');
        $this->regla($id);
        $this->reglaPrueba = $id;
        $this->entradaPrueba = [];
        $this->resultadoPrueba = null;
        $this->resetValidation();
    }

    public function updatedEntradaPrueba(): void
    {
        $this->resultadoPrueba = null;
    }

    public function probarCondiciones(): void
    {
        $actor = $this->autorizar('gestionarReglas');
        abort_unless($this->reglaPrueba, 422);
        $this->regla($this->reglaPrueba);
        $this->resultadoPrueba = null;
        $this->resetErrorBag('gobierno');
        try {
            $this->resultadoPrueba = app(GestionarReglasExpertas::class)->probar($actor, $this->reglaPrueba, $this->entradaPrueba);
        } catch (DomainException $e) {
            $this->addError('gobierno', $e->getMessage());
        }
    }

    public function agregarCondicion(): void
    {
        $this->autorizar('gestionarReglas');
        abort_if(count($this->nueva['condiciones']) >= 20, 422);
        $this->nueva['condiciones'][] = ['variable' => '', 'valor' => ''];
    }

    public function quitarCondicion(int $indice): void
    {
        $this->autorizar('gestionarReglas');
        abort_unless(count($this->nueva['condiciones']) > 1 && isset($this->nueva['condiciones'][$indice]), 422);
        unset($this->nueva['condiciones'][$indice]);
        $this->nueva['condiciones'] = array_values($this->nueva['condiciones']);
    }

    public function cancelarSeleccion(): void
    {
        $this->autorizar();
        $this->reglaArchivo = $this->reglaRevision = null;
        $this->reglaPrueba = $this->resultadoPrueba = null;
        $this->resetValidation();
    }

    public function crearSucesora(): void
    {
        $actor = $this->autorizar('gestionarReglas');
        $this->seleccion();
        $this->validate(['versionSeleccionada' => 'required|string|max:20', 'codigoVersion' => 'required|string|max:30',
            'motivoCambio' => 'required|string|min:10|max:5000', 'agregarRegla' => 'boolean']);
        try {
            $this->versionSeleccionada = app(GestionarReglasExpertas::class)->crearRevision($actor,
                $this->versionSeleccionada, $this->codigoVersion, $this->motivoCambio,
                $this->agregarRegla ? $this->nueva : [], $this->reglaArchivo ? [$this->reglaArchivo] : [],
                $this->agregarRegla ? $this->reglaArchivo : null);
            $this->reglaArchivo = $this->reglaRevision = null;
            $this->reglaPrueba = $this->resultadoPrueba = null;
            $this->codigoVersion = $this->motivoCambio = '';
            $this->agregarRegla = false;
            $this->mensaje = 'Nueva versión guardada. La revisión profesional queda pendiente; la historia anterior se conserva.';
            $this->resetPage();
        } catch (DomainException $e) {
            $this->addError('gobierno', $e->getMessage());
        }
    }

    public function guardarRevision(): void
    {
        $actor = $this->autorizar('revisarReglas');
        abort_unless($this->reglaRevision, 422);
        $this->regla($this->reglaRevision);
        $this->validate(['decision' => 'required|in:APROBADA,RECHAZADA', 'justificacion' => 'required|string|max:5000']);
        try {
            app(GestionarReglasExpertas::class)->revisar($actor, $this->reglaRevision, $this->decision, $this->justificacion);
            $this->reglaRevision = null;
            $this->mensaje = 'Decisión registrada con su cuenta, fecha y justificación.';
        } catch (DomainException $e) {
            $this->addError('gobierno', $e->getMessage());
        }
    }

    private function autorizar(string $ability = 'consultarGobierno'): User
    {
        $actor = User::query()->findOrFail(Auth::id());
        Gate::forUser($actor)->authorize($ability, VersionModeloExperto::class);

        return $actor;
    }

    private function seleccion(): ?VersionModeloExperto
    {
        return $this->versionSeleccionada === '' ? null : VersionModeloExperto::query()->whereKey($this->versionSeleccionada)->firstOrFail();
    }

    private function regla(string $id): ReglaExperta
    {
        return ReglaExperta::query()->where('cod_version_modelo', $this->versionSeleccionada)->whereKey($id)->firstOrFail();
    }

    public function render()
    {
        $actor = $this->autorizar();
        $seleccionada = $this->seleccion();
        $versiones = VersionModeloExperto::query()->orderByDesc('fecha_hora_creacion')->get();
        $reglas = ReglaExperta::query()->where('cod_version_modelo', $this->versionSeleccionada)
            ->with(['usuarioCreacion:cod_usuario,correo', 'usuarioRevision:cod_usuario,correo', 'reglaAnterior', 'reglasSucesoras'])
            ->orderBy('codigo_regla')->paginate(15);
        $tablas = $seleccionada ? app(ConocimientoVersionado::class)->instantanea($seleccionada->getKey()) : [];
        $contratoObservacional = count(array_filter($tablas['fuentes_datos_expertas'] ?? [], fn ($f) => $f['clave_adaptador'] === 'OBS_LITERAL')) > 0;
        $nombresNodos = array_column($tablas['nodos_semanticos'] ?? [], 'nombre', 'cod_nodo_semantico');
        $variablesDePrueba = array_column(array_filter($tablas['condiciones_regla_experta'] ?? [],
            fn ($c) => $c['cod_regla_experta'] === $this->reglaPrueba), 'cod_variable_experta');
        $variablesPrueba = array_filter($tablas['variables_expertas'] ?? [], fn ($v) => in_array($v['cod_variable_experta'], $variablesDePrueba, true));
        $editable = $seleccionada && $seleccionada->fecha_hora_retiro === null
            && ! preg_match('/SIM|TEST|EXPERIMENTAL/i', $seleccionada->codigo_version);
        $puedeGestionar = $editable && Gate::forUser($actor)->allows('gestionarReglas', VersionModeloExperto::class);
        $puedeRevisar = $editable && Gate::forUser($actor)->allows('revisarReglas', VersionModeloExperto::class);
        $puedeRegistrar = Gate::forUser($actor)->allows('gestionarReglas', VersionModeloExperto::class);

        return view('apoyodecision::livewire.conocimiento.reglas-panel', compact('versiones', 'reglas', 'seleccionada', 'tablas', 'nombresNodos', 'variablesPrueba', 'puedeGestionar', 'puedeRevisar', 'puedeRegistrar', 'contratoObservacional'))
            ->layout('layouts.sistema');
    }
}
