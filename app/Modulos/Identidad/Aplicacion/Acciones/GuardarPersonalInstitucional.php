<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\DocumentosUsuario;
use App\Compartido\Aplicacion\Contratos\NotificacionesRegistro;
use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use App\Models\Documento;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Dominio\Reglas\ClasificacionRolInstitucional;
use App\Modulos\Identidad\Dominio\Reglas\DocumentosPersonalInstitucional;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class GuardarPersonalInstitucional
{
    public function __construct(private readonly NotificacionesRegistro $notificaciones, private readonly DocumentosUsuario $archivos) {}

    /** @return array{usuario: User, correo_enviado: ?bool} */
    public function guardar(?User $actor, array $entrada): array
    {
        $actor = $actor ? User::query()->find($actor->getKey()) : null;
        abort_unless($actor?->estado === 'ACTIVO' && $actor->can('personal.gestionar'), 403);
        abort_if(app(RolePreviewService::class)->isActive($actor), 403);
        Validator::make($entrada, ['usuarioId' => ['nullable', 'string', 'max:20'], 'esEdicion' => ['required', 'boolean'],
            'roles_seleccionados' => ['required', 'array', 'min:1'], 'roles_seleccionados.*' => ['required', 'string']])->validate();
        $edicion = ! empty($entrada['usuarioId']);
        if ($edicion !== (bool) ($entrada['esEdicion'] ?? false)) {
            throw ValidationException::withMessages(['usuarioId' => 'La operación no coincide con la cuenta seleccionada.']);
        }
        $usuario = $edicion ? User::with(['personal', 'roles'])->findOrFail($entrada['usuarioId']) : new User;
        $alcance = app(AutorizarGestionPersonal::class);
        $alcance->autorizar($actor, $edicion ? $usuario : null, $entrada['roles_seleccionados']);
        abort_unless($edicion || $actor->can('usuarios.crear') || $alcance->esGerencia($actor), 403);
        $personal = $usuario->personal;
        $entrada['correo'] = mb_strtolower(trim((string) ($entrada['correo'] ?? '')));
        $roles = array_values($entrada['roles_seleccionados'] ?? []);
        $rolesActuales = $edicion ? $usuario->getRoleNames()->all() : [];
        $ordenados = $roles;
        $actualesOrdenados = $rolesActuales;
        sort($ordenados);
        sort($actualesOrdenados);
        $cambiaRoles = $ordenados !== $actualesOrdenados;
        $cambiaCuenta = $edicion && ($entrada['correo'] !== mb_strtolower(trim($usuario->correo)) || $cambiaRoles
            || $this->archivos->esArchivo($entrada['foto_perfil'] ?? null));
        abort_if($cambiaCuenta && ! $actor->can('usuarios.editar') && ! $alcance->esGerencia($actor), 403);
        abort_if($edicion && $usuario->esCuentaPrincipal() && $cambiaCuenta && ! $actor->esCuentaPrincipal(), 403);
        abort_if($cambiaRoles && in_array('SUPERADMINISTRADOR', $roles, true) && ! $actor->hasRole('SUPERADMINISTRADOR'), 403);
        if ($edicion && isset($entrada['estado']) && $entrada['estado'] !== $usuario->estado) {
            throw ValidationException::withMessages(['estado' => 'El acceso se cambia desde la gestión de cuentas.']);
        }
        if ($edicion && isset($entrada['estado_personal']) && $entrada['estado_personal'] !== $personal?->estado) {
            throw ValidationException::withMessages(['estado_personal'=>'Usa Cambiar situación laboral para conservar motivo y fecha de la transición.']);
        }
        foreach (['forzar_cambio_password', 'telefono_alternativo', 'institucion_formacion', 'anios_exp', 'subtipo_enfermeria', 'nivel_responsabilidad'] as $campo) {
            if (! empty($entrada[$campo])) {
                throw ValidationException::withMessages([$campo => 'Esta opción no forma parte del registro de personal disponible.']);
            }
        }
        $foto = $entrada['foto_perfil'] ?? null;
        if (is_string($foto) && $edicion && $foto === $usuario->foto) {
            $foto = null;
        }
        $datos = Validator::make([...$entrada, 'foto_perfil' => $foto], [
            'nombres' => ['required', 'string', 'max:100'], 'ap_paterno' => ['required', 'string', 'max:80'],
            'ap_materno' => ['nullable', 'string', 'max:80'],
            'correo' => ['required', 'email', 'max:120', Rule::unique('usuarios', 'correo')->ignore($usuario->getKey(), 'cod_usuario')],
            'numero_documento' => ['required', 'regex:/^[0-9]{5,10}$/', Rule::unique('personal', 'numero_documento')->ignore($personal?->getKey(), 'cod_personal')],
            'expedido' => ['required', 'string', 'max:20'], 'genero' => ['required', Rule::in(['M', 'F'])],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString(), 'after_or_equal:'.now()->subYears(100)->toDateString()],
            'telefono' => ['required', 'regex:/^[67][0-9]{7}$/'], 'direccion' => ['required', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string'], 'matricula_prof' => ['nullable', 'string', 'max:50'],
            'fecha_ingreso' => ['nullable', 'date'], 'estado_personal' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
            'roles_seleccionados' => ['required', 'array', $edicion && ! $cambiaRoles ? 'min:1' : 'size:1'],
            'roles_seleccionados.*' => [Rule::in(ClasificacionRolInstitucional::ROLES), Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'contrasena_temporal' => [$edicion ? 'nullable' : 'required', 'string', 'min:12', 'max:72'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'archivos_temporales' => ['sometimes', 'array'], 'archivos_temporales.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ])->validate();
        $clasificacion = ClasificacionRolInstitucional::paraRol($roles[0]);
        $catalogo = DocumentosPersonalInstitucional::paraRol($roles[0]);
        $catalogoPorCodigo = array_column($catalogo, null, 'id');
        $uploads = array_filter($datos['archivos_temporales'] ?? []);
        abort_if($uploads && ! $actor->can('documentos.subir') && ! $actor->can('documentos.gestionar') && ! $alcance->esGerencia($actor), 403);
        foreach (array_keys($uploads) as $codigo) {
            if (! isset($catalogoPorCodigo[$codigo])) {
                throw ValidationException::withMessages(['archivos_temporales' => 'El documento no corresponde al registro seleccionado.']);
            }
        }
        if (! $edicion) {
            foreach ($catalogo as $documento) {
                if ($documento['tipo'] === 'ingresante' && $documento['obligatorio_inmediato'] && ! isset($uploads[$documento['id']])) {
                    throw ValidationException::withMessages(['archivos_temporales.'.$documento['id'] => 'Adjunte '.$documento['nombre'].'.']);
                }
            }
        }
        if ($edicion && $clasificacion['rol_operativo'] === 'MEDICO_GENERAL' && $usuario->estado === 'ACTIVO'
            && User::role('MEDICO GENERAL/GERIATRA')->where('estado', 'ACTIVO')->whereKeyNot($usuario->getKey())->exists()) {
            throw ValidationException::withMessages(['roles_seleccionados' => 'Ya existe un Médico General principal activo en el sistema.']);
        }
        $nuevosArchivos = [];
        $correoEnviado = null;
        try {
            $guardado = DB::transaction(function () use ($actor, $usuario, $personal, $edicion, $datos, $roles, $cambiaRoles, $clasificacion, $uploads, $catalogoPorCodigo, &$nuevosArchivos, &$correoEnviado) {
                if (! $edicion) {
                    // El coordinador queda bloqueado hasta el commit exterior. La
                    // parte reintentable solo valida: no guarda archivos ni modelos.
                    $actor = app(GeneradorCodigo::class)->serializar('usuarios', function () use ($actor, $datos, $roles, $uploads) {
                        $alcance = app(AutorizarGestionPersonal::class);
                        $actual = $alcance->autorizar($actor, null, $roles);
                        abort_unless($actual->can('usuarios.crear') || $alcance->esGerencia($actual), 403);
                        abort_if($uploads && ! $actual->can('documentos.subir') && ! $actual->can('documentos.gestionar') && ! $alcance->esGerencia($actual), 403);
                        $this->validarUnicidad($datos);

                        return $actual;
                    });
                }
                if ($edicion) {
                    $superadmins = User::whereHas('roles', fn ($query) => $query->where('name', 'SUPERADMINISTRADOR')->where('guard_name', 'web'))->where('estado', 'ACTIVO')->orderBy('cod_usuario')->lockForUpdate()->get();
                    $usuario = User::query()->lockForUpdate()->findOrFail($usuario->getKey());
                    $actor = app(AutorizarGestionPersonal::class)->autorizar($actor, $usuario, $roles);
                    if ($cambiaRoles && $usuario->hasRole('SUPERADMINISTRADOR') && ! in_array('SUPERADMINISTRADOR', $roles, true)
                        && $superadmins->count() <= 1) {
                        throw ValidationException::withMessages(['roles_seleccionados' => 'No puedes quitar el rol al único superadministrador activo.']);
                    }
                } else {
                    $usuario->password = $datos['contrasena_temporal'];
                    $usuario->estado = 'ACTIVO';
                    if (app(\App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal::class)->disponible()) {
                        $usuario->forceFill(['fecha_hora_alta'=>now(),'cambio_password_pendiente'=>true]);
                    }
                }
                if (! $edicion || $actor->can('usuarios.editar') || app(AutorizarGestionPersonal::class)->esGerencia($actor)) {
                    $usuario->correo = $datos['correo'];
                }
                $usuario->save();
                if ($this->archivos->esArchivo($datos['foto_perfil'] ?? null)) {
                    $foto = $this->archivos->guardar($usuario->getKey(), $datos['foto_perfil'], 'perfiles');
                    $nuevosArchivos[] = $foto['ruta_archivo'];
                    $usuario->update(['foto' => $foto['ruta_archivo']]);
                }
                $personal = Personal::query()->where('cod_usuario', $usuario->getKey())->lockForUpdate()->first() ?? new Personal(['cod_personal' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('personal'), 'cod_usuario' => $usuario->getKey()]);
                $personal->fill(['nombres' => mb_strtoupper(trim($datos['nombres'])), 'apellido_paterno' => mb_strtoupper(trim($datos['ap_paterno'])),
                    'apellido_materno' => filled($datos['ap_materno'] ?? null) ? mb_strtoupper(trim($datos['ap_materno'])) : null,
                    'numero_documento' => $datos['numero_documento'], 'expedicion_documento' => $datos['expedido'],
                    'fecha_nacimiento' => $datos['fecha_nacimiento'], 'genero' => $datos['genero'], 'telefono' => $datos['telefono'],
                    'direccion' => $datos['direccion'], 'profesion' => $personal->exists && ! $cambiaRoles ? $personal->profesion : $roles[0], 'matricula_profesional' => $datos['matricula_prof'] ?? null,
                    'observacion' => $datos['observaciones'] ?? null]);
                if (array_key_exists('fecha_ingreso', $datos)) {
                    $personal->fecha_ingreso = $datos['fecha_ingreso'];
                }
                $personal->estado = $datos['estado_personal'] ?? $personal->estado ?? 'ACTIVO';
                $personal->save();
                if ($cambiaRoles) {
                    $usuario->syncRoles($roles);
                }
                $adjuntos = [];
                foreach ($uploads as $codigo => $archivo) {
                    $capacidadDocumental=app(\App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal::class)->disponible();
                    $previos = Documento::query()->where('cod_usuario', $usuario->getKey())
                        ->whereIn('tipo_documento', in_array($codigo, ['FOTO', 'FOTO_DOC'], true) ? ['FOTO', 'FOTO_DOC'] : [$codigo])
                        ->whereNull('cod_residente')->whereNull('cod_contacto')->whereNull('cod_preadmision')
                        ->whereNotIn('estado', ['ANULADO', 'ANULADA', 'REEMPLAZADO'])->lockForUpdate()->get();
                    if ($previos->count() > 1 || ($previos->first() && $previos->first()->tipo_documento !== $codigo)) {
                        throw ValidationException::withMessages(['archivos_temporales.'.$codigo => 'Actualiza la ficha y corrige el archivo de origen; hay otro registro vigente para este requisito.']);
                    }
                    $anterior = $previos->first();
                    $privado = $capacidadDocumental || str_starts_with($anterior?->ruta_archivo ?? '', 'documentos_personal_privados/');
                    $almacenado = $this->archivos->guardar($usuario->getKey(), $archivo, $privado ? 'documentos_personal_privados' : 'documentos_personal');
                    $nuevosArchivos[] = $almacenado['ruta_archivo'];
                    $procedencia = $capacidadDocumental
                        ? ['cod_usuario_carga'=>$actor->getKey(),'fecha_hora_carga'=>now()] : [];
                    Documento::create(['cod_documento' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('documentos'), 'cod_usuario' => $usuario->getKey(),
                        'cod_documento_anterior' => $anterior?->getKey(), 'tipo_documento' => $codigo, 'nombre' => $catalogoPorCodigo[$codigo]['nombre'],
                        ...$almacenado, ...$procedencia, 'estado' => 'CARGADO', 'observacion' => $procedencia ? null : 'Subido por: '.$actor->getKey()]);
                    $anterior?->update(['estado' => 'REEMPLAZADO']);
                    if ($catalogoPorCodigo[$codigo]['tipo'] === 'institucional') {
                        $adjuntos[$codigo.'.'.strtolower($archivo->getClientOriginalExtension())] = $this->archivos->rutaAbsoluta($almacenado['ruta_archivo']);
                    }
                }
                activity('Usuarios')->causedBy($actor)->performedOn($usuario)->event('personal')->log($edicion ? 'Actualizó datos de personal institucional.' : 'Registró cuenta y personal institucional.');
                $guardado = $usuario->fresh(['personal', 'roles']);
                if (! $edicion) {
                    $cargados = Documento::query()->where('cod_usuario', $usuario->getKey())->whereNotIn('estado', ['ANULADO', 'ANULADA', 'REEMPLAZADO'])->pluck('tipo_documento')->all();
                    // R01A: el catálogo de respaldos opcionales no es una solicitud pendiente.
                    $pendientes = array_column(array_filter($catalogoPorCodigo, fn ($doc) => $doc['obligatorio_inmediato'] && ! in_array($doc['id'], $cargados, true)), 'nombre');
                    $subidos = array_column(array_filter($catalogoPorCodigo, fn ($doc) => in_array($doc['id'], $cargados, true)), 'nombre');
                    DB::afterCommit(function () use ($guardado, $clasificacion, $pendientes, $subidos, $adjuntos, $datos, &$correoEnviado) {
                        try {
                            $this->notificaciones->bienvenidaPersonal($guardado, $clasificacion['rol_label'], 'Sin asignación operativa',
                                $guardado->estado, $pendientes, '', $adjuntos, $datos['contrasena_temporal'], $subidos);
                            $correoEnviado = true;
                        } catch (\Throwable $error) {
                            $correoEnviado = false;
                            logger()->warning('Registro confirmado; falló el envío de bienvenida.', ['usuario' => $guardado->getKey(), 'exception' => get_class($error)]);
                        }
                    });
                }

                return $guardado;
            });
        } catch (\Throwable $error) {
            foreach (array_reverse($nuevosArchivos) as $ruta) {
                $this->archivos->compensar($ruta);
            }
            if ($error instanceof UniqueConstraintViolationException) {
                // Tras rollback, traducir únicamente una colisión de identidad.
                // Un User nuevo revertido ya puede tener PK: no se ignora esa PK.
                $this->validarUnicidad($datos, $edicion ? $usuario->getKey() : null, $edicion ? $personal?->getKey() : null);
            }
            throw $error;
        }

        return ['usuario' => $guardado, 'correo_enviado' => $correoEnviado];
    }

    private function validarUnicidad(array $datos, ?string $usuario = null, ?string $personal = null): void
    {
        Validator::make($datos, [
            'correo' => [Rule::unique('usuarios', 'correo')->ignore($usuario, 'cod_usuario')],
            'numero_documento' => [Rule::unique('personal', 'numero_documento')->ignore($personal, 'cod_personal')],
        ], ['correo.unique' => 'El correo ya fue registrado. Actualiza la consulta antes de continuar.',
            'numero_documento.unique' => 'El documento de identidad ya fue registrado. Actualiza la consulta antes de continuar.'])->validate();
    }
}
