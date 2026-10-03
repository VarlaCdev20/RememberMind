<?php

namespace App\Backend\Modulos\Reportes\Servicios;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Datos de presentación para los roles que usan el dashboard compartido. */
final class RoleDashboardDataService
{
    public function forRole(User $user, string $role): array
    {
        return match ($role) {
            'SUPERADMINISTRADOR' => $this->institutional(),
            'GERENTE' => $this->management(),
            'MEDICO GENERAL/GERIATRA' => $this->medical($user),
            'NUTRICIONISTA' => $this->professional($user, 'nutricion'),
            'FISIOTERAPEUTA' => $this->professional($user, 'fisioterapia'),
            'PEDAGOGO' => $this->pedagogy($user),
            'FAMILIAR' => $this->family($user),
            default => ['metrics' => [], 'panels' => []],
        };
    }

    private function medical(User $user): array
    {
        $personal = $user->personal;
        if (! $personal || $personal->estado !== 'ACTIVO') {
            return ['metrics' => [], 'panels' => [
                $this->panel('Contexto profesional', 'list', [], 'No hay una vinculación de personal activo para consultar la agenda y los registros propios.', 'ph-identification-badge', 'wide'),
            ]];
        }

        $code = $personal->cod_personal;
        $care = DB::table('atenciones')->where('cod_personal', $code);
        $studies = DB::table('estudios_clinicos')->where('cod_personal', $code);
        $alerts = DB::table('alertas')->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->whereIn('prioridad', ['CRITICO', 'CRITICA', 'ALTA']);
        $pendingStudies = (clone $studies)->whereNull('fecha_realizacion')
            ->whereNotIn('estado', ['CANCELADO', 'CANCELADA', 'ANULADO', 'ANULADA']);
        $upcoming = (clone $care)->join('residentes as r', 'r.cod_residente', '=', 'atenciones.cod_residente')
            ->where('atenciones.fecha_hora', '>=', now())->whereIn('atenciones.estado', ['PROGRAMADA', 'PENDIENTE'])
            ->orderBy('atenciones.fecha_hora')->limit(6)
            ->get(['r.nombres', 'r.apellido_paterno', 'atenciones.tipo_atencion', 'atenciones.fecha_hora'])
            ->map(fn ($row) => ['label' => trim($row->nombres.' '.$row->apellido_paterno), 'detail' => $row->tipo_atencion.' · '.date('d/m H:i', strtotime($row->fecha_hora))])->all();
        $recentStudies = (clone $studies)->join('residentes as r', 'r.cod_residente', '=', 'estudios_clinicos.cod_residente')
            ->orderByDesc('estudios_clinicos.fecha_solicitud')->limit(5)
            ->get(['r.nombres', 'r.apellido_paterno', 'estudios_clinicos.estado', 'estudios_clinicos.fecha_solicitud'])
            ->map(fn ($row) => ['label' => trim($row->nombres.' '.$row->apellido_paterno), 'detail' => $row->estado.' · '.date('d/m/Y', strtotime($row->fecha_solicitud))])->all();
        $prescriptions = DB::table('prescripciones')->where('cod_personal', $code)
            ->select('estado as label')->selectRaw('COUNT(*) as total')
            ->groupBy('estado')->orderByDesc('total')->get();
        $recentNotes = DB::table('notas_clinicas as n')->join('residentes as r', 'r.cod_residente', '=', 'n.cod_residente')
            ->where('n.cod_personal', $code)->orderByDesc('n.fecha_hora')->limit(5)
            ->get(['r.nombres', 'r.apellido_paterno', 'n.tipo_nota', 'n.fecha_hora'])
            ->map(fn ($row) => ['label' => trim($row->nombres.' '.$row->apellido_paterno), 'detail' => $row->tipo_nota.' · '.date('d/m H:i', strtotime($row->fecha_hora))])->all();

        return [
            'metrics' => [
                $this->metric('Residentes atendidos', (clone $care)->where('fecha_hora', '>=', now()->subDays(90))->distinct('cod_residente')->count('cod_residente'), 'Por ti · últimos 90 días', 'ph-users-three', 'sky'),
                $this->metric('Atenciones próximas', (clone $care)->where('fecha_hora', '>=', now())->whereIn('estado', ['PROGRAMADA', 'PENDIENTE'])->count(), 'Registradas a tu nombre', 'ph-calendar-check', 'mint'),
                $this->metric('Alertas prioritarias', (clone $alerts)->count(), 'Abiertas de prioridad alta o crítica', 'ph-warning-circle', 'critical'),
                $this->metric('Estudios pendientes', (clone $pendingStudies)->count(), 'Solicitados por ti sin realización', 'ph-flask', 'neutral'),
            ],
            'panels' => [
                $this->panel('Próximas atenciones', 'timeline', $upcoming, 'No hay atenciones futuras registradas a tu nombre.', 'ph-calendar-check', 'wide'),
                $this->panel('Alertas clínicas prioritarias', 'list', (clone $alerts)->orderByDesc('fecha_hora')->limit(5)->get(['titulo', 'prioridad', 'fecha_hora'])
                    ->map(fn ($row) => ['label' => $row->titulo, 'detail' => $row->prioridad.' · '.date('d/m H:i', strtotime($row->fecha_hora))])->all(),
                    'No hay alertas clínicas prioritarias abiertas.', 'ph-warning-circle'),
                $this->panel('Estudios solicitados recientemente', 'list', $recentStudies, 'No hay estudios solicitados a tu nombre.', 'ph-flask'),
                $this->panel('Estado de tus prescripciones', 'segments', $this->countItems($prescriptions), 'No hay prescripciones registradas a tu nombre.', 'ph-pill'),
                $this->panel('Notas clínicas recientes', 'list', $recentNotes, 'No hay notas clínicas registradas a tu nombre.', 'ph-note-pencil', 'wide'),
            ],
        ];
    }

