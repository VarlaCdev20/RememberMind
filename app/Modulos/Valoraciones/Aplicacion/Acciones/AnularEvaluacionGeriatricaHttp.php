<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\AplicacionInstrumento;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

final class AnularEvaluacionGeriatricaHttp
{
    public function anularEvaluacionGeriatrica(User $usuario, array $entrada, Residente $adulto_mayor, $evaluacionId): mixed
    {
        Validator::make($entrada, ['motivo_anulacion' => 'required|string|min:10'], ['motivo_anulacion.required' => 'El motivo de anulación es obligatorio.', 'motivo_anulacion.min' => 'El motivo de anulación debe tener al menos 10 caracteres.'])->validate();
        \DB::transaction(function () use ($entrada, $adulto_mayor, $evaluacionId, $usuario) {
            $eval = AplicacionInstrumento::where('cod_residente', $adulto_mayor->cod_residente)->where('cod_aplicacion', $evaluacionId)->firstOrFail();
            $eval->update(['estado' => 'ANULADA', 'observacion' => trim(($eval->observacion ?? '')."\nANULADA: ".data_get($entrada, 'motivo_anulacion').' ('.$usuario->getKey().', '.now()->toDateTimeString().')')]);
        });

        return null;
    }
}
