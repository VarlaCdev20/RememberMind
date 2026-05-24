<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuariosPanel extends Component
{
    use WithPagination, WithFileUploads;

    // ── Filtros ──
    public $search = '';
    public $filtroRol = '';
    public $filtroEstado = '';
    public $filtroGenero = '';
    public $filtroArea = '';

    // ── Modal Formulario Crear/Editar ──
    public bool $mostrarFormulario = false;
    public int $pasoFormulario = 1;
    public ?string $usuarioId = null;
    public $isEdit = false;

    // ── Campos del Usuario ──
    public $cod_usu;
    public $nombres;
    public $ap_paterno;
    public $ap_materno;
    public $fecha_nacimiento;
    public $genero;
    public $pais_documento = 'Bolivia';
    public $tipo_documento = 'CI';
    public $numero_documento;
    public $expedido;
    public $correo;
    public $telefono;
    public $pais_telefono = '';
    public $codigo_telefono = '';
    public $rol;
    public $cod_area;
    public $estado = 'ACTIVO';
    public $acceso_sistema = 'HABILITADO';
    public $observaciones;
    public $password;
    public $password_actual;
    public $password_confirmation;

    // ── Campos condicionales (Personal Salud/Admin) ──
    public $fecha_ingreso;
    public $especialidad_salud;
    public $cargo_administrativo;

    // ── Campos adicionales ──
    public $direccion;
    public $zona;
    public $ciudad;
    public $contacto_emergencia;
    public $parentesco_emergencia;
    public $celular_emergencia;
    public $tipo_vinculacion = 'CONTRATO';
    public $matricula_prof;
    public $institucion_formacion;
    public $disponibilidad_inicial;
    public $area_apoyo_preferente;
    public $observacion_vinculo;

    // ── Nuevas propiedades de Domicilio y Emergencia ──
    public $calle;
    public $nro_domicilio;
    public $ap_paterno_emergencia;
    public $ap_materno_emergencia;

    // ── Foto de perfil upload y datos auxiliares ──
    public $foto_de_perfil_upload;
    public $edad = null;
    public $passwordTemporalVisual = null;

    // ── Vinculación de Adultos Mayores (Rol Familiar) ──
    public array $vinculosFamiliar = [];
    public $selected_cod_am = '';
    public $selected_parentesco = 'Hijo/a';
    public $selected_es_responsable = false;
    public $selected_observaciones = '';

    // Quick registration for Adulto Mayor
    public $mostrarQuickRegAdulto = false;
    public $quick_nombres = '';
    public $quick_ap_paterno = '';
    public $quick_ap_materno = '';
    public $quick_ci = '';
    public $quick_genero = 'MASCULINO';
    public $quick_fecha_nac = '';

    // ── Paises y codigos ──
    public array $paisesConfig = [
        'Bolivia' => ['codigo' => '+591', 'doc' => 'CI', 'placeholder' => 'Ej. 70012345'],
        'Argentina' => ['codigo' => '+54', 'doc' => 'DNI', 'placeholder' => 'Ej. 1112345678'],
        'Brasil' => ['codigo' => '+55', 'doc' => 'CPF', 'placeholder' => 'Ej. 11912345678'],
        'Chile' => ['codigo' => '+56', 'doc' => 'RUT', 'placeholder' => 'Ej. 912345678'],
        'Perú' => ['codigo' => '+51', 'doc' => 'DNI', 'placeholder' => 'Ej. 912345678'],
        'Paraguay' => ['codigo' => '+595', 'doc' => 'Cédula de Identidad', 'placeholder' => 'Ej. 971123456'],
        'Uruguay' => ['codigo' => '+598', 'doc' => 'Cédula de Identidad', 'placeholder' => 'Ej. 99123456'],
        'Colombia' => ['codigo' => '+57', 'doc' => 'Cédula de Ciudadanía', 'placeholder' => 'Ej. 3001234567'],
        'Ecuador' => ['codigo' => '+593', 'doc' => 'Cédula', 'placeholder' => 'Ej. 991234567'],
        'Venezuela' => ['codigo' => '+58', 'doc' => 'Cédula', 'placeholder' => 'Ej. 4121234567'],
        'México' => ['codigo' => '+52', 'doc' => 'CURP', 'placeholder' => 'Ej. 5512345678'],
        'España' => ['codigo' => '+34', 'doc' => 'DNI', 'placeholder' => 'Ej. 612345678'],
        'Estados Unidos' => ['codigo' => '+1', 'doc' => 'SSN', 'placeholder' => 'Ej. 2025550199'],
        'Otro' => ['codigo' => '', 'doc' => 'Pasaporte', 'placeholder' => 'Ej. 123456789']
    ];

    // ── Ficha Rápida Flotante (panel lateral derecho) ──
    public bool $mostrarFichaRapida = false;
    public ?string $usuarioFichaId = null;

    // ── Vista Completa (modal/panel expandido) ──
    public bool $mostrarVistaCompleta = false;
    public $usuarioVista = null;

    protected $listeners = ['usuario-guardado' => '$refresh'];

    // ══════════════════════════════════════════════
    // REACTIVE LIFECYCLE HOOKS
    // ══════════════════════════════════════════════

    public function updatedPaisTelefono($val)
    {
        if (empty($val)) {
            $this->codigo_telefono = '';
            return;
        }
        if (isset($this->paisesConfig[$val])) {
            $this->codigo_telefono = $this->paisesConfig[$val]['codigo'];
        }
    }

    public function updatedPaisDocumento($val)
    {
        if (empty($val)) {
            $this->tipo_documento = '';
            $this->pais_telefono = '';
            $this->codigo_telefono = '';
            return;
        }
        if (isset($this->paisesConfig[$val])) {
            $this->tipo_documento = $this->paisesConfig[$val]['doc'];
            $this->pais_telefono = $val;
            $this->codigo_telefono = $this->paisesConfig[$val]['codigo'];
        }
        if ($val !== 'Bolivia') {
            $this->expedido = null;
        }
    }

    public function updatedFechaNacimiento($val)
    {
        if (empty($val)) {
            $this->edad = null;
            return;
        }
        try {
            $nac = \Carbon\Carbon::parse($val);
            $this->edad = $nac->age;
        } catch (\Exception $e) {
            $this->edad = null;
        }
    }

    public function updatedRol($val)
    {
        if ($val === 'voluntario') {
            $this->cod_area = 'ARE_0008'; // Voluntariado y Relaciones Institucionales
            $this->especialidad_salud = null;
            $this->cargo_administrativo = null;
        } elseif ($val === 'familiar') {
            $this->cod_area = null;
            $this->especialidad_salud = null;
            $this->cargo_administrativo = null;
        } elseif ($val === 'personal_salud') {
            $this->cargo_administrativo = null;
            $this->cod_area = 'ARE_0004'; // Sugerencia inicial
        } elseif ($val === 'personal_admin') {
            $this->especialidad_salud = null;
            $this->cod_area = 'ARE_0003'; // Sugerencia inicial
        }
    }

    public function updatedEspecialidadSalud($val)
    {
        if ($this->rol !== 'personal_salud') return;
        
        $esp = \App\Models\Especialidad::find($val);
        if ($esp) {
            $nombre = strtoupper($esp->nombre);
            if (in_array($nombre, ['ENFERMERÍA', 'GERIATRÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])) {
                $this->cod_area = 'ARE_0004'; // Área de Atención Médica
            } elseif (in_array($nombre, ['PSICOLOGÍA', 'PEDAGOGÍA'])) {
                $this->cod_area = 'ARE_0005'; // Área de Psicología y Seguimiento Cognitivo
            }
        }
    }

    public function updatedCargoAdministrativo($val)
    {
        if ($this->rol !== 'personal_admin') return;
        
        $cargo = \App\Models\CargoAdministrativo::find($val);
        if ($cargo) {
            $nombre = strtoupper($cargo->nombre);
            if (str_contains($nombre, 'COORDINACIÓN DE PROGRAMAS') || str_contains($nombre, 'PROGRAMAS')) {
                $this->cod_area = 'ARE_0002'; // Coordinación de Programas y Servicios
            } elseif (str_contains($nombre, 'DIRECCIÓN')) {
                $this->cod_area = 'ARE_0001'; // Dirección General
            } else {
                $this->cod_area = 'ARE_0003'; // Área Administrativa y Registro Institucional
            }
        }
    }


    public function updatedCorreo($val)
    {
        $this->correo = strtolower(trim($val));
    }

    // ══════════════════════════════════════════════
    // VALIDACIÓN
    // ══════════════════════════════════════════════

    public function rules()
    {
        $rules = [
            'nombres' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'ap_paterno' => ['required_without:ap_materno', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'ap_materno' => ['required_without:ap_paterno', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'fecha_nacimiento' => [
                'required', 
                'date', 
                function ($attribute, $value, $fail) {
                    try {
                        $nac = \Carbon\Carbon::parse($value);
                        if ($nac->isFuture()) {
                            $fail('La fecha de nacimiento no puede ser una fecha futura.');
                            return;
                        }
                        $edad = $nac->age;
                        if ($edad < 16) {
                            $fail('El usuario registrado debe tener al menos 16 años.');
                        }
                        if ($edad > 60) {
                            $fail('El usuario registrado no puede superar los 60 años.');
                        }
                    } catch (\Exception $e) {
                        $fail('La fecha de nacimiento no es válida.');
                    }
                }
            ],
            'genero' => ['required', 'string', 'in:FEMENINO,MASCULINO'],
            'pais_documento' => ['required', 'string'],
            'tipo_documento' => ['required', 'string'],
            'numero_documento' => ['required', 'string', 'max:50'],
            'correo' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'correo')->ignore($this->usuarioId, 'cod_usu'),
                function ($attribute, $value, $fail) {
                    $dominio = env('INSTITUTIONAL_EMAIL_DOMAIN');
                    if (!empty($dominio)) {
                        $dominio = strtolower(trim($dominio));
                        if (!str_ends_with(strtolower($value), '@' . $dominio)) {
                            $fail("El correo debe pertenecer al dominio institucional (@{$dominio}).");
                        }
                    }
                }
            ],
            'telefono' => ['required', 'string', 'max:20'],
            'calle' => ['required', 'string', 'min:2', 'max:150'],
            'nro_domicilio' => ['required', 'string', 'max:20'],
            'zona' => ['required', 'string', 'min:2', 'max:100'],
            'ciudad' => ['required', 'string', 'min:2', 'max:100'],
            'contacto_emergencia' => ['required', 'string', 'min:2', 'max:150', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'ap_paterno_emergencia' => ['required_without:ap_materno_emergencia', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'ap_materno_emergencia' => ['required_without:ap_paterno_emergencia', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'parentesco_emergencia' => ['required', 'string', 'max:100'],
            'celular_emergencia' => ['required', 'string', 'max:20', 'different:telefono', 'regex:/^\d+$/'],
            'rol' => ['required', 'exists:roles,name'],
            'cod_area' => ['nullable', 'exists:areas_institucionales,cod_area'],
        ];

        if ($this->isEdit) {
            $rules['estado'] = ['required', 'in:ACTIVO,INACTIVO,ARCHIVADO'];
            $rules['acceso_sistema'] = ['required', 'in:HABILITADO,BLOQUEADO'];
            if ($this->usuarioId === auth()->id()) {
                $rules['password_actual'] = ['required_with:password', 'current_password'];
                $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
            }
        }

        if ($this->rol === 'personal_salud') {
            $rules['especialidad_salud'] = ['required', 'exists:especialidades,cod_esp'];
            $rules['matricula_prof'] = ['required', 'string', 'max:50'];
            $rules['institucion_formacion'] = ['nullable', 'string', 'max:255'];
            $rules['fecha_ingreso'] = ['required', 'date'];
        }

        if ($this->rol === 'personal_admin') {
            $rules['cargo_administrativo'] = ['required', 'exists:cargos_administrativos,cod_cargo_admin'];
            $rules['fecha_ingreso'] = ['required', 'date'];
        }

        if ($this->rol === 'voluntario') {
            $rules['disponibilidad_inicial'] = ['nullable', 'string', 'max:150'];
            $rules['area_apoyo_preferente'] = ['nullable', 'string', 'max:150'];
            $rules['fecha_ingreso'] = ['required', 'date'];
        }

        if ($this->rol === 'familiar') {
            $rules['observacion_vinculo'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'nombres.required' => 'Por favor, escriba los nombres del usuario.',
            'nombres.regex' => 'El nombre solo puede contener letras y espacios.',
            'ap_paterno.required_without' => 'Falta registrar al menos un apellido (paterno o materno) para el usuario.',
            'ap_materno.required_without' => 'Falta registrar al menos un apellido (paterno o materno) para el usuario.',
            'fecha_nacimiento.required' => 'Debe ingresar la fecha de nacimiento del usuario.',
            'fecha_nacimiento.date' => 'Debe ingresar una fecha de nacimiento válida.',
            'genero.required' => 'Debe seleccionar el género del usuario.',
            'numero_documento.required' => 'Falta registrar el número de documento de identidad.',
            'correo.required' => 'Por favor, ingrese la dirección de correo electrónico del usuario.',
            'correo.email' => 'La dirección de correo electrónico debe ser una cuenta válida.',
            'correo.unique' => 'Esta dirección de correo electrónico ya está registrada para otro usuario.',
            'telefono.required' => 'Debe registrar el número de teléfono celular de contacto.',
            'rol.required' => 'Debe seleccionar el rol correspondiente para este usuario.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas ingresadas no coinciden.',
            'password_actual.current_password' => 'La contraseña actual ingresada no es correcta.',
            'especialidad_salud.required' => 'Debe seleccionar la especialidad médica del profesional.',
            'cargo_administrativo.required' => 'Debe seleccionar el cargo administrativo del funcionario.',
            'fecha_ingreso.required' => 'Debe registrar la fecha de ingreso del trabajador.',
            'fecha_ingreso.date' => 'La fecha de ingreso registrada debe ser una fecha válida.',
            'matricula_prof.required' => 'Por favor, ingrese el número de matrícula profesional del personal de salud.',
            'contacto_emergencia.required' => 'Debe ingresar el nombre del contacto de emergencia.',
            'contacto_emergencia.min' => 'El nombre del contacto de emergencia debe tener al menos 2 caracteres.',
            'contacto_emergencia.regex' => 'El nombre del contacto solo puede contener letras y espacios.',
            'ap_paterno_emergencia.required_without' => 'Debe registrar al menos un apellido (paterno o materno) para el contacto de emergencia.',
            'ap_materno_emergencia.required_without' => 'Debe registrar al menos un apellido (paterno o materno) para el contacto de emergencia.',
            'ap_paterno_emergencia.regex' => 'El apellido paterno del contacto de emergencia solo puede contener letras.',
            'ap_materno_emergencia.regex' => 'El apellido materno del contacto de emergencia solo puede contener letras.',
            'parentesco_emergencia.required' => 'Debe seleccionar la relación o parentesco con el contacto de emergencia.',
            'celular_emergencia.required' => 'Debe registrar el número de celular del contacto de emergencia.',
            'celular_emergencia.different' => 'El celular del contacto de emergencia no puede ser igual al del propio usuario.',
            'celular_emergencia.regex' => 'El celular del contacto de emergencia solo puede contener números.',
            'calle.required' => 'Debe ingresar la calle o avenida del domicilio.',
            'calle.min' => 'La calle debe tener al menos 2 caracteres.',
            'nro_domicilio.required' => 'Falta ingresar el número de casa/departamento, o escriba S/N.',
            'zona.required' => 'Debe registrar la zona o barrio del domicilio.',
            'zona.min' => 'La zona debe tener al menos 2 caracteres.',
            'ciudad.required' => 'Debe seleccionar una ciudad.',
            'ciudad.min' => 'La ciudad debe tener al menos 2 caracteres.',
        ];
    }

    private function validarUnicidadDocumento($fail)
    {
        $query = User::where('pais_documento', $this->pais_documento)
            ->where('tipo_documento', $this->tipo_documento)
            ->where('numero_documento', $this->numero_documento);
        if ($this->pais_documento === 'Bolivia') {
            $query->where('expedido', $this->expedido);
        }
        if ($this->isEdit) {
            $query->where('cod_usu', '!=', $this->usuarioId);
        }
        if ($query->exists()) {
            $fail('El documento de identidad ya está registrado en el sistema.');
        }
    }

    private function validarTelefono($fail)
    {
        if (empty($this->pais_telefono)) {
            $fail('Debe seleccionar el país para el celular.');
            return;
        }

        if (empty($this->telefono)) {
            $fail('Debe registrar el número de teléfono celular.');
            return;
        }

        // 1. Unicidad del teléfono completo
        $query = User::where('codigo_telefono', $this->codigo_telefono)
            ->where('telefono', $this->telefono);
        if ($this->isEdit) {
            $query->where('cod_usu', '!=', $this->usuarioId);
        }
        if ($query->exists()) {
            $fail('El celular ya está registrado.');
            return;
        }

        // 2. Sin letras o símbolos
        if (!preg_match('/^\d+$/', $this->telefono)) {
            $fail('Debe ingresar un número de celular válido sin espacios, letras ni caracteres especiales.');
            return;
        }

        $longitud = strlen($this->telefono);
        $primerDigito = substr($this->telefono, 0, 1);

        switch ($this->pais_telefono) {
            case 'Bolivia':
                if ($longitud !== 8 || !in_array($primerDigito, ['6', '7'])) {
                    $fail('En Bolivia el celular debe tener exactamente 8 dígitos y comenzar con 6 o 7.');
                }
                break;
            case 'Chile':
                if ($longitud !== 9 || $primerDigito !== '9') {
                    $fail('En Chile el celular debe tener exactamente 9 dígitos y comenzar con 9.');
                }
                break;
            case 'Perú':
                if ($longitud !== 9 || $primerDigito !== '9') {
                    $fail('En Perú el celular debe tener exactamente 9 dígitos y comenzar con 9.');
                }
                break;
            case 'Colombia':
                if ($longitud !== 10 || $primerDigito !== '3') {
                    $fail('En Colombia el celular debe tener exactamente 10 dígitos y comenzar con 3.');
                }
                break;
            case 'México':
                if ($longitud !== 10) {
                    $fail('En México el celular debe tener exactamente 10 dígitos.');
                }
                break;
            case 'España':
                if ($longitud !== 9 || !in_array($primerDigito, ['6', '7'])) {
                    $fail('En España el celular debe tener exactamente 9 dígitos y comenzar con 6 o 7.');
                }
                break;
            case 'Estados Unidos':
                if ($longitud !== 10) {
                    $fail('En Estados Unidos el celular debe tener exactamente 10 dígitos.');
                }
                break;
            case 'Brasil':
                if ($longitud < 10 || $longitud > 11) {
                    $fail('En Brasil el celular debe tener entre 10 y 11 dígitos.');
                }
                break;
            default:
                if ($longitud < 6 || $longitud > 15) {
                    $fail('El celular debe tener entre 6 y 15 dígitos.');
                }
                break;
        }
    }

    private function normalizarTexto($texto)
    {
        if (empty($texto)) return null;
        $texto = preg_replace('/\s+/', ' ', trim($texto));
        return mb_convert_case($texto, MB_CASE_TITLE, "UTF-8");
    }

    private function normalizarMayusculas(?string $valor): ?string
    {
        return $valor ? mb_strtoupper(preg_replace('/\s+/', ' ', trim($valor)), 'UTF-8') : null;
    }

    // ══════════════════════════════════════════════
    // FORMULARIO: CREAR / EDITAR
    // ══════════════════════════════════════════════

    public function resetFormulario()
    {
        $this->reset([
            'cod_usu', 'nombres', 'ap_paterno', 'ap_materno', 'fecha_nacimiento', 'genero',
            'pais_documento', 'tipo_documento', 'numero_documento', 'expedido', 'correo',
            'telefono', 'pais_telefono', 'codigo_telefono', 'rol', 'cod_area', 'password', 'password_actual', 'password_confirmation',
            'usuarioId', 'fecha_ingreso', 'especialidad_salud', 'cargo_administrativo', 'observaciones',
            'foto_de_perfil_upload', 'edad', 'passwordTemporalVisual',
            'direccion', 'zona', 'ciudad', 'contacto_emergencia', 'parentesco_emergencia', 'celular_emergencia',
            'tipo_vinculacion', 'matricula_prof', 'institucion_formacion', 'disponibilidad_inicial',
            'area_apoyo_preferente', 'observacion_vinculo',
            'calle', 'nro_domicilio', 'ap_paterno_emergencia', 'ap_materno_emergencia',
            'vinculosFamiliar', 'selected_cod_am', 'selected_parentesco', 'selected_es_responsable', 'selected_observaciones',
            'mostrarQuickRegAdulto', 'quick_nombres', 'quick_ap_paterno', 'quick_ap_materno', 'quick_ci', 'quick_genero', 'quick_fecha_nac'
        ]);
        $this->isEdit = false;
        $this->pasoFormulario = 1;
        $this->resetValidation();
    }

    public function crearUsuario()
    {
        if (!auth()->user()->can('usuarios.crear')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para registrar usuarios.'
            ]);
            return;
        }

        $this->resetFormulario();
        $this->pais_documento = 'Bolivia';
        $this->tipo_documento = 'CI';
        $this->pais_telefono = '';
        $this->codigo_telefono = '';
        $this->fecha_ingreso = now()->format('Y-m-d');
        $this->estado = 'ACTIVO';
        $this->acceso_sistema = 'HABILITADO';
        $this->passwordTemporalVisual = $this->generarPasswordTemporal();
        $this->isEdit = false;
        $this->mostrarFormulario = true;
    }

    // ════════════════════════════════════════════════════════════════════════
    // MÉTODOS DE VINCULACIÓN Y REGISTRO DE ADULTOS MAYORES (ROL FAMILIAR)
    // ════════════════════════════════════════════════════════════════════════

    public function vincularAdultoMayor()
    {
        $this->validate([
            'selected_cod_am' => 'required',
            'selected_parentesco' => 'required|string|max:100',
        ], [
            'selected_cod_am.required' => 'Debe seleccionar un adulto mayor de la lista.',
            'selected_parentesco.required' => 'Debe ingresar o seleccionar el parentesco.',
        ]);

        $am = \App\Models\AdultoMayor::find($this->selected_cod_am);
        if (!$am) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'El adulto mayor seleccionado no existe.'
            ]);
            return;
        }

        // Verificar si ya está en la lista de vinculación
        foreach ($this->vinculosFamiliar as $vinculo) {
            if ($vinculo['cod_am'] === $this->selected_cod_am) {
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Ya vinculado',
                    'text' => 'Este adulto mayor ya está en la lista de vinculación.'
                ]);
                return;
            }
        }

        $nombreCompleto = trim("{$am->nombres} {$am->ap_paterno} {$am->ap_materno}");
        $this->vinculosFamiliar[] = [
            'cod_am' => $this->selected_cod_am,
            'nombres_completos' => $nombreCompleto,
            'parentesco_vinculo' => $this->selected_parentesco,
            'es_responsable' => $this->selected_es_responsable ? 'SI' : 'NO',
            'observaciones' => $this->selected_observaciones ?? '',
        ];

        // Limpiar campos de vinculación
        $this->selected_cod_am = '';
        $this->selected_parentesco = 'Hijo/a';
        $this->selected_es_responsable = false;
        $this->selected_observaciones = '';

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Adulto Mayor Vinculado',
            'text' => "Se ha agregado a {$nombreCompleto} a la lista de vinculación."
        ]);
    }

    public function desvincularAdultoMayor($index)
    {
        if (isset($this->vinculosFamiliar[$index])) {
            $nombre = $this->vinculosFamiliar[$index]['nombres_completos'];
            unset($this->vinculosFamiliar[$index]);
            $this->vinculosFamiliar = array_values($this->vinculosFamiliar);
            
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Vínculo removido',
                'text' => "Se quitó a {$nombre} de la lista de vinculación."
            ]);
        }
    }

    public function registrarYVincularAdulto()
    {
        $this->validate([
            'quick_nombres' => 'required|string|max:100',
            'quick_ap_paterno' => 'required|string|max:100',
            'quick_ap_materno' => 'nullable|string|max:100',
            'quick_ci' => 'required|string|max:20|unique:adulto_mayor,ci',
            'quick_genero' => 'required|in:MASCULINO,FEMENINO,OTRO',
            'quick_fecha_nac' => 'required|date|before:today',
        ], [
            'quick_nombres.required' => 'El nombre es obligatorio.',
            'quick_ap_paterno.required' => 'El apellido paterno es obligatorio.',
            'quick_ci.required' => 'El CI/Documento es obligatorio.',
            'quick_ci.unique' => 'Ya existe un adulto mayor registrado con este número de CI/Documento.',
            'quick_fecha_nac.required' => 'La fecha de nacimiento es obligatoria.',
            'quick_fecha_nac.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        ]);

        // Registrar Adulto Mayor
        $am = new \App\Models\AdultoMayor();
        $am->nombres = $this->normalizarTexto($this->quick_nombres);
        $am->ap_paterno = $this->normalizarTexto($this->quick_ap_paterno);
        $am->ap_materno = $this->normalizarTexto($this->quick_ap_materno);
        $am->ci = $this->quick_ci;
        $am->genero = $this->quick_genero;
        $am->fecha_nac = $this->quick_fecha_nac;
        $am->cod_est_adul = 1; // ACTIVO
        $am->fecha_ing = now()->format('Y-m-d');
        $am->save();

        $nombreCompleto = trim("{$am->nombres} {$am->ap_paterno} {$am->ap_materno}");
        
        // Agregar automáticamente a la lista de vinculación
        $this->vinculosFamiliar[] = [
            'cod_am' => $am->cod_am,
            'nombres_completos' => $nombreCompleto,
            'parentesco_vinculo' => $this->selected_parentesco,
            'es_responsable' => $this->selected_es_responsable ? 'SI' : 'NO',
            'observaciones' => $this->selected_observaciones ?? '',
        ];

        // Resetear campos quick reg
        $this->quick_nombres = '';
        $this->quick_ap_paterno = '';
        $this->quick_ap_materno = '';
        $this->quick_ci = '';
        $this->quick_fecha_nac = '';
        $this->mostrarQuickRegAdulto = false;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro Exitoso',
            'text' => "Se registró a {$nombreCompleto} y se vinculó automáticamente al familiar."
        ]);
    }

    public function editarUsuario($cod_usu)
    {
        if (!auth()->user()->can('usuarios.editar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para editar usuarios.'
            ]);
            return;
        }

        // Protección absoluta de USU_0001
        if ($cod_usu === 'USU_0001' && auth()->id() !== 'USU_0001') {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permisos para modificar al Administrador Principal.'
            ]);
            return;
        }

        $usuario = User::with(['personalSalud', 'personalAdmin'])->findOrFail($cod_usu);

        if ($usuario->estado !== 'ACTIVO') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acción no permitida',
                'text' => 'El usuario está inactivo. Debe activarlo antes de editar.'
            ]);
            return;
        }

        $this->resetValidation();
        $this->usuarioId = $cod_usu;
        $this->pasoFormulario = 1;
        $this->isEdit = true;

        $this->cod_usu = $usuario->cod_usu;
        $this->nombres = $usuario->nombres;
        $this->ap_paterno = $usuario->ap_paterno;
        $this->ap_materno = $usuario->ap_materno;
        $this->fecha_nacimiento = $usuario->fecha_nacimiento ? \Carbon\Carbon::parse($usuario->fecha_nacimiento)->format('Y-m-d') : '';
        if ($this->fecha_nacimiento) {
            $this->updatedFechaNacimiento($this->fecha_nacimiento);
        }
        $this->genero = $usuario->genero;
        $this->pais_documento = $usuario->pais_documento ?? 'Bolivia';
        $this->tipo_documento = $usuario->tipo_documento ?? 'CI';
        $this->numero_documento = $usuario->numero_documento;
        $this->expedido = $usuario->expedido;
        $this->correo = $usuario->correo;
        $this->telefono = $usuario->telefono;
        $this->pais_telefono = $usuario->pais_telefono ?? 'Bolivia';
        $this->codigo_telefono = $usuario->codigo_telefono ?? '+591';
        $this->cod_area = $usuario->cod_area;
        $this->estado = $usuario->estado;
        $this->acceso_sistema = $usuario->acceso_sistema;
        $this->observaciones = $usuario->observaciones;

        $this->direccion = $usuario->direccion;
        $this->calle = $usuario->calle;
        $this->nro_domicilio = $usuario->nro_domicilio;
        $this->zona = $usuario->zona;
        $this->ciudad = $usuario->ciudad;
        
        $this->contacto_emergencia = $usuario->contacto_emergencia;
        $this->ap_paterno_emergencia = $usuario->ap_paterno_emergencia;
        $this->ap_materno_emergencia = $usuario->ap_materno_emergencia;
        $this->parentesco_emergencia = $usuario->parentesco_emergencia;
        $this->celular_emergencia = $usuario->celular_emergencia;
        $this->tipo_vinculacion = $usuario->tipo_vinculacion ?? 'CONTRATO';

        $this->rol = $usuario->roles->first()?->name ?? '';

        if ($this->rol === 'personal_salud') {
            $ps = $usuario->personalSalud;
            $this->fecha_ingreso = $ps?->fecha_ing ? \Carbon\Carbon::parse($ps->fecha_ing)->format('Y-m-d') : '';
            $this->especialidad_salud = $ps?->cod_esp;
            $this->matricula_prof = $ps?->matricula_prof;
            $this->institucion_formacion = $ps?->institucion_formacion;
        } elseif ($this->rol === 'personal_admin') {
            $pa = $usuario->personalAdmin;
            $this->fecha_ingreso = $pa?->fecha_ingreso ? \Carbon\Carbon::parse($pa->fecha_ingreso)->format('Y-m-d') : '';
            $this->cargo_administrativo = $pa?->cod_cargo_admin;
        } elseif ($this->rol === 'voluntario') {
            $vol = \App\Models\Voluntario::where('cod_usu', $usuario->cod_usu)->first();
            if ($vol) {
                $this->fecha_ingreso = $vol->fecha_ing ? \Carbon\Carbon::parse($vol->fecha_ing)->format('Y-m-d') : '';
                $this->disponibilidad_inicial = $vol->disponibilidad_inicial;
                $this->area_apoyo_preferente = $vol->area_apoyo_preferente;
            }
        } elseif ($this->rol === 'familiar') {
            $fam = \App\Models\Familiar::where('cod_usu', $usuario->cod_usu)->first();
            if ($fam) {
                $this->parentesco_emergencia = $fam->parentesco;
                $this->observacion_vinculo = $fam->observacion_vinculo;
                
                // Cargar adultos mayores vinculados existentes
                $this->vinculosFamiliar = [];
                foreach ($fam->adultosMayores as $am) {
                    $this->vinculosFamiliar[] = [
                        'cod_am' => $am->cod_am,
                        'nombres_completos' => trim("{$am->nombres} {$am->ap_paterno} {$am->ap_materno}"),
                        'parentesco_vinculo' => $am->pivot->parentesco_vinculo ?? 'Familiar',
                        'es_responsable' => $am->pivot->es_responsable ? 'SI' : 'NO',
                        'observaciones' => $am->pivot->observaciones ?? '',
                    ];
                }
            }
        }

        $this->password = '';
        $this->password_confirmation = '';
        $this->foto_de_perfil_upload = null;
        $this->passwordTemporalVisual = null;

        // Cerrar paneles flotantes si estaban abiertos
        $this->cerrarFichaRapida();
        $this->cerrarVistaCompleta();

        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario()
    {
        $this->resetFormulario();
        $this->mostrarFormulario = false;
    }

    public function siguientePaso()
    {
        if ($this->pasoFormulario === 1) {
            $this->nombres = $this->normalizarMayusculas($this->nombres);
            $this->ap_paterno = $this->normalizarMayusculas($this->ap_paterno);
            $this->ap_materno = $this->normalizarMayusculas($this->ap_materno);

            $this->validate([
                'nombres' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
                'fecha_nacimiento' => [
                    'required', 
                    'date', 
                    function ($attribute, $value, $fail) {
                        try {
                            $nac = \Carbon\Carbon::parse($value);
                            if ($nac->isFuture()) {
                                $fail('La fecha de nacimiento no puede ser una fecha futura.');
                                return;
                            }
                            $edad = $nac->age;
                            if ($edad < 16) {
                                $fail('El usuario registrado debe tener al menos 16 años.');
                            }
                            if ($edad > 60) {
                                $fail('El usuario registrado no puede superar los 60 años.');
                            }
                        } catch (\Exception $e) {
                            $fail('La fecha de nacimiento no es válida.');
                        }
                    }
                ],
                'genero' => ['required', 'string', 'in:FEMENINO,MASCULINO'],
                'pais_documento' => ['required', 'string'],
                'tipo_documento' => ['required', 'string'],
                'numero_documento' => ['required', 'string', 'max:50'],
            ], $this->messages());

            if (empty($this->ap_paterno) && empty($this->ap_materno)) {
                $this->addError('ap_paterno', 'Debe registrar al menos un apellido (paterno o materno).');
                $this->addError('ap_materno', 'Debe registrar al menos un apellido (paterno o materno).');
                return;
            }

            if ($this->pais_documento === 'Bolivia' && $this->tipo_documento === 'CI') {
                if (!preg_match('/^\d+$/', $this->numero_documento) || strlen($this->numero_documento) < 5 || strlen($this->numero_documento) > 10) {
                    $this->addError('numero_documento', 'Para Bolivia el CI debe ser numérico y tener entre 5 y 10 dígitos.');
                    return;
                }
                if (empty($this->expedido)) {
                    $this->addError('expedido', 'El departamento de expedición es obligatorio para Bolivia.');
                    return;
                }
            }

            $this->validarUnicidadDocumento(function($err) {
                $this->addError('numero_documento', $err);
            });
            if ($this->getErrorBag()->has('numero_documento')) {
                return;
            }

            if ($this->foto_de_perfil_upload) {
                $this->validate([
                    'foto_de_perfil_upload' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']
                ]);
            }
        } elseif ($this->pasoFormulario === 2) {
            $this->correo = strtolower(trim($this->correo));
            $this->contacto_emergencia = $this->normalizarMayusculas($this->contacto_emergencia);
            $this->ap_paterno_emergencia = $this->normalizarMayusculas($this->ap_paterno_emergencia);
            $this->ap_materno_emergencia = $this->normalizarMayusculas($this->ap_materno_emergencia);
            $this->calle = $this->normalizarMayusculas($this->calle);
            $this->nro_domicilio = $this->nro_domicilio ? mb_strtoupper(trim($this->nro_domicilio), 'UTF-8') : $this->nro_domicilio;
            $this->zona = $this->normalizarMayusculas($this->zona);
            $this->ciudad = $this->normalizarMayusculas($this->ciudad);

            $this->validate([
                'correo' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users', 'correo')->ignore($this->usuarioId, 'cod_usu'),
                    function ($attribute, $value, $fail) {
                        $dominio = env('INSTITUTIONAL_EMAIL_DOMAIN');
                        if (!empty($dominio)) {
                            $dominio = strtolower(trim($dominio));
                            if (!str_ends_with(strtolower($value), '@' . $dominio)) {
                                $fail("El correo debe pertenecer al dominio institucional (@{$dominio}).");
                            }
                        }
                    }
                ],
                'telefono' => ['required', 'string', 'max:20'],
                'calle' => ['required', 'string', 'min:2', 'max:150'],
                'nro_domicilio' => ['required', 'string', 'max:20'],
                'zona' => ['required', 'string', 'min:2', 'max:100'],
                'ciudad' => ['required', 'string', 'min:2', 'max:100'],
                'contacto_emergencia' => ['required', 'string', 'min:2', 'max:150', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
                'ap_paterno_emergencia' => ['required_without:ap_materno_emergencia', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
                'ap_materno_emergencia' => ['required_without:ap_paterno_emergencia', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
                'parentesco_emergencia' => ['required', 'string', 'max:100'],
                'celular_emergencia' => ['required', 'string', 'max:20', 'different:telefono', 'regex:/^\d+$/'],
            ], array_merge($this->messages(), [
                'contacto_emergencia.regex' => 'El nombre del contacto solo puede contener letras.',
                'ap_paterno_emergencia.regex' => 'El apellido del contacto solo puede contener letras.',
                'ap_materno_emergencia.regex' => 'El apellido del contacto solo puede contener letras.',
                'celular_emergencia.different' => 'El celular de emergencia no puede ser igual al celular del usuario.',
                'celular_emergencia.regex' => 'El celular de emergencia solo puede contener números.'
            ]));

            $this->validarTelefono(function($err) {
                $this->addError('telefono', $err);
            });
            if ($this->getErrorBag()->has('telefono')) {
                return;
            }
        } elseif ($this->pasoFormulario === 3) {
            $this->tipo_vinculacion = match($this->rol) {
                'personal_salud' => 'CONTRATO',
                'personal_admin' => 'CONTRATO',
                'voluntario' => 'VOLUNTARIADO',
                'familiar' => 'FAMILIAR',
                default => 'OTRO'
            };

            if (!$this->isEdit) {
                $this->fecha_ingreso = now()->format('Y-m-d');
                $this->estado = 'ACTIVO';
                $this->acceso_sistema = 'HABILITADO';
            }

            $rules = [
                'rol' => ['required', 'exists:roles,name'],
            ];

            if ($this->isEdit) {
                $rules['estado'] = ['required', 'in:ACTIVO,INACTIVO,ARCHIVADO'];
                $rules['acceso_sistema'] = ['required', 'in:HABILITADO,BLOQUEADO'];
            }

            if ($this->rol === 'personal_salud') {
                $rules['especialidad_salud'] = ['required', 'exists:especialidades,cod_esp'];
                $rules['matricula_prof'] = ['required', 'string', 'max:50'];
                $rules['institucion_formacion'] = ['nullable', 'string', 'max:255'];
                $rules['fecha_ingreso'] = ['required', 'date'];
            }

            if ($this->rol === 'personal_admin') {
                $rules['cargo_administrativo'] = ['required', 'exists:cargos_administrativos,cod_cargo_admin'];
                $rules['fecha_ingreso'] = ['required', 'date'];
            }

            if ($this->rol === 'voluntario') {
                $rules['disponibilidad_inicial'] = ['nullable', 'string', 'max:150'];
                $rules['area_apoyo_preferente'] = ['nullable', 'string', 'max:150'];
                $rules['fecha_ingreso'] = ['required', 'date'];
            }

            if ($this->rol === 'familiar') {
                $rules['observacion_vinculo'] = ['nullable', 'string', 'max:255'];
            }

            $this->validate($rules, array_merge($this->messages(), [
                'matricula_prof.required' => 'Por favor, ingrese el número de matrícula profesional del personal de salud.',
                'fecha_ingreso.required' => 'Debe registrar la fecha de ingreso institucional.',
            ]));

            if (!$this->isEdit && empty($this->passwordTemporalVisual)) {
                $this->passwordTemporalVisual = $this->generarPasswordTemporal();
            }
        } elseif ($this->pasoFormulario === 4) {
            if ($this->isEdit && $this->usuarioId === auth()->id()) {
                $this->validate([
                    'password_actual' => ['required_with:password', 'current_password'],
                    'password' => ['nullable', 'string', 'min:8', 'confirmed']
                ]);
            }
        }

        $this->pasoFormulario++;
    }

    public function anteriorPaso()
    {
        if ($this->pasoFormulario > 1) {
            $this->pasoFormulario--;
        }
    }

    public function irPaso($paso)
    {
        if ($paso < $this->pasoFormulario) {
            $this->pasoFormulario = $paso;
        }
    }

    private function generarPasswordTemporal(): string
    {
        $letras = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numeros = '0123456789';
        $simbolos = '@#$*!';
        
        $pass = '';
        $pass .= $letras[rand(0, strlen($letras) - 1)];
        $pass .= $letras[rand(0, strlen($letras) - 1)];
        $pass .= $numeros[rand(0, strlen($numeros) - 1)];
        $pass .= $simbolos[rand(0, strlen($simbolos) - 1)];
        
        $todos = $letras . $numeros . $simbolos;
        for ($i = 0; $i < 7; $i++) {
            $pass .= $todos[rand(0, strlen($todos) - 1)];
        }
        
        return str_shuffle($pass);
    }

    public function regenerarPasswordTemporal()
    {
        $this->passwordTemporalVisual = $this->generarPasswordTemporal();
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Contraseña regenerada',
            'text' => 'Se ha generado una nueva contraseña temporal.'
        ]);
    }

    public function guardarUsuario()
    {
        $permisoRequerido = $this->isEdit ? 'usuarios.editar' : 'usuarios.crear';
        if (!auth()->user()->can($permisoRequerido)) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para realizar esta acción.'
            ]);
            return;
        }

        $this->nombres = $this->normalizarMayusculas($this->nombres);
        $this->ap_paterno = $this->normalizarMayusculas($this->ap_paterno);
        $this->ap_materno = $this->normalizarMayusculas($this->ap_materno);

        $rules = $this->rules();
        $this->validate($rules);

        if (empty($this->ap_paterno) && empty($this->ap_materno)) {
            $this->addError('ap_paterno', 'Debe registrar al menos un apellido (paterno o materno).');
            $this->addError('ap_materno', 'Debe registrar al menos un apellido (paterno o materno).');
            return;
        }

        if ($this->pais_documento === 'Bolivia' && $this->tipo_documento === 'CI') {
            if (!preg_match('/^\d+$/', $this->numero_documento) || strlen($this->numero_documento) < 5 || strlen($this->numero_documento) > 10) {
                $this->addError('numero_documento', 'Para Bolivia el CI debe ser numérico y tener entre 5 y 10 dígitos.');
                return;
            }
            if (empty($this->expedido)) {
                $this->addError('expedido', 'El departamento de expedición es obligatorio para Bolivia.');
                return;
            }
        }

        $this->validarUnicidadDocumento(function($err) {
            $this->addError('numero_documento', $err);
        });
        if ($this->getErrorBag()->has('numero_documento')) {
            return;
        }

        $this->validarTelefono(function($err) {
            $this->addError('telefono', $err);
        });
        if ($this->getErrorBag()->has('telefono')) {
            return;
        }

        // Determinar estado y acceso por defecto en modo creación, protegiendo a USU_0001
        $estadoGuardado = $this->estado;
        $accesoGuardado = $this->acceso_sistema;

        if (!$this->isEdit) {
            $estadoGuardado = 'ACTIVO';
            $accesoGuardado = 'HABILITADO';
        }

        if ($this->isEdit && $this->usuarioId === 'USU_0001') {
            $estadoGuardado = 'ACTIVO';
            $accesoGuardado = 'HABILITADO';
            $this->rol = 'super_admin';
            $this->correo = 'admincasaamandita@gmail.com';
        }

        $this->direccion = 'Calle ' . $this->calle . ' Nº ' . $this->nro_domicilio . ', ' . $this->zona . ', ' . $this->ciudad;

        $userData = [
            'nombres' => $this->nombres,
            'ap_paterno' => $this->ap_paterno,
            'ap_materno' => $this->ap_materno,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'genero' => $this->genero,
            'pais_documento' => $this->pais_documento,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'expedido' => $this->expedido,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'pais_telefono' => $this->pais_telefono,
            'codigo_telefono' => $this->codigo_telefono,
            'cod_area' => $this->cod_area ?: null,
            'acceso_sistema' => $accesoGuardado,
            'observaciones' => $this->observaciones,
            'calle' => $this->calle,
            'nro_domicilio' => $this->nro_domicilio,
            'zona' => $this->zona,
            'ciudad' => $this->ciudad,
            'direccion' => $this->direccion,
            'contacto_emergencia' => $this->contacto_emergencia,
            'ap_paterno_emergencia' => $this->ap_paterno_emergencia,
            'ap_materno_emergencia' => $this->ap_materno_emergencia,
            'parentesco_emergencia' => $this->parentesco_emergencia,
            'celular_emergencia' => $this->celular_emergencia,
            'tipo_vinculacion' => $this->tipo_vinculacion,
        ];

        if ($this->foto_de_perfil_upload) {
            if ($this->isEdit && $this->usuarioId) {
                $oldUser = User::find($this->usuarioId);
                if ($oldUser && $oldUser->foto_de_perfil && \Storage::disk('public')->exists($oldUser->foto_de_perfil)) {
                    \Storage::disk('public')->delete($oldUser->foto_de_perfil);
                }
            }
            $path = $this->foto_de_perfil_upload->store('usuarios/fotos', 'public');
            $userData['foto_de_perfil'] = $path;
        }

        $mensaje = '';
        $correoEnviado = false;
        $passwordTemporal = null;

        if ($this->isEdit) {
            $userData['estado'] = $estadoGuardado;
            $userData['acceso_sistema'] = $accesoGuardado;

            $usuario = User::findOrFail($this->usuarioId);

            if ($usuario->cod_usu === auth()->id()) {
                if (!empty($this->password)) {
                    $userData['password'] = Hash::make($this->password);
                    $userData['debe_cambiar_password'] = false;
                }
            } else {
                // For other users, password changes are NOT allowed via general edit form
                unset($userData['password']);
            }

            if ($usuario->hasRole('super_admin') && $this->rol !== 'super_admin') {
                $superadmins = User::role('super_admin')->where('estado', 'ACTIVO')->count();
                if ($superadmins <= 1) {
                    $this->addError('rol', 'No puedes quitar el rol de superadministrador al único superadministrador activo.');
                    return;
                }
            }

            $usuario->update($userData);
            $usuario->syncRoles([$this->rol]);

            // Guardar sub-modelos de forma unificada
            if ($this->rol === 'personal_salud') {
                \App\Models\PersonalSalud::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    [
                        'fecha_ing' => $this->fecha_ingreso ?: now()->format('Y-m-d'), 
                        'cod_esp' => $this->especialidad_salud,
                        'matricula_prof' => $this->matricula_prof,
                        'institucion_formacion' => $this->institucion_formacion,
                        'estado_laboral' => 'ACTIVO'
                    ]
                );
                \App\Models\PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Voluntario::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Familiar::where('cod_usu', $usuario->cod_usu)->delete();
            } elseif ($this->rol === 'personal_admin') {
                \App\Models\PersonalAdmin::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    [
                        'fecha_ingreso' => $this->fecha_ingreso ?: now()->format('Y-m-d'), 
                        'cod_cargo_admin' => $this->cargo_administrativo,
                        'cargo' => \App\Models\CargoAdministrativo::find($this->cargo_administrativo)?->nombre ?? 'Administrativo',
                        'area_admin' => $this->cod_area ? (\App\Models\AreaInstitucional::find($this->cod_area)?->nombre ?? 'Administración') : 'Administración',
                        'estado_laboral' => 'ACTIVO'
                    ]
                );
                \App\Models\PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Voluntario::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Familiar::where('cod_usu', $usuario->cod_usu)->delete();
            } elseif ($this->rol === 'voluntario') {
                \App\Models\Voluntario::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    [
                        'fecha_ing' => $this->fecha_ingreso ?: now()->format('Y-m-d'),
                        'area_apoyo' => $this->area_apoyo_preferente ?? 'General',
                        'disponibilidad_inicial' => $this->disponibilidad_inicial,
                        'area_apoyo_preferente' => $this->area_apoyo_preferente,
                        'estado' => 'ACTIVO'
                    ]
                );
                \App\Models\PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Familiar::where('cod_usu', $usuario->cod_usu)->delete();
            } elseif ($this->rol === 'familiar') {
                $fam = \App\Models\Familiar::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    [
                        'parentesco' => $this->parentesco_emergencia ?? 'Familiar',
                        'direccion' => $this->direccion,
                        'es_responsable' => 'SI',
                        'observacion_vinculo' => $this->observacion_vinculo
                    ]
                );
                
                // Sincronizar vínculos de adultos mayores
                $fam->adultosMayores()->detach();
                foreach ($this->vinculosFamiliar as $vinculo) {
                    $fam->adultosMayores()->attach($vinculo['cod_am'], [
                        'parentesco_vinculo' => $vinculo['parentesco_vinculo'],
                        'es_responsable' => $vinculo['es_responsable'] === 'SI',
                        'estado' => 'ACTIVO',
                        'observaciones' => $vinculo['observaciones'],
                    ]);
                }
                
                \App\Models\PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Voluntario::where('cod_usu', $usuario->cod_usu)->delete();
            }

            // Notificación por correo de clave actualizada
            $correoNotifEnviado = false;
            $passwordModificada = ($usuario->cod_usu === auth()->id() && !empty($this->password));
            if ($passwordModificada) {
                try {
                    \Mail::to($usuario->correo)->send(new \App\Mail\UsuarioPasswordActualizadaMail($usuario, $this->password));
                    $correoNotifEnviado = true;
                } catch (\Exception $e) {
                    \Log::error("Error al enviar correo de actualización de clave a {$usuario->correo}: " . $e->getMessage());
                }
            }

            if (function_exists('activity')) {
                $actMsg = "Se actualizaron los datos del usuario: {$usuario->name}.";
                if ($passwordModificada) {
                    $actMsg .= $correoNotifEnviado ? " Contraseña actualizada y notificada por correo." : " Contraseña actualizada, pero falló envío de notificación.";
                }
                activity('Usuarios')
                    ->causedBy(auth()->user())
                    ->performedOn($usuario)
                    ->event('edicion')
                    ->log($actMsg);
            }

            $mensaje = 'Usuario actualizado correctamente.';
            if ($passwordModificada && !$correoNotifEnviado) {
                $mensaje .= " ADVERTENCIA: No se pudo enviar el correo de notificación. La nueva contraseña es: {$this->password}";
                $swalData = [
                    'icon' => 'warning',
                    'title' => 'Usuario actualizado con advertencia',
                    'text' => $mensaje
                ];
            } else {
                $swalData = [
                    'icon' => 'success',
                    'title' => 'Éxito',
                    'text' => $mensaje
                ];
            }

            $this->resetFormulario();
            $this->mostrarFormulario = false;
            $this->dispatch('swal', $swalData);

        } else {
            $userData['estado'] = 'ACTIVO';
            $userData['acceso_sistema'] = 'HABILITADO';
            $passwordTemporal = $this->passwordTemporalVisual ?: $this->generarPasswordTemporal();
            $userData['password'] = Hash::make($passwordTemporal);
            $userData['debe_cambiar_password'] = true;

            $usuario = User::create($userData);
            $usuario->assignRole($this->rol);

            // Guardar sub-modelos de forma unificada
            if ($this->rol === 'personal_salud') {
                \App\Models\PersonalSalud::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    [
                        'fecha_ing' => $this->fecha_ingreso ?: now()->format('Y-m-d'), 
                        'cod_esp' => $this->especialidad_salud,
                        'matricula_prof' => $this->matricula_prof,
                        'institucion_formacion' => $this->institucion_formacion,
                        'estado_laboral' => 'ACTIVO'
                    ]
                );
                \App\Models\PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Voluntario::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Familiar::where('cod_usu', $usuario->cod_usu)->delete();
            } elseif ($this->rol === 'personal_admin') {
                \App\Models\PersonalAdmin::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    [
                        'fecha_ingreso' => $this->fecha_ingreso ?: now()->format('Y-m-d'), 
                        'cod_cargo_admin' => $this->cargo_administrativo,
                        'cargo' => \App\Models\CargoAdministrativo::find($this->cargo_administrativo)?->nombre ?? 'Administrativo',
                        'area_admin' => $this->cod_area ? (\App\Models\AreaInstitucional::find($this->cod_area)?->nombre ?? 'Administración') : 'Administración',
                        'estado_laboral' => 'ACTIVO'
                    ]
                );
                \App\Models\PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Voluntario::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Familiar::where('cod_usu', $usuario->cod_usu)->delete();
            } elseif ($this->rol === 'voluntario') {
                \App\Models\Voluntario::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    [
                        'fecha_ing' => $this->fecha_ingreso ?: now()->format('Y-m-d'),
                        'area_apoyo' => $this->area_apoyo_preferente ?? 'General',
                        'disponibilidad_inicial' => $this->disponibilidad_inicial,
                        'area_apoyo_preferente' => $this->area_apoyo_preferente,
                        'estado' => 'ACTIVO'
                    ]
                );
                \App\Models\PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Familiar::where('cod_usu', $usuario->cod_usu)->delete();
            } elseif ($this->rol === 'familiar') {
                $fam = \App\Models\Familiar::updateOrCreate(
                    ['cod_usu' => $usuario->cod_usu],
                    [
                        'parentesco' => $this->parentesco_emergencia ?? 'Familiar',
                        'direccion' => $this->direccion,
                        'es_responsable' => 'SI',
                        'observacion_vinculo' => $this->observacion_vinculo
                    ]
                );
                
                // Sincronizar vínculos de adultos mayores
                $fam->adultosMayores()->detach();
                foreach ($this->vinculosFamiliar as $vinculo) {
                    $fam->adultosMayores()->attach($vinculo['cod_am'], [
                        'parentesco_vinculo' => $vinculo['parentesco_vinculo'],
                        'es_responsable' => $vinculo['es_responsable'] === 'SI',
                        'estado' => 'ACTIVO',
                        'observaciones' => $vinculo['observaciones'],
                    ]);
                }
                
                \App\Models\PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
                \App\Models\Voluntario::where('cod_usu', $usuario->cod_usu)->delete();
            }

            // Enviar bienvenida
            $bienvenidaEnviada = false;
            try {
                $rolDisplay = strtoupper(str_replace('_', ' ', $this->rol));
                $areaDisplay = 'Sin área';
                if ($this->cod_area) {
                    $areaObj = \App\Models\AreaInstitucional::find($this->cod_area);
                    if ($areaObj) {
                        $areaDisplay = $areaObj->nombre;
                    }
                }
                \Mail::to($usuario->correo)->send(new \App\Mail\UsuarioBienvenidaMail($usuario, $rolDisplay, $areaDisplay));
                $bienvenidaEnviada = true;
            } catch (\Exception $e) {
                \Log::error("Error al enviar correo de bienvenida a {$usuario->correo}: " . $e->getMessage());
            }

            // Enviar credenciales
            $credencialesEnviadas = false;
            try {
                \Mail::to($usuario->correo)->send(new \App\Mail\UsuarioCredencialesInicialesMail($usuario, $passwordTemporal));
                $credencialesEnviadas = true;
            } catch (\Exception $e) {
                \Log::error("Error al enviar credenciales a {$usuario->correo}: " . $e->getMessage());
            }

            if (function_exists('activity')) {
                $logMsg = "Se registró un nuevo usuario: {$usuario->name} con rol {$this->rol}. " . ($credencialesEnviadas ? "Correo de credenciales enviado con éxito." : "Error al enviar correo de credenciales.");
                activity('Usuarios')
                    ->causedBy(auth()->user())
                    ->performedOn($usuario)
                    ->event('registro')
                    ->log($logMsg);
            }

            $this->resetFormulario();
            $this->mostrarFormulario = false;
            $this->resetPage();

            $this->dispatch('mostrar-post-registro', [
                'usuario_id' => $usuario->cod_usu,
                'nombre' => $usuario->nombres . ' ' . $usuario->ap_paterno . ' ' . $usuario->ap_materno,
                'email' => $usuario->correo,
                'credenciales_enviadas' => $credencialesEnviadas,
                'password_temporal' => $passwordTemporal,
            ]);
        }
    }

    // ══════════════════════════════════════════════
    // VISTA COMPLETA (modal/panel expandido)
    // ══════════════════════════════════════════════

    public function abrirVistaCompleta($codUsu)
    {
        return redirect()->route('admin.usuarios.show', $codUsu);
    }

    public function cerrarVistaCompleta(): void
    {
        $this->mostrarVistaCompleta = false;
        $this->usuarioVista = null;
    }

    // ══════════════════════════════════════════════
    // FICHA RÁPIDA FLOTANTE (panel lateral derecho)
    // ══════════════════════════════════════════════

    public function abrirFichaRapida($codUsu): void
    {
        $this->usuarioFichaId = $codUsu;
        $this->mostrarFichaRapida = true;
    }

    public function cerrarFichaRapida(): void
    {
        $this->mostrarFichaRapida = false;
        $this->usuarioFichaId = null;
    }

    // ══════════════════════════════════════════════
    // FILTROS
    // ══════════════════════════════════════════════

    public function aplicarFiltros(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'filtroRol', 'filtroEstado', 'filtroGenero', 'filtroArea']);
        $this->resetPage();
    }



    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroRol()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroArea()
    {
        $this->resetPage();
    }

    // ══════════════════════════════════════════════
    // TOGGLE ESTADO (ACTIVAR / INACTIVAR)
    // ══════════════════════════════════════════════

    public function toggleEstado($id)
    {
        if (!auth()->user()->can('usuarios.cambiar_estado')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para cambiar el estado de usuarios.'
            ]);
            return;
        }

        $usuario = User::findOrFail($id);

        // Protección absoluta del Administrador Principal
        if ($usuario->cod_usu === 'USU_0001' || $usuario->correo === 'admincasaamandita@gmail.com') {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción denegada',
                'text' => 'No puedes cambiar el estado del Administrador Principal.'
            ]);
            return;
        }

        // No permitir autoinactivación
        if ($usuario->cod_usu === auth()->id()) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción denegada',
                'text' => 'No puedes cambiar tu propio estado.'
            ]);
            return;
        }

        // Proteger último super_admin
        if ($usuario->estado === 'ACTIVO' && $usuario->hasRole('super_admin')) {
            $superadmins = User::role('super_admin')->where('estado', 'ACTIVO')->count();
            if ($superadmins <= 1) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Acción denegada',
                    'text' => 'No puedes desactivar al único superadministrador activo.'
                ]);
                return;
            }
        }

        $nuevoEstado = $usuario->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        $usuario->update(['estado' => $nuevoEstado]);

        // Registrar en bitácora
        if (function_exists('activity')) {
            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event($nuevoEstado === 'ACTIVO' ? 'activacion' : 'inactivacion')
                ->log("Se cambió el estado de {$usuario->name} a {$nuevoEstado}.");
        }

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado actualizado',
            'text' => "El usuario ahora está {$nuevoEstado}."
        ]);
    }

    public function restablecerPasswordUsuario($cod_usu)
    {
        if (!auth()->user()->can('usuarios.editar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para restablecer contraseñas.'
            ]);
            return;
        }

        $usuario = User::findOrFail($cod_usu);

        // Protección absoluta de USU_0001
        if ($usuario->cod_usu === 'USU_0001' && auth()->id() !== 'USU_0001') {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción no permitida',
                'text' => 'No tienes permisos para restablecer la contraseña del Administrador Principal.'
            ]);
            return;
        }

        $passwordTemporal = $this->generarPasswordTemporal();
        $usuario->update([
            'password' => Hash::make($passwordTemporal),
            'debe_cambiar_password' => true
        ]);

        $correoNotifEnviado = false;
        try {
            \Mail::to($usuario->correo)->send(new \App\Mail\UsuarioPasswordActualizadaMail($usuario, $passwordTemporal));
            $correoNotifEnviado = true;
        } catch (\Exception $e) {
            \Log::error("Error al enviar correo de restablecimiento a {$usuario->correo}: " . $e->getMessage());
        }

        if (function_exists('activity')) {
            $actMsg = "Se restableció la contraseña del usuario: {$usuario->name}.";
            if ($correoNotifEnviado) {
                $actMsg .= " Notificación enviada con éxito.";
            } else {
                $actMsg .= " Error al enviar notificación por correo.";
            }
            activity('Usuarios')
                ->causedBy(auth()->user())
                ->performedOn($usuario)
                ->event('restablecer_password')
                ->log($actMsg);
        }

        if ($correoNotifEnviado) {
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Contraseña restablecida',
                'text' => 'Se ha generado una nueva contraseña temporal y se ha enviado al correo del usuario.'
            ]);
        } else {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Restablecida con advertencia',
                'text' => "Se ha generado una nueva contraseña, pero no se pudo enviar el correo de notificación. La contraseña temporal es: {$passwordTemporal}"
            ]);
        }
    }

    // ── REPORTES Y EXPORTACIÓN ──

    public function exportarUsuariosPdf()
    {
        if (!auth()->user()->can('reportes.exportar_pdf')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para exportar PDF.'
            ]);
            return;
        }

        try {
            $query = User::query()->with(['area', 'roles']);

            if (!empty($this->search)) {
                $query->where(function ($q) {
                    $q->where('nombres', 'ilike', '%' . $this->search . '%')
                      ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                      ->orWhere('ap_materno', 'ilike', '%' . $this->search . '%')
                      ->orWhere('correo', 'ilike', '%' . $this->search . '%');
                });
            }

            if (!empty($this->filtroEstado)) {
                $query->where('estado', $this->filtroEstado);
            }

            if (!empty($this->filtroRol)) {
                $query->role($this->filtroRol);
            }

            if (!empty($this->filtroArea)) {
                $query->where('cod_area', $this->filtroArea);
            }

            $usuarios = $query->orderBy('ap_paterno')->orderBy('nombres')->get();

            $viewData = [
                'usuarios' => $usuarios,
                'fecha' => date('d/m/Y H:i'),
                'usuario' => auth()->user()->name,
            ];

            $fileNameService = app(\App\Services\Reports\ReportFileNameService::class);
            $filename = $fileNameService->generate('usuarios_remembermind', 'pdf');

            $exportService = app(\App\Services\Reports\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->log('Se exportó el reporte general de usuarios en formato PDF.');

            return $exportService->exportPdf('reports.usuarios.general', $viewData, $filename);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error al exportar PDF de usuarios: " . $e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el reporte en PDF: ' . $e->getMessage()
            ]);
        }
    }

    public function exportarUsuariosExcel()
    {
        if (!auth()->user()->can('reportes.exportar_excel')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para exportar Excel.'
            ]);
            return;
        }

        try {
            $fileNameService = app(\App\Services\Reports\ReportFileNameService::class);
            $filename = $fileNameService->generate('usuarios_remembermind', 'xlsx');

            $exportService = app(\App\Services\Reports\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->log('Se exportó el listado de usuarios en formato Excel.');

            return $exportService->exportExcel(new \App\Exports\UsuariosExport, $filename);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error al exportar Excel de usuarios: " . $e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el reporte en Excel: ' . $e->getMessage()
            ]);
        }
    }

    public function exportarUsuariosCsv()
    {
        if (!auth()->user()->can('reportes.exportar_excel')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para exportar CSV.'
            ]);
            return;
        }

        try {
            $fileNameService = app(\App\Services\Reports\ReportFileNameService::class);
            $filename = $fileNameService->generate('usuarios_remembermind', 'csv');

            $exportService = app(\App\Services\Reports\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->log('Se exportó el listado de usuarios en formato CSV.');

            return $exportService->exportCsv(new \App\Exports\UsuariosExport, $filename);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error al exportar CSV de usuarios: " . $e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el reporte en CSV: ' . $e->getMessage()
            ]);
        }
    }

    // ══════════════════════════════════════════════
    // RENDER
    // ══════════════════════════════════════════════

    public function render()
    {
        $query = User::query()->with([
            'roles',
            'personalSalud.especialidad',
            'personalAdmin.cargoAdmin',
            'areaInstitucional',
        ]);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nombres', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ap_materno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('correo', 'ilike', '%' . $this->search . '%')
                  ->orWhere('cod_usu', 'ilike', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filtroEstado)) {
            $query->where('estado', $this->filtroEstado);
        }

        if (!empty($this->filtroRol)) {
            $query->role($this->filtroRol);
        }

        if (!empty($this->filtroGenero)) {
            $query->where('genero', $this->filtroGenero);
        }

        if (!empty($this->filtroArea)) {
            $query->where('cod_area', $this->filtroArea);
        }

        // Ordenamiento: ACTIVOS primero, luego alfabético
        $query->orderByRaw("CASE WHEN estado = 'ACTIVO' THEN 0 ELSE 1 END")
              ->orderBy('nombres')
              ->orderBy('ap_paterno')
              ->orderBy('ap_materno');

        $usuarioFichaModel = null;
        if ($this->mostrarFichaRapida && $this->usuarioFichaId) {
            $usuarioFichaModel = User::with([
                'roles',
                'personalSalud.especialidad',
                'personalAdmin.cargoAdmin',
                'areaInstitucional',
            ])->where('cod_usu', $this->usuarioFichaId)->first();
        }

        return view('livewire.admin.usuarios.usuarios-panel', [
            'usuarios' => $query->paginate(12),
            'roles' => Role::all(),
            'especialidades' => \App\Models\Especialidad::all(),
            'cargosAdmin' => \App\Models\CargoAdministrativo::all(),
            'areas' => \App\Models\AreaInstitucional::activas()->orderBy('orden')->get(),
            'usuarioFicha' => $usuarioFichaModel,
        ]);
    }
}