    private function institutional(): array
    {
        $today = today();
        $activeAlerts = DB::table('alertas')
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
        $staffToday = DB::table('asignaciones_personal as ap')
            ->join('jornadas as j', 'j.cod_jornada', '=', 'ap.cod_jornada')
            ->join('personal as p', 'p.cod_personal', '=', 'ap.cod_personal')
            ->whereDate('j.fecha_jornada', $today)
            ->whereIn('j.estado', ['ABIERTA', 'ACTIVA'])
            ->where('ap.estado', 'ACTIVA')
            ->where('p.estado', 'ACTIVO');

        $alerts = (clone $activeAlerts)->whereIn('prioridad', ['CRITICO', 'CRITICA', 'ALTA']);
        $residentStates = DB::table('residentes')->select('estado')
            ->selectRaw('COUNT(*) as total')->groupBy('estado')->orderByDesc('total')->get();
        $preadmissions = DB::table('preadmisiones')->select('estado')
            ->selectRaw('COUNT(*) as total')->groupBy('estado')->orderByDesc('total')->get();
        $coverage = (clone $staffToday)
            ->join('areas as a', 'a.cod_area', '=', 'ap.cod_area')
            ->select('a.nombre as label')->selectRaw('COUNT(DISTINCT ap.cod_personal) as total')
            ->groupBy('a.cod_area', 'a.nombre')->orderByDesc('total')->limit(6)->get();
        $eligibleBeds = DB::table('camas as c')
            ->join('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
            ->whereIn('c.estado', ['ACTIVA', 'ACTIVO', 'DISPONIBLE'])
            ->whereIn('h.estado', ['ACTIVA', 'ACTIVO', 'DISPONIBLE']);
        $bedCapacity = (clone $eligibleBeds)->count();
        $availableBeds = (clone $eligibleBeds)
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('ocupaciones_cama as o')
                ->whereColumn('o.cod_cama', 'c.cod_cama')->whereIn('o.estado', ['ACTIVA', 'ACTIVO'])
                ->whereNull('o.fecha_hora_liberacion')->where('o.fecha_hora_asignacion', '<=', now()))
            ->count();
        $assignedStaff = (clone $staffToday)->distinct('ap.cod_personal')->count('ap.cod_personal');
        $activeStaff = DB::table('personal')->where('estado', 'ACTIVO')->count();
        $activeShiftNames = DB::table('jornadas as j')
            ->join('turnos as t', 't.cod_turno', '=', 'j.cod_turno')
            ->whereDate('j.fecha_jornada', $today)->whereIn('j.estado', ['ABIERTA', 'ACTIVA'])
            ->pluck('t.nombre')->unique()->values();
        $weekStart = $today->copy()->subDays(6);
        $recentAdmissions = DB::table('admisiones as a')
            ->join('residentes as r', 'r.cod_residente', '=', 'a.cod_residente')
            ->whereIn('r.estado', ['ACTIVO', 'ADMITIDO'])
            ->whereDate('a.fecha_hora_admision', '>=', $weekStart)
            ->where('a.fecha_hora_admision', '<=', now())
            ->pluck('a.fecha_hora_admision');
        $recentAlerts = (clone $alerts)->where('fecha_hora', '>=', $weekStart)
            ->pluck('fecha_hora');
        $clinicalActivity = DB::table('atenciones')->where('fecha_hora', '>=', now()->subDays(30))
            ->select('tipo_atencion as label')->selectRaw('COUNT(*) as total')
            ->groupBy('tipo_atencion')->orderByDesc('total')->limit(6)->get();
        $incidents = DB::table('incidentes')->where('fecha_hora', '>=', now()->subDays(30))
            ->select('tipo_incidente as label')->selectRaw('COUNT(*) as total')
            ->groupBy('tipo_incidente')->orderByDesc('total')->limit(6)->get();
        $administrations = DB::table('administraciones_medicacion')
            ->where('fecha_hora_administracion', '>=', now()->subDays(7))->count();

        return [
            'metrics' => [
                [...$this->metric('Residentes activos', DB::table('residentes')->whereIn('estado', ['ACTIVO', 'ADMITIDO'])->count(), 'Actualmente admitidos', 'ph-users-three', 'mint'),
                    'sparkbars' => $this->dailyCounts($recentAdmissions, $weekStart), 'sparkbarLabel' => 'Ingresos de residentes activos · 7 días'],
                [...$this->metric('Camas disponibles', $availableBeds, 'Disponibilidad actual', 'ph-bed', 'sky'),
                    'capacity' => $bedCapacity, 'occupied' => $bedCapacity - $availableBeds],
                [...$this->metric('Alertas prioritarias', (clone $alerts)->count(), 'Críticas o altas abiertas', 'ph-warning-circle', 'critical'),
                    'sparkbars' => $this->dailyCounts($recentAlerts, $weekStart), 'sparkbarLabel' => 'Alertas prioritarias aún abiertas · 7 días'],
                [...$this->metric('Personal de hoy', $assignedStaff, 'Asignado a jornada activa', 'ph-identification-badge', 'neutral'),
                    'capacity' => $activeStaff, 'areas' => $this->countItems($coverage)],
            ],
            'activeShift' => $activeShiftNames->count() === 1 ? $activeShiftNames->first() : null,
            'panels' => [
                $this->panel('Alertas prioritarias', 'list', (clone $alerts)->orderByDesc('fecha_hora')->limit(5)->get(['titulo', 'prioridad', 'fecha_hora'])->map(fn ($row) => [
                    'label' => $row->titulo, 'detail' => $row->prioridad.' · '.date('d/m H:i', strtotime($row->fecha_hora)),
                ])->all(), 'No hay alertas prioritarias abiertas.', 'ph-warning-circle', 'wide'),
                $this->panel('Próximas actividades', 'timeline', $this->upcomingActivities(), 'No hay actividades programadas.', 'ph-calendar-check'),
                $this->panel('Residentes por estado', 'donut', $this->countItems($residentStates), 'No hay residentes registrados.', 'ph-users-three'),
                $this->panel('Preadmisiones por estado', 'segments', $this->countItems($preadmissions), 'No hay preadmisiones registradas.', 'ph-user-plus'),
                $this->panel('Cobertura por área hoy', 'bars', $this->countItems($coverage), 'No hay personal asignado a una jornada activa hoy.', 'ph-buildings'),
                $this->panel('Seguimiento clínico · 30 días', 'bars', $this->countItems($clinicalActivity), 'No hay atenciones registradas en el período.', 'ph-first-aid-kit'),
                $this->panel('Administraciones · 7 días', 'list', $administrations > 0 ? [[
                    'label' => 'Administraciones registradas', 'detail' => 'Últimos siete días', 'value' => $administrations,
                ]] : [], 'No hay administraciones registradas en el período.', 'ph-pill'),
                $this->panel('Incidentes por tipo · 30 días', 'bars', $this->countItems($incidents), 'No hay incidentes registrados en el período.', 'ph-warning'),
                $this->panel('Documentos validados recientes', 'list', DB::table('documentos')->whereNotNull('fecha_validacion')
                    ->orderByDesc('fecha_validacion')->limit(5)->get(['nombre', 'tipo_documento', 'fecha_validacion'])
                    ->map(fn ($row) => ['label' => $row->nombre, 'detail' => $row->tipo_documento.' · '.date('d/m/Y', strtotime($row->fecha_validacion))])->all(),
                    'No hay documentos validados recientemente.', 'ph-file-text'),
            ],
        ];
    }

