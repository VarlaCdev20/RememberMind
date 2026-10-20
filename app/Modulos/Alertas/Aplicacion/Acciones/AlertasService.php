<?php

namespace App\Modulos\Alertas\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Models\Alerta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AlertasService
{
    public function __construct(private readonly ContextoEjecucion $contexto, private readonly TurnoEnfermeriaService $turnos) {}

    private function autorizarActor(User $usuario): void
    {
        abort_unless($this->contexto->usuario()?->getKey() === $usuario->getKey()
            && $usuario->fresh()?->estado === 'ACTIVO'
            && ! app(RolePreviewService::class)->isActive($usuario), 403);
    }

    private function autorizarEnfermeria(string $codResidente, string $permiso, User $usuario): void
    {
        $this->autorizarActor($usuario);
        abort_unless($usuario->hasRole('ENFERMEROS'), 403);
        $this->turnos->autorizarMutacionEnfermeria($codResidente, $permiso, $usuario);
    }

    public function autorizarCoordinacion(string $codResidente, string $permiso, User $usuario): void
    {
        abort_unless($this->contexto->usuario()?->getKey() === $usuario->cod_usuario && $usuario->fresh()?->estado === 'ACTIVO', 403);
        abort_if($usuario->hasRole('FAMILIAR'), 403);
        abort_if(app(RolePreviewService::class)->isActive($usuario), 403);
        abort_unless($usuario->canAny([$permiso, 'alertas.gestionar']), 403);
        if ($usuario->hasRole('ENFERMEROS')) {
            $this->autorizarEnfermeria($codResidente,
                $usuario->can($permiso) ? $permiso : 'alertas.gestionar', $usuario);
        } else {
            abort_unless($usuario->hasAnyRole(['ADMINISTRADOR', 'GERENTE', 'SUPERADMINISTRADOR']), 403);
            Residente::query()->findOrFail($codResidente);
        }
    }

    public function autorizarLectura(Alerta $alerta, User $usuario): void
    {
        abort_unless($usuario->fresh()?->estado === 'ACTIVO' && $usuario->can('alertas.ver'), 403);
        abort_if($usuario->hasRole('FAMILIAR'), 403);
        if (! $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            abort_unless($this->turnos->obtenerPacientesAsignadosQuery($usuario)
                ->whereKey($alerta->cod_residente)->exists(), 403);
        }
    }

    public function autorizarAtencion(Alerta $alerta, User $usuario): void
    {
        $this->autorizarEnfermeria($alerta->cod_residente,
            $usuario->can('alertas.seguimiento') ? 'alertas.seguimiento' : 'alertas.gestionar', $usuario);
        abort_unless(in_array($alerta->estado, Alerta::ESTADOS_ACTIVOS, true), 409,
            'El estado actual de la alerta no admite una intervención.');
    }

    public function crearDesdePanel(string $codResidente, array $datos, User $usuario): Alerta
    {
        $this->autorizarCoordinacion($codResidente, 'alertas.gestionar', $usuario);

        return $this->crear($codResidente, $datos, $usuario, coordinacion: true, deduplicar: false);
    }

