<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Models\Personal;
use App\Models\AsignacionResidenteJornada;
use App\Models\SignoVital;
use App\Models\User;
use App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SignosVitalesService
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    /** Captura estricta del formulario Nuevo registro de Enfermería (BDD V2.1). */
    public function registrarDesdeNuevoRegistro(string $codResidente, array $entrada, User $usuario): SignoVital
    {
        abort_unless(Auth::user()?->cod_usuario === $usuario->cod_usuario, 403, 'La autoría del registro no corresponde al usuario autenticado.');
        $turno = $this->turnos->autorizarMutacionEnfermeria($codResidente, 'signos_vitales.crear', $usuario);
        $codPersonal = $this->resolverPersonalActivo($usuario);
        $entrada = array_map(fn ($valor) => $valor === '' ? null : $valor, $entrada);
        $entrada['observacion'] = isset($entrada['observacion']) ? trim($entrada['observacion']) : null;

        $reglas = [
            'presion_sistolica' => ['nullable', 'integer', 'min:0', 'max:999'],
            'presion_diastolica' => ['nullable', 'integer', 'min:0', 'max:999'],
            'frecuencia_cardiaca' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'frecuencia_respiratoria' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'temperatura' => ['nullable', 'numeric', 'decimal:0,1', 'between:-99.9,999.9'],
            'saturacion_oxigeno' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'glucemia' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,999999.99'],
            'observacion' => ['nullable', 'string', 'max:5000'],
        ];
        $mensajes = [
            'required' => 'Ingresa :attribute.',
            'date_format' => 'Ingresa una fecha u hora válida.',
            'integer' => 'Ingresa un número entero válido.',
            'numeric' => 'Ingresa un valor numérico válido.',
            'decimal' => 'El valor excede los decimales permitidos por la base de datos.',
            'min' => 'El valor no puede ser negativo.',
            'max' => 'El valor supera la capacidad permitida.',
            'between' => 'El valor está fuera de la capacidad permitida.',
            'saturacion_oxigeno.min' => 'La saturación no puede ser menor a 0 %.',
            'saturacion_oxigeno.max' => 'La saturación no puede superar el 100 %.',
            'glucemia.min' => 'La glucemia no puede ser negativa.',
            'frecuencia_cardiaca.min' => 'El pulso no puede ser negativo.',
            'frecuencia_respiratoria.min' => 'La frecuencia respiratoria no puede ser negativa.',
            'observacion.max' => 'Las observaciones no pueden superar 5000 caracteres.',
        ];
        $atributos = [
            'presion_sistolica' => 'la presión sistólica', 'presion_diastolica' => 'la presión diastólica',
        ];
        $validator = Validator::make($entrada, $reglas, $mensajes, $atributos);
        $validator->after(function ($validator) use ($entrada): void {
            $sis = $entrada['presion_sistolica'] ?? null;
            $dia = $entrada['presion_diastolica'] ?? null;
            if ($sis !== null && $dia === null) {
                $validator->errors()->add('presion_diastolica', 'Completa también la presión diastólica.');
            } elseif ($dia !== null && $sis === null) {
                $validator->errors()->add('presion_sistolica', 'Completa también la presión sistólica.');
            }
            if (! collect(['presion_sistolica', 'presion_diastolica', 'frecuencia_cardiaca', 'frecuencia_respiratoria', 'temperatura', 'saturacion_oxigeno', 'glucemia'])
                ->contains(fn (string $campo) => ($entrada[$campo] ?? null) !== null)) {
                $validator->errors()->add('mediciones', 'Ingresa al menos una medición.');
            }
        });
        $datos = $validator->validate();

        $codJornada = AsignacionResidenteJornada::query()
            ->where('cod_residente', $codResidente)
            ->where('cod_personal', $codPersonal)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'ASIGNADO'])
            ->whereHas('jornada', fn ($q) => $q->where('cod_turno', $turno->cod_turno)
                ->whereDate('fecha_jornada', now()->toDateString())
                ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO']))
            ->value('cod_jornada');
        abort_unless($codJornada, 403, 'No existe una jornada activa para este residente.');

        return SignoVital::create([
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
            'observacion' => $datos['observacion'] ?: null,
            'estado' => 'ACTIVO',
        ]);
    }

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