    private function management(): array
    {
        $today = today();
        $assignments = DB::table('asignaciones_personal as ap')
            ->join('jornadas as j', 'j.cod_jornada', '=', 'ap.cod_jornada')
            ->whereDate('j.fecha_jornada', $today)
            ->whereIn('j.estado', ['ABIERTA', 'ACTIVA'])
            ->where('ap.estado', 'ACTIVA');
        $coverage = (clone $assignments)->join('areas as a', 'a.cod_area', '=', 'ap.cod_area')
            ->select('a.nombre as label')->selectRaw('COUNT(DISTINCT ap.cod_personal) as total')
            ->groupBy('a.cod_area', 'a.nombre')->orderByDesc('total')->limit(8)->get();
        $professions = DB::table('personal')->where('estado', 'ACTIVO')->select('profesion as label')
            ->selectRaw('COUNT(*) as total')->groupBy('profesion')->orderByDesc('total')->limit(8)->get();

        return [
            'metrics' => [
                $this->metric('Personal activo', DB::table('personal')->where('estado', 'ACTIVO')->count(), 'Plantilla vigente', 'ph-identification-badge', 'mint'),
                $this->metric('Personal asignado hoy', (clone $assignments)->distinct('ap.cod_personal')->count('ap.cod_personal'), 'Jornadas abiertas', 'ph-calendar-check', 'sky'),
                $this->metric('Áreas cubiertas hoy', $coverage->count(), 'Con personal asignado', 'ph-buildings', 'neutral'),
                $this->metric('Actividades próximas', DB::table('actividades')->where('estado', 'PROGRAMADA')->whereBetween('fecha_hora', [now(), now()->addDays(7)])->count(), 'Próximos siete días', 'ph-calendar-blank', 'coral'),
            ],
            'panels' => [
                $this->panel('Cobertura por área hoy', 'bars', $this->countItems($coverage), 'No hay asignaciones activas hoy.', 'ph-buildings', 'wide'),
                $this->panel('Equipo por profesión', 'bars', $this->countItems($professions), 'No hay personal activo registrado.', 'ph-users-three'),
                $this->panel('Próximas actividades', 'timeline', $this->upcomingActivities(), 'No hay actividades programadas.', 'ph-calendar-check'),
            ],
        ];
    }

