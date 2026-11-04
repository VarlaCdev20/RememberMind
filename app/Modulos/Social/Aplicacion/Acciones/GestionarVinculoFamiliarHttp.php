<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class GestionarVinculoFamiliarHttp
{
    public function store(User $usuario, array $entrada, Residente $residente): mixed
    {
        $a = app(GestionarRedApoyo::class);
        $a->autorizar($usuario, 'residentes_contactos.gestionar');

        return DB::transaction(function () use ($a, $usuario, $entrada, $residente) {
            $id = $entrada['cod_contacto'] ?? null;
            if (! $id) {
                $id = $a->guardarContacto($usuario, Validator::make($entrada, [
                    'nombres' => ['required', 'string', 'max:100'], 'apellido_paterno' => ['required', 'string', 'max:80'],
                    'apellido_materno' => ['nullable', 'string', 'max:80'], 'correo' => ['nullable', 'email', 'max:120'],
                ])->validate())->getKey();
            }

            return $a->vincular($usuario, $residente->getKey(), $id, $this->datos($entrada));
        });
    }

    public function update(User $usuario, array $entrada, Residente $residente, string $vinculo): mixed
    {
        $registro = $this->vinculo($residente, $vinculo);
        if (isset($entrada['estado']) && $entrada['estado'] !== $registro->estado) {
            throw ValidationException::withMessages(['estado' => 'El cambio de vigencia requiere motivo y se realiza desde Familiares y contactos.']);
        }

        return app(GestionarRedApoyo::class)->vincular($usuario, $residente->getKey(), $registro->cod_contacto, $this->datos($entrada), $registro->getKey());
    }

    public function destroy(User $usuario, array $entrada, Residente $residente, string $vinculo): mixed
    {
        app(GestionarRedApoyo::class)->cambiarVigencia($usuario, $residente->getKey(), $this->vinculo($residente, $vinculo)->getKey(), 'INACTIVO', $entrada['motivo'] ?? '');

        return null;
    }

    public function restore(User $usuario, array $entrada, Residente $residente, string $vinculo): mixed
    {
        app(GestionarRedApoyo::class)->cambiarVigencia($usuario, $residente->getKey(), $this->vinculo($residente, $vinculo)->getKey(), 'ACTIVO', $entrada['motivo'] ?? '');

        return null;
    }

    private function datos(array $entrada): array
    {
        return ['parentesco' => $entrada['parentesco_vinculo'] ?? '', 'responsable_principal' => $entrada['es_responsable'] ?? false,
            'contacto_emergencia' => $entrada['contacto_emergencia'] ?? false, 'observacion' => $entrada['observaciones'] ?? null];
    }

    private function vinculo(Residente $residente, string $id): ResidenteContacto
    {
        return ResidenteContacto::where('cod_residente', $residente->getKey())->where(fn ($q) => $q->where('cod_residente_contacto', $id)->orWhere('cod_contacto', $id))->firstOrFail();
    }
}
