<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\PlanCuidado;
use App\Services\Enfermeria\TurnoEnfermeriaService;
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

    public string $codAm         = '';
    public string $tipoPlan      = 'INICIAL';
    public string $nivelCuidado  = 'ESTANDAR';
    public string $estadoPlan    = 'BORRADOR';
    public string $origen        = 'ADMISION';
    public string $resumen       = '';
    public string $fechaInicio   = '';
    public string $fechaFin      = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('plan_cuidado.ver'), 403);
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()?->can('plan_cuidado.crear'), 403);
        $this->resetValidation();
        $this->reset('editandoId','codAm','tipoPlan','nivelCuidado','resumen','fechaFin');
        $this->tipoPlan   = 'INICIAL';
        $this->nivelCuidado = 'ESTANDAR';
        $this->estadoPlan = 'BORRADOR';
        $this->origen     = 'ADMISION';
        $this->fechaInicio= today()->format('Y-m-d');
        if ($this->filtroAdulto !== '') {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->filtroAdulto, Auth::user());
            $this->codAm = $this->filtroAdulto;
        }
        $this->modalForm  = true;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->can($this->editandoId ? 'plan_cuidado.editar' : 'plan_cuidado.crear'), 403);
        if ($this->editandoId) abort_unless(PlanCuidado::findOrFail($this->editandoId)->estado === 'BORRADOR', 409);
        $this->validate([
            'codAm'      => 'required|exists:adulto_mayor,cod_am',
            'tipoPlan'   => 'required|in:INICIAL,AJUSTE,REEVALUACION',
            'nivelCuidado'=> 'required|in:PREVENTIVO,ESTANDAR,INTENSIVO,PALIATIVO',
            'estadoPlan' => 'required|in:BORRADOR,ACTIVO',
            'fechaInicio'=> 'required|date',
            'fechaFin' => 'nullable|date|after_or_equal:fechaInicio',
            'resumen' => 'required|string|min:5|max:10000',
        ], [
            'codAm.required'     => 'Seleccione un adulto mayor.',
            'tipoPlan.required'  => 'Seleccione el tipo de plan.',
            'nivelCuidado.required'=> 'Seleccione el nivel de cuidado.',
            'fechaInicio.required'=> 'La fecha de inicio es obligatoria.',
        ]);
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria(
            $this->codAm, $this->editandoId ? 'plan_cuidado.editar' : 'plan_cuidado.crear', Auth::user()
        );

        // Validar que no exista plan ACTIVO
        if ($this->estadoPlan === 'ACTIVO') {
            $activoExistente = PlanCuidado::where('cod_am', $this->codAm)
                ->where('estado', 'ACTIVO')->when($this->editandoId, fn ($q) => $q->where('cod_plan', '!=', $this->editandoId))->exists();
            if ($activoExistente) {
                $this->addError('estadoPlan', 'Este adulto mayor ya tiene un plan de cuidado ACTIVO. Ciérrelo antes de crear uno nuevo.');
                return;
            }
        }

        // Validar que haya asignación activa para ACTIVO
        if ($this->estadoPlan === 'ACTIVO') {
            $asignado = AsignacionTurnoAdulto::where('cod_am', $this->codAm)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists();
            if (! $asignado) {
                $this->addError('codAm', 'El adulto mayor debe tener una asignación de turno activa antes de activar el plan.');
                return;
            }
        }

        // Calcular versión
        $version = $this->editandoId ? PlanCuidado::findOrFail($this->editandoId)->version : (PlanCuidado::where('cod_am', $this->codAm)->max('version') ?? 0) + 1;

        $datos = [
            'cod_am'       => $this->codAm,
            'tipo_plan'    => $this->tipoPlan,
            'version'      => $version,
            'nivel_cuidado'=> $this->nivelCuidado,
            'estado'       => $this->estadoPlan,
            'origen'       => $this->origen,
            'resumen'      => $this->resumen ?: null,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin'    => $this->fechaFin ?: null,
            'creado_por'   => Auth::id(),
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
        abort_unless(auth()->user()?->can('plan_cuidado.editar'), 403);
        $plan = PlanCuidado::findOrFail($id);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($plan->cod_am, Auth::user());
        abort_unless($plan->estado === 'BORRADOR', 409);
        $this->editandoId = $id;
        foreach (['codAm'=>'cod_am','tipoPlan'=>'tipo_plan','nivelCuidado'=>'nivel_cuidado','estadoPlan'=>'estado','origen'=>'origen','resumen'=>'resumen'] as $prop => $col) $this->$prop = $plan->$col ?? '';
        $this->fechaInicio = $plan->fecha_inicio->format('Y-m-d');
        $this->fechaFin = $plan->fecha_fin?->format('Y-m-d') ?? '';
        $this->modalForm = true;
    }

    public function cerrarPlan(string $id): void
    {
        abort_unless(auth()->user()?->can('plan_cuidado.cerrar'), 403);
        $plan = PlanCuidado::findOrFail($id);
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($plan->cod_am, 'plan_cuidado.cerrar', Auth::user());
        abort_unless($plan->estado === 'ACTIVO', 409, 'Solo puede cerrar un plan activo.');
        $plan->update([
            'estado'   => 'CERRADO',
            'fecha_fin'=> today()->toDateString(),
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
            ->when(!$turnoService->esSuperAdmin(auth()->user()), fn ($q) => $q->whereIn('cod_am', $pacientesIds))
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_am', $this->filtroAdulto))->with(['adultoMayor','creadoPor'])
            ->withCount('tareasActivas as tareas_activas_count')
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->whereLike('nombres','%'.$this->search.'%')
                      ->orWhereLike('ap_paterno','%'.$this->search.'%')
                )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->orderByDesc('fecha_inicio')
            ->paginate(12);

        return view('livewire.cuidados.plan-cuidado-panel', [
            'planes'  => $planes,
            'adultos' => AdultoMayor::select('cod_am','nombres','ap_paterno','ap_materno')
                ->when(!$turnoService->esSuperAdmin(auth()->user()), fn ($q) => $q->whereIn('cod_am', $pacientesIds))
                ->whereNull('archivado_en')->orderBy('ap_paterno')->get(),
        ])->layout('layouts.sistema');
    }
}
