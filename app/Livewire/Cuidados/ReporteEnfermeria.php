<?php

namespace App\Livewire\Cuidados;

use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\IncidenteResidente;
use App\Models\RegistroCuidado;
use App\Models\SignosVitalesAdulto;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReporteEnfermeria extends Component
{
    public string $desde = '', $hasta = '', $codAm = '', $tipo = 'TODOS';

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('enfermeria.ver_dashboard'), 403);
        $this->desde = now()->subDays(7)->toDateString();
        $this->hasta = today()->toDateString();
    }

    public function render()
    {
        $turnos = app(TurnoEnfermeriaService::class);
        $esSuperAdmin = $turnos->esSuperAdmin(Auth::user());
        $ids = $turnos->obtenerPacientesAsignadosIds(Auth::user());
        if ($this->codAm && in_array($this->codAm, $ids, true)) $ids = [$this->codAm];
        $rango = fn ($q, $campo) => $q->whereIn('cod_am', $ids)->whereBetween($campo, [$this->desde, $this->hasta.' 23:59:59']);

        $cuidados = $rango(RegistroCuidado::with('adultoMayor'), 'fecha_hora_evento')->latest('fecha_hora_evento')->get();
        $incidentes = $rango(IncidenteResidente::with('adultoMayor'), 'fecha_hora_evento')->latest('fecha_hora_evento')->get();
        $alertas = $rango(AlertaAdulto::query(), 'created_at')->get();
        $medicaciones = AdministracionMedicacion::whereIn('cod_am', $ids)->whereBetween('fecha', [$this->desde, $this->hasta])->get();
        $signos = SignosVitalesAdulto::whereIn('cod_am', $ids)->whereBetween('fecha', [$this->desde, $this->hasta])->get();
        $pacientes = AdultoMayor::whereIn('cod_am', $turnos->obtenerPacientesAsignadosIds(Auth::user()))->orderBy('ap_paterno')->get();

        $indicadores = [
            'cuidados' => $cuidados->count(), 'signos' => $signos->count(),
            'dosis' => $medicaciones->where('administrado', true)->count(),
            'omisiones' => $medicaciones->where('administrado', false)->count(),
            'incidentes' => $incidentes->count(), 'alertas_abiertas' => $alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count(),
        ];

        $tendencia = $cuidados->groupBy(fn ($registro) => $registro->fecha_hora_evento->toDateString())
            ->map(function ($dia, $fecha) {
                $alimentacion = $dia->where('tipo', 'ALIMENTACION')->whereNotNull('porcentaje');
                return [
                    'fecha' => $fecha,
                    'alimentacion' => $alimentacion->isNotEmpty() ? round($alimentacion->avg('porcentaje')) : null,
                    'hidratacion' => (int) $dia->where('tipo', 'HIDRATACION')->sum('cantidad_ml'),
                    'dolor' => $dia->whereNotNull('dolor')->isNotEmpty() ? round($dia->whereNotNull('dolor')->avg('dolor'), 1) : null,
                ];
            })->sortKeys()->take(-14)->values();

        return view('livewire.cuidados.reporte-enfermeria', compact('pacientes', 'cuidados', 'incidentes', 'indicadores', 'tendencia', 'esSuperAdmin'))
            ->layout('layouts.sistema');
    }
}
