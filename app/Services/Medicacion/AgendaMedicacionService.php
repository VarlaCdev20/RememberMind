<?php

namespace App\Services\Medicacion;

use App\Models\AdministracionMedicacion;
use App\Models\MedicacionAdulto;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AgendaMedicacionService
{
    /**
     * Construye la agenda de una fecha comparando la prescripción con el
     * registro clínico real. La ausencia de un registro significa pendiente;
     * nunca se crea una administración ficticia para representar la agenda.
     */
    public function paraAdultos(array $codigosAdulto, ?CarbonInterface $momento = null): Collection
    {
        $ahora = ($momento ? Carbon::instance($momento) : now())->copy();
        $fecha = $ahora->toDateString();

        if ($codigosAdulto === []) {
            return collect();
        }

        $medicaciones = MedicacionAdulto::query()
            ->with(['adultoMayor.habitacion', 'adultoMayor.cama'])
            ->whereIn('cod_am', $codigosAdulto)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->where(function ($query) use ($fecha) {
                $query->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $fecha);
            })
            ->whereNotNull('hora_programada')
            ->where('es_prn', false)
            ->orderBy('hora_programada')
            ->get();

        $administraciones = AdministracionMedicacion::query()
            ->whereIn('cod_am', $codigosAdulto)
            ->whereDate('fecha', $fecha)
            ->get()
            ->groupBy(fn (AdministracionMedicacion $registro) => $this->clave(
                $registro->cod_med_adulto,
                $registro->hora_programada
            ));

        return $medicaciones->flatMap(function (MedicacionAdulto $medicacion) use ($administraciones, $ahora, $fecha) {
            return collect($this->horariosDelDia($medicacion, $ahora))->map(function (string $hora) use ($medicacion, $administraciones, $ahora, $fecha) {
                $programada = Carbon::parse("{$fecha} {$hora}", $ahora->timezone);
                $registro = $administraciones->get($this->clave($medicacion->cod_med_adulto, $hora))?->first();

                if ($registro) {
                    $estado = $registro->administrado ? 'ADMINISTRADA' : 'OMITIDA';
                } elseif ($programada->isPast()) {
                    $estado = 'VENCIDA';
                } elseif ($ahora->diffInMinutes($programada, false) <= config('enfermeria.minutos_proximo_medicacion', 60)) {
                    $estado = 'PROXIMA';
                } else {
                    $estado = 'PENDIENTE';
                }

                return [
                    'id' => $medicacion->cod_med_adulto.'|'.$fecha.'|'.$hora,
                    'medicacion' => $medicacion,
                    'adulto' => $medicacion->adultoMayor,
                    'hora' => $hora,
                    'programada' => $programada,
                    'registro' => $registro,
                    'estado' => $estado,
                    'minutos' => (int) $ahora->diffInMinutes($programada, false),
                ];
            });
        })->sortBy('programada')->values();
    }

    public function paraAdulto(string $codAm, ?CarbonInterface $momento = null): Collection
    {
        return $this->paraAdultos([$codAm], $momento);
    }

    public function recordatorios(array $codigosAdulto, ?CarbonInterface $momento = null): Collection
    {
        return $this->paraAdultos($codigosAdulto, $momento)
            ->whereIn('estado', ['PROXIMA', 'VENCIDA'])
            ->values();
    }

    private function clave(string $codMedicacion, mixed $hora): string
    {
        return $codMedicacion.'|'.$this->normalizarHora($hora);
    }

    private function normalizarHora(mixed $hora): string
    {
        return Carbon::parse($hora)->format('H:i');
    }

    private function horariosDelDia(MedicacionAdulto $medicacion, CarbonInterface $momento): array
    {
        $horaInicial = Carbon::parse($medicacion->hora_programada);
        $minutoInicial = ($horaInicial->hour * 60) + $horaInicial->minute;
        $frecuencia = mb_strtoupper($medicacion->frecuencia ?? '');

        $intervaloHoras = $medicacion->intervalo_horas;
        if (!$intervaloHoras && preg_match('/CADA\s+(\d{1,2})\s+HORA/', $frecuencia, $coincidencia)) {
            $intervaloHoras = (int) $coincidencia[1];
        }

        if (!$intervaloHoras) {
            return [$horaInicial->format('H:i')];
        }
        if ($intervaloHoras < 1 || $intervaloHoras > 24) {
            return [$horaInicial->format('H:i')];
        }

        $intervalo = $intervaloHoras * 60;
        $primerMinuto = $minutoInicial;
        $esFechaInicio = $medicacion->fecha_inicio?->isSameDay($momento);
        if (!$esFechaInicio) {
            while ($primerMinuto - $intervalo >= 0) {
                $primerMinuto -= $intervalo;
            }
        }

        $horarios = [];
        for ($minuto = $primerMinuto; $minuto < 1440; $minuto += $intervalo) {
            $horarios[] = sprintf('%02d:%02d', intdiv($minuto, 60), $minuto % 60);
        }

        return $horarios;
    }
}
