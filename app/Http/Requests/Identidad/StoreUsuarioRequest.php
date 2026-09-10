<?php

namespace App\Http\Requests\Identidad;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombres'                 => Str::upper($this->nombres),
            'ap_paterno'              => $this->ap_paterno ? Str::upper($this->ap_paterno) : null,
            'ap_materno'              => $this->ap_materno ? Str::upper($this->ap_materno) : null,
            'correo'                  => Str::lower($this->correo),
            'numero_documento'        => Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $this->numero_documento)),
            'expedido'                => ($this->pais_documento === 'Bolivia' && $this->tipo_documento === 'CI') ? Str::upper($this->expedido) : null,
            'genero'                  => Str::upper($this->genero),
            'estado'                  => 'ACTIVO', // Forzado al crear
            'acceso_sistema'          => 'HABILITADO', // Forzado al crear
            'telefono'                => preg_replace('/[^0-9]/', '', $this->telefono),
            'contacto_emergencia'     => $this->contacto_emergencia ? Str::upper($this->contacto_emergencia) : null,
            'ap_paterno_emergencia'   => $this->ap_paterno_emergencia ? Str::upper($this->ap_paterno_emergencia) : null,
            'ap_materno_emergencia'   => $this->ap_materno_emergencia ? Str::upper($this->ap_materno_emergencia) : null,
            'celular_emergencia'      => preg_replace('/[^0-9]/', '', $this->celular_emergencia),
        ]);
    }

    public function rules(): array
    {
        return [
            'nombres'              => [
                'required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\-áéíóúÁÉÍÓÚñÑ]+$/u',
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\User::where('nombres', $this->nombres)
                        ->where('ap_paterno', $this->ap_paterno)
                        ->where('ap_materno', $this->ap_materno)
                        ->exists();
                    if ($exists) {
                        $fail('Ya existe un usuario con el mismo nombre completo. Verifique si se trata de la misma persona antes de continuar.');
                    }
                }
            ],
            'ap_paterno'           => ['nullable', 'string', 'max:100', 'required_without:ap_materno'],
            'ap_materno'           => ['nullable', 'string', 'max:100', 'required_without:ap_paterno'],
            'pais_documento'       => ['required', 'string', 'in:Bolivia,Brasil,Argentina,Perú,Chile,Colombia,México,Otro'],
            'tipo_documento'       => ['required', 'string'],
            'numero_documento'     => [
                'required', 'string', 'max:30', 'unique:users,numero_documento',
                function ($attribute, $value, $fail) {
                    $pais = $this->pais_documento;
                    $tipo = $this->tipo_documento;
                    $docLimpio = preg_replace('/[^A-Za-z0-9]/', '', $value);

                    if ($pais === 'Bolivia') {
                        if ($tipo !== 'CI') {
                            $fail('Para Bolivia, el único tipo de documento permitido es CI.');
                        }
                        if (strlen($docLimpio) < 5 || strlen($docLimpio) > 12) {
                            $fail('El CI de Bolivia debe tener entre 5 y 12 caracteres.');
                        }
                    } elseif ($pais === 'Brasil') {
                        if ($tipo === 'CPF' && strlen($docLimpio) !== 11) {
                            $fail('El CPF de Brasil debe tener exactamente 11 dígitos.');
                        }
                    } elseif ($pais === 'Argentina' && $tipo === 'DNI') {
                        if (strlen($docLimpio) < 7 || strlen($docLimpio) > 9) {
                            $fail('El DNI de Argentina debe tener entre 7 y 9 dígitos.');
                        }
                    } elseif ($pais === 'Perú' && $tipo === 'DNI') {
                        if (strlen($docLimpio) !== 8) {
                            $fail('El DNI de Perú debe tener exactamente 8 dígitos.');
                        }
                    } elseif ($pais === 'Colombia' && $tipo === 'CÉDULA') {
                        if (strlen($docLimpio) < 6 || strlen($docLimpio) > 10) {
                            $fail('La Cédula de Colombia debe tener entre 6 y 10 dígitos.');
                        }
                    } elseif ($pais === 'México' && $tipo === 'CURP') {
                        if (strlen($value) !== 18) {
                            $fail('El CURP de México debe tener exactamente 18 caracteres.');
                        }
                    }
                }
            ],
            'expedido'             => ['required_if:pais_documento,Bolivia', 'nullable', 'in:LP,CBBA,SCZ,OR,PT,CH,TJ,BN,PD'],
            'pais_telefono'        => ['required', 'string'],
            'codigo_telefono'      => ['required', 'string'],
            'telefono'             => [
                'required', 'string', 'min:6', 'max:15',
                function ($attribute, $value, $fail) {
                    $pais = $this->pais_telefono;
                    $telLimpio = preg_replace('/[^0-9]/', '', $value);

                    if ($pais === 'Bolivia' && strlen($telLimpio) !== 8) {
                        $fail('En Bolivia el celular debe tener 8 dígitos.');
                    } elseif ($pais === 'Brasil' && (strlen($telLimpio) < 10 || strlen($telLimpio) > 11)) {
                        $fail('En Brasil el celular debe tener 10 u 11 dígitos.');
                    } elseif ($pais === 'Argentina' && (strlen($telLimpio) < 10 || strlen($telLimpio) > 11)) {
                        $fail('En Argentina el celular debe tener 10 u 11 dígitos.');
                    } elseif ($pais === 'Perú' && strlen($telLimpio) !== 9) {
                        $fail('En Perú el celular debe tener 9 dígitos.');
                    } elseif ($pais === 'Chile' && strlen($telLimpio) !== 9) {
                        $fail('En Chile el celular debe tener 9 dígitos.');
                    } elseif ($pais === 'Colombia' && strlen($telLimpio) !== 10) {
                        $fail('En Colombia el celular debe tener 10 dígitos.');
                    } elseif ($pais === 'México' && strlen($telLimpio) !== 10) {
                        $fail('En México el celular debe tener 10 dígitos.');
                    }
                }
            ],
            'correo'               => ['required', 'email', 'unique:users,correo', 'max:150'],
            'rol'                  => ['required', 'string', 'exists:roles,name'],
            'especialidad_salud'   => ['required_if:rol,ENFERMEROS,MEDICO GENERAL/GERIATRA,PSICOLOGO/A,PEDAGOGO,NUTRICIONISTA,FISIOTERAPEUTA', 'nullable', 'string'],
            'cargo_administrativo' => ['required_if:rol,SUPERADMINISTRADOR,ADMINISTRADOR', 'nullable', 'string'],
            'genero'               => ['required', 'in:FEMENINO,MASCULINO,OTRO,PREFIERE NO ESPECIFICAR'],
            'fecha_nacimiento'     => [
                'required', 
                'date', 
                'before_or_equal:' . now()->subYears(16)->format('Y-m-d'),
                'after_or_equal:' . now()->subYears(60)->format('Y-m-d')
            ],
            'estado'               => ['required', 'in:ACTIVO'],
            'acceso_sistema'       => ['required', 'in:HABILITADO'],
            'foto_perfil'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'observaciones'        => ['nullable', 'string', 'max:500'],
            'fecha_ingreso'        => ['nullable', 'date', 'before_or_equal:today'],
            'calle'                 => ['required', 'string', 'min:2', 'max:150'],
            'nro_domicilio'         => ['required', 'string', 'max:20'],
            'zona'                  => ['required', 'string', 'min:2', 'max:100'],
            'ciudad'                => ['required', 'string', 'min:2', 'max:100'],
            'contacto_emergencia'   => ['required', 'string', 'min:2', 'max:150', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'ap_paterno_emergencia' => ['required_without:ap_materno_emergencia', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'ap_materno_emergencia' => ['required_without:ap_paterno_emergencia', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'parentesco_emergencia' => ['required', 'string', 'max:100'],
            'celular_emergencia'    => ['required', 'string', 'max:20', 'different:telefono', 'regex:/^\d+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombres.required'              => 'Por favor, escriba los nombres del usuario.',
            'nombres.regex'                 => 'El nombre solo debe contener letras y espacios.',
            'ap_paterno.required_without'   => 'Falta registrar al menos un apellido (paterno o materno) para el usuario.',
            'ap_materno.required_without'   => 'Falta registrar al menos un apellido (paterno o materno) para el usuario.',
            'numero_documento.required'     => 'Falta registrar el número de documento de identidad.',
            'numero_documento.unique'       => 'Este número de documento ya está registrado para otro usuario.',
            'correo.required'               => 'Por favor, ingrese la dirección de correo electrónico del usuario.',
            'correo.email'                  => 'La dirección de correo electrónico debe ser una cuenta válida.',
            'correo.unique'                 => 'Esta dirección de correo electrónico ya está registrada para otro usuario.',
            'expedido.required_if'          => 'Debe seleccionar el departamento de expedición para documentos de Bolivia.',
            'especialidad_salud.required_if' => 'Debe seleccionar la especialidad médica para el personal de salud.',
            'cargo_administrativo.required_if' => 'Debe seleccionar el cargo administrativo para el personal administrativo.',
            'genero.required'               => 'Debe seleccionar el género o sexo del usuario.',
            'genero.in'                     => 'Seleccione una opción de sexo válida del listado.',
            'fecha_nacimiento.required'     => 'Debe ingresar la fecha de nacimiento del usuario.',
            'fecha_nacimiento.before_or_equal' => 'El usuario registrado debe tener al menos 16 años.',
            'fecha_nacimiento.after_or_equal'  => 'El usuario registrado no puede superar los 60 años.',
            'foto_perfil.image'             => 'El archivo de foto de perfil cargado debe ser una imagen válida.',
            'foto_perfil.mimes'             => 'La foto cargada solo puede tener los formatos JPG, JPEG, PNG o WEBP.',
            'foto_perfil.max'               => 'La foto cargada no debe superar el tamaño límite de 2 MB.',
            'telefono.required'             => 'Debe registrar el número de teléfono celular de contacto.',
            'telefono.min'                  => 'El celular debe tener al menos 6 dígitos.',
            'telefono.max'                  => 'El celular no puede superar los 15 dígitos.',
            'calle.required'                => 'Debe ingresar la calle o avenida del domicilio.',
            'nro_domicilio.required'        => 'Falta ingresar el número de casa/departamento, o escriba S/N.',
            'zona.required'                 => 'Debe registrar la zona o barrio del domicilio.',
            'ciudad.required'               => 'Debe seleccionar la ciudad de residencia.',
        ];
    }
}
