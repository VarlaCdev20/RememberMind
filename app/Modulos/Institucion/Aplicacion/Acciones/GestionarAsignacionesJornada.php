<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class GestionarAsignacionesJornada
{
    public function guardar(User $usuario, array $entrada): void
    {

        abort_unless($usuario?->can('turnos.asignar'), 403);
        Validator::make($entrada, [
            'codResidente' => 'required|exists:residentes,cod_residente',
            'codTurno' => ['required', Rule::exists('turnos', 'cod_turno')->where('estado', 'ACTIVO')],
            'codEnfermero' => ['required', Rule::exists('usuarios', 'cod_usuario')->where('estado', 'ACTIVO')],
            'fechaInicio' => 'required|date|before_or_equal:today',
            'nivelSupervision' => 'required|in:MINIMO,ESTANDAR,INTENSIVO,CRITICO',
            'motivoAsignacion' => 'required|string|min:5|max:1000',
        ])->validate();

        DB::transaction(function () use ($entrada): void {
            $personal = Personal::query()->where('cod_usuario', $entrada['codEnfermero'])->firstOrFail();
            $jornada = Jornada::query()->firstOrCreate(
                ['cod_turno' => $entrada['codTurno'], 'fecha_jornada' => $entrada['fechaInicio']],
                ['cod_jornada' => 'JOR_'.Str::upper(Str::random(12)), 'estado' => 'ABIERTA']
            );

            if (AsignacionResidenteJornada::query()
                ->where('cod_residente', $entrada['codResidente'])
                ->where('cod_jornada', $jornada->cod_jornada)
                ->where('estado', 'ACTIVA')->exists()) {
                throw ValidationException::withMessages([
                    'codTurno' => 'Este residente ya tiene una asignación activa en esta jornada.',
                ]);
            }

            AsignacionResidenteJornada::query()->create([
                'cod_asignacion' => 'ARJ_'.Str::upper(Str::random(12)),
                'cod_residente' => $entrada['codResidente'],
                'cod_jornada' => $jornada->cod_jornada,
                'cod_personal' => $personal->cod_personal,
                'nivel_supervision' => $entrada['nivelSupervision'],
                'fecha_hora' => now(),
                'estado' => 'ACTIVA',
                'observacion' => trim($entrada['motivoAsignacion']),
            ]);
        });

    }

    public function finalizarAsignacion(User $usuario, array $entrada, string $id): void
    {

        abort_unless($usuario?->can('turnos.finalizar'), 403);
        AsignacionResidenteJornada::query()->findOrFail($id)->update(['estado' => 'FINALIZADA']);
    }
}
