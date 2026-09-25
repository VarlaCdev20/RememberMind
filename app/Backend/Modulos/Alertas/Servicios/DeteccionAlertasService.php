<?php
namespace App\Backend\Modulos\Alertas\Servicios;

use App\Frontend\Livewire\Compartido\Clinica\SignosVitalesPanel;
use App\Models\{AdultoMayor, Alerta, SignoVital, AdministracionMedicacion, Atencion, EjecucionCuidado};
use Illuminate\Support\Facades\DB;

class DeteccionAlertasService
{
    public function detectar(?string $codResidente = null): int
    {
        $creadas = 0;
        SignoVital::vigentes()->when($codResidente, fn ($q) => $q->where('cod_residente', $codResidente))->orderBy('cod_signo')->chunk(100, function ($registros) use (&$creadas) {
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

        AdministracionMedicacion::query()->when($codResidente, fn ($q) => $q->where('cod_residente', $codResidente))->where('resultado', 'OMITIDA')->orderBy('cod_administracion')->chunk(100, function ($registros) use (&$creadas) {
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

        EjecucionCuidado::whereIn('estado', ['PENDIENTE', 'EN_PROCESO', 'OMITIDA'])
            ->when($codResidente, fn ($q) => $q->where('cod_residente', $codResidente))
            ->whereDate('fecha_hora_programada', '<=', today())->orderBy('cod_ejecucion')->chunk(100, function ($registros) use (&$creadas) {
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

        Atencion::query()->when($codResidente, fn ($q) => $q->where('cod_residente', $codResidente))
            ->where(fn ($q) => $q->where('motivo', 'like', '%incidente%')->orWhere('motivo', 'like', '%medico%'))
            ->orderBy('cod_atencion')->chunk(100, function ($registros) use (&$creadas) {
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

    public function detectarPreventivas(?string $codResidente = null): int
    {
        $adultos = AdultoMayor::with([
            'fichasMedicas' => fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])->latest()->limit(1),
            'medicaciones' => fn ($q) => $q->whereIn('estado', ['ACTIVA', 'ACTIVO']),
            'administracionesMedicacion' => fn ($q) => $q->latest('fecha_hora_programada')->limit(3),
            'signosVitales' => fn ($q) => $q->where('estado', 'ACTIVO')->latest('fecha_hora')->limit(1),
            'valoracionesFuncionales' => fn ($q) => $q->latest('fecha_hora')->limit(1),
        ])
            ->when($codResidente, fn ($q) => $q->where('cod_residente', $codResidente))
            ->whereIn('estado', ['ACTIVO', 'ADMITIDO', 'ASIGNADO', 'EN_SEGUIMIENTO_ACTIVO', 'OBSERVADO', 'SEGUIMIENTO_ESPECIAL'])
            ->get();

        $creadas = 0;

        foreach ($adultos as $adulto) {
            $fichaMedica = $adulto->fichasMedicas->whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])->first();
            $medicacionesActivas = $adulto->medicaciones->whereIn('estado', ['ACTIVA', 'ACTIVO']);
            $valFuncional = $adulto->valoracionesFuncionales->sortByDesc('fecha_hora')->first();
            $ultimosSignos = $adulto->signosVitales->where('estado', 'VIGENTE')->sortByDesc('fecha')->first();

            // 1. Falta de Ficha Médica activa
            if (!$fichaMedica) {
                $alerta = $this->registrarPreventivaSiNoExiste(
                    $adulto->cod_residente,
                    'FICHA',
                    'FICHA MEDICA',
                    'MEDIO',
                    'Ficha médica no registrada. Requiere valoración clínica inicial.'
                );
                if ($alerta) $creadas++;
            }

            // 2. Dosis realmente vencidas según la pauta activa; las órdenes PRN no generan vencimiento.
            if ($medicacionesActivas->isNotEmpty()) {
                $dosisVencidas = app(\App\Backend\Modulos\Medicacion\Servicios\AgendaMedicacionService::class)
                    ->paraAdulto($adulto->cod_residente)
                    ->where('estado', 'VENCIDA');
                if ($dosisVencidas->isNotEmpty()) {
                    $alerta = $this->registrarPreventivaSiNoExiste(
                        $adulto->cod_residente,
                        'MEDICACION',
                        'MEDICACION SIN ADMINISTRACION',
                        'MEDIO',
                        $dosisVencidas->count().' dosis programada(s) vencida(s) sin administración u omisión registrada.'
                    );
                    if ($alerta) $creadas++;
                }
            }

            // 3. Signos vitales desregulados en último control
            if ($ultimosSignos) {
                if ($ultimosSignos->temperatura > 37.8 || ($ultimosSignos->saturacion !== null && $ultimosSignos->saturacion < 92)) {
                    $alerta = $this->registrarPreventivaSiNoExiste(
                        $adulto->cod_residente,
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
                        $adulto->cod_residente,
                        'VALORACION',
                        'RIESGO DE CAIDA',
                        'CRITICO',
                        'Riesgo de caída alto detectado en valoración funcional geriátrica.'
                    );
                    if ($alerta) $creadas++;
                }
                if (\in_array($valFuncional->nivel_dependencia, ['ALTA_DEPENDENCIA', 'SUPERVISION_PERMANENTE'])) {
                    $alerta = $this->registrarPreventivaSiNoExiste(
                        $adulto->cod_residente,
                        'VALORACION',
                        'DEPENDENCIA FUNCIONAL',
                        'MEDIO',
                        'Dependencia funcional alta detectada en valoración funcional.'
                    );
                    if ($alerta) $creadas++;
                }
            } else {
                $alerta = $this->registrarPreventivaSiNoExiste(
                    $adulto->cod_residente,
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
        string $codResidente,
        string $origen,
        string $tipoAlerta,
        string $nivel,
        string $motivo
    ): ?Alerta {
        return DB::transaction(function () use ($codResidente, $origen, $tipoAlerta, $nivel, $motivo) {
            $existe = Alerta::where('cod_residente', $codResidente)
                ->where('modulo', $origen)
                ->where('tipo', $tipoAlerta)
                ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
                ->exists();

            if ($existe) {
                return null;
            }

            return Alerta::create([
                'cod_alerta' => 'ALA_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'cod_residente' => $codResidente,
                'tipo' => $tipoAlerta,
                'prioridad' => $nivel,
                'modulo' => $origen,
                'titulo' => mb_substr($motivo, 0, 100),
                'descripcion' => $motivo,
                'fecha_hora' => now(),
                'generacion' => 'AUTOMATICA',
                'estado' => 'ABIERTA',
            ]);
        });
    }

    private function registrar($registro, string $origen, string $tipo, string $nivel, string $texto): int
    {
        $motivo = '['.$registro->getTable().':'.$registro->getKey().'] '.$texto;
        return DB::transaction(function () use ($registro, $origen, $tipo, $nivel, $motivo, $texto) {
            AdultoMayor::whereKey($registro->cod_residente)->lockForUpdate()->firstOrFail();
            $referencia = '['.$registro->getTable().':'.$registro->getKey().'] ';

            // 1. Idempotencia por referencia de entidad
            if (Alerta::where('cod_residente', $registro->cod_residente)->where('modulo', $origen)
                ->where('descripcion', 'like', $referencia.'%')->exists()) {
                return 0;
            }

            // 2. Idempotencia por tipo y condición activa (ABIERTA o EN_ATENCION)
            $alertaEquivalente = Alerta::where('cod_residente', $registro->cod_residente)
                ->where('modulo', $origen)
                ->where('tipo', $tipo)
                ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
                ->where(function ($q) use ($referencia, $texto) {
                    $q->where('descripcion', 'like', $referencia.'%')
                      ->orWhere('descripcion', 'like', '%'.$texto.'%');
                })
                ->exists();

            if ($alertaEquivalente) {
                return 0;
            }
            Alerta::create([
                'cod_alerta' => 'ALA_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'cod_residente' => $registro->cod_residente,
                'tipo' => $tipo,
                'prioridad' => $nivel,
                'modulo' => $origen,
                'titulo' => mb_substr($texto, 0, 100),
                'descripcion' => $motivo,
                'fecha_hora' => now(),
                'generacion' => 'AUTOMATICA',
                'estado' => 'ABIERTA',
            ]);
            return 1;
        });
    }
}
