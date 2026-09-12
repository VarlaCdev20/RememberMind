<?php

namespace App\Livewire\Clinica;

use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AreaGeriatrica;
use App\Models\AtencionAdulto;
use App\Models\EvaluacionGeriatrica;
use App\Models\FichaMedicaAdulto;
use App\Models\MedicacionAdulto;
use App\Models\NotaEvolucionMedica;
use App\Models\SeguimientoDiario;
use App\Models\SignosVitalesAdulto;
use App\Models\ValoracionFuncionalAdulto;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FichaClinicaIntegradaPanel extends Component
{
    /**
     * Tabs admitidos por el componente.
     * Se mantienen temporalmente "notas" y "geriatrico" por compatibilidad
     * con el Blade actual mientras se migra a la nueva estructura.
     */
    private const TABS = [
        'resumen',
        'consultas',
        'notas',
        'antecedentes',
        'medicacion',
        'signos',
        'cognicion',
        'geriatrico',
        'funcional',
        'interconsultas',
        'alertas',
        'historial',
    ];

    /**
     * Estados institucionales en los que el expediente puede consultarse,
     * pero no debe recibir nuevas mutaciones clínicas desde esta ficha.
     */
    private const ESTADOS_SOLO_LECTURA = [
        'ARCHIVADO',
        'EGRESADO',
        'FALLECIDO',
        'INACTIVO',
        'INACTIVA',
        'NO_ADMITIDO',
        'RETIRADO',
        'DERIVADO',
        'TRASLADADO',
    ];

    /**
     * Estados de medicación encontrados en el proyecto para una orden vigente.
     */
    private const ESTADOS_MEDICACION_ACTIVA = [
        'ACTIVO',
        'ACTIVA',
        'VIGENTE',
    ];

    public AdultoMayor $adulto;
    public string $tab = 'resumen';

    /**
     * Estas propiedades solo sirven para representar el estado de la interfaz.
     * Nunca se confía en ellas para autorizar una mutación: cada método vuelve
     * a validar permisos y estado del residente en el servidor.
     */
    public bool $soloLectura = false;
    public ?string $motivoSoloLectura = null;
    public array $permisosUI = [];

    // Datos base.
    public $fichaMedica = null;
    public $ultimosSignos = null;
    public array $signosRecientes = [];
    public array $notasRecientes = [];
    public array $interconsultasRecientes = [];
    public array $medicacionActiva = [];
    public array $administracionesRecientes = [];
    public array $omisionesRecientes = [];
    public int $omisionesUltimos7Dias = 0;

    // Funcionalidad.
    public ?array $valoracionFuncional = null;
    public ?array $valoracionFuncionalAnterior = null;
    public array $valoracionesFuncionalesRecientes = [];

    // Cognición / geriatría.
    public $evalCognitiva = null;
    public $evalCognitivaAnterior = null;
    public $evalAfectiva = null;
    public array $evaluacionesGeriatricasRecientes = [];

    // Alertas / seguimiento / controles.
    public array $alertasActivas = [];
    public int $cntAlertasActivas = 0;
    public array $seguimientosSolicitaronMedico = [];
    public $proximaAtencion = null;
    public array $atencionesRecientes = [];

    // Resumen y cronología.
    public int $edadPaciente = 0;
    public string $grupoSanguineoTexto = '';
    public string $alergiasClinicas = '';
    public array $antecedentesClinicos = [];
    public array $resumenClinico = [];
    public array $historialClinico = [];

    protected $listeners = [
        'nota-evolucion-guardada'        => 'refreshData',
        'signos-guardados'               => 'refreshData',
        'signos-actualizados'            => 'refreshData', // compatibilidad
        'valoracion-barthel-guardada'    => 'refreshData',
        'evaluacion-geriatrica-guardada' => 'refreshData',
    ];

    public function mount(AdultoMayor $adulto): void
    {
        abort_unless(Auth::check(), 401);

        $this->adulto = $this->recargarAdulto($adulto->cod_am);

        $this->autorizarLectura();
        $this->actualizarModoLectura();
        $this->cargarDatos();
    }

    /**
     * Refresca todo el expediente revalidando usuario, permisos y existencia
     * del residente. No reutiliza datos potencialmente obsoletos del cliente.
     */
    public function refreshData(): void
    {
        abort_unless(Auth::check(), 401);

        $this->adulto = $this->recargarAdulto($this->adulto->cod_am);

        $this->autorizarLectura();
        $this->actualizarModoLectura();
        $this->cargarDatos();
    }

    /**
     * Cambia de pestaña únicamente si el nombre está permitido y el usuario
     * posee el permiso de lectura correspondiente.
     */
    public function setTab(string $tab): void
    {
        $tab = trim($tab);

        abort_unless(in_array($tab, self::TABS, true), 404);
        $this->autorizarTab($tab);

        $this->tab = $tab;
    }

    /**
     * Nueva consulta / evolución médica.
     */
    public function nuevaNota(): void
    {
        $this->autorizarMutacion('atenciones.crear');

        $this->dispatch(
            'abrir-nota-evolucion',
            cod_am: $this->adulto->cod_am
        );
    }

    /**
     * Registro independiente de signos vitales.
     *
     * IMPORTANTE: el modal y el servicio actuales pasan por
     * TurnoEnfermeriaService. Por ello se replica aquí esa validación para no
     * mostrar/ejecutar una acción que el flujo inferior va a rechazar.
     */
    public function nuevosSignos(): void
    {
        $this->autorizarMutacion('signos_vitales.crear');

        app(TurnoEnfermeriaService::class)
            ->autorizarAccionPaciente($this->adulto->cod_am, Auth::user());

        $this->dispatch(
            'abrir-signos-vitales-medico',
            cod_am: $this->adulto->cod_am
        );
    }

    /**
     * Valoración Barthel.
     *
     * El modal existente exige además valoracion_enfermeria.crear y el flujo
     * de asignación de Enfermería. Se valida exactamente igual aquí para evitar
     * un botón aparentemente habilitado que después falle al guardar.
     */
    public function nuevaBarthel(): void
    {
        $this->autorizarMutacion('valoracion_funcional.crear');

        $usuario = Auth::user();
        abort_unless(
            $usuario && $usuario->can('valoracion_enfermeria.crear'),
            403,
            'La valoración Barthel actual requiere autorización del flujo de Enfermería.'
        );

        app(TurnoEnfermeriaService::class)
            ->autorizarAccionPaciente($this->adulto->cod_am, $usuario);

        $this->dispatch(
            'abrir-valoracion-barthel',
            cod_am: $this->adulto->cod_am
        );
    }

    /**
     * Abre una evaluación geriátrica solo para un área real, activa y con
     * instrumentos activos disponibles.
     */
    public function nuevaEvaluacionGeriatrica(string $codArea): void
    {
        $this->autorizarMutacion('evaluaciones.crear');

        $codArea = strtoupper(trim($codArea));

        abort_unless(
            $codArea !== '' && strlen($codArea) <= 30,
            422,
            'El área de evaluación indicada no es válida.'
        );

        $areaValida = AreaGeriatrica::query()
            ->where('cod_area', $codArea)
            ->where('estado', 'ACTIVO')
            ->whereHas('instrumentos', function ($q) {
                $q->where('estado', 'ACTIVO');
            })
            ->exists();

        abort_unless(
            $areaValida,
            422,
            'El área seleccionada no existe, está inactiva o no tiene instrumentos disponibles.'
        );

        $this->dispatch('evaluacion-geriatrica-area-abrir', [
            'cod_am'   => $this->adulto->cod_am,
            'cod_area' => $codArea,
        ]);
    }

    /**
     * Carga el expediente completo usando únicamente modelos y campos que ya
     * existen en el proyecto.
     */
    private function cargarDatos(): void
    {
        $cod = $this->adulto->cod_am;

        // ──────────────────────────────────────────────────────────────
        // Ficha médica / antecedentes
        // ──────────────────────────────────────────────────────────────
        $this->fichaMedica = FichaMedicaAdulto::query()
            ->where('cod_am', $cod)
            ->where('estado', 'ACTIVO')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();

        // ──────────────────────────────────────────────────────────────
        // Signos vitales
        // ──────────────────────────────────────────────────────────────
        $signos = SignosVitalesAdulto::query()
            ->where('cod_am', $cod)
            ->where('estado', 'VIGENTE')
            ->with('registradoPor')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $this->ultimosSignos = $signos->first();
        $this->signosRecientes = $signos->values()->toArray();

        // ──────────────────────────────────────────────────────────────
        // Consultas / evoluciones / interconsultas
        // ──────────────────────────────────────────────────────────────
        $notas = NotaEvolucionMedica::query()
            ->where('cod_am', $cod)
            ->where('estado', 'ACTIVO')
            ->with('registrador')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        $this->notasRecientes = $notas
            ->take(20)
            ->values()
            ->toArray();

        $this->interconsultasRecientes = $notas
            ->where('tipo_nota', 'INTERCONSULTA')
            ->take(10)
            ->values()
            ->toArray();

        // ──────────────────────────────────────────────────────────────
        // Medicación vigente
        // ──────────────────────────────────────────────────────────────
        $medicaciones = MedicacionAdulto::query()
            ->where('cod_am', $cod)
            ->whereIn('estado', self::ESTADOS_MEDICACION_ACTIVA)
            ->with('registrador')
            ->orderBy('nombre_medicamento')
            ->get();

        $this->medicacionActiva = $medicaciones->values()->toArray();

        // ──────────────────────────────────────────────────────────────
        // Administración / omisiones de medicación
        // ──────────────────────────────────────────────────────────────
        $administraciones = AdministracionMedicacion::query()
            ->where('cod_am', $cod)
            ->whereDate('fecha', '>=', today()->subDays(30)->toDateString())
            ->with(['medicacion', 'registrador'])
            ->orderByDesc('fecha')
            ->orderByDesc('hora_real')
            ->orderByDesc('hora_programada')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $this->administracionesRecientes = $administraciones
            ->take(30)
            ->values()
            ->toArray();

        $omisiones = $administraciones->filter(function ($registro) {
            return $registro->administrado === false
                || strtoupper((string) $registro->resultado) === 'OMITIDO';
        });

        $this->omisionesRecientes = $omisiones
            ->take(20)
            ->values()
            ->toArray();

        $this->omisionesUltimos7Dias = AdministracionMedicacion::query()
            ->where('cod_am', $cod)
            ->whereDate('fecha', '>=', today()->subDays(6)->toDateString())
            ->where(function ($q) {
                $q->where('administrado', false)
                    ->orWhere('resultado', 'OMITIDO');
            })
            ->count();

        // ──────────────────────────────────────────────────────────────
        // Funcionalidad: vigente + anterior para comparación longitudinal
        // ──────────────────────────────────────────────────────────────
        $funcionales = ValoracionFuncionalAdulto::query()
            ->where('cod_am', $cod)
            ->whereIn('estado', ['VIGENTE', 'HISTORICA'])
            ->with('registradoPor')
            ->orderByDesc('fecha_valoracion')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $this->valoracionFuncional = $funcionales->get(0)?->toArray();
        $this->valoracionFuncionalAnterior = $funcionales->get(1)?->toArray();
        $this->valoracionesFuncionalesRecientes = $funcionales
            ->values()
            ->toArray();

        // ──────────────────────────────────────────────────────────────
        // Cognición: exclusivamente ARE_COG
        // ──────────────────────────────────────────────────────────────
        $cognitivas = EvaluacionGeriatrica::query()
            ->where('cod_am', $cod)
            ->whereHas('instrumento', function ($q) {
                $q->where('cod_area', 'ARE_COG');
            })
            ->with(['instrumento', 'evaluador'])
            ->orderByDesc('fecha_eval')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $this->evalCognitiva = $cognitivas->get(0);
        $this->evalCognitivaAnterior = $cognitivas->get(1);

        // Afectiva: exclusivamente ARE_AFE.
        $this->evalAfectiva = EvaluacionGeriatrica::query()
            ->where('cod_am', $cod)
            ->whereHas('instrumento', function ($q) {
                $q->where('cod_area', 'ARE_AFE');
            })
            ->with(['instrumento', 'evaluador'])
            ->orderByDesc('fecha_eval')
            ->orderByDesc('created_at')
            ->first();

        // Historial geriátrico integral real, sin inventar áreas.
        $evaluacionesGeriatricas = EvaluacionGeriatrica::query()
            ->where('cod_am', $cod)
            ->with(['instrumento.area', 'evaluador'])
            ->orderByDesc('fecha_eval')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        $this->evaluacionesGeriatricasRecientes = $evaluacionesGeriatricas
            ->values()
            ->toArray();

        // ──────────────────────────────────────────────────────────────
        // Alertas formales
        // ──────────────────────────────────────────────────────────────
        $alertas = AlertaAdulto::query()
            ->where('cod_am', $cod)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->with(['responsable', 'acciones'])
            ->orderByDesc('created_at')
            ->get();

        $this->alertasActivas = $alertas->values()->toArray();
        $this->cntAlertasActivas = $alertas->count();

        // ──────────────────────────────────────────────────────────────
        // Señales desde Enfermería que solicitaron revisión médica.
        // No se llaman "pendientes" porque el modelo no tiene un campo que
        // pruebe si ya fueron resueltas por Medicina.
        // ──────────────────────────────────────────────────────────────
        $seguimientos = SeguimientoDiario::query()
            ->where('cod_am', $cod)
            ->where('requiere_medico', true)
            ->with('registradoPor')
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $this->seguimientosSolicitaronMedico = $seguimientos
            ->values()
            ->toArray();

        // ──────────────────────────────────────────────────────────────
        // Próxima atención registrada y atenciones recientes.
        // No se fuerza el término "control" porque AtencionAdulto puede
        // contener distintos tipos de atención.
        // ──────────────────────────────────────────────────────────────
        $this->proximaAtencion = AtencionAdulto::query()
            ->where('cod_am', $cod)
            ->whereDate('fecha', '>=', today()->toDateString())
            ->with('tipoAtencion')
            ->orderBy('fecha')
            ->orderBy('hora')
            ->first();

        $atenciones = AtencionAdulto::query()
            ->where('cod_am', $cod)
            ->whereDate('fecha', '<=', today()->toDateString())
            ->with('tipoAtencion')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $this->atencionesRecientes = $atenciones
            ->values()
            ->toArray();

        // ──────────────────────────────────────────────────────────────
        // Datos derivados seguros para la vista.
        // ──────────────────────────────────────────────────────────────
        $this->edadPaciente = $this->adulto->edad ?? 0;
        $this->grupoSanguineoTexto = $this->resolverGrupoSanguineo();
        $this->alergiasClinicas = $this->resolverAlergias();
        $this->antecedentesClinicos = $this->resolverAntecedentesClinicos();

        $this->resumenClinico = [
            'medicamentos_activos'         => count($this->medicacionActiva),
            'alertas_activas'              => $this->cntAlertasActivas,
            'omisiones_7_dias'             => $this->omisionesUltimos7Dias,
            'barthel_actual'                => $this->valoracionFuncional['indice_barthel'] ?? null,
            'barthel_anterior'              => $this->valoracionFuncionalAnterior['indice_barthel'] ?? null,
            'dependencia_actual'            => $this->valoracionFuncional['nivel_dependencia'] ?? null,
            'riesgo_caida_actual'           => $this->valoracionFuncional['riesgo_caida'] ?? null,
            'riesgo_cognitivo_actual'       => $this->evalCognitiva?->nivel_riesgo,
            'puntaje_cognitivo_actual'      => $this->evalCognitiva?->puntaje_total,
            'puntaje_cognitivo_anterior'    => $this->evalCognitivaAnterior?->puntaje_total,
            'instrumento_cognitivo_actual'  => $this->evalCognitiva?->instrumento?->nombre,
            'ultima_nota_fecha'             => $notas->first()?->fecha?->format('Y-m-d'),
            'ultima_nota_tipo'              => $notas->first()?->tipo_nota,
            'ultima_medicion_signos_fecha'  => $this->ultimosSignos?->fecha?->format('Y-m-d'),
            'proxima_atencion_fecha'        => $this->proximaAtencion?->fecha?->format('Y-m-d'),
            'seguimientos_requiere_medico'  => count($this->seguimientosSolicitaronMedico),
        ];

        $this->historialClinico = $this->construirHistorialClinico(
            $notas,
            $signos,
            $administraciones,
            $evaluacionesGeriatricas,
            $funcionales,
            $alertas,
            $seguimientos,
            $atenciones,
        );

        $this->cargarPermisosUI();
    }

    /**
     * Lectura de expediente: defensa adicional a la protección de la ruta.
     */
    private function autorizarLectura(): void
    {
        $usuario = Auth::user();

        abort_unless($usuario, 401);
        abort_unless(
            $usuario->can('valoracion_medica.ver')
                && $usuario->can('adultos.ver_expediente')
                && $usuario->can('salud.ver'),
            403,
            'No cuenta con permisos para consultar el expediente clínico.'
        );
    }

    /**
     * Cada mutación vuelve a consultar al residente y a verificar permiso,
     * archivo y estado institucional. No confía en propiedades públicas.
     */
    private function autorizarMutacion(string $permiso): void
    {
        abort_unless(Auth::check(), 401);

        $this->adulto = $this->recargarAdulto($this->adulto->cod_am);
        $this->autorizarLectura();
        $this->actualizarModoLectura();

        abort_unless(
            Auth::user()?->can($permiso),
            403,
            'No cuenta con el permiso requerido para esta acción.'
        );

        abort_if(
            $this->soloLectura,
            409,
            $this->motivoSoloLectura ?? 'El expediente se encuentra en modo de solo lectura.'
        );
    }

    private function autorizarTab(string $tab): void
    {
        $usuario = Auth::user();
        abort_unless($usuario, 401);

        $permiso = match ($tab) {
            'consultas', 'notas', 'interconsultas' => 'atenciones.ver',
            'medicacion'                            => 'medicacion.ver',
            'signos'                                => 'signos_vitales.ver',
            'cognicion', 'geriatrico'              => 'evaluaciones.ver',
            'alertas'                               => 'alertas.ver',
            'resumen', 'antecedentes', 'funcional' => 'salud.ver',
            'historial'                             => 'adultos.ver_expediente',
            default                                 => null,
        };

        abort_unless(
            $permiso !== null && $usuario->can($permiso),
            403,
            'No cuenta con permiso para consultar esta sección.'
        );
    }

    /**
     * Indicadores para habilitar/deshabilitar controles del Blade.
     * Las acciones siguen protegidas en servidor aunque estos valores fueran
     * alterados en el navegador.
     */
    private function cargarPermisosUI(): void
    {
        $usuario = Auth::user();

        if (!$usuario) {
            $this->permisosUI = [];
            return;
        }

        $turnos = app(TurnoEnfermeriaService::class);
        $puedePasarAsignacionActual = $turnos->esPacienteAsignado(
            $this->adulto->cod_am,
            $usuario
        );

        $this->permisosUI = [
            'ver_resumen'         => $usuario->can('salud.ver'),
            'ver_consultas'       => $usuario->can('atenciones.ver'),
            'crear_consulta'      => !$this->soloLectura && $usuario->can('atenciones.crear'),
            'ver_antecedentes'    => $usuario->can('salud.ver'),
            'ver_medicacion'      => $usuario->can('medicacion.ver'),
            'crear_medicacion'    => !$this->soloLectura && $usuario->can('medicacion.crear'),
            'editar_medicacion'   => !$this->soloLectura && $usuario->can('medicacion.editar'),
            'suspender_medicacion'=> !$this->soloLectura && $usuario->can('medicacion.suspender'),
            'ver_signos'          => $usuario->can('signos_vitales.ver'),

            // La disponibilidad de estos dos botones refleja también las
            // restricciones reales de los modales/servicios existentes.
            'registrar_signos'    => !$this->soloLectura
                && $usuario->can('signos_vitales.crear')
                && $puedePasarAsignacionActual,

            'ver_funcional'       => $usuario->can('salud.ver'),
            'crear_barthel'       => !$this->soloLectura
                && $usuario->can('valoracion_funcional.crear')
                && $usuario->can('valoracion_enfermeria.crear')
                && $puedePasarAsignacionActual,

            'ver_evaluaciones'    => $usuario->can('evaluaciones.ver'),
            'crear_evaluacion'    => !$this->soloLectura && $usuario->can('evaluaciones.crear'),
            'ver_interconsultas'  => $usuario->can('atenciones.ver'),
            'ver_alertas'         => $usuario->can('alertas.ver'),
            'atender_alertas'     => !$this->soloLectura && $usuario->can('alertas.atender'),
            'cerrar_alertas'      => !$this->soloLectura && $usuario->can('alertas.cerrar'),
            'ver_historial'       => $usuario->can('adultos.ver_expediente'),
        ];
    }

    private function actualizarModoLectura(): void
    {
        $estado = strtoupper((string) ($this->adulto->estado?->estado ?? ''));

        if ($this->adulto->archivado_en !== null) {
            $this->soloLectura = true;
            $this->motivoSoloLectura = 'El expediente está archivado y solo puede consultarse.';
            return;
        }

        if (in_array($estado, self::ESTADOS_SOLO_LECTURA, true)) {
            $this->soloLectura = true;
            $this->motivoSoloLectura = "El residente se encuentra en estado {$this->adulto->estado_humano}; el expediente es de solo lectura.";
            return;
        }

        $this->soloLectura = false;
        $this->motivoSoloLectura = null;
    }

    private function recargarAdulto(string $codAm): AdultoMayor
    {
        return AdultoMayor::query()
            ->with(['estado', 'habitacion', 'cama'])
            ->where('cod_am', $codAm)
            ->firstOrFail();
    }

    private function resolverGrupoSanguineo(): string
    {
        $grupo = trim((string) ($this->adulto->grupo_sanguineo ?? ''));
        $factor = trim((string) ($this->adulto->factor_rh ?? ''));

        return trim($grupo . ($factor !== '' ? ' ' . $factor : ''));
    }

    private function resolverAlergias(): string
    {
        $ficha = trim((string) ($this->fichaMedica?->alergias ?? ''));
        $institucional = trim((string) ($this->adulto->alergias ?? ''));

        return $ficha !== '' ? $ficha : $institucional;
    }

    /**
     * FichaMedicaAdulto no posee diagnóstico_principal ni
     * diagnósticos_secundarios. El resumen de antecedentes se construye con
     * los campos booleanos que realmente existen en ese modelo.
     */
    private function resolverAntecedentesClinicos(): array
    {
        if (!$this->fichaMedica) {
            return [];
        }

        $mapa = [
            'hipertension'            => 'Hipertensión arterial',
            'diabetes'                => 'Diabetes',
            'problemas_cardiacos'     => 'Problemas cardíacos',
            'acv'                     => 'Antecedente de ACV',
            'parkinson'               => 'Parkinson',
            'epilepsia'               => 'Epilepsia',
            'alzheimer_diagnosticado' => 'Alzheimer diagnosticado',
            'depresion'               => 'Depresión',
            'ansiedad'                => 'Ansiedad',
            'problemas_sueno'         => 'Problemas de sueño',
            'problemas_visuales'      => 'Problemas visuales',
            'problemas_auditivos'     => 'Problemas auditivos',
            'dolor_cronico'           => 'Dolor crónico',
        ];

        $activos = [];
        foreach ($mapa as $campo => $etiqueta) {
            if ((bool) $this->fichaMedica->{$campo}) {
                $activos[] = [
                    'campo'    => $campo,
                    'etiqueta' => $etiqueta,
                ];
            }
        }

        return $activos;
    }

    /**
     * Construye una línea de tiempo integrada solo con registros existentes.
     * No crea diagnósticos, alertas ni estados derivados.
     */
    private function construirHistorialClinico(
        $notas,
        $signos,
        $administraciones,
        $evaluaciones,
        $funcionales,
        $alertas,
        $seguimientos,
        $atenciones,
    ): array {
        $eventos = collect();

        foreach ($notas->take(15) as $nota) {
            $fechaHora = $this->resolverFechaHora($nota->fecha, $nota->hora);
            if (!$fechaHora) {
                continue;
            }

            $eventos->push([
                'orden'       => $fechaHora->getTimestamp(),
                'fecha_hora'  => $fechaHora->toDateTimeString(),
                'tipo'        => 'NOTA_MEDICA',
                'titulo'      => str_replace('_', ' ', (string) ($nota->tipo_nota ?: 'EVOLUCION')),
                'detalle'     => $nota->valoracion,
                'responsable' => $nota->registrador?->name,
            ]);
        }

        foreach ($signos->take(15) as $signo) {
            $fechaHora = $this->resolverFechaHora($signo->fecha, $signo->hora);
            if (!$fechaHora) {
                continue;
            }

            $eventos->push([
                'orden'       => $fechaHora->getTimestamp(),
                'fecha_hora'  => $fechaHora->toDateTimeString(),
                'tipo'        => 'SIGNOS_VITALES',
                'titulo'      => 'Signos vitales',
                'detalle'     => $signo->presion_formateada,
                'responsable' => $signo->registradoPor?->name,
            ]);
        }

        foreach ($administraciones->take(25) as $admin) {
            $hora = $admin->hora_real ?: $admin->hora_programada;
            $fechaHora = $this->resolverFechaHora($admin->fecha, $hora);
            if (!$fechaHora) {
                continue;
            }

            $resultado = $admin->resultado
                ?: ($admin->administrado ? 'ADMINISTRADO' : 'OMITIDO');

            $eventos->push([
                'orden'       => $fechaHora->getTimestamp(),
                'fecha_hora'  => $fechaHora->toDateTimeString(),
                'tipo'        => 'MEDICACION',
                'titulo'      => $admin->medicacion?->nombre_medicamento ?? 'Administración de medicación',
                'detalle'     => $resultado,
                'responsable' => $admin->registrador?->name,
            ]);
        }

        foreach ($evaluaciones->take(15) as $eval) {
            $fechaHora = $this->resolverFechaHora($eval->fecha_eval);
            if (!$fechaHora) {
                continue;
            }

            $eventos->push([
                'orden'       => $fechaHora->getTimestamp(),
                'fecha_hora'  => $fechaHora->toDateTimeString(),
                'tipo'        => 'EVALUACION_GERIATRICA',
                'titulo'      => $eval->instrumento?->nombre ?? 'Evaluación geriátrica',
                'detalle'     => $eval->resultado_cualitativo ?? $eval->nivel_riesgo,
                'responsable' => $eval->evaluador?->name,
            ]);
        }

        foreach ($funcionales->take(10) as $funcional) {
            $fechaHora = $this->resolverFechaHora($funcional->fecha_valoracion);
            if (!$fechaHora) {
                continue;
            }

            $eventos->push([
                'orden'       => $fechaHora->getTimestamp(),
                'fecha_hora'  => $fechaHora->toDateTimeString(),
                'tipo'        => 'VALORACION_FUNCIONAL',
                'titulo'      => 'Valoración funcional',
                'detalle'     => $funcional->nivel_dependencia,
                'responsable' => $funcional->registradoPor?->name,
            ]);
        }

        foreach ($alertas->take(15) as $alerta) {
            $fechaHora = $this->resolverFechaHora($alerta->created_at);
            if (!$fechaHora) {
                continue;
            }

            $eventos->push([
                'orden'       => $fechaHora->getTimestamp(),
                'fecha_hora'  => $fechaHora->toDateTimeString(),
                'tipo'        => 'ALERTA',
                'titulo'      => (string) ($alerta->tipo_alerta ?: 'Alerta clínica'),
                'detalle'     => $alerta->motivo,
                'responsable' => $alerta->responsable?->name,
            ]);
        }

        foreach ($seguimientos->take(10) as $seguimiento) {
            $fechaHora = $this->resolverFechaHora($seguimiento->fecha, $seguimiento->hora_inicio);
            if (!$fechaHora) {
                continue;
            }

            $eventos->push([
                'orden'       => $fechaHora->getTimestamp(),
                'fecha_hora'  => $fechaHora->toDateTimeString(),
                'tipo'        => 'SEGUIMIENTO_ENFERMERIA',
                'titulo'      => 'Seguimiento que solicitó revisión médica',
                'detalle'     => $seguimiento->observacion,
                'responsable' => $seguimiento->registradoPor?->name,
            ]);
        }

        foreach ($atenciones->take(15) as $atencion) {
            $fechaHora = $this->resolverFechaHora($atencion->fecha, $atencion->hora);
            if (!$fechaHora) {
                continue;
            }

            $eventos->push([
                'orden'       => $fechaHora->getTimestamp(),
                'fecha_hora'  => $fechaHora->toDateTimeString(),
                'tipo'        => 'ATENCION',
                'titulo'      => $atencion->tipoAtencion?->nombre ?? 'Atención',
                'detalle'     => $atencion->observacion,
                'responsable' => null,
            ]);
        }

        return $eventos
            ->sortByDesc('orden')
            ->take(60)
            ->values()
            ->map(function (array $evento) {
                unset($evento['orden']);
                return $evento;
            })
            ->all();
    }

    private function resolverFechaHora($fecha, $hora = null): ?Carbon
    {
        if (!$fecha) {
            return null;
        }

        try {
            $base = $fecha instanceof Carbon
                ? $fecha->copy()
                : Carbon::parse($fecha);

            if ($hora) {
                if ($hora instanceof Carbon) {
                    $horaTexto = $hora->format('H:i:s');
                } else {
                    $horaTexto = trim((string) $hora);
                    $horaTexto = strlen($horaTexto) >= 8
                        ? substr($horaTexto, 0, 8)
                        : substr($horaTexto, 0, 5);
                }

                if ($horaTexto !== '') {
                    $formato = strlen($horaTexto) === 5 ? 'H:i' : 'H:i:s';
                    $horaCarbon = Carbon::createFromFormat($formato, $horaTexto);
                    $base->setTime(
                        $horaCarbon->hour,
                        $horaCarbon->minute,
                        $horaCarbon->second
                    );
                }
            } else {
                $base->startOfDay();
            }

            return $base;
        } catch (\Throwable) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.clinica.ficha-clinica-integrada-panel')
            ->layout('layouts.sistema');
    }
}
