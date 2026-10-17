<?php

namespace App\Modulos\Medicacion\Aplicacion\Consultas;

use App\Models\AdministracionMedicacion;
use App\Models\HorarioPrescripcion;
use App\Models\Prescripcion;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AgendaMedicacionService
{
    /**
     * Construye la agenda desde prescripciones y horarios V2. La ausencia de
     * una administración significa pendiente y no genera registros ficticios.
     */
    public function paraAdultos(array $codigosResidente, ?CarbonInterface $momento = null, ?CarbonInterface $fechaAgenda = null): Collection
    {
        $ahora = ($momento ? Carbon::instance($momento) : now())->copy();
        $dia = $fechaAgenda ? Carbon::instance($fechaAgenda)->copy() : $ahora->copy();
        $fecha = $dia->toDateString();

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
            ->whereNotIn('estado', ['ANULADA','ANULADO'])
            ->whereIn('resultado', ['ADMINISTRADA','OMITIDA','RECHAZADA'])
            ->whereIn('cod_residente', $codigosResidente)
            ->whereDate('fecha_hora_programada', $fecha)
            ->get()
            ->groupBy(fn (AdministracionMedicacion $registro) => $this->clave(
                $registro->cod_prescripcion,
                $registro->fecha_hora_programada,
                $registro->cod_horario_prescripcion,
            ));

        return $prescripciones->flatMap(function (Prescripcion $prescripcion) use ($administraciones, $ahora, $fecha, $dia) {
            return $prescripcion->horarios
                ->filter(fn (HorarioPrescripcion $horario) => ! filled($horario->dias_semana)
                    || strtoupper(trim($horario->dias_semana)) === 'TODOS'
                    || app(MiTurnoService::class)->correspondeDiaSemana($horario->dias_semana, $dia))
                ->map(function (HorarioPrescripcion $horario) use ($prescripcion, $administraciones, $ahora, $fecha) {
                    $hora = $this->normalizarHora($horario->hora_programada);
                    $programada = Carbon::parse("{$fecha} {$hora}", $ahora->timezone);
                    $registro = $administraciones->get($this->clave($prescripcion->cod_prescripcion, $hora, $horario->cod_horario_prescripcion))?->first();

                    if ($registro) {
                        $estado = $registro->administrado ? 'ADMINISTRADA' : ($registro->resultado === 'RECHAZADA' ? 'RECHAZADA' : 'OMITIDA');
                    } elseif ($programada->lt($ahora)) {
                        $estado = 'VENCIDA';
                    } elseif ($ahora->diffInMinutes($programada, false) <= config('enfermeria.minutos_proximo_medicacion', 60)) {
                        $estado = 'PROXIMA';
                    } else {
                        $estado = 'PENDIENTE';
                    }

                    return [
                        'id' => $prescripcion->cod_prescripcion.'|'.$horario->cod_horario_prescripcion.'|'.$fecha.'|'.$hora,
                        'medicacion' => $prescripcion,
                        'adulto' => $prescripcion->residente,
                        'horario' => $horario,
                        'hora' => $hora,
                        'hora_12h' => Carbon::parse("2000-01-01 {$hora}")->format('h:i A'),
                        'se_paso' => $programada->lt($ahora) && ! $registro,
                        'programada' => $programada,
                        'registro' => $registro,
                        'estado' => $estado,
                        'minutos' => (int) $ahora->diffInMinutes($programada, false),
                    ];
                });
        })->sortBy('programada')->values();
    }

    public function paraAdulto(string $codResidente, ?CarbonInterface $momento = null, ?CarbonInterface $fechaAgenda = null): Collection
    {
        return $this->paraAdultos([$codResidente], $momento, $fechaAgenda);
    }

    public function recordatorios(array $codigosResidente, ?CarbonInterface $momento = null): Collection
    {
        return $this->paraAdultos($codigosResidente, $momento)
            ->whereIn('estado', ['PROXIMA', 'VENCIDA'])
            ->values();
    }

    private function clave(string $codPrescripcion, mixed $hora, ?string $codHorario): string
    {
        return $codPrescripcion.'|'.$codHorario.'|'.$this->normalizarHora($hora);
    }

    private function normalizarHora(mixed $hora): string
    {
        return Carbon::parse($hora)->format('H:i');
    }
}
