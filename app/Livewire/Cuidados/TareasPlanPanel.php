<?php

namespace App\Livewire\Cuidados;

use App\Models\PlanCuidado;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TareasPlanPanel extends Component
{
    use WithPagination;
    #[\Livewire\Attributes\Url(as: 'adulto')]
    public string $filtroAdulto = '';

    public string $search        = '';
    public string $filtroEstado  = 'PENDIENTE';
    public string $filtroArea    = '';
    public string $filtroPlan    = '';
    public string $filtroTurno   = '';

    public bool   $modalForm     = false;
    public bool   $modalResultado= false;
    public ?string   $editandoId    = null;
    public ?string   $resultandoId  = null;

    // Crear tarea
    public string $codPlan         = '';
    public string $codAm           = '';
    public string $codTurno        = '';
    public string $responsableId   = '';
    public string $area            = '';
    public string $titulo          = '';
    public string $descripcion     = '';
    public string $frecuencia      = '';
    public string $fechaProgramada = '';
    public string $horaProgramada  = '';
    public string $prioridad       = 'NORMAL';
    public string $estadoTarea     = 'PENDIENTE';

    // Registrar resultado
    public string $resultado       = '';
    public string $observacion     = '';
    public string $motivoOmision   = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('tareas.ver'), 403);
    }

    public function abrirCrear(?string $planId = null): void
    {
        abort_unless(auth()->user()?->can('tareas.crear'), 403);

        $this->resetValidation();
        $this->reset(
            'editandoId', 'codPlan', 'codAm', 'codTurno', 'responsableId', 'area',
            'titulo', 'descripcion', 'frecuencia', 'fechaProgramada', 'horaProgramada',
            'resultado', 'observacion', 'motivoOmision'
        );
        $this->codPlan = $planId ? (string) $planId : '';
        $this->codAm = $this->filtroAdulto;

        if ($this->codPlan !== '') {
            $plan = PlanCuidado::activos()->findOrFail($this->codPlan);
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($plan->cod_am, Auth::user());
            $this->codAm = $plan->cod_am;
        }

        $this->codTurno = app(TurnoEnfermeriaService::class)
            ->obtenerTurnoActivo(Auth::user())?->cod_turno ?? '';
        $this->area            = '';
        $this->prioridad       = 'NORMAL';
        $this->estadoTarea     = 'PENDIENTE';
        $this->fechaProgramada = today()->format('Y-m-d');
        $this->modalForm       = true;
    }

    public function updatedCodPlan(string $planId): void
    {
        if ($planId === '') {
            $this->codAm = $this->filtroAdulto;
            return;
        }

        $plan = PlanCuidado::activos()->find($planId);
        $this->codAm = $plan?->cod_am ?? '';
    }

    public function guardarTarea(): void
    {
        abort_unless(auth()->user()?->can('tareas.crear'), 403);

        $this->validate([
            'codPlan'        => 'required|exists:planes_cuidado,cod_plan',
            'codTurno'       => ['required', Rule::exists('turnos_enfermeria', 'cod_turno')->where('estado', 'ACTIVO')],
            'responsableId' => ['nullable', Rule::exists('users', 'cod_usu')->where('estado', 'ACTIVO')],
            'horaProgramada' => 'nullable|date_format:H:i',
            'area'           => 'required|in:SIGNOS,MEDICACION,MOVILIDAD,COGNITIVO,ALIMENTACION,HIDRATACION,HIGIENE,SUEÑO,SEGURIDAD,EMOCIONAL,FAMILIAR,REEVALUACION',
            'titulo'         => 'required|string|min:3|max:200',
            'descripcion'    => 'nullable|string|max:2000',
            'frecuencia'     => 'nullable|string|max:100',
            'fechaProgramada'=> 'required|date',
            'prioridad'      => 'required|in:BAJA,NORMAL,ALTA,URGENTE',
        ], [
            'codPlan.required'        => 'Seleccione un plan de cuidado.',
            'codTurno.required'       => 'Seleccione un turno.',
            'area.required'           => 'Seleccione el área de la tarea.',
            'titulo.required'         => 'El título es obligatorio.',
            'titulo.min'              => 'El título debe tener al menos 3 caracteres.',
            'fechaProgramada.required'=> 'La fecha es obligatoria.',
            'codTurno.exists'         => 'Seleccione un turno activo.',
            'responsableId.exists'    => 'Seleccione un responsable activo.',
        ]);

        $plan = PlanCuidado::findOrFail($this->codPlan);
        if ($plan->estado !== 'ACTIVO') {
            $this->addError('codPlan', 'Seleccione un plan activo.');
            return;
        }
        if ($this->codAm && $this->codAm !== $plan->cod_am) {
            $this->addError('codPlan', 'El plan de cuidados no corresponde al paciente seleccionado.');
            return;
        }
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($plan->cod_am, 'tareas.crear', Auth::user());

        TareaPlanCuidado::create([
            'cod_plan'         => $this->codPlan,
            'cod_am'           => $plan?->cod_am ?? '',
            'cod_turno'        => $this->codTurno,
            'responsable_id'   => $this->responsableId ?: null,
            'area'             => $this->area,
            'titulo'           => trim($this->titulo),
            'descripcion'      => filled($this->descripcion) ? trim($this->descripcion) : null,
            'frecuencia'       => filled($this->frecuencia) ? trim($this->frecuencia) : null,
            'fecha_programada' => $this->fechaProgramada,
            'hora_programada'  => $this->horaProgramada ? $this->horaProgramada . ':00' : null,
            'prioridad'        => $this->prioridad,
            'estado'           => 'PENDIENTE',
            'registrado_por'   => Auth::id(),
        ]);

        $this->modalForm = false;
        session()->flash('mensaje', 'Tarea creada correctamente.');
    }

    public function abrirResultado(string $id): void
    {
        abort_unless(auth()->user()?->can('tareas.registrar_resultado'), 403);

        $t = TareaPlanCuidado::findOrFail($id);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($t->cod_am, Auth::user());
        abort_unless($t->puedeCompletarse(), 409);

        $this->resetValidation();
        $this->resultandoId  = $id;
        $this->resultado     = $t->resultado ?? '';
        $this->observacion   = $t->observacion ?? '';
        $this->motivoOmision = $t->motivo_omision ?? '';
        $this->fechaProgramada = max(today()->format('Y-m-d'), $t->fecha_programada?->format('Y-m-d') ?? today()->format('Y-m-d'));
        $this->horaProgramada = $t->hora_programada ? substr($t->hora_programada, 0, 5) : now()->format('H:i');
        $this->estadoTarea   = 'REALIZADA';
        $this->modalResultado= true;
    }

    public function guardarResultado(): void
    {
        abort_unless(auth()->user()?->can('tareas.registrar_resultado'), 403);

        $this->validate([
            'estadoTarea'     => 'required|in:REALIZADA,OMITIDA,REPROGRAMADA',
            'fechaProgramada' => $this->estadoTarea === 'REPROGRAMADA' ? 'required|date|after_or_equal:today' : 'nullable|date',
            'horaProgramada'  => $this->estadoTarea === 'REPROGRAMADA' ? 'required|date_format:H:i' : 'nullable|date_format:H:i',
            'resultado'       => $this->estadoTarea === 'REALIZADA' ? 'required|string|min:5|max:2000' : 'nullable|string|max:2000',
            'observacion'     => 'nullable|string|max:2000',
            'motivoOmision'   => in_array($this->estadoTarea, ['OMITIDA', 'REPROGRAMADA']) ? 'required|string|min:5|max:1000' : 'nullable|string|max:1000',
        ], [
            'resultado.required'     => 'Registre el resultado de la tarea (mínimo 5 caracteres).',
            'resultado.min'          => 'El resultado debe tener al menos 5 caracteres.',
            'motivoOmision.required' => $this->estadoTarea === 'REPROGRAMADA' ? 'Registre el motivo de la reprogramación.' : 'Registre el motivo de omisión.',
            'motivoOmision.min'      => 'El motivo debe tener al menos 5 caracteres.',
            'horaProgramada.required'=> 'La nueva hora programada es obligatoria para reprogramar.',
            'fechaProgramada.after_or_equal' => 'La nueva fecha no puede estar en el pasado.',
        ]);

        if ($this->estadoTarea === 'OMITIDA') abort_unless(auth()->user()?->can('tareas.omitir'), 403);
        DB::transaction(function () {
        $tarea = TareaPlanCuidado::lockForUpdate()->findOrFail($this->resultandoId);
        abort_unless($tarea->puedeCompletarse(), 409);
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria(
            $tarea->cod_am,
            $this->estadoTarea === 'OMITIDA' ? 'tareas.omitir' : 'tareas.registrar_resultado',
            Auth::user()
        );
        if ($this->estadoTarea === 'REPROGRAMADA') {
            $nueva = $tarea->replicate();
            $nueva->fecha_programada = $this->fechaProgramada;
            $nueva->hora_programada = $this->horaProgramada . ':00';
            $nueva->estado = 'PENDIENTE';
            $nueva->resultado = null;
            $nueva->observacion = null;
            $nueva->motivo_omision = null;
            $nueva->fecha_realizada = null;
            $nueva->registrado_por = Auth::id();
            $nueva->save();
        }
        $tarea->update([
            'estado'         => $this->estadoTarea,
            'resultado'      => filled($this->resultado) ? trim($this->resultado) : null,
            'observacion'    => filled($this->observacion) ? trim($this->observacion) : null,
            'motivo_omision' => filled($this->motivoOmision) ? trim($this->motivoOmision) : null,
            'fecha_realizada'=> $this->estadoTarea === 'REALIZADA' ? now() : null,
            'registrado_por' => Auth::id(),
        ]);

        });
        $this->modalResultado = false;
        session()->flash('mensaje', 'Resultado registrado.');
    }

    public function cerrarModales(): void
    {
        $this->modalForm      = false;
        $this->modalResultado = false;
        $this->resultandoId   = null;
        $this->resetValidation();
    }

    public function render()
    {
        $turnoService = app(TurnoEnfermeriaService::class);
        $tareasQuery = TareaPlanCuidado::query()->when($this->filtroAdulto, fn ($q) => $q->where('cod_am', $this->filtroAdulto))->with(['adultoMayor','turno','responsable','plan']);
        $tareasQuery = $turnoService->acotarTareasQuery($tareasQuery, auth()->user(), $this->filtroTurno ?: null);

        $tareas = $tareasQuery
            ->when($this->search, fn($q) => $q->where(fn($q) =>
                $q->whereLike('titulo','%'.$this->search.'%')
                  ->orWhereHas('adultoMayor', fn($sq) =>
                      $sq->whereLike('nombres','%'.$this->search.'%')
                        ->orWhereLike('ap_paterno','%'.$this->search.'%')
                  )
            )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroArea,   fn($q) => $q->where('area',   $this->filtroArea))
            ->when($this->filtroPlan,   fn($q) => $q->where('cod_plan', $this->filtroPlan))
            ->when($this->filtroTurno,  fn($q) => $q->where('cod_turno', $this->filtroTurno))
            ->orderBy('fecha_programada')->orderBy('hora_programada')
            ->paginate(15);

        $planesQuery = PlanCuidado::activos()->with('adultoMayor');
        if (!$turnoService->esSuperAdmin(auth()->user())) {
            $pacientesIds = $turnoService->obtenerPacientesAsignadosIds(auth()->user());
            $planesQuery->whereIn('cod_am', $pacientesIds);
        }

        return view('livewire.cuidados.tareas-plan-panel', [
            'tareas'  => $tareas,
            'planes'  => $planesQuery->get(),
            'turnos'  => TurnoEnfermeria::activos()->get(),
            'usuarios'=> User::where('estado', 'ACTIVO')->orderBy('ap_paterno')->get(['cod_usu','nombres','ap_paterno']),
        ])->layout('layouts.sistema');
    }
}
