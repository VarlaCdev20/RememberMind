<?php

namespace App\Livewire\Clinica;

use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AtencionAdulto;
use App\Models\EvaluacionGeriatrica;
use App\Models\Habitacion;
use App\Models\MedicacionAdulto;
use App\Models\NotaEvolucionMedica;
use App\Models\SignosVitalesAdulto;
use App\Models\ValoracionFuncionalAdulto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class PacientesSeguimientoPanel extends Component
{
    use WithPagination;

    private const TABS = [
        'activos',
        'historial',
        'pendientes',
        'interconsultas',
    ];

    /**
     * Estados realmente definidos en el flujo actual del proyecto.
     */
    private const ESTADOS_EN_RESIDENCIA = [
        'ACTIVO',
        'ADMITIDO',
        'ASIGNADO',
        'EN_SEGUIMIENTO_ACTIVO',
        'OBSERVADO',
        'SEGUIMIENTO_ESPECIAL',
    ];

    private const ESTADOS_PENDIENTE_VALORACION = [
        'PENDIENTE_VALORACION_MEDICA',
    ];

    private const ESTADOS_HISTORICOS = [
        'ARCHIVADO',
        'INACTIVO',
        'NO_ADMITIDO',
        'DERIVADO',
        'EGRESADO',
        'FALLECIDO',
        'RETIRADO',
        'TRASLADADO',
    ];

    public string $busqueda = '';
    public string $tab = 'activos';

    public string $filtroEstado = '';
    public string $filtroAlertas = '';
    public string $filtroControl = '';
    public string $filtroRiesgo = '';
    public string $filtroDependencia = '';
    public string $filtroHabitacion = '';

    public string $orden = 'nombre';

    protected $listeners = [
        'nota-evolucion-guardada' => '$refresh',
        'signos-actualizados' => '$refresh',
        'signos-guardados' => '$refresh',
        'valoracion-barthel-guardada' => '$refresh',
        'medicacion-actualizada' => '$refresh',
        'alerta-actualizada' => '$refresh',
    ];

    protected $queryString = [
        'busqueda' => ['except' => ''],
        'tab' => ['except' => 'activos'],
        'filtroEstado' => ['except' => ''],
        'filtroAlertas' => ['except' => ''],
        'filtroControl' => ['except' => ''],
        'filtroRiesgo' => ['except' => ''],
        'filtroDependencia' => ['except' => ''],
        'filtroHabitacion' => ['except' => ''],
        'orden' => ['except' => 'nombre'],
    ];

    public function mount(): void
    {
        $this->autorizarVista();

        $routeName = request()->route()?->getName() ?? '';

        if ($routeName === 'admin.medico.pacientes.historial') {
            $this->tab = 'historial';
        } elseif ($routeName === 'admin.medico.interconsultas') {
            $this->tab = 'interconsultas';
        }

        if (!in_array($this->tab, self::TABS, true)) {
            $this->tab = 'activos';
        }

        $this->normalizarFiltros();
    }

    public function updatedBusqueda(): void
    {
        $this->busqueda = mb_substr(trim($this->busqueda), 0, 120);
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroAlertas(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroControl(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroRiesgo(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroDependencia(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroHabitacion(): void
    {
        $this->resetPage();
    }

    public function updatedOrden(): void
    {
        if (!in_array($this->orden, ['nombre', 'alertas', 'reciente'], true)) {
            $this->orden = 'nombre';
        }

        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        abort_unless(in_array($tab, self::TABS, true), 422);

        if ($this->tab === $tab) {
            return;
        }

        $this->tab = $tab;

        /**
         * La búsqueda es universal y se conserva.
         * Los demás filtros son contextuales y se limpian para evitar
         * contradicciones como "Históricos + Estado ADMITIDO".
         */
        $this->limpiarFiltrosContextuales(false);
        $this->orden = 'nombre';

        $this->resetPage();
    }

    public function toggleFiltroAlertas(): void
    {
        abort_unless($this->tab === 'activos', 422);

        $this->filtroAlertas = $this->filtroAlertas === 'ABIERTAS'
            ? ''
            : 'ABIERTAS';

        $this->resetPage();
    }

    public function toggleFiltroControl(): void
    {
        abort_unless($this->tab === 'activos', 422);

        $this->filtroControl = $this->filtroControl === 'PROXIMO'
            ? ''
            : 'PROXIMO';

        $this->resetPage();
    }

    public function limpiarBusqueda(): void
    {
        $this->busqueda = '';
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->limpiarFiltrosContextuales(false);
        $this->orden = 'nombre';
        $this->resetPage();
    }

    public function limpiarFiltro(string $filtro): void
    {
        $permitidos = [
            'estado',
            'alertas',
            'control',
            'riesgo',
            'dependencia',
            'habitacion',
        ];

        abort_unless(in_array($filtro, $permitidos, true), 422);

        match ($filtro) {
            'estado' => $this->filtroEstado = '',
            'alertas' => $this->filtroAlertas = '',
            'control' => $this->filtroControl = '',
            'riesgo' => $this->filtroRiesgo = '',
            'dependencia' => $this->filtroDependencia = '',
            'habitacion' => $this->filtroHabitacion = '',
        };

        $this->resetPage();
    }

    public function abrirFicha(string $codAm): void
    {
        $this->autorizarVista();

        abort_unless(
            Auth::user()?->can('adultos.ver_expediente'),
            403,
            'No cuenta con permiso para consultar el expediente.'
        );

        AdultoMayor::query()->findOrFail($codAm);

        $this->redirect(
            route('admin.medico.paciente.ficha', $codAm)
        );
    }

    public function nuevaNota(string $codAm): void
    {
        $this->autorizarMutacion('atenciones.crear');

        $adulto = AdultoMayor::query()
            ->with('estado')
            ->findOrFail($codAm);

        $this->autorizarResidenteModificable($adulto);

        $this->dispatch(
            'abrir-nota-evolucion',
            cod_am: $codAm
        );
    }

    public function nuevosSignos(string $codAm): void
    {
        $this->autorizarMutacion('signos_vitales.crear');

        $adulto = AdultoMayor::query()
            ->with('estado')
            ->findOrFail($codAm);

        $this->autorizarResidenteModificable($adulto);

        $this->dispatch(
            'abrir-signos-vitales-medico',
            cod_am: $codAm
        );
    }

    public function render()
    {
        $this->autorizarVista();
        $this->normalizarFiltros();

        $base = $this->querySegunVista();

        $this->aplicarBusqueda($base);
        $this->aplicarFiltros($base);
        $this->aplicarOrden($base);

        $pacientes = $base
            ->with(['estado', 'habitacion', 'cama'])
            ->paginate(12);

        $codAms = $pacientes
            ->getCollection()
            ->pluck('cod_am')
            ->filter()
            ->values()
            ->all();

        $datosPagina = $this->cargarDatosClinicosPagina($codAms);

        $cntActivos = $this->queryActivos()->count();
        $cntHistorial = $this->queryHistoricos()->count();
        $cntPendientes = $this->queryPendientes()->count();
        $cntInterconsultas = $this->queryInterconsultas()->count();

        $metricasVista = $this->metricasVista(
            $cntActivos,
            $cntHistorial,
            $cntPendientes,
            $cntInterconsultas
        );

        $estadosDisponibles = $this->estadosDisponiblesParaVista();
        $habitacionesDisponibles = $this->habitacionesDisponibles();
        $riesgosDisponibles = $this->riesgosCognitivosDisponibles();
        $dependenciasDisponibles = $this->dependenciasDisponibles();

        $filtrosActivos = $this->filtrosActivosEtiquetados(
            $estadosDisponibles,
            $habitacionesDisponibles
        );

        return view(
            'livewire.clinica.pacientes-seguimiento-panel',
            array_merge(
                [
                    'pacientes' => $pacientes,
                    'cntActivos' => $cntActivos,
                    'cntHistorial' => $cntHistorial,
                    'cntPendientes' => $cntPendientes,
                    'cntInterconsultas' => $cntInterconsultas,
                    'metricasVista' => $metricasVista,
                    'estadosDisponibles' => $estadosDisponibles,
                    'habitacionesDisponibles' => $habitacionesDisponibles,
                    'riesgosDisponibles' => $riesgosDisponibles,
                    'dependenciasDisponibles' => $dependenciasDisponibles,
                    'filtrosActivos' => $filtrosActivos,
                ],
                $datosPagina
            )
        )->layout('layouts.sistema');
    }

    private function querySegunVista(): Builder
    {
        return match ($this->tab) {
            'historial' => $this->queryHistoricos(),
            'pendientes' => $this->queryPendientes(),
            'interconsultas' => $this->queryInterconsultas(),
            default => $this->queryActivos(),
        };
    }

    private function queryActivos(): Builder
    {
        return AdultoMayor::query()
            ->whereNull('archivado_en')
            ->whereHas(
                'estado',
                fn(Builder $q) => $q->whereIn(
                    'estado',
                    self::ESTADOS_EN_RESIDENCIA
                )
            );
    }

    private function queryPendientes(): Builder
    {
        return AdultoMayor::query()
            ->whereNull('archivado_en')
            ->whereHas(
                'estado',
                fn(Builder $q) => $q->whereIn(
                    'estado',
                    self::ESTADOS_PENDIENTE_VALORACION
                )
            );
    }

    private function queryInterconsultas(): Builder
    {
        return $this->queryActivos()
            ->whereHas(
                'notas',
                fn(Builder $q) => $q
                    ->where('tipo_nota', 'INTERCONSULTA')
                    ->where('estado', 'ACTIVO')
            );
    }

    private function queryHistoricos(): Builder
    {
        return AdultoMayor::query()
            ->where(function (Builder $q) {
                $q->whereNotNull('archivado_en')
                    ->orWhereHas(
                        'estado',
                        fn(Builder $estado) => $estado->whereIn(
                            'estado',
                            self::ESTADOS_HISTORICOS
                        )
                    );
            });
    }

    private function aplicarBusqueda(Builder $query): void
    {
        $busqueda = trim($this->busqueda);

        if ($busqueda === '') {
            return;
        }

        /**
         * Cada término debe aparecer en alguno de los campos buscables.
         * Esto permite encontrar "María Pérez", aunque nombres y apellido
         * estén en columnas distintas.
         */
        $terminos = collect(
            preg_split('/\s+/u', $busqueda) ?: []
        )
            ->filter()
            ->take(6);

        foreach ($terminos as $termino) {
            $query->where(function (Builder $q) use ($termino) {
                $like = '%' . $termino . '%';

                $q->where('nombres', 'like', $like)
                    ->orWhere('ap_paterno', 'like', $like)
                    ->orWhere('ap_materno', 'like', $like)
                    ->orWhere('ci', 'like', $like)
                    ->orWhere('cod_am', 'like', $like)
                    ->orWhereHas(
                        'habitacion',
                        fn(Builder $hab) => $hab
                            ->where('codigo', 'like', $like)
                            ->orWhere('nombre', 'like', $like)
                            ->orWhere('ubicacion', 'like', $like)
                    )
                    ->orWhereHas(
                        'cama',
                        fn(Builder $cama) => $cama
                            ->where('codigo', 'like', $like)
                            ->orWhere('numero', 'like', $like)
                    );
            });
        }
    }

    private function aplicarFiltros(Builder $query): void
    {
        if ($this->filtroEstado !== '') {
            $query->whereHas(
                'estado',
                fn(Builder $q) => $q->where(
                    'estado',
                    $this->filtroEstado
                )
            );
        }

        if ($this->tab !== 'activos') {
            return;
        }

        if ($this->filtroAlertas === 'ABIERTAS') {
            $query->whereHas('alertasAbiertas');
        }

        if ($this->filtroControl === 'PROXIMO') {
            $desde = today()->toDateString();
            $hasta = today()->addDays(6)->toDateString();

            $query->whereHas(
                'atenciones',
                fn(Builder $atencion) => $atencion
                    ->whereBetween('fecha', [$desde, $hasta])
                    ->where(function (Builder $estado) {
                        $estado->whereNull('estado')
                            ->orWhere('estado', '!=', 'ANULADA');
                    })
                    ->whereHas(
                        'tipoAtencion',
                        fn(Builder $tipo) => $tipo->where(
                            'nombre',
                            'Médica General'
                        )
                    )
            );
        }

        if ($this->filtroHabitacion !== '') {
            $query->where(
                'cod_habitacion',
                $this->filtroHabitacion
            );
        }

        if ($this->filtroDependencia !== '') {
            $query->whereHas(
                'valoracionesFuncionales',
                fn(Builder $q) => $q
                    ->vigente()
                    ->where(
                        'nivel_dependencia',
                        $this->filtroDependencia
                    )
            );
        }

        if ($this->filtroRiesgo !== '') {
            $riesgo = $this->filtroRiesgo;

            /**
             * Filtra por la evaluación cognitiva MÁS RECIENTE de cada residente,
             * no por cualquier evaluación histórica.
             */
            $query->whereIn(
                'adulto_mayor.cod_am',
                function ($sub) use ($riesgo) {
                    $sub->select('eg.cod_am')
                        ->from('evaluaciones_geriatricas as eg')
                        ->join(
                            'instrumentos_geriatricos as ig',
                            'ig.cod_instrumento',
                            '=',
                            'eg.cod_instrumento'
                        )
                        ->where('ig.cod_area', 'ARE_COG')
                        ->whereNull('eg.deleted_at')
                        ->where('eg.nivel_riesgo', $riesgo)
                        ->whereNotExists(function ($newer) {
                            $newer->selectRaw('1')
                                ->from('evaluaciones_geriatricas as eg2')
                                ->join(
                                    'instrumentos_geriatricos as ig2',
                                    'ig2.cod_instrumento',
                                    '=',
                                    'eg2.cod_instrumento'
                                )
                                ->whereColumn(
                                    'eg2.cod_am',
                                    'eg.cod_am'
                                )
                                ->where(
                                    'ig2.cod_area',
                                    'ARE_COG'
                                )
                                ->whereNull('eg2.deleted_at')
                                ->where(function ($q) {
                                    $q->whereColumn(
                                        'eg2.fecha_eval',
                                        '>',
                                        'eg.fecha_eval'
                                    )->orWhere(function ($sameDate) {
                                        $sameDate
                                            ->whereColumn(
                                                'eg2.fecha_eval',
                                                'eg.fecha_eval'
                                            )
                                            ->whereColumn(
                                                'eg2.created_at',
                                                '>',
                                                'eg.created_at'
                                            );
                                    });
                                });
                        });
                }
            );
        }
    }

    private function aplicarOrden(Builder $query): void
    {
        if ($this->orden === 'alertas' && $this->tab === 'activos') {
            $query->withCount([
                'alertas as alertas_abiertas_orden' => fn(Builder $q) =>
                $q->whereIn(
                    'estado',
                    ['ABIERTA', 'EN_ATENCION']
                ),
            ])->orderByDesc('alertas_abiertas_orden')
                ->orderBy('ap_paterno')
                ->orderBy('nombres');

            return;
        }

        if ($this->orden === 'reciente') {
            $query->orderByDesc('updated_at')
                ->orderBy('ap_paterno')
                ->orderBy('nombres');

            return;
        }

        $query->orderBy('ap_paterno')
            ->orderBy('ap_materno')
            ->orderBy('nombres');
    }

    private function cargarDatosClinicosPagina(array $codAms): array
    {
        if ($codAms === []) {
            return [
                'ultimosSignos' => collect(),
                'ultimasNotas' => collect(),
                'ultimasInterconsultas' => collect(),
                'cntMedicacion' => collect(),
                'alertasPagina' => collect(),
                'barthelPagina' => collect(),
                'cognicionPagina' => collect(),
                'proximosControles' => collect(),
            ];
        }

        /**
         * unique() después del orden DESC conserva el registro realmente más reciente.
         * keyBy() directamente sobre una colección con duplicados reemplazaría
         * el primero por registros más antiguos.
         */
        $ultimosSignos = SignosVitalesAdulto::query()
            ->whereIn('cod_am', $codAms)
            ->where('estado', 'VIGENTE')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->orderByDesc('created_at')
            ->get()
            ->unique('cod_am')
            ->keyBy('cod_am');

        $ultimasNotas = NotaEvolucionMedica::query()
            ->whereIn('cod_am', $codAms)
            ->where('estado', 'ACTIVO')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->orderByDesc('created_at')
            ->get()
            ->unique('cod_am')
            ->keyBy('cod_am');

        $ultimasInterconsultas = NotaEvolucionMedica::query()
            ->whereIn('cod_am', $codAms)
            ->where('estado', 'ACTIVO')
            ->where('tipo_nota', 'INTERCONSULTA')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->orderByDesc('created_at')
            ->get()
            ->unique('cod_am')
            ->keyBy('cod_am');

        $cntMedicacion = MedicacionAdulto::query()
            ->whereIn('cod_am', $codAms)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
            ->selectRaw('cod_am, COUNT(*) as total')
            ->groupBy('cod_am')
            ->pluck('total', 'cod_am');

        $alertasPagina = AlertaAdulto::query()
            ->whereIn('cod_am', $codAms)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('cod_am');

        $barthelPagina = ValoracionFuncionalAdulto::query()
            ->whereIn('cod_am', $codAms)
            ->vigente()
            ->orderByDesc('fecha_valoracion')
            ->orderByDesc('created_at')
            ->get()
            ->unique('cod_am')
            ->keyBy('cod_am');

        $idsCognitivos = DB::table('evaluaciones_geriatricas as eg')
            ->join(
                'instrumentos_geriatricos as ig',
                'ig.cod_instrumento',
                '=',
                'eg.cod_instrumento'
            )
            ->whereIn('eg.cod_am', $codAms)
            ->where('ig.cod_area', 'ARE_COG')
            ->whereNull('eg.deleted_at')
            ->whereNotExists(function ($newer) {
                $newer->selectRaw('1')
                    ->from('evaluaciones_geriatricas as eg2')
                    ->join(
                        'instrumentos_geriatricos as ig2',
                        'ig2.cod_instrumento',
                        '=',
                        'eg2.cod_instrumento'
                    )
                    ->whereColumn('eg2.cod_am', 'eg.cod_am')
                    ->where('ig2.cod_area', 'ARE_COG')
                    ->whereNull('eg2.deleted_at')
                    ->where(function ($q) {
                        $q->whereColumn(
                            'eg2.fecha_eval',
                            '>',
                            'eg.fecha_eval'
                        )->orWhere(function ($sameDate) {
                            $sameDate
                                ->whereColumn(
                                    'eg2.fecha_eval',
                                    'eg.fecha_eval'
                                )
                                ->whereColumn(
                                    'eg2.created_at',
                                    '>',
                                    'eg.created_at'
                                );
                        });
                    });
            })
            ->pluck('eg.cod_eval_ger')
            ->all();

        $cognicionPagina = EvaluacionGeriatrica::query()
            ->with('instrumento')
            ->whereIn('cod_eval_ger', $idsCognitivos)
            ->get()
            ->keyBy('cod_am');

        $proximosControles = AtencionAdulto::query()
            ->with('tipoAtencion')
            ->whereIn('cod_am', $codAms)
            ->whereDate('fecha', '>=', today())
            ->where(function (Builder $q) {
                $q->whereNull('estado')
                    ->orWhere('estado', '!=', 'ANULADA');
            })
            ->whereHas(
                'tipoAtencion',
                fn(Builder $q) => $q->where(
                    'nombre',
                    'Médica General'
                )
            )
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get()
            ->unique('cod_am')
            ->keyBy('cod_am');

        return compact(
            'ultimosSignos',
            'ultimasNotas',
            'ultimasInterconsultas',
            'cntMedicacion',
            'alertasPagina',
            'barthelPagina',
            'cognicionPagina',
            'proximosControles'
        );
    }

    private function metricasVista(
        int $cntActivos,
        int $cntHistorial,
        int $cntPendientes,
        int $cntInterconsultas
    ): array {
        if ($this->tab === 'historial') {
            $base = $this->queryHistoricos();

            return [
                [
                    'label' => 'Expedientes históricos',
                    'value' => $cntHistorial,
                    'help' => 'Residentes fuera del seguimiento operativo',
                    'icon' => 'ph-archive',
                    'tone' => 'residentes',
                ],
                [
                    'label' => 'Egresados',
                    'value' => (clone $base)
                        ->whereHas(
                            'estado',
                            fn(Builder $q) => $q->where(
                                'estado',
                                'EGRESADO'
                            )
                        )->count(),
                    'help' => 'Estado institucional egresado',
                    'icon' => 'ph-sign-out',
                    'tone' => 'salud',
                ],
                [
                    'label' => 'Derivados / trasladados',
                    'value' => (clone $base)
                        ->whereHas(
                            'estado',
                            fn(Builder $q) => $q->whereIn(
                                'estado',
                                ['DERIVADO', 'TRASLADADO']
                            )
                        )->count(),
                    'help' => 'Salidas hacia otro recurso asistencial',
                    'icon' => 'ph-arrows-left-right',
                    'tone' => 'alertas',
                ],
                [
                    'label' => 'Archivados',
                    'value' => AdultoMayor::query()
                        ->whereNotNull('archivado_en')
                        ->count(),
                    'help' => 'Expedientes institucionalmente archivados',
                    'icon' => 'ph-folder-lock',
                    'tone' => 'cognitivo',
                ],
            ];
        }

        if ($this->tab === 'pendientes') {
            return [
                [
                    'label' => 'Por valorar',
                    'value' => $cntPendientes,
                    'help' => 'Pendientes de valoración médica',
                    'icon' => 'ph-hourglass',
                    'tone' => 'alertas',
                ],
                [
                    'label' => 'En residencia',
                    'value' => $cntActivos,
                    'help' => 'Residentes actualmente bajo seguimiento',
                    'icon' => 'ph-users-three',
                    'tone' => 'residentes',
                ],
            ];
        }

        if ($this->tab === 'interconsultas') {
            $notasSemana = NotaEvolucionMedica::query()
                ->where('estado', 'ACTIVO')
                ->where('tipo_nota', 'INTERCONSULTA')
                ->whereDate(
                    'fecha',
                    '>=',
                    today()->subDays(7)
                )
                ->count();

            return [
                [
                    'label' => 'Interconsultas activas',
                    'value' => $cntInterconsultas,
                    'help' => 'Residentes con interconsulta activa',
                    'icon' => 'ph-arrows-left-right',
                    'tone' => 'cognitivo',
                ],
                [
                    'label' => 'Registradas 7 días',
                    'value' => $notasSemana,
                    'help' => 'Notas de interconsulta recientes',
                    'icon' => 'ph-calendar-check',
                    'tone' => 'salud',
                ],
                [
                    'label' => 'En residencia',
                    'value' => $cntActivos,
                    'help' => 'Población clínica actual',
                    'icon' => 'ph-users-three',
                    'tone' => 'residentes',
                ],
            ];
        }

        $conAlertas = $this->queryActivos()
            ->whereHas('alertasAbiertas')
            ->count();

        $conControl = $this->queryActivos()
            ->whereHas(
                'atenciones',
                fn(Builder $atencion) => $atencion
                    ->whereBetween(
                        'fecha',
                        [
                            today()->toDateString(),
                            today()->addDays(6)->toDateString(),
                        ]
                    )
                    ->where(function (Builder $estado) {
                        $estado->whereNull('estado')
                            ->orWhere('estado', '!=', 'ANULADA');
                    })
                    ->whereHas(
                        'tipoAtencion',
                        fn(Builder $tipo) => $tipo->where(
                            'nombre',
                            'Médica General'
                        )
                    )
            )->count();

        return [
            [
                'label' => 'En residencia',
                'value' => $cntActivos,
                'help' => 'Residentes bajo seguimiento médico',
                'icon' => 'ph-users-three',
                'tone' => 'residentes',
            ],
            [
                'label' => 'Con alertas',
                'value' => $conAlertas,
                'help' => 'Alertas abiertas o en atención',
                'icon' => 'ph-warning-circle',
                'tone' => 'alertas',
            ],
            [
                'label' => 'Controles 7 días',
                'value' => $conControl,
                'help' => 'Atenciones Médica General programadas',
                'icon' => 'ph-calendar-check',
                'tone' => 'salud',
            ],
            [
                'label' => 'Interconsultas',
                'value' => $cntInterconsultas,
                'help' => 'Residentes con interconsulta activa',
                'icon' => 'ph-arrows-left-right',
                'tone' => 'cognitivo',
            ],
        ];
    }

    private function estadosDisponiblesParaVista(): array
    {
        $estados = match ($this->tab) {
            'historial' => self::ESTADOS_HISTORICOS,
            'pendientes' => self::ESTADOS_PENDIENTE_VALORACION,
            'interconsultas', 'activos' => self::ESTADOS_EN_RESIDENCIA,
            default => [],
        };

        return collect($estados)
            ->mapWithKeys(
                fn(string $estado) => [
                    $estado => $this->estadoHumano($estado),
                ]
            )
            ->all();
    }

    private function habitacionesDisponibles()
    {
        if ($this->tab !== 'activos') {
            return collect();
        }

        $ids = $this->queryActivos()
            ->whereNotNull('cod_habitacion')
            ->distinct()
            ->pluck('cod_habitacion');

        return Habitacion::query()
            ->whereIn('cod_habitacion', $ids)
            ->orderBy('codigo')
            ->get(['cod_habitacion', 'codigo', 'nombre']);
    }

    private function riesgosCognitivosDisponibles()
    {
        if ($this->tab !== 'activos') {
            return collect();
        }

        return DB::table('evaluaciones_geriatricas as eg')
            ->join(
                'instrumentos_geriatricos as ig',
                'ig.cod_instrumento',
                '=',
                'eg.cod_instrumento'
            )
            ->join(
                'adulto_mayor as am',
                'am.cod_am',
                '=',
                'eg.cod_am'
            )
            ->join(
                'estado_adulto as ea',
                'ea.cod_est_adul',
                '=',
                'am.cod_est_adul'
            )
            ->whereIn('ea.estado', self::ESTADOS_EN_RESIDENCIA)
            ->whereNull('am.archivado_en')
            ->where('ig.cod_area', 'ARE_COG')
            ->whereNull('eg.deleted_at')
            ->whereNotNull('eg.nivel_riesgo')
            ->where('eg.nivel_riesgo', '!=', '')
            ->whereNotExists(function ($newer) {
                $newer->selectRaw('1')
                    ->from('evaluaciones_geriatricas as eg2')
                    ->join(
                        'instrumentos_geriatricos as ig2',
                        'ig2.cod_instrumento',
                        '=',
                        'eg2.cod_instrumento'
                    )
                    ->whereColumn('eg2.cod_am', 'eg.cod_am')
                    ->where('ig2.cod_area', 'ARE_COG')
                    ->whereNull('eg2.deleted_at')
                    ->where(function ($q) {
                        $q->whereColumn(
                            'eg2.fecha_eval',
                            '>',
                            'eg.fecha_eval'
                        )->orWhere(function ($sameDate) {
                            $sameDate
                                ->whereColumn(
                                    'eg2.fecha_eval',
                                    'eg.fecha_eval'
                                )
                                ->whereColumn(
                                    'eg2.created_at',
                                    '>',
                                    'eg.created_at'
                                );
                        });
                    });
            })
            ->distinct()
            ->orderBy('eg.nivel_riesgo')
            ->pluck('eg.nivel_riesgo');
    }

    private function dependenciasDisponibles()
    {
        if ($this->tab !== 'activos') {
            return collect();
        }

        return ValoracionFuncionalAdulto::query()
            ->whereIn(
                'cod_am',
                $this->queryActivos()->select('cod_am')
            )
            ->vigente()
            ->whereNotNull('nivel_dependencia')
            ->where('nivel_dependencia', '!=', '')
            ->distinct()
            ->orderBy('nivel_dependencia')
            ->pluck('nivel_dependencia');
    }

    private function filtrosActivosEtiquetados(
        array $estadosDisponibles,
        $habitacionesDisponibles
    ): array {
        $filtros = [];

        if ($this->filtroEstado !== '') {
            $filtros[] = [
                'key' => 'estado',
                'label' => 'Estado',
                'value' => $estadosDisponibles[$this->filtroEstado]
                    ?? $this->estadoHumano($this->filtroEstado),
            ];
        }

        if ($this->filtroAlertas === 'ABIERTAS') {
            $filtros[] = [
                'key' => 'alertas',
                'label' => 'Alertas',
                'value' => 'Con alertas abiertas',
            ];
        }

        if ($this->filtroControl === 'PROXIMO') {
            $filtros[] = [
                'key' => 'control',
                'label' => 'Control',
                'value' => 'Próximos 7 días',
            ];
        }

        if ($this->filtroRiesgo !== '') {
            $filtros[] = [
                'key' => 'riesgo',
                'label' => 'Riesgo cognitivo',
                'value' => $this->filtroRiesgo,
            ];
        }

        if ($this->filtroDependencia !== '') {
            $filtros[] = [
                'key' => 'dependencia',
                'label' => 'Dependencia',
                'value' => $this->filtroDependencia,
            ];
        }

        if ($this->filtroHabitacion !== '') {
            $habitacion = $habitacionesDisponibles->firstWhere(
                'cod_habitacion',
                $this->filtroHabitacion
            );

            $filtros[] = [
                'key' => 'habitacion',
                'label' => 'Habitación',
                'value' => $habitacion?->codigo
                    ?? $habitacion?->nombre
                    ?? $this->filtroHabitacion,
            ];
        }

        return $filtros;
    }

    private function normalizarFiltros(): void
    {
        if (!in_array($this->tab, self::TABS, true)) {
            $this->tab = 'activos';
        }

        if (!in_array($this->orden, ['nombre', 'alertas', 'reciente'], true)) {
            $this->orden = 'nombre';
        }

        $estadosPermitidos = array_keys(
            $this->estadosDisponiblesParaVista()
        );

        if (
            $this->filtroEstado !== ''
            && !in_array(
                $this->filtroEstado,
                $estadosPermitidos,
                true
            )
        ) {
            $this->filtroEstado = '';
        }

        if ($this->tab !== 'activos') {
            $this->filtroAlertas = '';
            $this->filtroControl = '';
            $this->filtroRiesgo = '';
            $this->filtroDependencia = '';
            $this->filtroHabitacion = '';

            if ($this->orden === 'alertas') {
                $this->orden = 'nombre';
            }
        }

        if (!in_array($this->filtroAlertas, ['', 'ABIERTAS'], true)) {
            $this->filtroAlertas = '';
        }

        if (!in_array($this->filtroControl, ['', 'PROXIMO'], true)) {
            $this->filtroControl = '';
        }
    }

    private function limpiarFiltrosContextuales(bool $incluyeBusqueda = false): void
    {
        if ($incluyeBusqueda) {
            $this->busqueda = '';
        }

        $this->filtroEstado = '';
        $this->filtroAlertas = '';
        $this->filtroControl = '';
        $this->filtroRiesgo = '';
        $this->filtroDependencia = '';
        $this->filtroHabitacion = '';
    }

    private function estadoHumano(string $estado): string
    {
        return match ($estado) {
            'ACTIVO' => 'Activo',
            'ADMITIDO' => 'Admitido',
            'ASIGNADO' => 'Asignado',
            'EN_SEGUIMIENTO_ACTIVO' => 'En seguimiento',
            'OBSERVADO' => 'Observado',
            'SEGUIMIENTO_ESPECIAL' => 'Seguimiento especial',
            'PENDIENTE_VALORACION_MEDICA' => 'Pendiente valoración médica',
            'ARCHIVADO' => 'Archivado',
            'INACTIVO' => 'Inactivo',
            'NO_ADMITIDO' => 'No admitido',
            'DERIVADO' => 'Derivado',
            'EGRESADO' => 'Egresado',
            'FALLECIDO' => 'Fallecido',
            'RETIRADO' => 'Retirado',
            'TRASLADADO' => 'Trasladado',
            default => ucwords(
                strtolower(
                    str_replace('_', ' ', $estado)
                )
            ),
        };
    }

    private function autorizarVista(): void
    {
        $usuario = Auth::user();

        abort_unless($usuario, 401);

        abort_unless(
            $usuario->hasAnyRole([
                'SUPERADMINISTRADOR',
                'MEDICO GENERAL/GERIATRA',
            ]),
            403
        );

        abort_unless(
            $usuario->can('salud.ver')
                && $usuario->can('adultos.ver'),
            403,
            'No cuenta con permisos para consultar residentes.'
        );
    }

    private function autorizarMutacion(string $permiso): void
    {
        $this->autorizarVista();

        abort_unless(
            Auth::user()?->can($permiso),
            403,
            'No cuenta con permiso para realizar esta acción.'
        );
    }

    private function autorizarResidenteModificable(
        AdultoMayor $adulto
    ): void {
        abort_if(
            $adulto->archivado_en !== null,
            409,
            'El expediente está archivado y solo puede consultarse.'
        );

        $estado = strtoupper(
            trim((string) ($adulto->estado?->estado ?? ''))
        );

        abort_unless(
            in_array(
                $estado,
                self::ESTADOS_EN_RESIDENCIA,
                true
            ),
            409,
            'El estado actual del residente no permite registrar información clínica desde este panel.'
        );
    }
}
