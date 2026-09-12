<?php

namespace App\Services\Enfermeria;

use App\Models\IncidenteResidente;
use App\Models\LesionResidente;
use App\Models\SeguimientoLesion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LesionesEnfermeriaService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function crearDesdeIncidente(IncidenteResidente $incidente, array $datos, User $usuario): LesionResidente
    {
        abort_unless($incidente->registrado_por === $usuario->cod_usu || $incidente->exists, 403);
        $datos = Validator::make($datos, ['tipo' => 'required|string|max:50', 'zona_corporal' => 'required|string|max:120', 'lateralidad' => 'nullable|string|max:20'])->validate();
        return LesionResidente::create($datos + ['cod_am' => $incidente->cod_am, 'cod_incidente' => $incidente->getKey(), 'estado' => 'ACTIVA', 'fecha_deteccion' => now(), 'registrado_por' => $usuario->cod_usu]);
    }

    public function seguimiento(LesionResidente $lesion, array $datos, User $usuario): SeguimientoLesion
    {
        $this->turnos->autorizarMutacionEnfermeria($lesion->cod_am, 'seguimiento.crear', $usuario);
        abort_unless($lesion->estado === 'ACTIVA', 409, 'La lesión está cerrada.');
        $datos = Validator::make($datos, [
            'es_medible' => 'required|boolean', 'largo_cm' => 'required_if:es_medible,true|nullable|numeric|min:0|max:100',
            'ancho_cm' => 'required_if:es_medible,true|nullable|numeric|min:0|max:100', 'profundidad_cm' => 'nullable|numeric|min:0|max:100',
            'dolor' => 'nullable|integer|min:0|max:10', 'exudado' => 'nullable|string|max:50', 'piel_circundante' => 'nullable|string|max:120',
            'aspecto' => 'required|string|min:5|max:1000', 'accion_realizada' => 'required|string|min:5|max:1000', 'observacion' => 'nullable|string|max:1000',
        ])->validate();
        return SeguimientoLesion::create($datos + ['cod_lesion' => $lesion->getKey(), 'fecha_hora_evento' => now(), 'registrado_por' => $usuario->cod_usu]);
    }

    public function cerrar(LesionResidente $lesion, string $resultado, string $motivo, User $usuario): LesionResidente
    {
        $this->turnos->autorizarMutacionEnfermeria($lesion->cod_am, 'seguimiento.crear', $usuario);
        Validator::make(compact('resultado','motivo'), ['resultado' => 'required|string|min:5|max:2000', 'motivo' => 'required|string|min:5|max:2000'])->validate();
        return DB::transaction(function () use ($lesion, $resultado, $motivo, $usuario) {
            $bloqueada = LesionResidente::lockForUpdate()->findOrFail($lesion->getKey());
            abort_unless($bloqueada->estado === 'ACTIVA', 409, 'La lesión ya está cerrada.');
            abort_unless($bloqueada->seguimientos()->exists(), 409, 'Registre al menos una curación o seguimiento antes del cierre.');
            $bloqueada->update(['estado' => 'CERRADA', 'resultado_cierre' => trim($resultado), 'motivo_cierre' => trim($motivo), 'fecha_cierre' => now(), 'cerrado_por' => $usuario->cod_usu]);
            return $bloqueada->refresh();
        });
    }
}
