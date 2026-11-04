<?php

namespace App\Modulos\Cuidados\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Dominio\ObservacionCognitiva as Contrato;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class RegistrarObservacionCognitiva
{
    /** El código de captura procede exclusivamente del componente servidor, nunca del payload HTTP. */
    public function ejecutar(User $usuario, Residente $residente, array $entrada, ?string $captura = null): ControlCognitivo
    {
        $personal = app(AutorizacionClinicaService::class)->autorizarMutacion($usuario, $residente, 'controles_cognitivos.crear', ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA']);
        $reglas = ['observacion' => 'nullable|string', 'cod_atencion' => 'nullable|string|max:20|exists:atenciones,cod_atencion'];
        foreach (Contrato::TEXTOS as $campo) {
            $reglas[$campo] = ['nullable', 'string', 'max:30', Rule::in(array_keys(Contrato::opciones($campo)))];
        }
        foreach (Contrato::SENALES as $campo) {
            $reglas[$campo] = 'nullable|boolean';
        }
        foreach (array_keys($reglas) as $campo) {
            if (is_string($entrada[$campo] ?? null)) {
                $entrada[$campo] = trim($entrada[$campo]);
                if ($entrada[$campo] === '') {
                    $entrada[$campo] = null;
                }
            }
        }
        $datos = Validator::make($entrada, $reglas)->validate();
        foreach (Contrato::SENALES as $campo) {
            $datos[$campo] = isset($datos[$campo]) ? (bool) $datos[$campo] : null;
        }
        if (! collect($datos)->except('cod_atencion')->contains(fn ($valor) => $valor !== null && $valor !== '')) {
            throw ValidationException::withMessages(['observacion' => 'Registra al menos un indicador observado o una descripción significativa.']);
        }
        $jornada = app(MiTurnoService::class)->resolverJornadaActual($personal, now());
        if ($usuario->hasRole('ENFERMEROS') && ! $jornada) {
            throw ValidationException::withMessages(['jornada' => 'Necesitas una jornada activa y este residente asignado para registrar.']);
        }
        if (filled($datos['cod_atencion'] ?? null) && ! Atencion::where('cod_residente', $residente->getKey())->whereKey($datos['cod_atencion'])->exists()) {
            throw ValidationException::withMessages(['cod_atencion' => 'La atención debe pertenecer al residente actual.']);
        }
        $codigo = $captura ?? app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('controles_cognitivos');
        abort_unless((bool) preg_match('/^COD_CCO_[0-9]{6,12}$/', $codigo), 422);
        $existente = fn () => ControlCognitivo::whereKey($codigo)->where('cod_residente', $residente->getKey())->where('cod_personal', $personal->getKey())->first();
        if ($registro = $existente()) {
            return $registro;
        }
        try {
            return DB::transaction(function () use ($usuario, $residente, $personal, $jornada, $datos, $codigo) {
                $registro = ControlCognitivo::create([...$datos, 'cod_control_cognitivo' => $codigo,
                    'cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(),
                    'cod_jornada' => $jornada?->getKey(), 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
                activity('observacion-cognitiva')->causedBy($usuario)->performedOn($registro)->log('Observación cognitiva confirmada');

                return $registro;
            });
        } catch (QueryException $error) {
            // La PK única protege dos peticiones concurrentes de la misma captura.
            if ($error->getCode() === '23505' || $error->getCode() === '23000') {
                if ($registro = $existente()) {
                    return $registro;
                }
            }
            throw $error;
        }
    }
}
