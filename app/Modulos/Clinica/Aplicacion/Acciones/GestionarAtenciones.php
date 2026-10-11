<?php

namespace App\Modulos\Clinica\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarAtenciones
{
    public function store(User $usuario, array $entrada, Residente $adulto_mayor): mixed
    {
        Validator::make($entrada, ['fecha' => 'required|date', 'hora' => 'required', 'cod_tipo_aten' => 'required'])->validate();
        $fechaHora = data_get($entrada, 'fecha').' '.data_get($entrada, 'hora');
        $personal = Personal::where('cod_usuario', $usuario?->cod_usuario)->first();
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional asignados.');
        Atencion::create(['cod_atencion' => 'ATN_'.strtoupper(Str::random(10)), 'cod_residente' => $adulto_mayor->cod_residente, 'cod_area' => $codArea, 'cod_personal' => $personal->cod_personal, 'tipo_atencion' => data_get($entrada, 'cod_tipo_aten'), 'motivo' => data_get($entrada, 'motivo') ?? 'Atención programada', 'fecha_hora' => $fechaHora, 'estado' => data_get($entrada, 'estado', 'PENDIENTE'), 'observacion' => data_get($entrada, 'obs')]);

        return null;
    }

    public function update(User $usuario, array $entrada, Residente $adulto_mayor, $atencionId): mixed
    {
        $atencion = Atencion::where('cod_residente', $adulto_mayor->cod_residente)->where('cod_atencion', $atencionId)->firstOrFail();
        $atencion->update(['estado' => data_get($entrada, 'estado', $atencion->estado), 'observacion' => data_get($entrada, 'obs', $atencion->observacion)]);

        return null;
    }

    public function destroy(User $usuario, array $entrada, Residente $adulto_mayor, $atencionId): mixed
    {
        $atencion = Atencion::where('cod_residente', $adulto_mayor->cod_residente)->where('cod_atencion', $atencionId)->firstOrFail();
        $atencion->update(['estado' => 'ANULADO']);

        return null;
    }

    public function restore(User $usuario, array $entrada, Residente $adulto_mayor, $atencionId): mixed
    {
        $atencion = Atencion::where('cod_residente', $adulto_mayor->cod_residente)->where('cod_atencion', $atencionId)->firstOrFail();
        $atencion->update(['estado' => 'PENDIENTE']);

        return null;
    }
}
