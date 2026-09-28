<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Models\Personal;
use App\Models\SignoVital;
use App\Models\User;
use App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SignosVitalesService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function registrar(string $codResidente, array $entrada, User $usuario, string $permiso = 'signos_vitales.crear'): SignoVital
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, $permiso, $usuario);
        $datos = $this->validarYNormalizar($entrada);

        // usuarios.cod_usuario no es una FK clínica: primero se resuelve el
        // registro personal activo y luego se persiste personal.cod_personal.
        $codPersonal = $this->resolverPersonalActivo($usuario);

        $fechaHora = Carbon::parse($datos['fecha'] . ' ' . $datos['hora']);

        return SignoVital::create([
            'cod_signo'               => 'SGN_' . strtoupper(Str::random(10)),
            'cod_residente'           => $codResidente,
            'cod_personal'            => $codPersonal,
            'fecha_hora'              => $fechaHora,
            'presion_sistolica'       => $datos['presion_sistolica'],
            'presion_diastolica'      => $datos['presion_diastolica'],
            'frecuencia_cardiaca'     => $datos['frecuencia_cardiaca'],
            'frecuencia_respiratoria' => $datos['frecuencia_respiratoria'],
            'temperatura'             => $datos['temperatura'],
            'saturacion_oxigeno'      => $datos['saturacion'],
            'glucemia'                => $datos['glucosa'],
            'estado'                  => 'ACTIVO',
            'observacion'             => $datos['observacion'] ?? null,
        ]);
    }

    public function rectificar(SignoVital $original, array $entrada, string $motivo, User $usuario, string $permiso = 'signos_vitales.crear'): SignoVital
    {
        $this->turnos->autorizarMutacionEnfermeria($original->cod_residente, $permiso, $usuario);
        Validator::make(['motivo' => $motivo], ['motivo' => 'required|string|min:10|max:2000'], [
            'motivo.required' => 'Debe indicar el motivo de la rectificación.',
            'motivo.min' => 'El motivo de rectificación debe tener al menos 10 caracteres.',
        ])->validate();
        $datos = $this->validarYNormalizar($entrada);

        return DB::transaction(function () use ($original, $datos, $motivo, $usuario) {
            $bloqueado = SignoVital::lockForUpdate()->findOrFail($original->getKey());
            abort_unless(in_array($bloqueado->estado, ['VIGENTE', 'ACTIVO']), 409, 'El registro ya no admite rectificación.');
            $bloqueado->update(['estado' => 'RECTIFICADO']);

            // La rectificación conserva la autoría clínica del usuario autenticado.
            $codPersonal = $this->resolverPersonalActivo($usuario);

            $fechaHora = Carbon::parse($datos['fecha'] . ' ' . $datos['hora']);

            return SignoVital::create([
                'cod_signo'               => 'SGN_' . strtoupper(Str::random(10)),
                'cod_residente'           => $bloqueado->cod_residente,
                'cod_personal'            => $codPersonal,
                'fecha_hora'              => $fechaHora,
                'presion_sistolica'       => $datos['presion_sistolica'],
                'presion_diastolica'      => $datos['presion_diastolica'],
                'frecuencia_cardiaca'     => $datos['frecuencia_cardiaca'],
                'frecuencia_respiratoria' => $datos['frecuencia_respiratoria'],
                'temperatura'             => $datos['temperatura'],
                'saturacion_oxigeno'      => $datos['saturacion'],
                'glucemia'                => $datos['glucosa'],
                'estado'                  => 'ACTIVO',
                'observacion'             => trim(($datos['observacion'] ?? '') . "\n[Rectificación: {$motivo}]"),
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

    private function resolverPersonalActivo(User $usuario): string
    {
        $codPersonal = Personal::query()
            ->where('cod_usuario', $usuario->cod_usuario)
            ->where('estado', 'ACTIVO')
            ->value('cod_personal');

        if (! $codPersonal) {
            throw ValidationException::withMessages([
                'personal' => 'El usuario autenticado no tiene un registro de personal activo.',
            ]);
        }

        return $codPersonal;
    }
}
