<?php

namespace App\Backend\Modulos\Administracion\Servicios;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ConsultaOperativaService
{
    private const MODULOS = [
        'admisiones' => ['Admisiones', 'Seguimiento de ingresos pendientes y admisiones formalizadas.', 'admisiones.ver_dashboard', 'ph-clipboard-text'],
        'residentes' => ['Residentes', 'Personas formalmente admitidas y su situación residencial.', 'residentes.ver', 'ph-users-three'],
        'habitaciones' => ['Habitaciones y camas', 'Capacidad y estado de las camas institucionales.', 'habitaciones.ver', 'ph-bed'],
        'ocupacion' => ['Ocupación', 'Asignaciones de cama vigentes y disponibilidad.', 'ocupaciones_cama.ver', 'ph-chart-pie-slice'],
        'jornadas' => ['Jornadas', 'Jornadas de operación institucional.', 'jornadas.ver', 'ph-calendar-check'],
        'asignaciones' => ['Asignaciones', 'Personal asignado por jornada y área.', 'asignaciones_personal.ver', 'ph-users-four'],
        'contactos' => ['Contactos y responsables', 'Personas de contacto y vínculos administrativos.', 'contactos.ver', 'ph-address-book'],
        'documentacion' => ['Documentación', 'Documentos pendientes, vigentes y vencidos.', 'documentos.ver', 'ph-files'],
        'consentimientos' => ['Consentimientos', 'Consentimientos registrados para residentes admitidos.', 'consentimientos.ver', 'ph-signature'],
        'seguros' => ['Seguros', 'Coberturas registradas por residente.', 'seguros_residente.ver', 'ph-shield-check'],
        'actividades' => ['Actividades', 'Agenda de actividades residenciales.', 'actividades.ver', 'ph-calendar-dots'],
        'visitas' => ['Visitas', 'Visitas programadas y registradas.', 'visitas.ver', 'ph-door-open'],
        'alertas' => ['Alertas', 'Seguimiento de alertas que requieren coordinación.', 'alertas.ver', 'ph-bell-ringing'],
        'incidentes' => ['Incidentes', 'Registro de incidentes para seguimiento institucional.', 'incidentes.ver', 'ph-warning-octagon'],
        'reportes' => ['Reportes', 'Información administrativa disponible para consulta y exportación.', 'reportes.ver', 'ph-chart-bar'],
    ];

    public function definicion(string $modulo): ?array
    {
        if (! isset(self::MODULOS[$modulo])) {
            return null;
        }

        return array_combine(['titulo', 'descripcion', 'permiso', 'icono'], self::MODULOS[$modulo]);
    }

    public function columnas(string $modulo): array
    {
        return match ($modulo) {
            'admisiones' => ['documento' => 'Documento', 'etapa' => 'Etapa', 'habitacion' => 'Habitación', 'cama' => 'Cama', 'fecha' => 'Fecha', 'estado' => 'Estado'],
            'residentes' => ['documento' => 'Documento', 'admision' => 'Admisión', 'fecha' => 'Nacimiento', 'habitacion' => 'Habitación', 'cama' => 'Cama', 'responsable' => 'Responsable', 'estado' => 'Estado'],
            'habitaciones' => ['detalle' => 'Habitación', 'capacidad' => 'Capacidad habitación', 'tipo' => 'Tipo de cama', 'ocupante' => 'Ocupante', 'estado' => 'Estado'],
            'ocupacion' => ['detalle' => 'Habitación', 'ocupante' => 'Residente', 'fecha' => 'Inicio', 'fecha_fin' => 'Fin', 'estado' => 'Estado'],
            'jornadas' => ['fecha' => 'Fecha', 'horario' => 'Horario', 'asignados' => 'Personal asignado', 'estado' => 'Estado'],
            'asignaciones' => ['detalle' => 'Área', 'jornada' => 'Jornada', 'funcion' => 'Función', 'fecha' => 'Fecha', 'estado' => 'Estado'],
            'contactos' => ['detalle' => 'Residente', 'parentesco' => 'Parentesco', 'principal' => 'Responsable', 'emergencia' => 'Emergencia', 'telefono' => 'Teléfono', 'estado' => 'Estado'],
            'documentacion' => ['detalle' => 'Tipo', 'titular' => 'Titular', 'fecha' => 'Vencimiento', 'validacion' => 'Validación', 'estado' => 'Estado'],
            'consentimientos' => ['detalle' => 'Residente', 'firmante' => 'Firmante', 'fecha' => 'Fecha', 'estado' => 'Estado'],
            'seguros' => ['detalle' => 'Residente', 'plan' => 'Plan', 'afiliacion' => 'Afiliación', 'titular' => 'Titular', 'estado' => 'Estado'],
            'actividades' => ['detalle' => 'Lugar', 'area' => 'Área', 'responsable' => 'Responsable', 'fecha' => 'Fecha', 'duracion' => 'Duración (min)', 'cupo' => 'Cupo', 'participantes' => 'Participantes', 'estado' => 'Estado'],
            'visitas' => ['detalle' => 'Residente', 'programada' => 'Programada', 'ingreso' => 'Entrada', 'salida' => 'Salida', 'motivo' => 'Motivo', 'estado' => 'Estado'],
            'alertas' => ['residente' => 'Residente', 'detalle' => 'Prioridad', 'origen' => 'Origen', 'fecha' => 'Fecha', 'responsable' => 'Responsable', 'estado' => 'Estado'],
            'incidentes' => ['residente' => 'Residente', 'gravedad' => 'Severidad', 'detalle' => 'Lugar', 'fecha' => 'Fecha', 'requiere_medico' => 'Médico', 'requiere_derivacion' => 'Derivación', 'estado' => 'Estado'],
            default => ['detalle' => 'Detalle', 'fecha' => 'Fecha', 'estado' => 'Estado'],
        };
    }

