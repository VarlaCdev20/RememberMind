<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TendenciaOcupacionService
{
    /**
     * Camas activas ocupadas al cierre de cada día (hoy, al momento de consulta).
     * La serie se deriva de los intervalos de ocupación existentes, sin guardar datos nuevos.
     */
    public function ultimosSieteDias(): ?array
    {
        $camasActivas = DB::table('camas')->where('estado', 'ACTIVA')->pluck('cod_cama')->all();
        if ($camasActivas === []) {
            return null;
        }

        $ahora = Carbon::now();
        $primerDia = $ahora->copy()->subDays(6)->startOfDay();
        $ocupaciones = DB::table('ocupaciones_cama')
            ->whereIn('cod_cama', $camasActivas)
            ->where('fecha_hora_asignacion', '<=', $ahora)
            ->where(function ($query) use ($primerDia) {
                $query->where('fecha_hora_liberacion', '>', $primerDia)
                    ->orWhere(function ($abierta) {
                        $abierta->whereNull('fecha_hora_liberacion')->where('estado', 'ACTIVA');
                    });
            })
            ->get(['cod_cama', 'fecha_hora_asignacion', 'fecha_hora_liberacion'])
            ->map(fn ($ocupacion) => [
                'cama' => $ocupacion->cod_cama,
                'inicio' => Carbon::parse($ocupacion->fecha_hora_asignacion),
                'fin' => $ocupacion->fecha_hora_liberacion ? Carbon::parse($ocupacion->fecha_hora_liberacion) : null,
            ]);

        $dias = [];
        for ($i = 0; $i < 7; $i++) {
            $dia = $primerDia->copy()->addDays($i);
            $corte = $dia->isSameDay($ahora) ? $ahora : $dia->copy()->endOfDay();
            $ocupadas = [];
            foreach ($ocupaciones as $ocupacion) {
                if ($ocupacion['inicio']->lte($corte) && ($ocupacion['fin'] === null || $ocupacion['fin']->gt($corte))) {
                    $ocupadas[$ocupacion['cama']] = true;
                }
            }
            $dias[] = [
                'fecha' => $dia->toDateString(),
                'etiqueta' => $dia->format('d/m'),
                'ocupadas' => count($ocupadas),
            ];
        }

        return ['total_camas' => count($camasActivas), 'dias' => $dias];
    }
}
