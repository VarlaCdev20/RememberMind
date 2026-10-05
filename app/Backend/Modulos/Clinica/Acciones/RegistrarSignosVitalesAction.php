<?php

namespace App\Backend\Modulos\Clinica\Acciones;

use App\Backend\Modulos\Clinica\SignosVitales\EvaluadorSignosVitales;
use App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService;
use App\Backend\Modulos\Clinica\SignosVitales\Resultados\RegistroSignosVitales;
use App\Backend\Modulos\Clinica\SignosVitales\ServicioDecisionAlertaClinica;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Models\AsignacionResidenteJornada;
use App\Models\Personal;
use App\Models\SignoVital;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

final class RegistrarSignosVitalesAction
{
    public function __construct(
        private readonly EvaluadorSignosVitales $evaluador,
        private readonly ServicioDecisionAlertaClinica $alertas,
        private readonly TurnoEnfermeriaService $turnos,
    ) {}

    /** @param array<string, mixed> $datos Datos normalizados y validados en servidor. */
    public function ejecutar(string $codResidente, string $codPersonal, string $codJornada, array $datos, User $autor): RegistroSignosVitales
    {
        return DB::transaction(function () use ($codResidente, $codPersonal, $codJornada, $datos, $autor) {
            abort_unless(Auth::user()?->cod_usuario === $autor->cod_usuario, 403);
            $turno = $this->turnos->autorizarMutacionEnfermeria($codResidente, 'signos_vitales.crear', $autor);
            abort_unless(Personal::query()->where('cod_personal', $codPersonal)
                ->where('cod_usuario', $autor->cod_usuario)->where('estado', 'ACTIVO')->exists(), 403);
            abort_unless(AsignacionResidenteJornada::query()->where('cod_jornada', $codJornada)
                ->where('cod_residente', $codResidente)->where('cod_personal', $codPersonal)
                ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'ASIGNADO'])
                ->whereHas('jornada', fn ($q) => $q->where('cod_turno', $turno->cod_turno)
                    ->whereDate('fecha_jornada', now()->toDateString())
                    ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO']))->exists(), 403);
            $mediciones = ['presion_sistolica', 'presion_diastolica', 'frecuencia_cardiaca',
                'frecuencia_respiratoria', 'temperatura', 'saturacion_oxigeno', 'glucemia'];
            $validator = Validator::make($datos, [
                'presion_sistolica' => ['nullable', 'integer', 'min:1', 'max:'.ValidacionSignosVitalesService::PAS_MAX],
                'presion_diastolica' => ['nullable', 'integer', 'min:1', 'max:'.ValidacionSignosVitalesService::PAD_MAX],
                'frecuencia_cardiaca' => ['nullable', 'integer', 'min:1', 'max:'.ValidacionSignosVitalesService::FC_MAX],
                'frecuencia_respiratoria' => ['nullable', 'integer', 'min:1', 'max:'.ValidacionSignosVitalesService::FR_MAX],
                'temperatura' => ['nullable', 'numeric', 'decimal:0,1', 'between:'.ValidacionSignosVitalesService::TEMP_MIN.','.ValidacionSignosVitalesService::TEMP_MAX],
                'saturacion_oxigeno' => ['nullable', 'numeric', 'decimal:0,2', 'between:1,100'],
                'glucemia' => ['nullable', 'numeric', 'decimal:0,2', 'between:1,999999.99'],
                'observacion' => ['nullable', 'string', 'max:5000'],
            ]);
            $validator->after(function ($validator) use ($datos, $mediciones): void {
                if (! collect($mediciones)->contains(fn ($campo) => ($datos[$campo] ?? null) !== null)) {
                    $validator->errors()->add('mediciones', 'Ingresa al menos una medición.');
                }
                if (($datos['presion_sistolica'] ?? null) === null xor ($datos['presion_diastolica'] ?? null) === null) {
                    $validator->errors()->add('presion_arterial', 'Completa ambos valores de presión arterial.');
                }
            });
            $validator->validate();
            $evaluacion = $this->evaluador->evaluar($datos, $codResidente);
            $signo = SignoVital::create([
                'cod_residente' => $codResidente,
                'cod_personal' => $codPersonal,
                'cod_jornada' => $codJornada,
                'fecha_hora' => now(),
                'presion_sistolica' => $datos['presion_sistolica'] ?? null,
                'presion_diastolica' => $datos['presion_diastolica'] ?? null,
                'frecuencia_cardiaca' => $datos['frecuencia_cardiaca'] ?? null,
                'frecuencia_respiratoria' => $datos['frecuencia_respiratoria'] ?? null,
                'temperatura' => $datos['temperatura'] ?? null,
                'saturacion_oxigeno' => $datos['saturacion_oxigeno'] ?? null,
                'glucemia' => $datos['glucemia'] ?? null,
                'observacion' => $datos['observacion'] ?? null,
                'estado' => 'ACTIVO',
            ]);
            $alerta = $this->alertas->crearSiCorresponde($signo, $evaluacion, $autor);

            return new RegistroSignosVitales($signo, $evaluacion, $alerta);
        });
    }
}