    public function tabs(string $modulo): array
    {
        return match ($modulo) {
            'admisiones' => ['preparacion' => 'Por formalizar', 'admitidos' => 'Admitidos', 'historial' => 'Historial'],
            'ocupacion' => ['actual' => 'Ocupación actual', 'historial' => 'Historial'],
            'jornadas' => ['hoy' => 'Hoy', 'proximas' => 'Próximas', 'finalizadas' => 'Finalizadas', 'todas' => 'Todas'],
            'documentacion' => ['todos' => 'Todos', 'pendientes' => 'Pendientes', 'por_vencer' => 'Por vencer', 'vencidos' => 'Vencidos', 'validados' => 'Validados'],
            'consentimientos' => ['activos' => 'Activos', 'revocados' => 'Revocados', 'anulados' => 'Anulados', 'todos' => 'Todos'],
            'visitas' => ['hoy' => 'Hoy', 'programadas' => 'Programadas', 'dentro' => 'Dentro', 'finalizadas' => 'Finalizadas', 'todas' => 'Todas'],
            'alertas' => ['abiertas' => 'Abiertas', 'reconocidas' => 'Reconocidas', 'asignadas' => 'Asignadas', 'en_atencion' => 'En atención', 'cerradas' => 'Cerradas', 'todas' => 'Todas'],
            default => [],
        };
    }

