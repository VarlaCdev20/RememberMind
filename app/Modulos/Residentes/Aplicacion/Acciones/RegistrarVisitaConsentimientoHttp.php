<?php

namespace App\Modulos\Residentes\Aplicacion\Acciones;

use App\Models\Consentimiento;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class RegistrarVisitaConsentimientoHttp
{
    public function registrarVisita(User $usuario, array $entrada, Residente $residente): mixed
    {
        Gate::forUser($usuario)->authorize('view', $residente);
        abort_unless($usuario->can('visitas.gestionar'), 403);
        $datos = Validator::make($entrada, ['cod_contacto' => ['required', 'exists:contactos,cod_contacto'], 'fecha_hora_programada' => ['nullable', 'date'], 'fecha_hora_ingreso' => ['nullable', 'date'], 'fecha_hora_salida' => ['nullable', 'date', 'after_or_equal:fecha_hora_ingreso'], 'motivo' => ['nullable', 'string', 'max:160'], 'estado' => ['required', 'in:PROGRAMADA,AUTORIZADA,EN_CURSO,FINALIZADA,CANCELADA'], 'observacion' => ['nullable', 'string']])->validate();
        abort_unless(ResidenteContacto::query()->where('cod_residente', $residente->cod_residente)->where('cod_contacto', $datos['cod_contacto'])->where('estado', 'ACTIVO')->exists(), 422, 'El contacto no está vinculado al residente.');

        return Visita::query()->create(['cod_visita' => $this->codigo('VIS'), 'cod_residente' => $residente->cod_residente, 'cod_usuario_autorizacion' => $usuario->cod_usuario, ...$datos]);
    }

    public function registrarConsentimiento(User $usuario, array $entrada, Residente $residente): mixed
    {
        Gate::forUser($usuario)->authorize('view', $residente);
        abort_unless($usuario->can('consentimientos.gestionar'), 403);
        $admision = $residente->admisiones()->where('estado', 'ACTIVA')->latest('fecha_hora_admision')->firstOrFail();
        $datos = Validator::make($entrada, ['cod_residente_contacto' => ['nullable', 'exists:residentes_contactos,cod_residente_contacto'], 'cod_documento' => ['nullable', 'exists:documentos,cod_documento'], 'tipo_consentimiento' => ['required', 'string', 'max:80'], 'firma_residente' => ['required', 'boolean'], 'fecha_consentimiento' => ['nullable', 'date'], 'observacion' => ['nullable', 'string']])->validate();
        if (isset($datos['cod_residente_contacto'])) {
            abort_unless(ResidenteContacto::query()->whereKey($datos['cod_residente_contacto'])->where('cod_residente', $residente->cod_residente)->where('estado', 'ACTIVO')->exists(), 422, 'El firmante no está vinculado al residente.');
        }

        return Consentimiento::query()->create(['cod_consentimiento' => $this->codigo('CON'), 'cod_residente' => $residente->cod_residente, 'cod_admision' => $admision->cod_admision, 'cod_usuario_registro' => $usuario->cod_usuario, ...$datos, 'fecha_consentimiento' => $datos['fecha_consentimiento'] ?? now(), 'estado' => 'VIGENTE']);
    }

    private function codigo(string $prefijo): string
    {
        return $prefijo.'_'.Str::upper(Str::random(12));
    }
}
