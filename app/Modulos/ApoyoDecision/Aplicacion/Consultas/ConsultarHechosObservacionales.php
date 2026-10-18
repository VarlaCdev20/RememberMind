<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\ControlCognitivo;
use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Dominio\Reglas\EvaluadorHechosObservacionales;
use Illuminate\Support\Facades\Gate;

/**
 * Consulta de solo lectura. Respeta la Policy existente y nunca escribe
 * resultados clínicos, alertas o evaluaciones expertas.
 */
final class ConsultarHechosObservacionales
{
    public function consultar(User $usuario, Residente $residente): array
    {
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);

        $controles = ControlCognitivo::query()
            ->where('cod_residente', $residente->getKey())
            ->where('estado', 'VIGENTE')
            ->where('fecha_hora', '<=', now())
            ->with('personal:cod_personal,nombres,apellido_paterno,apellido_materno')
            ->orderByDesc('fecha_hora')
            ->orderByDesc('cod_control_cognitivo')
            ->limit(30)
            ->get(['cod_control_cognitivo', 'cod_personal', 'fecha_hora', 'repite_preguntas', 'olvida_indicaciones', 'cambio_cognitivo'])
            ->map(static function (ControlCognitivo $control): array {
                $autor = $control->personal
                    ? trim(implode(' ', array_filter([
                        $control->personal->nombres,
                        $control->personal->apellido_paterno,
                        $control->personal->apellido_materno,
                    ]))) : 'Autor no disponible';

                return [
                    'registro' => $control->getKey(),
                    'fecha' => $control->fecha_hora->format('d/m/Y H:i'),
                    'autor' => $autor,
                    'repite_preguntas' => $control->repite_preguntas,
                    'olvida_indicaciones' => $control->olvida_indicaciones,
                    'cambio_cognitivo' => $control->cambio_cognitivo,
                ];
            })->all();

        return (new EvaluadorHechosObservacionales)->evaluar($controles);
    }
}
