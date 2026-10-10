<?php

namespace App\Modulos\Cuidados\Presentacion\Livewire\Paginas;

use App\Models\PlanCuidado;
use App\Models\Residente;
use App\Modulos\Cuidados\Aplicacion\Acciones\GestionarPlanesCuidado;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PlanCuidadoPanel extends Component
{
    use WithPagination;

    #[Url(as: 'adulto')]
    public string $filtroAdulto = '';

    public string $search = '';

    public string $filtroEstado = '';

    public bool $modalForm = false;

    public bool $modalVer = false;

    public ?string $editandoId = null;

    public ?string $viendoId = null;

    public string $codResidente = '';

    public string $tipoPlan = 'INICIAL';

    public string $nivelCuidado = 'ESTANDAR';

    public string $estadoPlan = 'BORRADOR';

    public string $origen = 'ADMISION';

    public string $resumen = '';

    public string $fechaInicio = '';

    public string $fechaFin = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('planes_cuidado.ver'), 403);
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()?->can('planes_cuidado.crear'), 403);
        $this->resetValidation();
        $this->reset('editandoId', 'codResidente', 'tipoPlan', 'nivelCuidado', 'resumen', 'fechaFin');
        $this->tipoPlan = 'INICIAL';
        $this->nivelCuidado = 'ESTANDAR';
        $this->estadoPlan = 'BORRADOR';
        $this->origen = 'ADMISION';
        $this->fechaInicio = today()->format('Y-m-d');
        if ($this->filtroAdulto !== '') {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->filtroAdulto, Auth::user());
            $this->codResidente = $this->filtroAdulto;
        }
        $this->modalForm = true;
    }

    public function guardar(): void
    {
        $msg = app(GestionarPlanesCuidado::class)->guardar(auth()->user(), [
            'editandoId' => $this->editandoId,
            'codResidente' => $this->codResidente,
            'estadoPlan' => $this->estadoPlan,
            'tipoPlan' => $this->tipoPlan,
            'resumen' => $this->resumen,
            'nivelCuidado' => $this->nivelCuidado,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'origen' => $this->origen,
        ]);

        $this->modalForm = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => $msg]);
    }

    public function editarBorrador(string $id): void
    {
        abort_unless(auth()->user()?->can('planes_cuidado.editar'), 403);
        $plan = PlanCuidado::findOrFail($id);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($plan->cod_residente, Auth::user());
        abort_unless($plan->estado === 'BORRADOR', 409);
        $this->editandoId = $id;
        foreach (['codResidente' => 'cod_residente', 'tipoPlan' => 'tipo_plan', 'nivelCuidado' => 'prioridad', 'estadoPlan' => 'estado', 'origen' => 'observacion', 'resumen' => 'objetivo_general'] as $prop => $col) {
            $this->$prop = $plan->$col ?? '';
        }
        $this->fechaInicio = $plan->fecha_hora_apertura->format('Y-m-d');
        $this->fechaFin = $plan->fecha_hora_cierre?->format('Y-m-d') ?? '';
        $this->modalForm = true;
    }

    public function cerrarPlan(string $id): void
    {
        app(GestionarPlanesCuidado::class)->cerrarPlan(auth()->user(), [

        ], $id);

        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Plan de cuidado cerrado.']);
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->modalVer = false;
        $this->viendoId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $turnoService = app(TurnoEnfermeriaService::class);
        $pacientesIds = $turnoService->obtenerPacientesAsignadosIds(auth()->user());
        $planes = PlanCuidado::query()
            ->when(! $turnoService->esSuperAdmin(auth()->user()), fn ($q) => $q->whereIn('cod_residente', $pacientesIds))
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto))->with(['adultoMayor', 'creadoPor.usuario'])
            ->withCount('tareasActivas as tareas_activas_count')
            ->when($this->search, fn ($q) => $q->whereHas('adultoMayor', fn ($sq) => $sq->whereLike('nombres', '%'.$this->search.'%')
                ->orWhereLike('apellido_paterno', '%'.$this->search.'%')
            )
            )
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->orderByDesc('fecha_hora_apertura')
            ->paginate(12);

        return view('livewire.cuidados.plan-cuidado-panel', [
            'planes' => $planes,
            'adultos' => Residente::query()
                ->when(! $turnoService->esSuperAdmin(auth()->user()), fn ($q) => $q->whereIn('cod_residente', $pacientesIds))
                ->where('estado', 'ADMITIDO')->orderBy('apellido_paterno')->get(),
        ])->layout('layouts.sistema');
    }
}
