<?php

namespace App\Modulos\Medicacion\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\AdministracionMedicacion;
use App\Models\AsignacionResidenteJornada;
use App\Models\HorarioPrescripcion;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Prescripcion;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegistrarAdministracionMedicacionService
{
    public function __construct(
        private readonly TurnoEnfermeriaService $turnos,
        private readonly AgendaMedicacionService $agenda,
    ) {}

    public function registrarProgramada(
        User $usuario,
        string $codResidente,
        string $codPrescripcion,
        string $horaOcurrencia,
        bool $administrada,
        ?string $motivoOmision = null,
        ?string $observacion = null,
        ?string $efectoObservado = null,
        mixed $dosisAdministrada = null,
        ?string $reaccionAdversa = null,
        ?string $fechaHoraAdministracion = null,
        ?string $codHorario = null,
    ): AdministracionMedicacion {
        $turno = $this->turnos->autorizarMutacionPaciente(
            $codResidente,
            'administraciones_medicacion.crear',
            $usuario,
        );
        $hora = $this->normalizarHora($horaOcurrencia);
        $dia = today()->toDateString();
        $ocurrencias = $this->agenda->paraAdulto($codResidente)
            ->filter(fn (array $item) => $item['medicacion']->cod_prescripcion === $codPrescripcion
                && $item['hora'] === $hora
                && ($codHorario === null || $item['horario']->cod_horario_prescripcion === $codHorario));
        $this->exigir($ocurrencias->count() === 1, 'Selecciona un horario concreto y vigente de la prescripción.', 'cod_horario_prescripcion');
        $ocurrencia = $ocurrencias->first();

        $this->exigir($ocurrencia !== null, 'La dosis no corresponde a un horario activo de hoy.');
        $this->exigir($ocurrencia['registro'] === null, 'Esta dosis ya tiene una administración u omisión registrada.', 'cod_prescripcion');
        Validator::make([
            'motivo_omision' => $administrada ? null : trim((string) $motivoOmision),
            'observacion' => $observacion,
            'efecto_observado' => $administrada ? $efectoObservado : null,
            'reaccion_adversa' => $administrada ? $reaccionAdversa : null,
        ], [
            'motivo_omision' => $administrada ? 'nullable' : 'required|string|min:5|max:500',
            'observacion' => 'nullable|string|max:2000',
            'efecto_observado' => 'nullable|string|max:2000',
            'reaccion_adversa' => 'nullable|string|max:2000',
        ])->validate();

        $personal = $usuario->personal;
        $this->exigir($personal !== null, 'El usuario autenticado no tiene un personal institucional asociado.', 'personal');
        $asignacion = AsignacionResidenteJornada::query()
            ->with('jornada')
            ->where('cod_residente', $codResidente)
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'ASIGNADO'])
            ->whereHas('jornada', fn ($query) => $query
                ->where('cod_turno', $turno->cod_turno)
                ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO', 'ACTIVO']))
            ->get()->filter(function ($asignacion) {
                [$inicio, $fin] = app(MiTurnoService::class)->calcularVentanaTurno($asignacion->jornada);

                return now()->betweenIncluded($inicio, $fin);
            })->values();
        $this->exigir($asignacion->count() === 1, 'Se requiere una jornada vigente inequívoca para este residente.', 'jornada');
        $asignacion = $asignacion->first();

        return DB::transaction(function () use (
            $codResidente,
            $codPrescripcion,
            $hora,
            $administrada,
            $motivoOmision,
            $observacion,
            $efectoObservado,
            $dosisAdministrada,
            $reaccionAdversa,
            $fechaHoraAdministracion,
            $personal,
            $asignacion,
            $ocurrencia,
            $codHorario,
            $usuario,
            $dia,
        ): AdministracionMedicacion {
            $this->exigir(today()->toDateString() === $dia, 'Cambió el día del registro. Actualiza la agenda.', 'fecha');
            $usuarioVigente = User::query()->whereKey($usuario->getKey())->where('estado', 'ACTIVO')->lockForUpdate()->first();
            $this->exigir($usuarioVigente !== null, 'La cuenta ya no está activa.', 'personal');
            $this->turnos->autorizarMutacionPaciente($codResidente, 'administraciones_medicacion.crear', $usuarioVigente);
            $personalVigente = Personal::query()->whereKey($personal->getKey())
                ->where('cod_usuario', $usuarioVigente->getKey())->where('estado', 'ACTIVO')->lockForUpdate()->first();
            $this->exigir($personalVigente !== null && $personalVigente->getKey() === $personal->getKey(), 'Cambió el profesional asociado a la cuenta. Actualiza el formulario.', 'personal');
            $asignacionVigente = AsignacionResidenteJornada::query()->whereKey($asignacion->getKey())
                ->where('cod_residente', $codResidente)->where('cod_personal', $personalVigente->getKey())
                ->where('cod_jornada', $asignacion->cod_jornada)
                ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'ASIGNADO'])->lockForUpdate()->first();
            $jornada = Jornada::query()->whereKey($asignacion->cod_jornada)
                ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO', 'ACTIVO'])->with('turno')->lockForUpdate()->first();
            $this->exigir($asignacionVigente !== null && $jornada !== null, 'La asignación o jornada ya no está vigente.', 'jornada');
            [$inicio, $fin] = app(MiTurnoService::class)->calcularVentanaTurno($jornada);
            $this->exigir(now()->betweenIncluded($inicio, $fin), 'La jornada ya no está en curso.', 'jornada');
            $prescripcion = Prescripcion::query()
                ->whereKey($codPrescripcion)
                ->where('cod_residente', $codResidente)
                ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                ->where('segun_necesidad', false)
                ->where('fecha_hora_prescripcion', '<=', now())
                ->lockForUpdate()
                ->first();
            $this->exigir($prescripcion !== null, 'La prescripción no pertenece al residente o ya no está activa.');

            $horario = HorarioPrescripcion::query()
                ->whereKey($ocurrencia['horario']->cod_horario_prescripcion)
                ->where('cod_prescripcion', $prescripcion->cod_prescripcion)
                ->where('estado', 'ACTIVO')
                ->lockForUpdate()
                ->first();
            $this->exigir($horario !== null && ($codHorario === null || $horario->cod_horario_prescripcion === $codHorario),
                'El horario programado no corresponde a la prescripción activa.', 'cod_horario_prescripcion');
            $this->exigir(Carbon::parse($horario->hora_programada)->format('H:i') === $hora
                && (! filled($horario->dias_semana) || strtoupper(trim($horario->dias_semana)) === 'TODOS'
                    || app(MiTurnoService::class)->correspondeDiaSemana($horario->dias_semana, now())),
                'La hora o pauta del horario cambió. Actualiza la agenda antes de registrar.', 'cod_horario_prescripcion');

            $programada = Carbon::parse($dia.' '.$hora);
            $duplicada = AdministracionMedicacion::query()
                ->where('cod_prescripcion', $codPrescripcion)
                ->where('cod_horario_prescripcion', $horario->cod_horario_prescripcion)
                ->whereDate('fecha_hora_programada', today())
                ->lockForUpdate()
                ->exists();
            $this->exigir(! $duplicada, 'Esta dosis ya tiene una administración u omisión registrada.', 'cod_prescripcion');

            $dosisReal = $administrada ? ($dosisAdministrada ?? $horario->dosis_programada ?? $prescripcion->dosis) : null;
            $fechaReal = $administrada ? ($fechaHoraAdministracion ?? now()->format('Y-m-d H:i:s')) : null;
            Validator::make(['dosis_administrada' => $dosisReal, 'fecha_hora_administracion' => $fechaReal], [
                'dosis_administrada' => $administrada ? 'required|numeric|decimal:0,3|between:0.001,9999999.999' : 'nullable',
                'fecha_hora_administracion' => $administrada ? 'required|date|before_or_equal:now' : 'nullable',
            ])->validate();
            if ($administrada) {
                $this->exigir(Carbon::parse($fechaReal)->isSameDay(now()), 'La administración programada se registra con una hora real de hoy.', 'fecha_hora_administracion');
            }

            $registro = AdministracionMedicacion::query()->create([
                'cod_administracion' => app(CodigosEntidad::class)->siguiente('administraciones_medicacion'),
                'cod_prescripcion' => $prescripcion->cod_prescripcion,
                'cod_horario_prescripcion' => $horario->cod_horario_prescripcion,
                'cod_residente' => $codResidente,
                'cod_jornada' => $asignacionVigente->cod_jornada,
                'cod_personal' => $personalVigente->cod_personal,
                'fecha_hora_programada' => $programada,
                'fecha_hora_administracion' => $fechaReal,
                'resultado' => $administrada ? 'ADMINISTRADA' : 'OMITIDA',
                'dosis_administrada' => $dosisReal,
                'motivo_omision' => $administrada ? null : trim((string) $motivoOmision),
                'efecto_observado' => $administrada && filled($efectoObservado) ? trim((string) $efectoObservado) : null,
                'reaccion_adversa' => $administrada && filled($reaccionAdversa) ? trim((string) $reaccionAdversa) : null,
                'observacion' => filled($observacion) ? trim((string) $observacion) : null,
                'estado' => 'REGISTRADA',
            ]);
            activity('Administración Medicación')->causedBy($usuario)->performedOn($registro)
                ->event($administrada ? 'administrado' : 'omitido')
                ->withProperties(['cod_prescripcion' => $prescripcion->getKey(), 'cod_horario_prescripcion' => $horario->getKey(),
                    'cod_residente' => $codResidente, 'cod_jornada' => $asignacion->cod_jornada, 'resultado' => $registro->resultado])
                ->log('Se registró el resultado de una dosis programada.');

            return $registro;
        });
    }

    public function registrarPrn(
        User $usuario,
        string $codResidente,
        string $codPrescripcion,
        string $motivo,
        string $valoracionPrevia,
        int $intensidad,
        ?string $efectoObservado = null,
    ): AdministracionMedicacion {
        $turno = $this->turnos->autorizarMutacionPaciente(
            $codResidente,
            'administraciones_medicacion.crear',
            $usuario,
        );
        $this->exigir(mb_strlen(trim($motivo)) >= 5, 'El motivo clínico PRN debe tener al menos 5 caracteres.');
        $this->exigir($intensidad >= 0 && $intensidad <= 10, 'La intensidad previa debe estar entre 0 y 10.');
        $prescripcion = Prescripcion::query()->whereKey($codPrescripcion)
            ->where('cod_residente', $codResidente)->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->where('segun_necesidad', true)->first();
        $this->exigir($prescripcion !== null, 'La orden PRN no pertenece al residente o no está activa.');

        $personal = $usuario->personal;
        $this->exigir($personal !== null, 'El usuario autenticado no tiene un personal institucional asociado.', 'personal');
        $asignacion = AsignacionResidenteJornada::query()
            ->where('cod_residente', $codResidente)
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->whereHas('jornada', fn ($query) => $query->whereDate('fecha_jornada', today())
                ->where('cod_turno', $turno->cod_turno)->whereIn('estado', ['ABIERTA', 'ACTIVA']))
            ->firstOrFail();

        return AdministracionMedicacion::query()->create([
            'cod_administracion' => app(CodigosEntidad::class)->siguiente('administraciones_medicacion'),
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_residente' => $codResidente,
            'cod_jornada' => $asignacion->cod_jornada,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora_programada' => now(),
            'fecha_hora_administracion' => now(),
            'resultado' => 'ADMINISTRADA',
            'dosis_administrada' => $prescripcion->dosis,
            'efecto_observado' => $efectoObservado,
            'observacion' => trim("PRN: {$motivo}. Valoración previa: {$valoracionPrevia}. Intensidad: {$intensidad}/10."),
            'estado' => 'REGISTRADA',
        ]);
    }

    private function normalizarHora(string $hora): string
    {
        try {
            return Carbon::createFromFormat('H:i', substr($hora, 0, 5))->format('H:i');
        } catch (\Throwable) {
            throw ValidationException::withMessages(['hora_programada' => 'La hora programada no es válida.']);
        }
    }

    private function exigir(bool $condicion, string $mensaje, string $campo = 'medicacion'): void
    {
        if (! $condicion) {
            throw ValidationException::withMessages([$campo => $mensaje]);
        }
    }
}