    private function professional(User $user, string $discipline): array
    {
        $personal = $user->personal;
        if (! $personal || $personal->estado !== 'ACTIVO') {
            return ['metrics' => [], 'panels' => [
                $this->panel('Contexto profesional', 'list', [], 'No hay una vinculación de personal activo para consultar registros propios.', 'ph-identification-badge', 'wide'),
            ]];
        }

        $code = $personal->cod_personal;
        $table = $discipline === 'nutricion' ? 'valoraciones_nutricionales' : 'valoraciones_funcionales';
        $title = $discipline === 'nutricion' ? 'Valoraciones nutricionales' : 'Valoraciones funcionales';
        $icon = $discipline === 'nutricion' ? 'ph-bowl-food' : 'ph-person-simple-walk';
        $valuations = DB::table($table)->where('cod_personal', $code);
        $care = DB::table('atenciones')->where('cod_personal', $code)
            ->where('fecha_hora', '>=', now()->subDays(90));
        $plans = DB::table('planes_cuidado')->where('cod_personal', $code)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ABIERTO', 'ABIERTA']);
        $alerts = DB::table('alertas')->where('cod_personal_responsable', $code)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
        $recent = (clone $valuations)->join('residentes as r', 'r.cod_residente', '=', $table.'.cod_residente')
            ->orderByDesc($table.'.fecha_hora')->limit(6)
            ->get(['r.nombres', 'r.apellido_paterno', $table.'.fecha_hora'])
            ->map(fn ($row) => ['label' => trim($row->nombres.' '.$row->apellido_paterno), 'detail' => date('d/m/Y H:i', strtotime($row->fecha_hora))])->all();
        $trend = (clone $valuations)->where('fecha_hora', '>=', now()->subDays(30))
            ->selectRaw('DATE(fecha_hora) as label, COUNT(*) as total')
            ->groupByRaw('DATE(fecha_hora)')->orderBy('label')->get();
        $relatedTable = $discipline === 'nutricion' ? 'mediciones_antropometricas' : 'valoraciones_dolor';
        $relatedTitle = $discipline === 'nutricion' ? 'Mediciones recientes' : 'Registros de dolor recientes';
        $related = DB::table($relatedTable)->where('cod_personal', $code)
            ->where('fecha_hora', '>=', now()->subDays(30))
            ->join('residentes as r', 'r.cod_residente', '=', $relatedTable.'.cod_residente')
            ->orderByDesc($relatedTable.'.fecha_hora')->limit(5)
            ->get(['r.nombres', 'r.apellido_paterno', $relatedTable.'.fecha_hora'])
            ->map(fn ($row) => ['label' => trim($row->nombres.' '.$row->apellido_paterno), 'detail' => date('d/m/Y H:i', strtotime($row->fecha_hora))])->all();
        $upcoming = DB::table('atenciones as a')->join('residentes as r', 'r.cod_residente', '=', 'a.cod_residente')
            ->where('a.cod_personal', $code)->where('a.fecha_hora', '>=', now())
            ->whereIn('a.estado', ['PROGRAMADA', 'PENDIENTE'])
            ->orderBy('a.fecha_hora')->limit(6)
            ->get(['r.nombres', 'r.apellido_paterno', 'a.tipo_atencion', 'a.fecha_hora'])
            ->map(fn ($row) => ['label' => trim($row->nombres.' '.$row->apellido_paterno), 'detail' => $row->tipo_atencion.' · '.date('d/m H:i', strtotime($row->fecha_hora))])->all();

        return [
            'metrics' => [
                $this->metric('Residentes atendidos', (clone $care)->distinct('cod_residente')->count('cod_residente'), 'Por ti · últimos 90 días', 'ph-users-three', 'mint'),
                $this->metric($title.' · 30 días', (clone $valuations)->where('fecha_hora', '>=', now()->subDays(30))->count(), 'Registros propios', $icon, 'sky'),
                $this->metric('Planes a tu cargo', (clone $plans)->count(), 'Planes abiertos', 'ph-notebook', 'neutral'),
                $this->metric('Alertas a tu cargo', (clone $alerts)->count(), 'Abiertas o en atención', 'ph-warning-circle', 'critical'),
            ],
            'panels' => [
                $this->panel($title.' recientes', 'list', $recent, 'Aún no hay valoraciones registradas a tu nombre.', $icon, 'wide'),
                $this->panel('Evolución de valoraciones · 30 días', 'bars', $this->countItems($trend), 'Sin registros en los últimos 30 días.', 'ph-chart-bar'),
                $this->panel($relatedTitle, 'list', $related, 'No hay registros propios en los últimos 30 días.', $icon),
                $this->panel('Agenda de atenciones', 'timeline', $upcoming, 'No hay atenciones programadas a tu nombre.', 'ph-calendar-check'),
            ],
        ];
    }

