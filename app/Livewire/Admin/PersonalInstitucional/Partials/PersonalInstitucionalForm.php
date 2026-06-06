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
    public $rol_seleccionado = '';

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
    public $nivel_responsabilidad;

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

    // Contraseña temporal y opciones de acceso
    public $contrasena_temporal = '';
    public $mostrar_credenciales = true;
    public $forzar_cambio_password = true;
    public $tempId = '';

    // Wizard state
    public int $pasoActual = 1;
    public int $totalPasos = 7;

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
        $this->departamentos_list = $this->getDepartamentosCatalogo();
        $this->municipios_list = [];
        $this->zonas_list = [];
        $this->calles_list = [];

        $this->especialidades_list = Especialidad::orderBy('nombre')->get();
        $this->cargos_list = CargoAdministrativo::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        if (!$this->usuarioId) {
            $this->contrasena_temporal = Str::password(12, true, true, true, false);
            $this->tempId = (string) Str::uuid();
        }

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

        // Cargar direcciones jerárquicas usando catálogos internos
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

        $dept = collect($this->getDepartamentosCatalogo())->firstWhere('nombre', mb_strtoupper($ciudadRaw));
        if ($dept) {
            $this->ciudad_id = $dept->id;
            $this->municipios_list = $this->getMunicipiosCatalogo($dept->id);
            
            $mun = collect($this->municipios_list)->firstWhere('nombre', mb_strtoupper($municipioNombre));
            if ($mun) {
                $this->municipio_id = $mun->id;
                $this->zonas_list = $this->getZonasCatalogo($mun->id);

                $zonaModel = collect($this->zonas_list)->firstWhere('nombre', mb_strtoupper($zonaRaw));
                if ($zonaModel) {
                    $this->zona_id = $zonaModel->id;
                    $this->calles_list = $this->getCallesCatalogo($zonaModel->id);

                    $calleModel = collect($this->calles_list)->firstWhere('nombre', mb_strtoupper($calleNombre));
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
        $this->rol_seleccionado = count($this->roles_seleccionados) > 0 ? $this->roles_seleccionados[0] : '';
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
            ? $this->getMunicipiosCatalogo($value) 
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
            ? $this->getZonasCatalogo($value) 
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
            ? $this->getCallesCatalogo($value) 
            : [];
    }

    public function updatedCalleId($value)
    {
        if ($value !== 'OTRA') {
            $this->otra_calle = null;
        }
    }

    public function updatedRolSeleccionado($value)
    {
        $this->roles_seleccionados = $value ? [$value] : [];
        $this->sincronizarClasificacionDesdeRoles(false);
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
        $rol = $this->rol_seleccionado ?? (count($this->roles_seleccionados) > 0 ? $this->roles_seleccionados[0] : '');

        $docs = [];

        // --- BLOQUE: DOCUMENTACIÓN DEL INGRESANTE ---
        
        // Cédula de Identidad (Obligatorio inmediato para todos)
        $docs[] = [
            'id' => 'CI',
            'nombre' => 'Cédula de Identidad (Anverso y Reverso)',
            'desc' => 'Copia legible del documento de identidad',
            'obligatorio_inmediato' => true,
            'permite_plazo' => false,
            'tipo' => 'ingresante'
        ];

        // Fotografía actual (Obligatorio inmediato para todos)
        $docs[] = [
            'id' => 'FOTO_DOC',
            'nombre' => 'Fotografía Actual',
            'desc' => 'Fotografía formal fondo blanco',
            'obligatorio_inmediato' => true,
            'permite_plazo' => false,
            'tipo' => 'ingresante'
        ];

        // Hoja de Vida
        $docs[] = [
            'id' => 'CV',
            'nombre' => 'Hoja de Vida / CV',
            'desc' => 'Curriculum Vitae actualizado y documentado',
            'obligatorio_inmediato' => true,
            'permite_plazo' => false,
            'tipo' => 'ingresante'
        ];

        // Roles específicos
        if (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'])) {
            $docs[] = [
                'id' => 'TITULO',
                'nombre' => 'Título o Certificado de Formación',
                'desc' => 'Título profesional o certificado académico/técnico',
                'obligatorio_inmediato' => true,
                'permite_plazo' => false,
                'tipo' => 'ingresante'
            ];
            
            if (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
                $docs[] = [
                    'id' => 'MATRICULA',
                    'nombre' => 'Matrícula Profesional',
                    'desc' => 'Registro profesional vigente (Obligatorio para médicos)',
                    'obligatorio_inmediato' => $rol === 'MEDICO GENERAL/GERIATRA',
                    'permite_plazo' => $rol !== 'MEDICO GENERAL/GERIATRA',
                    'tipo' => 'ingresante'
                ];
            }
            
            if ($rol === 'MEDICO GENERAL/GERIATRA') {
                $docs[] = [
                    'id' => 'CERT_ESP',
                    'nombre' => 'Certificado de Especialidad',
                    'desc' => 'Requerido si se registra como especialista (Ej. Geriatra)',
                    'obligatorio_inmediato' => false,
                    'permite_plazo' => true,
                    'tipo' => 'ingresante'
                ];
            }
        }

        // Con Plazo (48 horas)
        $docs[] = [
            'id' => 'DOMICILIO',
            'nombre' => 'Referencia o Comprobante de Domicilio',
            'desc' => 'Factura de luz, agua o croquis',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'tipo' => 'ingresante'
        ];

        $docs[] = [
            'id' => 'EXP_LABORAL',
            'nombre' => 'Certificados de Experiencia',
            'desc' => 'Respaldo de la experiencia laboral',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'tipo' => 'ingresante'
        ];
        
        $docs[] = [
            'id' => 'CAPACITACION',
            'nombre' => 'Capacitaciones Específicas',
            'desc' => 'Certificados relevantes al cargo',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'tipo' => 'ingresante'
        ];

        $docs[] = [
            'id' => 'ANTECEDENTES',
            'nombre' => 'Certificado de Antecedentes',
            'desc' => 'FELCC, FELCN, REJAP (si la institución lo exige)',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'tipo' => 'ingresante'
        ];

        // Opcionales
        $docs[] = [
            'id' => 'RECOMENDACION',
            'nombre' => 'Cartas de Recomendación',
            'desc' => 'Referencias laborales opcionales',
            'obligatorio_inmediato' => false,
            'permite_plazo' => false,
            'tipo' => 'ingresante'
        ];
        
        $docs[] = [
            'id' => 'OTROS',
            'nombre' => 'Otros Respaldos',
            'desc' => 'Documentación adicional',
            'obligatorio_inmediato' => false,
            'permite_plazo' => false,
            'tipo' => 'ingresante'
        ];

        // --- BLOQUE: DOCUMENTACIÓN INSTITUCIONAL ---
        $docs = array_merge($docs, $this->obtenerDocumentosInstitucionalesPorRol($rol));

        return $docs;
    }

    public function obtenerDocumentosInstitucionalesPorRol($rol)
    {
        $docs = [];
        $esAdmin = in_array($rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']);

        // 1. Ficha institucional del personal
        $docs[] = [
            'id' => 'FICHA',
            'nombre' => 'Ficha Institucional del Personal',
            'desc' => 'Resumen de datos y relación con la institución',
            'tipo' => 'institucional',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'requiere_firma' => true,
            'archivo_generado' => null,
            'archivo_firmado' => null,
            'estado' => 'PENDIENTE'
        ];

        // 2. Contrato laboral o prestación profesional
        $docs[] = [
            'id' => 'CONTRATO',
            'nombre' => $esAdmin ? 'Contrato Laboral' : 'Contrato Laboral o Prestación Profesional',
            'desc' => 'Acuerdo formal de relación laboral o servicios',
            'tipo' => 'institucional',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'requiere_firma' => true,
            'archivo_generado' => null,
            'archivo_firmado' => null,
            'estado' => 'PENDIENTE'
        ];

        // 3. Declaración de confidencialidad
        $docs[] = [
            'id' => 'CONFIDENCIALIDAD',
            'nombre' => 'Declaración de Confidencialidad y Manejo de Información Sensible',
            'desc' => 'Compromiso de resguardo de información clínica/administrativa',
            'tipo' => 'institucional',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'requiere_firma' => true,
            'archivo_generado' => null,
            'archivo_firmado' => null,
            'estado' => 'PENDIENTE'
        ];

        // 4. Acta de asignación de funciones
        $descFunciones = match ($rol) {
            'SUPERADMINISTRADOR', 'ADMINISTRADOR' => 'Funciones administrativas, gestión institucional y documental',
            'ENFERMEROS' => 'Atención al adulto mayor, signos vitales, bioseguridad, medicamentos',
            'MEDICO GENERAL/GERIATRA' => 'Valoración médica, indicaciones, derivaciones y registro clínico',
            'PSICOLOGO/A' => 'Evaluaciones cognitivas/emocionales y observaciones psicológicas',
            'NUTRICIONISTA' => 'Valoración nutricional, dietas y restricciones alimentarias',
            'FISIOTERAPEUTA' => 'Movilidad segura, prevención de caídas y sesiones',
            'PEDAGOGO' => 'Estimulación cognitiva/social y acompañamiento socioeducativo',
            default => 'Asignación general de funciones'
        };

        $docs[] = [
            'id' => 'FUNCIONES',
            'nombre' => 'Acta de Asignación de Funciones y Horario',
            'desc' => $descFunciones,
            'tipo' => 'institucional',
            'obligatorio_inmediato' => false,
            'permite_plazo' => true,
            'requiere_firma' => true,
            'archivo_generado' => null,
            'archivo_firmado' => null,
            'estado' => 'PENDIENTE'
        ];

        return $docs;
    }

    public function descargarPdfInstitucional($codigo)
    {
        // En Livewire (AJAX) no se puede usar response()->download().
        // Si el PDF ya fue generado, abrirlo en nueva pestaña; si no, generarlo primero.
        $fileMap = [
            'FICHA'            => 'ficha.pdf',
            'CONTRATO'         => 'contrato.pdf',
            'CONFIDENCIALIDAD' => 'confidencialidad.pdf',
            'FUNCIONES'        => 'acta_funciones.pdf',
        ];

        if (!isset($fileMap[$codigo])) {
            $this->dispatch('mostrarAlerta', ['type' => 'error', 'title' => 'Error', 'message' => 'Documento no reconocido.']);
            return;
        }

        if (!empty($this->tempId)) {
            $filePath = "personal-institucional/temp/{$this->tempId}/{$fileMap[$codigo]}";
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
                $url = asset('storage/' . $filePath);
                $this->dispatch('abrirPdfGenerado', url: $url);
                return;
            }
        }

        $this->prepararGeneracionPdfInstitucional($codigo);
    }

    public function prepararGeneracionPdfInstitucional($codigo)
    {
        $viewMap = [
            'FICHA'            => 'pdf.personal-institucional.ficha',
            'CONTRATO'         => 'pdf.personal-institucional.contrato',
            'CONFIDENCIALIDAD' => 'pdf.personal-institucional.confidencialidad',
            'FUNCIONES'        => 'pdf.personal-institucional.acta-funciones',
        ];

        $fileMap = [
            'FICHA'            => 'ficha.pdf',
            'CONTRATO'         => 'contrato.pdf',
            'CONFIDENCIALIDAD' => 'confidencialidad.pdf',
            'FUNCIONES'        => 'acta_funciones.pdf',
        ];

        if (!isset($viewMap[$codigo])) {
            $this->dispatch('mostrarAlerta', ['type' => 'error', 'title' => 'Error', 'message' => 'Documento no reconocido.']);
            return;
        }

        try {
            if (empty($this->tempId)) {
                $this->tempId = (string) Str::uuid();
            }

            $data = $this->buildPdfData();
            $tempPath = "personal-institucional/temp/{$this->tempId}";
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($tempPath);

            $filePath = "{$tempPath}/{$fileMap[$codigo]}";

            // DomPDF: síncrono, sin Chromium, compatible con Livewire (no response()->download)
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewMap[$codigo], ['data' => $data]);
            \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $pdf->output());

            $this->estado_documentos[$codigo] = 'GENERADO';
            $url = asset('storage/' . $filePath);

            // Abrir en nueva pestaña vía evento JS (no hace reload del wizard)
            $this->dispatch('abrirPdfGenerado', url: $url);
            $this->dispatch('mostrarAlerta', [
                'type'    => 'success',
                'title'   => 'PDF generado',
                'message' => 'El documento se abrirá en una pestaña nueva. Imprima, firme y luego suba el firmado.',
            ]);
        } catch (\Throwable $e) {
            logger()->error("Error generando PDF institucional [{$codigo}]: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->dispatch('mostrarAlerta', [
                'type'    => 'error',
                'title'   => 'Error al generar PDF',
                'message' => 'No se pudo generar el documento. Verifique que los datos del trabajador estén completos.',
            ]);
        }
    }

    private function buildPdfData(): array
    {
        $clasificacion = $this->clasificacionDesdeRoles();
        return [
            'nombre_completo'  => trim("{$this->nombres} {$this->ap_paterno} {$this->ap_materno}"),
            'ci'               => $this->numero_documento,
            'expedido'         => $this->expedido,
            'correo'           => $this->correo,
            'telefono'         => $this->telefono,
            'direccion'        => $this->direccion,
            'rol'              => $this->rol_seleccionado ?: (empty($this->roles_seleccionados) ? 'No asignado' : $this->roles_seleccionados[0]),
            'clasificacion'    => $this->tipo_personal === 'salud' ? 'Personal de Salud' : 'Personal Administrativo',
            'cargo'            => $clasificacion['rol_label'] ?? 'No asignado',
            'area'             => $clasificacion['area_nombre'] ?? 'General',
            'tipo_personal'    => $this->tipo_personal,
            'rol_operativo'    => $clasificacion['rol_operativo'] ?? '',
            'anios_exp'        => $this->anios_exp,
            'matricula_prof'   => $this->matricula_prof,
            'institucion'      => $this->institucion_formacion,
            'fecha_ingreso'    => \Carbon\Carbon::parse($this->fecha_registro)->format('d/m/Y'),
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'responsable'      => auth()->user()
                                    ? auth()->user()->nombres . ' ' . auth()->user()->ap_paterno
                                    : 'Sistema',
        ];
    }

    public function updatedArchivosTemporales($value, $key)
    {
        $this->validateOnly("archivos_temporales.$key", [
            "archivos_temporales.$key" => 'nullable|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ], [
            "archivos_temporales.$key.mimes" => 'El archivo debe ser PDF, JPG, PNG o WEBP.',
            "archivos_temporales.$key.max" => 'El archivo no debe exceder los 5MB.',
        ]);

        $esInstitucional = collect($this->documentos_configurados)->firstWhere('id', $key)['tipo'] === 'institucional';
        $this->estado_documentos[$key] = $esInstitucional ? 'FIRMADO_SUBIDO' : 'CARGADO';
        
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
                'nombres' => 'required|string|max:255|regex:/^[\pL\s]+$/u',
                'ap_paterno' => 'required_without:ap_materno|nullable|string|max:255|regex:/^[\pL\s]+$/u',
                'ap_materno' => 'required_without:ap_paterno|nullable|string|max:255|regex:/^[\pL\s]+$/u',
                'numero_documento' => 'required|regex:/^[0-9]+$/|min:5|max:15|unique:users,numero_documento,' . $this->usuarioId . ',cod_usu',
                'expedido' => 'required|string|max:10',
                'genero' => 'required|in:M,F',
                'fecha_nacimiento' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d') . '|after_or_equal:' . now()->subYears(100)->format('Y-m-d'),
                'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ], [
                'nombres.required' => 'El nombre es obligatorio.',
                'nombres.regex' => 'El nombre no debe contener números.',
                'ap_paterno.required_without' => 'Debe ingresar el apellido paterno o materno.',
                'ap_paterno.regex' => 'El apellido paterno no debe contener números.',
                'ap_materno.required_without' => 'Debe ingresar el apellido paterno o materno.',
                'ap_materno.regex' => 'El apellido materno no debe contener números.',
                'numero_documento.required' => 'El CI es obligatorio.',
                'numero_documento.regex' => 'El documento de identidad debe contener solo números.',
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
                'ciudad_id' => 'required',
                'municipio_id' => 'required',
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
            $dept = collect($this->getDepartamentosCatalogo())->firstWhere('id', $this->ciudad_id);
            $mun = collect($this->getMunicipiosCatalogo($this->ciudad_id))->firstWhere('id', $this->municipio_id);
            
            $zonaNombre = '';
            if ($this->zona_id === 'OTRA') {
                $zonaNombre = mb_strtoupper($this->otra_zona);
            } else {
                $zonaModel = collect($this->getZonasCatalogo($this->municipio_id))->firstWhere('id', $this->zona_id);
                $zonaNombre = $zonaModel ? $zonaModel->nombre : '';
            }

            $calleNombre = '';
            if ($this->calle_id === 'OTRA') {
                $calleNombre = mb_strtoupper($this->otra_calle);
            } else {
                $calleModel = collect($this->getCallesCatalogo($this->zona_id))->firstWhere('id', $this->calle_id);
                $calleNombre = $calleModel ? $calleModel->nombre : '';
            }

            $this->ciudad = $dept ? $dept->nombre : '';
            $this->zona = $zonaNombre;
            $this->direccion = "MUNICIPIO: " . ($mun ? $mun->nombre : '') . " | CALLE: " . $calleNombre . " | NRO: " . mb_strtoupper($this->nro_casa);
            
        } elseif ($this->pasoActual === 4) {
            // Validar rol seleccionado
            $this->validate([
                'roles_seleccionados' => 'required|array|size:1',
                'roles_seleccionados.*' => 'exists:roles,name'
            ], [
                'roles_seleccionados.required' => 'Debe seleccionar un rol de sistema.',
                'roles_seleccionados.size' => 'Debe seleccionar únicamente un rol institucional.',
            ]);

            // Derivar clasificación
            $this->sincronizarClasificacionDesdeRoles();
            
            // Ya no se validan campos específicos de especialidad/cargo aquí.
            // Se manejan como documentos en el Paso 5.

        } elseif ($this->pasoActual === 5) {
            // Paso 5: Documentos del trabajador
            $this->validate([
                'archivos_temporales.*' => 'nullable|mimes:pdf,jpg,jpeg,png,webp|max:5120'
            ]);

            if (!$this->esEdicion) {
                $docsFaltantes = [];
                foreach ($this->documentos_configurados as $doc) {
                    if ($doc['tipo'] === 'ingresante' && $doc['obligatorio_inmediato']) {
                        if (!isset($this->archivos_temporales[$doc['id']])) {
                            $docsFaltantes[] = $doc['nombre'];
                        }
                    }
                }
                if (!empty($docsFaltantes)) {
                    $this->addError('archivos_temporales', 'Faltan documentos obligatorios: ' . implode(', ', $docsFaltantes));
                    return;
                }
            }

        } elseif ($this->pasoActual === 6) {
            // Paso 6: Documentación institucional — sincroniazr estado
            $this->faltan_documentos   = false;
            $this->faltan_recomendados = false;

            foreach ($this->documentos_configurados as $doc) {
                $estadoDoc = $this->estado_documentos[$doc['id']] ?? 'PENDIENTE';
                if ($estadoDoc === 'PENDIENTE') {
                    $this->faltan_documentos = true;
                    if (!($doc['obligatorio_inmediato'] ?? false)) {
                        $this->faltan_recomendados = true;
                    }
                }
            }

            if ($this->faltan_documentos && $this->estado === 'ACTIVO') {
                $this->estado = 'DOCUMENTACION_PENDIENTE';
                $this->dispatch('mostrarAlerta', [
                    'type'    => 'warning',
                    'title'   => 'Documentos pendientes',
                    'message' => 'El personal se registrará en estado DOCUMENTACION_PENDIENTE. Tendrá 48 horas para regularizar.',
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
                ? 'CARGADO'
                : ($this->estado_documentos[$doc['id']] ?? 'PENDIENTE');
            $obs = $this->observacion_documentos[$doc['id']]
                ?? ($estado === 'PENDIENTE' ? 'Pendiente (plazo: 48 horas)' : '');

            $documentosSession[] = [
                'id' => $doc['id'],
                'nombre' => $doc['nombre'],
                'obligatorio_inmediato' => $doc['obligatorio_inmediato'] ?? false,
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
                if ($this->pasoActual === 7) {
                    $this->estado = $this->determinarEstadoDocumental();
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

    public function gotoStep(int $paso): void
    {
        if ($paso >= 1 && $paso <= $this->totalPasos && $paso <= $this->pasoActual) {
            $this->pasoActual = $paso;
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

    public function determinarEstadoDocumental(): string
    {
        $faltanInstitucionales = false;
        $faltan48h = false;

        foreach ($this->documentos_configurados as $doc) {
            if ($doc['tipo'] === 'institucional') {
                $estado = $this->estado_documentos[$doc['id']] ?? 'PENDIENTE';
                if ($estado !== 'FIRMADO_SUBIDO') {
                    $faltanInstitucionales = true;
                }
            } elseif ($doc['tipo'] === 'ingresante' && isset($doc['permite_plazo']) && $doc['permite_plazo']) {
                if (!isset($this->archivos_temporales[$doc['id']])) {
                    $faltan48h = true;
                }
            }
        }

        if ($faltanInstitucionales) {
            return 'INSTITUCIONAL PENDIENTE';
        }

        if ($faltan48h) {
            return 'DOCUMENTACIÓN PENDIENTE';
        }

        return 'COMPLETO';
    }

    public function preGuardar()
    {
        $this->estado = $this->determinarEstadoDocumental();

        $this->dispatch('confirmarRegistroFinal', [
            'faltan_documentos' => $this->estado !== 'COMPLETO',
            'estado' => $this->estado
        ]);
    }

    public function guardar()
    {
        $clasificacion = $this->sincronizarClasificacionDesdeRoles();

        if (!$this->esEdicion) {
            $this->validate();
        }

        $this->estado = $this->determinarEstadoDocumental();

        DB::beginTransaction();
        try {
            if ($this->esEdicion) {
                $usuario = User::findOrFail($this->usuarioId);
            } else {
                $usuario = new User();
                // Usar la contraseña temporal que se mostró al usuario en el Paso 1
                $usuario->password = Hash::make($this->contrasena_temporal);
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
            $usuario->direccion = $this->direccion;
            $usuario->zona = $this->zona;
            $usuario->ciudad = $this->ciudad;
            $usuario->cod_area = $this->cod_area;
            
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
                $ps->institucion_formacion = $this->institucion_formacion ?: null;
                $ps->tipo_personal_salud = $this->rol_operativo ?: null;
                $ps->subtipo_enfermeria = $this->subtipo_enfermeria ?: null;
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
                $pa->observaciones = ($this->observaciones ? $this->observaciones . " | " : "") . "Nivel de responsabilidad: " . $this->nivel_responsabilidad;
                $pa->save();

                // Eliminar posible registro salud si cambió
                PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
            } else {
                PersonalSalud::where('cod_usu', $usuario->cod_usu)->delete();
                PersonalAdmin::where('cod_usu', $usuario->cod_usu)->delete();
            }

            // Guardar documentos utilizando el modelo DocumentoUsuario para asegurar esquema correcto y generación de ID
            $documentosFaltantes = [];
            $plazoVencimiento = \Carbon\Carbon::parse($this->fecha_registro)->addHours(48)->toDateString();
            $responsableId = auth()->id();

            foreach ($this->documentos_configurados as $doc) {
                $esInstitucional = ($doc['tipo'] === 'institucional');
                $tipoDoc  = $esInstitucional ? 'INSTITUCIONAL' : 'PERSONAL';
                $fechaDoc = $esInstitucional ? \Carbon\Carbon::parse($this->fecha_registro)->toDateString() : null;

                $estadoDoc = $this->estado_documentos[$doc['id']] ?? 'PENDIENTE';

                // Usar Eloquent para generar el ID (cod_doc_usu) automáticamente
                $docBD = \App\Models\DocumentoUsuario::where('cod_usu', $usuario->cod_usu)
                    ->where('nombre_documento', $doc['nombre'])
                    ->first();

                if (!$docBD) {
                    $docBD = new \App\Models\DocumentoUsuario();
                    $docBD->cod_usu = $usuario->cod_usu;
                    $docBD->nombre_documento = $doc['nombre'];
                    $docBD->tipo_documento = $tipoDoc;
                }

                if ($estadoDoc === 'CARGADO' && isset($this->archivos_temporales[$doc['id']])) {
                    $file = $this->archivos_temporales[$doc['id']];
                    if (!is_string($file)) {
                        $path = $file->store('documentos_personal', 'public');

                        $docBD->archivo = $path;
                        $docBD->extension = $file->getClientOriginalExtension();
                        $docBD->fecha_emision = $fechaDoc;
                        $docBD->mime_type = $file->getMimeType();
                        $docBD->tamanio = $file->getSize();
                        $docBD->estado = 'CARGADO';
                        $docBD->subido_por = $responsableId;
                        $docBD->observaciones = null;
                        $docBD->motivo_observacion = null;
                        $docBD->fecha_vencimiento = null;
                        $docBD->save();
                    }
                } elseif ($estadoDoc === 'OBSERVADO') {
                    $obsTexto = $this->observacion_documentos[$doc['id']] ?? 'Documento observado.';
                    
                    $docBD->estado = 'OBSERVADO';
                    $docBD->motivo_observacion = $obsTexto;
                    $docBD->fecha_vencimiento = $plazoVencimiento;
                    if (empty($docBD->archivo)) {
                        $docBD->archivo = 'pendiente';
                    }
                    $docBD->save();
                } elseif ($estadoDoc === 'PENDIENTE') {
                    $obsDefault = $esInstitucional
                        ? 'Documento institucional pendiente. Regularizar en 48 horas.'
                        : 'Pendiente de entrega (plazo: 48 horas).';

                    if ($esInstitucional && in_array($doc['id'], ['CONTRATO', 'CONFIDENCIALIDAD'])) {
                        $documentosFaltantes[] = $doc['nombre'];
                    }

                    $docBD->estado = 'PENDIENTE';
                    $docBD->observaciones = $obsDefault;
                    $docBD->fecha_vencimiento = $plazoVencimiento;
                    if (empty($docBD->archivo)) {
                        $docBD->archivo = 'pendiente';
                    }
                    $docBD->save();
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

            if (!$this->esEdicion) {
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
                        "<span class='font-mono font-bold text-lg tracking-widest text-titulo'>" . htmlspecialchars($this->contrasena_temporal) . "</span></div>" .
                        "</div>" .
                        "<div class='p-3 bg-estado-peligroBg border border-estado-peligro rounded-xl flex gap-2'>" .
                        "<i class='ph-bold ph-warning-circle text-estado-peligro text-lg'></i>" .
                        "<p class='text-xs text-estado-peligro font-semibold text-left'>Copie estas credenciales. La contraseña temporal no volverá a mostrarse. El usuario deberá cambiarla en su primer inicio de sesión.</p>" .
                        "</div>" .
                        "</div>";

                $this->dispatch('credencialesGeneradas', ['html' => $html]);
            } else {
                $this->dispatch('mostrarAlerta', [
                    'type' => 'success',
                    'title' => '¡Actualizado!',
                    'message' => 'Los datos del personal han sido actualizados correctamente.',
                ]);
                // En edición: refrescar tabla del panel pero mantener modal abierto
                $this->dispatch('actualizarTablaPersonal');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('mostrarAlerta', [
                'type' => 'error',
                'title' => 'Error al guardar',
                'message' => 'Ocurrió un error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $rolesPermitidos = [
            'SUPERADMINISTRADOR',
            'ADMINISTRADOR',
            'ENFERMEROS',
            'MEDICO GENERAL/GERIATRA',
            'PSICOLOGO/A',
            'PEDAGOGO',
            'NUTRICIONISTA',
            'FISIOTERAPEUTA'
        ];

        $rolesQuery = Role::query()->whereIn('name', $rolesPermitidos);
        
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

    public function getDepartamentosCatalogo() {
        return [
            (object)['id' => 'LPZ', 'nombre' => 'LA PAZ'],
        ];
    }
    
    public function getMunicipiosCatalogo($deptId) {
        if ($deptId === 'LPZ') {
            return [
                (object)['id' => 'MUN_LPZ', 'nombre' => 'LA PAZ'],
                (object)['id' => 'MUN_EAL', 'nombre' => 'EL ALTO'],
            ];
        }
        return [];
    }

    public function getZonasCatalogo($munId) {
        if ($munId === 'MUN_LPZ') {
            return [
                (object)['id' => 'ZON_SUR', 'nombre' => 'ZONA SUR (OBRAJES/CALACOTO/SAN MIGUEL)'],
                (object)['id' => 'ZON_CEN', 'nombre' => 'CENTRO'],
                (object)['id' => 'ZON_SOP', 'nombre' => 'SOPOCACHI'],
                (object)['id' => 'ZON_MIR', 'nombre' => 'MIRAFLORES'],
                (object)['id' => 'ZON_COT', 'nombre' => 'COTA COTA / CHASQUIPAMPA'],
            ];
        } elseif ($munId === 'MUN_EAL') {
            return [
                (object)['id' => 'ZON_SAT', 'nombre' => 'CIUDAD SATÉLITE'],
                (object)['id' => 'ZON_VAD', 'nombre' => 'VILLA ADELA'],
                (object)['id' => 'ZON_16J', 'nombre' => '16 DE JULIO'],
                (object)['id' => 'ZON_CEJ', 'nombre' => 'LA CEJA'],
            ];
        }
        return [];
    }

    public function getCallesCatalogo($zonaId) {
        switch ($zonaId) {
            case 'ZON_SUR':
                return [
                    (object)['id' => 'CAL_BAL', 'nombre' => 'AV. BALLIVIÁN'],
                    (object)['id' => 'CAL_HER', 'nombre' => 'AV. HERNANDO SILES'],
                    (object)['id' => 'CAL_21', 'nombre' => 'CALLE 21 DE CALACOTO'],
                ];
            case 'ZON_CEN':
                return [
                    (object)['id' => 'CAL_PRA', 'nombre' => 'AV. 16 DE JULIO (PRADO)'],
                    (object)['id' => 'CAL_CAM', 'nombre' => 'CALLE CAMACHO'],
                    (object)['id' => 'CAL_PST', 'nombre' => 'CALLE POTOSÍ'],
                ];
            case 'ZON_SOP':
                return [
                    (object)['id' => 'CAL_ARC', 'nombre' => 'AV. ARCE'],
                    (object)['id' => 'CAL_6AG', 'nombre' => 'AV. 6 DE AGOSTO'],
                    (object)['id' => 'CAL_20O', 'nombre' => 'AV. 20 DE OCTUBRE'],
                ];
            case 'ZON_MIR':
                return [
                    (object)['id' => 'CAL_BUS', 'nombre' => 'AV. BUSCH'],
                    (object)['id' => 'CAL_SAA', 'nombre' => 'AV. SAAVEDRA'],
                ];
            case 'ZON_SAT':
                return [
                    (object)['id' => 'CAL_SAT1', 'nombre' => 'PLAN 561'],
                    (object)['id' => 'CAL_SAT2', 'nombre' => 'PLAN 482'],
                ];
            default:
                return [
                    (object)['id' => 'CAL_PRI', 'nombre' => 'AV. PRINCIPAL'],
                    (object)['id' => 'CAL_SEC', 'nombre' => 'CALLE SECUNDARIA'],
                ];
        }
    }
}
