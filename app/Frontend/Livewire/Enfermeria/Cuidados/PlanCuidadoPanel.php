<?php

namespace App\Frontend\Livewire\Enfermeria\Cuidados;

use App\Models\Residente;
use App\Models\AsignacionResidenteJornada;
use App\Models\PlanCuidado;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PlanCuidadoPanel extends Component
{
    use WithPagination;
    #[\Livewire\Attributes\Url(as: 'adulto')]
    public string $filtroAdulto = '';

    public string $search        = '';
    public string $filtroEstado  = '';

    public bool   $modalForm  = false;
    public bool   $modalVer   = false;
    public ?string   $editandoId = null;
    public ?string   $viendoId   = null;

    public string $codResidente         = '';
    public string $tipoPlan      = 'INICIAL';
    public string $nivelCuidado  = 'ESTANDAR';
    public string $estadoPlan    = 'BORRADOR';
    public string $origen        = 'ADMISION';
    public string $resumen       = '';
    public string $fechaInicio   = '';
    public string $fechaFin      = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('planes_cuidado.ver'), 403);
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()?->can('planes_cuidado.crear'), 403);
        $this->resetValidation();
        $this->reset('editandoId','codResidente','tipoPlan','nivelCuidado','resumen','fechaFin');
        $this->tipoPlan   = 'INICIAL';
        $this->nivelCuidado = 'ESTANDAR';
        $this->estadoPlan = 'BORRADOR';
        $this->origen     = 'ADMISION';
        $this->fechaInicio= today()->format('Y-m-d');
        if ($this->filtroAdulto !== '') {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->filtroAdulto, Auth::user());
            $this->codResidente = $this->filtroAdulto;
        }
        $this->modalForm  = true;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->can($this->editandoId ? 'planes_cuidado.editar' : 'planes_cuidado.crear'), 403);
        if ($this->editandoId) abort_unless(PlanCuidado::findOrFail($this->editandoId)->estado === 'BORRADOR', 409);
        $this->validate([
            'codResidente'      => 'required|exists:residentes,cod_residente',
            'tipoPlan'   => 'required|in:INICIAL,AJUSTE,REEVALUACION',
            'nivelCuidado'=> 'required|in:PREVENTIVO,ESTANDAR,INTENSIVO,PALIATIVO',
            'estadoPlan' => 'required|in:BORRADOR,ACTIVO',
            'fechaInicio'=> 'required|date',
            'fechaFin' => 'nullable|date|after_or_equal:fechaInicio',
            'resumen' => 'required|string|min:5|max:10000',
        ], [
            'codResidente.required'     => 'Seleccione un adulto mayor.',
            'tipoPlan.required'  => 'Seleccione el tipo de plan.',
            'nivelCuidado.required'=> 'Seleccione el nivel de cuidado.',
            'fechaInicio.required'=> 'La fecha de inicio es obligatoria.',
        ]);
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria(
            $this->codResidente, $this->editandoId ? 'planes_cuidado.editar' : 'planes_cuidado.crear', Auth::user()
        );

        // Validar que no exista plan ACTIVO
        if ($this->estadoPlan === 'ACTIVO') {
            $activoExistente = PlanCuidado::where('cod_residente', $this->codResidente)
                ->where('estado', 'ACTIVO')->when($this->editandoId, fn ($q) => $q->where('cod_plan', '!=', $this->editandoId))->exists();
            if ($activoExistente) {
                $this->addError('estadoPlan', 'Este adulto mayor ya tiene un plan de cuidado ACTIVO. Ciérrelo antes de crear uno nuevo.');
                return;
            }
        }

        // Validar que haya asignación activa para ACTIVO
        if ($this->estadoPlan === 'ACTIVO') {
            $asignado = AsignacionResidenteJornada::where('cod_residente', $this->codResidente)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists();
            if (! $asignado) {
                $this->addError('codResidente', 'El adulto mayor debe tener una asignación de turno activa antes de activar el plan.');
                return;
            }
        }

        $personal = Auth::user()?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        if (! $personal || ! $codArea) {
            $this->addError('codResidente', 'El usuario debe tener personal y área institucional asignados.');
            return;
        }

        $datos = [
            'cod_residente' => $this->codResidente,
            'cod_area' => $codArea,
            'cod_personal' => $personal->cod_personal,
            'tipo_plan' => $this->tipoPlan,
            'nombre' => 'Plan de cuidados ' . mb_strtolower($this->tipoPlan),
            'objetivo_general' => $this->resumen,
            'prioridad' => $this->nivelCuidado,
            'fecha_hora_apertura' => $this->fechaInicio . ' 00:00:00',
            'fecha_hora_cierre' => $this->fechaFin ? $this->fechaFin . ' 23:59:59' : null,
            'estado' => $this->estadoPlan,
            'observacion' => $this->origen,
        ];

        if ($this->editandoId) {
            PlanCuidado::findOrFail($this->editandoId)->update($datos);
            $msg = 'Plan de cuidado actualizado.';
        } else {
            PlanCuidado::create($datos);
            $msg = 'Plan de cuidado creado.';
        }

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
        foreach (['codResidente'=>'cod_residente','tipoPlan'=>'tipo_plan','nivelCuidado'=>'prioridad','estadoPlan'=>'estado','origen'=>'observacion','resumen'=>'objetivo_general'] as $prop => $col) $this->$prop = $plan->$col ?? '';
        $this->fechaInicio = $plan->fecha_hora_apertura->format('Y-m-d');
        $this->fechaFin = $plan->fecha_hora_cierre?->format('Y-m-d') ?? '';
        $this->modalForm = true;
    }

    public function cerrarPlan(string $id): void
    {
        abort_unless(auth()->user()?->can('planes_cuidado.cerrar'), 403);
        $plan = PlanCuidado::findOrFail($id);
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($plan->cod_residente, 'planes_cuidado.cerrar', Auth::user());
        abort_unless($plan->estado === 'ACTIVO', 409, 'Solo puede cerrar un plan activo.');
        $plan->update([
            'estado'   => 'CERRADO',
            'fecha_hora_cierre'=> now(),
        ]);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Plan de cuidado cerrado.']);
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->modalVer  = false;
        $this->viendoId  = null;
        $this->resetValidation();
    }

    public function render()
    {
        $turnoService = app(TurnoEnfermeriaService::class);
        $pacientesIds = $turnoService->obtenerPacientesAsignadosIds(auth()->user());
        $planes = PlanCuidado::query()
            ->when(!$turnoService->esSuperAdmin(auth()->user()), fn ($q) => $q->whereIn('cod_residente', $pacientesIds))
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto))->with(['adultoMayor','creadoPor.usuario'])
            ->withCount('tareasActivas as tareas_activas_count')
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->whereLike('nombres','%'.$this->search.'%')
                      ->orWhereLike('apellido_paterno','%'.$this->search.'%')
                )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->orderByDesc('fecha_hora_apertura')
            ->paginate(12);

        return view('livewire.cuidados.plan-cuidado-panel', [
            'planes'  => $planes,
            'adultos' => Residente::query()
                ->when(!$turnoService->esSuperAdmin(auth()->user()), fn ($q) => $q->whereIn('cod_residente', $pacientesIds))
                ->where('estado', 'ADMITIDO')->orderBy('apellido_paterno')->get(),
        ])->layout('layouts.sistema');
    }
}
