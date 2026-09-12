<?php

namespace App\Services\Alertas;

use App\Models\AlertaAdulto;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AlertasService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function crear(string $codAm, array $datos, User $usuario, string $permiso = 'alertas.crear'): AlertaAdulto
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
            $existente = AlertaAdulto::lockForUpdate()->where('cod_am', $codAm)
                ->where('tipo_alerta', $tipo)->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->first();
            if ($existente) {
                return $existente;
            }
            return AlertaAdulto::create([
                'cod_am' => $codAm,
                'cod_turno' => $this->turnos->obtenerTurnoActivo($usuario)?->cod_turno,
                'origen' => $datos['origen'],
                'tipo_alerta' => $tipo,
                'nivel' => $datos['nivel'],
                'motivo' => trim($datos['motivo']),
                'responsable_id' => $usuario->cod_usu,
                'estado' => 'ABIERTA',
            ]);
        });
    }

    public function registrarIntervencion(AlertaAdulto $alerta, string $texto, User $usuario): AlertaAdulto
    {
        $this->turnos->autorizarMutacionEnfermeria($alerta->cod_am, 'alertas.atender', $usuario);
        Validator::make(['accion' => $texto], ['accion' => 'required|string|min:5|max:10000'])->validate();
        return $this->mutarActiva($alerta, function (AlertaAdulto $bloqueada) use ($texto, $usuario) {
            $bloqueada->update([
                'estado' => 'EN_ATENCION',
                'accion_tomada' => trim($texto),
                'fecha_atencion' => $bloqueada->fecha_atencion ?? now(),
                'atendido_por' => $usuario->cod_usu,
                'responsable_id' => $bloqueada->responsable_id ?? $usuario->cod_usu,
            ]);
            $this->accion($bloqueada, 'INTERVENCION', $texto, $usuario);
        });
    }

    public function asignarResponsable(AlertaAdulto $alerta, User $responsable, User $usuario): AlertaAdulto
    {
        $this->turnos->autorizarMutacionEnfermeria($alerta->cod_am, 'alertas.atender', $usuario);
        abort_unless($responsable->estado === 'ACTIVO', 422, 'El responsable seleccionado debe estar activo.');
        return $this->mutarActiva($alerta, function (AlertaAdulto $bloqueada) use ($responsable, $usuario) {
            $bloqueada->update(['responsable_id' => $responsable->getKey()]);
            $this->accion($bloqueada, 'ASIGNACION', 'Responsable: '.$responsable->name, $usuario);
        });
    }

    public function registrarSeguimiento(AlertaAdulto $alerta, string $texto, User $usuario): AlertaAdulto
    {
        $this->turnos->autorizarMutacionEnfermeria($alerta->cod_am, 'alertas.atender', $usuario);
        Validator::make(['seguimiento' => $texto], ['seguimiento' => 'required|string|min:5|max:10000'])->validate();
        return $this->mutarActiva($alerta, fn (AlertaAdulto $bloqueada) => $this->accion($bloqueada, 'SEGUIMIENTO', $texto, $usuario));
    }

    public function cerrar(AlertaAdulto $alerta, string $resultado, User $usuario): AlertaAdulto
    {
        $this->turnos->autorizarMutacionEnfermeria($alerta->cod_am, 'alertas.cerrar', $usuario);
        Validator::make(['resultado' => $resultado], ['resultado' => 'required|string|min:5|max:10000'])->validate();
        return $this->mutarActiva($alerta, function (AlertaAdulto $bloqueada) use ($resultado, $usuario) {
            $bloqueada->update(['estado' => 'CERRADA', 'fecha_cierre' => now(), 'cerrado_por' => $usuario->cod_usu, 'observacion_cierre' => trim($resultado)]);
            $this->accion($bloqueada, 'CIERRE', $resultado, $usuario);
        });
    }

    private function mutarActiva(AlertaAdulto $alerta, callable $operacion): AlertaAdulto
    {
        return DB::transaction(function () use ($alerta, $operacion) {
            $bloqueada = AlertaAdulto::lockForUpdate()->findOrFail($alerta->getKey());
            abort_unless($bloqueada->puedeCerrarse(), 409, 'La alerta ya está cerrada.');
            $operacion($bloqueada);
            return $bloqueada->refresh();
        });
    }

    private function accion(AlertaAdulto $alerta, string $tipo, string $texto, User $usuario): void
    {
        $alerta->acciones()->create([
            'accion' => "{$tipo}: ".trim($texto),
            'responsable_id' => $usuario->cod_usu,
            'fecha_accion' => now(),
            'estado' => 'REALIZADA',
        ]);
    }
}
