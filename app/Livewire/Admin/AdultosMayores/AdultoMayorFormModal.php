<?php

namespace App\Livewire\Admin\AdultosMayores;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\AdultoMayor;
use App\Services\Admin\AdultoMayorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdultoMayorFormModal extends Component
{
    use WithFileUploads;

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
    public $alergias = 'Ninguna';
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
            'consentimiento_datos'
        ]);
        $this->alergias = 'Ninguna';
        $this->fecha_ing = date('Y-m-d');
        $this->hora_ing = date('H:i');
        $this->tiene_celular = true;
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
        
        $this->fecha_nac = $adulto->fecha_nac ? $adulto->fecha_nac->format('Y-m-d') : null;
        $this->genero = $adulto->genero;
        $this->grupo_sanguineo = $adulto->grupo_sanguineo;
        $this->alergias = $adulto->alergias ?? 'Ninguna';
        $this->seguro_salud = $adulto->seguro_salud;
        $this->nivel_educat = $adulto->nivel_educat;
        
        $this->tiene_celular = (bool)$adulto->tiene_celular;
        $this->celular = $adulto->celular;
        $this->sabe_usar_whatsapp = (bool)$adulto->sabe_usar_whatsapp;
        $this->telefono_fijo = $adulto->telefono_fijo;
        $this->departamento_residencia = $adulto->departamento_residencia;
        $this->ciudad_municipio = $adulto->ciudad_municipio;
        $this->zona = $adulto->zona;
        $this->calle = $adulto->calle;
        
        $this->contacto_emergencia_nombre = $adulto->contacto_emergencia_nombre;
        $this->contacto_emergencia_parentesco = $adulto->contacto_emergencia_parentesco;
        $this->contacto_emergencia_celular = $adulto->contacto_emergencia_celular;
        $this->contacto_emergencia_direccion = $adulto->contacto_emergencia_direccion;
        $this->responsable_principal = (bool)$adulto->responsable_principal;
        $this->autorizado_informacion_medica = (bool)$adulto->autorizado_informacion_medica;
        
        $this->fecha_ing = $adulto->fecha_ing ? $adulto->fecha_ing->format('Y-m-d') : date('Y-m-d');
        $this->hora_ing = $adulto->hora_ing ? substr($adulto->hora_ing, 0, 5) : date('H:i');
        $this->tipo_ing = $adulto->tipo_ing;
        $this->permanencia = $adulto->permanencia;
        $this->cod_est_adul = $adulto->cod_est_adul;
        $this->observaciones = $adulto->observaciones;
        $this->consentimiento_datos = (bool)$adulto->consentimiento_datos;
        
        $this->fotoExistente = $adulto->foto;
    }

    public function getEdadProperty()
    {
        if (!$this->fecha_nac) return null;
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
        $rules = [];
        $messages = [
            'nombres.required' => 'El nombre es obligatorio.',
            'nombres.regex' => 'El nombre solo puede contener letras.',
            'ap_paterno.required' => 'El apellido paterno es obligatorio.',
            'ap_paterno.regex' => 'El apellido paterno solo puede contener letras.',
            'ci.required' => 'El carnet de identidad es obligatorio.',
            'ci.regex' => 'El CI debe contener entre 5 y 9 dígitos.',
            'expedicion_ci.required' => 'La expedición del CI es obligatoria.',
            'fecha_nac.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nac.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'celular.required_if' => 'El celular es obligatorio.',
            'celular.regex' => 'El celular debe tener 8 dígitos y comenzar con 6 o 7.',
            'contacto_emergencia_nombre.required' => 'El nombre del contacto es obligatorio.',
            'contacto_emergencia_celular.required' => 'El celular del contacto es obligatorio.',
            'contacto_emergencia_celular.different' => 'No puede ser igual al del adulto mayor.',
            'consentimiento_datos.accepted' => 'Debe aceptar el consentimiento.',
        ];

        switch ($this->paso) {
            case 1:
                $rules = [
                    'nombres' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
                    'ap_paterno' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
                    'ap_materno' => ['nullable', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
                    'ci' => ['required', 'string', 'regex:/^[0-9]{5,9}$/'],
                    'complemento_ci' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]{1,2}$/'],
                    'expedicion_ci' => ['required', 'string', 'in:LP,SC,CB,OR,PT,CH,TJ,BE,PA'],
                    'estado_civil' => ['required', 'string', 'in:SOLTERO/A,CASADO/A,VIUDO/A,DIVORCIADO/A,UNIÓN LIBRE,NO ESPECIFICADO'],
                ];
                // Único CI
                $ciUnique = Rule::unique('adulto_mayor', 'ci')
                    ->where('expedicion_ci', $this->expedicion_ci)
                    ->where('complemento_ci', $this->complemento_ci);
                if ($this->isEdit) $ciUnique->ignore($this->adultoId, 'cod_am');
                $rules['ci'][] = $ciUnique;
                break;
            case 2:
                $rules = [
                    'fecha_nac' => ['required', 'date', 'before:today'],
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
                    'hora_ing' => ['nullable', 'date_format:H:i'],
                    'tipo_ing' => ['required', 'string', 'in:REGULAR,DERIVADO,VOLUNTARIO,EMERGENCIA,OTRO'],
                    'permanencia' => ['required', 'string', 'in:PERMANENTE,TEMPORAL,EVENTUAL'],
                    'cod_est_adul' => ['required', 'exists:estado_adulto,cod_est_adul'],
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
        $this->validarPaso(); // Validar el último paso

        $data = [
            'nombres' => trim(ucwords(strtolower($this->nombres))),
            'ap_paterno' => trim(ucwords(strtolower($this->ap_paterno))),
            'ap_materno' => $this->ap_materno ? trim(ucwords(strtolower($this->ap_materno))) : null,
            'ci' => preg_replace('/\s+/', '', $this->ci),
            'complemento_ci' => $this->complemento_ci ? trim(strtoupper($this->complemento_ci)) : null,
            'expedicion_ci' => trim(strtoupper($this->expedicion_ci)),
            'estado_civil' => strtoupper($this->estado_civil),
            
            'fecha_nac' => $this->fecha_nac,
            'genero' => strtoupper($this->genero),
            'grupo_sanguineo' => strtoupper($this->grupo_sanguineo),
            'alergias' => empty($this->alergias) ? 'Ninguna' : trim($this->alergias),
            'seguro_salud' => strtoupper($this->seguro_salud),
            'nivel_educat' => strtoupper($this->nivel_educat),
            
            'tiene_celular' => (bool)$this->tiene_celular,
            'celular' => $this->tiene_celular ? preg_replace('/\s+/', '', $this->celular) : null,
            'sabe_usar_whatsapp' => $this->tiene_celular ? (bool)$this->sabe_usar_whatsapp : false,
            'telefono_fijo' => preg_replace('/\s+/', '', $this->telefono_fijo),
            'departamento_residencia' => strtoupper($this->departamento_residencia),
            'ciudad_municipio' => trim($this->ciudad_municipio),
            'zona' => trim($this->zona),
            'calle' => trim($this->calle),
            
            'contacto_emergencia_nombre' => trim(ucwords(strtolower($this->contacto_emergencia_nombre))),
            'contacto_emergencia_parentesco' => strtoupper($this->contacto_emergencia_parentesco),
            'contacto_emergencia_celular' => preg_replace('/\s+/', '', $this->contacto_emergencia_celular),
            'contacto_emergencia_direccion' => trim($this->contacto_emergencia_direccion),
            'responsable_principal' => (bool)$this->responsable_principal,
            'autorizado_informacion_medica' => (bool)$this->autorizado_informacion_medica,
            
            'fecha_ing' => $this->fecha_ing,
            'hora_ing' => $this->hora_ing,
            'tipo_ing' => strtoupper($this->tipo_ing),
            'permanencia' => strtoupper($this->permanencia),
            'cod_est_adul' => $this->cod_est_adul,
            'observaciones' => trim($this->observaciones),
            'consentimiento_datos' => (bool)$this->consentimiento_datos,
        ];

        $service = app(AdultoMayorService::class);

        try {
            DB::beginTransaction();

            if ($this->isEdit) {
                $service->actualizarAdultoMayor($this->adultoId, $data, $this->foto);
                $mensaje = 'Expediente actualizado correctamente.';
            } else {
                $service->crearAdultoMayor($data, $this->foto);
                $mensaje = 'Admisión registrada correctamente.';
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
                'text' => 'Ocurrió un error al guardar: ' . $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $service = app(AdultoMayorService::class);
        return view('livewire.admin.adultos-mayores.adulto-mayor-form-modal', [
            'estadosAdulto' => $service->obtenerEstados(),
        ]);
    }
}
