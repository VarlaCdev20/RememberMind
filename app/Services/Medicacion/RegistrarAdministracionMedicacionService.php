<?php

namespace App\Services\Medicacion;

use App\Models\AdministracionMedicacion;
use App\Models\MedicacionAdulto;
use App\Models\TareaPlanCuidado;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarAdministracionMedicacionService
{
    public function __construct(
        private readonly TurnoEnfermeriaService $turnos,
        private readonly AgendaMedicacionService $agenda,
    ) {}

    public function registrarProgramada(
        User $usuario,
        string $codAm,
        string $codMedicacion,
        string $horaOcurrencia,
        bool $administrada,
        ?string $motivoOmision = null,
        ?string $observacion = null,
    ): AdministracionMedicacion {
        $turno = $this->turnos->autorizarMutacionPaciente($codAm, 'administracion_medicacion.registrar', $usuario);
        $hora = $this->normalizarHora($horaOcurrencia);
        $ocurrencia = $this->agenda->paraAdulto($codAm)
            ->first(fn (array $item) => $item['medicacion']->cod_med_adulto === $codMedicacion && $item['hora'] === $hora);

        $this->exigir($ocurrencia !== null, 'La dosis indicada no corresponde a una ocurrencia programada real de hoy.');
        $this->exigir($ocurrencia['registro'] === null, 'Esta dosis ya tiene una administración u omisión registrada.', 'cod_med_adulto');
        if (! $administrada) {
            $this->exigir(mb_strlen(trim((string) $motivoOmision)) >= 5, 'Debe registrar un motivo de omisión de al menos 5 caracteres.');
        }

        return DB::transaction(function () use ($usuario, $codAm, $codMedicacion, $hora, $administrada, $motivoOmision, $observacion, $turno) {
            $medicacion = $this->ordenActivaBloqueada($codAm, $codMedicacion);
            $this->exigir(! $medicacion->es_prn, 'Una orden PRN debe registrarse mediante el flujo PRN.');

            $duplicada = AdministracionMedicacion::query()
                ->where('cod_med_adulto', $codMedicacion)
                ->whereDate('fecha', today())
                ->whereTime('hora_programada', $hora)
                ->lockForUpdate()
                ->exists();
            $this->exigir(! $duplicada, 'Esta dosis ya tiene una administración u omisión registrada.', 'cod_med_adulto');

            return AdministracionMedicacion::create([
                'cod_med_adulto' => $codMedicacion,
                'cod_am' => $codAm,
                'fecha' => today()->toDateString(),
                'hora_programada' => $hora,
                'hora_real' => $administrada ? now()->format('H:i:s') : null,
                'administrado' => $administrada,
                'resultado' => $administrada ? 'ADMINISTRADO' : 'OMITIDO',
                'motivo_omision' => $administrada ? null : trim((string) $motivoOmision),
                'observacion' => filled($observacion) ? trim((string) $observacion) : null,
                'registrado_por' => $usuario->cod_usu,
            ]);
        });
    }

    public function registrarPrn(
        User $usuario,
        string $codAm,
        string $codMedicacion,
        string $motivo,
        string $valoracionPrevia,
        int $intensidad,
        ?string $efectoObservado = null,
    ): AdministracionMedicacion {
        $turno = $this->turnos->autorizarMutacionPaciente($codAm, 'administracion_medicacion.registrar', $usuario);
        $this->exigir(mb_strlen(trim($motivo)) >= 5, 'El motivo clínico PRN debe tener al menos 5 caracteres.');
        $this->exigir(mb_strlen(trim($valoracionPrevia)) >= 5, 'La valoración previa PRN debe tener al menos 5 caracteres.');
        $this->exigir($intensidad >= 0 && $intensidad <= 10, 'La intensidad previa debe estar entre 0 y 10.');

        return DB::transaction(function () use ($usuario, $codAm, $codMedicacion, $motivo, $valoracionPrevia, $intensidad, $efectoObservado, $turno) {
            $medicacion = $this->ordenActivaBloqueada($codAm, $codMedicacion);
            $this->exigir((bool) $medicacion->es_prn, 'La orden seleccionada no es PRN.');
            $this->exigir(filled($medicacion->condicion_prn), 'La orden PRN no define la condición clínica de uso.');
            $this->exigir((int) $medicacion->intervalo_horas >= 1, 'La orden PRN no define un intervalo mínimo válido.');

            $ultima = AdministracionMedicacion::query()
                ->where('cod_med_adulto', $codMedicacion)
                ->where('administrado', true)
                ->orderByDesc('fecha')
                ->orderByDesc('hora_real')
                ->lockForUpdate()
                ->first();
            if ($ultima) {
                $momentoAnterior = Carbon::parse($ultima->fecha->toDateString().' '.($ultima->hora_real?->format('H:i:s') ?? '00:00:00'));
                $this->exigir($momentoAnterior->addHours((int) $medicacion->intervalo_horas)->lte(now()), 'Aún no se cumple el intervalo mínimo de la última dosis PRN.');
            }

            $reevaluacion = now()->addMinutes((int) config('enfermeria.minutos_reevaluacion_prn', 60));
            $registro = AdministracionMedicacion::create([
                'cod_med_adulto' => $codMedicacion,
                'cod_am' => $codAm,
                'fecha' => today()->toDateString(),
                'hora_programada' => now()->format('H:i:s'),
                'hora_real' => now()->format('H:i:s'),
                'administrado' => true,
                'resultado' => 'ADMINISTRADO',
                'motivo_prn' => trim($motivo),
                'valoracion_previa' => trim($valoracionPrevia),
                'intensidad_previa' => $intensidad,
                'requiere_reevaluacion' => true,
                'fecha_hora_reevaluacion' => $reevaluacion,
                'efecto_observado' => filled($efectoObservado) ? trim((string) $efectoObservado) : null,
                'registrado_por' => $usuario->cod_usu,
            ]);

            $plan = $medicacion->adultoMayor?->planCuidadoActivo;
            if ($plan) {
                TareaPlanCuidado::create([
                    'cod_plan' => $plan->cod_plan,
                    'cod_am' => $codAm,
                    'cod_turno' => $turno->cod_turno,
                    'responsable_id' => $usuario->cod_usu,
                    'area' => 'REEVALUACION',
                    'titulo' => 'Reevaluar respuesta a '.$medicacion->nombre_medicamento,
                    'descripcion' => 'Registrar respuesta clínica y posibles efectos posteriores a la dosis PRN.',
                    'frecuencia' => 'UNICA',
                    'fecha_programada' => $reevaluacion->toDateString(),
                    'hora_programada' => $reevaluacion->format('H:i:s'),
                    'prioridad' => 'ALTA',
                    'estado' => 'PENDIENTE',
                    'registrado_por' => $usuario->cod_usu,
                ]);
            }

            return $registro;
        });
    }

    private function ordenActivaBloqueada(string $codAm, string $codMedicacion): MedicacionAdulto
    {
        $medicacion = MedicacionAdulto::query()
            ->with('adultoMayor.planCuidadoActivo')
            ->where('cod_am', $codAm)
            ->whereKey($codMedicacion)
            ->lockForUpdate()
            ->first();
        $this->exigir($medicacion !== null, 'La orden no pertenece al residente indicado.');
        $this->exigir(in_array($medicacion->estado, ['ACTIVO', 'ACTIVA', 'VIGENTE'], true), 'La orden médica no está activa.');
        $this->exigir($medicacion->fecha_inicio?->lte(today()) === true && (! $medicacion->fecha_fin || $medicacion->fecha_fin->gte(today())), 'La orden médica no está vigente hoy.');
        $this->exigir(filled($medicacion->dosis), 'La orden médica no contiene una dosis válida.');
        $this->exigir(filled($medicacion->via_administracion), 'La orden médica no contiene una vía de administración válida.');
        return $medicacion;
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