    private function pedagogy(User $user): array
    {
        $personal = $user->personal;
        if (! $personal || $personal->estado !== 'ACTIVO') {
            return ['metrics' => [], 'panels' => [
                $this->panel('Contexto profesional', 'list', [], 'No hay una vinculación de personal activo para consultar registros propios.', 'ph-identification-badge', 'wide'),
            ]];
        }

        $code = $personal->cod_personal;
        $activities = DB::table('actividades')->where('cod_personal', $code);
        $followups = DB::table('seguimientos_pedagogicos')->where('cod_personal', $code);
        $todayActivities = (clone $activities)->whereDate('fecha_hora', today())
            ->whereIn('estado', ['PROGRAMADA', 'ACTIVA', 'EN_CURSO']);
        $participants = (clone $activities)->join('participantes_actividad as pa', 'pa.cod_actividad', '=', 'actividades.cod_actividad');
        $recent = (clone $followups)->join('residentes as r', 'r.cod_residente', '=', 'seguimientos_pedagogicos.cod_residente')
            ->orderByDesc('seguimientos_pedagogicos.fecha_hora')->limit(6)
            ->get(['r.nombres', 'r.apellido_paterno', 'seguimientos_pedagogicos.fecha_hora'])
            ->map(fn ($row) => ['label' => trim($row->nombres.' '.$row->apellido_paterno), 'detail' => date('d/m/Y H:i', strtotime($row->fecha_hora))])->all();
        $participation = (clone $participants)->select('actividades.nombre as label')
            ->selectRaw('COUNT(pa.cod_participante) as total')
            ->groupBy('actividades.cod_actividad', 'actividades.nombre')->orderByDesc('total')->limit(6)->get();

        return [
            'metrics' => [
                $this->metric('Residentes participantes', (clone $participants)->distinct('pa.cod_residente')->count('pa.cod_residente'), 'En tus actividades registradas', 'ph-users-three', 'mint'),
                $this->metric('Actividades de hoy', (clone $todayActivities)->count(), 'Programadas o en curso', 'ph-calendar-check', 'sky'),
                $this->metric('Participaciones', (clone $participants)->count(), 'Registros de tus actividades', 'ph-hand-heart', 'neutral'),
                $this->metric('Seguimientos · 30 días', (clone $followups)->where('fecha_hora', '>=', now()->subDays(30))->count(), 'Registros propios', 'ph-notebook', 'coral'),
            ],
            'panels' => [
                $this->panel('Actividades del día', 'timeline', (clone $todayActivities)->orderBy('fecha_hora')->limit(6)->get(['nombre', 'fecha_hora'])
                    ->map(fn ($row) => ['label' => $row->nombre, 'detail' => date('H:i', strtotime($row->fecha_hora))])->all(),
                    'No tienes actividades programadas hoy.', 'ph-calendar-check', 'wide'),
                $this->panel('Participación por actividad', 'bars', $this->countItems($participation), 'Aún no hay participantes registrados en tus actividades.', 'ph-chart-bar'),
                $this->panel('Seguimientos recientes', 'list', $recent, 'No hay seguimientos pedagógicos registrados a tu nombre.', 'ph-notebook'),
            ],
        ];
    }

