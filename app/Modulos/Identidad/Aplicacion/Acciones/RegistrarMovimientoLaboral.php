<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Models\MovimientoLaboralPersonal;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class RegistrarMovimientoLaboral
{
    public function ejecutar(User $actor, string $personal, array $entrada): MovimientoLaboralPersonal
    {
        app(CapacidadDocumentalPersonal::class)->exigir();
        $actor = app(AutorizarGestionPersonal::class)->autorizar($actor);
        abort_unless($actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']), 403);
        $datos = Validator::make($entrada, ['estado_esperado' => ['required', 'in:ACTIVO,INACTIVO'],
            'estado_nuevo' => ['required', 'in:ACTIVO,INACTIVO', 'different:estado_esperado'],
            'fecha_hora_efecto' => ['required', 'date', 'before_or_equal:now'], 'motivo' => ['required', 'string', 'min:5', 'max:4000']])->validate();

        return DB::transaction(function () use ($actor, $personal, $datos) {
            $trabajador = Personal::with('usuario')->lockForUpdate()->findOrFail($personal);
            abort_unless($trabajador->usuario, 422, 'El trabajador no tiene una cuenta gestionable en este flujo.');
            app(AutorizarGestionPersonal::class)->autorizar($actor, $trabajador->usuario);
            $ultimo = MovimientoLaboralPersonal::where('cod_personal', $personal)->orderByDesc('fecha_hora_efecto')->first();
            if ($trabajador->estado !== $datos['estado_esperado'] || ($ultimo && Carbon::parse($datos['fecha_hora_efecto'])->lt($ultimo->fecha_hora_efecto))) {
                throw ValidationException::withMessages(['estado_esperado' => 'El estado o la secuencia cambiaron. Actualiza la ficha antes de confirmar.']);
            }
            $movimiento = MovimientoLaboralPersonal::create(['cod_personal' => $personal, 'estado_anterior' => $trabajador->estado,
                'estado_nuevo' => $datos['estado_nuevo'], 'fecha_hora_efecto' => $datos['fecha_hora_efecto'],
                'fecha_hora_registro' => now(), 'cod_usuario_registro' => $actor->getKey(), 'motivo' => trim($datos['motivo'])]);
            $trabajador->update(['estado' => $datos['estado_nuevo']]);
            activity('Personal')->causedBy($actor)->performedOn($trabajador)->event('estado_laboral')->log('Registró un movimiento laboral sin modificar la cuenta o eliminar asignaciones.');

            return $movimiento;
        });
    }
}
