<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;

use App\Models\AdministracionMedicacion;
use App\Models\Alerta;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\CuracionHerida;
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
use App\Backend\Modulos\Medicacion\Servicios\AgendaMedicacionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaseTurnoService
{
    public function __construct(
        private readonly TurnoEnfermeriaService $turnos,
        private readonly AgendaMedicacionService $medicacion
    ) {}

    private function autorizarPersonal(?User $usuario, ?string $permiso = null): Personal
    {
        abort_unless($usuario?->estado === 'ACTIVO'
            && app(\App\Backend\Modulos\Clinica\Servicios\AccesoClinicoTemporalService::class)
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
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('pases_turno.ver'), 403);
        if ($usuario->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            return;
        }
        $personal = $this->autorizarPersonal($usuario);
        $propio = in_array($personal->cod_personal, [$pase->cod_personal_saliente, $pase->cod_personal_entrante], true);
        abort_unless($propio || AsignacionResidenteJornada::query()->where('cod_residente', $pase->cod_residente)
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('cod_jornada', [$pase->cod_jornada_saliente, $pase->cod_jornada_entrante])
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists(), 403);
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
        $usuario = $usuario ?: Auth::user();

        if (! $usuario || $usuario->estado !== 'ACTIVO') {
            throw ValidationException::withMessages([
                'usuario' => 'Se requiere un usuario activo para resolver la jornada saliente.',
            ]);
        }

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();

        if (! $personal || ! in_array($personal->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages([
                'usuario' => 'El usuario no posee un registro de personal activo.',
            ]);
        }

        // 1. Buscar jornada donde el personal tiene asignaciones activas hoy
        $asignacion = AsignacionResidenteJornada::where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
            ->whereDate('fecha_hora', today())
            ->whereHas('jornada', fn ($query) => $query
                ->whereDate('fecha_jornada', today())
                ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO']))
            ->latest('fecha_hora')
            ->first();

        if ($asignacion?->jornada) {
            return $asignacion->jornada;
        }

        $asigPersonal = AsignacionPersonal::where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO', 'PRESENTE'])
            ->whereDate('fecha_asignacion', today())
            ->whereHas('jornada', fn ($query) => $query
                ->whereDate('fecha_jornada', today())
                ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO']))
            ->latest('fecha_asignacion')
            ->first();

        if ($asigPersonal?->jornada) {
            return $asigPersonal->jornada;
        }

        // 2. Buscar jornada activa para el turno según la hora actual
        $turnoActual = $this->turnos->obtenerTurnoActivo($usuario, today()->toDateString());
        if (! $turnoActual) {
            throw ValidationException::withMessages([
                'jornada' => 'El personal no tiene un turno activo y asignado para la fecha actual.',
            ]);
        }

        $jornada = Jornada::where('cod_turno', $turnoActual->cod_turno)
            ->whereDate('fecha_jornada', today())
            ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO'])
            ->first();

        if (! $jornada) {
            throw ValidationException::withMessages([
                'jornada' => 'No existe una jornada abierta para el turno activo del personal.',
            ]);
        }

        return $jornada;
    }

    /**
     * Resuelve automáticamente la siguiente jornada según turnos.orden y fecha
     */
    public function resolverJornadaEntrante(Jornada $jornadaSaliente): Jornada
    {
        $turnoSaliente = $jornadaSaliente->turno ?: Turno::find($jornadaSaliente->cod_turno);

        if (! $turnoSaliente || ! in_array($turnoSaliente->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages([
                'jornada' => 'La jornada saliente no está vinculada a un turno activo.',
            ]);
        }

        $ordenActual = $turnoSaliente->orden;

        // Buscar siguiente turno con orden superior
        $siguienteTurno = Turno::whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->where('orden', '>', $ordenActual)
            ->orderBy('orden')
            ->first();

        $fechaEntrante = Carbon::parse($jornadaSaliente->fecha_jornada)->format('Y-m-d');

        if (!$siguienteTurno) {
            // Ciclo cumplido (rollover): vuelve al primer turno del día siguiente
            $siguienteTurno = Turno::whereIn('estado', ['ACTIVO', 'ACTIVA'])->orderBy('orden')->first();
            $fechaEntrante = Carbon::parse($jornadaSaliente->fecha_jornada)->addDay()->format('Y-m-d');
        }

        if (! $siguienteTurno) {
            throw ValidationException::withMessages([
                'turno_entrante' => 'No existe un turno activo configurado para continuar el pase.',
            ]);
        }

        $jornadaEntrante = Jornada::where('cod_turno', $siguienteTurno->cod_turno)
            ->whereDate('fecha_jornada', $fechaEntrante)
            ->whereIn('estado', ['PLANIFICADA', 'ABIERTA', 'ACTIVA', 'EN_CURSO'])
            ->first();

        if (! $jornadaEntrante) {
            throw ValidationException::withMessages([
                'jornada_entrante' => 'La siguiente jornada debe estar planificada antes de realizar el pase.',
            ]);
        }


        return $jornadaEntrante;
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
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(404, 'Personal no encontrado para el usuario.', null, [], 404);
        }

        $personal = Personal::find($sujeto) ?: Personal::where('cod_usuario', $sujeto)->first();
        if ($personal) {
            return $personal;
        }

        throw new \Symfony\Component\HttpKernel\Exception\HttpException(404, 'Personal no encontrado.', null, [], 404);
    }

    public function obtenerResidentesAEntregar(Personal|User $personalSaliente, Jornada $jornadaSaliente, ?Jornada $jornadaEntrante = null): \Illuminate\Support\Collection
    {
        if ($personalSaliente instanceof User) {
            $personalSaliente = $this->resolverPersonal($personalSaliente);
        }

        if (!$jornadaEntrante) {
            $jornadaEntrante = $this->resolverJornadaEntrante($jornadaSaliente);
        }

        // 1. Obtener asignaciones de residentes del profesional en la jornada saliente
        $asignaciones = AsignacionResidenteJornada::where('cod_jornada', $jornadaSaliente->cod_jornada)
            ->where('cod_personal', $personalSaliente->cod_personal)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->with(['residente.cama.habitacion'])
            ->get();

        $codigosResidentes = $asignaciones->pluck('cod_residente')->unique()->values()->all();

        // 2. Para cada residente, buscar quién lo tiene asignado en la jornada entrante
        $asignacionesEntrantes = AsignacionResidenteJornada::where('cod_jornada', $jornadaEntrante->cod_jornada)
            ->whereIn('cod_residente', $codigosResidentes)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->with(['personal.usuario'])
            ->get()
            ->keyBy('cod_residente');

        // 3. Buscar pases existentes entre estas dos jornadas
        $pasesExistentes = PaseTurno::where('cod_jornada_saliente', $jornadaSaliente->cod_jornada)
            ->where('cod_jornada_entrante', $jornadaEntrante->cod_jornada)
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
            if (!$residente) continue;

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
            if ($tieneAlertaCritica) $score += 100;
            if ($tieneIncidente) $score += 50;
            if (!$pase || $pase->esBorrador()) $score += 20;

            $resultado[] = [
                'cod_residente' => $codR,
                'residente' => $residente,
                'nombre_completo' => $residente->nombre_completo,
                'ubicacion' => $residente->ubicacion_formateada,
                'personal_entrante' => $personalEntrante,
                'cod_personal_entrante' => $personalEntrante?->cod_personal,
                'nombre_personal_entrante' => $personalEntrante ? ($personalEntrante->usuario?->name ?? "{$personalEntrante->nombres} {$personalEntrante->apellido_paterno}") : 'Pendiente de asignación',
                'nombre_receptor' => $personalEntrante ? ($personalEntrante->usuario?->name ?? "{$personalEntrante->nombres} {$personalEntrante->apellido_paterno}") : 'Pendiente de asignación',
                'tiene_receptor' => (bool)$personalEntrante,
                'pase' => $pase,
                'estado_pase' => $pase ? $pase->estado : 'SIN_INICIAR',
                'alertas_count' => $alertas->count(),
                'incidentes_count' => $incidentes->count(),
                'tiene_alerta_critica' => $tieneAlertaCritica,
                'score' => $score
            ];
        }

        // Ordenar según priorización institucional
        usort($resultado, fn ($a, $b) => $b['score'] <=> $a['score']);

        return collect($resultado);
    }

    /**
     * Obtiene los pases pendientes de recibir para el profesional autenticado en la jornada entrante
     */
    public function obtenerPasesPendientesRecibir(Personal $personalEntrante, Jornada $jornadaEntrante): array
    {
        // 1. Obtener residentes asignados al profesional entrante en esta jornada
        $residentesAsignados = AsignacionResidenteJornada::where('cod_jornada', $jornadaEntrante->cod_jornada)
            ->where('cod_personal', $personalEntrante->cod_personal)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->pluck('cod_residente')
            ->all();

        // 2. Buscar pases en estado ENTREGADO dirigidos a esta jornada
        // Donde el residente esté asignado a él O cod_personal_entrante sea él
        $query = PaseTurno::where('cod_jornada_entrante', $jornadaEntrante->cod_jornada)
            ->whereIn('estado', ['ENTREGADO', 'GENERADO'])
            ->where(function ($q) use ($personalEntrante, $residentesAsignados) {
                $q->where('cod_personal_entrante', $personalEntrante->cod_personal)
                  ->orWhere(function ($sub) use ($residentesAsignados) {
                      $sub->whereNull('cod_personal_entrante')
                          ->whereIn('cod_residente', $residentesAsignados);
                  });
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
        $fecha = Carbon::parse($jornadaSaliente->fecha_jornada)->format('Y-m-d');
        $codJornada = $jornadaSaliente->cod_jornada;

        // 1. Medicación administrada
        $medsAdministradas = AdministracionMedicacion::where('cod_residente', $codResidente)
            ->whereDate('fecha_hora_administracion', $fecha)
            ->where('resultado', 'ADMINISTRADA')
            ->whereIn('estado', ['REGISTRADA', 'ADMINISTRADA'])
            ->with('prescripcion.medicamento')
            ->get()
            ->map(fn ($m) => [
                'medicamento' => $m->prescripcion?->medicamento?->nombre_generico ?? 'Medicación',
                'dosis' => $m->dosis_administrada ?? $m->prescripcion?->dosis,
                'via' => $m->prescripcion?->via_administracion,
                'hora' => Carbon::parse($m->fecha_hora_administracion)->format('H:i')
            ])->all();

        // Omisión documentada es un hecho; ausencia de registro se deriva de la agenda.
        $medsOmitidas = AdministracionMedicacion::where('cod_residente', $codResidente)
            ->whereDate('fecha_hora_programada', $fecha)
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
        $momentoAgenda = Carbon::parse($fecha)->isToday() ? now() : Carbon::parse($fecha)->endOfDay();
        $agendaPendiente = $this->medicacion->paraAdulto($codResidente, $momentoAgenda)
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
                'hora' => $c->fecha_hora_ejecucion ? Carbon::parse($c->fecha_hora_ejecucion)->format('H:i') : null
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
                'estado' => $c->estado
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
                'estado' => $i->estado
            ])->all();

        // 6. Alertas activas
        $alertas = Alerta::where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVA', 'PENDIENTE', 'ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'EN_ATENCION'])
            ->latest('fecha_hora')
            ->get()
            ->map(fn ($a) => [
                'titulo' => $a->titulo,
                'prioridad' => $a->prioridad,
                'tipo' => $a->tipo
            ])->all();

        // 7. Últimos Signos vitales
        $signos = SignoVital::where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVO', 'VIGENTE'])
            ->latest('fecha_hora')
            ->first();

        // 8. Dolor
        $dolor = ValoracionDolor::where('cod_residente', $codResidente)
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
                'ultima_curacion' => $h->curaciones->first()?->fecha_hora?->format('d/m H:i')
            ])->all();

        // 10. Movilidad
        $movilidad = RegistroMovilidad::where('cod_residente', $codResidente)->latest('fecha_hora')->first();

        // 11. Ingesta
        $ingesta = RegistroIngesta::where('cod_residente', $codResidente)->latest('fecha_hora')->first();

        // 12. Hidratación
        $hidratacion = RegistroHidratacion::where('cod_residente', $codResidente)->latest('fecha_hora')->first();

        // 13. Eliminación
        $eliminacion = RegistroEliminacion::where('cod_residente', $codResidente)->latest('fecha_hora')->first();

        // Observaciones cognitivas guardadas; no constituyen un diagnóstico.
        $cognicion = \App\Models\ControlCognitivo::where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVO', 'VIGENTE'])->latest('fecha_hora')->first();

        // 14. Conducta y Sueño
        $conducta = RegistroConductual::where('cod_residente', $codResidente)->latest('fecha_hora')->first();
        $sueno = RegistroSueno::where('cod_residente', $codResidente)->latest('fecha')->first();

        return [
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
            $usuario = $usuario ?: Auth::user();
        }
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
            $usuario = $usuario ?: Auth::user();
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
    private function procesarPase(string $codResidente, array $datos, ?User $usuario, string $estadoDestino): PaseTurno
    {
        $personalSaliente = $this->autorizarPersonal($usuario);
        if (!$personalSaliente) {
            throw ValidationException::withMessages(['usuario' => 'El usuario no posee un registro de personal activo.']);
        }

        $jornadaSaliente = $this->resolverJornadaSaliente($usuario);
        $jornadaEntrante = $this->resolverJornadaEntrante($jornadaSaliente);

        // Validar que el residente pertenezca al alcance/asignación
        $asignadoSaliente = AsignacionResidenteJornada::where('cod_residente', $codResidente)
            ->where('cod_personal', $personalSaliente->cod_personal)
            ->where('cod_jornada', $jornadaSaliente->cod_jornada)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->exists();

        if (! $asignadoSaliente) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'El residente no está asignado a su guardia actual.', null, [], 403);
        }

        // Resolver receptor para este residente en la jornada entrante
        $asigEntrante = AsignacionResidenteJornada::where('cod_jornada', $jornadaEntrante->cod_jornada)
            ->where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->first();
        $codPersonalEntrante = $asigEntrante?->cod_personal;

        return DB::transaction(function () use ($codResidente, $jornadaSaliente, $jornadaEntrante, $personalSaliente, $codPersonalEntrante, $datos, $estadoDestino, $usuario) {
            $paseExistente = PaseTurno::where('cod_residente', $codResidente)
                ->where('cod_jornada_saliente', $jornadaSaliente->cod_jornada)
                ->where('cod_jornada_entrante', $jornadaEntrante->cod_jornada)
                ->lockForUpdate()
                ->first();

            $this->turnos->autorizarMutacionPaciente($codResidente,
                $paseExistente ? 'pases_turno.editar' : 'pases_turno.crear', $usuario);
            if ($paseExistente) {
                // Si ya fue entregado o recibido, no se puede sobrescribir
                if (! $paseExistente->puedeEditarse()) { throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, 'El pase ya fue confirmado y no puede modificarse.', null, [], 409); }

                $paseExistente->estado_general = $datos['estado_general'] ?? $paseExistente->estado_general;
                $paseExistente->resumen = trim($datos['resumen'] ?? $paseExistente->resumen);
                $paseExistente->pendientes = $datos['pendientes'] ?? $paseExistente->pendientes;
                $paseExistente->vigilancia = $datos['vigilancia'] ?? $paseExistente->vigilancia;
                $paseExistente->recomendacion = $datos['recomendacion'] ?? $paseExistente->recomendacion;
                $paseExistente->cod_personal_entrante = $codPersonalEntrante ?: $paseExistente->cod_personal_entrante;

                if ($estadoDestino === 'ENTREGADO') {
                    $paseExistente->estado = 'ENTREGADO';
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

            $pase = new PaseTurno();
            $pase->cod_pase = 'PAS_' . strtoupper(Str::random(10));
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

        public function confirmarRecepcion(mixed $arg1, mixed $arg2 = null, mixed $arg3 = null): PaseTurno
    {
        if ($arg1 instanceof User || $arg1 instanceof Personal) {
            $usuario = $arg1 instanceof User ? $arg1 : $arg1->usuario;
            $pase = $arg2 instanceof PaseTurno ? $arg2 : PaseTurno::findOrFail($arg2);
            $observacionRecepcion = $arg3 !== null ? (string)$arg3 : null;
        } else {
            $pase = $arg1 instanceof PaseTurno ? $arg1 : PaseTurno::findOrFail($arg1);
            $observacionRecepcion = is_string($arg2) ? $arg2 : null;
            $usuario = $arg3 instanceof User ? $arg3 : ($arg2 instanceof User ? $arg2 : Auth::user());
        }

        $personal = $this->autorizarPersonal($usuario, 'pases_turno.editar');
        if (!$personal) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'El usuario no posee un registro de personal activo.', null, [], 403);
        }

        if (!$pase->puedeRecibirse()) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, 'El pase no se encuentra en estado entregado para recepción.', null, [], 409);
        }

        $asignadoEntrante = AsignacionResidenteJornada::where('cod_jornada', $pase->cod_jornada_entrante)
            ->where('cod_residente', $pase->cod_residente)
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->exists();

        $esReceptorDesignado = $pase->cod_personal_entrante === $personal->cod_personal;
        if (! $asignadoEntrante && ! $esReceptorDesignado) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'No está autorizado para recibir este residente en la jornada entrante.', null, [], 403);
        }

        return DB::transaction(function () use ($pase, $personal, $observacionRecepcion, $usuario) {
            $bloqueado = PaseTurno::lockForUpdate()->findOrFail($pase->getKey());
            if (!$bloqueado->puedeRecibirse()) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(409, 'El pase ya fue recibido previamente.', null, [], 409);
            }

            if (empty($bloqueado->cod_personal_entrante)) {
                $bloqueado->cod_personal_entrante = $personal->cod_personal;
            }

            $bloqueado->estado = PaseTurno::ESTADO_RECIBIDO ?? 'RECIBIDO';
            $bloqueado->fecha_hora_recepcion = now();
            $bloqueado->observacion_recepcion = $observacionRecepcion !== null && trim($observacionRecepcion) !== '' ? trim($observacionRecepcion) : null;
            $bloqueado->save();

            activity()
                ->performedOn($bloqueado)
                ->causedBy($usuario)
                ->withProperties(['estado' => PaseTurno::ESTADO_RECIBIDO ?? 'RECIBIDO', 'receptor' => $personal->cod_personal])
                ->log('Confirmación de recepción de pase de turno');

            return $bloqueado->refresh();
        });
    }

    /**
     * Anula lógicamente un pase de turno
     */
    public function anularPase(mixed $arg1, mixed $arg2 = null, ?string $motivo = null): PaseTurno
    {
        if ($arg1 instanceof User || $arg1 instanceof Personal) {
            $usuario = $arg1 instanceof User ? $arg1 : $arg1->usuario;
            $pase = $arg2 instanceof PaseTurno ? $arg2 : PaseTurno::findOrFail($arg2);
        } else {
            $pase = $arg1 instanceof PaseTurno ? $arg1 : PaseTurno::findOrFail($arg1);
            $usuario = $arg2 instanceof User ? $arg2 : Auth::user();
        }

        $personal = $this->autorizarPersonal($usuario, 'pases_turno.editar');
        abort_unless($pase->cod_personal_saliente === $personal->cod_personal
            && AsignacionResidenteJornada::query()->where('cod_residente', $pase->cod_residente)
                ->where('cod_personal', $personal->cod_personal)->where('cod_jornada', $pase->cod_jornada_saliente)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists(), 403);

        return DB::transaction(function () use ($pase, $usuario, $motivo) {
            $pase = PaseTurno::query()->lockForUpdate()->findOrFail($pase->getKey());
            abort_if($pase->esAnulado(), 409, 'El pase ya está anulado.');
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
            ->get()->map(fn ($r) => ['tipo' => 'INCIDENTE', 'id' => $r->getKey(), 'detalle' => $r->tipo_incidente . ': ' . $r->descripcion, 'estado' => $r->estado])->all();

        $lesiones = Herida::where('cod_residente', $codResidente)->where('estado', 'ACTIVA')
            ->get()->map(fn ($r) => ['tipo' => 'LESION', 'id' => $r->getKey(), 'detalle' => 'Herida activa en ' . ($r->ubicacion ?: 'cuerpo'), 'estado' => 'ACTIVA'])->all();

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
        if (!$receptor->hasRole('ENFERMEROS') || $receptor->getKey() === $usuario->getKey()) {
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

        $jornadaEntrante = Jornada::whereDate('fecha_jornada', today())
            ->where('cod_turno', $turnoEntranteId)
            ->whereIn('estado', ['PLANIFICADA', 'ABIERTA', 'ACTIVA', 'EN_CURSO'])
            ->first();

        if (! $jornadaEntrante) {
            throw ValidationException::withMessages([
                'jornada_entrante' => 'La jornada entrante debe existir y estar planificada antes de generar el pase.',
            ]);
        }

        $this->validarContextoEmision($usuario, $codResidente, [
            'cod_jornada_saliente' => $jornadaSaliente->cod_jornada,
            'cod_jornada_entrante' => $jornadaEntrante->cod_jornada,
            'cod_personal_entrante' => $codPersonalEntrante,
        ]);

        $paseExistente = PaseTurno::where('cod_residente', $codResidente)
            ->where('cod_jornada_saliente', $jornadaSaliente->cod_jornada)
            ->whereDate('fecha_hora', today())
            ->exists();

        if ($paseExistente) {
            throw ValidationException::withMessages(['cod_residente' => 'Ya existe el pase de este residente para el turno actual.']);
        }

        $pendientes = $this->pendientes($codResidente, $saliente);
        $resumen = trim($datosValidados['observaciones']);

        return PaseTurno::create([
            'cod_pase' => 'PAS_' . strtoupper(Str::random(10)),
            'cod_residente' => $codResidente,
            'cod_jornada_saliente' => $jornadaSaliente->cod_jornada,
            'cod_jornada_entrante' => $jornadaEntrante->cod_jornada,
            'cod_personal_saliente' => $codPersonalSaliente,
            'cod_personal_entrante' => $codPersonalEntrante,
            'fecha_hora' => now(),
            'estado_general' => $datosValidados['estado_general'] ?? null,
            'resumen' => $resumen,
            'pendientes' => json_encode($pendientes),
            'vigilancia' => $datosValidados['vigilancia'] ? ($datosValidados['motivo_vigilancia'] ?? 'SI') : null,
            'recomendacion' => $datosValidados['recomendacion'] ?? null,
            'estado' => 'GENERADO',
        ]);
    }

    public function recibir(PaseTurno $pase, User $usuario): PaseTurno
    {
        return $this->confirmarRecepcion($pase, null, $usuario);
    }
}
