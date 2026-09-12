<?php

namespace App\Services\Enfermeria;

use App\Models\SeguimientoDiario;
use App\Models\User;
use App\Services\Alertas\AlertasService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EvolucionEnfermeriaService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos, private readonly AlertasService $alertas) {}

    public function registrar(string $codAm, array $datos, User $usuario): SeguimientoDiario
    {
        $this->turnos->autorizarMutacionEnfermeria($codAm, 'seguimiento.crear', $usuario);
        $datos = Validator::make($datos, [
            'estado_general' => 'required|in:ESTABLE,VIGILANCIA,DELICADO,CRITICO',
            'alimentacion' => 'required|in:COMPLETA,PARCIAL,RECHAZADA,AYUNO', 'porcentaje_alimentacion' => 'nullable|integer|min:0|max:100',
            'hidratacion' => 'nullable|string|max:50', 'movilidad' => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'higiene' => 'nullable|string|max:50', 'sueno' => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'orientacion' => 'nullable|string|max:50', 'conducta' => 'nullable|string|max:50', 'participacion' => 'nullable|string|max:50',
            'incidente' => 'required|boolean', 'requiere_medico' => 'required|boolean', 'observacion' => 'nullable|string|max:5000',
        ])->validate();
        if (($datos['incidente'] || $datos['requiere_medico']) && mb_strlen(trim($datos['observacion'] ?? '')) < 15) {
            throw \Illuminate\Validation\ValidationException::withMessages(['observacion' => 'Describa la situación clínica y las medidas iniciales con al menos 15 caracteres.']);
        }
        $turno = $this->turnos->obtenerTurnoActivo($usuario);
        abort_unless($turno, 409, 'No existe un turno vigente para registrar la evolución.');
        $registro = DB::transaction(function () use ($codAm, $datos, $usuario, $turno) {
            abort_if(SeguimientoDiario::where('cod_am', $codAm)->whereDate('fecha', today())->where('cod_turno', $turno->cod_turno)->lockForUpdate()->exists(), 409, 'Ya existe una evolución para este residente durante el turno vigente.');
            return SeguimientoDiario::create($datos + ['cod_am' => $codAm, 'cod_turno' => $turno->cod_turno, 'registrado_por' => $usuario->cod_usu, 'fecha' => today(), 'hora_inicio' => now()->format('H:i:s')]);
        });
        if ($datos['incidente'] || $datos['requiere_medico']) {
            $this->alertas->crear($codAm, ['origen' => 'SEGUIMIENTO', 'tipo_alerta' => $datos['requiere_medico'] ? 'REVISIÓN MÉDICA REQUERIDA' : 'INCIDENTE EN EVOLUCIÓN', 'nivel' => $datos['estado_general'] === 'CRITICO' ? 'CRITICO' : 'ALTO', 'motivo' => $datos['observacion']], $usuario);
        }
        return $registro;
    }
}
