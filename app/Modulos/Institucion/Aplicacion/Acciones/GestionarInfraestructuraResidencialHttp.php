<?php

namespace App\Modulos\Institucion\Aplicacion\Acciones;

use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarInfraestructuraResidencialHttp
{
    public function habitacion(User $usuario, array $entrada): mixed
    {
        abort_unless($usuario->can('habitaciones.gestionar'), 403);
        $datos = Validator::make($entrada, ['codigo' => ['required', 'string', 'max:30', 'unique:habitaciones,codigo'], 'nombre' => ['nullable', 'string', 'max:80'], 'tipo' => ['nullable', 'string', 'max:40'], 'piso' => ['nullable', 'string', 'max:30'], 'capacidad' => ['required', 'integer', 'min:1'], 'observacion' => ['nullable', 'string']])->validate();

        return Habitacion::query()->create(['cod_habitacion' => $this->codigo('HAB'), ...$datos, 'estado' => 'ACTIVA']);
    }

    public function cama(User $usuario, array $entrada, Habitacion $habitacion): mixed
    {
        abort_unless($usuario->can('camas.gestionar'), 403);
        abort_if($habitacion->camas()->count() >= $habitacion->capacidad, 422, 'La habitación alcanzó su capacidad.');
        $datos = Validator::make($entrada, ['codigo' => ['required', 'string', 'max:30', 'unique:camas,codigo'], 'tipo' => ['nullable', 'string', 'max:40'], 'observacion' => ['nullable', 'string']])->validate();

        return Cama::query()->create(['cod_cama' => $this->codigo('CAM'), 'cod_habitacion' => $habitacion->cod_habitacion, ...$datos, 'estado' => 'ACTIVA']);
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
