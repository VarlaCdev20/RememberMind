<?php

namespace App\Frontend\Livewire\Compartido\Alertas;

use Livewire\Component;
use App\Models\AdultoMayor;

class SaludAlertasPanel extends Component
{
    public $filtroTipo = '';

    public function render()
    {
        // Persistir y sincronizar alertas preventivas hacia Alerta sin duplicados
        app(\App\Backend\Modulos\Alertas\Servicios\DeteccionAlertasService::class)->detectarPreventivas();
        // Cargamos solo adultos activos con relaciones acotadas para evitar timeout
        $adultos = AdultoMayor::with([
            'fichasMedicas'           => fn ($q) => $q->where('estado', 'ACTIVA')->latest()->limit(1),
            'medicaciones'            => fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO']),
            'administracionesMedicacion' => fn ($q) => $q->latest('fecha_hora_programada')->limit(3),
            'signosVitales'           => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'VIGENTE'])->latest('fecha_hora')->limit(1),
            'valoracionesFuncionales' => fn ($q) => $q->latest('fecha_hora')->limit(1),
        ])
            ->whereIn('estado', ['ACTIVO', 'ADMITIDO', 'SEGUIMIENTO_ESPECIAL'])
            ->orderBy('apellido_paterno')
            ->get();
        $alertas = collect();

        foreach ($adultos as $adulto) {
            $fichaMedica = $adulto->fichasMedicas->where('estado', 'ACTIVA')->first();
            $medicacionesActivas = $adulto->medicaciones->whereIn('estado', ['ACTIVA', 'ACTIVO']);
            $valFuncional = $adulto->valoracionesFuncionales->sortByDesc('fecha_hora')->first();
            $ultimosSignos = $adulto->signosVitales->whereIn('estado', ['ACTIVO', 'VIGENTE'])->sortByDesc('fecha_hora')->first();
            
            if (!$fichaMedica) {
                $alertas->push([
                    'adulto' => $adulto,
                    'tipo' => 'Ficha Médica',
                    'nivel' => 'atencion',
                    'mensaje' => 'Ficha médica no registrada.',
                    'accion' => 'requiere revisión',
                    'ruta' => route('admin.salud-seguimiento.ficha', $adulto->cod_residente)
                ]);
            }

            if ($medicacionesActivas->isNotEmpty()) {
                $ultimaToma = $adulto->administracionesMedicacion->sortByDesc('fecha_hora_programada')->first();
                if (!$ultimaToma || \Carbon\Carbon::parse($ultimaToma->fecha_hora_programada)->diffInDays(now()) >= 1) {
                    $alertas->push([
                        'adulto' => $adulto,
                        'tipo' => 'Medicación',
                        'nivel' => 'atencion',
                        'mensaje' => 'Medicación activa sin administración reciente (más de 24h).',
                        'accion' => 'requiere revisión',
                        'ruta' => route('admin.salud-seguimiento.administracion', $adulto->cod_residente)
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
                        'ruta' => route('admin.salud-seguimiento.signos', $adulto->cod_residente)
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
                        'ruta' => route('admin.salud-seguimiento.valoracion', $adulto->cod_residente)
                    ]);
                }
                if (\in_array($valFuncional->nivel_dependencia, ['ALTA_DEPENDENCIA', 'SUPERVISION_PERMANENTE'])) {
                    $alertas->push([
                        'adulto' => $adulto,
                        'tipo' => 'Valoración',
                        'nivel' => 'atencion',
                        'mensaje' => 'Dependencia funcional alta.',
                        'accion' => 'requiere revisión',
                        'ruta' => route('admin.salud-seguimiento.valoracion', $adulto->cod_residente)
                    ]);
                }
            } else {
                $alertas->push([
                    'adulto' => $adulto,
                    'tipo' => 'Valoración',
                    'nivel' => 'atencion',
                    'mensaje' => 'Sin valoración funcional registrada.',
                    'accion' => 'requiere revisión',
                    'ruta' => route('admin.salud-seguimiento.valoracion', $adulto->cod_residente)
                ]);
            }
        }

        if ($this->filtroTipo) {
            $alertas = $alertas->where('tipo', $this->filtroTipo);
        }

        return view('livewire.alertas.salud-alertas-panel', [
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
