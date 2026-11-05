<?php

namespace App\Modulos\Identidad\Presentacion\Livewire\Funcionalidades;

use App\Models\Area;
use App\Modulos\Identidad\Dominio\Reglas\IdentificacionInternacionalPersonal;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GuardarPersonalInstitucional;
use App\Modulos\Identidad\Dominio\Reglas\ClasificacionRolInstitucional;
use App\Modulos\Identidad\Dominio\Reglas\DocumentosPersonalInstitucional;
use App\Modulos\Identidad\Dominio\Reglas\DatosPersonalesTrabajador;
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

    #[Locked] public ?array $direccionMapa = null;

    public $esEdicion = false;
    public ?string $pais_documento = 'BO';
    public ?string $tipo_documento = 'CI';
    public ?string $pais_telefono = 'BO';
    #[Locked] public bool $otro_pais_documento = false;
    #[Locked] public bool $otro_pais_telefono = false;

    #[Locked]
    public array $camposValidados = [];

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

    #[Locked] public ?string $foto_guardada = null;

    public $observaciones;

    public array $documentos_con_plazo = [];
    public ?string $fecha_limite_documentacion = null;

    // Direcciones jerárquicas
    public ?string $pais_direccion = 'BO';

    public ?string $otro_departamento = null;

    public ?string $otro_municipio = null;

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
            $this->fecha_ingreso = today()->toDateString();
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
        $this->foto_guardada = $usuario->foto;
        $this->fecha_registro = $usuario->fecha_hora_alta?->toDateString();
        $this->hora_registro = $usuario->fecha_hora_alta?->format('H:i');
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

        // La dirección existente se conserva como texto, sin inferir sus componentes.
        $this->roles_seleccionados = $usuario->roles->pluck('name')->toArray();
        $this->rol_seleccionado = count($this->roles_seleccionados) > 0 ? $this->roles_seleccionados[0] : '';
        $this->matricula_prof = $personal?->matricula_profesional;
        $this->pais_documento = $personal?->pais_documento;
        $this->tipo_documento = $personal?->tipo_documento;
        $this->pais_telefono = $personal?->pais_telefono;
        $this->otro_pais_documento = $this->pais_documento !== null && ! isset(IdentificacionInternacionalPersonal::PAISES[$this->pais_documento]);
        $this->otro_pais_telefono = $this->pais_telefono !== null && ! isset(IdentificacionInternacionalPersonal::PAISES[$this->pais_telefono]);
        $prefijo = IdentificacionInternacionalPersonal::paisesTelefono()[$this->pais_telefono]['codigo'] ?? null;
        if ($prefijo && str_starts_with($this->telefono ?? '', $prefijo)) {
            $this->telefono = substr($this->telefono, strlen($prefijo));
        }
        foreach ($usuario->documentos->whereNotIn('estado', ['ANULADO', 'ANULADA', 'REEMPLAZADO']) as $documento) {
            $this->estado_documentos[$documento->tipo_documento] = $documento->estado;
        }

        $this->sincronizarClasificacionDesdeRoles(false);
    }

    protected function rules()
    {
        $internacional = IdentificacionInternacionalPersonal::reglas($this->pais_documento, $this->tipo_documento, $this->pais_telefono);
        return [
            ...$internacional,
            ...DatosPersonalesTrabajador::reglasNombre(),
            'correo' => 'required|email|max:120|unique:usuarios,correo,'.$this->usuarioId.',cod_usuario',
            'fecha_ingreso' => 'nullable|date_format:Y-m-d',
            'numero_documento' => [...$internacional['numero_documento'], Rule::unique('personal', 'numero_documento')->ignore($this->usuarioId ? Personal::where('cod_usuario', $this->usuarioId)->value('cod_personal') : null, 'cod_personal')],
            'estado' => 'required|string',
            'fecha_nacimiento' => DatosPersonalesTrabajador::reglaNacimiento(),
            'foto_perfil' => $this->reglaFotografia(),
            'roles_seleccionados' => 'required|array|min:1',
            'roles_seleccionados.*' => 'exists:roles,name',
            'tipo_personal' => 'required|in:salud,admin',
        ];
    }

    protected function messages()
    {
        return [
            'nombres.regex' => 'Usa solo letras, espacios, guiones o apóstrofos.',
            'ap_paterno.regex' => 'Usa solo letras, espacios, guiones o apóstrofos.',
            'ap_materno.regex' => 'Usa solo letras, espacios, guiones o apóstrofos.',
            'pais_documento.required' => 'Selecciona el país emisor del documento.',
            'pais_documento.in' => 'Selecciona un país disponible.',
            'tipo_documento.required' => 'Selecciona el tipo de documento.',
            'tipo_documento.in' => 'El tipo de documento no corresponde al país seleccionado.',
            'pais_telefono.required' => 'Selecciona el país del celular.',
            'pais_telefono.in' => 'Selecciona un país disponible.',
            'expedido.in' => 'Selecciona un departamento de expedición válido.',
            'expedido.prohibited' => 'La expedición departamental solo corresponde al CI de Bolivia.',
            'telefono.required' => 'El celular es obligatorio.',
            'telefono.regex' => $this->pais_telefono === 'BO' ? 'Escribe 8 dígitos, empezando por 5, 6 o 7.' : 'El celular no tiene el formato del país seleccionado.',
            'direccion.required' => 'Completa la ubicación y pulsa «Usar esta dirección».',
            'fecha_ingreso.date_format' => 'Introduce una fecha de ingreso válida.',
            'matricula_prof.max' => 'La matrícula admite hasta 50 caracteres.',
            'documentos_con_plazo.*.in'=>'Este documento no admite plazo para la función elegida.',
            'fecha_limite_documentacion.required'=>'Elige la fecha límite para los documentos solicitados.',
            'fecha_limite_documentacion.after_or_equal'=>'La fecha límite no puede estar vencida.',
            'contrasena_temporal.max' => 'La contraseña admite hasta 72 caracteres.',
            'archivos_temporales.*.file' => 'Selecciona un archivo válido.',
            'archivos_temporales.*.mimes' => 'Usa un archivo PDF, JPG, PNG o WebP.',
            'archivos_temporales.*.max' => 'El archivo no debe superar 5 MB.',
            'roles_seleccionados.size' => 'Selecciona una función institucional.',
            'contrasena_temporal.required' => 'La contraseña temporal es obligatoria.',
            'contrasena_temporal.min' => 'La contraseña debe tener al menos 12 caracteres.',
            'archivos_temporales.*.required' => 'Adjunte este documento requerido para el alta.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El formato del correo es inválido.',
            'correo.unique' => 'Este correo ya está registrado.',
            'nombres.required' => 'El nombre es obligatorio.',
            'ap_paterno.required_without' => 'Escribe al menos uno de los dos apellidos.',
            'ap_materno.required_without' => 'Escribe al menos uno de los dos apellidos.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.regex' => IdentificacionInternacionalPersonal::formato($this->pais_documento, $this->tipo_documento)['ayuda'],
            'numero_documento.unique' => 'Este documento ya está registrado.',
            'genero.required' => 'El género es obligatorio.',
            'expedido.required' => 'El departamento de expedición es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date_format' => 'Introduce una fecha válida con día, mes y año.',
            'fecha_nacimiento.before_or_equal' => 'El trabajador debe tener al menos 18 años.',
            'fecha_nacimiento.after_or_equal' => 'El trabajador no puede tener más de 60 años.',
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

    public function updatedFechaIngreso(): void
    {
        if (! $this->esEdicion && blank($this->fecha_ingreso)) {
            $this->fecha_ingreso = today()->toDateString();
            $this->validarCampoFormulario('fecha_ingreso');
        }
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
        $this->otro_departamento = null;
        $this->otro_municipio = null;

        $this->municipios_list = $value
            ? $this->getMunicipiosCatalogo($value)
            : [];
        $this->zonas_list = [];
        $this->calles_list = [];
        $this->limpiarValidacionUbicacion(['ciudad_id','municipio_id','zona_id','calle_id','otro_departamento','otro_municipio','otra_zona','otra_calle']);
        $this->validarCampoFormulario('ciudad_id');
    }

    public function updatedMunicipioId($value)
    {
        $this->zona_id = null;
        $this->calle_id = null;
        $this->otra_zona = null;
        $this->otra_calle = null;
        $this->otro_municipio = null;

        $this->zonas_list = $value
            ? $this->getZonasCatalogo($value)
            : [];
        $this->calles_list = [];
        $this->limpiarValidacionUbicacion(['municipio_id','zona_id','calle_id','otro_municipio','otra_zona','otra_calle']);
        $this->validarCampoFormulario('municipio_id');
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
        $this->limpiarValidacionUbicacion(['zona_id','calle_id','otra_zona','otra_calle']);
        $this->validarCampoFormulario('zona_id');
    }

    public function updatedCalleId($value)
    {
        if ($value !== 'OTRA') {
            $this->otra_calle = null;
        }
        $this->limpiarValidacionUbicacion(['calle_id','otra_calle']);
        $this->validarCampoFormulario('calle_id');
    }

    public function updatedPaisDireccion(): void
    {
        $this->ciudad_id = null;
        $this->municipio_id = $this->zona_id = $this->calle_id = null;
        $this->otro_departamento = $this->otro_municipio = $this->otra_zona = $this->otra_calle = null;
        $this->municipios_list = $this->zonas_list = $this->calles_list = [];
        $this->departamentos_list = $this->getDepartamentosCatalogo();
        $this->limpiarValidacionUbicacion(['pais_direccion','ciudad_id','municipio_id','zona_id','calle_id','otro_departamento','otro_municipio','otra_zona','otra_calle']);
        $this->validarCampoFormulario('pais_direccion');
    }

    private function reglasUbicacion(): array
    {
        return [
            'pais_direccion' => ['required', Rule::in(array_keys(IdentificacionInternacionalPersonal::paises()))],
            'ciudad_id' => ['required', Rule::in([...array_column($this->getDepartamentosCatalogo(), 'id'), 'OTRA'])],
            'municipio_id' => ['required', Rule::in([...array_column($this->getMunicipiosCatalogo($this->ciudad_id), 'id'), 'OTRA'])],
            'zona_id' => ['required', Rule::in([...array_column($this->getZonasCatalogo($this->municipio_id), 'id'), 'OTRA'])],
            'calle_id' => ['required', Rule::in([...array_column($this->getCallesCatalogo($this->zona_id), 'id'), 'OTRA'])],
            'otro_departamento' => ['nullable', 'required_if:ciudad_id,OTRA', 'string', 'max:60', 'not_regex:~[\r\n\x00-\x1F]~'],
            'otro_municipio' => ['nullable', 'required_if:municipio_id,OTRA', 'string', 'max:60', 'not_regex:~[\r\n\x00-\x1F]~'],
            'otra_zona' => ['nullable', 'required_if:zona_id,OTRA', 'string', 'max:60', 'not_regex:~[\r\n\x00-\x1F]~'],
            'otra_calle' => ['nullable', 'required_if:calle_id,OTRA', 'string', 'max:80', 'not_regex:~[\r\n\x00-\x1F]~'],
            'nro_casa' => ['nullable', 'string', 'max:15', 'not_regex:~[\r\n\x00-\x1F]~'],
        ];
    }

    private function limpiarValidacionUbicacion(array $campos): void
    {
        $this->resetValidation($campos);
        foreach ($campos as $campo) unset($this->camposValidados[$campo]);
    }

    protected function validationAttributes(): array
    {
        return ['pais_direccion'=>'país de residencia', 'ciudad_id'=>'departamento o región', 'municipio_id'=>'municipio o ciudad',
            'zona_id'=>'zona o barrio', 'calle_id'=>'calle o avenida', 'otro_departamento'=>'otro departamento o región',
            'otro_municipio'=>'otro municipio o ciudad', 'otra_zona'=>'otra zona o barrio', 'otra_calle'=>'otra calle o avenida', 'nro_casa'=>'número de vivienda'];
    }

    public function completarDireccion(): void
    {
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(), $this->usuarioId ? User::findOrFail($this->usuarioId) : null);
        foreach (['otro_departamento', 'otro_municipio', 'otra_zona', 'otra_calle', 'nro_casa'] as $campo) {
            if (is_string($this->{$campo})) $this->{$campo} = mb_strtoupper(trim($this->{$campo}));
        }
        $this->validate($this->reglasUbicacion());
        $nombre = fn ($lista, $id, $otro) => $id === 'OTRA' ? $otro : collect($lista)->firstWhere('id', $id)->nombre;
        $partes = [
            $nombre($this->getCallesCatalogo($this->zona_id), $this->calle_id, $this->otra_calle),
            filled($this->nro_casa) ? 'N.º '.$this->nro_casa : null,
            'ZONA '.$nombre($this->getZonasCatalogo($this->municipio_id), $this->zona_id, $this->otra_zona),
            $nombre($this->getMunicipiosCatalogo($this->ciudad_id), $this->municipio_id, $this->otro_municipio),
            $nombre($this->getDepartamentosCatalogo(), $this->ciudad_id, $this->otro_departamento),
            IdentificacionInternacionalPersonal::paises()[$this->pais_direccion]['nombre'],
        ];
        $direccion = mb_strtoupper(implode(', ', array_filter($partes)));
        \Illuminate\Support\Facades\Validator::make(['direccion'=>$direccion], ['direccion'=>['required', 'string', 'max:255']],
            ['direccion.max'=>'La dirección supera 255 caracteres. Abrevia sus componentes antes de aplicarla.'])->validate();
        $this->direccion = $direccion;
        $this->validarCampoFormulario('direccion');
        foreach (array_keys($this->reglasUbicacion()) as $campo) $this->camposValidados[$campo] = filled($this->{$campo});
        $this->dispatch('direccion-personal-completada');
    }

    public function updatedRolSeleccionado($value)
    {
        $this->roles_seleccionados = $value ? [$value] : [];
        $this->sincronizarClasificacionDesdeRoles(false);
        $permitidos = array_column($this->documentosConfigurados, 'id');
        foreach (array_keys($this->archivos_temporales) as $codigo) {
            if (! in_array($codigo, $permitidos, true)) $this->removerDocumento($codigo);
        }
        $this->documentos_con_plazo = [];
        $this->fecha_limite_documentacion = null;
        $this->resetValidation(['documentos_con_plazo', 'fecha_limite_documentacion']);
        unset($this->camposValidados['documentos_con_plazo'], $this->camposValidados['fecha_limite_documentacion']);
        $this->dispatch('documentos-personal-catalogo', codigos: $permitidos);
        $this->validarCampoFormulario('roles_seleccionados');
    }

    public function updatedDocumentosConPlazo(): void
    {
        if ($this->documentos_con_plazo && ! $this->fecha_limite_documentacion) {
            $this->fecha_limite_documentacion = today()->addDays(7)->toDateString();
            $this->resetValidation('fecha_limite_documentacion');
            unset($this->camposValidados['fecha_limite_documentacion']);
        }
        if (! $this->documentos_con_plazo) {
            $this->fecha_limite_documentacion = null;
            $this->resetValidation('fecha_limite_documentacion');
            unset($this->camposValidados['fecha_limite_documentacion']);
        }
        $this->validarCampoFormulario('documentos_con_plazo');
    }

    private function contextoMapaDireccion(): array
    {
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(), $this->usuarioId ? User::findOrFail($this->usuarioId) : null);
        $reglas = $this->reglasUbicacion();
        $this->validate(array_intersect_key($reglas, array_flip(['pais_direccion', 'ciudad_id', 'otro_departamento'])));
        $departamento = $this->ciudad_id === 'OTRA' ? $this->otro_departamento
            : collect($this->getDepartamentosCatalogo())->firstWhere('id', $this->ciudad_id)->nombre;
        $municipio = $this->municipio_id === 'OTRA' ? $this->otro_municipio
            : (collect($this->getMunicipiosCatalogo($this->ciudad_id))->firstWhere('id', $this->municipio_id)?->nombre);
        \Illuminate\Support\Facades\Validator::make(['departamento'=>$departamento, 'municipio'=>$municipio],
            ['departamento'=>['required','string','max:60','not_regex:~[\r\n\x00-\x1F]~'], 'municipio'=>['nullable','string','max:60','not_regex:~[\r\n\x00-\x1F]~']])->validate();
        return ['pais'=>$this->pais_direccion, 'nombrePais'=>IdentificacionInternacionalPersonal::paises()[$this->pais_direccion]['nombre'],
            'departamento'=>$departamento, 'municipio'=>$municipio];
    }

    public function centrarMapaDireccion(): array
    {
        $contexto = $this->contextoMapaDireccion();
        $this->direccionMapa = null;
        $this->resetValidation('mapa_direccion');
        return app(\App\Modulos\Identidad\Infraestructura\Geografia\GeocodificadorDireccionPersonal::class)->centrar(...$contexto);
    }

    public function consultarDireccionMapa($latitud, $longitud): array
    {
        $contexto = $this->contextoMapaDireccion();
        $this->direccionMapa = null;
        $this->resetValidation('mapa_direccion');
        $datos = \Illuminate\Support\Facades\Validator::make(['latitud'=>$latitud, 'longitud'=>$longitud],
            ['latitud'=>['required','numeric','between:-85,85'], 'longitud'=>['required','numeric','between:-180,180']])->validate();
        $resultado = app(\App\Modulos\Identidad\Infraestructura\Geografia\GeocodificadorDireccionPersonal::class)
            ->resolver((float) $datos['latitud'], (float) $datos['longitud'], $contexto['pais']);
        $this->direccionMapa = [...$resultado, 'contexto'=>$contexto];
        return $resultado;
    }

    public function usarDireccionMapa(string $direccion): void
    {
        $contexto = $this->contextoMapaDireccion();
        if (! $this->direccionMapa || $this->direccionMapa['contexto'] !== $contexto) {
            throw ValidationException::withMessages(['mapa_direccion'=>'Vuelve a consultar el punto después de cambiar el departamento o municipio.']);
        }
        $direccion = mb_strtoupper(trim($direccion));
        \Illuminate\Support\Facades\Validator::make(['direccion'=>$direccion],
            ['direccion'=>['required','string','min:5','max:255','not_regex:~[\r\n\x00-\x1F]~']])->validate();
        $datos = $this->direccionMapa;
        $buscar = fn ($lista, $nombre) => collect($lista)->first(fn ($fila) => Str::ascii(mb_strtoupper($fila->nombre)) === Str::ascii($nombre))?->id;
        if (filled($datos['departamento'])) {
            $this->ciudad_id = $buscar($this->getDepartamentosCatalogo(), $datos['departamento']) ?: 'OTRA';
            $this->otro_departamento = $this->ciudad_id === 'OTRA' ? $datos['departamento'] : null;
        }
        $this->municipio_id = $buscar($this->getMunicipiosCatalogo($this->ciudad_id), $datos['municipio']) ?: 'OTRA';
        $this->otro_municipio = $this->municipio_id === 'OTRA' ? $datos['municipio'] : null;
        $this->zona_id = $buscar($this->getZonasCatalogo($this->municipio_id), $datos['zona']) ?: 'OTRA';
        $this->otra_zona = $this->zona_id === 'OTRA' ? $datos['zona'] : null;
        $this->calle_id = $buscar($this->getCallesCatalogo($this->zona_id), $datos['calle']) ?: 'OTRA';
        $this->otra_calle = $this->calle_id === 'OTRA' ? $datos['calle'] : null;
        $this->nro_casa = $datos['numero'];
        $this->direccion = $direccion;
        $this->limpiarValidacionUbicacion(array_keys($this->reglasUbicacion()));
        $this->validarCampoFormulario('direccion');
        $this->direccionMapa = null;
        $this->resetValidation('mapa_direccion');
        $this->dispatch('direccion-personal-completada');
    }

    public function getGoogleMapsUrlProperty(): ?string
    {
        if (! filled($this->direccion)) return null;
        $url = 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($this->direccion);
        return strlen($url) <= 2048 ? $url : null;
    }

    public function getDocumentosPostergablesProperty(): array
    {
        return DocumentosPersonalInstitucional::postergables($this->rol_seleccionado ?: '');
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
        $documento = collect($this->documentosConfigurados)->firstWhere('id', $key);
        if (! $documento) throw ValidationException::withMessages(['archivos_temporales'=>'El documento no corresponde a esta función institucional.']);
        unset($this->camposValidados["archivos_temporales.$key"]);
        $this->validateOnly("archivos_temporales.$key", [
            "archivos_temporales.$key" => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ], [
            "archivos_temporales.$key.mimes" => 'El archivo debe ser PDF, JPG, PNG o WEBP.',
            "archivos_temporales.$key.max" => 'El archivo no debe exceder los 5MB.',
        ]);

        if (! filled($value)) return;
        $this->documentos_con_plazo = array_values(array_diff($this->documentos_con_plazo, [$key]));
        if (! $this->documentos_con_plazo) {
            $this->fecha_limite_documentacion = null;
            $this->resetValidation('fecha_limite_documentacion');
            unset($this->camposValidados['fecha_limite_documentacion']);
        }
        $esInstitucional = $documento['tipo'] === 'institucional';
        $this->camposValidados['archivos_temporales.'.$key] = true;
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
        unset($this->archivos_temporales[$key], $this->camposValidados['archivos_temporales.'.$key]);
        $this->resetValidation('archivos_temporales.'.$key);
        $this->estado_documentos[$key] = 'PENDIENTE';
        unset($this->observacion_documentos[$key]);
    }

    public function updatedFotoPerfil(): void
    {
        unset($this->camposValidados['foto_perfil']);
        $this->validateOnly('foto_perfil');
        $this->camposValidados['foto_perfil'] = filled($this->foto_perfil);
    }

    public function validarPasoActual(): void
    {
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(), $this->usuarioId ? User::findOrFail($this->usuarioId) : null, $this->roles_seleccionados);
        $this->validarReglasFormulario($this->reglasPaso($this->pasoActual));
    }

    private function validarReglasFormulario(array $reglas): void
    {
        $this->validate($reglas);
        foreach (array_keys($reglas) as $campo) {
            if (! str_contains($campo, '*') && $campo !== 'archivos_temporales') {
                $this->camposValidados[$campo] = filled(data_get($this, $campo));
            }
        }
    }

    private function validarTodosLosPasos(): void
    {
        foreach (range(1, 4) as $paso) {
            try {
                $this->validarReglasFormulario($this->reglasPaso($paso));
            } catch (ValidationException $error) {
                $this->pasoActual = $paso;
                $this->dispatch('personal-validacion-fallida');
                throw $error;
            }
        }
    }

    private function reglasPaso(int $paso): array
    {
        $internacional = IdentificacionInternacionalPersonal::reglas($this->pais_documento, $this->tipo_documento, $this->pais_telefono);
        return match ($paso) {
            1 => [...$internacional,'foto_perfil'=>$this->reglaFotografia(),...DatosPersonalesTrabajador::reglasNombre(),
                'numero_documento'=>[...$internacional['numero_documento'],Rule::unique('personal','numero_documento')->ignore($this->usuarioId ? Personal::where('cod_usuario',$this->usuarioId)->value('cod_personal') : null,'cod_personal')],
                'genero'=>'required|in:M,F','fecha_nacimiento'=>DatosPersonalesTrabajador::reglaNacimiento(),
                'direccion'=>'required|string|max:255'],
            2 => ['roles_seleccionados'=>'required|array|'.($this->esEdicion ? 'min:1' : 'size:1'),'roles_seleccionados.*'=>'exists:roles,name','fecha_ingreso'=>'nullable|date_format:Y-m-d','observaciones'=>'nullable|string'],
            3 => ['correo'=>['required','email','max:120',Rule::unique('usuarios','correo')->ignore($this->usuarioId,'cod_usuario')],
                'contrasena_temporal'=>($this->esEdicion ? 'nullable' : 'required').'|string|min:12|max:72'],
            4 => $this->reglasDocumentos(),
            default => ['pasoActual'=>'in:1,2,3,4'],
        };
    }

    private function reglaFotografia(): array
    {
        if ($this->esEdicion && filled($this->foto_guardada) && $this->foto_perfil === $this->foto_guardada) {
            return ['nullable', 'string', Rule::in([$this->foto_guardada])];
        }
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
    }

    private function reglasDocumentos(): array
    {
        $reglas = ['archivos_temporales' => 'array', 'matricula_prof'=>'nullable|string|max:50',
            'documentos_con_plazo'=>['array', 'max:'.count($this->documentosPostergables)],
            'documentos_con_plazo.*'=>['string', 'distinct', Rule::in(array_column($this->documentosPostergables, 'id'))],
            'fecha_limite_documentacion'=>[$this->documentos_con_plazo ? 'required' : 'nullable', 'date_format:Y-m-d', 'after_or_equal:today']];
        foreach ($this->documentosConfigurados as $documento) {
            if ($documento['tipo'] === 'ingresante') {
                $requerido = ! $this->esEdicion && $documento['obligatorio_inmediato'];
                $reglas['archivos_temporales.'.$documento['id']] = ($requerido ? 'required' : 'nullable').'|file|mimes:pdf,jpg,jpeg,png,webp|max:5120';
            }
        }
        return $reglas;
    }

    public function updatedPaisDocumento(): void
    {
        $this->tipo_documento = IdentificacionInternacionalPersonal::paises()[$this->pais_documento]['doc'] ?? null;
        $this->expedido = null;
        $this->resetValidation(['pais_documento', 'tipo_documento', 'numero_documento', 'expedido']);
        foreach (['tipo_documento', 'numero_documento', 'expedido'] as $campo) unset($this->camposValidados[$campo]);
        if (filled($this->numero_documento)) $this->validarCampoFormulario('numero_documento');
    }

    public function updatedTipoDocumento(): void
    {
        if ($this->pais_documento !== 'BO' || $this->tipo_documento !== 'CI') $this->expedido = null;
        $this->resetValidation(['numero_documento', 'expedido']);
        unset($this->camposValidados['numero_documento'], $this->camposValidados['expedido']);
        if (filled($this->numero_documento)) $this->validarCampoFormulario('numero_documento');
    }

    public function updatedPaisTelefono(): void
    {
        $this->resetValidation('telefono');
        unset($this->camposValidados['telefono']);
        if (filled($this->telefono)) $this->validarCampoFormulario('telefono');
    }

    public function activarOtroPais(string $ambito): void
    {
        app(AutorizarGestionPersonal::class)->autorizar(auth()->user(), $this->usuarioId ? User::findOrFail($this->usuarioId) : null);
        abort_unless(in_array($ambito, ['documento','telefono'], true), 422);
        $modo = 'otro_pais_'.$ambito;
        $this->{$modo} = ! $this->{$modo};
        $campo = 'pais_'.$ambito;
        $this->{$campo} = null;
        $this->resetValidation($campo);
        unset($this->camposValidados[$campo]);
        if ($ambito === 'documento') {
            $this->tipo_documento = null;
            $this->expedido = null;
            $this->resetValidation(['tipo_documento','expedido','numero_documento']);
            unset($this->camposValidados['tipo_documento'], $this->camposValidados['expedido'], $this->camposValidados['numero_documento']);
        } else {
            $this->resetValidation('telefono');
            unset($this->camposValidados['telefono']);
        }
    }

    public function getTodosPaisesProperty(): array { return IdentificacionInternacionalPersonal::paises(); }
    public function getTodosPaisesTelefonoProperty(): array { return IdentificacionInternacionalPersonal::paisesTelefono(); }

    public function getPaisesProperty(): array { return IdentificacionInternacionalPersonal::PAISES; }
    public function getTiposDocumentoProperty(): array { return IdentificacionInternacionalPersonal::tipos($this->pais_documento); }
    public function getFormatoDocumentoProperty(): array { return IdentificacionInternacionalPersonal::formato($this->pais_documento, $this->tipo_documento); }

    public function updated(string $campo): void
    {
        if (in_array($campo, DatosPersonalesTrabajador::MAYUSCULAS, true) && is_string($this->{$campo})) {
            $this->{$campo} = mb_strtoupper(trim($this->{$campo}));
        }
        if ($campo === 'correo') {
            $this->correo = trim(mb_strtolower($this->correo ?? ''));
        }
        if (! in_array($campo, ['rol_seleccionado', 'foto_perfil', 'pais_direccion', 'ciudad_id', 'municipio_id', 'zona_id', 'calle_id'], true) && ! str_starts_with($campo, 'archivos_temporales.')) {
            $this->validarCampoFormulario($campo);
        }
    }

    private function validarCampoFormulario(string $campo): void
    {
        $ubicacion = $this->reglasUbicacion();
        if (array_key_exists($campo, $ubicacion)) {
            unset($this->camposValidados[$campo]);
            $this->validateOnly($campo, $ubicacion);
            $this->camposValidados[$campo] = filled($this->{$campo});
            return;
        }
        if (in_array($campo, ['ap_paterno', 'ap_materno'], true)) {
            unset($this->camposValidados['ap_paterno'], $this->camposValidados['ap_materno']);
            $reglas = array_intersect_key(DatosPersonalesTrabajador::reglasNombre(), array_flip(['ap_paterno', 'ap_materno']));
            foreach (['ap_paterno', 'ap_materno'] as $apellido) {
                $this->validateOnly($apellido, $reglas);
            }
            $this->camposValidados['ap_paterno'] = filled($this->ap_paterno);
            $this->camposValidados['ap_materno'] = filled($this->ap_materno);
            return;
        }
        for ($paso = 1; $paso <= 4; $paso++) {
            $reglas = $this->reglasPaso($paso);
            if (! array_key_exists($campo, $reglas)) {
                continue;
            }
            unset($this->camposValidados[$campo]);
            $this->validateOnly($campo, $reglas);
            $this->camposValidados[$campo] = filled(data_get($this, $campo));
            return;
        }
    }

    public function avanzarPaso(): void
    {
        try {
            $this->validarPasoActual();

            if ($this->pasoActual < $this->totalPasos) {
                $this->pasoActual++;
            }
        } catch (ValidationException $e) {
            $this->dispatch('personal-validacion-fallida');
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
        $this->sincronizarClasificacionDesdeRoles(false);

        $this->validarTodosLosPasos();

        if (! $this->esEdicion) {
            $this->estado = 'ACTIVO';
        }
        $estadoDocumental = $this->determinarEstadoDocumental();

        try {
            $resultado = app(GuardarPersonalInstitucional::class)->guardar(auth()->user(), ['esEdicion' => $this->esEdicion, 'usuarioId' => $this->usuarioId, 'contrasena_temporal' => $this->contrasena_temporal, 'forzar_cambio_password' => $this->forzar_cambio_password, 'fecha_registro' => $this->fecha_registro, 'hora_registro' => $this->hora_registro, 'fecha_ingreso' => $this->fecha_ingreso, 'nombres' => $this->nombres, 'ap_paterno' => $this->ap_paterno, 'ap_materno' => $this->ap_materno, 'correo' => $this->correo, 'telefono' => $this->telefono, 'pais_documento' => $this->pais_documento, 'tipo_documento' => $this->tipo_documento, 'pais_telefono' => $this->pais_telefono, 'numero_documento' => $this->numero_documento, 'expedido' => $this->expedido, 'fecha_nacimiento' => $this->fecha_nacimiento, 'genero' => $this->genero, 'estado' => $this->estado, 'observaciones' => $this->observaciones, 'direccion' => $this->direccion, 'zona' => $this->zona, 'ciudad' => $this->ciudad, 'cod_area' => $this->cod_area, 'foto_perfil' => $this->foto_perfil, 'roles_seleccionados' => $this->roles_seleccionados, 'documentos_configurados' => $this->documentos_configurados, 'estado_documentos' => $this->estado_documentos, 'archivos_temporales' => $this->archivos_temporales, 'observacion_documentos' => $this->observacion_documentos, 'tempId' => $this->tempId, 'institucion_formacion' => $this->institucion_formacion, 'matricula_prof' => $this->matricula_prof, 'anios_exp' => $this->anios_exp, 'subtipo_enfermeria' => $this->subtipo_enfermeria, 'nivel_responsabilidad' => $this->nivel_responsabilidad, 'documentos_con_plazo'=>$this->documentos_con_plazo, 'fecha_limite_documentacion'=>$this->fecha_limite_documentacion]);
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
            $this->dispatch('personal-validacion-fallida');
            foreach (range(1, 4) as $paso) {
                if (array_intersect(array_keys($e->errors()), array_keys($this->reglasPaso($paso)))) {
                    $this->pasoActual = $paso;
                    break;
                }
            }
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
        if ($this->pais_direccion !== 'BO') return [];
        return [
            (object) ['id' => 'LPZ', 'nombre' => 'LA PAZ'],
            (object) ['id' => 'CHQ', 'nombre' => 'CHUQUISACA'],
            (object) ['id' => 'CBB', 'nombre' => 'COCHABAMBA'],
            (object) ['id' => 'ORU', 'nombre' => 'ORURO'],
            (object) ['id' => 'PTS', 'nombre' => 'POTOSÍ'],
            (object) ['id' => 'TJA', 'nombre' => 'TARIJA'],
            (object) ['id' => 'SCZ', 'nombre' => 'SANTA CRUZ'],
            (object) ['id' => 'BEN', 'nombre' => 'BENI'],
            (object) ['id' => 'PND', 'nombre' => 'PANDO'],
        ];
    }

    public function getMunicipiosCatalogo($deptId)
    {
        if ($this->pais_direccion === 'BO' && $deptId === 'LPZ') {
            return [
                (object) ['id' => 'MUN_LPZ', 'nombre' => 'LA PAZ'],
                (object) ['id' => 'MUN_EAL', 'nombre' => 'EL ALTO'],
            ];
        }

        return [];
    }

    public function getZonasCatalogo($munId)
    {
        if (! in_array($munId, array_column($this->getMunicipiosCatalogo($this->ciudad_id), 'id'), true)) return [];
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
        if (! in_array($zonaId, array_column($this->getZonasCatalogo($this->municipio_id), 'id'), true)) return [];
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
                return [];
        }
    }
}
