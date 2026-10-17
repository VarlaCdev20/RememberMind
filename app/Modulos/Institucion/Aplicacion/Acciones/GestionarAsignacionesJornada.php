<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class GestionarAsignacionesJornada
{
    public function guardar(User $usuario, array $entrada): void
    {

        abort_unless($usuario?->estado === 'ACTIVO' && $usuario->can('turnos.asignar'), 403);
        Validator::make($entrada, [
            'codResidente' => 'required|exists:residentes,cod_residente',
            'codTurno' => ['required', Rule::exists('turnos', 'cod_turno')->where('estado', 'ACTIVO')],
            'codEnfermero' => ['required', Rule::exists('usuarios', 'cod_usuario')->where('estado', 'ACTIVO')],
            'fechaInicio' => 'required|date',
            'nivelSupervision' => 'required|in:MINIMO,ESTANDAR,INTENSIVO,CRITICO',
            'motivoAsignacion' => 'required|string|min:5|max:1000',
        ])->validate();

        DB::transaction(function () use ($entrada, $usuario): void {
            $personal = Personal::query()->where('cod_usuario', $entrada['codEnfermero'])->lockForUpdate()->firstOrFail();
            $futura = Carbon::parse($entrada['fechaInicio'])->startOfDay()->gt(today());
            $jornada = $futura ? $this->jornadaPlanificada($entrada, $personal) : Jornada::query()->firstOrCreate(
                ['cod_turno' => $entrada['codTurno'], 'fecha_jornada' => $entrada['fechaInicio']],
                ['cod_jornada' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('jornadas'), 'estado' => 'ABIERTA']
            );

            $jornada = Jornada::query()->lockForUpdate()->findOrFail($jornada->cod_jornada);
            if (AsignacionResidenteJornada::query()
                ->where('cod_residente', $entrada['codResidente'])
                ->where('cod_jornada', $jornada->cod_jornada)
                ->whereIn('estado', ['ACTIVA', 'ACTIVO'])->exists()) {
                throw ValidationException::withMessages(['codTurno' => 'Este residente ya tiene una asignación activa en esta jornada.']);
            }

            $asignacion = AsignacionResidenteJornada::query()->create([
                'cod_asignacion' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('asignaciones_residente_jornada'),
                'cod_residente' => $entrada['codResidente'],
                'cod_jornada' => $jornada->cod_jornada,
                'cod_personal' => $personal->cod_personal,
                'nivel_supervision' => $entrada['nivelSupervision'],
                'fecha_hora' => now(),
                'estado' => 'ACTIVA',
                'observacion' => trim($entrada['motivoAsignacion']),
            ]);

            activity('asignaciones_jornada')->causedBy($usuario)->performedOn($asignacion)
                ->withProperties(['cod_jornada' => $jornada->cod_jornada, 'planificada' => $futura])
                ->log('Residente asignado a jornada');
        });

    }

    private function jornadaPlanificada(array $entrada, Personal $personal): Jornada
    {
        $enfermero = $personal->usuario;
        if ($personal->estado !== 'ACTIVO' || $enfermero?->estado !== 'ACTIVO' || ! $enfermero->hasRole('ENFERMEROS')) {
            throw ValidationException::withMessages(['codEnfermero' => 'La planificación requiere una cuenta y un perfil de Enfermería activos.']);
        }

        $jornada = Jornada::query()->where('cod_turno', $entrada['codTurno'])
            ->whereDate('fecha_jornada', $entrada['fechaInicio'])->lockForUpdate()->first();
        if (! $jornada || ! in_array($jornada->estado, ['PLANIFICADA', 'ABIERTA', 'ACTIVA', 'EN_CURSO'], true)) {
            throw ValidationException::withMessages(['fechaInicio' => 'Seleccione una jornada futura existente y abierta a planificación.']);
        }
        if (! $personal->asignaciones()->where('cod_jornada', $jornada->cod_jornada)->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->whereHas('area', fn ($query) => $query->whereIn('estado', ['ACTIVA', 'ACTIVO']))->lockForUpdate()->first()) {
            throw ValidationException::withMessages(['codEnfermero' => 'El profesional debe tener una asignación laboral activa en esa jornada.']);
        }
        if (! Residente::query()->whereKey($entrada['codResidente'])->whereIn('estado', ['ADMITIDO', 'ACTIVO', 'EST_001'])->lockForUpdate()->first()) {
            throw ValidationException::withMessages(['codResidente' => 'El residente no se encuentra admitido.']);
        }

        return $jornada;
    }

    public function finalizarAsignacion(User $usuario, array $entrada, string $id): void
    {

        abort_unless($usuario?->can('turnos.finalizar'), 403);
        AsignacionResidenteJornada::query()->findOrFail($id)->update(['estado' => 'FINALIZADA']);
    }
}
