<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Models\Contacto;
use App\Models\ResidenteContacto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarRedApoyo
{
    public function guardarVinculo(User $usuario, array $entrada): void
    {
        abort_unless($usuario->can('residentes_contactos.gestionar'), 403);
        Validator::make($entrada, $this->rules($usuario, $entrada))->validate();
        DB::transaction(function () use ($usuario, $entrada) {
            $contacto = $this->resolverContacto($usuario, $entrada);

            if ((bool) $entrada['form']['es_responsable']) {
                ResidenteContacto::where('cod_residente', $entrada['adultoSeleccionado'])
                    ->when($entrada['vinculoId'], fn ($q) => $q->where('cod_residente_contacto', '!=', $entrada['vinculoId']))
                    ->update(['responsable_principal' => false]);
            }

            if ((bool) $entrada['form']['es_contacto_emergencia']) {
                ResidenteContacto::where('cod_residente', $entrada['adultoSeleccionado'])
                    ->when($entrada['vinculoId'], fn ($q) => $q->where('cod_residente_contacto', '!=', $entrada['vinculoId']))
                    ->update(['contacto_emergencia' => false]);
            }

            $payload = [
                'cod_contacto' => $contacto->cod_contacto,
                'cod_residente' => $entrada['adultoSeleccionado'],
                'parentesco' => $entrada['form']['parentesco_vinculo'],
                'responsable_principal' => (bool) $entrada['form']['es_responsable'],
                'contacto_emergencia' => (bool) $entrada['form']['es_contacto_emergencia'],
                'estado' => $entrada['form']['estado'],
                'observacion' => $entrada['form']['observaciones'] ?: null,
            ];

            if ($entrada['modoEdicion'] && $entrada['vinculoId']) {
                ResidenteContacto::where('cod_residente_contacto', $entrada['vinculoId'])->update($payload);
            } else {
                $payload['cod_residente_contacto'] = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('residentes_contactos');
                ResidenteContacto::create($payload);
            }
        });

    }

    public function marcarResponsable(User $usuario, array $entrada, string $vinculoId): void
    {
        abort_unless($usuario->can('residentes_contactos.gestionar'), 403);
        ResidenteContacto::where('cod_residente', $entrada['adultoSeleccionado'])->update(['responsable_principal' => false]);
        ResidenteContacto::where('cod_residente_contacto', $vinculoId)->update(['responsable_principal' => true, 'estado' => 'ACTIVO']);

    }

    public function marcarContactoEmergencia(User $usuario, array $entrada, string $vinculoId): void
    {
        abort_unless($usuario->can('residentes_contactos.gestionar'), 403);
        ResidenteContacto::where('cod_residente', $entrada['adultoSeleccionado'])->update(['contacto_emergencia' => false]);
        ResidenteContacto::where('cod_residente_contacto', $vinculoId)->update(['contacto_emergencia' => true, 'estado' => 'ACTIVO']);

    }

    public function desactivarVinculo(User $usuario, array $entrada, string $vinculoId): void
    {
        abort_unless($usuario->can('residentes_contactos.gestionar'), 403);
        ResidenteContacto::where('cod_residente_contacto', $vinculoId)
            ->where('cod_residente', $entrada['adultoSeleccionado'])
            ->update(['estado' => 'INACTIVO']);

    }

    public function activarVinculo(User $usuario, array $entrada, string $vinculoId): void
    {
        abort_unless($usuario->can('residentes_contactos.gestionar'), 403);
        ResidenteContacto::where('cod_residente_contacto', $vinculoId)
            ->where('cod_residente', $entrada['adultoSeleccionado'])
            ->update(['estado' => 'ACTIVO']);

    }

    private function resolverContacto(User $usuario, array $entrada): Contacto
    {
        if (! empty($entrada['form']['cod_contacto'])) {
            $contacto = Contacto::findOrFail($entrada['form']['cod_contacto']);
            $contacto->update([
                'nombres' => $entrada['form']['nombres'] ?: $contacto->nombres,
                'apellido_paterno' => $entrada['form']['ap_paterno'] ?: $contacto->apellido_paterno,
                'apellido_materno' => $entrada['form']['ap_materno'] ?: null,
                'celular' => $entrada['form']['telefono'] ?: null,
                'correo' => $entrada['form']['correo'] ?: null,
                'direccion' => $entrada['form']['direccion'] ?: null,
            ]);

            return $contacto;
        }

        return Contacto::create([
            'cod_contacto' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('contactos'),
            'nombres' => $entrada['form']['nombres'],
            'apellido_paterno' => $entrada['form']['ap_paterno'],
            'apellido_materno' => $entrada['form']['ap_materno'] ?: null,
            'celular' => $entrada['form']['telefono'] ?: null,
            'correo' => $entrada['form']['correo'] ?: null,
            'direccion' => $entrada['form']['direccion'] ?: null,
            'estado' => 'ACTIVO',
        ]);
    }

    protected function rules(User $usuario, array $entrada): array
    {
        $requiereNuevo = ! $entrada['modoEdicion'] && empty($entrada['form']['cod_contacto']);

        return [
            'adultoSeleccionado' => ['required', 'exists:residentes,cod_residente'],
            'form.cod_contacto' => ['nullable', 'exists:contactos,cod_contacto'],
            'form.nombres' => [$requiereNuevo ? 'required' : 'nullable', 'string', 'max:100'],
            'form.ap_paterno' => [$requiereNuevo ? 'required' : 'nullable', 'string', 'max:80'],
            'form.ap_materno' => ['nullable', 'string', 'max:80'],
            'form.correo' => ['nullable', 'email', 'max:120'],
            'form.telefono' => ['nullable'],
            'form.direccion' => ['nullable', 'string', 'max:150'],
            'form.parentesco_vinculo' => ['required', 'string', 'max:100'],
            'form.es_responsable' => ['boolean'],
            'form.es_contacto_emergencia' => ['boolean'],
            'form.estado' => ['required', 'in:ACTIVO,INACTIVO'],
            'form.observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
