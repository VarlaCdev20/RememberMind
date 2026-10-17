<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use App\Compartido\Aplicacion\Contratos\NotificacionesRegistro;
use App\Models\Contacto;
use App\Models\Personal;
use App\Models\ResidenteContacto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class GestionarUsuariosPanel
{
    public function __construct(private readonly NotificacionesRegistro $notificaciones, private readonly ArchivosAplicacion $ficheros) {}

    public function guardarUsuario(User $actor, array $entrada, array $userData, string $estadoGuardado, string $accesoGuardado): array
    {
        abort_unless($actor->can($entrada['isEdit'] ? 'usuarios.editar' : 'usuarios.crear'), 403);
        if ($entrada['isEdit'] && $entrada['usuarioId'] === 'USU_0001') {
            $estadoGuardado = 'ACTIVO';
            $accesoGuardado = 'HABILITADO';
            $entrada['rol'] = 'admin';
            $entrada['correo'] = 'admincasaamandita@gmail.com';
            $userData['correo'] = $entrada['correo'];
        }
        // Manejar archivo cargado fuera de la transacción
        $oldPhotoToDelete = null;
        $newPhotoPath = null;
        if ($entrada['foto_de_perfil_upload']) {
            if ($entrada['isEdit'] && $entrada['usuarioId']) {
                $oldUser = User::find($entrada['usuarioId']);
                if ($oldUser && $oldUser->foto_de_perfil && $this->ficheros->existe('public', $oldUser->foto_de_perfil)) {
                    $oldPhotoToDelete = $oldUser->foto_de_perfil;
                }
            }
            $newPhotoPath = $this->ficheros->recibir($entrada['foto_de_perfil_upload'], 'usuarios/fotos', 'public');
            $userData['foto_de_perfil'] = $newPhotoPath;
        }

        $passwordTemporal = null;
        $usuario = null;
        try {
            DB::beginTransaction();

            if ($entrada['isEdit']) {
                $userData['estado'] = $estadoGuardado;
                $userData['acceso_sistema'] = $accesoGuardado;

                $usuario = User::findOrFail($entrada['usuarioId']);

                if ($usuario->cod_usuario === $actor?->getKey()) {
                    if (! empty($entrada['password'])) {
                        $userData['password'] = Hash::make($entrada['password']);
                        $userData['debe_cambiar_password'] = false;
                    }
                } else {
                    // Para otros usuarios, la contraseña no se cambia mediante el formulario general
                    unset($userData['password']);
                }

                if ($usuario->hasRole('SUPERADMINISTRADOR') && $entrada['rol'] !== 'SUPERADMINISTRADOR') {
                    $superadmins = User::role('SUPERADMINISTRADOR')->where('estado', 'ACTIVO')->count();
                    if ($superadmins <= 1) {
                        throw new \Exception('No puedes quitar el rol de superadministrador al único superadministrador activo.');
                    }
                }

                $usuario->update($userData);
                $usuario->syncRoles([$entrada['rol']]);

                // Guardar sub-modelos de forma unificada en edición
                $this->guardarSubModelos($actor, $entrada, $usuario);

            } else {
                $userData['estado'] = 'ACTIVO';
                $userData['acceso_sistema'] = 'HABILITADO';
                $passwordTemporal = $entrada['passwordTemporalVisual'] ?: $this->generarPasswordTemporal($actor, $entrada);
                $userData['password'] = $passwordTemporal;
                $userData['debe_cambiar_password'] = true;

                $usuario = User::create($userData);
                $usuario->assignRole($entrada['rol']);

                // Guardar sub-modelos de forma unificada en creación
                $this->guardarSubModelos($actor, $entrada, $usuario);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($newPhotoPath && $this->ficheros->existe('public', $newPhotoPath)) {
                $this->ficheros->eliminar('public', $newPhotoPath);
            }
            throw $e;
        }

        return ['usuario' => $usuario, 'passwordTemporal' => $passwordTemporal, 'oldPhotoToDelete' => $oldPhotoToDelete];
    }

    public function toggleEstado(User $actor, User $usuario): string
    {
        abort_unless($actor->can('usuarios.cambiar_estado'), 403);
        abort_if($usuario->cod_usuario === 'USU_0001' || $usuario->correo === 'admincasaamandita@gmail.com', 422, 'No puedes cambiar el estado del Administrador Principal.');
        abort_if($usuario->cod_usuario === $actor->getKey(), 422, 'No puedes cambiar tu propio estado.');
        if ($usuario->estado === 'ACTIVO' && $usuario->hasRole('SUPERADMINISTRADOR')) {
            abort_if(User::role('SUPERADMINISTRADOR')->where('estado', 'ACTIVO')->count() <= 1, 422, 'No puedes desactivar al único superadministrador activo.');
        }
        $nuevoEstado = $usuario->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $usuario->update(['estado' => $nuevoEstado]);

        // Registrar en bitácora
        if (function_exists('activity')) {
            activity('Usuarios')
                ->causedBy($actor)
                ->performedOn($usuario)
                ->event($nuevoEstado === 'ACTIVO' ? 'activacion' : 'inactivacion')
                ->log("Se cambió el estado de {$usuario->name} a {$nuevoEstado}.");
        }

        return $nuevoEstado;
    }

    public function restablecerPasswordUsuario(User $actor, User $usuario): array
    {
        abort_unless($actor->can('usuarios.editar'), 403);
        abort_if($usuario->cod_usuario === 'USU_0001' && $actor->getKey() !== 'USU_0001', 403, 'No tienes permisos para restablecer la contraseña del Administrador Principal.');
        $entrada = [];
        $passwordTemporal = $this->generarPasswordTemporal($actor, $entrada);
        $usuario->update([
            'password' => $passwordTemporal,
        ]);

        $correoNotifEnviado = false;
        try {
            $this->notificaciones->claveActualizada($usuario, $passwordTemporal);
            $correoNotifEnviado = true;
        } catch (\Exception $e) {
            \Log::error("Error al enviar correo de restablecimiento a {$usuario->correo}: ".$e->getMessage());
        }

        if (function_exists('activity')) {
            $actMsg = "Se restableció la contraseña del usuario: {$usuario->name}.";
            if ($correoNotifEnviado) {
                $actMsg .= ' Notificación enviada con éxito.';
            } else {
                $actMsg .= ' Error al enviar notificación por correo.';
            }
            activity('Usuarios')
                ->causedBy($actor)
                ->performedOn($usuario)
                ->event('restablecer_password')
                ->log($actMsg);
        }

        return ['passwordTemporal' => $passwordTemporal, 'correoNotifEnviado' => $correoNotifEnviado];
    }

    private function guardarSubModelos(User $actor, array $entrada, User $usuario)
    {
        if ($entrada['rol'] === 'FAMILIAR') {
            $fam = Contacto::updateOrCreate(
                ['cod_usuario' => $usuario->cod_usuario],
                [
                    'cod_contacto' => Contacto::where('cod_usuario', $usuario->cod_usuario)->value('cod_contacto') ?: ('CON_'.strtoupper(Str::random(10))),
                    'nombres' => $entrada['nombres'],
                    'apellido_paterno' => $entrada['ap_paterno'],
                    'apellido_materno' => $entrada['ap_materno'],
                    'numero_documento' => $entrada['numero_documento'],
                    'telefono' => $entrada['telefono'],
                    'celular' => $entrada['telefono'],
                    'correo' => $entrada['correo'],
                    'direccion' => $entrada['direccion'],
                    'estado' => 'ACTIVO',
                    'observacion' => trim(($entrada['parentesco_emergencia'] ?? 'Familiar').' | '.($entrada['observacion_vinculo'] ?? '')),
                ]
            );

            $this->guardarVinculosFamiliar($actor, $entrada, $fam);
        } else {
            Personal::updateOrCreate(
                ['cod_usuario' => $usuario->cod_usuario],
                [
                    'cod_personal' => Personal::where('cod_usuario', $usuario->cod_usuario)->value('cod_personal') ?: ('PER_'.strtoupper(Str::random(10))),
                    'nombres' => $entrada['nombres'],
                    'apellido_paterno' => $entrada['ap_paterno'],
                    'apellido_materno' => $entrada['ap_materno'],
                    'numero_documento' => $entrada['numero_documento'],
                    'profesion' => $entrada['rol'],
                    'telefono' => $entrada['telefono'],
                    'fecha_ingreso' => $entrada['fecha_ingreso'],
                    'estado' => $usuario->estado,
                ]
            );
        }
    }

    // ══════════════════════════════════════════════
    // VISTA COMPLETA (modal/panel expandido)
    // ══════════════════════════════════════════════

    private function guardarVinculosFamiliar(User $actor, array $entrada, Contacto $contacto): void
    {
        foreach ($entrada['vinculosFamiliar'] as $vinculo) {
            $obsSerialized = 'Salud: '.($vinculo['responsable_salud'] ?? 'NO')
                .' | Económico: '.($vinculo['responsable_economico'] ?? 'NO')
                .' | Obs: '.($vinculo['observaciones'] ?? '');

            $relacion = ResidenteContacto::firstOrNew([
                'cod_contacto' => $contacto->cod_contacto,
                'cod_residente' => $vinculo['cod_residente'] ?? null,
            ]);
            $relacion->cod_residente_contacto ??= 'RCO_'.strtoupper(Str::random(10));
            $relacion->fill([
                'parentesco' => $vinculo['parentesco_vinculo'],
                'responsable_principal' => ($vinculo['es_responsable'] ?? 'NO') === 'SI',
                'contacto_emergencia' => true,
                'autoriza_informacion' => true,
                'autoriza_salida' => false,
                'estado' => 'ACTIVO',
                'observacion' => $obsSerialized,
            ])->save();
        }
    }

    private function generarPasswordTemporal(User $actor, array $entrada): string
    {
        return Str::password(16, letters: true, numbers: true, symbols: true, spaces: false);
    }
}
