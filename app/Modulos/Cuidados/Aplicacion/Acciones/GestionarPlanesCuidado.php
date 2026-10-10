<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Models\AsignacionResidenteJornada;
use App\Models\PlanCuidado;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class GestionarPlanesCuidado
{
    public function guardar(User $usuario, array $entrada): mixed
    {

        abort_unless($usuario?->can($entrada['editandoId'] ? 'planes_cuidado.editar' : 'planes_cuidado.crear'), 403);
        if ($entrada['editandoId']) {
            abort_unless(PlanCuidado::findOrFail($entrada['editandoId'])->estado === 'BORRADOR', 409);
        }
        Validator::make($entrada, [
            'codResidente' => 'required|exists:residentes,cod_residente',
            'tipoPlan' => 'required|in:INICIAL,AJUSTE,REEVALUACION',
            'nivelCuidado' => 'required|in:PREVENTIVO,ESTANDAR,INTENSIVO,PALIATIVO',
            'estadoPlan' => 'required|in:BORRADOR,ACTIVO',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'nullable|date|after_or_equal:fechaInicio',
            'resumen' => 'required|string|min:5|max:10000',
        ], [
            'codResidente.required' => 'Seleccione un adulto mayor.',
            'tipoPlan.required' => 'Seleccione el tipo de plan.',
            'nivelCuidado.required' => 'Seleccione el nivel de cuidado.',
            'fechaInicio.required' => 'La fecha de inicio es obligatoria.',
        ])->validate();
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria(
            $entrada['codResidente'], $entrada['editandoId'] ? 'planes_cuidado.editar' : 'planes_cuidado.crear', $usuario
        );

        // Validar que no exista plan ACTIVO
        if ($entrada['estadoPlan'] === 'ACTIVO') {
            $activoExistente = PlanCuidado::where('cod_residente', $entrada['codResidente'])
                ->where('estado', 'ACTIVO')->when($entrada['editandoId'], fn ($q) => $q->where('cod_plan', '!=', $entrada['editandoId']))->exists();
            if ($activoExistente) {
                throw ValidationException::withMessages(['estadoPlan' => 'Este adulto mayor ya tiene un plan de cuidado ACTIVO. Ciérrelo antes de crear uno nuevo.']);
            }
        }

        // Validar que haya asignación activa para ACTIVO
        if ($entrada['estadoPlan'] === 'ACTIVO') {
            $asignado = AsignacionResidenteJornada::where('cod_residente', $entrada['codResidente'])
                ->whereIn('estado', ['ACTIVO', 'ACTIVA'])->exists();
            if (! $asignado) {
                throw ValidationException::withMessages(['codResidente' => 'El adulto mayor debe tener una asignación de turno activa antes de activar el plan.']);
            }
        }

        $personal = $usuario?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        if (! $personal || ! $codArea) {
            throw ValidationException::withMessages(['codResidente' => 'El usuario debe tener personal y área institucional asignados.']);
        }

        $datos = [
            'cod_residente' => $entrada['codResidente'],
            'cod_area' => $codArea,
            'cod_personal' => $personal->cod_personal,
            'tipo_plan' => $entrada['tipoPlan'],
            'nombre' => 'Plan de cuidados '.mb_strtolower($entrada['tipoPlan']),
            'objetivo_general' => $entrada['resumen'],
            'prioridad' => $entrada['nivelCuidado'],
            'fecha_hora_apertura' => $entrada['fechaInicio'].' 00:00:00',
            'fecha_hora_cierre' => $entrada['fechaFin'] ? $entrada['fechaFin'].' 23:59:59' : null,
            'estado' => $entrada['estadoPlan'],
            'observacion' => $entrada['origen'],
        ];

        if ($entrada['editandoId']) {
            PlanCuidado::findOrFail($entrada['editandoId'])->update($datos);
            $msg = 'Plan de cuidado actualizado.';
        } else {
            PlanCuidado::create($datos);
            $msg = 'Plan de cuidado creado.';
        }

        return $msg;
    }

    public function cerrarPlan(User $usuario, array $entrada, string $id): void
    {

        abort_unless($usuario?->can('planes_cuidado.cerrar'), 403);
        $plan = PlanCuidado::findOrFail($id);
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($plan->cod_residente, 'planes_cuidado.cerrar', $usuario);
        abort_unless($plan->estado === 'ACTIVO', 409, 'Solo puede cerrar un plan activo.');
        $plan->update([
            'estado' => 'CERRADO',
            'fecha_hora_cierre' => now(),
        ]);
    }
}
