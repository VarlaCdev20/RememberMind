<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Models\Contacto;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class VincularContactoResidenteHttp
{
    public function vincularContacto(User $usuario, array $entrada, Residente $residente): mixed
    {
        Gate::forUser($usuario)->authorize('view', $residente);
        abort_unless($usuario->can('contactos.gestionar'), 403);
        $datos = Validator::make($entrada, ['cod_contacto' => ['nullable', 'exists:contactos,cod_contacto'], 'contacto' => ['nullable', 'array'], 'contacto.cod_usuario' => ['nullable', 'exists:usuarios,cod_usuario', 'unique:contactos,cod_usuario'], 'contacto.nombres' => ['required_without:cod_contacto', 'string', 'max:100'], 'contacto.apellido_paterno' => ['required_without:cod_contacto', 'string', 'max:80'], 'contacto.apellido_materno' => ['nullable', 'string', 'max:80'], 'contacto.numero_documento' => ['nullable', 'string', 'max:30'], 'contacto.telefono' => ['nullable', 'string', 'max:30'], 'contacto.celular' => ['nullable', 'string', 'max:30'], 'contacto.correo' => ['nullable', 'email', 'max:120'], 'contacto.direccion' => ['nullable', 'string', 'max:255'], 'parentesco' => ['required', 'string', 'max:40'], 'responsable_principal' => ['required', 'boolean'], 'contacto_emergencia' => ['required', 'boolean'], 'autoriza_informacion' => ['required', 'boolean'], 'autoriza_salida' => ['required', 'boolean'], 'observacion' => ['nullable', 'string']])->validate();
        $vinculo = DB::transaction(function () use ($datos, $residente): ResidenteContacto {
            $contacto = isset($datos['cod_contacto']) ? Contacto::query()->findOrFail($datos['cod_contacto']) : Contacto::query()->create(['cod_contacto' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('contactos'), ...$datos['contacto'], 'estado' => 'ACTIVO']);
            if ($datos['responsable_principal']) {
                ResidenteContacto::query()->where('cod_residente', $residente->cod_residente)->where('estado', 'ACTIVO')->update(['responsable_principal' => false]);
            }

            return ResidenteContacto::query()->create(['cod_residente_contacto' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('residentes_contactos'), 'cod_residente' => $residente->cod_residente, 'cod_contacto' => $contacto->cod_contacto, 'parentesco' => $datos['parentesco'], 'responsable_principal' => $datos['responsable_principal'], 'contacto_emergencia' => $datos['contacto_emergencia'], 'autoriza_informacion' => $datos['autoriza_informacion'], 'autoriza_salida' => $datos['autoriza_salida'], 'estado' => 'ACTIVO', 'observacion' => $datos['observacion'] ?? null]);
        });

        return $vinculo->load('contacto');
    }

}
