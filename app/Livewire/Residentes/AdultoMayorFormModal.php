<?php

namespace App\Livewire\Residentes;

use App\Models\AdultoMayor;
use App\Services\Residentes\AdultoMayorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class AdultoMayorFormModal extends Component
{
    use WithFileUploads;

    private const CAMPOS_MAYUSCULA = [
        'nombres',
        'ap_paterno',
        'ap_materno',
        'complemento_ci',
        'expedicion_ci',
        'estado_civil',
        'genero',
        'grupo_sanguineo',
        'alergias',
        'seguro_salud',
        'nivel_educat',
        'departamento_residencia',
        'ciudad_municipio',
        'zona',
        'calle',
        'contacto_emergencia_nombre',
        'contacto_emergencia_parentesco',
        'contacto_emergencia_direccion',
        'tipo_ing',
        'permanencia',
        'observaciones',
    ];

    private const CAMPOS_DIGITOS = [
        'ci',
        'celular',
        'telefono_fijo',
        'contacto_emergencia_celular',
    ];

    // Estado del modal
    public $mostrar = false;
    public $adultoId = null;
    public $paso = 1;
    public $totalPasos = 6;
    public $isEdit = false;

    // Campos del formulario
    public $foto;
    public $fotoExistente;
    public $nombres;
    public $ap_paterno;
    public $ap_materno;
    public $ci;
    public $complemento_ci;
    public $expedicion_ci;
    public $estado_civil;

    public $fecha_nac;
    public $genero;
    public $grupo_sanguineo;
    public $alergias = 'NINGUNA';
    public $seguro_salud;
    public $nivel_educat;

    public $tiene_celular = true;
    public $celular;
    public $sabe_usar_whatsapp = false;
    public $telefono_fijo;
    public $departamento_residencia;
    public $ciudad_municipio;
    public $zona;
    public $calle;

    public $contacto_emergencia_nombre;
    public $contacto_emergencia_parentesco;
    public $contacto_emergencia_celular;
    public $contacto_emergencia_direccion;
    public $responsable_principal = false;
    public $autorizado_informacion_medica = false;

    public $fecha_ing;
    public $hora_ing;
    public $tipo_ing;
    public $permanencia;
    public $cod_est_adul;
    public $observaciones;

    public $consentimiento_datos = false;

    protected $listeners = [
        'adulto-mayor-form-abrir' => 'abrir',
    ];

    public function mount()
    {
        $this->fecha_ing = date('Y-m-d');
        $this->hora_ing = date('H:i');

        $estadoActivo = DB::table('estado_adulto')
            ->whereRaw('UPPER(estado) = ?', ['ACTIVO'])
            ->first();

        $this->cod_est_adul = $estadoActivo ? $estadoActivo->cod_est_adul : 1;
    }

    public function abrir($adultoId = null)
    {
        $this->resetForm();
        $this->adultoId = $adultoId;
        $this->mostrar = true;
        $this->isEdit = !is_null($adultoId);

        if ($this->isEdit) {
            $this->cargarAdulto();
        }
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, self::CAMPOS_MAYUSCULA, true)) {
            $normalizado = $this->toUpperText($this->{$propertyName});
            if ($this->{$propertyName} !== $normalizado) {
                $this->{$propertyName} = $normalizado;
            }
        }

        if (in_array($propertyName, self::CAMPOS_DIGITOS, true)) {
            $normalizado = $this->onlyDigits($this->{$propertyName});
            if ($this->{$propertyName} !== $normalizado) {
                $this->{$propertyName} = $normalizado;
            }
        }

        if ($propertyName === 'fecha_nac') {
            if ($this->edad !== null && $this->edad < 60) {
                $this->addError('fecha_nac', 'El adulto mayor debe tener 60 anos o mas para ser registrado.');
            } else {
                $this->resetValidation('fecha_nac');
            }
        }
    }

    public function cerrar()
    {
        $this->mostrar = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'adultoId', 'paso', 'isEdit', 'foto', 'fotoExistente',
            'nombres', 'ap_paterno', 'ap_materno', 'ci', 'complemento_ci', 'expedicion_ci', 'estado_civil',
            'fecha_nac', 'genero', 'grupo_sanguineo', 'alergias', 'seguro_salud', 'nivel_educat',
            'tiene_celular', 'celular', 'sabe_usar_whatsapp', 'telefono_fijo', 'departamento_residencia', 'ciudad_municipio', 'zona', 'calle',
            'contacto_emergencia_nombre', 'contacto_emergencia_parentesco', 'contacto_emergencia_celular', 'contacto_emergencia_direccion',
            'responsable_principal', 'autorizado_informacion_medica',
            'fecha_ing', 'hora_ing', 'tipo_ing', 'permanencia', 'cod_est_adul', 'observaciones',
            'consentimiento_datos',
        ]);

        $this->alergias = 'NINGUNA';
        $this->fecha_ing = date('Y-m-d');
        $this->hora_ing = date('H:i');
        $this->tiene_celular = true;

        $estadoActivo = DB::table('estado_adulto')
            ->whereRaw('UPPER(estado) = ?', ['ACTIVO'])
            ->first();

        $this->cod_est_adul = $estadoActivo ? $estadoActivo->cod_est_adul : 1;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    private function cargarAdulto()
    {
        $adulto = AdultoMayor::findOrFail($this->adultoId);

        $this->nombres = $adulto->nombres;
        $this->ap_paterno = $adulto->ap_paterno;
        $this->ap_materno = $adulto->ap_materno;
        $this->ci = $adulto->ci;
        $this->complemento_ci = $adulto->complemento_ci;
        $this->expedicion_ci = $adulto->expedicion_ci;
        $this->estado_civil = $adulto->estado_civil;

        $this->fecha_nac = $adulto->fecha_nac ? Carbon::parse($adulto->fecha_nac)->format('Y-m-d') : null;
        $this->genero = $adulto->genero;
        $this->grupo_sanguineo = $adulto->grupo_sanguineo;
        $this->alergias = $adulto->alergias ?? 'NINGUNA';
        $this->seguro_salud = $adulto->seguro_salud;
        $this->nivel_educat = $adulto->nivel_educat;

        $this->tiene_celular = (bool) $adulto->tiene_celular;
        $this->celular = $adulto->celular;
        $this->sabe_usar_whatsapp = (bool) $adulto->sabe_usar_whatsapp;
        $this->telefono_fijo = $adulto->telefono_fijo;
        $this->departamento_residencia = $adulto->departamento_residencia;
        $this->ciudad_municipio = $adulto->ciudad_municipio;
        $this->zona = $adulto->zona;
        $this->calle = $adulto->calle;

        $this->contacto_emergencia_nombre = $adulto->contacto_emergencia_nombre;
        $this->contacto_emergencia_parentesco = $adulto->contacto_emergencia_parentesco;
        $this->contacto_emergencia_celular = $adulto->contacto_emergencia_celular;
        $this->contacto_emergencia_direccion = $adulto->contacto_emergencia_direccion;
        $this->responsable_principal = (bool) $adulto->responsable_principal;
        $this->autorizado_informacion_medica = (bool) $adulto->autorizado_informacion_medica;

        $this->fecha_ing = $adulto->fecha_ing ? Carbon::parse($adulto->fecha_ing)->format('Y-m-d') : date('Y-m-d');
        $this->hora_ing = $adulto->hora_ing ? substr($adulto->hora_ing, 0, 5) : date('H:i');
        $this->tipo_ing = $adulto->tipo_ing;
        $this->permanencia = $adulto->permanencia;
        $this->cod_est_adul = $adulto->cod_est_adul;
        $this->observaciones = $adulto->observaciones;
        $this->consentimiento_datos = (bool) $adulto->consentimiento_datos;

        $this->fotoExistente = $adulto->foto;
    }

    private function toUpperText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return mb_strtoupper($value, 'UTF-8');
    }

    private function onlyDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        return $digits === '' ? null : $digits;
    }

    private function normalizarDatosFormulario(): void
    {
        // Identificacion
        $this->nombres = $this->toUpperText($this->nombres);
        $this->ap_paterno = $this->toUpperText($this->ap_paterno);
        $this->ap_materno = $this->toUpperText($this->ap_materno);
        $this->ci = $this->onlyDigits($this->ci);
        $this->complemento_ci = $this->toUpperText($this->complemento_ci);
        $this->expedicion_ci = $this->toUpperText($this->expedicion_ci);
        $this->estado_civil = $this->toUpperText($this->estado_civil);

        // Datos personales e institucionales basicos
        $this->genero = $this->toUpperText($this->genero);
        $this->grupo_sanguineo = $this->toUpperText($this->grupo_sanguineo);
        $this->alergias = $this->toUpperText($this->alergias) ?? 'NINGUNA';
        $this->seguro_salud = $this->toUpperText($this->seguro_salud);
        $this->nivel_educat = $this->toUpperText($this->nivel_educat);

        // Contacto y direccion
        $this->celular = $this->onlyDigits($this->celular);
        $this->telefono_fijo = $this->onlyDigits($this->telefono_fijo);
        $this->departamento_residencia = $this->toUpperText($this->departamento_residencia);
        $this->ciudad_municipio = $this->toUpperText($this->ciudad_municipio);
        $this->zona = $this->toUpperText($this->zona);
        $this->calle = $this->toUpperText($this->calle);

        // Contacto de emergencia
        $this->contacto_emergencia_nombre = $this->toUpperText($this->contacto_emergencia_nombre);
        $this->contacto_emergencia_parentesco = $this->toUpperText($this->contacto_emergencia_parentesco);
        $this->contacto_emergencia_celular = $this->onlyDigits($this->contacto_emergencia_celular);
        $this->contacto_emergencia_direccion = $this->toUpperText($this->contacto_emergencia_direccion);

        // Ingreso institucional
        $this->tipo_ing = $this->toUpperText($this->tipo_ing);
        $this->permanencia = $this->toUpperText($this->permanencia);
        $this->observaciones = $this->toUpperText($this->observaciones);

        if (!$this->tiene_celular) {
            $this->celular = null;
            $this->sabe_usar_whatsapp = false;
        }
    }

    public function getEdadProperty()
    {
        if (!$this->fecha_nac) {
            return null;
        }

        try {
            return Carbon::parse($this->fecha_nac)->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function siguiente()
    {
        $this->validarPaso();

        if ($this->paso < $this->totalPasos) {
            $this->paso++;
        }
    }

    public function anterior()
    {
        if ($this->paso > 1) {
            $this->paso--;
        }
    }

    private function validarPaso()
    {
        $this->normalizarDatosFormulario();

        $rules = [];
        $messages = [
            // Paso 1
            'nombres.required' => 'El nombre es obligatorio.',
            'nombres.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombres.regex' => 'El nombre solo puede contener letras y espacios.',
            'ap_paterno.required' => 'El apellido paterno es obligatorio.',
            'ap_paterno.min' => 'El apellido paterno debe tener al menos 2 caracteres.',
            'ap_paterno.regex' => 'El apellido paterno solo puede contener letras.',
            'ap_materno.min' => 'El apellido materno debe tener al menos 2 caracteres.',
            'ap_materno.regex' => 'El apellido materno solo puede contener letras.',
            'ci.required' => 'El carnet de identidad es obligatorio.',
            'ci.regex' => 'El CI debe contener entre 5 y 9 digitos numericos.',
            'ci.unique' => 'Ya existe un adulto mayor registrado con este CI y expedicion.',
            'complemento_ci.regex' => 'El complemento debe tener entre 1 y 2 caracteres alfanumericos.',
            'expedicion_ci.required' => 'El departamento de expedicion del CI es obligatorio.',
            'expedicion_ci.in' => 'Seleccione un departamento de expedicion valido.',
            'estado_civil.required' => 'El estado civil es obligatorio.',
            'estado_civil.in' => 'Seleccione un estado civil valido.',

            // Paso 2
            'fecha_nac.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nac.before_or_equal' => 'El adulto mayor debe tener al menos 60 anos de edad.',
            'fecha_nac.after_or_equal' => 'La fecha de nacimiento no puede exceder 120 anos.',
            'genero.required' => 'El genero es obligatorio.',
            'genero.in' => 'Seleccione un genero valido.',
            'grupo_sanguineo.required' => 'El grupo sanguineo es obligatorio.',
            'grupo_sanguineo.in' => 'Seleccione un grupo sanguineo valido.',
            'alergias.required' => 'Indique las alergias conocidas o escriba "NINGUNA".',
            'seguro_salud.required' => 'El tipo de seguro de salud es obligatorio.',
            'seguro_salud.in' => 'Seleccione un seguro de salud valido.',
            'nivel_educat.required' => 'El nivel educativo es obligatorio.',
            'nivel_educat.in' => 'Seleccione un nivel educativo valido.',

            // Paso 3
            'celular.required_if' => 'El numero de celular es obligatorio.',
            'celular.regex' => 'El celular debe tener 8 digitos y comenzar con 6 o 7.',
            'telefono_fijo.regex' => 'El telefono fijo debe tener 7 u 8 digitos y comenzar con 2, 3 o 4.',
            'departamento_residencia.required' => 'El departamento de residencia es obligatorio.',
            'departamento_residencia.in' => 'Seleccione un departamento valido.',
            'ciudad_municipio.required' => 'La ciudad o municipio es obligatorio.',
            'ciudad_municipio.min' => 'La ciudad o municipio debe tener al menos 2 caracteres.',
            'zona.required' => 'La zona o barrio es obligatoria.',
            'zona.min' => 'La zona debe tener al menos 2 caracteres.',
            'calle.required' => 'La calle o avenida es obligatoria.',
            'calle.min' => 'La direccion debe tener al menos 3 caracteres.',

            // Paso 4
            'contacto_emergencia_nombre.required' => 'El nombre del contacto es obligatorio.',
            'contacto_emergencia_nombre.min' => 'El nombre del contacto debe tener al menos 3 caracteres.',
            'contacto_emergencia_nombre.regex' => 'El nombre del contacto solo puede contener letras y espacios.',
            'contacto_emergencia_parentesco.required' => 'El parentesco es obligatorio.',
            'contacto_emergencia_parentesco.in' => 'Seleccione un parentesco valido.',
            'contacto_emergencia_celular.required' => 'El celular del contacto es obligatorio.',
            'contacto_emergencia_celular.regex' => 'El celular del contacto debe tener 8 digitos y comenzar con 6 o 7.',
            'contacto_emergencia_celular.different' => 'El celular del contacto no puede ser igual al del adulto mayor.',

            // Paso 5
            'fecha_ing.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ing.after_or_equal' => 'La fecha de ingreso no puede ser anterior a la fecha de nacimiento.',
            'fecha_ing.before_or_equal' => 'La fecha de ingreso no puede ser en el futuro.',
            'tipo_ing.required' => 'El tipo de ingreso es obligatorio.',
            'tipo_ing.in' => 'Seleccione un tipo de ingreso valido.',
            'permanencia.required' => 'El tipo de permanencia es obligatorio.',
            'permanencia.in' => 'Seleccione un tipo de permanencia valido.',

            // Paso 6
            'consentimiento_datos.accepted' => 'Debe aceptar el consentimiento para continuar.',
        ];

        switch ($this->paso) {
            case 1:
                $rules = [
                    'nombres' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
                    'ap_paterno' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
                    'ap_materno' => ['nullable', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
                    'ci' => ['required', 'string', 'regex:/^[0-9]{5,9}$/'],
                    'complemento_ci' => ['nullable', 'string', 'regex:/^[A-Z0-9]{1,2}$/'],
                    'expedicion_ci' => ['required', 'string', 'in:LP,SC,CB,OR,PT,CH,TJ,BE,PA'],
                    'estado_civil' => ['required', 'string', 'in:SOLTERO/A,CASADO/A,VIUDO/A,DIVORCIADO/A,UNIÓN LIBRE,NO ESPECIFICADO'],
                ];

                $ciUnique = Rule::unique('adulto_mayor', 'ci')
                    ->where('expedicion_ci', $this->expedicion_ci)
                    ->where('complemento_ci', $this->complemento_ci);

                if ($this->isEdit) {
                    $ciUnique->ignore($this->adultoId, 'cod_am');
                }

                $rules['ci'][] = $ciUnique;
                break;

            case 2:
                $rules = [
                    'fecha_nac' => [
                        'required',
                        'date',
                        'after_or_equal:' . now()->subYears(120)->format('Y-m-d'),
                        'before_or_equal:' . now()->subYears(60)->format('Y-m-d'),
                    ],
                    'genero' => ['required', 'string', 'in:MASCULINO,FEMENINO,OTRO'],
                    'grupo_sanguineo' => ['required', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
                    'alergias' => ['required', 'string', 'max:1000'],
                    'seguro_salud' => ['required', 'string', 'in:SUS,CAJA NACIONAL CNS,CAJA PETROLERA,SEGURO PRIVADO,NINGUNO,OTRO'],
                    'nivel_educat' => ['required', 'string', 'in:ANALFABETO,PRIMARIA,SECUNDARIA,TÉCNICO,UNIVERSITARIO,POSTGRADO,NO ESPECIFICADO'],
                ];
                break;

            case 3:
                $rules = [
                    'tiene_celular' => ['boolean'],
                    'celular' => ['nullable', 'required_if:tiene_celular,true', 'string', 'regex:/^[67][0-9]{7}$/'],
                    'sabe_usar_whatsapp' => ['boolean'],
                    'telefono_fijo' => ['nullable', 'string', 'regex:/^[2-4][0-9]{6,7}$/'],
                    'departamento_residencia' => ['required', 'string', 'in:LA PAZ,SANTA CRUZ,COCHABAMBA,ORURO,POTOSÍ,CHUQUISACA,TARIJA,BENI,PANDO'],
                    'ciudad_municipio' => ['required', 'string', 'min:2', 'max:100'],
                    'zona' => ['required', 'string', 'min:2', 'max:100'],
                    'calle' => ['required', 'string', 'min:3', 'max:150'],
                ];
                break;

            case 4:
                $rules = [
                    'contacto_emergencia_nombre' => ['required', 'string', 'min:3', 'max:150', 'regex:/^[\pL\s\'-]+$/u'],
                    'contacto_emergencia_parentesco' => ['required', 'string', 'in:HIJO/A,CÓNYUGE,NIETO/A,SOBRINO/A,HERMANO/A,TUTOR LEGAL,OTRO'],
                    'contacto_emergencia_celular' => ['required', 'string', 'regex:/^[67][0-9]{7}$/', 'different:celular'],
                    'contacto_emergencia_direccion' => ['nullable', 'string', 'max:200'],
                    'responsable_principal' => ['nullable', 'boolean'],
                    'autorizado_informacion_medica' => ['nullable', 'boolean'],
                ];
                break;

            case 5:
                $rules = [
                    'fecha_ing' => ['required', 'date', 'after_or_equal:fecha_nac', 'before_or_equal:today'],
                    'hora_ing' => ['nullable'],
                    'tipo_ing' => ['required', 'string', 'in:REGULAR,DERIVADO,VOLUNTARIO,EMERGENCIA,OTRO'],
                    'permanencia' => ['required', 'string', 'in:PERMANENTE,TEMPORAL,EVENTUAL'],
                    'observaciones' => ['nullable', 'string', 'max:1500'],
                ];
                break;

            case 6:
                $rules = [
                    'consentimiento_datos' => ['accepted'],
                ];
                break;
        }

        $this->validate($rules, $messages);
    }

    public function guardar()
    {
        $this->validarPaso();

        if ($this->edad !== null && $this->edad < 60) {
            $this->paso = 2;
            $this->addError('fecha_nac', 'El adulto mayor debe tener 60 anos o mas para ser registrado.');
            return;
        }

        $codEst = $this->cod_est_adul;
        if (!$this->isEdit) {
            $estadoActivo = DB::table('estado_adulto')
                ->whereRaw('UPPER(estado) = ?', ['ACTIVO'])
                ->first();

            if (!$estadoActivo) {
                $id = DB::table('estado_adulto')->insertGetId([
                    'estado' => 'ACTIVO',
                    'created_at' => now(),
                    'updated_at' => now(),
                ], 'cod_est_adul');
                $codEst = $id;
            } else {
                $codEst = $estadoActivo->cod_est_adul;
            }
        }

        $data = [
            'nombres' => $this->nombres,
            'ap_paterno' => $this->ap_paterno,
            'ap_materno' => $this->ap_materno,
            'ci' => $this->ci,
            'complemento_ci' => $this->complemento_ci,
            'expedicion_ci' => $this->expedicion_ci,
            'estado_civil' => $this->estado_civil,

            'fecha_nac' => $this->fecha_nac,
            'genero' => $this->genero,
            'grupo_sanguineo' => $this->grupo_sanguineo,
            'alergias' => $this->alergias ?: 'NINGUNA',
            'seguro_salud' => $this->seguro_salud,
            'nivel_educat' => $this->nivel_educat,

            'tiene_celular' => (bool) $this->tiene_celular,
            'celular' => $this->tiene_celular ? $this->celular : null,
            'sabe_usar_whatsapp' => $this->tiene_celular ? (bool) $this->sabe_usar_whatsapp : false,
            'telefono_fijo' => $this->telefono_fijo,
            'departamento_residencia' => $this->departamento_residencia,
            'ciudad_municipio' => $this->ciudad_municipio,
            'zona' => $this->zona,
            'calle' => $this->calle,

            'contacto_emergencia_nombre' => $this->contacto_emergencia_nombre,
            'contacto_emergencia_parentesco' => $this->contacto_emergencia_parentesco,
            'contacto_emergencia_celular' => $this->contacto_emergencia_celular,
            'contacto_emergencia_direccion' => $this->contacto_emergencia_direccion,
            'responsable_principal' => (bool) $this->responsable_principal,
            'autorizado_informacion_medica' => (bool) $this->autorizado_informacion_medica,

            'fecha_ing' => $this->fecha_ing,
            'hora_ing' => $this->hora_ing,
            'tipo_ing' => $this->tipo_ing,
            'permanencia' => $this->permanencia,
            'cod_est_adul' => $codEst,
            'observaciones' => $this->observaciones,
            'consentimiento_datos' => (bool) $this->consentimiento_datos,
        ];

        $service = app(AdultoMayorService::class);

        try {
            DB::beginTransaction();

            if ($this->isEdit) {
                $service->actualizarAdultoMayor($this->adultoId, $data, $this->foto);
                $mensaje = 'Expediente actualizado correctamente.';
            } else {
                $service->crearAdultoMayor($data, $this->foto);
                $mensaje = 'Admision registrada correctamente.';
            }

            DB::commit();

            $this->dispatch('adulto-mayor-guardado');
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Excelente',
                'text' => $mensaje,
            ]);

            $this->cerrar();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Ocurrio un error al guardar: ' . $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $service = app(AdultoMayorService::class);

        return view('livewire.residentes.adulto-mayor-form-modal', [
            'estadosAdulto' => $service->obtenerEstados(),
        ]);
    }
}
