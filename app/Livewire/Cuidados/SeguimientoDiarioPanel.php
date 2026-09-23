<?php

namespace App\Livewire\Cuidados;

use App\Models\Residente;
use App\Models\PlanCuidado;
use App\Models\Atencion;
use App\Models\TurnoEnfermeria;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SeguimientoDiarioPanel extends Component
{
    use WithPagination;
    #[\Livewire\Attributes\Url(as: 'adulto')]
    public string $filtroAdulto = '';

    public string $search       = '';
    public string $filtroTurno  = '';
    public string $filtroFecha  = '';

    public bool   $modalForm = false;
    public ?string $editandoId= null;

    public string $codAm                = '';
    public string $codTurno             = '';
    public string $codPlan              = '';
    public string $fecha                = '';
    public string $horaInicio           = '';
    public string $horaFin              = '';
    public string $estadoGeneral        = '';
    public string $alimentacion         = '';
    public string $porcentajeAlimentacion = '';
    public string $hidratacion          = '';
    public string $movilidad            = '';
    public bool   $intentoCaminarSolo   = false;
    public string $higiene              = '';
    public string $sueno                = '';
    public string $orientacion          = '';
    public bool   $repitePreguntas      = false;
    public bool   $confusionObservable  = false;
    public string $conducta             = '';
    public string $participacion        = '';
    public bool   $incidente            = false;
    public bool   $requiereMedico       = false;
    public string $observacion          = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('seguimiento.ver'), 403);
        $this->filtroFecha = today()->toDateString();

        $horaActual = now()->format('H:i:s');
        $turnoActual = TurnoEnfermeria::whereTime('hora_inicio', '<=', $horaActual)
            ->whereTime('hora_cierre', '>=', $horaActual)
            ->first() ?? TurnoEnfermeria::first();

        if ($turnoActual) {
            $this->filtroTurno = (string) $turnoActual->cod_turno;
            $this->codTurno = (string) $turnoActual->cod_turno;
        }
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()?->can('seguimiento.crear'), 403);
        $this->resetValidation();
        $this->reset([
            'editandoId', 'codAm', 'codPlan', 'horaFin', 'higiene', 'orientacion',
            'conducta', 'participacion', 'observacion', 'intentoCaminarSolo',
            'repitePreguntas', 'confusionObservable', 'incidente', 'requiereMedico',
        ]);
        $this->fecha     = today()->format('Y-m-d');
        $this->horaInicio= now()->format('H:i');
        if ($this->filtroTurno !== '') {
            $this->codTurno = $this->filtroTurno;
        }
        if ($this->filtroAdulto !== '') {
            $this->codAm = $this->filtroAdulto;
        }
        $this->estadoGeneral = 'ESTABLE';
        $this->alimentacion = 'COMPLETA';
        $this->porcentajeAlimentacion = '100';
        $this->hidratacion = 'ADECUADA';
        $this->movilidad = 'INDEPENDIENTE';
        $this->sueno = 'NORMAL';
        $this->modalForm = true;
    }

    public function abrirEditar(string $id): void
    {
        abort_unless(auth()->user()?->can('seguimiento.editar'), 403);
        $seguimiento = Atencion::findOrFail($id);
        app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)
            ->autorizarAccionPaciente($seguimiento->cod_residente, Auth::user());

        $this->resetValidation();
        $this->editandoId = $seguimiento->cod_seg_diario;
        $this->codAm = $seguimiento->cod_residente;
        $this->codTurno = $seguimiento->cod_turno;
        $this->codPlan = $seguimiento->cod_plan ?? '';
        $this->fecha = $seguimiento->fecha->format('Y-m-d');
        $this->horaInicio = substr((string) $seguimiento->hora_inicio, 0, 5);
        $this->horaFin = substr((string) ($seguimiento->hora_fin ?? ''), 0, 5);
        $this->estadoGeneral = $seguimiento->estado_general ?? 'ESTABLE';
        $this->alimentacion = $seguimiento->alimentacion ?? 'COMPLETA';
        $this->porcentajeAlimentacion = (string) ($seguimiento->porcentaje_alimentacion ?? 100);
        $this->hidratacion = $seguimiento->hidratacion ?? 'ADECUADA';
        $this->movilidad = $seguimiento->movilidad ?? 'INDEPENDIENTE';
        $this->intentoCaminarSolo = (bool) $seguimiento->intento_caminar_solo;
        $this->higiene = $seguimiento->higiene ?? '';
        $this->sueno = $seguimiento->sueno ?? 'NORMAL';
        $this->orientacion = $seguimiento->orientacion ?? '';
        $this->repitePreguntas = (bool) $seguimiento->repite_preguntas;
        $this->confusionObservable = (bool) $seguimiento->confusion_observable;
        $this->conducta = $seguimiento->conducta ?? '';
        $this->participacion = $seguimiento->participacion ?? '';
        $this->incidente = (bool) $seguimiento->incidente;
        $this->requiereMedico = (bool) $seguimiento->requiere_medico;
        $this->observacion = $seguimiento->observacion ?? '';
        $this->modalForm = true;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->can($this->editandoId ? 'seguimiento.editar' : 'seguimiento.crear'), 403);
        abort_unless(Auth::check(), 401);
        $this->validate([
            'codAm'                  => 'required|exists:residentes,cod_residente',
            'codTurno'              => 'required|exists:turnos,cod_turno',
            'fecha'                 => 'required|date|before_or_equal:today',
            'codPlan'               => ['nullable', \Illuminate\Validation\Rule::exists('planes_cuidado','cod_plan')->where('cod_residente', $this->codAm)],
            'horaInicio'            => 'required|date_format:H:i',
            'horaFin'               => 'nullable|date_format:H:i|after:horaInicio',
            'estadoGeneral'         => 'required|in:ESTABLE,VIGILANCIA,DELICADO,CRITICO',
            'alimentacion'          => 'required|in:COMPLETA,PARCIAL,RECHAZADA,AYUNO',
            'porcentajeAlimentacion'=> 'required|integer|min:0|max:100',
            'hidratacion'           => 'required|in:ADECUADA,PARCIAL,INSUFICIENTE,RECHAZADA',
            'movilidad'             => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'higiene'               => 'nullable|in:COMPLETA,PARCIAL,PENDIENTE,RECHAZADA',
            'sueno'                 => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'orientacion'           => 'nullable|in:ORIENTADO,PARCIALMENTE_ORIENTADO,DESORIENTADO',
            'conducta'              => 'nullable|in:TRANQUILO,ANSIOSO,AGITADO,APATICO',
            'participacion'         => 'nullable|in:ACTIVA,PARCIAL,NO_PARTICIPA',
            'observacion'           => 'required|string|min:10|max:10000',
        ], [
            'codAm.required'                  => 'Seleccione un adulto mayor.',
            'codTurno.required'               => 'Seleccione el turno.',
            'fecha.required'                  => 'La fecha es obligatoria.',
            'horaInicio.required'             => 'La hora de inicio es obligatoria.',
            'horaFin.after'                   => 'La hora de fin debe ser posterior a la hora de inicio.',
            'estadoGeneral.required'          => 'El estado general es obligatorio.',
            'alimentacion.required'           => 'El registro de alimentación es obligatorio.',
            'porcentajeAlimentacion.required' => 'El porcentaje de ingesta es obligatorio (0-100%).',
            'porcentajeAlimentacion.min'      => 'El porcentaje de alimentación debe ser de al menos 0%.',
            'porcentajeAlimentacion.max'      => 'El porcentaje de alimentación no puede superar el 100%.',
            'hidratacion.required'            => 'La hidratación es obligatoria.',
            'movilidad.required'              => 'La movilidad es obligatoria.',
            'sueno.required'                  => 'El patrón de sueño es obligatorio.',
            'observacion.required'            => 'La nota de seguimiento es obligatoria.',
            'observacion.min'                 => 'La nota de seguimiento debe tener al menos 10 caracteres.',
        ]);

        $turnoVigente = app(\App\Services\Enfermeria\TurnoEnfermeriaService::class)
            ->autorizarMutacionPaciente(
                $this->codAm,
                $this->editandoId ? 'seguimiento.editar' : 'seguimiento.crear',
                Auth::user()
            );
        $this->codTurno = $turnoVigente->cod_turno;
        $this->fecha = today()->toDateString();
        $this->horaInicio = now()->format('H:i');

        if ($this->codPlan !== '' && !PlanCuidado::where('cod_plan', $this->codPlan)->where('cod_residente', $this->codAm)->exists()) {
            $this->addError('codPlan', 'El plan de cuidados seleccionado no pertenece al adulto mayor.');
            return;
        }

        if ($this->incidente && mb_strlen(trim($this->observacion)) < 15) {
            $this->addError('observacion', 'Si reporta un incidente, detalle lo ocurrido en las observaciones con al menos 15 caracteres.');
            return;
        }

        if ($this->requiereMedico && mb_strlen(trim($this->observacion)) < 15) {
            $this->addError('observacion', 'Si requiere evaluación médica, detalle el motivo clínico en las observaciones con al menos 15 caracteres.');
            return;
        }

        // Verificar duplicado
        $duplicado = Atencion::where('cod_residente', $this->codAm)
            ->whereDate('fecha_hora', $this->fecha)
            ->where('tipo_atencion', 'SEGUIMIENTO_DIARIO')
            ->when($this->editandoId, fn($q) => $q->where('cod_atencion', '!=', $this->editandoId))
            ->exists();

        if ($duplicado) {
            $this->addError('codTurno', 'Ya existe un seguimiento para este adulto en este turno y fecha.');
            return;
        }

        $personal = Auth::user()?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        if (! $personal || ! $codArea) {
            $this->addError('codAm', 'El usuario debe tener personal y área institucional asignados.');
            return;
        }
        $datos = [
            'cod_residente' => $this->codAm,
            'cod_area' => $codArea,
            'cod_personal' => $personal->cod_personal,
            'tipo_atencion' => 'SEGUIMIENTO_DIARIO',
            'motivo' => $this->estadoGeneral ?: 'Seguimiento diario',
            'fecha_hora' => $this->fecha . ' ' . ($this->horaInicio ?: now()->format('H:i')) . ':00',
            'estado' => 'FINALIZADA',
            'observacion' => $this->observacion,
        ];

        if ($this->editandoId) {
            Atencion::findOrFail($this->editandoId)->update($datos);
            $msg = 'Seguimiento corregido con trazabilidad.';
        } else {
            Atencion::create($datos);
            $msg = 'Seguimiento diario registrado.';
        }

        $this->modalForm = false;
        $this->editandoId = null;
        session()->flash('mensaje', $msg);
    }

    public function cerrarModales(): void
    {
        $this->modalForm  = false;
        $this->editandoId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $turnoService = app(\App\Services\Enfermeria\TurnoEnfermeriaService::class);
        $seguimientosQuery = Atencion::query()->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto))->with(['adultoMayor','personal']);
        $seguimientosQuery = $turnoService->acotarSeguimientosQuery($seguimientosQuery, auth()->user(), $this->filtroTurno ?: null);

        $seguimientos = $seguimientosQuery
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->whereLike('nombres','%'.$this->search.'%')
                      ->orWhereLike('apellido_paterno','%'.$this->search.'%')
                )
            )
            // Turno filtrado via asignación de residente
            ->when($this->filtroFecha, fn($q) => $q->whereDate('fecha_hora', $this->filtroFecha))
            ->orderByDesc('fecha_hora')
            ->paginate(12);

        $adultosQuery = $turnoService->obtenerPacientesAsignadosQuery(auth()->user())
            ->select('cod_residente', 'nombres', 'apellido_paterno')
            ->whereIn('estado', ['ACTIVO', 'ADMITIDO'])
            ->orderBy('apellido_paterno');

        return view('livewire.cuidados.seguimiento-diario-panel', [
            'seguimientos' => $seguimientos,
            'adultos'      => $adultosQuery->get(),
            'turnos'       => TurnoEnfermeria::activos()->get(),
            'planes'       => PlanCuidado::activos()->with('adultoMayor')->get(),
        ])->layout('layouts.sistema');
    }
}
