<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class GestionarInstitucionHttp
{
    public function guardarPersonal(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario->can('personal.gestionar'), 403);
        $datos = Validator::make($entrada, ['cod_usuario' => ['required', 'exists:usuarios,cod_usuario', 'unique:personal,cod_usuario'], 'nombres' => ['required', 'string', 'max:100'], 'apellido_paterno' => ['required', 'string', 'max:80'], 'apellido_materno' => ['nullable', 'string', 'max:80'], 'numero_documento' => ['required', 'string', 'max:30', 'unique:personal,numero_documento'], 'profesion' => ['required', 'string', 'max:80'], 'especialidad' => ['nullable', 'string', 'max:120'], 'matricula_profesional' => ['nullable', 'string', 'max:50'], 'telefono' => ['nullable', 'string', 'max:30'], 'estado' => ['required', 'in:ACTIVO,INACTIVO']])->validate();

        return Personal::query()->create(['cod_personal' => app(CodigosEntidad::class)->siguiente('personal'), ...$datos]);
    }

    public function guardarArea(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario->can('areas.gestionar'), 403);
        $datos = Validator::make($entrada, ['nombre' => ['required', 'string', 'max:80', 'unique:areas,nombre'], 'descripcion' => ['nullable', 'string'], 'estado' => ['required', 'in:ACTIVA,INACTIVA']])->validate();

        return Area::query()->create(['cod_area' => app(CodigosEntidad::class)->siguiente('areas'), ...$datos]);
    }

    public function guardarTurno(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario->can('turnos.gestionar'), 403);
        $datos = Validator::make($entrada, ['nombre' => ['required', 'string', 'max:50', 'unique:turnos,nombre'], 'hora_inicio' => ['required', 'date_format:H:i'], 'hora_cierre' => ['required', 'date_format:H:i'], 'orden' => ['required', 'integer', 'min:1'], 'estado' => ['required', 'in:ACTIVO,INACTIVO'], 'observacion' => ['nullable', 'string']])->validate();

        return Turno::query()->create(['cod_turno' => app(CodigosEntidad::class)->siguiente('turnos'), ...$datos]);
    }

    public function abrirJornada(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario?->estado === 'ACTIVO' && $usuario->can('jornadas.gestionar'), 403);
        $datos = Validator::make($entrada, ['cod_turno' => ['required', Rule::exists('turnos', 'cod_turno')->where('estado', 'ACTIVO')], 'fecha_jornada' => ['required', 'date']])->validate();

        return DB::transaction(function () use ($datos, $usuario) {
            $turno = Turno::whereKey($datos['cod_turno'])->lockForUpdate()->firstOrFail();
            if ($turno->estado !== 'ACTIVO') {
                throw ValidationException::withMessages(['cod_turno' => 'El turno ya no está activo.']);
            }

            return Jornada::query()->create(['cod_jornada' => app(CodigosEntidad::class)->siguiente('jornadas'), ...$datos, 'cod_usuario_apertura' => $usuario->cod_usuario, 'estado' => 'ABIERTA']);
        });
    }

    public function asignarPersonal(User $usuario, array $entrada, Jornada $jornada): mixed
    {
        abort_unless($usuario?->estado === 'ACTIVO' && $usuario->can('asignaciones_personal.gestionar'), 403);
        $datos = Validator::make($entrada, ['cod_personal' => ['required', Rule::exists('personal', 'cod_personal')->where('estado', 'ACTIVO')], 'cod_area' => ['required', Rule::exists('areas', 'cod_area')->whereIn('estado', ['ACTIVO', 'ACTIVA'])], 'funcion' => ['nullable', 'string', 'max:80'], 'tipo_asignacion' => ['required', 'string', 'max:30']])->validate();
        $asignacion = DB::transaction(function () use ($jornada, $datos, $usuario) {
            $usuario = $usuario->fresh();
            abort_unless($usuario?->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR') && $usuario->can('asignaciones_personal.gestionar'), 403);
            abort_if(app(RolePreviewService::class)->isActive($usuario), 403);
            $area = Area::whereKey($datos['cod_area'])->lockForUpdate()->firstOrFail();
            $personal = Personal::whereKey($datos['cod_personal'])->lockForUpdate()->firstOrFail();
            $turno = Turno::whereKey($jornada->cod_turno)->lockForUpdate()->firstOrFail();
            if (! in_array($area->estado, ['ACTIVO', 'ACTIVA'], true) || $turno->estado !== 'ACTIVO' || $personal->estado !== 'ACTIVO') {
                throw ValidationException::withMessages(['cod_area' => 'Área, turno y trabajador deben permanecer activos.']);
            }
            $jornada = Jornada::query()->lockForUpdate()->findOrFail($jornada->cod_jornada);
            if (! in_array($jornada->estado, ['PLANIFICADA', 'ABIERTA', 'ACTIVA', 'EN_CURSO'], true)) {
                throw ValidationException::withMessages(['jornada' => 'La jornada no admite asignaciones de personal.']);
            }

            $asignacion = AsignacionPersonal::query()->create(['cod_asignacion_personal' => app(CodigosEntidad::class)->siguiente('asignaciones_personal'), 'cod_jornada' => $jornada->cod_jornada, ...$datos, 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
            app(ConservarEventoAsignacion::class)->conservar($asignacion, $usuario, 'ALTA', null, Carbon::parse($jornada->fecha_jornada)->setTimeFromTimeString($turno->hora_inicio), 'Alta operativa de asignación en jornada.');

            return $asignacion;
        });

        return $asignacion;
    }
}
