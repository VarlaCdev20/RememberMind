<?php
namespace App\Services\Alertas;

use App\Livewire\Clinica\SignosVitalesPanel;
use App\Models\{AdultoMayor, AlertaAdulto, SignosVitalesAdulto, AdministracionMedicacion, SeguimientoDiario, TareaPlanCuidado};
use Illuminate\Support\Facades\DB;

class DeteccionAlertasService
{
    public function detectar(?string $codAm = null): int
    {
        $creadas = 0;
        SignosVitalesAdulto::vigentes()->when($codAm, fn ($q) => $q->where('cod_am', $codAm))->orderBy('cod_signo')->chunk(100, function ($registros) use (&$creadas) {
            foreach ($registros as $s) {
                $nivel = SignosVitalesPanel::nivelGlobal(
                    $s->presion_sistolica,
                    $s->presion_diastolica,
                    $s->frecuencia_cardiaca,
                    $s->frecuencia_respiratoria,
                    $s->temperatura !== null ? (float) $s->temperatura : null,
                    $s->saturacion,
                    $s->glucosa !== null ? (float) $s->glucosa : null
                );
                if ($nivel !== 'normal') {
                    $creadas += $this->registrar(
                        $s,
                        'SIGNOS',
                        'SIGNOS FUERA DE PARÁMETROS',
                        $nivel === 'critico' ? 'CRITICO' : 'MEDIO',
                        'Control del ' . ($s->fecha?->format('d/m/Y') ?? 'hoy') . ' ' . $s->hora . ': Parámetros clínicos fuera de rango de referencia. Requiere valoración de enfermería.'
                    );
                }
            }
        });

        AdministracionMedicacion::query()->when($codAm, fn ($q) => $q->where('cod_am', $codAm))->where('administrado', false)->orderBy('cod_admin_med')->chunk(100, function ($registros) use (&$creadas) {
            foreach ($registros as $r) {
                $creadas += $this->registrar(
                    $r,
                    'MEDICACION',
                    'MEDICACIÓN OMITIDA',
                    'ALTO',
                    'Omisión de medicación: ' . ($r->motivo_omision ?: 'Sin motivo registrado') . '. Requiere reevaluación clínica.'
                );
            }
        });

        TareaPlanCuidado::whereIn('estado', ['PENDIENTE', 'EN_PROCESO', 'OMITIDA'])
            ->when($codAm, fn ($q) => $q->where('cod_am', $codAm))
            ->whereDate('fecha_programada', '<=', today())->orderBy('cod_tarea')->chunk(100, function ($registros) use (&$creadas) {
                foreach ($registros as $r) {
                    if ($r->estado !== 'OMITIDA' && $r->fecha_programada->isToday()
                        && (!$r->hora_programada || $r->hora_programada > now()->format('H:i:s'))) {
                        continue;
                    }
                    $creadas += $this->registrar(
                        $r,
                        'PLAN',
                        'TAREA PENDIENTE U OMITIDA',
                        'MEDIO',
                        'Cuidado pendiente: ' . $r->titulo . ' (Fecha: ' . $r->fecha_programada->format('d/m/Y') . ' ' . $r->hora_programada . '). Requiere cumplimiento.'
                    );
                }
            });

        SeguimientoDiario::query()->when($codAm, fn ($q) => $q->where('cod_am', $codAm))->where(fn ($q) => $q->where('incidente', true)->orWhere('requiere_medico', true))
            ->orderBy('cod_seg_diario')->chunk(100, function ($registros) use (&$creadas) {
                foreach ($registros as $r) {
                    $creadas += $this->registrar(
                        $r,
                        $r->requiere_medico ? 'SOLICITUD_MEDICA' : 'INCIDENTE',
                        $r->requiere_medico ? 'REQUIERE REVISIÓN MÉDICA' : 'INCIDENTE EN SEGUIMIENTO',
                        'ALTO',
                        $r->requiere_medico
                            ? ('Evaluación médica requerida: ' . ($r->observacion ?: 'Solicitud de valoración médica en turno.'))
                            : ('Incidente registrado en turno: ' . ($r->observacion ?: 'Seguimiento de enfermería.'))
                    );
                }
            });

        return $creadas;
    }

