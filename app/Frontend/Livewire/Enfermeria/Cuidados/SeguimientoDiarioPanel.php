<?php

namespace App\Frontend\Livewire\Enfermeria\Cuidados;

use App\Backend\Modulos\Enfermeria\Servicios\SeguimientoDiarioService;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Models\Atencion;
use App\Models\Residente;
use App\Models\TurnoEnfermeria;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SeguimientoDiarioPanel extends Component
{
    use WithPagination;

    #[Url(as: 'adulto')]
    public string $filtroAdulto = '';

    public string $search = '';

    public string $filtroTurno = '';

    public string $filtroFecha = '';

    public bool $modalForm = false;

    public ?string $editandoId = null;

    public string $codResidente = '';

    public string $codTurno = '';

    public string $fecha = '';

    public string $horaInicio = '';

    public string $estadoGeneral = '';

    public string $tipoComida = '';

    public string $alimentacion = '';

    public string $porcentajeAlimentacion = '';

    public string $tipoLiquido = '';

    public string $cantidadHidratacionMl = '';

    public string $hidratacion = '';

    public string $movilidad = '';

    public bool $intentoCaminarSolo = false;

    public string $higiene = '';

    public string $sueno = '';

    public string $orientacion = '';

    public bool $repitePreguntas = false;

    public bool $confusionObservable = false;

    public string $conducta = '';

    public string $participacion = '';

    public bool $incidente = false;

    public bool $requiereMedico = false;

    public string $observacion = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('atenciones.ver'), 403);
        $this->filtroFecha = today()->toDateString();

        $turnoActual = app(TurnoEnfermeriaService::class)
            ->obtenerTurnoActivo(Auth::user());

        if ($turnoActual) {
            $this->filtroTurno = (string) $turnoActual->cod_turno;
            $this->codTurno = (string) $turnoActual->cod_turno;
        }
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()?->can('atenciones.crear'), 403);
        $this->resetValidation();
        $this->reset([
            'editandoId', 'codResidente', 'higiene', 'orientacion',
            'conducta', 'participacion', 'observacion', 'intentoCaminarSolo',
            'repitePreguntas', 'confusionObservable', 'incidente', 'requiereMedico',
            'estadoGeneral', 'alimentacion', 'porcentajeAlimentacion', 'hidratacion',
            'movilidad', 'sueno', 'tipoComida', 'tipoLiquido', 'cantidadHidratacionMl',
        ]);
        $this->fecha = today()->format('Y-m-d');
        $this->horaInicio = now()->format('H:i');
        if ($this->filtroTurno !== '') {
            $this->codTurno = $this->filtroTurno;
        }
        if ($this->filtroAdulto !== '') {
            $this->codResidente = $this->filtroAdulto;
        }
        $this->modalForm = true;
    }

    public function abrirEditar(string $id): void
    {
        abort_unless(auth()->user()?->can('atenciones.editar'), 403);
        $seguimiento = Atencion::findOrFail($id);
        app(TurnoEnfermeriaService::class)
            ->autorizarAccionPaciente($seguimiento->cod_residente, Auth::user());

        $this->resetValidation();
        $this->editandoId = $seguimiento->cod_seg_diario;
        $this->codResidente = $seguimiento->cod_residente;
        $this->codTurno = (string) (app(TurnoEnfermeriaService::class)
            ->obtenerTurnoActivo(Auth::user())?->cod_turno ?? '');
        $this->fecha = $seguimiento->fecha->format('Y-m-d');
        $this->horaInicio = $seguimiento->fecha_hora->format('H:i');
        $datos = app(SeguimientoDiarioService::class)->datosEdicion($seguimiento);
        $this->estadoGeneral = (string) ($datos['estado_general'] ?? '');
        $this->tipoComida = (string) ($datos['tipo_comida'] ?? '');
        $this->alimentacion = (string) ($datos['alimentacion'] ?? '');
        $this->porcentajeAlimentacion = $datos['porcentaje_alimentacion'] !== null ? (string) $datos['porcentaje_alimentacion'] : '';
        $this->tipoLiquido = (string) ($datos['tipo_liquido'] ?? '');
        $this->cantidadHidratacionMl = $datos['cantidad_hidratacion_ml'] !== null ? (string) $datos['cantidad_hidratacion_ml'] : '';
        $this->hidratacion = (string) ($datos['hidratacion'] ?? '');
        $this->movilidad = (string) ($datos['movilidad'] ?? '');
        $this->intentoCaminarSolo = (bool) ($datos['intento_caminar_solo'] ?? false);
        $this->sueno = (string) ($datos['sueno'] ?? '');
        $this->orientacion = (string) ($datos['orientacion'] ?? '');
        $this->repitePreguntas = (bool) ($datos['repite_preguntas'] ?? false);
        $this->confusionObservable = (bool) ($datos['confusion_observable'] ?? false);
        $this->conducta = (string) ($datos['conducta'] ?? '');
        $this->participacion = (string) ($datos['participacion'] ?? '');
        $this->incidente = false;
        $this->requiereMedico = false;
        $this->observacion = (string) ($datos['observacion'] ?? '');
        $this->modalForm = true;
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->can($this->editandoId ? 'atenciones.editar' : 'atenciones.crear'), 403);
        abort_unless(Auth::check(), 401);
        $this->validate([
            'codResidente' => 'required|exists:residentes,cod_residente',
            'codTurno' => 'required|exists:turnos,cod_turno',
            'fecha' => 'required|date|before_or_equal:today',
            'horaInicio' => 'required|date_format:H:i',
            'estadoGeneral' => 'required|in:ESTABLE,VIGILANCIA,DELICADO,CRITICO',
            'tipoComida' => 'required|in:DESAYUNO,MEDIA_MANANA,ALMUERZO,MERIENDA,CENA,COLACION',
            'alimentacion' => 'required|in:COMPLETA,PARCIAL,RECHAZADA,AYUNO',
            'porcentajeAlimentacion' => 'required|integer|min:0|max:100',
            'tipoLiquido' => 'required|string|min:2|max:60',
            'cantidadHidratacionMl' => 'required|integer|min:1|max:10000',
            'hidratacion' => 'required|in:ADECUADA,PARCIAL,INSUFICIENTE,RECHAZADA',
            'movilidad' => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'higiene' => 'nullable|in:COMPLETA,PARCIAL,PENDIENTE,RECHAZADA',
            'sueno' => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'orientacion' => 'nullable|in:ORIENTADO,PARCIALMENTE_ORIENTADO,DESORIENTADO',
            'conducta' => 'nullable|in:TRANQUILO,ANSIOSO,AGITADO,APATICO',
            'participacion' => 'nullable|in:ACTIVA,PARCIAL,NO_PARTICIPA',
            'observacion' => 'required|string|min:10|max:10000',
        ], [
            'codResidente.required' => 'Seleccione un adulto mayor.',
            'codTurno.required' => 'Seleccione el turno.',
            'fecha.required' => 'La fecha es obligatoria.',
            'horaInicio.required' => 'La hora de inicio es obligatoria.',
            'estadoGeneral.required' => 'El estado general es obligatorio.',
            'tipoComida.required' => 'Seleccione la comida observada.',
            'alimentacion.required' => 'El registro de alimentación es obligatorio.',
            'porcentajeAlimentacion.required' => 'El porcentaje de ingesta es obligatorio (0-100%).',
            'porcentajeAlimentacion.min' => 'El porcentaje de alimentación debe ser de al menos 0%.',
            'porcentajeAlimentacion.max' => 'El porcentaje de alimentación no puede superar el 100%.',
            'tipoLiquido.required' => 'Indique el tipo de líquido administrado.',
            'cantidadHidratacionMl.required' => 'Registre la cantidad real de hidratación en mililitros.',
            'hidratacion.required' => 'La hidratación es obligatoria.',
            'movilidad.required' => 'La movilidad es obligatoria.',
            'sueno.required' => 'El patrón de sueño es obligatorio.',
            'observacion.required' => 'La nota de seguimiento es obligatoria.',
            'observacion.min' => 'La nota de seguimiento debe tener al menos 10 caracteres.',
        ]);

        $turnoVigente = app(TurnoEnfermeriaService::class)
            ->autorizarMutacionPaciente(
                $this->codResidente,
                $this->editandoId ? 'atenciones.editar' : 'atenciones.crear',
                Auth::user()
            );
        $this->codTurno = $turnoVigente->cod_turno;
        if ($this->incidente && mb_strlen(trim($this->observacion)) < 15) {
            $this->addError('observacion', 'Si reporta un incidente, detalle lo ocurrido en las observaciones con al menos 15 caracteres.');

            return;
        }

        if ($this->requiereMedico && mb_strlen(trim($this->observacion)) < 15) {
            $this->addError('observacion', 'Si requiere evaluación médica, detalle el motivo clínico en las observaciones con al menos 15 caracteres.');

            return;
        }

        // Verificar duplicado
        $duplicado = Atencion::where('cod_residente', $this->codResidente)
            ->whereDate('fecha_hora', $this->fecha)
            ->where('tipo_atencion', 'SEGUIMIENTO_DIARIO')
            ->when($this->editandoId, fn ($q) => $q->where('cod_atencion', '!=', $this->editandoId))
            ->exists();

        if ($duplicado) {
            $this->addError('codTurno', 'Ya existe un seguimiento para este adulto en este turno y fecha.');

            return;
        }

        $datos = [
            'cod_residente' => $this->codResidente,
            'fecha' => $this->fecha,
            'hora_inicio' => $this->horaInicio,
            'estado_general' => $this->estadoGeneral,
            'tipo_comida' => $this->tipoComida,
            'alimentacion' => $this->alimentacion,
            'porcentaje_alimentacion' => (int) $this->porcentajeAlimentacion,
            'tipo_liquido' => $this->tipoLiquido,
            'cantidad_hidratacion_ml' => (int) $this->cantidadHidratacionMl,
            'hidratacion' => $this->hidratacion,
            'movilidad' => $this->movilidad,
            'intento_caminar_solo' => $this->intentoCaminarSolo,
            'higiene' => $this->higiene,
            'sueno' => $this->sueno,
            'orientacion' => $this->orientacion,
            'repite_preguntas' => $this->repitePreguntas,
            'confusion_observable' => $this->confusionObservable,
            'conducta' => $this->conducta,
            'participacion' => $this->participacion,
            'incidente' => $this->incidente,
            'requiere_medico' => $this->requiereMedico,
            'observacion' => $this->observacion,
        ];

        app(SeguimientoDiarioService::class)->guardar(
            $datos,
            Auth::user(),
            $this->editandoId ? Atencion::findOrFail($this->editandoId) : null,
        );
        $msg = $this->editandoId ? 'Seguimiento corregido con trazabilidad.' : 'Seguimiento diario registrado.';

        $this->modalForm = false;
        $this->editandoId = null;
        session()->flash('mensaje', $msg);
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->editandoId = null;
        $this->resetValidation();
    }

    public function render()
    {
        $turnoService = app(TurnoEnfermeriaService::class);
        $seguimientosQuery = Atencion::query()->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto))->with(['adultoMayor', 'personal']);
        $seguimientosQuery = $turnoService->acotarSeguimientosQuery($seguimientosQuery, auth()->user(), $this->filtroTurno ?: null);

        $seguimientos = $seguimientosQuery
            ->when($this->search, fn ($q) => $q->whereHas('adultoMayor', fn ($sq) => $sq->whereLike('nombres', '%'.$this->search.'%')
                ->orWhereLike('apellido_paterno', '%'.$this->search.'%')
            )
            )
            // Turno filtrado via asignación de residente
            ->when($this->filtroFecha, fn ($q) => $q->whereDate('fecha_hora', $this->filtroFecha))
            ->orderByDesc('fecha_hora')
            ->paginate(12);

        $adultosQuery = $turnoService->obtenerPacientesAsignadosQuery(auth()->user())
            ->select('cod_residente', 'nombres', 'apellido_paterno')
            ->whereIn('estado', ['ACTIVO', 'ADMITIDO'])
            ->orderBy('apellido_paterno');

        return view('livewire.cuidados.seguimiento-diario-panel', [
            'seguimientos' => $seguimientos,
            'adultos' => $adultosQuery->get(),
            'turnos' => TurnoEnfermeria::activos()->get(),
        ])->layout('layouts.sistema');
    }
}
