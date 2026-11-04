<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class GestionarTurnosInstitucionales
{
    public function guardar(User $usuario, array $entrada): mixed
    {

        $usuario = $usuario->fresh();
        abort_unless($usuario?->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR') && $usuario->can('turnos.gestionar'), 403);
        abort_if(app(RolePreviewService::class)->isActive($usuario), 403);
        Validator::make($entrada, [
            'nombre' => ['required', 'string', 'max:50', Rule::unique('turnos', 'nombre')->ignore($entrada['editandoId'], 'cod_turno')],
            'horaInicio' => 'required|date_format:H:i',
            'horaFin' => 'required|date_format:H:i|different:horaInicio',
            'orden' => ['required', 'integer', 'min:1', 'max:10', Rule::unique('turnos', 'orden')->ignore($entrada['editandoId'], 'cod_turno')],
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'observacion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Ya existe un turno con este nombre.',
            'horaInicio.required' => 'La hora de inicio es obligatoria.',
            'horaFin.required' => 'La hora de fin es obligatoria.',
            'horaFin.different' => 'La hora de fin debe ser distinta a la hora de inicio.',
            'orden.required' => 'El orden es obligatorio.',
            'orden.unique' => 'Ya existe un turno con este número de orden.',
        ])->validate();

        $datos = [
            'nombre' => strtoupper(trim($entrada['nombre'])),
            'hora_inicio' => $entrada['horaInicio'].':00',
            'hora_cierre' => $entrada['horaFin'].':00',
            'orden' => (int) $entrada['orden'],
            'estado' => $entrada['estado'],
            'observacion' => $entrada['observacion'] ?: null,
        ];

        return DB::transaction(function () use ($entrada, $datos, $usuario) {
            if ($entrada['editandoId']) {
                $turno = Turno::lockForUpdate()->findOrFail($entrada['editandoId']);
                if (($turno->hora_inicio !== $datos['hora_inicio'] || $turno->hora_cierre !== $datos['hora_cierre']) && Jornada::where('cod_turno', $turno->getKey())->exists()) {
                    throw ValidationException::withMessages(['horaInicio' => 'Este turno ya tiene jornadas registradas. Crea otro turno para conservar los horarios anteriores.']);
                }
                $turno->update($datos);
                $msg = 'Turno actualizado correctamente.';
            } else {
                $turno = Turno::create($datos + ['cod_turno' => app(CodigosEntidad::class)->siguiente('turnos')]);
                $msg = 'Turno registrado correctamente.';
            }

            activity('Turnos')->causedBy($usuario)->performedOn($turno)->event('turno_guardado')->log($msg);

            return $msg;
        });
    }
}
