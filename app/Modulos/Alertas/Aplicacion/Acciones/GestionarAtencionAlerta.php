<?php

namespace App\Modulos\Alertas\Aplicacion\Acciones;

use App\Models\Alerta;
use App\Models\EventoAlerta;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class GestionarAtencionAlerta
{
    public function atenderAlerta(User $usuario, string $id, string $texto): void
    {
        $this->comprobarPermiso($usuario, 'atender');
        $this->autorizarAlerta($usuario, $id);
        DB::transaction(function () use ($id, $texto, $usuario) {
            $alerta = Alerta::lockForUpdate()->findOrFail($id);
            abort_unless($alerta->puedeCerrarse(), 409, 'La alerta no está abierta para atención.');

            $textoAccion = $texto;

            $alerta->update(['estado' => 'EN_ATENCION']);

            EventoAlerta::query()->create([
                'cod_evento_alerta' => 'EVA_'.Str::upper(Str::random(16)),
                'cod_alerta' => $alerta->cod_alerta,
                'cod_usuario' => $usuario->getKey(),
                'tipo_evento' => 'ATENCION',
                'estado_anterior' => 'ABIERTA',
                'estado_nuevo' => 'EN_ATENCION',
                'fecha_hora' => now(),
                'descripcion' => $textoAccion,
            ]);
        });

    }

    public function cerrarAlerta(User $usuario, string $id, string $texto): void
    {
        $this->comprobarPermiso($usuario, 'cerrar');
        $this->autorizarAlerta($usuario, $id);
        DB::transaction(function () use ($id, $texto, $usuario) {
            $alerta = Alerta::lockForUpdate()->findOrFail($id);
            abort_unless($alerta->puedeCerrarse(), 409, 'La alerta ya está cerrada.');

            $textoObs = $texto;

            $estadoAnterior = $alerta->estado;
            $alerta->update(['estado' => 'CERRADA']);

            EventoAlerta::query()->create([
                'cod_evento_alerta' => 'EVA_'.Str::upper(Str::random(16)),
                'cod_alerta' => $alerta->cod_alerta,
                'cod_usuario' => $usuario->getKey(),
                'tipo_evento' => 'CIERRE',
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => 'CERRADA',
                'fecha_hora' => now(),
                'descripcion' => $textoObs,
            ]);
        });

    }

    private function comprobarPermiso(User $usuario, string $accion): void
    {
        $user = $usuario;
        abort_unless($user, 401);

        $permiso = match ($accion) {
            'ver' => 'alertas.ver',
            'cerrar' => 'alertas.cerrar',
            default => 'alertas.seguimiento',
        };
        abort_unless($user->canAny([$permiso, 'alertas.gestionar']), 403);
    }

    private function autorizarAlerta(User $usuario, string $id): void
    {
        $user = $usuario;

        if ($user?->hasRole('ENFERMEROS')) {
            $pacientes = app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds($user);
            abort_unless(Alerta::whereKey($id)->whereIn('cod_residente', $pacientes)->exists(), 403);
        }
    }
}
