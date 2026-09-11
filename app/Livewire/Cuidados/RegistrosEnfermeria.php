<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\DispositivoResidente;
use App\Models\HistorialEstadoOperativo;
use App\Models\IncidenteResidente;
use App\Models\LesionResidente;
use App\Models\RegistroCuidado;
use App\Models\SeguimientoLesion;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class RegistrosEnfermeria extends Component
{
    #[Url(as: 'adulto')]
    public string $codAm = '';
    public string $seccion = 'CUIDADOS';

    public string $tipo = 'ALIMENTACION', $subtipo = '', $nivelAyuda = '', $tolerancia = '', $resultado = '';
    public ?int $porcentaje = null, $cantidadMl = null, $cantidadDespertares = null, $dolor = null;
    public string $consistencia = '', $ayudaTecnica = '', $cambioBasal = 'SIN_CAMBIOS', $calidad = '';
    public string $estadoGeneral = 'SIN_CAMBIOS', $conciencia = '', $cognicion = '', $conducta = '', $respiracion = '';
    public ?bool $esContinente = null, $presentaDificultad = null, $presentaDolor = null, $usaDispositivo = null;
    public bool $deambulacionNocturna = false, $agitacion = false;
    public string $horaInicio = '', $horaFin = '', $motivo = '', $observacion = '';

    public string $estadoOperativo = 'EN_CENTRO', $motivoEstado = '';
    public string $tipoDispositivo = 'OXIGENO', $ubicacionDispositivo = '', $indicacionDispositivo = '';

    public string $tipoIncidente = 'CAIDA', $lugarIncidente = '', $actividadPrevia = '', $testigo = '', $descripcionIncidente = '';
    public bool $presenciado = false, $hayLesion = false, $cambioCognitivo = false, $medicoInformado = false, $familiarInformado = false, $requiereSeguimiento = true;
    public string $movilidadPosterior = '';
    public ?int $dolorIncidente = null;
    public string $tipoLesion = '', $zonaLesion = '', $lateralidad = '';
    public string $lesionId = '', $exudadoLesion = '', $pielLesion = '', $aspectoLesion = '', $accionLesion = '', $observacionLesion = '';
    public ?float $largoLesion = null, $anchoLesion = null, $profundidadLesion = null;
    public ?int $dolorLesion = null;
    public bool $lesionMedible = true;

    public function mount(): void
    {
        abort_unless(Auth::user()?->canAny(['seguimiento.ver', 'enfermeria.ver_ficha_paciente']), 403);
        if ($this->codAm) $this->autorizar($this->codAm);
    }

    private function autorizar(string $codAm): void
    {
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codAm, Auth::user());
    }

    private function turnoActual(): ?string
    {
        return app(TurnoEnfermeriaService::class)->obtenerTurnoActivo(Auth::user(), today()->toDateString())?->cod_turno;
    }

    public function updatedCodAm(string $valor): void
    {
        if ($valor) $this->autorizar($valor);
        $adulto = $valor ? AdultoMayor::find($valor) : null;
        $this->estadoOperativo = $adulto?->estado_operativo ?: 'EN_CENTRO';
    }

    public function guardarCuidado(): void
    {
        abort_unless(Auth::user()?->can('seguimiento.crear'), 403);
        $this->autorizar($this->codAm);
        $this->validate([
            'codAm' => 'required|exists:adulto_mayor,cod_am',
            'tipo' => 'required|in:ALIMENTACION,HIDRATACION,ELIMINACION,HIGIENE,MOVILIDAD,SUENO,VALORACION_RAPIDA,PROCEDIMIENTO',
            'subtipo' => 'required|string|max:50', 'porcentaje' => 'nullable|integer|in:0,25,50,75,100',
            'cantidadMl' => 'nullable|integer|min:1|max:10000', 'dolor' => 'nullable|integer|min:0|max:10',
            'nivelAyuda' => 'nullable|string|max:30', 'tolerancia' => 'nullable|string|max:30',
            'resultado' => 'nullable|string|max:30', 'motivo' => 'nullable|string|max:500',
            'observacion' => 'nullable|string|max:2000', 'horaInicio' => 'nullable|date_format:H:i',
            'horaFin' => 'nullable|date_format:H:i|after:horaInicio', 'cantidadDespertares' => 'nullable|integer|min:0|max:30',
        ], [
            'codAm.required' => 'Seleccione al residente.', 'subtipo.required' => 'Seleccione el detalle del cuidado realizado.',
            'cantidadMl.min' => 'La cantidad debe ser mayor a cero.', 'horaFin.after' => 'La hora final debe ser posterior a la hora inicial.',
        ]);

        if ($this->tipo === 'ALIMENTACION' && $this->porcentaje !== null && $this->porcentaje < config('enfermeria.porcentaje_baja_ingesta', 50) && mb_strlen(trim($this->motivo)) < 3) {
            $this->addError('motivo', 'Indique el motivo de la baja ingesta.'); return;
        }
        if ($this->tipo === 'VALORACION_RAPIDA' && $this->estadoGeneral === 'CON_CAMBIOS'
            && collect([$this->conciencia, $this->cognicion, $this->conducta, $this->respiracion, $this->dolor, $this->observacion])->filter(fn ($valor) => filled($valor))->isEmpty()) {
            $this->addError('estadoGeneral', 'Seleccione o describa al menos un cambio observado.'); return;
        }
        if (in_array($this->tipo, ['HIGIENE', 'PROCEDIMIENTO']) && in_array($this->resultado, ['PARCIAL', 'NO_REALIZADO', 'CANCELADO']) && mb_strlen(trim($this->motivo)) < 3) {
            $this->addError('motivo', 'El motivo es obligatorio cuando el cuidado no fue completado.'); return;
        }

        $registro = RegistroCuidado::create([
            'cod_am' => $this->codAm, 'cod_turno' => $this->turnoActual(), 'registrado_por' => Auth::id(),
            'tipo' => $this->tipo, 'fecha_hora_evento' => now(), 'estado' => 'FIRMADO', 'subtipo' => mb_strtoupper($this->subtipo),
            'estado_general' => $this->tipo === 'VALORACION_RAPIDA' ? $this->estadoGeneral : null,
            'conciencia' => $this->conciencia ?: null, 'cognicion' => $this->cognicion ?: null,
            'conducta' => $this->conducta ?: null, 'respiracion' => $this->respiracion ?: null,
            'porcentaje' => $this->porcentaje, 'cantidad_ml' => $this->cantidadMl, 'nivel_ayuda' => $this->nivelAyuda ?: null,
            'tolerancia' => $this->tolerancia ?: null, 'resultado' => $this->resultado ?: null, 'consistencia' => $this->consistencia ?: null,
            'es_continente' => $this->esContinente, 'presenta_dificultad' => $this->presentaDificultad, 'presenta_dolor' => $this->presentaDolor,
            'usa_dispositivo' => $this->usaDispositivo, 'ayuda_tecnica' => $this->ayudaTecnica ?: null,
            'cambio_respecto_basal' => $this->cambioBasal ?: null, 'hora_inicio' => $this->horaInicio ?: null, 'hora_fin' => $this->horaFin ?: null,
            'cantidad_despertares' => $this->cantidadDespertares, 'calidad' => $this->calidad ?: null,
            'deambulacion_nocturna' => $this->deambulacionNocturna, 'agitacion' => $this->agitacion, 'dolor' => $this->dolor,
            'motivo' => $this->motivo ?: null, 'observacion' => $this->observacion ?: null,
        ]);

        if ($this->cambioBasal === 'PEOR' || $this->estadoGeneral === 'CON_CAMBIOS' || ($this->tipo === 'ALIMENTACION' && $this->porcentaje !== null && $this->porcentaje < config('enfermeria.porcentaje_baja_ingesta', 50))) {
            AlertaAdulto::create([
                'cod_am' => $this->codAm, 'cod_turno' => $this->turnoActual(), 'origen' => 'SEGUIMIENTO',
                'tipo_alerta' => ($this->cambioBasal === 'PEOR' || $this->estadoGeneral === 'CON_CAMBIOS') ? 'CAMBIO RESPECTO AL ESTADO BASAL' : 'BAJA INGESTA',
                'nivel' => 'MEDIO', 'motivo' => '[registros_cuidados:'.$registro->getKey().'] '.($this->motivo ?: $this->observacion ?: 'Requiere seguimiento de Enfermería.'),
                'estado' => 'ABIERTA', 'responsable_id' => Auth::id(),
            ]);
        }

        $this->resetFormularioCuidado();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Cuidado registrado', 'text' => 'El registro quedó firmado y disponible en la evolución del residente.']);
    }

    private function resetFormularioCuidado(): void
    {
        $this->reset(['subtipo','nivelAyuda','tolerancia','resultado','porcentaje','cantidadMl','cantidadDespertares','dolor','consistencia','ayudaTecnica','calidad','esContinente','presentaDificultad','presentaDolor','usaDispositivo','deambulacionNocturna','agitacion','horaInicio','horaFin','motivo','observacion','conciencia','cognicion','conducta','respiracion']);
        $this->cambioBasal = 'SIN_CAMBIOS';
        $this->estadoGeneral = 'SIN_CAMBIOS';
    }

    public function cambiarEstadoOperativo(): void
    {
        abort_unless(Auth::user()?->can('seguimiento.crear'), 403);
        $this->autorizar($this->codAm);
        $this->validate(['estadoOperativo' => 'required|in:EN_CENTRO,SALIDA_TEMPORAL,HOSPITALIZADO,EGRESADO,FALLECIDO', 'motivoEstado' => 'required|string|min:5|max:1000'], [
            'motivoEstado.required' => 'Debe explicar el cambio de disponibilidad del residente.', 'motivoEstado.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);
        DB::transaction(function () {
            $adulto = AdultoMayor::lockForUpdate()->findOrFail($this->codAm);
            HistorialEstadoOperativo::create(['cod_am' => $adulto->cod_am, 'estado_anterior' => $adulto->estado_operativo, 'estado_nuevo' => $this->estadoOperativo, 'motivo' => $this->motivoEstado, 'fecha_hora' => now(), 'registrado_por' => Auth::id()]);
            $adulto->update(['estado_operativo' => $this->estadoOperativo, 'motivo_estado_operativo' => $this->motivoEstado, 'estado_operativo_desde' => now()]);
        });
        $this->motivoEstado = '';
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Estado actualizado', 'text' => 'La agenda se ajustó conservando el historial del cambio.']);
    }

    public function guardarDispositivo(): void
    {
        abort_unless(Auth::user()?->can('seguimiento.crear'), 403); $this->autorizar($this->codAm);
        $this->validate(['tipoDispositivo' => 'required|in:OXIGENO,SONDA_URINARIA,OSTOMIA,ALIMENTACION_ENTERAL,OTRO', 'ubicacionDispositivo' => 'nullable|string|max:120', 'indicacionDispositivo' => 'required|string|min:5|max:1000']);
        DispositivoResidente::create(['cod_am' => $this->codAm, 'tipo' => $this->tipoDispositivo, 'ubicacion' => $this->ubicacionDispositivo ?: null, 'fecha_colocacion' => now(), 'estado' => 'ACTIVO', 'indicacion' => $this->indicacionDispositivo, 'registrado_por' => Auth::id()]);
        $this->reset(['ubicacionDispositivo','indicacionDispositivo']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dispositivo registrado', 'text' => 'El dispositivo ya aparece en la identificación segura del residente.']);
    }

    public function retirarDispositivo(string $codigo): void
    {
        abort_unless(Auth::user()?->can('seguimiento.crear'), 403);
        $dispositivo = DispositivoResidente::where('cod_am', $this->codAm)->where('estado', 'ACTIVO')->findOrFail($codigo);
        $this->autorizar($dispositivo->cod_am);
        $dispositivo->update(['estado' => 'RETIRADO', 'fecha_retiro' => now(), 'observacion' => 'Retiro registrado por '.Auth::user()->name]);
    }

    public function guardarIncidente(): void
    {
        abort_unless(Auth::user()?->canAny(['seguimiento.crear', 'alertas.crear']), 403); $this->autorizar($this->codAm);
        $this->validate([
            'tipoIncidente' => 'required|in:CAIDA,GOLPE,ERROR_MEDICACION,LESION,CAMBIO_CLINICO,OTRO', 'lugarIncidente' => 'required|string|min:2|max:120',
            'descripcionIncidente' => 'required|string|min:10|max:3000', 'testigo' => 'required_if:presenciado,true|nullable|string|max:200',
            'dolorIncidente' => 'nullable|integer|min:0|max:10', 'tipoLesion' => 'required_if:hayLesion,true|nullable|string|max:50',
            'zonaLesion' => 'required_if:hayLesion,true|nullable|string|max:120',
        ], ['descripcionIncidente.min' => 'Describa el incidente con al menos 10 caracteres.', 'testigo.required_if' => 'Identifique al testigo del incidente.', 'zonaLesion.required_if' => 'Indique la zona de la lesión.']);

        DB::transaction(function () {
            $incidente = IncidenteResidente::create([
                'cod_am' => $this->codAm, 'cod_turno' => $this->turnoActual(), 'registrado_por' => Auth::id(), 'fecha_hora_evento' => now(),
                'tipo' => $this->tipoIncidente, 'lugar' => $this->lugarIncidente, 'actividad_previa' => $this->actividadPrevia ?: null,
                'fue_presenciado' => $this->presenciado, 'testigo' => $this->testigo ?: null, 'descripcion' => $this->descripcionIncidente,
                'dolor' => $this->dolorIncidente, 'lesion' => $this->hayLesion, 'movilidad_posterior' => $this->movilidadPosterior ?: null,
                'cambio_cognitivo' => $this->cambioCognitivo, 'medico_informado' => $this->medicoInformado,
                'familiar_informado' => $this->familiarInformado, 'requiere_seguimiento' => $this->requiereSeguimiento,
            ]);
            if ($this->hayLesion) LesionResidente::create(['cod_am' => $this->codAm, 'cod_incidente' => $incidente->cod_incidente, 'tipo' => $this->tipoLesion, 'zona_corporal' => $this->zonaLesion, 'lateralidad' => $this->lateralidad ?: null, 'fecha_deteccion' => now(), 'registrado_por' => Auth::id()]);
            AlertaAdulto::create(['cod_am' => $this->codAm, 'cod_turno' => $this->turnoActual(), 'origen' => 'INCIDENTE', 'tipo_alerta' => $this->tipoIncidente, 'nivel' => $this->tipoIncidente === 'CAIDA' ? 'ALTO' : 'MEDIO', 'motivo' => '[incidentes_residente:'.$incidente->cod_incidente.'] '.$this->descripcionIncidente, 'estado' => 'ABIERTA', 'responsable_id' => Auth::id()]);
        });
        $this->reset(['lugarIncidente','actividadPrevia','presenciado','testigo','descripcionIncidente','dolorIncidente','hayLesion','movilidadPosterior','cambioCognitivo','medicoInformado','familiarInformado','tipoLesion','zonaLesion','lateralidad']);
        $this->requiereSeguimiento = true;
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Incidente registrado', 'text' => 'Se creó la alerta y se conservaron los datos para seguimiento.']);
    }

    public function guardarSeguimientoLesion(): void
    {
        abort_unless(Auth::user()?->can('seguimiento.crear'), 403);
        $lesion = LesionResidente::where('cod_am', $this->codAm)->where('estado', 'ACTIVA')->findOrFail($this->lesionId);
        $this->autorizar($lesion->cod_am);
        $this->validate([
            'lesionId' => 'required', 'largoLesion' => 'required_if:lesionMedible,true|nullable|numeric|min:0|max:100',
            'anchoLesion' => 'required_if:lesionMedible,true|nullable|numeric|min:0|max:100',
            'profundidadLesion' => 'nullable|numeric|min:0|max:100', 'dolorLesion' => 'nullable|integer|min:0|max:10',
            'aspectoLesion' => 'required|string|min:5|max:1000', 'accionLesion' => 'required|string|min:5|max:1000',
            'observacionLesion' => 'nullable|string|max:1000',
        ], ['largoLesion.required_if' => 'Registre el largo o marque la lesión como no medible.', 'anchoLesion.required_if' => 'Registre el ancho o marque la lesión como no medible.', 'aspectoLesion.required' => 'Describa el aspecto actual de la lesión.', 'accionLesion.required' => 'Registre la curación o intervención realizada.']);

        SeguimientoLesion::create([
            'cod_lesion' => $lesion->cod_lesion, 'fecha_hora_evento' => now(), 'es_medible' => $this->lesionMedible,
            'largo_cm' => $this->lesionMedible ? $this->largoLesion : null, 'ancho_cm' => $this->lesionMedible ? $this->anchoLesion : null,
            'profundidad_cm' => $this->lesionMedible ? $this->profundidadLesion : null, 'dolor' => $this->dolorLesion,
            'exudado' => $this->exudadoLesion ?: null, 'piel_circundante' => $this->pielLesion ?: null,
            'aspecto' => $this->aspectoLesion, 'accion_realizada' => $this->accionLesion,
            'observacion' => $this->observacionLesion ?: null, 'registrado_por' => Auth::id(),
        ]);
        $this->reset(['lesionId','largoLesion','anchoLesion','profundidadLesion','dolorLesion','exudadoLesion','pielLesion','aspectoLesion','accionLesion','observacionLesion']);
        $this->lesionMedible = true;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Seguimiento registrado', 'text' => 'El control se añadió sin sobrescribir mediciones anteriores.']);
    }

    public function cerrarLesion(string $codigo): void
    {
        abort_unless(Auth::user()?->can('seguimiento.crear'), 403);
        $lesion = LesionResidente::where('cod_am', $this->codAm)->where('estado', 'ACTIVA')->findOrFail($codigo);
        $this->autorizar($lesion->cod_am);
        abort_unless($lesion->seguimientos()->exists(), 409, 'Registre al menos un seguimiento antes de cerrar la lesión.');
        $lesion->update(['estado' => 'CERRADA', 'fecha_cierre' => now()]);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Lesión cerrada', 'text' => 'La evolución histórica permanece disponible.']);
    }

    public function render()
    {
        $turnos = app(TurnoEnfermeriaService::class);
        $esSuperAdmin = $turnos->esSuperAdmin(Auth::user());
        $ids = $turnos->obtenerPacientesAsignadosIds(Auth::user());
        $pacientes = AdultoMayor::whereIn('cod_am', $ids)->orderBy('ap_paterno')->get();
        $adulto = $this->codAm ? AdultoMayor::with(['dispositivosActivos', 'registrosCuidados' => fn ($q) => $q->latest('fecha_hora_evento')->limit(30), 'incidentes' => fn ($q) => $q->latest('fecha_hora_evento')->limit(20), 'lesiones.seguimientos'])->find($this->codAm) : null;
        return view('livewire.cuidados.registros-enfermeria', compact('pacientes', 'adulto', 'esSuperAdmin'))->layout('layouts.sistema');
    }
}
