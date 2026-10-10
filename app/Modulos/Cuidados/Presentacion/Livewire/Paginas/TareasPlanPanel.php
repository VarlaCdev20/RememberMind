<?php

namespace App\Modulos\Cuidados\Presentacion\Livewire\Paginas;

use App\Models\EjecucionCuidado;
use App\Models\PlanCuidado;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Acciones\GestionarTareasPlan;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TareasPlanPanel extends Component
{
    use WithPagination;

    #[Url(as: 'adulto')]
    public string $filtroAdulto = '';

    public string $search = '';

    public string $filtroEstado = 'PENDIENTE';

    public string $filtroArea = '';

    public string $filtroPlan = '';

    public string $filtroTurno = '';

    public bool $modalForm = false;

    public bool $modalResultado = false;

    public ?string $editandoId = null;

    public ?string $resultandoId = null;

    // Crear tarea
    public string $codPlan = '';

    public string $codResidente = '';

    public string $codTurno = '';

    public string $responsableId = '';

    public string $area = '';

    public string $titulo = '';

    public string $descripcion = '';

    public string $frecuencia = '';

    public string $fechaProgramada = '';

    public string $horaProgramada = '';

    public string $prioridad = 'NORMAL';

    public string $estadoTarea = 'PENDIENTE';

    // Registrar resultado
    public string $resultado = '';

    public string $observacion = '';

    public string $motivoOmision = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('ejecuciones_cuidado.ver'), 403);
    }

    public function abrirCrear(?string $planId = null): void
    {
        abort_unless(auth()->user()?->can('ejecuciones_cuidado.gestionar'), 403);

        $this->resetValidation();
        $this->reset(
            'editandoId', 'codPlan', 'codResidente', 'codTurno', 'responsableId', 'area',
            'titulo', 'descripcion', 'frecuencia', 'fechaProgramada', 'horaProgramada',
            'resultado', 'observacion', 'motivoOmision'
        );
        $this->codPlan = $planId ? (string) $planId : '';
        $this->codResidente = $this->filtroAdulto;

        if ($this->codPlan !== '') {
            $plan = PlanCuidado::activos()->findOrFail($this->codPlan);
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($plan->cod_residente, Auth::user());
            $this->codResidente = $plan->cod_residente;
        }

        $this->codTurno = app(TurnoEnfermeriaService::class)
            ->obtenerTurnoActivo(Auth::user())?->cod_turno ?? '';
        $this->area = '';
        $this->prioridad = 'NORMAL';
        $this->estadoTarea = 'PENDIENTE';
        $this->fechaProgramada = today()->format('Y-m-d');
        $this->modalForm = true;
    }

    public function updatedCodPlan(string $planId): void
    {
        if ($planId === '') {
            $this->codResidente = $this->filtroAdulto;

            return;
        }

        $plan = PlanCuidado::activos()->find($planId);
        $this->codResidente = $plan?->cod_residente ?? '';
    }

    public function guardarTarea(): void
    {
        app(GestionarTareasPlan::class)->guardarTarea(auth()->user(), [
            'codPlan' => $this->codPlan,
            'codResidente' => $this->codResidente,
            'responsableId' => $this->responsableId,
            'codTurno' => $this->codTurno,
            'fechaProgramada' => $this->fechaProgramada,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'area' => $this->area,
            'prioridad' => $this->prioridad,
            'frecuencia' => $this->frecuencia,
            'horaProgramada' => $this->horaProgramada,
        ]);

        $this->modalForm = false;
        session()->flash('mensaje', 'Tarea creada correctamente.');
    }

    public function abrirResultado(string $id): void
    {
        abort_unless(auth()->user()?->can('ejecuciones_cuidado.gestionar'), 403);

        $t = EjecucionCuidado::findOrFail($id);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($t->cod_residente, Auth::user());
        abort_unless($t->puedeCompletarse(), 409);

        $this->resetValidation();
        $this->resultandoId = $id;
        $this->resultado = $t->resultado ?? '';
        $this->observacion = $t->observacion ?? '';
        $this->motivoOmision = $t->motivo_omision ?? '';
        $this->fechaProgramada = max(today()->format('Y-m-d'), $t->fecha_hora_programada?->format('Y-m-d') ?? today()->format('Y-m-d'));
        $this->horaProgramada = $t->fecha_hora_programada?->format('H:i') ?? now()->format('H:i');
        $this->estadoTarea = 'REALIZADA';
        $this->modalResultado = true;
    }

    public function guardarResultado(): void
    {
        app(GestionarTareasPlan::class)->guardarResultado(auth()->user(), [
            'estadoTarea' => $this->estadoTarea,
            'resultandoId' => $this->resultandoId,
            'fechaProgramada' => $this->fechaProgramada,
            'horaProgramada' => $this->horaProgramada,
            'resultado' => $this->resultado,
            'observacion' => $this->observacion,
            'motivoOmision' => $this->motivoOmision,
        ]);

        $this->modalResultado = false;
        session()->flash('mensaje', 'Resultado registrado.');
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->modalResultado = false;
        $this->resultandoId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $turnoService = app(TurnoEnfermeriaService::class);
        // Los filtros de plan, área y turno recorren relaciones V2 porque esas
        // columnas no existen directamente en ejecuciones_cuidado.
        $tareasQuery = EjecucionCuidado::query()
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto))
            ->with(['adultoMayor', 'jornada.turno', 'responsable', 'intervencion.plan']);
        $tareasQuery = $turnoService->acotarTareasQuery($tareasQuery, auth()->user(), $this->filtroTurno ?: null);

        $tareas = $tareasQuery
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->whereHas('intervencion', fn ($intervencion) => $intervencion->whereLike('nombre', '%'.$this->search.'%'))
                ->orWhereHas('adultoMayor', fn ($sq) => $sq->whereLike('nombres', '%'.$this->search.'%')
                    ->orWhereLike('apellido_paterno', '%'.$this->search.'%')
                )
            )
            )
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroArea, fn ($q) => $q->whereHas('intervencion.plan', fn ($plan) => $plan->where('cod_area', $this->filtroArea)))
            ->when($this->filtroPlan, fn ($q) => $q->whereHas('intervencion', fn ($intervencion) => $intervencion->where('cod_plan', $this->filtroPlan)))
            ->when($this->filtroTurno, fn ($q) => $q->whereHas('jornada', fn ($jornada) => $jornada->where('cod_turno', $this->filtroTurno)))
            ->orderBy('fecha_hora_programada')
            ->paginate(15);

        $planesQuery = PlanCuidado::activos()->with('adultoMayor');
        if (! $turnoService->esSuperAdmin(auth()->user())) {
            $pacientesIds = $turnoService->obtenerPacientesAsignadosIds(auth()->user());
            $planesQuery->whereIn('cod_residente', $pacientesIds);
        }

        return view('livewire.cuidados.tareas-plan-panel', [
            'tareas' => $tareas,
            'planes' => $planesQuery->get(),
            'turnos' => Turno::activos()->orderBy('orden')->get(),
            'usuarios' => User::query()->leftJoin('personal', 'usuarios.cod_usuario', '=', 'personal.cod_usuario')->where('usuarios.estado', 'ACTIVO')->orderBy('personal.apellido_paterno')->select('usuarios.*')->with('personal')->get(),
        ])->layout('layouts.enfermeria');
    }
}
