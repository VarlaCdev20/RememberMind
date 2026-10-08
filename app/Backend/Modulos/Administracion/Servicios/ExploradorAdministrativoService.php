<?php

namespace App\Backend\Modulos\Administracion\Servicios;

use App\Models\Documento;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Bandejas de coordinación: una consulta autorizada alimenta vistas y resúmenes. */
class ExploradorAdministrativoService
{
    public const MODULOS = ['jornadas', 'asignaciones', 'contactos', 'documentacion', 'consentimientos', 'seguros', 'actividades', 'visitas', 'alertas', 'incidentes'];

    private const PRESENTACION = [
        'jornadas' => ['Organiza la operación diaria', 'Consulta horarios y cobertura por jornada.', 'calendario', ['calendario', 'agenda', 'tarjetas', 'tabla'], 'horario', 'Horarios registrados', 'fecha', 'jornadas'],
        'asignaciones' => ['Revisa la cobertura del equipo', 'Personas, áreas y funciones en cada jornada.', 'cobertura', ['cobertura', 'lista', 'tarjetas', 'tabla'], 'detalle', 'Asignaciones por área', 'dia_jornada', 'asignaciones'],
        'contactos' => ['Encuentra la red de apoyo', 'Contactos, vínculos responsables y canales de comunicación.', 'tarjetas', ['tarjetas', 'lista', 'tabla'], 'vinculos', 'Vínculos activos por contacto', null, 'contactos'],
        'documentacion' => ['Ordena la documentación', 'Consulta validaciones y vencimientos; descarga mediante acceso privado.', 'lista', ['lista', 'tarjetas', 'tabla'], 'detalle', 'Tipos de documento', 'fecha', 'documentos'],
        'consentimientos' => ['Consulta consentimientos registrados', 'Consulta estado, fecha y firmante de cada consentimiento.', 'cronologia', ['cronologia', 'tarjetas', 'tabla'], 'titulo', 'Tipos de consentimiento', 'fecha', 'consentimientos'],
        'seguros' => ['Consulta la cobertura registrada', 'Entidades, planes y referencias vinculadas al residente.', 'tarjetas', ['tarjetas', 'lista', 'tabla'], 'titulo', 'Entidades de cobertura', null, 'seguros'],
        'actividades' => ['Explora la agenda institucional', 'Fechas, lugares, cupos y participación registrados.', 'calendario', ['calendario', 'agenda', 'tarjetas', 'tabla'], 'area', 'Actividades por área', 'fecha', 'actividades'],
        'visitas' => ['Coordina los encuentros', 'Distingue programación de entradas y salidas registradas.', 'calendario', ['calendario', 'agenda', 'lista', 'tabla'], 'estado', 'Estados de visita', 'programada', 'visitas'],
        'alertas' => ['Prioriza el seguimiento', 'Consulta prioridad, responsable y estado sin sustituir la valoración profesional.', 'tablero', ['tablero', 'lista', 'tarjetas', 'tabla'], 'detalle', 'Prioridades registradas', 'fecha', 'alertas'],
        'incidentes' => ['Consulta los sucesos registrados', 'Seguimiento institucional y detalles del registro autorizado.', 'cronologia', ['cronologia', 'lista', 'tabla'], 'gravedad', 'Gravedad registrada por el profesional', 'fecha', 'incidentes'],
    ];

    public function __construct(private ConsultaOperativaService $consulta) {}