    public function detectarPreventivas(?string $codAm = null): int
    {
        $adultos = AdultoMayor::with([
            'fichasMedicas' => fn ($q) => $q->where('estado', 'ACTIVA')->latest()->limit(1),
            'medicaciones' => fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO']),
            'administracionesMedicacion' => fn ($q) => $q->latest('fecha')->limit(3),
            'signosVitales' => fn ($q) => $q->where('estado', 'VIGENTE')->latest('fecha')->limit(1),
            'valoracionesFuncionales' => fn ($q) => $q->latest('fecha_valoracion')->limit(1),
        ])
            ->when($codAm, fn ($q) => $q->where('cod_am', $codAm))
            ->whereHas('estado', fn ($q) => $q->whereIn('estado', ['ACTIVO', 'SEGUIMIENTO_ESPECIAL']))
            ->get();

        $creadas = 0;

        foreach ($adultos as $adulto) {
            $fichaMedica = $adulto->fichasMedicas->where('estado', 'ACTIVA')->first();
            $medicacionesActivas = $adulto->medicaciones->whereIn('estado', ['ACTIVA', 'ACTIVO']);
            $valFuncional = $adulto->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
            $ultimosSignos = $adulto->signosVitales->where('estado', 'VIGENTE')->sortByDesc('fecha')->first();

            // 1. Falta de Ficha Médica activa
            if (!$fichaMedica) {
                $alerta = $this->registrarPreventivaSiNoExiste(
                    $adulto->cod_am,
                    'FICHA',
                    'FICHA MEDICA',
                    'MEDIO',
                    'Ficha médica no registrada. Requiere valoración clínica inicial.'
                );
                if ($alerta) $creadas++;
            }

            // 2. Medicación activa sin administración reciente (>= 24h)
            if ($medicacionesActivas->isNotEmpty()) {
                $ultimaToma = $adulto->administracionesMedicacion->sortByDesc('fecha')->first();
                if (!$ultimaToma || \Carbon\Carbon::parse($ultimaToma->fecha)->diffInDays(now()) >= 1) {
                    $alerta = $this->registrarPreventivaSiNoExiste(
                        $adulto->cod_am,
                        'MEDICACION',
                        'MEDICACION SIN ADMINISTRACION',
                        'MEDIO',
                        'Medicación activa sin administración reciente registrada (más de 24h).'
                    );
                    if ($alerta) $creadas++;
                }
            }

            // 3. Signos vitales desregulados en último control
            if ($ultimosSignos) {
                if ($ultimosSignos->temperatura > 37.8 || ($ultimosSignos->saturacion !== null && $ultimosSignos->saturacion < 92)) {
                    $alerta = $this->registrarPreventivaSiNoExiste(
                        $adulto->cod_am,
                        'SIGNOS',
                        'SIGNOS FUERA DE RANGO',
                        'CRITICO',
                        'Signos vitales fuera de rango en el último control (Temp: ' . $ultimosSignos->temperatura . '°C, Sat: ' . $ultimosSignos->saturacion . '%).'
                    );
                    if ($alerta) $creadas++;
                }
            }

            // 4. Valoración Funcional: Riesgo de caída, dependencia alta o falta de valoración
            if ($valFuncional) {
                if ($valFuncional->riesgo_caida === 'ALTO') {
                    $alerta = $this->registrarPreventivaSiNoExiste(
                        $adulto->cod_am,
                        'VALORACION',
                        'RIESGO DE CAIDA',
                        'CRITICO',
                        'Riesgo de caída alto detectado en valoración funcional geriátrica.'
                    );
                    if ($alerta) $creadas++;
                }
                if (\in_array($valFuncional->nivel_dependencia, ['ALTA_DEPENDENCIA', 'SUPERVISION_PERMANENTE'])) {
                    $alerta = $this->registrarPreventivaSiNoExiste(
                        $adulto->cod_am,
                        'VALORACION',
                        'DEPENDENCIA FUNCIONAL',
                        'MEDIO',
                        'Dependencia funcional alta detectada en valoración funcional.'
                    );
                    if ($alerta) $creadas++;
                }
            } else {
                $alerta = $this->registrarPreventivaSiNoExiste(
                    $adulto->cod_am,
                    'VALORACION',
                    'VALORACION FALTANTE',
                    'MEDIO',
                    'Sin valoración funcional registrada.'
                );
                if ($alerta) $creadas++;
            }
        }

        return $creadas;
    }

    public function registrarPreventivaSiNoExiste(
        string $codAm,
        string $origen,
        string $tipoAlerta,
        string $nivel,
        string $motivo
    ): ?AlertaAdulto {
        return DB::transaction(function () use ($codAm, $origen, $tipoAlerta, $nivel, $motivo) {
            $existe = AlertaAdulto::where('cod_am', $codAm)
                ->where('origen', $origen)
                ->where('tipo_alerta', $tipoAlerta)
                ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
                ->exists();

            if ($existe) {
                return null;
            }

            return AlertaAdulto::create([
                'cod_am' => $codAm,
                'origen' => $origen,
                'tipo_alerta' => $tipoAlerta,
                'nivel' => $nivel,
                'motivo' => $motivo,
                'estado' => 'ABIERTA',
            ]);
        });
    }

    private function registrar($registro, string $origen, string $tipo, string $nivel, string $texto): int
    {
        $motivo = '['.$registro->getTable().':'.$registro->getKey().'] '.$texto;
        return DB::transaction(function () use ($registro, $origen, $tipo, $nivel, $motivo, $texto) {
            AdultoMayor::whereKey($registro->cod_am)->lockForUpdate()->firstOrFail();
            $referencia = '['.$registro->getTable().':'.$registro->getKey().'] ';

            // 1. Idempotencia por referencia de entidad
            if (AlertaAdulto::where('cod_am', $registro->cod_am)->where('origen', $origen)
                ->where('motivo', 'like', $referencia.'%')->exists()) {
                return 0;
            }

            // 2. Idempotencia por tipo y condición activa (ABIERTA o EN_ATENCION)
            $alertaEquivalente = AlertaAdulto::where('cod_am', $registro->cod_am)
                ->where('origen', $origen)
                ->where('tipo_alerta', $tipo)
                ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
                ->where(function ($q) use ($referencia, $texto) {
                    $q->where('motivo', 'like', $referencia.'%')
                      ->orWhere('motivo', 'like', '%'.$texto.'%');
                })
                ->exists();

            if ($alertaEquivalente) {
                return 0;
            }
            AlertaAdulto::create([
                'cod_am' => $registro->cod_am,
                'cod_turno' => $registro->cod_turno ?: null,
                'origen' => $origen,
                'tipo_alerta' => $tipo,
                'nivel' => $nivel,
                'motivo' => $motivo,
                'estado' => 'ABIERTA',
            ]);
            return 1;
        });
    }
}
