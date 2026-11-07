<?php

namespace App\Modulos\Admisiones\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\HistorialEstadoResidente;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class RegistrarEgresoResidente
{
    public function __construct(private readonly ContextoEjecucion $contexto) {}

    public function autorizar(Residente $residente, User $usuario): void
    {
        $actor = $usuario->fresh();
        abort_unless($this->contexto->usuario()?->getKey() === $usuario->getKey()
            && $actor?->estado === 'ACTIVO' && $actor->hasRole('ADMINISTRADOR') && ! $actor->hasRole('FAMILIAR')
            && $actor->can('residentes.gestionar') && ! app(RolePreviewService::class)->isActive($actor), 403);
        Gate::forUser($actor)->authorize('view', $residente);
    }

    public function ejecutar(Residente $residente, User $usuario, string $motivo, string $codAdmision, ?string $codOcupacion): void
    {
        $this->autorizar($residente, $usuario);
        $motivo = trim($motivo);
        Validator::make(['motivo' => $motivo], ['motivo' => ['required', 'string', 'max:1000']],
            ['motivo.required' => 'Indica el motivo del egreso.'])->validate();

        DB::transaction(function () use ($residente, $usuario, $motivo, $codAdmision, $codOcupacion): void {
            $residente = Residente::query()->lockForUpdate()->findOrFail($residente->getKey());
            $this->autorizar($residente, $usuario);
            $admisiones = $residente->admisiones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->lockForUpdate()->get();
            if (! in_array($residente->estado, ['ADMITIDO', 'ACTIVO'], true)
                || $admisiones->count() !== 1 || $admisiones->first()->getKey() !== $codAdmision) {
                throw ValidationException::withMessages(['egreso' => 'La admisión vigente cambió o ya fue cerrada. Actualiza el expediente antes de continuar.']);
            }

            $admision = $admisiones->sole();
            $ocupaciones = $residente->ocupacionesCama()->whereIn('estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA)->lockForUpdate()->get();
            $ocupacion = $ocupaciones->first();
            if ($ocupaciones->count() > 1 || $ocupacion?->getKey() !== $codOcupacion
                || ($ocupacion && $ocupacion->cod_admision !== $admision->getKey())) {
                throw ValidationException::withMessages(['egreso' => 'La ocupación cambió o no corresponde a la admisión. Revisa el alojamiento antes de continuar.']);
            }

            $fecha = now();
            $ultimaOcupacion = $admision->ocupacionesCama()->latest('fecha_hora_asignacion')->first();
            if ($fecha->lt($admision->fecha_hora_admision) || ($ultimaOcupacion && $fecha->lt($ultimaOcupacion->fecha_hora_liberacion ?? $ultimaOcupacion->fecha_hora_asignacion))) {
                throw ValidationException::withMessages(['egreso' => 'Revisa las fechas de admisión y alojamiento antes de confirmar el egreso.']);
            }

            if ($ocupacion) {
                $cama = Cama::query()->lockForUpdate()->findOrFail($ocupacion->cod_cama);
                $habitacion = Habitacion::query()->lockForUpdate()->findOrFail($cama->cod_habitacion);
                $ocupacion->update(['estado' => 'FINALIZADA', 'fecha_hora_liberacion' => $fecha, 'motivo_liberacion' => $motivo]);
                if ($cama->estado === 'OCUPADA') {
                    $cama->update(['estado' => 'DISPONIBLE']);
                }
                if ($habitacion->estado === 'OCUPADA' && Cama::query()->where('cod_habitacion', $habitacion->getKey())
                    ->whereIn('estado', FormalizarAdmision::ESTADOS_HABILITADOS)
                    ->whereDoesntHave('ocupaciones', fn ($query) => $query->whereIn('estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA))->exists()) {
                    $habitacion->update(['estado' => 'DISPONIBLE']);
                }
            }

            $estadoAnterior = $residente->estado;
            $admision->update(['estado' => 'FINALIZADA']);
            $residente->update(['estado' => 'INACTIVO']);
            $historial = HistorialEstadoResidente::create([
                'cod_residente' => $residente->getKey(), 'cod_usuario_registro' => $usuario->getKey(),
                'estado_anterior' => $estadoAnterior, 'estado_nuevo' => 'INACTIVO',
                'fecha_hora' => $fecha, 'motivo' => $motivo,
            ]);
            activity('Admisiones')->causedBy($usuario)->performedOn($admision)
                ->withProperties(['cod_historial_estado' => $historial->getKey(), 'cod_ocupacion' => $codOcupacion])
                ->log('Egreso definitivo del residente registrado.');
        }, 3);
    }
}
