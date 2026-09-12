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
use App\Services\Enfermeria\CuidadosEnfermeriaService;
use App\Services\Enfermeria\IncidentesEnfermeriaService;
use App\Services\Enfermeria\LesionesEnfermeriaService;
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
    public string $motivoRetiroDispositivo = '';

    public string $tipoIncidente = 'CAIDA', $lugarIncidente = '', $actividadPrevia = '', $testigo = '', $descripcionIncidente = '';
    public bool $presenciado = false, $hayLesion = false, $cambioCognitivo = false, $medicoInformado = false, $familiarInformado = false, $requiereSeguimiento = true;
    public string $movilidadPosterior = '';
    public ?int $dolorIncidente = null;
    public string $tipoLesion = '', $zonaLesion = '', $lateralidad = '';
    public string $lesionId = '', $exudadoLesion = '', $pielLesion = '', $aspectoLesion = '', $accionLesion = '', $observacionLesion = '';
    public ?float $largoLesion = null, $anchoLesion = null, $profundidadLesion = null;
    public ?int $dolorLesion = null;
    public bool $lesionMedible = true;
    public string $incidenteId = '', $seguimientoIncidente = '', $evaluacionFinalIncidente = '', $resultadoIncidente = '';
    public string $resultadoCierreLesion = '', $motivoCierreLesion = '';
    public string $faseDolor = 'VALORACION', $valoracionDolorId = '', $detalleDolor = '', $resultadoDolor = '';
    public ?int $intensidadDolor = null;
    public string $registroRectificarId = '', $motivoRectificacion = '';

    public function mount(): void
    {
        abort_unless(Auth::user()?->canAny(['seguimiento.ver', 'enfermeria.ver_ficha_paciente']), 403);
        if ($this->codAm) $this->autorizar($this->codAm);
    }

    private function autorizar(string $codAm): void
    {
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codAm, Auth::user());
    }

    private function autorizarMutacion(string $codAm): void
    {
        app(TurnoEnfermeriaService::class)
            ->autorizarMutacionPaciente($codAm, 'seguimiento.crear', Auth::user());
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
        $this->resetValidation();
        app(CuidadosEnfermeriaService::class)->registrar($this->codAm, $this->datosCuidado(), Auth::user());
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
        $this->autorizarMutacion($this->codAm);
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
        app(CuidadosEnfermeriaService::class)->colocarDispositivo($this->codAm, [
            'tipo' => $this->tipoDispositivo,
            'ubicacion' => $this->ubicacionDispositivo,
            'indicacion' => $this->indicacionDispositivo,
        ], Auth::user());
        $this->reset(['ubicacionDispositivo','indicacionDispositivo']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dispositivo registrado', 'text' => 'El dispositivo ya aparece en la identificación segura del residente.']);
    }

    public function retirarDispositivo(string $codigo): void
    {
        $dispositivo = DispositivoResidente::where('cod_am', $this->codAm)->findOrFail($codigo);
        app(CuidadosEnfermeriaService::class)->retirarDispositivo($dispositivo, $this->motivoRetiroDispositivo, Auth::user());
        $this->motivoRetiroDispositivo = '';
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dispositivo retirado']);
    }

    public function guardarIncidente(): void
    {
        app(IncidentesEnfermeriaService::class)->registrar($this->codAm, [
            'tipo' => $this->tipoIncidente, 'lugar' => $this->lugarIncidente, 'actividad_previa' => $this->actividadPrevia,
            'fue_presenciado' => $this->presenciado, 'testigo' => $this->testigo, 'descripcion' => $this->descripcionIncidente,
            'dolor' => $this->dolorIncidente, 'lesion' => $this->hayLesion, 'movilidad_posterior' => $this->movilidadPosterior,
            'cambio_cognitivo' => $this->cambioCognitivo, 'medico_informado' => $this->medicoInformado,
            'familiar_informado' => $this->familiarInformado, 'requiere_seguimiento' => $this->requiereSeguimiento,
            'tipo_lesion' => $this->tipoLesion, 'zona_lesion' => $this->zonaLesion, 'lateralidad' => $this->lateralidad,
        ], Auth::user());
        $this->reset(['lugarIncidente','actividadPrevia','presenciado','testigo','descripcionIncidente','dolorIncidente','hayLesion','movilidadPosterior','cambioCognitivo','medicoInformado','familiarInformado','tipoLesion','zonaLesion','lateralidad']);
        $this->requiereSeguimiento = true;
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Incidente registrado', 'text' => 'Se creó la alerta y se conservaron los datos para seguimiento.']);
    }

    public function guardarSeguimientoLesion(): void
    {
        $lesion = LesionResidente::where('cod_am', $this->codAm)->findOrFail($this->lesionId);
        app(LesionesEnfermeriaService::class)->seguimiento($lesion, [
            'es_medible' => $this->lesionMedible, 'largo_cm' => $this->largoLesion, 'ancho_cm' => $this->anchoLesion,
            'profundidad_cm' => $this->profundidadLesion, 'dolor' => $this->dolorLesion, 'exudado' => $this->exudadoLesion,
            'piel_circundante' => $this->pielLesion, 'aspecto' => $this->aspectoLesion,
            'accion_realizada' => $this->accionLesion, 'observacion' => $this->observacionLesion,
        ], Auth::user());
        $this->reset(['lesionId','largoLesion','anchoLesion','profundidadLesion','dolorLesion','exudadoLesion','pielLesion','aspectoLesion','accionLesion','observacionLesion']);
        $this->lesionMedible = true;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Seguimiento registrado', 'text' => 'El control se añadió sin sobrescribir mediciones anteriores.']);
    }

    public function cerrarLesion(string $codigo): void
    {
        $lesion = LesionResidente::where('cod_am', $this->codAm)->findOrFail($codigo);
        app(LesionesEnfermeriaService::class)->cerrar($lesion, $this->resultadoCierreLesion, $this->motivoCierreLesion, Auth::user());
        $this->reset(['resultadoCierreLesion','motivoCierreLesion']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Lesión cerrada', 'text' => 'La evolución histórica permanece disponible.']);
    }

    public function guardarDolor(): void
    {
        $valoracion = $this->valoracionDolorId ? RegistroCuidado::where('cod_am', $this->codAm)->findOrFail($this->valoracionDolorId) : null;
        app(CuidadosEnfermeriaService::class)->registrarDolor(
            $this->codAm, $this->faseDolor, (int) $this->intensidadDolor, $this->detalleDolor,
            Auth::user(), $valoracion, $this->resultadoDolor ?: null,
        );
        $this->reset(['valoracionDolorId','detalleDolor','resultadoDolor','intensidadDolor']);
        $this->faseDolor = 'VALORACION';
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dolor registrado', 'text' => 'La secuencia clínica quedó vinculada.']);
    }

    public function rectificarCuidado(): void
    {
        $original = RegistroCuidado::where('cod_am', $this->codAm)->findOrFail($this->registroRectificarId);
        app(CuidadosEnfermeriaService::class)->rectificar($original, $this->datosCuidado(), $this->motivoRectificacion, Auth::user());
        $this->reset(['registroRectificarId','motivoRectificacion']);
        $this->resetFormularioCuidado();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Rectificación registrada', 'text' => 'El original permanece visible y sin modificaciones.']);
    }

    public function seguimientoIncidente(): void
    {
        $incidente = IncidenteResidente::where('cod_am', $this->codAm)->findOrFail($this->incidenteId);
        app(IncidentesEnfermeriaService::class)->registrarSeguimiento($incidente, $this->seguimientoIncidente, Auth::user());
        $this->reset(['incidenteId','seguimientoIncidente']);
    }

    public function cerrarIncidente(): void
    {
        $incidente = IncidenteResidente::where('cod_am', $this->codAm)->findOrFail($this->incidenteId);
        app(IncidentesEnfermeriaService::class)->cerrar($incidente, $this->evaluacionFinalIncidente, $this->resultadoIncidente, Auth::user());
        $this->reset(['incidenteId','evaluacionFinalIncidente','resultadoIncidente']);
    }

    private function datosCuidado(): array
    {
        return [
            'tipo' => $this->tipo, 'subtipo' => mb_strtoupper($this->subtipo), 'porcentaje' => $this->porcentaje,
            'cantidad_ml' => $this->cantidadMl, 'nivel_ayuda' => $this->nivelAyuda, 'tolerancia' => $this->tolerancia,
            'resultado' => $this->resultado, 'consistencia' => $this->consistencia,
            'es_continente' => $this->esContinente, 'presenta_dificultad' => $this->presentaDificultad,
            'presenta_dolor' => $this->presentaDolor, 'usa_dispositivo' => $this->usaDispositivo,
            'ayuda_tecnica' => $this->ayudaTecnica, 'cambio_respecto_basal' => $this->cambioBasal,
            'hora_inicio' => $this->horaInicio, 'hora_fin' => $this->horaFin, 'cantidad_despertares' => $this->cantidadDespertares,
            'calidad' => $this->calidad, 'deambulacion_nocturna' => $this->deambulacionNocturna,
            'agitacion' => $this->agitacion, 'dolor' => $this->dolor, 'motivo' => $this->motivo,
            'observacion' => $this->observacion, 'estado_general' => $this->estadoGeneral,
            'conciencia' => $this->conciencia, 'cognicion' => $this->cognicion,
            'conducta' => $this->conducta, 'respiracion' => $this->respiracion,
        ];
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
