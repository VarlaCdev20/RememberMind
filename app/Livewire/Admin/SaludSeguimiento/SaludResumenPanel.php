<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use App\Models\AdultoMayor;

class SaludResumenPanel extends Component
{
    public AdultoMayor $adulto;

    public function mount(AdultoMayor $adulto)
    {
        $this->adulto = $adulto;
    }

    public function render()
    {
        // Ficha Médica Activa
        $fichaMedica = $this->adulto->fichasMedicas()->where('estado', 'ACTIVA')->latest()->first();

        // Medicación Activa
        $medicacionActiva = $this->adulto->medicaciones()->where('estado', 'ACTIVA')->latest()->get();

        // Últimas Administraciones
        $ultimasAdministraciones = $this->adulto->administracionesMedicacion()->latest('fecha')->latest('hora_programada')->take(5)->get();

        // Dosis omitidas recientes
        $dosisOmitidas = $this->adulto->administracionesMedicacion()->where('estado', 'OMITIDA')->latest('fecha')->take(3)->get();

        // Últimos signos vitales
        $ultimosSignos = $this->adulto->signosVitales()->where('estado', 'VIGENTE')->latest('fecha')->latest('hora')->take(3)->get();

        // Valoración Funcional vigente
        $valoracionFuncional = $this->adulto->valoracionesFuncionales()->latest('fecha_valoracion')->first();

        // Alertas Dinámicas (Cálculo al vuelo)
        $alertas = collect();

        if (!$fichaMedica) {
            $alertas->push([
                'tipo' => 'Ficha Médica Pendiente',
                'nivel' => 'atencion',
                'mensaje' => 'No hay una ficha médica activa registrada para este adulto mayor.',
            ]);
        }

        if ($medicacionActiva->isEmpty()) {
            $alertas->push([
                'tipo' => 'Sin Medicación',
                'nivel' => 'informativa',
                'mensaje' => 'El paciente no tiene medicación activa registrada actualmente.',
            ]);
        } else {
            // Verificar si hay medicación activa sin administración reciente (ej. hace 24h)
            $ultimaToma = $this->adulto->administracionesMedicacion()->latest('fecha')->first();
            if (!$ultimaToma || \Carbon\Carbon::parse($ultimaToma->fecha)->diffInDays(now()) >= 1) {
                $alertas->push([
                    'tipo' => 'Posible Omisión',
                    'nivel' => 'atencion',
                    'mensaje' => 'Hay medicación activa pero no se registran administraciones en las últimas 24 horas.',
                ]);
            }
        }

        if ($dosisOmitidas->isNotEmpty()) {
            $alertas->push([
                'tipo' => 'Medicación Omitida',
                'nivel' => 'critica',
                'mensaje' => 'Se han registrado dosis omitidas o rechazadas recientemente. Requiere revisión.',
            ]);
        }

        if ($valoracionFuncional) {
            if (in_array(strtoupper($valoracionFuncional->riesgo_caida), ['ALTO', 'CRÍTICO'])) {
                $alertas->push([
                    'tipo' => 'Riesgo de Caída Alto',
                    'nivel' => 'critica',
                    'mensaje' => 'El paciente presenta alto riesgo de caída según su última valoración.',
                ]);
            }
            if (in_array(strtoupper($valoracionFuncional->nivel_dependencia), ['ALTO', 'TOTAL', 'SEVERA'])) {
                $alertas->push([
                    'tipo' => 'Alta Dependencia',
                    'nivel' => 'atencion',
                    'mensaje' => 'El paciente requiere asistencia significativa para sus actividades funcionales.',
                ]);
            }
        }

        // Secciones integradas sin duplicación (Solo Lectura)
        $observaciones = $this->adulto->observaciones()->latest('fecha')->take(3)->get();
        $atenciones = $this->adulto->atenciones()->latest('fecha')->take(3)->get();
        $evaluacionCognitiva = $this->adulto->evaluacionesCognitivas()->latest('fecha_evaluacion')->first();

        return view('livewire.admin.salud-seguimiento.salud-resumen-panel', [
            'fichaMedica' => $fichaMedica,
            'medicacionActiva' => $medicacionActiva,
            'ultimasAdministraciones' => $ultimasAdministraciones,
            'dosisOmitidas' => $dosisOmitidas,
            'ultimosSignos' => $ultimosSignos,
            'valoracionFuncional' => $valoracionFuncional,
            'alertas' => $alertas,
            'observaciones' => $observaciones,
            'atenciones' => $atenciones,
            'evaluacionCognitiva' => $evaluacionCognitiva,
        ])->layout('layouts.sistema');
    }
}
