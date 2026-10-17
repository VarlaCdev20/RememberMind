<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Compartido\Aplicacion\Formato\TextoVisible;
use App\Models\AdministracionMedicacion;
use App\Models\Alerta;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\ControlCognitivo;
use App\Models\EjecucionCuidado;
use App\Models\Herida;
use App\Models\Incidente;
use App\Models\Jornada;
use App\Models\PaseTurno;
use App\Models\Personal;
use App\Models\RegistroConductual;
use App\Models\RegistroEliminacion;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\RegistroMovilidad;
use App\Models\RegistroSueno;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\Turno;
use App\Models\User;
use App\Models\ValoracionDolor;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaseTurnoService
{
    public function __construct(private readonly ContextoEjecucion $contexto,
        private readonly TurnoEnfermeriaService $turnos,
        private readonly AgendaMedicacionService $medicacion
    ) {}

    private function autorizarPersonal(?User $usuario, ?string $permiso = null): Personal
    {
        abort_unless($usuario?->estado === 'ACTIVO'
            && app(AccesoClinicoTemporalService::class)
                ->tieneRol($usuario, ['ENFERMEROS']), 403, 'La operación requiere una cuenta activa de Enfermería.');
        abort_if($permiso !== null && ! $usuario->can($permiso), 403, 'No cuenta con el permiso requerido para esta acción.');
        $personal = Personal::query()->where('cod_usuario', $usuario->cod_usuario)->where('estado', 'ACTIVO')->first();
        abort_unless($personal, 403, 'No cuenta con un registro de personal activo.');

        return $personal;
    }

    public function autorizarLecturaResidente(string $codResidente, User $usuario, ?Jornada $jornada = null): void
    {
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('pases_turno.ver'), 403);
        if ($usuario->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            return;
        }
        $personal = $this->autorizarPersonal($usuario);
        abort_unless(AsignacionResidenteJornada::query()->where('cod_residente', $codResidente)
            ->where('cod_personal', $personal->cod_personal)->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->when($jornada, fn ($query) => $query->where('cod_jornada', $jornada->cod_jornada))
            ->exists(), 403, 'El residente no pertenece al alcance de su jornada.');
    }

    public function autorizarLecturaPase(PaseTurno $pase, User $usuario): void
    {
        abort_unless($this->acotarConsultaPases(PaseTurno::query(), $usuario)->whereKey($pase->getKey())->exists(), 403);
    }

    /** El historial debe respetar el mismo alcance que la apertura de un pase. */
    public function acotarConsultaPases(Builder $query, User $usuario): Builder
    {
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('pases_turno.ver'), 403);
        if ($usuario->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            return $query;
        }
        $personal = $this->autorizarPersonal($usuario);

        return $query->where(fn ($pases) => $pases
            ->where('cod_personal_saliente', $personal->cod_personal)
            ->orWhere('cod_personal_entrante', $personal->cod_personal)
            ->orWhereHas('residente.asignacionesJornada', fn ($asignaciones) => $asignaciones
                ->where('cod_personal', $personal->cod_personal)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->where(fn ($jornadas) => $jornadas
                    ->whereColumn('asignaciones_residente_jornada.cod_jornada', 'pases_turno.cod_jornada_saliente')
                    ->orWhereColumn('asignaciones_residente_jornada.cod_jornada', 'pases_turno.cod_jornada_entrante'))));
    }

    public function validarContextoEmision(User $usuario, string $codResidente, array $datos): void
    {
        $personal = $this->autorizarPersonal($usuario, 'pases_turno.crear');
        $saliente = $this->resolverJornadaSaliente($usuario);
        $entrante = $this->resolverJornadaEntrante($saliente);
        abort_unless(AsignacionResidenteJornada::query()->where('cod_residente', $codResidente)
            ->where('cod_personal', $personal->cod_personal)->where('cod_jornada', $saliente->cod_jornada)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])->exists(), 403);
        if (($datos['cod_jornada_saliente'] ?? null) !== $saliente->cod_jornada
            || ($datos['cod_jornada_entrante'] ?? null) !== $entrante->cod_jornada) {
            throw ValidationException::withMessages(['cod_jornada_saliente' => 'Las jornadas deben corresponder al relevo del personal saliente.']);
        }
        if (! empty($datos['cod_personal_entrante'])) {
            $receptor = Personal::query()->whereKey($datos['cod_personal_entrante'])->where('estado', 'ACTIVO')->first();
            if ($receptor?->usuario?->estado !== 'ACTIVO' || ! $receptor?->usuario?->hasRole('ENFERMEROS')
                || ! AsignacionResidenteJornada::query()->where('cod_residente', $codResidente)
                    ->where('cod_personal', $receptor->cod_personal)->where('cod_jornada', $entrante->cod_jornada)
                    ->whereIn('estado', ['ACTIVA', 'ACTIVO'])->exists()) {
                throw ValidationException::withMessages(['cod_personal_entrante' => 'El receptor debe pertenecer a la asignación entrante del residente.']);
            }
        }
    }

    // ========================================================
    // 1. RESOLUCIÓN AUTOMÁTICA DE JORNADAS Y TURNOS
    // ========================================================

    /**
     * Resuelve la jornada activa/saliente del usuario o turno actual
     */
    public function resolverJornadaSaliente(?User $usuario = null): Jornada
    {
        $usuario ??= $this->contexto->usuario();
        if (! $usuario || $usuario->estado !== 'ACTIVO') {
            throw ValidationException::withMessages(['usuario' => 'Se requiere una cuenta activa.']);
        }
        $personal = Personal::query()->where('cod_usuario', $usuario->getKey())->where('estado', 'ACTIVO')->first();
        if (! $personal) {
            throw ValidationException::withMessages(['usuario' => 'No cuenta con personal activo vinculado.']);
        }
        $relaciones = collect();
        foreach ([AsignacionPersonal::class, AsignacionResidenteJornada::class] as $modelo) {
            $relaciones = $relaciones->concat($modelo::query()->where('cod_personal', $personal->getKey())
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->whereHas('jornada', fn ($q) => $q
                ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO'])
                ->whereDate('fecha_jornada', '>=', today()->subDay()->toDateString())
                ->whereDate('fecha_jornada', '<=', today()->toDateString()))
                ->with('jornada.turno')->get()->pluck('jornada'));
        }
        $ventanas = app(MiTurnoService::class);
        $jornadas = $relaciones->filter()->unique('cod_jornada')->filter(function ($jornada) use ($ventanas) {
            if (! $jornada->turno || ! in_array($jornada->turno->estado, ['ACTIVO', 'ACTIVA'], true)) {
                return false;
            }
            [$inicio, $fin] = $ventanas->calcularVentanaTurno($jornada);

            return now()->gte($inicio) && now()->lt($fin);
        })->values();
        if ($jornadas->count() !== 1) {
            throw ValidationException::withMessages(['jornada' => $jornadas->isEmpty()
                ? 'No tiene una jornada activa en este momento. Puede consultar sus pases en el historial.'
                : 'Hay varias jornadas activas superpuestas. Solicite revisar la planificación antes de entregar.']);
        }

        return $jornadas->first();
    }

    /**
     * Resuelve automáticamente la siguiente jornada según turnos.orden y fecha
     */
    public function resolverJornadaEntrante(Jornada $jornadaSaliente): Jornada
    {
        $ventanas = app(MiTurnoService::class);
        if (! $jornadaSaliente->turno || ! in_array($jornadaSaliente->turno->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages(['jornada' => 'La jornada saliente requiere un turno activo.']);
        }
        [, $fin] = $ventanas->calcularVentanaTurno($jornadaSaliente);
        $areas = AsignacionPersonal::query()->where('cod_jornada', $jornadaSaliente->getKey())
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'PRESENTE', 'PLANIFICADO', 'PLANIFICADA', 'ASIGNADO'])->pluck('cod_area');
        $candidatas = Jornada::query()->whereKeyNot($jornadaSaliente->getKey())
            ->whereIn('estado', ['PLANIFICADA', 'ABIERTA', 'ACTIVA', 'EN_CURSO'])
            ->whereDate('fecha_jornada', '>=', $fin->toDateString())
            ->whereDate('fecha_jornada', '<=', $fin->copy()->addDay()->toDateString())
            ->whereHas('turno', fn ($q) => $q->whereIn('estado', ['ACTIVO', 'ACTIVA']))
            ->when($areas->isNotEmpty(), fn ($q) => $q->whereHas('asignacionesPersonal', fn ($a) => $a
                ->whereIn('cod_area', $areas)->whereIn('estado', ['ACTIVO', 'ACTIVA', 'PRESENTE', 'PLANIFICADO', 'PLANIFICADA', 'ASIGNADO'])))
            ->with('turno')->get()
            ->filter(fn ($j) => $ventanas->calcularVentanaTurno($j)[0]->gte($fin));
        $residentes = AsignacionResidenteJornada::query()->where('cod_jornada', $jornadaSaliente->getKey())
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->pluck('cod_residente');
        $vinculadas = AsignacionResidenteJornada::query()->whereIn('cod_jornada', $candidatas->modelKeys())
            ->whereIn('cod_residente', $residentes)->whereIn('estado', ['ACTIVO', 'ACTIVA'])->pluck('cod_jornada');
        $candidatas = $candidatas->sortBy(fn ($j) => $ventanas->calcularVentanaTurno($j)[0]->getTimestamp())->values();
        if ($candidatas->isEmpty()) {
            throw ValidationException::withMessages(['jornada_entrante' => 'La siguiente jornada debe estar planificada antes de realizar el pase.']);
        }
        $inicio = $ventanas->calcularVentanaTurno($candidatas->first())[0];
        $candidatas = $candidatas->filter(fn ($j) => $ventanas->calcularVentanaTurno($j)[0]->equalTo($inicio));
        $vinculadasAlInicio = $candidatas->whereIn('cod_jornada', $vinculadas);
        if ($vinculadasAlInicio->isNotEmpty()) {
            $candidatas = $vinculadasAlInicio;
        }
        if ($candidatas->count() !== 1) {
            throw ValidationException::withMessages(['jornada_entrante' => 'Hay varias jornadas posibles para el relevo. Solicite revisar la planificación.']);
        }

        return $candidatas->first();
    }

    // ========================================================
    // 2. RESOLUCIÓN DE RESIDENTES Y RECEPTORES POR RESIDENTE
    // ========================================================

    /**
     * Obtiene la lista estructurada de residentes a entregar por el profesional autenticado
     */
    /**
     * Resuelve el modelo Personal asociado a un User, ID o instancia.
     */
    public function resolverPersonal(Personal|User|string $sujeto): Personal
    {
        if ($sujeto instanceof Personal) {
            return $sujeto;
        }

        if ($sujeto instanceof User) {
            $personal = $sujeto->personal ?: Personal::where('cod_usuario', $sujeto->cod_usuario)->first();
            if ($personal) {
                return $personal;
            }
            throw new HttpException(404, 'Personal no encontrado para el usuario.', null, [], 404);
        }

        $personal = Personal::find($sujeto) ?: Personal::where('cod_usuario', $sujeto)->first();
        if ($personal) {
            return $personal;
        }

        throw new HttpException(404, 'Personal no encontrado.', null, [], 404);
    }

    public function obtenerResidentesAEntregar(Personal|User $personalSaliente, Jornada $jornadaSaliente, ?Jornada $jornadaEntrante = null): Collection
    {
        if ($personalSaliente instanceof User) {
            $personalSaliente = $this->resolverPersonal($personalSaliente);
        }

        if (! $jornadaEntrante) {
            try {
                $jornadaEntrante = $this->resolverJornadaEntrante($jornadaSaliente);
            } catch (ValidationException) {
                // La consulta conserva los residentes propios aunque falte planificar el relevo.
                $jornadaEntrante = null;
            }
        }

        // 1. Obtener asignaciones de residentes del profesional en la jornada saliente
        $asignaciones = AsignacionResidenteJornada::where('cod_jornada', $jornadaSaliente->cod_jornada)
            ->where('cod_personal', $personalSaliente->cod_personal)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->with(['residente.cama.habitacion'])
            ->get();

        $codigosResidentes = $asignaciones->pluck('cod_residente')->unique()->values()->all();

        // 2. Para cada residente, buscar quién lo tiene asignado en la jornada entrante
        $asignacionesEntrantes = AsignacionResidenteJornada::where('cod_jornada', $jornadaEntrante?->cod_jornada)
            ->whereIn('cod_residente', $codigosResidentes)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->with(['personal.usuario'])
            ->get()
            ->keyBy('cod_residente');

        // 3. Buscar pases existentes entre estas dos jornadas
        $pasesExistentes = PaseTurno::where('cod_jornada_saliente', $jornadaSaliente->cod_jornada)
            ->when($jornadaEntrante, fn ($q) => $q->where('cod_jornada_entrante', $jornadaEntrante->cod_jornada))
            ->whereIn('cod_residente', $codigosResidentes)
            ->get()
            ->keyBy('cod_residente');

        // 4. Buscar alertas e incidentes vigentes para priorización clínica
        $alertasCriticas = Alerta::whereIn('cod_residente', $codigosResidentes)
            ->whereIn('estado', ['ACTIVA', 'PENDIENTE', 'ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'EN_ATENCION'])
            ->get()
            ->groupBy('cod_residente');

        $incidentesActivos = Incidente::whereIn('cod_residente', $codigosResidentes)
            ->whereIn('estado', ['ABIERTO', 'EN_SEGUIMIENTO'])
            ->get()
            ->groupBy('cod_residente');

        $resultado = [];

        foreach ($asignaciones as $asig) {
            $residente = $asig->residente;
            if (! $residente) {
                continue;
            }

            $codR = $residente->cod_residente;
            $asigEntrante = $asignacionesEntrantes->get($codR);
            $personalEntrante = $asigEntrante?->personal;

            $pase = $pasesExistentes->get($codR);
            $alertas = $alertasCriticas->get($codR, collect());
            $incidentes = $incidentesActivos->get($codR, collect());

            $tieneAlertaCritica = $alertas->contains(fn ($a) => in_array(strtoupper($a->prioridad ?? $a->nivel ?? ''), ['CRITICA', 'CRITICO', 'ALTA', 'ALTO']));
            $tieneIncidente = $incidentes->isNotEmpty();

            // Puntuación de ordenamiento: 1. Alerta crítica, 2. Incidente, 3. Pase pendiente, 4. Normal
            $score = 0;
            if ($tieneAlertaCritica) {
                $score += 100;
            }
            if ($tieneIncidente) {
                $score += 50;
            }
            if (! $pase || $pase->esBorrador()) {
                $score += 20;
            }

            $resultado[] = [
                'cod_residente' => $codR,
                'residente' => $residente,
                'nombre_completo' => $residente->nombre_completo,
                'ubicacion' => $residente->ubicacion_formateada,
                'personal_entrante' => $personalEntrante,
                'cod_personal_entrante' => $personalEntrante?->cod_personal,
                'nombre_personal_entrante' => $personalEntrante ? ($personalEntrante->usuario?->name ?? "{$personalEntrante->nombres} {$personalEntrante->apellido_paterno}") : 'Pendiente de asignación',
                'nombre_receptor' => $personalEntrante ? ($personalEntrante->usuario?->name ?? "{$personalEntrante->nombres} {$personalEntrante->apellido_paterno}") : 'Pendiente de asignación',
                'tiene_receptor' => (bool) $personalEntrante,
                'pase' => $pase,
                'estado_pase' => $pase ? $pase->estado : 'SIN_INICIAR',
                'alertas_count' => $alertas->count(),
                'incidentes_count' => $incidentes->count(),
                'tiene_alerta_critica' => $tieneAlertaCritica,
                'score' => $score,
            ];
        }

        // Ordenar según priorización institucional
        usort($resultado, fn ($a, $b) => $b['score'] <=> $a['score']);

        return collect($resultado);
    }

    /**
     * Obtiene los pases pendientes de recibir para el profesional autenticado en la jornada entrante
     */
    public function obtenerPasesPendientesRecibir(Personal $personalEntrante, ?Jornada $jornadaEntrante = null): array
    {
        // Designación o asignación del residente en la jornada entrante exacta.
        $query = PaseTurno::query()
            ->when($jornadaEntrante, fn ($q) => $q->where('cod_jornada_entrante', $jornadaEntrante->cod_jornada))
            ->whereIn('estado', ['ENTREGADO', 'GENERADO', 'EMITIDO', 'PENDIENTE'])
            ->where(function ($q) use ($personalEntrante) {
                $q->where('cod_personal_entrante', $personalEntrante->cod_personal)
                    ->orWhereHas('residente.asignacionesJornada', fn ($a) => $a
                        ->where('cod_personal', $personalEntrante->getKey())
                        ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                        ->whereColumn('asignaciones_residente_jornada.cod_jornada', 'pases_turno.cod_jornada_entrante'));
            })
            ->with(['residente.cama.habitacion', 'personalSaliente.usuario', 'jornadaSaliente.turno', 'jornadaEntrante.turno']);

        return $query->latest('fecha_hora')->get()->all();
    }

    // ========================================================
    // 3. CONTEXTO CLÍNICO DEL RESIDENTE (14 FUENTES DEL TURNO)
    // ========================================================

    /**
     * Recopila el contexto clínico de las 14 fuentes del turno en solo lectura
     */
    public function obtenerContextoClinicoResidente(string $codResidente, Jornada $jornadaSaliente): array
    {
        [$inicio, $fin] = app(MiTurnoService::class)->calcularVentanaTurno($jornadaSaliente);
        $fecha = Carbon::parse($jornadaSaliente->fecha_jornada)->format('Y-m-d');
        $codJornada = $jornadaSaliente->cod_jornada;

        // 1. Medicación administrada
        $medsAdministradas = AdministracionMedicacion::where('cod_residente', $codResidente)
            ->where('fecha_hora_administracion', '>=', $inicio)->where('fecha_hora_administracion', '<', $fin)
            ->where('resultado', 'ADMINISTRADA')
            ->whereIn('estado', ['REGISTRADA', 'ADMINISTRADA'])
            ->with('prescripcion.medicamento')
            ->get()
            ->map(fn ($m) => [
                'medicamento' => $m->prescripcion?->medicamento?->nombre_generico ?? 'Medicación',
                'dosis' => $m->dosis_administrada ?? $m->prescripcion?->dosis,
                'unidad_dosis' => $m->prescripcion?->unidad_dosis,
                'via' => $m->prescripcion?->via_administracion,
                'hora' => Carbon::parse($m->fecha_hora_administracion)->format('H:i'),
            ])->all();

        // Omisión documentada es un hecho; ausencia de registro se deriva de la agenda.
        $medsOmitidas = AdministracionMedicacion::where('cod_residente', $codResidente)
            ->where('fecha_hora_programada', '>=', $inicio)->where('fecha_hora_programada', '<', $fin)
            ->where('resultado', 'OMITIDA')
            ->whereIn('estado', ['REGISTRADA', 'OMITIDA'])
            ->with('prescripcion.medicamento')
            ->get()->map(fn ($m) => [
                'medicamento' => $m->prescripcion?->medicamento?->nombre_generico ?? 'Medicación',
                'estado' => 'OMITIDA',
                'hora' => Carbon::parse($m->fecha_hora_programada)->format('H:i'),
                'motivo' => $m->motivo_omision,
                'cod_administracion' => $m->cod_administracion,
            ])->all();
        $agenda = collect();
        for ($dia = $inicio->copy()->startOfDay(); $dia->lt($fin); $dia->addDay()) {
            $agenda = $agenda->concat($this->medicacion->paraAdulto($codResidente, now(), $dia));
        }
        $agendaPendiente = $agenda->filter(fn ($item) => $item['programada']->gte($inicio) && $item['programada']->lt($fin))
            ->whereIn('estado', ['PENDIENTE', 'PROXIMA', 'VENCIDA'])
            ->map(fn ($item) => [
                'medicamento' => $item['medicacion']->medicamento?->nombre_generico ?? 'Medicación',
                'estado' => $item['estado'], 'hora' => $item['hora'], 'id' => $item['id'],
            ])->values()->all();
        $medsPendientes = array_merge($medsOmitidas, $agendaPendiente);

        // 3. Cuidados realizados
        $cuidadosRealizados = EjecucionCuidado::where('cod_residente', $codResidente)
            ->where('cod_jornada', $codJornada)
            ->whereIn('estado', ['REALIZADA', 'EJECUTADA'])
            ->with('intervencion')
            ->get()
            ->map(fn ($c) => [
                'intervencion' => $c->intervencion?->nombre ?? 'Cuidado de enfermería',
                'hora' => $c->fecha_hora_ejecucion ? Carbon::parse($c->fecha_hora_ejecucion)->format('H:i') : null,
            ])->all();

        // 4. Cuidados pendientes / omitidos
        $cuidadosPendientes = EjecucionCuidado::where('cod_residente', $codResidente)
            ->where(fn ($q) => $q->where('cod_jornada', $codJornada)
                ->orWhere('fecha_hora_programada', '<=', Carbon::parse($fecha)->endOfDay()))
            ->whereIn('estado', ['PENDIENTE', 'NO_REALIZADA', 'OMITIDA'])
            ->with('intervencion')
            ->get()
            ->map(fn ($c) => [
                'intervencion' => $c->intervencion?->nombre ?? 'Cuidado asistencial',
                'estado' => $c->estado,
            ])->all();

        // 5. Incidentes
        $incidentes = Incidente::where('cod_residente', $codResidente)
            ->where(function ($q) use ($codJornada, $fecha) {
                $q->where('cod_jornada', $codJornada)
                    ->orWhereDate('fecha_hora', $fecha)
                    ->orWhereIn('estado', ['ABIERTO', 'EN_SEGUIMIENTO']);
            })
            ->latest('fecha_hora')
            ->get()
            ->map(fn ($i) => [
                'tipo' => $i->tipo_incidente,
                'gravedad' => $i->gravedad,
                'descripcion' => $i->descripcion,
                'estado' => $i->estado,
            ])->all();

        // 6. Alertas activas
        $alertas = Alerta::where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVA', 'PENDIENTE', 'ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'EN_ATENCION'])
            ->latest('fecha_hora')
            ->get()
            ->map(fn ($a) => [
                'titulo' => TextoVisible::sinCodigosInternos($a->titulo),
                'prioridad' => $a->prioridad,
                'tipo' => $a->tipo,
            ])->all();

        // 7. Últimos Signos vitales
        $signos = SignoVital::where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVO', 'VIGENTE'])
            ->latest('fecha_hora')
            ->first();

        // 8. Dolor
        $dolor = ValoracionDolor::where('cod_residente', $codResidente)
            ->whereNotIn('estado', ['ANULADO', 'RECTIFICADO'])
            ->latest('fecha_hora')
            ->first();

        // 9. Heridas / Curaciones
        $heridas = Herida::where('cod_residente', $codResidente)
            ->where('estado', 'ACTIVA')
            ->with(['curaciones' => fn ($q) => $q->latest('fecha_hora')->limit(1)])
            ->get()
            ->map(fn ($h) => [
                'ubicacion' => $h->ubicacion ?? 'Herida activa',
                'tipo' => $h->tipo_herida ?? 'Lesión cutánea',
                'ultima_curacion' => $h->curaciones->first()?->fecha_hora?->format('d/m H:i'),
            ])->all();

        // 10. Movilidad
        $movilidad = RegistroMovilidad::where('cod_residente', $codResidente)->whereNotIn('estado', ['ANULADO', 'RECTIFICADO'])->latest('fecha_hora')->first();

        // 11. Ingesta
        $ingesta = RegistroIngesta::where('cod_residente', $codResidente)->whereNotIn('estado', ['ANULADO', 'RECTIFICADO'])->latest('fecha_hora')->first();

        // 12. Hidratación
        $hidratacion = RegistroHidratacion::where('cod_residente', $codResidente)->whereNotIn('estado', ['ANULADO', 'RECTIFICADO'])->latest('fecha_hora')->first();

        // 13. Eliminación
        $eliminacion = RegistroEliminacion::where('cod_residente', $codResidente)->whereNotIn('estado', ['ANULADO', 'RECTIFICADO'])->latest('fecha_hora')->first();

        // Observaciones cognitivas guardadas; no constituyen un diagnóstico.
        $cognicion = ControlCognitivo::where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVO', 'VIGENTE'])->latest('fecha_hora')->first();

        // 14. Conducta y Sueño
        $conducta = RegistroConductual::where('cod_residente', $codResidente)->whereNotIn('estado', ['ANULADO', 'RECTIFICADO'])->latest('fecha_hora')->first();
        $sueno = RegistroSueno::where('cod_residente', $codResidente)->whereNotIn('estado', ['ANULADO', 'RECTIFICADO'])->latest('fecha')->first();

        return [
            'ventana' => $inicio->format('d/m/Y H:i').' — '.$fin->format('d/m/Y H:i'),
            'meds_administradas' => $medsAdministradas,
            'meds_pendientes' => $medsPendientes,
            'meds_omitidas' => $medsOmitidas,
            'cuidados_realizados' => $cuidadosRealizados,
            'cuidados_pendientes' => $cuidadosPendientes,
            'incidentes' => $incidentes,
            'alertas' => $alertas,
            'signos_vitales' => $signos,
            'dolor' => $dolor,
            'heridas' => $heridas,
            'movilidad' => $movilidad,
            'ingesta' => $ingesta,
            'hidratacion' => $hidratacion,
            'eliminacion' => $eliminacion,
            'conducta' => $conducta,
            'cognicion' => $cognicion,
            'sueno' => $sueno,
        ];
    }

    // ========================================================
    // 4. ACCIONES DEL CICLO DE VIDA (BORRADOR, ENTREGA, RECEPCIÓN)
    // ========================================================

    /**
     * Guarda el pase de turno en estado BORRADOR
     */
    public function guardarBorrador(string|User $residenteOUsuario, array $datos, ?User $usuario = null): PaseTurno
    {
        if ($residenteOUsuario instanceof User) {
            $usuario = $residenteOUsuario;
            $codResidente = $datos['cod_residente'] ?? '';
        } else {
            $codResidente = $residenteOUsuario;
            $usuario = $usuario ?: $this->contexto->usuario();
        }

        Validator::make($datos, [
            'resumen' => 'required|string|max:5000', 'estado_general' => 'nullable|string|max:200',
            'pendientes' => 'nullable|string|max:3000', 'vigilancia' => 'nullable|string|max:3000',
            'recomendacion' => 'nullable|string|max:3000',
        ])->validate();

        return $this->procesarPase($codResidente, $datos, $usuario, 'BORRADOR');
    }

    /**
     * Confirma la entrega formal del pase de turno (estado ENTREGADO)
     */
    public function confirmarEntrega(string|User $residenteOUsuario, array $datos, ?User $usuario = null): PaseTurno
    {
        if ($residenteOUsuario instanceof User) {
            $usuario = $residenteOUsuario;
            $codResidente = $datos['cod_residente'] ?? '';
        } else {
            $codResidente = $residenteOUsuario;
            $usuario = $usuario ?: $this->contexto->usuario();
        }

        Validator::make($datos, [
            'resumen' => 'required|string|min:5|max:5000',
            'estado_general' => 'nullable|string|max:200',
            'pendientes' => 'nullable|string|max:3000',
            'vigilancia' => 'nullable|string|max:3000',
            'recomendacion' => 'nullable|string|max:3000',
        ], [
            'resumen.required' => 'El resumen clínico del turno es obligatorio para confirmar la entrega.',
            'resumen.min' => 'El resumen debe tener al menos 5 caracteres.',
        ])->validate();

        return $this->procesarPase($codResidente, $datos, $usuario, 'ENTREGADO');
    }

    /**
     * Lógica interna para persistir BORRADOR o ENTREGADO sin permitir duplicados
     */
    public function emitir(User $usuario, string $codResidente, array $datos): PaseTurno
    {
        $this->validarContextoEmision($usuario, $codResidente, $datos);
        Validator::make($datos, [
            'resumen' => 'required|string|min:5|max:5000', 'estado_general' => 'nullable|string|max:200',
            'pendientes' => 'nullable|string|max:3000', 'vigilancia' => 'nullable|string|max:3000',
            'recomendacion' => 'nullable|string|max:3000',
        ])->validate();

        return $this->procesarPase($codResidente, $datos, $usuario, 'EMITIDO', true);
    }

    private function procesarPase(string $codResidente, array $datos, ?User $usuario, string $estadoDestino, bool $soloCrear = false): PaseTurno
    {
        return DB::transaction(function () use ($codResidente, $datos, $usuario, $estadoDestino, $soloCrear) {
            // Bloqueo común incluso cuando todavía no existe un pase para este relevo.
            Residente::query()->whereKey($codResidente)->lockForUpdate()->firstOrFail();
            $personalSaliente = $this->autorizarPersonal($usuario);
            if (! $personalSaliente) {
                throw ValidationException::withMessages(['usuario' => 'El usuario no posee un registro de personal activo.']);
            }

            try {
                $jornadaSaliente = $this->resolverJornadaSaliente($usuario);
            } catch (ValidationException $exception) {
                throw new HttpException(403, 'La entrega requiere una jornada vigente y asignada.', $exception);
            }
            $jornadaEntrante = $this->resolverJornadaEntrante($jornadaSaliente);

            // Validar que el residente pertenezca al alcance/asignación
            $asignadoSaliente = AsignacionResidenteJornada::where('cod_residente', $codResidente)
                ->where('cod_personal', $personalSaliente->cod_personal)
                ->where('cod_jornada', $jornadaSaliente->cod_jornada)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->exists();

            if (! $asignadoSaliente) {
                throw new HttpException(403, 'El residente no está asignado a su guardia actual.', null, [], 403);
            }

            // Resolver receptor para este residente en la jornada entrante
            $asigEntrante = AsignacionResidenteJornada::where('cod_jornada', $jornadaEntrante->cod_jornada)
                ->where('cod_residente', $codResidente)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->first();
            $codPersonalEntrante = $asigEntrante?->cod_personal;

            if (array_key_exists('cod_personal_entrante', $datos)) {
                $this->validarContextoEmision($usuario, $codResidente, $datos);
                $codPersonalEntrante = $datos['cod_personal_entrante'];
            }

            $paseExistente = PaseTurno::where('cod_residente', $codResidente)
                ->where('cod_jornada_saliente', $jornadaSaliente->cod_jornada)
                ->where('cod_jornada_entrante', $jornadaEntrante->cod_jornada)
                ->lockForUpdate()
                ->first();

            if ($soloCrear && $paseExistente) {
                throw ValidationException::withMessages(['cod_residente' => 'Ya existe un pase para este residente y relevo. Consulta el pase registrado.']);
            }

            $this->turnos->autorizarMutacionPaciente($codResidente,
                $paseExistente ? 'pases_turno.editar' : 'pases_turno.crear', $usuario);
            if ($paseExistente) {
                abort_unless($paseExistente->cod_personal_saliente === $personalSaliente->cod_personal, 403,
                    'Solo el profesional que preparó el borrador puede modificarlo o entregarlo.');
                // Si ya fue entregado o recibido, no se puede sobrescribir
                if (! $paseExistente->puedeEditarse()) {
                    throw new HttpException(409, 'El pase ya fue confirmado y no puede modificarse.', null, [], 409);
                }

                $paseExistente->estado_general = $datos['estado_general'] ?? $paseExistente->estado_general;
                $paseExistente->resumen = trim($datos['resumen'] ?? $paseExistente->resumen);
                $paseExistente->pendientes = $datos['pendientes'] ?? $paseExistente->pendientes;
                $paseExistente->vigilancia = $datos['vigilancia'] ?? $paseExistente->vigilancia;
                $paseExistente->recomendacion = $datos['recomendacion'] ?? $paseExistente->recomendacion;
                $paseExistente->cod_personal_entrante = $codPersonalEntrante ?: $paseExistente->cod_personal_entrante;

                if ($estadoDestino !== 'BORRADOR') {
                    $paseExistente->estado = $estadoDestino;
                    $paseExistente->fecha_hora = now();
                }
                $paseExistente->save();

                activity()
                    ->performedOn($paseExistente)
                    ->causedBy($usuario)
                    ->withProperties(['estado' => $paseExistente->estado, 'residente' => $codResidente])
                    ->log("Actualización de pase de turno ({$paseExistente->estado})");

                return $paseExistente;
            }

            $pase = new PaseTurno;
            $pase->cod_pase = app(CodigosEntidad::class)->siguiente('pases_turno');
            $pase->cod_residente = $codResidente;
            $pase->cod_jornada_saliente = $jornadaSaliente->cod_jornada;
            $pase->cod_jornada_entrante = $jornadaEntrante->cod_jornada;
            $pase->cod_personal_saliente = $personalSaliente->cod_personal;
            $pase->cod_personal_entrante = $codPersonalEntrante;
            $pase->fecha_hora = now();
            $pase->estado_general = $datos['estado_general'] ?? null;
            $pase->resumen = trim($datos['resumen'] ?? 'Resumen de guardia');
            $pase->pendientes = $datos['pendientes'] ?? null;
            $pase->vigilancia = $datos['vigilancia'] ?? null;
            $pase->recomendacion = $datos['recomendacion'] ?? null;
            $pase->estado = $estadoDestino;
            $pase->save();

            activity()
                ->performedOn($pase)
                ->causedBy($usuario)
                ->withProperties(['estado' => $estadoDestino, 'residente' => $codResidente])
                ->log("Creación de pase de turno ({$estadoDestino})");

            return $pase;
        });
    }

    public function puedeRecibir(PaseTurno $pase, User $usuario): bool
    {
        $personal = $usuario->personal;

        return $usuario->estado === 'ACTIVO' && $usuario->can('pases_turno.editar')
            && app(AccesoClinicoTemporalService::class)->tieneRol($usuario, ['ENFERMEROS'])
            && $personal?->estado === 'ACTIVO' && $pase->puedeRecibirse()
            && ($pase->cod_personal_entrante === $personal->getKey()
                || AsignacionResidenteJornada::query()->where('cod_residente', $pase->cod_residente)
                    ->where('cod_personal', $personal->getKey())->where('cod_jornada', $pase->cod_jornada_entrante)
                    ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists());
    }

    public function confirmarRecepcion(mixed $arg1, mixed $arg2 = null, mixed $arg3 = null): PaseTurno
    {
        if ($arg1 instanceof User || $arg1 instanceof Personal) {
            $usuario = $arg1 instanceof User ? $arg1 : $arg1->usuario;
            $pase = $arg2 instanceof PaseTurno ? $arg2 : PaseTurno::findOrFail($arg2);
            $observacionRecepcion = $arg3 !== null ? (string) $arg3 : null;
        } else {
            $pase = $arg1 instanceof PaseTurno ? $arg1 : PaseTurno::findOrFail($arg1);
            $observacionRecepcion = is_string($arg2) ? $arg2 : null;
            $usuario = $arg3 instanceof User ? $arg3 : ($arg2 instanceof User ? $arg2 : $this->contexto->usuario());
        }

        $personal = $this->autorizarPersonal($usuario, 'pases_turno.editar');
        if (! $personal) {
            throw new HttpException(403, 'El usuario no posee un registro de personal activo.', null, [], 403);
        }

        if (! $pase->puedeRecibirse()) {
            throw new HttpException(409, 'El pase no se encuentra en estado entregado para recepción.', null, [], 409);
        }

        Validator::make(['observacion_recepcion' => $observacionRecepcion], [
            'observacion_recepcion' => 'nullable|string|max:3000',
        ])->validate();

        return DB::transaction(function () use ($pase, $observacionRecepcion, $usuario) {
            $bloqueado = PaseTurno::lockForUpdate()->findOrFail($pase->getKey());
            $personal = $this->autorizarPersonal($usuario, 'pases_turno.editar');
            if (! $bloqueado->puedeRecibirse()) {
                throw new HttpException(409, 'El pase ya fue recibido previamente.', null, [], 409);
            }
            $asignadoEntrante = AsignacionResidenteJornada::where('cod_jornada', $bloqueado->cod_jornada_entrante)
                ->where('cod_residente', $bloqueado->cod_residente)
                ->where('cod_personal', $personal->cod_personal)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->exists();

            $esReceptorDesignado = $bloqueado->cod_personal_entrante === $personal->cod_personal;
            if (! $asignadoEntrante && ! $esReceptorDesignado) {
                throw new HttpException(403, 'No está autorizado para recibir este residente en la jornada entrante.', null, [], 403);
            }

            $receptorDesignado = $bloqueado->cod_personal_entrante;
            $bloqueado->cod_personal_entrante = $personal->cod_personal;

            $bloqueado->estado = PaseTurno::ESTADO_RECIBIDO ?? 'RECIBIDO';
            $bloqueado->fecha_hora_recepcion = now();
            $bloqueado->observacion_recepcion = $observacionRecepcion !== null && trim($observacionRecepcion) !== '' ? trim($observacionRecepcion) : null;
            $bloqueado->save();

            activity()
                ->performedOn($bloqueado)
                ->causedBy($usuario)
                ->withProperties(['estado' => PaseTurno::ESTADO_RECIBIDO, 'receptor' => $personal->cod_personal, 'receptor_designado' => $receptorDesignado])
                ->log('Confirmación de recepción de pase de turno');

            return $bloqueado->refresh();
        });
    }

    /**
     * Anula lógicamente un pase de turno
     */
    public function puedeAnular(PaseTurno $pase, User $usuario): bool
    {
        $personal = $usuario->personal;

        return $usuario->estado === 'ACTIVO' && $usuario->can('pases_turno.editar')
            && app(AccesoClinicoTemporalService::class)->tieneRol($usuario, ['ENFERMEROS'])
            && $personal?->estado === 'ACTIVO' && ! $pase->esAnulado()
            && $pase->cod_personal_saliente === $personal->getKey()
            && AsignacionResidenteJornada::query()->where('cod_residente', $pase->cod_residente)
                ->where('cod_personal', $personal->getKey())->where('cod_jornada', $pase->cod_jornada_saliente)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists();
    }

    public function anularPase(mixed $arg1, mixed $arg2 = null, ?string $motivo = null): PaseTurno
    {
        if ($arg1 instanceof User || $arg1 instanceof Personal) {
            $usuario = $arg1 instanceof User ? $arg1 : $arg1->usuario;
            $pase = $arg2 instanceof PaseTurno ? $arg2 : PaseTurno::findOrFail($arg2);
        } else {
            $pase = $arg1 instanceof PaseTurno ? $arg1 : PaseTurno::findOrFail($arg1);
            $usuario = $arg2 instanceof User ? $arg2 : $this->contexto->usuario();
        }

        $personal = $this->autorizarPersonal($usuario, 'pases_turno.editar');
        abort_unless($pase->cod_personal_saliente === $personal->cod_personal
            && AsignacionResidenteJornada::query()->where('cod_residente', $pase->cod_residente)
                ->where('cod_personal', $personal->cod_personal)->where('cod_jornada', $pase->cod_jornada_saliente)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists(), 403);

        return DB::transaction(function () use ($pase, $usuario, $motivo) {
            $pase = PaseTurno::query()->lockForUpdate()->findOrFail($pase->getKey());
            abort_if($pase->esAnulado(), 409, 'El pase ya está anulado.');
            abort_unless($this->puedeAnular($pase, $usuario), 403);
            Validator::make(['motivo' => $motivo], ['motivo' => 'nullable|string|max:3000'])->validate();
            $pase->update(['estado' => PaseTurno::ESTADO_ANULADO]);
            activity()->performedOn($pase)->causedBy($usuario)->withProperties(['motivo' => $motivo])
                ->log('Anulación lógica de pase de turno');

            return $pase;
        });
    }

    // ========================================================
    // 5. MÉTODOS DE COMPATIBILIDAD CON TESTS EXISTENTES
    // ========================================================

    public function pendientes(string $codResidente, Turno $turno): array
    {
        $cuidados = EjecucionCuidado::where('cod_residente', $codResidente)
            ->where('fecha_hora_programada', '<=', today()->endOfDay())
            ->whereIn('estado', ['PENDIENTE', 'NO_REALIZADA', 'OMITIDA'])
            ->get()->map(fn ($r) => ['tipo' => 'CUIDADO', 'id' => $r->getKey(), 'detalle' => $r->observaciones ?: 'Cuidado asistencial', 'estado' => $r->estado])->all();

        $alertas = Alerta::where('cod_residente', $codResidente)->whereIn('estado', ['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'EN_ATENCION', 'ACTIVA', 'PENDIENTE'])
            ->get()->map(fn ($r) => ['tipo' => 'ALERTA', 'id' => $r->getKey(), 'detalle' => $r->titulo ?: $r->tipo, 'estado' => $r->estado, 'prioridad' => $r->prioridad, 'nivel' => $r->prioridad])->all();

        $incidentes = Incidente::where('cod_residente', $codResidente)->whereIn('estado', ['ABIERTO', 'EN_SEGUIMIENTO'])
            ->get()->map(fn ($r) => ['tipo' => 'INCIDENTE', 'id' => $r->getKey(), 'detalle' => $r->tipo_incidente.': '.$r->descripcion, 'estado' => $r->estado])->all();

        $lesiones = Herida::where('cod_residente', $codResidente)->where('estado', 'ACTIVA')
            ->get()->map(fn ($r) => ['tipo' => 'LESION', 'id' => $r->getKey(), 'detalle' => 'Herida activa en '.($r->ubicacion ?: 'cuerpo'), 'estado' => 'ACTIVA'])->all();

        $medicacion = $this->medicacion->paraAdulto($codResidente)
            ->whereIn('estado', ['PENDIENTE', 'PROXIMA', 'VENCIDA'])
            ->map(fn ($item) => ['tipo' => 'MEDICACION', 'id' => $item['id'],
                'detalle' => ($item['medicacion']->medicamento?->nombre_generico ?? 'Medicación').' '.$item['hora'],
                'estado' => $item['estado'], 'hora' => $item['hora']])->values()->all();

        return array_values(array_merge($alertas, $cuidados, $incidentes, $lesiones, $medicacion));
    }

    public function generar(string $codResidente, string $turnoEntranteId, string $enfermeroEntranteId, array $datos, User $usuario): PaseTurno
    {
        $saliente = $this->turnos->autorizarMutacionPaciente($codResidente, 'pases_turno.crear', $usuario);

        $datosValidados = Validator::make($datos, [
            'observaciones' => 'required|string|min:5|max:5000',
            'estado_general' => 'nullable|string|max:100',
            'recomendacion' => 'nullable|string|max:5000',
            'vigilancia' => 'required|boolean',
            'motivo_vigilancia' => 'required_if:vigilancia,true|nullable|string|min:10|max:2000',
        ])->validate();

        $receptor = User::whereKey($enfermeroEntranteId)->where('estado', 'ACTIVO')->firstOrFail();
        if (! $receptor->hasRole('ENFERMEROS') || $receptor->getKey() === $usuario->getKey()) {
            throw ValidationException::withMessages(['enfermero_entrante_id' => 'El receptor debe ser otro enfermero activo.']);
        }

        $codPersonalSaliente = $usuario->personal?->cod_personal;
        $codPersonalEntrante = $receptor->personal?->cod_personal;

        if (! $codPersonalSaliente || ! $codPersonalEntrante) {
            throw ValidationException::withMessages([
                'personal' => 'El personal saliente y entrante deben estar vinculados a usuarios activos.',
            ]);
        }

        $jornadaSaliente = $this->resolverJornadaSaliente($usuario);
        $turnoEntrante = Turno::query()
            ->whereKey($turnoEntranteId)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->first();

        if (! $turnoEntrante) {
            throw ValidationException::withMessages([
                'turno_entrante_id' => 'El turno entrante no existe o no está activo.',
            ]);
        }

        $jornadaEntrante = $this->resolverJornadaEntrante($jornadaSaliente);

        if ($jornadaEntrante->cod_turno !== $turnoEntranteId) {
            throw ValidationException::withMessages([
                'jornada_entrante' => 'La jornada entrante debe existir y estar planificada antes de generar el pase.',
            ]);
        }

        $this->validarContextoEmision($usuario, $codResidente, [
            'cod_jornada_saliente' => $jornadaSaliente->cod_jornada,
            'cod_jornada_entrante' => $jornadaEntrante->cod_jornada,
            'cod_personal_entrante' => $codPersonalEntrante,
        ]);

        return $this->procesarPase($codResidente, [
            'cod_jornada_saliente' => $jornadaSaliente->getKey(),
            'cod_jornada_entrante' => $jornadaEntrante->getKey(),
            'cod_personal_entrante' => $codPersonalEntrante,
            'estado_general' => $datosValidados['estado_general'] ?? null,
            'resumen' => trim($datosValidados['observaciones']),
            'pendientes' => json_encode($this->pendientes($codResidente, $saliente), JSON_THROW_ON_ERROR),
            'vigilancia' => $datosValidados['vigilancia'] ? $datosValidados['motivo_vigilancia'] : null,
            'recomendacion' => $datosValidados['recomendacion'] ?? null,
        ], $usuario, 'GENERADO', true);
    }

    public function recibir(PaseTurno $pase, User $usuario): PaseTurno
    {
        return $this->confirmarRecepcion($pase, null, $usuario);
    }
}
