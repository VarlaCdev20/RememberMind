<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class GestionarInstitucionHttp
{
    public function guardarPersonal(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario->can('personal.gestionar'), 403);
        $datos = Validator::make($entrada, ['cod_usuario' => ['required', 'exists:usuarios,cod_usuario', 'unique:personal,cod_usuario'], 'nombres' => ['required', 'string', 'max:100'], 'apellido_paterno' => ['required', 'string', 'max:80'], 'apellido_materno' => ['nullable', 'string', 'max:80'], 'numero_documento' => ['required', 'string', 'max:30', 'unique:personal,numero_documento'], 'profesion' => ['required', 'string', 'max:80'], 'especialidad' => ['nullable', 'string', 'max:120'], 'matricula_profesional' => ['nullable', 'string', 'max:50'], 'telefono' => ['nullable', 'string', 'max:30'], 'estado' => ['required', 'in:ACTIVO,INACTIVO']])->validate();

        return Personal::query()->create(['cod_personal' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('personal'), ...$datos]);
    }

    public function guardarArea(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario->can('areas.gestionar'), 403);
        $datos = Validator::make($entrada, ['nombre' => ['required', 'string', 'max:80', 'unique:areas,nombre'], 'descripcion' => ['nullable', 'string'], 'estado' => ['required', 'in:ACTIVA,INACTIVA']])->validate();

        return Area::query()->create(['cod_area' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('areas'), ...$datos]);
    }

    public function guardarTurno(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario->can('turnos.gestionar'), 403);
        $datos = Validator::make($entrada, ['nombre' => ['required', 'string', 'max:50', 'unique:turnos,nombre'], 'hora_inicio' => ['required', 'date_format:H:i'], 'hora_cierre' => ['required', 'date_format:H:i'], 'orden' => ['required', 'integer', 'min:1'], 'estado' => ['required', 'in:ACTIVO,INACTIVO'], 'observacion' => ['nullable', 'string']])->validate();

        return Turno::query()->create(['cod_turno' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('turnos'), ...$datos]);
    }

    public function abrirJornada(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario?->estado === 'ACTIVO' && $usuario->can('jornadas.gestionar'), 403);
        $datos = Validator::make($entrada, ['cod_turno' => ['required', Rule::exists('turnos', 'cod_turno')->where('estado', 'ACTIVO')], 'fecha_jornada' => ['required', 'date']])->validate();

        return Jornada::query()->create(['cod_jornada' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('jornadas'), ...$datos, 'cod_usuario_apertura' => $usuario->cod_usuario, 'estado' => 'ABIERTA']);
    }

    public function asignarPersonal(User $usuario, array $entrada, Jornada $jornada): mixed
    {
        abort_unless($usuario?->estado === 'ACTIVO' && $usuario->can('asignaciones_personal.gestionar'), 403);
        $datos = Validator::make($entrada, ['cod_personal' => ['required', Rule::exists('personal', 'cod_personal')->where('estado', 'ACTIVO')], 'cod_area' => ['required', Rule::exists('areas', 'cod_area')->where('estado', 'ACTIVA')], 'funcion' => ['nullable', 'string', 'max:80'], 'tipo_asignacion' => ['required', 'string', 'max:30']])->validate();
        $asignacion = DB::transaction(function () use ($jornada, $datos) {
            $jornada = Jornada::query()->lockForUpdate()->findOrFail($jornada->cod_jornada);
            if (! in_array($jornada->estado, ['PLANIFICADA', 'ABIERTA', 'ACTIVA', 'EN_CURSO'], true)) {
                throw ValidationException::withMessages(['jornada' => 'La jornada no admite asignaciones de personal.']);
            }

            return AsignacionPersonal::query()->create(['cod_asignacion_personal' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('asignaciones_personal'), 'cod_jornada' => $jornada->cod_jornada, ...$datos, 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        });

        return $asignacion;
    }

}
