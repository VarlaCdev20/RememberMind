<?php

declare(strict_types=1);

namespace App\Modulos\Cuidados\Aplicacion\Consultas;

use App\Models\EjecucionCuidado;
use App\Models\ProgramacionCuidado;
use App\Models\Turno;
use Carbon\Carbon;

/** Identidad temporal de una ocurrencia explícita; no deduce horarios de frecuencia textual. */
final class OcurrenciasCuidado
{
    public function momento(ProgramacionCuidado $programacion, Carbon $fecha, ?Turno $turno = null): ?Carbon
    {
        if (! in_array($programacion->estado, ['ACTIVO', 'ACTIVA', 'VIGENTE'], true)
            || ! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/D', (string) $programacion->hora_programada)
            || ($programacion->cod_turno !== null && $programacion->cod_turno !== $turno?->getKey())) {
            return null;
        }
        $momento = $fecha->copy()->setTimeFromTimeString($programacion->hora_programada);
        if (($programacion->fecha_activacion && $momento->toDateString() < $programacion->fecha_activacion->toDateString())
            || ($programacion->fecha_desactivacion && $momento->toDateString() > $programacion->fecha_desactivacion->toDateString())
            || ! app(MiTurnoService::class)->correspondeDiaSemana($programacion->dias_semana, $momento)) {
            return null;
        }

        return $momento;
    }

    public function corresponde(EjecucionCuidado $ejecucion, string $residente, string $intervencion, Carbon $momento): bool
    {
        return $ejecucion->cod_residente === $residente && $ejecucion->cod_intervencion === $intervencion
            && $ejecucion->fecha_hora_programada !== null && Carbon::parse($ejecucion->fecha_hora_programada)->equalTo($momento);
    }

    public function estado(?EjecucionCuidado $ejecucion): string
    {
        return match ($ejecucion?->estado) {
            'REALIZADA', 'FINALIZADA', 'COMPLETADA', 'EJECUTADA' => 'REALIZADA',
            'OMITIDA', 'NO_REALIZADA' => 'NO_REALIZADA',
            'CANCELADA' => 'CANCELADA',
            default => 'PENDIENTE',
        };
    }
}
