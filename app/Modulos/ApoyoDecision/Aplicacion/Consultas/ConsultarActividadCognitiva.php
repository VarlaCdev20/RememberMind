<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\ControlCognitivo;
use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class ConsultarActividadCognitiva
{
    public function consultar(User $usuario, Residente $residente): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $registros = FuentesValoracionOperativa::limitar(ControlCognitivo::query())->where('cod_residente', $residente->getKey())
            ->where('estado', 'VIGENTE')->whereBetween('fecha_hora', [now()->subDays(90)->startOfDay(), now()])
            ->orderBy('fecha_hora')->get(['fecha_hora']);

        return $registros->groupBy(fn ($r) => $r->fecha_hora->format('Y-m-d'))->map(fn ($grupo, $fecha) => [
            'fecha' => $fecha.'T12:00:00'.now()->format('P'), 'valor' => $grupo->count(), 'unidad' => 'Controles registrados',
            'fuente' => 'Controles cognitivos vigentes', 'interpretacion' => 'Actividad de registro; no mide mejoría ni deterioro.',
        ])->values()->all();
    }
}
