<?php

namespace App\Backend\Modulos\Admisiones\Acciones;

use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Mantenimiento físico; no asigna residentes ni sustituye la admisión. */
class GuardarEspacioResidencial
{
    public function habitacion(array $datos, User $usuario, ?string $codigo = null): Habitacion
    {
        return DB::transaction(function () use ($datos, $usuario, $codigo) {
            $habitacion = $codigo ? Habitacion::lockForUpdate()->findOrFail($codigo) : new Habitacion;
            $this->autorizar($habitacion, $usuario);
            $datos = Validator::make($this->normalizar($datos), $this->reglas('habitaciones', 'cod_habitacion', $codigo, $habitacion->estado) + [
                'nombre' => ['nullable', 'string', 'max:80'], 'piso' => ['nullable', 'string', 'max:30'],
                'capacidad' => ['required', 'integer', 'min:1', 'max:50'],
            ], $this->mensajes())->validate();
            if ($habitacion->exists && (int) $datos['capacidad'] < $habitacion->camas()->count()) {
                throw ValidationException::withMessages(['capacidad' => 'La capacidad no puede ser menor al número de camas registradas.']);
            }
            $ocupada = $habitacion->exists && $habitacion->camas()->whereHas('ocupaciones', fn ($query) => $query->whereIn('estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA))->exists();
            $this->validarEstado($datos['estado'], $ocupada);
            $habitacion->fill($datos)->save();
            $this->auditar($habitacion, $usuario);

            return $habitacion;
        }, 3);
    }

    public function cama(array $datos, User $usuario, string $codigoHabitacion, ?string $codigo = null): Cama
    {
        return DB::transaction(function () use ($datos, $usuario, $codigoHabitacion, $codigo) {
            // Cama → habitación, como el traslado institucional.
            $cama = $codigo ? Cama::lockForUpdate()->findOrFail($codigo) : new Cama;
            $this->autorizar($cama, $usuario);
            abort_if($cama->exists && $cama->cod_habitacion !== $codigoHabitacion, 403);
            $habitacion = Habitacion::lockForUpdate()->findOrFail($codigoHabitacion);
            $datos = Validator::make($this->normalizar($datos), $this->reglas('camas', 'cod_cama', $codigo, $cama->estado), $this->mensajes())->validate();
            if (! $cama->exists && $habitacion->camas()->count() >= $habitacion->capacidad) {
                throw ValidationException::withMessages(['codigo' => 'La habitación alcanzó su capacidad. Amplíela antes de registrar otra cama física.']);
            }
            $ocupada = $cama->exists && $cama->ocupaciones()->whereIn('estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA)->exists();
            $this->validarEstado($datos['estado'], $ocupada);
            $cama->fill($datos + ['cod_habitacion' => $habitacion->getKey()])->save();
            $this->auditar($cama, $usuario);

            return $cama;
        }, 3);
    }

    private function autorizar(Habitacion|Cama $espacio, User $usuario): void
    {
        abort_unless(auth()->id() === $usuario->getKey(), 403);
        Gate::forUser($usuario)->authorize($espacio->exists ? 'update' : 'create', $espacio->exists ? $espacio : $espacio::class);
    }

    private function reglas(string $tabla, string $pk, ?string $codigo, ?string $estadoActual): array
    {
        return [
            'codigo' => ['required', 'string', 'max:30', Rule::unique($tabla, 'codigo')->ignore($codigo, $pk)],
            'tipo' => ['nullable', 'string', 'max:40'],
            'estado' => ['required', Rule::in(array_unique(array_filter(['ACTIVA', 'ACTIVO', 'DISPONIBLE', 'MANTENIMIENTO', 'BLOQUEADA', $estadoActual])))],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function normalizar(array $datos): array
    {
        $datos = array_map(fn ($valor) => is_string($valor) ? (trim($valor) === '' ? null : trim($valor)) : $valor, $datos);
        if (is_string($datos['codigo'] ?? null)) {
            $datos['codigo'] = mb_strtoupper($datos['codigo']);
        }

        return $datos;
    }

    private function validarEstado(string $estado, bool $ocupada): void
    {
        if ($ocupada && ! in_array($estado, [...FormalizarAdmision::ESTADOS_HABILITADOS, 'OCUPADA'], true)) {
            throw ValidationException::withMessages(['estado' => 'Hay una ocupación activa. Traslade o finalice el alojamiento antes de deshabilitar este espacio.']);
        }
        if (! $ocupada && $estado === 'OCUPADA') {
            throw ValidationException::withMessages(['estado' => 'La ocupación se registra mediante admisión o traslado, no desde este formulario.']);
        }
    }

    private function auditar(Habitacion|Cama $espacio, User $usuario): void
    {
        activity('infraestructura')->performedOn($espacio)->causedBy($usuario)->withProperties(['codigo' => $espacio->getKey(), 'estado' => $espacio->estado])
            ->log($espacio->wasRecentlyCreated ? 'Espacio residencial registrado' : 'Espacio residencial actualizado');
    }

    private function mensajes(): array
    {
        return ['codigo.required' => 'Indique una referencia para identificar el espacio.', 'codigo.unique' => 'Esta referencia ya está registrada.', 'capacidad.min' => 'Indique al menos una plaza.', 'estado.in' => 'Seleccione un estado registrado válido.'];
    }
}
