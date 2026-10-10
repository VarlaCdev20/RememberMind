<?php

namespace App\Modulos\Clinica\Presentacion\Livewire\Paginas;

use App\Compartido\Infraestructura\Documentos\DocumentacionResidenteService;
use App\Models\Alerta;
use App\Models\Documento;
use App\Models\EjecucionCuidado;
use App\Models\Incidente;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Modulos\Alertas\Aplicacion\Acciones\AlertasService;
use App\Modulos\Alertas\Aplicacion\Acciones\RegistrarIncidenteEnFicha;
use App\Modulos\Clinica\Aplicacion\Acciones\SignosVitalesService;
use App\Modulos\Clinica\Aplicacion\Consultas\EventosClinicosService;
use App\Modulos\Clinica\Aplicacion\Consultas\ResultadosEstudiosService;
use App\Modulos\Clinica\Aplicacion\Consultas\ResumenLongitudinalResidente;
use App\Modulos\Cuidados\Aplicacion\Acciones\GestionarCuidadosEnFicha;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Medicacion\Aplicacion\Acciones\RegistrarAdministracionMedicacionService;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use App\Modulos\Residentes\Aplicacion\Acciones\IncorporarDocumentosExpediente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class FichaPaciente extends Component
{
    protected $listeners = [
        'medicacion-guardada' => 'refrescarFicha',
        'receta-creada' => 'refrescarFicha',
        'nota-guardada' => 'refrescarFicha',
    ];

    public function refrescarFicha(): void
    {
        if ($this->adultoMayor) {
            $this->cargarAdulto($this->adultoMayor->cod_residente);
        }
    }

    use WithFileUploads;

    public Residente $adultoMayor;

    public string $tabActivo = 'resumen';

    // Modales de Acción Rápida
    // Filtros y Métricas de Historial Clínico
    public string $historialFiltroTipo = 'TODOS';

    public ?string $historialFechaDesde = null;

    public ?string $historialFechaHasta = null;

    public string $metricaSignosSeleccionada = 'PA';

    public string $periodoSignos = '7d';

    public function setHistorialFiltro(string $tipo): void
    {
        $this->historialFiltroTipo = $tipo;
    }

    public function setMetricaSignos(string $metrica): void
    {
        $m = strtoupper(trim($metrica));
        if ($m === 'TEMPERATURA') {
            $m = 'TEMP';
        }
        if ($m === 'GLUCEMIA') {
            $m = 'GLUCOSA';
        }
        if ($m === 'SATURACION') {
            $m = 'SPO2';
        }
        if ($m === 'RESPIRACION' || $m === 'FRECUENCIA_RESPIRATORIA') {
            $m = 'FR';
        }
        if ($m === 'DOLOR_EVA') {
            $m = 'DOLOR';
        }
        $this->metricaSignosSeleccionada = $m;
    }

    public function setPeriodoSignos(string $periodo): void
    {
        $validos = ['24h', '7d', '30d', '3m', 'personalizado'];
        $p = strtolower(trim($periodo));
        $this->periodoSignos = in_array($p, $validos) ? $p : '7d';
    }

    public function limpiarFiltrosHistorial(): void
    {
        $this->historialFiltroTipo = 'TODOS';
        $this->historialFechaDesde = null;
        $this->historialFechaHasta = null;
    }

    // =========================================================================
    // RESULTADOS Y ESTUDIOS CLÍNICOS
    // =========================================================================
    public string $subtabEstudio = 'TODOS';

    public string $filtroBusquedaEstudio = '';

    public string $filtroPeriodoEstudio = '6m';

    public string $parametroGraficoEstudio = 'glucosa';

    public ?string $estudioSeleccionadoId = null;

    public function setSubtabEstudio(string $subtab): void
    {
        $validos = ['TODOS', 'LABORATORIO', 'IMAGEN', 'CARDIOLOGICO', 'OTROS'];
        $s = strtoupper(trim($subtab));
        $this->subtabEstudio = in_array($s, $validos) ? $s : 'TODOS';
        $this->estudioSeleccionadoId = null;
    }

    public function setFiltroPeriodoEstudio(string $periodo): void
    {
        $validos = ['30d', '3m', '6m', '1a', 'todos'];
        $p = strtolower(trim($periodo));
        $this->filtroPeriodoEstudio = in_array($p, $validos) ? $p : '6m';
    }

    public function setParametroGraficoEstudio(string $parametro): void
    {
        $validos = ['glucosa', 'hemoglobina', 'creatinina', 'sodio', 'potasio'];
        $p = strtolower(trim($parametro));
        $this->parametroGraficoEstudio = in_array($p, $validos) ? $p : 'glucosa';
    }

    public function seleccionarEstudio(string $id): void
    {
        $this->estudioSeleccionadoId = $id;
    }

    // =========================================================================
    // MÓDULO DOCUMENTACIÓN — GOLDEN REFERENCE
    // =========================================================================
    public string $filtroBusquedaDoc = '';

    public string $filtroTipoDoc = 'TODOS';

    public string $filtroCategoriaDoc = 'TODOS';

    public string $ordenDoc = 'recientes';

    public ?string $documentoSeleccionadoId = null;

    public string $tabDetalleDoc = 'preview'; // preview, informacion, historial

    public int $paginaDoc = 1;

    public int $porPaginaDoc = 8;

    public int $zoomDoc = 100;

    // Modal Subir Documento
    public bool $modalSubirDoc = false;

    public $nuevoDocArchivo = null;

    public string $nuevoDocNombre = '';

    public string $nuevoDocTipo = 'MEDICO';

    public string $nuevoDocCategoria = 'CLINICO';

    public string $nuevoDocFecha = '';

    public string $nuevoDocDescripcion = '';

    public string $nuevoDocObservaciones = '';

    public function seleccionarDocumento(string $id): void
    {
        $this->documentoSeleccionadoId = $id;
    }

    public function setTabDetalleDoc(string $tab): void
    {
        $validos = ['preview', 'informacion', 'historial'];
        $this->tabDetalleDoc = in_array($tab, $validos) ? $tab : 'preview';
    }

    public function cambiarPaginaDoc(int $pagina): void
    {
        if ($pagina >= 1) {
            $this->paginaDoc = $pagina;
        }
    }

    public function ajustarZoomDoc(int $delta): void
    {
        $nuevo = $this->zoomDoc + $delta;
        if ($nuevo >= 50 && $nuevo <= 200) {
            $this->zoomDoc = $nuevo;
        }
    }

    public function abrirModalSubirDoc(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->nuevoDocArchivo = null;
        $this->nuevoDocNombre = '';
        $this->nuevoDocTipo = 'MEDICO';
        $this->nuevoDocCategoria = 'CLINICO';
        $this->nuevoDocFecha = today()->format('Y-m-d');
        $this->nuevoDocDescripcion = '';
        $this->nuevoDocObservaciones = '';
        $this->modalSubirDoc = true;
    }

    public function cerrarModalSubirDoc(): void
    {
        $this->modalSubirDoc = false;
        $this->nuevoDocArchivo = null;
        $this->resetValidation();
    }

    public function guardarNuevoDocumento(): void
    {
        $this->resetValidation();
        $doc = app(IncorporarDocumentosExpediente::class)->guardarNuevoDocumento(auth()->user(), [
            'nuevoDocArchivo' => $this->nuevoDocArchivo,
            'adultoMayor' => $this->adultoMayor,
            'nuevoDocNombre' => $this->nuevoDocNombre,
            'nuevoDocTipo' => $this->nuevoDocTipo,
            'nuevoDocFecha' => $this->nuevoDocFecha,
            'nuevoDocObservaciones' => $this->nuevoDocObservaciones,
            'nuevoDocDescripcion' => $this->nuevoDocDescripcion,
            'nuevoDocCategoria' => $this->nuevoDocCategoria,
        ]);

        $this->documentoSeleccionadoId = 'doc_bd_'.$doc->cod_documento;
        $this->cerrarModalSubirDoc();
        session()->flash('success', "Documento '{$doc->nombre}' registrado correctamente.");
    }

    public bool $modalSignos = false;

    public ?string $signoPA = null;

    public ?string $signoFC = null;

    public ?string $signoFR = null;

    public ?string $signoTemp = null;

    public ?string $signoSat = null;

    public ?string $signoGlucosa = null;

    public ?string $signoDolor = null;

    public ?string $signoObs = null;

    public string $signoPosicion = '';

    public bool $signoUsaOxigeno = false;

    public bool $signoConfirmarAtipico = false;

    public bool $modalMed = false;

    public ?string $medSeleccionadoId = null;

    public ?string $medHoraProgramada = null;

    public string $medNombre = '';

    public string $medDosis = '';

    public string $medVia = '';

    public string $medAccion = 'ADMINISTRAR';

    public bool $medAdministrado = true;

    public string $medMotivoOmision = '';

    public string $medEfectoObs = '';

    public bool $medEsPrn = false;

    public string $medCondicionPrn = '';

    public string $medMotivoPrn = '';

    public string $medValoracionPrevia = '';

    public ?int $medIntensidadPrevia = null;

    public bool $modalTarea = false;

    public ?string $tareaAccionId = null;

    public string $tareaTitulo = '';

    public string $tareaEstadoAccion = 'REALIZADA';

    public string $tareaResultado = '';

    public string $tareaMotivoOmision = '';

    public bool $modalSeguimiento = false;

    public string $segEstado = '';

    public string $segAlimentacion = '';

    public string $segMovilidad = '';

    public string $segSueno = '';

    public bool $segIncidente = false;

    public bool $segRequiereMedico = false;

    public string $segObs = '';

    public bool $modalIncidente = false;

    public string $incidenteTipo = 'INCIDENTE';

    public string $incidenteNivel = 'ALTO';

    public string $incidenteMotivo = '';

    public bool $modalAtenderAlerta = false;

    public ?string $alertaAccionId = null;

    public string $accionTomadaAlerta = '';

    public bool $modalCerrarAlerta = false;

    public string $observacionCierreAlerta = '';

    // =========================================================================
    // MÓDULO: EVENTOS CLÍNICOS (GOLDEN REFERENCE)
    // =========================================================================
    public string $eventoSeleccionadoId = '';

    public string $filtroTipoEvento = 'TODOS';

    public string $filtroBusquedaEvento = '';

    public string $filtroPeriodoEvento = '6m';

    public string $tabDetalleEvento = 'resumen';

    public bool $modalRegistrarEvento = false;

    public string $nuevoEventoTipo = 'CAIDA';

    public string $nuevoEventoFechaHora = '';

    public string $nuevoEventoLugar = '';

    public string $nuevoEventoSeveridad = 'MODERADA';

    public string $nuevoEventoDescripcion = '';

    public int $nuevoEventoDolor = 0;

    public bool $nuevoEventoLesion = false;

    public bool $nuevoEventoPresenciado = false;

    public string $nuevoEventoTestigo = '';

    public string $nuevoEventoMovilidad = '';

    public bool $nuevoEventoMedicoInformado = false;

    public bool $nuevoEventoFamiliarInformado = false;

    public bool $nuevoEventoRequiereSeguimiento = true;

    public ?string $nuevoEventoFechaSeguimiento = null;

    public function mount(string|Residente $adulto)
    {
        $codRes = $adulto instanceof Residente ? $adulto->cod_residente : (string) $adulto;

        $this->cargarAdulto($codRes);

        // Administración y Gerencia consultan el expediente por sus permisos
        // institucionales; las mutaciones clínicas conservan sus propios guards.
        $user = Auth::user();
        if (! $user?->hasAnyRole(['ADMINISTRADOR', 'GERENTE']) || ! $user->can('residentes.ver')) {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, $user);
        }

        if (request()->query('tab')) {
            $this->tabActivo = request()->query('tab');
        }
    }

    public function cargarAdulto(string $codResidente): void
    {
        $this->adultoMayor = Residente::with([
            'ocupacionActiva.cama.habitacion',
            'admisiones',
            'familiares',
            'alergias' => fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO'])->orderByDesc('fecha_hora'),
            'diagnosticos' => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])->orderByDesc('fecha_hora'),
            'antecedentesClinicos' => fn ($q) => $q->whereNotIn('estado', ['ANULADO', 'INACTIVO'])->orderByDesc('fecha_referencia'),
            'seguros' => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'ACTIVA']),
            'asignacionesJornada.jornada.turno',
            'asignacionesJornada.personal.usuario',
            'atenciones',
            'pasesTurno.personalSaliente.usuario',
            'notasClinicas' => fn ($q) => $q->whereNotIn('estado', ['ANULADA', 'ANULADO', 'INACTIVA', 'INACTIVO'])->orderByDesc('fecha_hora')->take(10),
            'actividades' => fn ($q) => $q->orderBy('fecha_hora')->take(20),
            'signosVitales' => fn ($q) => $q->orderByDesc('fecha_hora')->take(60),
            'ejecucionesCuidado' => fn ($q) => $q->with('intervencion')->orderBy('fecha_hora_programada')->take(30),
            'registrosIngesta' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
            'registrosHidratacion' => fn ($q) => $q->orderByDesc('fecha_hora')->take(30),
            'registrosMovilidad' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
            'controlesCognitivos' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
            'valoracionesDolor' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
            'medicaciones' => fn ($q) => $q->with(['medicamento', 'horarios'])->where('estado', 'ACTIVA'),
            'administracionesMedicacion' => fn ($q) => $q->with('medicacion.medicamento')->orderByDesc('fecha_hora_programada')->take(30),
            'alertas' => fn ($q) => $q->with('eventos.usuario')->orderByDesc('fecha_hora')->take(25),
            'evaluacionesGeriatricas' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
            'valoracionesFuncionales' => fn ($q) => $q->with('registradoPor')->orderByDesc('fecha_hora')->take(10),
        ])->findOrFail($codResidente);
    }

    public function cambiarTab(string $tab): void
    {
        if ($tab === 'cuidados') {
            $tab = 'cuidado';
        }
        if ($tab === 'medicaciones') {
            $tab = 'medicacion';
        }
        if ($tab === 'estudios' || $tab === 'estudio' || $tab === 'resultados') {
            $tab = 'estudios';
        }
        if ($tab === 'documentos' || $tab === 'documento' || $tab === 'documentacion') {
            $tab = 'documentos';
        }
        $this->tabActivo = $tab;
    }

    public function abrirModalSignos(): void
    {
        $this->abrirRegistrarSignos();
    }

    public function cerrarModalSignos(): void
    {
        $this->modalSignos = false;
        $this->resetValidation();
    }

    public function abrirModalMedicacion(?string $codMed = null, ?string $horaProgramada = null): void
    {
        $this->abrirAdministrarMed($codMed, $horaProgramada);
    }

    public function cerrarModalMedicacion(): void
    {
        $this->modalMed = false;
        $this->resetValidation();
    }

    public function guardarMedicacion(): void
    {
        $this->confirmarAdministracionMed();
    }

    public function abrirModalTarea(string $codTarea): void
    {
        $this->abrirEjecutarTarea($codTarea);
    }

    public function cerrarModalTarea(): void
    {
        $this->modalTarea = false;
        $this->resetValidation();
    }

    public function guardarTarea(): void
    {
        $this->guardarAccionTarea();
    }

    public function abrirModalSeguimiento(): void
    {
        $this->abrirRegistrarSeguimiento();
    }

    public function cerrarModalSeguimiento(): void
    {
        $this->modalSeguimiento = false;
        $this->resetValidation();
    }

    public function abrirModalIncidente(): void
    {
        $this->abrirReportarIncidente();
    }

    public function cerrarModalIncidente(): void
    {
        $this->modalIncidente = false;
        $this->resetValidation();
    }

    public function abrirModalAtenderAlerta(string $codAlerta): void
    {
        $this->abrirAtenderAlerta($codAlerta);
    }

    public function cerrarModalAtenderAlerta(): void
    {
        $this->modalAtenderAlerta = false;
        $this->resetValidation();
    }

    public function guardarAtenderAlerta(): void
    {
        $this->confirmarAtencionAlerta();
    }

    public function abrirModalCerrarAlerta(string $codAlerta): void
    {
        $this->abrirCerrarAlerta($codAlerta);
    }

    public function cerrarModalCerrarAlerta(): void
    {
        $this->modalCerrarAlerta = false;
        $this->resetValidation();
    }

    public function guardarCerrarAlerta(): void
    {
        $this->confirmarCierreAlerta();
    }

    // =========================================================================
    // MÉTODOS Y ACCIONES DE EVENTOS CLÍNICOS
    // =========================================================================
    public function seleccionarEvento(string $id): void
    {
        $this->eventoSeleccionadoId = $id;
        $this->dispatch('evento-seleccionado', id: $id);
    }

    public function setFiltroTipoEvento(string $tipo): void
    {
        $this->filtroTipoEvento = strtoupper(trim($tipo));
        $this->dispatch('render-graficos-eventos');
    }

    public function setFiltroPeriodoEvento(string $periodo): void
    {
        $this->filtroPeriodoEvento = $periodo;
        $this->dispatch('render-graficos-eventos');
    }

    public function setTabDetalleEvento(string $tab): void
    {
        $this->tabDetalleEvento = $tab;
    }

    public function abrirModalRegistrarEvento(): void
    {
        $this->resetValidation();
        $this->nuevoEventoTipo = 'CAIDA';
        $this->nuevoEventoFechaHora = now()->format('Y-m-d\TH:i');
        $this->nuevoEventoLugar = '';
        $this->nuevoEventoSeveridad = 'MODERADA';
        $this->nuevoEventoDescripcion = '';
        $this->nuevoEventoDolor = 0;
        $this->nuevoEventoLesion = false;
        $this->nuevoEventoPresenciado = false;
        $this->nuevoEventoTestigo = '';
        $this->nuevoEventoMovilidad = '';
        $this->nuevoEventoMedicoInformado = false;
        $this->nuevoEventoFamiliarInformado = false;
        $this->nuevoEventoRequiereSeguimiento = true;
        $this->nuevoEventoFechaSeguimiento = null;
        $this->modalRegistrarEvento = true;
    }

    public function cerrarModalRegistrarEvento(): void
    {
        $this->modalRegistrarEvento = false;
        $this->resetValidation();
    }

    public function guardarNuevoEvento(): void
    {
        $this->resetValidation();
        $incidente = app(RegistrarIncidenteEnFicha::class)->guardarNuevoEvento(auth()->user(), [
            'adultoMayor' => $this->adultoMayor,
            'nuevoEventoMovilidad' => $this->nuevoEventoMovilidad,
            'nuevoEventoDolor' => $this->nuevoEventoDolor,
            'nuevoEventoLesion' => $this->nuevoEventoLesion,
            'nuevoEventoPresenciado' => $this->nuevoEventoPresenciado,
            'nuevoEventoTestigo' => $this->nuevoEventoTestigo,
            'nuevoEventoFamiliarInformado' => $this->nuevoEventoFamiliarInformado,
            'nuevoEventoTipo' => $this->nuevoEventoTipo,
            'nuevoEventoSeveridad' => $this->nuevoEventoSeveridad,
            'nuevoEventoLugar' => $this->nuevoEventoLugar,
            'nuevoEventoFechaHora' => $this->nuevoEventoFechaHora,
            'nuevoEventoDescripcion' => $this->nuevoEventoDescripcion,
            'nuevoEventoMedicoInformado' => $this->nuevoEventoMedicoInformado,
            'nuevoEventoRequiereSeguimiento' => $this->nuevoEventoRequiereSeguimiento,
        ]);

        $this->modalRegistrarEvento = false;
        $this->cargarAdulto($incidente->cod_residente);
        $this->eventoSeleccionadoId = $incidente->cod_incidente;

        session()->flash('success', 'Evento clínico registrado exitosamente.');
        $this->dispatch('render-graficos-eventos');
    }

    public function getEventosClinicosProperty()
    {
        return app(EventosClinicosService::class)->obtenerEventos($this->adultoMayor);
    }

    public function getEventosFiltradosProperty()
    {
        $eventos = $this->eventosClinicos;

        // Filtro por tipo
        if ($this->filtroTipoEvento !== 'TODOS') {
            $eventos = $eventos->filter(fn ($e) => $e['tipo'] === $this->filtroTipoEvento);
        }

        // Filtro por período
        $ahora = now();
        $eventos = match ($this->filtroPeriodoEvento) {
            '30d' => $eventos->filter(fn ($e) => $e['fecha_hora_carbon']->diffInDays($ahora) <= 30),
            '3m' => $eventos->filter(fn ($e) => $e['fecha_hora_carbon']->diffInDays($ahora) <= 90),
            '6m' => $eventos->filter(fn ($e) => $e['fecha_hora_carbon']->diffInDays($ahora) <= 180),
            '1a' => $eventos->filter(fn ($e) => $e['fecha_hora_carbon']->diffInDays($ahora) <= 365),
            default => $eventos
        };

        // Filtro por búsqueda
        if (! empty(trim($this->filtroBusquedaEvento))) {
            $q = mb_strtolower(trim($this->filtroBusquedaEvento));
            $eventos = $eventos->filter(function ($e) use ($q) {
                return str_contains(mb_strtolower($e['titulo']), $q) ||
                       str_contains(mb_strtolower($e['descripcion_resumida']), $q) ||
                       str_contains(mb_strtolower($e['descripcion_completa']), $q) ||
                       str_contains(mb_strtolower($e['profesional_nombre']), $q) ||
                       str_contains(mb_strtolower($e['tipo_label']), $q);
            });
        }

        return $eventos->values();
    }

    public function getEventoActivoProperty()
    {
        $filtrados = $this->eventosFiltrados;
        if ($filtrados->isEmpty()) {
            return $this->eventosClinicos->first();
        }

        if (! empty($this->eventoSeleccionadoId)) {
            $sel = $filtrados->firstWhere('id', $this->eventoSeleccionadoId);
            if ($sel) {
                return $sel;
            }
        }

        return $filtrados->first();
    }

    public function getMetricasEventosProperty(): array
    {
        $todos = $this->eventosClinicos;
        $activos = $todos->filter(fn ($e) => in_array($e['estado'], ['ABIERTO', 'ACTIVO']))->count();
        $enSeguimiento = $todos->filter(fn ($e) => $e['estado'] === 'EN_SEGUIMIENTO')->count();
        $resueltos = $todos->filter(fn ($e) => $e['estado'] === 'RESUELTO')->count();
        $criticos = $todos->filter(fn ($e) => ($e['severidad'] ?? '') === 'Grave / Crítica' || ($e['severidad'] ?? '') === 'Crítica')->count();

        return [
            'activos' => $activos,
            'en_seguimiento' => $enSeguimiento,
            'resueltos' => $resueltos,
            'criticos' => $criticos,
        ];
    }

    public function getEventosPorMesDataProperty(): array
    {
        $meses = [];
        $labels = [];
        $data = [];

        // Generar últimos 6 meses en orden cronológico
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $key = $m->format('Y-m');
            $meses[$key] = [
                'label' => ucfirst($m->translatedFormat('M')),
                'count' => 0,
            ];
        }

        foreach ($this->eventosClinicos as $ev) {
            $key = $ev['fecha_hora_carbon']->format('Y-m');
            if (isset($meses[$key])) {
                $meses[$key]['count']++;
            }
        }

        foreach ($meses as $m) {
            $labels[] = $m['label'];
            $data[] = $m['count'];
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public function getEventosPorTipoDataProperty(): array
    {
        $conteo = [
            'Caídas' => 0,
            'Lesiones' => 0,
            'Incidentes' => 0,
            'Complicaciones' => 0,
            'Otros' => 0,
        ];

        foreach ($this->eventosClinicos as $ev) {
            match ($ev['tipo']) {
                'CAIDA' => $conteo['Caídas']++,
                'LESION' => $conteo['Lesiones']++,
                'COMPLICACION' => $conteo['Complicaciones']++,
                'OTRO' => $conteo['Otros']++,
                default => $conteo['Incidentes']++
            };
        }

        $total = array_sum($conteo);
        $percentages = [];
        foreach ($conteo as $k => $v) {
            $percentages[$k] = $total > 0 ? round(($v / $total) * 100) : 0;
        }

        return [
            'labels' => array_keys($conteo),
            'data' => array_values($conteo),
            'percentages' => $percentages,
            'total' => $total,
        ];
    }

    // ── Computed Properties: Resultados y Estudios Clínicos ─────────────────

    public function getEstudiosClinicosProperty()
    {
        if (! isset($this->adultoMayor)) {
            return collect();
        }

        return app(ResultadosEstudiosService::class)->obtenerEstudios($this->adultoMayor);
    }

    public function getEstudiosFiltradosProperty()
    {
        $estudios = $this->estudiosClinicos;

        // Filtro por subtab
        if ($this->subtabEstudio !== 'TODOS') {
            $estudios = $estudios->filter(fn ($e) => ($e['tipo_categoria'] ?? '') === $this->subtabEstudio);
        }

        // Filtro por búsqueda textual
        if (! empty(trim($this->filtroBusquedaEstudio))) {
            $busq = mb_strtolower(trim($this->filtroBusquedaEstudio));
            $estudios = $estudios->filter(function ($e) use ($busq) {
                return str_contains(mb_strtolower($e['titulo'] ?? ''), $busq)
                    || str_contains(mb_strtolower($e['tipo_texto'] ?? ''), $busq)
                    || str_contains(mb_strtolower($e['resultado_valor'] ?? ''), $busq)
                    || str_contains(mb_strtolower($e['observaciones'] ?? ''), $busq)
                    || str_contains(mb_strtolower($e['profesional_nombre'] ?? ''), $busq)
                    || str_contains(mb_strtolower($e['estado_badge'] ?? ''), $busq);
            });
        }

        return $estudios->values();
    }

    public function getEstudioActivoProperty()
    {
        $estudios = $this->estudiosFiltrados;
        if ($estudios->isEmpty()) {
            return null;
        }

        if ($this->estudioSeleccionadoId) {
            $encontrado = $estudios->firstWhere('id', $this->estudioSeleccionadoId);
            if ($encontrado) {
                return $encontrado;
            }
        }

        return $estudios->first();
    }

    public function getMetricasEstudiosProperty(): array
    {
        return app(ResultadosEstudiosService::class)->obtenerMetricas($this->estudiosClinicos);
    }

    public function getGraficoEvolucionEstudiosProperty(): array
    {
        return app(ResultadosEstudiosService::class)->obtenerDatosGrafico(
            $this->adultoMayor,
            $this->parametroGraficoEstudio,
            $this->filtroPeriodoEstudio
        );
    }

    public function getRangosReferenciaParametroProperty(): array
    {
        return app(ResultadosEstudiosService::class)->obtenerRangosReferencia($this->adultoMayor, $this->parametroGraficoEstudio);
    }

    // ── Computed Properties: Documentación ──────────────────────────────────

    public function getDocumentosResidenteProperty()
    {
        if (! isset($this->adultoMayor)) {
            return collect();
        }

        return app(DocumentacionResidenteService::class)->obtenerDocumentos($this->adultoMayor);
    }

    public function getDocumentosFiltradosProperty()
    {
        return app(DocumentacionResidenteService::class)->filtrarYOrdenar(
            $this->documentosResidente,
            $this->filtroBusquedaDoc,
            $this->filtroTipoDoc,
            $this->filtroCategoriaDoc,
            $this->ordenDoc
        );
    }

    public function getDocumentosPaginadosProperty()
    {
        $coleccion = $this->documentosFiltrados;
        $total = $coleccion->count();
        $porPagina = max(1, $this->porPaginaDoc);
        $totalPaginas = max(1, (int) ceil($total / $porPagina));

        if ($this->paginaDoc > $totalPaginas) {
            $this->paginaDoc = $totalPaginas;
        }

        $items = $coleccion->forPage($this->paginaDoc, $porPagina)->values();

        return [
            'items' => $items,
            'total' => $total,
            'pagina_actual' => $this->paginaDoc,
            'total_paginas' => $totalPaginas,
            'desde' => $total > 0 ? (($this->paginaDoc - 1) * $porPagina) + 1 : 0,
            'hasta' => min($this->paginaDoc * $porPagina, $total),
        ];
    }

    public function getDocumentoActivoProperty()
    {
        $filtrados = $this->documentosFiltrados;
        if ($filtrados->isEmpty()) {
            return null;
        }

        if ($this->documentoSeleccionadoId) {
            $encontrado = $filtrados->firstWhere('id', $this->documentoSeleccionadoId);
            if ($encontrado) {
                return $encontrado;
            }
        }

        return $filtrados->first();
    }

    public function getMetricasDocumentosProperty(): array
    {
        return app(DocumentacionResidenteService::class)->obtenerMetricas($this->documentosResidente);
    }

    // ─── 1. REGISTRAR SIGNOS ──────────────────────────────────────────

    public function abrirRegistrarSignos(): void
    {
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());
        $this->reset(['signoPA', 'signoFC', 'signoFR', 'signoTemp', 'signoSat', 'signoGlucosa', 'signoDolor', 'signoObs', 'signoPosicion', 'signoUsaOxigeno', 'signoConfirmarAtipico']);
        $this->modalSignos = true;
    }

    public function guardarSignos(): void
    {
        app(SignosVitalesService::class)->registrar($this->adultoMayor->cod_residente, [
            'presion_arterial' => $this->signoPA,
            'frecuencia_cardiaca' => $this->signoFC,
            'frecuencia_respiratoria' => $this->signoFR,
            'temperatura' => $this->signoTemp,
            'saturacion' => $this->signoSat,
            'glucosa' => $this->signoGlucosa,
            'dolor' => $this->signoDolor,
            'posicion' => $this->signoPosicion,
            'usa_oxigeno' => $this->signoUsaOxigeno,
            'valor_atipico_confirmado' => $this->signoConfirmarAtipico,
            'observacion' => $this->signoObs,
        ], Auth::user());

        $this->modalSignos = false;
        $this->cargarAdulto($this->adultoMayor->cod_residente);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Signos vitales registrados', 'text' => 'Control hemodinámico guardado correctamente.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. ADMINISTRAR O OMITIR MEDICACIÓN
    // ──────────────────────────────────────────────────────────────────────────

    public function abrirAdministrarMed(?string $codMed = null, ?string $horaProgramada = null): void
    {
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());

        $codResidente = $this->adultoMayor->cod_residente ?? $this->adultoMayor->cod_residente;
        if (empty($codMed)) {
            $this->tabActivo = 'medicacion';
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Seleccione una dosis',
                'text' => 'Elija explícitamente la prescripción y el horario que va a registrar.',
            ]);

            return;
        }

        $med = Prescripcion::where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
            ->where('cod_prescripcion', $codMed)
            ->firstOrFail();
        $this->medSeleccionadoId = $med->cod_prescripcion;
        $this->medHoraProgramada = $horaProgramada;
        $this->medNombre = $med->nombre_medicamento;
        $this->medDosis = $med->dosis ?? '';
        $this->medVia = $med->via_administracion ?? 'Oral';
        $this->medEsPrn = (bool) $med->es_prn;
        $this->medCondicionPrn = $med->condicion_prn ?? '';
        $this->medMotivoPrn = '';
        $this->medValoracionPrevia = '';
        $this->medIntensidadPrevia = null;
        $this->medAccion = 'ADMINISTRAR';
        $this->medAdministrado = true;
        $this->medMotivoOmision = '';
        $this->medEfectoObs = '';
        $this->modalMed = true;
    }

    public function guardarAdministracionMed(): void
    {
        $this->confirmarAdministracionMed();
    }

    public function confirmarAdministracionMed(): void
    {
        abort_unless(auth()->user()?->can('administraciones_medicacion.crear'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());

        $codResidente = $this->adultoMayor->cod_residente ?? $this->adultoMayor->cod_residente;
        $med = Prescripcion::where('cod_residente', $codResidente)
            ->where('cod_prescripcion', $this->medSeleccionadoId)
            ->firstOrFail();

        if (! in_array($med->estado, ['ACTIVO', 'ACTIVA', 'VIGENTE'])) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Error', 'text' => 'El medicamento no está activo.']);

            return;
        }

        $resultado = match ($this->medAccion) {
            'ADMINISTRAR' => 'ADMINISTRADO', 'RECHAZAR' => 'RECHAZADO',
            'NO_DISPONIBLE' => 'NO_DISPONIBLE', default => 'OMITIDO',
        };
        $esAdmin = $resultado === 'ADMINISTRADO';

        if (! $esAdmin) {
            $this->validate(['medMotivoOmision' => 'required|string|min:5|max:500'], ['medMotivoOmision.min' => 'El motivo de omisión debe tener al menos 5 caracteres.']);
        }
        $this->validate(['medEfectoObs' => 'nullable|string|max:1000']);
        if ($med->es_prn && $esAdmin) {
            $this->validate([
                'medMotivoPrn' => 'required|string|min:5|max:500',
                'medValoracionPrevia' => 'required|string|min:5|max:1000',
                'medIntensidadPrevia' => 'required|integer|min:0|max:10',
            ], [
                'medMotivoPrn.required' => 'Indique el síntoma o motivo que justifica la medicación PRN.',
                'medValoracionPrevia.required' => 'Registre la valoración previa antes de administrar PRN.',
            ]);
        }

        $servicio = app(RegistrarAdministracionMedicacionService::class);
        if ($med->es_prn && $esAdmin) {
            $servicio->registrarPrn(
                Auth::user(), $this->adultoMayor->cod_residente, $this->medSeleccionadoId,
                $this->medMotivoPrn, $this->medValoracionPrevia, (int) $this->medIntensidadPrevia,
                $this->medEfectoObs,
            );
        } else {
            if ($med->es_prn) {
                $this->addError('medAccion', 'Las dosis PRN solo se registran cuando se administran por una indicación clínica presente.');

                return;
            }
            $horaProgramada = $this->medHoraProgramada ?: ($med->hora_programada ? Carbon::parse($med->hora_programada)->format('H:i') : now()->format('H:i'));
            $servicio->registrarProgramada(
                Auth::user(), $this->adultoMayor->cod_residente, $this->medSeleccionadoId,
                $horaProgramada, $esAdmin, $this->medMotivoOmision, $this->medEfectoObs,
            );
        }

        $this->modalMed = false;
        $this->cargarAdulto($this->adultoMayor->cod_residente);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Medicación registrada', 'text' => 'Trazabilidad de fármaco registrada con éxito.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. EJECUTAR TAREA DE CUIDADOS
    // ──────────────────────────────────────────────────────────────────────────

    public function abrirEjecutarTarea(string $codTarea, string $estado = 'REALIZADA'): void
    {
        $tarea = EjecucionCuidado::findOrFail($codTarea);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($tarea->cod_residente, Auth::user());

        $this->tareaAccionId = $codTarea;
        $this->tareaTitulo = $tarea->titulo;
        $this->tareaEstadoAccion = $estado;
        $this->tareaResultado = '';
        $this->tareaMotivoOmision = '';
        $this->modalTarea = true;
    }

    public function completarTarea(string $codTarea): void
    {
        app(GestionarCuidadosEnFicha::class)->completarTarea(auth()->user(), [

        ], $codTarea);

        $this->cargarAdulto($this->adultoMayor->cod_residente);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tarea realizada', 'text' => 'El cuidado fue registrado como completado.']);
    }

    public function omitirTarea(string $codTarea): void
    {
        $this->abrirEjecutarTarea($codTarea, 'OMITIDA');
    }

    public function registrarCuidadoDirecto(string $codIntervencion, string $resultado = 'REALIZADA', ?string $observaciones = null): void
    {
        $intervencion = app(GestionarCuidadosEnFicha::class)->registrarCuidadoDirecto(auth()->user(), [
            'adultoMayor' => $this->adultoMayor,
        ], $codIntervencion, $resultado, $observaciones);

        $this->cargarAdulto($this->adultoMayor->cod_residente);
        $this->dispatch('swal', [
            'icon' => $resultado === 'INCIDENCIA' ? 'warning' : 'success',
            'title' => $resultado === 'INCIDENCIA' ? 'Incidencia registrada' : 'Cuidado registrado',
            'text' => "El cuidado '{$intervencion->nombre}' se registró satisfactoriamente en la ficha del residente.",
        ]);
    }

    public function registrarAdministracionDirecta(string $codMed, string $resultado = 'ADMINISTRADA', ?string $horaReal = null, ?string $observaciones = null, ?string $motivo = null): void
    {
        abort_unless(Auth::check(), 401);

        $resultado = mb_strtoupper(trim($resultado));
        validator([
            'resultado' => $resultado,
            'observaciones' => $observaciones,
            'motivo' => $motivo,
        ], [
            'resultado' => 'required|in:ADMINISTRADA,ADMINISTRADO,OMITIDA,OMITIDO',
            'observaciones' => 'nullable|string|max:1000',
            'motivo' => 'required_unless:resultado,ADMINISTRADA,ADMINISTRADO|nullable|string|min:5|max:500',
        ])->validate();

        $med = Prescripcion::where('cod_residente', $this->adultoMayor->cod_residente ?? $this->adultoMayor->cod_residente)
            ->where('cod_prescripcion', $codMed)
            ->firstOrFail();

        $esAdmin = in_array($resultado, ['ADMINISTRADA', 'ADMINISTRADO'], true);
        $horaProgramada = $med->hora_programada
            ? Carbon::parse($med->hora_programada)->format('H:i')
            : now()->format('H:i');

        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            Auth::user(),
            $this->adultoMayor->cod_residente,
            $med->cod_prescripcion,
            $horaProgramada,
            $esAdmin,
            $esAdmin ? null : $motivo,
            $observaciones,
        );

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Administración Registrada',
            'text' => $esAdmin ? 'La dosis ha sido registrada correctamente en el expediente.' : 'Se ha registrado la justificación de omisión/demora.',
        ]);

        $this->cargarAdulto($this->adultoMayor->cod_residente);
    }

    public function guardarAccionTarea(): void
    {
        $this->resetValidation();
        app(GestionarCuidadosEnFicha::class)->guardarAccionTarea(auth()->user(), [
            'tareaAccionId' => $this->tareaAccionId,
            'tareaEstadoAccion' => $this->tareaEstadoAccion,
            'tareaResultado' => $this->tareaResultado,
            'tareaMotivoOmision' => $this->tareaMotivoOmision,
        ]);

        $this->modalTarea = false;
        $this->cargarAdulto($this->adultoMayor->cod_residente);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tarea actualizada', 'text' => 'Estado del plan de cuidados actualizado.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. REGISTRAR SEGUIMIENTO / EVOLUCIÓN
    // ──────────────────────────────────────────────────────────────────────────

    public function abrirRegistrarSeguimiento(): void
    {
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());
        $this->reset([
            'segEstado', 'segAlimentacion', 'segMovilidad', 'segSueno',
            'segObs', 'segIncidente', 'segRequiereMedico',
        ]);
        $this->modalSeguimiento = true;
    }

    public function guardarSeguimiento(): void
    {
        $this->resetValidation();
        app(GestionarCuidadosEnFicha::class)->guardarSeguimiento(auth()->user(), [
            'adultoMayor' => $this->adultoMayor,
            'segIncidente' => $this->segIncidente,
            'segObs' => $this->segObs,
            'segRequiereMedico' => $this->segRequiereMedico,
            'segEstado' => $this->segEstado,
            'segAlimentacion' => $this->segAlimentacion,
            'segMovilidad' => $this->segMovilidad,
            'segSueno' => $this->segSueno,
        ]);

        $this->modalSeguimiento = false;
        $this->cargarAdulto($this->adultoMayor->cod_residente);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Seguimiento guardado', 'text' => 'Nota de evolución clínica registrada.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 5. REPORTAR INCIDENTE / ALERTA
    // ──────────────────────────────────────────────────────────────────────────

    public function abrirReportarIncidente(): void
    {
        abort_unless(auth()->user()?->can('alertas.gestionar'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($this->adultoMayor, Auth::user());
        $this->reset(['incidenteMotivo']);
        $this->incidenteTipo = 'INCIDENTE';
        $this->incidenteNivel = 'ALTO';
        $this->modalIncidente = true;
    }

    public function guardarIncidente(): void
    {
        app(AlertasService::class)->crear($this->adultoMayor->cod_residente, [
            'origen' => 'INCIDENTE', 'tipo_alerta' => $this->incidenteTipo,
            'nivel' => $this->incidenteNivel, 'motivo' => $this->incidenteMotivo,
        ], Auth::user());

        $this->modalIncidente = false;
        $this->cargarAdulto($this->adultoMayor->cod_residente);
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Incidente reportado', 'text' => 'Se generó la alerta clínica correspondiente.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 6. GESTIÓN DE ALERTAS (ATENDER / CERRAR)
    // ──────────────────────────────────────────────────────────────────────────

    public function abrirAtenderAlerta(string $codAlerta): void
    {
        abort_unless(auth()->user()?->can('alertas.gestionar'), 403);
        $alerta = Alerta::findOrFail($codAlerta);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_residente, Auth::user());

        $this->alertaAccionId = $codAlerta;
        $this->accionTomadaAlerta = '';
        $this->modalAtenderAlerta = true;
    }

    public function confirmarAtencionAlerta(): void
    {
        app(AlertasService::class)->registrarIntervencion(Alerta::findOrFail($this->alertaAccionId), $this->accionTomadaAlerta, Auth::user());

        $this->modalAtenderAlerta = false;
        $this->cargarAdulto($this->adultoMayor->cod_residente);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Alerta en atención', 'text' => 'Acción registrada con éxito.']);
    }

    public function abrirCerrarAlerta(string $codAlerta): void
    {
        abort_unless(auth()->user()?->can('alertas.gestionar'), 403);
        $alerta = Alerta::findOrFail($codAlerta);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_residente, Auth::user());

        $this->alertaAccionId = $codAlerta;
        $this->observacionCierreAlerta = '';
        $this->modalCerrarAlerta = true;
    }

    public function confirmarCierreAlerta(): void
    {
        $alerta = Alerta::findOrFail($this->alertaAccionId);
        app(AlertasService::class)->cerrar($alerta, $this->observacionCierreAlerta, Auth::user());

        $this->modalCerrarAlerta = false;
        $this->cargarAdulto($this->adultoMayor->cod_residente);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Alerta cerrada', 'text' => 'La alerta fue cerrada con trazabilidad registrada.']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 7. LÓGICA DE HISTORIAL CLÍNICO CRONOLÓGICO INTEGRADO
    // ──────────────────────────────────────────────────────────────────────────

    private function resolverTimestamp($fecha, $hora = null, $createdAt = null): ?Carbon
    {
        if (! empty($fecha)) {
            $fStr = $fecha instanceof Carbon ? $fecha->format('Y-m-d') : (string) $fecha;
            $hStr = '00:00:00';
            if ($hora instanceof Carbon) {
                $hStr = $hora->format('H:i:s');
            } elseif (is_string($hora) && ! empty($hora)) {
                if (strlen($hora) >= 10 && str_contains($hora, '-')) {
                    return Carbon::parse($hora);
                }
                $hStr = strlen($hora) === 5 ? $hora.':00' : substr($hora, 0, 8);
            }
            if (strlen($fStr) >= 10) {
                return Carbon::parse(substr($fStr, 0, 10).' '.$hStr);
            }
        }

        if ($createdAt instanceof Carbon) {
            return $createdAt;
        }
        if (is_string($createdAt) && ! empty($createdAt)) {
            return Carbon::parse($createdAt);
        }

        return null;
    }

    public function getHistorialCronologicoProperty()
    {
        $eventos = collect();

        // 1. Pases de Turno
        foreach ($this->adultoMayor->pasesTurno as $pase) {
            $saliente = $pase->turnoSaliente->nombre ?? 'No registrado';
            $entrante = $pase->turnoEntrante->nombre ?? 'No registrado';
            $resumenPase = "Relevo: {$saliente} → {$entrante}";
            if ($pase->requiere_vigilancia_especial) {
                $resumenPase .= ' · Vigilancia especial'.($pase->motivo_vigilancia ? ': '.$pase->motivo_vigilancia : '');
            } elseif ($pase->resumen_turno) {
                $resumenPase .= ' · '.$pase->resumen_turno;
            }

            $eventos->push([
                'tipo' => 'PASE_TURNO',
                'tipo_label' => 'Pase de turno',
                'titulo' => 'Pase de Turno ('.$saliente.' → '.$entrante.')',
                'resumen' => $resumenPase,
                'descripcion' => $pase->resumen_turno ?: 'Sin resumen registrado.',
                'fecha' => $pase->fecha ? Carbon::parse($pase->fecha)->format('Y-m-d') : null,
                'hora' => $pase->created_at?->format('H:i') ?? '',
                'timestamp' => $this->resolverTimestamp($pase->fecha, null, $pase->created_at),
                'responsable' => $pase->enfermeroSaliente->name ?? 'No registrado',
                'estado_badge' => $pase->estado === 'RECIBIDO' ? 'CONFIRMADO' : 'ENTREGADO',
                'badge' => $pase->estado === 'RECIBIDO' ? 'RECEPCIÓN CONFIRMADA' : 'ENTREGADO',
                'badge_color' => 'indigo',
                'icon' => 'ph-arrows-left-right',
                'es_incidente' => (bool) $pase->requiere_vigilancia_especial,
            ]);
        }

        // 2. Alertas Clínicas
        foreach ($this->adultoMayor->alertas as $alerta) {
            $resumenAlerta = $alerta->descripcion ?: 'Sin descripción registrada';

            $esIncidente = in_array($alerta->prioridad, ['CRITICO', 'ALTO'])
                || str_contains(strtoupper($alerta->tipo), 'INCIDENTE')
                || str_contains(strtoupper($alerta->tipo), 'CAIDA');

            $eventos->push([
                'tipo' => 'ALERTA',
                'tipo_label' => 'Alerta clínica',
                'titulo' => 'Alerta: '.$alerta->tipo.' ['.$alerta->prioridad.']',
                'resumen' => $resumenAlerta,
                'descripcion' => $alerta->descripcion,
                'fecha' => $alerta->fecha_hora?->format('Y-m-d'),
                'hora' => $alerta->fecha_hora ? $alerta->fecha_hora->format('H:i') : '',
                'timestamp' => $alerta->fecha_hora,
                'responsable' => $alerta->responsable?->usuario?->name ?? 'No registrado',
                'estado_badge' => $alerta->estado === 'CERRADA' ? 'CERRADA' : ($alerta->estado === 'EN_ATENCION' ? 'EN ATENCIÓN' : 'ABIERTA'),
                'badge' => $alerta->estado,
                'badge_color' => $alerta->estado === 'CERRADA' ? 'zinc' : ($alerta->prioridad === 'CRITICO' ? 'red' : 'amber'),
                'icon' => 'ph-bell-ringing',
                'es_incidente' => $esIncidente,
            ]);
        }

        // 3. Signos Vitales
        foreach ($this->adultoMayor->signosVitales as $signo) {
            $pa = $signo->presion_arterial ? "PA {$signo->presion_arterial}" : '';
            $fc = $signo->frecuencia_cardiaca ? "FC {$signo->frecuencia_cardiaca}" : '';
            $sat = $signo->saturacion ? "SpO2 {$signo->saturacion}%" : '';
            $temp = $signo->temperatura ? "Temp {$signo->temperatura}°C" : '';
            $glu = $signo->glucosa ? "Gluc {$signo->glucosa} mg/dL" : '';
            $resumenSignos = implode(' · ', array_filter([$pa, $fc, $sat, $temp, $glu]));

            $eventos->push([
                'tipo' => 'SIGNOS',
                'tipo_label' => 'Signos vitales',
                'titulo' => 'Control de Signos Vitales',
                'resumen' => $resumenSignos ?: 'Sin valores cuantitativos registrados',
                'descripcion' => $resumenSignos ?: 'Sin valores cuantitativos registrados',
                'fecha' => $signo->fecha ? Carbon::parse($signo->fecha)->format('Y-m-d') : null,
                'hora' => $signo->hora ? substr($signo->hora, 0, 5) : ($signo->created_at ? $signo->created_at->format('H:i') : ''),
                'timestamp' => $this->resolverTimestamp($signo->fecha, $signo->hora, $signo->created_at),
                'responsable' => $signo->personal?->usuario?->name ?? 'No registrado',
                'estado_badge' => 'REGISTRADO',
                'badge' => 'SIGNOS VITALES',
                'badge_color' => 'emerald',
                'icon' => 'ph-heartbeat',
                'es_incidente' => false,
            ]);
        }

        // 4. Medicaciones y Administraciones
        foreach ($this->adultoMayor->administracionesMedicacion as $admin) {
            $adminOk = (bool) $admin->administrado;
            $medNombre = $admin->medicacion->nombre_medicamento ?? 'Medicamento no identificado';
            $dosis = $admin->medicacion?->dosis ? " {$admin->medicacion->dosis}" : '';
            $resumenMed = $adminOk
                ? "{$medNombre}{$dosis} administrado".($admin->fecha_hora_administracion ? ' a las '.$admin->fecha_hora_administracion->format('H:i') : '')
                : 'Omisión: '.($admin->motivo_omision ?: 'Motivo no registrado');

            $eventos->push([
                'tipo' => 'MEDICACION',
                'tipo_label' => 'Medicación',
                'titulo' => ($adminOk ? 'Medicación Administrada: ' : 'Medicación Omitida: ').$medNombre,
                'resumen' => $resumenMed,
                'descripcion' => $resumenMed,
                'fecha' => ($admin->fecha_hora_administracion ?: $admin->fecha_hora_programada)?->format('Y-m-d'),
                'hora' => ($admin->fecha_hora_administracion ?: $admin->fecha_hora_programada)?->format('H:i') ?? '',
                'timestamp' => $admin->fecha_hora_administracion ?: $admin->fecha_hora_programada,
                'responsable' => $admin->personal?->usuario?->name ?? 'No registrado',
                'estado_badge' => $adminOk ? 'ADMINISTRADA' : 'OMITIDA',
                'badge' => $adminOk ? 'ADMINISTRADA' : 'OMITIDA',
                'badge_color' => $adminOk ? 'teal' : 'amber',
                'icon' => 'ph-pill',
                'es_incidente' => ! $adminOk,
            ]);
        }

        // 5. Tareas de Planes de Cuidados
        $tareasHistorial = EjecucionCuidado::where('cod_residente', $this->adultoMayor->cod_residente)
            ->whereIn('estado', ['REALIZADA', 'OMITIDA'])
            ->with(['personal.usuario', 'intervencion.plan.area'])
            ->orderByDesc('fecha_hora_ejecucion')
            ->orderByDesc('fecha_hora_programada')
            ->take(40)
            ->get();

        foreach ($tareasHistorial as $tarea) {
            $resumenTarea = $tarea->estado === 'REALIZADA'
                ? "{$tarea->titulo} realizada".($tarea->resultado ? " · Resultado: {$tarea->resultado}" : '')
                : "Omisión: {$tarea->titulo} · ".($tarea->motivo_omision ?: 'Motivo no registrado');

            $eventos->push([
                'tipo' => 'TAREA',
                'tipo_label' => 'Cuidados',
                'titulo' => 'Tarea ['.($tarea->intervencion?->plan?->area?->nombre ?? 'Cuidado').']: '.$tarea->titulo,
                'resumen' => $resumenTarea,
                'descripcion' => ($tarea->resultado ?: 'Sin resultado registrado.').($tarea->motivo_omision ? " (Motivo omisión: {$tarea->motivo_omision})" : ''),
                'fecha' => ($tarea->fecha_hora_ejecucion ?: $tarea->fecha_hora_programada)?->format('Y-m-d'),
                'hora' => ($tarea->fecha_hora_ejecucion ?: $tarea->fecha_hora_programada)?->format('H:i') ?? '',
                'timestamp' => $tarea->fecha_hora_ejecucion ?: $tarea->fecha_hora_programada,
                'responsable' => $tarea->personal?->usuario?->name ?? 'No registrado',
                'estado_badge' => $tarea->estado,
                'badge' => $tarea->estado,
                'badge_color' => $tarea->estado === 'REALIZADA' ? 'blue' : 'rose',
                'icon' => 'ph-check-circle',
                'es_incidente' => $tarea->estado === 'OMITIDA',
            ]);
        }

        // 6. Seguimientos Diarios
        foreach ($this->adultoMayor->seguimientosDiarios as $seg) {
            if ($seg->tipo_atencion->tipo !== 'SEGUIMIENTO_DIARIO') {
                continue;
            }
            $alertaSeguimiento = $this->adultoMayor->alertas->firstWhere('cod_registro', $seg->cod_atencion);
            $esIncidente = (bool) $alertaSeguimiento;
            $resumenSeg = $seg->observacion ?: 'Seguimiento diario registrado';

            $eventos->push([
                'tipo' => 'SEGUIMIENTO',
                'tipo_label' => 'Seguimiento',
                'titulo' => 'Evolución de Enfermería'.($esIncidente ? ' [ALERTA CLÍNICA]' : ''),
                'resumen' => $resumenSeg,
                'descripcion' => $seg->observacion ?: 'Seguimiento registrado en guardia',
                'fecha' => $seg->fecha_hora?->format('Y-m-d'),
                'hora' => $seg->fecha_hora?->format('H:i') ?? '',
                'timestamp' => $seg->fecha_hora,
                'responsable' => $seg->personal?->usuario?->name ?? 'No registrado',
                'estado_badge' => $esIncidente ? 'ALERTA CLÍNICA' : 'REGISTRADO',
                'badge' => $esIncidente ? 'ALERTA' : 'EVOLUCION',
                'badge_color' => $esIncidente ? 'red' : 'purple',
                'icon' => 'ph-notebook',
                'es_incidente' => $esIncidente,
            ]);
        }

        // 7. Evaluaciones y Valoraciones
        foreach ($this->adultoMayor->evaluacionesGeriatricas as $eval) {
            $resumenEval = $eval->diagnostico_principal ?: ($eval->observaciones ?: 'Evaluación geriátrica clínica');

            $eventos->push([
                'tipo' => 'VALORACION',
                'tipo_label' => 'Valoraciones',
                'titulo' => 'Evaluación Geriátrica Inicial',
                'resumen' => $resumenEval,
                'descripcion' => $eval->diagnostico_principal ?: ($eval->observaciones ?: 'Valoración clínica de ingreso'),
                'fecha' => $eval->fecha_evaluacion ? Carbon::parse($eval->fecha_evaluacion)->format('Y-m-d') : $eval->created_at?->format('Y-m-d'),
                'hora' => $eval->created_at?->format('H:i') ?? '',
                'timestamp' => $this->resolverTimestamp($eval->fecha_evaluacion, null, $eval->created_at),
                'responsable' => $eval->evaluador->name ?? 'No registrado',
                'estado_badge' => 'COMPLETADA',
                'badge' => 'VALORACION MEDICA',
                'badge_color' => 'cyan',
                'icon' => 'ph-clipboard-text',
                'es_incidente' => false,
            ]);
        }

        foreach ($this->adultoMayor->valoracionesFuncionales as $val) {
            $resumenVal = "Barthel: {$val->barthel_total}/100 · Nivel: {$val->nivel_dependencia}";
            if ($val->riesgo_caida) {
                $resumenVal .= " · Riesgo caída: {$val->riesgo_caida}";
            }

            $eventos->push([
                'tipo' => 'VALORACION',
                'tipo_label' => 'Valoraciones',
                'titulo' => "Valoración Funcional (Barthel: {$val->barthel_total}, Katz: {$val->katz_total})",
                'resumen' => $resumenVal,
                'descripcion' => "Dependencia: {$val->nivel_dependencia} | Riesgo caída: {$val->riesgo_caida}",
                'fecha' => $val->fecha_valoracion ? Carbon::parse($val->fecha_valoracion)->format('Y-m-d') : $val->created_at?->format('Y-m-d'),
                'hora' => $val->created_at?->format('H:i') ?? '',
                'timestamp' => $this->resolverTimestamp($val->fecha_valoracion, null, $val->created_at),
                'responsable' => $val->registradoPor->name ?? ($val->evaluador->name ?? 'No registrado'),
                'estado_badge' => 'COMPLETADA',
                'badge' => 'VALORACION FUNCIONAL',
                'badge_color' => 'sky',
                'icon' => 'ph-gauge',
                'es_incidente' => false,
            ]);
        }

        return $eventos->sortByDesc(fn ($e) => $e['timestamp'] ? Carbon::parse($e['timestamp'])->timestamp : 0)->values();
    }

    public function getHistorialFiltradoProperty()
    {
        $cronologia = $this->historialCronologico;

        if ($this->historialFiltroTipo !== 'TODOS') {
            $cronologia = $cronologia->filter(function ($evento) {
                if ($this->historialFiltroTipo === 'INCIDENTES') {
                    return ! empty($evento['es_incidente']);
                }
                if ($this->historialFiltroTipo === 'CUIDADOS') {
                    return in_array($evento['tipo'], ['TAREA', 'CUIDADOS']);
                }
                if ($this->historialFiltroTipo === 'PASES') {
                    return $evento['tipo'] === 'PASE_TURNO';
                }
                if ($this->historialFiltroTipo === 'VALORACIONES') {
                    return $evento['tipo'] === 'VALORACION';
                }
                if ($this->historialFiltroTipo === 'ALERTAS') {
                    return $evento['tipo'] === 'ALERTA';
                }

                return $evento['tipo'] === $this->historialFiltroTipo;
            });
        }

        if ($this->historialFechaDesde) {
            $desde = Carbon::parse($this->historialFechaDesde)->startOfDay();
            $cronologia = $cronologia->filter(
                fn ($e) => ! empty($e['timestamp']) && Carbon::parse($e['timestamp'])->gte($desde)
            );
        }

        if ($this->historialFechaHasta) {
            $hasta = Carbon::parse($this->historialFechaHasta)->endOfDay();
            $cronologia = $cronologia->filter(
                fn ($e) => ! empty($e['timestamp']) && Carbon::parse($e['timestamp'])->lte($hasta)
            );
        }

        return $cronologia->values();
    }

    public function getResumenLongitudinalProperty(): array
    {
        return app(ResumenLongitudinalResidente::class)->consultar($this->adultoMayor, $this->periodoSignos, $this->metricaSignosSeleccionada);
    }

    public function dehydrate(): void
    {
        if (isset($this->adultoMayor)) {
            $this->adultoMayor->withoutRelations();
        }
    }

    public function render()
    {
        if (isset($this->adultoMayor)) {
            $this->adultoMayor->loadMissing([
                'ocupacionActiva.cama.habitacion',
                'asignacionesJornada.jornada.turno',
                'asignacionesJornada.personal.usuario',
                'atenciones',
                'alergias' => fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO'])->orderByDesc('fecha_hora'),
                'diagnosticos' => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])->orderByDesc('fecha_hora'),
                'antecedentesClinicos' => fn ($q) => $q->whereNotIn('estado', ['ANULADO', 'INACTIVO'])->orderByDesc('fecha_referencia'),
                'seguros' => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'ACTIVA']),
                'signosVitales' => fn ($q) => $q->orderByDesc('fecha_hora')->take(60),
                'medicaciones' => fn ($q) => $q->with(['medicamento', 'horarios'])->where('estado', 'ACTIVA'),
                'administracionesMedicacion' => fn ($q) => $q->with('medicacion.medicamento')->orderByDesc('fecha_hora_programada')->take(30),
                'alertas' => fn ($q) => $q->with('eventos.usuario')->orderByDesc('fecha_hora')->take(25),
                'evaluacionesGeriatricas' => fn ($q) => $q->orderByDesc('fecha_hora')->take(10),
                'valoracionesFuncionales' => fn ($q) => $q->with('registradoPor')->orderByDesc('fecha_hora')->take(10),
            ]);
        }

        return view('livewire.cuidados.ficha-paciente', [
            'agendaMedicacion' => isset($this->adultoMayor)
                ? app(AgendaMedicacionService::class)->paraAdulto($this->adultoMayor->cod_residente)
                : collect(),
        ])->layout(request()->routeIs('admin.enfermeria.*') ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
