<?php

namespace App\Livewire\Cuidados;

use App\Models\PlanCuidado;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\ProgramacionCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        abort_unless(auth()->user()?->can('ejecuciones_cuidado.ver'), 403);
    }

    public function abrirCrear(?string $planId = null): void
    {
        abort_unless(auth()->user()?->can('ejecuciones_cuidado.gestionar'), 403);

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
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($plan->cod_residente, Auth::user());
            $this->codAm = $plan->cod_residente;
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
        $this->codAm = $plan?->cod_residente ?? '';
    }

    public function guardarTarea(): void
    {
        abort_unless(auth()->user()?->can('ejecuciones_cuidado.gestionar'), 403);

        $this->validate([
            'codPlan'        => 'required|exists:planes_cuidado,cod_plan',
            'codTurno'       => ['required', Rule::exists('turnos', 'cod_turno')->where('estado', 'ACTIVO')],
            'responsableId' => ['nullable', Rule::exists('usuarios', 'cod_usuario')->where('estado', 'ACTIVO')],
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
        if ($this->codAm && $this->codAm !== $plan->cod_residente) {
            $this->addError('codPlan', 'El plan de cuidados no corresponde al paciente seleccionado.');
            return;
        }
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($plan->cod_residente, 'ejecuciones_cuidado.gestionar', Auth::user());

        // ejecuciones_cuidado.cod_personal exige personal.cod_personal; nunca se
        // guarda usuarios.cod_usuario como si fuera una FK clínica.
        $responsable = $this->responsableId
            ? User::query()->with('personal')->findOrFail($this->responsableId)->personal
            : Auth::user()?->personal;
        abort_unless($responsable, 422, 'El usuario responsable no está vinculado a personal.');

        // La ejecución pertenece a una jornada concreta, no directamente a un turno.
        $jornada = Jornada::query()
            ->where('cod_turno', $this->codTurno)
            ->whereDate('fecha_jornada', $this->fechaProgramada)
            ->whereIn('estado', ['ABIERTA', 'ACTIVA'])
            ->first();
        abort_unless($jornada, 422, 'No existe una jornada activa para el turno y la fecha seleccionados.');

        // Una tarea V2 se compone de intervención, programación y ejecución.
        // Las tres filas se crean atómicamente para evitar tareas incompletas.
        DB::transaction(function () use ($plan, $responsable, $jornada): void {
            $intervencion = IntervencionCuidado::query()->create([
                'cod_intervencion' => 'INT_'.Str::upper(Str::random(10)),
                'cod_plan' => $plan->cod_plan,
                'nombre' => trim($this->titulo),
                'descripcion' => filled($this->descripcion) ? trim($this->descripcion) : trim($this->titulo),
                'objetivo_especifico' => filled($this->area) ? 'Área de cuidado: '.$this->area : null,
                'prioridad' => $this->prioridad,
                'estado' => 'ACTIVA',
            ]);

            ProgramacionCuidado::query()->create([
                'cod_programacion' => 'PRG_'.Str::upper(Str::random(10)),
                'cod_intervencion' => $intervencion->cod_intervencion,
                'cod_turno' => $this->codTurno,
                'frecuencia' => filled($this->frecuencia) ? trim($this->frecuencia) : 'UNA VEZ',
                'dias_semana' => null,
                'hora_programada' => $this->horaProgramada ?: null,
                'fecha_activacion' => $this->fechaProgramada,
                'fecha_desactivacion' => null,
                'estado' => 'ACTIVA',
            ]);

            EjecucionCuidado::query()->create([
                'cod_ejecucion' => 'EJC_'.Str::upper(Str::random(10)),
                'cod_intervencion' => $intervencion->cod_intervencion,
                'cod_residente' => $plan->cod_residente,
                'cod_jornada' => $jornada->cod_jornada,
                'cod_personal' => $responsable->cod_personal,
                'fecha_hora_programada' => $this->fechaProgramada.' '.($this->horaProgramada ?: '00:00').':00',
                'estado' => 'PENDIENTE',
            ]);
        });

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
        $this->resultandoId  = $id;
        $this->resultado     = $t->resultado ?? '';
        $this->observacion   = $t->observacion ?? '';
        $this->motivoOmision = $t->motivo_omision ?? '';
        $this->fechaProgramada = max(today()->format('Y-m-d'), $t->fecha_hora_programada?->format('Y-m-d') ?? today()->format('Y-m-d'));
        $this->horaProgramada = $t->fecha_hora_programada?->format('H:i') ?? now()->format('H:i');
        $this->estadoTarea   = 'REALIZADA';
        $this->modalResultado= true;
    }

    public function guardarResultado(): void
    {
        abort_unless(auth()->user()?->can('ejecuciones_cuidado.gestionar'), 403);

        $this->validate([
            'estadoTarea'     => 'required|in:REALIZADA,OMITIDA,REPROGRAMADA',
            'fechaProgramada' => $this->estadoTarea === 'REPROGRAMADA' ? 'required|date|after_or_equal:today' : 'nullable|date',
            'horaProgramada'  => $this->estadoTarea === 'REPROGRAMADA' ? 'required|date_format:H:i' : 'nullable|date_format:H:i',
            'resultado'       => $this->estadoTarea === 'REALIZADA' ? 'required|string|min:5|max:60' : 'nullable|string|max:60',
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

        if ($this->estadoTarea === 'OMITIDA') abort_unless(auth()->user()?->can('ejecuciones_cuidado.gestionar'), 403);
        DB::transaction(function (): void {
            // El bloqueo impide que dos enfermeros completen la misma ejecución.
            $tarea = EjecucionCuidado::lockForUpdate()->findOrFail($this->resultandoId);
            abort_unless($tarea->puedeCompletarse(), 409);
            app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria(
                $tarea->cod_residente,
                'ejecuciones_cuidado.gestionar',
                Auth::user()
            );

            if ($this->estadoTarea === 'REPROGRAMADA') {
                // Se conserva la ejecución original y se crea una nueva pendiente;
                // así no se pierde la trazabilidad clínica de la reprogramación.
                $nueva = $tarea->replicate();
                $nueva->cod_ejecucion = 'EJC_'.Str::upper(Str::random(10));
                $nueva->fecha_hora_programada = $this->fechaProgramada.' '.$this->horaProgramada.':00';
                $nueva->estado = 'PENDIENTE';
                $nueva->resultado = null;
                $nueva->observacion = null;
                $nueva->motivo_omision = null;
                $nueva->fecha_hora_ejecucion = null;
                $nueva->save();
            }

            $tarea->update([
                'estado' => $this->estadoTarea,
                'resultado' => filled($this->resultado) ? trim($this->resultado) : null,
                'observacion' => filled($this->observacion) ? trim($this->observacion) : null,
                'motivo_omision' => filled($this->motivoOmision) ? trim($this->motivoOmision) : null,
                'fecha_hora_ejecucion' => $this->estadoTarea === 'REALIZADA' ? now() : null,
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
        // Los filtros de plan, área y turno recorren relaciones V2 porque esas
        // columnas no existen directamente en ejecuciones_cuidado.
        $tareasQuery = EjecucionCuidado::query()
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto))
            ->with(['adultoMayor', 'jornada.turno', 'responsable', 'intervencion.plan']);
        $tareasQuery = $turnoService->acotarTareasQuery($tareasQuery, auth()->user(), $this->filtroTurno ?: null);

        $tareas = $tareasQuery
            ->when($this->search, fn($q) => $q->where(fn($q) =>
                $q->whereHas('intervencion', fn ($intervencion) => $intervencion->whereLike('nombre', '%'.$this->search.'%'))
                  ->orWhereHas('adultoMayor', fn($sq) =>
                      $sq->whereLike('nombres','%'.$this->search.'%')
                        ->orWhereLike('apellido_paterno','%'.$this->search.'%')
                  )
            )
            )
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroArea, fn ($q) => $q->whereHas('intervencion.plan', fn ($plan) => $plan->where('cod_area', $this->filtroArea)))
            ->when($this->filtroPlan, fn ($q) => $q->whereHas('intervencion', fn ($intervencion) => $intervencion->where('cod_plan', $this->filtroPlan)))
            ->when($this->filtroTurno, fn ($q) => $q->whereHas('jornada', fn ($jornada) => $jornada->where('cod_turno', $this->filtroTurno)))
            ->orderBy('fecha_hora_programada')
            ->paginate(15);

        $planesQuery = PlanCuidado::activos()->with('adultoMayor');
        if (!$turnoService->esSuperAdmin(auth()->user())) {
            $pacientesIds = $turnoService->obtenerPacientesAsignadosIds(auth()->user());
            $planesQuery->whereIn('cod_residente', $pacientesIds);
        }

        return view('livewire.cuidados.tareas-plan-panel', [
            'tareas'  => $tareas,
            'planes'  => $planesQuery->get(),
            'turnos'  => TurnoEnfermeria::activos()->get(),
            'usuarios'=> User::query()->leftJoin('personal', 'usuarios.cod_usuario', '=', 'personal.cod_usuario')->where('usuarios.estado', 'ACTIVO')->orderBy('personal.apellido_paterno')->select('usuarios.*')->with('personal')->get(),
        ])->layout('layouts.enfermeria');
    }
}
