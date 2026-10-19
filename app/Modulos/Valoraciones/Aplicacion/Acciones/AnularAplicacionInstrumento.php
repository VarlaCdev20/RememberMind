<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\AplicacionInstrumento;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/** Anula el acto conservando respuestas, fecha y autor originales; el motivo pertenece a su auditoría. */
final class AnularAplicacionInstrumento
{
    public function anular(User $usuario, Residente $residente, string $codigo, array $entrada): void
    {
        DB::transaction(function () use ($usuario, $residente, $codigo, $entrada): void {
            $usuario = User::query()->lockForUpdate()->findOrFail($usuario->getKey());
            $residente = Residente::query()->lockForUpdate()->findOrFail($residente->getKey());
            app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $residente,
                'aplicaciones_instrumento.anular', AplicarInstrumento::ROLES_APLICADORES);
            $datos = Validator::make($entrada, ['motivo_anulacion' => ['required', 'string', 'min:10', 'max:500']])->validate();
            $aplicacion = AplicacionInstrumento::query()->where('cod_residente', $residente->getKey())->lockForUpdate()->findOrFail($codigo);
            if (in_array($aplicacion->estado, ['ANULADA', 'ANULADO'], true)) {
                return;
            }
            $anterior = $aplicacion->estado;
            $aplicacion->update(['estado' => 'ANULADA']);
            activity('instrumentos')->causedBy($usuario)->performedOn($aplicacion)->event('aplicacion_anulada')
                ->withProperties(['estado_anterior' => $anterior, 'motivo' => $datos['motivo_anulacion']])
                ->log('Aplicación anulada conservando su contenido original.');
        }, 3);
    }
}
