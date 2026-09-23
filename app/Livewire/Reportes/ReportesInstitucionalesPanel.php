<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use App\Models\Residente;
use App\Models\Documento;
use App\Models\Atencion;
use App\Models\SignoVital;
use App\Models\ValoracionFuncional;
use App\Models\AplicacionInstrumento;
use App\Models\NotaClinica;
use App\Models\HistorialEstadoResidente;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportesInstitucionalesPanel extends Component
{
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $filtroEstado = 'todos';
    public $tipoReporte = 'general'; // general, documental, red_de_apoyo, salud, evaluaciones, seguimiento, trazabilidad

    public function mount()
    {
        $this->fechaDesde = date('Y') . '-01-01';
        $this->fechaHasta = date('Y-m-d');
    }

    public function render()
    {
        // ── FILTROS Y POBLACIÓN BASE ──────────────────────────────
        $query = Residente::query();

        if ($this->filtroEstado !== 'todos') {
            $query->where('estado', $this->filtroEstado);
        }

        if (!empty($this->fechaDesde)) {
            $query->whereHas('admisiones', fn($q) => $q->whereDate('fecha_hora_admision', '>=', $this->fechaDesde));
        }

        if (!empty($this->fechaHasta)) {
            $query->whereHas('admisiones', fn($q) => $q->whereDate('fecha_hora_admision', '<=', $this->fechaHasta));
        }

        $adultosIds = $query->pluck('cod_residente');
        $totalAdultos = $query->count();

        // Obtener catálogo de estados para el filtro
        $estadosList = Residente::query()
            ->select('estado')
            ->distinct()
            ->orderBy('estado')
            ->get()
            ->map(fn($r) => (object)['cod_est_adul' => $r->estado, 'estado' => $r->estado]);

        // ── 1. REPORTE GENERAL ───────────────────────────────────
        $activos = $query->clone()->where('estado', 'ACTIVO')->count();
        $archivados = $query->clone()->whereIn('estado', ['INACTIVO', 'BAJA', 'FALLECIDO'])->count();

        // Distribución por rango de edad
        $menores65 = 0;
        $de65a75 = 0;
        $de76a85 = 0;
        $mayores85 = 0;
        
        $adultosNac = $query->clone()->select('fecha_nacimiento')->get();
        foreach ($adultosNac as $a) {
            if ($a->fecha_nacimiento) {
                $edad = Carbon::parse($a->fecha_nacimiento)->age;
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
        $conDocumentos = Documento::whereIn('cod_residente', $adultosIds)->distinct('cod_residente')->count('cod_residente');
        $sinDocumentos = max(0, $totalAdultos - $conDocumentos);
        
        $documentosActivos = Documento::whereIn('cod_residente', $adultosIds)->where('estado', 'ACTIVO')->count();
        $documentosAnulados = Documento::whereIn('cod_residente', $adultosIds)->where('estado', '<>', 'ACTIVO')->count();

        // ── 3. REPORTE RED DE APOYO ──────────────────────────────
        $conFamiliar = DB::table('residentes_contactos')->whereIn('cod_residente', $adultosIds)->distinct('cod_residente')->count('cod_residente');
        $sinFamiliar = max(0, $totalAdultos - $conFamiliar);

        $conResponsable = DB::table('residentes_contactos')
            ->whereIn('cod_residente', $adultosIds)
            ->where('responsable_principal', true)
            ->distinct('cod_residente')
            ->count('cod_residente');
        $sinResponsable = max(0, $totalAdultos - $conResponsable);

        $sinContactoEmergencia = $sinFamiliar;

        // ── 4. REPORTE SALUD Y CUIDADOS ──────────────────────────
        $conFichaMedica = Atencion::whereIn('cod_residente', $adultosIds)->distinct('cod_residente')->count('cod_residente');
        $sinFichaMedica = max(0, $totalAdultos - $conFichaMedica);
        $conSignosVitales = SignoVital::whereIn('cod_residente', $adultosIds)->distinct('cod_residente')->count('cod_residente');
        $conValoracionFuncional = ValoracionFuncional::whereIn('cod_residente', $adultosIds)->distinct('cod_residente')->count('cod_residente');

        // ── 5. EVALUACIONES GERIÁTRICAS ──────────────────────────
        $totalEvaluaciones = AplicacionInstrumento::whereIn('cod_residente', $adultosIds)->count();

        // Evaluaciones por área
        $evaluacionesPorArea = DB::table('aplicaciones_instrumento')
            ->join('instrumentos', 'aplicaciones_instrumento.cod_instrumento', '=', 'instrumentos.cod_instrumento')
            ->whereIn('aplicaciones_instrumento.cod_residente', $adultosIds)
            ->select('instrumentos.tipo as area_nombre', DB::raw('count(*) as total'))
            ->groupBy('instrumentos.tipo')
            ->pluck('total', 'area_nombre')
            ->toArray();

        // Evaluaciones por nivel de alerta
        $evaluacionesPorNivel = [
            'NORMAL' => $totalEvaluaciones,
            'PREVENTIVO' => 0,
            'CRITICO' => 0,
        ];

        $conEvaluacionIds = AplicacionInstrumento::whereIn('cod_residente', $adultosIds)->pluck('cod_residente')->unique();
        $sinEvaluacionGeriatrica = $adultosIds->diff($conEvaluacionIds)->count();

        // ── 6. REPORTE DE SEGUIMIENTO ────────────────────────────
        $observaciones = NotaClinica::whereIn('cod_residente', $adultosIds)->count();
        $observacionesAlta = NotaClinica::whereIn('cod_residente', $adultosIds)->where('tipo_nota', 'URGENCIA')->count();

        $atenciones = Atencion::whereIn('cod_residente', $adultosIds)->count();
        $actividades = DB::table('participantes_actividad')->whereIn('cod_residente', $adultosIds)->count();

        $atencionesPendientes = Atencion::whereIn('cod_residente', $adultosIds)->whereIn('estado', ['PROGRAMADA', 'EN_PROCESO'])->count();
        $actividadesPendientes = DB::table('participantes_actividad')
            ->join('actividades', 'participantes_actividad.cod_actividad', '=', 'actividades.cod_actividad')
            ->whereIn('participantes_actividad.cod_residente', $adultosIds)
            ->whereIn('actividades.estado', ['PROGRAMADA', 'ACTIVA'])
            ->count();

        $pendientesSeguimiento = $atencionesPendientes + $actividadesPendientes;

        // ── 7. REPORTE DE TRAZABILIDAD ───────────────────────────
        $cambiosEstado = HistorialEstadoResidente::whereIn('cod_residente', $adultosIds)->count();
        $actividadBitacora = DB::table('activity_log')->count();

        // ── DATOS PARA GRÁFICAS (CHART.JS) ────────────────────────
        $graficaEstados = DB::table('residentes')
            ->whereIn('cod_residente', $adultosIds)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $graficaEdades = [
            'Menores de 65' => $menores65,
            '65 a 75 años' => $de65a75,
            '76 a 85 años' => $de76a85,
            'Mayores de 85' => $mayores85,
        ];

        $graficaNiveles = $evaluacionesPorNivel;
        $graficaAreas = $evaluacionesPorArea;

        $graficaDocumentos = [
            'Digitalizados' => $documentosActivos,
            'Anulados / Archivados' => $documentosAnulados,
        ];

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

        return view('livewire.reportes.reportes-institucionales-panel', [
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
                'sinEvaluacion' => $sinEvaluacionGeriatrica,
                'observaciones' => $observaciones,
                'observacionesAlta' => $observacionesAlta,
                'atenciones' => $atenciones,
                'actividades' => $actividades,
                'pendientes' => $pendientesSeguimiento,
                'cambiosEstado' => $cambiosEstado,
                'bitacora' => $actividadBitacora,
            ],
            'graficaEstados' => $graficaEstados,
            'graficaEdades' => $graficaEdades,
            'graficaNiveles' => $graficaNiveles,
            'graficaAreas' => $graficaAreas,
            'graficaDocumentos' => $graficaDocumentos,
            'graficaSeguimiento' => $graficaSeguimiento,
        ]);
    }
}