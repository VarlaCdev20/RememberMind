<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Compartido\Infraestructura\Notificaciones\ExigirTransporteCorreoReal;
use App\Models\Contacto;
use App\Models\ResidenteContacto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

final class GestionarAccesoFamiliar
{
    public function autorizar(User $actor, Contacto $contacto): User
    {
        $actor = $actor->fresh();
        abort_unless($actor?->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR') && $actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR']) && $actor->can('contactos.gestionar'), 403);
        abort_if(app(RolePreviewService::class)->isActive($actor), 403);
        if ($contacto->cod_usuario) {
            $usuario = User::findOrFail($contacto->cod_usuario);
            abort_unless($usuario->getRoleNames()->all() === ['FAMILIAR'] && ! $usuario->personal, 403);
        }

        return $actor;
    }

    public function crear(User $actor, string $contactoId, string $correo): User
    {
        $contacto = Contacto::findOrFail($contactoId);
        $actor = $this->autorizar($actor, $contacto);
        $datos = Validator::make(['correo' => mb_strtolower(trim($correo))], ['correo' => ['required', 'email', 'max:120', 'unique:usuarios,correo']])->validate();
        app(ExigirTransporteCorreoReal::class)->verificar();
        // Una clave aleatoria inaccesible evita una credencial compartida: solo el enlace de un uso permite elegir la propia.
        $usuario = DB::transaction(function () use ($actor, $contactoId, $datos) {
            $contacto = Contacto::lockForUpdate()->findOrFail($contactoId);
            $this->autorizar($actor, $contacto);
            if ($contacto->cod_usuario) {
                throw ValidationException::withMessages(['correo' => 'Este contacto ya tiene una cuenta. Usa su recuperación de acceso.']);
            }
            abort_unless($contacto->estado === 'ACTIVO', 422, 'El contacto debe estar activo.');
            $apto = ResidenteContacto::where('cod_contacto', $contactoId)->where('estado', 'ACTIVO')->where('autoriza_informacion', true)
                ->whereHas('residente', fn ($q) => $q->where('estado', 'ADMITIDO'))->exists();
            if (! $apto) {
                throw ValidationException::withMessages(['correo' => 'Falta un vínculo activo con autorización de información ya acreditada. El alta no concede esa autorización.']);
            }
            $usuario = User::create(['correo' => $datos['correo'], 'password' => Str::password(64), 'estado' => 'ACTIVO']);
            $usuario->assignRole('FAMILIAR');
            $contacto->update(['cod_usuario' => $usuario->getKey()]);
            activity('Familiares')->causedBy($actor)->performedOn($contacto)->event('cuenta_familiar_creada')
                ->log('Creó una cuenta Familiar vinculada; el titular debe elegir su contraseña mediante enlace.');

            return $usuario;
        });
        // Si el transporte falla la cuenta sigue identificada y se puede reemitir; nunca se afirma que la operación SQL no ocurrió.
        try {
            $this->enviarEnlace($usuario);
        } catch (\Throwable $error) {
            report($error);
            throw ValidationException::withMessages(['correo' => 'La cuenta fue creada, pero no se pudo completar el envío del enlace. Usa Enviar enlace de acceso; no vuelvas a crear la cuenta.']);
        }

        return $usuario;
    }

    public function recuperar(User $actor, string $contactoId): void
    {
        $contacto = Contacto::findOrFail($contactoId);
        $actor = $this->autorizar($actor, $contacto);
        $usuario = $contacto->usuario;
        abort_unless($usuario, 422, 'El contacto no tiene una cuenta.');
        if ($usuario->estado !== 'ACTIVO') {
            throw ValidationException::withMessages(['acceso' => 'Activa el acceso antes de solicitar el enlace.']);
        }
        app(ExigirTransporteCorreoReal::class)->verificar();
        $this->enviarEnlace($usuario);
        activity('Familiares')->causedBy($actor)->performedOn($contacto)->event('enlace_familiar_solicitado')->log('Solicitó un enlace seguro sin cambiar vínculos ni contraseña.');
    }

    private function enviarEnlace(User $usuario): void
    {
        $estado = Password::broker(config('fortify.passwords'))->sendResetLink([Fortify::email() => $usuario->correo]);
        if ($estado !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['acceso' => __($estado)]);
        }
    }

    public function actualizarCorreo(User $actor, string $contactoId, string $correo): void
    {
        DB::transaction(function () use ($actor, $contactoId, $correo) {
            $contacto = Contacto::lockForUpdate()->findOrFail($contactoId);
            $actor = $this->autorizar($actor, $contacto);
            $usuario = User::lockForUpdate()->findOrFail($contacto->cod_usuario);
            $datos = Validator::make(['correo' => mb_strtolower(trim($correo))], ['correo' => ['required', 'email', 'max:120', Rule::unique('usuarios', 'correo')->ignore($usuario->getKey(), 'cod_usuario')]])->validate();
            if ($usuario->correo === $datos['correo']) {
                return;
            }
            Password::broker(config('fortify.passwords'))->deleteToken($usuario);
            $usuario->update(['correo' => $datos['correo']]);
            activity('Familiares')->causedBy($actor)->performedOn($contacto)->event('correo_acceso_familiar')->log('Actualizó el correo de acceso sin alterar el correo de contacto. Los enlaces anteriores dejaron de ser válidos.');
        });
    }

    public function cambiarEstado(User $actor, string $contactoId, string $esperado): void
    {
        DB::transaction(function () use ($actor, $contactoId, $esperado) {
            $contacto = Contacto::lockForUpdate()->findOrFail($contactoId);
            $actor = $this->autorizar($actor, $contacto);
            $usuario = User::lockForUpdate()->findOrFail($contacto->cod_usuario);
            if ($usuario->estado !== $esperado) {
                throw ValidationException::withMessages(['acceso' => 'El acceso cambió. Actualiza la consulta antes de confirmar.']);
            }
            $usuario->estado = $esperado === 'ACTIVO' ? 'SUSPENDIDO' : 'ACTIVO';
            $usuario->save();
            activity('Familiares')->causedBy($actor)->performedOn($contacto)->event('acceso_familiar')->withProperties(['anterior' => $esperado, 'nuevo' => $usuario->estado])->log('Cambió el acceso sin modificar contacto, vínculos o autorizaciones.');
        });
    }
}
