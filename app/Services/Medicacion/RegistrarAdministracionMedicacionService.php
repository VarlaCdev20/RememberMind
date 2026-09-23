<?php

namespace App\Services\Medicacion;

use App\Models\AdministracionMedicacion;
use App\Models\AsignacionResidenteJornada;
use App\Models\Prescripcion;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
    ): AdministracionMedicacion {
        $turno = $this->turnos->autorizarMutacionPaciente(
            $codResidente,
            'administraciones_medicacion.crear',
            $usuario,
        );
        $hora = $this->normalizarHora($horaOcurrencia);
        $ocurrencia = $this->agenda->paraAdulto($codResidente)
            ->first(fn (array $item) => $item['medicacion']->cod_prescripcion === $codPrescripcion
                && $item['hora'] === $hora);

        $this->exigir($ocurrencia !== null, 'La dosis no corresponde a un horario activo de hoy.');
        $this->exigir($ocurrencia['registro'] === null, 'Esta dosis ya tiene una administración u omisión registrada.', 'cod_med_adulto');
        if (! $administrada) {
            $this->exigir(mb_strlen(trim((string) $motivoOmision)) >= 5, 'Debe registrar un motivo de omisión de al menos 5 caracteres.');
        }

        $personal = $usuario->personal;
        $asignacion = AsignacionResidenteJornada::query()
            ->with('jornada')
            ->where('cod_residente', $codResidente)
            ->where('cod_personal', $personal->cod_personal)
            ->where('estado', 'ACTIVA')
            ->whereHas('jornada', fn ($query) => $query
                ->whereDate('fecha_jornada', today())
                ->where('cod_turno', $turno->cod_turno)
                ->where('estado', 'ABIERTA'))
            ->firstOrFail();

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
            $personal,
            $asignacion,
            $ocurrencia,
        ): AdministracionMedicacion {
            $prescripcion = Prescripcion::query()
                ->whereKey($codPrescripcion)
                ->where('cod_residente', $codResidente)
                ->where('estado', 'ACTIVA')
                ->lockForUpdate()
                ->first();
            $this->exigir($prescripcion !== null, 'La prescripción no pertenece al residente o ya no está activa.');

            $programada = Carbon::parse(today()->toDateString().' '.$hora);
            $duplicada = AdministracionMedicacion::query()
                ->where('cod_prescripcion', $codPrescripcion)
                ->where('cod_horario_prescripcion', $ocurrencia['horario']->cod_horario_prescripcion)
                ->whereDate('fecha_hora_programada', today())
                ->lockForUpdate()
                ->exists();
            $this->exigir(! $duplicada, 'Esta dosis ya tiene una administración u omisión registrada.', 'cod_med_adulto');

            return AdministracionMedicacion::query()->create([
                'cod_administracion' => 'ADM_'.Str::upper(Str::random(12)),
                'cod_prescripcion' => $prescripcion->cod_prescripcion,
                'cod_horario_prescripcion' => $ocurrencia['horario']->cod_horario_prescripcion,
                'cod_residente' => $codResidente,
                'cod_jornada' => $asignacion->cod_jornada,
                'cod_personal' => $personal->cod_personal,
                'fecha_hora_programada' => $programada,
                'fecha_hora_administracion' => $administrada ? now() : null,
                'resultado' => $administrada ? 'ADMINISTRADA' : 'OMITIDA',
                'dosis_administrada' => $administrada
                    ? ($dosisAdministrada !== null ? $dosisAdministrada : ($ocurrencia['horario']->dosis_programada ?? $prescripcion->dosis))
                    : null,
                'motivo_omision' => $administrada ? null : trim((string) $motivoOmision),
                'efecto_observado' => filled($efectoObservado) ? trim((string) $efectoObservado) : null,
                'reaccion_adversa' => filled($reaccionAdversa) ? trim((string) $reaccionAdversa) : null,
                'observacion' => filled($observacion) ? trim((string) $observacion) : null,
                'estado' => 'REGISTRADA',
            ]);
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
            ->where('cod_residente', $codResidente)->where('estado', 'ACTIVA')
            ->where('segun_necesidad', true)->first();
        $this->exigir($prescripcion !== null, 'La orden PRN no pertenece al residente o no está activa.');

        $asignacion = AsignacionResidenteJornada::query()
            ->where('cod_residente', $codResidente)
            ->where('cod_personal', $usuario->personal->cod_personal)
            ->where('estado', 'ACTIVA')
            ->whereHas('jornada', fn ($query) => $query->whereDate('fecha_jornada', today())
                ->where('cod_turno', $turno->cod_turno)->where('estado', 'ABIERTA'))
            ->firstOrFail();

        return AdministracionMedicacion::query()->create([
            'cod_administracion' => 'ADM_'.Str::upper(Str::random(12)),
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_residente' => $codResidente,
            'cod_jornada' => $asignacion->cod_jornada,
            'cod_personal' => $usuario->personal->cod_personal,
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
