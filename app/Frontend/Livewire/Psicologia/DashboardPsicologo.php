<?php

namespace App\Frontend\Livewire\Psicologia;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardPsicologo extends Component
{
    protected $listeners = ['evaluacion-geriatrica-guardada' => '$refresh'];

    public function mount(): void
    {
        if (! auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'PSICOLOGO/A'])) {
            abort(403, 'Acceso denegado. Solo personal de psicología autorizado.');
        }
    }

    public function render()
    {
        $personal = auth()->user()->personal;
        $metrics = [];
        $panels = [];

        if ($personal && $personal->estado === 'ACTIVO') {
            $code = $personal->cod_personal;
            $care = DB::table('atenciones')->where('cod_personal', $code);
            $applications = DB::table('aplicaciones_instrumento')->where('cod_personal', $code);
            $alerts = DB::table('alertas')->where('cod_personal_responsable', $code)
                ->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);

            $metrics = [
                ['label' => 'Residentes atendidos', 'value' => (clone $care)->where('fecha_hora', '>=', now()->subDays(90))->distinct('cod_residente')->count('cod_residente'), 'description' => 'Atenciones propias · 90 días', 'icon' => 'ph-users-three', 'variant' => 'mint'],
                ['label' => 'Instrumentos aplicados', 'value' => (clone $applications)->where('fecha_hora', '>=', now()->subDays(30))->count(), 'description' => 'Registros propios · 30 días', 'icon' => 'ph-clipboard-text', 'variant' => 'sky'],
                ['label' => 'Atenciones de hoy', 'value' => (clone $care)->whereDate('fecha_hora', today())->count(), 'description' => 'Registradas a tu nombre', 'icon' => 'ph-calendar-check', 'variant' => 'neutral'],
                ['label' => 'Alertas a tu cargo', 'value' => (clone $alerts)->count(), 'description' => 'Abiertas o en atención', 'icon' => 'ph-warning-circle', 'variant' => 'critical'],
            ];

            $recent = (clone $applications)->join('residentes as r', 'r.cod_residente', '=', 'aplicaciones_instrumento.cod_residente')
                ->join('instrumentos as i', 'i.cod_instrumento', '=', 'aplicaciones_instrumento.cod_instrumento')
                ->orderByDesc('aplicaciones_instrumento.fecha_hora')->limit(6)
                ->get(['r.nombres', 'r.apellido_paterno', 'i.nombre as instrumento', 'aplicaciones_instrumento.fecha_hora'])
                ->map(fn ($row) => ['label' => trim($row->nombres.' '.$row->apellido_paterno), 'detail' => $row->instrumento.' · '.date('d/m/Y H:i', strtotime($row->fecha_hora))])->all();
            $upcoming = (clone $care)->join('residentes as r', 'r.cod_residente', '=', 'atenciones.cod_residente')
                ->where('atenciones.fecha_hora', '>=', now())
                ->whereIn('atenciones.estado', ['PROGRAMADA', 'PENDIENTE'])
                ->orderBy('atenciones.fecha_hora')->limit(6)
                ->get(['r.nombres', 'r.apellido_paterno', 'atenciones.tipo_atencion', 'atenciones.fecha_hora'])
                ->map(fn ($row) => ['label' => trim($row->nombres.' '.$row->apellido_paterno), 'detail' => $row->tipo_atencion.' · '.date('d/m H:i', strtotime($row->fecha_hora))])->all();
            $priority = (clone $alerts)->whereIn('prioridad', ['CRITICO', 'CRITICA', 'ALTA'])
                ->orderByDesc('fecha_hora')->limit(6)->get(['titulo', 'prioridad', 'fecha_hora'])
                ->map(fn ($row) => ['label' => $row->titulo, 'detail' => $row->prioridad.' · '.date('d/m H:i', strtotime($row->fecha_hora))])->all();
            $instruments = (clone $applications)->join('instrumentos as i', 'i.cod_instrumento', '=', 'aplicaciones_instrumento.cod_instrumento')
                ->where('aplicaciones_instrumento.fecha_hora', '>=', now()->subDays(90))
                ->select('i.nombre as label')->selectRaw('COUNT(*) as total')
                ->groupBy('i.cod_instrumento', 'i.nombre')->orderByDesc('total')->limit(6)->get()
                ->map(fn ($row) => ['label' => $row->label, 'value' => (int) $row->total])->all();

            $panels = [
                ['title' => 'Próximas atenciones', 'type' => 'timeline', 'items' => $upcoming, 'empty' => 'No hay atenciones futuras registradas a tu nombre.', 'icon' => 'ph-calendar-check', 'span' => 'wide'],
                ['title' => 'Alertas prioritarias a tu cargo', 'type' => 'list', 'items' => $priority, 'empty' => 'No hay alertas prioritarias abiertas a tu cargo.', 'icon' => 'ph-warning-circle'],
                ['title' => 'Instrumentos recientes', 'type' => 'list', 'items' => $recent, 'empty' => 'Aún no hay instrumentos aplicados a tu nombre.', 'icon' => 'ph-clipboard-text', 'span' => 'wide'],
                ['title' => 'Instrumentos aplicados · 90 días', 'type' => 'bars', 'items' => $instruments, 'empty' => 'Sin aplicaciones registradas en el período.', 'icon' => 'ph-chart-bar'],
            ];
        } else {
            $panels[] = ['title' => 'Contexto profesional', 'type' => 'list', 'items' => [], 'empty' => 'No hay una vinculación de personal activo para consultar registros propios.', 'icon' => 'ph-identification-badge', 'span' => 'wide'];
        }

        return view('livewire.valoraciones.dashboard-psicologo', compact('metrics', 'panels'))
            ->layout('layouts.sistema');
    }
}