    public function datos(string $modulo, array $filtros, User $usuario): array
    {
        abort_unless(in_array($modulo, self::MODULOS, true), 404);
        abort_unless(auth()->id() === $usuario->getKey() && $usuario->estado === 'ACTIVO'
            && ! $usuario->hasRole('FAMILIAR') && $usuario->can($this->consulta->definicion($modulo)['permiso']), 403);
        if (! in_array($modulo, ['jornadas', 'asignaciones', 'actividades'], true)) {
            Gate::forUser($usuario)->authorize('viewAny', Residente::class);
        }
        [$objetivo, $ayuda, $vistaInicial, $vistas, $categoria, $tituloCategoria, $campoFecha, $unidad] = self::PRESENTACION[$modulo];
        if ($modulo === 'visitas' && ($filtros['fecha_visita'] ?? '') === 'ingreso') {
            $campoFecha = 'ingreso';
            $ayuda = 'Consulta entradas registradas, con el periodo elegido en Reportes.';
        }
        $presentacion = compact('objetivo', 'ayuda', 'vistas', 'categoria', 'tituloCategoria', 'campoFecha', 'unidad');
        $tabs = $this->consulta->tabs($modulo);
        $vista = $filtros['vista'] ?? $vistaInicial;
        $tab = $filtros['tab'] ?? ($vista === 'calendario' && array_key_exists('todas', $tabs) ? 'todas' : array_key_first($tabs));
        abort_if($tab !== null && ! array_key_exists($tab, $tabs), 404);
        Validator::make(['vista' => $vista], ['vista' => Rule::in($vistas)])->validate();
        $porPagina = (int) ($filtros['por_pagina'] ?? 10);
        $config = $this->consulta->consulta($modulo, $tab ?? '');
        $sinFiltros = DB::query()->fromSub($config['query'], 'registro');
        $estados = (clone $sinFiltros)->select('registro.estado')->selectRaw('COUNT(*) as cantidad')
            ->groupBy('registro.estado')->orderBy('registro.estado')->get();
        $prioridades = $modulo === 'alertas' ? (clone $sinFiltros)->distinct()->orderBy('registro.detalle')->pluck('registro.detalle')->filter() : collect();
        $opcionesCategoria = in_array($modulo, ['asignaciones', 'actividades'], true)
            ? (clone $sinFiltros)->distinct()->orderBy('registro.'.$categoria)->pluck('registro.'.$categoria)->filter()->values() : collect();
        Validator::make($filtros, [
            'estado' => ['nullable', Rule::in($estados->pluck('estado')->filter()->push('__sin_estado__')->all())],
            'prioridad' => ['nullable', Rule::in($prioridades->all())],
        ])->validate();

        $fuente = clone $config['query'];
        $termino = mb_strtolower(trim((string) ($filtros['search'] ?? '')));
        if ($termino !== '') {
            foreach (preg_split('/\s+/u', $termino) as $palabra) {
                $fuente->where(function ($query) use ($config, $palabra) {
                    foreach ($config['busqueda'] as $campo) {
                        $query->orWhereRaw('LOWER('.$campo.') LIKE ?', ['%'.$palabra.'%']);
                    }
                });
            }
        }
        $base = DB::query()->fromSub($fuente, 'registro');
        if ($campoFecha) {
            if (filled($filtros['desde'] ?? '')) {
                $base->whereDate('registro.'.$campoFecha, '>=', $filtros['desde']);
            }
            if (filled($filtros['hasta'] ?? '')) {
                $base->whereDate('registro.'.$campoFecha, '<=', $filtros['hasta']);
            }
        }
        if ($modulo === 'alertas' && filled($filtros['prioridad'] ?? '')) {
            $base->where('registro.detalle', $filtros['prioridad']);
        }
        if ($modulo === 'visitas' && filled($filtros['fecha'] ?? '')) {
            $base->where(fn ($query) => $query->whereDate('registro.programada', $filtros['fecha'])->orWhereDate('registro.ingreso', $filtros['fecha']));
        }
        $mesCalendario = Carbon::createFromFormat('!Y-m', $filtros['mes'] ?? (filled($filtros['dia'] ?? '') ? substr($filtros['dia'], 0, 7) : now()->format('Y-m')))->startOfMonth();
        if (($vista === 'calendario' || filled($filtros['mes'] ?? '')) && filled($filtros['dia'] ?? '') && substr($filtros['dia'], 0, 7) !== $mesCalendario->format('Y-m')) {
            throw ValidationException::withMessages(['dia' => 'Elige un día del mes mostrado.']);
        }
        $baseSinMes = clone $base;
        if ($campoFecha && in_array($modulo, ['jornadas', 'actividades', 'visitas'], true) && ($vista === 'calendario' || filled($filtros['mes'] ?? ''))) {
            $base->whereDate('registro.'.$campoFecha, '>=', $mesCalendario->toDateString())->whereDate('registro.'.$campoFecha, '<=', $mesCalendario->copy()->endOfMonth()->toDateString());
        }
        $distribucion = (clone $base)->select('registro.estado')->selectRaw('COUNT(*) as cantidad')->groupBy('registro.estado')->orderBy('registro.estado')->get();
        $totalContexto = (int) $distribucion->sum('cantidad');
        $resultados = clone $base;
        $this->aplicarTab($resultados, $modulo, $tab);
        if (filled($filtros['estado'] ?? '')) {
            ($filtros['estado'] === '__sin_estado__') ? $resultados->whereNull('registro.estado') : $resultados->where('registro.estado', $filtros['estado']);
        }
        if (filled($filtros['categoria'] ?? '')) {
            $catalogo = clone $sinFiltros;
            $this->filtrarCategoria($catalogo, $categoria, $filtros['categoria']);
            if (! $catalogo->exists()) {
                throw ValidationException::withMessages(['categoria' => 'Selecciona una categoría registrada en el gráfico.']);
            }
            $this->filtrarCategoria($resultados, $categoria, $filtros['categoria']);
        }
        if ($modulo === 'asignaciones' && filled($filtros['funcion'] ?? '')) {
            $valor = $filtros['funcion'] === '__sin_funcion__' ? '__sin_categoria__' : $filtros['funcion'];
            $catalogo = clone $sinFiltros;
            $this->filtrarCategoria($catalogo, 'funcion', $valor);
            if (! $catalogo->exists()) {
                throw ValidationException::withMessages(['funcion' => 'Selecciona una función registrada.']);
            }
            $this->filtrarCategoria($resultados, 'funcion', $valor);
        }
        $diasCalendario = collect();
        $eventosCalendario = collect();
        $totalCalendario = 0;
        $calendarioSinFecha = 0;
        if ($vista === 'calendario') {
            $sinFecha = clone $baseSinMes;
            $this->aplicarTab($sinFecha, $modulo, $tab);
            if (filled($filtros['estado'] ?? '')) {
                ($filtros['estado'] === '__sin_estado__') ? $sinFecha->whereNull('registro.estado') : $sinFecha->where('registro.estado', $filtros['estado']);
            }
            if (filled($filtros['categoria'] ?? '')) {
                $this->filtrarCategoria($sinFecha, $categoria, $filtros['categoria']);
            }
            $calendarioSinFecha = $sinFecha->whereNull('registro.'.$campoFecha)->count();
            $calendario = (clone $resultados)->whereDate('registro.'.$campoFecha, '>=', $mesCalendario->toDateString())
                ->whereDate('registro.'.$campoFecha, '<=', $mesCalendario->copy()->endOfMonth()->toDateString());
            $diaSql = 'SUBSTR(CAST(registro.'.$campoFecha.' AS TEXT), 1, 10)';
            $diasCalendario = (clone $calendario)->selectRaw($diaSql.' AS dia, COUNT(*) as cantidad')->groupByRaw($diaSql)->orderBy('dia')->get();
            $totalCalendario = (int) $diasCalendario->sum('cantidad');
            $previsualizaciones = (clone $calendario)->select('registro.*')->selectRaw($diaSql.' AS dia_calendario')
                ->selectRaw('ROW_NUMBER() OVER(PARTITION BY '.$diaSql.' ORDER BY registro.'.$campoFecha.', registro.codigo) AS fila_calendario');
            $eventosCalendario = DB::query()->fromSub($previsualizaciones, 'evento')->where('fila_calendario', '<=', 3)->orderBy('dia_calendario')->orderBy('fila_calendario')->get();
            $resultados = $calendario;
        }
        if ($campoFecha && in_array($modulo, ['jornadas', 'actividades', 'visitas'], true) && filled($filtros['dia'] ?? '')) {
            $resultados->whereDate('registro.'.$campoFecha, $filtros['dia']);
        }
        $estadosAnalisis = (clone $resultados)->select('registro.estado')->selectRaw('COUNT(*) as cantidad')->groupBy('registro.estado')->orderBy('registro.estado')->get();
        $totalAnalisis = (int) $estadosAnalisis->sum('cantidad');
        $categorias = (clone $resultados)->select('registro.'.$categoria.' as etiqueta')->selectRaw('COUNT(*) as cantidad')
            ->groupBy('registro.'.$categoria)->orderByDesc('cantidad')->orderBy('etiqueta')->limit(8)->get();
        $historia = $campoFecha ? (clone $resultados)->whereNotNull('registro.'.$campoFecha)
            ->selectRaw('SUBSTR(CAST(registro.'.$campoFecha.' AS TEXT), 1, 7) as mes, COUNT(*) as cantidad')
            ->groupByRaw('SUBSTR(CAST(registro.'.$campoFecha.' AS TEXT), 1, 7)')->orderByDesc('mes')->limit(8)->get()->reverse()->values() : collect();
        $comparacionOperativa = match ($modulo) {
            'jornadas' => (clone $resultados)->select('registro.codigo', 'registro.titulo', 'registro.fecha', 'registro.horario', 'registro.asignados as cantidad')->orderByDesc('registro.asignados')->orderBy('registro.codigo')->limit(8)->get(),
            'asignaciones' => (clone $resultados)->select('registro.funcion as etiqueta')->selectRaw('COUNT(*) as cantidad')->groupBy('registro.funcion')->orderByDesc('cantidad')->limit(8)->get(),
            'actividades' => (clone $resultados)->select('registro.codigo', 'registro.titulo', 'registro.fecha', 'registro.participantes as cantidad', 'registro.cupo')->orderByDesc('registro.participantes')->orderBy('registro.codigo')->limit(8)->get(),
            default => collect(),
        };
        $orden = $campoFecha ? 'registro.'.$campoFecha : 'registro.titulo';
        $ordenInicial = $vista === 'agenda' ? 'antiguas' : ($campoFecha ? 'recientes' : 'antiguas');
        $direccion = ($filtros['orden'] ?? $ordenInicial) === 'antiguas' ? 'asc' : 'desc';
        $registros = $resultados->select('registro.*')->orderBy($orden, $direccion)->orderBy('registro.codigo')->paginate($porPagina)->appends(request()->except('detalle'));
        $detalle = null;
        $vinculos = collect();
        $descarga = false;
        $camposDetalle = [];
        $historialAlerta = collect();
        if (filled($filtros['detalle'] ?? '')) {
            $detalle = (clone $sinFiltros)->where('registro.codigo', $filtros['detalle'])->select('registro.*')->first();
            abort_unless($detalle, 404);
            if ($detalle->_cod_residente ?? null) {
                Gate::forUser($usuario)->authorize('view', Residente::findOrFail($detalle->_cod_residente));
            }
            $camposDetalle = $this->camposDetalle($modulo, $detalle->codigo);
            if ($modulo === 'alertas') {
                $historialAlerta = DB::table('eventos_alerta')->where('cod_alerta', $detalle->codigo)
                    ->select('tipo_evento', 'estado_anterior', 'estado_nuevo', 'fecha_hora', 'descripcion')
                    ->orderByDesc('fecha_hora')->orderByDesc('cod_evento_alerta')->limit(12)->get();
            }
            if ($modulo === 'contactos') {
                $vinculos = DB::table('residentes_contactos as rc')->join('residentes as r', 'r.cod_residente', '=', 'rc.cod_residente')
                    ->where('rc.cod_contacto', $detalle->codigo)->where('rc.estado', 'ACTIVO')
                    ->select('rc.parentesco', 'rc.responsable_principal', 'rc.contacto_emergencia', 'r.cod_residente', 'r.nombres', 'r.apellido_paterno')->orderBy('r.nombres')->limit(20)->get();
                $residentes = Residente::whereKey($vinculos->pluck('cod_residente'))->get()->keyBy('cod_residente');
                $vinculos = $vinculos->filter(fn ($vinculo) => Gate::forUser($usuario)->allows('view', $residentes->get($vinculo->cod_residente)));
            }
            if ($modulo === 'documentacion') {
                $descarga = Storage::disk('local')->exists(Documento::findOrFail($detalle->codigo)->ruta_archivo);
            }
        }
        $columnas = $this->consulta->columnas($modulo);

        return compact('modulo', 'presentacion', 'tabs', 'tab', 'vista', 'ordenInicial', 'porPagina', 'registros', 'estados', 'prioridades', 'distribucion', 'estadosAnalisis', 'categorias', 'historia', 'totalContexto', 'totalAnalisis', 'detalle', 'vinculos', 'descarga', 'camposDetalle', 'historialAlerta', 'columnas', 'opcionesCategoria', 'mesCalendario', 'diasCalendario', 'eventosCalendario', 'totalCalendario', 'calendarioSinFecha', 'comparacionOperativa');
    }

