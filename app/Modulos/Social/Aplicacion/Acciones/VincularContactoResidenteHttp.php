<?php

namespace App\Modulos\Social\Aplicacion\Acciones;

use App\Models\Residente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class VincularContactoResidenteHttp
{
    public function vincularContacto(User $usuario, array $entrada, Residente $residente): mixed
    {
        $a = app(GestionarRedApoyo::class);
        $a->autorizar($usuario, 'residentes_contactos.gestionar');
        Validator::make($entrada, ['contacto.cod_usuario' => ['prohibited'], 'autoriza_informacion' => ['nullable', 'declined'], 'autoriza_salida' => ['nullable', 'declined']])->validate();

        return DB::transaction(function () use ($a, $usuario, $entrada, $residente) {
            $id = $entrada['cod_contacto'] ?? null;
            if (! $id) {
                $id = $a->guardarContacto($usuario, $entrada['contacto'] ?? [])->getKey();
            }

            return $a->vincular($usuario, $residente->getKey(), $id, collect($entrada)->only(['parentesco', 'responsable_principal', 'contacto_emergencia', 'observacion'])->all())->load('contacto');
        });
    }
}
