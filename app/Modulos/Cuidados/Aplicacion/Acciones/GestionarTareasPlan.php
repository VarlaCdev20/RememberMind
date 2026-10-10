<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\PlanCuidado;
use App\Models\ProgramacionCuidado;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class GestionarTareasPlan
{
    public function guardarTarea(User $usuario, array $entrada): void
    {

        abort_unless($usuario?->can('ejecuciones_cuidado.gestionar'), 403);

        Validator::make($entrada, [
            'codPlan' => 'required|exists:planes_cuidado,cod_plan',
            'codTurno' => ['required', Rule::exists('turnos', 'cod_turno')->where('estado', 'ACTIVO')],
            'responsableId' => ['nullable', Rule::exists('usuarios', 'cod_usuario')->where('estado', 'ACTIVO')],
            'horaProgramada' => 'nullable|date_format:H:i',
            'area' => 'required|in:SIGNOS,MEDICACION,MOVILIDAD,COGNITIVO,ALIMENTACION,HIDRATACION,HIGIENE,SUEÑO,SEGURIDAD,EMOCIONAL,FAMILIAR,REEVALUACION',
            'titulo' => 'required|string|min:3|max:200',
            'descripcion' => 'nullable|string|max:2000',
            'frecuencia' => 'nullable|string|max:100',
            'fechaProgramada' => 'required|date',
            'prioridad' => 'required|in:BAJA,NORMAL,ALTA,URGENTE',
        ], [
            'codPlan.required' => 'Seleccione un plan de cuidado.',
            'codTurno.required' => 'Seleccione un turno.',
            'area.required' => 'Seleccione el área de la tarea.',
            'titulo.required' => 'El título es obligatorio.',
            'titulo.min' => 'El título debe tener al menos 3 caracteres.',
            'fechaProgramada.required' => 'La fecha es obligatoria.',
            'codTurno.exists' => 'Seleccione un turno activo.',
            'responsableId.exists' => 'Seleccione un responsable activo.',
        ])->validate();

        $plan = PlanCuidado::findOrFail($entrada['codPlan']);
        if ($plan->estado !== 'ACTIVO') {
            throw ValidationException::withMessages(['codPlan' => 'Seleccione un plan activo.']);
        }
        if ($entrada['codResidente'] && $entrada['codResidente'] !== $plan->cod_residente) {
            throw ValidationException::withMessages(['codPlan' => 'El plan de cuidados no corresponde al paciente seleccionado.']);
        }
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($plan->cod_residente, 'ejecuciones_cuidado.gestionar', $usuario);

        // ejecuciones_cuidado.cod_personal exige personal.cod_personal; nunca se
        // guarda usuarios.cod_usuario como si fuera una FK clínica.
        $responsable = $entrada['responsableId']
            ? User::query()->with('personal')->findOrFail($entrada['responsableId'])->personal
            : $usuario?->personal;
        abort_unless($responsable, 422, 'El usuario responsable no está vinculado a personal.');

        // La ejecución pertenece a una jornada concreta, no directamente a un turno.
        $jornada = Jornada::query()
            ->where('cod_turno', $entrada['codTurno'])
            ->whereDate('fecha_jornada', $entrada['fechaProgramada'])
            ->whereIn('estado', ['ABIERTA', 'ACTIVA'])
            ->first();
        abort_unless($jornada, 422, 'No existe una jornada activa para el turno y la fecha seleccionados.');

        // Una tarea V2 se compone de intervención, programación y ejecución.
        // Las tres filas se crean atómicamente para evitar tareas incompletas.
        DB::transaction(function () use ($plan, $responsable, $jornada, $entrada): void {
            $intervencion = IntervencionCuidado::query()->create([
                'cod_intervencion' => 'INT_'.Str::upper(Str::random(10)),
                'cod_plan' => $plan->cod_plan,
                'nombre' => trim($entrada['titulo']),
                'descripcion' => filled($entrada['descripcion']) ? trim($entrada['descripcion']) : trim($entrada['titulo']),
                'objetivo_especifico' => filled($entrada['area']) ? 'Área de cuidado: '.$entrada['area'] : null,
                'prioridad' => $entrada['prioridad'],
                'estado' => 'ACTIVA',
            ]);

            ProgramacionCuidado::query()->create([
                'cod_programacion' => 'PRG_'.Str::upper(Str::random(10)),
                'cod_intervencion' => $intervencion->cod_intervencion,
                'cod_turno' => $entrada['codTurno'],
                'frecuencia' => filled($entrada['frecuencia']) ? trim($entrada['frecuencia']) : 'UNA VEZ',
                'dias_semana' => null,
                'hora_programada' => $entrada['horaProgramada'] ?: null,
                'fecha_activacion' => $entrada['fechaProgramada'],
                'fecha_desactivacion' => null,
                'estado' => 'ACTIVA',
            ]);

            EjecucionCuidado::query()->create([
                'cod_ejecucion' => 'EJC_'.Str::upper(Str::random(10)),
                'cod_intervencion' => $intervencion->cod_intervencion,
                'cod_residente' => $plan->cod_residente,
                'cod_jornada' => $jornada->cod_jornada,
                'cod_personal' => $responsable->cod_personal,
                'fecha_hora_programada' => $entrada['fechaProgramada'].' '.($entrada['horaProgramada'] ?: '00:00').':00',
                'estado' => 'PENDIENTE',
            ]);
        });

    }

    public function guardarResultado(User $usuario, array $entrada): void
    {

        abort_unless($usuario?->can('ejecuciones_cuidado.gestionar'), 403);

        Validator::make($entrada, [
            'estadoTarea' => 'required|in:REALIZADA,OMITIDA,REPROGRAMADA',
            'fechaProgramada' => $entrada['estadoTarea'] === 'REPROGRAMADA' ? 'required|date|after_or_equal:today' : 'nullable|date',
            'horaProgramada' => $entrada['estadoTarea'] === 'REPROGRAMADA' ? 'required|date_format:H:i' : 'nullable|date_format:H:i',
            'resultado' => $entrada['estadoTarea'] === 'REALIZADA' ? 'required|string|min:5|max:60' : 'nullable|string|max:60',
            'observacion' => 'nullable|string|max:2000',
            'motivoOmision' => in_array($entrada['estadoTarea'], ['OMITIDA', 'REPROGRAMADA']) ? 'required|string|min:5|max:1000' : 'nullable|string|max:1000',
        ], [
            'resultado.required' => 'Registre el resultado de la tarea (mínimo 5 caracteres).',
            'resultado.min' => 'El resultado debe tener al menos 5 caracteres.',
            'motivoOmision.required' => $entrada['estadoTarea'] === 'REPROGRAMADA' ? 'Registre el motivo de la reprogramación.' : 'Registre el motivo de omisión.',
            'motivoOmision.min' => 'El motivo debe tener al menos 5 caracteres.',
            'horaProgramada.required' => 'La nueva hora programada es obligatoria para reprogramar.',
            'fechaProgramada.after_or_equal' => 'La nueva fecha no puede estar en el pasado.',
        ])->validate();

        if ($entrada['estadoTarea'] === 'OMITIDA') {
            abort_unless($usuario?->can('ejecuciones_cuidado.gestionar'), 403);
        }
        DB::transaction(function () use ($usuario, $entrada): void {
            // El bloqueo impide que dos enfermeros completen la misma ejecución.
            $tarea = EjecucionCuidado::lockForUpdate()->findOrFail($entrada['resultandoId']);
            abort_unless($tarea->puedeCompletarse(), 409);
            app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria(
                $tarea->cod_residente,
                'ejecuciones_cuidado.gestionar',
                $usuario
            );

            if ($entrada['estadoTarea'] === 'REPROGRAMADA') {
                // Se conserva la ejecución original y se crea una nueva pendiente;
                // así no se pierde la trazabilidad clínica de la reprogramación.
                $nueva = $tarea->replicate();
                $nueva->cod_ejecucion = 'EJC_'.Str::upper(Str::random(10));
                $nueva->fecha_hora_programada = $entrada['fechaProgramada'].' '.$entrada['horaProgramada'].':00';
                $nueva->estado = 'PENDIENTE';
                $nueva->resultado = null;
                $nueva->observacion = null;
                $nueva->motivo_omision = null;
                $nueva->fecha_hora_ejecucion = null;
                $nueva->save();
            }

            $tarea->update([
                'estado' => $entrada['estadoTarea'],
                'resultado' => filled($entrada['resultado']) ? trim($entrada['resultado']) : null,
                'observacion' => filled($entrada['observacion']) ? trim($entrada['observacion']) : null,
                'motivo_omision' => filled($entrada['motivoOmision']) ? trim($entrada['motivoOmision']) : null,
                'fecha_hora_ejecucion' => $entrada['estadoTarea'] === 'REALIZADA' ? now() : null,
            ]);
        });
    }
}
