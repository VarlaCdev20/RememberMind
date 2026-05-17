<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombres'           => Str::upper($this->nombres),
            'ap_paterno'        => $this->ap_paterno ? Str::upper($this->ap_paterno) : null,
            'ap_materno'        => $this->ap_materno ? Str::upper($this->ap_materno) : null,
            'correo'            => Str::lower($this->correo),
            'numero_documento'  => Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $this->numero_documento)),
            'expedido'          => ($this->pais_documento === 'Bolivia' && $this->tipo_documento === 'CI') ? Str::upper($this->expedido) : null,
            'genero'            => Str::upper($this->genero),
            'estado'            => Str::upper($this->estado),
            'acceso_sistema'    => Str::upper($this->acceso_sistema),
            'telefono'          => preg_replace('/[^0-9]/', '', $this->telefono),
        ]);
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $codUsu = is_object($usuario) ? $usuario->cod_usu : $usuario;

        return [
            'nombres'              => [
                'required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\s\-áéíóúÁÉÍÓÚñÑ]+$/u',
                function ($attribute, $value, $fail) use ($codUsu) {
                    $exists = \App\Models\User::where('nombres', $this->nombres)
                        ->where('ap_paterno', $this->ap_paterno)
                        ->where('ap_materno', $this->ap_materno)
                        ->where('cod_usu', '!=', $codUsu)
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
                'required', 'string', 'max:30', 
                Rule::unique('users', 'numero_documento')->ignore($codUsu, 'cod_usu'),
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
            'correo'               => [
                'required', 
                'email', 
                'max:150', 
                Rule::unique('users', 'correo')->ignore($codUsu, 'cod_usu')
            ],
            'rol'                  => ['required', 'string', 'exists:roles,name'],
            'especialidad_salud'   => ['required_if:rol,personal_salud', 'nullable', 'exists:especialidades,cod_esp'],
            'cargo_administrativo' => ['required_if:rol,personal_admin', 'nullable', 'exists:cargos_administrativos,cod_cargo_admin'],
            'genero'               => ['required', 'in:FEMENINO,MASCULINO,OTRO,PREFIERE NO ESPECIFICAR'],
            'fecha_nacimiento'     => [
                'required', 
                'date', 
                'before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
                'after_or_equal:' . now()->subYears(100)->format('Y-m-d')
            ],
            'estado'               => ['required', 'in:ACTIVO,INACTIVO,ARCHIVADO'],
            'acceso_sistema'       => ['required', 'in:HABILITADO,BLOQUEADO'],
            'foto_perfil'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'observaciones'        => ['nullable', 'string', 'max:500'],
            'fecha_ingreso'        => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombres.regex'                 => 'El nombre solo debe contener letras y espacios.',
            'ap_paterno.required_without'   => 'Debe ingresar al menos un apellido: paterno o materno.',
            'ap_materno.required_without'   => 'Debe ingresar al menos un apellido: paterno o materno.',
            'numero_documento.unique'       => 'Este número de documento ya está registrado.',
            'correo.unique'                 => 'Este correo ya se encuentra registrado.',
            'expedido.required_if'          => 'Debe seleccionar el lugar de expedición para documentos de Bolivia.',
            'especialidad_salud.required_if' => 'La especialidad es obligatoria para el personal de salud.',
            'cargo_administrativo.required_if' => 'El cargo es obligatorio para el personal administrativo.',
            'genero.required'               => 'El sexo es obligatorio.',
            'fecha_nacimiento.required'     => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before_or_equal' => 'El usuario debe tener entre 18 y 100 años.',
            'fecha_nacimiento.after_or_equal'  => 'El usuario debe tener entre 18 y 100 años.',
            'telefono.min'                  => 'El celular debe tener al menos 6 dígitos.',
            'telefono.max'                  => 'El celular no puede superar los 15 dígitos.',
        ];
    }
}
