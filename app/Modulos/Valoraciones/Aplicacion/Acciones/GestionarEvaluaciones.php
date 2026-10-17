<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\AplicacionInstrumento;
use App\Models\Instrumento;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarEvaluaciones
{
    public function store(User $usuario, array $entrada, Residente $adulto_mayor): mixed
    {
        Validator::make($entrada, ['cod_tipo_eval' => 'required|exists:instrumentos,cod_instrumento', 'fecha_eval' => 'required|date|before_or_equal:today', 'puntaje_total' => 'required|numeric|min:0', 'observaciones' => 'nullable|string'])->validate();
        $instrumento = Instrumento::findOrFail(data_get($entrada, 'cod_tipo_eval'));
        $personal = Personal::where('cod_usuario', $usuario?->cod_usuario)->first();
        $interpretacion = $this->interpretarPuntaje($instrumento, data_get($entrada, 'puntaje_total'));
        AplicacionInstrumento::create(['cod_aplicacion' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('aplicaciones_instrumento'), 'cod_instrumento' => $instrumento->cod_instrumento, 'cod_residente' => $adulto_mayor->cod_residente, 'cod_personal' => $personal?->cod_personal, 'fecha_hora' => data_get($entrada, 'fecha_eval').' '.now()->format('H:i:s'), 'puntaje_total' => data_get($entrada, 'puntaje_total'), 'puntaje_maximo' => $instrumento->puntaje_maximo ?? 30, 'clasificacion' => $interpretacion['riesgo'], 'interpretacion' => $interpretacion['resultado'], 'observacion' => data_get($entrada, 'observaciones'), 'estado' => 'ACTIVO']);

        return null;
    }

    private function interpretarPuntaje($instrumento, $puntaje)
    {
        $resultado = 'Normal';
        $riesgo = 'BAJO';
        $codigo = strtoupper($instrumento->codigo ?? '');
        if (str_contains($codigo, 'MOCA')) {
            if ($puntaje < 26) {
                $resultado = 'Deterioro Cognitivo Leve';
                $riesgo = 'MEDIO';
            }
            if ($puntaje < 18) {
                $resultado = 'Deterioro Severo';
                $riesgo = 'ALTO';
            }
        } elseif (str_contains($codigo, 'MMSE')) {
            if ($puntaje < 24) {
                $resultado = 'Deterioro Cognitivo';
                $riesgo = 'MEDIO';
            }
            if ($puntaje < 12) {
                $resultado = 'Deterioro Severo';
                $riesgo = 'ALTO';
            }
        }

        return ['resultado' => $resultado, 'riesgo' => $riesgo];
    }

    public function destroy(User $usuario, array $entrada, Residente $adulto_mayor, $id): mixed
    {
        $evaluacion = AplicacionInstrumento::where('cod_residente', $adulto_mayor->cod_residente)->where('cod_aplicacion', $id)->firstOrFail();
        $evaluacion->update(['estado' => 'ANULADO']);

        return null;
    }

    public function restore(User $usuario, array $entrada, Residente $adulto_mayor, $id): mixed
    {
        $evaluacion = AplicacionInstrumento::where('cod_residente', $adulto_mayor->cod_residente)->where('cod_aplicacion', $id)->firstOrFail();
        $evaluacion->update(['estado' => 'ACTIVO']);

        return null;
    }
}
