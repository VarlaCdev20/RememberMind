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

    public const MOTIVOS_MOVILIDAD = ['CONTROL_DIARIO', 'CAMBIO_FUNCIONAL', 'POST_CAIDA', 'TRAS_FISIOTERAPIA', 'ANTES_TRASLADO', 'OTRO'];
    public const ACTIVIDADES_MOVILIDAD = ['CAMINAR_HABITACION', 'CAMINAR_PASILLO', 'LEVANTARSE_CAMA', 'TRANSFERENCIA_CAMA_SILLON', 'CAMBIO_POSTURAL', 'SEDESTACION', 'BIPEDESTACION'];
    public const ACTIVIDADES_DEAMBULACION = ['CAMINAR_HABITACION', 'CAMINAR_PASILLO'];
    public const DISPOSITIVOS_MOVILIDAD = ['NINGUNO', 'BASTON', 'ANDADOR', 'SILLA_RUEDAS', 'BARANDILLA', 'OTRO'];
    public const TOLERANCIAS_MOVILIDAD = ['BUENA', 'PARCIAL', 'MALA'];
    public const CAMBIOS_HABITUALES = ['SIN_CAMBIOS', 'MEJOR', 'PEOR'];

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
        $campos = array_merge(['tipo_eliminacion'], RegistroEliminacion::COMUNES, RegistroEliminacion::URINARIOS, RegistroEliminacion::INTESTINALES);
        if (array_diff(array_keys($datos), $campos) !== []) {
            throw ValidationException::withMessages(['datos' => 'Utiliza los campos estructurados del formulario de eliminación.']);
        }
        foreach ($datos as $campo => $valor) {
            if (is_string($valor)) {
                $datos[$campo] = trim($valor);
                if ($datos[$campo] === '') $datos[$campo] = null;
            }
        }
        $tipo = $datos['tipo_eliminacion'] ?? null;
        if (in_array($tipo, self::TIPOS_ELIMINACION_REGISTRO, true)) {
            $prohibidos = $tipo === 'URINARIA' ? RegistroEliminacion::INTESTINALES : RegistroEliminacion::URINARIOS;
            foreach ($prohibidos as $campo) {
                if (array_key_exists($campo, $datos) && $datos[$campo] !== null) {
                    throw ValidationException::withMessages([$campo => 'El formulario contiene datos que no corresponden a una eliminación '.strtolower($tipo).'.']);
                }
            }
        }
        $reglas = [
            'tipo_eliminacion' => ['required', Rule::in(self::TIPOS_ELIMINACION_REGISTRO)],
            'volumen_ml' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,999999.99'],
            'tipo_bristol' => ['nullable', 'integer', 'between:1,7'],
            'continencia' => ['nullable', Rule::in($tipo === 'URINARIA' ? self::CONTINENCIAS_URINARIAS : self::CONTINENCIAS_INTESTINALES)],
            'presencia_sangre' => ['nullable', 'boolean'], 'presencia_moco' => ['nullable', 'boolean'],
            'molestia_eliminacion' => ['nullable', 'boolean'],
            'descripcion_molestia' => ['nullable', 'string', 'max:250'],
            'observacion' => ['nullable', 'string', 'max:5000'],
        ];
        foreach (RegistroEliminacion::OPCIONES as $campo => $opciones) $reglas[$campo] = ['nullable', Rule::in(array_keys($opciones))];
        $validados = Validator::make($datos, $reglas, [
            'tipo_eliminacion.required' => 'Selecciona el tipo de eliminación.',
            'tipo_eliminacion.in' => 'Selecciona un tipo de eliminación válido.',
            'volumen_ml.between' => 'El volumen debe estar entre 0 y 999999.99 mL.',
            'volumen_ml.decimal' => 'El volumen admite como máximo dos decimales.',
            'tipo_bristol.between' => 'Selecciona un tipo de Bristol del 1 al 7.',
            'continencia.in' => 'Selecciona una continencia válida para este tipo de eliminación.',
            'descripcion_molestia.max' => 'La descripción admite hasta 250 caracteres.',
            'observacion.max' => 'La observación admite hasta 5000 caracteres.',
        ])->validate();
        if (! in_array($validados['molestia_eliminacion'] ?? null, [true, 1, '1'], true)) $validados['descripcion_molestia'] = null;
        $personal = $usuario->personal;
        abort_unless($personal && in_array($personal->estado, ['ACTIVO', 'ACTIVA'], true), 403);
        $jornada = app(MiTurnoService::class)->resolverJornadaActual($personal, now());
        if (! $jornada) throw ValidationException::withMessages(['jornada' => 'No existe una jornada activa asignada al personal.']);
        return RegistroEliminacion::create(array_merge(array_fill_keys($campos, null), $validados, [
            'cod_eliminacion' => 'ELI_'.strtoupper(Str::random(10)), 'cod_residente' => $codResidente,
            'cod_personal' => $personal->cod_personal, 'cod_jornada' => $jornada->cod_jornada,
            'fecha_hora' => now(), 'cantidad' => null, 'caracteristica' => null, 'estado' => 'VIGENTE',
        ]));
    }

    public function registrarMovilidad(string $codResidente, array $datos, User $usuario): RegistroMovilidad
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'registros_movilidad.crear', $usuario);

        // Las precisiones de «Otro» forman parte de la narrativa, no son columnas nuevas.
        $detallesOtro = ['motivo_otro' => 'motivo_registro', 'dispositivo_otro' => 'dispositivo'];
        $camposPermitidos = [...RegistroMovilidad::CAMPOS_CAPTURA, ...array_keys($detallesOtro)];
        if (array_diff(array_keys($datos), $camposPermitidos) !== []) {
            throw ValidationException::withMessages([
                'datos' => 'El formulario incluye campos que este registro de movilidad no puede guardar.',
            ]);
        }

        foreach (['observacion', ...array_keys($detallesOtro)] as $campo) {
            if (is_string($datos[$campo] ?? null)) {
                $datos[$campo] = trim($datos[$campo]);
                if ($datos[$campo] === '') {
                    $datos[$campo] = null;
                }
            }
        }
        $validados = Validator::make($datos, [
            'motivo_otro' => ['required_if:motivo_registro,OTRO', 'nullable', 'string', 'max:500', Rule::prohibitedIf(($datos['motivo_registro'] ?? null) !== 'OTRO')],
            'dispositivo_otro' => ['required_if:dispositivo,OTRO', 'nullable', 'string', 'max:500', Rule::prohibitedIf(($datos['dispositivo'] ?? null) !== 'OTRO')],
            'motivo_registro' => ['nullable', Rule::in(self::MOTIVOS_MOVILIDAD)],
            'actividad_realizada' => ['nullable', Rule::in(self::ACTIVIDADES_MOVILIDAD)],
            'dispositivo' => ['nullable', 'string', 'max:80', Rule::in(self::DISPOSITIVOS_MOVILIDAD)],
            'distancia_metros' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,99999.99'],
            'tolerancia_movilidad' => ['nullable', Rule::in(self::TOLERANCIAS_MOVILIDAD)],
            'cambio_habitual' => ['nullable', Rule::in(self::CAMBIOS_HABITUALES)],
            'dolor_movilidad' => ['nullable', 'boolean'],
            'mareo' => ['nullable', 'boolean'],
            'disnea' => ['nullable', 'boolean'],
            'debilidad' => ['nullable', 'boolean'],
            'marcha' => ['required', Rule::in(self::MOVILIDAD_OBSERVADA)],
            'traslado' => ['nullable', Rule::in(self::TRASLADOS)],
            'tipo_apoyo' => ['nullable', Rule::in(self::NIVELES_AYUDA)],
            'equilibrio' => ['nullable', Rule::in(self::EQUILIBRIOS)],
            'fatiga' => ['nullable', Rule::in(self::FATIGAS)],
            'riesgo_caida' => ['nullable', Rule::in(self::RIESGOS_CAIDA)],
            'observacion' => ['nullable', 'string', 'max:5000'],
        ], [
            'motivo_otro.required_if' => 'Especifica cuál es el otro motivo.',
            'dispositivo_otro.required_if' => 'Especifica cuál es el otro dispositivo.',
            'motivo_otro.string' => 'Describe el otro motivo con texto.',
            'dispositivo_otro.string' => 'Describe el otro dispositivo con texto.',
            'motivo_otro.max' => 'El otro motivo no puede superar 500 caracteres.',
            'dispositivo_otro.max' => 'El otro dispositivo no puede superar 500 caracteres.',
            'motivo_otro.prohibited' => 'El detalle de otro motivo solo corresponde a la opción Otro.',
            'dispositivo_otro.prohibited' => 'El detalle de otro dispositivo solo corresponde a la opción Otro.',
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

        if (isset($validados['distancia_metros']) && ! in_array($validados['actividad_realizada'] ?? null, self::ACTIVIDADES_DEAMBULACION, true)) {
            throw ValidationException::withMessages(['distancia_metros' => 'La distancia solo corresponde a una actividad de deambulación.']);
        }

        $narrativa = [];
        foreach (['motivo_otro' => 'Otro motivo', 'dispositivo_otro' => 'Otro dispositivo'] as $campo => $label) {
            if (isset($validados[$campo])) {
                $narrativa[] = $label.': '.$validados[$campo];
            }
            unset($validados[$campo]);
        }
        if (isset($validados['observacion'])) {
            $narrativa[] = $validados['observacion'];
        }
        $validados['observacion'] = $narrativa ? implode("\n", $narrativa) : null;
        if (mb_strlen($validados['observacion'] ?? '') > 5000) {
            throw ValidationException::withMessages(['observacion' => 'Las observaciones y los detalles de Otro juntos no pueden superar 5000 caracteres.']);
        }

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
            'dispositivo' => $validados['dispositivo'] ?? null,
            'fatiga' => $validados['fatiga'] ?? null,
            'riesgo_caida' => $validados['riesgo_caida'] ?? null,
            'observacion' => $validados['observacion'] ?? null,
            'estado' => 'VIGENTE',
        ] + $validados);
    }

    public const TOLERANCIAS_HIDRATACION = ['ADECUADA', 'PARCIAL', 'RECHAZO', 'NAUSEAS'];

    public function registrarHidratacion(string $codResidente, array $datos, User $usuario): RegistroHidratacion
    {
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'registros_hidratacion.crear', $usuario);
        if (array_diff(array_keys($datos), ['cantidad_ml', 'tipo_liquido', 'tolerancia', 'observacion']) !== []) {
            throw ValidationException::withMessages(['datos' => 'El aporte incluye campos que este registro no puede guardar.']);
        }
        foreach (['tipo_liquido', 'tolerancia', 'observacion'] as $campo) {
            if (is_string($datos[$campo] ?? null)) {
                $datos[$campo] = trim($datos[$campo]);
                if ($datos[$campo] === '') $datos[$campo] = null;
            }
        }
        $validados = Validator::make($datos, [
            'cantidad_ml' => ['required', 'integer', 'between:1,10000'],
            'tipo_liquido' => ['nullable', 'string', 'max:60'],
            'tolerancia' => ['nullable', Rule::in(self::TOLERANCIAS_HIDRATACION)],
            'observacion' => ['nullable', 'string', 'max:5000'],
        ], [
            'cantidad_ml.required' => 'Ingresa el volumen del aporte.',
            'cantidad_ml.integer' => 'Ingresa un volumen entero en mililitros.',
            'cantidad_ml.between' => 'El volumen debe estar entre 1 y 10 000 mL.',
            'tipo_liquido.max' => 'El tipo de líquido admite hasta 60 caracteres.',
            'tolerancia.in' => 'Selecciona una tolerancia válida.',
            'observacion.max' => 'La observación admite hasta 5000 caracteres.',
        ])->validate();

        $personal = $usuario->personal;
        abort_unless($personal && in_array($personal->estado, ['ACTIVO', 'ACTIVA'], true), 403);
        $jornada = app(MiTurnoService::class)->resolverJornadaActual($personal, now());
        if (! $jornada) {
            throw ValidationException::withMessages(['jornada' => 'No existe una jornada activa asignada al personal.']);
        }

        return RegistroHidratacion::create([
            'cod_hidratacion' => 'HID_'.strtoupper(Str::random(10)),
            'cod_residente' => $codResidente, 'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada, 'fecha_hora' => now(),
            'cantidad_ml' => $validados['cantidad_ml'], 'tipo_liquido' => $validados['tipo_liquido'] ?? null,
            'tolerancia' => $validados['tolerancia'] ?? null, 'observacion' => $validados['observacion'] ?? null,
            'estado' => 'VIGENTE',
        ]);
    }

    public function registrar(string $codResidente, array $datos, User $usuario): object
    {
        if (strtoupper(trim($datos['tipo'] ?? '')) === 'MOVILIDAD') {
            $captura = array_diff_key($datos, array_flip(['tipo', 'subtipo', 'nivel_ayuda', 'ayuda_tecnica', 'tolerancia', 'motivo']));
            $captura['marcha'] = $datos['marcha'] ?? $datos['subtipo'] ?? null;
            $captura['tipo_apoyo'] = $datos['tipo_apoyo'] ?? $datos['nivel_ayuda'] ?? null;
            $captura['dispositivo'] = $datos['dispositivo'] ?? $datos['ayuda_tecnica'] ?? null;
            $captura['fatiga'] = $datos['fatiga'] ?? $datos['tolerancia'] ?? null;
            $captura['observacion'] = $datos['observacion'] ?? $datos['motivo'] ?? null;
            return $this->registrarMovilidad($codResidente, $captura, $usuario);
        }
        // El consumidor genérico conserva su entrada, pero comparte el único escritor de hidratación.
        if (($datos['tipo'] ?? null) === 'HIDRATACION') {
            return $this->registrarHidratacion($codResidente, [
                'cantidad_ml' => $datos['cantidad_ml'] ?? null, 'tipo_liquido' => $datos['subtipo'] ?? null,
                'tolerancia' => $datos['tolerancia'] ?? null, 'observacion' => $datos['observacion'] ?? null,
            ], $usuario);
        }
        if (($datos['tipo'] ?? null) === 'ELIMINACION') {
            $captura = array_diff_key($datos, array_flip(['tipo', 'subtipo']));
            $captura['tipo_eliminacion'] = $datos['tipo_eliminacion'] ?? $datos['subtipo'] ?? null;
            return $this->registrarEliminacion($codResidente, $captura, $usuario);
        }
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

    /** Valoración inicial o nueva reevaluación: nunca modifica la fila de origen. */
    public function registrarValoracionDolor(string $codResidente, array $entrada, User $usuario): ValoracionDolor
    {
        abort_unless(\Illuminate\Support\Facades\Auth::user()?->cod_usuario === $usuario->cod_usuario, 403);
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'valoraciones_dolor.crear', $usuario);

        foreach (['ubicacion', 'duracion_unidad', 'frecuencia', 'desencadenante', 'factores_alivio', 'intervencion', 'respuesta'] as $campo) {
            if (isset($entrada[$campo]) && is_string($entrada[$campo])) {
                $entrada[$campo] = trim($entrada[$campo]);
            }
        }
        $validador = Validator::make($entrada, [
            'fecha_hora' => ['prohibited'],
            'intensidad' => ['required', 'integer', 'between:0,10'],
            'ubicacion' => ['nullable', 'string', 'max:120'],
            'duracion_valor' => ['nullable', 'numeric', 'gt:0'],
            'duracion_unidad' => ['required_with:duracion_valor', 'nullable', 'string', 'max:60'],
            'desencadenante' => ['nullable', 'string'],
            'intervencion' => ['nullable', 'string'],
            'frecuencia' => ['nullable', 'string', 'max:40'],
            'factores_alivio' => ['nullable', 'string'],
            'cod_valoracion_origen' => ['nullable', 'string', 'max:20'],
            'tipo_dolor' => ['prohibited'],
            'eva_posterior' => ['prohibited'],
            'hora_reevaluacion' => ['prohibited'],
            'observacion' => ['prohibited'],
            'respuesta' => [Rule::prohibitedIf(blank($entrada['cod_valoracion_origen'] ?? null)), 'nullable', 'string'],
            'cod_personal' => ['prohibited'],
        ], [
            'fecha_hora.prohibited' => 'La fecha y hora se generan automáticamente en el servidor.',
            'intensidad.required' => 'Selecciona la intensidad EVA actual.',
            'intensidad.integer' => 'La intensidad EVA debe ser un número entero.',
            'intensidad.between' => 'La intensidad EVA debe estar entre 0 y 10.',
            'ubicacion.max' => 'La localización no puede superar 120 caracteres.',
            'frecuencia.max' => 'La frecuencia no puede superar 40 caracteres.',
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

        // La propietaria aprobó el momento de registro automático para este flujo.
        // Ni la UI ni un consumidor directo pueden atribuir una fecha o autor externos.
        $fechaHora = now();
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

        return DB::transaction(function () use ($codResidente, $datos, $personal, $fechaHora, $duracion, $usuario) {
            $origen = filled($datos['cod_valoracion_origen'] ?? null)
                ? $this->resolverRaizDolor($codResidente, $datos['cod_valoracion_origen'], true)
                : null;
            if ($origen) {
                abort_unless($usuario->can('valoraciones_dolor.ver'), 403);
            }

            return ValoracionDolor::create([
                'cod_valoracion_dolor' => 'VD_'.strtoupper(Str::random(10)),
                'cod_residente' => $codResidente,
                'cod_personal' => $personal->cod_personal,
                'fecha_hora' => $fechaHora,
                'intensidad' => (int) $datos['intensidad'],
                'ubicacion' => filled($datos['ubicacion'] ?? null) ? $datos['ubicacion'] : null,
                'duracion' => $duracion,
                'cod_valoracion_origen' => $origen?->cod_valoracion_dolor,
                'frecuencia' => filled($datos['frecuencia'] ?? null) ? $datos['frecuencia'] : null,
                'factores_alivio' => filled($datos['factores_alivio'] ?? null) ? $datos['factores_alivio'] : null,
                'desencadenante' => filled($datos['desencadenante'] ?? null) ? $datos['desencadenante'] : null,
                'intervencion' => filled($datos['intervencion'] ?? null) ? $datos['intervencion'] : null,
                'respuesta' => $origen && filled($datos['respuesta'] ?? null) ? $datos['respuesta'] : null,
                'estado' => 'VIGENTE',
            ]);
        });
    }

    public function prepararReevaluacionDolor(string $codResidente, string $codValoracion, User $usuario): ValoracionDolor
    {
        abort_unless(\Illuminate\Support\Facades\Auth::user()?->cod_usuario === $usuario->cod_usuario, 403);
        $this->turnos->autorizarMutacionEnfermeria($codResidente, 'valoraciones_dolor.crear', $usuario);
        abort_unless($usuario->can('valoraciones_dolor.ver'), 403);

        return $this->resolverRaizDolor($codResidente, $codValoracion);
    }

    private function resolverRaizDolor(string $codResidente, string $codValoracion, bool $lock = false): ValoracionDolor
    {
        $visitadas = [];
        while (! isset($visitadas[$codValoracion]) && count($visitadas) < 32) {
            $visitadas[$codValoracion] = true;
            $query = ValoracionDolor::query()->whereKey($codValoracion)
                ->where('cod_residente', $codResidente)->where('estado', 'VIGENTE')
                ->where('fecha_hora', '<=', now());
            $registro = ($lock ? $query->lockForUpdate() : $query)->first();
            if (! $registro) {
                throw ValidationException::withMessages(['cod_valoracion_origen' =>
                    'La valoración de origen no está disponible para este residente. Revisa el historial.']);
            }
            if ($registro->cod_valoracion_origen === null) {
                return $registro;
            }
            $codValoracion = $registro->cod_valoracion_origen;
        }
        throw ValidationException::withMessages(['cod_valoracion_origen' =>
            'El episodio de dolor tiene un vínculo inconsistente. Solicita su revisión.']);
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
