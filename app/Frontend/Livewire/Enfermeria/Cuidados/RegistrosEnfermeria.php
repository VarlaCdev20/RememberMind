<?php

namespace App\Frontend\Livewire\Enfermeria\Cuidados;

use App\Backend\Modulos\Enfermeria\Servicios\MiTurnoService;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Models\Alerta;
use App\Models\CuracionHerida;
use App\Models\DispositivoClinico;
use App\Models\EjecucionCuidado;
use App\Models\Herida;
use App\Models\Incidente;
use App\Models\IntervencionCuidado;
use App\Models\RegistroEliminacion;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\RegistroMovilidad;
use App\Models\RegistroSueno;
use App\Models\Residente;
use App\Models\ValoracionDolor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;

class RegistrosEnfermeria extends Component
{
    #[Url(as: 'adulto')]
    public string $codResidente = '';

    public string $seccion = 'CUIDADOS';

    public string $tipo = 'ALIMENTACION';

    public string $subtipo = '';

    public string $nivelAyuda = '';

    public string $tolerancia = '';

    public string $resultado = '';

    public ?int $porcentaje = null;

    public ?int $cantidadMl = null;

    public ?int $cantidadDespertares = null;

    public ?int $dolor = null;

    public string $consistencia = '';

    public string $ayudaTecnica = '';

    public string $cambioBasal = 'SIN_CAMBIOS';

    public string $calidad = '';

    public string $estadoGeneral = 'SIN_CAMBIOS';

    public string $conciencia = '';

    public string $cognicion = '';

    public string $conducta = '';

    public string $respiracion = '';

    public ?bool $esContinente = null;

    public ?bool $presentaDificultad = null;

    public ?bool $presentaDolor = null;

    public ?bool $usaDispositivo = null;

    public bool $deambulacionNocturna = false;

    public bool $agitacion = false;

    public string $horaInicio = '';

    public string $horaFin = '';

    public string $motivo = '';

    public string $observacion = '';

    public string $intervencionId = '';

    public string $tipoDispositivo = 'OXIGENO';

    public string $ubicacionDispositivo = '';

    public string $indicacionDispositivo = '';

    public string $motivoRetiroDispositivo = '';

    public string $tipoIncidente = 'CAIDA';

    public string $lugarIncidente = '';

    public string $actividadPrevia = '';

    public string $testigo = '';

    public string $descripcionIncidente = '';

    public bool $presenciado = false;

    public bool $hayLesion = false;

    public bool $cambioCognitivo = false;

    public bool $medicoInformado = false;

    public bool $familiarInformado = false;

    public bool $requiereSeguimiento = true;

    public string $movilidadPosterior = '';

    public ?int $dolorIncidente = null;

    public string $tipoLesion = '';

    public string $zonaLesion = '';

    public string $lateralidad = '';

    public string $lesionId = '';

    public string $exudadoLesion = '';

    public string $pielLesion = '';

    public string $aspectoLesion = '';

    public string $accionLesion = '';

    public string $observacionLesion = '';

    public ?float $largoLesion = null;

    public ?float $anchoLesion = null;

    public ?float $profundidadLesion = null;

    public ?int $dolorLesion = null;

    public bool $lesionMedible = true;

    public string $incidenteId = '';

    public string $seguimientoIncidente = '';

    public string $evaluacionFinalIncidente = '';

    public string $resultadoIncidente = '';

    public string $resultadoCierreLesion = '';

    public string $motivoCierreLesion = '';

    public string $faseDolor = 'VALORACION';

    public string $valoracionDolorId = '';

    public string $detalleDolor = '';

    public string $resultadoDolor = '';

    public ?int $intensidadDolor = null;

    public string $registroRectificarId = '';

    public string $motivoRectificacion = '';

    public function mount(?string $codResidente = null): void
    {
        abort_unless(Auth::user()?->canAny(['atenciones.ver', 'enfermeria.ver_ficha_paciente']), 403);
        $this->codResidente = $codResidente ?: $this->codResidente;

        if ($this->codResidente) {
            $this->autorizar($this->codResidente);
        }
    }