    public function crear(string $codResidente, array $datos, User $usuario, string $permiso = 'alertas.gestionar', bool $coordinacion = false, bool $deduplicar = true): Alerta
    {
        if ($coordinacion) {
            $this->autorizarCoordinacion($codResidente, $permiso, $usuario);
        } else {
            $this->autorizarEnfermeria($codResidente, $permiso, $usuario);
        }
        $datos = Validator::make($datos, [
            'origen' => 'required|in:SIGNOS,MEDICACION,SEGUIMIENTO,PLAN,INCIDENTE,SOLICITUD_MEDICA,MANUAL,FICHA,VALORACION',
            'tipo_alerta' => 'required|string|min:3|max:60',
            'nivel' => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
            'motivo' => 'required|string|min:10|max:10000',
        ])->validate();
        $tipo = mb_strtoupper(trim($datos['tipo_alerta']));

        return DB::transaction(function () use ($codResidente, $datos, $tipo, $usuario, $deduplicar) {
            Residente::query()->whereKey($codResidente)->lockForUpdate()->firstOrFail();
            $existente = Alerta::lockForUpdate()->where('cod_residente', $codResidente)
                ->where('tipo', $tipo)->abiertas()->first();
            if ($deduplicar && $existente) {
                return $existente;
            }
            $codPersonal = $usuario->personal?->cod_personal;
            $alerta = Alerta::create([
                'cod_alerta' => app(CodigosEntidad::class)->siguiente('alertas'),
                'cod_residente' => $codResidente,
                'cod_personal_responsable' => $codPersonal,
                'tipo' => $tipo,
                'prioridad' => $datos['nivel'],
                'modulo' => $datos['origen'],
                'titulo' => mb_substr(trim($datos['motivo']), 0, 100),
                'descripcion' => trim($datos['motivo']),
                'fecha_hora' => now(),
                'generacion' => 'MANUAL',
                'estado' => 'ABIERTA',
            ]);
            $this->accion($alerta, 'CREACION', 'Creación manual: '.$datos['origen'], $usuario, null);

            return $alerta;
        });
    }

