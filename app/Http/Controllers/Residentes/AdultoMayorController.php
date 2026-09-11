<?php

namespace App\Http\Controllers\Residentes;

use App\Exports\AdultoIndividualExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinica\StoreCambioEstadoRequest;
use App\Http\Requests\Residentes\StoreAdultoMayorRequest;
use App\Http\Requests\Residentes\UpdateAdultoMayorRequest;
use App\Models\ActividadAdulto;
use App\Models\AdultoMayor;
use App\Models\AreaGeriatrica;
use App\Models\AtencionAdulto;
use App\Models\DocumentoAdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\EvaluacionGeriatrica;
use App\Models\Familiar;
use App\Models\HistorialEstadoAdulto;
use App\Models\InstrumentoGeriatrico;
use App\Models\ObsAdulto;
use App\Models\TipoActividadAdulto;
use App\Models\TipoAtencionAdulto;
use App\Services\Reportes\AdultoMayorBitacoraService;
use App\Services\Residentes\AdultoMayorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class AdultoMayorController extends Controller
{
    protected $adultoMayorService;

    protected $bitacoraService;

    public function __construct(
        AdultoMayorService $adultoMayorService,
        AdultoMayorBitacoraService $bitacoraService
    ) {
        $this->adultoMayorService = $adultoMayorService;
        $this->bitacoraService = $bitacoraService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filtros = $request->all();
        $adultos = $this->adultoMayorService->obtenerListado($filtros);

        $totales = [
            'total' => AdultoMayor::count(),
            'activos' => AdultoMayor::whereHas('estado', fn ($q) => $q->whereRaw('UPPER(estado) IN (?, ?)', ['ACTIVO', 'ADMITIDO']))->count(),
            'archivados' => AdultoMayor::whereHas('estado', fn ($q) => $q->whereRaw('UPPER(estado) IN (?, ?)', ['ARCHIVADO', 'INACTIVO']))->count(),
            'sin_seguimiento' => AdultoMayor::doesntHave('observaciones')->doesntHave('atenciones')->count(),
        ];

        // Datos para modales
        $estadosAdulto = EstadoAdulto::all();
        $tiposActividad = TipoActividadAdulto::all();
        $tiposAtencion = TipoAtencionAdulto::all();
        $familiares = Familiar::with('usuario')->get();

        return view('pages.adultos-mayores.index', compact(
            'adultos',
            'totales',
            'estadosAdulto',
            'tiposActividad',
            'tiposAtencion',
            'familiares'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $estadosAdulto = $this->adultoMayorService->obtenerEstados();

        return view('pages.adultos-mayores.create', compact('estadosAdulto'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdultoMayorRequest $request)
    {
        $adulto = $this->adultoMayorService->crearAdultoMayor($request->validated(), $request->file('foto'));

        return redirect()->route('admin.adultos-mayores.show', $adulto->cod_am)
            ->with('success', 'Ficha registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AdultoMayor $adulto_mayor)
    {
        $adulto = $this->adultoMayorService->obtenerDetalle($adulto_mayor->cod_am);

        // Familiares - Divididos por estado del vínculo
        $familiaresActivos = $adulto->familiares()->wherePivot('estado', 'ACTIVO')->get();
        $familiaresInactivos = $adulto->familiares()->wherePivot('estado', 'INACTIVO')->get();

        $observacionesActivas = $adulto->observaciones()->latest()->get();
        $observacionesAnuladas = $adulto->observaciones()->onlyTrashed()->latest()->get();

        $actividadesActivas = $adulto->actividades()->latest()->get();
        $actividadesAnuladas = collect();

        $atencionesActivas = $adulto->atenciones()->where('estado', '!=', 'ANULADO')->latest()->get();
        $atencionesAnuladas = $adulto->atenciones()->where('estado', 'ANULADO')->latest()->get();

        $documentosActivos = $adulto->documentos()->where('estado', '!=', 'ARCHIVADO')->latest()->get();
        $documentosArchivados = $adulto->documentos()->where('estado', 'ARCHIVADO')->latest()->get();

        $asignaciones = $adulto->voluntarios()->get();

        // Evaluaciones - Divididas por SoftDeletes
        $evaluacionesActivas = $adulto->evaluacionesGeriatricas()->with(['instrumento', 'registrador'])->latest()->get();
        $evaluacionesAnuladas = $adulto->evaluacionesGeriatricas()->onlyTrashed()->with(['instrumento', 'registrador'])->latest('deleted_at')->get();

        // Evaluaciones Geriátricas Integrales (Fase 2)
        $evaluacionesGeriatricasActivas = EvaluacionGeriatrica::where('cod_am', $adulto_mayor->cod_am)
            ->where('estado', '<>', 'ANULADO')
            ->with(['instrumento.area', 'registrador'])
            ->latest('fecha_eval')
            ->get();
        $evaluacionesGeriatricasAnuladas = EvaluacionGeriatrica::where('cod_am', $adulto_mayor->cod_am)
            ->where('estado', 'ANULADO')
            ->with(['instrumento.area', 'registrador'])
            ->latest('fecha_eval')
            ->get();
        $areasGeriatricas = AreaGeriatrica::where('estado', 'ACTIVO')->get();

        // FASE 3: Módulos médicos (Pre-cargados para uso futuro en vistas)
        $fichasMedicas = $adulto->fichasMedicas()->latest()->get();
        $medicaciones = $adulto->medicaciones()->with('receta')->latest()->get();
        $administracionesMedicacion = $adulto->administracionesMedicacion()->latest('fecha')->latest('hora_programada')->get();
        $signosVitales = $adulto->signosVitales()->latest('fecha')->latest('hora')->get();
        $valoracionesFuncionales = $adulto->valoracionesFuncionales()->latest('fecha_valoracion')->get();
        $historialEstados = $adulto->historialEstados()->with(['estadoAnteriorRelacion', 'estadoNuevoRelacion'])->latest('fecha_cambio')->get();

        $estadosAdulto = $this->adultoMayorService->obtenerEstados();
        $tiposAtenciones = $this->adultoMayorService->obtenerTiposAtenciones();
        $tiposEvaluaciones = InstrumentoGeriatrico::where('estado', 'ACTIVO')->get();
        $tiposActividades = TipoActividadAdulto::all();

        // Bitácora escalable desde Spatie Activitylog
        $bitacora = $this->bitacoraService->obtenerBitacora($adulto_mayor->cod_am, 60);
        $eventosFiltro = $this->bitacoraService->obtenerTiposEventos($adulto_mayor->cod_am);

        return view('pages.adultos-mayores.show', compact(
            'adulto',
            'familiaresActivos',
            'familiaresInactivos',
            'observacionesActivas',
            'observacionesAnuladas',
            'actividadesActivas',
            'actividadesAnuladas',
            'atencionesActivas',
            'atencionesAnuladas',
            'documentosActivos',
            'documentosArchivados',
            'asignaciones',
            'evaluacionesActivas',
            'evaluacionesAnuladas',
            'evaluacionesGeriatricasActivas',
            'evaluacionesGeriatricasAnuladas',
            'areasGeriatricas',
            'fichasMedicas',
            'medicaciones',
            'administracionesMedicacion',
            'signosVitales',
            'valoracionesFuncionales',
            'historialEstados',
            'estadosAdulto',
            'tiposAtenciones',
            'tiposEvaluaciones',
            'tiposActividades',
            'bitacora',
            'eventosFiltro'
        ));
    }

    public function reporteIndividual(Request $request, AdultoMayor $adulto_mayor)
    {
        $format = $request->query('format', 'pdf');

        $adulto = $this->adultoMayorService->obtenerDetalle($adulto_mayor->cod_am);
        $adulto->edad = $this->adultoMayorService->calcularEdad($adulto->fecha_nac);

        $evaluaciones = $adulto->evaluacionesGeriatricas()->with(['instrumento', 'registrador'])->latest()->get();
        $evaluacionesAnuladas = $adulto->evaluacionesGeriatricas()->onlyTrashed()->with(['instrumento', 'registrador'])->latest('deleted_at')->get();

        $atenciones = $adulto->atenciones()->with('tipoAtencion')->latest()->get();
        $atencionesAnuladas = collect();

        $actividades = $adulto->actividades()->with('tipoActividad')->latest()->get();
        $actividadesAnuladas = collect();

        $observaciones = $adulto->observaciones()->latest()->get();
        $observacionesAnuladas = collect();

        $documentos = $adulto->documentos()->latest()->get();
        $documentosArchivados = collect();

        $familiares = $adulto->familiares()->wherePivot('estado', 'ACTIVO')->get();
        $familiaresInactivos = $adulto->familiares()->wherePivot('estado', 'INACTIVO')->get();

        // Medical modules (Fase 2 additions)
        $fichasMedicas = $adulto->fichasMedicas()->latest()->get();
        $medicaciones = $adulto->medicaciones()->with('receta')->latest()->get();
        $administracionesMedicacion = $adulto->administracionesMedicacion()->latest('fecha')->latest('hora_programada')->get();
        $signosVitales = $adulto->signosVitales()->latest('fecha')->latest('hora')->get();
        $valoracionesFuncionales = $adulto->valoracionesFuncionales()->latest('fecha_valoracion')->get();
        $historialEstados = $adulto->historialEstados()->with(['estadoAnteriorRelacion', 'estadoNuevoRelacion'])->latest('fecha_cambio')->get();

        // Bitácora específica para este adulto mayor usando Spatie Activitylog
        $bitacora = Activity::where('subject_type', AdultoMayor::class)
            ->where('subject_id', $adulto->cod_am)
            ->orWhere(function ($query) use ($adulto) {
                $query->where('properties->cod_am', $adulto->cod_am);
            })
            ->latest()
            ->take(50)
            ->get();

        // Métricas para el resumen final
        $resumen = [
            'total_familiares' => $familiares->count(),
            'total_observaciones' => $observaciones->count(),
            'total_atenciones' => $atenciones->count(),
            'total_actividades' => $actividades->count(),
            'total_documentos' => $documentos->count(),
            'total_evaluaciones' => $evaluaciones->count(),
            'ultima_fecha_seguimiento' => $observaciones->first()?->fecha ?? $atenciones->first()?->fecha ?? 'Sin seguimiento reciente',
        ];

        $viewData = [
            'adulto' => $adulto,
            'evaluaciones' => $evaluaciones,
            'evaluacionesAnuladas' => $evaluacionesAnuladas,
            'atenciones' => $atenciones,
            'atencionesAnuladas' => $atencionesAnuladas,
            'actividades' => $actividades,
            'actividadesAnuladas' => $actividadesAnuladas,
            'observaciones' => $observaciones,
            'observacionesAnuladas' => $observacionesAnuladas,
            'documentos' => $documentos,
            'documentosArchivados' => $documentosArchivados,
            'familiares' => $familiares,
            'familiaresInactivos' => $familiaresInactivos,
            'bitacora' => $bitacora,
            'resumen' => $resumen,
            'fichasMedicas' => $fichasMedicas,
            'medicaciones' => $medicaciones,
            'administracionesMedicacion' => $administracionesMedicacion,
            'signosVitales' => $signosVitales,
            'valoracionesFuncionales' => $valoracionesFuncionales,
            'historialEstados' => $historialEstados,
        ];

        // Register Activity Log for Report Generation
        activity()
            ->causedBy(auth()->user())
            ->performedOn($adulto)
            ->event('reporte_generado')
            ->log('Se generó el reporte individual integral del adulto mayor.');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pages.adultos-mayores.reportes.pdf_individual', $viewData)->setPaper('a4', 'portrait');

            return $pdf->download("Expediente_Integral_{$adulto->cod_am}.pdf");
        }

        if ($format === 'excel') {
            activity()->causedBy(auth()->user())->performedOn($adulto)->event('reporte_generado')
                ->log('Se descargó el expediente completo en formato Excel.');

            return Excel::download(new AdultoIndividualExport($adulto), "Expediente_{$adulto->cod_am}.xlsx");
        }

        if ($format === 'word') {
            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => "attachment; filename=\"Ficha_{$adulto->cod_am}.doc\"",
            ];
            $content = view('pages.adultos-mayores.reportes.word_individual', $viewData)->render();

            return response($content, 200, $headers);
        }

        // Vista previa HTML
        return view('pages.adultos-mayores.reportes.pdf_individual', $viewData);
    }

    public function reporteEspecifico(Request $request, AdultoMayor $adulto_mayor, $tipo)
    {
        $format = $request->query('format', 'html');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $adulto = $this->adultoMayorService->obtenerDetalle($adulto_mayor->cod_am);

        $viewData = [
            'adulto' => $adulto,
            'tipo' => $tipo,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        // Función ayudante para aplicar el filtro de fechas
        $applyDateFilter = function ($query, $dateColumn) use ($startDate, $endDate) {
            if ($startDate) {
                $query->where($dateColumn, '>=', Carbon::parse($startDate)->startOfDay());
            }
            if ($endDate) {
                $query->where($dateColumn, '<=', Carbon::parse($endDate)->endOfDay());
            }

            return $query;
        };

        switch ($tipo) {
            case 'medico':
                $viewData['fichas'] = $adulto->fichasMedicas()->latest()->get(); // La ficha no suele filtrarse por fecha
                $viewData['atenciones'] = $applyDateFilter($adulto->atenciones()->with('tipoAtencion'), 'fecha')->latest()->get();
                $titulo = 'Reporte Médico';
                break;
            case 'medicacion':
                $viewData['medicaciones'] = $applyDateFilter($adulto->medicaciones()->with('receta'), 'fecha_inicio')->latest()->get();
                $viewData['administraciones'] = $applyDateFilter($adulto->administracionesMedicacion(), 'fecha')->latest('fecha')->latest('hora_programada')->get();
                $titulo = 'Reporte de Medicación';
                break;
            case 'signos':
                $viewData['signosVitales'] = $applyDateFilter($adulto->signosVitales(), 'fecha')->latest('fecha')->latest('hora')->get();
                $titulo = 'Reporte de Signos Vitales';
                break;
            case 'funcional':
                $viewData['valoraciones'] = $applyDateFilter($adulto->valoracionesFuncionales(), 'fecha_valoracion')->latest('fecha_valoracion')->get();
                $titulo = 'Reporte de Valoración Funcional Institucional';
                break;
            case 'cognitivo':
                $viewData['evaluaciones'] = $applyDateFilter($adulto->evaluacionesGeriatricas()->with(['instrumento', 'registrador']), 'fecha_eval')->latest('fecha_eval')->get();
                $titulo = 'Historial de Evaluaciones Cognitivas';
                break;
            default:
                abort(404, 'Tipo de reporte no válido');
        }

        $viewData['titulo'] = $titulo;

        $logText = "Se generó el {$titulo} del residente.";
        if ($startDate || $endDate) {
            $logText .= ' Filtros aplicados: '.($startDate ?: 'Inicio').' al '.($endDate ?: 'Actualidad').'.';
        }

        // Register Activity Log for Report Generation
        activity()
            ->causedBy(auth()->user())
            ->performedOn($adulto)
            ->event('reporte_generado')
            ->log($logText);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pages.adultos-mayores.reportes.pdf_especifico', $viewData)->setPaper('a4', 'portrait');

            return $pdf->download("{$titulo}_{$adulto->cod_am}.pdf");
        }

        return view('pages.adultos-mayores.reportes.pdf_especifico', $viewData);
    }

    public function reporteGeneral()
    {
        $adultos = AdultoMayor::with('estado')->get();

        $totalObservaciones = ObsAdulto::count();
        $totalAtenciones = AtencionAdulto::count();
        $totalActividades = ActividadAdulto::count();
        $totalDocumentos = DocumentoAdultoMayor::count();
        $totalEvaluaciones = EvaluacionGeriatrica::count();

        // Adultos sin seguimiento (sin observaciones ni atenciones en los últimos 30 días)
        $hace30Dias = now()->subDays(30);
        $sinSeguimiento = AdultoMayor::whereDoesntHave('observaciones', function ($q) use ($hace30Dias) {
            $q->where('fecha', '>=', $hace30Dias);
        })->whereDoesntHave('atenciones', function ($q) use ($hace30Dias) {
            $q->where('fecha', '>=', $hace30Dias);
        })->count();

        $stats = [
            'total' => $adultos->count(),
            'activos' => $adultos->where('cod_est_adul', 1)->count(),
            'archivados' => $adultos->where('cod_est_adul', 2)->count(),
            'hombres' => $adultos->where('genero', 'MASCULINO')->count(),
            'mujeres' => $adultos->where('genero', 'FEMENINO')->count(),
            'promedio_edad' => round($adultos->avg(fn ($a) => $this->adultoMayorService->calcularEdad($a->fecha_nac))),
            'obs_totales' => $totalObservaciones,
            'aten_totales' => $totalAtenciones,
            'act_totales' => $totalActividades,
            'doc_totales' => $totalDocumentos,
            'eval_totales' => $totalEvaluaciones,
            'sin_seguimiento' => $sinSeguimiento,
            'ultimos_adultos' => AdultoMayor::latest()->take(5)->get(),
        ];

        return view('pages.adultos-mayores.reportes.general', compact('adultos', 'stats'));
    }

    public function reporteInstitucional()
    {
        // Este reporte es más orientado a impacto y estadísticas globales
        $adultos = AdultoMayor::all();
        $totalAtenciones = AtencionAdulto::count();
        $totalActividades = ActividadAdulto::count();
        $totalEvaluaciones = EvaluacionGeriatrica::count();

        $stats = [
            'poblacion' => $adultos->count(),
            'impacto_salud' => $totalAtenciones,
            'impacto_social' => $totalActividades,
            'seguimiento_cognitivo' => $totalEvaluaciones,
            'efectividad' => 98, // Dato representativo
        ];

        return view('pages.adultos-mayores.reportes.institucional', compact('stats'));
    }

    public function reporteBienestar()
    {
        $adultos = AdultoMayor::all();
        $totalEvaluaciones = EvaluacionGeriatrica::count();

        $distribucionRiesgo = [
            'bajo' => EvaluacionGeriatrica::where('nivel_riesgo', 'BAJO')->count(),
            'medio' => EvaluacionGeriatrica::where('nivel_riesgo', 'MEDIO')->count(),
            'alto' => EvaluacionGeriatrica::where('nivel_riesgo', 'ALTO')->count(),
        ];

        return view('pages.adultos-mayores.reportes.bienestar', compact('distribucionRiesgo', 'totalEvaluaciones'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdultoMayor $adulto_mayor)
    {
        $adulto = $this->adultoMayorService->obtenerDetalle($adulto_mayor->cod_am);
        $estadosAdulto = $this->adultoMayorService->obtenerEstados();

        return view('pages.adultos-mayores.edit', compact('adulto', 'estadosAdulto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdultoMayorRequest $request, AdultoMayor $adulto_mayor)
    {
        $this->adultoMayorService->actualizarAdultoMayor($adulto_mayor->cod_am, $request->validated(), $request->file('foto'));

        return redirect()->route('admin.adultos-mayores.show', $adulto_mayor->cod_am)
            ->with('success', 'Ficha actualizada correctamente.');
    }

    /**
     * Archivar un registro (soft-disable).
     */
    public function archivar(Request $request, AdultoMayor $adulto_mayor)
    {
        $this->adultoMayorService->archivar($adulto_mayor->cod_am, $request->motivo);

        return redirect()->route('admin.adultos-mayores.index')
            ->with('success', 'Se archivó el registro del adulto mayor.');
    }

    /**
     * Restaurar un registro.
     */
    public function restaurar(AdultoMayor $adulto_mayor)
    {
        $this->adultoMayorService->restaurar($adulto_mayor->cod_am);

        return redirect()->route('admin.adultos-mayores.index')
            ->with('success', 'Se restauró el registro del adulto mayor.');
    }

    /**
     * Cambiar estado directamente (Integra historial_estado_adulto).
     */
    public function cambiarEstado(StoreCambioEstadoRequest $request, AdultoMayor $adulto_mayor)
    {
        try {
            \DB::beginTransaction();

            $estadoAnteriorId = $adulto_mayor->cod_est_adul;
            $nuevoEstadoId = $request->cod_est_adul;

            // Obtener el nombre del estado seleccionado para decidir si archivar
            $estadoSeleccionado = \DB::table('estado_adulto')
                ->where('cod_est_adul', $nuevoEstadoId)
                ->first();

            // 1. Actualizar Adulto Mayor
            $adulto_mayor->cod_est_adul = $nuevoEstadoId;

            $esArchivoOInactivo = $estadoSeleccionado &&
                in_array(strtoupper($estadoSeleccionado->estado), ['ARCHIVADO', 'INACTIVO']);

            if ($esArchivoOInactivo) {
                $adulto_mayor->archivado_en = Carbon::now();
                $adulto_mayor->motivo_archivado = 'Cambio de estado rápido';
            } else {
                $adulto_mayor->archivado_en = null;
                $adulto_mayor->motivo_archivado = null;
            }

            $adulto_mayor->save();

            // 2. Registrar Historial de Cambio
            $historial = HistorialEstadoAdulto::create([
                'cod_am' => $adulto_mayor->cod_am,
                'estado_anterior' => $estadoAnteriorId,
                'estado_nuevo' => $nuevoEstadoId,
                'fecha_cambio' => now(),
                'motivo' => $request->motivo,
                'documento_respaldo' => $request->documento_respaldo,
                'observacion' => $request->observacion,
                'cambiado_por' => auth()->user()->cod_usu,
            ]);

            \DB::commit();

            return redirect()->back()
                ->with('success', 'Se actualizó el estado correctamente y se guardó en el historial.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()->back()
                ->with('error', 'Error al cambiar de estado: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // No eliminar físicamente según reglas.
        return redirect()->back()->with('error', 'La eliminación física no está permitida. Use archivar.');
    }

    public function anularEvaluacionGeriatrica(Request $request, AdultoMayor $adulto_mayor, $evaluacionId)
    {
        $request->validate([
            'motivo_anulacion' => 'required|string|min:10',
        ], [
            'motivo_anulacion.required' => 'El motivo de anulación es obligatorio.',
            'motivo_anulacion.min' => 'El motivo de anulación debe tener al menos 10 caracteres.',
        ]);

        try {
            \DB::transaction(function () use ($request, $adulto_mayor, $evaluacionId) {
                $eval = EvaluacionGeriatrica::where('cod_am', $adulto_mayor->cod_am)
                    ->where('cod_eval_ger', $evaluacionId)
                    ->firstOrFail();

                $eval->update([
                    'estado_eval' => 'ANULADO',
                    'motivo_anulacion' => $request->input('motivo_anulacion'),
                    'anulado_por' => auth()->user()->cod_usu,
                    'anulado_en' => now(),
                ]);
            });

            return redirect()->back()
                ->with('success', 'Evaluación geriátrica anulada correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al anular la evaluación: '.$e->getMessage());
        }
    }

    public function pdfEvaluacionGeriatrica(AdultoMayor $adulto_mayor, $evaluacionId)
    {
        $adulto = $this->adultoMayorService->obtenerDetalle($adulto_mayor->cod_am);
        $adulto->edad = $this->adultoMayorService->calcularEdad($adulto->fecha_nac);

        $evaluacion = EvaluacionGeriatrica::where('cod_am', $adulto_mayor->cod_am)
            ->where('cod_eval_ger', $evaluacionId)
            ->with(['instrumento.area', 'registrador'])
            ->firstOrFail();

        $pdf = Pdf::loadView('pages.adultos-mayores.reportes.pdf_evaluacion_individual', compact('adulto', 'evaluacion'));

        return $pdf->stream("evaluacion_{$evaluacion->cod_eval_ger}.pdf");
    }
}