    private function autorizar(string $codResidente): void
    {
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codResidente, Auth::user());
    }

    private function autorizarMutacion(string $codResidente): void
    {
        app(TurnoEnfermeriaService::class)
            ->autorizarMutacionPaciente($codResidente, 'atenciones.crear', Auth::user());
    }

    private function turnoActual(): ?string
    {
        return app(TurnoEnfermeriaService::class)->obtenerTurnoActivo(Auth::user(), today()->toDateString())?->cod_turno;
    }

    public function updatedCodAm(string $valor): void
    {
        $this->codResidente = $valor;
        if ($valor) {
            $this->autorizar($valor);
        }
        $this->intervencionId = '';
    }

    public function updatedCodResidente(string $valor): void
    {
        if ($valor) {
            $this->autorizar($valor);
        }
        $this->intervencionId = '';
    }

    public function guardarCuidado(): void
    {
        $this->resetValidation();
        $this->autorizarMutacion($this->codResidente);

        $user = Auth::user();
        $personal = $user?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');

        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());
        if (! $jornada) {
            throw ValidationException::withMessages([
                'jornada' => 'No existe una jornada activa asignada al personal clínico.',
            ]);
        }

        $tipoNorm = strtoupper(trim($this->tipo));

        $reglas = [
            'tipo' => 'required|in:ALIMENTACION,HIDRATACION,ELIMINACION,HIGIENE,MOVILIDAD,SUENO,VALORACION_RAPIDA,PROCEDIMIENTO',
            'subtipo' => 'required|string|max:60',
            'porcentaje' => 'nullable|integer|in:0,25,50,75,100',
            'cantidadMl' => 'required_if:tipo,HIDRATACION|nullable|integer|min:1|max:10000',
            'cantidadDespertares' => 'nullable|integer|min:0|max:30',
            'dolor' => 'nullable|integer|min:0|max:10',
            'motivo' => 'nullable|string|max:2000',
            'observacion' => 'nullable|string|max:5000',
            'intervencionId' => in_array($tipoNorm, ['ALIMENTACION', 'HIDRATACION', 'ELIMINACION', 'MOVILIDAD', 'SUENO'], true)
                ? 'nullable|string|max:20'
                : 'required|string|max:20',
        ];
        $this->validate($reglas, [
            'subtipo.required' => 'Describa el cuidado realmente realizado.',
            'cantidadMl.required_if' => 'Registre la cantidad real administrada en mililitros.',
            'intervencionId.required' => 'Seleccione la intervención vigente del plan de cuidados.',
        ]);

        if ($tipoNorm === 'ALIMENTACION' && $this->porcentaje !== null && $this->porcentaje < 50 && empty(trim($this->motivo))) {
            $this->addError('motivo', 'Indique el motivo de la baja ingesta.');

            return;
        }

        DB::transaction(function () use ($personal, $jornada, $tipoNorm) {
            if ($tipoNorm === 'ALIMENTACION') {
                RegistroIngesta::create([
                    'cod_ingesta' => 'ING_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada->cod_jornada,
                    'tipo_comida' => $this->subtipo,
                    'porcentaje_consumido' => $this->porcentaje !== null ? (float) $this->porcentaje : null,
                    'apetito' => $this->estadoGeneral ?: null,
                    'tolerancia' => $this->tolerancia ?: null,
                    'dificultad_deglucion' => (bool) $this->presentaDificultad,
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $this->observacion ?: $this->motivo ?: null,
                ]);
            } elseif ($tipoNorm === 'HIDRATACION') {
                RegistroHidratacion::create([
                    'cod_hidratacion' => 'HID_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada->cod_jornada,
                    'cantidad_ml' => (float) $this->cantidadMl,
                    'tipo_liquido' => $this->subtipo,
                    'tolerancia' => $this->tolerancia ?: null,
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $this->observacion ?: $this->motivo ?: null,
                ]);
            } elseif ($tipoNorm === 'ELIMINACION') {
                RegistroEliminacion::create([
                    'cod_eliminacion' => 'ELI_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada->cod_jornada,
                    'tipo_eliminacion' => $this->subtipo,
                    'caracteristica' => $this->consistencia ?: null,
                    'continencia' => $this->esContinente === null ? null : ($this->esContinente ? 'CONTINENTE' : 'INCONTINENTE'),
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $this->observacion ?: $this->motivo ?: null,
                ]);
            } elseif ($tipoNorm === 'MOVILIDAD') {
                RegistroMovilidad::create([
                    'cod_movilidad' => 'MOV_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada->cod_jornada,
                    'tipo_apoyo' => $this->nivelAyuda ?: null,
                    'dispositivo' => $this->ayudaTecnica ?: null,
                    'marcha' => $this->estadoGeneral ?: null,
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $this->observacion ?: $this->motivo ?: null,
                ]);
            } elseif ($tipoNorm === 'SUENO') {
                RegistroSueno::create([
                    'cod_registro_sueno' => 'RSU_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada->cod_jornada,
                    'fecha' => today()->toDateString(),
                    'despertares' => $this->cantidadDespertares !== null ? (int) $this->cantidadDespertares : null,
                    'insomnio' => (bool) $this->agitacion,
                    'calidad' => $this->calidad ?: null,
                    'estado' => 'VIGENTE',
                    'observacion' => $this->observacion ?: $this->motivo ?: null,
                ]);
            } else {
                // HIGIENE, PROCEDIMIENTO y cuidados generales
                // Validar: intervencion -> plan -> plan.cod_residente == residente actual
                $queryIntervencion = IntervencionCuidado::whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])
                    ->whereHas('plan', function ($q) {
                        $q->where('cod_residente', $this->codResidente)
                            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE']);
                    });

                $intervencion = $queryIntervencion->where('cod_intervencion', $this->intervencionId)->first();
                if (! $intervencion) {
                    $intervencionOtroResidente = IntervencionCuidado::where('cod_intervencion', $this->intervencionId)->first();
                    if ($intervencionOtroResidente) {
                        abort(422, 'La intervención seleccionada no pertenece al plan de cuidados activo del residente.');
                    }
                    abort(422, 'Intervención de cuidado no válida o no activa.');
                }

                $codIntervencion = $intervencion->cod_intervencion;

                EjecucionCuidado::create([
                    'cod_intervencion' => $codIntervencion,
                    'cod_ejecucion' => 'EJC_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada->cod_jornada,
                    'fecha_hora_programada' => now(),
                    'fecha_hora_ejecucion' => now(),
                    'resultado' => $this->resultado ?: 'REALIZADA',
                    'estado' => 'REALIZADA',
                    'observacion' => trim(($this->subtipo ? $this->subtipo.': ' : '').($this->observacion ?: $this->motivo ?: 'Cuidado firmado en turno.')),
                ]);
            }

            if ($this->dolor !== null) {
                ValoracionDolor::create([
                    'cod_valoracion_dolor' => 'VDL_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'fecha_hora' => now(),
                    'intensidad' => (int) $this->dolor,
                    'ubicacion' => null,
                    'tipo_dolor' => null,
                    'estado' => 'ACTIVA',
                    'observacion' => null,
                ]);
            }

            if ($this->cambioBasal === 'PEOR' || ($tipoNorm === 'ALIMENTACION' && $this->porcentaje !== null && $this->porcentaje < 50)) {
                Alerta::create([
                    'cod_alerta' => 'ALE_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal_responsable' => $personal->cod_personal,
                    'origen' => 'CUIDADO',
                    'tipo' => $this->cambioBasal === 'PEOR' ? 'CAMBIO RESPECTO AL ESTADO BASAL' : 'BAJA INGESTA',
                    'prioridad' => 'MEDIA',
                    'titulo' => 'Alerta clínica generada desde cuidado',
                    'descripcion' => $this->motivo ?: 'Alerta automática por registro de cuidado.',
                    'fecha_hora' => now(),
                    'generacion' => 'AUTOMATICA',
                    'estado' => 'ABIERTA',
                ]);
            }
        });

        $this->resetFormularioCuidado();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Cuidado registrado', 'text' => 'El registro quedó firmado y disponible en la evolución del residente.']);
    }

    private function resetFormularioCuidado(): void
    {
        $this->reset(['intervencionId', 'subtipo', 'nivelAyuda', 'tolerancia', 'resultado', 'porcentaje', 'cantidadMl', 'cantidadDespertares', 'dolor', 'consistencia', 'ayudaTecnica', 'calidad', 'esContinente', 'presentaDificultad', 'presentaDolor', 'usaDispositivo', 'deambulacionNocturna', 'agitacion', 'horaInicio', 'horaFin', 'motivo', 'observacion', 'conciencia', 'cognicion', 'conducta', 'respiracion']);
        $this->cambioBasal = 'SIN_CAMBIOS';
        $this->estadoGeneral = 'SIN_CAMBIOS';
    }

    public function guardarDispositivo(): void
    {
        $this->autorizarMutacion($this->codResidente);
        $this->validate([
            'tipoDispositivo' => 'required|in:OXIGENO,SONDA_URINARIA,OSTOMIA,ALIMENTACION_ENTERAL,OTRO,SONDA_VESICAL',
            'ubicacionDispositivo' => 'nullable|string|max:120',
            'indicacionDispositivo' => 'required|string|min:5|max:1000',
        ], [
            'indicacionDispositivo.required' => 'Registre la indicación clínica del dispositivo.',
        ]);
        $personal = Auth::user()?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');

        DispositivoClinico::create([
            'cod_dispositivo' => 'DIS_'.strtoupper(Str::random(10)),
            'cod_residente' => $this->codResidente,
            'cod_personal' => $personal->cod_personal,
            'tipo' => $this->tipoDispositivo,
            'ubicacion' => $this->ubicacionDispositivo ?: null,
            'descripcion' => $this->indicacionDispositivo ?: null,
            'fecha_colocacion' => now(),
            'estado' => 'ACTIVO',
            'observacion' => null,
        ]);

        $this->reset(['ubicacionDispositivo', 'indicacionDispositivo']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dispositivo registrado', 'text' => 'El dispositivo ya aparece en la identificación segura del residente.']);
    }

    public function retirarDispositivo(string $codigo): void
    {
        $this->autorizarMutacion($this->codResidente);
        $this->validate([
            'motivoRetiroDispositivo' => 'required|string|min:5|max:1000',
        ], [
            'motivoRetiroDispositivo.required' => 'El motivo del retiro es obligatorio.',
        ]);
        $dispositivo = DispositivoClinico::where('cod_residente', $this->codResidente)->findOrFail($codigo);
        abort_unless($dispositivo->estado === 'ACTIVO', 409, 'El dispositivo ya fue retirado.');
        $dispositivo->update([
            'estado' => 'RETIRADO',
            'fecha_retiro' => now(),
            'observacion' => trim(($dispositivo->observacion ? $dispositivo->observacion.' | ' : '').'Retiro: '.$this->motivoRetiroDispositivo),
        ]);

        $this->motivoRetiroDispositivo = '';
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dispositivo retirado']);
    }

    public function guardarIncidente(): void
    {
        $this->autorizarMutacion($this->codResidente);
        $this->validate([
            'tipoIncidente' => 'required|in:CAIDA,GOLPE,ERROR_MEDICACION,LESION,CAMBIO_CLINICO,OTRO',
            'lugarIncidente' => 'required|string|max:120',
            'actividadPrevia' => 'nullable|string|max:500',
            'testigo' => 'required_if:presenciado,true|nullable|string|max:120',
            'descripcionIncidente' => 'required|string|min:10|max:2000',
            'dolorIncidente' => 'nullable|integer|min:0|max:10',
            'tipoLesion' => 'required_if:hayLesion,true|nullable|string|max:60',
            'zonaLesion' => 'required_if:hayLesion,true|nullable|string|max:120',
        ]);
        $personal = Auth::user()?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');

        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());
        abort_unless($jornada, 409, 'No existe una jornada profesional activa para firmar el incidente.');

        $medida = $this->movilidadPosterior ? 'Movilidad: '.$this->movilidadPosterior : null;
        $obs = [];
        if ($this->dolorIncidente !== null) {
            $obs[] = 'Dolor: '.$this->dolorIncidente.'/10';
        }
        if ($this->hayLesion) {
            $obs[] = 'Lesión evidente';
        }
        if ($this->presenciado && $this->testigo) {
            $obs[] = 'Testigo: '.$this->testigo;
        }
        if ($this->familiarInformado) {
            $obs[] = 'Familiar informado';
        }
        $observacionTexto = ! empty($obs) ? implode('. ', $obs) : null;

        Incidente::create([
            'cod_incidente' => 'INC_'.strtoupper(Str::random(10)),
            'cod_residente' => $this->codResidente,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'tipo_incidente' => mb_strtoupper(trim($this->tipoIncidente)),
            'gravedad' => $this->dolorIncidente === null ? null : ($this->dolorIncidente >= 7 ? 'GRAVE' : ($this->dolorIncidente >= 4 ? 'MODERADA' : 'LEVE')),
            'lugar' => $this->lugarIncidente,
            'fecha_hora' => now(),
            'descripcion' => $this->descripcionIncidente,
            'medida_inmediata' => $medida,
            'requiere_medico' => (bool) $this->medicoInformado,
            'requiere_derivacion' => false,
            'estado' => $this->requiereSeguimiento ? 'EN_SEGUIMIENTO' : 'ABIERTO',
            'observacion' => $observacionTexto,
        ]);

        if ($this->hayLesion && $this->tipoLesion) {
            Herida::create([
                'cod_herida' => 'HER_'.strtoupper(Str::random(10)),
                'cod_residente' => $this->codResidente,
                'cod_personal' => $personal->cod_personal,
                'tipo_herida' => mb_strtoupper(trim($this->tipoLesion)),
                'ubicacion' => $this->zonaLesion,
                'causa' => 'Incidente: '.mb_strtoupper(trim($this->tipoIncidente)),
                'fecha_hora_identificacion' => now(),
                'estado' => 'ACTIVA',
            ]);
        }

        Alerta::create([
            'cod_alerta' => 'ALE_'.strtoupper(Str::random(10)),
            'cod_residente' => $this->codResidente,
            'cod_personal_responsable' => $personal->cod_personal,
            'origen' => 'INCIDENTE',
            'tipo' => mb_strtoupper(trim($this->tipoIncidente)),
            'generacion' => 'AUTOMATICA',
            'prioridad' => mb_strtoupper(trim($this->tipoIncidente)) === 'CAIDA' ? 'ALTA' : 'MEDIA',
            'titulo' => 'Incidente reportado: '.mb_strtoupper(trim($this->tipoIncidente)),
            'descripcion' => $this->descripcionIncidente,
            'fecha_hora' => now(),
            'estado' => 'ABIERTA',
        ]);

        $this->reset(['lugarIncidente', 'actividadPrevia', 'presenciado', 'testigo', 'descripcionIncidente', 'dolorIncidente', 'hayLesion', 'movilidadPosterior', 'cambioCognitivo', 'medicoInformado', 'familiarInformado', 'tipoLesion', 'zonaLesion', 'lateralidad']);
        $this->requiereSeguimiento = true;
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Incidente registrado', 'text' => 'El incidente quedó registrado en el expediente V2 del residente.']);
    }

    public function seguimientoIncidente(): void
    {
        $this->autorizarMutacion($this->codResidente);
        $this->validate([
            'incidenteId' => 'required|string|max:20',
            'seguimientoIncidente' => 'required|string|min:10|max:2000',
        ]);
        $incidente = Incidente::where('cod_residente', $this->codResidente)
            ->whereIn('estado', ['ABIERTO', 'EN_SEGUIMIENTO'])
            ->findOrFail($this->incidenteId);
        $incidente->update([
            'estado' => 'EN_SEGUIMIENTO',
            'observacion' => trim($incidente->observacion."\n".'['.now()->format('d/m/Y H:i').'] '.$this->seguimientoIncidente),
        ]);
        $this->reset(['incidenteId', 'seguimientoIncidente']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Seguimiento registrado']);
    }

    public function cerrarIncidente(): void
    {
        $this->autorizarMutacion($this->codResidente);
        $this->validate([
            'incidenteId' => 'required|string|max:20',
            'evaluacionFinalIncidente' => 'required|string|min:10|max:2000',
            'resultadoIncidente' => 'required|string|min:5|max:1000',
        ]);
        $incidente = Incidente::where('cod_residente', $this->codResidente)
            ->whereIn('estado', ['ABIERTO', 'EN_SEGUIMIENTO'])
            ->findOrFail($this->incidenteId);
        $incidente->update([
            'estado' => 'CERRADO',
            'observacion' => trim($incidente->observacion."\n[Cierre: ".$this->evaluacionFinalIncidente.' - '.$this->resultadoIncidente.']'),
        ]);
        $this->reset(['incidenteId', 'evaluacionFinalIncidente', 'resultadoIncidente']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Incidente cerrado']);
    }

    public function guardarSeguimientoLesion(): void
    {
        $this->autorizarMutacion($this->codResidente);
        $this->validate([
            'lesionId' => 'required|string|max:20',
            'largoLesion' => 'nullable|numeric|min:0|max:99999',
            'anchoLesion' => 'nullable|numeric|min:0|max:99999',
            'profundidadLesion' => 'nullable|numeric|min:0|max:99999',
            'dolorLesion' => 'nullable|integer|min:0|max:10',
            'aspectoLesion' => 'required|string|min:5|max:1000',
            'accionLesion' => 'required|string|min:5|max:2000',
            'observacionLesion' => 'nullable|string|max:2000',
        ]);
        $personal = Auth::user()?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');

        $herida = Herida::query()
            ->where('cod_residente', $this->codResidente)
            ->where('estado', 'ACTIVA')
            ->findOrFail($this->lesionId);

        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());
        abort_unless($jornada, 409, 'No existe una jornada profesional activa para firmar la curación.');

        CuracionHerida::create([
            'cod_curacion' => 'CUR_'.strtoupper(Str::random(10)),
            'cod_herida' => $herida->cod_herida,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'fecha_hora' => now(),
            'longitud' => $this->largoLesion !== null ? (float) $this->largoLesion : null,
            'ancho' => $this->anchoLesion !== null ? (float) $this->anchoLesion : null,
            'profundidad' => $this->profundidadLesion !== null ? (float) $this->profundidadLesion : null,
            'tejido' => $this->aspectoLesion ?: null,
            'exudado' => $this->exudadoLesion ?: null,
            'dolor' => $this->dolorLesion !== null ? (string) $this->dolorLesion : null,
            'procedimiento' => $this->accionLesion,
            'observacion' => $this->observacionLesion ?: null,
        ]);

        $this->reset(['lesionId', 'largoLesion', 'anchoLesion', 'profundidadLesion', 'dolorLesion', 'exudadoLesion', 'pielLesion', 'aspectoLesion', 'accionLesion', 'observacionLesion']);
        $this->lesionMedible = true;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Curación registrada', 'text' => 'El control se añadió conservando mediciones anteriores.']);
    }

    public function cerrarLesion(string $codigo): void
    {
        $this->autorizarMutacion($this->codResidente);
        $this->validate([
            'resultadoCierreLesion' => 'required|string|min:5|max:1000',
            'motivoCierreLesion' => 'required|string|min:5|max:1000',
        ]);
        $herida = Herida::where('cod_residente', $this->codResidente)
            ->where('estado', 'ACTIVA')
            ->findOrFail($codigo);
        $herida->update([
            'estado' => 'CERRADA',
            'fecha_hora_cierre' => now(),
            'observacion' => trim($herida->observacion."\n[Cierre: ".$this->resultadoCierreLesion.' - '.$this->motivoCierreLesion.']'),
        ]);

        $this->reset(['resultadoCierreLesion', 'motivoCierreLesion']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Herida cerrada', 'text' => 'La evolución histórica permanece disponible.']);
    }

    public function guardarDolor(): void
    {
        $this->autorizarMutacion($this->codResidente);
        $this->validate([
            'faseDolor' => 'required|in:VALORACION,INTERVENCION,REEVALUACION',
            'intensidadDolor' => 'required|integer|min:0|max:10',
            'detalleDolor' => 'required|string|min:5|max:2000',
            'resultadoDolor' => 'required_if:faseDolor,REEVALUACION|nullable|string|min:3|max:1000',
        ]);
        $personal = Auth::user()?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');

        ValoracionDolor::create([
            'cod_valoracion_dolor' => 'VDL_'.strtoupper(Str::random(10)),
            'cod_residente' => $this->codResidente,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora' => now(),
            'intensidad' => (int) $this->intensidadDolor,
            'ubicacion' => $this->detalleDolor,
            'intervencion' => $this->resultadoDolor ?: null,
            'estado' => 'ACTIVA',
            'observacion' => null,
        ]);

        $this->reset(['valoracionDolorId', 'detalleDolor', 'resultadoDolor', 'intensidadDolor']);
        $this->faseDolor = 'VALORACION';
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dolor registrado', 'text' => 'La secuencia clínica quedó vinculada.']);
    }

    public function rectificarCuidado(): void
    {
        $this->autorizarMutacion($this->codResidente);
        $this->validate([
            'registroRectificarId' => 'required|string|max:20',
            'motivoRectificacion' => 'required|string|min:10|max:2000',
        ]);
        $original = EjecucionCuidado::where('cod_residente', $this->codResidente)->findOrFail($this->registroRectificarId);
        $original->update([
            'observacion' => trim($original->observacion.' [Rectificación '.now()->format('d/m/Y H:i').' por '.Auth::user()->personal->cod_personal.': '.$this->motivoRectificacion.']'),
        ]);

        $this->reset(['registroRectificarId', 'motivoRectificacion']);
        $this->resetFormularioCuidado();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Rectificación registrada', 'text' => 'El original permanece visible con la anotación de rectificación.']);
    }

    public function render()
    {
        $turnos = app(TurnoEnfermeriaService::class);
        $esSuperAdmin = $turnos->esSuperAdmin(Auth::user());
        $ids = $turnos->obtenerPacientesAsignadosIds(Auth::user());
        $pacientes = Residente::whereIn('cod_residente', $ids)->orderBy('apellido_paterno')->get();
        $adulto = $this->codResidente ? Residente::with([
            'dispositivosActivos',
            'heridas.curaciones',
            'ejecucionesCuidado' => fn ($q) => $q->latest('fecha_hora_programada')->limit(30),
            'incidentesClinicos' => fn ($q) => $q->latest('fecha_hora')->limit(20),
        ])->find($this->codResidente) : null;

        $intervencionesActivas = $this->codResidente ? IntervencionCuidado::whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
            ->whereHas('plan', fn ($q) => $q->where('cod_residente', $this->codResidente)->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE']))
            ->get() : collect();

        return view('livewire.cuidados.registros-enfermeria', compact('pacientes', 'adulto', 'esSuperAdmin', 'intervencionesActivas'))
            ->layout('layouts.enfermeria');
    }
}
