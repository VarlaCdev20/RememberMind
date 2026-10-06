<?php

namespace App\Backend\Modulos\Admisiones\Acciones;

use App\Models\Admision;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\OcupacionCama;
use App\Models\Residente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/** Traslado interno o nueva ocupación dentro de una admisión ya formalizada. */
class CambiarAlojamientoResidente
{
    public function autorizar(Residente $residente, User $usuario): void
    {
        abort_unless(auth()->id() === $usuario->getKey() && $usuario->estado === 'ACTIVO'
            && $usuario->can('ocupaciones_cama.gestionar'), 403);
        Gate::forUser($usuario)->authorize('viewAny', Residente::class);
        Gate::forUser($usuario)->authorize('view', $residente);
    }

    public function ejecutar(Residente $residente, array $datos, User $usuario): OcupacionCama
    {
        $this->autorizar($residente, $usuario);
        $datos = Validator::make($datos, [
            'cod_cama' => ['required', 'string', 'exists:camas,cod_cama'],
            'cod_ocupacion_anterior' => ['present', 'nullable', 'string'],
            'fecha_hora' => ['required', 'date'],
            'motivo' => ['nullable', 'string', 'max:1000'],
        ], ['cod_cama.required' => 'Seleccione una cama disponible.', 'fecha_hora.required' => 'Indique cuándo se realiza el cambio.'])->validate();

        return DB::transaction(function () use ($residente, $datos, $usuario): OcupacionCama {
            $residente = Residente::query()->lockForUpdate()->findOrFail($residente->getKey());
            $this->autorizar($residente, $usuario);
            if (! in_array($residente->estado, ['ACTIVO', 'ADMITIDO'], true)) {
                throw ValidationException::withMessages(['residente' => 'El residente no tiene una estancia activa. Revise su estado antes de cambiar el alojamiento.']);
            }
            $admisiones = Admision::query()->where('cod_residente', $residente->getKey())
                ->whereIn('estado', ['ACTIVA', 'ACTIVO'])->lockForUpdate()->get();
            if ($admisiones->count() !== 1) {
                throw ValidationException::withMessages(['residente' => 'Debe existir una única admisión formal activa. La primera cama se registra al formalizar la admisión.']);
            }
            $admision = $admisiones->sole();
            $ocupaciones = $residente->ocupacionesCama()->whereIn('estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA)->lockForUpdate()->get();
            if ($ocupaciones->count() > 1) {
                throw ValidationException::withMessages(['residente' => 'Hay más de una ocupación activa. Revise el historial de alojamiento antes de continuar.']);
            }
            $anterior = $ocupaciones->first();
            if ($anterior?->getKey() !== ($datos['cod_ocupacion_anterior'] ?? null)) {
                throw ValidationException::withMessages(['cod_cama' => 'El alojamiento cambió mientras completaba el formulario. Cierre y vuelva a abrir para revisar la ubicación actual.']);
            }
            if ($anterior && $anterior->cod_admision !== $admision->getKey()) {
                throw ValidationException::withMessages(['residente' => 'La ocupación actual no corresponde a la admisión activa. Revise el historial antes de continuar.']);
            }
            $ultima = $admision->ocupacionesCama()->orderByDesc('fecha_hora_asignacion')->orderByDesc('cod_ocupacion')->first();
            if (! $ultima) {
                throw ValidationException::withMessages(['residente' => 'Esta admisión no tiene la ocupación inicial registrada. Revise la admisión formal; este cambio no puede sustituirla.']);
            }
            $fecha = Carbon::parse($datos['fecha_hora']);
            $inicio = $anterior?->fecha_hora_asignacion ?? $ultima->fecha_hora_liberacion ?? $ultima->fecha_hora_asignacion;
            if ($fecha->lt($inicio) || $fecha->lt($admision->fecha_hora_admision)) {
                throw ValidationException::withMessages(['fecha_hora' => 'La fecha del cambio no puede ser anterior a la admisión ni a la ocupación vigente o su última liberación.']);
            }
            if ($anterior?->cod_cama === $datos['cod_cama']) {
                throw ValidationException::withMessages(['cod_cama' => 'Esta es la cama actual. Elija otra cama para registrar el traslado.']);
            }

            // Orden común para bloquear origen y destino; los índices de la BDD
            // conservan la exclusividad también frente a otros consumidores.
            $codigos = array_values(array_filter([$anterior?->cod_cama, $datos['cod_cama']]));
            $camas = Cama::query()->whereKey($codigos)->orderBy('cod_cama')->lockForUpdate()->get()->keyBy('cod_cama');
            $destino = $camas->get($datos['cod_cama']);
            abort_unless($destino, 404);
            $habitaciones = Habitacion::query()->whereKey($camas->pluck('cod_habitacion')->unique())
                ->orderBy('cod_habitacion')->lockForUpdate()->get()->keyBy('cod_habitacion');
            if (! FormalizarAdmision::filtrarCamasDisponibles(Cama::query())->whereKey($destino->getKey())->exists()) {
                throw ValidationException::withMessages(['cod_cama' => 'La cama o su habitación ya no están disponibles. El alojamiento anterior se conserva; elija otra cama.']);
            }

            if ($anterior) {
                $anterior->update(['estado' => 'FINALIZADA', 'fecha_hora_liberacion' => $fecha, 'motivo_liberacion' => trim($datos['motivo'] ?? '') ?: null]);
                $origen = $camas->get($anterior->cod_cama);
                if ($origen?->estado === 'OCUPADA') {
                    $origen->update(['estado' => 'DISPONIBLE']);
                }
                $habitacionOrigen = $habitaciones->get($origen?->cod_habitacion);
                if ($habitacionOrigen?->estado === 'OCUPADA' && Cama::query()->where('cod_habitacion', $habitacionOrigen->getKey())
                    ->whereIn('estado', FormalizarAdmision::ESTADOS_HABILITADOS)
                    ->whereDoesntHave('ocupaciones', fn ($query) => $query->whereIn('estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA))->exists()) {
                    $habitacionOrigen->update(['estado' => 'DISPONIBLE']);
                }
            }
            $nueva = OcupacionCama::query()->create([
                'cod_residente' => $residente->getKey(), 'cod_admision' => $admision->getKey(),
                'cod_cama' => $destino->getKey(), 'cod_usuario_registro' => $usuario->getKey(),
                'fecha_hora_asignacion' => $fecha, 'estado' => 'ACTIVA',
            ]);
            activity('Admisiones')->causedBy($usuario)->performedOn($nueva)->withProperties([
                'cod_admision' => $admision->getKey(), 'cod_ocupacion_anterior' => $anterior?->getKey(),
                'cod_cama_anterior' => $anterior?->cod_cama, 'cod_cama_nueva' => $destino->getKey(),
                'fecha_hora' => $fecha->toIso8601String(),
                'motivo' => trim($datos['motivo'] ?? '') ?: null,
            ])->log($anterior ? 'Traslado interno de alojamiento registrado.' : 'Alojamiento asignado dentro de la admisión vigente.');

            return $nueva->load('cama.habitacion');
        }, 3);
    }
}
