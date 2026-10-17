<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use App\Models\User;
use App\Modulos\Reportes\Aplicacion\Consultas\DashboardService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class GestionarUsuariosHttp
{
    public function __construct(private readonly ArchivosAplicacion $ficheros) {}

    public function store(User $actor, array $entrada): mixed
    {
        abort_unless($actor->can('usuarios.crear'), 403);
        try {
            DB::beginTransaction();
            $passwordLimpia = Str::password(16, letters: true, numbers: true, symbols: true, spaces: false);
            $data = ['nombres' => data_get($entrada, 'nombres'), 'ap_paterno' => data_get($entrada, 'ap_paterno'), 'ap_materno' => data_get($entrada, 'ap_materno'), 'pais_documento' => data_get($entrada, 'pais_documento'), 'tipo_documento' => data_get($entrada, 'tipo_documento'), 'numero_documento' => data_get($entrada, 'numero_documento'), 'expedido' => data_get($entrada, 'expedido'), 'correo' => data_get($entrada, 'correo'), 'password' => Hash::make($passwordLimpia), 'telefono' => data_get($entrada, 'telefono'), 'pais_telefono' => data_get($entrada, 'pais_telefono'), 'codigo_telefono' => data_get($entrada, 'codigo_telefono'), 'genero' => data_get($entrada, 'genero'), 'fecha_nacimiento' => data_get($entrada, 'fecha_nacimiento'), 'estado' => 'ACTIVO', 'acceso_sistema' => 'HABILITADO', 'observaciones' => Str::upper(data_get($entrada, 'observaciones'))];
            if (data_get($entrada, 'foto_perfil') instanceof UploadedFile) {
                $path = $this->ficheros->recibir(data_get($entrada, 'foto_perfil'), 'perfiles', 'public');
                $data['foto_de_perfil'] = $path;
            }
            $usuario = User::create($data);
            $usuario->assignRole(data_get($entrada, 'rol'));
            // Registro en tablas especializadas según rol (Removido por limpieza de base de datos)
            activity('Usuarios')->causedBy($actor)->performedOn($usuario)->event('registro')->log("Se registró un nuevo usuario institucional: {$usuario->name} con el rol ".data_get($entrada, 'rol').'.');
            app(DashboardService::class)->limpiarCache($actor->getKey());
            DB::commit();

            return $passwordLimpia;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(User $actor, array $entrada, User $usuario): mixed
    {
        abort_unless($actor->can('usuarios.editar'), 403);
        try {
            DB::beginTransaction();
            $data = Arr::only($entrada, ['nombres', 'ap_paterno', 'ap_materno', 'correo', 'telefono', 'estado', 'pais_documento', 'tipo_documento', 'numero_documento', 'expedido', 'pais_telefono', 'codigo_telefono', 'genero', 'fecha_nacimiento', 'acceso_sistema', 'observaciones']);
            if (data_get($entrada, 'foto_perfil') instanceof UploadedFile) {
                $path = $this->ficheros->recibir(data_get($entrada, 'foto_perfil'), 'perfiles', 'public');
                $data['foto_de_perfil'] = $path;
            }
            if ($usuario->esCuentaPrincipal()) {
                unset($data['estado'], $data['acceso_sistema']);
            }
            $usuario->update($data);
            if (! $usuario->esCuentaPrincipal()) {
                $usuario->syncRoles([data_get($entrada, 'rol')]);
            }
            // Actualizar o crear registros vinculados (Removido por limpieza de base de datos)
            activity('Usuarios')->causedBy($actor)->performedOn($usuario)->event('edicion')->log("Se actualizó la información institucional del usuario {$usuario->name}.");
            app(DashboardService::class)->limpiarCache($actor->getKey());
            DB::commit();

            return null;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function desactivar(User $actor, array $entrada, User $usuario): mixed
    {
        abort_unless($actor->can('usuarios.cambiar_estado'), 403);
        $usuario->update(['estado' => 'INACTIVO']);
        app(DashboardService::class)->limpiarCache($actor->getKey());

        return null;
    }

    public function activar(User $actor, array $entrada, User $usuario): mixed
    {
        abort_unless($actor->can('usuarios.cambiar_estado'), 403);
        $usuario->update(['estado' => 'ACTIVO']);
        app(DashboardService::class)->limpiarCache($actor->getKey());

        return null;
    }
}
