<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\EjecucionCuidado;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class GestionarRegistrosDashboardTurno
{
    public function completarTarea(User $usuario, array $entrada, string $codTarea): void
    {

        $this->asegurarModoOperativo($usuario, $entrada);
        $tarea = EjecucionCuidado::findOrFail($codTarea);
        $this->asegurarModoOperativo($usuario, $entrada, $tarea->cod_residente, 'ejecuciones_cuidado.gestionar');
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_residente, 'ejecuciones_cuidado.gestionar', $usuario);

        DB::transaction(function () use ($tarea) {
            $bloqueada = EjecucionCuidado::lockForUpdate()->findOrFail($tarea->cod_ejecucion);
            abort_unless($bloqueada->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');
            $bloqueada->update([
                'estado' => 'REALIZADA',
                'resultado' => 'Completada y confirmada desde el panel del turno.',
                'fecha_hora_ejecucion' => now(),
            ]);
        });

    }

    public function confirmarOmisionTarea(User $usuario, array $entrada): mixed
    {

        $this->asegurarModoOperativo($usuario, $entrada);
        $tarea = EjecucionCuidado::findOrFail($entrada['tareaOmitirId']);
        $this->asegurarModoOperativo($usuario, $entrada, $tarea->cod_residente, 'ejecuciones_cuidado.gestionar');
        Validator::make($entrada, [
            'motivoOmisionTarea' => 'required|string|min:5|max:500',
        ], [
            'motivoOmisionTarea.required' => 'Debe registrar el motivo de la omisión.',
        ])->validate();

        $tarea = EjecucionCuidado::findOrFail($entrada['tareaOmitirId']);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_residente, 'ejecuciones_cuidado.gestionar', $usuario);
        abort_unless($tarea->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');
        $tarea->update([
            'estado' => 'OMITIDA',
            'motivo_omision' => trim($entrada['motivoOmisionTarea']),
        ]);

        return $tarea;
    }

    public function guardarSeguimiento(User $usuario, array $entrada): void
    {

        $this->asegurarModoOperativo($usuario, $entrada, $entrada['segCodResidente'], 'atenciones.crear');
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($entrada['segCodResidente'], 'atenciones.crear', $usuario);
        Validator::make($entrada, [
            'segEstadoGeneral' => 'required|in:ESTABLE,VIGILANCIA,DELICADO,CRITICO',
            'segAlimentacion' => 'required|in:COMPLETA,PARCIAL,RECHAZADA,AYUNO',
            'segMovilidad' => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'segSueno' => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'segObservacion' => 'required|string|min:10|max:1000',
        ])->validate();

        if (($entrada['segIncidente'] || $entrada['segRequiereMedico']) && mb_strlen(trim($entrada['segObservacion'])) < 15) {
            throw ValidationException::withMessages(['segObservacion' => 'Describa la situación clínica y las medidas iniciales con al menos 15 caracteres.']);
        }
        if (! $entrada['turnoActual']) {
            throw ValidationException::withMessages(['segObservacion' => 'No existe un turno activo para registrar el seguimiento.']);
        }
        $personal = $usuario?->personal;
        abort_unless($personal, 422, 'El usuario no está vinculado a personal clínico.');
        $asignacionArea = AsignacionPersonal::query()
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->latest('fecha_asignacion')
            ->first();
        abort_unless($asignacionArea, 422, 'El personal no tiene un área institucional activa.');

        if (Atencion::where('cod_residente', $entrada['segCodResidente'])
            ->whereDate('fecha_hora', $entrada['filtroFecha'])
            ->where('cod_personal', $personal->cod_personal)
            ->where('tipo_atencion', 'SEGUIMIENTO_ENFERMERIA')->exists()) {
            throw ValidationException::withMessages(['segObservacion' => 'Ya existe un seguimiento de este residente para el turno y la fecha seleccionados.']);
        }

        Atencion::create([
            'cod_residente' => $entrada['segCodResidente'],
            'cod_area' => $asignacionArea->cod_area,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora' => now(),
            'tipo_atencion' => 'SEGUIMIENTO_ENFERMERIA',
            'motivo' => "Estado: {$entrada['segEstadoGeneral']}; alimentación: {$entrada['segAlimentacion']}; movilidad: {$entrada['segMovilidad']}; sueño: {$entrada['segSueno']}",
            'estado' => 'FINALIZADA',
            'observacion' => trim($entrada['segObservacion']),
        ]);

    }

    public function asegurarModoOperativo(User $usuario, array $entrada, ?string $codResidente = null, ?string $permiso = null): void
    {
        // 1. Usuario autenticado y cuenta activa
        $user = $usuario;
        abort_unless($user, 403, 'Acción no permitida: Usuario no autenticado.');
        abort_unless(strtoupper(trim((string) $user->estado)) === 'ACTIVO', 403, 'Acción no permitida: Cuenta de usuario no activa.');

        // 2. Personal institucional vinculado
        $personal = $user->personal;
        abort_unless($personal, 403, 'Acción no permitida: Personal no vinculado al usuario.');

        $accesoTemporal = app(AccesoClinicoTemporalService::class);
        if ($accesoTemporal->sustituyeRol($user)) {
            if ($permiso) {
                abort_unless($user->can($permiso), 403);
            }
            if ($codResidente && $permiso) {
                app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($codResidente, $permiso, $user);
            }

            return;
        }

        // 3. Jornada activa actual en tiempo real
        // Si la jornada terminó mientras la pantalla estaba abierta, resolverJornadaActual retornará null
        $miTurnoService = app(MiTurnoService::class);
        $ahora = Carbon::now();
        $jornadaActual = $miTurnoService->resolverJornadaActual($personal, $ahora);
        abort_unless($jornadaActual, 403, 'Acción no permitida: Usuario fuera de turno en modo consulta o su jornada ha finalizado.');

        // 4. Permiso si se especifica
        if ($permiso) {
            abort_unless($user->can($permiso), 403, "Acción no permitida: Carece del permiso [{$permiso}].");
        }

        abort_unless(app(AccesoClinicoTemporalService::class)
            ->tieneRol($user, ['ENFERMEROS']), 403, 'Acción clínica no permitida: Rol ENFERMEROS requerido.');

        // 5. Residente asignado en la jornada activa
        if ($codResidente) {
            $esAsignado = AsignacionResidenteJornada::query()
                ->where('cod_jornada', $jornadaActual->cod_jornada)
                ->where('cod_personal', $personal->cod_personal)
                ->where('cod_residente', $codResidente)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
                ->exists();
            abort_unless($esAsignado, 403, 'Acción no permitida: El residente no está asignado a su turno activo.');
        }
    }
}
