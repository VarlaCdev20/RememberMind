<?php

namespace App\Livewire\Cuidados;

use App\Models\AdministracionMedicacion;
use App\Models\Residente;
use App\Models\Alerta;
use App\Models\EjecucionCuidado;
use App\Models\SignoVital;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReporteEnfermeria extends Component
{
    public string $desde = '', $hasta = '', $codResidente = '', $codAm = '', $tipo = 'TODOS';

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
        $targetId = $this->codResidente ?: $this->codAm;
        if ($targetId && in_array($targetId, $ids, true)) $ids = [$targetId];
        $cuidados = EjecucionCuidado::with(['residente', 'intervencion'])
            ->whereIn('cod_residente', $ids)
            ->whereBetween('fecha_hora_programada', [$this->desde, $this->hasta.' 23:59:59'])
            ->latest('fecha_hora_programada')
            ->get();
        $incidentes = Alerta::with('residente')
            ->whereIn('cod_residente', $ids)
            ->whereIn('tipo', ['INCIDENTE', 'CAIDA', 'EVENTO_ADVERSO'])
            ->whereBetween('fecha_hora', [$this->desde, $this->hasta.' 23:59:59'])
            ->latest('fecha_hora')
            ->get();
        $alertas = Alerta::query()
            ->whereIn('cod_residente', $ids)
            ->whereBetween('fecha_hora', [$this->desde, $this->hasta.' 23:59:59'])
            ->latest('fecha_hora')
            ->get();
        $medicaciones = AdministracionMedicacion::whereIn('cod_residente', $ids)
            ->whereBetween('fecha_hora_programada', [$this->desde, $this->hasta.' 23:59:59'])
            ->get();
        $signos = SignoVital::whereIn('cod_residente', $ids)->whereBetween('fecha_hora', [$this->desde, $this->hasta.' 23:59:59'])->get();
        $pacientes = Residente::whereIn('cod_residente', $turnos->obtenerPacientesAsignadosIds(Auth::user()))->orderBy('apellido_paterno')->get();

        $indicadores = [
            'cuidados' => $cuidados->count(),
            'signos' => $signos->count(),
            'dosis' => $medicaciones->filter(fn ($m) => $m->esAdministrada())->count(),
            'omisiones' => $medicaciones->filter(fn ($m) => $m->esOmitida())->count(),
            'incidentes' => $incidentes->count(),
            'alertas_abiertas' => $alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count(),
        ];

        $tendencia = collect();

        return view('livewire.cuidados.reporte-enfermeria', compact('pacientes', 'cuidados', 'incidentes', 'indicadores', 'tendencia', 'esSuperAdmin'))
            ->layout(request()->routeIs('admin.enfermeria.*') ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}