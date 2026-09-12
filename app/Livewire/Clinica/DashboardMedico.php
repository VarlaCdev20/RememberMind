<?php

namespace App\Livewire\Clinica;

use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AtencionAdulto;
use App\Models\EvaluacionGeriatrica;
use App\Models\FichaMedicaAdulto;
use App\Models\InstrumentoGeriatrico;
use App\Models\MedicacionAdulto;
use App\Models\NotaEvolucionMedica;
use App\Models\SignosVitalesAdulto;
use App\Models\ValoracionFuncionalAdulto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class DashboardMedico extends Component
{
    /**
     * Sección activa del panel.
     *
     * Se conserva por compatibilidad con las rutas actuales:
     * - admin.medico.dashboard
     * - admin.medico.valoraciones
     * - admin.medico.decisiones
     */
    public string $seccion = 'dashboard';

    protected $queryString = ['seccion'];

    /** @var \Illuminate\Support\Collection<int, \App\Models\AdultoMayor> */
    public $valoracionesPendientes;

    // -------------------------------------------------------------------------
    // KPIs existentes (se mantienen para no romper el Blade actual)
    // -------------------------------------------------------------------------
    public int $totalResidentes = 0;
    public int $pendientesValoracion = 0;
    public int $enSeguimientoActivo = 0;
    public int $alertasCriticas = 0;
    public int $notasHoy = 0;
    public int $signosHoy = 0;
    public int $totalMedicacionActiva = 0;

    // -------------------------------------------------------------------------
    // KPIs clínicos adicionales, construidos únicamente con tablas existentes
    // -------------------------------------------------------------------------
    public int $alertasAbiertas = 0;
    public int $controlesHoy = 0;
    public int $controlesVencidos = 0;
    public int $cognitivoAlto = 0;
    public int $cognitivoMedio = 0;
    public int $cognitivoBajo = 0;
    public int $cognitivoSinEvaluacion = 0;
    public int $omisionesMedicacion7d = 0;
    public int $reevaluacionesMedicacion = 0;

    // -------------------------------------------------------------------------
    // Datos para gráficos
    // -------------------------------------------------------------------------
    public array $chartEdad = [];
    public array $chartDiagnosticos = [];
    public array $chartDependencia = [];
    public array $chartImc = [];
    public array $chartTendencia = [];
    public array $chartEstados = [];
    public array $chartNotasTipo = [];
    public array $chartCognitivo = [];

    // -------------------------------------------------------------------------
    // Monitor clínico
    // -------------------------------------------------------------------------
    public float|int $kpiPaMedia = 0;
    public float|int $kpiFcMedia = 0;
    public float|int $kpiSatMedia = 0;
    public int $kpiSinRegistroHoy = 0;
    public int $kpiAdvertenciaSignos = 0;

    public array $ultimosSignosDash = [];

    // -------------------------------------------------------------------------
    // Listas operativas
    // -------------------------------------------------------------------------
    public array $alertasPacientes = [];
    public array $notasRecientes = [];
    public array $prioridadesMedicas = [];
    public array $controlesProximos = [];
    public array $resumenCognitivo = [];
    public array $incidenciasMedicacion = [];

    protected $listeners = [
        'valoracionMedicaCompletada' => '$refresh',
        'nota-evolucion-guardada' => '$refresh',
        'signos-actualizados' => '$refresh',
        'alerta-actualizada' => '$refresh',
        'evaluacion-geriatrica-guardada' => '$refresh',
        'medicacion-actualizada' => '$refresh',
    ];

    public function mount(): void
    {
        $usuario = auth()->user();

        abort_unless($usuario, 401);

        // La ruta médica ya está protegida por valoracion_medica.ver.
        // Se conserva además el control de rol para impedir acceso directo
        // a perfiles operativos que compartan otros permisos clínicos.
        if (!$usuario->hasAnyRole([
            'SUPERADMINISTRADOR',
            'MEDICO GENERAL/GERIATRA',
        ])) {
            abort(403);
        }

        $routeName = request()->route()?->getName() ?? '';

        $this->seccion = match ($routeName) {
            'admin.medico.valoraciones' => 'valoraciones',
            'admin.medico.decisiones' => 'decisiones',
            default => $this->seccion ?: 'dashboard',
        };

        $this->valoracionesPendientes = collect();
    }

    public function render()
    {
        $hoy = now()->toDateString();
        $estadosActivos = $this->estadosResidenteActivos();
        $estadosPendientesValoracion = $this->estadosValoracionMedicaPendiente();

        // Un solo universo clínico: todos los residentes activos del centro.
        $residentesActivos = AdultoMayor::query()
            ->whereHas('estado', fn(Builder $q) => $q->whereIn('estado', $estadosActivos))
            ->get(['cod_am', 'nombres', 'ap_paterno', 'ap_materno', 'fecha_nac', 'cod_est_adul']);

        $codigosResidentesActivos = $residentesActivos->pluck('cod_am')->values();

        $this->cargarValoracionesAdmision($estadosPendientesValoracion);
        $this->cargarKpisBasicos($hoy, $codigosResidentesActivos);

        $evaluacionesCognitivasActuales = $this->cargarCognicion($codigosResidentesActivos);

        $this->cargarAlertas($codigosResidentesActivos);
        $this->cargarControles($hoy, $codigosResidentesActivos);
        $this->cargarMedicacion($codigosResidentesActivos);
        $this->cargarNotasRecientes();
        $this->cargarMonitorSignos($hoy, $codigosResidentesActivos);

        $this->prioridadesMedicas = $this->construirPrioridadesMedicas(
            $evaluacionesCognitivasActuales,
            $codigosResidentesActivos
        );

        // Analítica poblacional secundaria.
        $this->chartEdad = $this->computeEdad($estadosActivos);
        $this->chartDiagnosticos = $this->computeDiagnosticos($codigosResidentesActivos);
        $this->chartDependencia = $this->computeDependencia($codigosResidentesActivos);
        $this->chartImc = $this->computeImc($codigosResidentesActivos);
        $this->chartTendencia = $this->computeTendencia($codigosResidentesActivos);
        $this->chartEstados = $this->computeEstados();
        $this->chartNotasTipo = $this->computeNotasTipo();

        return view('livewire.clinica.dashboard-medico')
            ->layout('layouts.sistema');
    }

    // =========================================================================
    // CARGA DE BLOQUES CLÍNICOS
    // =========================================================================

    private function cargarValoracionesAdmision(array $estadosPendientes): void
    {
        $this->valoracionesPendientes = AdultoMayor::query()
            ->whereHas('estado', fn(Builder $q) => $q->whereIn('estado', $estadosPendientes))
            ->with(['estado'])
            ->orderByDesc('created_at')
            ->get();

        $this->pendientesValoracion = $this->valoracionesPendientes->count();
    }

    private function cargarKpisBasicos(string $hoy, Collection $codigosActivos): void
    {
        $this->totalResidentes = $codigosActivos->count();

        $this->enSeguimientoActivo = AdultoMayor::query()
            ->whereIn('cod_am', $codigosActivos)
            ->whereHas('estado', fn(Builder $q) => $q->where('estado', 'EN_SEGUIMIENTO_ACTIVO'))
            ->count();

        $this->totalMedicacionActiva = MedicacionAdulto::query()
            ->whereIn('cod_am', $codigosActivos)
            ->where('estado', 'ACTIVO')
            ->count();

        $this->notasHoy = NotaEvolucionMedica::query()
            ->whereIn('cod_am', $codigosActivos)
            ->whereDate('fecha', $hoy)
            ->where('estado', 'ACTIVO')
            ->count();

        $this->signosHoy = SignosVitalesAdulto::query()
            ->whereIn('cod_am', $codigosActivos)
            ->whereDate('fecha', $hoy)
            ->where('estado', 'VIGENTE')
            ->count();
    }

    /**
     * Obtiene la evaluación cognitiva más reciente de cada residente activo.
     *
     * IMPORTANTE: NO existe ni se usa EvaluacionCognitiva.
     * Cognición se obtiene desde evaluaciones_geriatricas filtrando instrumentos
     * cuyo cod_area sea ARE_COG.
     *
     * @return \Illuminate\Support\Collection<string, \App\Models\EvaluacionGeriatrica>
     */
    private function cargarCognicion(Collection $codigosActivos): Collection
    {
        $instrumentosCognitivos = InstrumentoGeriatrico::query()
            ->where('cod_area', 'ARE_COG')
            ->where('estado', 'ACTIVO')
            ->pluck('cod_instrumento');

        if ($instrumentosCognitivos->isEmpty() || $codigosActivos->isEmpty()) {
            $this->cognitivoAlto = 0;
            $this->cognitivoMedio = 0;
            $this->cognitivoBajo = 0;
            $this->cognitivoSinEvaluacion = $codigosActivos->count();
            $this->resumenCognitivo = [];
            $this->chartCognitivo = [
                'labels' => ['Alto', 'Medio', 'Bajo', 'Sin evaluación'],
                'values' => [0, 0, 0, $this->cognitivoSinEvaluacion],
            ];

            return collect();
        }

        $ultimas = EvaluacionGeriatrica::query()
            ->with([
                'adulto:cod_am,nombres,ap_paterno,ap_materno',
                'instrumento:cod_instrumento,nombre,siglas,cod_area',
            ])
            ->whereIn('cod_am', $codigosActivos)
            ->whereIn('cod_instrumento', $instrumentosCognitivos)
            ->whereIn('estado', ['COMPLETADA', 'ALERTA'])
            ->orderByDesc('fecha_eval')
            ->orderByDesc('created_at')
            ->get()
            ->unique('cod_am')
            ->keyBy('cod_am');

        $conteo = [
            'ALTO' => 0,
            'MEDIO' => 0,
            'BAJO' => 0,
            'SIN_CLASIFICAR' => 0,
        ];

        foreach ($ultimas as $evaluacion) {
            $riesgo = $this->normalizarNivelRiesgo($evaluacion->nivel_riesgo);
            $conteo[$riesgo] = ($conteo[$riesgo] ?? 0) + 1;
        }

        $this->cognitivoAlto = $conteo['ALTO'];
        $this->cognitivoMedio = $conteo['MEDIO'];
        $this->cognitivoBajo = $conteo['BAJO'];
        $this->cognitivoSinEvaluacion = max(0, $codigosActivos->count() - $ultimas->count());

        $this->resumenCognitivo = $ultimas
            ->sortByDesc(function (EvaluacionGeriatrica $evaluacion) {
                return match ($this->normalizarNivelRiesgo($evaluacion->nivel_riesgo)) {
                    'ALTO' => 3,
                    'MEDIO' => 2,
                    'BAJO' => 1,
                    default => 0,
                };
            })
            ->take(8)
            ->map(function (EvaluacionGeriatrica $evaluacion) {
                return [
                    'cod_am' => $evaluacion->cod_am,
                    'paciente' => $evaluacion->adulto?->nombre_completo
                        ?? trim(($evaluacion->adulto?->nombres ?? '') . ' ' . ($evaluacion->adulto?->ap_paterno ?? '')),
                    'instrumento' => $evaluacion->instrumento?->siglas
                        ?: ($evaluacion->instrumento?->nombre ?? 'Instrumento'),
                    'puntaje' => $evaluacion->puntaje_total ?? $evaluacion->puntaje,
                    'resultado' => $evaluacion->categoria_resultado
                        ?? $evaluacion->resultado_cualitativo
                        ?? 'Sin clasificación',
                    'riesgo' => $this->normalizarNivelRiesgo($evaluacion->nivel_riesgo),
                    'alerta' => $evaluacion->nivel_alerta,
                    'fecha' => $this->formatearFecha($evaluacion->fecha_eval),
                ];
            })
            ->values()
            ->toArray();

        $this->chartCognitivo = [
            'labels' => ['Alto', 'Medio', 'Bajo', 'Sin evaluación'],
            'values' => [
                $this->cognitivoAlto,
                $this->cognitivoMedio,
                $this->cognitivoBajo,
                $this->cognitivoSinEvaluacion,
            ],
        ];

        return $ultimas;
    }

    private function cargarAlertas(Collection $codigosActivos): void
    {
        if ($codigosActivos->isEmpty()) {
            $this->alertasAbiertas = 0;
            $this->alertasCriticas = 0;
            $this->alertasPacientes = [];
            $this->kpiAdvertenciaSignos = 0;
            return;
        }

        $alertasAbiertas = AlertaAdulto::query()
            ->abiertas()
            ->whereIn('cod_am', $codigosActivos)
            ->with('adultoMayor:cod_am,nombres,ap_paterno,ap_materno')
            ->orderByRaw("CASE nivel WHEN 'CRITICO' THEN 1 WHEN 'ALTO' THEN 2 WHEN 'MEDIO' THEN 3 ELSE 4 END")
            ->orderByDesc('created_at')
            ->get();

        $this->alertasAbiertas = $alertasAbiertas->count();

        $this->alertasCriticas = $alertasAbiertas
            ->where('nivel', 'CRITICO')
            ->pluck('cod_am')
            ->unique()
            ->count();

        // Para el Blade actual, añadimos el último signo disponible de cada
        // residente sin volver a "crear" una alerta desde rangos hardcodeados.
        $codigosConAlerta = $alertasAbiertas->pluck('cod_am')->unique()->values();
        $ultimosSignos = $this->ultimosSignosPorResidente($codigosConAlerta);

        $this->alertasPacientes = $alertasAbiertas
            ->unique('cod_am')
            ->take(8)
            ->map(function (AlertaAdulto $alerta) use ($ultimosSignos) {
                /** @var \App\Models\SignosVitalesAdulto|null $sv */
                $sv = $ultimosSignos->get($alerta->cod_am);

                return [
                    'cod_alerta' => $alerta->cod_alerta,
                    'cod_am' => $alerta->cod_am,
                    'nombre' => $alerta->adultoMayor?->nombre_completo
                        ?? trim(($alerta->adultoMayor?->nombres ?? '') . ' ' . ($alerta->adultoMayor?->ap_paterno ?? '')),
                    'tipo' => $alerta->tipo_alerta ?: 'Alerta clínica',
                    'nivel' => $alerta->nivel,
                    'motivo' => $alerta->motivo,
                    'origen' => $alerta->origen,
                    'estado' => $alerta->estado,
                    'pa' => $sv?->presion_formateada ?? '—',
                    'sat' => $sv?->saturacion,
                    'gluc' => $sv?->glucosa !== null ? number_format((float) $sv->glucosa, 0) : null,
                    'fecha' => $alerta->created_at?->format('d/m/Y H:i') ?? '—',
                ];
            })
            ->values()
            ->toArray();

        // Ya no se calcula una "advertencia" paralela con umbrales locales.
        // El sistema formal de alertas es la fuente de verdad.
        $this->kpiAdvertenciaSignos = $alertasAbiertas
            ->filter(fn(AlertaAdulto $alerta) => str_contains(Str::upper((string) $alerta->origen), 'SIGN'))
            ->pluck('cod_am')
            ->unique()
            ->count();
    }

    private function cargarControles(string $hoy, Collection $codigosActivos): void
    {
        if ($codigosActivos->isEmpty()) {
            $this->controlesHoy = 0;
            $this->controlesVencidos = 0;
            $this->controlesProximos = [];
            return;
        }

        $estadosPendientes = ['PENDIENTE'];
        $estadosNoActivos = ['CANCELADA', 'CANCELADO', 'ANULADA', 'ANULADO'];

        $this->controlesHoy = AtencionAdulto::query()
            ->whereIn('cod_am', $codigosActivos)
            ->whereDate('fecha', $hoy)
            ->whereNotIn('estado', $estadosNoActivos)
            ->count();

        $this->controlesVencidos = AtencionAdulto::query()
            ->whereIn('cod_am', $codigosActivos)
            ->whereDate('fecha', '<', $hoy)
            ->whereIn('estado', $estadosPendientes)
            ->count();

        $this->controlesProximos = AtencionAdulto::query()
            ->with([
                'adultoMayor:cod_am,nombres,ap_paterno,ap_materno',
                'tipoAtencion:cod_tipo_aten,nombre',
            ])
            ->whereIn('cod_am', $codigosActivos)
            ->whereDate('fecha', '>=', $hoy)
            ->whereNotIn('estado', $estadosNoActivos)
            ->orderBy('fecha')
            ->orderBy('hora')
            ->limit(8)
            ->get()
            ->map(function (AtencionAdulto $atencion) {
                return [
                    'cod_atencion' => $atencion->cod_aten_adul,
                    'cod_am' => $atencion->cod_am,
                    'paciente' => $atencion->adultoMayor?->nombre_completo
                        ?? trim(($atencion->adultoMayor?->nombres ?? '') . ' ' . ($atencion->adultoMayor?->ap_paterno ?? '')),
                    'tipo' => $atencion->tipoAtencion?->nombre ?? 'Atención',
                    'fecha' => $this->formatearFecha($atencion->fecha),
                    'hora' => $atencion->hora ? substr((string) $atencion->hora, 0, 5) : '—',
                    'estado' => $atencion->estado,
                    'observacion' => Str::limit((string) ($atencion->observacion ?? ''), 100),
                ];
            })
            ->toArray();
    }

    private function cargarMedicacion(Collection $codigosActivos): void
    {
        if ($codigosActivos->isEmpty()) {
            $this->omisionesMedicacion7d = 0;
            $this->reevaluacionesMedicacion = 0;
            $this->incidenciasMedicacion = [];
            return;
        }

        $desde = now()->subDays(7)->toDateString();

        $omisiones = AdministracionMedicacion::query()
            ->with([
                'adultoMayor:cod_am,nombres,ap_paterno,ap_materno',
                'medicacion:cod_med_adulto,nombre_medicamento,dosis,via_administracion',
            ])
            ->whereIn('cod_am', $codigosActivos)
            ->whereDate('fecha', '>=', $desde)
            ->where('administrado', false)
            ->orderByDesc('fecha')
            ->orderByDesc('hora_programada')
            ->get();

        $this->omisionesMedicacion7d = $omisiones->count();

        // La tabla existente no tiene un campo "reevaluación completada".
        // Por eso este KPI no afirma que estén vencidas: cuenta indicaciones de
        // reevaluación recientes que todavía son clínicamente relevantes.
        $this->reevaluacionesMedicacion = AdministracionMedicacion::query()
            ->whereIn('cod_am', $codigosActivos)
            ->whereDate('fecha', '>=', now()->subDays(2)->toDateString())
            ->where('requiere_reevaluacion', true)
            ->count();

        $this->incidenciasMedicacion = $omisiones
            ->take(8)
            ->map(function (AdministracionMedicacion $registro) {
                return [
                    'cod_am' => $registro->cod_am,
                    'paciente' => $registro->adultoMayor?->nombre_completo
                        ?? trim(($registro->adultoMayor?->nombres ?? '') . ' ' . ($registro->adultoMayor?->ap_paterno ?? '')),
                    'medicamento' => $registro->medicacion?->nombre_medicamento ?? 'Medicamento',
                    'dosis' => $registro->medicacion?->dosis,
                    'motivo' => $registro->motivo_omision ?: 'Sin motivo registrado',
                    'fecha' => $this->formatearFecha($registro->fecha),
                    'hora' => $registro->hora_programada?->format('H:i') ?? '—',
                ];
            })
            ->toArray();
    }

    private function cargarNotasRecientes(): void
    {
        $this->notasRecientes = NotaEvolucionMedica::query()
            ->where('estado', 'ACTIVO')
            ->with([
                'adulto:cod_am,nombres,ap_paterno,ap_materno',
                'registrador:cod_usu,name',
            ])
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->limit(6)
            ->get()
            ->map(function (NotaEvolucionMedica $nota) {
                return [
                    'tipo' => $nota->tipo_nota,
                    'paciente' => $nota->adulto?->nombre_completo
                        ?? trim(($nota->adulto?->nombres ?? '') . ' ' . ($nota->adulto?->ap_paterno ?? '')),
                    'cod_am' => $nota->cod_am,
                    'plan' => Str::limit((string) ($nota->plan ?? ''), 90),
                    'valoracion' => Str::limit((string) ($nota->valoracion ?? ''), 70),
                    'fecha' => $this->formatearFecha($nota->fecha),
                    'hora' => $nota->hora ? substr((string) $nota->hora, 0, 5) : '',
                    'medico' => $nota->registrador?->name ?? 'Sistema',
                ];
            })
            ->toArray();
    }

    private function cargarMonitorSignos(string $hoy, Collection $codigosActivos): void
    {
        $ultimos = $this->ultimosSignosPorResidente($codigosActivos);

        if ($ultimos->isEmpty()) {
            $this->kpiPaMedia = 0;
            $this->kpiFcMedia = 0;
            $this->kpiSatMedia = 0;
            $this->kpiSinRegistroHoy = $codigosActivos->count();
            $this->ultimosSignosDash = [];
            return;
        }

        $pa = $ultimos->pluck('presion_sistolica')->filter(fn($v) => $v !== null && $v > 0);
        $fc = $ultimos->pluck('frecuencia_cardiaca')->filter(fn($v) => $v !== null && $v > 0);
        $sat = $ultimos->pluck('saturacion')->filter(fn($v) => $v !== null && $v > 0);

        $this->kpiPaMedia = $pa->isNotEmpty() ? (int) round($pa->avg()) : 0;
        $this->kpiFcMedia = $fc->isNotEmpty() ? (int) round($fc->avg()) : 0;
        $this->kpiSatMedia = $sat->isNotEmpty() ? round((float) $sat->avg(), 1) : 0;

        $registradosHoy = SignosVitalesAdulto::query()
            ->whereIn('cod_am', $codigosActivos)
            ->whereDate('fecha', $hoy)
            ->where('estado', 'VIGENTE')
            ->distinct('cod_am')
            ->count('cod_am');

        $this->kpiSinRegistroHoy = max(0, $codigosActivos->count() - $registradosHoy);

        $this->ultimosSignosDash = $ultimos
            ->sortByDesc(fn(SignosVitalesAdulto $sv) => $this->fechaHoraSigno($sv)->timestamp)
            ->take(8)
            ->map(function (SignosVitalesAdulto $sv) {
                return [
                    'cod_am' => $sv->cod_am,
                    'paciente' => $sv->adultoMayor?->nombre_completo
                        ?? trim(($sv->adultoMayor?->nombres ?? '') . ' ' . ($sv->adultoMayor?->ap_paterno ?? '')),
                    'pa' => $sv->presion_formateada ?? '—',
                    'fc' => $sv->frecuencia_cardiaca,
                    'fr' => $sv->frecuencia_respiratoria,
                    'temperatura' => $sv->temperatura,
                    'saturacion' => $sv->saturacion,
                    'glucosa' => $sv->glucosa,
                    'fecha' => $this->formatearFecha($sv->fecha),
                    'hora' => $sv->hora_formateada,
                    'confirmado' => (bool) $sv->valor_atipico_confirmado,
                ];
            })
            ->values()
            ->toArray();
    }

    // =========================================================================
    // PRIORIDAD MÉDICA
    // =========================================================================

    private function construirPrioridadesMedicas(
        Collection $evaluacionesCognitivasActuales,
        Collection $codigosActivos
    ): array {
        $items = collect();

        // 1) Alertas formales abiertas: fuente principal de urgencia clínica.
        AlertaAdulto::query()
            ->abiertas()
            ->whereIn('cod_am', $codigosActivos)
            ->whereIn('nivel', ['CRITICO', 'ALTO'])
            ->with('adultoMayor:cod_am,nombres,ap_paterno,ap_materno')
            ->orderByRaw("CASE nivel WHEN 'CRITICO' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->each(function (AlertaAdulto $alerta) use ($items) {
                $items->push([
                    'orden' => $alerta->nivel === 'CRITICO' ? 100 : 90,
                    'cod_am' => $alerta->cod_am,
                    'paciente' => $alerta->adultoMayor?->nombre_completo
                        ?? trim(($alerta->adultoMayor?->nombres ?? '') . ' ' . ($alerta->adultoMayor?->ap_paterno ?? '')),
                    'prioridad' => $alerta->nivel === 'CRITICO' ? 'CRITICA' : 'ALTA',
                    'origen' => 'ALERTA_CLINICA',
                    'motivo' => $alerta->motivo ?: ($alerta->tipo_alerta ?: 'Alerta clínica abierta'),
                    'estado' => $alerta->estado,
                    'fecha' => $alerta->created_at?->format('d/m/Y H:i') ?? '—',
                ]);
            });

        // 2) Valoraciones médicas de admisión pendientes.
        $this->valoracionesPendientes
            ->take(10)
            ->each(function (AdultoMayor $adulto) use ($items) {
                $items->push([
                    'orden' => 80,
                    'cod_am' => $adulto->cod_am,
                    'paciente' => $adulto->nombre_completo,
                    'prioridad' => 'ALTA',
                    'origen' => 'ADMISION',
                    'motivo' => 'Valoración médica de admisión pendiente',
                    'estado' => $adulto->estado?->estado ?? 'PENDIENTE',
                    'fecha' => $adulto->created_at?->format('d/m/Y H:i') ?? '—',
                ]);
            });

        // 3) Controles vencidos registrados en atenciones_adulto.
        AtencionAdulto::query()
            ->whereIn('cod_am', $codigosActivos)
            ->whereDate('fecha', '<', now()->toDateString())
            ->where('estado', 'PENDIENTE')
            ->with('adultoMayor:cod_am,nombres,ap_paterno,ap_materno')
            ->orderBy('fecha')
            ->limit(10)
            ->get()
            ->each(function (AtencionAdulto $atencion) use ($items) {
                $items->push([
                    'orden' => 70,
                    'cod_am' => $atencion->cod_am,
                    'paciente' => $atencion->adultoMayor?->nombre_completo
                        ?? trim(($atencion->adultoMayor?->nombres ?? '') . ' ' . ($atencion->adultoMayor?->ap_paterno ?? '')),
                    'prioridad' => 'MEDIA',
                    'origen' => 'CONTROL',
                    'motivo' => 'Control clínico pendiente desde ' . $this->formatearFecha($atencion->fecha),
                    'estado' => $atencion->estado,
                    'fecha' => $this->formatearFecha($atencion->fecha),
                ]);
            });

        // 4) Riesgo cognitivo ALTO ya almacenado por el módulo geriátrico.
        // No se recalculan puntos de corte ni se inventan reglas nuevas aquí.
        $evaluacionesCognitivasActuales
            ->filter(fn(EvaluacionGeriatrica $e) => $this->normalizarNivelRiesgo($e->nivel_riesgo) === 'ALTO')
            ->take(10)
            ->each(function (EvaluacionGeriatrica $evaluacion) use ($items) {
                $items->push([
                    'orden' => 65,
                    'cod_am' => $evaluacion->cod_am,
                    'paciente' => $evaluacion->adulto?->nombre_completo
                        ?? trim(($evaluacion->adulto?->nombres ?? '') . ' ' . ($evaluacion->adulto?->ap_paterno ?? '')),
                    'prioridad' => 'MEDIA',
                    'origen' => 'COGNICION',
                    'motivo' => sprintf(
                        'Riesgo cognitivo alto%s',
                        $evaluacion->instrumento?->siglas ? ' · ' . $evaluacion->instrumento->siglas : ''
                    ),
                    'estado' => $evaluacion->estado,
                    'fecha' => $this->formatearFecha($evaluacion->fecha_eval),
                ]);
            });

        // 5) Omisiones recientes de medicación, sin asumir causalidad clínica.
        AdministracionMedicacion::query()
            ->whereIn('cod_am', $codigosActivos)
            ->whereDate('fecha', '>=', now()->subDays(2)->toDateString())
            ->where('administrado', false)
            ->with([
                'adultoMayor:cod_am,nombres,ap_paterno,ap_materno',
                'medicacion:cod_med_adulto,nombre_medicamento',
            ])
            ->orderByDesc('fecha')
            ->limit(10)
            ->get()
            ->each(function (AdministracionMedicacion $registro) use ($items) {
                $items->push([
                    'orden' => 60,
                    'cod_am' => $registro->cod_am,
                    'paciente' => $registro->adultoMayor?->nombre_completo
                        ?? trim(($registro->adultoMayor?->nombres ?? '') . ' ' . ($registro->adultoMayor?->ap_paterno ?? '')),
                    'prioridad' => 'MEDIA',
                    'origen' => 'MEDICACION',
                    'motivo' => 'Medicación omitida: ' . ($registro->medicacion?->nombre_medicamento ?? 'medicamento'),
                    'estado' => 'OMITIDO',
                    'fecha' => $this->formatearFecha($registro->fecha),
                ]);
            });

        return $items
            ->sortByDesc('orden')
            ->unique(fn(array $item) => $item['origen'] . '|' . $item['cod_am'] . '|' . $item['motivo'])
            ->take(12)
            ->map(function (array $item) {
                unset($item['orden']);
                return $item;
            })
            ->values()
            ->toArray();
    }

    // =========================================================================
    // GRÁFICOS
    // =========================================================================

    private function computeEdad(array $estadosActivos): array
    {
        $adultos = AdultoMayor::query()
            ->whereHas('estado', fn(Builder $q) => $q->whereIn('estado', $estadosActivos))
            ->whereNotNull('fecha_nac')
            ->get(['fecha_nac', 'genero']);

        $grupos = [
            '60-69' => ['M' => 0, 'F' => 0],
            '70-79' => ['M' => 0, 'F' => 0],
            '80-89' => ['M' => 0, 'F' => 0],
            '90+' => ['M' => 0, 'F' => 0],
        ];

        foreach ($adultos as $adulto) {
            $edad = Carbon::parse($adulto->fecha_nac)->age;
            $sexo = strtoupper(substr((string) ($adulto->genero ?? 'M'), 0, 1)) === 'F' ? 'F' : 'M';

            if ($edad >= 90) {
                $grupos['90+'][$sexo]++;
            } elseif ($edad >= 80) {
                $grupos['80-89'][$sexo]++;
            } elseif ($edad >= 70) {
                $grupos['70-79'][$sexo]++;
            } else {
                $grupos['60-69'][$sexo]++;
            }
        }

        return [
            'labels' => ['60-69 años', '70-79 años', '80-89 años', '90+ años'],
            'masculino' => array_column($grupos, 'M'),
            'femenino' => array_column($grupos, 'F'),
        ];
    }

    private function computeDiagnosticos(Collection $codigosActivos): array
    {
        if ($codigosActivos->isEmpty()) {
            return ['labels' => [], 'values' => []];
        }

        $fichas = FichaMedicaAdulto::query()
            ->whereIn('cod_am', $codigosActivos)
            ->where('estado', 'ACTIVO')
            ->get([
                'hipertension',
                'diabetes',
                'problemas_cardiacos',
                'acv',
                'parkinson',
                'epilepsia',
                'alzheimer_diagnosticado',
                'depresion',
                'ansiedad',
                'dolor_cronico',
            ]);

        $mapa = [
            'hipertension' => 'Hipertensión',
            'diabetes' => 'Diabetes',
            'problemas_cardiacos' => 'Cardiopatía',
            'acv' => 'ACV / Ictus',
            'alzheimer_diagnosticado' => 'Alzheimer',
            'parkinson' => 'Parkinson',
            'depresion' => 'Depresión',
            'dolor_cronico' => 'Dolor crónico',
            'ansiedad' => 'Ansiedad',
            'epilepsia' => 'Epilepsia',
        ];

        $conteos = [];

        foreach ($mapa as $campo => $etiqueta) {
            $conteos[$etiqueta] = $fichas->where($campo, true)->count();
        }

        arsort($conteos);

        return [
            'labels' => array_keys($conteos),
            'values' => array_values($conteos),
        ];
    }

    /**
     * Distribución funcional actual por residente, no por cantidad histórica
     * de valoraciones.
     */
    private function computeDependencia(Collection $codigosActivos): array
    {
        if ($codigosActivos->isEmpty()) {
            return ['labels' => [], 'values' => []];
        }

        $ultimas = ValoracionFuncionalAdulto::query()
            ->whereIn('cod_am', $codigosActivos)
            ->where(function (Builder $q) {
                $q->whereNull('estado')
                    ->orWhereNotIn('estado', ['ANULADA', 'ANULADO']);
            })
            ->whereNotNull('nivel_dependencia')
            ->where('nivel_dependencia', '!=', '')
            ->orderByDesc('fecha_valoracion')
            ->orderByDesc('created_at')
            ->get(['cod_am', 'nivel_dependencia', 'fecha_valoracion', 'estado', 'created_at'])
            ->unique('cod_am');

        $conteos = $ultimas
            ->groupBy('nivel_dependencia')
            ->map(fn(Collection $grupo) => $grupo->count())
            ->sortDesc();

        return [
            'labels' => $conteos->keys()->values()->toArray(),
            'values' => $conteos->values()->map(fn($v) => (int) $v)->toArray(),
        ];
    }

    /**
     * IMC actual: toma el último registro de signos de cada residente.
     */
    private function computeImc(Collection $codigosActivos): array
    {
        $ultimos = $this->ultimosSignosPorResidente($codigosActivos);

        $categorias = [
            'Bajo peso' => 0,
            'Normal' => 0,
            'Sobrepeso' => 0,
            'Obesidad I' => 0,
            'Obesidad II+' => 0,
        ];

        foreach ($ultimos as $signo) {
            $imc = (float) ($signo->imc ?? 0);

            if ($imc <= 0) {
                continue;
            }

            if ($imc < 18.5) {
                $categorias['Bajo peso']++;
            } elseif ($imc < 25) {
                $categorias['Normal']++;
            } elseif ($imc < 30) {
                $categorias['Sobrepeso']++;
            } elseif ($imc < 35) {
                $categorias['Obesidad I']++;
            } else {
                $categorias['Obesidad II+']++;
            }
        }

        return [
            'labels' => array_keys($categorias),
            'values' => array_values($categorias),
        ];
    }

    private function computeTendencia(Collection $codigosActivos): array
    {
        if ($codigosActivos->isEmpty()) {
            return ['labels' => [], 'pa' => [], 'sat' => [], 'gluc' => []];
        }

        $datos = SignosVitalesAdulto::query()
            ->whereIn('cod_am', $codigosActivos)
            ->where('estado', 'VIGENTE')
            ->whereDate('fecha', '>=', now()->subDays(30)->toDateString())
            ->select(
                DB::raw('DATE(fecha) as dia'),
                DB::raw('ROUND(CAST(AVG(CASE WHEN presion_sistolica > 0 THEN presion_sistolica ELSE NULL END) AS DECIMAL(12,2)), 0) as avg_pa'),
                DB::raw('ROUND(CAST(AVG(CASE WHEN saturacion > 0 THEN saturacion ELSE NULL END) AS DECIMAL(12,2)), 1) as avg_sat'),
                DB::raw('ROUND(CAST(AVG(CASE WHEN glucosa > 0 THEN glucosa ELSE NULL END) AS DECIMAL(12,2)), 0) as avg_gluc')
            )
            ->groupBy(DB::raw('DATE(fecha)'))
            ->orderBy(DB::raw('DATE(fecha)'))
            ->get();

        return [
            'labels' => $datos->pluck('dia')
                ->map(fn($d) => Carbon::parse($d)->format('d/m'))
                ->toArray(),
            'pa' => $datos->pluck('avg_pa')
                ->map(fn($v) => $v !== null ? (int) $v : null)
                ->toArray(),
            'sat' => $datos->pluck('avg_sat')
                ->map(fn($v) => $v !== null ? (float) $v : null)
                ->toArray(),
            'gluc' => $datos->pluck('avg_gluc')
                ->map(fn($v) => $v !== null ? (int) $v : null)
                ->toArray(),
        ];
    }

    private function computeEstados(): array
    {
        $datos = DB::table('adulto_mayor as am')
            ->join('estado_adulto as ea', 'am.cod_est_adul', '=', 'ea.cod_est_adul')
            ->select('ea.estado', DB::raw('COUNT(*) as total'))
            ->groupBy('ea.estado')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $datos->pluck('estado')
                ->map(fn($estado) => str_replace('_', ' ', (string) $estado))
                ->toArray(),
            'values' => $datos->pluck('total')
                ->map(fn($valor) => (int) $valor)
                ->toArray(),
        ];
    }

    private function computeNotasTipo(): array
    {
        $tipos = [
            'EVOLUCION' => 'Evolución',
            'INGRESO' => 'Ingreso',
            'EGRESO' => 'Egreso',
            'INTERCONSULTA' => 'Interconsulta',
            'URGENCIA' => 'Urgencia',
            'PROCEDIMIENTO' => 'Procedimiento',
        ];

        $datos = NotaEvolucionMedica::query()
            ->where('estado', 'ACTIVO')
            ->select('tipo_nota', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_nota')
            ->get()
            ->keyBy('tipo_nota');

        $labels = [];
        $values = [];

        foreach ($tipos as $clave => $etiqueta) {
            $labels[] = $etiqueta;
            $values[] = (int) ($datos[$clave]->total ?? 0);
        }

        return compact('labels', 'values');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function estadosResidenteActivos(): array
    {
        return [
            'ACTIVO',
            'ADMITIDO',
            'ASIGNADO',
            'EN_SEGUIMIENTO_ACTIVO',
            'OBSERVADO',
            'SEGUIMIENTO_ESPECIAL',
        ];
    }

    private function estadosValoracionMedicaPendiente(): array
    {
        return [
            'PENDIENTE_VALORACION_MEDICA',
            'VALORACION_MEDICA',
            'DECISION_ADMISION',
        ];
    }

    /**
     * Devuelve un registro vigente (el más reciente) por residente.
     *
     * @return \Illuminate\Support\Collection<string, \App\Models\SignosVitalesAdulto>
     */
    private function ultimosSignosPorResidente(Collection $codigos): Collection
    {
        if ($codigos->isEmpty()) {
            return collect();
        }

        return SignosVitalesAdulto::query()
            ->with('adultoMayor:cod_am,nombres,ap_paterno,ap_materno')
            ->whereIn('cod_am', $codigos)
            ->where('estado', 'VIGENTE')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->orderByDesc('created_at')
            ->get()
            ->unique('cod_am')
            ->keyBy('cod_am');
    }

    private function normalizarNivelRiesgo(?string $nivel): string
    {
        $valor = Str::upper(Str::ascii(trim((string) $nivel)));

        if ($valor === '') {
            return 'SIN_CLASIFICAR';
        }

        if (str_contains($valor, 'CRIT') || str_contains($valor, 'ALTO')) {
            return 'ALTO';
        }

        if (str_contains($valor, 'MEDIO') || str_contains($valor, 'MODER')) {
            return 'MEDIO';
        }

        if (
            str_contains($valor, 'BAJO')
            || str_contains($valor, 'NORMAL')
            || str_contains($valor, 'SIN RIESGO')
        ) {
            return 'BAJO';
        }

        return 'SIN_CLASIFICAR';
    }

    private function formatearFecha($fecha): string
    {
        if (!$fecha) {
            return '—';
        }

        return $fecha instanceof Carbon
            ? $fecha->format('d/m/Y')
            : Carbon::parse($fecha)->format('d/m/Y');
    }

    private function fechaHoraSigno(SignosVitalesAdulto $signo): Carbon
    {
        $fecha = $signo->fecha instanceof Carbon
            ? $signo->fecha->copy()
            : Carbon::parse($signo->fecha);

        if ($signo->hora) {
            $hora = substr((string) $signo->hora, 0, 8);
            return Carbon::parse($fecha->format('Y-m-d') . ' ' . $hora);
        }

        return $fecha->startOfDay();
    }

    // =========================================================================
    // ACCIONES YA UTILIZADAS POR EL BLADE / MODALES EXISTENTES
    // =========================================================================

    public function iniciarValoracionMedica(string $cod_am): void
    {
        $this->dispatch('abrirValoracionMedica', $cod_am);
    }

    public function abrirDecisionAdmision(string $cod_am): void
    {
        $this->dispatch('abrirDecisionAdmision', $cod_am);
    }
}
