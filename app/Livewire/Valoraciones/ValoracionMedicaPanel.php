<?php

namespace App\Livewire\Valoraciones;

use App\Models\AdultoMayor;
use App\Models\FichaMedicaAdulto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ValoracionMedicaPanel extends Component
{
    use WithPagination;

    private const ESTADOS_REGISTRO = [
        'BORRADOR',
        'ACTIVO',
    ];

    private const ESTADOS_FORMULARIO = [
        'BORRADOR',
        'COMPLETADA',
    ];

    private const RESULTADOS_ADMISION = [
        'ADMITIDO',
        'NO_ADMITIDO',
        'DERIVADO',
        'OBSERVADO',
        'CANCELADO',
    ];

    private const CONDICIONES_MEDICAS = [
        'ESTABLE',
        'REQUIERE_OBSERVACION',
        'DELICADA',
        'NO_APTA',
    ];

    private const ESTADOS_NEUROLOGICOS = [
        'NORMAL',
        'CONFUSION_LEVE',
        'DESORIENTACION',
        'ALTERADO',
        'NO_EVALUABLE',
    ];

    private const NIVELES_DEPENDENCIA = [
        'INDEPENDIENTE',
        'DEPENDENCIA_LEVE',
        'DEPENDENCIA_MODERADA',
        'DEPENDENCIA_SEVERA',
        'DEPENDENCIA_TOTAL',
        'NO_EVALUABLE',
    ];

    private const FILTROS_ALERGIAS = [
        'CON_DATO',
        'SIN_DATO',
    ];

    private const FILTROS_PERIODO = [
        'HOY',
        '7_DIAS',
        '30_DIAS',
        '90_DIAS',
    ];

    private const ORDENES = [
        'RECIENTES',
        'ANTIGUOS',
        'NOMBRE',
    ];

    private const ANTECEDENTES = [
        'hipertension' => 'Hipertensión',
        'diabetes' => 'Diabetes',
        'problemas_cardiacos' => 'Problemas cardíacos',
        'acv' => 'ACV',
        'parkinson' => 'Parkinson',
        'epilepsia' => 'Epilepsia',
        'alzheimer_diagnosticado' => 'Alzheimer diagnosticado',
        'depresion' => 'Depresión',
        'ansiedad' => 'Ansiedad',
        'problemas_sueno' => 'Problemas del sueño',
        'problemas_visuales' => 'Problemas visuales',
        'problemas_auditivos' => 'Problemas auditivos',
        'dolor_cronico' => 'Dolor crónico',
    ];

    public string $search = '';
    public string $filtroEstado = '';
    public string $filtroAlergias = '';
    public string $filtroAntecedente = '';
    public string $filtroPeriodo = '';
    public string $orden = 'RECIENTES';

    /**
     * Selección maestro-detalle.
     * La tabla se mantiene en la misma pantalla y el detalle se presenta
     * lateralmente sin abrir un segundo modal.
     */
    public ?string $seleccionadoId = null;

    public bool $modalForm = false;
    public ?string $editandoId = null;

    public string $codAm = '';
    public string $fecha = '';
    public string $hora = '';

    public bool $hipertension = false;
    public bool $diabetes = false;
    public bool $problemasCardiacos = false;
    public bool $acv = false;
    public bool $parkinson = false;
    public bool $epilepsia = false;
    public bool $alzheimerDiagnosticado = false;
    public bool $depresion = false;
    public bool $ansiedad = false;
    public bool $problemasSueno = false;
    public bool $problemasVisuales = false;
    public bool $problemasAuditivos = false;
    public bool $dolorCronico = false;

    public string $diagnosticosReferidos = '';
    public string $antecedentesRelevantes = '';
    public string $medicacionActualResumen = '';
    public string $alergiasReferidas = '';
    public string $restriccionesAlimentarias = '';
    public string $hospitalizaciones = '';
    public string $cirugias = '';

    public string $condicionMedicaGeneral = '';
    public string $estadoNeurologicoBasico = '';
    public string $nivelDependenciaSugerido = '';
    public bool $requiereSeguimientoEsp = false;

    public string $resultadoAdmision = '';
    public string $motivoDecision = '';
    public string $recomendacionMedica = '';
    public string $estadoForm = 'BORRADOR';

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'filtroAlergias' => ['except' => ''],
        'filtroAntecedente' => ['except' => ''],
        'filtroPeriodo' => ['except' => ''],
        'orden' => ['except' => 'RECIENTES'],
    ];

    public function mount(): void
    {
        $this->autorizarVista();
        $this->normalizarFiltros();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->cerrarVistaPrevia();
    }

    public function updatedSearch(): void
    {
        $this->search = mb_substr(trim($this->search), 0, 120);
    }

    public function updatedFiltroEstado(): void
    {
        $this->filtroEstado = $this->normalizarOpcion(
            $this->filtroEstado,
            self::ESTADOS_REGISTRO
        );

        $this->resetPage();
        $this->cerrarVistaPrevia();
    }

    public function updatedFiltroAlergias(): void
    {
        $this->filtroAlergias = $this->normalizarOpcion(
            $this->filtroAlergias,
            self::FILTROS_ALERGIAS
        );

        $this->resetPage();
        $this->cerrarVistaPrevia();
    }

    public function updatedFiltroAntecedente(): void
    {
        $this->filtroAntecedente = array_key_exists(
            $this->filtroAntecedente,
            self::ANTECEDENTES
        )
            ? $this->filtroAntecedente
            : '';

        $this->resetPage();
        $this->cerrarVistaPrevia();
    }

    public function updatedFiltroPeriodo(): void
    {
        $this->filtroPeriodo = $this->normalizarOpcion(
            $this->filtroPeriodo,
            self::FILTROS_PERIODO
        );

        $this->resetPage();
        $this->cerrarVistaPrevia();
    }

    public function updatedOrden(): void
    {
        $this->orden = $this->normalizarOpcion(
            $this->orden,
            self::ORDENES,
            'RECIENTES'
        );

        $this->resetPage();
    }

    /**
     * Validación reactiva del formulario.
     */
    public function updated(string $property): void
    {
        if (
            in_array(
                $property,
                [
                    'search',
                    'filtroEstado',
                    'filtroAlergias',
                    'filtroAntecedente',
                    'filtroPeriodo',
                    'orden',
                ],
                true
            )
        ) {
            return;
        }

        $reglas = $this->reglas();

        if (!array_key_exists($property, $reglas)) {
            return;
        }

        $this->validateOnly(
            $property,
            $reglas,
            $this->mensajes()
        );
    }

    public function filtrarEstado(string $estado = ''): void
    {
        $estado = strtoupper(trim($estado));

        abort_unless(
            $estado === '' || in_array($estado, self::ESTADOS_REGISTRO, true),
            422
        );

        $this->filtroEstado = $estado;
        $this->resetPage();
        $this->cerrarVistaPrevia();
    }

    public function filtrarAlergias(string $filtro = ''): void
    {
        $filtro = strtoupper(trim($filtro));

        abort_unless(
            $filtro === '' || in_array($filtro, self::FILTROS_ALERGIAS, true),
            422
        );

        $this->filtroAlergias = $filtro;
        $this->resetPage();
        $this->cerrarVistaPrevia();
    }

    public function limpiarFiltro(string $campo): void
    {
        $permitidos = [
            'estado',
            'alergias',
            'antecedente',
            'periodo',
        ];

        abort_unless(in_array($campo, $permitidos, true), 422);

        match ($campo) {
            'estado' => $this->filtroEstado = '',
            'alergias' => $this->filtroAlergias = '',
            'antecedente' => $this->filtroAntecedente = '',
            'periodo' => $this->filtroPeriodo = '',
        };

        $this->resetPage();
        $this->cerrarVistaPrevia();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = '';
        $this->filtroAlergias = '';
        $this->filtroAntecedente = '';
        $this->filtroPeriodo = '';
        $this->orden = 'RECIENTES';

        $this->resetPage();
        $this->cerrarVistaPrevia();
    }

    public function abrirCrear(): void
    {
        $this->autorizarCreacion();

        $this->cerrarVistaPrevia();
        $this->resetFormulario();

        $this->fecha = today()->toDateString();
        $this->hora = now()->format('H:i');
        $this->estadoForm = 'BORRADOR';
        $this->modalForm = true;
    }

    public function abrirEditar(string $id): void
    {
        $this->autorizarEdicion();

        $ficha = FichaMedicaAdulto::query()
            ->with('adultoMayor')
            ->findOrFail($id);

        abort_unless(
            strtoupper((string) $ficha->estado) === 'BORRADOR',
            409,
            'Solo las valoraciones en borrador pueden continuar editándose.'
        );

        abort_if(
            $ficha->adultoMayor?->archivado_en !== null,
            409,
            'El expediente está archivado y no admite modificaciones.'
        );

        $this->cerrarVistaPrevia();
        $this->resetFormulario();
        $this->cargarFormularioDesdeFicha($ficha);

        $this->editandoId = $ficha->cod_ficha_medica;
        $this->modalForm = true;
    }

    public function seleccionarValoracion(string $id): void
    {
        $this->autorizarVista();

        FichaMedicaAdulto::query()
            ->select('cod_ficha_medica')
            ->findOrFail($id);

        $this->seleccionadoId = $id;
    }

    public function cerrarVistaPrevia(): void
    {
        $this->seleccionadoId = null;
    }

    /**
     * El stepper del modal llama a este método antes de avanzar.
     */
    public function validarPaso(int $paso): bool
    {
        abort_unless(in_array($paso, [1, 2, 3, 4], true), 422);

        $this->normalizarCampos();

        $reglas = $this->reglasPaso($paso);

        if ($reglas !== []) {
            $this->validate($reglas, $this->mensajes());
        }

        if ($paso === 1) {
            $this->validarFechaHora();
        }

        return true;
    }

    public function guardarBorrador(): void
    {
        $this->estadoForm = 'BORRADOR';
        $this->guardar();
    }

    public function finalizarValoracion(): void
    {
        $this->estadoForm = 'COMPLETADA';
        $this->guardar();
    }

    public function guardar(): void
    {
        if ($this->editandoId) {
            $this->autorizarEdicion();
        } else {
            $this->autorizarCreacion();
        }

        $this->normalizarCampos();

        $this->validate(
            $this->reglas(),
            $this->mensajes()
        );

        $this->validarFechaHora();

        try {
            $ficha = DB::transaction(function (): FichaMedicaAdulto {
                $adulto = AdultoMayor::query()
                    ->lockForUpdate()
                    ->findOrFail($this->codAm);

                abort_if(
                    $adulto->archivado_en !== null,
                    409,
                    'El expediente está archivado y no admite nuevas valoraciones.'
                );

                $datos = $this->datosPersistencia($adulto->cod_am);

                if ($this->editandoId) {
                    $ficha = FichaMedicaAdulto::query()
                        ->lockForUpdate()
                        ->findOrFail($this->editandoId);

                    abort_unless(
                        strtoupper((string) $ficha->estado) === 'BORRADOR',
                        409,
                        'La valoración ya no se encuentra disponible para edición.'
                    );

                    abort_unless(
                        (string) $ficha->cod_am === (string) $adulto->cod_am,
                        409,
                        'La valoración no corresponde al residente seleccionado.'
                    );

                    $ficha->update($datos);

                    return $ficha->fresh([
                        'adultoMayor',
                        'registrador',
                    ]);
                }

                $datos['registrado_por'] = Auth::user()->cod_usu;

                return FichaMedicaAdulto::create($datos)->fresh([
                    'adultoMayor',
                    'registrador',
                ]);
            });

            $mensaje = $this->estadoForm === 'COMPLETADA'
                ? 'Valoración médica completada correctamente.'
                : (
                    $this->editandoId
                    ? 'Borrador actualizado correctamente.'
                    : 'Borrador de valoración médica guardado correctamente.'
                );

            $estadoPersistido = $ficha->estado;
            $fichaId = $ficha->cod_ficha_medica;

            $this->cerrarModalFormulario();

            if (
                $this->filtroEstado === ''
                || $this->filtroEstado === $estadoPersistido
            ) {
                $this->seleccionadoId = $fichaId;
            }

            $this->dispatch('valoracion-medica-actualizada');

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => $mensaje,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            $this->addError(
                'general',
                'No se pudo guardar la valoración médica. Intente nuevamente.'
            );
        }
    }

    public function cerrarModalFormulario(): void
    {
        $this->modalForm = false;
        $this->resetFormulario();
        $this->resetValidation();
    }

    /**
     * Compatibilidad con el nombre utilizado en vistas anteriores.
     */
    public function cerrarModales(): void
    {
        $this->cerrarModalFormulario();
    }

    public function render()
    {
        $this->autorizarVista();
        $this->normalizarFiltros();

        $baseContextual = FichaMedicaAdulto::query();
        $this->aplicarBusqueda($baseContextual, trim($this->search));
        $this->aplicarFiltrosContextuales($baseContextual);

        $metricas = [
            'total' => (clone $baseContextual)->count(),

            'borradores' => (clone $baseContextual)
                ->where('estado', 'BORRADOR')
                ->count(),

            'completadas' => (clone $baseContextual)
                ->where('estado', 'ACTIVO')
                ->count(),

            'alergiasConDato' => (clone $baseContextual)
                ->whereNotNull('alergias')
                ->whereRaw("TRIM(alergias) <> ''")
                ->count(),
        ];

        $valoracionesQuery = FichaMedicaAdulto::query()
            ->with([
                'adultoMayor',
                'registrador',
            ]);

        $this->aplicarBusqueda(
            $valoracionesQuery,
            trim($this->search)
        );

        $this->aplicarFiltrosContextuales(
            $valoracionesQuery
        );

        if ($this->filtroEstado !== '') {
            $valoracionesQuery->where(
                'estado',
                $this->filtroEstado
            );
        }

        $this->aplicarOrden($valoracionesQuery);

        $valoraciones = $valoracionesQuery->paginate(12);

        $seleccionado = $this->cargarValoracion(
            $this->seleccionadoId
        );

        $adultoFormSeleccionado = $this->cargarAdultoFormulario();

        return view(
            'livewire.valoraciones.valoracion-medica-panel',
            [
                'valoraciones' => $valoraciones,
                'metricas' => $metricas,
                'antecedentesDisponibles' => self::ANTECEDENTES,
                'adultos' => $this->adultosDisponibles(),
                'adultoFormSeleccionado' => $adultoFormSeleccionado,
                'seleccionado' => $seleccionado,
                'seleccionadoSintesis' => $seleccionado
                    ? $this->parsearSintesis($seleccionado->observacion_medica)
                    : [],
                'seleccionadoAntecedentes' => $seleccionado
                    ? $this->antecedentesActivos($seleccionado)
                    : [],
                'seleccionadoAlergia' => $seleccionado
                    ? $this->clasificarAlergia($seleccionado->alergias)
                    : null,
            ]
        )->layout('layouts.sistema');
    }

    private function aplicarBusqueda(
        Builder $query,
        string $busqueda
    ): void {
        if ($busqueda === '') {
            return;
        }

        $terminos = collect(
            preg_split('/\s+/u', $busqueda) ?: []
        )
            ->filter()
            ->take(6);

        foreach ($terminos as $termino) {
            $query->whereHas(
                'adultoMayor',
                function (Builder $adulto) use ($termino) {
                    $like = '%' . $termino . '%';

                    $adulto->where(function (Builder $q) use ($like) {
                        $q->where('nombres', 'like', $like)
                            ->orWhere('ap_paterno', 'like', $like)
                            ->orWhere('ap_materno', 'like', $like)
                            ->orWhere('ci', 'like', $like)
                            ->orWhere('cod_am', 'like', $like);
                    });
                }
            );
        }
    }

    private function aplicarFiltrosContextuales(
        Builder $query
    ): void {
        if ($this->filtroAlergias === 'CON_DATO') {
            $query
                ->whereNotNull('alergias')
                ->whereRaw("TRIM(alergias) <> ''");
        }

        if ($this->filtroAlergias === 'SIN_DATO') {
            $query->where(function (Builder $q) {
                $q->whereNull('alergias')
                    ->orWhereRaw("TRIM(alergias) = ''");
            });
        }

        if (
            $this->filtroAntecedente !== ''
            && array_key_exists(
                $this->filtroAntecedente,
                self::ANTECEDENTES
            )
        ) {
            $query->where(
                $this->filtroAntecedente,
                true
            );
        }

        if ($this->filtroPeriodo !== '') {
            $desde = match ($this->filtroPeriodo) {
                'HOY' => today(),
                '7_DIAS' => today()->subDays(6),
                '30_DIAS' => today()->subDays(29),
                '90_DIAS' => today()->subDays(89),
                default => null,
            };

            if ($desde) {
                $query->whereDate(
                    'created_at',
                    '>=',
                    $desde->toDateString()
                );
            }
        }
    }

    private function aplicarOrden(
        Builder $query
    ): void {
        match ($this->orden) {
            'ANTIGUOS' => $query
                ->orderBy('created_at')
                ->orderBy('cod_ficha_medica'),

            'NOMBRE' => $query
                ->orderBy(
                    AdultoMayor::query()
                        ->select('ap_paterno')
                        ->whereColumn(
                            'adulto_mayor.cod_am',
                            'ficha_medica_adulto.cod_am'
                        )
                        ->limit(1)
                )
                ->orderBy(
                    AdultoMayor::query()
                        ->select('nombres')
                        ->whereColumn(
                            'adulto_mayor.cod_am',
                            'ficha_medica_adulto.cod_am'
                        )
                        ->limit(1)
                ),

            default => $query
                ->orderByDesc('created_at')
                ->orderByDesc('cod_ficha_medica'),
        };
    }

    private function adultosDisponibles(): Collection
    {
        if (!$this->modalForm) {
            return collect();
        }

        return AdultoMayor::query()
            ->select(
                'cod_am',
                'nombres',
                'ap_paterno',
                'ap_materno',
                'ci'
            )
            ->whereNull('archivado_en')
            ->orderBy('ap_paterno')
            ->orderBy('ap_materno')
            ->orderBy('nombres')
            ->get();
    }

    private function cargarAdultoFormulario(): ?AdultoMayor
    {
        if (!$this->modalForm || $this->codAm === '') {
            return null;
        }

        return AdultoMayor::query()
            ->with([
                'estado',
                'habitacion',
                'cama',
            ])
            ->find($this->codAm);
    }

    private function cargarValoracion(
        ?string $id
    ): ?FichaMedicaAdulto {
        if (!$id) {
            return null;
        }

        return FichaMedicaAdulto::query()
            ->with([
                'adultoMayor.estado',
                'adultoMayor.habitacion',
                'adultoMayor.cama',
                'registrador',
            ])
            ->find($id);
    }

    private function reglas(): array
    {
        $base = [
            'codAm' => [
                'required',
                'string',
                'exists:adulto_mayor,cod_am',
            ],

            'fecha' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'hora' => [
                'required',
                'date_format:H:i',
            ],

            'hipertension' => ['boolean'],
            'diabetes' => ['boolean'],
            'problemasCardiacos' => ['boolean'],
            'acv' => ['boolean'],
            'parkinson' => ['boolean'],
            'epilepsia' => ['boolean'],
            'alzheimerDiagnosticado' => ['boolean'],
            'depresion' => ['boolean'],
            'ansiedad' => ['boolean'],
            'problemasSueno' => ['boolean'],
            'problemasVisuales' => ['boolean'],
            'problemasAuditivos' => ['boolean'],
            'dolorCronico' => ['boolean'],

            'diagnosticosReferidos' => [
                'nullable',
                'string',
                'max:1500',
            ],

            'antecedentesRelevantes' => [
                'nullable',
                'string',
                'max:1500',
            ],

            'medicacionActualResumen' => [
                'nullable',
                'string',
                'max:1500',
            ],

            'alergiasReferidas' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'restriccionesAlimentarias' => [
                'nullable',
                'string',
                'max:1500',
            ],

            'hospitalizaciones' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'cirugias' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'condicionMedicaGeneral' => [
                'nullable',
                Rule::in(self::CONDICIONES_MEDICAS),
            ],

            'estadoNeurologicoBasico' => [
                'nullable',
                Rule::in(self::ESTADOS_NEUROLOGICOS),
            ],

            'nivelDependenciaSugerido' => [
                'nullable',
                Rule::in(self::NIVELES_DEPENDENCIA),
            ],

            'requiereSeguimientoEsp' => ['boolean'],

            'estadoForm' => [
                'required',
                Rule::in(self::ESTADOS_FORMULARIO),
            ],
        ];

        if ($this->estadoForm === 'COMPLETADA') {
            $base['condicionMedicaGeneral'] = [
                'required',
                Rule::in(self::CONDICIONES_MEDICAS),
            ];

            $base['estadoNeurologicoBasico'] = [
                'required',
                Rule::in(self::ESTADOS_NEUROLOGICOS),
            ];

            $base['nivelDependenciaSugerido'] = [
                'required',
                Rule::in(self::NIVELES_DEPENDENCIA),
            ];

            $base['resultadoAdmision'] = [
                'required',
                Rule::in(self::RESULTADOS_ADMISION),
            ];

            $base['motivoDecision'] = [
                'required',
                'string',
                'min:10',
                'max:2000',
            ];
        } else {
            $base['resultadoAdmision'] = [
                'nullable',
                Rule::in(self::RESULTADOS_ADMISION),
            ];

            $base['motivoDecision'] = [
                'nullable',
                'string',
                'max:2000',
            ];
        }

        $base['recomendacionMedica'] = [
            'nullable',
            'string',
            'max:2000',
        ];

        return $base;
    }

    private function reglasPaso(int $paso): array
    {
        $reglas = $this->reglas();

        return match ($paso) {
            1 => array_intersect_key(
                $reglas,
                array_flip([
                    'codAm',
                    'fecha',
                    'hora',
                ])
            ),

            2 => array_intersect_key(
                $reglas,
                array_flip([
                    'hipertension',
                    'diabetes',
                    'problemasCardiacos',
                    'acv',
                    'parkinson',
                    'epilepsia',
                    'alzheimerDiagnosticado',
                    'depresion',
                    'ansiedad',
                    'problemasSueno',
                    'problemasVisuales',
                    'problemasAuditivos',
                    'dolorCronico',
                    'diagnosticosReferidos',
                    'antecedentesRelevantes',
                    'medicacionActualResumen',
                    'alergiasReferidas',
                    'restriccionesAlimentarias',
                    'hospitalizaciones',
                    'cirugias',
                ])
            ),

            3 => array_intersect_key(
                $reglas,
                array_flip([
                    'condicionMedicaGeneral',
                    'estadoNeurologicoBasico',
                    'nivelDependenciaSugerido',
                    'requiereSeguimientoEsp',
                ])
            ),

            4 => array_intersect_key(
                $reglas,
                array_flip([
                    'resultadoAdmision',
                    'motivoDecision',
                    'recomendacionMedica',
                    'estadoForm',
                ])
            ),

            default => [],
        };
    }

    private function mensajes(): array
    {
        return [
            'codAm.required' => 'Seleccione un adulto mayor.',
            'codAm.exists' => 'El adulto mayor seleccionado no existe.',

            'fecha.required' => 'La fecha de valoración es obligatoria.',
            'fecha.date' => 'La fecha de valoración no es válida.',
            'fecha.before_or_equal' => 'La fecha de valoración no puede ser futura.',

            'hora.required' => 'La hora de valoración es obligatoria.',
            'hora.date_format' => 'La hora debe tener formato HH:MM.',

            'diagnosticosReferidos.max' => 'Los diagnósticos referidos no pueden superar 1500 caracteres.',
            'antecedentesRelevantes.max' => 'Los antecedentes no pueden superar 1500 caracteres.',
            'medicacionActualResumen.max' => 'La medicación referida no puede superar 1500 caracteres.',
            'alergiasReferidas.max' => 'Las alergias no pueden superar 1000 caracteres.',
            'restriccionesAlimentarias.max' => 'Las restricciones alimentarias no pueden superar 1500 caracteres.',
            'hospitalizaciones.max' => 'Las hospitalizaciones no pueden superar 2000 caracteres.',
            'cirugias.max' => 'Las cirugías no pueden superar 2000 caracteres.',

            'condicionMedicaGeneral.required' => 'Seleccione la condición médica general.',
            'condicionMedicaGeneral.in' => 'La condición médica seleccionada no es válida.',

            'estadoNeurologicoBasico.required' => 'Seleccione el estado neurológico básico.',
            'estadoNeurologicoBasico.in' => 'El estado neurológico seleccionado no es válido.',

            'nivelDependenciaSugerido.required' => 'Seleccione el nivel de dependencia sugerido.',
            'nivelDependenciaSugerido.in' => 'El nivel de dependencia seleccionado no es válido.',

            'resultadoAdmision.required' => 'Seleccione el resultado de la valoración.',
            'resultadoAdmision.in' => 'El resultado seleccionado no es válido.',

            'motivoDecision.required' => 'El fundamento de la decisión es obligatorio.',
            'motivoDecision.min' => 'Describa el fundamento con al menos 10 caracteres.',
            'motivoDecision.max' => 'El fundamento no puede superar 2000 caracteres.',

            'recomendacionMedica.max' => 'La recomendación no puede superar 2000 caracteres.',

            'estadoForm.required' => 'Seleccione el estado del registro.',
            'estadoForm.in' => 'El estado del registro no es válido.',
        ];
    }

    private function validarFechaHora(): void
    {
        if ($this->fecha !== today()->toDateString()) {
            return;
        }

        if ($this->hora > now()->format('H:i')) {
            throw ValidationException::withMessages([
                'hora' => 'La hora de valoración no puede ser futura.',
            ]);
        }
    }

    private function datosPersistencia(
        string $codAm
    ): array {
        return [
            'cod_am' => $codAm,

            'hipertension' => $this->hipertension,
            'diabetes' => $this->diabetes,
            'problemas_cardiacos' => $this->problemasCardiacos,
            'acv' => $this->acv,
            'parkinson' => $this->parkinson,
            'epilepsia' => $this->epilepsia,
            'alzheimer_diagnosticado' => $this->alzheimerDiagnosticado,
            'depresion' => $this->depresion,
            'ansiedad' => $this->ansiedad,
            'problemas_sueno' => $this->problemasSueno,
            'problemas_visuales' => $this->problemasVisuales,
            'problemas_auditivos' => $this->problemasAuditivos,
            'dolor_cronico' => $this->dolorCronico,

            'alergias' => $this->textoONull(
                $this->alergiasReferidas
            ),

            'restricciones_alimentarias' => $this->textoONull(
                $this->restriccionesAlimentarias
            ),

            'hospitalizaciones' => $this->textoONull(
                $this->hospitalizaciones
            ),

            'cirugias' => $this->textoONull(
                $this->cirugias
            ),

            'observacion_medica' => $this->construirSintesis(),
            'estado' => $this->estadoPersistido(),
        ];
    }

    private function construirSintesis(): ?string
    {
        $lineas = [
            "Fecha de valoración: {$this->fecha} {$this->hora}",

            $this->diagnosticosReferidos !== ''
                ? 'Diagnósticos referidos: ' . $this->diagnosticosReferidos
                : null,

            $this->antecedentesRelevantes !== ''
                ? 'Antecedentes adicionales: ' . $this->antecedentesRelevantes
                : null,

            $this->medicacionActualResumen !== ''
                ? 'Medicación actual: ' . $this->medicacionActualResumen
                : null,

            $this->condicionMedicaGeneral !== ''
                ? 'Condición general: ' . $this->condicionMedicaGeneral
                : null,

            $this->estadoNeurologicoBasico !== ''
                ? 'Estado neurológico: ' . $this->estadoNeurologicoBasico
                : null,

            $this->nivelDependenciaSugerido !== ''
                ? 'Dependencia sugerida: ' . $this->nivelDependenciaSugerido
                : null,

            'Requiere seguimiento especial: ' . (
                $this->requiereSeguimientoEsp
                ? 'SÍ'
                : 'NO'
            ),

            $this->resultadoAdmision !== ''
                ? 'Resultado de valoración: ' . $this->resultadoAdmision
                : null,

            $this->motivoDecision !== ''
                ? 'Fundamento: ' . $this->motivoDecision
                : null,

            $this->recomendacionMedica !== ''
                ? 'Recomendación: ' . $this->recomendacionMedica
                : null,
        ];

        $texto = trim(
            implode(
                PHP_EOL,
                array_filter(
                    $lineas,
                    fn($linea) => filled($linea)
                )
            )
        );

        return $texto !== ''
            ? $texto
            : null;
    }

    private function cargarFormularioDesdeFicha(
        FichaMedicaAdulto $ficha
    ): void {
        $resumen = collect(
            $this->parsearSintesis($ficha->observacion_medica)
        )->mapWithKeys(
            fn(array $item) => [
                $item['titulo'] => $item['valor'],
            ]
        );

        $fechaHora = $resumen->get('Fecha de valoración');

        if (
            $fechaHora
            && preg_match(
                '/^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2})$/',
                $fechaHora,
                $coincidencia
            )
        ) {
            $this->fecha = $coincidencia[1];
            $this->hora = $coincidencia[2];
        } else {
            $this->fecha = $ficha->created_at?->toDateString()
                ?? today()->toDateString();

            $this->hora = $ficha->created_at?->format('H:i')
                ?? now()->format('H:i');
        }

        $this->codAm = (string) $ficha->cod_am;

        $this->hipertension = (bool) $ficha->hipertension;
        $this->diabetes = (bool) $ficha->diabetes;
        $this->problemasCardiacos = (bool) $ficha->problemas_cardiacos;
        $this->acv = (bool) $ficha->acv;
        $this->parkinson = (bool) $ficha->parkinson;
        $this->epilepsia = (bool) $ficha->epilepsia;
        $this->alzheimerDiagnosticado = (bool) $ficha->alzheimer_diagnosticado;
        $this->depresion = (bool) $ficha->depresion;
        $this->ansiedad = (bool) $ficha->ansiedad;
        $this->problemasSueno = (bool) $ficha->problemas_sueno;
        $this->problemasVisuales = (bool) $ficha->problemas_visuales;
        $this->problemasAuditivos = (bool) $ficha->problemas_auditivos;
        $this->dolorCronico = (bool) $ficha->dolor_cronico;

        $this->diagnosticosReferidos = (string) $resumen->get(
            'Diagnósticos referidos',
            ''
        );

        $this->antecedentesRelevantes = (string) (
            $resumen->get('Antecedentes adicionales')
            ?? $resumen->get('Antecedentes')
            ?? ''
        );

        $this->medicacionActualResumen = (string) $resumen->get(
            'Medicación actual',
            ''
        );

        $this->alergiasReferidas = (string) ($ficha->alergias ?? '');
        $this->restriccionesAlimentarias = (string) (
            $ficha->restricciones_alimentarias ?? ''
        );
        $this->hospitalizaciones = (string) ($ficha->hospitalizaciones ?? '');
        $this->cirugias = (string) ($ficha->cirugias ?? '');

        $this->condicionMedicaGeneral = (string) $resumen->get(
            'Condición general',
            ''
        );

        $this->estadoNeurologicoBasico = (string) $resumen->get(
            'Estado neurológico',
            ''
        );

        $this->nivelDependenciaSugerido = (string) $resumen->get(
            'Dependencia sugerida',
            ''
        );

        $this->requiereSeguimientoEsp = mb_strtoupper(
            (string) $resumen->get(
                'Requiere seguimiento especial',
                'NO'
            )
        ) === 'SÍ';

        $this->resultadoAdmision = (string) (
            $resumen->get('Resultado de valoración')
            ?? $resumen->get('Resultado admisión')
            ?? ''
        );

        $this->motivoDecision = (string) (
            $resumen->get('Fundamento')
            ?? $resumen->get('Motivo')
            ?? ''
        );

        $this->recomendacionMedica = (string) $resumen->get(
            'Recomendación',
            ''
        );

        $this->estadoForm = 'BORRADOR';
    }

    private function parsearSintesis(
        ?string $texto
    ): array {
        if (!$texto) {
            return [];
        }

        $resultado = [];

        foreach (
            preg_split('/\R/u', $texto) ?: []
            as $linea
        ) {
            $linea = trim($linea);

            if ($linea === '') {
                continue;
            }

            $partes = explode(':', $linea, 2);

            $resultado[] = [
                'titulo' => count($partes) === 2
                    ? trim($partes[0])
                    : 'Nota',

                'valor' => count($partes) === 2
                    ? trim($partes[1])
                    : $linea,
            ];
        }

        return $resultado;
    }

    private function antecedentesActivos(
        FichaMedicaAdulto $ficha
    ): array {
        return collect(self::ANTECEDENTES)
            ->filter(
                fn(string $label, string $campo) =>
                (bool) $ficha->{$campo}
            )
            ->values()
            ->all();
    }

    private function clasificarAlergia(
        ?string $alergias
    ): array {
        $texto = trim((string) $alergias);

        if ($texto === '') {
            return [
                'tipo' => 'SIN_DATO',
                'label' => 'Sin información de alergias',
                'texto' => null,
            ];
        }

        $normalizado = mb_strtoupper($texto);

        if (
            in_array(
                $normalizado,
                [
                    'NINGUNA',
                    'NINGUNA CONOCIDA',
                    'SIN ALERGIAS',
                    'NO REFIERE',
                ],
                true
            )
        ) {
            return [
                'tipo' => 'SIN_CONOCIDAS',
                'label' => 'Sin alergias conocidas',
                'texto' => $texto,
            ];
        }

        return [
            'tipo' => 'REGISTRADA',
            'label' => 'Alergia documentada',
            'texto' => $texto,
        ];
    }

    private function normalizarCampos(): void
    {
        foreach (
            [
                'codAm',
                'diagnosticosReferidos',
                'antecedentesRelevantes',
                'medicacionActualResumen',
                'alergiasReferidas',
                'restriccionesAlimentarias',
                'hospitalizaciones',
                'cirugias',
                'condicionMedicaGeneral',
                'estadoNeurologicoBasico',
                'nivelDependenciaSugerido',
                'resultadoAdmision',
                'motivoDecision',
                'recomendacionMedica',
                'estadoForm',
            ] as $campo
        ) {
            $this->{$campo} = trim(
                (string) $this->{$campo}
            );
        }
    }

    private function normalizarFiltros(): void
    {
        $this->filtroEstado = $this->normalizarOpcion(
            $this->filtroEstado,
            self::ESTADOS_REGISTRO
        );

        $this->filtroAlergias = $this->normalizarOpcion(
            $this->filtroAlergias,
            self::FILTROS_ALERGIAS
        );

        $this->filtroPeriodo = $this->normalizarOpcion(
            $this->filtroPeriodo,
            self::FILTROS_PERIODO
        );

        $this->orden = $this->normalizarOpcion(
            $this->orden,
            self::ORDENES,
            'RECIENTES'
        );

        if (
            $this->filtroAntecedente !== ''
            && !array_key_exists(
                $this->filtroAntecedente,
                self::ANTECEDENTES
            )
        ) {
            $this->filtroAntecedente = '';
        }
    }

    private function normalizarOpcion(
        string $valor,
        array $permitidos,
        string $default = ''
    ): string {
        $valor = strtoupper(
            trim($valor)
        );

        return in_array(
            $valor,
            $permitidos,
            true
        )
            ? $valor
            : $default;
    }

    private function resetFormulario(): void
    {
        $this->editandoId = null;

        $this->codAm = '';
        $this->fecha = '';
        $this->hora = '';

        $this->hipertension = false;
        $this->diabetes = false;
        $this->problemasCardiacos = false;
        $this->acv = false;
        $this->parkinson = false;
        $this->epilepsia = false;
        $this->alzheimerDiagnosticado = false;
        $this->depresion = false;
        $this->ansiedad = false;
        $this->problemasSueno = false;
        $this->problemasVisuales = false;
        $this->problemasAuditivos = false;
        $this->dolorCronico = false;

        $this->diagnosticosReferidos = '';
        $this->antecedentesRelevantes = '';
        $this->medicacionActualResumen = '';
        $this->alergiasReferidas = '';
        $this->restriccionesAlimentarias = '';
        $this->hospitalizaciones = '';
        $this->cirugias = '';

        $this->condicionMedicaGeneral = '';
        $this->estadoNeurologicoBasico = '';
        $this->nivelDependenciaSugerido = '';
        $this->requiereSeguimientoEsp = false;

        $this->resultadoAdmision = '';
        $this->motivoDecision = '';
        $this->recomendacionMedica = '';
        $this->estadoForm = 'BORRADOR';

        $this->resetValidation();
    }

    private function estadoPersistido(): string
    {
        return $this->estadoForm === 'COMPLETADA'
            ? 'ACTIVO'
            : 'BORRADOR';
    }

    private function textoONull(
        ?string $valor
    ): ?string {
        $valor = trim(
            (string) $valor
        );

        return $valor !== ''
            ? $valor
            : null;
    }

    private function autorizarVista(): void
    {
        $usuario = Auth::user();

        abort_unless(
            $usuario,
            401
        );

        abort_unless(
            $usuario->can('valoracion_medica.ver'),
            403,
            'No cuenta con permiso para consultar valoraciones médicas.'
        );
    }

    private function autorizarCreacion(): void
    {
        $usuario = Auth::user();

        abort_unless(
            $usuario,
            401
        );

        abort_unless(
            $usuario->hasAnyRole([
                'SUPERADMINISTRADOR',
                'MEDICO GENERAL/GERIATRA',
            ]),
            403,
            'La valoración médica solo puede ser registrada por personal médico autorizado.'
        );

        abort_unless(
            $usuario->can('valoracion_medica.crear')
                && $usuario->can('ficha_medica.crear'),
            403,
            'No cuenta con permisos para registrar una valoración médica.'
        );
    }

    private function autorizarEdicion(): void
    {
        $usuario = Auth::user();

        abort_unless(
            $usuario,
            401
        );

        abort_unless(
            $usuario->hasAnyRole([
                'SUPERADMINISTRADOR',
                'MEDICO GENERAL/GERIATRA',
            ]),
            403,
            'La valoración médica solo puede ser modificada por personal médico autorizado.'
        );

        abort_unless(
            $usuario->can('valoracion_medica.crear')
                && $usuario->can('ficha_medica.editar'),
            403,
            'No cuenta con permisos para continuar un borrador médico.'
        );
    }
}
