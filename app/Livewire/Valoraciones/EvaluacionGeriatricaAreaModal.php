<?php

namespace App\Livewire\Valoraciones;

use App\Models\AdultoMayor;
use App\Models\AreaGeriatrica;
use App\Models\EvaluacionGeriatrica;
use App\Models\InstrumentoGeriatrico;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class EvaluacionGeriatricaAreaModal extends Component
{
    /**
     * Este modal es "por área": siempre debe abrirse con un cod_area válido.
     * Si además llega cod_am desde una ficha clínica, el residente queda fijado
     * para evitar selecciones accidentales y reducir carga cognitiva.
     */
    private const ESTADOS_NO_EDITABLES = [
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

    private const TIPOS_CON_PUNTAJE = [
        'CUANTITATIVO',
        'MIXTO',
        'TIEMPO',
    ];

    private const TIPOS_SIN_PUNTAJE = [
        'CUALITATIVO',
        'FRACCION_VISUAL',
    ];

    private const NIVELES_ALERTA = [
        'NORMAL',
        'PREVENTIVO',
        'CRITICO',
    ];

    public bool $mostrar = false;

    public ?string $cod_am = null;
    public ?string $cod_area = null;
    public string $nombreArea = '';

    /**
     * Estado UX: cuando el modal se abre desde la ficha de un residente,
     * no tiene sentido permitir cambiar de persona dentro del formulario.
     */
    public bool $pacienteFijado = false;
    public bool $areaFijada = true;

    public $adultoSeleccionado = null;

    public ?string $cod_instrumento = null;
    public string $fecha_eval = '';

    /**
     * Se conserva por compatibilidad con el Blade actual, pero la tabla/modelo
     * de EvaluacionGeriatrica no persiste hora_eval. El Blade optimizado debe
     * omitir este campo para no pedir información que después se pierde.
     */
    public string $hora_eval = '';

    public ?string $puntaje_total = null;
    public ?string $categoria_resultado = null;
    public string $nivel_alerta = 'NORMAL';
    public ?string $nivel_riesgo = null;
    public ?string $observaciones = null;

    /** @var \Illuminate\Support\Collection<int, AdultoMayor>|array */
    public $pacientes = [];

    /** @var \Illuminate\Support\Collection<int, InstrumentoGeriatrico>|array */
    public $instrumentos = [];

    public $instrumentoSeleccionado = null;

    protected $listeners = [
        'evaluacion-geriatrica-area-abrir' => 'abrir',
    ];

    public function mount(): void
    {
        abort_unless(Auth::check(), 401);

        $this->fecha_eval = today()->toDateString();
        $this->hora_eval = now()->format('H:i');
    }

    /**
     * Abre el modal únicamente cuando el contexto clínico es válido.
     */
    public function abrir(array $data): void
    {
        $this->autorizarCreacion();
        $this->resetForm();

        $codArea = trim((string) ($data['cod_area'] ?? ''));
        $codAm = trim((string) ($data['cod_am'] ?? ''));

        if ($codArea === '') {
            $this->notificarError(
                'Área no disponible',
                'No se pudo determinar el área geriátrica de la evaluación.'
            );
            return;
        }

        $area = AreaGeriatrica::query()
            ->whereKey($codArea)
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $area) {
            $this->notificarError(
                'Área no disponible',
                'El área geriátrica no existe o no se encuentra activa.'
            );
            return;
        }

        $this->cod_area = $area->cod_area;
        $this->nombreArea = (string) $area->nombre;
        $this->areaFijada = true;

        $this->instrumentos = InstrumentoGeriatrico::query()
            ->where('cod_area', $area->cod_area)
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        if ($this->instrumentos->isEmpty()) {
            $this->notificarError(
                'Sin instrumentos disponibles',
                'El área seleccionada no tiene instrumentos activos para registrar una evaluación.'
            );
            return;
        }

        if ($codAm !== '') {
            $adulto = $this->buscarAdultoEditable($codAm);

            if (! $adulto) {
                $this->notificarError(
                    'Residente no disponible',
                    'El residente no existe, está archivado o su expediente se encuentra en solo lectura.'
                );
                return;
            }

            $this->cod_am = $adulto->cod_am;
            $this->adultoSeleccionado = $adulto;
            $this->pacienteFijado = true;
            $this->pacientes = collect([$adulto]);
        } else {
            $this->pacienteFijado = false;
            $this->cargarPacientesDisponibles();
        }

        $this->fecha_eval = today()->toDateString();
        $this->hora_eval = now()->format('H:i');
        $this->mostrar = true;
    }

    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    /**
     * Si el modal se utiliza fuera de una ficha clínica y el usuario elige
     * residente, actualizamos únicamente el contexto visual necesario.
     */
    public function updatedCodAm(?string $value): void
    {
        $this->resetValidation('cod_am');

        if ($this->pacienteFijado) {
            return;
        }

        $value = trim((string) $value);

        if ($value === '') {
            $this->adultoSeleccionado = null;
            return;
        }

        $adulto = $this->buscarAdultoEditable($value);

        if (! $adulto) {
            $this->adultoSeleccionado = null;
            $this->addError('cod_am', 'El residente seleccionado no está disponible para nuevas evaluaciones.');
            return;
        }

        $this->adultoSeleccionado = $adulto;
    }

    /**
     * Cambio de instrumento = nuevo contexto de captura. Se limpian los
     * resultados dependientes para impedir que un puntaje de una escala termine
     * guardándose accidentalmente en otra.
     */
    public function updatedCodInstrumento(?string $value): void
    {
        $this->resetValidation([
            'cod_instrumento',
            'puntaje_total',
            'categoria_resultado',
            'nivel_riesgo',
        ]);

        $this->puntaje_total = null;
        $this->categoria_resultado = null;
        $this->nivel_riesgo = null;
        $this->nivel_alerta = 'NORMAL';
        $this->instrumentoSeleccionado = null;

        $value = trim((string) $value);

        if ($value === '' || ! $this->cod_area) {
            return;
        }

        $instrumento = InstrumentoGeriatrico::query()
            ->whereKey($value)
            ->where('cod_area', $this->cod_area)
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $instrumento) {
            $this->cod_instrumento = null;
            $this->addError(
                'cod_instrumento',
                'El instrumento seleccionado no pertenece al área o ya no está activo.'
            );
            return;
        }

        $this->instrumentoSeleccionado = $instrumento;
    }

    /**
     * Propiedades calculadas orientadas a una interfaz más clara.
     */
    public function getRequierePuntajeProperty(): bool
    {
        $tipo = strtoupper((string) ($this->instrumentoSeleccionado?->tipo_resultado ?? ''));

        return in_array($tipo, self::TIPOS_CON_PUNTAJE, true);
    }

    public function getPermitePuntajeProperty(): bool
    {
        $tipo = strtoupper((string) ($this->instrumentoSeleccionado?->tipo_resultado ?? ''));

        return $tipo === '' || ! in_array($tipo, self::TIPOS_SIN_PUNTAJE, true);
    }

    public function getRequiereResultadoCualitativoProperty(): bool
    {
        $tipo = strtoupper((string) ($this->instrumentoSeleccionado?->tipo_resultado ?? ''));

        return in_array($tipo, self::TIPOS_SIN_PUNTAJE, true);
    }

    public function getPuntajeMaximoProperty(): ?float
    {
        if (! $this->instrumentoSeleccionado?->puntaje_maximo) {
            return null;
        }

        return (float) $this->instrumentoSeleccionado->puntaje_maximo;
    }

    public function getPuedeGuardarProperty(): bool
    {
        if (! $this->mostrar || ! $this->cod_am || ! $this->cod_area || ! $this->cod_instrumento) {
            return false;
        }

        if ($this->requierePuntaje && ($this->puntaje_total === null || $this->puntaje_total === '')) {
            return false;
        }

        if ($this->requiereResultadoCualitativo && blank($this->categoria_resultado)) {
            return false;
        }

        return true;
    }

    protected function rules(): array
    {
        $tipo = strtoupper((string) ($this->instrumentoSeleccionado?->tipo_resultado ?? ''));
        $puntajeMaximo = $this->instrumentoSeleccionado?->puntaje_maximo;

        $rules = [
            'cod_am' => [
                'required',
                'string',
                Rule::exists('adulto_mayor', 'cod_am')
                    ->where(fn($query) => $query->whereNull('archivado_en')),
            ],
            'cod_area' => [
                'required',
                'string',
                Rule::exists('areas_geriatricas', 'cod_area')
                    ->where(fn($query) => $query->where('estado', 'ACTIVO')),
            ],
            'cod_instrumento' => [
                'required',
                'string',
                Rule::exists('instrumentos_geriatricos', 'cod_instrumento')
                    ->where(fn($query) => $query
                        ->where('cod_area', $this->cod_area)
                        ->where('estado', 'ACTIVO')),
            ],
            'fecha_eval' => ['required', 'date', 'before_or_equal:today'],

            // Compatibilidad temporal con el Blade actual. No se persiste.
            'hora_eval' => ['nullable', 'date_format:H:i'],

            'categoria_resultado' => [
                $this->requiereResultadoCualitativo ? 'required' : 'nullable',
                'string',
                'max:150',
            ],
            'nivel_alerta' => ['required', Rule::in(self::NIVELES_ALERTA)],
            'nivel_riesgo' => ['nullable', 'string', 'max:30'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];

        if (in_array($tipo, self::TIPOS_CON_PUNTAJE, true)) {
            $puntajeRules = ['required', 'numeric', 'min:0'];

            if ($tipo !== 'TIEMPO' && $puntajeMaximo !== null) {
                $puntajeRules[] = 'max:' . (float) $puntajeMaximo;
            }

            $rules['puntaje_total'] = $puntajeRules;
        } else {
            $rules['puntaje_total'] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'cod_am.required' => 'Debe seleccionar un residente.',
            'cod_am.exists' => 'El residente seleccionado no está disponible.',
            'cod_area.required' => 'No se pudo determinar el área geriátrica.',
            'cod_area.exists' => 'El área geriátrica no existe o no está activa.',
            'cod_instrumento.required' => 'Seleccione el instrumento que se aplicó.',
            'cod_instrumento.exists' => 'El instrumento no pertenece al área o ya no está activo.',
            'fecha_eval.required' => 'La fecha de evaluación es obligatoria.',
            'fecha_eval.date' => 'Ingrese una fecha de evaluación válida.',
            'fecha_eval.before_or_equal' => 'La fecha de evaluación no puede ser futura.',
            'hora_eval.date_format' => 'La hora debe tener el formato HH:MM.',
            'puntaje_total.required' => 'Ingrese el resultado numérico obtenido con este instrumento.',
            'puntaje_total.numeric' => 'El resultado debe ser un valor numérico.',
            'puntaje_total.min' => 'El resultado no puede ser negativo.',
            'puntaje_total.max' => 'El resultado supera el máximo permitido por el instrumento.',
            'categoria_resultado.required' => 'Registre el resultado cualitativo de la evaluación.',
            'categoria_resultado.max' => 'El resultado cualitativo no puede superar 150 caracteres.',
            'nivel_alerta.required' => 'Seleccione el nivel de alerta.',
            'nivel_alerta.in' => 'El nivel de alerta seleccionado no es válido.',
            'nivel_riesgo.max' => 'El nivel de riesgo no puede superar 30 caracteres.',
            'observaciones.max' => 'Las observaciones no pueden superar 1000 caracteres.',
        ];
    }

    public function guardar(): void
    {
        $this->autorizarCreacion();
        $this->normalizarEntrada();

        // Rehidratar el instrumento en servidor antes de construir reglas dinámicas.
        $this->instrumentoSeleccionado = $this->resolverInstrumentoActual();

        $this->validate();
        $this->validarContextoClinico();

        try {
            DB::transaction(function (): void {
                $adulto = AdultoMayor::query()
                    ->with('estado')
                    ->lockForUpdate()
                    ->findOrFail($this->cod_am);

                $this->asegurarAdultoEditable($adulto);

                $area = AreaGeriatrica::query()
                    ->lockForUpdate()
                    ->whereKey($this->cod_area)
                    ->where('estado', 'ACTIVO')
                    ->first();

                abort_unless($area, 409, 'El área geriátrica dejó de estar disponible.');

                $instrumento = InstrumentoGeriatrico::query()
                    ->lockForUpdate()
                    ->whereKey($this->cod_instrumento)
                    ->where('cod_area', $area->cod_area)
                    ->where('estado', 'ACTIVO')
                    ->first();

                abort_unless($instrumento, 409, 'El instrumento dejó de estar disponible para esta área.');

                EvaluacionGeriatrica::create([
                    'cod_am' => $adulto->cod_am,
                    'cod_instrumento' => $instrumento->cod_instrumento,
                    'registrado_por' => Auth::user()->cod_usu,
                    'evaluador_id' => Auth::user()->cod_usu,
                    'fecha_eval' => $this->fecha_eval,
                    'puntaje' => $this->puntaje_total,
                    'puntaje_total' => $this->puntaje_total,
                    'resultado_cualitativo' => $this->categoria_resultado,
                    'categoria_resultado' => $this->categoria_resultado,
                    'nivel_alerta' => $this->nivel_alerta,
                    'nivel_riesgo' => $this->nivel_riesgo,
                    'observaciones' => $this->observaciones,
                    'estado' => 'COMPLETADA',
                    'estado_eval' => 'COMPLETADA',
                ]);
            });

            $this->mostrar = false;
            $this->dispatch('evaluacion-geriatrica-guardada');
            $this->dispatch('swal:alert', [
                'type' => 'success',
                'title' => 'Evaluación registrada',
                'message' => 'La evaluación geriátrica se guardó correctamente.',
            ]);
            $this->resetForm();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            $this->dispatch('swal:alert', [
                'type' => 'error',
                'title' => 'No se pudo guardar',
                'message' => 'Ocurrió un problema al registrar la evaluación. Verifique los datos e intente nuevamente.',
            ]);
        }
    }

    private function autorizarCreacion(): void
    {
        abort_unless(Auth::check(), 401);

        $user = Auth::user();

        abort_unless(
            $user->can('evaluaciones.crear'),
            403,
            'No cuenta con permiso para registrar evaluaciones geriátricas.'
        );

        abort_unless(
            $user->can('adultos.ver_expediente'),
            403,
            'No cuenta con permiso para acceder al expediente del residente.'
        );
    }

    private function cargarPacientesDisponibles(): void
    {
        $this->pacientes = AdultoMayor::query()
            ->with('estado')
            ->whereNull('archivado_en')
            ->where(function ($query) {
                $query->whereNull('estado_operativo')
                    ->orWhereNotIn('estado_operativo', self::ESTADOS_NO_EDITABLES);
            })
            ->where(function ($query) {
                $query->whereDoesntHave('estado')
                    ->orWhereHas('estado', fn($estado) => $estado->whereNotIn('estado', self::ESTADOS_NO_EDITABLES));
            })
            ->orderBy('nombres')
            ->orderBy('ap_paterno')
            ->get([
                'cod_am',
                'nombres',
                'ap_paterno',
                'ap_materno',
                'ci',
                'fecha_nac',
                'cod_est_adul',
                'estado_operativo',
                'archivado_en',
            ]);
    }

    private function buscarAdultoEditable(string $codAm): ?AdultoMayor
    {
        $adulto = AdultoMayor::query()
            ->with('estado')
            ->whereNull('archivado_en')
            ->find($codAm);

        if (! $adulto) {
            return null;
        }

        try {
            $this->asegurarAdultoEditable($adulto);
            return $adulto;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException) {
            return null;
        }
    }

    private function asegurarAdultoEditable(AdultoMayor $adulto): void
    {
        abort_if($adulto->archivado_en !== null, 409, 'El expediente se encuentra archivado.');

        $estadoCatalogo = strtoupper((string) ($adulto->estado?->estado ?? ''));
        $estadoOperativo = strtoupper((string) ($adulto->estado_operativo ?? ''));

        abort_if(
            in_array($estadoCatalogo, self::ESTADOS_NO_EDITABLES, true)
                || in_array($estadoOperativo, self::ESTADOS_NO_EDITABLES, true),
            409,
            'El expediente se encuentra en modo de solo lectura.'
        );
    }

    private function resolverInstrumentoActual(): ?InstrumentoGeriatrico
    {
        if (! $this->cod_instrumento || ! $this->cod_area) {
            return null;
        }

        return InstrumentoGeriatrico::query()
            ->whereKey($this->cod_instrumento)
            ->where('cod_area', $this->cod_area)
            ->where('estado', 'ACTIVO')
            ->first();
    }

    private function validarContextoClinico(): void
    {
        $adulto = AdultoMayor::query()
            ->with('estado')
            ->whereNull('archivado_en')
            ->find($this->cod_am);

        if (! $adulto) {
            throw ValidationException::withMessages([
                'cod_am' => 'El residente ya no está disponible para registrar esta evaluación.',
            ]);
        }

        try {
            $this->asegurarAdultoEditable($adulto);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException) {
            throw ValidationException::withMessages([
                'cod_am' => 'El expediente del residente se encuentra en modo de solo lectura.',
            ]);
        }

        if (! $this->instrumentoSeleccionado) {
            throw ValidationException::withMessages([
                'cod_instrumento' => 'El instrumento seleccionado ya no está disponible.',
            ]);
        }

        // La hora no se persiste en la estructura actual, pero mientras el Blade
        // legado la exponga impedimos seleccionar un momento futuro de hoy.
        if ($this->fecha_eval === today()->toDateString() && filled($this->hora_eval)) {
            try {
                $fechaHora = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $this->fecha_eval . ' ' . $this->hora_eval,
                    config('app.timezone')
                );

                if ($fechaHora->isFuture()) {
                    throw ValidationException::withMessages([
                        'hora_eval' => 'La hora de evaluación no puede ser futura.',
                    ]);
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable) {
                throw ValidationException::withMessages([
                    'hora_eval' => 'Ingrese una hora de evaluación válida.',
                ]);
            }
        }
    }

    private function normalizarEntrada(): void
    {
        $this->cod_am = $this->normalizarNullable($this->cod_am);
        $this->cod_area = $this->normalizarNullable($this->cod_area);
        $this->cod_instrumento = $this->normalizarNullable($this->cod_instrumento);
        $this->categoria_resultado = $this->normalizarNullable($this->categoria_resultado);
        $this->nivel_riesgo = $this->normalizarNullable($this->nivel_riesgo);
        $this->observaciones = $this->normalizarNullable($this->observaciones);
        $this->puntaje_total = $this->normalizarNullable($this->puntaje_total);

        $this->nivel_alerta = strtoupper(trim($this->nivel_alerta));

        if (! $this->permitePuntaje) {
            $this->puntaje_total = null;
        }
    }

    private function normalizarNullable(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function notificarError(string $titulo, string $mensaje): void
    {
        $this->dispatch('swal:alert', [
            'type' => 'warning',
            'title' => $titulo,
            'message' => $mensaje,
        ]);
    }

    private function resetForm(): void
    {
        $this->cod_am = null;
        $this->cod_area = null;
        $this->nombreArea = '';
        $this->pacienteFijado = false;
        $this->areaFijada = true;
        $this->adultoSeleccionado = null;

        $this->cod_instrumento = null;
        $this->fecha_eval = today()->toDateString();
        $this->hora_eval = now()->format('H:i');
        $this->puntaje_total = null;
        $this->categoria_resultado = null;
        $this->nivel_alerta = 'NORMAL';
        $this->nivel_riesgo = null;
        $this->observaciones = null;

        $this->pacientes = collect();
        $this->instrumentos = collect();
        $this->instrumentoSeleccionado = null;

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.valoraciones.evaluacion-geriatrica-area-modal');
    }
}
