<?php

namespace App\Modulos\Clinica\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\NotaClinica;
use App\Models\SignoVital;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegistrarNotaEvolucionMedica
{
    public function ejecutar(User $usuario, array $datos): void
    {

        DB::transaction(function () use ($usuario, $datos) {
            $personal = $usuario?->personal;
            $codArea = $personal?->asignaciones()
                ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                ->latest('fecha_asignacion')
                ->value('cod_area');
            abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');

            $fechaHora = $datos['fecha'].' '.($datos['hora'] ?: now()->format('H:i'));
            $atencion = Atencion::query()->create([
                'cod_atencion' => 'ATN_'.Str::upper(Str::random(10)),
                'cod_residente' => $datos['cod_residente'],
                'cod_area' => $codArea,
                'cod_personal' => $personal->cod_personal,
                'tipo_atencion' => $datos['tipo_nota'],
                'motivo' => $datos['valoracion'],
                'fecha_hora' => $fechaHora,
                'estado' => 'FINALIZADA',
                'observacion' => $datos['observaciones'] ?: null,
            ]);

            NotaClinica::query()->create([
                'cod_nota' => 'NCL_'.Str::upper(Str::random(10)),
                'cod_atencion' => $atencion->cod_atencion,
                'cod_residente' => $datos['cod_residente'],
                'cod_personal' => $personal->cod_personal,
                'tipo_nota' => $datos['tipo_nota'],
                'contenido' => collect([
                    filled($datos['subjetivo']) ? "Subjetivo: {$datos['subjetivo']}" : null,
                    filled($datos['objetivo']) ? "Objetivo: {$datos['objetivo']}" : null,
                    "Valoración: {$datos['valoracion']}",
                    "Plan: {$datos['plan']}",
                    filled($datos['observaciones']) ? "Observaciones: {$datos['observaciones']}" : null,
                ])->filter()->implode("\n"),
                'fecha_hora' => $fechaHora,
                'estado' => 'ACTIVA',
            ]);

            if ($datos['incluirSignos']) {
                SignoVital::query()->create([
                    'cod_signo' => 'SGN_'.Str::upper(Str::random(10)),
                    'cod_residente' => $datos['cod_residente'],
                    'cod_personal' => $personal->cod_personal,
                    'cod_atencion' => $atencion->cod_atencion,
                    'fecha_hora' => $fechaHora,
                    'presion_sistolica' => $datos['pa_sistolica'] ?: null,
                    'presion_diastolica' => $datos['pa_diastolica'] ?: null,
                    'frecuencia_cardiaca' => $datos['fc'] ?: null,
                    'frecuencia_respiratoria' => $datos['fr'] ?: null,
                    'temperatura' => $datos['temperatura'] ?: null,
                    'saturacion_oxigeno' => $datos['saturacion'] ?: null,
                    'glucemia' => $datos['glucosa'] ?: null,
                    'estado' => 'VIGENTE',
                    'observacion' => $datos['peso'] ? "Peso informado: {$datos['peso']} kg" : null,
                ]);
            }
        });
    }
}
