<?php

namespace App\Backend\Modulos\Enfermeria\Servicios;

use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\DispositivoClinico;
use App\Models\Personal;
use App\Models\RegistroEliminacion;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\RegistroMovilidad;
use App\Models\RegistroSueno;
use App\Models\User;
use App\Models\ValoracionDolor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CuidadosEnfermeriaService
{
    public const TIPOS_COMIDA = ['DESAYUNO', 'MEDIA_MANANA', 'ALMUERZO', 'MERIENDA', 'CENA', 'COLACION'];

    public const TOLERANCIAS_INGESTA = ['BUENA', 'REGULAR', 'MALA', 'NAUSEAS', 'VOMITO'];

    public const TIPOS_ELIMINACION_REGISTRO = ['URINARIA', 'INTESTINAL'];

    public const CONTINENCIAS_URINARIAS = ['CONTINENTE', 'INCONTINENCIA_URINARIA'];

    public const CONTINENCIAS_INTESTINALES = ['CONTINENTE', 'INCONTINENCIA_FECAL'];

    public const MOVILIDAD_OBSERVADA = ['INDEPENDIENTE', 'ASISTIDA', 'SILLA_RUEDAS', 'ENCAMADO'];

    public const TRASLADOS = ['INDEPENDIENTE', 'SUPERVISION', 'AYUDA_UNA_PERSONA', 'AYUDA_DOS_PERSONAS', 'GRUA'];

    public const NIVELES_AYUDA = ['INDEPENDIENTE', 'SUPERVISION', 'PARCIAL', 'COMPLETA', 'UNA_PERSONA', 'DOS_PERSONAS'];

    public const EQUILIBRIOS = ['ESTABLE', 'INESTABLE', 'NO_VALORABLE'];

    public const FATIGAS = ['SIN_FATIGA', 'LEVE', 'MODERADA', 'SEVERA'];

    public const RIESGOS_CAIDA = ['BAJO', 'MEDIO', 'ALTO'];

    public function __construct(private readonly TurnoEnfermeriaService $turnos, private readonly AlertasService $alertas) {}

    public function registrarAlimentacion(string $codResidente, array $datos, User $usuario): RegistroIngesta
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'registros_ingesta.crear', $usuario);

        $datos['observacion'] = is_string($datos['observacion'] ?? null) ? trim($datos['observacion']) : ($datos['observacion'] ?? null);
        if ($datos['observacion'] === '') {
            $datos['observacion'] = null;
        }
        $validados = Validator::make($datos, [
            'tipo_comida' => ['required', Rule::in(self::TIPOS_COMIDA)],
            'porcentaje_consumido' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,100'],
            'cantidad_ml' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,999999.99'],
            'tolerancia' => ['nullable', Rule::in(self::TOLERANCIAS_INGESTA)],
            'dificultad_deglucion' => ['required', 'boolean'],
            'observacion' => ['nullable', 'string', 'max:5000'],
        ], [
            'tipo_comida.required' => 'Selecciona el tipo de comida.',
            'tipo_comida.in' => 'Selecciona un tipo de comida válido.',
            'porcentaje_consumido.numeric' => 'Ingresa un porcentaje numérico válido.',
            'porcentaje_consumido.decimal' => 'El porcentaje admite hasta dos decimales.',
            'porcentaje_consumido.between' => 'El porcentaje debe estar entre 0 y 100.',
            'cantidad_ml.numeric' => 'Ingresa un volumen numérico válido.',
            'cantidad_ml.decimal' => 'El volumen admite hasta dos decimales.',
            'cantidad_ml.between' => 'El volumen debe ser mayor o igual a 0 y no superar 999999,99 mL.',
            'tolerancia.in' => 'Selecciona una tolerancia válida.',
            'dificultad_deglucion.required' => 'Indica si hubo dificultad para deglutir.',
            'dificultad_deglucion.boolean' => 'Indica si hubo dificultad para deglutir.',
            'observacion.string' => 'Ingresa observaciones válidas.',
            'observacion.max' => 'Las observaciones no pueden superar 5000 caracteres.',
        ])->validate();

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();
        if (! $personal || ! in_array($personal->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages(['usuario' => 'El usuario no posee un registro de personal activo.']);
        }
        $jornada = app(MiTurnoService::class)->resolverJornadaActual($personal, now());
        if (! $jornada) {
            throw ValidationException::withMessages(['jornada' => 'No existe una jornada activa asignada al personal.']);
        }

        if (isset($validados['cantidad_ml'])) {
            $this->turnos->autorizarMutacionEnfermeria($codResidente, 'registros_hidratacion.crear', $usuario);
        }

        return DB::transaction(function () use ($codResidente, $personal, $jornada, $validados, $usuario) {
            $fechaHora = now();
            $ingesta = RegistroIngesta::create([
                'cod_ingesta' => 'ING_'.strtoupper(Str::random(10)),
                'cod_residente' => $codResidente,
                'cod_personal' => $personal->cod_personal,
                'cod_jornada' => $jornada->cod_jornada,
                'fecha_hora' => $fechaHora,
                'tipo_comida' => $validados['tipo_comida'],
                'porcentaje_consumido' => $validados['porcentaje_consumido'] ?? null,
                'apetito' => null,
                'tolerancia' => $validados['tolerancia'] ?? null,
                'dificultad_deglucion' => $validados['dificultad_deglucion'],
                'observacion' => $validados['observacion'] ?? null,
                'estado' => 'VIGENTE',
            ]);

            if (isset($validados['cantidad_ml'])) {
                RegistroHidratacion::create([
                    'cod_hidratacion' => 'HID_'.strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_personal' => $personal->cod_personal,
                    'cod_jornada' => $jornada->cod_jornada,
                    'fecha_hora' => $fechaHora,
                    'cantidad_ml' => $validados['cantidad_ml'],
                    'tipo_liquido' => null,
                    'tolerancia' => null,
                    'observacion' => null,
                    'estado' => 'VIGENTE',
                ]);
            }

            if (isset($validados['porcentaje_consumido'])
                && $validados['porcentaje_consumido'] < config('enfermeria.porcentaje_baja_ingesta', 50)) {
                $this->alertas->crear($codResidente, [
                    'origen' => 'SEGUIMIENTO',
                    'tipo_alerta' => 'BAJA INGESTA',
                    'nivel' => 'MEDIO',
                    'motivo' => 'Alerta clínica: '.($validados['observacion'] ?? 'Ingesta inferior al umbral institucional.'),
                ], $usuario);
            }

            return $ingesta;
        });
    }

    public function registrarEliminacion(string $codResidente, array $datos, User $usuario): RegistroEliminacion
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'registros_eliminacion.crear', $usuario);

        foreach (['cantidad', 'caracteristica', 'continencia', 'observacion'] as $campo) {
            if (is_string($datos[$campo] ?? null)) {
                $datos[$campo] = trim($datos[$campo]);
                if ($datos[$campo] === '') {
                    $datos[$campo] = null;
                }
            }
        }
        $tipo = $datos['tipo_eliminacion'] ?? null;
        $continencias = $tipo === 'URINARIA' ? self::CONTINENCIAS_URINARIAS : self::CONTINENCIAS_INTESTINALES;
        $validados = Validator::make($datos, [
            'tipo_eliminacion' => ['required', Rule::in(self::TIPOS_ELIMINACION_REGISTRO)],
            'cantidad' => ['nullable', 'numeric', 'min:0', function (string $atributo, mixed $valor, \Closure $fail) {
                if (mb_strlen((string) $valor) > 40) {
                    $fail('La cantidad no puede superar 40 caracteres.');
                }
            }],
            'caracteristica' => ['nullable', 'string', 'max:120'],
            'continencia' => ['nullable', Rule::in($continencias)],
            'observacion' => ['nullable', 'string', 'max:5000'],
        ], [
            'tipo_eliminacion.required' => 'Selecciona el tipo de eliminación.',
            'tipo_eliminacion.in' => 'Selecciona un tipo de eliminación válido.',
            'cantidad.numeric' => 'Ingresa una cantidad numérica válida.',
            'cantidad.min' => 'La cantidad debe ser mayor o igual a 0.',
            'caracteristica.string' => 'Ingresa características válidas.',
            'caracteristica.max' => 'Las características no pueden superar 120 caracteres.',
            'continencia.in' => 'Selecciona una continencia válida para este tipo de eliminación.',
            'observacion.string' => 'Ingresa observaciones válidas.',
            'observacion.max' => 'Las observaciones no pueden superar 5000 caracteres.',
        ])->validate();

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();
        if (! $personal || ! in_array($personal->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages(['usuario' => 'El usuario no posee un registro de personal activo.']);
        }
        $jornada = app(MiTurnoService::class)->resolverJornadaActual($personal, now());
        if (! $jornada) {
            throw ValidationException::withMessages(['jornada' => 'No existe una jornada activa asignada al personal.']);
        }

        return RegistroEliminacion::create([
            'cod_eliminacion' => 'ELI_'.strtoupper(Str::random(10)),
            'cod_residente' => $codResidente,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'fecha_hora' => now(),
            'tipo_eliminacion' => $validados['tipo_eliminacion'],
            'cantidad' => $validados['cantidad'] ?? null,
            'caracteristica' => $validados['caracteristica'] ?? null,
            'continencia' => $validados['continencia'] ?? null,
            'observacion' => $validados['observacion'] ?? null,
            'estado' => 'VIGENTE',
        ]);
    }

    public function registrarMovilidad(string $codResidente, array $datos, User $usuario): RegistroMovilidad
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'registros_movilidad.crear', $usuario);

        $camposPermitidos = ['marcha', 'traslado', 'tipo_apoyo', 'equilibrio', 'fatiga', 'riesgo_caida', 'observacion'];
        if (array_diff(array_keys($datos), $camposPermitidos) !== []) {
            throw ValidationException::withMessages([
                'datos' => 'El formulario incluye campos que este registro de movilidad no puede guardar.',
            ]);
        }

        if (is_string($datos['observacion'] ?? null)) {
            $datos['observacion'] = trim($datos['observacion']);
            if ($datos['observacion'] === '') {
                $datos['observacion'] = null;
            }
        }
        $validados = Validator::make($datos, [
            'marcha' => ['required', Rule::in(self::MOVILIDAD_OBSERVADA)],
            'traslado' => ['nullable', Rule::in(self::TRASLADOS)],
            'tipo_apoyo' => ['nullable', Rule::in(self::NIVELES_AYUDA)],
            'equilibrio' => ['nullable', Rule::in(self::EQUILIBRIOS)],
            'fatiga' => ['nullable', Rule::in(self::FATIGAS)],
            'riesgo_caida' => ['nullable', Rule::in(self::RIESGOS_CAIDA)],
            'observacion' => ['nullable', 'string', 'max:5000'],
        ], [
            'marcha.required' => 'Selecciona la movilidad observada.',
            'marcha.in' => 'Selecciona una movilidad válida.',
            'traslado.in' => 'Selecciona un tipo de traslado válido.',
            'tipo_apoyo.in' => 'Selecciona un nivel de ayuda válido.',
            'equilibrio.in' => 'Selecciona un equilibrio válido.',
            'fatiga.in' => 'Selecciona una fatiga válida.',
            'riesgo_caida.in' => 'Selecciona un riesgo de caída válido.',
            'observacion.string' => 'Ingresa observaciones válidas.',
            'observacion.max' => 'Las observaciones no pueden superar 5000 caracteres.',
        ])->validate();

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();
        if (! $personal || ! in_array($personal->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages(['usuario' => 'El usuario no posee un registro de personal activo.']);
        }
        $jornada = app(MiTurnoService::class)->resolverJornadaActual($personal, now());
        if (! $jornada) {
            throw ValidationException::withMessages(['jornada' => 'No existe una jornada activa asignada al personal.']);
        }

        return RegistroMovilidad::create([
            'cod_movilidad' => 'MOV_'.strtoupper(Str::random(10)),
            'cod_residente' => $codResidente,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_atencion' => null,
            'fecha_hora' => now(),
            'marcha' => $validados['marcha'],
            'traslado' => $validados['traslado'] ?? null,
            'tipo_apoyo' => $validados['tipo_apoyo'] ?? null,
            'equilibrio' => $validados['equilibrio'] ?? null,
            'dispositivo' => null,
            'fatiga' => $validados['fatiga'] ?? null,
            'riesgo_caida' => $validados['riesgo_caida'] ?? null,
            'observacion' => $validados['observacion'] ?? null,
            'estado' => 'VIGENTE',
        ]);
    }

    public function registrar(string $codResidente, array $datos, User $usuario): object
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'atenciones.crear', $usuario);
        $datos = $this->validar($datos);

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();
        if (! $personal || ! in_array($personal->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages([
                'usuario' => 'El usuario no posee un registro de personal activo.',
            ]);
        }

        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());
        if (! $jornada) {
            throw ValidationException::withMessages([
                'jornada' => 'No existe una jornada activa asignada al personal.',
            ]);
        }

        $asignacionArea = AsignacionPersonal::query()
            ->where('cod_personal', $personal->cod_personal)
            ->where('cod_jornada', $jornada->cod_jornada)
            ->whereNotNull('cod_area')
            ->latest('fecha_asignacion')
            ->first();

        $codPersonal = $personal->cod_personal;
        $codArea = $asignacionArea?->cod_area;
        $codJornada = $jornada?->cod_jornada;

        $tipo = strtoupper(trim($datos['tipo']));

        return DB::transaction(function () use ($codResidente, $datos, $usuario, $codPersonal, $codArea, $codJornada, $tipo) {
            $registro = null;

            if ($tipo === 'ALIMENTACION') {
                $registro = RegistroIngesta::create([
                    'cod_ingesta' => 'ING_'.strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_personal' => $codPersonal,
                    'cod_jornada' => $codJornada,
                    'tipo_comida' => $datos['subtipo'],
                    'porcentaje_consumido' => isset($datos['porcentaje']) ? (float) $datos['porcentaje'] : null,
                    'apetito' => $datos['estado_general'] ?? null,
                    'tolerancia' => $datos['tolerancia'] ?? null,
                    'dificultad_deglucion' => ! empty($datos['presenta_dificultad']),
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? null,
                ]);
            } elseif ($tipo === 'HIDRATACION') {
                $registro = RegistroHidratacion::create([
                    'cod_hidratacion' => 'HID_'.strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_personal' => $codPersonal,
                    'cod_jornada' => $codJornada,
                    'tipo_liquido' => $datos['subtipo'],
                    'cantidad_ml' => (int) $datos['cantidad_ml'],
                    'via' => 'ORAL',
                    'tolerancia' => $datos['tolerancia'] ?? null,
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? null,
                ]);
            } elseif ($tipo === 'ELIMINACION') {
                $registro = RegistroEliminacion::create([
                    'cod_eliminacion' => 'ELM_'.strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_personal' => $codPersonal,
                    'cod_jornada' => $codJornada,
                    'tipo_eliminacion' => $datos['subtipo'],
                    'consistencia' => $datos['consistencia'] ?? null,
                    'es_continente' => ! empty($datos['es_continente']),
                    'usa_dispositivo' => ! empty($datos['usa_dispositivo']),
                    'dificultad' => ! empty($datos['presenta_dificultad']),
                    'dolor' => ! empty($datos['presenta_dolor']),
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? null,
                ]);
            } elseif ($tipo === 'MOVILIDAD') {
                $registro = RegistroMovilidad::create([
                    'cod_movilidad' => 'MOV_'.strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_personal' => $codPersonal,
                    'cod_jornada' => $codJornada,
                    'marcha' => $datos['subtipo'],
                    'tipo_apoyo' => $datos['nivel_ayuda'] ?? null,
                    'dispositivo' => $datos['ayuda_tecnica'] ?? null,
                    'fatiga' => $datos['tolerancia'] ?? null,
                    'fecha_hora' => now(),
                    'estado' => 'VIGENTE',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? null,
                ]);
            } elseif ($tipo === 'SUENO') {
                $registro = RegistroSueno::create([
                    'cod_registro_sueno' => 'RSU_'.strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_personal' => $codPersonal,
                    'cod_jornada' => $codJornada,
                    'fecha' => today(),
                    'despertares' => $datos['cantidad_despertares'] ?? null,
                    'insomnio' => ($datos['calidad'] ?? null) === 'INSOMNIO',
                    'somnolencia_diurna' => ($datos['calidad'] ?? null) === 'SOMNOLENCIA',
                    'agitacion_nocturna' => ! empty($datos['agitacion']),
                    'calidad' => $datos['calidad'] ?? $datos['subtipo'],
                    'estado' => 'VIGENTE',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? null,
                ]);
            } else {
                if (! $codArea) {
                    throw ValidationException::withMessages([
                        'area' => 'La jornada activa no tiene un área asignada para registrar esta atención.',
                    ]);
                }

                $registro = Atencion::create([
                    'cod_atencion' => 'ATN_'.strtoupper(Str::random(10)),
                    'cod_residente' => $codResidente,
                    'cod_area' => $codArea,
                    'cod_personal' => $codPersonal,
                    'tipo_atencion' => 'CUIDADO',
                    'motivo' => $tipo.' - '.$datos['subtipo'],
                    'fecha_hora' => now(),
                    'estado' => 'REALIZADA',
                    'observacion' => $datos['observacion'] ?? $datos['motivo'] ?? null,
                ]);
            }

            $esBajaIngesta = $tipo === 'ALIMENTACION'
                && isset($datos['porcentaje'])
                && $datos['porcentaje'] < config('enfermeria.porcentaje_baja_ingesta', 50);

            if (($datos['cambio_respecto_basal'] ?? null) === 'PEOR' || $esBajaIngesta) {
                $this->alertas->crear($codResidente, [
                    'origen' => 'SEGUIMIENTO',
                    'tipo_alerta' => ($datos['cambio_respecto_basal'] ?? null) === 'PEOR' ? 'CAMBIO RESPECTO AL ESTADO BASAL' : 'BAJA INGESTA',
                    'nivel' => 'MEDIO',
                    'motivo' => 'Alerta clínica: '.($datos['motivo'] ?? $datos['observacion'] ?? 'Requiere seguimiento de Enfermería.'),
                ], $usuario);
            }

            return $registro;
        });
    }

    public function registrarDolor(string $codResidente, string $fase, int $intensidad, string $detalle, User $usuario, ?ValoracionDolor $valoracion = null, ?string $resultado = null): ValoracionDolor
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'atenciones.crear', $usuario);
        $fase = mb_strtoupper($fase);
        if ($fase === 'VALORACION') {
            $this->turnos->autorizarContinuidadControl($codResidente);
        }
        Validator::make(compact('fase', 'intensidad', 'detalle', 'resultado'), [
            'fase' => 'required|in:VALORACION,INTERVENCION,REEVALUACION',
            'intensidad' => 'required|integer|min:0|max:10',
            'detalle' => 'required|string|min:5|max:2000',
            'resultado' => 'required_if:fase,REEVALUACION|nullable|string|min:3|max:200',
        ])->validate();

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();
        if (! $personal || ! in_array($personal->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages(['usuario' => 'El usuario no posee un registro de personal activo.']);
        }

        return ValoracionDolor::create([
            'cod_valoracion_dolor' => 'VD_'.strtoupper(Str::random(10)),
            'cod_residente' => $codResidente,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora' => now(),
            'intensidad' => $intensidad,
            'ubicacion' => null,
            'tipo_dolor' => null,
            'desencadenante' => $fase,
            'intervencion' => $detalle,
            'respuesta' => $resultado,
            'estado' => 'VIGENTE',
        ]);
    }

    /** Captura de la valoración inicial desde Nuevo registro, con columnas V2.1 reales. */
    public function registrarValoracionDolor(string $codResidente, array $entrada, User $usuario): ValoracionDolor
    {
        abort_unless(\Illuminate\Support\Facades\Auth::user()?->cod_usuario === $usuario->cod_usuario, 403);
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'valoraciones_dolor.crear', $usuario);

        foreach (['ubicacion', 'duracion_unidad', 'desencadenante', 'intervencion'] as $campo) {
            if (isset($entrada[$campo]) && is_string($entrada[$campo])) {
                $entrada[$campo] = trim($entrada[$campo]);
            }
        }
        $validador = Validator::make($entrada, [
            'fecha_hora' => ['required', 'date_format:Y-m-d\TH:i'],
            'intensidad' => ['required', 'integer', 'between:0,10'],
            'ubicacion' => ['nullable', 'string', 'max:120'],
            'duracion_valor' => ['nullable', 'numeric', 'gt:0'],
            'duracion_unidad' => ['required_with:duracion_valor', 'nullable', 'string', 'max:60'],
            'desencadenante' => ['nullable', 'string'],
            'intervencion' => ['nullable', 'string'],
            'tipo_dolor' => ['prohibited'],
            'eva_posterior' => ['prohibited'],
            'hora_reevaluacion' => ['prohibited'],
            'observacion' => ['prohibited'],
        ], [
            'fecha_hora.required' => 'Ingresa la fecha y hora de valoración.',
            'fecha_hora.date_format' => 'Ingresa una fecha y hora válidas.',
            'intensidad.required' => 'Selecciona la intensidad EVA inicial.',
            'intensidad.integer' => 'La intensidad EVA debe ser un número entero.',
            'intensidad.between' => 'La intensidad EVA debe estar entre 0 y 10.',
            'ubicacion.max' => 'La localización no puede superar 120 caracteres.',
            'duracion_valor.numeric' => 'Ingresa una duración numérica válida.',
            'duracion_valor.gt' => 'La duración debe ser mayor que cero.',
            'duracion_unidad.required_with' => 'Indica la unidad de duración.',
            'duracion_unidad.max' => 'La unidad de duración es demasiado larga.',
            'prohibited' => 'Este campo no está disponible en el formulario de dolor.',
        ]);
        $validador->after(function ($validador) use ($entrada): void {
            if (filled($entrada['duracion_unidad'] ?? null) && ! filled($entrada['duracion_valor'] ?? null)) {
                $validador->errors()->add('duracion_valor', 'Ingresa el valor de la duración.');
            }
        });
        $datos = $validador->validate();

        $fechaHora = \Carbon\Carbon::createFromFormat('!Y-m-d\TH:i', $datos['fecha_hora'], config('app.timezone'));
        if ($fechaHora->isFuture()) {
            throw ValidationException::withMessages(['fecha_hora' => 'La valoración no puede tener una fecha u hora futura.']);
        }
        $duracion = filled($datos['duracion_valor'] ?? null)
            ? trim((string) $datos['duracion_valor'].' '.(string) ($datos['duracion_unidad'] ?? ''))
            : null;
        if ($duracion !== null && mb_strlen($duracion) > 80) {
            throw ValidationException::withMessages(['duracion_valor' => 'La duración completa no puede superar 80 caracteres.']);
        }

        $personal = Personal::query()->where('cod_usuario', $usuario->cod_usuario)
            ->where('estado', 'ACTIVO')->first();
        if (! $personal) {
            throw ValidationException::withMessages(['personal' => 'El usuario autenticado no tiene personal activo.']);
        }

        return ValoracionDolor::create([
            'cod_valoracion_dolor' => 'VD_'.strtoupper(Str::random(10)),
            'cod_residente' => $codResidente,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora' => $fechaHora,
            'intensidad' => (int) $datos['intensidad'],
            'ubicacion' => filled($datos['ubicacion'] ?? null) ? $datos['ubicacion'] : null,
            'duracion' => $duracion,
            'desencadenante' => filled($datos['desencadenante'] ?? null) ? $datos['desencadenante'] : null,
            'intervencion' => filled($datos['intervencion'] ?? null) ? $datos['intervencion'] : null,
            'respuesta' => null,
            'estado' => 'VIGENTE',
        ]);
    }

    public function colocarDispositivo(string $codResidente, array $datos, User $usuario): DispositivoClinico
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'atenciones.crear', $usuario);
        $datos = Validator::make($datos, [
            'tipo' => 'required|in:OXIGENO,SONDA_URINARIA,OSTOMIA,ALIMENTACION_ENTERAL,OTRO',
            'ubicacion' => 'nullable|string|max:120',
            'indicacion' => 'required|string|min:5|max:1000',
        ])->validate();

        $personal = $usuario->personal ?: Personal::where('cod_usuario', $usuario->cod_usuario)->first();
        if (! $personal || ! in_array($personal->estado, ['ACTIVO', 'ACTIVA'], true)) {
            throw ValidationException::withMessages(['usuario' => 'El usuario no posee un registro de personal activo.']);
        }

        return DispositivoClinico::create([
            'cod_dispositivo' => 'DIS_'.strtoupper(Str::random(10)),
            'cod_residente' => $codResidente,
            'cod_personal' => $personal->cod_personal,
            'tipo' => $datos['tipo'],
            'ubicacion' => $datos['ubicacion'] ?? null,
            'fecha_colocacion' => now(),
            'estado' => 'ACTIVO',
            'observacion' => $datos['indicacion'] ?? null,
        ]);
    }

    public function retirarDispositivo(DispositivoClinico $dispositivo, string $motivo, User $usuario): void
    {
        $this->turnos->autorizarMutacionEnfermeria($dispositivo->cod_residente, 'atenciones.crear', $usuario);
        Validator::make(['motivo' => $motivo], ['motivo' => 'required|string|min:5|max:1000'])->validate();
        abort_unless($dispositivo->estado === 'ACTIVO', 409, 'El dispositivo ya fue retirado.');
        $dispositivo->update([
            'estado' => 'RETIRADO',
            'fecha_retiro' => now(),
            'observacion' => trim(($dispositivo->observacion ? $dispositivo->observacion.' | ' : '').'Retiro: '.$motivo),
        ]);
    }

    private function validar(array $datos): array
    {
        $datos = array_map(fn ($v) => $v === '' ? null : $v, $datos);
        $validados = Validator::make($datos, [
            'tipo' => 'required|in:ALIMENTACION,HIDRATACION,ELIMINACION,HIGIENE,MOVILIDAD,SUENO,VALORACION_RAPIDA,PROCEDIMIENTO,DOLOR,DISPOSITIVO,OBSERVACION',
            'subtipo' => 'required|string|max:50',
            'porcentaje' => 'nullable|integer|in:0,25,50,75,100',
            'cantidad_ml' => 'required_if:tipo,HIDRATACION|nullable|integer|min:1|max:10000',
            'dolor' => 'nullable|integer|min:0|max:10',
            'nivel_ayuda' => 'nullable|string|max:30',
            'tolerancia' => 'nullable|string|max:30',
            'resultado' => 'nullable|string|max:200',
            'motivo' => 'nullable|string|max:2000',
            'observacion' => 'nullable|string|max:5000',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i|after:hora_inicio',
            'cantidad_despertares' => 'nullable|integer|min:0|max:30',
            'fase_dolor' => 'nullable|in:VALORACION,INTERVENCION,REEVALUACION',
            'cambio_respecto_basal' => 'nullable|in:MEJOR,PEOR,SIN_CAMBIOS,NO_EVALUABLE',
            'estado_general' => 'nullable|string|max:30',
            'conciencia' => 'nullable|string|max:30',
            'cognicion' => 'nullable|string|max:30',
            'conducta' => 'nullable|string|max:30',
            'respiracion' => 'nullable|string|max:30',
            'consistencia' => 'nullable|string|max:50',
            'es_continente' => 'nullable|boolean',
            'presenta_dificultad' => 'nullable|boolean',
            'presenta_dolor' => 'nullable|boolean',
            'usa_dispositivo' => 'nullable|boolean',
            'ayuda_tecnica' => 'nullable|string|max:80',
            'calidad' => 'nullable|string|max:30',
            'deambulacion_nocturna' => 'nullable|boolean',
            'agitacion' => 'nullable|boolean',
        ])->validate();

        if ($validados['tipo'] === 'ALIMENTACION' && isset($validados['porcentaje']) && $validados['porcentaje'] < config('enfermeria.porcentaje_baja_ingesta', 50)
            && mb_strlen(trim($validados['motivo'] ?? '')) < 3) {
            throw ValidationException::withMessages(['motivo' => 'Indique el motivo de la baja ingesta.']);
        }
        if (in_array($validados['tipo'], ['HIGIENE', 'PROCEDIMIENTO']) && in_array($validados['resultado'] ?? null, ['PARCIAL', 'NO_REALIZADO', 'CANCELADO'])
            && mb_strlen(trim($validados['motivo'] ?? '')) < 3) {
            throw ValidationException::withMessages(['motivo' => 'El motivo es obligatorio cuando el cuidado no fue completado.']);
        }

        return $validados;
    }
}
