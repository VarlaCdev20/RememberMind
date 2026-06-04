<?php

namespace App\Livewire\Admin\PersonalInstitucional\Partials;

use Livewire\Component;
use App\Models\User;
use App\Models\PersonalSalud;
use App\Models\PersonalAdmin;
use App\Models\Especialidad;
use App\Models\CargoAdministrativo;
use App\Models\AreaInstitucional;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

class PersonalInstitucionalForm extends Component
{
    use WithFileUploads;

    public $usuarioId;
    public $esEdicion = false;
    
    // Acceso y Roles
    public $correo;
    public $estado;
    public $roles_seleccionados = [];

    // Identidad y Contacto
    public $nombres;
    public $ap_paterno;
    public $ap_materno;
    public $numero_documento;
    public $tipo_documento = 'CI';
    public $expedido;
    public $fecha_nacimiento;
    public $edad;
    public $telefono;
    public $telefono_alternativo;
    public $genero;
    public $direccion;
    public $zona;
    public $ciudad;
    public $foto_perfil;
    public $observaciones;

    // Direcciones jerárquicas
    public $ciudad_id;
    public $municipio_id;
    public $zona_id;
    public $calle_id;
    public $otra_zona;
    public $otra_calle;
    public $nro_casa;

    // Colecciones para selects dependientes
    public $departamentos_list = [];
    public $municipios_list = [];
    public $zonas_list = [];
    public $calles_list = [];

    // Datos Laborales (Step 6)
    public $cod_esp;
    public $anios_exp = 0;
    public $matricula_prof;
    public $institucion_formacion;
    public $subtipo_enfermeria;
    public $cod_cargo_admin;

    // Colecciones para selects laborables
    public $especialidades_list = [];
    public $cargos_list = [];

    // Documentación
    public $archivos_temporales = [];
    public $estado_documentos = [];
    public $observacion_documentos = [];
    public $documentacion_postergada = false;
    public $faltan_documentos = false;
    public $faltan_recomendados = false;

    // Wizard state
    public int $pasoActual = 1;
    public int $totalPasos = 8;

    // Modo y Opciones Step 1
    public $fecha_registro;
    public $hora_registro;

    // Clasificación derivada desde los roles seleccionados
    public $tipo_personal = 'salud'; // salud, admin
    public $area_institucional;
    public $rol_operativo;
    public $cod_area;


    public function mount($usuarioId = null)
    {
        $this->usuarioId = $usuarioId;
        $this->estado = 'ACTIVO';
        $this->fecha_registro = now()->format('Y-m-d');
        $this->hora_registro = now()->format('H:i');
        $this->departamentos_list = \App\Models\LocDepartamento::where('activo', true)->orderBy('nombre')->get();
        $this->municipios_list = [];
        $this->zonas_list = [];
        $this->calles_list = [];
        
        $this->especialidades_list = \App\Models\Especialidad::orderBy('nombre')->get();
        $this->cargos_list = \App\Models\CargoAdministrativo::where('estado', 'ACTIVO')->orderBy('nombre')->get();
        
        if ($this->usuarioId) {
            $this->esEdicion = true;
            $this->cargarDatos();
        }
    }

    public function cargarDatos()
    {
        $usuario = User::with(['areaInstitucional', 'personalSalud', 'personalAdmin', 'roles'])->find($this->usuarioId);
        if (!$usuario) return;

        $this->nombres = $usuario->nombres;
        $this->ap_paterno = $usuario->ap_paterno;
        $this->ap_materno = $usuario->ap_materno;
        $this->correo = $usuario->correo;
        $this->telefono = $usuario->telefono;
        $this->numero_documento = $usuario->numero_documento;
        $this->expedido = $usuario->expedido;
        $this->genero = $usuario->genero;
        $this->fecha_nacimiento = $usuario->fecha_nacimiento ? \Carbon\Carbon::parse($usuario->fecha_nacimiento)->format('Y-m-d') : null;
        $this->calcularEdad($this->fecha_nacimiento);
        $this->estado = $usuario->estado;

        $this->direccion = $usuario->direccion;
        $this->zona = $usuario->zona;
        $this->ciudad = $usuario->ciudad;
        $this->observaciones = $usuario->observaciones;

        // Cargar direcciones jerárquicas
        $direccionRaw = $usuario->direccion;
        $ciudadRaw = $usuario->ciudad;
        $zonaRaw = $usuario->zona;

        $municipioNombre = '';
        $calleNombre = '';
        $nroCasa = '';

        if ($direccionRaw && preg_match('/MUNICIPIO:\s*(.*?)\s*\|\s*CALLE:\s*(.*?)\s*\|\s*NRO:\s*(.*)/i', $direccionRaw, $matches)) {
            $municipioNombre = trim($matches[1]);
            $calleNombre = trim($matches[2]);
            $nroCasa = trim($matches[3]);
        } else {
            $calleNombre = $direccionRaw ?: '';
            $nroCasa = 'S/N';
        }

        $dept = \App\Models\LocDepartamento::where('nombre', mb_strtoupper($ciudadRaw))->first();
        if ($dept) {
            $this->ciudad_id = $dept->id;
            $this->municipios_list = \App\Models\LocMunicipio::where('departamento_id', $dept->id)->orderBy('nombre')->get();
            
            $mun = \App\Models\LocMunicipio::where('departamento_id', $dept->id)
                ->where('nombre', mb_strtoupper($municipioNombre))
                ->first();
            if ($mun) {
                $this->municipio_id = $mun->id;
                $this->zonas_list = \App\Models\LocZona::where('municipio_id', $mun->id)->orderBy('nombre')->get();

                $zonaModel = \App\Models\LocZona::where('municipio_id', $mun->id)
                    ->where('nombre', mb_strtoupper($zonaRaw))
                    ->first();
                if ($zonaModel) {
                    $this->zona_id = $zonaModel->id;
                    $this->calles_list = \App\Models\LocCalle::where('zona_id', $zonaModel->id)->orderBy('nombre')->get();

                    $calleModel = \App\Models\LocCalle::where('zona_id', $zonaModel->id)
                        ->where('nombre', mb_strtoupper($calleNombre))
                        ->first();
                    if ($calleModel) {
                        $this->calle_id = $calleModel->id;
                    } else {
                        if ($calleNombre) {
                            $this->calle_id = 'OTRA';
                            $this->otra_calle = $calleNombre;
                        }
                    }
                } else {
                    if ($zonaRaw) {
                        $this->zona_id = 'OTRA';
                        $this->otra_zona = $zonaRaw;
                        $this->calle_id = 'OTRA';
                        $this->otra_calle = $calleNombre;
                    }
                }
            }
        }
        $this->nro_casa = $nroCasa;
        $this->roles_seleccionados = $usuario->roles->pluck('name')->toArray();
        $this->cod_area = $usuario->cod_area;

        if ($usuario->personalSalud) {
            $this->cod_esp = $usuario->personalSalud->cod_esp;
            $this->anios_exp = $usuario->personalSalud->anios_exp;
            $this->matricula_prof = $usuario->personalSalud->matricula_prof;
            $this->institucion_formacion = $usuario->personalSalud->institucion_formacion;
            $this->subtipo_enfermeria = $usuario->personalSalud->subtipo_enfermeria;
        }

        if ($usuario->personalAdmin) {
            $this->cod_cargo_admin = $usuario->personalAdmin->cod_cargo_admin;
        }

        $this->sincronizarClasificacionDesdeRoles(false);
    }