    public function registrarIntervencion(Alerta $alerta, string $texto, User $usuario): Alerta
    {
        abort_unless($usuario->hasRole('ENFERMEROS'), 403);
        $this->autorizarEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        Validator::make(['accion' => $texto], ['accion' => 'required|string|min:5|max:10000'])->validate();

        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($texto, $usuario) {
            $anterior = $bloqueada->estado;
            $bloqueada->update(['estado' => 'EN_ATENCION']);
            $this->accion($bloqueada, 'INTERVENCION', $texto, $usuario, $anterior);
        });
    }

    public function asignarResponsable(Alerta $alerta, User $responsable, User $usuario, bool $coordinacion = false): Alerta
    {
        if ($coordinacion) {
            $this->autorizarCoordinacion($alerta->cod_residente, 'alertas.asignar', $usuario);
        } else {
            $this->autorizarEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        }
        abort_unless($responsable->estado === 'ACTIVO', 422, 'El responsable seleccionado debe estar activo.');
        abort_unless($responsable->personal?->estado === 'ACTIVO'
            && $responsable->hasAnyRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'])
            && $responsable->can('alertas.ver'), 422,
            'Seleccione personal activo con competencia profesional y permiso para consultar esta alerta.');
        $this->autorizarLectura($alerta, $responsable);

        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($responsable, $usuario) {
            $codPersonal = $responsable->personal?->cod_personal;
            abort_unless($codPersonal && $responsable->personal->estado === 'ACTIVO', 422, 'El responsable seleccionado debe tener personal activo.');
            $anterior = $bloqueada->estado;
            $cambios = ['cod_personal_responsable' => $codPersonal];
            if (in_array($anterior, ['ABIERTA', 'RECONOCIDA'], true)) {
                $cambios['estado'] = 'ASIGNADA';
            }
            $bloqueada->update($cambios);
            $this->accion($bloqueada, 'ASIGNACION', 'Responsable: '.$responsable->name, $usuario, $anterior);
        });
    }

    public function registrarSeguimiento(Alerta $alerta, string $texto, User $usuario, bool $coordinacion = false): Alerta
    {
        if ($coordinacion) {
            $this->autorizarCoordinacion($alerta->cod_residente, 'alertas.seguimiento', $usuario);
        } else {
            $this->autorizarEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        }
        Validator::make(['seguimiento' => $texto], ['seguimiento' => 'required|string|min:5|max:10000'])->validate();

        return $this->mutarActiva($alerta, fn (Alerta $bloqueada) => $this->accion($bloqueada, 'SEGUIMIENTO', $texto, $usuario, $bloqueada->estado));
    }

    public function registrarDesdePanel(Alerta $alerta, string $texto, User $usuario, bool $atencion = false): Alerta
    {
        $this->autorizarCoordinacion($alerta->cod_residente, 'alertas.seguimiento', $usuario);
        // La coordinación administrativa registra seguimiento, nunca una intervención clínica.
        $clinico = $usuario->hasRole('ENFERMEROS');
        abort_if($atencion && ! $clinico, 403, 'La intervención clínica requiere rol de Enfermería.');
        if ($clinico || $atencion) {
            $this->autorizarAtencion($alerta, $usuario);
        }
        Validator::make(['accion' => $texto], ['accion' => 'required|string|min:'.($atencion ? 5 : 3).'|max:10000'])->validate();

        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($texto, $usuario, $atencion, $clinico) {
            $anterior = $bloqueada->estado;
            $tipo = $clinico && ($atencion || in_array($anterior, ['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'PENDIENTE'], true)) ? 'INTERVENCION' : 'SEGUIMIENTO';
            if ($tipo === 'INTERVENCION') {
                $cambios = ['estado' => 'EN_ATENCION'];
                if ($atencion && ! $bloqueada->cod_personal_responsable) {
                    $cambios['cod_personal_responsable'] = $usuario->personal?->cod_personal;
                }
                $bloqueada->update($cambios);
            }
            $this->accion($bloqueada, $tipo, $atencion ? 'Atención: '.$texto : $texto, $usuario, $anterior);
        });
    }

    public function cerrar(Alerta $alerta, string $resultado, User $usuario, bool $coordinacion = false): Alerta
    {
        if ($coordinacion) {
            $this->autorizarCoordinacion($alerta->cod_residente, 'alertas.cerrar', $usuario);
        } else {
            $this->autorizarEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        }
        Validator::make(['resultado' => $resultado], ['resultado' => 'required|string|min:5|max:10000'])->validate();

        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($resultado, $usuario) {
            $anterior = $bloqueada->estado;
            $bloqueada->update(['estado' => 'CERRADA']);
            $this->accion($bloqueada, 'CIERRE', $usuario->hasRole('ENFERMEROS') ? $resultado : 'Cierre de coordinación; no acredita resolución clínica: '.$resultado, $usuario, $anterior);
        }, permitirAtendida: true);
    }

    private function mutarActiva(Alerta $alerta, callable $operacion, bool $permitirAtendida = false): Alerta
    {
        return DB::transaction(function () use ($alerta, $operacion, $permitirAtendida) {
            $bloqueada = Alerta::lockForUpdate()->findOrFail($alerta->getKey());
            abort_unless(in_array($bloqueada->estado, Alerta::ESTADOS_ACTIVOS, true)
                || ($permitirAtendida && $bloqueada->estado === 'ATENDIDA'),
                409, 'El estado actual de la alerta no admite esta operación.');
            $operacion($bloqueada);

            return $bloqueada->refresh();
        });
    }

    public function crearDesdeHttp(string $codResidente, array $datos, User $usuario): Alerta
    {
        $this->autorizarEnfermeria($codResidente, 'alertas.gestionar', $usuario);
        $datos = Validator::make($datos, [
            'tipo' => 'required|string|min:3|max:60', 'prioridad' => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
            'modulo' => 'nullable|string|max:60', 'cod_registro' => 'nullable|string|max:20',
            'titulo' => 'required|string|min:3|max:180', 'descripcion' => 'required|string|min:10|max:10000',
            'fecha_hora_limite' => 'nullable|date|after:now',
        ])->validate();

        return DB::transaction(function () use ($codResidente, $datos, $usuario) {
            $alerta = Alerta::create($datos + ['cod_residente' => $codResidente,
                'cod_personal_responsable' => $usuario->personal->cod_personal,
                'fecha_hora' => now(), 'generacion' => 'MANUAL', 'estado' => 'ABIERTA']);
            $this->accion($alerta, 'CREADA', 'Creación manual HTTP: '.($datos['modulo'] ?? 'MANUAL'), $usuario, null);

            return $alerta;
        });
    }

    public function cambiarEstado(Alerta $alerta, string $estado, string $texto, User $usuario): Alerta
    {
        $datos = Validator::make(['estado' => $estado, 'descripcion' => $texto], [
            'estado' => 'required|in:RECONOCIDA,ASIGNADA,EN_ATENCION,ATENDIDA,CERRADA,ANULADA',
            'descripcion' => 'required|string|min:5|max:10000',
        ])->validate();
        $permiso = match ($estado) {
            'RECONOCIDA' => 'alertas.reconocer', 'ASIGNADA' => 'alertas.asignar',
            'EN_ATENCION', 'ATENDIDA' => 'alertas.seguimiento', 'CERRADA', 'ANULADA' => 'alertas.cerrar',
        };
        if ($estado === 'EN_ATENCION' || $estado === 'ATENDIDA') {
            abort_unless($usuario->hasRole('ENFERMEROS'), 403);
            $this->autorizarEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        } elseif ($usuario->hasAnyRole(['ADMINISTRADOR', 'GERENTE', 'SUPERADMINISTRADOR']) && ! $usuario->hasRole('ENFERMEROS')) {
            $this->autorizarCoordinacion($alerta->cod_residente, $permiso, $usuario);
        } else {
            $this->autorizarEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        }

        return DB::transaction(function () use ($alerta, $datos, $usuario) {
            $bloqueada = Alerta::lockForUpdate()->findOrFail($alerta->getKey());
            $anterior = strtoupper((string) $bloqueada->estado);
            $transiciones = [
                'ABIERTA' => ['RECONOCIDA', 'ASIGNADA', 'EN_ATENCION', 'ATENDIDA', 'CERRADA', 'ANULADA'],
                'RECONOCIDA' => ['ASIGNADA', 'EN_ATENCION', 'ATENDIDA', 'CERRADA', 'ANULADA'],
                'ASIGNADA' => ['EN_ATENCION', 'ATENDIDA', 'CERRADA', 'ANULADA'],
                'EN_ATENCION' => ['ATENDIDA', 'CERRADA', 'ANULADA'], 'ATENDIDA' => ['CERRADA'],
                'CERRADA' => [], 'ANULADA' => [],
            ];
            abort_unless(in_array($datos['estado'], $transiciones[$anterior] ?? [], true), 409,
                'La transición de estado solicitada no es válida para esta alerta.');
            $bloqueada->update(['estado' => $datos['estado']]);
            $descripcion = $datos['descripcion'];
            if (! $usuario->hasRole('ENFERMEROS') && in_array($datos['estado'], ['CERRADA', 'ANULADA'], true)) {
                $descripcion = 'Cierre o anulación de coordinación; no acredita resolución clínica: '.$descripcion;
            }
            $this->accion($bloqueada, 'CAMBIO_ESTADO', $descripcion, $usuario, $anterior);

            return $bloqueada->refresh();
        });
    }

    public function registrarCreacionAutomatica(Alerta $alerta, User $usuario): void
    {
        $this->autorizarCoordinacion($alerta->cod_residente, 'alertas.gestionar', $usuario);
        $this->accion($alerta, 'CREACION', 'Detección '.$alerta->modulo.' / '.$alerta->tipo, $usuario, null);
    }

    /** El llamador ya confirmó el origen y conserva su transacción institucional. */
    public function registrarEventoOrigen(Alerta $alerta, User $usuario): void
    {
        abort_unless(DB::transactionLevel() > 0, 500, 'El origen y su alerta deben registrarse de forma atómica.');
        abort_unless($this->contexto->usuario()?->getKey() === $usuario->getKey()
            && $usuario->fresh()?->estado === 'ACTIVO'
            && ! app(RolePreviewService::class)->isActive($usuario), 403);
        $this->accion($alerta, 'CREACION', 'Alerta vinculada al registro confirmado de '.$alerta->modulo.'.', $usuario, null);
    }

    private function accion(Alerta $alerta, string $tipo, string $texto, User $usuario, ?string $estadoAnterior): void
    {
        $alerta->eventos()->create([
            'cod_evento_alerta' => app(CodigosEntidad::class)->siguiente('eventos_alerta'),
            'cod_usuario' => $usuario->cod_usuario,
            'tipo_evento' => $tipo,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $alerta->estado,
            'fecha_hora' => now(),
            'descripcion' => trim($texto),
        ]);
    }
}
