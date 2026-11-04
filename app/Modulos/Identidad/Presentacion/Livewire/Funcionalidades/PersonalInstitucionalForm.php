<?php

namespace App\Modulos\Identidad\Presentacion\Livewire\Funcionalidades;

use App\Models\Area;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GuardarPersonalInstitucional;
use App\Modulos\Identidad\Dominio\Reglas\ClasificacionRolInstitucional;
use App\Modulos\Identidad\Dominio\Reglas\DocumentosPersonalInstitucional;
use App\Modulos\Identidad\Infraestructura\Documentos\GenerarDocumentoInstitucional;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Locked;
use App\Modulos\Identidad\Aplicacion\Acciones\AutorizarGestionPersonal;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PersonalInstitucionalForm extends Component
{
    use WithFileUploads;

    #[Locked] public $usuarioId;

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

    public $forzar_cambio_password = false;

    public $fecha_ingreso;

    public $tempId = '';

    // Wizard state
    public int $pasoActual = 1;

    #[Locked] public int $totalPasos = 4;

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
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(), $usuarioId ? User::findOrFail($usuarioId) : null);
        $this->usuarioId = $usuarioId;
        $this->estado = 'ACTIVO';
        $this->fecha_registro = now()->format('Y-m-d');
        $this->hora_registro = now()->format('H:i');
        $this->departamentos_list = $this->getDepartamentosCatalogo();
        $this->municipios_list = [];
        $this->zonas_list = [];
        $this->calles_list = [];

        $this->especialidades_list = $this->catalogoEspecialidadesPorRol();
        $this->cargos_list = $this->catalogoCargosAdministrativosPorRol();

        if (! $this->usuarioId) {
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
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(), User::findOrFail($this->usuarioId));
        $usuario = User::with(['roles', 'personal', 'documentos'])->find($this->usuarioId);
        if (! $usuario) {
            return;
        }

        $personal = $usuario->personal;
        $this->fecha_ingreso = $personal?->fecha_ingreso?->format('Y-m-d');
        $this->foto_perfil = $usuario->foto;
        $this->nombres = $personal?->nombres;
        $this->ap_paterno = $personal?->apellido_paterno;
        $this->ap_materno = $personal?->apellido_materno;
        $this->correo = $usuario->correo;
        $this->telefono = $personal?->telefono;
        $this->numero_documento = $personal?->numero_documento;
        $this->expedido = $personal?->expedicion_documento;
        $this->genero = $personal?->genero;
        $this->fecha_nacimiento = $personal?->fecha_nacimiento?->format('Y-m-d');
        $this->calcularEdad($this->fecha_nacimiento);
        $this->estado = $usuario->estado;

        $this->direccion = $personal?->direccion;
        $this->zona = null;
        $this->ciudad = null;
        $this->observaciones = $personal?->observacion;

        // Cargar direcciones jerárquicas usando catálogos internos
        $direccionRaw = $personal?->direccion;
        $ciudadRaw = null;
        $zonaRaw = null;

        $municipioNombre = '';
        $calleNombre = '';
        $nroCasa = '';

        if ($direccionRaw && preg_match('/^DEPARTAMENTO:\s*(.*?)\s*\|\s*MUNICIPIO:\s*(.*?)\s*\|\s*ZONA:\s*(.*?)\s*\|\s*CALLE:\s*(.*?)\s*\|\s*NRO:\s*(.*)$/i', $direccionRaw, $matches)) {
            [$ciudadRaw, $municipioNombre, $zonaRaw, $calleNombre, $nroCasa] = array_map('trim', array_slice($matches, 1));
        } elseif ($direccionRaw && preg_match('/MUNICIPIO:\s*(.*?)\s*\|\s*CALLE:\s*(.*?)\s*\|\s*NRO:\s*(.*)/i', $direccionRaw, $matches)) {
            $municipioNombre = trim($matches[1]);
            $calleNombre = trim($matches[2]);
            $nroCasa = trim($matches[3]);
        } else {
            $calleNombre = $direccionRaw ?: '';
            $nroCasa = 'S/N';
        }

        $dept = collect($this->getDepartamentosCatalogo())->firstWhere('nombre', mb_strtoupper($ciudadRaw ?? ''));
        if ($dept) {
            $this->ciudad_id = $dept->id;
            $this->municipios_list = $this->getMunicipiosCatalogo($dept->id);

            $mun = collect($this->municipios_list)->firstWhere('nombre', mb_strtoupper($municipioNombre));
            if ($mun) {
                $this->municipio_id = $mun->id;
                $this->zonas_list = $this->getZonasCatalogo($mun->id);

                $zonaModel = collect($this->zonas_list)->firstWhere('nombre', mb_strtoupper($zonaRaw ?? ''));
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
        $this->matricula_prof = $personal?->matricula_profesional;
        foreach ($usuario->documentos->whereNotIn('estado', ['ANULADO', 'ANULADA', 'REEMPLAZADO']) as $documento) {
            $this->estado_documentos[$documento->tipo_documento] = $documento->estado;
        }

        $this->sincronizarClasificacionDesdeRoles(false);
    }

    protected function rules()
    {
        return [
            'nombres' => 'required|string|max:100',
            'ap_paterno' => 'required|string|max:80',
            'ap_materno' => 'nullable|string|max:80',
            'correo' => 'required|email|max:120|unique:usuarios,correo,'.$this->usuarioId.',cod_usuario',
            'fecha_ingreso' => 'nullable|date',
            'numero_documento' => ['required', 'regex:/^[0-9]{5,10}$/', Rule::unique('personal', 'numero_documento')->ignore(Personal::where('cod_usuario', $this->usuarioId)->value('cod_personal'), 'cod_personal')],
            'estado' => 'required|string',
            'fecha_nacimiento' => 'required|date|before_or_equal:'.now()->subYears(18)->format('Y-m-d').'|after_or_equal:'.now()->subYears(100)->format('Y-m-d'),
            'foto_perfil' => is_string($this->foto_perfil) ? 'nullable|string' : 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
                $this->edad = Carbon::parse($value)->age;
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
        $clasificacion = ClasificacionRolInstitucional::paraRol($rol);
        if ($clasificacion === null) {
            return null;
        }
        $codigo = $clasificacion['cod_area_referencia'];
        $nombre = $codigo ? Area::find($codigo)?->nombre : null;
        $nombre ??= 'Selecciona un área institucional';

        return [...$clasificacion, 'cod_area' => $codigo, 'area_nombre' => $nombre];
    }

    public function getDocumentosConfiguradosProperty(): array
    {
        return DocumentosPersonalInstitucional::paraRol((string) ($this->roles_seleccionados[0] ?? ''));
    }

    public function obtenerDocumentosInstitucionalesPorRol($rol): array
    {
        return DocumentosPersonalInstitucional::institucionales((string) $rol);
    }

    public function descargarPdfInstitucional($codigo)
    {
        // En Livewire (AJAX) no se puede usar response()->download().
        // Si el PDF ya fue generado, abrirlo en nueva pestaña; si no, generarlo primero.
        $fileMap = [
            'FICHA' => 'ficha.pdf',
            'CONTRATO' => 'contrato.pdf',
            'CONFIDENCIALIDAD' => 'confidencialidad.pdf',
            'FUNCIONES' => 'acta_funciones.pdf',
        ];

        if (! isset($fileMap[$codigo])) {
            $this->dispatch('mostrarAlerta', ['type' => 'error', 'title' => 'Error', 'message' => 'Documento no reconocido.']);

            return;
        }

        if (! empty($this->tempId)) {
            $filePath = "personal-institucional/temp/{$this->tempId}/{$fileMap[$codigo]}";
            if (Storage::disk('public')->exists($filePath)) {
                $url = asset('storage/'.$filePath);
                $this->dispatch('abrirPdfGenerado', url: $url);

                return;
            }
        }

        $this->prepararGeneracionPdfInstitucional($codigo);
    }

    public function prepararGeneracionPdfInstitucional($codigo)
    {
        $viewMap = [
            'FICHA' => 'pdf.personal-institucional.ficha',
            'CONTRATO' => 'pdf.personal-institucional.contrato',
            'CONFIDENCIALIDAD' => 'pdf.personal-institucional.confidencialidad',
            'FUNCIONES' => 'pdf.personal-institucional.acta-funciones',
        ];

        $fileMap = [
            'FICHA' => 'ficha.pdf',
            'CONTRATO' => 'contrato.pdf',
            'CONFIDENCIALIDAD' => 'confidencialidad.pdf',
            'FUNCIONES' => 'acta_funciones.pdf',
        ];

        if (! isset($viewMap[$codigo])) {
            $this->dispatch('mostrarAlerta', ['type' => 'error', 'title' => 'Error', 'message' => 'Documento no reconocido.']);

            return;
        }

        try {
            if (empty($this->tempId)) {
                $this->tempId = (string) Str::uuid();
            }

            $data = $this->buildPdfData();
            $filePath = app(GenerarDocumentoInstitucional::class)->ejecutar($this->tempId, $viewMap[$codigo], $fileMap[$codigo], $data);
            $this->estado_documentos[$codigo] = 'GENERADO';
            $url = asset('storage/'.$filePath);

            // Abrir en nueva pestaña vía evento JS (no hace reload del wizard)
            $this->dispatch('abrirPdfGenerado', url: $url);
            $this->dispatch('mostrarAlerta', [
                'type' => 'success',
                'title' => 'PDF generado',
                'message' => 'El documento se abrirá en una pestaña nueva. Imprima, firme y luego suba el firmado.',
            ]);
        } catch (\Throwable $e) {
            logger()->error("Error generando PDF institucional [{$codigo}]: ".$e->getMessage()."\n".$e->getTraceAsString());
            $this->dispatch('mostrarAlerta', [
                'type' => 'error',
                'title' => 'Error al generar PDF',
                'message' => 'No se pudo generar el documento. Verifique que los datos del trabajador estén completos.',
            ]);
        }
    }

    private function buildPdfData(): array
    {
        $clasificacion = $this->clasificacionDesdeRoles();

        return [
            'nombre_completo' => trim("{$this->nombres} {$this->ap_paterno} {$this->ap_materno}"),
            'ci' => $this->numero_documento,
            'expedido' => $this->expedido,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'rol' => $this->rol_seleccionado ?: (empty($this->roles_seleccionados) ? 'No asignado' : $this->roles_seleccionados[0]),
            'clasificacion' => $this->tipo_personal === 'salud' ? 'Personal de Salud' : 'Personal Administrativo',
            'cargo' => $clasificacion['rol_label'] ?? 'No asignado',
            'area' => $clasificacion['area_nombre'] ?? 'General',
            'tipo_personal' => $this->tipo_personal,
            'rol_operativo' => $clasificacion['rol_operativo'] ?? '',
            'anios_exp' => $this->anios_exp,
            'matricula_prof' => $this->matricula_prof,
            'institucion' => $this->institucion_formacion,
            'fecha_ingreso' => $this->fecha_ingreso ? Carbon::parse($this->fecha_ingreso)->format('d/m/Y') : 'No registrada',
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'responsable' => auth()->user()
                                    ? auth()->user()->nombres.' '.auth()->user()->ap_paterno
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
            activity()->log('Cargó temporalmente el documento: '.$key);
        }
    }

    public function observarDocumento($key, $observacion)
    {
        if (empty(trim($observacion))) {
            $this->addError("observacion_$key", 'La observación es obligatoria.');

            return;
        }
        $this->observacion_documentos[$key] = $observacion;
        $this->estado_documentos[$key] = 'OBSERVADO';

        if (function_exists('activity')) {
            activity()->log('Observó el documento: '.$key);
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
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(), $this->usuarioId ? User::findOrFail($this->usuarioId) : null, $this->roles_seleccionados);
        $reglas = match ($this->pasoActual) {
            1 => ['nombres'=>'required|string|max:100','ap_paterno'=>'required|string|max:80','ap_materno'=>'nullable|string|max:80',
                'numero_documento'=>['required','regex:/^[0-9]{5,10}$/',Rule::unique('personal','numero_documento')->ignore(Personal::where('cod_usuario',$this->usuarioId)->value('cod_personal'),'cod_personal')],
                'expedido'=>'required|string|max:20','genero'=>'required|in:M,F','fecha_nacimiento'=>'required|date|before_or_equal:'.now()->subYears(18)->toDateString(),
                'telefono'=>['required','regex:/^[67][0-9]{7}$/'],'direccion'=>'required|string|max:255'],
            2 => ['roles_seleccionados'=>'required|array|size:1','roles_seleccionados.*'=>'exists:roles,name','fecha_ingreso'=>'nullable|date','matricula_prof'=>'nullable|string|max:50'],
            3 => ['correo'=>['required','email','max:120',Rule::unique('usuarios','correo')->ignore($this->usuarioId,'cod_usuario')],
                'contrasena_temporal'=>($this->esEdicion ? 'nullable' : 'required').'|string|min:12|max:72'],
            4 => ['archivos_temporales'=>'array','archivos_temporales.*'=>'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120'],
            default => ['pasoActual'=>'in:1,2,3,4'],
        };
        $this->validate($reglas);
    }

    public function avanzarPaso(): void
    {
        try {
            $this->validarPasoActual();

            if ($this->pasoActual < $this->totalPasos) {
                $this->pasoActual++;
                if ($this->pasoActual === 4) {
                    // El resumen documental no modifica el estado de acceso de la cuenta.
                    $this->sincronizarSessionTemporal();
                }
            }
        } catch (ValidationException $e) {
            if ($this->pasoActual === 1 && $e->validator->errors()->has('correo') && str_contains($e->validator->errors()->first('correo'), 'registrado')) {
                $this->dispatch('mostrarAlerta', [
                    'type' => 'error',
                    'title' => 'Correo Duplicado',
                    'message' => 'El correo ingresado ya existe en la base de datos. Por favor, utilice otro correo institucional.',
                ]);
            } else {
                $this->dispatch('mostrarAlerta', [
                    'type' => 'error',
                    'title' => 'Campos Incompletos',
                    'message' => 'Revise los campos obligatorios antes de continuar.',
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
                $exists = User::role('MEDICO GENERAL/GERIATRA')
                    ->where('estado', 'ACTIVO')
                    ->where('cod_usuario', '!=', $this->usuarioId)
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
                    'roles' => empty($this->roles_seleccionados) ? 'Ninguno' : implode(', ', $this->roles_seleccionados),
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
                if (! isset($this->archivos_temporales[$doc['id']])) {
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
        $estadoDocumental = $this->determinarEstadoDocumental();

        $this->dispatch('confirmarRegistroFinal', [
            'faltan_documentos' => $estadoDocumental !== 'COMPLETO',
            'estado' => 'ACTIVO',
        ]);
    }

    public function guardar()
    {
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(), $this->usuarioId ? User::findOrFail($this->usuarioId) : null, $this->roles_seleccionados);
        $clasificacion = $this->sincronizarClasificacionDesdeRoles();

        $this->validate();

        if (! $this->esEdicion) {
            $this->estado = 'ACTIVO';
        }
        $estadoDocumental = $this->determinarEstadoDocumental();

        try {
            $resultado = app(GuardarPersonalInstitucional::class)->guardar(auth()->user(), ['esEdicion' => $this->esEdicion, 'usuarioId' => $this->usuarioId, 'contrasena_temporal' => $this->contrasena_temporal, 'forzar_cambio_password' => $this->forzar_cambio_password, 'fecha_registro' => $this->fecha_registro, 'hora_registro' => $this->hora_registro, 'fecha_ingreso' => $this->fecha_ingreso, 'nombres' => $this->nombres, 'ap_paterno' => $this->ap_paterno, 'ap_materno' => $this->ap_materno, 'correo' => $this->correo, 'telefono' => $this->telefono, 'numero_documento' => $this->numero_documento, 'expedido' => $this->expedido, 'fecha_nacimiento' => $this->fecha_nacimiento, 'genero' => $this->genero, 'estado' => $this->estado, 'observaciones' => $this->observaciones, 'direccion' => $this->direccion, 'zona' => $this->zona, 'ciudad' => $this->ciudad, 'cod_area' => $this->cod_area, 'foto_perfil' => $this->foto_perfil, 'roles_seleccionados' => $this->roles_seleccionados, 'documentos_configurados' => $this->documentos_configurados, 'estado_documentos' => $this->estado_documentos, 'archivos_temporales' => $this->archivos_temporales, 'observacion_documentos' => $this->observacion_documentos, 'tempId' => $this->tempId, 'institucion_formacion' => $this->institucion_formacion, 'matricula_prof' => $this->matricula_prof, 'anios_exp' => $this->anios_exp, 'subtipo_enfermeria' => $this->subtipo_enfermeria, 'nivel_responsabilidad' => $this->nivel_responsabilidad]);
            $usuario = $resultado['usuario'];

            if (app(AutorizarGestionPersonal::class)->esGerencia(auth()->user())) {
                $falloCorreo = !$this->esEdicion && $resultado['correo_enviado'] === false;
                $this->dispatch('swal', [
                    'icon' => $falloCorreo ? 'warning' : 'success',
                    'title' => $this->esEdicion ? 'Datos actualizados' : 'Trabajador registrado',
                    'text' => $falloCorreo ? 'El registro quedó guardado, pero la bienvenida no pudo enviarse. Revisa la entrega del acceso.' : 'Los datos del trabajador quedaron guardados.',
                ]);
                $this->contrasena_temporal = '';
                $this->dispatch('actualizarTablaPersonal');
                $this->dispatch('cerrar-modal-gestion-personal');
                return;
            }

            if (! $this->esEdicion) {
                $correoTexto = match ($resultado['correo_enviado']) {
                    true => 'La bienvenida se envió al correo registrado.',
                    false => 'La cuenta quedó registrada, pero no se pudo enviar la bienvenida. Solicita a gestión de cuentas la entrega del acceso.',
                    default => 'La bienvenida se enviará cuando se confirme la operación.',
                };
                $html = '<div class="text-left space-y-3 mt-4"><p class="font-semibold text-titulo">'.e($usuario->name).'</p>'
                    .'<p>'.e($usuario->correo).'</p><p>'.e($correoTexto).'</p>';
                if ($this->mostrar_credenciales) {
                    $html .= '<div class="p-4 bg-boton-acento/10 border border-boton-acento rounded-xl"><span class="font-bold text-boton-acento">Contraseña temporal</span>'
                        .'<p class="font-mono text-titulo">'.e($this->contrasena_temporal).'</p><p>Entrega estas credenciales de forma segura.</p></div>';
                }
                $html .= '</div>';
                $this->dispatch('credencialesGeneradas', ['html' => $html, 'usuarioId' => $usuario->getKey()]);
                $this->contrasena_temporal = '';
            } else {
                $this->dispatch('mostrarAlerta', [
                    'type' => 'success',
                    'title' => '¡Actualizado!',
                    'message' => 'Los datos del personal han sido actualizados correctamente.',
                ]);
                // En edición: refrescar tabla del panel pero mantener modal abierto
                $this->dispatch('actualizarTablaPersonal');
            }

        } catch (ValidationException $e) {
            throw $e;
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('guardado', 'No se pudo completar el registro. Los datos siguen disponibles para revisar e intentar de nuevo.');
            $this->dispatch('mostrarAlerta', [
                'type' => 'error',
                'title' => 'Error al guardar',
                'message' => 'No se pudo completar el registro. Compruebe los datos e inténtelo nuevamente.',
            ]);
        }
    }

    public function render()
    {
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(), $this->usuarioId ? User::findOrFail($this->usuarioId) : null);
        $rolesPermitidos = ClasificacionRolInstitucional::ROLES;

        $rolesQuery = Role::query()->whereIn('name', $rolesPermitidos);

        if (auth()->check() && ! auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            $rolesQuery->whereIn('name', AutorizarGestionPersonal::ROLES_TRABAJADOR);
        }

        return view('livewire.identidad.personal-institucional-form', [
            'roles' => $rolesQuery->get(),
            'especialidades' => $this->catalogoEspecialidadesPorRol(),
            'cargos' => $this->catalogoCargosAdministrativosPorRol(),
            'areas' => Area::whereIn('estado', ['ACTIVA', 'ACTIVO'])->get(),
            'clasificacion_derivada' => $this->clasificacionDesdeRoles(),
        ]);
    }

    private function catalogoEspecialidadesPorRol(): Collection
    {
        return collect([
            (object) ['cod_esp' => 'ENFERMEROS', 'nombre' => 'ENFERMEROS'],
            (object) ['cod_esp' => 'MEDICO GENERAL/GERIATRA', 'nombre' => 'MEDICO GENERAL/GERIATRA'],
            (object) ['cod_esp' => 'PSICOLOGO/A', 'nombre' => 'PSICOLOGO/A'],
            (object) ['cod_esp' => 'PEDAGOGO', 'nombre' => 'PEDAGOGO'],
            (object) ['cod_esp' => 'NUTRICIONISTA', 'nombre' => 'NUTRICIONISTA'],
            (object) ['cod_esp' => 'FISIOTERAPEUTA', 'nombre' => 'FISIOTERAPEUTA'],
        ]);
    }

    private function catalogoCargosAdministrativosPorRol(): Collection
    {
        return collect([
            (object) ['cod_cargo_admin' => 'SUPERADMINISTRADOR', 'nombre' => 'SUPERADMINISTRADOR'],
            (object) ['cod_cargo_admin' => 'ADMINISTRADOR', 'nombre' => 'ADMINISTRADOR'],
        ]);
    }

    public function getDepartamentosCatalogo()
    {
        return [
            (object) ['id' => 'LPZ', 'nombre' => 'LA PAZ'],
        ];
    }

    public function getMunicipiosCatalogo($deptId)
    {
        if ($deptId === 'LPZ') {
            return [
                (object) ['id' => 'MUN_LPZ', 'nombre' => 'LA PAZ'],
                (object) ['id' => 'MUN_EAL', 'nombre' => 'EL ALTO'],
            ];
        }

        return [];
    }

    public function getZonasCatalogo($munId)
    {
        if ($munId === 'MUN_LPZ') {
            return [
                (object) ['id' => 'ZON_SUR', 'nombre' => 'ZONA SUR (OBRAJES/CALACOTO/SAN MIGUEL)'],
                (object) ['id' => 'ZON_CEN', 'nombre' => 'CENTRO'],
                (object) ['id' => 'ZON_SOP', 'nombre' => 'SOPOCACHI'],
                (object) ['id' => 'ZON_MIR', 'nombre' => 'MIRAFLORES'],
                (object) ['id' => 'ZON_COT', 'nombre' => 'COTA COTA / CHASQUIPAMPA'],
            ];
        } elseif ($munId === 'MUN_EAL') {
            return [
                (object) ['id' => 'ZON_SAT', 'nombre' => 'CIUDAD SATÉLITE'],
                (object) ['id' => 'ZON_VAD', 'nombre' => 'VILLA ADELA'],
                (object) ['id' => 'ZON_16J', 'nombre' => '16 DE JULIO'],
                (object) ['id' => 'ZON_CEJ', 'nombre' => 'LA CEJA'],
            ];
        }

        return [];
    }

    public function getCallesCatalogo($zonaId)
    {
        switch ($zonaId) {
            case 'ZON_SUR':
                return [
                    (object) ['id' => 'CAL_BAL', 'nombre' => 'AV. BALLIVIÁN'],
                    (object) ['id' => 'CAL_HER', 'nombre' => 'AV. HERNANDO SILES'],
                    (object) ['id' => 'CAL_21', 'nombre' => 'CALLE 21 DE CALACOTO'],
                ];
            case 'ZON_CEN':
                return [
                    (object) ['id' => 'CAL_PRA', 'nombre' => 'AV. 16 DE JULIO (PRADO)'],
                    (object) ['id' => 'CAL_CAM', 'nombre' => 'CALLE CAMACHO'],
                    (object) ['id' => 'CAL_PST', 'nombre' => 'CALLE POTOSÍ'],
                ];
            case 'ZON_SOP':
                return [
                    (object) ['id' => 'CAL_ARC', 'nombre' => 'AV. ARCE'],
                    (object) ['id' => 'CAL_6AG', 'nombre' => 'AV. 6 DE AGOSTO'],
                    (object) ['id' => 'CAL_20O', 'nombre' => 'AV. 20 DE OCTUBRE'],
                ];
            case 'ZON_MIR':
                return [
                    (object) ['id' => 'CAL_BUS', 'nombre' => 'AV. BUSCH'],
                    (object) ['id' => 'CAL_SAA', 'nombre' => 'AV. SAAVEDRA'],
                ];
            case 'ZON_SAT':
                return [
                    (object) ['id' => 'CAL_SAT1', 'nombre' => 'PLAN 561'],
                    (object) ['id' => 'CAL_SAT2', 'nombre' => 'PLAN 482'],
                ];
            default:
                return [
                    (object) ['id' => 'CAL_PRI', 'nombre' => 'AV. PRINCIPAL'],
                    (object) ['id' => 'CAL_SEC', 'nombre' => 'CALLE SECUNDARIA'],
                ];
        }
    }
}
