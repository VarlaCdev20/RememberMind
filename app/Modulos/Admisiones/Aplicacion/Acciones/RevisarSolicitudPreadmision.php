<?php

namespace App\Modulos\Admisiones\Aplicacion\Acciones;

use App\Models\Preadmision;
use App\Models\User;

final class RevisarSolicitudPreadmision
{
    private function autorizar(User $usuario): void
    {
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('preadmisiones.revisar') && $usuario->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'ADMINISTRADOR', 'SUPERADMINISTRADOR']), 403);
    }

    public function aprobar(User $usuario, Preadmision $preadmision): void
    {
        $this->autorizar($usuario);
        abort_if($preadmision->admision()->exists() || in_array($preadmision->estado, ['APROBADA', 'ADMITIDA', 'RECHAZADA'], true), 409);
        $preadmision->update([
            'estado' => 'APROBADA',
            'fecha_revision' => now(),
            'cod_usuario_revision' => $usuario->getKey(),
            'motivo_rechazo' => null,
        ]);

        activity('Admisiones')->causedBy($usuario)->performedOn($preadmision)
            ->log('Solicitud de preadmisión aprobada; queda pendiente formalizar el ingreso.');

    }

    public function rechazar(User $usuario, Preadmision $preadmision, string $motivo): void
    {
        $this->autorizar($usuario);
        abort_if($preadmision->admision()->exists() || in_array($preadmision->estado, ['APROBADA', 'ADMITIDA'], true), 409);
        $preadmision->update([
            'estado' => 'RECHAZADA',
            'motivo_rechazo' => mb_strtoupper(trim($motivo), 'UTF-8'),
            'fecha_revision' => now(),
            'cod_usuario_revision' => $usuario->getKey(),
        ]);

        activity('Admisiones')
            ->causedBy($usuario)
            ->performedOn($preadmision)
            ->log("Preadmisión {$preadmision->cod_preadmision} rechazada.");

    }
}
