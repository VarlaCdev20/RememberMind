<?php

namespace App\Livewire\Admin\AdultosMayores;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\EstadoAdulto;
use Illuminate\Support\Facades\DB;

class ReportesInstitucionalesPanel extends Component
{
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $filtroEstado = 'todos';
    public $tipoReporte = 'general'; // general, documental, red_de_apoyo, salud, evaluaciones, seguimiento, trazabilidad

    public function mount()
    {
        // Por defecto, inicializar con el año actual
        $this->fechaDesde = date('Y') . '-01-01';
        $this->fechaHasta = date('Y-m-d');
    }

    public function render()
    {
        // ── FILTROS Y POBLACIÓN BASE ──────────────────────────────
        $query = AdultoMayor::query();

        if ($this->filtroEstado !== 'todos') {
            $query->where('cod_est_adul', $this->filtroEstado);
        }

        if (!empty($this->fechaDesde)) {
            $query->where('fecha_ing', '>=', $this->fechaDesde);
        }

        if (!empty($this->fechaHasta)) {
            $query->where('fecha_ing', '<=', $this->fechaHasta);
        }

        $adultosIds = $query->pluck('cod_am');
        $totalAdultos = $query->count();

        // Obtener catálogo de estados para el filtro
        $estadosList = EstadoAdulto::all();

        // ── 1. REPORTE GENERAL ───────────────────────────────────
        $activos = $query->clone()->whereHas('estado', function ($q) {
            $q->whereRaw('UPPER(estado) = ?', ['ACTIVO']);
        })->count();

        $archivados = $query->clone()->whereHas('estado', function ($q) {
            $q->whereRaw('UPPER(estado) IN (?, ?)', ['ARCHIVADO', 'INACTIVO']);
        })->count();

        // Distribución por rango de edad
        $menores65 = 0;
        $de65a75 = 0;
        $de76a85 = 0;
        $mayores85 = 0;
        
        $adultosNac = $query->clone()->select('fecha_nac')->get();
        foreach ($adultosNac as $a) {
            if ($a->fecha_nac) {
                $edad = $a->fecha_nac->age;
                if ($edad < 65) $menores65++;
                elseif ($edad <= 75) $de65a75++;
                elseif ($edad <= 85) $de76a85++;
                else $mayores85++;
            }
        }

        // Distribución por género
        $generoM = $query->clone()->whereRaw('UPPER(genero) = ?', ['MASCULINO'])->count();
        $generoF = $query->clone()->whereRaw('UPPER(genero) = ?', ['FEMENINO'])->count();

        // ── 2. REPORTE DOCUMENTAL ────────────────────────────────
        $conDocumentos = AdultoMayor::whereIn('cod_am', $adultosIds)->has('documentos')->count();
        $sinDocumentos = AdultoMayor::whereIn('cod_am', $adultosIds)->doesntHave('documentos')->count();
        
        $documentosActivos = \App\Models\DocumentoAdultoMayor::whereIn('cod_am', $adultosIds)->count();
        $documentosAnulados = \App\Models\DocumentoAdultoMayor::whereIn('cod_am', $adultosIds)->onlyTrashed()->count();

        // ── 3. REPORTE RED DE APOYO ──────────────────────────────
        $conFamiliar = AdultoMayor::whereIn('cod_am', $adultosIds)->has('familiares')->count();
        $sinFamiliar = AdultoMayor::whereIn('cod_am', $adultosIds)->doesntHave('familiares')->count();

        $sinResponsable = AdultoMayor::whereIn('cod_am', $adultosIds)
            ->whereDoesntHave('familiares', function ($q) {
                $q->where('familiar_adulto.es_responsable', 1);
            })->count();

        $sinContactoEmergencia = AdultoMayor::whereIn('cod_am', $adultosIds)
            ->where(function ($q) {
                $q->whereNull('contacto_emergencia_nombre')
                  ->orWhere('contacto_emergencia_nombre', '')
                  ->orWhereNull('contacto_emergencia_celular')
                  ->orWhere('contacto_emergencia_celular', '');
            })->count();

        // ── 4. REPORTE SALUD Y CUIDADOS ──────────────────────────
        $conFichaMedica = AdultoMayor::whereIn('cod_am', $adultosIds)->has('fichasMedicas')->count();
        $sinFichaMedica = AdultoMayor::whereIn('cod_am', $adultosIds)->doesntHave('fichasMedicas')->count();
        $conSignosVitales = AdultoMayor::whereIn('cod_am', $adultosIds)->has('signosVitales')->count();
        $conValoracionFuncional = AdultoMayor::whereIn('cod_am', $adultosIds)->has('valoracionesFuncionales')->count();

        // ── 5. EVALUACIONES GERIÁTRICAS ──────────────────────────
        $totalEvaluaciones = \App\Models\EvaluacionGeriatrica::whereIn('cod_am', $adultosIds)
            ->whereNull('anulado_en')
            ->count();

        // Evaluaciones por área
        $evaluacionesPorArea = DB::table('evaluaciones_geriatricas')
            ->join('instrumentos_geriatricos', 'evaluaciones_geriatricas.cod_instrumento', '=', 'instrumentos_geriatricos.cod_instrumento')
            ->join('areas_geriatricas', 'instrumentos_geriatricos.cod_area', '=', 'areas_geriatricas.cod_area')
            ->whereIn('evaluaciones_geriatricas.cod_am', $adultosIds)
            ->whereNull('evaluaciones_geriatricas.anulado_en')
            ->select('areas_geriatricas.nombre as area_nombre', DB::raw('count(*) as total'))
            ->groupBy('areas_geriatricas.nombre')
            ->pluck('total', 'area_nombre')
            ->toArray();

        // Evaluaciones por nivel de alerta
        $evaluacionesPorNivel = \App\Models\EvaluacionGeriatrica::whereIn('cod_am', $adultosIds)
            ->whereNull('anulado_en')
            ->select('nivel_alerta', DB::raw('count(*) as total'))
            ->groupBy('nivel_alerta')
            ->pluck('total', 'nivel_alerta')
            ->toArray();

        // Sin evaluación geriátrica registrada
        $conEvaluacionIds = \App\Models\EvaluacionGeriatrica::whereNull('anulado_en')->pluck('cod_am')->unique();
        $sinEvaluacionGeriatrica = $adultosIds->diff($conEvaluacionIds)->count();

        // ── 6. REPORTE DE SEGUIMIENTO ────────────────────────────
        $observaciones = \App\Models\ObsAdulto::whereIn('cod_am', $adultosIds)->count();
        $observacionesAlta = \App\Models\ObsAdulto::whereIn('cod_am', $adultosIds)
            ->whereIn(DB::raw('UPPER(nivel_importancia)'), ['ALTA', 'URGENTE', 'PRIORITARIA'])
            ->count();

        $atenciones = \App\Models\AtencionAdulto::whereIn('cod_am', $adultosIds)->count();
        $actividades = \App\Models\ActividadAdulto::whereIn('cod_am', $adultosIds)->count();

        $atencionesPendientes = \App\Models\AtencionAdulto::whereIn('cod_am', $adultosIds)
            ->whereIn(DB::raw('UPPER(estado)'), ['PENDIENTE', 'PROGRAMADA'])
            ->count();

        $actividadesPendientes = \App\Models\ActividadAdulto::whereIn('cod_am', $adultosIds)
            ->whereIn(DB::raw('UPPER(estado)'), ['PENDIENTE', 'PROGRAMADA'])
            ->count();

        $pendientesSeguimiento = $atencionesPendientes + $actividadesPendientes;

        // ── 7. REPORTE DE TRAZABILIDAD ───────────────────────────
        $cambiosEstado = \App\Models\HistorialEstadoAdulto::whereIn('cod_am', $adultosIds)->count();
        $actividadBitacora = \Spatie\Activitylog\Models\Activity::where('subject_type', AdultoMayor::class)
            ->whereIn('subject_id', $adultosIds)
            ->count();

        // ── DATOS PARA GRÁFICAS (CHART.JS) ────────────────────────
        // Adultos por estado institucional
        $graficaEstados = [];
        $distribucionEstados = DB::table('adulto_mayor')
            ->join('estado_adulto', 'adulto_mayor.cod_est_adul', '=', 'estado_adulto.cod_est_adul')
            ->whereIn('adulto_mayor.cod_am', $adultosIds)
            ->select('estado_adulto.estado', DB::raw('count(*) as total'))
            ->groupBy('estado_adulto.estado')
            ->get();
        foreach ($distribucionEstados as $de) {
            $graficaEstados[$de->estado] = $de->total;
        }

        // Adultos por rango de edad
        $graficaEdades = [
            'Menores de 65' => $menores65,
            '65 a 75 años' => $de65a75,
            '76 a 85 años' => $de76a85,
            'Mayores de 85' => $mayores85,
        ];

        // Evaluaciones por nivel de alerta
        $graficaNiveles = [
            'NORMAL' => $evaluacionesPorNivel['NORMAL'] ?? 0,
            'PREVENTIVO' => $evaluacionesPorNivel['PREVENTIVO'] ?? 0,
            'CRITICO' => $evaluacionesPorNivel['CRITICO'] ?? 0,
        ];

        // Evaluaciones por área
        $graficaAreas = $evaluacionesPorArea;

        // Documentos activos vs archivados
        $graficaDocumentos = [
            'Digitalizados' => $documentosActivos,
            'Anulados / Archivados' => $documentosAnulados,
        ];

        // Seguimiento por tipo de evento
        $graficaSeguimiento = [
            'Observaciones' => $observaciones,
            'Atenciones' => $atenciones,
            'Actividades' => $actividades,
        ];

        // ── INDICADORES GENERALES SUPERIORES ──────────────────────
        $indicadores = [
            'total' => $totalAdultos,
            'activos' => $activos,
            'sin_documentos' => $sinDocumentos,
            'sin_evaluacion' => $sinEvaluacionGeriatrica,
            'urgentes' => $observacionesAlta,
            'pendientes' => $pendientesSeguimiento,
        ];

        return view('livewire.admin.adultos-mayores.reportes-institucionales-panel', [
            'estadosList' => $estadosList,
            'indicadores' => $indicadores,
            'stats' => [
                'total' => $totalAdultos,
                'activos' => $activos,
                'archivados' => $archivados,
                'hombres' => $generoM,
                'mujeres' => $generoF,
                'menores65' => $menores65,
                'de65a75' => $de65a75,
                'de76a85' => $de76a85,
                'mayores85' => $mayores85,
                'conDocumentos' => $conDocumentos,
                'sinDocumentos' => $sinDocumentos,
                'documentosActivos' => $documentosActivos,
                'documentosAnulados' => $documentosAnulados,
                'conFamiliar' => $conFamiliar,
                'sinFamiliar' => $sinFamiliar,
                'sinResponsable' => $sinResponsable,
                'sinContacto' => $sinContactoEmergencia,
                'conFicha' => $conFichaMedica,
                'sinFicha' => $sinFichaMedica,
                'conSignos' => $conSignosVitales,
                'conValoracion' => $conValoracionFuncional,
                'totalEvaluaciones' => $totalEvaluaciones,
                'evaluacionesPorArea' => $evaluacionesPorArea,
                'evaluacionesPorNivel' => $evaluacionesPorNivel,
                'sinEvaluacion' => $sinEvaluacionGeriatrica,
                'observaciones' => $observaciones,
                'observacionesAlta' => $observacionesAlta,
                'atenciones' => $atenciones,
                'actividades' => $actividades,
                'pendientesSeguimiento' => $pendientesSeguimiento,
                'cambiosEstado' => $cambiosEstado,
                'actividadBitacora' => $actividadBitacora,
            ],
            // Datos serializados para gráficas
            'graficaEstados' => $graficaEstados,
            'graficaEdades' => $graficaEdades,
            'graficaNiveles' => $graficaNiveles,
            'graficaAreas' => $graficaAreas,
            'graficaDocumentos' => $graficaDocumentos,
            'graficaSeguimiento' => $graficaSeguimiento,
        ])->layout('layouts.sistema');
    }
}
