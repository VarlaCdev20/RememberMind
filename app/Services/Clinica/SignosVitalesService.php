<?php

namespace App\Services\Clinica;

use App\Models\SignosVitalesAdulto;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SignosVitalesService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function registrar(string $codAm, array $entrada, User $usuario, string $permiso = 'signos_vitales.crear'): SignosVitalesAdulto
    {
        $this->turnos->autorizarMutacionEnfermeria($codAm, $permiso, $usuario);
        $datos = $this->validarYNormalizar($entrada);

        if ($usuario->hasRole('ENFERMEROS')) {
            $datos['fecha'] = today()->toDateString();
            $datos['hora'] = now()->format('H:i:s');
        }

        return SignosVitalesAdulto::create($datos + [
            'cod_am' => $codAm,
            'registrado_por' => $usuario->cod_usu,
            'estado' => 'VIGENTE',
        ]);
    }

    public function rectificar(SignosVitalesAdulto $original, array $entrada, string $motivo, User $usuario, string $permiso = 'signos_vitales.crear'): SignosVitalesAdulto
    {
        $this->turnos->autorizarMutacionEnfermeria($original->cod_am, $permiso, $usuario);
        Validator::make(['motivo' => $motivo], ['motivo' => 'required|string|min:10|max:2000'], [
            'motivo.required' => 'Debe indicar el motivo de la rectificación.',
            'motivo.min' => 'El motivo de rectificación debe tener al menos 10 caracteres.',
        ])->validate();
        $datos = $this->validarYNormalizar($entrada);

        return DB::transaction(function () use ($original, $datos, $motivo, $usuario) {
            $bloqueado = SignosVitalesAdulto::lockForUpdate()->findOrFail($original->getKey());
            abort_unless($bloqueado->estado === 'VIGENTE' && $bloqueado->rectifica_a === null, 409, 'El registro ya no admite rectificación.');
            $bloqueado->update(['estado' => 'RECTIFICADO']);
            return SignosVitalesAdulto::create($datos + [
                'cod_am' => $bloqueado->cod_am,
                'registrado_por' => $usuario->cod_usu,
                'estado' => 'VIGENTE',
                'rectifica_a' => $bloqueado->getKey(),
                'motivo_rectificacion' => trim($motivo),
            ]);
        });
    }

    public function validarYNormalizar(array $entrada): array
    {
        if (array_key_exists('confirmar_presion_atipica', $entrada)) {
            $entrada['valor_atipico_confirmado'] = $entrada['confirmar_presion_atipica'];
        }
        $entrada = $this->normalizarVacios($entrada);
        [$sis, $dia] = $this->resolverPresion($entrada);
        $entrada['presion_sistolica'] = $sis;
        $entrada['presion_diastolica'] = $dia;

        $validator = Validator::make($entrada, ValidacionSignosVitalesService::reglas() + [
            'fecha' => 'nullable|date|before_or_equal:today',
            'hora' => 'nullable|date_format:H:i,H:i:s',
            'posicion' => 'nullable|in:SENTADO,ACOSTADO,DE_PIE',
            'usa_oxigeno' => 'nullable|boolean',
            'valor_atipico_confirmado' => 'nullable|boolean',
        ], ValidacionSignosVitalesService::mensajes() + [
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'posicion.in' => 'Seleccione una posición válida para la medición.',
        ]);
        $validator->after(function ($validator) use ($entrada, $sis, $dia) {
            ValidacionSignosVitalesService::validarIntegridadCruzada(
                $validator,
                $sis,
                $dia,
                collect(['presion_sistolica','frecuencia_cardiaca','frecuencia_respiratoria','temperatura','saturacion','glucosa','peso','dolor'])
                    ->map(fn ($campo) => $entrada[$campo] ?? null)->all(),
                'presion_arterial',
                'general',
                (bool) ($entrada['valor_atipico_confirmado'] ?? false),
            );
        });
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $peso = isset($entrada['peso']) ? (float) $entrada['peso'] : null;
        $talla = isset($entrada['talla']) ? (float) $entrada['talla'] : null;
        return [
            'fecha' => $entrada['fecha'] ?? today()->toDateString(),
            'hora' => $entrada['hora'] ?? now()->format('H:i:s'),
            'presion_arterial' => $sis !== null ? "{$sis}/{$dia}" : null,
            'presion_sistolica' => $sis,
            'presion_diastolica' => $dia,
            'frecuencia_cardiaca' => $entrada['frecuencia_cardiaca'] ?? null,
            'frecuencia_respiratoria' => $entrada['frecuencia_respiratoria'] ?? null,
            'temperatura' => $entrada['temperatura'] ?? null,
            'saturacion' => $entrada['saturacion'] ?? null,
            'glucosa' => $entrada['glucosa'] ?? null,
            'peso' => $peso,
            'talla' => ValidacionSignosVitalesService::normalizarTalla($talla),
            'imc' => ValidacionSignosVitalesService::calcularImc($peso, $talla),
            'dolor' => $entrada['dolor'] ?? null,
            'posicion' => $entrada['posicion'] ?? null,
            'usa_oxigeno' => (bool) ($entrada['usa_oxigeno'] ?? false),
            'valor_atipico_confirmado' => (bool) ($entrada['valor_atipico_confirmado'] ?? false),
            'observacion' => $entrada['observacion'] ?? null,
        ];
    }

    private function resolverPresion(array $entrada): array
    {
        $sis = isset($entrada['presion_sistolica']) ? (int) $entrada['presion_sistolica'] : null;
        $dia = isset($entrada['presion_diastolica']) ? (int) $entrada['presion_diastolica'] : null;
        if (($entrada['presion_arterial'] ?? null) !== null) {
            if (! preg_match('/^\s*(\d{1,3})\s*\/\s*(\d{1,3})\s*$/', (string) $entrada['presion_arterial'], $partes)) {
                throw ValidationException::withMessages(['presion_arterial' => 'La presión arterial debe tener el formato sistólica/diastólica, por ejemplo 120/80.']);
            }
            [$sis, $dia] = [(int) $partes[1], (int) $partes[2]];
        }
        return [$sis, $dia];
    }

    private function normalizarVacios(array $datos): array
    {
        return array_map(fn ($valor) => $valor === '' ? null : $valor, $datos);
    }
}
