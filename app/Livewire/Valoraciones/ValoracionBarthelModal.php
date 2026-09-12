<?php

namespace App\Livewire\Valoraciones;

use App\Models\AdultoMayor;
use App\Models\ValoracionFuncionalAdulto;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ValoracionBarthelModal extends Component
{
    /**
     * Estados institucionales que dejan el expediente en modo consulta.
     * No implica cambios de estructura en la base de datos.
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

    /**
     * Puntuaciones admitidas por cada ítem según la configuración actual
     * del Índice de Barthel utilizada por el proyecto.
     */
    private const ITEMS_BARTHEL = [
        'alimentacion' => [0, 5, 10],
        'bano' => [0, 5],
        'aseo_personal' => [0, 5],
        'vestido' => [0, 5, 10],
        'control_intestinal' => [0, 5, 10],
        'control_vesical' => [0, 5, 10],
        'uso_retrete' => [0, 5, 10],
        'traslados' => [0, 5, 10, 15],
        'deambulacion' => [0, 5, 10, 15],
        'escaleras' => [0, 5, 10],
    ];

    private const ETIQUETAS_ITEMS = [
        'alimentacion' => 'Alimentación',
        'bano' => 'Baño / ducha',
        'aseo_personal' => 'Aseo personal',
        'vestido' => 'Vestido / desvestido',
        'control_intestinal' => 'Control intestinal',
        'control_vesical' => 'Control vesical',
        'uso_retrete' => 'Uso del retrete / WC',
        'traslados' => 'Traslados silla-cama',
        'deambulacion' => 'Deambulación',
        'escaleras' => 'Subir y bajar escaleras',
    ];

    public bool $mostrar = false;

    public ?string $cod_am = null;

    public ?AdultoMayor $adulto = null;

    public string $fecha_valoracion = '';

    /**
     * Null significa "sin responder".
     * Esto evita confundir el valor clínico 0 con un campo que nunca fue evaluado.
     */
    public ?int $alimentacion = null;

    public ?int $bano = null;

    public ?int $aseo_personal = null;

    public ?int $vestido = null;

    public ?int $control_intestinal = null;

    public ?int $control_vesical = null;

    public ?int $uso_retrete = null;

    public ?int $traslados = null;

    public ?int $deambulacion = null;

    public ?int $escaleras = null;

    // Ayudas técnicas / observaciones clínicas complementarias.
    public bool $usa_baston = false;

    public bool $usa_andador = false;

    public bool $usa_silla_ruedas = false;

    // Sensoriales / supervisión.
    public bool $baja_vision = false;

    public bool $baja_audicion = false;

    public bool $dificultad_hablar = false;

    public bool $necesita_supervision = false;

    /**
     * Resultado calculado únicamente cuando los 10 ítems están completos.
     */
    public string $nivel_dependencia = '';

    /**
     * El riesgo de caída es una valoración independiente del Barthel.
     * No se infiere automáticamente del puntaje.
     */
    public ?string $riesgo_caida = null;

    public string $observacion = '';

    protected $listeners = [
        'abrir-valoracion-barthel' => 'abrir',
    ];

    public function mount(): void
    {
        $this->fecha_valoracion = today()->toDateString();
    }

    /**
     * Abre el modal únicamente si el usuario, el residente y el expediente
     * cumplen todos los requisitos de escritura.
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
        $this->fecha_valoracion = today()->toDateString();

        $this->mostrar = true;
    }

    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    /**
     * Selección segura de un ítem del Barthel.
     * No acepta nombres de campo ni valores fuera de la configuración.
     */
    public function seleccionarItem(string $campo, int $valor): void
    {
        abort_unless(array_key_exists($campo, self::ITEMS_BARTHEL), 422);
        abort_unless(in_array($valor, self::ITEMS_BARTHEL[$campo], true), 422);

        $this->{$campo} = $valor;

        $this->resetValidation($campo);
        $this->validateOnly($campo);

        $this->recalcular();
        $this->validarCoherenciaMovilidadEnTiempoReal();
    }

    /**
     * Permite corregir una respuesta sin convertir automáticamente el 0
     * en "sin responder".
     */
    public function limpiarItem(string $campo): void
    {
        abort_unless(array_key_exists($campo, self::ITEMS_BARTHEL), 422);

        $this->{$campo} = null;
        $this->nivel_dependencia = '';

        $this->resetValidation($campo);
        $this->validarCoherenciaMovilidadEnTiempoReal();
    }

    /**
     * Validación reactiva de campos que no usan seleccionarItem().
     */
    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['fecha_valoracion', 'riesgo_caida', 'observacion'], true)) {
            $this->resetValidation($propiedad);

            try {
                $this->validateOnly($propiedad);
            } catch (ValidationException $e) {
                // Livewire ya coloca los errores en el error bag.
            }
        }

        if (in_array($propiedad, ['usa_silla_ruedas', 'usa_baston', 'usa_andador'], true)) {
            $this->validarCoherenciaMovilidadEnTiempoReal();
        }

        if (array_key_exists($propiedad, self::ITEMS_BARTHEL)) {
            $this->recalcular();
            $this->validarCoherenciaMovilidadEnTiempoReal();
        }
    }

    public function getTotalBarthelProperty(): int
    {
        return collect(array_keys(self::ITEMS_BARTHEL))
            ->sum(fn(string $campo) => (int) ($this->{$campo} ?? 0));
    }

    public function getItemsRespondidosProperty(): int
    {
        return collect(array_keys(self::ITEMS_BARTHEL))
            ->filter(fn(string $campo) => $this->{$campo} !== null)
            ->count();
    }

    public function getTotalItemsProperty(): int
    {
        return count(self::ITEMS_BARTHEL);
    }

    public function getProgresoEvaluacionProperty(): int
    {
        return (int) round(($this->itemsRespondidos / $this->totalItems) * 100);
    }

    public function getEvaluacionCompletaProperty(): bool
    {
        return $this->itemsRespondidos === $this->totalItems;
    }

    public function getItemsPendientesProperty(): array
    {
        return collect(array_keys(self::ITEMS_BARTHEL))
            ->filter(fn(string $campo) => $this->{$campo} === null)
            ->map(fn(string $campo) => self::ETIQUETAS_ITEMS[$campo])
            ->values()
            ->all();
    }

    /**
     * Mientras la evaluación esté incompleta no se interpreta el puntaje parcial.
     */
    public function getClasificacionBarthelProperty(): string
    {
        if (!$this->evaluacionCompleta) {
            return 'Evaluación incompleta';
        }

        return match (true) {
            $this->totalBarthel >= 100 => 'Independiente',
            $this->totalBarthel >= 91 => 'Dependencia escasa',
            $this->totalBarthel >= 61 => 'Dependencia leve',
            $this->totalBarthel >= 41 => 'Dependencia moderada',
            $this->totalBarthel >= 21 => 'Dependencia severa',
            default => 'Dependencia total',
        };
    }

    public function getPuedeGuardarProperty(): bool
    {
        return $this->cod_am !== null
            && $this->evaluacionCompleta
            && $this->fecha_valoracion !== ''
            && in_array($this->riesgo_caida, ['BAJO', 'MODERADO', 'ALTO'], true)
            && count($this->getErrorBag()->all()) === 0;
    }

    public function getTieneCambiosProperty(): bool
    {
        return $this->itemsRespondidos > 0
            || $this->riesgo_caida !== null
            || trim($this->observacion) !== ''
            || $this->usa_baston
            || $this->usa_andador
            || $this->usa_silla_ruedas
            || $this->baja_vision
            || $this->baja_audicion
            || $this->dificultad_hablar
            || $this->necesita_supervision;
    }

    protected function rules(): array
    {
        return [
            'cod_am' => ['required', 'string', 'exists:adulto_mayor,cod_am'],
            'fecha_valoracion' => ['required', 'date', 'before_or_equal:today'],

            'alimentacion' => ['required', 'integer', 'in:0,5,10'],
            'bano' => ['required', 'integer', 'in:0,5'],
            'aseo_personal' => ['required', 'integer', 'in:0,5'],
            'vestido' => ['required', 'integer', 'in:0,5,10'],
            'control_intestinal' => ['required', 'integer', 'in:0,5,10'],
            'control_vesical' => ['required', 'integer', 'in:0,5,10'],
            'uso_retrete' => ['required', 'integer', 'in:0,5,10'],
            'traslados' => ['required', 'integer', 'in:0,5,10,15'],
            'deambulacion' => ['required', 'integer', 'in:0,5,10,15'],
            'escaleras' => ['required', 'integer', 'in:0,5,10'],

            'usa_baston' => ['boolean'],
            'usa_andador' => ['boolean'],
            'usa_silla_ruedas' => ['boolean'],
            'baja_vision' => ['boolean'],
            'baja_audicion' => ['boolean'],
            'dificultad_hablar' => ['boolean'],
            'necesita_supervision' => ['boolean'],

            'riesgo_caida' => ['required', 'string', 'in:BAJO,MODERADO,ALTO'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected $messages = [
        'cod_am.required' => 'No se pudo identificar al residente.',
        'cod_am.exists' => 'El residente seleccionado ya no existe.',
        'fecha_valoracion.required' => 'La fecha de valoración es obligatoria.',
        'fecha_valoracion.date' => 'La fecha de valoración no es válida.',
        'fecha_valoracion.before_or_equal' => 'La fecha de valoración no puede ser futura.',

        'alimentacion.required' => 'Evalúe la alimentación.',
        'bano.required' => 'Evalúe el baño / ducha.',
        'aseo_personal.required' => 'Evalúe el aseo personal.',
        'vestido.required' => 'Evalúe vestido / desvestido.',
        'control_intestinal.required' => 'Evalúe el control intestinal.',
        'control_vesical.required' => 'Evalúe el control vesical.',
        'uso_retrete.required' => 'Evalúe el uso del retrete.',
        'traslados.required' => 'Evalúe los traslados.',
        'deambulacion.required' => 'Evalúe la deambulación.',
        'escaleras.required' => 'Evalúe el uso de escaleras.',

        'alimentacion.in' => 'Seleccione una opción válida para alimentación.',
        'bano.in' => 'Seleccione una opción válida para baño / ducha.',
        'aseo_personal.in' => 'Seleccione una opción válida para aseo personal.',
        'vestido.in' => 'Seleccione una opción válida para vestido.',
        'control_intestinal.in' => 'Seleccione una opción válida para control intestinal.',
        'control_vesical.in' => 'Seleccione una opción válida para control vesical.',
        'uso_retrete.in' => 'Seleccione una opción válida para uso del retrete.',
        'traslados.in' => 'Seleccione una opción válida para traslados.',
        'deambulacion.in' => 'Seleccione una opción válida para deambulación.',
        'escaleras.in' => 'Seleccione una opción válida para escaleras.',

        'riesgo_caida.required' => 'Seleccione el riesgo de caída valorado.',
        'riesgo_caida.in' => 'Seleccione un nivel válido de riesgo de caída.',
        'observacion.max' => 'Las observaciones no pueden superar los 1000 caracteres.',
    ];

    public function guardar(): void
    {
        $usuario = Auth::user();
        abort_unless($usuario, 401);

        $this->resetValidation();

        $adulto = AdultoMayor::query()
            ->with('estado')
            ->findOrFail($this->cod_am);

        $this->autorizarOperacion($adulto);
        $this->validarExpedienteModificable($adulto);

        $this->recalcular();
        $this->validate();
        $this->validarCoherenciaMovilidad();

        abort_unless($this->evaluacionCompleta, 422, 'La valoración Barthel está incompleta.');

        $totalGuardado = $this->totalBarthel;
        $clasificacionGuardada = $this->clasificacionBarthel;

        try {
            DB::transaction(function () use ($adulto, $usuario, $totalGuardado, $clasificacionGuardada): void {
                /**
                 * Bloquear al residente serializa las valoraciones concurrentes
                 * del mismo expediente, incluso si todavía no existe una valoración vigente.
                 */
                $adultoBloqueado = AdultoMayor::query()
                    ->lockForUpdate()
                    ->findOrFail($adulto->cod_am);

                $adultoBloqueado->load('estado');

                $this->autorizarOperacion($adultoBloqueado);
                $this->validarExpedienteModificable($adultoBloqueado);

                /**
                 * Cualquier valoración funcional vigente anterior pasa a histórica
                 * antes de crear la nueva.
                 */
                $anteriores = ValoracionFuncionalAdulto::query()
                    ->where('cod_am', $adultoBloqueado->cod_am)
                    ->vigente()
                    ->lockForUpdate()
                    ->get();

                foreach ($anteriores as $anterior) {
                    $anterior->update(['estado' => 'HISTORICA']);
                }

                ValoracionFuncionalAdulto::create([
                    'cod_am' => $adultoBloqueado->cod_am,
                    'fecha_valoracion' => $this->fecha_valoracion,

                    'come_solo' => $this->alimentacion === 10,
                    'se_bana_solo' => $this->bano === 5,
                    'se_viste_solo' => $this->vestido === 10,
                    'va_bano_solo' => $this->uso_retrete === 10,
                    'camina_solo' => $this->deambulacion === 15,

                    'usa_baston' => $this->usa_baston,
                    'usa_andador' => $this->usa_andador,
                    'usa_silla_ruedas' => $this->usa_silla_ruedas,

                    'baja_vision' => $this->baja_vision,
                    'baja_audicion' => $this->baja_audicion,
                    'dificultad_hablar' => $this->dificultad_hablar,
                    'necesita_supervision' => $this->necesita_supervision,

                    'nivel_dependencia' => $clasificacionGuardada,
                    'riesgo_caida' => $this->riesgo_caida,
                    'indice_barthel' => $totalGuardado,
                    'estado' => 'VIGENTE',

                    'observacion' => trim($this->observacion) !== ''
                        ? trim($this->observacion)
                        : null,

                    'registrado_por' => $usuario->cod_usu,
                ]);
            });

            /**
             * Guardamos el resultado antes de cerrar, porque cerrar() reinicia
             * todas las propiedades del formulario.
             */
            $mensajeResultado = "Índice de Barthel: {$totalGuardado}/100 — {$clasificacionGuardada}.";

            $this->cerrar();

            $this->dispatch('valoracion-barthel-guardada');

            $this->dispatch('swal:alert', [
                'type' => 'success',
                'title' => 'Barthel registrado',
                'message' => $mensajeResultado,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            $this->addError(
                'general',
                'Ocurrió un problema al registrar la valoración funcional. Verifique los datos e intente nuevamente.'
            );
        }
    }

    /**
     * El Índice de Barthel calcula la dependencia funcional.
     * No modifica ni calcula automáticamente el riesgo de caída.
     */
    private function recalcular(): void
    {
        $this->nivel_dependencia = $this->evaluacionCompleta
            ? $this->clasificacionBarthel
            : '';
    }

    /**
     * Coherencia complementaria:
     * en la configuración actual, deambulación = 5 representa independencia
     * en silla. Por eso se exige registrar el uso de silla de ruedas.
     */
    private function validarCoherenciaMovilidad(): void
    {
        if ($this->deambulacion === 5 && !$this->usa_silla_ruedas) {
            throw ValidationException::withMessages([
                'deambulacion' => 'La opción de 5 puntos en deambulación corresponde a independencia en silla; marque también "Usa silla de ruedas".',
            ]);
        }
    }

    private function validarCoherenciaMovilidadEnTiempoReal(): void
    {
        $this->resetValidation('deambulacion');

        if ($this->deambulacion === 5 && !$this->usa_silla_ruedas) {
            $this->addError(
                'deambulacion',
                'Para esta opción, confirme también el uso de silla de ruedas.'
            );
        }
    }

    /**
     * Médico: permiso funcional sin depender de una asignación de Enfermería.
     * Enfermería: conserva turno, recepción y residente asignado.
     */
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
            $usuario->can('valoracion_funcional.crear'),
            403,
            'No cuenta con permiso para registrar valoraciones funcionales.'
        );

        if ($usuario->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente(
                $adulto,
                'valoracion_funcional.crear',
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
            'El estado actual del residente no permite registrar una nueva valoración funcional.'
        );
    }

    private function resetForm(): void
    {
        $this->cod_am = null;
        $this->adulto = null;

        $this->fecha_valoracion = today()->toDateString();

        $this->alimentacion = null;
        $this->bano = null;
        $this->aseo_personal = null;
        $this->vestido = null;
        $this->control_intestinal = null;
        $this->control_vesical = null;
        $this->uso_retrete = null;
        $this->traslados = null;
        $this->deambulacion = null;
        $this->escaleras = null;

        $this->usa_baston = false;
        $this->usa_andador = false;
        $this->usa_silla_ruedas = false;

        $this->baja_vision = false;
        $this->baja_audicion = false;
        $this->dificultad_hablar = false;
        $this->necesita_supervision = false;

        $this->nivel_dependencia = '';
        $this->riesgo_caida = null;
        $this->observacion = '';

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.valoraciones.valoracion-barthel-modal', [
            'totalBarthel' => $this->totalBarthel,
            'clasificacionBarthel' => $this->clasificacionBarthel,
        ]);
    }
}
