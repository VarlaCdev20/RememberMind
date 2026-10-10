<?php

namespace App\Modulos\Admisiones\Aplicacion\Acciones;

use App\Models\HistorialEstadoResidente;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use Illuminate\Support\Facades\DB;

/** Conserva el flujo histórico DECISION_ADMISION; no sustituye FormalizarAdmision. */
final class RegistrarDecisionAdmision
{
    public function ejecutar(User $usuario, array $datos): Residente
    {
        abort_unless(app(AccesoClinicoTemporalService::class)
            ->tieneRol($usuario, ['MEDICO GENERAL/GERIATRA']), 403);
        abort_unless($datos['adulto'] && $datos['adulto']->fresh()->estado === 'DECISION_ADMISION', 409);

        return DB::transaction(function () use ($usuario, $datos): Residente {
            $datos['adulto'] = Residente::lockForUpdate()->findOrFail($datos['adulto']->cod_residente);
            if ($datos['adulto']->estado !== 'DECISION_ADMISION') {
                throw new \RuntimeException('La decisión ya fue registrada.');
            }
            $estadoAnterior = $datos['adulto']->estado;
            $nuevoEstadoStr = ($datos['decision'] === 'DERIVADO') ? 'DERIVADO' : 'PENDIENTE_ASIGNACION';
            $datos['adulto']->update(['estado' => $nuevoEstadoStr]);

            HistorialEstadoResidente::create([
                'cod_residente' => $datos['adulto']->cod_residente,
                'cod_usuario_registro' => $usuario->getKey(),
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstadoStr,
                'motivo' => $datos['decision'].' - '.($datos['motivo_decision'] ?: ($datos['motivo_derivacion'] ?: 'Decisión de Admisión')),
            ]);

            // Registrar Log
            activity('Medico')
                ->causedBy($usuario)
                ->performedOn($datos['adulto'])
                ->withProperties([
                    'decision' => $datos['decision'],
                    'motivo_decision' => $datos['motivo_decision'],
                    'seguimiento_requerido' => $datos['seguimiento_requerido'],
                    'cuidado_especial_requerido' => $datos['cuidado_especial_requerido'],
                    'motivo_derivacion' => $datos['motivo_derivacion'],
                    'institucion_derivada' => $datos['institucion_derivada'],
                    'recomendacion_final' => $datos['recomendacion_final'],
                ])
                ->log("Tomó la decisión de {$datos['decision']} para el paciente.");

            return $datos['adulto'];
        });
    }
}