    private function camposDetalle(string $modulo, string $codigo): array
    {
        [$tabla, $pk, $campos] = match ($modulo) {
            'jornadas' => [null, null, []],
            'asignaciones' => ['asignaciones_personal', 'cod_asignacion_personal', ['tipo_asignacion' => 'Tipo de asignación', 'observacion' => 'Observación registrada']],
            'contactos' => ['contactos', 'cod_contacto', ['telefono' => 'Teléfono alternativo', 'correo' => 'Correo', 'observacion' => 'Observación registrada']],
            'documentacion' => ['documentos', 'cod_documento', ['tipo_archivo' => 'Tipo de archivo', 'observacion' => 'Observación registrada']],
            'consentimientos' => ['consentimientos', 'cod_consentimiento', ['observacion' => 'Observación registrada']],
            'seguros' => ['seguros_residente', 'cod_seguro', ['cobertura' => 'Cobertura registrada', 'telefono' => 'Teléfono de la entidad']],
            'actividades' => ['actividades', 'cod_actividad', ['tipo' => 'Tipo de actividad', 'descripcion' => 'Descripción registrada', 'observacion' => 'Observación registrada']],
            'visitas' => ['visitas', 'cod_visita', ['observacion' => 'Observación registrada']],
            'alertas' => ['alertas', 'cod_alerta', ['descripcion' => 'Descripción registrada', 'generacion' => 'Origen de generación']],
            'incidentes' => ['incidentes', 'cod_incidente', ['descripcion' => 'Descripción registrada', 'medida_inmediata' => 'Medida registrada por el profesional', 'observacion' => 'Observación registrada']],
        };
        if (! $campos) {
            return [];
        }
        $registro = DB::table($tabla)->where($pk, $codigo)->first(array_keys($campos));

        return collect($campos)->mapWithKeys(fn ($etiqueta, $campo) => [$etiqueta => $registro->{$campo} ?? null])->all();
    }

