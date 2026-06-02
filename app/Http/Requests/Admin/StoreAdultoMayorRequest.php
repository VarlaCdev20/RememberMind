<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdultoMayorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Asumiendo que la autorización la hace un middleware/política
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'nombres' => $this->nombres ? trim(ucwords(strtolower($this->nombres))) : null,
            'ap_paterno' => $this->ap_paterno ? trim(ucwords(strtolower($this->ap_paterno))) : null,
            'ap_materno' => $this->ap_materno ? trim(ucwords(strtolower($this->ap_materno))) : null,
            'ci' => $this->ci ? preg_replace('/\s+/', '', $this->ci) : null,
            'complemento_ci' => $this->complemento_ci ? trim(strtoupper($this->complemento_ci)) : null,
            'expedicion_ci' => $this->expedicion_ci ? trim(strtoupper($this->expedicion_ci)) : null,
            'genero' => $this->genero ? strtoupper($this->genero) : null,
            'estado_civil' => $this->estado_civil ? strtoupper($this->estado_civil) : null,
            'nivel_educat' => $this->nivel_educat ? strtoupper($this->nivel_educat) : null,
            'permanencia' => $this->permanencia ? strtoupper($this->permanencia) : null,
            'tipo_ing' => $this->tipo_ing ? strtoupper($this->tipo_ing) : null,
            'alergias' => empty($this->alergias) ? 'Ninguna' : trim($this->alergias),
            'tiene_celular' => $this->has('tiene_celular') ? filter_var($this->tiene_celular, FILTER_VALIDATE_BOOLEAN) : false,
            'sabe_usar_whatsapp' => $this->has('sabe_usar_whatsapp') ? filter_var($this->sabe_usar_whatsapp, FILTER_VALIDATE_BOOLEAN) : false,
            'responsable_principal' => $this->has('responsable_principal') ? filter_var($this->responsable_principal, FILTER_VALIDATE_BOOLEAN) : false,
            'autorizado_informacion_medica' => $this->has('autorizado_informacion_medica') ? filter_var($this->autorizado_informacion_medica, FILTER_VALIDATE_BOOLEAN) : false,
            'consentimiento_datos' => $this->has('consentimiento_datos') ? filter_var($this->consentimiento_datos, FILTER_VALIDATE_BOOLEAN) : false,
        ]);
        
        if (!$this->tiene_celular) {
            $this->merge(['celular' => null, 'sabe_usar_whatsapp' => false]);
        }
    }

    public function rules(): array
    {
        return [
            // Identidad
            'nombres' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'ap_paterno' => ['required_without:ap_materno', 'nullable', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'ap_materno' => ['required_without:ap_paterno', 'nullable', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\'-]+$/u'],
            'ci' => ['required', 'string', 'regex:/^[0-9]{5,9}$/'],
            'complemento_ci' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]{1,2}$/'],
            'expedicion_ci' => ['required', 'string', 'in:LP,SC,CB,OR,PT,CH,TJ,BE,PA'],
            
            // Regla compuesta única para ci + exp + comp
            'ci_unique' => [
                Rule::unique('adulto_mayor', 'ci')
                    ->where('expedicion_ci', $this->expedicion_ci)
                    ->where('complemento_ci', $this->complemento_ci)
            ],

            'estado_civil' => ['required', 'string', 'in:SOLTERO/A,CASADO/A,VIUDO/A,DIVORCIADO/A,UNIÓN LIBRE,NO ESPECIFICADO'],

            // Contacto y Ubicación
            'tiene_celular' => ['boolean'],
            'sabe_usar_whatsapp' => ['boolean'],
            'celular' => ['nullable', 'required_if:tiene_celular,true', 'string', 'regex:/^[67][0-9]{7}$/'],
            'telefono_fijo' => ['nullable', 'string', 'regex:/^[2-4][0-9]{6,7}$/'],
            'departamento_residencia' => ['required', 'string', 'in:LA PAZ,SANTA CRUZ,COCHABAMBA,ORURO,POTOSÍ,CHUQUISACA,TARIJA,BENI,PANDO'],
            'ciudad_municipio' => ['required', 'string', 'min:2', 'max:100'],
            'zona' => ['required', 'string', 'min:2', 'max:100'],
            'calle' => ['required', 'string', 'min:3', 'max:150'],

            // Información Médica Base
            'fecha_nac' => ['required', 'date', 'before_or_equal:' . now()->subYears(60)->format('Y-m-d')],
            'genero' => ['required', 'string', 'in:MASCULINO,FEMENINO,OTRO'],
            'grupo_sanguineo' => ['required', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'alergias' => ['required', 'string', 'max:1000'],
            'seguro_salud' => ['required', 'string', 'in:SUS,CAJA NACIONAL CNS,CAJA PETROLERA,SEGURO PRIVADO,NINGUNO,OTRO'],
            'nivel_educat' => ['required', 'string', 'in:ANALFABETO,PRIMARIA,SECUNDARIA,TÉCNICO,UNIVERSITARIO,POSTGRADO,NO ESPECIFICADO'],

            // Datos Institucionales
            'fecha_ing' => ['required', 'date', 'after_or_equal:fecha_nac', 'before_or_equal:today'],
            'hora_ing' => ['nullable', 'date_format:H:i'],
            'tipo_ing' => ['required', 'string', 'in:REGULAR,DERIVADO,VOLUNTARIO,EMERGENCIA,OTRO'],
            'permanencia' => ['required', 'string', 'in:PERMANENTE,TEMPORAL,EVENTUAL'],
            'cod_est_adul' => ['required', 'exists:estado_adulto,cod_est_adul'],
            'observaciones' => ['nullable', 'string', 'max:1500'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Responsable / Contacto de Emergencia
            'contacto_emergencia_nombre' => ['required', 'string', 'min:3', 'max:150', 'regex:/^[\pL\s\'-]+$/u'],
            'contacto_emergencia_parentesco' => ['required', 'string', 'in:HIJO/A,CÓNYUGE,NIETO/A,SOBRINO/A,HERMANO/A,TUTOR LEGAL,OTRO'],
            'contacto_emergencia_celular' => ['required', 'string', 'regex:/^[67][0-9]{7}$/', 'different:celular'],
            'contacto_emergencia_direccion' => ['nullable', 'string', 'max:200'],
            'responsable_principal' => ['nullable', 'boolean'],
            'autorizado_informacion_medica' => ['nullable', 'boolean'],

            // Consentimiento
            'consentimiento_datos' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombres.required' => 'El nombre es obligatorio.',
            'nombres.regex' => 'El nombre solo puede contener letras.',
            'ap_paterno.required_without' => 'Debe registrar al menos un apellido (paterno o materno).',
            'ap_paterno.regex' => 'El apellido paterno solo puede contener letras.',
            'ci.required' => 'El carnet de identidad es obligatorio.',
            'ci.regex' => 'El CI debe contener entre 5 y 9 dígitos.',
            'ci_unique' => 'La combinación de CI, complemento y expedición ya está registrada.',
            'expedicion_ci.required' => 'La expedición del CI es obligatoria.',
            'celular.required_if' => 'El celular es obligatorio si indica que tiene uno.',
            'celular.regex' => 'El celular debe tener 8 dígitos y comenzar con 6 o 7.',
            'fecha_nac.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nac.before_or_equal' => 'El adulto mayor debe tener al menos 60 años de edad cumplidos.',
            'genero.required' => 'El género es obligatorio.',
            'grupo_sanguineo.required' => 'Debe seleccionar un grupo sanguíneo válido.',
            'alergias.required' => 'Debe indicar alergias o seleccionar Ninguna.',
            'contacto_emergencia_nombre.required' => 'Debe registrar un contacto de emergencia.',
            'contacto_emergencia_celular.required' => 'El celular de emergencia es obligatorio.',
            'contacto_emergencia_celular.different' => 'El celular del responsable no puede ser igual al celular del adulto mayor.',
            'consentimiento_datos.accepted' => 'Debe aceptar el consentimiento para el registro y tratamiento de datos.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'La imagen debe ser JPG, JPEG, PNG o WEBP.',
            'foto.max' => 'La imagen no debe superar los 2 MB.',
        ];
    }
}
