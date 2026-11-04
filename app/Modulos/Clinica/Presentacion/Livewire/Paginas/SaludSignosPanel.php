<?php

namespace App\Modulos\Clinica\Presentacion\Livewire\Paginas;

use App\Models\Residente;
use App\Models\SignoVital;
use App\Modulos\Clinica\Aplicacion\Acciones\GestionarEstadoSignosPanel;
use App\Modulos\Clinica\Aplicacion\Acciones\SignosVitalesService;
use App\Modulos\Clinica\Aplicacion\Consultas\AnalisisSignosPanel;
use App\Modulos\Clinica\Aplicacion\Validacion\ValidacionSignosVitalesService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class SaludSignosPanel extends Component
{
    use WithPagination;

    public ?Residente $adulto = null;

    public string $adultoSeleccionado = '';

    public string $buscarPaciente = '';

    public bool $modalFormulario = false;

    public bool $modalPresion = false;

    public bool $modalCardiaca = false;

    public bool $modalTemperatura = false;

    public bool $modalPeso = false;

    public bool $modalObservacion = false;

    public bool $modalDetalle = false;

    public bool $modalAnular = false;

    public ?string $signoId = null;

    public string $fecha = '';

    public string $hora = '';

    public ?int $presion_sistolica = null;

    public ?int $presion_diastolica = null;

    public ?int $frecuencia_cardiaca = null;

    public ?int $frecuencia_respiratoria = null;

    public mixed $temperatura = null;

    public ?int $saturacion = null;

    public mixed $glucosa = null;

    public mixed $peso = null;

    public mixed $talla = null;

    public mixed $imc = null;

    public ?int $dolor = null;

    public string $observacion = '';

    public string $motivoRectificacion = '';

    public ?string $signoIdAnular = null;

    public string $motivoAnulacion = '';

    public ?string $signoDetalleId = null;

    public string $fechaDesde = '';

    public string $fechaHasta = '';

    public string $parametro = 'todos';

    public string $estadoClinico = 'todos';

    public string $responsable = 'todos';

    public string $grafica = 'presion';

    public int $perPage = 10;

    public bool $guardarConfirmado = false;

    protected $paginationTheme = 'tailwind';

    private ?AnalisisSignosPanel $analisisSignos = null;

    public function mount(?Residente $adulto = null): void
    {
        if ($adulto && $adulto->exists) {
            $this->cargarAdulto($adulto->cod_residente);
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['peso', 'talla'], true)) {
            $this->calcularImcVisual();
        }

        if (in_array($property, ['fechaDesde', 'fechaHasta', 'parametro', 'estadoClinico', 'responsable', 'perPage'], true)) {
            $this->resetPage();
        }

        // Removed automatic update on adultoSeleccionado. It will be handled by buscarPacienteAction().
    }

    public function render()
    {
        $baseSignos = collect();
        if ($this->adulto) {
            $baseSignos = $this->adulto->signosVitales()
                ->with('registradoPor')
                ->orderByDesc('fecha_hora')
                ->get();
        }

        $this->analizador()->preparar($baseSignos);
        $signosFiltrados = $this->aplicarFiltros($baseSignos);
        $signosVigentesFiltrados = $signosFiltrados
            ->where('estado', 'VIGENTE')
            ->values();

        $ultimoVigente = $baseSignos->firstWhere('estado', 'VIGENTE');
        $totalVigentes = $baseSignos->where('estado', 'VIGENTE')->count();
        $totalAnulados = $baseSignos->where('estado', 'ANULADO')->count();
        $signoDetalle = $this->signoDetalleId
            ? $baseSignos->firstWhere('cod_signo', $this->signoDetalleId)
            : null;

        $analisisPorRegistro = $signosFiltrados
            ->mapWithKeys(fn ($signo) => [$signo->cod_signo => $this->analizarRegistro($signo)])
            ->all();

        $chartData = $this->construirChartData($signosVigentesFiltrados);

        return view('livewire.clinica.salud-signos-panel', [
            'adulto' => $this->adulto,
            'historial' => $this->adulto ? $this->paginarColeccion($signosFiltrados) : new LengthAwarePaginator([], 0, $this->perPage),
            'signosFiltrados' => $signosFiltrados,
            'ultimoVigente' => $ultimoVigente,
            'totalVigentes' => $totalVigentes,
            'totalAnulados' => $totalAnulados,
            'signoDetalle' => $signoDetalle,
            'alertasFormulario' => $this->modalFormulario ? $this->detectarAlertas() : [],
            'metricas' => $this->adulto ? $this->construirMetricas($signosVigentesFiltrados) : [],
            'resumenPeriodo' => $this->construirResumenPeriodo($signosVigentesFiltrados, $baseSignos),
            'interpretacion' => $this->construirInterpretacion($signosVigentesFiltrados),
            'chartData' => $chartData,
            'chartKey' => md5(json_encode($chartData)),
            'responsables' => $this->obtenerResponsables($baseSignos),
            'analisisPorRegistro' => $analisisPorRegistro,
            'pacientesSelector' => $this->obtenerPacientesSelector(),
            'pacienteResumen' => $this->adulto ? $this->construirPacienteResumen($baseSignos, $ultimoVigente) : null,
            'reporteClinico' => $this->adulto ? $this->construirReporteClinico($signosVigentesFiltrados, $ultimoVigente) : null,
        ]); // Removed ->layout('layouts.sistema') as it's a subcomponent now
    }

    public function buscarPacienteAction(): void
    {
        if (trim($this->adultoSeleccionado) === '') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Seleccione un paciente',
                'text' => 'Seleccione un adulto mayor antes de buscar.',
            ]);

            return;
        }

        $adultoExistente = Residente::find($this->adultoSeleccionado);
        if (! $adultoExistente) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No encontrado',
                'text' => 'El adulto mayor seleccionado no existe.',
            ]);

            return;
        }

        $this->cargarAdulto($this->adultoSeleccionado);

        if ($this->adulto->signosVitales()->count() === 0) {
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Sin registros',
                'text' => 'No existen registros de signos vitales para este adulto mayor.',
            ]);
        }
    }

    public function actualizarPanel(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->fechaDesde = '';
        $this->fechaHasta = '';
        $this->parametro = 'todos';
        $this->estadoClinico = 'todos';
        $this->responsable = 'todos';
        $this->perPage = 10;
        $this->resetPage();
    }

    private function cargarAdulto(string $codResidente): void
    {
        if (auth()->user()?->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codResidente, auth()->user());
        }
        $this->adulto = Residente::findOrFail($codResidente);
        $this->adultoSeleccionado = $this->adulto->cod_residente;
        $this->signoDetalleId = null;
        $this->limpiarFiltros();
    }

    private function obtenerPacientesSelector()
    {
        $query = Residente::query()
            ->withCount('signosVitales')
            ->orderBy('nombres')
            ->orderBy('apellido_paterno');
        if (auth()->user()?->hasRole('ENFERMEROS')) {
            $query->whereIn('cod_residente', app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds(auth()->user()));
        }

        if (trim($this->buscarPaciente) !== '') {
            $busqueda = '%'.trim($this->buscarPaciente).'%';
            $query->where(function ($subQuery) use ($busqueda) {
                $subQuery->where('cod_residente', 'like', $busqueda)
                    ->orWhere('nombres', 'like', $busqueda)
                    ->orWhere('apellido_paterno', 'like', $busqueda)
                    ->orWhere('apellido_materno', 'like', $busqueda)
                    ->orWhere('numero_documento', 'like', $busqueda);
            });
        }

        return $query->limit(12)->get();
    }

    private function construirPacienteResumen(Collection $baseSignos, ?SignoVital $ultimoVigente): array
    {
        $nombre = trim("{$this->adulto->nombres} {$this->adulto->ap_paterno} {$this->adulto->ap_materno}");

        return [
            'nombre' => $nombre !== '' ? $nombre : 'Adulto mayor',
            'codigo' => $this->adulto->cod_residente,
            'estado' => optional($this->adulto->estado)->estado ?? 'Sin estado',
            'edad' => $this->adulto->fecha_nac ? $this->adulto->fecha_nac->age.' años' : 'Edad no registrada',
            'ci' => $this->adulto->ci ?: 'S/D',
            'ultimo_control' => $ultimoVigente ? $this->formatearFechaHora($ultimoVigente) : 'Sin controles',
            'registros' => $baseSignos->count(),
            'vigentes' => $baseSignos->where('estado', 'VIGENTE')->count(),
        ];
    }

    private function construirReporteClinico(Collection $signos, ?SignoVital $ultimoVigente): array
    {
        $analisisUltimo = $ultimoVigente
            ? $this->analizarRegistro($ultimoVigente)
            : [
                'key' => 'sin_datos',
                'label' => 'Sin datos',
                'message' => 'No existen mediciones registradas para este adulto mayor.',
                'motivos' => [],
            ];

        $metricas = $this->construirMetricas($signos);
        $principal = collect($metricas)->firstWhere('key', $this->grafica) ?? $metricas[0];

        $observaciones = $signos
            ->filter(fn ($signo) => filled($signo->observacion))
            ->take(3)
            ->map(fn ($signo) => [
                'fecha' => $this->formatearFechaHora($signo),
                'texto' => $signo->observacion,
            ])
            ->values()
            ->all();

        return [
            'estado' => $analisisUltimo,
            'metrica' => $principal,
            'observaciones' => $observaciones,
            'total_periodo' => $signos->count(),
            'mensaje' => $analisisUltimo['message'],
        ];
    }

    public function abrirFormularioNuevo(): void
    {
        abort_if(! $this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(! auth()->user()->can('signos_vitales.crear'), 403);
        $this->limpiarFormulario();
        $this->modalFormulario = true;
    }

    public function abrirFormularioEditar(string $id): void
    {
        abort_if(auth()->user()?->hasRole('ENFERMEROS'), 403, 'Los registros clínicos firmados se rectifican; no se editan.');
        abort_if(! auth()->user()->can('signos_vitales.editar'), 403);

        $signo = SignoVital::findOrFail($id);
        abort_if(! $this->adulto || $signo->cod_residente !== $this->adulto->cod_residente, 403);

        if ($signo->estado === 'ANULADO') {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No permitido',
                'text' => 'No se puede editar un registro anulado.',
            ]);

            return;
        }

        $this->signoId = $signo->cod_signo;
        $this->fecha = $signo->fecha->format('Y-m-d');
        $this->hora = $signo->hora_formateada;
        $this->presion_sistolica = $signo->presion_sistolica;
        $this->presion_diastolica = $signo->presion_diastolica;
        $this->frecuencia_cardiaca = $signo->frecuencia_cardiaca;
        $this->frecuencia_respiratoria = $signo->frecuencia_respiratoria;
        $this->temperatura = $signo->temperatura;
        $this->saturacion = $signo->saturacion;
        $this->glucosa = $signo->glucosa;
        $this->peso = $signo->peso;
        $this->talla = $signo->talla;
        $this->imc = $signo->imc;
        $this->dolor = $signo->dolor;
        $this->observacion = $signo->observacion ?? '';
        $this->guardarConfirmado = false;
        $this->modalFormulario = true;
    }

    public function abrirDetalle(string $id): void
    {
        $this->signoDetalleId = $id;
        $this->modalDetalle = true;
    }

    public function guardar(): void
    {
        abort_if(! $this->adulto, 403, 'Paciente no seleccionado.');
        $esRectificacion = $this->signoId !== null;
        $permiso = $esRectificacion ? 'signos_vitales.editar' : 'signos_vitales.crear';
        abort_unless(auth()->user()->can($permiso), 403);

        $alertas = $this->detectarAlertas();
        if (! empty($alertas) && ! $this->guardarConfirmado) {
            $this->dispatch('signos-confirmar-alertas', [
                'titulo' => 'Valores fuera del rango referencial',
                'texto' => 'Se detectaron valores que requieren revisión del personal de salud. Verifique la información antes de guardar.',
            ]);

            return;
        }
        $this->guardarConfirmado = false;
        $datos = [
            'fecha' => $this->fecha, 'hora' => $this->hora,
            'presion_sistolica' => $this->presion_sistolica, 'presion_diastolica' => $this->presion_diastolica,
            'frecuencia_cardiaca' => $this->frecuencia_cardiaca, 'frecuencia_respiratoria' => $this->frecuencia_respiratoria,
            'temperatura' => $this->temperatura, 'saturacion' => $this->saturacion, 'glucosa' => $this->glucosa,
            'peso' => $this->peso, 'talla' => $this->talla, 'dolor' => $this->dolor, 'observacion' => $this->observacion,
            'valor_atipico_confirmado' => ! empty($alertas),
        ];
        $servicio = app(SignosVitalesService::class);
        if ($esRectificacion) {
            $original = SignoVital::where('cod_residente', $this->adulto->cod_residente)->findOrFail($this->signoId);
            $servicio->rectificar($original, $datos, $this->motivoRectificacion, auth()->user());
        } else {
            $servicio->registrar($this->adulto->cod_residente, $datos, auth()->user(), $permiso);
        }

        $this->cerrarFormulario();
        $this->resetPage();
        $this->dispatch('swal', [
            'icon' => ! empty($alertas) ? 'warning' : 'success',
            'title' => $esRectificacion ? 'Rectificación registrada' : 'Registro guardado',
            'text' => $esRectificacion ? 'El registro original se conserva sin modificaciones.' : 'Los signos vitales se guardaron correctamente.',
        ]);
    }

    public function confirmarGuardarConAlertas(): void
    {
        abort_if(! $this->adulto, 403, 'Paciente no seleccionado.');
        $this->guardarConfirmado = true;
        $this->guardar();
    }

    public function abrirAnular(string $id): void
    {
        abort_if(auth()->user()?->hasRole('ENFERMEROS'), 403, 'Los registros firmados se rectifican; no se eliminan ni restauran desde Enfermería.');
        abort_if(! auth()->user()->can('signos_vitales.anular'), 403);
        $this->dispatch('signos-confirmar-anulacion', ['id' => $id]);
    }

    public function anularConMotivo(string $id, string $motivo): void
    {
        abort_if(auth()->user()?->hasRole('ENFERMEROS'), 403, 'Los registros firmados se rectifican; no se eliminan ni restauran desde Enfermería.');
        abort_if(! $this->adulto, 403, 'Paciente no seleccionado.');
        abort_if(! auth()->user()->can('signos_vitales.anular'), 403);

        $motivo = trim($motivo);

        if (mb_strlen($motivo) < 10) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Motivo requerido',
                'text' => 'El motivo de anulación debe tener al menos 10 caracteres.',
            ]);

            return;
        }

        app(GestionarEstadoSignosPanel::class)->anularConMotivo(auth()->user(), ['adulto' => $this->adulto], $id, $motivo);

        $this->signoDetalleId = null;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro anulado',
            'text' => 'El control de signos vitales fue anulado correctamente. No se eliminaron datos clínicos.',
        ]);
    }

    public function confirmarAnular(): void
    {
        app(GestionarEstadoSignosPanel::class)->confirmarAnular(auth()->user(), [
            'adulto' => $this->adulto,
            'signoIdAnular' => $this->signoIdAnular,
            'motivoAnulacion' => $this->motivoAnulacion,
        ]);

        $this->modalAnular = false;
        $this->signoIdAnular = null;
        $this->motivoAnulacion = '';

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro anulado',
            'text' => 'El control de signos vitales fue anulado correctamente. No se eliminaron datos clínicos.',
        ]);
    }

    public function restaurarRegistro(string $id): void
    {
        app(GestionarEstadoSignosPanel::class)->restaurarRegistro(auth()->user(), [
            'adulto' => $this->adulto,
        ], $id);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro restaurado',
            'text' => 'El control de signos vitales fue restaurado correctamente.',
        ]);
    }

    public function abrirFormularioPresion(): void
    {
        abort_if(! $this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(! auth()->user()->can('signos_vitales.crear'), 403);
        $this->limpiarFormulario();
        $this->modalPresion = true;
    }

    public function abrirFormularioCardiaca(): void
    {
        abort_if(! $this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(! auth()->user()->can('signos_vitales.crear'), 403);
        $this->limpiarFormulario();
        $this->modalCardiaca = true;
    }

    public function abrirFormularioTemperatura(): void
    {
        abort_if(! $this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(! auth()->user()->can('signos_vitales.crear'), 403);
        $this->limpiarFormulario();
        $this->modalTemperatura = true;
    }

    public function abrirFormularioPeso(): void
    {
        abort_if(! $this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(! auth()->user()->can('signos_vitales.crear'), 403);
        $this->limpiarFormulario();
        $this->modalPeso = true;
    }

    public function abrirFormularioObservacion(): void
    {
        abort_if(! $this->adulto, 403, 'Debe seleccionar un paciente antes de registrar signos vitales.');
        abort_if(! auth()->user()->can('signos_vitales.crear'), 403);
        $this->limpiarFormulario();
        $this->modalObservacion = true;
    }

    public function cerrarModal(): void
    {
        $this->cerrarFormulario();
        $this->cerrarDetalle();
        $this->cerrarAnular();
    }

    public function cerrarFormulario(): void
    {
        $this->modalFormulario = false;
        $this->modalPresion = false;
        $this->modalCardiaca = false;
        $this->modalTemperatura = false;
        $this->modalPeso = false;
        $this->modalObservacion = false;
        $this->guardarConfirmado = false;
        $this->limpiarFormulario();
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalle = false;
        $this->signoDetalleId = null;
    }

    public function cerrarAnular(): void
    {
        $this->modalAnular = false;
        $this->signoIdAnular = null;
        $this->motivoAnulacion = '';
    }

    public function limpiarFormulario(): void
    {
        $this->signoId = null;
        $this->motivoRectificacion = '';
        $this->fecha = now()->toDateString();
        $this->hora = now()->format('H:i');
        $this->presion_sistolica = null;
        $this->presion_diastolica = null;
        $this->frecuencia_cardiaca = null;
        $this->frecuencia_respiratoria = null;
        $this->temperatura = null;
        $this->saturacion = null;
        $this->glucosa = null;
        $this->peso = null;
        $this->talla = null;
        $this->imc = null;
        $this->dolor = null;
        $this->observacion = '';
        $this->guardarConfirmado = false;
        $this->resetErrorBag();
    }

    public function detectarAlertas(): array
    {
        return $this->analizador()->detectarAlertas([
            'presion_sistolica' => $this->presion_sistolica,
            'presion_diastolica' => $this->presion_diastolica,
            'frecuencia_cardiaca' => $this->frecuencia_cardiaca,
            'saturacion' => $this->saturacion,
            'temperatura' => $this->temperatura,
            'frecuencia_respiratoria' => $this->frecuencia_respiratoria,
            'glucosa' => $this->glucosa,
            'fecha_hora' => ($this->fecha && $this->hora && strtotime($this->fecha.' '.$this->hora) !== false) ? $this->fecha.' '.$this->hora : null,
        ], $this->adulto?->cod_residente);
    }

    private function calcularImcVisual(): void
    {
        if (! $this->peso || ! $this->talla || (float) $this->peso <= 0 || (float) $this->talla <= 0) {
            $this->imc = null;

            return;
        }

        $this->imc = ValidacionSignosVitalesService::calcularImc((float) $this->peso, (float) $this->talla);
    }

    private function aplicarFiltros(Collection $signos): Collection
    {
        return $signos
            ->filter(function ($signo) {
                if ($this->fechaDesde && $signo->fecha->lt(Carbon::parse($this->fechaDesde)->startOfDay())) {
                    return false;
                }

                if ($this->fechaHasta && $signo->fecha->gt(Carbon::parse($this->fechaHasta)->endOfDay())) {
                    return false;
                }

                if ($this->responsable !== 'todos' && (string) $signo->registrado_por !== (string) $this->responsable) {
                    return false;
                }

                if (! $this->cumpleFiltroParametro($signo)) {
                    return false;
                }

                if ($this->estadoClinico !== 'todos') {
                    return $this->analizarRegistro($signo)['key'] === $this->estadoClinico;
                }

                return true;
            })
            ->values();
    }

    private function cumpleFiltroParametro(SignoVital $signo): bool
    {
        return match ($this->parametro) {
            'presion' => $signo->presion_sistolica !== null || $signo->presion_diastolica !== null || $signo->presion_arterial,
            'frecuencia_cardiaca' => $signo->frecuencia_cardiaca !== null,
            'saturacion' => $signo->saturacion !== null,
            'temperatura' => $signo->temperatura !== null,
            'frecuencia_respiratoria' => $signo->frecuencia_respiratoria !== null,
            'peso' => $signo->peso !== null,
            'glucosa' => $signo->glucosa !== null,
            'dolor' => $signo->dolor !== null,
            default => true,
        };
    }

    private function paginarColeccion(Collection $items): LengthAwarePaginator
    {
        $page = $this->getPage();
        $perPage = max(5, min(25, $this->perPage));

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    private function obtenerResponsables(Collection $signos): array
    {
        return $signos
            ->filter(fn ($signo) => $signo->registrado_por)
            ->mapWithKeys(fn ($signo) => [
                $signo->registrado_por => optional($signo->registradoPor)->name ?? 'Profesional no identificado',
            ])
            ->sort()
            ->all();
    }

    private function construirMetricas(Collection $signos): array
    {
        return $this->analizador()->construirMetricas($signos);
    }

    private function construirResumenPeriodo(Collection $signos, Collection $baseSignos): array
    {
        return $this->analizador()->construirResumenPeriodo($signos, $baseSignos);
    }

    private function construirInterpretacion(Collection $signos): array
    {
        return $this->analizador()->construirInterpretacion($signos);
    }

    private function construirChartData(Collection $signos): array
    {
        $ordenados = $signos
            ->sortBy(fn ($signo) => $signo->fecha->format('Ymd').$signo->hora_formateada)
            ->values();

        $serie = $ordenados->slice(max(0, $ordenados->count() - 24))->values();
        $labels = $serie->map(fn ($signo) => $signo->fecha->format('d/m').' '.$signo->hora_formateada)->all();
        $estados = $signos->map(fn ($signo) => $this->analizarRegistro($signo)['key']);

        return [
            'labels' => $labels,
            'active' => $this->grafica,
            'main' => $this->construirGraficaPrincipal($serie, $labels),
            'pressure' => [
                'sistolica' => $serie->map(fn ($signo) => $signo->presion_sistolica)->all(),
                'diastolica' => $serie->map(fn ($signo) => $signo->presion_diastolica)->all(),
            ],
            'heart' => $serie->map(fn ($signo) => $signo->frecuencia_cardiaca)->all(),
            'saturation' => $serie->map(fn ($signo) => $signo->saturacion)->all(),
            'temperature' => $serie->map(fn ($signo) => $signo->temperatura ? (float) $signo->temperatura : null)->all(),
            'respiration' => $serie->map(fn ($signo) => $signo->frecuencia_respiratoria)->all(),
            'weight' => $serie->map(fn ($signo) => $signo->peso ? (float) $signo->peso : null)->all(),
            'imc' => $serie->map(fn ($signo) => $signo->imc ? (float) $signo->imc : null)->all(),
            'monthly' => $this->construirResumenSemanal($signos),
            'states' => [
                'labels' => ['En referencia', 'Advertencia / revisión sugerida', 'Alto', 'Crítico', 'Sin datos', 'Sin interpretación automática', 'En objetivo médico'],
                'data' => [
                    $estados->filter(fn ($key) => $key === 'normal')->count(),
                    $estados->filter(fn ($key) => $key === 'observacion')->count(),
                    $estados->filter(fn ($key) => $key === 'fuera_rango')->count(),
                    $estados->filter(fn ($key) => $key === 'requiere_revision')->count(),
                    $estados->filter(fn ($key) => $key === 'sin_datos')->count(),
                    $estados->filter(fn ($key) => $key === 'sin_interpretacion')->count(),
                    $estados->filter(fn ($key) => $key === 'objetivo_personalizado')->count(),
                ],
            ],
            'hasEnough' => [
                'pressure' => $serie->whereNotNull('presion_sistolica')->count() >= 2 && $serie->whereNotNull('presion_diastolica')->count() >= 2,
                'heart' => $serie->whereNotNull('frecuencia_cardiaca')->count() >= 2,
                'saturation' => $serie->whereNotNull('saturacion')->count() >= 2,
                'temperature' => $serie->whereNotNull('temperatura')->count() >= 2,
                'respiration' => $serie->whereNotNull('frecuencia_respiratoria')->count() >= 2,
                'weight' => $serie->whereNotNull('peso')->count() >= 2,
                'imc' => $serie->whereNotNull('imc')->count() >= 2,
                'monthly' => $signos->count() >= 1,
                'states' => $signos->count() >= 1,
            ],
        ];
    }

    private function construirGraficaPrincipal(Collection $serie, array $labels): array
    {
        $configuraciones = [
            'presion' => [
                'title' => 'Evolución de presión arterial',
                'subtitle' => 'Sistólica y diastólica en mmHg.',
                'unit' => 'mmHg',
                'hasEnough' => $serie->whereNotNull('presion_sistolica')->count() >= 2 && $serie->whereNotNull('presion_diastolica')->count() >= 2,
                'datasets' => [
                    ['label' => 'Sistólica', 'data' => $serie->map(fn ($signo) => $signo->presion_sistolica)->all(), 'tone' => 'clinical', 'fill' => true],
                    ['label' => 'Diastólica', 'data' => $serie->map(fn ($signo) => $signo->presion_diastolica)->all(), 'tone' => 'reference', 'fill' => false],
                ],
            ],
            'frecuencia_cardiaca' => [
                'title' => 'Evolución de frecuencia cardíaca',
                'subtitle' => 'Tendencia temporal en lpm.',
                'unit' => 'lpm',
                'hasEnough' => $serie->whereNotNull('frecuencia_cardiaca')->count() >= 2,
                'datasets' => [
                    ['label' => 'Frecuencia cardíaca', 'data' => $serie->map(fn ($signo) => $signo->frecuencia_cardiaca)->all(), 'tone' => 'clinical', 'fill' => true],
                ],
            ],
            'saturacion' => [
                'title' => 'Evolución de saturación de oxígeno',
                'subtitle' => 'Seguimiento de SpO2 en porcentaje.',
                'unit' => '%',
                'hasEnough' => $serie->whereNotNull('saturacion')->count() >= 2,
                'datasets' => [
                    ['label' => 'Saturación O2', 'data' => $serie->map(fn ($signo) => $signo->saturacion)->all(), 'tone' => 'clinical', 'fill' => true],
                ],
            ],
            'temperatura' => [
                'title' => 'Evolución de temperatura',
                'subtitle' => 'Últimos controles registrados en °C.',
                'unit' => '°C',
                'hasEnough' => $serie->whereNotNull('temperatura')->count() >= 2,
                'datasets' => [
                    ['label' => 'Temperatura', 'data' => $serie->map(fn ($signo) => $signo->temperatura ? (float) $signo->temperatura : null)->all(), 'tone' => 'clinical', 'fill' => true],
                ],
            ],
            'frecuencia_respiratoria' => [
                'title' => 'Evolución de frecuencia respiratoria',
                'subtitle' => 'Tendencia respiratoria en rpm.',
                'unit' => 'rpm',
                'hasEnough' => $serie->whereNotNull('frecuencia_respiratoria')->count() >= 2,
                'datasets' => [
                    ['label' => 'Frecuencia respiratoria', 'data' => $serie->map(fn ($signo) => $signo->frecuencia_respiratoria)->all(), 'tone' => 'clinical', 'fill' => true],
                ],
            ],
            'peso' => [
                'title' => 'Evolución de peso e IMC',
                'subtitle' => 'Seguimiento antropométrico referencial.',
                'unit' => 'kg / kg/m²',
                'hasEnough' => $serie->whereNotNull('peso')->count() >= 2,
                'datasets' => [
                    ['label' => 'Peso', 'data' => $serie->map(fn ($signo) => $signo->peso ? (float) $signo->peso : null)->all(), 'tone' => 'care', 'fill' => true],
                    ['label' => 'IMC', 'data' => $serie->map(fn ($signo) => $signo->imc ? (float) $signo->imc : null)->all(), 'tone' => 'reference', 'fill' => false],
                ],
            ],
        ];

        return [
            'labels' => $labels,
            ...($configuraciones[$this->grafica] ?? $configuraciones['presion']),
        ];
    }

    private function construirResumenSemanal(Collection $signos): array
    {
        $labels = [];
        $data = [];
        $inicio = now()->subWeeks(5)->startOfWeek();

        for ($i = 0; $i < 6; $i++) {
            $desde = $inicio->copy()->addWeeks($i);
            $hasta = $desde->copy()->endOfWeek();
            $labels[] = $desde->format('d/m').' - '.$hasta->format('d/m');
            $data[] = $signos
                ->filter(fn ($signo) => $signo->fecha->betweenIncluded($desde, $hasta))
                ->count();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function analizador(): AnalisisSignosPanel
    {
        return $this->analisisSignos ??= app(AnalisisSignosPanel::class);
    }

    private function analizarRegistro(SignoVital $signo): array
    {
        return $this->analizador()->analizarRegistro($signo);
    }

    private function formatearFechaHora(SignoVital $signo): string
    {
        return $this->analizador()->formatearFechaHora($signo);
    }
}