    protected function rules()
    {
        return [
            'nombres' => 'required|string|max:255',
            'ap_paterno' => 'required_without:ap_materno|string|max:255|nullable',
            'ap_materno' => 'required_without:ap_paterno|string|max:255|nullable',
            'correo' => 'required|email|max:255|unique:users,correo,' . $this->usuarioId . ',cod_usu',
            'numero_documento' => 'required|regex:/^[0-9]{5,10}$/|unique:users,numero_documento,' . $this->usuarioId . ',cod_usu',
            'estado' => 'required|string',
            'fecha_nacimiento' => 'nullable|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d') . '|after_or_equal:' . now()->subYears(100)->format('Y-m-d'),
            'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'roles_seleccionados' => 'required|array|min:1',
            'roles_seleccionados.*' => 'exists:roles,name',
            'tipo_personal' => 'required|in:salud,admin',
        ];
    }

    protected function messages()
    {
        return [
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El formato del correo es inválido.',
            'correo.unique' => 'Este correo ya está registrado.',
            'nombres.required' => 'El nombre es obligatorio.',
            'ap_paterno.required_without' => 'Debe ingresar el apellido paterno o materno.',
            'ap_materno.required_without' => 'Debe ingresar el apellido paterno o materno.',
            'numero_documento.required' => 'El CI es obligatorio.',
            'numero_documento.regex' => 'El documento de identidad debe contener solo números y tener entre 5 y 10 dígitos.',
            'numero_documento.unique' => 'Este CI ya se encuentra registrado.',
            'genero.required' => 'El género es obligatorio.',
            'expedido.required' => 'El departamento de expedición es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before_or_equal' => 'El usuario debe ser mayor de 18 años.',
            'fecha_nacimiento.after_or_equal' => 'La edad excede el límite permitido (100 años).',
            'foto_perfil.image' => 'El archivo debe ser una imagen válida.',
            'foto_perfil.mimes' => 'La foto debe ser en formato JPG, JPEG, PNG o WEBP.',
            'foto_perfil.max' => 'La fotografía no debe superar los 2MB de peso.',
            'roles_seleccionados.required' => 'Debe seleccionar al menos un rol de sistema.',
            'roles_seleccionados.min' => 'Debe seleccionar al menos un rol de sistema.',
        ];
    }

    public function updatedCorreo($value)
    {
        $this->correo = trim(mb_strtolower($value));
    }

    public function updatedFechaNacimiento($value)
    {
        $this->calcularEdad($value);
    }

    public function calcularEdad($value)
    {
        if ($value) {
            try {
                $this->edad = \Carbon\Carbon::parse($value)->age;
            } catch (\Exception $e) {
                $this->edad = null;
            }
        } else {
            $this->edad = null;
        }
    }

    public function updatedCiudadId($value)
    {
        $this->municipio_id = null;
        $this->zona_id = null;
        $this->calle_id = null;
        $this->otra_zona = null;
        $this->otra_calle = null;
        
        $this->municipios_list = $value 
            ? \App\Models\LocMunicipio::where('departamento_id', $value)->where('activo', true)->orderBy('nombre')->get() 
            : [];
        $this->zonas_list = [];
        $this->calles_list = [];
    }

    public function updatedMunicipioId($value)
    {
        $this->zona_id = null;
        $this->calle_id = null;
        $this->otra_zona = null;
        $this->otra_calle = null;

        $this->zonas_list = $value 
            ? \App\Models\LocZona::where('municipio_id', $value)->where('activo', true)->orderBy('nombre')->get() 
            : [];
        $this->calles_list = [];
    }

    public function updatedZonaId($value)
    {
        $this->calle_id = null;
        $this->otra_calle = null;
        if ($value !== 'OTRA') {
            $this->otra_zona = null;
        }

        $this->calles_list = ($value && $value !== 'OTRA') 
            ? \App\Models\LocCalle::where('zona_id', $value)->where('activo', true)->orderBy('nombre')->get() 
            : [];
    }

    public function updatedCalleId($value)
    {
        if ($value !== 'OTRA') {
            $this->otra_calle = null;
        }
    }

    public function updatedRolesSeleccionados($value = null, $key = null): void
    {
        $this->sincronizarClasificacionDesdeRoles(false);
    }

    public function getClasificacionDerivadaProperty(): ?array
    {
        return $this->clasificacionDesdeRoles();
    }

    private function clasificacionDesdeRoles(): ?array
    {
        $roles = array_values(array_filter($this->roles_seleccionados));

        foreach ($roles as $rol) {
            $clasificacion = $this->mapearRolInstitucional($rol);
            if ($clasificacion) {
                return $clasificacion;
            }
        }

        return null;
    }

    private function sincronizarClasificacionDesdeRoles(bool $lanzarExcepcion = true): ?array
    {
        $clasificacion = $this->clasificacionDesdeRoles();

        if (! $clasificacion) {
            if ($lanzarExcepcion) {
                throw ValidationException::withMessages([
                    'roles_seleccionados' => 'Seleccione un rol institucional compatible para derivar la clasificación.',
                ]);
            }

            return null;
        }

        $this->tipo_personal = $clasificacion['tipo_personal'];
        $this->rol_operativo = $clasificacion['rol_operativo'];
        $this->area_institucional = $clasificacion['area_nombre'];
        $this->cod_area = $clasificacion['cod_area'];

        return $clasificacion;
    }

    private function mapearRolInstitucional(string $rol): ?array
    {
        return match ($rol) {
            'ENFERMEROS' => $this->clasificacionSalud($rol, 'ENFERMERO', 'Enfermero(a)', 'ARE_0004'),
            'MEDICO GENERAL', 'MEDICO GENERAL/GERIATRA', 'MEDICO_GENERAL' => $this->clasificacionSalud($rol, 'MEDICO_GENERAL', 'Médico General/Geriatra', 'ARE_0004'),
            'PSICOLOGO/A' => $this->clasificacionSalud($rol, 'PSICOLOGO', 'Psicólogo(a)', 'ARE_0005'),
            'PEDAGOGO' => $this->clasificacionSalud($rol, 'PEDAGOGO', 'Pedagogo(a)', 'ARE_0005'),
            'NUTRICIONISTA' => $this->clasificacionSalud($rol, 'NUTRICIONISTA', 'Nutricionista', 'ARE_0004'),
            'FISIOTERAPEUTA' => $this->clasificacionSalud($rol, 'FISIOTERAPEUTA', 'Fisioterapeuta', 'ARE_0004'),
            'SUPERADMINISTRADOR' => $this->clasificacionAdministrativa($rol, 'SUPERADMINISTRADOR', 'Superadministrador(a)', 'ARE_0009'),
            'ADMINISTRADOR' => $this->clasificacionAdministrativa($rol, 'ADMINISTRADOR', 'Administrador(a)', 'ARE_0003'),
            default => null,
        };
    }

    private function clasificacionSalud(string $rol, string $rolOperativo, string $rolLabel, string $codArea): array
    {
        return $this->clasificacionBase($rol, 'salud', 'Personal de Salud', $rolOperativo, $rolLabel, $codArea);
    }

    private function clasificacionAdministrativa(string $rol, string $rolOperativo, string $rolLabel, string $codArea): array
    {
        return $this->clasificacionBase($rol, 'admin', 'Personal Administrativo', $rolOperativo, $rolLabel, $codArea);
    }

    private function clasificacionBase(string $rol, string $tipoPersonal, string $tipoLabel, string $rolOperativo, string $rolLabel, string $codArea): array
    {
        $areaNombre = AreaInstitucional::find($codArea)?->nombre ?? $this->nombreAreaPorDefecto($codArea);

        return [
            'rol_sistema' => $rol,
            'tipo_personal' => $tipoPersonal,
            'tipo_label' => $tipoLabel,
            'rol_operativo' => $rolOperativo,
            'rol_label' => $rolLabel,
            'cod_area' => $codArea,
            'area_nombre' => $areaNombre,
        ];
    }

    private function nombreAreaPorDefecto(string $codArea): string
    {
        return match ($codArea) {
            'ARE_0003' => 'Área Administrativa y Registro Institucional',
            'ARE_0004' => 'Área de Atención Médica',
            'ARE_0005' => 'Área de Psicología y Seguimiento Cognitivo',
            'ARE_0009' => 'Administración del Sistema',
            default => 'Área institucional',
        };
    }

    public function getDocumentosConfiguradosProperty()
    {
        $clasificacion = $this->clasificacionDesdeRoles();
        $tipoPersonal = $clasificacion['tipo_personal'] ?? $this->tipo_personal;
        $rolOperativo = $clasificacion['rol_operativo'] ?? $this->rol_operativo;

        $docs = [];

        // --- BLOQUE: DOCUMENTACIÓN DEL INGRESANTE ---
        
        // Cédula de Identidad (Obligatorio para todo rol)
        $docs[] = [
            'id' => 'CI',
            'nombre' => 'Cédula de Identidad (Anverso y Reverso)',
            'desc' => 'Copia legible del documento de identidad',
            'obligatorio' => true,
            'tipo' => 'ingresante'
        ];

        if ($tipoPersonal === 'salud') {
            // Título profesional (Obligatorio solo para médico y enfermero)
            $esMedicoOEnfermero = in_array($rolOperativo, ['MEDICO_GENERAL', 'ENFERMERO']);
            $docs[] = [
                'id' => 'TITULO',
                'nombre' => 'Título o Certificado Profesional',
                'desc' => 'Título en provisión nacional o certificado académico',
                'obligatorio' => $esMedicoOEnfermero,
                'tipo' => 'ingresante'
            ];

            // Matrícula profesional
            $docs[] = [
                'id' => 'MATRICULA',
                'nombre' => 'Matrícula o Respaldo Profesional',
                'desc' => 'Registro profesional vigente ante el SEDES o colegio profesional si aplica',
                'obligatorio' => false,
                'tipo' => 'ingresante'
            ];
        }

        // Hoja de Vida
        $docs[] = [
            'id' => 'CV',
            'nombre' => 'Hoja de Vida',
            'desc' => 'Curriculum Vitae actualizado y documentado',
            'obligatorio' => false,
            'tipo' => 'ingresante'
        ];

        if ($tipoPersonal === 'salud') {
            // Certificado médico de aptitud
            $docs[] = [
                'id' => 'CERT_MEDICO',
                'nombre' => 'Certificado Médico de Aptitud',
                'desc' => 'Evaluación de salud ocupacional de aptitud física e intelectual',
                'obligatorio' => false,
                'tipo' => 'ingresante'
            ];
        }

        // Certificado de antecedentes o equivalente
        $docs[] = [
            'id' => 'ANTECEDENTES',
            'nombre' => 'Certificado de Antecedentes',
            'desc' => 'Certificado de antecedentes emitido por FELCC, FELCN, REJAP o equivalente',
            'obligatorio' => false,
            'tipo' => 'ingresante'
        ];

        // Certificado de capacitación si existe
        $docs[] = [
            'id' => 'CAPACITACION',
            'nombre' => 'Certificado de Capacitación',
            'desc' => 'Cursos, talleres, diplomados o capacitación técnica',
            'obligatorio' => false,
            'tipo' => 'ingresante'
        ];

        // --- BLOQUE: DOCUMENTACIÓN INSTITUCIONAL ---
        
        // Contrato o Designación
        $docs[] = [
            'id' => 'CONTRATO',
            'nombre' => 'Contrato o Acuerdo de Prestación de Servicios',
            'desc' => 'Contrato o acuerdo de prestación de servicios debidamente firmado',
            'obligatorio' => false,
            'tipo' => 'institucional'
        ];

        // Compromiso de Confidencialidad
        $docs[] = [
            'id' => 'CONFIDENCIALIDAD',
            'nombre' => 'Compromiso de Confidencialidad y Protección de Datos',
            'desc' => 'Compromiso firmado de confidencialidad y resguardo de datos institucionales',
            'obligatorio' => false,
            'tipo' => 'institucional'
        ];

        // Aceptación de Reglamento Interno
        $docs[] = [
            'id' => 'REGLAMENTO',
            'nombre' => 'Aceptación de Reglamento Interno y Acta de Recepción',
            'desc' => 'Constancia de aceptación firmada del reglamento interno y manuales de funciones',
            'obligatorio' => false,
            'tipo' => 'institucional'
        ];

        // Formulario de Asignación Inicial de Funciones
        $docs[] = [
            'id' => 'FUNCIONES',
            'nombre' => 'Formulario de Asignación Inicial de Funciones',
            'desc' => 'Asignación de funciones y responsabilidades del cargo firmada',
            'obligatorio' => false,
            'tipo' => 'institucional'
        ];

        return $docs;
    }

    public function updatedArchivosTemporales($value, $key)
    {
        $this->validateOnly("archivos_temporales.$key", [
            "archivos_temporales.$key" => 'nullable|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ], [
            "archivos_temporales.$key.mimes" => 'El archivo debe ser PDF, JPG, PNG o WEBP.',
            "archivos_temporales.$key.max" => 'El archivo no debe exceder los 5MB.',
        ]);

        $this->estado_documentos[$key] = 'CARGADO';
        
        if (function_exists('activity')) {
            activity()->log("Cargó temporalmente el documento: " . $key);
        }
    }

    public function observarDocumento($key, $observacion)
    {
        if (empty(trim($observacion))) {
            $this->addError("observacion_$key", "La observación es obligatoria.");
            return;
        }
        $this->observacion_documentos[$key] = $observacion;
        $this->estado_documentos[$key] = 'OBSERVADO';
        
        if (function_exists('activity')) {
            activity()->log("Observó el documento: " . $key);
        }
    }

    public function removerDocumento($key)
    {
        unset($this->archivos_temporales[$key]);
        $this->estado_documentos[$key] = 'PENDIENTE';
        unset($this->observacion_documentos[$key]);
    }

    public function validarPasoActual(): void
    {
        if ($this->pasoActual === 1) {
            $this->correo = trim(mb_strtolower($this->correo));
            $this->validate([
                'correo' => 'required|email|max:255|unique:users,correo' . ($this->usuarioId ? ',' . $this->usuarioId . ',cod_usu' : ''),
                'estado' => 'required|in:ACTIVO,PENDIENTE,BLOQUEADO',
                'fecha_registro' => 'required|date|before_or_equal:today',
                'hora_registro' => 'required|date_format:H:i'
            ], [
                'correo.required' => 'El correo es obligatorio.',
                'correo.email' => 'El formato del correo es inválido.',
                'correo.unique' => 'Este correo ya está registrado.',
                'estado.required' => 'El estado de acceso es obligatorio.',
                'fecha_registro.required' => 'La fecha de registro es obligatoria.',
                'fecha_registro.before_or_equal' => 'La fecha no puede ser futura.',
                'hora_registro.required' => 'La hora es obligatoria.'
            ]);
        } elseif ($this->pasoActual === 2) {
            $this->validate([
                'nombres' => 'required|string|max:255',
                'ap_paterno' => 'required_without:ap_materno|string|max:255|nullable',
                'ap_materno' => 'required_without:ap_paterno|string|max:255|nullable',
                'numero_documento' => 'required|regex:/^[0-9]{5,10}$/|unique:users,numero_documento,' . $this->usuarioId . ',cod_usu',
                'expedido' => 'required|string|max:10',
                'genero' => 'required|in:M,F',
                'fecha_nacimiento' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d') . '|after_or_equal:' . now()->subYears(100)->format('Y-m-d'),
                'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ], [
                'nombres.required' => 'El nombre es obligatorio.',
                'ap_paterno.required_without' => 'Debe ingresar el apellido paterno o materno.',
                'ap_materno.required_without' => 'Debe ingresar el apellido paterno o materno.',
                'numero_documento.required' => 'El CI es obligatorio.',
                'numero_documento.regex' => 'El documento de identidad debe contener solo números y tener entre 5 y 10 dígitos.',
                'numero_documento.unique' => 'Este CI ya se encuentra registrado.',
                'expedido.required' => 'El departamento de expedición es obligatorio.',
                'genero.required' => 'El género es obligatorio.',
                'genero.in' => 'El género seleccionado no es válido.',
                'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
                'fecha_nacimiento.before_or_equal' => 'El usuario debe ser mayor de 18 años.',
                'fecha_nacimiento.after_or_equal' => 'La edad excede el límite permitido (100 años).',
                'foto_perfil.image' => 'El archivo debe ser una imagen válida.',
                'foto_perfil.mimes' => 'La foto debe ser en formato JPG, JPEG, PNG o WEBP.',
                'foto_perfil.max' => 'La fotografía no debe superar los 2MB de peso.'
            ]);

            $this->nombres = mb_strtoupper($this->nombres);
            $this->ap_paterno = mb_strtoupper($this->ap_paterno);
            $this->ap_materno = mb_strtoupper($this->ap_materno);
            
        } elseif ($this->pasoActual === 3) {
            // Normalizar quitando espacios y guiones antes de validar
            if ($this->telefono) {
                $this->telefono = preg_replace('/[\s\-]/', '', $this->telefono);
            }
            if ($this->telefono_alternativo) {
                $this->telefono_alternativo = preg_replace('/[\s\-]/', '', $this->telefono_alternativo);
            }

            $this->validate([
                'telefono' => ['required', 'regex:/^[67][0-9]{7}$/'],
                'telefono_alternativo' => ['nullable', 'regex:/^[0-9]{7,8}$/'],
                'ciudad_id' => 'required|exists:loc_departamentos,id',
                'municipio_id' => 'required|exists:loc_municipios,id',
                'zona_id' => 'required',
                'otra_zona' => 'required_if:zona_id,OTRA|nullable|string|max:100',
                'calle_id' => 'required',
                'otra_calle' => 'required_if:calle_id,OTRA|nullable|string|max:100',
                'nro_casa' => 'required|string|max:50',
            ], [
                'telefono.required' => 'El número de celular es obligatorio.',
                'telefono.regex' => 'El celular debe tener 8 dígitos y comenzar con 6 o 7.',
                'telefono_alternativo.regex' => 'El teléfono alternativo debe contener solo números y tener entre 7 y 8 dígitos.',
                'ciudad_id.required' => 'El departamento/ciudad es obligatorio.',
                'municipio_id.required' => 'El municipio es obligatorio.',
                'zona_id.required' => 'La zona/barrio es obligatoria.',
                'otra_zona.required_if' => 'Debe escribir el nombre de la otra zona.',
                'calle_id.required' => 'La calle o avenida es obligatoria.',
                'otra_calle.required_if' => 'Debe escribir el nombre de la otra calle o avenida.',
                'nro_casa.required' => 'El número de casa es obligatorio.',
            ]);

            // Formatear dirección para guardar en variables locales antes de la persistencia
            $dept = \App\Models\LocDepartamento::find($this->ciudad_id);
            $mun = \App\Models\LocMunicipio::find($this->municipio_id);
            
            $zonaNombre = '';
            if ($this->zona_id === 'OTRA') {
                $zonaNombre = mb_strtoupper($this->otra_zona);
            } else {
                $zonaModel = \App\Models\LocZona::find($this->zona_id);
                $zonaNombre = $zonaModel ? $zonaModel->nombre : '';
            }

            $calleNombre = '';
            if ($this->calle_id === 'OTRA') {
                $calleNombre = mb_strtoupper($this->otra_calle);
            } else {
                $calleModel = \App\Models\LocCalle::find($this->calle_id);
                $calleNombre = $calleModel ? $calleModel->nombre : '';
            }

            $this->ciudad = $dept ? $dept->nombre : '';
            $this->zona = $zonaNombre;
            $this->direccion = "MUNICIPIO: " . ($mun ? $mun->nombre : '') . " | CALLE: " . $calleNombre . " | NRO: " . mb_strtoupper($this->nro_casa);
            
        } elseif ($this->pasoActual === 4) {
            $this->validate([
                'roles_seleccionados' => 'required|array|min:1',
                'roles_seleccionados.*' => 'exists:roles,name'
            ], [
                'roles_seleccionados.required' => 'Debe seleccionar al menos un rol de sistema.'
            ]);
            
        } elseif ($this->pasoActual === 5) {
            $this->sincronizarClasificacionDesdeRoles();
        } elseif ($this->pasoActual === 6) {
            if ($this->tipo_personal === 'salud') {
                $this->validate([
                    'cod_esp' => 'nullable|exists:especialidades,cod_esp',
                    'anios_exp' => 'required|integer|min:0|max:80',
                    'matricula_prof' => 'nullable|string|max:50',
                    'institucion_formacion' => 'nullable|string|max:150',
                    'subtipo_enfermeria' => 'nullable|string|max:50',
                ], [
                    'anios_exp.required' => 'Los años de experiencia son obligatorios.',
                    'anios_exp.integer' => 'Los años de experiencia deben ser un número entero.',
                    'anios_exp.min' => 'Los años de experiencia no pueden ser negativos.',
                ]);
            } elseif ($this->tipo_personal === 'admin') {
                $this->validate([
                    'cod_cargo_admin' => 'nullable|exists:cargos_administrativos,cod_cargo_admin',
                ], [
                    'cod_cargo_admin.exists' => 'El cargo administrativo seleccionado no es válido.',
                ]);
            }
        } elseif ($this->pasoActual === 7) {
            // CI obligatoria para todo ingresante nuevo
            $this->validate([
                'archivos_temporales.CI' => $this->esEdicion ? 'nullable' : 'required',
            ], [
                'archivos_temporales.CI.required' => 'La Cédula de Identidad (anverso/reverso) es obligatoria para realizar el registro.',
            ]);

            // Título obligatorio para médico y enfermero
            $clasificacion = $this->clasificacionDesdeRoles();
            $rolOperativo  = $clasificacion['rol_operativo'] ?? $this->rol_operativo;
            if (in_array($rolOperativo, ['MEDICO_GENERAL', 'ENFERMERO'])) {
                $this->validate([
                    'archivos_temporales.TITULO' => $this->esEdicion ? 'nullable' : 'required',
                ], [
                    'archivos_temporales.TITULO.required' => 'El Título o Certificado Profesional es obligatorio para personal médico/enfermería.',
                ]);
            }

            $this->faltan_documentos    = false;
            $this->faltan_recomendados  = false;

            foreach ($this->documentos_configurados as $doc) {
                $estadoDoc = $this->estado_documentos[$doc['id']]
                    ?? \App\Models\DocumentoUsuario::ESTADO_PENDIENTE;

                if ($estadoDoc === \App\Models\DocumentoUsuario::ESTADO_PENDIENTE) {
                    $this->faltan_documentos = true;
                    if (!$doc['obligatorio']) {
                        $this->faltan_recomendados = true;
                    }
                }
            }

            if ($this->faltan_documentos && $this->estado === 'ACTIVO') {
                $this->estado = 'DOCUMENTACION_PENDIENTE';
                $this->dispatch('mostrarAlerta', [
                    'type'    => 'warning',
                    'title'   => 'Documentos pendientes',
                    'message' => 'El personal se registrará en estado DOCUMENTACION_PENDIENTE. Tendrá 48 horas para entregar los documentos faltantes.',
                ]);
            }
        }
    }

    public function sincronizarSessionTemporal()
    {
        $clasificacion = $this->clasificacionDesdeRoles();
        
        $documentosSession = [];
        foreach ($this->documentos_configurados as $doc) {
            $estado = isset($this->archivos_temporales[$doc['id']])
                ? \App\Models\DocumentoUsuario::ESTADO_CARGADO
                : ($this->estado_documentos[$doc['id']] ?? \App\Models\DocumentoUsuario::ESTADO_PENDIENTE);
            $obs = $this->observacion_documentos[$doc['id']]
                ?? ($estado === \App\Models\DocumentoUsuario::ESTADO_PENDIENTE ? 'Pendiente (plazo: 48 horas)' : '');
            
            $documentosSession[] = [
                'id' => $doc['id'],
                'nombre' => $doc['nombre'],
                'obligatorio' => $doc['obligatorio'],
                'tipo' => $doc['tipo'] === 'ingresante' ? 'Ingresante' : 'Institucional',
                'estado' => $estado,
                'observacion' => $obs
            ];
        }

        $sessionData = [
            'nombres' => $this->nombres,
            'ap_paterno' => $this->ap_paterno,
            'ap_materno' => $this->ap_materno,
            'nro_documento' => $this->numero_documento,
            'expedido' => $this->expedido,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'genero' => $this->genero,
            'celular' => $this->telefono,
            'telefono_alternativo' => $this->telefono_alternativo,
            'direccion' => $this->direccion,
            'tipo_personal' => $clasificacion['tipo_personal'] ?? $this->tipo_personal,
            'rol_operativo' => $clasificacion['rol_operativo'] ?? $this->rol_operativo,
            'rol_label' => $clasificacion['rol_label'] ?? 'Personal',
            'area_nombre' => $clasificacion['area_nombre'] ?? 'Área Operativa',
            'responsable_nombre' => auth()->user() ? (auth()->user()->nombres . ' ' . auth()->user()->ap_paterno) : 'Sistema',
            'responsable_cod' => auth()->user() ? auth()->user()->cod_usu : null,
            'fecha_registro' => $this->fecha_registro,
            'estado_general' => $this->estado,
            'documentos' => $documentosSession,
            'observaciones_registro' => $this->observaciones,
        ];
        session(['personal_registro_temp' => $sessionData]);
    }

    public function avanzarPaso(): void
    {
        try {
            $this->validarPasoActual();

            if ($this->pasoActual < $this->totalPasos) {
                $this->pasoActual++;
                if ($this->pasoActual === 8) {
                    $this->sincronizarSessionTemporal();
                }
            }
        } catch (ValidationException $e) {
            if ($this->pasoActual === 1 && $e->validator->errors()->has('correo') && str_contains($e->validator->errors()->first('correo'), 'registrado')) {
                $this->dispatch('mostrarAlerta', [
                    'type' => 'error',
                    'title' => 'Correo Duplicado',
                    'message' => 'El correo ingresado ya existe en la base de datos. Por favor, utilice otro correo institucional.'
                ]);
            } else {
                $this->dispatch('mostrarAlerta', [
                    'type' => 'error',
                    'title' => 'Campos Incompletos',
                    'message' => 'Revise los campos obligatorios antes de continuar.'
                ]);
            }
            throw $e;
        }
    }

    public function retrocederPaso(): void
    {
        if ($this->pasoActual > 1) {
            $this->pasoActual--;
        }
    }

    public function confirmarActualizacion()
    {
        if ($this->esEdicion) {
            $this->sincronizarClasificacionDesdeRoles();
            $this->validate();
            
            if ($this->tipo_personal === 'salud' && $this->rol_operativo === 'MEDICO_GENERAL' && $this->estado === 'ACTIVO') {
                $exists = PersonalSalud::where('tipo_personal_salud', 'MEDICO_GENERAL')
                    ->where('estado_laboral', 'ACTIVO')
                    ->where('cod_usu', '!=', $this->usuarioId)
                    ->exists();
                if ($exists) {
                    $this->addError('roles_seleccionados', 'Ya existe un Médico General principal activo en el sistema.');
                    return;
                }
            }

            $usuario = User::find($this->usuarioId);
            $roles_actuales = $usuario->roles->pluck('name')->toArray();
            $roles_nuevos = $this->roles_seleccionados;
            
            sort($roles_actuales);
            sort($roles_nuevos);

            if ($roles_actuales !== $roles_nuevos) {
                $this->dispatch('confirmarCambioRoles', [
                    'roles' => empty($this->roles_seleccionados) ? 'Ninguno' : implode(', ', $this->roles_seleccionados)
                ]);
                return;
            }
        }

        $this->guardar();
    }

    public function validarDocumentosInstitucionales(): bool
    {
        $faltanObligatorios = false;

        if (!isset($this->archivos_temporales['CONTRATO'])) {
            $existeContrato = DB::table('documentos_usuarios')
                ->where('cod_usu', $this->usuarioId)
                ->where('nom_doc', 'Contrato o Acuerdo de Prestación de Servicios')
                ->where('ruta_archivo', '!=', 'PENDIENTE')
                ->exists();
            if (!$existeContrato) {
                $faltanObligatorios = true;
            }
        }

        if (!isset($this->archivos_temporales['CONFIDENCIALIDAD'])) {
            $existeConf = DB::table('documentos_usuarios')
                ->where('cod_usu', $this->usuarioId)
                ->where('nom_doc', 'Compromiso de Confidencialidad y Protección de Datos')
                ->where('ruta_archivo', '!=', 'PENDIENTE')
                ->exists();
            if (!$existeConf) {
                $faltanObligatorios = true;
            }
        }

        if ($faltanObligatorios) {
            if ($this->estado !== 'PENDIENTE_INSTITUCIONAL') {
                $this->estado = 'PENDIENTE_INSTITUCIONAL';
                $this->dispatch('mostrarAlerta', [
                    'type' => 'warning',
                    'title' => 'Documentación Firmada Pendiente',
                    'message' => 'Faltan documentos institucionales obligatorios firmados (Contrato o Confidencialidad). Para continuar, el estado del personal se ha establecido como PENDIENTE_INSTITUCIONAL y se registrará una alerta de seguimiento.'
                ]);
                return false;
            }
        }

        return true;
    }

    public function preGuardar()
    {
        if (!$this->validarDocumentosInstitucionales()) {
            return;
        }

        $this->dispatch('confirmarRegistroFinal', [
            'faltan_documentos' => $this->faltan_documentos,
            'estado' => $this->estado
        ]);
    }

    public function guardar()
    {
        $clasificacion = $this->sincronizarClasificacionDesdeRoles();

        if (!$this->esEdicion) {
            $this->validate();
        }

        if (!$this->validarDocumentosInstitucionales()) {
            return;
        }

        DB::beginTransaction();
        try {
            $plainPassword = null;
            
            if ($this->esEdicion) {
                $usuario = User::findOrFail($this->usuarioId);
            } else {
                $usuario = new User();
                $plainPassword = Str::password(12, true, true, true, false);
                $usuario->password = Hash::make($plainPassword);
                $usuario->created_at = \Carbon\Carbon::parse($this->fecha_registro . ' ' . $this->hora_registro);
            }

            $usuario->nombres = $this->nombres;
            $usuario->ap_paterno = $this->ap_paterno;
            $usuario->ap_materno = $this->ap_materno;
            $usuario->correo = trim(mb_strtolower($this->correo));
            $usuario->telefono = $this->telefono;
            $usuario->numero_documento = $this->numero_documento;
            $usuario->expedido = $this->expedido;
            $usuario->fecha_nacimiento = $this->fecha_nacimiento;
            $usuario->genero = $this->genero;
            $usuario->estado = $this->estado;
            $usuario->observaciones = $this->observaciones;
            // Columnas pendientes de migración — no asignar hasta que existan en la BD:
            // $usuario->cod_area      → sin migración aún
            // $usuario->direccion     → 2026_05_18_020000
            // $usuario->zona          → 2026_05_18_020000
            // $usuario->ciudad        → 2026_05_18_020000
            
            if ($this->foto_perfil && !is_string($this->foto_perfil)) {
                $path = $this->foto_perfil->store('perfiles', 'public');
                $usuario->foto_de_perfil = $path;
            }
            
            $usuario->save();

            // Sync Spatie Roles correctly
            if (!empty($this->roles_seleccionados)) {
                $usuario->syncRoles($this->roles_seleccionados);
            } else {
                $usuario->syncRoles([]);
            }

            // Manage Personal Classification
            if ($this->tipo_personal === 'salud') {
                $ps = PersonalSalud::firstOrNew(['cod_usu' => $usuario->cod_usu]);
                $ps->cod_esp = $this->cod_esp ?: null;
                $ps->fecha_ing = now()->format('Y-m-d');
                $ps->anios_exp = $this->anios_exp ?: 0;
                $ps->matricula_prof = $this->matricula_prof ?: null;
                $ps->estado_laboral = 'ACTIVO';
                $ps->observaciones = $this->observaciones ?: null;
                // Columnas pendientes de migración — no asignar hasta que existan en la BD:
                // $ps->institucion_formacion → 2026_05_18_020000
                // $ps->tipo_personal_salud   → sin migración aún
                // $ps->subtipo_enfermeria    → sin migración aún
                $ps->save();

                // Eliminar posible registro admin si cambió
                PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
            } elseif ($this->tipo_personal === 'admin') {
                $pa = PersonalAdmin::firstOrNew(['cod_usu' => $usuario->cod_usu]);
                $pa->cod_cargo_admin = $this->cod_cargo_admin ?: null;
                $pa->cargo = $clasificacion['rol_operativo'];
                $pa->fecha_ingreso = now()->format('Y-m-d');
                $pa->area_admin = $clasificacion['area_nombre'];
                $pa->estado_laboral = 'ACTIVO';
                $pa->observaciones = $this->observaciones ?: null;
                $pa->save();

                // Eliminar posible registro salud si cambió
                PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
            } else {
                PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
                PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
            }

            // Guardar documentos — esquema real: nom_doc, tipo_doc, ruta_archivo, extension, fecha_doc
            // Control documental: estado, subido_por, validado_por, fecha_validacion, mime_type, tamanio, fecha_vencimiento_plazo
            $documentosFaltantes = [];
            $plazoVencimiento = \Carbon\Carbon::parse($this->fecha_registro)->addHours(48)->toDateString();
            $responsableId = auth()->id();

            foreach ($this->documentos_configurados as $doc) {
                $esInstitucional = ($doc['tipo'] === 'institucional');
                $tipoDoc  = $esInstitucional ? 'INSTITUCIONAL' : 'PERSONAL';
                $fechaDoc = $esInstitucional ? \Carbon\Carbon::parse($this->fecha_registro)->toDateString() : null;

                $estadoDoc = $this->estado_documentos[$doc['id']] ?? \App\Models\DocumentoUsuario::ESTADO_PENDIENTE;

                $docBD = DB::table('documentos_usuarios')
                    ->where('cod_usu', $usuario->cod_usu)
                    ->where('nom_doc', $doc['nombre'])
                    ->first();

                if ($estadoDoc === \App\Models\DocumentoUsuario::ESTADO_CARGADO
                    && isset($this->archivos_temporales[$doc['id']])) {

                    $file = $this->archivos_temporales[$doc['id']];
                    if (!is_string($file)) {
                        $path = $file->store('documentos_personal', 'public');

                        $datosArchivo = [
                            'tipo_doc'     => $tipoDoc,
                            'ruta_archivo' => $path,
                            'extension'    => $file->getClientOriginalExtension(),
                            'fecha_doc'    => $fechaDoc,
                            'mime_type'    => $file->getMimeType(),
                            'tamanio'      => $file->getSize(),
                            'estado'       => \App\Models\DocumentoUsuario::ESTADO_CARGADO,
                            'subido_por'   => $responsableId,
                            'observaciones'       => null,
                            'observacion_validacion' => null,
                            'fecha_vencimiento_plazo' => null,
                            'updated_at'   => now(),
                        ];

                        if ($docBD) {
                            DB::table('documentos_usuarios')
                                ->where('cod_doc_usu', $docBD->cod_doc_usu)
                                ->update($datosArchivo);
                        } else {
                            DB::table('documentos_usuarios')->insert(array_merge($datosArchivo, [
                                'cod_usu'    => $usuario->cod_usu,
                                'nom_doc'    => $doc['nombre'],
                                'created_at' => now(),
                            ]));
                        }
                    }

                } elseif ($estadoDoc === \App\Models\DocumentoUsuario::ESTADO_OBSERVADO) {

                    $obsTexto = $this->observacion_documentos[$doc['id']] ?? 'Documento observado.';
                    $datosObs = [
                        'tipo_doc'              => $tipoDoc,
                        'estado'                => \App\Models\DocumentoUsuario::ESTADO_OBSERVADO,
                        'observacion_validacion' => $obsTexto,
                        'updated_at'            => now(),
                    ];

                    if ($docBD) {
                        DB::table('documentos_usuarios')
                            ->where('cod_doc_usu', $docBD->cod_doc_usu)
                            ->update($datosObs);
                    } else {
                        DB::table('documentos_usuarios')->insert(array_merge($datosObs, [
                            'cod_usu'               => $usuario->cod_usu,
                            'nom_doc'               => $doc['nombre'],
                            'ruta_archivo'          => 'PENDIENTE',
                            'extension'             => '',
                            'fecha_doc'             => null,
                            'observaciones'         => null,
                            'fecha_vencimiento_plazo' => $plazoVencimiento,
                            'created_at'            => now(),
                        ]));
                    }

                } elseif ($estadoDoc === \App\Models\DocumentoUsuario::ESTADO_PENDIENTE) {

                    $obsDefault = $esInstitucional
                        ? 'Documento institucional pendiente. Regularizar en 48 horas.'
                        : 'Pendiente de entrega (plazo: 48 horas).';

                    if ($esInstitucional && in_array($doc['id'], ['CONTRATO', 'CONFIDENCIALIDAD'])) {
                        $documentosFaltantes[] = $doc['nombre'];
                    }

                    $datosPend = [
                        'tipo_doc'               => $tipoDoc,
                        'estado'                 => \App\Models\DocumentoUsuario::ESTADO_PENDIENTE,
                        'observaciones'          => $obsDefault,
                        'fecha_vencimiento_plazo' => $plazoVencimiento,
                        'updated_at'             => now(),
                    ];

                    if ($docBD) {
                        DB::table('documentos_usuarios')
                            ->where('cod_doc_usu', $docBD->cod_doc_usu)
                            ->update($datosPend);
                    } else {
                        DB::table('documentos_usuarios')->insert(array_merge($datosPend, [
                            'cod_usu'      => $usuario->cod_usu,
                            'nom_doc'      => $doc['nombre'],
                            'ruta_archivo' => 'PENDIENTE',
                            'extension'    => '',
                            'fecha_doc'    => null,
                            'created_at'   => now(),
                        ]));
                    }
                }
            }

            // Log follow-up alert if institutional documents are missing
            if (!empty($documentosFaltantes) && function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($usuario)
                    ->useLog('AlertaSeguimiento')
                    ->withProperties([
                        'usuario_registrado' => $usuario->nombres . ' ' . $usuario->ap_paterno,
                        'ci' => $usuario->numero_documento,
                        'documentos_faltantes' => $documentosFaltantes,
                        'plazo_horas' => 48
                    ])
                    ->log("Alerta de Seguimiento: Ingresante registrado en estado PENDIENTE_INSTITUCIONAL debido a falta de documentación institucional firmada obligatoria (" . implode(', ', $documentosFaltantes) . "). Requiere regularización en 48 horas.");
            }

            // Log activity
            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($usuario)
                    ->log($this->esEdicion ? 'Actualizó datos de personal institucional' : 'Registró nuevo personal institucional');
            }

            DB::commit();

            // Enviar correo de confirmación y paquete documental al usuario registrado
            try {
                $docsPendientes = [];
                foreach ($this->documentos_configurados as $doc) {
                    $estadoDoc = $this->estado_documentos[$doc['id']]
                        ?? \App\Models\DocumentoUsuario::ESTADO_PENDIENTE;
                    if ($estadoDoc === \App\Models\DocumentoUsuario::ESTADO_PENDIENTE) {
                        $docsPendientes[] = $doc['nombre'];
                    }
                }
                if (!isset($this->archivos_temporales['REGLAMENTO'])) {
                    $docsPendientes[] = 'Aceptación de Reglamento Interno y Acta de Recepción';
                }
                if (!isset($this->archivos_temporales['FUNCIONES'])) {
                    $docsPendientes[] = 'Formulario de Asignación Inicial de Funciones';
                }
                $docsPendientes = array_values(array_unique($docsPendientes));

                $attachedPaths = [];
                foreach ($this->documentos_configurados as $doc) {
                    if ($doc['tipo'] === 'institucional') {
                        $savedDoc = DB::table('documentos_usuarios')
                            ->where('cod_usu', $usuario->cod_usu)
                            ->where('nom_doc', $doc['nombre'])
                            ->first();

                        if ($savedDoc && $savedDoc->ruta_archivo && $savedDoc->ruta_archivo !== 'PENDIENTE') {
                            $fullPath = storage_path('app/public/' . $savedDoc->ruta_archivo);
                            if (file_exists($fullPath)) {
                                $attachedPaths[$doc['nombre'] . '.pdf'] = $fullPath;
                            }
                        }
                    }
                }

                $fechaLimite = now()->addHours(48)->format('d/m/Y H:i');
                $rolLabel = empty($this->roles_seleccionados) ? 'Ninguno' : implode(', ', $this->roles_seleccionados);
                $areaNombre = \App\Models\AreaInstitucional::where('cod_area', $usuario->cod_area)->value('nombre') ?? 'General';

                \Illuminate\Support\Facades\Mail::to($usuario->correo)->send(
                    new \App\Mail\PersonalIngresanteConfirmacionMail(
                        $usuario,
                        $rolLabel,
                        $areaNombre,
                        $usuario->estado,
                        $docsPendientes,
                        $fechaLimite,
                        $attachedPaths
                    )
                );
            } catch (\Exception $mailEx) {
                logger()->error("Error al enviar correo de confirmación de registro: " . $mailEx->getMessage());
            }

            if (!$this->esEdicion && $plainPassword) {
                $roles = empty($this->roles_seleccionados) ? 'Ninguno' : implode(', ', $this->roles_seleccionados);
                $html = "<div class='text-left space-y-3 mt-4'>" .
                        "<div class='p-3 bg-fondo border border-borde rounded-xl'>" .
                        "<p class='text-sm mb-1'><span class='font-bold text-apoyo uppercase text-[10px] block'>Usuario:</span> <span class='font-semibold text-titulo'>{$this->nombres} {$this->ap_paterno}</span></p>" .
                        "<p class='text-sm mb-1'><span class='font-bold text-apoyo uppercase text-[10px] block'>Correo:</span> <span class='font-semibold text-titulo'>{$this->correo}</span></p>" .
                        "<p class='text-sm mb-1'><span class='font-bold text-apoyo uppercase text-[10px] block'>Rol Asignado:</span> <span class='font-semibold text-titulo'>{$roles}</span></p>" .
                        "<p class='text-sm mb-1'><span class='font-bold text-apoyo uppercase text-[10px] block'>Estado:</span> <span class='font-bold " . ($this->estado === 'ACTIVO' ? 'text-estado-exito' : 'text-estado-peligro') . "'>{$this->estado}</span></p>" .
                        "</div>" .
                        "<div class='p-4 bg-boton-acento/10 border border-boton-acento rounded-xl flex items-center justify-between'>" .
                        "<div><span class='font-bold text-boton-acento uppercase text-[10px] block'>Contraseña Temporal</span>" .
                        "<span class='font-mono font-bold text-lg tracking-widest text-titulo'>" . htmlspecialchars($plainPassword) . "</span></div>" .
                        "</div>" .
                        "<div class='p-3 bg-estado-peligroBg border border-estado-peligro rounded-xl flex gap-2'>" .
                        "<i class='ph-bold ph-warning-circle text-estado-peligro text-lg'></i>" .
                        "<p class='text-xs text-estado-peligro font-semibold text-left'>Copie estas credenciales. La contraseña temporal no volverá a mostrarse. El usuario deberá cambiarla en su primer inicio de sesión.</p>" .
                        "</div>" .
                        "</div>";

                $this->dispatch('credencialesGeneradas', [
                    'html' => $html
                ]);
            } else {
                $this->dispatch('mostrarAlerta', [
                    'type' => 'success',
                    'title' => '¡Éxito!',
                    'message' => 'Personal ' . ($this->esEdicion ? 'actualizado' : 'vinculado/registrado') . ' correctamente.'
                ]);

                $this->dispatch('actualizarTablaPersonal');
                if (!$this->esEdicion) {
                    $this->dispatch('cerrarModalGestion');
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mostrarAlerta', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Ocurrió un error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $rolesQuery = Role::query()->whereNotIn('name', ['FAMILIAR', 'VOLUNTARIO']);
        if (auth()->check() && !auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            $rolesQuery->where('name', '!=', 'SUPERADMINISTRADOR');
        }

        return view('livewire.admin.personal-institucional.partials.personal-institucional-form', [
            'roles' => $rolesQuery->get(),
            'especialidades' => Especialidad::all(),
            'cargos' => CargoAdministrativo::where('estado', 'ACTIVO')->get(),
            'areas' => AreaInstitucional::where('estado', 'ACTIVO')->get(),
            'clasificacion_derivada' => $this->clasificacionDesdeRoles(),
        ]);
    }
}
