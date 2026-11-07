<?php

namespace App\Modulos\Residentes\Aplicacion\Acciones;

use App\Models\Residente;
use App\Models\User;
use App\Models\Visita;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class GestionarVisitaResidente
{
    public function autorizar(?User $actor, Residente $residente): void
    {
        abort_unless($actor && auth()->id() === $actor->getKey() && $actor->estado === 'ACTIVO'
            && $actor->hasRole('ADMINISTRADOR') && ! $actor->hasRole('FAMILIAR') && $actor->hasAllPermissions(['visitas.ver', 'visitas.gestionar'])
            && ! app(RolePreviewService::class)->isActive($actor), 403);
        Gate::forUser($actor)->authorize('view', $residente);
    }

    public function programar(User $actor, Residente $residente, array $datos): void
    {
        $this->autorizar($actor, $residente);
        DB::transaction(function () use ($actor, $residente, $datos): void {
            $residente = Residente::query()->lockForUpdate()->findOrFail($residente->getKey());
            $this->autorizar($actor->fresh(), $residente);
            $this->comprobarResidenteActivo($residente);
            $visita = app(RegistrarVisitaConsentimientoHttp::class)->registrarVisita($actor, [
                'cod_contacto' => $datos['contacto'], 'fecha_hora_programada' => $datos['fecha'],
                'motivo' => $datos['motivo'], 'estado' => 'PROGRAMADA',
            ], $residente);
            activity('Visitas')->causedBy($actor)->performedOn($visita)->log('Visita programada.');
        });
    }

    public function cambiar(User $actor, Residente $residente, string $codigo, string $accion): void
    {
        $this->autorizar($actor, $residente);
        DB::transaction(function () use ($actor, $residente, $codigo, $accion): void {
            $residente = Residente::query()->lockForUpdate()->findOrFail($residente->getKey());
            $this->autorizar($actor->fresh(), $residente);
            $visita = Visita::query()->where('cod_residente', $residente->getKey())->lockForUpdate()->findOrFail($codigo);
            $pendiente = in_array($visita->estado, ['PROGRAMADA', 'AUTORIZADA'], true)
                && ! $visita->fecha_hora_ingreso && ! $visita->fecha_hora_salida;
            $ahora = now();
            $cambios = match (true) {
                $accion === 'entrada' && $pendiente => ['estado' => 'EN_CURSO', 'fecha_hora_ingreso' => $ahora],
                $accion === 'salida' && $visita->estado === 'EN_CURSO' && $visita->fecha_hora_ingreso
                    && ! $visita->fecha_hora_salida && $ahora->gte($visita->fecha_hora_ingreso) => ['estado' => 'FINALIZADA', 'fecha_hora_salida' => $ahora],
                $accion === 'cancelar' && $pendiente => ['estado' => 'CANCELADA'],
                default => throw ValidationException::withMessages(['visita' => 'La visita cambió de estado o la acción ya fue registrada.']),
            };
            if ($accion === 'entrada') {
                $this->comprobarResidenteActivo($residente);
                if (! $residente->vinculosContacto()->where('cod_contacto', $visita->cod_contacto)->where('estado', 'ACTIVO')->exists()) {
                    throw ValidationException::withMessages(['visita' => 'El contacto ya no tiene un vínculo activo con el residente.']);
                }
            }
            $anterior = $visita->estado;
            $visita->update($cambios);
            activity('Visitas')->causedBy($actor)->performedOn($visita)
                ->withProperties(['estado_anterior' => $anterior, 'estado_nuevo' => $visita->estado])->log('Estado de visita actualizado.');
        });
    }

    private function comprobarResidenteActivo(Residente $residente): void
    {
        if (! in_array($residente->estado, ['ADMITIDO', 'ACTIVO', 'EST_001'], true)) {
            throw ValidationException::withMessages(['visita' => 'El residente debe estar admitido para programar o iniciar una visita.']);
        }
    }
}