    private function family(User $user): array
    {
        $links = DB::table('residentes_contactos as rc')
            ->join('contactos as c', 'c.cod_contacto', '=', 'rc.cod_contacto')
            ->join('residentes as r', 'r.cod_residente', '=', 'rc.cod_residente')
            ->where('c.cod_usuario', $user->cod_usuario)
            ->where('c.estado', 'ACTIVO')
            ->where('rc.estado', 'ACTIVO')
            ->where('rc.autoriza_informacion', true);
        $residents = (clone $links)->orderBy('r.apellido_paterno')->limit(10)
            ->get(['r.cod_residente', 'r.nombres', 'r.apellido_paterno', 'rc.parentesco'])
            ->map(fn ($row) => ['label' => trim($row->nombres.' '.$row->apellido_paterno), 'detail' => $row->parentesco])->all();
        if ($residents === []) {
            return ['metrics' => [], 'panels' => [
                $this->panel('Tu familiar', 'list', [], 'No hay un vínculo activo con autorización de información para esta cuenta.', 'ph-heart', 'wide'),
            ]];
        }

        $activityRows = (clone $links)
            ->join('participantes_actividad as pa', 'pa.cod_residente', '=', 'rc.cod_residente')
            ->join('actividades as a', 'a.cod_actividad', '=', 'pa.cod_actividad')
            ->where('a.estado', 'PROGRAMADA')->where('a.fecha_hora', '>=', now())
            ->orderBy('a.fecha_hora')->limit(5)
            ->get(['a.nombre', 'a.fecha_hora', 'r.nombres as residente']);
        $visits = (clone $links)->join('visitas as v', function ($join) {
            $join->on('v.cod_residente', '=', 'rc.cod_residente')->on('v.cod_contacto', '=', 'rc.cod_contacto');
        });
        $upcomingVisits = (clone $visits)->whereIn('v.estado', ['PROGRAMADA', 'AUTORIZADA'])
            ->where('v.fecha_hora_programada', '>=', now())->orderBy('v.fecha_hora_programada')->limit(5)
            ->get(['r.nombres as residente', 'v.fecha_hora_programada'])
            ->map(fn ($row) => ['label' => $row->residente, 'detail' => date('d/m/Y H:i', strtotime($row->fecha_hora_programada))])->all();
        $recentVisits = (clone $visits)->whereNotNull('v.fecha_hora_ingreso')
            ->orderByDesc('v.fecha_hora_ingreso')->limit(5)
            ->get(['r.nombres as residente', 'v.fecha_hora_ingreso'])
            ->map(fn ($row) => ['label' => $row->residente, 'detail' => date('d/m/Y H:i', strtotime($row->fecha_hora_ingreso))])->all();

        return [
            'metrics' => [],
            'panels' => [
                $this->panel('Tu familiar', 'list', $residents, 'No hay información compartida.', 'ph-heart', 'wide'),
                $this->panel('Próximas actividades', 'timeline', $activityRows->map(fn ($row) => [
                    'label' => $row->nombre, 'detail' => $row->residente.' · '.date('d/m/Y H:i', strtotime($row->fecha_hora)),
                ])->all(), 'No hay actividades próximas vinculadas a tu familiar.', 'ph-calendar-check'),
                $this->panel('Próximas visitas', 'timeline', $upcomingVisits, 'No hay visitas programadas para ti.', 'ph-door-open'),
                $this->panel('Tus visitas recientes', 'list', $recentVisits, 'Aún no hay visitas registradas para ti.', 'ph-clock-counter-clockwise'),
                $this->panel('Documentos compartidos', 'list', [], 'Todavía no existe un mecanismo para publicar documentos individuales a familiares.', 'ph-file-text'),
            ],
        ];
    }

