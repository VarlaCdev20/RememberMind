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

        $tipoNorm = strtoupper(trim($this->tipo));

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
                    'cod_jornada' => $jornada?->cod_jornada,
                    'tipo_comida' => $this->subtipo ?: 'PRINCIPAL',
                    'porcentaje_consumido' => $this->porcentaje !== null ? (float) $this->porcentaje : null,
                    'apetito' => $this->estadoGeneral ?: null,
                    'tolerancia' => $this->tolerancia ?: null,
                    'dificultad_deglucion' => (bool) $this->presentaDificultad,
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $this->observacion ?: $this->motivo ?: 'Registro de alimentación firmado.',
                ]);
            } elseif ($tipoNorm === 'HIDRATACION') {
                RegistroHidratacion::create([
                    'cod_hidratacion' => 'HID_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada?->cod_jornada,
                    'cantidad_ml' => $this->cantidadMl ? (float) $this->cantidadMl : 0,
                    'tipo_liquido' => $this->subtipo ?: 'AGUA',
                    'tolerancia' => $this->tolerancia ?: null,
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $this->observacion ?: $this->motivo ?: 'Registro de hidratación firmado.',
                ]);
            } elseif ($tipoNorm === 'ELIMINACION') {
                RegistroEliminacion::create([
                    'cod_eliminacion' => 'ELI_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada?->cod_jornada,
                    'tipo_eliminacion' => $this->subtipo ?: 'DIURESIS',
                    'caracteristica' => $this->consistencia ?: null,
                    'continencia' => $this->esContinente ? 'CONTINENTE' : 'INCONTINENTE',
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $this->observacion ?: $this->motivo ?: 'Registro de eliminación firmado.',
                ]);
            } elseif ($tipoNorm === 'MOVILIDAD') {
                RegistroMovilidad::create([
                    'cod_movilidad' => 'MOV_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada?->cod_jornada,
                    'tipo_apoyo' => $this->nivelAyuda ?: null,
                    'dispositivo' => $this->ayudaTecnica ?: null,
                    'marcha' => $this->estadoGeneral ?: null,
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $this->observacion ?: $this->motivo ?: 'Registro de movilidad firmado.',
                ]);
            } elseif ($tipoNorm === 'SUENO') {
                RegistroSueno::create([
                    'cod_registro_sueno' => 'RSU_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada?->cod_jornada,
                    'fecha' => today()->toDateString(),
                    'despertares' => $this->cantidadDespertares !== null ? (int) $this->cantidadDespertares : 0,
                    'insomnio' => (bool) $this->agitacion,
                    'calidad' => $this->calidad ?: 'NORMAL',
                    'estado' => 'VIGENTE',
                    'observacion' => $this->observacion ?: $this->motivo ?: 'Registro de descanso/sueño firmado.',
                ]);
            } else {
                // HIGIENE, PROCEDIMIENTO y cuidados generales
                // Validar: intervencion -> plan -> plan.cod_residente == residente actual
                $queryIntervencion = IntervencionCuidado::whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])
                    ->whereHas('plan', function ($q) {
                        $q->where('cod_residente', $this->codResidente)
                            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE']);
                    });

                if (! empty($this->intervencionId)) {
                    $intervencion = (clone $queryIntervencion)->where('cod_intervencion', $this->intervencionId)->first();
                    if (! $intervencion) {
                        $intervencionOtroResidente = IntervencionCuidado::where('cod_intervencion', $this->intervencionId)->first();
                        if ($intervencionOtroResidente) {
                            abort(422, 'La intervención seleccionada no pertenece al plan de cuidados activo del residente.');
                        }
                        abort(422, 'Intervención de cuidado no válida o no activa.');
                    }
                } else {
                    $intervencion = $queryIntervencion->first();
                }

                if (! $intervencion) {
                    abort(422, 'El residente no cuenta con un plan de cuidados activo con intervenciones vigentes para este cuidado.');
                }

                $codIntervencion = $intervencion->cod_intervencion;

                EjecucionCuidado::create([
                    'cod_intervencion' => $codIntervencion,
                    'cod_ejecucion' => 'EJC_'.strtoupper(Str::random(10)),
                    'cod_residente' => $this->codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada?->cod_jornada,
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
                    'ubicacion' => 'General / Control asistencial',
                    'tipo_dolor' => 'EVALUACION_RUTINA',
                    'estado' => 'ACTIVA',
                    'observacion' => 'Registrado durante control de cuidado.',
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
            'observacion' => 'Instalación asistencial de dispositivo.',
        ]);

        $this->reset(['ubicacionDispositivo', 'indicacionDispositivo']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dispositivo registrado', 'text' => 'El dispositivo ya aparece en la identificación segura del residente.']);
    }

    public function retirarDispositivo(string $codigo): void
    {
        $this->autorizarMutacion($this->codResidente);
        $dispositivo = DispositivoClinico::where('cod_residente', $this->codResidente)->findOrFail($codigo);
        $dispositivo->update([
            'estado' => 'RETIRADO',
            'fecha_retiro' => now(),
            'observacion' => $this->motivoRetiroDispositivo ?: $dispositivo->observacion,
        ]);

        $this->motivoRetiroDispositivo = '';
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dispositivo retirado']);
    }

    public function guardarIncidente(): void
    {
        $this->autorizarMutacion($this->codResidente);
        $personal = Auth::user()?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');

        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());

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
            'cod_jornada' => $jornada?->cod_jornada,
            'tipo_incidente' => mb_strtoupper(trim($this->tipoIncidente)),
            'gravedad' => $this->dolorIncidente ? ($this->dolorIncidente >= 7 ? 'GRAVE' : ($this->dolorIncidente >= 4 ? 'MODERADA' : 'LEVE')) : 'MODERADA',
            'lugar' => $this->lugarIncidente ?: 'Habitación',
            'fecha_hora' => now(),
            'descripcion' => $this->descripcionIncidente ?: 'Incidente asistencial reportado en turno.',
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
                'ubicacion' => $this->zonaLesion ?: 'General',
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
            'descripcion' => $this->descripcionIncidente ?: 'Incidente registrado.',
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
        $incidente = Incidente::where('cod_residente', $this->codResidente)->findOrFail($this->incidenteId);
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
        $incidente = Incidente::where('cod_residente', $this->codResidente)->findOrFail($this->incidenteId);
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
        $personal = Auth::user()?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');

        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());

        CuracionHerida::create([
            'cod_curacion' => 'CUR_'.strtoupper(Str::random(10)),
            'cod_herida' => $this->lesionId,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada?->cod_jornada,
            'fecha_hora' => now(),
            'longitud' => $this->largoLesion !== null ? (float) $this->largoLesion : null,
            'ancho' => $this->anchoLesion !== null ? (float) $this->anchoLesion : null,
            'profundidad' => $this->profundidadLesion !== null ? (float) $this->profundidadLesion : null,
            'tejido' => $this->aspectoLesion ?: null,
            'exudado' => $this->exudadoLesion ?: null,
            'dolor' => $this->dolorLesion !== null ? (string) $this->dolorLesion : null,
            'procedimiento' => $this->accionLesion ?: 'Curación y desinfección protocolar',
            'observacion' => $this->observacionLesion ?: null,
        ]);

        $this->reset(['lesionId', 'largoLesion', 'anchoLesion', 'profundidadLesion', 'dolorLesion', 'exudadoLesion', 'pielLesion', 'aspectoLesion', 'accionLesion', 'observacionLesion']);
        $this->lesionMedible = true;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Curación registrada', 'text' => 'El control se añadió conservando mediciones anteriores.']);
    }

    public function cerrarLesion(string $codigo): void
    {
        $this->autorizarMutacion($this->codResidente);
        $herida = Herida::where('cod_residente', $this->codResidente)->findOrFail($codigo);
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
        $personal = Auth::user()?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');

        ValoracionDolor::create([
            'cod_valoracion_dolor' => 'VDL_'.strtoupper(Str::random(10)),
            'cod_residente' => $this->codResidente,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora' => now(),
            'intensidad' => (int) $this->intensidadDolor,
            'ubicacion' => $this->detalleDolor ?: 'General',
            'intervencion' => $this->resultadoDolor ?: null,
            'estado' => 'ACTIVA',
            'observacion' => 'Valoración secuencial de dolor.',
        ]);

        $this->reset(['valoracionDolorId', 'detalleDolor', 'resultadoDolor', 'intensidadDolor']);
        $this->faseDolor = 'VALORACION';
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dolor registrado', 'text' => 'La secuencia clínica quedó vinculada.']);
    }

    public function rectificarCuidado(): void
    {
        $this->autorizarMutacion($this->codResidente);
        $original = EjecucionCuidado::where('cod_residente', $this->codResidente)->findOrFail($this->registroRectificarId);
        $original->update([
            'observacion' => trim($original->observacion.' [Rectificación: '.$this->motivoRectificacion.']'),
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
