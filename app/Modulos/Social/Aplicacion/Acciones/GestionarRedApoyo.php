<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\Contacto;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class GestionarRedApoyo
{
    public function autorizar(User $actor, string $permiso, bool $escritura = true): User
    {
        $actor = $actor->fresh();
        abort_unless($actor?->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR') && $actor->can($permiso), 403);
        abort_if($escritura && app(RolePreviewService::class)->isActive($actor), 403);

        return $actor;
    }

    public function guardarContacto(User $actor, array $entrada, ?string $codigo = null): Contacto
    {
        $actor = $this->autorizar($actor, 'contactos.gestionar');
        $datos = Validator::make($entrada, [
            'nombres' => ['required', 'string', 'max:100'], 'apellido_paterno' => ['required', 'string', 'max:80'],
            'apellido_materno' => ['nullable', 'string', 'max:80'], 'numero_documento' => ['nullable', 'string', 'max:30'],
            'telefono' => ['nullable', 'string', 'max:30'], 'celular' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:120'], 'direccion' => ['nullable', 'string', 'max:255'],
            'observacion' => ['nullable', 'string', 'max:4000'], 'cod_usuario' => ['prohibited'], 'estado' => ['prohibited'],
        ])->validate();

        return DB::transaction(function () use ($actor, $datos, $codigo) {
            $this->autorizar($actor, 'contactos.gestionar');
            $contacto = $codigo ? Contacto::lockForUpdate()->findOrFail($codigo) : new Contacto;
            $antes = $contacto->exists ? $contacto->getAttributes() : null;
            $contacto->fill($datos);
            if (! $contacto->exists) {
                $contacto->estado = 'ACTIVO';
            }
            $contacto->save();
            activity('Contactos')->causedBy($actor)->performedOn($contacto)->event($antes ? 'contacto_corregido' : 'contacto_registrado')
                ->withProperties(['antes' => $antes, 'cambios' => $contacto->getChanges()])->log('Guardó la identidad de contacto sin crear cuentas, vínculos o permisos.');

            return $contacto;
        });
    }

    public function vincular(User $actor, string $residenteId, string $contactoId, array $entrada, ?string $vinculoId = null): ResidenteContacto
    {
        $actor = $this->autorizar($actor, 'residentes_contactos.gestionar');
        if ($vinculoId) {
            app(ConservarVersionVinculo::class)->exigir();
        }
        $datos = Validator::make($entrada, [
            'parentesco' => ['required', 'string', 'max:40'], 'responsable_principal' => ['required', 'boolean'],
            'contacto_emergencia' => ['required', 'boolean'], 'observacion' => ['nullable', 'string', 'max:4000'],
            'autoriza_informacion' => ['prohibited'], 'autoriza_salida' => ['prohibited'], 'estado' => ['prohibited'],
        ])->validate();

        return DB::transaction(function () use ($actor, $residenteId, $contactoId, $datos, $vinculoId) {
            $this->autorizar($actor, 'residentes_contactos.gestionar');
            $residente = Residente::lockForUpdate()->findOrFail($residenteId);
            Gate::forUser($actor)->authorize('view', $residente);
            abort_unless($residente->estado === 'ADMITIDO', 422, 'Selecciona un residente admitido.');
            $contacto = Contacto::lockForUpdate()->findOrFail($contactoId);
            abort_unless($contacto->estado === 'ACTIVO', 422, 'El contacto debe estar activo.');
            $vinculo = $vinculoId
                ? ResidenteContacto::where('cod_residente', $residenteId)->where('cod_contacto', $contactoId)->lockForUpdate()->findOrFail($vinculoId)
                : new ResidenteContacto;
            $antes = $vinculo->exists ? $vinculo->getAttributes() : null;
            if ($antes) {
                app(ConservarVersionVinculo::class)->conservar($vinculo, null, null);
            }
            if (! $vinculo->exists) {
                if (ResidenteContacto::where('cod_residente', $residenteId)->where('cod_contacto', $contactoId)->where('estado', 'ACTIVO')->exists()) {
                    throw ValidationException::withMessages(['contactoId' => 'La persona ya tiene un vínculo activo con este residente.']);
                }
                $vinculo->cod_residente_contacto = app(CodigosEntidad::class)->siguiente('residentes_contactos');
                $vinculo->fill(['cod_residente' => $residenteId, 'cod_contacto' => $contactoId, 'estado' => 'ACTIVO', 'autoriza_informacion' => false, 'autoriza_salida' => false]);
            }
            $vinculo->fill($datos)->save();
            app(ConservarVersionVinculo::class)->conservar($vinculo, $actor, $antes ? 'Corrección de parentesco y funciones del vínculo.' : 'Registro original del vínculo.');
            activity('RedApoyo')->causedBy($actor)->performedOn($residente)->event($antes ? 'vinculo_corregido' : 'contacto_vinculado')
                ->withProperties(['cod_vinculo' => $vinculo->getKey(), 'antes' => $antes, 'despues' => $vinculo->getAttributes()])
                ->log('Conservó el vínculo sin conceder información o salida ni modificar otros residentes.');

            return $vinculo;
        });
    }

    public function cambiarVigencia(User $actor, string $residenteId, string $vinculoId, string $estado, string $motivo): void
    {
        $actor = $this->autorizar($actor, 'residentes_contactos.gestionar');
        app(ConservarVersionVinculo::class)->exigir();
        Validator::make(compact('estado', 'motivo'), ['estado' => ['required', 'in:ACTIVO,INACTIVO'], 'motivo' => ['required', 'string', 'min:5', 'max:4000']])->validate();
        DB::transaction(function () use ($actor, $residenteId, $vinculoId, $estado, $motivo) {
            $this->autorizar($actor, 'residentes_contactos.gestionar');
            $residente = Residente::lockForUpdate()->findOrFail($residenteId);
            Gate::forUser($actor)->authorize('view', $residente);
            $vinculo = ResidenteContacto::where('cod_residente', $residenteId)->lockForUpdate()->findOrFail($vinculoId);
            if ($vinculo->estado === $estado) {
                return;
            }
            $antes = $vinculo->estado;
            app(ConservarVersionVinculo::class)->conservar($vinculo, null, null);
            $vinculo->estado = $estado;
            $vinculo->save();
            app(ConservarVersionVinculo::class)->conservar($vinculo, $actor, $motivo);
            activity('RedApoyo')->causedBy($actor)->performedOn($residente)->event('vigencia_vinculo')
                ->withProperties(['cod_vinculo' => $vinculo->getKey(), 'estado_anterior' => $antes, 'estado_nuevo' => $estado, 'motivo' => $motivo])
                ->log('Cambió la vigencia del vínculo conservando la cuenta y las autorizaciones originales.');
        });
    }

    // Entradas existentes convergen en las mismas operaciones y bloqueos.
    public function guardarVinculo(User $actor, array $entrada): void
    {
        $form = $entrada['form'];
        DB::transaction(function () use ($actor, $entrada, $form) {
            $contactoId = $form['cod_contacto'] ?? '';
            if (! $contactoId) {
                $contactoId = $this->guardarContacto($actor, [
                    'nombres' => $form['nombres'] ?? '', 'apellido_paterno' => $form['ap_paterno'] ?? '',
                    'apellido_materno' => $form['ap_materno'] ?? null, 'celular' => $form['telefono'] ?? null,
                    'correo' => $form['correo'] ?? null, 'direccion' => $form['direccion'] ?? null,
                ])->getKey();
            }
            $this->vincular($actor, $entrada['adultoSeleccionado'], $contactoId, [
                'parentesco' => $form['parentesco_vinculo'], 'responsable_principal' => $form['es_responsable'],
                'contacto_emergencia' => $form['es_contacto_emergencia'], 'observacion' => $form['observaciones'] ?? null,
            ], ($entrada['modoEdicion'] ?? false) ? ($entrada['vinculoId'] ?? null) : null);
        });
    }

    public function marcarResponsable(User $actor, array $entrada, string $id): void
    {
        $this->marcar($actor, $entrada['adultoSeleccionado'], $id, 'responsable_principal');
    }

    public function marcarContactoEmergencia(User $actor, array $entrada, string $id): void
    {
        $this->marcar($actor, $entrada['adultoSeleccionado'], $id, 'contacto_emergencia');
    }

    private function marcar(User $actor, string $residente, string $id, string $campo): void
    {
        $v = ResidenteContacto::where('cod_residente', $residente)->findOrFail($id);
        $datos = $v->only(['parentesco', 'responsable_principal', 'contacto_emergencia', 'observacion']);
        $datos[$campo] = true;
        $this->vincular($actor, $residente, $v->cod_contacto, $datos, $id);
    }

    public function desactivarVinculo(User $actor, array $entrada, string $id): void
    {
        $this->cambiarVigencia($actor, $entrada['adultoSeleccionado'], $id, 'INACTIVO', $entrada['motivo'] ?? '');
    }

    public function activarVinculo(User $actor, array $entrada, string $id): void
    {
        $this->cambiarVigencia($actor, $entrada['adultoSeleccionado'], $id, 'ACTIVO', $entrada['motivo'] ?? '');
    }
}
