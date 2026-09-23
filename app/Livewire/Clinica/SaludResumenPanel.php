<?php

namespace App\Livewire\Clinica;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\AdultoMayor;

class SaludResumenPanel extends Component
{
    public AdultoMayor $adulto;

    public function mount(AdultoMayor $adulto): void
    {
        $this->adulto = $adulto;
    }

    public function render()
    {
        // 1. Ficha médica activa
        $fichaMedica = $this->adulto->fichasMedicas()
            ->whereNotIn('estado', ['ANULADA', 'CANCELADA'])
            ->latest('fecha_hora')
            ->first();

        // 2. Medicaciones activas (enum correcto: ACTIVO)
        $medicacionActiva = $this->adulto->medicaciones()
            ->where('estado', 'ACTIVA')
            ->latest('fecha_hora_prescripcion')
            ->get();

        // 3. Última administración (sin filtro estado: columna no existe en administracion_medicacion)
        $ultimaAdministracion = $this->adulto->administracionesMedicacion()
            ->latest('fecha_hora_programada')
            ->first();

        // 4. Tomas no administradas recientes (columna boolean: administrado)
        $dosisOmitidas = $this->adulto->administracionesMedicacion()
            ->where('resultado', 'OMITIDA')
            ->latest('fecha_hora_programada')
            ->take(3)
            ->get();

        // 5. Último registro de signos vitales (tabla no tiene columna estado)
        $ultimoSigno = $this->adulto->signosVitales()
            ->latest('fecha_hora')
            ->first();

        // 6. Valoración funcional vigente (tabla sí tiene columna estado)
        $valoracionFuncional = $this->adulto->valoracionesFuncionales()
            ->whereIn('estado', ['ACTIVA', 'VIGENTE'])
            ->latest('fecha_hora')
            ->first();

        // 7. Observaciones recientes
        $observaciones = $this->adulto->observaciones()
            ->latest('fecha_hora')
            ->take(3)
            ->get();

        // 8. Atenciones recientes
        $atenciones = $this->adulto->atenciones()
            ->latest('fecha_hora')
            ->take(3)
            ->get();

        // 9. Evaluación cognitiva más reciente (columna real: fecha_eval)
        $evaluacionCognitiva = $this->adulto->evaluacionesGeriatricas()
            ->latest('fecha_hora')
            ->first();

        // ── Alertas orientativas ─────────────────────────────────
        $alertas = collect();

        if (!$fichaMedica) {
            $alertas->push([
                'tipo'    => 'Ficha Médica',
                'nivel'   => 'atencion',
                'mensaje' => 'No se ha registrado una ficha médica activa. Seguimiento pendiente.',
            ]);
        }

        if ($medicacionActiva->isNotEmpty()) {
            $ultimaToma = $this->adulto->administracionesMedicacion()
                ->where('resultado', 'ADMINISTRADA')
                ->latest('fecha_hora_administracion')
                ->first();
            if (!$ultimaToma || $ultimaToma->fecha_hora_administracion?->diffInDays(now()) >= 1) {
                $alertas->push([
                    'tipo'    => 'Administración Pendiente',
                    'nivel'   => 'atencion',
                    'mensaje' => 'Alerta orientativa: hay medicación activa sin registro de administración confirmada en las últimas 24 horas. Requiere revisión.',
                ]);
            }
        }

        if ($dosisOmitidas->isNotEmpty()) {
            $alertas->push([
                'tipo'    => 'Tomas No Administradas',
                'nivel'   => 'requiere_revision',
                'mensaje' => 'Se registran tomas sin administrar recientemente. Requiere revisión.',
            ]);
        }

        if ($valoracionFuncional) {
            if (in_array($valoracionFuncional->nivel_funcional, ['DEPENDENCIA_ALTA', 'SUPERVISION_PERMANENTE'])) {
                $alertas->push([
                    'tipo'    => 'Dependencia Elevada',
                    'nivel'   => 'atencion',
                    'mensaje' => 'Se registra nivel de dependencia elevado en la valoración funcional. Seguimiento pendiente.',
                ]);
            }

        }

        if (!$ultimoSigno || $ultimoSigno->fecha_hora?->diffInDays(now()) > 7) {
            $alertas->push([
                'tipo'    => 'Signos Vitales',
                'nivel'   => 'seguimiento',
                'mensaje' => 'No se han registrado signos vitales en los últimos 7 días. Seguimiento pendiente.',
            ]);
        }

        if ($evaluacionCognitiva && $evaluacionCognitiva->fecha_hora?->diffInMonths(now()) >= 6) {
            $alertas->push([
                'tipo'    => 'Evaluación Cognitiva',
                'nivel'   => 'seguimiento',
                'mensaje' => 'Alerta orientativa: la última evaluación cognitiva tiene más de 6 meses. Seguimiento pendiente.',
            ]);
        }

        return view('livewire.clinica.salud-resumen-panel', [
            'fichaMedica'          => $fichaMedica,
            'medicacionActiva'     => $medicacionActiva,
            'ultimaAdministracion' => $ultimaAdministracion,
            'dosisOmitidas'        => $dosisOmitidas,
            'ultimoSigno'          => $ultimoSigno,
            'valoracionFuncional'  => $valoracionFuncional,
            'alertas'              => $alertas,
            'observaciones'        => $observaciones,
            'atenciones'           => $atenciones,
            'evaluacionCognitiva'  => $evaluacionCognitiva,
        ])->layout('layouts.sistema');
    }
}
