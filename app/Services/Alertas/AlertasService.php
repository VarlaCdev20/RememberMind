<?php

namespace App\Services\Alertas;

use App\Models\Alerta;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AlertasService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function crear(string $codAm, array $datos, User $usuario, string $permiso = 'alertas.gestionar'): Alerta
    {
        $this->turnos->autorizarMutacionEnfermeria($codAm, $permiso, $usuario);
        $datos = Validator::make($datos, [
            'origen' => 'required|in:SIGNOS,MEDICACION,SEGUIMIENTO,PLAN,INCIDENTE,SOLICITUD_MEDICA,MANUAL,FICHA,VALORACION',
            'tipo_alerta' => 'required|string|min:3|max:80',
            'nivel' => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
            'motivo' => 'required|string|min:10|max:10000',
        ])->validate();
        $tipo = mb_strtoupper(trim($datos['tipo_alerta']));

        return DB::transaction(function () use ($codAm, $datos, $tipo, $usuario) {
            $existente = Alerta::lockForUpdate()->where('cod_residente', $codAm)
                ->where('tipo', $tipo)->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->first();
            if ($existente) {
                return $existente;
            }
            $codPersonal = $usuario->personal?->cod_personal;
            return Alerta::create([
                'cod_alerta' => 'ALA_' . strtoupper(\Illuminate\Support\Str::random(10)),
                'cod_residente' => $codAm,
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
        });
    }

    public function registrarIntervencion(Alerta $alerta, string $texto, User $usuario): Alerta
    {
        $this->turnos->autorizarMutacionEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        Validator::make(['accion' => $texto], ['accion' => 'required|string|min:5|max:10000'])->validate();
        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($texto, $usuario) {
            $bloqueada->update(['estado' => 'EN_ATENCION']);
            $this->accion($bloqueada, 'INTERVENCION', $texto, $usuario);
        });
    }

    public function asignarResponsable(Alerta $alerta, User $responsable, User $usuario): Alerta
    {
        $this->turnos->autorizarMutacionEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        abort_unless($responsable->estado === 'ACTIVO', 422, 'El responsable seleccionado debe estar activo.');
        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($responsable, $usuario) {
            $codPersonal = $responsable->personal?->cod_personal;
            abort_unless($codPersonal, 422, 'El responsable seleccionado no tiene un registro de personal.');
            $bloqueada->update(['cod_personal_responsable' => $codPersonal]);
            $this->accion($bloqueada, 'ASIGNACION', 'Responsable: '.$responsable->name, $usuario);
        });
    }

    public function registrarSeguimiento(Alerta $alerta, string $texto, User $usuario): Alerta
    {
        $this->turnos->autorizarMutacionEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        Validator::make(['seguimiento' => $texto], ['seguimiento' => 'required|string|min:5|max:10000'])->validate();
        return $this->mutarActiva($alerta, fn (Alerta $bloqueada) => $this->accion($bloqueada, 'SEGUIMIENTO', $texto, $usuario));
    }

    public function cerrar(Alerta $alerta, string $resultado, User $usuario): Alerta
    {
        $this->turnos->autorizarMutacionEnfermeria($alerta->cod_residente, 'alertas.gestionar', $usuario);
        Validator::make(['resultado' => $resultado], ['resultado' => 'required|string|min:5|max:10000'])->validate();
        return $this->mutarActiva($alerta, function (Alerta $bloqueada) use ($resultado, $usuario) {
            $bloqueada->update(['estado' => 'CERRADA']);
            $this->accion($bloqueada, 'CIERRE', $resultado, $usuario);
        });
    }

    private function mutarActiva(Alerta $alerta, callable $operacion): Alerta
    {
        return DB::transaction(function () use ($alerta, $operacion) {
            $bloqueada = Alerta::lockForUpdate()->findOrFail($alerta->getKey());
            abort_unless($bloqueada->puedeCerrarse(), 409, 'La alerta ya está cerrada.');
            $operacion($bloqueada);
            return $bloqueada->refresh();
        });
    }

    private function accion(Alerta $alerta, string $tipo, string $texto, User $usuario): void
    {
        $alerta->eventos()->create([
            'cod_evento_alerta' => 'EVA_' . strtoupper(\Illuminate\Support\Str::random(10)),
            'cod_usuario' => $usuario->cod_usuario,
            'tipo_evento' => $tipo,
            'fecha_hora' => now(),
            'descripcion' => trim($texto),
        ]);
    }
}
