<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class GestionarEjecucionesAgenda
{
    public function recibirTurno(User $usuario, array $entrada): void
    {

        $turnos = app(TurnoEnfermeriaService::class);
        abort_unless(app(AccesoClinicoTemporalService::class)
            ->tieneRol($usuario, ['ENFERMEROS']) && $usuario?->can('enfermeria.ver_dashboard'), 403,
            'La recepción corresponde al personal operativo de Enfermería.');
        $turno = $turnos->obtenerTurnoActivo($usuario, today()->toDateString());
        abort_unless($turno, 409, 'No tiene un turno activo asignado para recibir.');

        $codPersonal = $usuario?->personal?->cod_personal;
        abort_unless($codPersonal, 409, 'El usuario no tiene un perfil de personal activo.');

        $jornada = Jornada::where('cod_turno', $turno->cod_turno)
            ->whereDate('fecha_jornada', today())
            ->first();
        abort_unless($jornada, 409, 'La jornada del turno no está formalizada. Contacte a administración.');

        DB::transaction(function () use ($jornada, $codPersonal, $entrada) {
            $asignacion = AsignacionPersonal::where('cod_jornada', $jornada->cod_jornada)
                ->where('cod_personal', $codPersonal)
                ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                ->lockForUpdate()
                ->first();
            abort_unless($asignacion, 409, 'No existe una asignación institucional activa para recibir este turno.');

            $notaRecepcion = trim($entrada['observacionRecepcion']) ?: 'Recepción de turno confirmada';
            $asignacion->update([
                'observacion' => trim(implode(' | ', array_filter([
                    $asignacion->observacion,
                    $notaRecepcion.' por '.$codPersonal.' el '.now()->format('Y-m-d H:i'),
                ]))),
            ]);
        });

    }

    public function registrarEjecucion(User $usuario, array $entrada): bool
    {

        Validator::make($entrada, [
            'resultado' => 'required|string|min:3|max:60',
            'observacion' => 'nullable|string|max:500',
            'motivoOmision' => 'nullable|string|max:500',
        ])->validate();

        if ($entrada['esNoRealizada'] || in_array(strtolower($entrada['resultado']), ['no realizada', 'omitida', 'no realizada / omitida'])) {
            if (mb_strlen(trim($entrada['motivoOmision'])) < 5) {
                throw ValidationException::withMessages(['motivoOmision' => 'El motivo de omisión es obligatorio cuando la intervención no se realiza (mínimo 5 caracteres).']);
            }
        }

        $user = $usuario;
        $turno = app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria(
            $entrada['residenteId'],
            'ejecuciones_cuidado.crear',
            $user,
        );
        $personal = $user?->personal()
            ->where('estado', 'ACTIVO')
            ->first();
        abort_unless($personal, 409, 'El usuario no tiene un perfil de personal activo.');

        $asignacion = AsignacionResidenteJornada::query()
            ->with('jornada')
            ->where('cod_residente', $entrada['residenteId'])
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'ASIGNADO'])
            ->whereHas('jornada', fn ($query) => $query
                ->where('cod_turno', $turno->cod_turno)
                ->whereDate('fecha_jornada', today())
                ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO']))
            ->latest('fecha_hora')
            ->first();
        abort_unless($asignacion?->jornada, 409, 'No existe una jornada activa asignada para registrar el cuidado.');

        $intervencionValida = IntervencionCuidado::query()
            ->where('cod_intervencion', $entrada['intervencionId'])
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->whereHas('plan', fn ($query) => $query
                ->where('cod_residente', $entrada['residenteId'])
                ->whereIn('estado', ['ACTIVO', 'ACTIVA']))
            ->exists();
        abort_unless($intervencionValida, 422, 'La intervención no pertenece al plan activo del residente.');

        $codPersonal = $personal->cod_personal;
        $codJornada = $asignacion->cod_jornada;

        $horaProg = $entrada['datosModal']['hora_programada'] ?? now()->format('H:i');
        $fechaHoraProgramada = Carbon::parse(today()->toDateString().' '.$horaProg);

        // Idempotencia: evitar doble ejecución de la misma intervención y programación en el mismo turno
        $existente = EjecucionCuidado::where('cod_intervencion', $entrada['intervencionId'])
            ->where('cod_residente', $entrada['residenteId'])
            ->where('cod_jornada', $codJornada)
            ->where('fecha_hora_programada', $fechaHoraProgramada)
            ->first();

        if ($existente) {
            return false;
        }

        $estadoEjecucion = ($entrada['esNoRealizada'] || in_array(strtolower($entrada['resultado']), ['no realizada', 'omitida', 'no realizada / omitida']))
            ? 'NO_REALIZADA'
            : 'REALIZADA';

        DB::transaction(function () use ($codPersonal, $codJornada, $fechaHoraProgramada, $estadoEjecucion, $entrada) {
            EjecucionCuidado::create([
                'cod_ejecucion' => 'EJC_'.strtoupper(Str::random(10)),
                'cod_intervencion' => $entrada['intervencionId'],
                'cod_residente' => $entrada['residenteId'],
                'cod_jornada' => $codJornada,
                'cod_personal' => $codPersonal,
                'fecha_hora_programada' => $fechaHoraProgramada,
                'fecha_hora_ejecucion' => now(),
                'resultado' => $entrada['resultado'],
                'motivo_omision' => $entrada['motivoOmision'] ?: null,
                'estado' => $estadoEjecucion,
                'observacion' => $entrada['observacion'] ?: null,
            ]);
        });

        return true;
    }
}