    public function consulta(string $modulo, string $tab = ''): array
    {
        switch ($modulo) {
            case 'admisiones':
                if (in_array($tab, ['admitidos', 'historial'], true)) {
                    $query = DB::table('admisiones as ad')->join('residentes as r', 'r.cod_residente', '=', 'ad.cod_residente')
                        ->leftJoin('ocupaciones_cama as oc', fn ($join) => $join->on('oc.cod_admision', '=', 'ad.cod_admision')
                            ->where('oc.estado', 'ACTIVA')->whereNull('oc.fecha_hora_liberacion'))
                        ->leftJoin('camas as c', 'c.cod_cama', '=', 'oc.cod_cama')
                        ->leftJoin('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
                        ->select('ad.cod_admision as codigo',
                            'r.numero_documento as documento', 'ad.tipo_ingreso', 'ad.cod_residente',
                            'h.codigo as habitacion', 'c.codigo as cama',
                            'ad.fecha_hora_admision as fecha', 'ad.estado as estado')
                        ->selectRaw("TRIM(r.nombres || ' ' || r.apellido_paterno) as titulo, 'Formalizada' as etapa");
                    $query->where('ad.estado', $tab === 'admitidos' ? '=' : '!=', 'ACTIVA');
                    return $this->armar(
                        $query,
                        ['r.nombres', 'r.apellido_paterno', 'ad.cod_admision'], 'ad.estado', 'ad.fecha_hora_admision'
                    );
                }
                return $this->armar(
                    DB::table('preadmisiones as pre')->where('pre.estado', 'APROBADA')
                        ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('admisiones as ad')
                            ->whereColumn('ad.cod_preadmision', 'pre.cod_preadmision'))
                        ->select('pre.cod_preadmision as codigo',
                            'pre.numero_documento as documento', 'pre.tipo_ingreso', 'pre.fecha_revision as fecha', 'pre.estado as estado')
                        ->selectRaw("TRIM(pre.nombres || ' ' || pre.apellido_paterno) as titulo, 'Por formalizar' as etapa"),
                    ['pre.nombres', 'pre.apellido_paterno', 'pre.cod_preadmision'], 'pre.estado', 'pre.fecha_revision'
                );
            case 'residentes':
                return $this->armar(
                    DB::table('residentes as r')
                        ->leftJoin('ocupaciones_cama as oc', fn ($join) => $join->on('oc.cod_residente', '=', 'r.cod_residente')
                            ->whereNull('oc.fecha_hora_liberacion')->where('oc.estado', 'ACTIVA'))
                        ->leftJoin('camas as c', 'c.cod_cama', '=', 'oc.cod_cama')
                        ->leftJoin('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
                        ->leftJoin('residentes_contactos as rc', fn ($join) => $join->on('rc.cod_residente', '=', 'r.cod_residente')
                            ->where('rc.responsable_principal', true)->where('rc.estado', 'ACTIVO'))
                        ->leftJoin('contactos as co', 'co.cod_contacto', '=', 'rc.cod_contacto')
                        ->select('r.cod_residente as codigo', 'r.numero_documento as documento',
                            'r.fecha_nacimiento as fecha', 'r.foto', 'h.codigo as habitacion', 'h.nombre as sector',
                            'c.codigo as cama', 'rc.parentesco', 'r.estado as estado')
                        ->selectRaw("TRIM(r.nombres || ' ' || r.apellido_paterno || ' ' || COALESCE(r.apellido_materno, '')) as titulo")
                        ->selectRaw("TRIM(COALESCE(co.nombres, '') || ' ' || COALESCE(co.apellido_paterno, '') || ' ' || COALESCE(co.apellido_materno, '')) as responsable")
                        ->selectRaw("CASE WHEN EXISTS (SELECT 1 FROM admisiones ad WHERE ad.cod_residente = r.cod_residente) THEN 'Registrada' ELSE 'Sin admisión' END as admision"),
                    ['r.nombres', 'r.apellido_paterno', 'r.numero_documento', 'r.cod_residente'], 'r.estado', 'r.cod_residente'
                );
            case 'habitaciones':
                $query = DB::table('camas as c')->join('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
                    ->leftJoin('ocupaciones_cama as oc', fn ($join) => $join->on('oc.cod_cama', '=', 'c.cod_cama')
                        ->whereNull('oc.fecha_hora_liberacion')->where('oc.estado', 'ACTIVA'))
                    ->leftJoin('residentes as r', 'r.cod_residente', '=', 'oc.cod_residente')
                    ->select('c.cod_cama as codigo', 'c.codigo as titulo', 'h.codigo as detalle',
                        'oc.fecha_hora_asignacion as fecha', 'oc.fecha_hora_liberacion as fecha_fin',
                        'h.capacidad', 'c.tipo')
                    ->selectRaw("TRIM(r.nombres || ' ' || r.apellido_paterno) as ocupante");
                $query->addSelect(DB::raw("CASE WHEN oc.cod_ocupacion IS NOT NULL THEN 'OCUPADA' ELSE c.estado END as estado"));
                return $this->armar($query, ['c.codigo', 'h.codigo', 'c.cod_cama'], 'c.estado', 'c.codigo');
            case 'ocupacion':
                $query = DB::table('ocupaciones_cama as oc')
                    ->join('camas as c', 'c.cod_cama', '=', 'oc.cod_cama')
                    ->join('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
                    ->join('residentes as r', 'r.cod_residente', '=', 'oc.cod_residente')
                    ->select('oc.cod_ocupacion as codigo', 'c.codigo as titulo', 'h.codigo as detalle',
                        'oc.fecha_hora_asignacion as fecha',
                        'oc.fecha_hora_liberacion as fecha_fin', 'oc.estado as estado');
                $query->selectRaw("TRIM(r.nombres || ' ' || r.apellido_paterno) as ocupante");
                if ($tab === 'historial') {
                    $query->whereNotNull('oc.fecha_hora_liberacion');
                } else {
                    $query->whereNull('oc.fecha_hora_liberacion')->where('oc.estado', 'ACTIVA');
                }
                return $this->armar($query, ['r.nombres', 'r.apellido_paterno', 'c.codigo', 'h.codigo', 'oc.cod_ocupacion'], 'oc.estado', 'oc.fecha_hora_asignacion');
            case 'jornadas':
                return $this->armar(
                    DB::table('jornadas as j')->join('turnos as t', 't.cod_turno', '=', 'j.cod_turno')
                        ->leftJoinSub(DB::table('asignaciones_personal')->where('estado', 'ACTIVA')
                            ->select('cod_jornada', DB::raw('COUNT(DISTINCT cod_personal) as asignados'))
                            ->groupBy('cod_jornada'), 'ap', 'ap.cod_jornada', '=', 'j.cod_jornada')
                        ->select('j.cod_jornada as codigo', 't.nombre as titulo', 'j.cod_jornada as detalle',
                            'j.fecha_jornada as fecha', 't.hora_inicio', 't.hora_cierre',
                            'ap.asignados', 'j.estado as estado')
                        ->selectRaw("t.hora_inicio || '–' || t.hora_cierre as horario"),
                    ['t.nombre', 'j.cod_jornada'], 'j.estado', 'j.fecha_jornada'
                );
            case 'asignaciones':
                return $this->armar(
                    DB::table('asignaciones_personal as ap')->join('personal as p', 'p.cod_personal', '=', 'ap.cod_personal')
                        ->join('areas as a', 'a.cod_area', '=', 'ap.cod_area')
                        ->join('jornadas as j', 'j.cod_jornada', '=', 'ap.cod_jornada')
                        ->join('turnos as t', 't.cod_turno', '=', 'j.cod_turno')
                        ->select('ap.cod_asignacion_personal as codigo', 'a.nombre as detalle',
                            'ap.fecha_asignacion as fecha', 't.nombre as jornada',
                            'ap.funcion', 'ap.estado as estado')
                        ->selectRaw("TRIM(p.nombres || ' ' || p.apellido_paterno) as titulo"),
                    ['p.nombres', 'p.apellido_paterno', 'a.nombre', 't.nombre', 'ap.cod_asignacion_personal'], 'ap.estado', 'ap.fecha_asignacion'
                );
            case 'contactos':
                return $this->armar(
                    DB::table('contactos as c')
                        ->leftJoin('residentes_contactos as rc', fn ($join) => $join->on('rc.cod_contacto', '=', 'c.cod_contacto')
                            ->where('rc.estado', 'ACTIVO'))
                        ->leftJoin('residentes as r', 'r.cod_residente', '=', 'rc.cod_residente')
                        ->select('c.cod_contacto as codigo', 'c.nombres as titulo',
                            'r.nombres as detalle', 'c.celular as telefono', 'rc.parentesco',
                            'rc.responsable_principal as principal', 'rc.contacto_emergencia as emergencia',
                            'c.estado as estado'),
                    ['c.nombres', 'c.apellido_paterno', 'c.numero_documento', 'c.cod_contacto'], 'c.estado', 'c.cod_contacto'
                );
            case 'documentacion':
                return $this->armar(
                    DB::table('documentos as d')
                        ->leftJoin('residentes as r', 'r.cod_residente', '=', 'd.cod_residente')
                        ->leftJoin('preadmisiones as p', 'p.cod_preadmision', '=', 'd.cod_preadmision')
                        ->leftJoin('contactos as c', 'c.cod_contacto', '=', 'd.cod_contacto')
                        ->where(fn ($query) => $query->whereNotNull('d.cod_residente')
                            ->orWhereNotNull('d.cod_preadmision')->orWhereNotNull('d.cod_contacto'))
                        ->select('d.cod_documento as codigo', 'd.nombre as titulo',
                            'd.tipo_documento as detalle', 'd.fecha_vencimiento as fecha',
                            'd.fecha_validacion as validacion', 'd.estado as estado')
                        ->selectRaw('COALESCE(r.nombres, p.nombres, c.nombres) as titular'),
                    ['d.nombre', 'd.tipo_documento', 'd.cod_documento'], 'd.estado', 'd.cod_documento'
                );
            case 'consentimientos':
                return $this->armar(
                    DB::table('consentimientos as co')->join('residentes as r', 'r.cod_residente', '=', 'co.cod_residente')
                        ->select('co.cod_consentimiento as codigo', 'co.tipo_consentimiento as titulo',
                            'r.nombres as detalle', 'co.fecha_consentimiento as fecha', 'co.estado as estado')
                        ->selectRaw("CASE WHEN co.firma_residente = TRUE THEN 'Residente' ELSE 'Contacto responsable' END as firmante"),
                    ['co.tipo_consentimiento', 'r.nombres', 'r.apellido_paterno', 'co.cod_consentimiento'], 'co.estado', 'co.fecha_consentimiento'
                );
            case 'seguros':
                return $this->armar(
                    DB::table('seguros_residente as s')->join('residentes as r', 'r.cod_residente', '=', 's.cod_residente')
                        ->select('s.cod_seguro as codigo', 's.entidad as titulo', 'r.nombres as detalle',
                            's.plan', 's.numero_afiliacion as afiliacion', 's.titular', 's.estado as estado'),
                    ['s.entidad', 'r.nombres', 'r.apellido_paterno', 's.cod_seguro'], 's.estado', 's.cod_seguro'
                );
            case 'actividades':
                return $this->armar(
                    DB::table('actividades as a')
                        ->join('areas as ar', 'ar.cod_area', '=', 'a.cod_area')
                        ->join('personal as p', 'p.cod_personal', '=', 'a.cod_personal')
                        ->leftJoinSub(DB::table('participantes_actividad')
                            ->select('cod_actividad', DB::raw('COUNT(*) as participantes'))
                            ->groupBy('cod_actividad'), 'pa', 'pa.cod_actividad', '=', 'a.cod_actividad')
                        ->select('a.cod_actividad as codigo', 'a.nombre as titulo',
                            'a.lugar as detalle', 'a.fecha_hora as fecha', 'a.duracion_minutos as duracion',
                            'a.cupo', 'ar.nombre as area', 'p.nombres as responsable', 'pa.participantes',
                            'a.estado as estado'),
                    ['a.nombre', 'a.lugar', 'a.cod_actividad'], 'a.estado', 'a.fecha_hora'
                );
            case 'visitas':
                return $this->armar(
                    DB::table('visitas as v')->join('contactos as c', 'c.cod_contacto', '=', 'v.cod_contacto')
                        ->join('residentes as r', 'r.cod_residente', '=', 'v.cod_residente')
                        ->select('v.cod_visita as codigo', 'c.nombres as titulo', 'r.nombres as detalle',
                            DB::raw('COALESCE(v.fecha_hora_programada, v.fecha_hora_ingreso) as fecha'),
                            'v.fecha_hora_programada as programada', 'v.fecha_hora_ingreso as ingreso',
                            'v.fecha_hora_salida as salida', 'v.motivo', 'v.estado as estado'),
                    ['c.nombres', 'c.apellido_paterno', 'r.nombres', 'v.cod_visita'], 'v.estado', 'v.cod_visita'
                );
            case 'alertas':
                return $this->armar(
                    DB::table('alertas as a')
                        ->join('residentes as r', 'r.cod_residente', '=', 'a.cod_residente')
                        ->leftJoin('personal as p', 'p.cod_personal', '=', 'a.cod_personal_responsable')
                        ->select('a.cod_alerta as codigo', 'a.titulo as titulo',
                            'a.prioridad as detalle', 'a.tipo as origen', 'r.nombres as residente',
                            'p.nombres as responsable', 'a.fecha_hora as fecha', 'a.estado as estado'),
                    ['a.titulo', 'a.cod_alerta'], 'a.estado', 'a.fecha_hora'
                );
            case 'incidentes':
                return $this->armar(
                    DB::table('incidentes as i')->join('residentes as r', 'r.cod_residente', '=', 'i.cod_residente')
                        ->select('i.cod_incidente as codigo', 'i.tipo_incidente as titulo',
                            'i.lugar as detalle', 'r.nombres as residente', 'i.gravedad',
                            'i.requiere_medico', 'i.requiere_derivacion',
                            'i.fecha_hora as fecha', 'i.estado as estado'),
                    ['i.tipo_incidente', 'i.lugar', 'i.cod_incidente'], 'i.estado', 'i.fecha_hora'
                );
        }

        return [];
    }

    private function armar(Builder $query, array $busqueda, ?string $estado, string $orden): array
    {
        return compact('query', 'busqueda', 'estado', 'orden');
    }
}
