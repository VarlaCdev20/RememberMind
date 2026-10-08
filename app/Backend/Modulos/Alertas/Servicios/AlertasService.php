<?php

namespace App\Backend\Modulos\Alertas\Servicios;

use App\Models\Alerta;
use App\Models\Residente;
use App\Models\User;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AlertasService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function autorizarCoordinacion(string $codResidente, string $permiso, User $usuario): void
    {
        abort_unless(Auth::id() === $usuario->cod_usuario && $usuario->estado === 'ACTIVO', 403);
        abort_if($usuario->hasRole('FAMILIAR'), 403);
        abort_unless($usuario->canAny([$permiso, 'alertas.gestionar']), 403);
        if ($usuario->hasRole('ENFERMEROS')) {
            $this->turnos->autorizarMutacionEnfermeria($codResidente,
                $usuario->can($permiso) ? $permiso : 'alertas.gestionar', $usuario);
        } else {
            abort_unless($usuario->hasAnyRole(['ADMINISTRADOR', 'GERENTE', 'SUPERADMINISTRADOR']), 403);
            Residente::query()->findOrFail($codResidente);
        }
    }

    public function autorizarLectura(Alerta $alerta, User $usuario): void
    {
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('alertas.ver'), 403);
        abort_if($usuario->hasRole('FAMILIAR'), 403);
        if (! $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            abort_unless($this->turnos->obtenerPacientesAsignadosQuery($usuario)
                ->whereKey($alerta->cod_residente)->exists(), 403);
        }
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
            $this->turnos->autorizarMutacionEnfermeria($codResidente, $permiso, $usuario);
        }
        $datos = Validator::make($datos, [
            'origen' => 'required|in:SIGNOS,MEDICACION,SEGUIMIENTO,PLAN,INCIDENTE,SOLICITUD_MEDICA,MANUAL,FICHA,VALORACION',
            'tipo_alerta' => 'required|string|min:3|max:80',
            'nivel' => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
            'motivo' => 'required|string|min:10|max:10000',
        ])->validate();
        $tipo = mb_strtoupper(trim($datos['tipo_alerta']));

        return DB::transaction(function () use ($codResidente, $datos, $tipo, $usuario, $deduplicar) {
            Residente::query()->whereKey($codResidente)->lockForUpdate()->firstOrFail();
            $existente = Alerta::lockForUpdate()->where('cod_residente', $codResidente)
                ->where('tipo', $tipo)->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->first();
            if ($deduplicar && $existente) {
                return $existente;
            }
            $codPersonal = $usuario->personal?->cod_personal;
            $alerta = Alerta::create([
                'cod_alerta' => 'ALA_' . strtoupper(\Illuminate\Support\Str::random(10)),
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
        $this->turnos->autorizarMutacionEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        Validator::make(['accion' => $texto], ['accion' => 'required|string|min:5|max:10000'])->validate();
        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($texto, $usuario) {
            $anterior = $bloqueada->estado;
            $bloqueada->update(['estado' => 'EN_ATENCION']);
            $this->accion($bloqueada, 'INTERVENCION', $texto, $usuario, $anterior);
        }, permitirInicio: true);
    }

    public function asignarResponsable(Alerta $alerta, User $responsable, User $usuario, bool $coordinacion = false): Alerta
    {
        if ($coordinacion) {
            $this->autorizarCoordinacion($alerta->cod_residente, 'alertas.asignar', $usuario);
        } else {
            $this->turnos->autorizarMutacionEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        }
        abort_unless($responsable->estado === 'ACTIVO', 422, 'El responsable seleccionado debe estar activo.');
        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($responsable, $usuario) {
            $codPersonal = $responsable->personal?->cod_personal;
            abort_unless($codPersonal && $responsable->personal->estado === 'ACTIVO', 422, 'El responsable seleccionado debe tener personal activo.');
            $bloqueada->update(['cod_personal_responsable' => $codPersonal]);
            $this->accion($bloqueada, 'ASIGNACION', 'Responsable: '.$responsable->name, $usuario, $bloqueada->estado);
        });
    }

    public function registrarSeguimiento(Alerta $alerta, string $texto, User $usuario, bool $coordinacion = false): Alerta
    {
        if ($coordinacion) {
            $this->autorizarCoordinacion($alerta->cod_residente, 'alertas.seguimiento', $usuario);
        } else {
            $this->turnos->autorizarMutacionEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        }
        Validator::make(['seguimiento' => $texto], ['seguimiento' => 'required|string|min:5|max:10000'])->validate();
        return $this->mutarActiva($alerta, fn (Alerta $bloqueada) => $this->accion($bloqueada, 'SEGUIMIENTO', $texto, $usuario, $bloqueada->estado));
    }

    public function registrarDesdePanel(Alerta $alerta, string $texto, User $usuario, bool $atencion = false): Alerta
    {
        $this->autorizarCoordinacion($alerta->cod_residente, 'alertas.seguimiento', $usuario);
        Validator::make(['accion' => $texto], ['accion' => 'required|string|min:'.($atencion ? 5 : 3).'|max:10000'])->validate();
        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($texto, $usuario, $atencion) {
            $anterior = $bloqueada->estado;
            $tipo = $atencion || in_array($anterior, ['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'PENDIENTE'], true) ? 'INTERVENCION' : 'SEGUIMIENTO';
            if ($tipo === 'INTERVENCION') {
                $cambios = ['estado' => 'EN_ATENCION'];
                if ($atencion && ! $bloqueada->cod_personal_responsable) {
                    $cambios['cod_personal_responsable'] = $usuario->personal?->cod_personal;
                }
                $bloqueada->update($cambios);
            }
            $this->accion($bloqueada, $tipo, $atencion ? 'Atención: '.$texto : $texto, $usuario, $anterior);
        }, permitirInicio: true);
    }

    public function cerrar(Alerta $alerta, string $resultado, User $usuario, bool $coordinacion = false): Alerta
    {
        if ($coordinacion) {
            $this->autorizarCoordinacion($alerta->cod_residente, 'alertas.cerrar', $usuario);
        } else {
            $this->turnos->autorizarMutacionEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        }
        Validator::make(['resultado' => $resultado], ['resultado' => 'required|string|min:5|max:10000'])->validate();
        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($resultado, $usuario) {
            $anterior = $bloqueada->estado;
            $bloqueada->update(['estado' => 'CERRADA']);
            $this->accion($bloqueada, 'CIERRE', $resultado, $usuario, $anterior);
        });
    }

    private function mutarActiva(Alerta $alerta, callable $operacion, bool $permitirInicio = false): Alerta
    {
        return DB::transaction(function () use ($alerta, $operacion, $permitirInicio) {
            $bloqueada = Alerta::lockForUpdate()->findOrFail($alerta->getKey());
            abort_unless($bloqueada->puedeCerrarse()
                || ($permitirInicio && in_array($bloqueada->estado, ['RECONOCIDA', 'ASIGNADA', 'PENDIENTE'], true)),
                409, 'La alerta ya está cerrada.');
            $operacion($bloqueada);
            return $bloqueada->refresh();
        });
    }

    public function crearDesdeHttp(string $codResidente, array $datos, User $usuario): Alerta
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'alertas.gestionar', $usuario);
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
        if ($usuario->hasRole('ADMINISTRADOR')) {
            abort_unless(Auth::id() === $usuario->cod_usuario && $usuario->estado === 'ACTIVO' && $usuario->can($permiso), 403);
        } else {
            $this->turnos->autorizarMutacionEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
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
            $this->accion($bloqueada, 'CAMBIO_ESTADO', $datos['descripcion'], $usuario, $anterior);
            return $bloqueada->refresh();
        });
    }

    public function registrarCreacionAutomatica(Alerta $alerta, User $usuario): void
    {
        $this->autorizarCoordinacion($alerta->cod_residente, 'alertas.gestionar', $usuario);
        $this->accion($alerta, 'CREACION', 'Detección '.$alerta->modulo.' / '.$alerta->tipo, $usuario, null);
    }

    private function accion(Alerta $alerta, string $tipo, string $texto, User $usuario, ?string $estadoAnterior): void
    {
        $alerta->eventos()->create([
            'cod_evento_alerta' => 'EVA_' . strtoupper(\Illuminate\Support\Str::random(10)),
            'cod_usuario' => $usuario->cod_usuario,
            'tipo_evento' => $tipo,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $alerta->estado,
            'fecha_hora' => now(),
            'descripcion' => trim($texto),
        ]);
    }
}
