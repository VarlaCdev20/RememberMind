<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\AplicacionInstrumento;
use App\Models\Atencion;
use App\Models\Instrumento;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegistrarEvaluacionGeriatrica
{
    public function ejecutar(User $usuario, array $datos): void
    {

        DB::transaction(function () use ($usuario, $datos) {
            $personal = Personal::query()
                ->where('cod_usuario', $usuario->cod_usuario)
                ->where('estado', 'ACTIVO')
                ->firstOrFail();

            $atencion = Atencion::query()
                ->where('cod_residente', $datos['cod_residente'])
                ->where('cod_personal', $personal->cod_personal)
                ->latest('fecha_hora')
                ->first();

            $instrumento = Instrumento::findOrFail($datos['cod_instrumento']);
            $interpretacion = collect([$datos['categoria_resultado'], $datos['nivel_riesgo']])
                ->filter()
                ->implode(' · ');

            AplicacionInstrumento::create([
                'cod_aplicacion' => 'API_'.Str::upper(Str::random(12)),
                'cod_instrumento' => $datos['cod_instrumento'],
                'cod_residente' => $datos['cod_residente'],
                'cod_personal' => $personal->cod_personal,
                'cod_atencion' => $atencion?->cod_atencion,
                'fecha_hora' => $datos['fecha_eval'].' '.($datos['hora_eval'] ?: '00:00'),
                'puntaje_total' => $datos['puntaje_total'],
                'puntaje_maximo' => $instrumento->puntaje_maximo,
                'clasificacion' => $datos['nivel_alerta'],
                'interpretacion' => $interpretacion ?: null,
                'estado' => 'ACTIVA',
                'observacion' => $datos['observaciones'],
            ]);
        });
    }
}
