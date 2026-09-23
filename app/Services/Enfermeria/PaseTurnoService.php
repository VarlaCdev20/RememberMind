<?php

namespace App\Services\Enfermeria;

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
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Models\ValoracionDolor;
use App\Services\Medicacion\AgendaMedicacionService;
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

    // ========================================================
    // 1. RESOLUCIÓN AUTOMÁTICA DE JORNADAS Y TURNOS
    // ========================================================

    /**
     * Resuelve la jornada activa/saliente del usuario o turno actual
     */
    public function resolverJornadaSaliente(?User $usuario = null): Jornada
    {
        $usuario = $usuario ?: Auth::user();
        $personal = $usuario?->personal ?: Personal::where('cod_usuario', $usuario?->cod_usuario ?? $usuario?->cod_usu)->first();

        // 1. Buscar jornada donde el personal tiene asignaciones activas hoy
        if ($personal) {
            $asignacion = AsignacionResidenteJornada::where('cod_personal', $personal->cod_personal)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->whereDate('fecha_hora', today())
                ->latest('fecha_hora')
                ->first();

            if ($asignacion && $asignacion->jornada) {
                return $asignacion->jornada;
            }

            $asigPersonal = AsignacionPersonal::where('cod_personal', $personal->cod_personal)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->whereDate('fecha_asignacion', today())
                ->latest('fecha_asignacion')
                ->first();

            if ($asigPersonal && $asigPersonal->jornada) {
                return $asigPersonal->jornada;
            }
        }

        // 2. Buscar jornada activa para el turno según la hora actual
        $turnoActual = $this->turnos->obtenerTurnoActivo($usuario, today()->toDateString());
        $codTurno = $turnoActual ? $turnoActual->cod_turno : (Turno::where('estado', 'ACTIVO')->orderBy('orden')->value('cod_turno'));

        $jornada = Jornada::where('cod_turno', $codTurno)
            ->whereDate('fecha_jornada', today())
            ->first();

        if (!$jornada) {
            $jornada = Jornada::create([
                'cod_jornada' => 'JOR_' . strtoupper(Str::random(10)),
                'cod_turno' => $codTurno,
                'cod_usuario_apertura' => $usuario?->cod_usuario ?? 'SISTEMA',
                'fecha_jornada' => today(),
                'estado' => 'ACTIVA',
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
        $ordenActual = $turnoSaliente ? $turnoSaliente->orden : 1;

        // Buscar siguiente turno con orden superior
        $siguienteTurno = Turno::where('estado', 'ACTIVO')
            ->where('orden', '>', $ordenActual)
            ->orderBy('orden')
            ->first();

        $fechaEntrante = Carbon::parse($jornadaSaliente->fecha_jornada)->format('Y-m-d');

        if (!$siguienteTurno) {
            // Ciclo cumplido (rollover): vuelve al primer turno del día siguiente
            $siguienteTurno = Turno::where('estado', 'ACTIVO')->orderBy('orden')->first();
            $fechaEntrante = Carbon::parse($jornadaSaliente->fecha_jornada)->addDay()->format('Y-m-d');
        }

        $codTurnoEntrante = $siguienteTurno ? $siguienteTurno->cod_turno : $jornadaSaliente->cod_turno;

        $jornadaEntrante = Jornada::where('cod_turno', $codTurnoEntrante)
            ->whereDate('fecha_jornada', $fechaEntrante)
            ->first();

        if (!$jornadaEntrante) {
            $jornadaEntrante = Jornada::create([
                'cod_jornada' => 'JOR_' . strtoupper(Str::random(10)),
                'cod_turno' => $codTurnoEntrante,
                'cod_usuario_apertura' => Auth::user()?->cod_usuario ?? 'SISTEMA',
                'fecha_jornada' => $fechaEntrante,
                'estado' => 'ACTIVA',
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

        // Si no hay asignaciones en esa jornada específica pero es superadmin o demo, buscamos asignaciones activas hoy
        if ($asignaciones->isEmpty()) {
            $asignaciones = AsignacionResidenteJornada::where('cod_personal', $personalSaliente->cod_personal)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->with(['residente.cama.habitacion'])
                ->latest('fecha_hora')
                ->take(15)
                ->get();
        }

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
            ->whereIn('estado', ['ACTIVA', 'PENDIENTE', 'ABIERTA', 'EN_ATENCION'])
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
            ->where('estado', 'ADMINISTRADA')
            ->with('prescripcion')
            ->get()
            ->map(fn ($m) => [
                'medicamento' => $m->prescripcion?->medicamento ?? 'Medicación',
                'dosis' => $m->dosis_administrada ?? $m->prescripcion?->dosis,
                'via' => $m->prescripcion?->via_administracion,
                'hora' => Carbon::parse($m->fecha_hora_administracion)->format('H:i')
            ])->all();

        // 2. Medicación pendiente / omitida / retrasada
        $medsPendientes = AdministracionMedicacion::where('cod_residente', $codResidente)
            ->whereDate('fecha_hora_programada', $fecha)
            ->whereIn('estado', ['PENDIENTE', 'OMITIDA', 'RECHAZADA', 'SUSPENDIDA'])
            ->with('prescripcion')
            ->get()
            ->map(fn ($m) => [
                'medicamento' => $m->prescripcion?->medicamento ?? 'Medicación',
                'estado' => $m->estado,
                'hora' => Carbon::parse($m->fecha_hora_programada)->format('H:i')
            ])->all();

        // 3. Cuidados realizados
        $cuidadosRealizados = EjecucionCuidado::where('cod_residente', $codResidente)
            ->where('cod_jornada', $codJornada)
            ->where('estado', 'REALIZADA')
            ->with('intervencion')
            ->get()
            ->map(fn ($c) => [
                'intervencion' => $c->intervencion?->nombre ?? 'Cuidado de enfermería',
                'hora' => $c->fecha_hora_ejecucion ? Carbon::parse($c->fecha_hora_ejecucion)->format('H:i') : null
            ])->all();

        // 4. Cuidados pendientes / omitidos
        $cuidadosPendientes = EjecucionCuidado::where('cod_residente', $codResidente)
            ->where('cod_jornada', $codJornada)
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
            ->whereIn('estado', ['ACTIVA', 'PENDIENTE', 'ABIERTA', 'EN_ATENCION'])
            ->latest('fecha_hora')
            ->get()
            ->map(fn ($a) => [
                'titulo' => $a->titulo,
                'prioridad' => $a->prioridad,
                'tipo' => $a->tipo
            ])->all();

        // 7. Últimos Signos vitales
        $signos = SignoVital::where('cod_residente', $codResidente)
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
                'tipo' => $h->tipo ?? 'Lesión cutánea',
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

        // 14. Conducta y Sueño
        $conducta = RegistroConductual::where('cod_residente', $codResidente)->latest('fecha_hora')->first();
        $sueno = RegistroSueno::where('cod_residente', $codResidente)->latest('fecha_hora')->first();

        return [
            'meds_administradas' => $medsAdministradas,
            'meds_pendientes' => $medsPendientes,
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
    private function procesarPase(string $codResidente, array $datos, User $usuario, string $estadoDestino): PaseTurno
    {
        $personalSaliente = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario ?? $usuario->cod_usu)->first();
        if (!$personalSaliente) {
            throw ValidationException::withMessages(['usuario' => 'El usuario no posee un registro de personal activo.']);
        }

        $jornadaSaliente = $this->resolverJornadaSaliente($usuario);
        $jornadaEntrante = $this->resolverJornadaEntrante($jornadaSaliente);

        // Validar que el residente pertenezca al alcance/asignación
        $asignadoSaliente = AsignacionResidenteJornada::where('cod_residente', $codResidente)
            ->where('cod_personal', $personalSaliente->cod_personal)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->exists();

        if (!$asignadoSaliente && !$usuario->hasRole('SUPERADMINISTRADOR')) {
            throw ValidationException::withMessages(['cod_residente' => 'El residente no está asignado a su guardia actual.']);
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

            if ($paseExistente) {
                // Si ya fue entregado o recibido, no se puede sobrescribir
                if ($paseExistente->esRecibido() || ($paseExistente->esEntregado() && $estadoDestino === 'BORRADOR')) {
                    throw ValidationException::withMessages(['cod_residente' => 'El pase ya fue confirmado y no puede modificarse.']);
                }

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

    /**
     * Confirma la recepción del pase por parte del profesional entrante
     */
    public function confirmarRecepcion(PaseTurno $pase, ?string $observacionRecepcion, User $usuario): PaseTurno
    {
        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario ?? $usuario->cod_usu)->first();
        if (!$personal) {
            abort(403, 'El usuario no posee un registro de personal activo.');
        }

        // Validar que el pase esté entregado
        abort_unless($pase->puedeRecibirse(), 409, 'El pase no se encuentra en estado entregado para recepción.');

        // Validar que el receptor sea válido: asignado en la jornada entrante o receptor designado
        $asignadoEntrante = AsignacionResidenteJornada::where('cod_jornada', $pase->cod_jornada_entrante)
            ->where('cod_residente', $pase->cod_residente)
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->exists();

        $esReceptorDesignado = $pase->cod_personal_entrante === $personal->cod_personal;
        $esSuperAdmin = $usuario->hasRole('SUPERADMINISTRADOR');

        if (!$asignadoEntrante && !$esReceptorDesignado && !$esSuperAdmin) {
            abort(403, 'No está autorizado para recibir este residente en la jornada entrante.');
        }

        return DB::transaction(function () use ($pase, $personal, $observacionRecepcion, $usuario) {
            $bloqueado = PaseTurno::lockForUpdate()->findOrFail($pase->getKey());
            abort_unless($bloqueado->puedeRecibirse(), 409, 'El pase ya fue recibido previamente.');

            // Si el pase no tenía personal entrante designado y este profesional lo recibe, se asigna
            if (empty($bloqueado->cod_personal_entrante)) {
                $bloqueado->cod_personal_entrante = $personal->cod_personal;
            }

            $bloqueado->estado = 'RECIBIDO';
            $bloqueado->fecha_hora_recepcion = now();
            $bloqueado->observacion_recepcion = $observacionRecepcion ? trim($observacionRecepcion) : null;
            $bloqueado->save();

            activity()
                ->performedOn($bloqueado)
                ->causedBy($usuario)
                ->withProperties(['estado' => 'RECIBIDO', 'receptor' => $personal->cod_personal])
                ->log('Confirmación de recepción de pase de turno');

            return $bloqueado->refresh();
        });
    }

    // ========================================================
    // 5. MÉTODOS DE COMPATIBILIDAD CON TESTS EXISTENTES
    // ========================================================

    public function pendientes(string $codResidente, TurnoEnfermeria $turno): array
    {
        $cuidados = EjecucionCuidado::where('cod_residente', $codResidente)
            ->whereDate('fecha_hora_programada', today())
            ->whereIn('estado', ['PENDIENTE', 'NO_REALIZADA', 'OMITIDA'])
            ->get()->map(fn ($r) => ['tipo' => 'CUIDADO', 'id' => $r->getKey(), 'detalle' => $r->observaciones ?: 'Cuidado asistencial', 'estado' => $r->estado])->all();

        $alertas = Alerta::where('cod_residente', $codResidente)->whereIn('estado', ['ABIERTA', 'EN_ATENCION', 'ACTIVA', 'PENDIENTE'])
            ->get()->map(fn ($r) => ['tipo' => 'ALERTA', 'id' => $r->getKey(), 'detalle' => $r->titulo ?: $r->tipo, 'estado' => $r->estado, 'prioridad' => $r->prioridad, 'nivel' => $r->prioridad])->all();

        $incidentes = Incidente::where('cod_residente', $codResidente)->whereIn('estado', ['ABIERTO', 'EN_SEGUIMIENTO'])
            ->get()->map(fn ($r) => ['tipo' => 'INCIDENTE', 'id' => $r->getKey(), 'detalle' => $r->tipo_incidente . ': ' . $r->descripcion, 'estado' => $r->estado])->all();

        $lesiones = Herida::where('cod_residente', $codResidente)->where('estado', 'ACTIVA')
            ->get()->map(fn ($r) => ['tipo' => 'LESION', 'id' => $r->getKey(), 'detalle' => 'Herida activa en ' . ($r->ubicacion ?: 'cuerpo'), 'estado' => 'ACTIVA'])->all();

        return array_values(array_merge($alertas, $cuidados, $incidentes, $lesiones));
    }

    public function generar(string $codResidente, string $turnoEntranteId, string $enfermeroEntranteId, array $datos, User $usuario): PaseTurno
    {
        $saliente = $this->turnos->autorizarMutacionPaciente($codResidente, 'pase_turno.generar', $usuario);

        $datosValidados = Validator::make($datos, [
            'observaciones' => 'nullable|string|max:5000',
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

        $jornadaSaliente = $this->resolverJornadaSaliente($usuario);
        $jornadaEntrante = Jornada::whereDate('fecha_jornada', today())
            ->where('cod_turno', $turnoEntranteId)
            ->first();

        if (!$jornadaEntrante) {
            $jornadaEntrante = Jornada::create([
                'cod_jornada' => 'JOR_' . strtoupper(Str::random(10)),
                'cod_turno' => $turnoEntranteId,
                'cod_usuario_apertura' => $receptor->cod_usuario,
                'fecha_jornada' => today(),
                'estado' => 'ABIERTA',
            ]);
        }

        $paseExistente = PaseTurno::where('cod_residente', $codResidente)
            ->where('cod_jornada_saliente', $jornadaSaliente->cod_jornada)
            ->whereDate('fecha_hora', today())
            ->exists();

        if ($paseExistente) {
            throw ValidationException::withMessages(['cod_residente' => 'Ya existe el pase de este residente para el turno actual.']);
        }

        $pendientes = $this->pendientes($codResidente, $saliente);
        $resumen = trim(($datosValidados['observaciones'] ?? 'Se entrega guardia con novedades normales.'));

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
