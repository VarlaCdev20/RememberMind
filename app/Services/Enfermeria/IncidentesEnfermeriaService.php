<?php

namespace App\Services\Enfermeria;

use App\Models\IncidenteResidente;
use App\Models\User;
use App\Services\Alertas\AlertasService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IncidentesEnfermeriaService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos, private readonly AlertasService $alertas, private readonly LesionesEnfermeriaService $lesiones) {}

    public function registrar(string $codAm, array $datos, User $usuario): IncidenteResidente
    {
        $this->turnos->autorizarMutacionEnfermeria($codAm, 'seguimiento.crear', $usuario);
        $datos = Validator::make($datos, [
            'tipo' => 'required|in:CAIDA,GOLPE,ERROR_MEDICACION,LESION,CAMBIO_CLINICO,OTRO',
            'lugar' => 'required|string|min:2|max:120', 'actividad_previa' => 'nullable|string|max:200',
            'fue_presenciado' => 'required|boolean', 'testigo' => 'required_if:fue_presenciado,true|nullable|string|max:200',
            'descripcion' => 'required|string|min:10|max:3000', 'dolor' => 'nullable|integer|min:0|max:10',
            'lesion' => 'required|boolean', 'movilidad_posterior' => 'nullable|string|max:50',
            'cambio_cognitivo' => 'required|boolean', 'medico_informado' => 'required|boolean',
            'familiar_informado' => 'required|boolean', 'requiere_seguimiento' => 'required|boolean',
            'tipo_lesion' => 'required_if:lesion,true|nullable|string|max:50',
            'zona_lesion' => 'required_if:lesion,true|nullable|string|max:120', 'lateralidad' => 'nullable|string|max:20',
        ])->validate();

        return DB::transaction(function () use ($codAm, $datos, $usuario) {
            $incidente = IncidenteResidente::create([
                ...collect($datos)->except(['tipo_lesion','zona_lesion','lateralidad'])->all(),
                'cod_am' => $codAm,
                'cod_turno' => $this->turnos->obtenerTurnoActivo($usuario)?->cod_turno,
                'registrado_por' => $usuario->cod_usu,
                'responsable_id' => $usuario->cod_usu,
                'fecha_hora_evento' => now(),
                'estado' => 'ABIERTO',
            ]);
            if ($datos['lesion']) {
                $this->lesiones->crearDesdeIncidente($incidente, [
                    'tipo' => $datos['tipo_lesion'], 'zona_corporal' => $datos['zona_lesion'], 'lateralidad' => $datos['lateralidad'] ?? null,
                ], $usuario);
            }
            $this->alertas->crear($codAm, [
                'origen' => 'INCIDENTE', 'tipo_alerta' => $datos['tipo'],
                'nivel' => $datos['tipo'] === 'CAIDA' ? 'ALTO' : 'MEDIO',
                'motivo' => '[incidentes_residente:'.$incidente->getKey().'] '.$datos['descripcion'],
            ], $usuario);
            return $incidente;
        });
    }

    public function registrarSeguimiento(IncidenteResidente $incidente, string $accion, User $usuario): IncidenteResidente
    {
        $this->turnos->autorizarMutacionEnfermeria($incidente->cod_am, 'seguimiento.crear', $usuario);
        Validator::make(['accion' => $accion], ['accion' => 'required|string|min:10|max:5000'])->validate();
        return DB::transaction(function () use ($incidente, $accion, $usuario) {
            $bloqueado = IncidenteResidente::lockForUpdate()->findOrFail($incidente->getKey());
            abort_unless(in_array($bloqueado->estado, ['ABIERTO','EN_SEGUIMIENTO']), 409, 'El incidente está cerrado.');
            $historial = trim(collect([$bloqueado->seguimiento, '['.now()->format('Y-m-d H:i').'] '.trim($accion)])->filter()->implode("\n"));
            $bloqueado->update(['estado' => 'EN_SEGUIMIENTO', 'seguimiento' => $historial, 'fecha_seguimiento' => now(), 'responsable_id' => $usuario->cod_usu]);
            return $bloqueado->refresh();
        });
    }

    public function cerrar(IncidenteResidente $incidente, string $evaluacion, string $resultado, User $usuario): IncidenteResidente
    {
        $this->turnos->autorizarMutacionEnfermeria($incidente->cod_am, 'seguimiento.crear', $usuario);
        Validator::make(compact('evaluacion','resultado'), [
            'evaluacion' => 'required|string|min:10|max:5000', 'resultado' => 'required|string|min:5|max:5000',
        ])->validate();
        return DB::transaction(function () use ($incidente, $evaluacion, $resultado, $usuario) {
            $bloqueado = IncidenteResidente::lockForUpdate()->findOrFail($incidente->getKey());
            abort_unless(in_array($bloqueado->estado, ['ABIERTO','EN_SEGUIMIENTO']), 409, 'El incidente ya está cerrado.');
            $bloqueado->update(['estado' => 'CERRADO', 'evaluacion_final' => trim($evaluacion), 'resultado_cierre' => trim($resultado), 'fecha_cierre' => now(), 'cerrado_por' => $usuario->cod_usu, 'responsable_id' => $usuario->cod_usu]);
            return $bloqueado->refresh();
        });
    }
}
