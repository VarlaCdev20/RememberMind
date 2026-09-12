<?php

namespace App\Livewire\Clinica;

use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;
use App\Services\Clinica\SignosVitalesService;
use App\Services\Clinica\ValidacionSignosVitalesService;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class RegistroSignosVitalesModal extends Component
{
    /**
     * Estados en los que el expediente queda en modo consulta.
     * No modifica la estructura de BD.
     */
    private const ESTADOS_NO_MODIFICABLES = [
        'ARCHIVADO',
        'EGRESADO',
        'FALLECIDO',
        'INACTIVO',
        'INACTIVA',
        'NO_ADMITIDO',
        'RETIRADO',
        'DERIVADO',
        'TRASLADADO',
    ];

    private const CAMPOS_MEDICION = [
        'pa_sistolica',
        'pa_diastolica',
        'fc',
        'fr',
        'temperatura',
        'saturacion',
        'glucosa',
        'peso',
        'dolor',
    ];

    private const MAPA_CAMPOS_SERVICIO = [
        'pa_sistolica' => 'presion_sistolica',
        'pa_diastolica' => 'presion_diastolica',
        'fc' => 'frecuencia_cardiaca',
        'fr' => 'frecuencia_respiratoria',
        'temperatura' => 'temperatura',
        'saturacion' => 'saturacion',
        'glucosa' => 'glucosa',
        'peso' => 'peso',
        'talla' => 'talla',
        'dolor' => 'dolor',
        'observacion' => 'observacion',
    ];

    public bool $mostrar = false;

    public ?string $cod_am = null;

    public ?AdultoMayor $adulto = null;

    public string $fecha = '';

    public string $hora = '';

    public ?string $pa_sistolica = null;

    public ?string $pa_diastolica = null;

    public ?string $fc = null;

    public ?string $fr = null;

    public ?string $temperatura = null;

    public ?string $saturacion = null;

    public ?string $glucosa = null;

    public ?string $peso = null;

    public ?string $talla = null;

    public ?string $imc = null;

    public ?string $dolor = null;

    public ?string $posicion = null;

    public bool $usa_oxigeno = false;

    public string $observacion = '';

    public bool $confirmar_presion_atipica = false;

    /**
     * Estados de UX calculados en servidor.
     */
    public bool $presion_incompleta = false;

    public bool $presion_atipica = false;

    public bool $tieneMedicion = false;

    public bool $puedeGuardar = false;

    /**
     * Referencias históricas. No se convierten automáticamente en una medición nueva.
     */
    public ?string $ultimo_peso_conocido = null;

    public ?string $fecha_ultimo_peso = null;

    public ?string $ultima_talla_conocida = null;

    public ?string $fecha_ultima_talla = null;

    protected $listeners = [
        'abrir-signos-vitales-medico' => 'abrir',
    ];

    public function mount(): void
    {
        $this->inicializarFechaHora();
        $this->actualizarEstadoFormulario();
    }

    protected function rules(): array
    {
        return [
            'cod_am' => ['required', 'string', 'exists:adulto_mayor,cod_am'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'posicion' => ['nullable', 'in:SENTADO,ACOSTADO,DE_PIE'],
            'usa_oxigeno' => ['boolean'],
            'confirmar_presion_atipica' => ['boolean'],
        ];
    }

    protected $messages = [
        'cod_am.required' => 'No se pudo identificar al residente.',
        'cod_am.exists' => 'El residente seleccionado ya no existe.',
        'fecha.required' => 'La fecha del control es obligatoria.',
        'fecha.date' => 'La fecha del control no es válida.',
        'fecha.before_or_equal' => 'La fecha del control no puede ser futura.',
        'hora.required' => 'La hora del control es obligatoria.',
        'hora.date_format' => 'La hora debe tener el formato HH:MM.',
        'posicion.in' => 'Seleccione una posición válida para la medición.',
    ];

    /**
     * Apertura segura. Si no cumple permisos o contexto institucional,
     * el modal no se abre.
     */
    public function abrir(string $cod_am): void
    {
        $usuario = Auth::user();
        abort_unless($usuario, 401);

        $adulto = AdultoMayor::query()
            ->with('estado')
            ->findOrFail($cod_am);

        $this->autorizarOperacion($adulto);
        $this->validarExpedienteModificable($adulto);

        $this->resetForm();

        $this->cod_am = $adulto->cod_am;
        $this->adulto = $adulto;
        $this->inicializarFechaHora();

        $this->cargarReferenciasAntropometricas($adulto->cod_am);

        // La talla es una referencia relativamente estable; el peso debe medirse de nuevo.
        if ($this->ultima_talla_conocida !== null) {
            $this->talla = $this->ultima_talla_conocida;
        }

        $this->calcularImc();
        $this->actualizarEstadoFormulario();

        $this->mostrar = true;
    }

    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    /**
     * Validación reactiva para todos los campos relevantes.
     * Livewire llama este método después de cada actualización.
     */
    public function updated(string $propiedad): void
    {
        if (array_key_exists($propiedad, self::MAPA_CAMPOS_SERVICIO)) {
            $this->validarCampoTecnico($propiedad);
        }

        if (in_array($propiedad, ['pa_sistolica', 'pa_diastolica'], true)) {
            $this->actualizarEstadoPresion();
        }

        if (in_array($propiedad, ['peso', 'talla'], true)) {
            $this->calcularImc();
        }

        if (in_array($propiedad, ['fecha', 'hora'], true)) {
            $this->validarFechaHoraEnTiempoReal();
        }

        if ($propiedad === 'posicion') {
            $this->validarCampoBase('posicion');
        }

        if ($propiedad === 'confirmar_presion_atipica' && $this->presion_atipica) {
            $this->resetValidation('confirmar_presion_atipica');

            if (!$this->confirmar_presion_atipica) {
                $this->addError(
                    'confirmar_presion_atipica',
                    'Debe confirmar que repitió la medición antes de registrar esta presión.'
                );
            }
        }

        $this->actualizarEstadoFormulario();
    }

    /**
     * Guardado seguro y revalidado.
     */
    public function guardar(): void
    {
        $this->resetValidation();

        $usuario = Auth::user();
        abort_unless($usuario, 401);

        $this->validate();
        $this->validarFechaHoraNoFutura();

        $adulto = AdultoMayor::query()
            ->with('estado')
            ->findOrFail($this->cod_am);

        $this->autorizarOperacion($adulto);
        $this->validarExpedienteModificable($adulto);

        try {
            $entrada = $this->entradaSignos();

            if ($usuario->hasRole('ENFERMEROS')) {
                /**
                 * Enfermería mantiene turno + recepción + asignación.
                 * El servicio vuelve a validar todo en servidor.
                 */
                app(SignosVitalesService::class)->registrar(
                    $adulto->cod_am,
                    $entrada,
                    $usuario,
                    'signos_vitales.crear'
                );
            } else {
                /**
                 * Médico/Superadmin autorizado:
                 * mismas reglas técnicas, sin exigir asignación de turno de Enfermería.
                 */
                $datos = app(SignosVitalesService::class)->validarYNormalizar($entrada);

                DB::transaction(function () use ($adulto, $usuario, $datos): void {
                    $adultoBloqueado = AdultoMayor::query()
                        ->lockForUpdate()
                        ->findOrFail($adulto->cod_am);

                    $adultoBloqueado->load('estado');

                    // Previene guardar si el expediente cambió mientras el modal estaba abierto.
                    $this->autorizarOperacion($adultoBloqueado);
                    $this->validarExpedienteModificable($adultoBloqueado);

                    SignosVitalesAdulto::create($datos + [
                        'cod_am' => $adultoBloqueado->cod_am,
                        'registrado_por' => $usuario->cod_usu,
                        'estado' => 'VIGENTE',
                    ]);
                });
            }

            $this->cerrar();

            $this->dispatch('signos-guardados');
            $this->dispatch('signos-actualizados');

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Signos vitales registrados',
                'text' => 'El control clínico se guardó correctamente.',
            ]);
        } catch (ValidationException $e) {
            $this->trasladarErroresServicio($e);
            $this->actualizarEstadoFormulario();
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            $this->addError(
                'general',
                'Ocurrió un problema al registrar los signos vitales. Verifique los datos e intente nuevamente.'
            );

            $this->puedeGuardar = false;
        }
    }

    /**
     * Valida un signo de forma aislada al escribir, usando exactamente
     * las reglas técnicas centralizadas del proyecto.
     */
    private function validarCampoTecnico(string $propiedad): void
    {
        $campoServicio = self::MAPA_CAMPOS_SERVICIO[$propiedad] ?? null;

        if (!$campoServicio) {
            return;
        }

        $reglas = ValidacionSignosVitalesService::reglas();

        if (!array_key_exists($campoServicio, $reglas)) {
            return;
        }

        $this->resetValidation($propiedad);

        $valor = $this->normalizarVacio($this->{$propiedad});

        $validator = Validator::make(
            [$campoServicio => $valor],
            [$campoServicio => $reglas[$campoServicio]],
            ValidacionSignosVitalesService::mensajes()
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->get($campoServicio) as $mensaje) {
                $this->addError($propiedad, $mensaje);
            }
        }
    }

    private function validarCampoBase(string $campo): void
    {
        $this->resetValidation($campo);

        $validator = Validator::make(
            [$campo => $this->{$campo}],
            [$campo => $this->rules()[$campo]],
            $this->messages
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->get($campo) as $mensaje) {
                $this->addError($campo, $mensaje);
            }
        }
    }

    /**
     * Validación reactiva de fecha y hora, incluyendo hora futura el mismo día.
     */
    private function validarFechaHoraEnTiempoReal(): void
    {
        $this->resetValidation(['fecha', 'hora']);

        foreach (['fecha', 'hora'] as $campo) {
            $validator = Validator::make(
                [$campo => $this->{$campo}],
                [$campo => $this->rules()[$campo]],
                $this->messages
            );

            if ($validator->fails()) {
                foreach ($validator->errors()->get($campo) as $mensaje) {
                    $this->addError($campo, $mensaje);
                }
            }
        }

        if ($this->getErrorBag()->has('fecha') || $this->getErrorBag()->has('hora')) {
            return;
        }

        try {
            $this->validarFechaHoraNoFutura();
        } catch (ValidationException $e) {
            foreach ($e->errors() as $campo => $mensajes) {
                foreach ($mensajes as $mensaje) {
                    $this->addError($campo, $mensaje);
                }
            }
        }
    }

    /**
     * Integridad de presión en tiempo real:
     * - si se captura una parte, exige la otra;
     * - si sistólica <= diastólica, exige reconfirmación explícita.
     */
    private function actualizarEstadoPresion(): void
    {
        $sistolica = $this->aEnteroNullable($this->pa_sistolica);
        $diastolica = $this->aEnteroNullable($this->pa_diastolica);

        $this->presion_incompleta =
            ($sistolica !== null && $diastolica === null)
            || ($sistolica === null && $diastolica !== null);

        $nuevaAtipica =
            $sistolica !== null
            && $diastolica !== null
            && $sistolica <= $diastolica;

        // Una confirmación anterior deja de ser válida al modificar la presión.
        if ($this->presion_atipica !== $nuevaAtipica || $nuevaAtipica) {
            $this->confirmar_presion_atipica = false;
        }

        $this->presion_atipica = $nuevaAtipica;

        if (!$this->presion_atipica) {
            $this->resetValidation('confirmar_presion_atipica');
        }
    }

    /**
     * Estado general del formulario usado por la UI para habilitar/bloquear Guardar.
     */
    private function actualizarEstadoFormulario(): void
    {
        $this->tieneMedicion = collect(self::CAMPOS_MEDICION)
            ->contains(fn(string $campo) => $this->normalizarVacio($this->{$campo}) !== null);

        $presionValida =
            !$this->presion_incompleta
            && (!$this->presion_atipica || $this->confirmar_presion_atipica);

        $fechaHoraCompletas = trim($this->fecha) !== '' && trim($this->hora) !== '';

        $this->puedeGuardar =
            $this->cod_am !== null
            && $fechaHoraCompletas
            && $this->tieneMedicion
            && $presionValida
            && count($this->getErrorBag()->all()) === 0;
    }

    private function entradaSignos(): array
    {
        return [
            'fecha' => $this->fecha,
            'hora' => $this->hora,
            'presion_sistolica' => $this->normalizarVacio($this->pa_sistolica),
            'presion_diastolica' => $this->normalizarVacio($this->pa_diastolica),
            'frecuencia_cardiaca' => $this->normalizarVacio($this->fc),
            'frecuencia_respiratoria' => $this->normalizarVacio($this->fr),
            'temperatura' => $this->normalizarVacio($this->temperatura),
            'saturacion' => $this->normalizarVacio($this->saturacion),
            'glucosa' => $this->normalizarVacio($this->glucosa),
            'peso' => $this->normalizarVacio($this->peso),
            'talla' => $this->normalizarVacio($this->talla),
            'dolor' => $this->normalizarVacio($this->dolor),
            'posicion' => $this->normalizarVacio($this->posicion),
            'usa_oxigeno' => $this->usa_oxigeno,
            'valor_atipico_confirmado' => $this->confirmar_presion_atipica,
            'observacion' => trim($this->observacion) !== ''
                ? trim($this->observacion)
                : null,
        ];
    }

    private function autorizarOperacion(AdultoMayor $adulto): void
    {
        $usuario = Auth::user();

        abort_unless($usuario, 401);

        abort_unless(
            $usuario->can('salud.ver'),
            403,
            'No cuenta con permiso para acceder al módulo de salud.'
        );

        abort_unless(
            $usuario->can('adultos.ver_expediente'),
            403,
            'No cuenta con permiso para consultar el expediente del residente.'
        );

        abort_unless(
            $usuario->can('signos_vitales.crear'),
            403,
            'No cuenta con permiso para registrar signos vitales.'
        );

        if ($usuario->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente(
                $adulto,
                'signos_vitales.crear',
                $usuario
            );
        }
    }

    private function validarExpedienteModificable(AdultoMayor $adulto): void
    {
        abort_if(
            $adulto->archivado_en !== null,
            409,
            'El expediente está archivado y solo puede consultarse.'
        );

        $estadoInstitucional = strtoupper(trim((string) ($adulto->estado?->estado ?? '')));
        $estadoOperativo = strtoupper(trim((string) ($adulto->estado_operativo ?? '')));

        abort_if(
            in_array($estadoInstitucional, self::ESTADOS_NO_MODIFICABLES, true)
                || in_array($estadoOperativo, self::ESTADOS_NO_MODIFICABLES, true),
            409,
            'El estado actual del residente no permite registrar nuevos signos vitales.'
        );
    }

    private function validarFechaHoraNoFutura(): void
    {
        try {
            $momento = Carbon::createFromFormat(
                'Y-m-d H:i',
                "{$this->fecha} {$this->hora}",
                config('app.timezone')
            );
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'hora' => 'La fecha u hora ingresada no es válida.',
            ]);
        }

        if ($momento->isFuture()) {
            throw ValidationException::withMessages([
                'hora' => 'La fecha y hora del control no pueden estar en el futuro.',
            ]);
        }
    }

    private function cargarReferenciasAntropometricas(string $codAm): void
    {
        $base = SignosVitalesAdulto::query()
            ->where('cod_am', $codAm)
            ->where('estado', 'VIGENTE');

        $ultimoPeso = (clone $base)
            ->whereNotNull('peso')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->orderByDesc('created_at')
            ->first();

        $ultimaTalla = (clone $base)
            ->whereNotNull('talla')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->orderByDesc('created_at')
            ->first();

        $this->ultimo_peso_conocido = $ultimoPeso?->peso !== null
            ? (string) $ultimoPeso->peso
            : null;

        $this->fecha_ultimo_peso = $ultimoPeso?->fecha?->format('Y-m-d');

        $this->ultima_talla_conocida = $ultimaTalla?->talla !== null
            ? (string) $ultimaTalla->talla
            : null;

        $this->fecha_ultima_talla = $ultimaTalla?->fecha?->format('Y-m-d');
    }

    private function calcularImc(): void
    {
        $peso = $this->aFloatNullable($this->peso);
        $talla = $this->aFloatNullable($this->talla);

        $imc = ValidacionSignosVitalesService::calcularImc($peso, $talla);

        $this->imc = $imc !== null ? (string) $imc : null;
    }

    private function trasladarErroresServicio(ValidationException $e): void
    {
        $mapa = [
            'presion_sistolica' => 'pa_sistolica',
            'presion_diastolica' => 'pa_diastolica',
            'frecuencia_cardiaca' => 'fc',
            'frecuencia_respiratoria' => 'fr',
            'valor_atipico_confirmado' => 'confirmar_presion_atipica',
        ];

        foreach ($e->errors() as $campo => $mensajes) {
            if ($campo === 'presion_arterial') {
                foreach ($mensajes as $mensaje) {
                    $this->addError('pa_sistolica', $mensaje);
                    $this->addError('pa_diastolica', $mensaje);
                }

                continue;
            }

            $campoUI = $mapa[$campo] ?? $campo;

            foreach ($mensajes as $mensaje) {
                $this->addError($campoUI, $mensaje);
            }
        }
    }

    private function normalizarVacio(mixed $valor): mixed
    {
        if (is_string($valor)) {
            $valor = trim($valor);
        }

        return $valor === '' ? null : $valor;
    }

    private function aEnteroNullable(?string $valor): ?int
    {
        $valor = $this->normalizarVacio($valor);

        return $valor === null || !is_numeric($valor)
            ? null
            : (int) $valor;
    }

    private function aFloatNullable(?string $valor): ?float
    {
        $valor = $this->normalizarVacio($valor);

        return $valor === null || !is_numeric($valor)
            ? null
            : (float) $valor;
    }

    private function inicializarFechaHora(): void
    {
        $this->fecha = today()->toDateString();
        $this->hora = now()->format('H:i');
    }

    private function resetForm(): void
    {
        $this->cod_am = null;
        $this->adulto = null;

        $this->inicializarFechaHora();

        $this->pa_sistolica = null;
        $this->pa_diastolica = null;
        $this->fc = null;
        $this->fr = null;
        $this->temperatura = null;
        $this->saturacion = null;
        $this->glucosa = null;
        $this->peso = null;
        $this->talla = null;
        $this->imc = null;
        $this->dolor = null;
        $this->posicion = null;
        $this->usa_oxigeno = false;
        $this->observacion = '';
        $this->confirmar_presion_atipica = false;

        $this->presion_incompleta = false;
        $this->presion_atipica = false;
        $this->tieneMedicion = false;
        $this->puedeGuardar = false;

        $this->ultimo_peso_conocido = null;
        $this->fecha_ultimo_peso = null;
        $this->ultima_talla_conocida = null;
        $this->fecha_ultima_talla = null;

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.clinica.registro-signos-vitales-modal');
    }
}
