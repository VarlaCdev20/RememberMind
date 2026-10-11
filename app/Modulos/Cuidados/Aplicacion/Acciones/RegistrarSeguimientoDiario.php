<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class RegistrarSeguimientoDiario
{
    public function guardarSeguimiento(User $usuario, array $entrada): void
    {

        abort_if($entrada['esModoConsulta'], 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless($usuario?->can('atenciones.crear'), 403);
        $turno = app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($entrada['modalCodResidente'], 'atenciones.crear', $usuario);
        $service = app(TurnoEnfermeriaService::class);
        $turno = $service->obtenerTurnoActivo($usuario);
        Validator::make($entrada, [
            'segEstado' => 'required|in:ESTABLE,VIGILANCIA,DELICADO,CRITICO',
            'segAlimentacion' => 'required|in:COMPLETA,PARCIAL,RECHAZADA,AYUNO',
            'segMovilidad' => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'segSueno' => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'segObs' => 'required|string|min:10|max:1000',
        ])->validate();
        if (($entrada['segIncidente'] || $entrada['segRequiereMedico']) && mb_strlen(trim($entrada['segObs'])) < 15) {
            throw ValidationException::withMessages(['segObs' => 'Describa la situación clínica y las medidas iniciales con al menos 15 caracteres.']);
        }
        if (! $turno) {
            throw ValidationException::withMessages(['segObs' => 'No existe un turno activo para registrar el seguimiento.']);
        }
        if (Atencion::where('cod_residente', $entrada['modalCodResidente'])->whereDate('fecha_hora', today())
            ->where('tipo_atencion', 'SEGUIMIENTO_DIARIO')->exists()) {
            throw ValidationException::withMessages(['segObs' => 'Ya existe un seguimiento de este residente para el turno actual.']);
        }

        $personal = $usuario?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional asignados.');
        Atencion::create([
            'cod_residente' => $entrada['modalCodResidente'],
            'cod_area' => $codArea,
            'cod_personal' => $personal->cod_personal,
            'tipo_atencion' => 'SEGUIMIENTO_DIARIO',
            'motivo' => $entrada['segEstado'],
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
            'observacion' => trim($entrada['segObs']),
        ]);

    }
}
