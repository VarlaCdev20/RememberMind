<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Residentes\UpdateAdultoMayorRequest;
use App\Models\AdultoMayor;
use App\Models\AplicacionInstrumento;
use App\Services\Reportes\AdultoMayorBitacoraService;
use App\Services\Residentes\AdultoMayorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        return view('pages.adultos-mayores.index');
    }

    /**
     * Display the specified resource.
     */
        public function show(AdultoMayor $adulto_mayor)
    {
        $adulto = $this->adultoMayorService->obtenerDetalle($adulto_mayor->cod_residente);

        // Contactos / Familiares
        $familiaresActivos = $adulto->contactos()->wherePivot('estado', 'ACTIVO')->get();
        $familiaresInactivos = $adulto->contactos()->wherePivot('estado', 'INACTIVO')->get();

        // Notas Clínicas / Observaciones
        $observacionesActivas = $adulto->observaciones()->where('estado', 'ACTIVO')->orderByDesc('fecha_hora')->get();
        $observacionesAnuladas = $adulto->observaciones()->where('estado', 'ANULADO')->orderByDesc('fecha_hora')->get();

        // Actividades
        $actividadesActivas = $adulto->actividades()->orderByDesc('fecha_hora')->get();
        $actividadesAnuladas = collect();

        // Atenciones
        $atencionesActivas = $adulto->atenciones()->where('estado', '!=', 'ANULADO')->orderByDesc('fecha_hora')->get();
        $atencionesAnuladas = $adulto->atenciones()->where('estado', 'ANULADO')->orderByDesc('fecha_hora')->get();

        // Documentos
        $documentosActivos = $adulto->documentos()->where('estado', '!=', 'ARCHIVADO')->orderByDesc('cod_documento')->get();
        $documentosArchivados = $adulto->documentos()->where('estado', 'ARCHIVADO')->orderByDesc('cod_documento')->get();

        // Evaluaciones / Instrumentos V2
        $evaluacionesActivas = $adulto->aplicacionesInstrumento()->with('instrumento')->orderByDesc('fecha_hora')->get();
        $evaluacionesAnuladas = collect();

        $evaluacionesGeriatricasActivas = $evaluacionesActivas;
        $evaluacionesGeriatricasAnuladas = collect();
        $areasGeriatricas = \App\Models\Area::where('estado', 'ACTIVO')->get();

        // FASE 3: Módulos médicos V2
        $fichasMedicas = $adulto->atenciones()->orderByDesc('fecha_hora')->get();
        $medicaciones = $adulto->prescripciones()->with('medicamento')->get();
        $administracionesMedicacion = $adulto->administracionesMedicacion()->orderByDesc('fecha_hora_programada')->get();
        $signosVitales = $adulto->signosVitales()->orderByDesc('fecha_hora')->get();
        $valoracionesFuncionales = $adulto->valoracionesFuncionales()->orderByDesc('fecha_hora')->get();
        $historialEstados = $adulto->historialEstados()->orderByDesc('fecha_hora')->get();

        $estadosAdulto = $this->adultoMayorService->obtenerEstados();
                $tiposAtenciones = collect([
            (object)['cod_tipo_aten' => 'MEDICA', 'tipo' => 'Médica', 'descripcion' => 'Consulta y control médico'],
            (object)['cod_tipo_aten' => 'ENFERMERIA', 'tipo' => 'Enfermería', 'descripcion' => 'Cuidados y signos vitales'],
            (object)['cod_tipo_aten' => 'PSICOLOGIA', 'tipo' => 'Psicológica', 'descripcion' => 'Evaluación y apoyo emocional'],
            (object)['cod_tipo_aten' => 'NUTRICION', 'tipo' => 'Nutricional', 'descripcion' => 'Control dietético y peso'],
            (object)['cod_tipo_aten' => 'FISIOTERAPIA', 'tipo' => 'Fisioterapia', 'descripcion' => 'Rehabilitación física'],
            (object)['cod_tipo_aten' => 'SOCIAL', 'tipo' => 'Trabajo Social', 'descripcion' => 'Vínculo familiar y social'],
        ]);
        $tiposEvaluaciones = \App\Models\Instrumento::where('estado', 'ACTIVO')->get();
        $tiposActividades = collect([
            (object)['cod_tipo_act' => 'RECREATIVA', 'tipo' => 'Recreativa'],
            (object)['cod_tipo_act' => 'COGNITIVA', 'tipo' => 'Cognitiva'],
            (object)['cod_tipo_act' => 'FISICA', 'tipo' => 'Física'],
            (object)['cod_tipo_act' => 'CULTURAL', 'tipo' => 'Cultural'],
            (object)['cod_tipo_act' => 'TERAPEUTICA', 'tipo' => 'Terapéutica'],
        ]);

        // Bitácora escalable desde Spatie Activitylog
        $bitacora = $this->bitacoraService->obtenerBitacora($adulto_mayor->cod_residente, 60);
        $eventosFiltro = $this->bitacoraService->obtenerTiposEventos($adulto_mayor->cod_residente);

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
        $format = $request->query('format', 'html');

        $adulto = $this->adultoMayorService->obtenerDetalle($adulto_mayor->cod_residente);
        $adulto->edad = $this->adultoMayorService->calcularEdad($adulto->fecha_nacimiento);

        $evaluaciones = $adulto->aplicacionesInstrumento()->with('instrumento')->orderByDesc('fecha_hora')->get();
        $evaluacionesAnuladas = collect();

        $atenciones = $adulto->atenciones()->orderByDesc('fecha_hora')->get();
        $atencionesAnuladas = collect();

        $actividades = $adulto->actividades()->orderByDesc('fecha_hora')->get();
        $actividadesAnuladas = collect();

        $observaciones = $adulto->observaciones()->orderByDesc('fecha_hora')->get();
        $observacionesAnuladas = collect();

        $documentos = $adulto->documentos()->orderByDesc('cod_documento')->get();
        $documentosArchivados = collect();

        $familiares = $adulto->contactos()->wherePivot('estado', 'ACTIVO')->get();
        $familiaresInactivos = $adulto->contactos()->wherePivot('estado', 'INACTIVO')->get();

        $fichasMedicas = $adulto->atenciones()->orderByDesc('fecha_hora')->get();
        $medicaciones = $adulto->prescripciones()->with('medicamento')->get();
        $administracionesMedicacion = $adulto->administracionesMedicacion()->orderByDesc('fecha_hora_programada')->get();
        $signosVitales = $adulto->signosVitales()->orderByDesc('fecha_hora')->get();
        $valoracionesFuncionales = $adulto->valoracionesFuncionales()->orderByDesc('fecha_hora')->get();
        $historialEstados = $adulto->historialEstados()->orderByDesc('fecha_hora')->get();

        $bitacora = \Spatie\Activitylog\Models\Activity::where('subject_id', $adulto->cod_residente)
            ->orWhere('description', 'like', '%' . $adulto->cod_residente . '%')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        $resumen = [
            'total_familiares' => $familiares->count(),
            'total_observaciones' => $observaciones->count(),
            'total_atenciones' => $atenciones->count(),
            'total_actividades' => $actividades->count(),
            'total_documentos' => $documentos->count(),
            'total_evaluaciones' => $evaluaciones->count(),
            'ultima_fecha_seguimiento' => $observaciones->first()?->fecha_hora?->format('d/m/Y') ?? 'Sin seguimiento reciente',
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

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pages.adultos-mayores.reportes.pdf_individual', $viewData)->setPaper('a4', 'portrait');
            $nombreArchivo = \Illuminate\Support\Str::slug($adulto->nombre_completo, '_');
            return $pdf->download("Expediente_Integral_{$nombreArchivo}.pdf");
        }

        return view('pages.adultos-mayores.reportes.pdf_individual', $viewData);
    }

    public function reporteEspecifico(Request $request, AdultoMayor $adulto_mayor, $tipo)
    {
        $format = $request->query('format', 'html');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $adulto = $this->adultoMayorService->obtenerDetalle($adulto_mayor->cod_residente);

        $viewData = [
            'adulto' => $adulto,
            'tipo' => $tipo,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $applyDateFilter = function ($query, $dateColumn = 'fecha_hora') use ($startDate, $endDate) {
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
                $viewData['fichas'] = $adulto->atenciones()->orderByDesc('fecha_hora')->get();
                $viewData['atenciones'] = $applyDateFilter($adulto->atenciones())->orderByDesc('fecha_hora')->get();
                $titulo = 'Reporte Médico';
                break;
            case 'medicacion':
                $viewData['medicaciones'] = $adulto->prescripciones()->with('medicamento')->get();
                $viewData['administraciones'] = $applyDateFilter($adulto->administracionesMedicacion(), 'fecha_hora_programada')->orderByDesc('fecha_hora_programada')->get();
                $titulo = 'Reporte de Medicación';
                break;
            case 'signos':
                $viewData['signos'] = $viewData['signosVitales'] = $applyDateFilter($adulto->signosVitales())->orderByDesc('fecha_hora')->get();
                $titulo = 'Reporte de Signos Vitales';
                break;
            case 'funcional':
                $viewData['valoraciones'] = $applyDateFilter($adulto->valoracionesFuncionales())->orderByDesc('fecha_hora')->get();
                $titulo = 'Reporte de Valoraciones Funcionales';
                break;
            default:
                $titulo = 'Reporte Clínico General';
                break;
        }

        $viewData['titulo'] = $titulo;

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('pages.adultos-mayores.reportes.pdf_especifico', $viewData);
            return $pdf->download("reporte_{$tipo}_{$adulto->cod_residente}.pdf");
        }

        return view('pages.adultos-mayores.reportes.pdf_especifico', $viewData);
    }

    public function reporteGeneral()
    {
        $adultos = \App\Models\Residente::all();

        $totalObservaciones = \App\Models\NotaClinica::count();
        $totalAtenciones = \App\Models\Atencion::count();
        $totalActividades = \App\Models\Actividad::count();
        $totalDocumentos = \App\Models\Documento::count();
        $totalEvaluaciones = \App\Models\AplicacionInstrumento::count();

        $hace30Dias = now()->subDays(30);
        $sinSeguimiento = \App\Models\Residente::whereDoesntHave('observaciones', function ($q) use ($hace30Dias) {
            $q->where('fecha_hora', '>=', $hace30Dias);
        })->whereDoesntHave('atenciones', function ($q) use ($hace30Dias) {
            $q->where('fecha_hora', '>=', $hace30Dias);
        })->count();

        $stats = [
            'total' => $adultos->count(),
            'activos' => $adultos->where('estado', 'ACTIVO')->count(),
            'archivados' => $adultos->where('estado', 'INACTIVO')->count(),
            'hombres' => $adultos->where('genero', 'MASCULINO')->count(),
            'mujeres' => $adultos->where('genero', 'FEMENINO')->count(),
            'promedio_edad' => round($adultos->avg(fn ($a) => $this->adultoMayorService->calcularEdad($a->fecha_nacimiento))),
            'obs_totales' => $totalObservaciones,
            'aten_totales' => $totalAtenciones,
            'act_totales' => $totalActividades,
            'doc_totales' => $totalDocumentos,
            'eval_totales' => $totalEvaluaciones,
            'sin_seguimiento' => $sinSeguimiento,
            'ultimos_adultos' => \App\Models\Residente::orderByDesc('cod_residente')->take(5)->get(),
        ];

        return view('pages.adultos-mayores.reportes.general', compact('adultos', 'stats'));
    }

    public function reporteInstitucional()
    {
        $adultos = \App\Models\Residente::all();
        $totalAtenciones = \App\Models\Atencion::count();
        $totalActividades = \App\Models\Actividad::count();
        $totalEvaluaciones = \App\Models\AplicacionInstrumento::count();

        $stats = [
            'poblacion' => $adultos->count(),
            'impacto_salud' => $totalAtenciones,
            'impacto_social' => $totalActividades,
            'seguimiento_cognitivo' => $totalEvaluaciones,
            'efectividad' => 98,
        ];

        return view('pages.adultos-mayores.reportes.institucional', compact('stats'));
    }

    public function reporteBienestar()
    {
        $adultos = \App\Models\Residente::all();
        $totalEvaluaciones = \App\Models\AplicacionInstrumento::count();

        $distribucionRiesgo = [
            'bajo' => \Illuminate\Support\Facades\DB::table('registros_movilidad')->where('riesgo_caida', 'BAJO')->count(),
            'medio' => \Illuminate\Support\Facades\DB::table('registros_movilidad')->where('riesgo_caida', 'MEDIO')->count(),
            'alto' => \Illuminate\Support\Facades\DB::table('registros_movilidad')->where('riesgo_caida', 'ALTO')->count(),
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
     * Cambiar estado directamente y registrar historial_estados_residente.
     */
    public function cambiarEstado(\Illuminate\Http\Request $request, AdultoMayor $adulto_mayor)
    {
        try {
            \DB::beginTransaction();

            $estadoAnterior = $adulto_mayor->estado;
            $nuevoEstado = $request->input('estado') ?? $request->input('cod_est_adul') ?? 'ACTIVO';
            if (is_numeric($nuevoEstado)) {
                $nuevoEstado = ((int)$nuevoEstado === 2 || (int)$nuevoEstado === 3) ? 'INACTIVO' : 'ACTIVO';
            }

            $adulto_mayor->estado = $nuevoEstado;
            $adulto_mayor->observacion = $request->motivo ?? $adulto_mayor->observacion;
            $adulto_mayor->save();

            \App\Models\HistorialEstadoResidente::create([
                'cod_historial_estado' => 'HER_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'cod_residente' => $adulto_mayor->cod_residente,
                'cod_usuario_registro' => auth()->id() ?? \App\Models\User::value('cod_usuario'),
                'estado_anterior' => $estadoAnterior ?? 'ACTIVO',
                'estado_nuevo' => $nuevoEstado,
                'fecha_hora' => now(),
                'motivo' => $request->motivo ?? 'Cambio de estado administrativo',
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
                $eval = AplicacionInstrumento::where('cod_residente', $adulto_mayor->cod_residente)
                    ->where('cod_aplicacion', $evaluacionId)
                    ->firstOrFail();

                $eval->update([
                    'estado' => 'ANULADA',
                    'observacion' => trim(($eval->observacion ?? '')."\nANULADA: ".$request->input('motivo_anulacion').' ('.auth()->id().', '.now()->toDateTimeString().')'),
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

        $evaluacion = AplicacionInstrumento::where('cod_residente', $adulto_mayor->cod_residente)
            ->where('cod_aplicacion', $evaluacionId)
            ->with(['instrumento', 'evaluador'])
            ->firstOrFail();

        $pdf = Pdf::loadView('pages.adultos-mayores.reportes.pdf_evaluacion_individual', compact('adulto', 'evaluacion'));

        return $pdf->stream("evaluacion_{$evaluacion->cod_eval_ger}.pdf");
    }
}
