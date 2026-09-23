<?php

namespace App\Services\Enfermeria;

use App\Models\User;
use App\Models\Personal;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Residente;
use App\Models\Alerta;
use App\Models\EventoAlerta;
use App\Models\Prescripcion;
use App\Models\HorarioPrescripcion;
use App\Models\AdministracionMedicacion;
use App\Models\PlanCuidado;
use App\Models\IntervencionCuidado;
use App\Models\ProgramacionCuidado;
use App\Models\EjecucionCuidado;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MiTurnoService
{
    /**
     * Mapeo centralizado basado EXCLUSIVAMENTE en valores reales existentes
     * en BDD Operativa V2:
     * alertas.estado: ABIERTA, EN_ATENCION, CERRADA
     * alertas.prioridad: BAJO/BAJA, MEDIO/MEDIA, ALTO/ALTA, CRITICO/CRITICA
     * eventos_alerta.tipo_evento: ATENCION, INTERVENCION, ASIGNACION, SEGUIMIENTO, CIERRE
     * eventos_alerta.estado_nuevo: EN_ATENCION, CERRADA
     */
    public const ESTADOS_ALERTA_ACTIVOS = ['ABIERTA', 'EN_ATENCION', 'RECONOCIDA', 'ASIGNADA', 'PENDIENTE', 'ACTIVA', 'ACTIVO'];
    public const ESTADOS_ALERTA_RESUELTOS = ['CERRADA', 'RESUELTA', 'ATENDIDA', 'CANCELADA', 'INACTIVA'];
    public const PRIORIDADES_CRITICAS = ['CRITICO', 'CRITICA'];
    public const PRIORIDADES_ALTAS = ['ALTO', 'ALTA'];
    public const PRIORIDADES_RELEVANTES = ['CRITICO', 'CRITICA', 'ALTO', 'ALTA', 'MEDIO', 'MEDIA'];

    public const RESULTADOS_MEDICACION_RESUELTOS = ['ADMINISTRADA', 'ADMINISTRADO', 'REALIZADA', 'OMITIDA', 'OMITIDO', 'RECHAZADA', 'RECHAZADO'];
    public const RESULTADOS_CUIDADO_RESUELTOS = ['REALIZADA', 'COMPLETADA', 'OMITIDA', 'NO_REALIZADA'];

    /**
     * Resuelve los datos completos del Dashboard "Mi turno" para el usuario autenticado.
     * Es estrictamente de SOLO LECTURA: no muta ni crea registros clínicos.
     */
    public function obtenerDatosDashboard(User $user, ?string $filtroFecha = null): array
    {
        // 1. Verificación de seguridad básica de usuario activo y rol
        $this->validarSeguridadUsuario($user);

        $esSuperAdmin = $user->hasRole('SUPERADMINISTRADOR');
        $momentoActual = Carbon::now();

        // 2. Personal institucional
        $personal = Personal::query()
            ->where('cod_usuario', $user->cod_usuario)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->first();

        // Metadatos de usuario
        $primerNombre = $personal?->nombres ? explode(' ', trim($personal->nombres))[0] : ($user->nombres ? explode(' ', trim($user->nombres))[0] : 'Elena');
        $apellidos = trim(($personal?->apellido_paterno ?? $user->apellido_paterno ?? '') . ' ' . ($personal?->apellido_materno ?? $user->apellido_materno ?? ''));
        $nombreCompleto = $personal ? "{$personal->nombres} {$apellidos}" : ($user->name ?: 'Elena Salazar');

        $iniciales = '';
        foreach (explode(' ', $nombreCompleto) as $part) {
            if ($part !== '') {
                $iniciales .= mb_strtoupper(mb_substr($part, 0, 1));
            }
            if (mb_strlen($iniciales) >= 2) break;
        }
        $iniciales = $iniciales ?: 'ES';

        // 3. Resolver si el usuario autenticado tiene jornada activa en este momento
        $jornada = $this->resolverJornadaActual($personal, $momentoActual);

        // =========================================================================
        // MODO 1: EN TURNO (ACTIVO Y OPERATIVO)
        // =========================================================================
        if ($jornada) {
            $turno = $jornada->turno;
            $area = $this->resolverAreaPersonal($personal, $jornada);

            // Calcular ventana temporal exacta del turno (soportando turnos nocturnos)
            [$inicioTurno, $finTurno] = $this->calcularVentanaTurno($jornada);

            // Residentes asignados para esta guardia al profesional
            $asigResidentes = $this->resolverAsignacionesResidentes($personal, $jornada, $esSuperAdmin);
            $codResidentes = $asigResidentes->pluck('cod_residente')->filter()->unique()->values()->toArray();

            // Alertas del turno
            $alertas = $this->resolverAlertasTurno($codResidentes, $inicioTurno, $finTurno);
            $kpisData = $this->calcularKpisAlertas($alertas);
            $alertaCritica = $alertas->first(function ($a) {
                $p = strtoupper(trim((string)$a->prioridad));
                $est = strtoupper(trim((string)$a->estado));
                return in_array($p, self::PRIORIDADES_CRITICAS, true) && in_array($est, self::ESTADOS_ALERTA_ACTIVOS, true);
            });

            // Agenda unificada basada en la programación
                        // Mapa de timestamps de asignación de cada residente cuando fue asignado a mitad del turno
            $fechasAsignacionResidentes = [];
            foreach ($asigResidentes as $asig) {
                if (!empty($asig->fecha_hora)) {
                    $fechaHoraAsig = Carbon::parse($asig->fecha_hora);
                    if ($fechaHoraAsig->gt($inicioTurno)) {
                        $esReasignacion = AsignacionResidenteJornada::query()
                            ->where('cod_jornada', $jornada->cod_jornada)
                            ->where('cod_residente', $asig->cod_residente)
                            ->where('cod_personal', '!=', $personal?->cod_personal)
                            ->where('fecha_hora', '<', $fechaHoraAsig)
                            ->exists();

                        if ($esReasignacion || str_contains(strtolower($asig->observacion ?? ''), 'mitad') || str_contains(strtolower($asig->observacion ?? ''), 'reasig')) {
                            $fechasAsignacionResidentes[$asig->cod_residente] = $fechaHoraAsig;
                        }
                    }
                }
            }

            // Agenda unificada basada en la programación
            $agendaUnificada = $this->resolverAgendaProgramada($codResidentes, $jornada, $inicioTurno, $finTurno, $momentoActual, $fechasAsignacionResidentes);

            // Progreso del turno
            $progresoTurno = $this->calcularProgresoTurno($agendaUnificada);

            // Estado General del turno
            $estadoGeneral = $this->calcularEstadoGeneral($alertas, (int)($progresoTurno['retrasadas'] ?? 0), $alertaCritica, $momentoActual);

            // Distribución de cuidados
            $distribucionCuidados = $this->resolverDistribucionCuidados($codResidentes, $agendaUnificada);

            // Formateo de tarjetas de residentes asignados con su información clínica real
            $nombreTurno = $turno?->nombre ? (str_starts_with(strtolower(trim($turno->nombre)), 'turno') ? trim($turno->nombre) : "Turno {$turno->nombre}") : "Jornada {$jornada->cod_jornada}";
            $responsablePropio = $personal ? "A cargo de: Enf. {$primerNombre} " . ($personal->apellido_paterno ?? '') : "A cargo de: {$nombreCompleto}";
            $residentesCards = $this->formatearResidentesCards($asigResidentes, $alertas, trim($responsablePropio), $nombreTurno);

            // Formateo de stream de alertas recientes del turno
            $alertasRecientes = $this->formatearAlertasRecientes($alertas);

            $totalDist = (int)(collect($distribucionCuidados)->sum('total'));
            $distribucionMap = [
                'medicacion' => $totalDist > 0 ? (int)(collect($distribucionCuidados)->firstWhere('label', 'Medicación')['total'] ?? (collect($distribucionCuidados)->first(fn($i) => stripos($i['label'] ?? '', 'medic') !== false)['total'] ?? 0)) : null,
                'signos' => $totalDist > 0 ? (int)(collect($distribucionCuidados)->first(fn($i) => stripos($i['label'] ?? '', 'signo') !== false)['total'] ?? 0) : null,
                'higiene' => $totalDist > 0 ? (int)(collect($distribucionCuidados)->first(fn($i) => stripos($i['label'] ?? '', 'higien') !== false)['total'] ?? 0) : null,
                'movilizacion' => $totalDist > 0 ? (int)(collect($distribucionCuidados)->first(fn($i) => stripos($i['label'] ?? '', 'movil') !== false)['total'] ?? 0) : null,
            ];

            return [
                'modo' => 'EN_TURNO',
                'modo_label' => 'MI TURNO / ACTIVO',
                'submodo_label' => null,
                'es_modo_consulta' => false,
                'estado' => 'ACTIVA',
                'usuario' => [
                    'cod_usuario' => $user->cod_usuario,
                    'cod_personal' => $personal?->cod_personal,
                    'nombre_completo' => $nombreCompleto,
                    'nombres' => $primerNombre,
                    'rol' => $user->roles->first()?->name ?? 'ENFERMEROS',
                    'iniciales' => $iniciales,
                    'area_nombre' => $area?->nombre ?? 'Sector General',
                ],
                'jornada' => [
                    'cod_jornada' => $jornada->cod_jornada,
                    'nombre' => $nombreTurno,
                    'hora_inicio' => $turno?->hora_inicio ? Carbon::parse($turno->hora_inicio)->format('H:i') : '07:00',
                    'hora_fin' => $turno?->hora_cierre ? Carbon::parse($turno->hora_cierre)->format('H:i') : '15:00',
                    'activa' => true,
                    'fecha' => Carbon::parse($jornada->fecha_jornada)->format('Y-m-d'),
                    'fecha_humana' => ucfirst(Carbon::parse($jornada->fecha_jornada)->locale('es')->translatedFormat('l, d \d\e F \d\e Y')),
                    'area_nombre' => $area?->nombre ?? 'Sector General',
                ],
                'kpis' => $kpisData,
                'estado_general' => $estadoGeneral,
                'alerta_critica' => $alertaCritica ? [
                    'cod_alerta' => $alertaCritica->cod_alerta,
                    'residente_nombre' => $alertaCritica->residente?->nombre_completo ?? 'Residente asignado',
                    'ubicacion' => $this->formatearUbicacion($alertaCritica->residente),
                    'titulo' => $alertaCritica->titulo ?: ($alertaCritica->descripcion ?: 'Alerta crítica activa'),
                    'tiempo_relativo' => $alertaCritica->fecha_hora ? Carbon::parse($alertaCritica->fecha_hora)->diffForHumans() : 'Hace unos momentos',
                    'prioridad' => 'CRÍTICA',
                ] : null,
                'agenda' => array_slice($agendaUnificada, 0, 10),
                'agenda_hoy' => array_slice($agendaUnificada, 0, 10),
                'estado_tareas' => $progresoTurno,
                'progreso' => [
                    'completadas' => $progresoTurno['realizadas'],
                    'pendientes' => $progresoTurno['pendientes'],
                    'retrasadas' => $progresoTurno['retrasadas'],
                    'total' => $progresoTurno['total'],
                    'cumplimiento' => $progresoTurno['porcentaje'],
                ],
                'distribucion_cuidados' => $distribucionCuidados,
                'distribucion' => array_merge(
                    $distribucionMap,
                    collect($distribucionCuidados)->pluck('total', 'label')->toArray()
                ),
                'residentes' => $residentesCards,
                'alertas' => count($alertasRecientes),
                'alertas_recientes' => $alertasRecientes,
            ];
        }

        // =========================================================================
        // MODO 2: FUERA DE TURNO (MODO CONSULTA / SOLO LECTURA)
        // =========================================================================
        // NO usar asignaciones históricas del usuario autenticado como si fueran actuales.
        // Consultar las jornadas de Enfermería actualmente activas:
        // jornadas activas -> asignaciones_personal -> personal -> asignaciones_residente_jornada -> residentes
        $jornadasActivas = $this->resolverJornadasActivasSistema($momentoActual);

        if ($jornadasActivas->isEmpty()) {
            return [
                'modo' => 'FUERA_DE_TURNO',
                'modo_label' => 'FUERA DE TURNO',
                'submodo_label' => 'MODO CONSULTA / SOLO LECTURA',
                'es_modo_consulta' => true,
                'estado' => 'SIN_JORNADA_ACTIVA',
                'jornada' => null,
                'residentes' => [],
                'agenda' => [],
                'agenda_hoy' => [],
                'alertas' => 0,
                'alertas_recientes' => [],
                'progreso' => null,
                'alerta_critica' => null,
                'estado_tareas' => [
                    'realizadas' => null,
                    'pendientes' => null,
                    'retrasadas' => null,
                    'total' => 0,
                    'porcentaje' => null,
                ],
                'distribucion_cuidados' => [],
                'distribucion' => [
                    'medicacion' => null,
                    'signos' => null,
                    'higiene' => null,
                    'movilizacion' => null,
                ],
                'estado_general' => [
                    'badge' => 'MODO CONSULTA',
                    'titulo' => 'Sin turno activo en curso',
                    'mensaje' => 'No se registran jornadas activas de Enfermería en este momento.',
                    'tiempo' => $momentoActual->format('H:i \h\r\s'),
                    'tipo' => 'neutral',
                ],
                'kpis' => [
                    'total_registro' => ['numero' => 0, 'texto' => '0 alertas registradas'],
                    'criticas_altas' => ['numero' => 0, 'texto' => '0 alertas de alta prioridad'],
                    'por_atender' => ['numero' => 0, 'texto' => '0 abiertas sin atención'],
                    'en_atencion' => ['numero' => 0, 'texto' => '0 protocolos en curso'],
                    'resueltas' => ['numero' => 0, 'texto' => '0 resueltas e historial'],
                ],
                'usuario' => [
                    'cod_usuario' => $user->cod_usuario,
                    'cod_personal' => $personal?->cod_personal,
                    'nombre_completo' => $nombreCompleto,
                    'nombres' => $primerNombre,
                    'rol' => $user->roles->first()?->name ?? 'ENFERMEROS',
                    'iniciales' => $iniciales,
                    'area_nombre' => 'Modo Consulta',
                ],
            ];
        }

        $codJornadasActivas = $jornadasActivas->pluck('cod_jornada')->all();

        $asigPersonalActivas = AsignacionPersonal::query()
            ->whereIn('cod_jornada', $codJornadasActivas)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->with(['personal', 'area'])
            ->get();

        $codPersonalActivos = $asigPersonalActivas->pluck('cod_personal')->filter()->unique()->values()->all();

        $asigResidentesActivas = AsignacionResidenteJornada::query()
            ->whereIn('cod_jornada', $codJornadasActivas)
            ->whereIn('cod_personal', $codPersonalActivos)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
            ->with([
                'personal.usuario.roles',
                'jornada.turno',
                'residente.planesCuidado.intervenciones',
                'residente.ocupacionesCama.cama.habitacion',
            ])
            ->get();

        $residentesAgrupados = $asigResidentesActivas->groupBy('cod_residente');
        $residentesCards = [];
        $codResidentes = [];

        foreach ($residentesAgrupados as $codRes => $asigs) {
            $residente = $asigs->first()?->residente;
            if (!$residente) continue;

            $codResidentes[] = $residente->cod_residente;

            // 3. RESPONSABLE DE ENFERMERIA REAL:
            // Autoridad estricta: Rol Spatie ENFERMEROS del usuario vinculado.
            // NO utilizar funcion, profesion o cargo como sustituto del rol.
            $responsables = [];
            foreach ($asigs as $asig) {
                if ($asig->personal) {
                    $usuario = $asig->personal->usuario;
                    $esEnfermero = $usuario && method_exists($usuario, 'hasRole') && $usuario->hasRole('ENFERMEROS');

                    if ($esEnfermero) {
                        $nom = trim($asig->personal->nombres . ' ' . $asig->personal->apellido_paterno);
                        $responsables[$asig->personal->cod_personal] = "Enf. {$nom}";
                    }
                }
            }
            $responsableTexto = !empty($responsables)
                ? 'A cargo de: ' . implode(', ', array_values($responsables))
                : 'Sin enfermero asignado';

            $rawTurnoNombre = $asigs->first()?->jornada?->turno?->nombre;
            $turnoNombre = $rawTurnoNombre
                ? (str_starts_with(strtolower(trim($rawTurnoNombre)), 'turno') ? trim($rawTurnoNombre) : "Turno {$rawTurnoNombre}")
                : 'Turno activo';

            $supervisionRaw = strtoupper(trim((string)($asigs->pluck('nivel_supervision')->filter()->first() ?? '')));
            $supervisionLabel = match($supervisionRaw) {
                'BAJO', 'BAJA' => 'Supervisión baja',
                'ALTO', 'ALTA', 'DIRECTA' => 'Supervisión alta',
                'ESTANDAR', 'ESTÁNDAR', 'MEDIA' => 'Supervisión media',
                default => $supervisionRaw ? "Supervisión {$supervisionRaw}" : 'Supervisión no especificada',
            };

            $edadStr = $residente->fecha_nacimiento
                ? Carbon::parse($residente->fecha_nacimiento)->age . ' años'
                : 'Edad no registrada';

            $movilidadLabel = 'Movilidad asistida';
            $plan = $residente->planesCuidado?->first();
            if ($plan && !empty($plan->objetivo_general)) {
                $obj = strtolower($plan->objetivo_general);
                if (str_contains($obj, 'independien') || str_contains($obj, 'autonom')) {
                    $movilidadLabel = 'Autónomo';
                } elseif (str_contains($obj, 'baston') || str_contains($obj, 'andador') || str_contains($obj, 'dispositiv')) {
                    $movilidadLabel = 'Usa dispositivo';
                }
            }

            $residentesCards[] = [
                'cod_residente' => $residente->cod_residente,
                'nombre_completo' => $residente->nombre_completo,
                'edad' => $edadStr,
                'ubicacion' => $this->formatearUbicacion($residente),
                'iniciales' => $this->extraerIniciales($residente->nombre_completo),
                'foto' => $residente->foto,
                'estado_seguimiento' => 'ESTABLE',
                'estado_institucional' => strtoupper(trim((string)($residente->estado ?: 'ACTIVO'))),
                'estado_label' => 'ESTABLE',
                'estado_operacional' => strtoupper(trim((string)($residente->estado ?: 'ACTIVO'))),
                'supervision_label' => $supervisionLabel,
                'movilidad_label' => $movilidadLabel,
                'alertas_count' => 0,
                'responsable_texto' => $responsableTexto,
                'responsables' => array_values($responsables),
                'turno_actual' => $turnoNombre,
            ];
        }

        $jornadaPrincipal = $jornadasActivas->first();
        $turnoPrincipal = $jornadaPrincipal->turno;
        $areaPrincipal = $asigPersonalActivas->first()?->area;
        [$inicioTurno, $finTurno] = $this->calcularVentanaTurno($jornadaPrincipal);

        $alertas = $this->resolverAlertasTurno($codResidentes, $inicioTurno, $finTurno);
        $kpisData = $this->calcularKpisAlertas($alertas);
        $alertaCritica = $alertas->first(function ($a) {
            $p = strtoupper(trim((string)$a->prioridad));
            $est = strtoupper(trim((string)$a->estado));
            return in_array($p, self::PRIORIDADES_CRITICAS, true) && in_array($est, self::ESTADOS_ALERTA_ACTIVOS, true);
        });

        foreach ($residentesCards as &$card) {
            $alertasRes = $alertas->where('cod_residente', $card['cod_residente']);
            $card['alertas_count'] = $alertasRes->filter(fn($a) => in_array(strtoupper(trim((string)$a->estado)), self::ESTADOS_ALERTA_ACTIVOS, true))->count();
            $tieneCritica = $alertasRes->contains(fn($a) => in_array(strtoupper(trim((string)$a->prioridad)), self::PRIORIDADES_CRITICAS, true) && in_array(strtoupper(trim((string)$a->estado)), self::ESTADOS_ALERTA_ACTIVOS, true));
            $estadoSeg = $tieneCritica ? 'CRÍTICO' : ($card['alertas_count'] > 0 ? 'VIGILANCIA' : 'ESTABLE');
            $card['estado_seguimiento'] = $estadoSeg;
            $card['estado_label'] = $estadoSeg;
        }
        unset($card);

        $agendaUnificada = $this->resolverAgendaProgramada($codResidentes, $jornadaPrincipal, $inicioTurno, $finTurno, $momentoActual);
        $progresoTurno = $this->calcularProgresoTurno($agendaUnificada);
        $estadoGeneral = $this->calcularEstadoGeneral($alertas, (int)($progresoTurno['retrasadas'] ?? 0), $alertaCritica, $momentoActual);

        if (!$alertaCritica && ($progresoTurno['retrasadas'] ?? 0) === 0) {
            $estadoGeneral['titulo'] = 'Turno en curso estable (Modo Consulta)';
            $estadoGeneral['mensaje'] = 'Los residentes del turno activo se encuentran estables y con tareas en curso.';
        }

        $distribucionCuidados = $this->resolverDistribucionCuidados($codResidentes, $agendaUnificada);
        $totalDist = (int)(collect($distribucionCuidados)->sum('total'));
        $distribucionMap = [
            'medicacion' => $totalDist > 0 ? (int)(collect($distribucionCuidados)->firstWhere('label', 'Medicación')['total'] ?? (collect($distribucionCuidados)->first(fn($i) => stripos($i['label'] ?? '', 'medic') !== false)['total'] ?? 0)) : null,
            'signos' => $totalDist > 0 ? (int)(collect($distribucionCuidados)->first(fn($i) => stripos($i['label'] ?? '', 'signo') !== false)['total'] ?? 0) : null,
            'higiene' => $totalDist > 0 ? (int)(collect($distribucionCuidados)->first(fn($i) => stripos($i['label'] ?? '', 'higien') !== false)['total'] ?? 0) : null,
            'movilizacion' => $totalDist > 0 ? (int)(collect($distribucionCuidados)->first(fn($i) => stripos($i['label'] ?? '', 'movil') !== false)['total'] ?? 0) : null,
        ];

        $alertasRecientes = $this->formatearAlertasRecientes($alertas);

        return [
            'modo' => 'FUERA_DE_TURNO',
            'modo_label' => 'FUERA DE TURNO',
            'submodo_label' => 'MODO CONSULTA / SOLO LECTURA',
            'es_modo_consulta' => true,
            'estado' => 'ACTIVA',
            'usuario' => [
                'cod_usuario' => $user->cod_usuario,
                'cod_personal' => $personal?->cod_personal,
                'nombre_completo' => $nombreCompleto,
                'nombres' => $primerNombre,
                'rol' => $user->roles->first()?->name ?? 'ENFERMEROS',
                'iniciales' => $iniciales,
                'area_nombre' => $areaPrincipal?->nombre ?? 'Modo Consulta',
            ],
            'jornada' => [
                'cod_jornada' => $jornadaPrincipal->cod_jornada,
                'nombre' => $turnoPrincipal?->nombre ? (str_starts_with(strtolower(trim($turnoPrincipal->nombre)), 'turno') ? trim($turnoPrincipal->nombre) : "Turno {$turnoPrincipal->nombre}") : "Jornada {$jornadaPrincipal->cod_jornada}",
                'hora_inicio' => $turnoPrincipal?->hora_inicio ? Carbon::parse($turnoPrincipal->hora_inicio)->format('H:i') : '07:00',
                'hora_fin' => $turnoPrincipal?->hora_cierre ? Carbon::parse($turnoPrincipal->hora_cierre)->format('H:i') : '15:00',
                'activa' => true,
                'fecha' => Carbon::parse($jornadaPrincipal->fecha_jornada)->format('Y-m-d'),
                'fecha_humana' => ucfirst(Carbon::parse($jornadaPrincipal->fecha_jornada)->locale('es')->translatedFormat('l, d \d\e F \d\e Y')),
                'area_nombre' => $areaPrincipal?->nombre ?? 'Sector General',
            ],
            'kpis' => $kpisData,
            'estado_general' => $estadoGeneral,
            'alerta_critica' => $alertaCritica ? [
                'cod_alerta' => $alertaCritica->cod_alerta,
                'residente_nombre' => $alertaCritica->residente?->nombre_completo ?? 'Residente asignado',
                'ubicacion' => $this->formatearUbicacion($alertaCritica->residente),
                'titulo' => $alertaCritica->titulo ?: ($alertaCritica->descripcion ?: 'Alerta crítica activa'),
                'tiempo_relativo' => $alertaCritica->fecha_hora ? Carbon::parse($alertaCritica->fecha_hora)->diffForHumans() : 'Hace unos momentos',
                'prioridad' => 'CRÍTICA',
            ] : null,
            'agenda' => array_slice($agendaUnificada, 0, 10),
            'agenda_hoy' => array_slice($agendaUnificada, 0, 10),
            'estado_tareas' => $progresoTurno,
            'progreso' => [
                'completadas' => $progresoTurno['realizadas'],
                'pendientes' => $progresoTurno['pendientes'],
                'retrasadas' => $progresoTurno['retrasadas'],
                'total' => $progresoTurno['total'],
                'cumplimiento' => $progresoTurno['porcentaje'],
            ],
            'distribucion_cuidados' => $distribucionCuidados,
            'distribucion' => array_merge(
                $distribucionMap,
                collect($distribucionCuidados)->pluck('total', 'label')->toArray()
            ),
            'residentes' => $residentesCards,
            'alertas' => count($alertasRecientes),
            'alertas_recientes' => $alertasRecientes,
        ];
    }

        public function validarSeguridadUsuario(User $user): void
    {
        if ($user->estado !== 'ACTIVO') {
            throw new HttpException(403, 'Usuario inactivo en el sistema.');
        }

        if (!$user->hasRole('ENFERMEROS') && !$user->hasRole('SUPERADMINISTRADOR')) {
            throw new HttpException(403, 'Acceso denegado: El módulo de Mi turno requiere rol asistencial.');
        }
    }

    /**
     * Valida que un residente esté efectivamente asignado a la enfermera autenticada en su jornada activa.
     */
    public function autorizarAccesoResidente(User $user, string $codResidente): bool
    {
        $this->validarSeguridadUsuario($user);

        if ($user->hasRole('SUPERADMINISTRADOR')) {
            return true;
        }

        $personal = Personal::query()
            ->where('cod_usuario', $user->cod_usuario)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->first();

        if (!$personal) {
            throw new HttpException(403, 'Personal no registrado.');
        }

        $jornada = $this->resolverJornadaActual($personal);
        if (!$jornada) {
            throw new HttpException(403, 'Acceso denegado: No tienes una jornada activa en este momento.');
        }

        $asignado = AsignacionResidenteJornada::query()
            ->where('cod_jornada', $jornada->cod_jornada)
            ->where('cod_personal', $personal->cod_personal)
            ->where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
            ->exists();

        if (!$asignado) {
            throw new HttpException(403, 'Acceso denegado: El residente no está asignado a tu guardia actual.');
        }

        return true;
    }

    /**
     * Resuelve la jornada activa para el profesional autenticado mediante:
     * personal -> asignaciones_personal -> jornadas -> turnos
     *
     * Valida estrictamente:
     * - Jornada asignada al profesional con asignación activa.
     * - Estado real vigente de la jornada (ACTIVA, ABIERTA, EN_CURSO, ACTIVO).
     * - Fecha correspondiente.
     * - Hora actual dentro del turno.
     * - Soporta turnos nocturnos donde hora_cierre < hora_inicio.
     *
     * Si no existe una jornada válida, retorna NULL. CERO fallback a jornadas anteriores o futuras.
     */
    /**
     * Resuelve todas las jornadas de enfermería actualmente activas en el centro:
     * - Jornadas con estado vigente (ACTIVA, ABIERTA, EN_CURSO, ACTIVO).
     * - Fecha correspondiente.
     * - Hora actual dentro de la ventana del turno (soporta nocturnos).
     */
    public function resolverJornadasActivasSistema(?Carbon $momentoActual = null): Collection
    {
        $ahora = $momentoActual ?? Carbon::now();

        return Jornada::query()
            ->whereIn('estado', ['ACTIVA', 'ABIERTA', 'EN_CURSO', 'ACTIVO'])
            ->with(['turno', 'asignacionesPersonal.personal.usuario'])
            ->get()
            ->filter(function (Jornada $jornada) use ($ahora) {
                if (!$jornada->turno || empty($jornada->turno->hora_inicio)) {
                    return false;
                }
                [$inicioTurno, $finTurno] = $this->calcularVentanaTurno($jornada);
                return $ahora->gte($inicioTurno) && $ahora->lte($finTurno);
            })
            ->values();
    }

    public function resolverJornadaActual(?Personal $personal, ?Carbon $momentoActual = null): ?Jornada
    {
        if (!$personal) {
            return null;
        }

        $ahora = $momentoActual ?? Carbon::now();

        // 1. Asignaciones activas del profesional
        $asignaciones = AsignacionPersonal::query()
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->with(['jornada.turno', 'area'])
            ->get();

        foreach ($asignaciones as $asig) {
            $jornada = $asig->jornada;
            if (!$jornada) {
                continue;
            }

            // 2. Estado real vigente de la jornada
            $estadoJornada = strtoupper(trim((string)$jornada->estado));
            if (!in_array($estadoJornada, ['ACTIVA', 'ABIERTA', 'EN_CURSO', 'ACTIVO'], true)) {
                continue;
            }

            $turno = $jornada->turno;
            if (!$turno || empty($turno->hora_inicio)) {
                continue;
            }

            // 3. Ventana horaria (soportando nocturnos)
            [$inicioTurno, $finTurno] = $this->calcularVentanaTurno($jornada);

            if ($ahora->gte($inicioTurno) && $ahora->lte($finTurno)) {
                return $jornada;
            }
        }

        // Cero fallback: si no hay jornada activa válida para el profesional en este momento, retorna null
        return null;
    }

    /**
     * Calcula la ventana de inicio y fin para una jornada y su turno (soporta nocturnos).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function calcularVentanaTurno(Jornada $jornada): array
    {
        $turno = $jornada->turno;
        $fechaJornadaStr = Carbon::parse($jornada->fecha_jornada)->format('Y-m-d');
        $horaInicio = trim((string)($turno?->hora_inicio ?? '07:00:00'));
        $horaCierre = trim((string)($turno?->hora_cierre ?: ($turno?->hora_fin ?? '15:00:00')));

        $esNocturno = $horaCierre < $horaInicio;

        $inicioTurno = Carbon::parse("{$fechaJornadaStr} {$horaInicio}");
        $finTurno = $esNocturno
            ? Carbon::parse("{$fechaJornadaStr} {$horaCierre}")->addDay()
            : Carbon::parse("{$fechaJornadaStr} {$horaCierre}");

        return [$inicioTurno, $finTurno];
    }

    /**
     * Resuelve el área institucional asociada a la enfermera en su jornada.
     */
    private function resolverAreaPersonal(?Personal $personal, ?Jornada $jornada): ?Area
    {
        if (!$personal || !$jornada) return null;

        $asig = AsignacionPersonal::query()
            ->where('cod_personal', $personal->cod_personal)
            ->where('cod_jornada', $jornada->cod_jornada)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->with('area')
            ->first();

        return $asig?->area;
    }

    /**
     * Obtiene los residentes asignados a la enfermera en la jornada activa.
     */
    private function resolverAsignacionesResidentes(?Personal $personal, ?Jornada $jornada, bool $esSuperAdmin): Collection
    {
        if (!$personal || !$jornada) {
            return collect();
        }

        $query = AsignacionResidenteJornada::query()
            ->where('cod_jornada', $jornada->cod_jornada)
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
            ->with([
                'residente.planesCuidado.intervenciones',
                'residente.ocupacionesCama.cama.habitacion',
            ]);

        return $query->get();
    }

    /**
     * Obtiene las alertas relevantes para el turno actual.
     * NO cuenta todo el historial:
     * - Alertas que estaban activas al inicio del turno (estado ABIERTA o EN_ATENCION).
     * - Alertas creadas durante la jornada (fecha_hora entre inicioTurno y finTurno).
     * - Alertas resueltas durante este turno (evento de cierre en este turno).
     * - Excluye alertas cerradas antes del inicio del turno.
     */
    private function resolverAlertasTurno(array $codResidentes, Carbon $inicioTurno, Carbon $finTurno): Collection
    {
        if (empty($codResidentes)) {
            return collect();
        }

        return Alerta::query()
            ->whereIn('cod_residente', $codResidentes)
            ->where(function ($q) use ($inicioTurno, $finTurno) {
                // 1. Alertas actualmente activas (estaban activas al inicio o creadas durante el turno)
                $q->whereIn('estado', self::ESTADOS_ALERTA_ACTIVOS)
                  // 2. Alertas creadas durante este turno
                  ->orWhereBetween('fecha_hora', [$inicioTurno, $finTurno])
                  // 3. Alertas cerradas pero cuyo cierre ocurrió durante este turno
                  ->orWhere(function ($sq) use ($inicioTurno, $finTurno) {
                      $sq->whereIn('estado', self::ESTADOS_ALERTA_RESUELTOS)
                         ->whereExists(function ($eq) use ($inicioTurno, $finTurno) {
                             $eq->selectRaw('1')
                                ->from('eventos_alerta')
                                ->whereColumn('eventos_alerta.cod_alerta', 'alertas.cod_alerta')
                                ->whereIn('tipo_evento', ['CIERRE', 'RESOLUCION', 'ATENCION'])
                                ->whereBetween('fecha_hora', [$inicioTurno, $finTurno]);
                         });
                  });
            })
            ->with(['residente.ocupacionesCama.cama.habitacion'])
            ->orderByDesc('fecha_hora')
            ->get();
    }

    /**
     * Construye la agenda unificada a partir de la PROGRAMACIÓN (lo que DEBE ocurrir).
     *
     * MEDICACIÓN:
     * prescripciones -> horarios_prescripcion -> administraciones_medicacion
     *
     * CUIDADOS:
     * planes_cuidado -> intervenciones_cuidado -> programaciones_cuidado -> ejecuciones_cuidado
     * (Relacionando ejecución por cod_intervencion + cod_residente + cod_jornada + fecha_hora_programada)
     */
    public function resolverAgendaProgramada(
        array $codResidentes,
        Jornada $jornada,
        Carbon $inicioTurno,
        Carbon $finTurno,
        Carbon $momentoActual,
        array $fechasAsignacionResidentes = []
    ): array {
        if (empty($codResidentes)) {
            return [];
        }

        $fechaJornadaStr = Carbon::parse($jornada->fecha_jornada)->format('Y-m-d');
        $agenda = [];

        // -------------------------------------------------------------
        // A. MEDICACIÓN PROGRAMADA
        // -------------------------------------------------------------
        $prescripciones = Prescripcion::query()
            ->whereIn('cod_residente', $codResidentes)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])
            ->where(function ($q) use ($inicioTurno) {
                $q->whereNull('fecha_hora_suspension')
                  ->orWhere('fecha_hora_suspension', '>', $inicioTurno);
            })
            ->with([
                'horarios' => fn($q) => $q->whereIn('estado', ['ACTIVO', 'ACTIVA']),
                'medicamento',
                'residente.ocupacionesCama.cama.habitacion',
            ])
            ->get();

        // Obtener administraciones ya registradas para esta jornada y residentes
        $administraciones = AdministracionMedicacion::query()
            ->whereIn('cod_residente', $codResidentes)
            ->where(function ($q) use ($jornada, $inicioTurno, $finTurno) {
                $q->where('cod_jornada', $jornada->cod_jornada)
                  ->orWhereBetween('fecha_hora_programada', [$inicioTurno, $finTurno]);
            })
            ->get();

        foreach ($prescripciones as $prescripcion) {
            // Regla: segun_necesidad = true no crea pendiente automático
            if (!empty($prescripcion->segun_necesidad)) {
                continue;
            }

            $nombreMed = $prescripcion->medicamento?->nombre_comercial
                ?: ($prescripcion->medicamento?->nombre_generico ?: 'Medicamento');
            $dosisCompleta = trim("{$prescripcion->dosis} {$prescripcion->unidad_dosis}");

            foreach ($prescripcion->horarios as $horario) {
                // Regla: horario inactivo no genera tarea
                if (!in_array(strtoupper(trim((string)$horario->estado)), ['ACTIVO', 'ACTIVA'], true)) {
                    continue;
                }

                if (empty($horario->hora_programada)) {
                    continue;
                }

                $horaStr = Carbon::parse($horario->hora_programada)->format('H:i');

                // Resolver fecha exacta de la hora programada en esta guardia
                $fechaHoraProg = $this->resolverMomentoProgramado($fechaJornadaStr, $horario->hora_programada, $inicioTurno, $finTurno);

                // Si la hora programada no cae dentro de esta guardia, continuar
                if (!$fechaHoraProg || $fechaHoraProg->lt($inicioTurno) || $fechaHoraProg->gt($finTurno)) {
                    continue;
                }

                // Regla: Si el residente se asigna al enfermero a mitad del turno,
                // no atribuir tareas anteriores a fecha_hora de la asignación como responsabilidad del nuevo enfermero
                if (isset($fechasAsignacionResidentes[$prescripcion->cod_residente])) {
                    $fechaAsig = $fechasAsignacionResidentes[$prescripcion->cod_residente];
                    if ($fechaAsig && $fechaHoraProg->lt($fechaAsig)) {
                        continue;
                    }
                }

                // Regla: prescripción todavía no iniciada no genera tarea (fecha de prescripción o inicio futura)
                $fechaPrescDate = !empty($prescripcion->fecha_hora_prescripcion)
                    ? Carbon::parse($prescripcion->fecha_hora_prescripcion)->toDateString()
                    : null;
                $fechaInicioDate = !empty($prescripcion->fecha_inicio)
                    ? Carbon::parse($prescripcion->fecha_inicio)->toDateString()
                    : null;

                if ($fechaInicioDate && $fechaInicioDate > $fechaHoraProg->toDateString()) {
                    continue;
                }
                if ($fechaPrescDate && $fechaPrescDate > $fechaHoraProg->toDateString()) {
                    continue;
                }

                // Regla: prescripción suspendida antes del horario no genera tarea
                if (!empty($prescripcion->fecha_hora_suspension) && Carbon::parse($prescripcion->fecha_hora_suspension)->lte($fechaHoraProg)) {
                    continue;
                }

                // Regla: dias_semana incorrecto no genera tarea
                if (!empty($horario->dias_semana) && !$this->correspondeDiaSemana($horario->dias_semana, $fechaHoraProg)) {
                    continue;
                }

                // Reglas de administración:
                // - cod_residente de administración debe coincidir con la prescripción
                // - dos horarios de una misma prescripción son dos ocurrencias diferentes
                // - administrar una no completa la otra
                $adminReal = $administraciones->first(function ($adm) use ($prescripcion, $horario, $fechaHoraProg) {
                    if ($adm->cod_residente !== $prescripcion->cod_residente) {
                        return false;
                    }
                    if ($adm->cod_prescripcion !== $prescripcion->cod_prescripcion) {
                        return false;
                    }
                    if (!empty($adm->cod_horario_prescripcion)) {
                        return $adm->cod_horario_prescripcion === $horario->cod_horario_prescripcion;
                    }
                    if ($adm->fecha_hora_programada) {
                        return abs(Carbon::parse($adm->fecha_hora_programada)->diffInMinutes($fechaHoraProg)) <= 30;
                    }
                    return false;
                });

                // Determinar estado de la tarea programada
                $estado = 'PENDIENTE';
                $detalleOmitida = null;

                if ($adminReal) {
                    $resAdm = strtoupper(trim((string)$adminReal->resultado));
                    $estAdm = strtoupper(trim((string)$adminReal->estado));

                    if (in_array($resAdm, ['OMITIDA', 'OMITIDO', 'RECHAZADA', 'RECHAZADO'], true) || $estAdm === 'OMITIDA' || !empty($adminReal->motivo_omision)) {
                        $estado = 'REALIZADO';
                        $detalleOmitida = $adminReal->motivo_omision ?: 'Omitida justificadamente';
                    } elseif (in_array($resAdm, ['ADMINISTRADA', 'ADMINISTRADO', 'REALIZADA'], true) || $estAdm === 'REALIZADA' || $estAdm === 'REGISTRADA') {
                        $estado = 'REALIZADO';
                    } else {
                        $estado = 'REALIZADO';
                    }
                } else {
                    if ($momentoActual->gt($fechaHoraProg->copy()->addMinutes(60))) {
                        $estado = 'RETRASADO';
                    } elseif ($momentoActual->diffInMinutes($fechaHoraProg, false) <= 60 && $fechaHoraProg->isFuture()) {
                        $estado = 'PRÓXIMO';
                    }
                }

                $claveMed = "MED_{$prescripcion->cod_prescripcion}_{$horario->cod_horario_prescripcion}_" . $fechaHoraProg->format('YmdHi');
                $agenda[$claveMed] = [
                    'hora' => $horaStr,
                    'momento' => $fechaHoraProg->timestamp,
                    'tipo' => 'MEDICACION',
                    'icono' => 'ph-pill',
                    'accion' => "{$nombreMed} ({$dosisCompleta})",
                    'residente' => $prescripcion->residente?->nombre_completo ?? 'Residente asignado',
                    'ubicacion' => $this->formatearUbicacion($prescripcion->residente),
                    'cod_residente' => $prescripcion->cod_residente,
                    'estado' => $estado,
                    'detalle_omision' => $detalleOmitida,
                ];
            }
        }

        // -------------------------------------------------------------
        // B. CUIDADOS PROGRAMADOS
        // -------------------------------------------------------------
        $planes = PlanCuidado::query()
            ->whereIn('cod_residente', $codResidentes)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
            ->with([
                'intervenciones' => function ($q) use ($jornada) {
                    $q->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
                      ->with(['programaciones' => function ($pq) use ($jornada) {
                          $pq->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
                             ->where(function ($sq) use ($jornada) {
                                 $sq->whereNull('cod_turno')
                                    ->orWhere('cod_turno', $jornada->cod_turno);
                             });
                      }]);
                },
                'residente.ocupacionesCama.cama.habitacion',
            ])
            ->get();

        // Obtener ejecuciones de cuidado para esta guardia
        $ejecuciones = EjecucionCuidado::query()
            ->whereIn('cod_residente', $codResidentes)
            ->where(function ($q) use ($jornada, $inicioTurno, $finTurno) {
                $q->where('cod_jornada', $jornada->cod_jornada)
                  ->orWhereBetween('fecha_hora_programada', [$inicioTurno, $finTurno])
                  ->orWhereBetween('fecha_hora_ejecucion', [$inicioTurno, $finTurno]);
            })
            ->get();

        foreach ($planes as $plan) {
            // Regla: Plan vigente (y fecha de cierre si existe posterior)
            if (!in_array(strtoupper(trim((string)$plan->estado)), ['ACTIVO', 'ACTIVA', 'VIGENTE'], true)) {
                continue;
            }
            if (!empty($plan->fecha_hora_cierre) && Carbon::parse($plan->fecha_hora_cierre)->lt($inicioTurno)) {
                continue;
            }

            $tipoPlan = strtoupper(trim((string)$plan->tipo_plan ?: 'CUIDADO'));
            $icono = match(true) {
                str_contains($tipoPlan, 'SIGNO') || str_contains($tipoPlan, 'VITAL') => 'ph-heartbeat',
                str_contains($tipoPlan, 'HIGIENE') => 'ph-drop',
                str_contains($tipoPlan, 'MOVIL') => 'ph-arrows-clockwise',
                default => 'ph-stethoscope',
            };

            foreach ($plan->intervenciones as $intervencion) {
                // Regla: Intervención vigente
                if (!in_array(strtoupper(trim((string)$intervencion->estado)), ['ACTIVO', 'ACTIVA', 'VIGENTE'], true)) {
                    continue;
                }

                foreach ($intervencion->programaciones as $programacion) {
                    // Regla: Programación vigente
                    if (!in_array(strtoupper(trim((string)$programacion->estado)), ['ACTIVO', 'ACTIVA', 'VIGENTE'], true)) {
                        continue;
                    }

                    // Regla: No interpretar frecuencia textual para inventar horarios (debe tener hora_programada)
                    if (empty($programacion->hora_programada)) {
                        continue;
                    }

                    // Regla: Corresponde a cod_turno cuando esté definido
                    if (!empty($programacion->cod_turno) && $programacion->cod_turno !== $jornada->cod_turno) {
                        continue;
                    }

                    $horaStr = Carbon::parse($programacion->hora_programada)->format('H:i');
                    $fechaHoraProg = $this->resolverMomentoProgramado($fechaJornadaStr, $programacion->hora_programada, $inicioTurno, $finTurno);

                    if (!$fechaHoraProg || $fechaHoraProg->lt($inicioTurno) || $fechaHoraProg->gt($finTurno)) {
                        continue;
                    }

                    // Regla: Si el residente se asigna a mitad del turno, no atribuir tareas anteriores a fecha_hora de la asignación
                    if (isset($fechasAsignacionResidentes[$plan->cod_residente])) {
                        $fechaAsig = $fechasAsignacionResidentes[$plan->cod_residente];
                        if ($fechaAsig && $fechaHoraProg->lt($fechaAsig)) {
                            continue;
                        }
                    }

                    $fechaProgDate = $fechaHoraProg->toDateString();

                    // Regla: fecha >= fecha_activacion
                    if (!empty($programacion->fecha_activacion) && $fechaProgDate < Carbon::parse($programacion->fecha_activacion)->toDateString()) {
                        continue;
                    }

                    // Regla: fecha <= fecha_desactivacion cuando exista
                    if (!empty($programacion->fecha_desactivacion) && $fechaProgDate > Carbon::parse($programacion->fecha_desactivacion)->toDateString()) {
                        continue;
                    }

                    // Regla: Corresponde a dias_semana
                    if (!empty($programacion->dias_semana) && !$this->correspondeDiaSemana($programacion->dias_semana, $fechaHoraProg)) {
                        continue;
                    }

                    // Relacionar ejecución con tarea programada
                    $ejecReal = $ejecuciones->first(function ($ej) use ($intervencion, $plan, $jornada, $fechaHoraProg) {
                        if ($ej->cod_intervencion !== $intervencion->cod_intervencion) {
                            return false;
                        }
                        if ($ej->cod_residente !== $plan->cod_residente) {
                            return false;
                        }
                        if ($ej->cod_jornada === $jornada->cod_jornada) {
                            return true;
                        }
                        if ($ej->fecha_hora_programada) {
                            return abs(Carbon::parse($ej->fecha_hora_programada)->diffInMinutes($fechaHoraProg)) <= 30;
                        }
                        return false;
                    });

                    $estado = 'PENDIENTE';
                    $detalleOmitida = null;

                    if ($ejecReal) {
                        $resEj = strtoupper(trim((string)$ejecReal->resultado));
                        $estEj = strtoupper(trim((string)$ejecReal->estado));

                        if (in_array($resEj, ['OMITIDA', 'NO_REALIZADA'], true) || $estEj === 'OMITIDA' || !empty($ejecReal->motivo_omision)) {
                            $estado = 'REALIZADO';
                            $detalleOmitida = $ejecReal->motivo_omision ?: 'Cuidado omitido justificadamente';
                        } elseif (in_array($resEj, ['REALIZADA', 'COMPLETADA'], true) || $estEj === 'REALIZADA' || $estEj === 'FINALIZADA') {
                            $estado = 'REALIZADO';
                        } else {
                            $estado = 'REALIZADO';
                        }
                    } else {
                        if ($momentoActual->gt($fechaHoraProg->copy()->addMinutes(60))) {
                            $estado = 'RETRASADO';
                        } elseif ($momentoActual->diffInMinutes($fechaHoraProg, false) <= 60 && $fechaHoraProg->isFuture()) {
                            $estado = 'PRÓXIMO';
                        }
                    }

                    $codProgId = $programacion->cod_programacion ?: ($programacion->cod_programacion_cuidado ?: 'PROG');
                    $claveCuidado = "CUID_{$codProgId}_{$plan->cod_residente}_" . $fechaHoraProg->format('YmdHi');
                    if (isset($agenda[$claveCuidado])) {
                        continue;
                    }
                    $agenda[$claveCuidado] = [
                        'hora' => $horaStr,
                        'momento' => $fechaHoraProg->timestamp,
                        'tipo' => $tipoPlan,
                        'icono' => $icono,
                        'accion' => $intervencion->nombre ?: $plan->nombre,
                        'residente' => $plan->residente?->nombre_completo ?? 'Residente asignado',
                        'ubicacion' => $this->formatearUbicacion($plan->residente),
                        'cod_residente' => $plan->cod_residente,
                        'estado' => $estado,
                        'detalle_omision' => $detalleOmitida,
                    ];
                }
            }
        }

        // Orden determinista cuando dos tareas tienen la misma hora
        $agendaArray = array_values($agenda);
        usort($agendaArray, function ($a, $b) {
            if ($a['momento'] !== $b['momento']) {
                return $a['momento'] <=> $b['momento'];
            }
            $cmpTipo = strcmp($a['tipo'], $b['tipo']);
            if ($cmpTipo !== 0) {
                return $cmpTipo;
            }
            $cmpAccion = strcmp($a['accion'], $b['accion']);
            if ($cmpAccion !== 0) {
                return $cmpAccion;
            }
            return strcmp($a['residente'], $b['residente']);
        });

        return $agendaArray;
    }

    /**
     * Resuelve el momento DateTime de una hora programada considerando guardias nocturnas.
     */
    public function resolverMomentoProgramado(string $fechaBaseStr, string $horaProgramadaStr, Carbon $inicioTurno, Carbon $finTurno): ?Carbon
    {
        $horaFormateada = Carbon::parse($horaProgramadaStr)->format('H:i:s');
        $candidatoMismoDia = Carbon::parse("{$fechaBaseStr} {$horaFormateada}");

        if ($candidatoMismoDia->gte($inicioTurno) && $candidatoMismoDia->lte($finTurno)) {
            return $candidatoMismoDia;
        }

        // Si es turno nocturno que cruza medianoche, probar en el día siguiente
        $candidatoDiaSiguiente = $candidatoMismoDia->copy()->addDay();
        if ($candidatoDiaSiguiente->gte($inicioTurno) && $candidatoDiaSiguiente->lte($finTurno)) {
            return $candidatoDiaSiguiente;
        }

        return null;
    }

    /**
     * Calcula los KPIs de alertas del turno.
     * Solo considera alertas activas al inicio o creadas durante este turno.
     */
    private function calcularKpisAlertas(Collection $alertas): array
    {
        $total = $alertas->count();

        $criticasAltas = $alertas->filter(function ($a) {
            $p = strtoupper(trim((string)$a->prioridad));
            return in_array($p, array_merge(self::PRIORIDADES_CRITICAS, self::PRIORIDADES_ALTAS), true);
        })->count();

        $porAtender = $alertas->filter(function ($a) {
            return strtoupper(trim((string)$a->estado)) === 'ABIERTA';
        })->count();

        $enAtencion = $alertas->filter(function ($a) {
            return strtoupper(trim((string)$a->estado)) === 'EN_ATENCION';
        })->count();

        $resueltas = $alertas->filter(function ($a) {
            return in_array(strtoupper(trim((string)$a->estado)), self::ESTADOS_ALERTA_RESUELTOS, true);
        })->count();

        return [
            'total_registro' => [
                'numero' => $total,
                'texto' => $total === 1 ? '1 alerta registrada' : "{$total} alertas registradas",
            ],
            'criticas_altas' => [
                'numero' => $criticasAltas,
                'texto' => $criticasAltas === 1 ? '1 alerta de alta prioridad' : "{$criticasAltas} alertas de alta prioridad",
            ],
            'por_atender' => [
                'numero' => $porAtender,
                'texto' => $porAtender === 1 ? '1 abierta sin atención' : "{$porAtender} abiertas sin atención",
            ],
            'en_atencion' => [
                'numero' => $enAtencion,
                'texto' => $enAtencion === 1 ? '1 protocolo en curso' : "{$enAtencion} protocolos en curso",
            ],
            'resueltas' => [
                'numero' => $resueltas,
                'texto' => $resueltas === 1 ? '1 resuelta en el turno' : "{$resueltas} resueltas en el turno",
            ],
        ];
    }

    /**
     * Calcula el Estado General del turno considerando:
     * - Alertas críticas activas.
     * - Alertas relevantes activas (altas y medias).
     * - Tareas retrasadas de medicación y cuidados.
     *
     * REGLAS:
     * CRÍTICO: existe alerta crítica activa o situación crítica.
     * VIGILANCIA: existen alertas relevantes no críticas O tareas retrasadas.
     * ESTABLE: no existen alertas relevantes activas Y no existen tareas retrasadas.
     * Nunca muestra ESTABLE si hay tareas vencidas.
     */
    private function calcularEstadoGeneral(Collection $alertas, int $tareasRetrasadas, ?Alerta $alertaCritica, Carbon $momentoActual): array
    {
        $horaFormateada = $momentoActual->format('H:i \h\r\s');

        // 1. Condición CRÍTICO
        if ($alertaCritica) {
            $nombreResidente = $alertaCritica->residente?->nombre_completo ?? 'Residente asignado';
            $tituloAlerta = $alertaCritica->titulo ?: ($alertaCritica->descripcion ?: 'Alerta crítica activa');
            return [
                'badge' => 'CRÍTICO',
                'titulo' => 'Atención crítica requerida',
                'mensaje' => "{$nombreResidente} - {$tituloAlerta}",
                'tiempo' => $horaFormateada,
                'tipo' => 'critico',
            ];
        }

        // 2. Condición VIGILANCIA
        $alertasRelevantesActivas = $alertas->filter(function ($a) {
            $est = strtoupper(trim((string)$a->estado));
            $p = strtoupper(trim((string)$a->prioridad));
            return in_array($est, self::ESTADOS_ALERTA_ACTIVOS, true)
                && in_array($p, self::PRIORIDADES_RELEVANTES, true);
        })->count();

        if ($alertasRelevantesActivas > 0 || $tareasRetrasadas > 0) {
            if ($tareasRetrasadas > 0 && $alertasRelevantesActivas > 0) {
                $titulo = 'Tareas vencidas y alertas en curso';
                $mensaje = "Se registran {$tareasRetrasadas} tarea(s) retrasada(s) y {$alertasRelevantesActivas} alerta(s) activa(s).";
            } elseif ($tareasRetrasadas > 0) {
                $titulo = 'Tareas de atención con retraso';
                $mensaje = "Existen {$tareasRetrasadas} tarea(s) programada(s) de medicación o cuidado fuera de horario.";
            } else {
                $titulo = 'Vigilancia clínica activa';
                $mensaje = "Existen {$alertasRelevantesActivas} alerta(s) médica(s) bajo seguimiento en tu guardia.";
            }

            return [
                'badge' => 'VIGILANCIA',
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'tiempo' => $horaFormateada,
                'tipo' => 'vigilancia',
            ];
        }

        // 3. Condición ESTABLE (sin alertas relevantes y sin tareas retrasadas)
        return [
            'badge' => 'ESTABLE',
            'titulo' => 'Sin alertas activas en tu turno',
            'mensaje' => 'Todos los residentes asignados se encuentran con signos estables y cuidados al día.',
            'tiempo' => $horaFormateada,
            'tipo' => 'estable',
        ];
    }

    /**
     * Calcula el progreso del turno a partir de tareas realmente programadas:
     * medicación programada + cuidados programados.
     *
     * Estados: REALIZADO, PENDIENTE, RETRASADO.
     * Si total = 0, porcentaje = null.
     */
    private function calcularProgresoTurno(array $agenda): array
    {
        $realizadas = 0;
        $pendientes = 0;
        $retrasadas = 0;

        foreach ($agenda as $item) {
            if ($item['estado'] === 'REALIZADO') {
                $realizadas++;
            } elseif ($item['estado'] === 'RETRASADO') {
                $retrasadas++;
            } else {
                $pendientes++;
            }
        }

        $total = $realizadas + $pendientes + $retrasadas;
        $porcentaje = $total > 0 ? (int) round(($realizadas / $total) * 100) : null;

        return [
            'realizadas' => $total > 0 ? $realizadas : null,
            'pendientes' => $total > 0 ? $pendientes : null,
            'retrasadas' => $total > 0 ? $retrasadas : null,
            'total' => $total,
            'porcentaje' => $porcentaje,
        ];
    }

    /**
     * Obtiene la distribución de cuidados a partir de planes_cuidado.tipo_plan reales + Medicación.
     */
    private function resolverDistribucionCuidados(array $codResidentes, array $agenda): array
    {
        if (empty($codResidentes)) {
            return [
                ['label' => 'Medicación', 'total' => 0],
            ];
        }

        // Conteo de medicación programada en la agenda
        $totalMedicacion = count(array_filter($agenda, fn($i) => $i['tipo'] === 'MEDICACION'));

        // Conteo agrupado por planes_cuidado.tipo_plan
        $conteoPlanes = PlanCuidado::query()
            ->whereIn('cod_residente', $codResidentes)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
            ->selectRaw('tipo_plan, count(*) as total')
            ->groupBy('tipo_plan')
            ->pluck('total', 'tipo_plan')
            ->toArray();

        $distribucion = [
            ['label' => 'Medicación', 'total' => $totalMedicacion],
        ];

        foreach ($conteoPlanes as $tipo => $total) {
            $nombreEtiqueta = ucfirst(strtolower(trim((string)$tipo)));
            $distribucion[] = [
                'label' => $nombreEtiqueta ?: 'Cuidado general',
                'total' => (int) $total,
            ];
        }

        return $distribucion;
    }

    /**
     * Formatea las tarjetas de residentes asignados con su información clínica real.
     */
    private function formatearResidentesCards(Collection $asigResidentes, Collection $alertas, ?string $responsableTexto = null, ?string $turnoActual = null): array
    {
        $cards = [];

        foreach ($asigResidentes as $asig) {
            $residente = $asig->residente;
            if (!$residente) continue;

            $edadStr = $residente->fecha_nacimiento
                ? Carbon::parse($residente->fecha_nacimiento)->age . ' años'
                : 'Edad no registrada';

            $alertasRes = $alertas->where('cod_residente', $residente->cod_residente);
            $alertasCount = $alertasRes->filter(function ($a) {
                return in_array(strtoupper(trim((string)$a->estado)), self::ESTADOS_ALERTA_ACTIVOS, true);
            })->count();

            // Supervisión real desde la asignación de jornada
            $supervisionRaw = strtoupper(trim((string)($asig->nivel_supervision ?? '')));
            $supervisionLabel = match($supervisionRaw) {
                'BAJO', 'BAJA' => 'Supervisión baja',
                'ALTO', 'ALTA', 'DIRECTA' => 'Supervisión alta',
                'ESTANDAR', 'ESTÁNDAR', 'MEDIA' => 'Supervisión media',
                default => $supervisionRaw ? "Supervisión {$supervisionRaw}" : 'Supervisión no especificada',
            };

            // Movilidad real desde plan de cuidado o diagnóstico
            $movilidadLabel = 'Movilidad asistida';
            $plan = $residente->planesCuidado?->first();
            if ($plan && !empty($plan->objetivo_general)) {
                $obj = strtolower($plan->objetivo_general);
                if (str_contains($obj, 'independien') || str_contains($obj, 'autonom')) {
                    $movilidadLabel = 'Autónomo';
                } elseif (str_contains($obj, 'baston') || str_contains($obj, 'andador') || str_contains($obj, 'dispositiv')) {
                    $movilidadLabel = 'Usa dispositivo';
                }
            }

            // Estado de salud derivado de alertas activas
            $tieneCritica = $alertasRes->contains(function ($a) {
                $p = strtoupper(trim((string)$a->prioridad));
                $est = strtoupper(trim((string)$a->estado));
                return in_array($p, self::PRIORIDADES_CRITICAS, true) && in_array($est, self::ESTADOS_ALERTA_ACTIVOS, true);
            });

            $tieneVigilancia = $alertasCount > 0;
            $estadoLabel = $tieneCritica ? 'CRÍTICO' : ($tieneVigilancia ? 'VIGILANCIA' : 'ESTABLE');

            $cards[] = [
                'cod_residente' => $residente->cod_residente,
                'nombre_completo' => $residente->nombre_completo,
                'edad' => $edadStr,
                'ubicacion' => $this->formatearUbicacion($residente),
                'iniciales' => $this->extraerIniciales($residente->nombre_completo),
                'foto' => $residente->foto,
                'estado_seguimiento' => $estadoLabel,
                'estado_institucional' => strtoupper(trim((string)($residente->estado ?: 'ACTIVO'))),
                'estado_label' => $estadoLabel,
                'estado_operacional' => strtoupper(trim((string)($residente->estado ?: 'ACTIVO'))),
                'supervision_label' => $supervisionLabel,
                'movilidad_label' => $movilidadLabel,
                'alertas_count' => $alertasCount,
                'responsable_texto' => $responsableTexto,
                'responsables' => $responsableTexto ? [$responsableTexto] : [],
                'turno_actual' => $turnoActual,
            ];
        }

        return $cards;
    }

    /**
     * Formatea la lista de alertas recientes para el stream de notificaciones.
     */
    private function formatearAlertasRecientes(Collection $alertas): array
    {
        return $alertas->take(6)->map(function ($a) {
            $prioridadRaw = strtoupper(trim((string)$a->prioridad));
            $prioridadFormateada = in_array($prioridadRaw, self::PRIORIDADES_CRITICAS, true)
                ? 'CRÍTICA'
                : (in_array($prioridadRaw, self::PRIORIDADES_ALTAS, true) ? 'ALTA' : ($prioridadRaw ?: 'MEDIA'));

            return [
                'cod_alerta' => $a->cod_alerta,
                'prioridad' => $prioridadFormateada,
                'tiempo_relativo' => $a->fecha_hora ? Carbon::parse($a->fecha_hora)->diffForHumans() : 'Hace unos momentos',
                'titulo' => $a->titulo ?: ($a->descripcion ?: 'Notificación clínica'),
                'residente' => $a->residente?->nombre_completo ?? 'Residente asignado',
                'estado' => strtoupper(trim((string)$a->estado)),
            ];
        })->values()->toArray();
    }

    /**
     * Resuelve la ubicación física de un residente mediante su cama activa.
     */
    public function formatearUbicacion(?Residente $residente): string
    {
        if (!$residente) {
            return 'Sin ubicación asignada';
        }

        $ocupacionesActivas = $residente->ocupacionesCama?->filter(function ($o) {
            return in_array(strtoupper(trim((string)$o->estado)), ['ACTIVO', 'ACTIVA'], true);
        });

        if ($ocupacionesActivas && $ocupacionesActivas->count() > 1) {
            return 'Inconsistencia de asignación de cama';
        }

        $ocupacion = $ocupacionesActivas?->first();

        if ($ocupacion && $ocupacion->cama) {
            $cama = $ocupacion->cama;
            $habNumero = $cama->habitacion?->codigo ?: ($cama->habitacion?->numero ?? 'S/N');
            $camaCodigo = $cama->nombre ?: ($cama->codigo ?: ($cama->numero ?: $cama->cod_cama));
            return "Hab. {$habNumero} · Cama {$camaCodigo}";
        }

        return 'Sin ubicación asignada';
    }

    private function extraerIniciales(string $nombre): string
    {
        $ini = '';
        foreach (explode(' ', trim($nombre)) as $p) {
            if ($p !== '') $ini .= mb_strtoupper(mb_substr($p, 0, 1));
            if (mb_strlen($ini) >= 2) break;
        }
        return $ini ?: 'RE';
    }

    /**
     * Valida si una fecha corresponde a la definición de días de la semana de la tarea.
     */
    public function correspondeDiaSemana(?string $diasSemanaStr, Carbon $fecha): bool
    {
        if (empty($diasSemanaStr)) {
            return true;
        }

        $diasMap = [
            1 => ['LUNES', 'LUN', '1', 'MONDAY'],
            2 => ['MARTES', 'MAR', '2', 'TUESDAY'],
            3 => ['MIERCOLES', 'MIÉRCOLES', 'MIE', '3', 'WEDNESDAY'],
            4 => ['JUEVES', 'JUE', '4', 'THURSDAY'],
            5 => ['VIERNES', 'VIE', '5', 'FRIDAY'],
            6 => ['SABADO', 'SÁBADO', 'SAB', '6', 'SATURDAY'],
            7 => ['DOMINGO', 'DOM', '7', 'SUNDAY'],
        ];

        $iso = $fecha->dayOfWeekIso; // 1 (Mon) .. 7 (Sun)
        $validos = $diasMap[$iso] ?? [];

        $tokens = array_map(function ($t) {
            $clean = strtoupper(trim($t));
            return str_replace(['Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U'], $clean);
        }, preg_split('/[,\|\s;\-\/]+/', $diasSemanaStr));

        foreach ($validos as $v) {
            $cleanV = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U'], $v);
            if (in_array($cleanV, $tokens, true)) {
                return true;
            }
        }

        return false;
    }
}