    private function filtrarCategoria(Builder $query, string $campo, string $valor): void
    {
        if ($valor === '__sin_categoria__') {
            $query->where(fn ($where) => $where->whereNull('registro.'.$campo)->orWhereRaw('CAST(registro.'.$campo." AS TEXT) = ''"));
        } else {
            $query->whereRaw('CAST(registro.'.$campo.' AS TEXT) = ?', [$valor]);
        }
    }

    private function aplicarTab(Builder $query, string $modulo, ?string $tab): void
    {
        if ($modulo === 'jornadas') {
            match ($tab) {
                'hoy' => $query->whereDate('registro.fecha', today()),
                'proximas' => $query->whereDate('registro.fecha', '>', today()),
                'finalizadas' => $query->where('registro.estado', 'FINALIZADA'),
                default => null,
            };
        } elseif ($modulo === 'documentacion') {
            match ($tab) {
                'pendientes' => $query->where('registro.estado', 'PENDIENTE'),
                'por_vencer' => $query->whereBetween('registro.fecha', [today()->toDateString(), today()->addDays(30)->endOfDay()]),
                'vencidos' => $query->whereDate('registro.fecha', '<', today()),
                'validados' => $query->whereNotNull('registro.validacion'),
                default => null,
            };
        } elseif ($modulo === 'consentimientos' && $tab !== 'todos') {
            $query->where('registro.estado', match ($tab) {
                'activos' => 'VIGENTE', 'revocados' => 'REVOCADO', 'anulados' => 'ANULADO'
            });
        } elseif ($modulo === 'visitas') {
            match ($tab) {
                'hoy' => $query->where(fn ($q) => $q->whereDate('registro.programada', today())->orWhereDate('registro.ingreso', today())),
                'programadas' => $query->whereNull('registro.ingreso')->whereNotNull('registro.programada'),
                'dentro' => $query->whereNotNull('registro.ingreso')->whereNull('registro.salida'),
                'finalizadas' => $query->whereNotNull('registro.salida'),
                default => null,
            };
        } elseif ($modulo === 'alertas' && $tab !== 'todas') {
            $query->where('registro.estado', match ($tab) {
                'abiertas' => 'ABIERTA', 'reconocidas' => 'RECONOCIDA', 'asignadas' => 'ASIGNADA', 'en_atencion' => 'EN_ATENCION', 'cerradas' => 'CERRADA'
            });
        }
    }
}
