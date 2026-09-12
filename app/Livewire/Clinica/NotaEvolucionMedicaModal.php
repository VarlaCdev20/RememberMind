<?php

namespace App\Livewire\Clinica;

use App\Models\AdultoMayor;
use App\Models\NotaEvolucionMedica;
use App\Services\Clinica\ValidacionSignosVitalesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class NotaEvolucionMedicaModal extends Component
{
    /**
     * Tipos permitidos por el modelo/flujo actual.
     * No se agregan estados ni tipos que no existan en el proyecto.
     */
    private const TIPOS_NOTA = [
        'EVOLUCION',
        'INGRESO',
        'EGRESO',
        'INTERCONSULTA',
        'URGENCIA',
        'PROCEDIMIENTO',
    ];

    /**
     * Estados en los que el expediente puede consultarse, pero no debe recibir
     * nuevas notas médicas desde este modal.
     */
    private const ESTADOS_SOLO_LECTURA = [
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

    public bool $mostrar = false;
    public ?string $cod_am = null;
    public $adulto = null;

    public string $tipo_nota = 'EVOLUCION';
    public string $fecha = '';
    public string $hora = '';
    public string $subjetivo = '';
    public string $objetivo = '';
    public string $valoracion = '';
    public string $plan = '';
    public string $observaciones = '';

    /**
     * Signos vitales opcionales incorporados a la nota.
     * Son una instantánea clínica dentro de NotaEvolucionMedica; este modal no
     * crea por sí mismo un registro separado en signos_vitales_adulto.
     */
    public bool $incluirSignos = false;
    public ?string $pa_sistolica = null;
    public ?string $pa_diastolica = null;
    public ?string $fc = null;
    public ?string $fr = null;
    public ?string $temperatura = null;
    public ?string $saturacion = null;
    public ?string $glucosa = null;
    public ?string $peso = null;

    protected $listeners = [
        'abrir-nota-evolucion' => 'abrir',
    ];

    public function mount(): void
    {
        $this->fecha = today()->toDateString();
        $this->hora = now()->format('H:i');
    }

    /**
     * Abre el modal únicamente después de comprobar usuario, permisos,
     * existencia del residente y que el expediente admita escritura.
     */
    public function abrir(string $cod_am): void
    {
        $codAm = trim($cod_am);

        abort_unless($codAm !== '' && strlen($codAm) <= 50, 404);

        $adulto = $this->autorizarEscritura($codAm);

        $this->resetForm();
        $this->cod_am = $adulto->cod_am;
        $this->adulto = $adulto;
        $this->fecha = today()->toDateString();
        $this->hora = now()->format('H:i');
        $this->mostrar = true;
    }

    public function cerrar(): void
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    /**
     * Regla única para el formulario. Los signos reutilizan los límites
     * técnicos centralizados en ValidacionSignosVitalesService para evitar
     * tener criterios distintos en cada módulo.
     */
    protected function rules(): array
    {
        $rules = [
            'cod_am' => ['required', 'string', 'max:50', 'exists:adulto_mayor,cod_am'],
            'tipo_nota' => ['required', 'string', 'in:' . implode(',', self::TIPOS_NOTA)],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'hora' => ['nullable', 'date_format:H:i'],
            'subjetivo' => ['nullable', 'string', 'max:2000'],
            'objetivo' => ['nullable', 'string', 'max:2000'],
            'valoracion' => ['required', 'string', 'min:10', 'max:3000'],
            'plan' => ['required', 'string', 'min:5', 'max:3000'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'incluirSignos' => ['boolean'],
        ];

        if ($this->incluirSignos) {
            $reglasSignos = ValidacionSignosVitalesService::reglas();

            $rules['pa_sistolica'] = $reglasSignos['presion_sistolica'];
            $rules['pa_diastolica'] = $reglasSignos['presion_diastolica'];
            $rules['fc'] = $reglasSignos['frecuencia_cardiaca'];
            $rules['fr'] = $reglasSignos['frecuencia_respiratoria'];
            $rules['temperatura'] = $reglasSignos['temperatura'];
            $rules['saturacion'] = $reglasSignos['saturacion'];
            $rules['glucosa'] = $reglasSignos['glucosa'];
            $rules['peso'] = $reglasSignos['peso'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'cod_am.required' => 'No se ha identificado al residente.',
            'cod_am.exists' => 'El residente seleccionado ya no existe.',
            'tipo_nota.required' => 'Debe seleccionar el tipo de nota.',
            'tipo_nota.in' => 'El tipo de nota seleccionado no es válido.',
            'fecha.required' => 'La fecha de la nota es obligatoria.',
            'fecha.date' => 'La fecha indicada no es válida.',
            'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            'hora.date_format' => 'La hora debe tener el formato HH:MM.',
            'subjetivo.max' => 'El apartado subjetivo no puede superar 2000 caracteres.',
            'objetivo.max' => 'El apartado objetivo no puede superar 2000 caracteres.',
            'valoracion.required' => 'La valoración o impresión clínica es obligatoria.',
            'valoracion.min' => 'La valoración debe tener al menos 10 caracteres.',
            'valoracion.max' => 'La valoración no puede superar 3000 caracteres.',
            'plan.required' => 'El plan es obligatorio.',
            'plan.min' => 'El plan debe tener al menos 5 caracteres.',
            'plan.max' => 'El plan no puede superar 3000 caracteres.',
            'observaciones.max' => 'Las observaciones no pueden superar 1000 caracteres.',

            // Signos vitales: mensajes consistentes con el validador clínico central.
            'pa_sistolica.integer' => 'La presión sistólica debe ser un número entero.',
            'pa_diastolica.integer' => 'La presión diastólica debe ser un número entero.',
            'fc.integer' => 'La frecuencia cardíaca debe ser un número entero.',
            'fr.integer' => 'La frecuencia respiratoria debe ser un número entero.',
            'temperatura.numeric' => 'La temperatura debe ser numérica.',
            'saturacion.integer' => 'La saturación debe ser un número entero.',
            'glucosa.numeric' => 'La glucosa debe ser numérica.',
            'peso.numeric' => 'El peso debe ser numérico.',

            'pa_sistolica.min' => 'La presión sistólica debe ser de al menos ' . ValidacionSignosVitalesService::PAS_MIN . ' mmHg.',
            'pa_sistolica.max' => 'La presión sistólica no puede exceder ' . ValidacionSignosVitalesService::PAS_MAX . ' mmHg.',
            'pa_diastolica.min' => 'La presión diastólica debe ser de al menos ' . ValidacionSignosVitalesService::PAD_MIN . ' mmHg.',
            'pa_diastolica.max' => 'La presión diastólica no puede exceder ' . ValidacionSignosVitalesService::PAD_MAX . ' mmHg.',
            'fc.min' => 'La frecuencia cardíaca debe ser de al menos ' . ValidacionSignosVitalesService::FC_MIN . ' bpm.',
            'fc.max' => 'La frecuencia cardíaca no puede exceder ' . ValidacionSignosVitalesService::FC_MAX . ' bpm.',
            'fr.min' => 'La frecuencia respiratoria debe ser de al menos ' . ValidacionSignosVitalesService::FR_MIN . ' rpm.',
            'fr.max' => 'La frecuencia respiratoria no puede exceder ' . ValidacionSignosVitalesService::FR_MAX . ' rpm.',
            'temperatura.min' => 'La temperatura debe ser de al menos ' . ValidacionSignosVitalesService::TEMP_MIN . ' °C.',
            'temperatura.max' => 'La temperatura no puede exceder ' . ValidacionSignosVitalesService::TEMP_MAX . ' °C.',
            'saturacion.min' => 'La saturación de oxígeno debe ser de al menos ' . ValidacionSignosVitalesService::SPO2_MIN . '%.',
            'saturacion.max' => 'La saturación de oxígeno no puede exceder ' . ValidacionSignosVitalesService::SPO2_MAX . '%.',
            'glucosa.min' => 'La glucosa debe ser de al menos ' . ValidacionSignosVitalesService::GLUCOSA_MIN . ' mg/dL.',
            'peso.min' => 'El peso debe ser de al menos ' . ValidacionSignosVitalesService::PESO_MIN . ' kg.',
            'peso.max' => 'El peso no puede exceder ' . ValidacionSignosVitalesService::PESO_MAX . ' kg.',
        ];
    }

    /**
     * Guarda una nota médica con autorización y validación nuevamente en el
     * servidor. No se confía en que el modal haya sido abierto previamente de
     * forma legítima, ni en botones deshabilitados del navegador.
     */
    public function guardar(): void
    {
        abort_unless($this->mostrar, 409, 'El formulario de nota médica no se encuentra activo.');

        $codAm = trim((string) $this->cod_am);
        $adulto = $this->autorizarEscritura($codAm);

        // Capturar signos dentro de una nota también exige permiso de captura.
        if ($this->incluirSignos) {
            abort_unless(
                Auth::user()?->can('signos_vitales.crear'),
                403,
                'No cuenta con permiso para registrar mediciones de signos vitales.'
            );
        }

        $this->normalizarFormulario();

        $validator = Validator::make(
            $this->datosParaValidar(),
            $this->rules(),
            $this->messages()
        );

        $validator->after(function ($validator): void {
            $this->validarFechaHoraNoFutura($validator);

            if ($this->incluirSignos) {
                $this->validarIntegridadSignos($validator);
            }
        });

        $validated = $validator->validate();

        try {
            $nota = DB::transaction(function () use ($adulto, $validated) {
                /**
                 * Se vuelve a bloquear y validar al residente dentro de la
                 * transacción para evitar guardar si su estado cambió entre la
                 * apertura del modal y el INSERT.
                 */
                $adultoBloqueado = AdultoMayor::query()
                    ->with('estado')
                    ->where('cod_am', $adulto->cod_am)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->validarExpedienteEscribible($adultoBloqueado);

                return NotaEvolucionMedica::create([
                    'cod_am' => $adultoBloqueado->cod_am,
                    'tipo_nota' => $validated['tipo_nota'],
                    'fecha' => $validated['fecha'],
                    'hora' => $validated['hora'] ?: null,
                    'subjetivo' => $this->valorONull($validated['subjetivo'] ?? null),
                    'objetivo' => $this->valorONull($validated['objetivo'] ?? null),
                    'valoracion' => trim($validated['valoracion']),
                    'plan' => trim($validated['plan']),
                    'observaciones' => $this->valorONull($validated['observaciones'] ?? null),

                    'pa_sistolica' => $this->incluirSignos ? $this->valorONull($validated['pa_sistolica'] ?? null) : null,
                    'pa_diastolica' => $this->incluirSignos ? $this->valorONull($validated['pa_diastolica'] ?? null) : null,
                    'fc' => $this->incluirSignos ? $this->valorONull($validated['fc'] ?? null) : null,
                    'fr' => $this->incluirSignos ? $this->valorONull($validated['fr'] ?? null) : null,
                    'temperatura' => $this->incluirSignos ? $this->valorONull($validated['temperatura'] ?? null) : null,
                    'saturacion' => $this->incluirSignos ? $this->valorONull($validated['saturacion'] ?? null) : null,
                    'glucosa' => $this->incluirSignos ? $this->valorONull($validated['glucosa'] ?? null) : null,
                    'peso' => $this->incluirSignos ? $this->valorONull($validated['peso'] ?? null) : null,

                    'registrado_por' => Auth::user()->cod_usu,
                    'estado' => 'ACTIVO',
                ]);
            });

            $this->cerrar();
            $this->dispatch('nota-evolucion-guardada');
            $this->dispatch('swal:alert', [
                'type' => 'success',
                'title' => 'Consulta registrada',
                'message' => 'La nota médica fue registrada correctamente.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            $this->dispatch('swal:alert', [
                'type' => 'error',
                'title' => 'No se pudo guardar',
                'message' => 'Ocurrió un problema al registrar la nota médica. Intente nuevamente.',
            ]);
        }
    }

    /**
     * Autorización médica de escritura. Se usan permisos existentes del
     * proyecto para evitar introducir un permiso ficticio de "nota médica".
     */
    private function autorizarEscritura(string $codAm): AdultoMayor
    {
        abort_unless(Auth::check(), 401);

        $usuario = Auth::user();

        abort_unless(
            $usuario->can('valoracion_medica.ver')
                && $usuario->can('adultos.ver_expediente')
                && $usuario->can('salud.ver')
                && $usuario->can('atenciones.crear'),
            403,
            'No cuenta con permisos para registrar una consulta o evolución médica.'
        );

        abort_unless($codAm !== '' && strlen($codAm) <= 50, 404);

        $adulto = AdultoMayor::query()
            ->with('estado')
            ->where('cod_am', $codAm)
            ->firstOrFail();

        $this->validarExpedienteEscribible($adulto);

        return $adulto;
    }

    /**
     * La nota no puede escribirse en expedientes archivados o institucionalmente
     * cerrados. Se revisa tanto el catálogo de estado como estado_operativo.
     */
    private function validarExpedienteEscribible(AdultoMayor $adulto): void
    {
        abort_if(
            $adulto->archivado_en !== null,
            409,
            'El expediente está archivado y solo puede consultarse.'
        );

        $estadoInstitucional = strtoupper(trim((string) ($adulto->estado?->estado ?? '')));
        $estadoOperativo = strtoupper(trim((string) ($adulto->estado_operativo ?? '')));

        abort_if(
            in_array($estadoInstitucional, self::ESTADOS_SOLO_LECTURA, true)
                || in_array($estadoOperativo, self::ESTADOS_SOLO_LECTURA, true),
            409,
            'El residente se encuentra en un estado que no admite nuevas notas clínicas.'
        );
    }

    /**
     * Impide registrar una combinación fecha/hora futura cuando la fecha es hoy.
     * La regla before_or_equal:today por sí sola no valida la hora.
     */
    private function validarFechaHoraNoFutura($validator): void
    {
        if ($this->fecha === '' || $this->hora === '') {
            return;
        }

        try {
            $fechaHora = Carbon::createFromFormat('Y-m-d H:i', "{$this->fecha} {$this->hora}");

            if ($fechaHora->isAfter(now())) {
                $validator->errors()->add(
                    'hora',
                    'La fecha y hora de la nota no pueden estar en el futuro.'
                );
            }
        } catch (\Throwable $e) {
            // La regla date/date_format mostrará el error correspondiente.
        }
    }

    /**
     * Validaciones cruzadas que las reglas simples no pueden expresar.
     */
    private function validarIntegridadSignos($validator): void
    {
        $sis = $this->numeroONull($this->pa_sistolica);
        $dia = $this->numeroONull($this->pa_diastolica);

        if (($sis !== null && $dia === null) || ($sis === null && $dia !== null)) {
            $validator->errors()->add(
                'pa_sistolica',
                'La presión arterial requiere registrar tanto la sistólica como la diastólica.'
            );
        }

        $mediciones = [
            $this->pa_sistolica,
            $this->pa_diastolica,
            $this->fc,
            $this->fr,
            $this->temperatura,
            $this->saturacion,
            $this->glucosa,
            $this->peso,
        ];

        $tieneMedicion = collect($mediciones)->contains(
            fn($valor) => $valor !== null && $valor !== ''
        );

        if (!$tieneMedicion) {
            $validator->errors()->add(
                'incluirSignos',
                'Si activa la opción de signos vitales debe registrar al menos una medición.'
            );
        }
    }

    /**
     * Normaliza espacios y vacíos antes de validar/guardar. Si el usuario
     * desactiva los signos luego de haber escrito valores, éstos se limpian para
     * evitar persistencia accidental de datos ocultos en la interfaz.
     */
    private function normalizarFormulario(): void
    {
        $this->cod_am = trim((string) $this->cod_am);
        $this->tipo_nota = strtoupper(trim($this->tipo_nota));
        $this->fecha = trim($this->fecha);
        $this->hora = trim($this->hora);
        $this->subjetivo = trim($this->subjetivo);
        $this->objetivo = trim($this->objetivo);
        $this->valoracion = trim($this->valoracion);
        $this->plan = trim($this->plan);
        $this->observaciones = trim($this->observaciones);

        foreach (
            [
                'pa_sistolica',
                'pa_diastolica',
                'fc',
                'fr',
                'temperatura',
                'saturacion',
                'glucosa',
                'peso',
            ] as $campo
        ) {
            if ($this->{$campo} !== null) {
                $this->{$campo} = trim((string) $this->{$campo});
                if ($this->{$campo} === '') {
                    $this->{$campo} = null;
                }
            }
        }

        if (!$this->incluirSignos) {
            $this->limpiarSignos();
        }
    }

    private function datosParaValidar(): array
    {
        return [
            'cod_am' => $this->cod_am,
            'tipo_nota' => $this->tipo_nota,
            'fecha' => $this->fecha,
            'hora' => $this->hora !== '' ? $this->hora : null,
            'subjetivo' => $this->subjetivo !== '' ? $this->subjetivo : null,
            'objetivo' => $this->objetivo !== '' ? $this->objetivo : null,
            'valoracion' => $this->valoracion,
            'plan' => $this->plan,
            'observaciones' => $this->observaciones !== '' ? $this->observaciones : null,
            'incluirSignos' => $this->incluirSignos,
            'pa_sistolica' => $this->pa_sistolica,
            'pa_diastolica' => $this->pa_diastolica,
            'fc' => $this->fc,
            'fr' => $this->fr,
            'temperatura' => $this->temperatura,
            'saturacion' => $this->saturacion,
            'glucosa' => $this->glucosa,
            'peso' => $this->peso,
        ];
    }

    private function limpiarSignos(): void
    {
        $this->pa_sistolica = null;
        $this->pa_diastolica = null;
        $this->fc = null;
        $this->fr = null;
        $this->temperatura = null;
        $this->saturacion = null;
        $this->glucosa = null;
        $this->peso = null;
    }

    private function valorONull(mixed $valor): mixed
    {
        if ($valor === null) {
            return null;
        }

        if (is_string($valor)) {
            $valor = trim($valor);
            return $valor === '' ? null : $valor;
        }

        return $valor;
    }

    private function numeroONull(?string $valor): ?float
    {
        if ($valor === null || trim($valor) === '' || !is_numeric($valor)) {
            return null;
        }

        return (float) $valor;
    }

    private function resetForm(): void
    {
        $this->cod_am = null;
        $this->adulto = null;
        $this->tipo_nota = 'EVOLUCION';
        $this->fecha = today()->toDateString();
        $this->hora = now()->format('H:i');
        $this->subjetivo = '';
        $this->objetivo = '';
        $this->valoracion = '';
        $this->plan = '';
        $this->observaciones = '';
        $this->incluirSignos = false;
        $this->limpiarSignos();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.clinica.nota-evolucion-medica-modal');
    }
}
