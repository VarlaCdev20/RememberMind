<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Models\Alerta;
use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardPsicologo extends Component
{
    protected $listeners = ['evaluacion-geriatrica-guardada' => '$refresh'];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['SUPERADMINISTRADOR', 'PSICOLOGO/A']), 403);
    }

    public function render()
    {
        $usuario = auth()->user();
        $personal = $usuario->personal;
        $sinPersonalActivo = ! $personal || $personal->estado !== 'ACTIVO' || $personal->profesion !== 'PSICOLOGIA';
        $nombre = trim((string) ($usuario->nombres ?: $usuario->name));
        $primerNombre = explode(' ', $nombre)[0] ?: 'profesional';
        $hora = (int) now()->format('H');
        $saludo = $hora < 12 ? 'Buenos días' : ($hora < 20 ? 'Buenas tardes' : 'Buenas noches');

        $metrics = [];
        $panels = [];
        $recientes = [];
        $chartEvolucion = null;
        $chartInstrumentos = null;

        if (! $sinPersonalActivo) {
            $codigo = $personal->getKey();
            $atenciones = DB::table('atenciones')->where('cod_personal', $codigo);
            $aplicaciones = DB::table('aplicaciones_instrumento')->where('cod_personal', $codigo);
            $aplicacionesValidas = (clone $aplicaciones)->whereIn('aplicaciones_instrumento.estado', ['COMPLETA', 'COMPLETADA', 'VIGENTE']);
            $alertas = app(BandejaAlertas::class)->pendientes($usuario)->where('cod_personal_responsable', $codigo);
            $alertasAbiertas = (clone $alertas)->count();

            $metrics = [
                ['label' => 'Aplicaciones registradas', 'value' => (clone $aplicacionesValidas)->where('fecha_hora', '>=', now()->subDays(30))->count(), 'description' => 'Completas o vigentes · 30 días', 'icon' => 'ph-clipboard-text', 'variant' => 'sky'],
                ['label' => 'Atenciones de hoy', 'value' => (clone $atenciones)->whereDate('fecha_hora', today())->count(), 'description' => 'Registradas a tu nombre', 'icon' => 'ph-calendar-check', 'variant' => 'neutral'],
                ['label' => 'Alertas a tu cargo', 'value' => $alertasAbiertas, 'description' => 'Abiertas o en atención', 'icon' => 'ph-warning-circle', 'variant' => $alertasAbiertas > 0 ? 'critical' : 'neutral'],
            ];

            $proximas = (clone $atenciones)->join('residentes as r', 'r.cod_residente', '=', 'atenciones.cod_residente')
                ->where('atenciones.fecha_hora', '>=', now())
                ->whereIn('atenciones.estado', ['PROGRAMADA', 'PENDIENTE'])
                ->orderBy('atenciones.fecha_hora')->limit(5)
                ->get(['r.nombres', 'r.apellido_paterno', 'atenciones.tipo_atencion', 'atenciones.fecha_hora'])
                ->map(fn ($fila) => [
                    'label' => trim($fila->nombres.' '.$fila->apellido_paterno),
                    'detail' => trim((string) $fila->tipo_atencion).' · '.date('d/m/Y H:i', strtotime($fila->fecha_hora)),
                ])->all();

            $prioritarias = (clone $alertas)->whereIn('prioridad', Alerta::PRIORIDADES_ALTAS)
                ->orderByDesc('fecha_hora')->limit(5)->get(['titulo', 'prioridad', 'fecha_hora'])
                ->map(fn ($fila) => [
                    'label' => $fila->titulo ?: 'Alerta pendiente de revisión',
                    'detail' => $fila->prioridad.' · '.date('d/m/Y H:i', strtotime($fila->fecha_hora)),
                ])->all();

            $recientes = (clone $aplicacionesValidas)->join('residentes as r', 'r.cod_residente', '=', 'aplicaciones_instrumento.cod_residente')
                ->join('instrumentos as i', 'i.cod_instrumento', '=', 'aplicaciones_instrumento.cod_instrumento')
                ->orderByDesc('aplicaciones_instrumento.fecha_hora')->limit(5)
                ->get(['r.nombres', 'r.apellido_paterno', 'i.nombre as instrumento', 'aplicaciones_instrumento.fecha_hora'])
                ->map(fn ($fila) => [
                    'residente' => trim($fila->nombres.' '.$fila->apellido_paterno),
                    'instrumento' => $fila->instrumento,
                    'fecha' => date('d/m/Y H:i', strtotime($fila->fecha_hora)),
                    'fecha_iso' => date('c', strtotime($fila->fecha_hora)),
                ])->all();

            // Solo actividad clínica finalizada en las gráficas; el resto permanece en sus registros.
            $fechaCorte = now()->subMonths(5)->startOfMonth();
            $atencionesRegs = (clone $atenciones)->where('estado', 'FINALIZADA')
                ->whereBetween('fecha_hora', [$fechaCorte, now()])->get(['fecha_hora']);
            $aplicacionesCompletadas = (clone $aplicacionesValidas)
                ->whereBetween('aplicaciones_instrumento.fecha_hora', [$fechaCorte, now()]);
            $aplicacionesRegs = (clone $aplicacionesCompletadas)->get(['fecha_hora']);

            $labelsMeses = [];
            $datosAtenciones = [];
            $datosAplicaciones = [];
            for ($m = 5; $m >= 0; $m--) {
                $f = now()->subMonths($m);
                $k = $f->format('Y-m');
                $labelsMeses[] = ucfirst($f->translatedFormat('M Y'));
                $datosAtenciones[] = $atencionesRegs->filter(fn ($r) => substr((string) $r->fecha_hora, 0, 7) === $k)->count();
                $datosAplicaciones[] = $aplicacionesRegs->filter(fn ($r) => substr((string) $r->fecha_hora, 0, 7) === $k)->count();
            }

            $chartEvolucion = [
                'labels' => $labelsMeses,
                'atenciones' => $datosAtenciones,
                'aplicaciones' => $datosAplicaciones,
                'tieneDatos' => (array_sum($datosAtenciones) + array_sum($datosAplicaciones)) > 0,
            ];

            $instrumentos = (clone $aplicacionesCompletadas)
                ->join('instrumentos as i', 'i.cod_instrumento', '=', 'aplicaciones_instrumento.cod_instrumento')
                ->select('i.nombre')->selectRaw('COUNT(*) as total')
                ->groupBy('i.cod_instrumento', 'i.nombre')
                ->orderByDesc('total')->orderBy('i.nombre')->get();
            $principales = $instrumentos->take(4);
            $otros = $instrumentos->skip(4)->sum('total');
            $cantidadOtros = $instrumentos->skip(4)->count();
            $chartInstrumentos = [
                'labels' => $principales->pluck('nombre')->all(),
                'data' => $principales->pluck('total')->map(fn ($cantidad) => (int) $cantidad)->all(),
                'total' => (int) $instrumentos->sum('total'),
            ];
            if ($otros > 0) {
                $chartInstrumentos['labels'][] = 'Otros '.$cantidadOtros.' '.($cantidadOtros === 1 ? 'instrumento' : 'instrumentos');
                $chartInstrumentos['data'][] = (int) $otros;
            }

            if ($prioritarias !== []) {
                $panels[] = ['title' => 'Alertas prioritarias a tu cargo', 'type' => 'list', 'items' => $prioritarias, 'icon' => 'ph-warning-circle'];
            }
            $panels[] = ['title' => 'Próximas atenciones', 'type' => 'timeline', 'items' => $proximas, 'empty' => 'No hay atenciones futuras registradas a tu nombre.', 'icon' => 'ph-calendar-check'];
        }

        return view('livewire.valoraciones.dashboard-psicologo', compact(
            'saludo', 'primerNombre', 'sinPersonalActivo', 'metrics', 'panels', 'recientes', 'chartEvolucion', 'chartInstrumentos'
        ))->layout('layouts.sistema');
    }
}
