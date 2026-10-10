<?php

namespace App\Frontend\Livewire\Enfermeria\Cuidados;

use App\Backend\Modulos\Enfermeria\Servicios\SeguimientoDiarioService;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Models\Atencion;
use App\Models\Residente;
use App\Models\Turno;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SeguimientoDiarioPanel extends Component
{
    use WithPagination;

    #[Url(as: 'adulto')]
    public string $filtroAdulto = '';

    #[Url]
    public string $cuidado = '';

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

    public string $toleranciaIngesta = '';

    public bool $dificultadDeglucion = false;

    public string $tipoLiquido = '';

    public string $cantidadHidratacionMl = '';

    public string $hidratacion = '';

    public string $movilidad = '';

    public string $equilibrio = '';

    public string $traslado = '';

    public string $tipoApoyo = '';

    public string $dispositivo = '';

    public string $fatiga = '';

    public string $riesgoCaida = '';

    public bool $intentoCaminarSolo = false;

    public string $higiene = '';

    public string $sueno = '';

    public string $horasSueno = '';

    public string $despertares = '';

    public bool $agitacionNocturna = false;

    public string $orientacion = '';

    public string $orientacionLugar = '';

    public string $orientacionTiempo = '';

    public string $memoriaReciente = '';

    public string $memoriaRemota = '';

    public string $atencionCognitiva = '';

    public string $comprension = '';

    public string $lenguaje = '';

    public bool $sigueInstrucciones = false;

    public bool $repitePreguntas = false;

    public bool $olvidaIndicaciones = false;

    public bool $reconocePersonas = false;

    public bool $reconoceEntorno = false;

    public bool $confusionObservable = false;

    public bool $cambioCognitivo = false;

    public string $conducta = '';

    public bool $apatia = false;

    public bool $agitacion = false;

    public bool $agresividad = false;

    public bool $ansiedad = false;

    public bool $aislamiento = false;

    public bool $deambulacion = false;

    public bool $cambioConducta = false;

    public string $intervencionConducta = '';

    public string $respuestaConducta = '';

    public string $participacion = '';

    public string $tipoEliminacion = '';

    public string $cantidadEliminacion = '';

    public string $caracteristicaEliminacion = '';

    public string $continencia = '';

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
            'toleranciaIngesta', 'dificultadDeglucion', 'movilidad', 'equilibrio',
            'traslado', 'tipoApoyo', 'dispositivo', 'fatiga', 'riesgoCaida',
            'sueno', 'horasSueno', 'despertares', 'agitacionNocturna',
            'tipoComida', 'tipoLiquido', 'cantidadHidratacionMl', 'orientacionLugar',
            'orientacionTiempo', 'memoriaReciente', 'memoriaRemota', 'atencionCognitiva',
            'comprension', 'lenguaje', 'sigueInstrucciones', 'olvidaIndicaciones',
            'reconocePersonas', 'reconoceEntorno', 'cambioCognitivo', 'apatia',
            'agitacion', 'agresividad', 'ansiedad', 'aislamiento', 'deambulacion',
            'cambioConducta', 'intervencionConducta', 'respuestaConducta',
            'tipoEliminacion', 'cantidadEliminacion', 'caracteristicaEliminacion', 'continencia',
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
        $this->toleranciaIngesta = (string) ($datos['tolerancia_ingesta'] ?? '');
        $this->dificultadDeglucion = (bool) ($datos['dificultad_deglucion'] ?? false);
        $this->tipoLiquido = (string) ($datos['tipo_liquido'] ?? '');
        $this->cantidadHidratacionMl = $datos['cantidad_hidratacion_ml'] !== null ? (string) $datos['cantidad_hidratacion_ml'] : '';
        $this->hidratacion = (string) ($datos['hidratacion'] ?? '');
        $this->movilidad = (string) ($datos['movilidad'] ?? '');
        $this->equilibrio = (string) ($datos['equilibrio'] ?? '');
        $this->traslado = (string) ($datos['traslado'] ?? '');
        $this->tipoApoyo = (string) ($datos['tipo_apoyo'] ?? '');
        $this->dispositivo = (string) ($datos['dispositivo'] ?? '');
        $this->fatiga = (string) ($datos['fatiga'] ?? '');
        $this->riesgoCaida = (string) ($datos['riesgo_caida'] ?? '');
        $this->intentoCaminarSolo = (bool) ($datos['intento_caminar_solo'] ?? false);
        $this->sueno = (string) ($datos['sueno'] ?? '');
        $this->horasSueno = $datos['horas_sueno'] !== null ? (string) $datos['horas_sueno'] : '';
        $this->despertares = $datos['despertares'] !== null ? (string) $datos['despertares'] : '';
        $this->agitacionNocturna = (bool) ($datos['agitacion_nocturna'] ?? false);
        $this->orientacion = (string) ($datos['orientacion'] ?? '');
        $this->orientacionLugar = (string) ($datos['orientacion_lugar'] ?? '');
        $this->orientacionTiempo = (string) ($datos['orientacion_tiempo'] ?? '');
        $this->memoriaReciente = (string) ($datos['memoria_reciente'] ?? '');
        $this->memoriaRemota = (string) ($datos['memoria_remota'] ?? '');
        $this->atencionCognitiva = (string) ($datos['atencion_cognitiva'] ?? '');
        $this->comprension = (string) ($datos['comprension'] ?? '');
        $this->lenguaje = (string) ($datos['lenguaje'] ?? '');
        $this->sigueInstrucciones = (bool) ($datos['sigue_instrucciones'] ?? false);
        $this->repitePreguntas = (bool) ($datos['repite_preguntas'] ?? false);
        $this->olvidaIndicaciones = (bool) ($datos['olvida_indicaciones'] ?? false);
        $this->reconocePersonas = (bool) ($datos['reconoce_personas'] ?? false);
        $this->reconoceEntorno = (bool) ($datos['reconoce_entorno'] ?? false);
        $this->confusionObservable = (bool) ($datos['confusion_observable'] ?? false);
        $this->cambioCognitivo = (bool) ($datos['cambio_cognitivo'] ?? false);
        $this->conducta = (string) ($datos['conducta'] ?? '');
        $this->apatia = (bool) ($datos['apatia'] ?? false);
        $this->agitacion = (bool) ($datos['agitacion'] ?? false);
        $this->agresividad = (bool) ($datos['agresividad'] ?? false);
        $this->ansiedad = (bool) ($datos['ansiedad'] ?? false);
        $this->aislamiento = (bool) ($datos['aislamiento'] ?? false);
        $this->deambulacion = (bool) ($datos['deambulacion'] ?? false);
        $this->cambioConducta = (bool) ($datos['cambio_conducta'] ?? false);
        $this->intervencionConducta = (string) ($datos['intervencion_conducta'] ?? '');
        $this->respuestaConducta = (string) ($datos['respuesta_conducta'] ?? '');
        $this->participacion = (string) ($datos['participacion'] ?? '');
        $this->tipoEliminacion = (string) ($datos['tipo_eliminacion'] ?? '');
        $this->cantidadEliminacion = (string) ($datos['cantidad_eliminacion'] ?? '');
        $this->caracteristicaEliminacion = (string) ($datos['caracteristica_eliminacion'] ?? '');
        $this->continencia = (string) ($datos['continencia'] ?? '');
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
            'toleranciaIngesta' => 'nullable|in:BUENA,REGULAR,MALA,NAUSEAS,VOMITO',
            'tipoLiquido' => 'required|string|min:2|max:60',
            'cantidadHidratacionMl' => 'required|integer|min:1|max:10000',
            'hidratacion' => 'required|in:ADECUADA,PARCIAL,INSUFICIENTE,RECHAZADA',
            'movilidad' => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'equilibrio' => 'nullable|in:ESTABLE,INESTABLE,NO_VALORABLE',
            'traslado' => 'nullable|in:INDEPENDIENTE,SUPERVISION,AYUDA_UNA_PERSONA,AYUDA_DOS_PERSONAS,GRUA',
            'tipoApoyo' => 'nullable|string|max:60',
            'dispositivo' => 'nullable|string|max:80',
            'fatiga' => 'nullable|in:SIN_FATIGA,LEVE,MODERADA,SEVERA',
            'riesgoCaida' => 'nullable|in:BAJO,MEDIO,ALTO',
            'higiene' => 'nullable|in:COMPLETA,PARCIAL,PENDIENTE,RECHAZADA',
            'sueno' => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'horasSueno' => 'nullable|numeric|min:0|max:24',
            'despertares' => 'nullable|integer|min:0|max:30',
            'orientacion' => 'nullable|in:ORIENTADO,PARCIALMENTE_ORIENTADO,DESORIENTADO',
            'orientacionLugar' => 'nullable|in:ORIENTADO,PARCIALMENTE_ORIENTADO,DESORIENTADO',
            'orientacionTiempo' => 'nullable|in:ORIENTADO,PARCIALMENTE_ORIENTADO,DESORIENTADO',
            'memoriaReciente' => 'nullable|in:CONSERVADA,ALTERACION_LEVE,ALTERADA,NO_VALORABLE',
            'memoriaRemota' => 'nullable|in:CONSERVADA,ALTERACION_LEVE,ALTERADA,NO_VALORABLE',
            'atencionCognitiva' => 'nullable|in:CONSERVADA,FLUCTUANTE,DISMINUIDA,NO_VALORABLE',
            'comprension' => 'nullable|in:CONSERVADA,PARCIAL,ALTERADA,NO_VALORABLE',
            'lenguaje' => 'nullable|in:CONSERVADO,LIMITADO,ALTERADO,NO_VALORABLE',
            'conducta' => 'nullable|in:TRANQUILO,ANSIOSO,AGITADO,APATICO',
            'participacion' => 'nullable|in:ACTIVA,PARCIAL,NO_PARTICIPA',
            'intervencionConducta' => 'nullable|string|max:2000',
            'respuestaConducta' => 'nullable|string|max:2000',
            'tipoEliminacion' => 'nullable|in:URINARIA,INTESTINAL,AMBAS',
            'cantidadEliminacion' => 'nullable|string|max:40',
            'caracteristicaEliminacion' => 'nullable|string|max:120',
            'continencia' => 'nullable|in:CONTINENTE,INCONTINENCIA_URINARIA,INCONTINENCIA_FECAL,DOBLE_INCONTINENCIA',
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
            'horasSueno.max' => 'Las horas de sueño no pueden superar 24.',
            'despertares.max' => 'Revise la cantidad de despertares; el máximo admitido es 30.',
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
            'tolerancia_ingesta' => $this->toleranciaIngesta,
            'dificultad_deglucion' => $this->dificultadDeglucion,
            'tipo_liquido' => $this->tipoLiquido,
            'cantidad_hidratacion_ml' => (int) $this->cantidadHidratacionMl,
            'hidratacion' => $this->hidratacion,
            'movilidad' => $this->movilidad,
            'equilibrio' => $this->equilibrio,
            'traslado' => $this->traslado,
            'tipo_apoyo' => $this->tipoApoyo,
            'dispositivo' => $this->dispositivo,
            'fatiga' => $this->fatiga,
            'riesgo_caida' => $this->riesgoCaida,
            'intento_caminar_solo' => $this->intentoCaminarSolo,
            'higiene' => $this->higiene,
            'sueno' => $this->sueno,
            'horas_sueno' => $this->horasSueno,
            'despertares' => $this->despertares,
            'agitacion_nocturna' => $this->agitacionNocturna,
            'orientacion' => $this->orientacion,
            'orientacion_lugar' => $this->orientacionLugar,
            'orientacion_tiempo' => $this->orientacionTiempo,
            'memoria_reciente' => $this->memoriaReciente,
            'memoria_remota' => $this->memoriaRemota,
            'atencion_cognitiva' => $this->atencionCognitiva,
            'comprension' => $this->comprension,
            'lenguaje' => $this->lenguaje,
            'sigue_instrucciones' => $this->sigueInstrucciones,
            'repite_preguntas' => $this->repitePreguntas,
            'olvida_indicaciones' => $this->olvidaIndicaciones,
            'reconoce_personas' => $this->reconocePersonas,
            'reconoce_entorno' => $this->reconoceEntorno,
            'confusion_observable' => $this->confusionObservable,
            'cambio_cognitivo' => $this->cambioCognitivo,
            'conducta' => $this->conducta,
            'apatia' => $this->apatia,
            'agitacion' => $this->agitacion,
            'agresividad' => $this->agresividad,
            'ansiedad' => $this->ansiedad,
            'aislamiento' => $this->aislamiento,
            'deambulacion' => $this->deambulacion,
            'cambio_conducta' => $this->cambioConducta,
            'intervencion_conducta' => $this->intervencionConducta,
            'respuesta_conducta' => $this->respuestaConducta,
            'participacion' => $this->participacion,
            'tipo_eliminacion' => $this->tipoEliminacion,
            'cantidad_eliminacion' => $this->cantidadEliminacion,
            'caracteristica_eliminacion' => $this->caracteristicaEliminacion,
            'continencia' => $this->continencia,
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

    public function limpiarErroresCaptura(): void
    {
        abort_unless(auth()->user()?->estado === 'ACTIVO', 403);
        $this->resetValidation();
    }

    public function cerrarModales(): void
    {
        $this->modalForm = false;
        $this->editandoId = null;
        $this->resetValidation();
    }

    public function render()
    {
        if ($this->cuidado !== '') {
            $opcion = \App\Backend\Modulos\Enfermeria\Servicios\NavegacionCuidadosService::opcion($this->cuidado);
            abort_unless(in_array($this->cuidado, ['cognicion', 'conducta'], true), 404);
            abort_unless(Auth::user()?->can($opcion['permission']), 403);
        }
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
            'turnos' => Turno::activos()->orderBy('orden')->get(),
        ])->layout('layouts.enfermeria');
    }
}
