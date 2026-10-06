<?php

namespace App\Http\Controllers\Administracion;

use App\Backend\Modulos\Administracion\Servicios\ConsultaOperativaService;
use App\Backend\Modulos\Administracion\Servicios\DirectorioResidentesService;
use App\Backend\Modulos\Administracion\Servicios\PanelResidenteService;
use App\Http\Controllers\Controller;
use App\Models\Residente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperacionController extends Controller
{
    public function index(Request $request, ConsultaOperativaService $consulta, PanelResidenteService $panelService, DirectorioResidentesService $directorio, string $modulo)
    {
        $definicion = $consulta->definicion($modulo);
        abort_unless($definicion, 404);
        abort_unless($request->user()?->can($definicion['permiso']), 403);

        $filtros = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'estado' => $modulo === 'residentes' ? ['nullable', 'string', 'max:30', 'exists:residentes,estado'] : ['nullable', 'string', 'max:30'],
            'prioridad' => ['nullable', 'in:CRITICA,ALTA,MEDIA,BAJA'],
            'fecha' => ['nullable', 'date'],
            'tab' => ['nullable', 'string', 'max:30'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'residente' => ['nullable', 'string', 'max:20'],
            'panel_tab' => ['nullable', 'in:resumen,datos,salud,documentos,historial'],
            'vista' => ['nullable', $modulo === 'residentes' ? 'in:tarjetas,tabla,lista,camas' : 'in:tarjetas,tabla,lista'],
            'por_pagina' => ['nullable', 'integer', 'in:10,20,50'],
            'page' => ['nullable', 'integer', 'min:1'],
            'orden' => ['nullable', 'in:recientes,antiguas'],
            'piso' => ['nullable', 'string', 'max:30', ...($request->input('piso') === '__sin_piso__' ? [] : ['exists:habitaciones,piso'])],
            'cod_habitacion' => ['nullable', 'string', 'max:20', 'exists:habitaciones,cod_habitacion'],
            'alojamiento' => ['nullable', 'in:con_cama,sin_cama'],
        ]);
        $registros = null;
        $reportes = [];
        $columnas = $consulta->columnas($modulo);
        $tabs = $consulta->tabs($modulo);
        $resumenAdmision = null;
        $resumenResidentes = null;
        $panelResidente = null;
        $panelTab = 'resumen';
        $panelDatos = [];
        $vistaResidentes = $filtros['vista'] ?? 'tarjetas';
        $vistaAdmisiones = $filtros['vista'] ?? 'tabla';
        $porPagina = (int) ($filtros['por_pagina'] ?? 10);
        $ordenAdmisiones = $filtros['orden'] ?? 'recientes';
        $estadosAdmision = collect();
        if ($modulo === 'admisiones') {
            $estadosAdmision = $consulta->estadosAdmision();
            $resumenAdmision = $consulta->resumenAdmision($estadosAdmision);
        }
        if ($modulo === 'residentes') {
            $this->authorize('viewAny', Residente::class);
            if (filled($filtros['residente'] ?? null)) {
                $panelResidente = Residente::query()->findOrFail($filtros['residente']);
                $this->authorize('view', $panelResidente);
                $panelTab = $filtros['panel_tab'] ?? 'resumen';
                $panelDatos = $panelService->datos($panelResidente, $panelTab);
            }

            return view('pages.admin.administracion.residentes', $directorio->datos($filtros, $vistaResidentes, $porPagina))
                ->with(compact('modulo', 'definicion', 'filtros', 'columnas', 'panelResidente', 'panelTab', 'panelDatos', 'vistaResidentes', 'porPagina'));
        }
        $tab = $filtros['tab'] ?? ($modulo === 'admisiones' && $resumenAdmision['por_formalizar'] === 0
            ? 'admitidos' : array_key_first($tabs));
        if ($tab !== null && ! array_key_exists($tab, $tabs)) {
            abort(404);
        }
        if ($modulo === 'reportes') {
            $desde = $filtros['desde'] ?? now()->startOfMonth()->toDateString();
            $hasta = $filtros['hasta'] ?? now()->toDateString();
            $definiciones = [
                ['titulo' => 'Preadmisiones', 'tabla' => 'preadmisiones', 'fecha' => 'fecha_solicitud', 'permiso' => 'preadmisiones.ver', 'ruta' => 'admin.admisiones.preadmisiones', 'icono' => 'ph-clipboard-text'],
                ['titulo' => 'Admisiones', 'tabla' => 'admisiones', 'fecha' => 'fecha_hora_admision', 'permiso' => 'admisiones.ver_dashboard', 'ruta' => 'admin.administracion.admisiones', 'icono' => 'ph-user-check'],
                ['titulo' => 'Visitas', 'tabla' => 'visitas', 'fecha' => 'fecha_hora_ingreso', 'permiso' => 'visitas.ver', 'ruta' => 'admin.administracion.visitas', 'icono' => 'ph-door-open'],
                ['titulo' => 'Actividades', 'tabla' => 'actividades', 'fecha' => 'fecha_hora', 'permiso' => 'actividades.ver', 'ruta' => 'admin.administracion.actividades', 'icono' => 'ph-calendar-dots'],
                ['titulo' => 'Alertas', 'tabla' => 'alertas', 'fecha' => 'fecha_hora', 'permiso' => 'alertas.ver', 'ruta' => 'admin.administracion.alertas', 'icono' => 'ph-bell-ringing'],
                ['titulo' => 'Incidentes', 'tabla' => 'incidentes', 'fecha' => 'fecha_hora', 'permiso' => 'incidentes.ver', 'ruta' => 'admin.administracion.incidentes', 'icono' => 'ph-warning-octagon'],
            ];
            foreach ($definiciones as $reporte) {
                if (! $request->user()->can($reporte['permiso'])) {
                    continue;
                }
                $reporte['total'] = DB::table($reporte['tabla'])
                    ->whereDate($reporte['fecha'], '>=', $desde)
                    ->whereDate($reporte['fecha'], '<=', $hasta)
                    ->count();
                $reportes[] = $reporte;
            }
            if ($request->user()->can('ocupaciones_cama.ver')) {
                $reportes[] = [
                    'titulo' => 'Ocupación actual',
                    'total' => DB::table('ocupaciones_cama as oc')->join('camas as c', 'c.cod_cama', '=', 'oc.cod_cama')
                        ->join('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
                        ->where('oc.estado', 'ACTIVA')->whereNull('oc.fecha_hora_liberacion')
                        ->whereIn('c.estado', ['ACTIVA', 'ACTIVO', 'DISPONIBLE'])
                        ->whereIn('h.estado', ['ACTIVA', 'ACTIVO', 'DISPONIBLE'])->count(),
                    'ruta' => 'admin.administracion.ocupacion',
                    'icono' => 'ph-bed',
                ];
            }
        } else {
            $configuracion = $consulta->consulta($modulo, $tab ?? '');
            $query = $configuracion['query'];
            if ($modulo === 'jornadas') {
                match ($tab) {
                    'hoy' => $query->whereDate('j.fecha_jornada', today()),
                    'proximas' => $query->whereDate('j.fecha_jornada', '>', today()),
                    'finalizadas' => $query->where('j.estado', 'FINALIZADA'),
                    default => null,
                };
            }
            if ($modulo === 'documentacion') {
                match ($tab) {
                    'pendientes' => $query->where('d.estado', 'PENDIENTE'),
                    'por_vencer' => $query->whereBetween('d.fecha_vencimiento', [today(), today()->addDays(30)]),
                    'vencidos' => $query->whereDate('d.fecha_vencimiento', '<', today()),
                    'validados' => $query->whereNotNull('d.fecha_validacion'),
                    default => null,
                };
            }
            if ($modulo === 'consentimientos' && $tab !== 'todos') {
                $query->where('co.estado', match ($tab) {
                    'activos' => 'VIGENTE', 'revocados' => 'REVOCADO', 'anulados' => 'ANULADO',
                });
            }
            if ($modulo === 'visitas') {
                match ($tab) {
                    'hoy' => $query->where(fn ($where) => $where->whereDate('v.fecha_hora_programada', today())->orWhereDate('v.fecha_hora_ingreso', today())),
                    'programadas' => $query->whereNull('v.fecha_hora_ingreso')->whereNotNull('v.fecha_hora_programada'),
                    'dentro' => $query->whereNotNull('v.fecha_hora_ingreso')->whereNull('v.fecha_hora_salida'),
                    'finalizadas' => $query->whereNotNull('v.fecha_hora_salida'),
                    default => null,
                };
            }
            if ($modulo === 'alertas' && $tab !== 'todas') {
                $query->where('a.estado', match ($tab) {
                    'abiertas' => 'ABIERTA', 'reconocidas' => 'RECONOCIDA',
                    'asignadas' => 'ASIGNADA', 'en_atencion' => 'EN_ATENCION', 'cerradas' => 'CERRADA',
                });
            }
            $termino = trim((string) ($filtros['search'] ?? ''));
            if ($termino !== '') {
                $query->where(function ($where) use ($configuracion, $termino) {
                    foreach ($configuracion['busqueda'] as $campo) {
                        $where->orWhere($campo, 'like', '%'.$termino.'%');
                    }
                });
            }
            if (! empty($filtros['estado']) && $configuracion['estado']) {
                $query->where($configuracion['estado'], $filtros['estado']);
            }
            if ($modulo === 'alertas' && ! empty($filtros['prioridad'])) {
                $query->where('a.prioridad', $filtros['prioridad']);
            }
            if ($modulo === 'visitas' && ! empty($filtros['fecha'])) {
                $query->where(function ($where) use ($filtros) {
                    $where->whereDate('v.fecha_hora_programada', $filtros['fecha'])
                        ->orWhereDate('v.fecha_hora_ingreso', $filtros['fecha']);
                });
            }
            if ($modulo === 'admisiones') {
                if (! empty($filtros['desde'])) {
                    $query->whereDate($configuracion['orden'], '>=', $filtros['desde']);
                }
                if (! empty($filtros['hasta'])) {
                    $query->whereDate($configuracion['orden'], '<=', $filtros['hasta']);
                }
                $direccion = $ordenAdmisiones === 'antiguas' ? 'asc' : 'desc';
                $registros = $query->orderBy($configuracion['orden'], $direccion)->orderBy('codigo', $direccion)
                    ->paginate($porPagina)->withQueryString();
            } else {
                $registros = $query->orderByDesc($configuracion['orden'])->paginate(15)->withQueryString();
            }
        }

        return view($modulo === 'admisiones' ? 'pages.admin.administracion.admisiones' : 'pages.admin.administracion.operacion', compact('modulo', 'definicion', 'registros', 'filtros', 'columnas', 'reportes', 'tabs', 'tab', 'resumenAdmision', 'resumenResidentes', 'panelResidente', 'panelTab', 'panelDatos', 'vistaResidentes', 'vistaAdmisiones', 'porPagina', 'ordenAdmisiones', 'estadosAdmision'));
    }
}
