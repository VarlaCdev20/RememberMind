<?php

namespace App\Livewire\Medicacion;

use App\Models\AdultoMayor;
use App\Models\MedicacionAdulto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class MedicacionAdultoModal extends Component
{
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

    private const ESTADOS_MEDICACION = [
        'ACTIVO',
        'PAUSADO',
        'EN REVISION',
        'SUSPENDIDO',
        'FINALIZADO',
    ];

    private const ESTADOS_TERMINALES = [
        'SUSPENDIDO',
        'FINALIZADO',
    ];

    private const ESTADOS_EDITABLES = [
        'ACTIVO',
        'ACTIVA',
        'VIGENTE',
        'PAUSADO',
        'EN REVISION',
    ];

    private const VIAS_ADMINISTRACION = [
        'ORAL',
        'SUBLINGUAL',
        'TOPICA',
        'OFTALMICA',
        'OTICA',
        'NASAL',
        'INHALATORIA',
        'INTRAVENOSA',
        'INTRAMUSCULAR',
        'SUBCUTANEA',
        'RECTAL',
        'OTRA',
    ];

    public bool $showModal = false;
    public bool $isEditing = false;
    public bool $pacienteFijado = false;
    public bool $formularioSucio = false;

    public ?string $cod_med_adulto = null;
    public ?string $cod_am = null;
    public $adulto = null;

    public string $nombre_medicamento = '';
    public string $dosis = '';
    public string $frecuencia = '';
    public bool $es_prn = false;
    public string $condicion_prn = '';
    public ?string $intervalo_horas = null;
    public string $via_administracion = '';
    public string $hora_programada = '';
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public string $medico_indica = '';
    public string $observacion = '';
    public string $estado = 'ACTIVO';

    /**
     * Prevención de duplicados.
     * Son propiedades auxiliares de UI; no se persisten.
     */
    public bool $posibleDuplicado = false;
    public bool $confirmar_duplicado = false;
    public ?string $medicacionDuplicadaResumen = null;

    protected $listeners = [
        'abrirModalMedicacion',
    ];

    public function rules(): array
    {
        return [
            'cod_am' => ['required', 'string', 'exists:adulto_mayor,cod_am'],
            'nombre_medicamento' => ['required', 'string', 'min:2', 'max:100'],
            'dosis' => ['required', 'string', 'min:1', 'max:50'],
            'frecuencia' => ['required', 'string', 'min:2', 'max:100'],
            'es_prn' => ['boolean'],

            'condicion_prn' => [
                Rule::requiredIf(fn() => $this->es_prn),
                'nullable',
                'string',
                'min:5',
                'max:500',
            ],

            'intervalo_horas' => [
                Rule::requiredIf(fn() => $this->es_prn),
                'nullable',
                'integer',
                'min:1',
                'max:24',
            ],

            'via_administracion' => [
                'required',
                'string',
                Rule::in(self::VIAS_ADMINISTRACION),
            ],

            'hora_programada' => [
                Rule::requiredIf(fn() => !$this->es_prn),
                'nullable',
                'date_format:H:i',
            ],

            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],

            'medico_indica' => ['nullable', 'string', 'max:100'],
            'observacion' => ['nullable', 'string', 'max:255'],

            'estado' => [
                'required',
                'string',
                Rule::in(self::ESTADOS_MEDICACION),
            ],

            'confirmar_duplicado' => $this->posibleDuplicado
                ? ['required', 'accepted']
                : ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'cod_am.required' => 'Debe identificar al residente.',
            'cod_am.exists' => 'El residente seleccionado ya no existe.',

            'nombre_medicamento.required' => 'El medicamento es obligatorio.',
            'nombre_medicamento.min' => 'Ingrese al menos 2 caracteres para el medicamento.',
            'nombre_medicamento.max' => 'El medicamento no puede superar los 100 caracteres.',

            'dosis.required' => 'La dosis es obligatoria.',
            'dosis.max' => 'La dosis no puede superar los 50 caracteres.',

            'frecuencia.required' => 'La frecuencia es obligatoria.',
            'frecuencia.min' => 'Describa la frecuencia con mayor precisión.',
            'frecuencia.max' => 'La frecuencia no puede superar los 100 caracteres.',

            'via_administracion.required' => 'Seleccione la vía de administración.',
            'via_administracion.in' => 'Seleccione una vía de administración válida.',

            'hora_programada.required' => 'La hora programada es obligatoria para una orden fija.',
            'hora_programada.date_format' => 'La hora programada debe tener el formato HH:MM.',

            'condicion_prn.required' => 'La medicación PRN requiere una condición clínica explícita.',
            'condicion_prn.required_if' => 'La medicación PRN requiere una condición clínica explícita.',
            'condicion_prn.min' => 'Describa la condición PRN con al menos 5 caracteres.',
            'condicion_prn.max' => 'La condición PRN no puede superar los 500 caracteres.',

            'intervalo_horas.required' => 'Indique el intervalo mínimo entre dosis PRN.',
            'intervalo_horas.required_if' => 'Indique el intervalo mínimo entre dosis PRN.',
            'intervalo_horas.integer' => 'El intervalo debe expresarse en horas enteras.',
            'intervalo_horas.min' => 'El intervalo mínimo es de 1 hora.',
            'intervalo_horas.max' => 'El intervalo no puede superar 24 horas.',

            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio no es válida.',
            'fecha_fin.date' => 'La fecha de fin no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',

            'medico_indica.max' => 'El nombre del médico prescriptor no puede superar 100 caracteres.',
            'observacion.max' => 'Las indicaciones especiales no pueden superar 255 caracteres.',

            'estado.required' => 'El estado del tratamiento es obligatorio.',
            'estado.in' => 'El estado del tratamiento seleccionado no es válido.',

            'confirmar_duplicado.required' => 'Debe revisar y confirmar la posible duplicidad antes de guardar.',
            'confirmar_duplicado.accepted' => 'Debe confirmar que revisó la posible duplicidad.',
        ];
    }

    public function abrirModalMedicacion($cod_am = null, $id_med = null): void
    {
        $this->autorizarRolMedico();

        $this->resetFormulario();

        $usuario = Auth::user();
        abort_unless($usuario, 401);

        if ($id_med) {
            $medicacion = MedicacionAdulto::query()->findOrFail($id_med);
            $cod_am = $cod_am ?: $medicacion->cod_am;

            abort_unless(
                (string) $medicacion->cod_am === (string) $cod_am,
                404,
                'La medicación no pertenece al residente indicado.'
            );

            $this->autorizarPermiso('medicacion.editar');

            abort_unless(
                in_array((string) $medicacion->estado, self::ESTADOS_EDITABLES, true),
                409,
                'Una orden suspendida, finalizada o archivada ya no puede editarse.'
            );

            $adulto = AdultoMayor::query()
                ->with('estado')
                ->findOrFail($cod_am);

            $this->autorizarContextoResidente($adulto);

            $this->isEditing = true;
            $this->pacienteFijado = true;
            $this->cod_med_adulto = $medicacion->cod_med_adulto;
            $this->cod_am = $adulto->cod_am;
            $this->adulto = $adulto;

            $this->cargarDatosDesdeModelo($medicacion);
            $this->detectarPosibleDuplicado();

            $this->formularioSucio = false;
            $this->showModal = true;

            return;
        }

        $this->autorizarPermiso('medicacion.crear');

        $this->isEditing = false;
        $this->estado = 'ACTIVO';
        $this->fecha_inicio = today()->toDateString();

        if ($cod_am) {
            $adulto = AdultoMayor::query()
                ->with('estado')
                ->findOrFail($cod_am);

            $this->autorizarContextoResidente($adulto);

            $this->pacienteFijado = true;
            $this->cod_am = $adulto->cod_am;
            $this->adulto = $adulto;
        } else {
            $this->pacienteFijado = false;
        }

        $this->formularioSucio = false;
        $this->showModal = true;
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->resetFormulario();
    }

    public function updated(string $propiedad): void
    {
        if (!$this->showModal) {
            return;
        }

        $this->formularioSucio = true;

        if ($propiedad === 'cod_am') {
            $this->actualizarResidenteSeleccionado();
        }

        if ($propiedad === 'es_prn') {
            $this->adaptarModoAdministracion();
        }

        if ($propiedad === 'nombre_medicamento') {
            $this->confirmar_duplicado = false;
            $this->detectarPosibleDuplicado();
        }

        if ($propiedad === 'fecha_inicio') {
            $this->resetValidation('fecha_fin');
        }

        $camposValidables = [
            'cod_am',
            'nombre_medicamento',
            'dosis',
            'frecuencia',
            'es_prn',
            'condicion_prn',
            'intervalo_horas',
            'via_administracion',
            'hora_programada',
            'fecha_inicio',
            'fecha_fin',
            'medico_indica',
            'observacion',
            'estado',
            'confirmar_duplicado',
        ];

        if (in_array($propiedad, $camposValidables, true)) {
            $this->resetValidation($propiedad);

            try {
                $this->validateOnly($propiedad);
            } catch (ValidationException) {
                // Livewire conserva los mensajes en el error bag.
            }
        }

        /**
         * Al cambiar PRN cambian también reglas de hora, condición e intervalo.
         */
        if ($propiedad === 'es_prn') {
            foreach (['hora_programada', 'condicion_prn', 'intervalo_horas'] as $campo) {
                $this->resetValidation($campo);

                try {
                    $this->validateOnly($campo);
                } catch (ValidationException) {
                    // La UI muestra inmediatamente el dato requerido.
                }
            }
        }
    }

    public function getPuedeGuardarProperty(): bool
    {
        if (!$this->cod_am) {
            return false;
        }

        if (
            trim($this->nombre_medicamento) === ''
            || trim($this->dosis) === ''
            || trim($this->frecuencia) === ''
            || trim($this->via_administracion) === ''
            || trim($this->fecha_inicio) === ''
        ) {
            return false;
        }

        if ($this->es_prn) {
            if (mb_strlen(trim($this->condicion_prn)) < 5) {
                return false;
            }

            if (
                !$this->intervalo_horas
                || !is_numeric($this->intervalo_horas)
                || (int) $this->intervalo_horas < 1
                || (int) $this->intervalo_horas > 24
            ) {
                return false;
            }
        } elseif (trim($this->hora_programada) === '') {
            return false;
        }

        if ($this->posibleDuplicado && !$this->confirmar_duplicado) {
            return false;
        }

        return count($this->getErrorBag()->all()) === 0;
    }

    public function getTipoOrdenProperty(): string
    {
        return $this->es_prn ? 'PRN / según necesidad' : 'Programada';
    }

    public function getResumenHorarioProperty(): string
    {
        if ($this->es_prn) {
            if ($this->intervalo_horas) {
                return "PRN · mínimo cada {$this->intervalo_horas} h";
            }

            return 'PRN · intervalo pendiente';
        }

        return $this->hora_programada !== ''
            ? "Hora {$this->hora_programada}"
            : 'Hora pendiente';
    }

    public function getTieneCambiosProperty(): bool
    {
        return $this->formularioSucio;
    }

    public function guardar(): void
    {
        $this->autorizarRolMedico();

        $usuario = Auth::user();
        abort_unless($usuario, 401);

        $permiso = $this->isEditing
            ? 'medicacion.editar'
            : 'medicacion.crear';

        $this->autorizarPermiso($permiso);

        $this->resetValidation();

        $adulto = AdultoMayor::query()
            ->with('estado')
            ->findOrFail($this->cod_am);

        $this->autorizarContextoResidente($adulto);

        $this->detectarPosibleDuplicado();
        $this->validate();

        $datos = $this->datosNormalizados();

        try {
            DB::transaction(function () use ($adulto, $usuario, $datos): void {
                $adultoBloqueado = AdultoMayor::query()
                    ->lockForUpdate()
                    ->findOrFail($adulto->cod_am);

                $adultoBloqueado->load('estado');

                $this->autorizarContextoResidente($adultoBloqueado);

                /**
                 * Recalcular duplicidad dentro de la transacción evita que aparezca
                 * otra orden similar entre la apertura y el guardado.
                 */
                $duplicadoActual = $this->buscarPosibleDuplicado();

                if ($duplicadoActual && !$this->confirmar_duplicado) {
                    throw ValidationException::withMessages([
                        'confirmar_duplicado' => 'Existe una orden similar. Revísela y confirme antes de continuar.',
                    ]);
                }

                if ($this->isEditing) {
                    $medicacion = MedicacionAdulto::query()
                        ->where('cod_am', $adultoBloqueado->cod_am)
                        ->lockForUpdate()
                        ->findOrFail($this->cod_med_adulto);

                    abort_unless(
                        in_array((string) $medicacion->estado, self::ESTADOS_EDITABLES, true),
                        409,
                        'La orden cambió de estado y ya no puede modificarse.'
                    );

                    $this->validarTransicionEstado($medicacion->estado);

                    $medicacion->update($datos);

                    return;
                }

                MedicacionAdulto::create($datos + [
                    'cod_am' => $adultoBloqueado->cod_am,
                    'registrado_por' => $usuario->cod_usu,
                ]);
            });

            $mensaje = $this->isEditing
                ? 'La prescripción fue actualizada correctamente.'
                : 'La prescripción fue registrada correctamente.';

            $this->cerrarModal();

            $this->dispatch('medicacion-actualizada');

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Prescripción guardada',
                'text' => $mensaje,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            $this->addError(
                'general',
                'Ocurrió un problema al guardar la prescripción. Verifique los datos e intente nuevamente.'
            );
        }
    }

    /**
     * Mantiene compatibilidad con paneles existentes.
     * Los estados terminales usan el permiso específico de suspensión.
     */
    public function cambiarEstado($id, $estado): void
    {
        $this->autorizarRolMedico();

        validator(
            ['estado' => $estado],
            ['estado' => ['required', Rule::in(self::ESTADOS_MEDICACION)]],
            ['estado.in' => 'El estado solicitado no es válido.']
        )->validate();

        $permiso = in_array($estado, self::ESTADOS_TERMINALES, true)
            ? 'medicacion.suspender'
            : 'medicacion.editar';

        $this->autorizarPermiso($permiso);

        try {
            DB::transaction(function () use ($id, $estado): void {
                $medicacion = MedicacionAdulto::query()
                    ->lockForUpdate()
                    ->findOrFail($id);

                abort_unless(
                    in_array((string) $medicacion->estado, self::ESTADOS_EDITABLES, true),
                    409,
                    'Una orden suspendida o finalizada no puede cambiar de estado desde este flujo.'
                );

                $adulto = AdultoMayor::query()
                    ->with('estado')
                    ->lockForUpdate()
                    ->findOrFail($medicacion->cod_am);

                $this->autorizarContextoResidente($adulto);

                if ($medicacion->estado === $estado) {
                    return;
                }

                $medicacion->estado = $estado;
                $medicacion->save();
            });

            $this->dispatch('medicacion-actualizada');

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Estado actualizado',
                'text' => "La medicación ahora está {$estado}.",
            ]);
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo actualizar',
                'text' => 'Ocurrió un problema al actualizar el estado de la medicación.',
            ]);
        }
    }

    private function cargarDatosDesdeModelo(MedicacionAdulto $med): void
    {
        $this->nombre_medicamento = (string) ($med->nombre_medicamento ?? '');
        $this->dosis = (string) ($med->dosis ?? '');
        $this->frecuencia = (string) ($med->frecuencia ?? '');

        $this->es_prn = (bool) $med->es_prn;
        $this->condicion_prn = (string) ($med->condicion_prn ?? '');
        $this->intervalo_horas = $med->intervalo_horas !== null
            ? (string) $med->intervalo_horas
            : null;

        $this->via_administracion = (string) ($med->via_administracion ?? '');

        $this->hora_programada = $med->hora_programada
            ? $med->hora_programada->format('H:i')
            : '';

        $this->fecha_inicio = $med->fecha_inicio
            ? $med->fecha_inicio->format('Y-m-d')
            : '';

        $this->fecha_fin = $med->fecha_fin
            ? $med->fecha_fin->format('Y-m-d')
            : '';

        $this->medico_indica = (string) ($med->medico_indica ?? '');
        $this->observacion = (string) ($med->observacion ?? '');
        $estadoActual = (string) ($med->estado ?? 'ACTIVO');

        $this->estado = in_array($estadoActual, ['ACTIVO', 'ACTIVA', 'VIGENTE'], true)
            ? 'ACTIVO'
            : $estadoActual;
    }

    private function adaptarModoAdministracion(): void
    {
        if ($this->es_prn) {
            $this->hora_programada = '';
        } else {
            $this->condicion_prn = '';
        }

        $this->confirmar_duplicado = false;
    }

    private function actualizarResidenteSeleccionado(): void
    {
        $this->adulto = null;
        $this->confirmar_duplicado = false;
        $this->posibleDuplicado = false;
        $this->medicacionDuplicadaResumen = null;

        if (!$this->cod_am) {
            return;
        }

        $adulto = AdultoMayor::query()
            ->with('estado')
            ->find($this->cod_am);

        if (!$adulto) {
            return;
        }

        $this->autorizarContextoResidente($adulto);
        $this->adulto = $adulto;

        $this->detectarPosibleDuplicado();
    }

    private function detectarPosibleDuplicado(): void
    {
        $this->posibleDuplicado = false;
        $this->medicacionDuplicadaResumen = null;

        if (!$this->cod_am || mb_strlen(trim($this->nombre_medicamento)) < 2) {
            return;
        }

        $duplicado = $this->buscarPosibleDuplicado();

        if (!$duplicado) {
            return;
        }

        $this->posibleDuplicado = true;

        $pauta = $duplicado->es_prn
            ? 'PRN'
            : ($duplicado->hora_programada
                ? $duplicado->hora_programada->format('H:i')
                : 'sin hora');

        $this->medicacionDuplicadaResumen =
            "{$duplicado->nombre_medicamento} · {$duplicado->dosis} · {$duplicado->frecuencia} · {$pauta}";
    }

    private function buscarPosibleDuplicado(): ?MedicacionAdulto
    {
        $nombre = trim($this->nombre_medicamento);

        if (!$this->cod_am || $nombre === '') {
            return null;
        }

        return MedicacionAdulto::query()
            ->where('cod_am', $this->cod_am)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE', 'PAUSADO', 'EN REVISION'])
            ->where('nombre_medicamento', $nombre)
            ->when(
                $this->isEditing && $this->cod_med_adulto,
                fn($query) => $query->where('cod_med_adulto', '!=', $this->cod_med_adulto)
            )
            ->orderByDesc('fecha_inicio')
            ->first();
    }

    private function datosNormalizados(): array
    {
        return [
            'cod_am' => $this->cod_am,
            'nombre_medicamento' => trim($this->nombre_medicamento),
            'dosis' => trim($this->dosis),
            'frecuencia' => trim($this->frecuencia),

            'es_prn' => $this->es_prn,

            'condicion_prn' => $this->es_prn
                ? trim($this->condicion_prn)
                : null,

            'intervalo_horas' => $this->intervalo_horas !== null
                && $this->intervalo_horas !== ''
                ? (int) $this->intervalo_horas
                : null,

            'via_administracion' => trim($this->via_administracion),

            'hora_programada' => $this->es_prn
                ? null
                : $this->hora_programada,

            'fecha_inicio' => $this->fecha_inicio,

            'fecha_fin' => trim($this->fecha_fin) !== ''
                ? $this->fecha_fin
                : null,

            'medico_indica' => trim($this->medico_indica) !== ''
                ? trim($this->medico_indica)
                : null,

            'observacion' => trim($this->observacion) !== ''
                ? trim($this->observacion)
                : null,

            'estado' => $this->isEditing
                ? $this->estado
                : 'ACTIVO',
        ];
    }

    private function validarTransicionEstado(?string $estadoOriginal = null): void
    {
        if ($estadoOriginal !== null) {
            abort_unless(
                in_array($estadoOriginal, self::ESTADOS_EDITABLES, true),
                409,
                'La orden actual ya no admite modificaciones.'
            );
        }

        if (!$this->isEditing) {
            abort_unless(
                $this->estado === 'ACTIVO',
                422,
                'Una nueva prescripción debe registrarse inicialmente como ACTIVA.'
            );

            return;
        }

        if (
            in_array($this->estado, self::ESTADOS_TERMINALES, true)
            && $estadoOriginal !== $this->estado
        ) {
            $this->autorizarPermiso('medicacion.suspender');
        }
    }

    private function autorizarRolMedico(): void
    {
        $usuario = Auth::user();

        abort_unless($usuario, 401);

        abort_unless(
            $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']),
            403,
            'Las órdenes médicas solo pueden ser creadas o modificadas por personal médico autorizado.'
        );

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
    }

    private function autorizarPermiso(string $permiso): void
    {
        abort_unless(
            Auth::user()?->can($permiso),
            403,
            'No cuenta con el permiso requerido para esta acción.'
        );
    }

    private function autorizarContextoResidente(AdultoMayor $adulto): void
    {
        $this->autorizarRolMedico();

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
            'El estado actual del residente no permite modificar órdenes médicas.'
        );
    }

    private function resetFormulario(): void
    {
        $this->showModal = false;
        $this->isEditing = false;
        $this->pacienteFijado = false;
        $this->formularioSucio = false;

        $this->cod_med_adulto = null;
        $this->cod_am = null;
        $this->adulto = null;

        $this->nombre_medicamento = '';
        $this->dosis = '';
        $this->frecuencia = '';
        $this->es_prn = false;
        $this->condicion_prn = '';
        $this->intervalo_horas = null;
        $this->via_administracion = '';
        $this->hora_programada = '';
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->medico_indica = '';
        $this->observacion = '';
        $this->estado = 'ACTIVO';

        $this->posibleDuplicado = false;
        $this->confirmar_duplicado = false;
        $this->medicacionDuplicadaResumen = null;

        $this->resetValidation();
    }

    public function render()
    {
        $adultosDisponibles = collect();

        if ($this->showModal && !$this->pacienteFijado) {
            $adultosDisponibles = AdultoMayor::query()
                ->whereNull('archivado_en')
                ->orderBy('nombres')
                ->orderBy('ap_paterno')
                ->get(['cod_am', 'nombres', 'ap_paterno', 'ap_materno', 'ci', 'estado_operativo']);
        }

        return view('livewire.medicacion.medicacion-adulto-modal', [
            'adultosDisponibles' => $adultosDisponibles,
            'adultoSeleccionado' => $this->adulto,
        ]);
    }
}
