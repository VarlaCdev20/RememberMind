<?php

namespace App\Http\Controllers\Administracion;

use App\Backend\Modulos\Administracion\Servicios\ConsultaOperativaService;
use App\Backend\Modulos\Administracion\Servicios\DirectorioResidentesService;
use App\Backend\Modulos\Administracion\Servicios\ExploradorAdministrativoService;
use App\Backend\Modulos\Administracion\Servicios\MapaHabitacionesService;
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
        abort_unless($request->user()->estado === 'ACTIVO' && ! $request->user()->hasRole('FAMILIAR'), 403);
        if (in_array($modulo, ['habitaciones', 'ocupacion'], true)) {
            abort_if($request->user()->hasRole('FAMILIAR'), 403);
        }

        $filtros = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'estado' => match ($modulo) {
                'residentes' => ['nullable', 'string', 'max:30', 'exists:residentes,estado'],
                'habitaciones' => ['nullable', 'string', 'max:30', 'exists:camas,estado'],
                default => ['nullable', 'string', 'max:30'],
            },
            'prioridad' => $modulo === 'alertas' ? ['nullable', 'string', 'max:20'] : ['nullable', 'in:CRITICA,ALTA,MEDIA,BAJA'],
            'fecha' => ['nullable', 'date_format:Y-m-d'],
            'fecha_visita' => $modulo === 'visitas' ? ['nullable', 'in:programacion,ingreso'] : ['prohibited'],
            'mes' => ['nullable', 'date_format:Y-m'],
            'dia' => ['nullable', 'date_format:Y-m-d'],
            'tab' => ['nullable', 'string', 'max:30', ...($modulo === 'ocupacion' ? ['in:actual,historial'] : [])],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', ...($request->filled('desde') ? ['after_or_equal:desde'] : [])],
            'residente' => ['nullable', 'string', 'max:20'],
            'panel_tab' => ['nullable', 'in:resumen,datos,salud,documentos,historial'],
            'vista' => ['nullable', in_array($modulo, ['residentes', 'habitaciones', 'ocupacion'], true) ? 'in:tarjetas,tabla,lista,camas' : 'in:tarjetas,tabla,lista,agenda,cronologia,cobertura,tablero,calendario'],
            'detalle' => ['nullable', 'string', 'max:20'],
            'categoria' => ['nullable', 'string', 'max:180'],
            'funcion' => $modulo === 'asignaciones' ? ['nullable', 'string', 'max:180'] : ['prohibited'],
            'por_pagina' => ['nullable', 'integer', 'in:10,20,50'],
            'page' => ['nullable', 'integer', 'min:1'],
            'orden' => ['nullable', 'in:recientes,antiguas'],
            'piso' => ['nullable', 'string', 'max:30', ...($request->input('piso') === '__sin_piso__' ? [] : ['exists:habitaciones,piso'])],
            'cod_habitacion' => ['nullable', 'string', 'max:20', 'exists:habitaciones,cod_habitacion'],
            'alojamiento' => ['nullable', 'in:con_cama,sin_cama'],
            'disponibilidad' => ['nullable', 'in:ocupado,disponible,no_habilitado'],
            'cama' => ['nullable', 'string', 'max:20'],
        ], [
            'hasta.after_or_equal' => 'La fecha hasta debe ser igual o posterior a la fecha desde.',
            'desde.date_format' => 'Elige una fecha válida para desde.',
            'hasta.date_format' => 'Elige una fecha válida para hasta.',
            'fecha.date_format' => 'Elige una fecha válida en el calendario.',
            'por_pagina.in' => 'Elige 10, 20 o 50 registros por página.',
        ]);
        if (in_array($modulo, ExploradorAdministrativoService::MODULOS, true)) {
            $datos = app(ExploradorAdministrativoService::class)->datos($modulo, $filtros, $request->user());
            if ($request->header('X-RM-Ficha') === '1') {
                abort_unless($datos['detalle'], 404);

                return response()->view('pages.admin.administracion.partials.ficha-fragmento', $datos + compact('filtros', 'definicion'))->header('X-RM-Ficha', '1')->header('Cache-Control', 'private, no-store');
            }

            return view('pages.admin.administracion.operacion', $datos)
                ->with(compact('filtros', 'definicion'));
        }
        if ($modulo === 'habitaciones' || ($modulo === 'ocupacion' && ($filtros['tab'] ?? '') !== 'historial' && $request->user()->can('habitaciones.ver'))) {
            return view('pages.admin.administracion.habitaciones', app(MapaHabitacionesService::class)->datos($filtros, $request->user()))
                ->with(compact('filtros'))->with('esOcupacion', $modulo === 'ocupacion');
        }
        if ($modulo === 'ocupacion') {
            $this->authorize('viewAny', Residente::class);
        }
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
                $registros = $query->orderByDesc($configuracion['orden'])->paginate($modulo === 'ocupacion' ? $porPagina : 15)->withQueryString();
            }
        }

        return view($modulo === 'admisiones' ? 'pages.admin.administracion.admisiones' : 'pages.admin.administracion.operacion', compact('modulo', 'definicion', 'registros', 'filtros', 'columnas', 'reportes', 'tabs', 'tab', 'resumenAdmision', 'resumenResidentes', 'panelResidente', 'panelTab', 'panelDatos', 'vistaResidentes', 'vistaAdmisiones', 'porPagina', 'ordenAdmisiones', 'estadosAdmision'));
    }
}
