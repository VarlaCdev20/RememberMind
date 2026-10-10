<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Acciones\AlertasService;
use App\Modulos\Clinica\Aplicacion\Consultas\ContextoClinicoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class GestionarCuidadosEnFicha
{
    public function completarTarea(User $usuario, array $entrada, string $codTarea): void
    {

        abort_unless($usuario?->can('ejecuciones_cuidado.gestionar'), 403);
        $tarea = EjecucionCuidado::findOrFail($codTarea);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_residente, 'ejecuciones_cuidado.gestionar', $usuario);
        abort_unless($tarea->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');

        $tarea->update([
            'estado' => 'REALIZADA',
            'resultado' => 'Tarea efectuada y confirmada en turno.',
            'cod_personal' => $usuario->personal->cod_personal,
            'fecha_hora_ejecucion' => now(),
        ]);

    }

    public function guardarAccionTarea(User $usuario, array $entrada): void
    {

        abort_unless($usuario?->can('ejecuciones_cuidado.gestionar'), 403);
        $tarea = EjecucionCuidado::findOrFail($entrada['tareaAccionId']);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_residente, 'ejecuciones_cuidado.gestionar', $usuario);
        abort_unless($tarea->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');

        Validator::make($entrada, [
            'tareaEstadoAccion' => 'required|in:REALIZADA,OMITIDA',
        ])->validate();

        if ($entrada['tareaEstadoAccion'] === 'REALIZADA') {
            Validator::make($entrada, ['tareaResultado' => 'required|string|min:5|max:60'], ['tareaResultado.required' => 'El resultado de la tarea es obligatorio.', 'tareaResultado.min' => 'El resultado debe tener al menos 5 caracteres.'])->validate();
        } elseif ($entrada['tareaEstadoAccion'] === 'OMITIDA') {
            abort_unless($usuario?->can('ejecuciones_cuidado.gestionar'), 403);
            Validator::make($entrada, ['tareaMotivoOmision' => 'required|string|min:5|max:500'], ['tareaMotivoOmision.required' => 'El motivo de omisión es obligatorio.', 'tareaMotivoOmision.min' => 'El motivo debe tener al menos 5 caracteres.'])->validate();
        }

        $tarea->update([
            'estado' => $entrada['tareaEstadoAccion'] === 'REALIZADA' ? 'REALIZADA' : 'OMITIDA',
            'resultado' => $entrada['tareaResultado'] ?: ($entrada['tareaEstadoAccion'] === 'REALIZADA' ? 'Completada conforme al plan.' : null),
            'motivo_omision' => $entrada['tareaEstadoAccion'] === 'OMITIDA' ? $entrada['tareaMotivoOmision'] : null,
            'cod_personal' => $usuario->personal->cod_personal,
            'fecha_hora_ejecucion' => $entrada['tareaEstadoAccion'] === 'REALIZADA' ? now() : null,
        ]);

    }

    public function guardarSeguimiento(User $usuario, array $entrada): void
    {

        abort_unless($usuario?->can('atenciones.crear'), 403);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($entrada['adultoMayor'], 'atenciones.crear', $usuario);
        Validator::make($entrada, [
            'segEstado' => 'required|in:ESTABLE,VIGILANCIA,DELICADO,CRITICO',
            'segAlimentacion' => 'required|in:COMPLETA,PARCIAL,RECHAZADA,AYUNO',
            'segMovilidad' => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'segSueno' => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'segObs' => 'required|string|min:10|max:1000',
        ], [
            'segObs.required' => 'La nota de seguimiento es obligatoria.',
            'segObs.min' => 'La nota de seguimiento debe tener al menos 10 caracteres.',
        ])->validate();

        if ($entrada['segIncidente'] && mb_strlen(trim($entrada['segObs'])) < 15) {
            throw ValidationException::withMessages(['segObs' => 'Si reporta un incidente, detalle lo ocurrido con al menos 15 caracteres.']);
        }

        if ($entrada['segRequiereMedico'] && mb_strlen(trim($entrada['segObs'])) < 15) {
            throw ValidationException::withMessages(['segObs' => 'Si requiere evaluación médica, detalle el motivo con al menos 15 caracteres.']);
        }

        $turnoActivo = app(TurnoEnfermeriaService::class)->obtenerTurnoActivo($usuario);
        if (! $turnoActivo) {
            throw ValidationException::withMessages(['segObs' => 'No existe un turno activo para registrar el seguimiento.']);
        }

        if (Atencion::where('cod_residente', $entrada['adultoMayor']->cod_residente)
            ->whereDate('fecha_hora', today())
            ->where('tipo_atencion', 'SEGUIMIENTO_DIARIO')
            ->exists()) {
            throw ValidationException::withMessages(['segObs' => 'Ya existe un seguimiento registrado para este residente en el turno actual.']);
        }

        $personal = $usuario?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional asignados.');
        $atencion = Atencion::create([
            'cod_residente' => $entrada['adultoMayor']->cod_residente,
            'cod_area' => $codArea,
            'cod_personal' => $personal->cod_personal,
            'tipo_atencion' => 'SEGUIMIENTO_DIARIO',
            'motivo' => $entrada['segEstado'],
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
            'observacion' => $entrada['segObs'],
        ]);

        if ($entrada['segIncidente'] || $entrada['segRequiereMedico']) {
            app(AlertasService::class)->crear($entrada['adultoMayor']->cod_residente, [
                'origen' => $entrada['segIncidente'] ? 'INCIDENTE' : 'SOLICITUD_MEDICA',
                'tipo_alerta' => $entrada['segIncidente'] ? 'INCIDENTE_EN_SEGUIMIENTO' : 'EVALUACION_MEDICA_REQUERIDA',
                'nivel' => $entrada['segIncidente'] ? 'ALTO' : 'MEDIO',
                'motivo' => $entrada['segObs'],
            ], $usuario)->update([
                'cod_registro' => $atencion->cod_atencion,
            ]);
        }

    }

    public function registrarCuidadoDirecto(User $usuario, array $entrada, string $codIntervencion, string $resultado, ?string $observaciones): IntervencionCuidado
    {

        abort_unless($usuario !== null, 401);
        abort_unless($usuario->can('ejecuciones_cuidado.gestionar'), 403);
        validator(compact('resultado', 'observaciones'), [
            'resultado' => 'required|in:REALIZADA,INCIDENCIA',
            'observaciones' => 'required|string|min:10|max:2000',
        ])->validate();

        $turno = app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente(
            $entrada['adultoMayor']->cod_residente,
            'ejecuciones_cuidado.gestionar',
            $usuario,
        );
        $personal = app(ContextoClinicoService::class)
            ->personalActivo($usuario);
        $intervencion = IntervencionCuidado::query()
            ->where('cod_intervencion', $codIntervencion)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->whereHas('plan', fn ($query) => $query
                ->where('cod_residente', $entrada['adultoMayor']->cod_residente)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA']))
            ->firstOrFail();
        $jornada = Jornada::query()
            ->where('cod_turno', $turno->cod_turno)
            ->whereDate('fecha_jornada', today())
            ->whereIn('estado', ['ABIERTA', 'ACTIVA'])
            ->firstOrFail();

        $tarea = EjecucionCuidado::query()
            ->where('cod_residente', $entrada['adultoMayor']->cod_residente)
            ->where('cod_intervencion', $intervencion->cod_intervencion)
            ->where('cod_jornada', $jornada->cod_jornada)
            ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
            ->first();

        if ($tarea) {
            $tarea->update([
                'estado' => $resultado === 'INCIDENCIA' ? 'OMITIDA' : 'REALIZADA',
                'resultado' => $observaciones,
                'motivo_omision' => $resultado === 'INCIDENCIA' ? $observaciones : null,
                'cod_personal' => $personal->cod_personal,
                'fecha_hora_ejecucion' => now(),
            ]);
        } else {
            EjecucionCuidado::query()->create([
                'cod_intervencion' => $intervencion->cod_intervencion,
                'cod_residente' => $entrada['adultoMayor']->cod_residente,
                'cod_jornada' => $jornada->cod_jornada,
                'cod_personal' => $personal->cod_personal,
                'fecha_hora_programada' => now(),
                'fecha_hora_ejecucion' => now(),
                'resultado' => $observaciones,
                'motivo_omision' => $resultado === 'INCIDENCIA' ? $observaciones : null,
                'estado' => $resultado === 'INCIDENCIA' ? 'OMITIDA' : 'REALIZADA',
                'observacion' => $observaciones,
            ]);
        }

        return $intervencion;
    }
}