    private function upcomingActivities(): array
    {
        return DB::table('actividades')->where('estado', 'PROGRAMADA')
            ->whereBetween('fecha_hora', [now(), now()->addDays(7)])
            ->orderBy('fecha_hora')->limit(5)->get(['nombre', 'fecha_hora'])
            ->map(fn ($row) => ['label' => $row->nombre, 'detail' => date('d/m H:i', strtotime($row->fecha_hora))])->all();
    }

    private function countItems($rows): array
    {
        return $rows->map(fn ($row) => [
            'label' => (string) ($row->label ?? $row->estado ?? 'Sin clasificar'),
            'value' => (int) $row->total,
        ])->all();
    }

    private function dailyCounts(iterable $dates, \Illuminate\Support\Carbon $start): array
    {
        $counts = [];
        for ($day = 0; $day < 7; $day++) {
            $counts[$start->copy()->addDays($day)->toDateString()] = 0;
        }

        foreach ($dates as $date) {
            $key = substr((string) $date, 0, 10);
            if (isset($counts[$key])) {
                $counts[$key]++;
            }
        }

        return array_values($counts);
    }

    private function metric(string $label, int $value, string $description, string $icon, string $variant): array
    {
        return compact('label', 'value', 'description', 'icon', 'variant');
    }

    private function panel(string $title, string $type, array $items, string $empty, string $icon, string $span = 'normal'): array
    {
        return compact('title', 'type', 'items', 'empty', 'icon', 'span');
    }
}
