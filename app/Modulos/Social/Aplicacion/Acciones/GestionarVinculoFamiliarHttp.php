<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Models\Contacto;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class GestionarVinculoFamiliarHttp
{
    public function store(User $usuario, array $entrada, Residente $adulto_mayor): mixed
    {
        abort_unless($usuario->can('residentes_contactos.gestionar'), 403);
        $data = $entrada;
        DB::transaction(function () use ($data, $adulto_mayor): void {
            $contacto = filled($data['cod_fam'] ?? null) ? Contacto::query()->findOrFail($data['cod_fam']) : $this->crearContacto($data);
            if (! empty($data['es_responsable'])) {
                ResidenteContacto::query()->where('cod_residente', $adulto_mayor->cod_residente)->update(['responsable_principal' => false]);
            }
            $vinculo = ResidenteContacto::query()->firstOrNew(['cod_residente' => $adulto_mayor->cod_residente, 'cod_contacto' => $contacto->cod_contacto]);
            if (! $vinculo->exists) {
                $vinculo->cod_residente_contacto = app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('residentes_contactos');
            }
            $vinculo->fill(['parentesco' => $data['parentesco_vinculo'], 'responsable_principal' => (bool) $data['es_responsable'], 'contacto_emergencia' => (bool) $data['es_responsable'], 'autoriza_informacion' => true, 'autoriza_salida' => false, 'estado' => $data['estado'], 'observacion' => $data['observaciones'] ?? null])->save();
        });
        activity('Residentes')->performedOn($adulto_mayor)->event('contacto_vinculado')->log("Se vinculó un contacto al residente {$adulto_mayor->cod_residente}.");

        return null;
    }

    public function update(User $usuario, array $entrada, Residente $adulto_mayor, string $familiar): mixed
    {
        abort_unless($usuario->can('residentes_contactos.gestionar'), 403);
        $data = Validator::make($entrada, ['parentesco_vinculo' => ['required', 'string', 'max:40'], 'es_responsable' => ['required', 'boolean'], 'estado' => ['required', 'in:ACTIVO,INACTIVO'], 'observaciones' => ['nullable', 'string', 'max:1000']])->validate();
        DB::transaction(function () use ($data, $adulto_mayor, $familiar): void {
            $vinculo = $this->vinculo($adulto_mayor, $familiar);
            if ($data['es_responsable']) {
                ResidenteContacto::query()->where('cod_residente', $adulto_mayor->cod_residente)->where('cod_residente_contacto', '!=', $vinculo->cod_residente_contacto)->update(['responsable_principal' => false]);
            }
            $vinculo->update(['parentesco' => $data['parentesco_vinculo'], 'responsable_principal' => (bool) $data['es_responsable'], 'estado' => $data['estado'], 'observacion' => $data['observaciones'] ?? null]);
        });

        return null;
    }

    public function destroy(User $usuario, array $entrada, Residente $adulto_mayor, string $familiar): mixed
    {
        abort_unless($usuario->can('residentes_contactos.gestionar'), 403);
        $this->vinculo($adulto_mayor, $familiar)->update(['estado' => 'INACTIVO']);

        return null;
    }

    public function restore(User $usuario, array $entrada, Residente $adulto_mayor, string $familiar): mixed
    {
        abort_unless($usuario->can('residentes_contactos.gestionar'), 403);
        $this->vinculo($adulto_mayor, $familiar)->update(['estado' => 'ACTIVO']);

        return null;
    }

    private function crearContacto(array $data): Contacto
    {
        $partes = preg_split('/\s+/u', trim($data['nombre_nuevo'])) ?: [];
        $apellido = count($partes) > 1 ? array_pop($partes) : 'SIN APELLIDO';
        $nombres = trim(implode(' ', $partes)) ?: trim($data['nombre_nuevo']);

        return Contacto::query()->create(['cod_contacto' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('contactos'), 'nombres' => $nombres, 'apellido_paterno' => $apellido, 'correo' => $data['email_nuevo'] ?? null, 'estado' => 'ACTIVO']);
    }

    private function vinculo(Residente $adulto, string $id): ResidenteContacto
    {
        return ResidenteContacto::query()->where('cod_residente', $adulto->cod_residente)->where(function ($query) use ($id): void {
            $query->where('cod_residente_contacto', $id)->orWhere('cod_contacto', $id);
        })->firstOrFail();
    }
}
