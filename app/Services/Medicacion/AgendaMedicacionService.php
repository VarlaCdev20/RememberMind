<?php

namespace App\Services\Medicacion;

use App\Models\AdministracionMedicacion;
use App\Models\HorarioPrescripcion;
use App\Models\Prescripcion;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AgendaMedicacionService
{
    /**
     * Construye la agenda desde prescripciones y horarios V2. La ausencia de
     * una administración significa pendiente y no genera registros ficticios.
     */
    public function paraAdultos(array $codigosResidente, ?CarbonInterface $momento = null): Collection
    {
        $ahora = ($momento ? Carbon::instance($momento) : now())->copy();
        $fecha = $ahora->toDateString();

        if ($codigosResidente === []) {
            return collect();
        }

        $prescripciones = Prescripcion::query()
            ->with([
                'medicamento',
                'horarios' => fn ($query) => $query->where('estado', 'ACTIVO')->orderBy('hora_programada'),
                'adultoMayor.ocupacionActiva.cama.habitacion',
            ])
            ->whereIn('cod_residente', $codigosResidente)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->where('segun_necesidad', false)
            ->where('fecha_hora_prescripcion', '<=', $ahora)
            ->get();

        $administraciones = AdministracionMedicacion::query()
            ->whereIn('cod_residente', $codigosResidente)
            ->whereDate('fecha_hora_programada', $fecha)
            ->get()
            ->groupBy(fn (AdministracionMedicacion $registro) => $this->clave(
                $registro->cod_prescripcion,
                $registro->fecha_hora_programada,
            ));

        return $prescripciones->flatMap(function (Prescripcion $prescripcion) use ($administraciones, $ahora, $fecha) {
            return $prescripcion->horarios->map(function (HorarioPrescripcion $horario) use ($prescripcion, $administraciones, $ahora, $fecha) {
                $hora = $this->normalizarHora($horario->hora_programada);
                $programada = Carbon::parse("{$fecha} {$hora}", $ahora->timezone);
                $registro = $administraciones->get($this->clave($prescripcion->cod_prescripcion, $hora))?->first();

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
                    'id' => $prescripcion->cod_prescripcion.'|'.$fecha.'|'.$hora,
                    'medicacion' => $prescripcion,
                    'adulto' => $prescripcion->adultoMayor,
                    'horario' => $horario,
                    'hora' => $hora,
                    'hora_12h' => Carbon::parse("2000-01-01 {$hora}")->format('h:i A'),
                    'se_paso' => $programada->isPast() && ! $registro,
                    'programada' => $programada,
                    'registro' => $registro,
                    'estado' => $estado,
                    'minutos' => (int) $ahora->diffInMinutes($programada, false),
                ];
            });
        })->sortBy('programada')->values();
    }

    public function paraAdulto(string $codResidente, ?CarbonInterface $momento = null): Collection
    {
        return $this->paraAdultos([$codResidente], $momento);
    }

    public function recordatorios(array $codigosResidente, ?CarbonInterface $momento = null): Collection
    {
        return $this->paraAdultos($codigosResidente, $momento)
            ->whereIn('estado', ['PROXIMA', 'VENCIDA'])
            ->values();
    }

    private function clave(string $codPrescripcion, mixed $hora): string
    {
        return $codPrescripcion.'|'.$this->normalizarHora($hora);
    }

    private function normalizarHora(mixed $hora): string
    {
        return Carbon::parse($hora)->format('H:i');
    }
}
