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
            ->where('estado', 'ACTIVO')
            ->latest()
            ->first();

        // 2. Medicaciones activas (enum correcto: ACTIVO)
        $medicacionActiva = $this->adulto->medicaciones()
            ->where('estado', 'ACTIVO')
            ->latest()
            ->get();

        // 3. Última administración (sin filtro estado: columna no existe en administracion_medicacion)
        $ultimaAdministracion = $this->adulto->administracionesMedicacion()
            ->latest('fecha')
            ->first();

        // 4. Tomas no administradas recientes (columna boolean: administrado)
        $dosisOmitidas = $this->adulto->administracionesMedicacion()
            ->where('administrado', false)
            ->latest('fecha')
            ->take(3)
            ->get();

        // 5. Último registro de signos vitales (tabla no tiene columna estado)
        $ultimoSigno = $this->adulto->signosVitales()
            ->latest('fecha')
            ->latest('hora')
            ->first();

        // 6. Valoración funcional vigente (tabla sí tiene columna estado)
        $valoracionFuncional = $this->adulto->valoracionesFuncionales()
            ->where('estado', 'VIGENTE')
            ->latest('fecha_valoracion')
            ->first();

        // 7. Observaciones recientes
        $observaciones = $this->adulto->observaciones()
            ->latest('fecha')
            ->take(3)
            ->get();

        // 8. Atenciones recientes
        $atenciones = $this->adulto->atenciones()
            ->latest('fecha')
            ->take(3)
            ->get();

        // 9. Evaluación cognitiva más reciente (columna real: fecha_eval)
        $evaluacionCognitiva = $this->adulto->evaluacionesGeriatricas()->whereHas('instrumento', fn ($q) => $q->where('cod_area', 'ARE_COG'))
            ->latest('fecha_eval')
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
                ->where('administrado', true)
                ->latest('fecha')
                ->first();
            if (!$ultimaToma || Carbon::parse($ultimaToma->fecha)->diffInDays(now()) >= 1) {
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
            if (strtoupper((string) $valoracionFuncional->riesgo_caida) === 'ALTO') {
                $alertas->push([
                    'tipo'    => 'Riesgo de Caída',
                    'nivel'   => 'requiere_revision',
                    'mensaje' => 'Alerta orientativa: la valoración funcional registra riesgo de caída alto. Requiere seguimiento.',
                ]);
            }

            if (in_array($valoracionFuncional->nivel_dependencia, ['ALTA_DEPENDENCIA', 'SUPERVISION_PERMANENTE'])) {
                $alertas->push([
                    'tipo'    => 'Dependencia Elevada',
                    'nivel'   => 'atencion',
                    'mensaje' => 'Se registra nivel de dependencia elevado en la valoración funcional. Seguimiento pendiente.',
                ]);
            }

            if ($valoracionFuncional->indice_barthel !== null && (int) $valoracionFuncional->indice_barthel < 40) {
                $alertas->push([
                    'tipo'    => 'Índice de Barthel',
                    'nivel'   => 'seguimiento',
                    'mensaje' => 'Alerta orientativa: el índice de Barthel registrado es menor a 40. Requiere revisión.',
                ]);
            }
        }

        if (!$ultimoSigno || Carbon::parse($ultimoSigno->fecha)->diffInDays(now()) > 7) {
            $alertas->push([
                'tipo'    => 'Signos Vitales',
                'nivel'   => 'seguimiento',
                'mensaje' => 'No se han registrado signos vitales en los últimos 7 días. Seguimiento pendiente.',
            ]);
        }

        if ($evaluacionCognitiva && Carbon::parse($evaluacionCognitiva->fecha_eval)->diffInMonths(now()) >= 6) {
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
