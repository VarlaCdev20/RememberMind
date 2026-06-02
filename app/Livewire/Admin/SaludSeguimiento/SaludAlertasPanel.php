<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use App\Models\AdultoMayor;
use Illuminate\Support\Facades\Auth;

class SaludAlertasPanel extends Component
{
    public $filtroTipo = '';

    public function render()
    {
        // Cargamos solo adultos activos con relaciones acotadas para evitar timeout
        $adultos = AdultoMayor::query()
            ->visiblesClinicamentePara(Auth::user())
            ->with([
            'fichasMedicas'           => fn ($q) => $q->where('estado', 'ACTIVA')->latest()->limit(1),
            'medicaciones'            => fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO']),
            'administracionesMedicacion' => fn ($q) => $q->latest('fecha')->limit(3),
            'signosVitales'           => fn ($q) => $q->where('estado', 'VIGENTE')->latest('fecha')->limit(1),
            'valoracionesFuncionales' => fn ($q) => $q->latest('fecha_valoracion')->limit(1),
        ])
            ->whereHas('estado', fn ($q) => $q->whereIn('estado', ['ACTIVO', 'SEGUIMIENTO_ESPECIAL']))
            ->orderBy('ap_paterno')
            ->get();
        $alertas = collect();

        foreach ($adultos as $adulto) {
            $fichaMedica = $adulto->fichasMedicas->where('estado', 'ACTIVA')->first();
            $medicacionesActivas = $adulto->medicaciones->whereIn('estado', ['ACTIVA', 'ACTIVO']);
            $valFuncional = $adulto->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
            $ultimosSignos = $adulto->signosVitales->where('estado', 'VIGENTE')->sortByDesc('fecha')->first();
            
            if (!$fichaMedica) {
                $alertas->push([
                    'adulto' => $adulto,
                    'tipo' => 'Ficha Médica',
                    'nivel' => 'atencion',
                    'mensaje' => 'Ficha médica no registrada.',
                    'accion' => 'requiere revisión',
                    'ruta' => route('admin.salud-seguimiento.ficha', $adulto->cod_am)
                ]);
            }

            if ($medicacionesActivas->isNotEmpty()) {
                $ultimaToma = $adulto->administracionesMedicacion->sortByDesc('fecha')->first();
                if (!$ultimaToma || \Carbon\Carbon::parse($ultimaToma->fecha)->diffInDays(now()) >= 1) {
                    $alertas->push([
                        'adulto' => $adulto,
                        'tipo' => 'Medicación',
                        'nivel' => 'atencion',
                        'mensaje' => 'Medicación activa sin administración reciente (más de 24h).',
                        'accion' => 'requiere revisión',
                        'ruta' => route('admin.salud-seguimiento.administracion', $adulto->cod_am)
                    ]);
                }
            }

            if ($ultimosSignos) {
                if ($ultimosSignos->temperatura > 37.8 || ($ultimosSignos->saturacion !== null && $ultimosSignos->saturacion < 92)) {
                    $alertas->push([
                        'adulto' => $adulto,
                        'tipo' => 'Signos Vitales',
                        'nivel' => 'critica',
                        'mensaje' => 'Signos vitales fuera de rango orientativo en el último control.',
                        'accion' => 'este aviso no constituye diagnóstico médico',
                        'ruta' => route('admin.salud-seguimiento.signos', $adulto->cod_am)
                    ]);
                }
            }

            if ($valFuncional) {
                if ($valFuncional->riesgo_caida === 'ALTO') {
                    $alertas->push([
                        'adulto' => $adulto,
                        'tipo' => 'Valoración',
                        'nivel' => 'critica',
                        'mensaje' => 'Riesgo de caída alto.',
                        'accion' => 'requiere revisión',
                        'ruta' => route('admin.salud-seguimiento.valoracion', $adulto->cod_am)
                    ]);
                }
                if (\in_array($valFuncional->nivel_dependencia, ['ALTA_DEPENDENCIA', 'SUPERVISION_PERMANENTE'])) {
                    $alertas->push([
                        'adulto' => $adulto,
                        'tipo' => 'Valoración',
                        'nivel' => 'atencion',
                        'mensaje' => 'Dependencia funcional alta.',
                        'accion' => 'requiere revisión',
                        'ruta' => route('admin.salud-seguimiento.valoracion', $adulto->cod_am)
                    ]);
                }
            } else {
                $alertas->push([
                    'adulto' => $adulto,
                    'tipo' => 'Valoración',
                    'nivel' => 'atencion',
                    'mensaje' => 'Sin valoración funcional registrada.',
                    'accion' => 'requiere revisión',
                    'ruta' => route('admin.salud-seguimiento.valoracion', $adulto->cod_am)
                ]);
            }
        }

        if ($this->filtroTipo) {
            $alertas = $alertas->where('tipo', $this->filtroTipo);
        }

        return view('livewire.admin.salud-seguimiento.salud-alertas-panel', [
            'alertas' => $alertas,
            'conteos' => [
                'total' => $alertas->count(),
                'criticas' => $alertas->where('nivel', 'critica')->count(),
                'preventivas' => $alertas->where('nivel', 'atencion')->count(),
                'tipos' => $alertas->groupBy('tipo')->map->count(),
            ],
        ]);
    }
}
