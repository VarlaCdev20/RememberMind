<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\AplicacionInstrumento;
use App\Models\Instrumento;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegistrarEvaluacionGeriatricaArea
{
    public function ejecutar(User $usuario, array $datos): void
    {
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('aplicaciones_instrumento.crear'), 403);
        DB::transaction(function () use ($usuario, $datos) {
            $personal = $usuario?->personal;
            abort_unless($personal, 422, 'El usuario debe tener un registro de personal asociado.');
            $instrumento = Instrumento::query()->findOrFail($datos['cod_instrumento']);

            AplicacionInstrumento::create([
                'cod_aplicacion' => 'APL_'.Str::upper(Str::random(10)),
                'cod_residente' => $datos['cod_residente'],
                'cod_instrumento' => $instrumento->cod_instrumento,
                'cod_personal' => $personal->cod_personal,
                'fecha_hora' => $datos['fecha_eval'].' '.($datos['hora_eval'] ?: now()->format('H:i')),
                'puntaje_total' => $datos['puntaje_total'],
                'puntaje_maximo' => $instrumento->puntaje_maximo,
                'clasificacion' => $datos['categoria_resultado'] ?: $datos['nivel_alerta'],
                'interpretacion' => $datos['nivel_riesgo'],
                'observacion' => $datos['observaciones'],
                'estado' => 'COMPLETA',
            ]);
        });
    }
}
