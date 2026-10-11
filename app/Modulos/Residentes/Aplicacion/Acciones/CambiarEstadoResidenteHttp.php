<?php

namespace App\Modulos\Residentes\Aplicacion\Acciones;

use App\Models\HistorialEstadoResidente;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Str;

final class CambiarEstadoResidenteHttp
{
    /**
     * Cambiar estado directamente y registrar historial_estados_residente.
     */
    public function cambiarEstado(User $usuario, array $entrada, Residente $adulto_mayor): mixed
    {
        abort_unless($usuario->can('residentes.gestionar'), 403);
        try {
            \DB::beginTransaction();
            $estadoAnterior = $adulto_mayor->estado;
            $nuevoEstado = data_get($entrada, 'estado') ?? data_get($entrada, 'cod_est_adul') ?? 'ACTIVO';
            if (is_numeric($nuevoEstado)) {
                $nuevoEstado = (int) $nuevoEstado === 2 || (int) $nuevoEstado === 3 ? 'INACTIVO' : 'ACTIVO';
            }
            $adulto_mayor->estado = $nuevoEstado;
            $adulto_mayor->observacion = data_get($entrada, 'motivo') ?? $adulto_mayor->observacion;
            $adulto_mayor->save();
            HistorialEstadoResidente::create(['cod_historial_estado' => 'HER_'.strtoupper(Str::random(10)), 'cod_residente' => $adulto_mayor->cod_residente, 'cod_usuario_registro' => $usuario->cod_usuario, 'estado_anterior' => $estadoAnterior ?? 'ACTIVO', 'estado_nuevo' => $nuevoEstado, 'fecha_hora' => now(), 'motivo' => data_get($entrada, 'motivo') ?? 'Cambio de estado administrativo']);
            \DB::commit();

            return null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }
}
