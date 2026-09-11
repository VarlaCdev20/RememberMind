<?php

namespace App\Livewire\Identidad;

use App\Models\User;
use App\Models\AreaInstitucional;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public $pais_documento = 'BOLIVIA';
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
    public $referencia_domicilio;
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

    // Catalogos internos para domicilio hasta contar con tablas territoriales.
    public array $catalogDepartamentos = [
        'LA PAZ' => ['LA PAZ', 'EL ALTO', 'OTRO'],
        'SANTA CRUZ' => ['SANTA CRUZ DE LA SIERRA', 'MONTERO', 'WARNES', 'LA GUARDIA', 'EL TORNO', 'OTRO'],
        'COCHABAMBA' => ['COCHABAMBA', 'SACABA', 'QUILLACOLLO', 'COLCAPIRHUA', 'TIRAQUE', 'OTRO'],
        'ORURO' => ['ORURO', 'CHALLAPATA', 'HUANUNI', 'OTRO'],
        'POTOSI' => ['POTOSI', 'TUPIZA', 'VILLAZON', 'UYUNI', 'OTRO'],
        'CHUQUISACA' => ['SUCRE', 'MONTEAGUDO', 'YOTALA', 'OTRO'],
        'TARIJA' => ['TARIJA', 'YACUIBA', 'BERMEJO', 'VILLAMONTES', 'OTRO'],
        'BENI' => ['TRINIDAD', 'RIBERALTA', 'GUAYARAMERIN', 'OTRO'],
        'PANDO' => ['COBIJA', 'SENA', 'PORVENIR', 'OTRO'],
        'OTRO' => ['OTRO'],
    ];

    public array $catalogZonas = [
        'LA PAZ' => ['CENTRO', 'SOPOCACHI', 'MIRAFLORES', 'SAN PEDRO', 'OBRAJES', 'CALACOTO', 'IRPAVI', 'ACHUMANI', 'VILLA FÁTIMA', 'VILLA COPACABANA', 'MAX PAREDES', 'COTAHUMA', 'MUNAYPATA', 'PAMPAHASI', 'ALTO OBRAJES', 'OTRO'],
        'EL ALTO' => ['CIUDAD SATÉLITE', 'VILLA DOLORES', '16 DE JULIO', 'RÍO SECO', 'VILLA ADELA', 'SENKATA', 'CEJA', 'LOS ANDES', 'OTRO'],
        'COCHABAMBA' => ['CALA CALA', 'QUERU QUERU', 'SARCO', 'LAS CUADRAS', 'CENTRO', 'OTRO'],
        'SANTA CRUZ DE LA SIERRA' => ['EQUIPETROL', 'URBARI', 'LAS PALMAS', 'CENTRO', 'VILLA PRIMERO DE MAYO', 'PLAN TRES MIL', 'OTRO'],
    ];

    public $departamento_domicilio = 'LA PAZ';
    public $municipio_domicilio = 'LA PAZ';
    public $zona_domicilio = '';
    public $otro_departamento = '';
    public $otro_municipio = '';
    public $otra_zona = '';

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
    public $selected_parentesco = 'HIJO/A';
    public $selected_es_responsable = false;
    public $selected_responsable_salud = false;
    public $selected_responsable_economico = false;
    public $selected_observaciones = '';
    public $busquedaAdultoMayor = '';
    public array $adultosMayoresFiltrados = [];
    public bool $mostrarDropdownAdultosMayores = false;
    public $adultoMayorSeleccionadoLabel = '';

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

    // ── Navegación Detalle Interno ──
    public string $modoVista = 'listado'; // 'listado' o 'detalle'
    public $usuarioDetalle = null;
    public string $seccionActivaDetalle = 'resumen';

    // ── Post-Registro Flow ──
    public bool $mostrarPostRegistro = false;
    public $usuarioPostRegistro = null;
    public array $documentosRequeridos = [];
    public $fechaLimiteDocumentacion = null;
    public $passwordTemporalPostRegistro = null;
    public bool $correoRequisitosEnviado = false;
    public ?string $mensajeCorreoRequisitos = null;

    // ── Document Upload Properties ──
    public $archivoTemporal;
    public $tipoDocSeleccionado;
    public $tipoDocNombre;
    public $fechaEmisionDoc;
    public $fechaVencimientoDoc;
    public $observacionesDoc;
    public bool $mostrarModalSubirDoc = false;

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
        $paisKey = collect(array_keys($this->paisesConfig))
            ->first(fn ($pais) => mb_strtoupper($pais, 'UTF-8') === mb_strtoupper($val, 'UTF-8'));

        if ($paisKey && isset($this->paisesConfig[$paisKey])) {
            $this->tipo_documento = $this->normalizarMayusculas($this->paisesConfig[$paisKey]['doc']);
            $this->pais_telefono = $paisKey;
            $this->codigo_telefono = $this->paisesConfig[$paisKey]['codigo'];
        }
        if (mb_strtoupper($val, 'UTF-8') !== 'BOLIVIA') {
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
        // Limpieza por rol: resetear campos específicos del rol anterior para no dejar datos huérfanos
        $this->especialidad_salud = null;
        $this->matricula_prof = null;
        $this->institucion_formacion = null;
        $this->cargo_administrativo = null;
        $this->disponibilidad_inicial = null;
        $this->area_apoyo_preferente = null;
        $this->observacion_vinculo = null;
        $this->vinculosFamiliar = [];

        if ($val === 'VOLUNTARIO') {
            $this->cod_area = 'ARE_0008'; // Voluntariado y Relaciones Institucionales
            $this->fecha_ingreso = now()->format('Y-m-d');
        } elseif ($val === 'FAMILIAR') {
            $this->cod_area = null;
            $this->fecha_ingreso = null;
        } elseif (in_array($val, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
            $this->cod_area = 'ARE_0004'; // Sugerencia inicial
            $this->fecha_ingreso = now()->format('Y-m-d');
        } elseif (in_array($val, ['SUPERADMINISTRADOR', 'ADMINISTRADOR'])) {
            $this->cod_area = 'ARE_0003'; // Sugerencia inicial
            $this->fecha_ingreso = now()->format('Y-m-d');
        }

        $this->limpiarCamposDependientesDelRol();
    }

    public function updatedDepartamentoDomicilio($val)
    {
        $this->municipio_domicilio = $val === 'OTRO' ? 'OTRO' : '';
        $this->otro_municipio = '';
        $this->zona_domicilio = '';
        $this->otra_zona = '';
    }

    public function updatedMunicipioDomicilio($val)
    {
        $this->zona_domicilio = '';
        $this->otra_zona = '';
    }

    private function resolverDepartamentoDomicilio(): ?string
    {
        return $this->departamento_domicilio === 'OTRO'
            ? $this->normalizarMayusculas($this->otro_departamento)
            : $this->normalizarMayusculas($this->departamento_domicilio);
    }

    private function resolverMunicipioDomicilio(): ?string
    {
        return $this->municipio_domicilio === 'OTRO'
            ? $this->normalizarMayusculas($this->otro_municipio)
            : $this->normalizarMayusculas($this->municipio_domicilio);
    }

    private function resolverZonaDomicilio(): ?string
    {
        if (isset($this->catalogZonas[$this->municipio_domicilio]) && $this->zona_domicilio !== 'OTRO') {
            return $this->normalizarMayusculas($this->zona_domicilio);
        }

        return $this->normalizarMayusculas($this->otra_zona);
    }

    private function armarDireccionCompleta(): ?string
    {
        $departamento = $this->resolverDepartamentoDomicilio();
        $municipio = $this->resolverMunicipioDomicilio();
        $zona = $this->resolverZonaDomicilio();

        $this->ciudad = $municipio;
        $this->zona = $zona;

        $partes = [];
        if (!empty($departamento)) {
            $partes[] = "DEPARTAMENTO: {$departamento}";
        }
        if (!empty($municipio)) {
            $partes[] = "MUNICIPIO: {$municipio}";
        }
        if (!empty($zona)) {
            $partes[] = "ZONA: {$zona}";
        }
        if (!empty($this->calle)) {
            $partes[] = "CALLE/AV.: {$this->calle}";
        }
        if (!empty($this->nro_domicilio)) {
            $partes[] = "NRO.: {$this->nro_domicilio}";
        }
        if (!empty($this->referencia_domicilio)) {
            $partes[] = "REF.: {$this->referencia_domicilio}";
        }

        $this->direccion = $partes ? implode('; ', $partes) : $this->normalizarMayusculas($this->direccion);

        return $this->direccion;
    }

    private function reglasDireccion(): array
    {
        $rules = [
            'departamento_domicilio' => ['required', 'string'],
            'municipio_domicilio' => ['required', 'string'],
            'calle' => ['required', 'string', 'min:2', 'max:150'],
            'nro_domicilio' => ['required', 'string', 'max:20', 'regex:/^(S\/N|[0-9A-Z\-\/]+)$/u'],
            'referencia_domicilio' => ['nullable', 'string', 'max:255'],
        ];

        $rules['otro_departamento'] = $this->departamento_domicilio === 'OTRO'
            ? ['required', 'string', 'min:2', 'max:100']
            : ['nullable', 'string', 'max:100'];

        $rules['otro_municipio'] = $this->municipio_domicilio === 'OTRO'
            ? ['required', 'string', 'min:2', 'max:100']
            : ['nullable', 'string', 'max:100'];

        if (empty($this->municipio_domicilio)) {
            $rules['zona_domicilio'] = ['required', 'string'];
            $rules['otra_zona'] = ['nullable', 'string', 'max:100'];
        } elseif (isset($this->catalogZonas[$this->municipio_domicilio])) {
            $rules['zona_domicilio'] = ['required', 'string'];
            $rules['otra_zona'] = $this->zona_domicilio === 'OTRO'
                ? ['required', 'string', 'min:2', 'max:100']
                : ['nullable', 'string', 'max:100'];
        } else {
            $rules['zona_domicilio'] = ['nullable', 'string'];
            $rules['otra_zona'] = ['required', 'string', 'min:2', 'max:100'];
        }

        return $rules;
    }

    private function mensajesDireccion(): array
    {
        return [
            'departamento_domicilio.required' => 'Seleccione el departamento.',
            'municipio_domicilio.required' => 'Seleccione el municipio o ciudad.',
            'zona_domicilio.required' => 'Seleccione la zona o barrio.',
            'otro_departamento.required' => 'Especifique el departamento.',
            'otro_municipio.required' => 'Especifique el municipio o ciudad.',
            'otra_zona.required' => 'Especifique la zona o barrio.',
            'calle.required' => 'Ingrese la calle o avenida.',
            'calle.min' => 'La calle o avenida debe tener al menos 2 caracteres.',
            'nro_domicilio.required' => 'Ingrese el número de domicilio o S/N.',
            'nro_domicilio.regex' => 'Ingrese un número de domicilio válido o S/N.',
        ];
    }

    /**
     * Método preparado para buscar un familiar existente por documento o correo
     * y permitir su reutilización en futuros flujos sin duplicar el registro.
     */
    public function buscarFamiliarExistente($numeroDocumento, $correo)
    {
        return User::where('correo', $correo)
            ->orWhere('numero_documento', $numeroDocumento)
            ->whereHas('roles', function($q) {
                $q->where('name', 'FAMILIAR');
            })->first();
    }

    private function normalizarDatosFormulario()
    {
        $this->nombres = $this->normalizarMayusculas($this->nombres);
        $this->ap_paterno = $this->normalizarMayusculas($this->ap_paterno);
        $this->ap_materno = $this->normalizarMayusculas($this->ap_materno);
        $this->genero = $this->normalizarMayusculas($this->genero);
        $this->pais_documento = $this->normalizarMayusculas($this->pais_documento);
        $this->tipo_documento = $this->normalizarMayusculas($this->tipo_documento);
        $this->numero_documento = $this->normalizarMayusculas($this->numero_documento);
        $this->expedido = $this->normalizarMayusculas($this->expedido);
        $this->departamento_domicilio = $this->normalizarMayusculas($this->departamento_domicilio);
        $this->municipio_domicilio = $this->normalizarMayusculas($this->municipio_domicilio);
        $this->zona_domicilio = $this->normalizarMayusculas($this->zona_domicilio);
        $this->otro_departamento = $this->normalizarMayusculas($this->otro_departamento);
        $this->otro_municipio = $this->normalizarMayusculas($this->otro_municipio);
        $this->otra_zona = $this->normalizarMayusculas($this->otra_zona);
        $this->calle = $this->normalizarMayusculas($this->calle);
        $this->nro_domicilio = $this->normalizarMayusculas($this->nro_domicilio);
        
        $this->contacto_emergencia = $this->normalizarMayusculas($this->contacto_emergencia);
        $this->ap_paterno_emergencia = $this->normalizarMayusculas($this->ap_paterno_emergencia);
        $this->ap_materno_emergencia = $this->normalizarMayusculas($this->ap_materno_emergencia);
        $this->parentesco_emergencia = $this->normalizarMayusculas($this->parentesco_emergencia);
        $this->institucion_formacion = $this->normalizarMayusculas($this->institucion_formacion);
        $this->disponibilidad_inicial = $this->normalizarMayusculas($this->disponibilidad_inicial);
        $this->area_apoyo_preferente = $this->normalizarMayusculas($this->area_apoyo_preferente);
        $this->observacion_vinculo = $this->normalizarMayusculas($this->observacion_vinculo);
        $this->observaciones = $this->normalizarMayusculas($this->observaciones);
        $this->referencia_domicilio = $this->normalizarMayusculas($this->referencia_domicilio);
        $this->direccion = $this->normalizarMayusculas($this->direccion);
        $this->armarDireccionCompleta();
        $this->selected_parentesco = $this->normalizarMayusculas($this->selected_parentesco);
        $this->selected_observaciones = $this->normalizarMayusculas($this->selected_observaciones);

        foreach ($this->vinculosFamiliar as $index => $vinculo) {
            $this->vinculosFamiliar[$index]['parentesco_vinculo'] = $this->normalizarMayusculas($vinculo['parentesco_vinculo'] ?? null);
            $this->vinculosFamiliar[$index]['observaciones'] = $this->normalizarMayusculas($vinculo['observaciones'] ?? null);
        }
        
        $this->correo = $this->correo ? mb_strtolower(trim($this->correo), 'UTF-8') : null;
        
        if ($this->telefono) {
            $this->telefono = preg_replace('/\D+/', '', $this->telefono);
        }
        if ($this->celular_emergencia) {
            $this->celular_emergencia = preg_replace('/\D+/', '', $this->celular_emergencia);
        }

        $this->limpiarCamposDependientesDelRol();
    }

    private function limpiarCamposDependientesDelRol(): void
    {
        if (in_array($this->rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR'])) {
            $this->especialidad_salud = null;
            $this->matricula_prof = null;
            $this->institucion_formacion = null;
            $this->disponibilidad_inicial = null;
            $this->area_apoyo_preferente = null;
            $this->observacion_vinculo = null;
            $this->vinculosFamiliar = [];
            return;
        }

        if (in_array($this->rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
            $this->cargo_administrativo = null;
            $this->matricula_prof = null;
            $this->disponibilidad_inicial = null;
            $this->area_apoyo_preferente = null;
            $this->observacion_vinculo = null;
            $this->vinculosFamiliar = [];
            return;
        }

        if ($this->rol === 'VOLUNTARIO') {
            $this->cargo_administrativo = null;
            $this->especialidad_salud = null;
            $this->matricula_prof = null;
            $this->institucion_formacion = null;
            $this->observacion_vinculo = null;
            $this->vinculosFamiliar = [];
            return;
        }

        if ($this->rol === 'FAMILIAR') {
            $this->cod_area = null;
            $this->cargo_administrativo = null;
            $this->especialidad_salud = null;
            $this->matricula_prof = null;
            $this->institucion_formacion = null;
            $this->disponibilidad_inicial = null;
            $this->area_apoyo_preferente = null;
            $this->fecha_ingreso = null;
        }
    }

    private function guardarVinculosFamiliar(\App\Models\Familiar $fam): void
    {
        foreach ($this->vinculosFamiliar as $vinculo) {
            $obsSerialized = "Salud: " . ($vinculo['responsable_salud'] ?? 'NO')
                . " | Económico: " . ($vinculo['responsable_economico'] ?? 'NO')
                . " | Obs: " . ($vinculo['observaciones'] ?? '');

            $relacion = \App\Models\FamiliarAdulto::withTrashed()->updateOrCreate(
                [
                    'cod_fam' => $fam->cod_fam,
                    'cod_am' => $vinculo['cod_am'],
                ],
                [
                    'parentesco_vinculo' => $vinculo['parentesco_vinculo'],
                    'es_responsable' => ($vinculo['es_responsable'] ?? 'NO') === 'SI',
                    'estado' => 'ACTIVO',
                    'observaciones' => $obsSerialized,
                ]
            );

            if ($relacion->trashed()) {
                $relacion->restore();
            }
        }
    }

    private function adultoMayorLabel(\App\Models\AdultoMayor $adultoMayor): string
    {
        $nombre = trim("{$adultoMayor->nombres} {$adultoMayor->ap_paterno} {$adultoMayor->ap_materno}");
        $documento = $adultoMayor->ci
            ? trim(($adultoMayor->ci ?? '') . ' ' . ($adultoMayor->expedicion_ci ?? ''))
            : 'SIN DOCUMENTO REGISTRADO';
        $edad = $adultoMayor->fecha_nac ? ' — ' . \Carbon\Carbon::parse($adultoMayor->fecha_nac)->age . ' AÑOS' : '';

        return $this->normalizarMayusculas("{$nombre} — {$documento}{$edad}");
    }

    public function obtenerEtiquetaAdultoMayor($adultoMayor): string
    {
        if (!$adultoMayor) {
            return '';
        }

        $nombre = trim("{$adultoMayor->nombres} {$adultoMayor->ap_paterno} {$adultoMayor->ap_materno}");
        $documento = $adultoMayor->ci
            ? 'CI ' . trim(($adultoMayor->ci ?? '') . ' ' . ($adultoMayor->expedicion_ci ?? ''))
            : 'SIN DOCUMENTO REGISTRADO';
        $edad = $adultoMayor->fecha_nac ? ' - ' . \Carbon\Carbon::parse($adultoMayor->fecha_nac)->age . ' AÑOS' : '';

        return $this->normalizarMayusculas("{$nombre} - {$documento}{$edad}");
    }

    private function filtrarAdultosMayoresActivos(?string $criterio): array
    {
        $criterio = trim((string) $criterio);
        if ($criterio === '') {
            return [];
        }

        $termino = '%' . $criterio . '%';

        return \App\Models\AdultoMayor::query()
            ->where('cod_est_adul', 1)
            ->where(function ($q) use ($termino) {
                $q->whereLike('nombres', $termino)
                    ->orWhereLike('ap_paterno', $termino)
                    ->orWhereLike('ap_materno', $termino)
                    ->orWhereLike('ci', $termino)
                    ->orWhereRaw("CONCAT_WS(' ', nombres, ap_paterno, ap_materno) ILIKE ?", [$termino]);
            })
            ->orderBy('ap_paterno')
            ->orderBy('ap_materno')
            ->orderBy('nombres')
            ->limit(8)
            ->get()
            ->map(fn ($adultoMayor) => [
                'cod_am' => $adultoMayor->cod_am,
                'label' => $this->obtenerEtiquetaAdultoMayor($adultoMayor),
            ])
            ->values()
            ->all();
    }

    public function updatedBusquedaAdultoMayor($value): void
    {
        $texto = trim((string) $value);

        if ($this->selected_cod_am && $texto !== $this->adultoMayorSeleccionadoLabel) {
            $this->selected_cod_am = '';
            $this->adultoMayorSeleccionadoLabel = '';
        }

        $this->adultosMayoresFiltrados = $this->filtrarAdultosMayoresActivos($texto);
        $this->mostrarDropdownAdultosMayores = $texto !== '';
    }

    public function seleccionarAdultoMayor($codAm): void
    {
        $adultoMayor = \App\Models\AdultoMayor::where('cod_est_adul', 1)->where('cod_am', $codAm)->first();

        if (!$adultoMayor) {
            $this->addError('selected_cod_am', 'Seleccione un adulto mayor válido de la lista.');
            $this->limpiarSeleccionAdultoMayor();
            return;
        }

        $label = $this->obtenerEtiquetaAdultoMayor($adultoMayor);
        $this->selected_cod_am = $adultoMayor->cod_am;
        $this->busquedaAdultoMayor = $label;
        $this->adultoMayorSeleccionadoLabel = $label;
        $this->adultosMayoresFiltrados = [];
        $this->mostrarDropdownAdultosMayores = false;
        $this->resetErrorBag('selected_cod_am');
    }

    public function limpiarSeleccionAdultoMayor(): void
    {
        $this->selected_cod_am = '';
        $this->busquedaAdultoMayor = '';
        $this->adultoMayorSeleccionadoLabel = '';
        $this->adultosMayoresFiltrados = [];
        $this->mostrarDropdownAdultosMayores = false;
    }

    public function obtenerAdultoMayorSeleccionado()
    {
        return $this->selected_cod_am
            ? \App\Models\AdultoMayor::where('cod_est_adul', 1)->where('cod_am', $this->selected_cod_am)->first()
            : null;
    }

    public function obtenerDireccionResumen(): string
    {
        $departamento = $this->resolverDepartamentoDomicilio();
        $municipio = $this->resolverMunicipioDomicilio();
        $zona = $this->resolverZonaDomicilio();

        $partes = [];
        if (!empty($departamento)) {
            $partes[] = "DEPARTAMENTO: {$departamento}";
        }
        if (!empty($municipio)) {
            $partes[] = "MUNICIPIO: {$municipio}";
        }
        if (!empty($zona)) {
            $partes[] = "ZONA: {$zona}";
        }
        if (!empty($this->calle)) {
            $partes[] = "CALLE/AV.: {$this->normalizarMayusculas($this->calle)}";
        }
        if (!empty($this->nro_domicilio)) {
            $partes[] = "NRO.: {$this->normalizarMayusculas($this->nro_domicilio)}";
        }
        if (!empty($this->referencia_domicilio)) {
            $partes[] = "REF.: {$this->normalizarMayusculas($this->referencia_domicilio)}";
        }

        if ($partes) {
            return implode('; ', $partes);
        }

        return $this->direccion ? $this->normalizarMayusculas($this->direccion) : 'DIRECCIÓN NO REGISTRADA';
    }

    public function updatedEspecialidadSalud($val)
    {
        if (!in_array($this->rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) return;
        
        $nombre = strtoupper((string) $val);
        if ($nombre !== '') {
            if (in_array($nombre, ['ENFERMERÍA', 'GERIATRÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])) {
                $this->cod_area = 'ARE_0004'; // Área de Atención Médica
            } elseif (in_array($nombre, ['PSICOLOGÍA', 'PEDAGOGÍA'])) {
                $this->cod_area = 'ARE_0005'; // Área de Psicología y Seguimiento Cognitivo
            }
        }
    }

    public function updatedCargoAdministrativo($val)
    {
        if (! in_array($this->rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR', 'personal_admin'], true)) return;
        
        $nombre = strtoupper((string) $val);
        if ($nombre !== '') {
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
        $this->correo = mb_strtolower(trim($val), 'UTF-8');
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
            'departamento_domicilio' => ['required', 'string'],
            'municipio_domicilio' => ['required', 'string'],
            'referencia_domicilio' => ['nullable', 'string', 'max:255'],
            'contacto_emergencia' => ['required', 'string', 'min:2', 'max:150', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'ap_paterno_emergencia' => ['required_without:ap_materno_emergencia', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'ap_materno_emergencia' => ['required_without:ap_paterno_emergencia', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
            'parentesco_emergencia' => ['required', 'string', 'max:100'],
            'celular_emergencia' => ['required', 'string', 'max:20', 'different:telefono', 'regex:/^\d+$/'],
            'rol' => ['required', 'exists:roles,name'],
            'cod_area' => ['nullable', 'string', 'in:ARE_0001,ARE_0002,ARE_0003,ARE_0004,ARE_0005'],
        ];

        $rules = array_merge($rules, $this->reglasDireccion());

        if ($this->isEdit) {
            $rules['estado'] = ['required', 'in:ACTIVO,INACTIVO,ARCHIVADO'];
            $rules['acceso_sistema'] = ['required', 'in:HABILITADO,BLOQUEADO'];
            if ($this->usuarioId === auth()->id()) {
                $rules['password_actual'] = ['required_with:password', 'current_password'];
                $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
            }
        }

        if (in_array($this->rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
            $rules['especialidad_salud'] = ['nullable', 'string'];
            $rules['institucion_formacion'] = ['nullable', 'string', 'max:255'];
            $rules['fecha_ingreso'] = ['required', 'date'];
        }

        if (in_array($this->rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR'])) {
            $rules['cargo_administrativo'] = ['nullable', 'string'];
            $rules['fecha_ingreso'] = ['required', 'date'];
        }

        if ($this->rol === 'VOLUNTARIO') {
            $rules['disponibilidad_inicial'] = ['nullable', 'string', 'max:150'];
            $rules['area_apoyo_preferente'] = ['nullable', 'string', 'max:150'];
            $rules['fecha_ingreso'] = ['required', 'date'];
        }

        if ($this->rol === 'FAMILIAR') {
            $rules['observacion_vinculo'] = ['nullable', 'string', 'max:255'];
            $rules['vinculosFamiliar'] = ['required', 'array', 'min:1'];
            $rules['vinculosFamiliar.*.parentesco_vinculo'] = ['required', 'string', 'max:100'];
        }

        return $rules;
    }

    public function messages()
    {
        return array_merge([
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
            'vinculosFamiliar.required' => 'Debe vincular al menos un adulto mayor al familiar.',
            'vinculosFamiliar.min' => 'Debe vincular al menos un adulto mayor al familiar.',
            'vinculosFamiliar.*.parentesco_vinculo.required' => 'Debe registrar el parentesco del vinculo familiar.',
        ], $this->mensajesDireccion());
    }

    private function validarUnicidadDocumento($fail)
    {
        $query = User::whereRaw('UPPER(pais_documento) = ?', [mb_strtoupper((string) $this->pais_documento, 'UTF-8')])
            ->whereRaw('UPPER(tipo_documento) = ?', [mb_strtoupper((string) $this->tipo_documento, 'UTF-8')])
            ->whereRaw('UPPER(numero_documento) = ?', [mb_strtoupper((string) $this->numero_documento, 'UTF-8')]);
        if (mb_strtoupper((string) $this->pais_documento, 'UTF-8') === 'BOLIVIA') {
            $query->whereRaw('UPPER(expedido) = ?', [mb_strtoupper((string) $this->expedido, 'UTF-8')]);
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
            'direccion', 'referencia_domicilio', 'zona', 'ciudad', 'contacto_emergencia', 'parentesco_emergencia', 'celular_emergencia',
            'tipo_vinculacion', 'matricula_prof', 'institucion_formacion', 'disponibilidad_inicial',
            'area_apoyo_preferente', 'observacion_vinculo',
            'calle', 'nro_domicilio', 'ap_paterno_emergencia', 'ap_materno_emergencia',
            'vinculosFamiliar', 'selected_cod_am', 'selected_parentesco', 'selected_es_responsable', 'selected_observaciones',
            'busquedaAdultoMayor', 'adultosMayoresFiltrados', 'mostrarDropdownAdultosMayores', 'adultoMayorSeleccionadoLabel',
            'mostrarQuickRegAdulto', 'quick_nombres', 'quick_ap_paterno', 'quick_ap_materno', 'quick_ci', 'quick_genero', 'quick_fecha_nac',
            'departamento_domicilio', 'municipio_domicilio', 'zona_domicilio',
            'otro_departamento', 'otro_municipio', 'otra_zona',
            'selected_responsable_salud', 'selected_responsable_economico'
        ]);
        $this->departamento_domicilio = 'LA PAZ';
        $this->municipio_domicilio = 'LA PAZ';
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
        $this->pais_documento = 'BOLIVIA';
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
        $this->selected_parentesco = $this->normalizarMayusculas($this->selected_parentesco);
        $this->selected_observaciones = $this->normalizarMayusculas($this->selected_observaciones);

        $this->validate([
            'selected_cod_am' => 'required',
            'selected_parentesco' => 'required|string|max:100',
        ], [
            'selected_cod_am.required' => 'Seleccione un adulto mayor válido de la lista.',
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

        $nombreCompleto = $this->obtenerEtiquetaAdultoMayor($am);
        $this->vinculosFamiliar[] = [
            'cod_am' => $this->selected_cod_am,
            'nombres_completos' => $nombreCompleto,
            'parentesco_vinculo' => $this->selected_parentesco,
            'es_responsable' => $this->selected_es_responsable ? 'SI' : 'NO',
            'responsable_salud' => $this->selected_responsable_salud ? 'SI' : 'NO',
            'responsable_economico' => $this->selected_responsable_economico ? 'SI' : 'NO',
            'observaciones' => $this->selected_observaciones ?? '',
        ];

        // Limpiar campos de vinculación
        $this->selected_cod_am = '';
        $this->selected_parentesco = 'HIJO/A';
        $this->selected_es_responsable = false;
        $this->selected_responsable_salud = false;
        $this->selected_responsable_economico = false;
        $this->selected_observaciones = '';
        $this->limpiarSeleccionAdultoMayor();

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
        $am->nombres = $this->normalizarMayusculas($this->quick_nombres);
        $am->ap_paterno = $this->normalizarMayusculas($this->quick_ap_paterno);
        $am->ap_materno = $this->normalizarMayusculas($this->quick_ap_materno);
        $am->ci = $this->normalizarMayusculas($this->quick_ci);
        $am->genero = $this->normalizarMayusculas($this->quick_genero);
        if (!empty($this->quick_fecha_nac)) {
            $am->setAttribute('fecha_nac', \Carbon\Carbon::parse($this->quick_fecha_nac)->toDateString());
        } else {
            $am->setAttribute('fecha_nac', null);
        }
        $am->cod_est_adul = 1; // ACTIVO
        $am->setAttribute('fecha_ing', \Carbon\Carbon::today()->toDateString());
        $am->save();

        $nombreCompleto = $this->obtenerEtiquetaAdultoMayor($am);
        
        // Agregar automáticamente a la lista de vinculación
        $this->vinculosFamiliar[] = [
            'cod_am' => $am->cod_am,
            'nombres_completos' => $nombreCompleto,
            'parentesco_vinculo' => $this->selected_parentesco,
            'es_responsable' => $this->selected_es_responsable ? 'SI' : 'NO',
            'responsable_salud' => $this->selected_responsable_salud ? 'SI' : 'NO',
            'responsable_economico' => $this->selected_responsable_economico ? 'SI' : 'NO',
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

        $usuario = User::with(['roles'])->findOrFail($cod_usu);

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
        $this->pais_documento = $this->normalizarMayusculas($usuario->pais_documento ?? 'BOLIVIA');
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
        $this->referencia_domicilio = null;
        if (preg_match('/REF:\s*(.+)$/u', (string) $usuario->direccion, $match)) {
            $this->referencia_domicilio = trim($match[1]);
        }
        $this->calle = $usuario->calle;
        $this->nro_domicilio = $usuario->nro_domicilio;
        $this->zona = $usuario->zona;
        $this->ciudad = $usuario->ciudad;

        // Cargar selects dependientes de dirección
        $ciudadUpper = mb_strtoupper(trim($usuario->ciudad ?? ''), 'UTF-8');
        $zonaUpper = mb_strtoupper(trim($usuario->zona ?? ''), 'UTF-8');
        
        $foundCity = false;
        foreach ($this->catalogDepartamentos as $dept => $muniList) {
            if (in_array($ciudadUpper, $muniList) && $dept !== 'OTRO' && $ciudadUpper !== 'OTRO') {
                $this->departamento_domicilio = $dept;
                $this->municipio_domicilio = $ciudadUpper;
                $foundCity = true;
                break;
            }
        }
        
        if (!$foundCity) {
            if (array_key_exists($ciudadUpper, $this->catalogDepartamentos) && $ciudadUpper !== 'OTRO') {
                $this->departamento_domicilio = $ciudadUpper;
                $this->municipio_domicilio = 'OTRO';
                $this->otro_municipio = $ciudadUpper;
            } else {
                $this->departamento_domicilio = 'OTRO';
                $this->otro_departamento = $ciudadUpper;
                $this->municipio_domicilio = 'OTRO';
                $this->otro_municipio = $ciudadUpper;
            }
        } else {
            $this->otro_departamento = '';
            $this->otro_municipio = '';
        }

        // Cargar zonas dependientes
        if (isset($this->catalogZonas[$this->municipio_domicilio])) {
            if (in_array($zonaUpper, $this->catalogZonas[$this->municipio_domicilio])) {
                $this->zona_domicilio = $zonaUpper;
                $this->otra_zona = '';
            } else {
                $this->zona_domicilio = 'OTRO';
                $this->otra_zona = $zonaUpper;
            }
        } else {
            $this->zona_domicilio = 'OTRO';
            $this->otra_zona = $zonaUpper;
        }
        
        $this->contacto_emergencia = $usuario->contacto_emergencia;
        $this->ap_paterno_emergencia = $usuario->ap_paterno_emergencia;
        $this->ap_materno_emergencia = $usuario->ap_materno_emergencia;
        $this->parentesco_emergencia = $usuario->parentesco_emergencia;
        $this->celular_emergencia = $usuario->celular_emergencia;
        $this->tipo_vinculacion = $usuario->tipo_vinculacion ?? 'CONTRATO';

        $this->rol = $usuario->roles->first()?->name ?? '';

        if (in_array($this->rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
            $ps = $usuario->personalSalud;
            $this->fecha_ingreso = $ps?->fecha_ing ? \Carbon\Carbon::parse($ps->fecha_ing)->format('Y-m-d') : '';
            $this->especialidad_salud = $ps?->cod_esp;
            $this->matricula_prof = null;
            $this->institucion_formacion = $ps?->institucion_formacion;
        } elseif (in_array($this->rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR'])) {
            $pa = $usuario->personalAdmin;
            $this->fecha_ingreso = $pa?->fecha_ingreso ? \Carbon\Carbon::parse($pa->fecha_ingreso)->format('Y-m-d') : '';
            $this->cargo_administrativo = $pa?->cod_cargo_admin;
        } elseif ($this->rol === 'VOLUNTARIO') {
            $vol = \App\Models\Voluntario::where('cod_usu', $usuario->cod_usu)->first();
            if ($vol) {
                $this->fecha_ingreso = $vol->fecha_ing ? \Carbon\Carbon::parse($vol->fecha_ing)->format('Y-m-d') : '';
                $this->disponibilidad_inicial = $vol->disponibilidad_inicial;
                $this->area_apoyo_preferente = $vol->area_apoyo_preferente;
            }
        } elseif ($this->rol === 'FAMILIAR') {
            $fam = \App\Models\Familiar::where('cod_usu', $usuario->cod_usu)->first();
            if ($fam) {
                $this->parentesco_emergencia = $fam->parentesco;
                $this->observacion_vinculo = $fam->observaciones;
                
                // Cargar adultos mayores vinculados existentes
                $this->vinculosFamiliar = [];
                foreach ($fam->adultosMayores as $am) {
                    $obs = $am->pivot->observaciones ?? '';
                    $salud = 'NO';
                    $economico = 'NO';
                    $cleanObs = $obs;
                    // Deserializar responsabilidades (acepta SI sin acento)
                    if (preg_match('/Salud:\s*(\w+)/u', $obs, $m)) {
                        $salud = in_array(mb_strtoupper($m[1], 'UTF-8'), ['SI']) ? 'SI' : 'NO';
                    }
                    if (preg_match('/Econ.mico:\s*(\w+)/u', $obs, $m)) {
                        $economico = in_array(mb_strtoupper($m[1], 'UTF-8'), ['SI']) ? 'SI' : 'NO';
                    }
                    if (preg_match('/Obs:\s*(.+)$/u', $obs, $m)) {
                        $cleanObs = trim($m[1]);
                    }

                    $this->vinculosFamiliar[] = [
                        'cod_am' => $am->cod_am,
                        'nombres_completos' => $this->obtenerEtiquetaAdultoMayor($am),
                        'parentesco_vinculo' => $am->pivot->parentesco_vinculo ?? 'Familiar',
                        'es_responsable' => $am->pivot->es_responsable ? 'SI' : 'NO',
                        'responsable_salud' => $salud,
                        'responsable_economico' => $economico,
                        'observaciones' => $cleanObs,
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
            $this->genero = $this->normalizarMayusculas($this->genero);
            $this->pais_documento = $this->normalizarMayusculas($this->pais_documento);
            $this->tipo_documento = $this->normalizarMayusculas($this->tipo_documento);
            $this->numero_documento = $this->normalizarMayusculas($this->numero_documento);
            $this->expedido = $this->normalizarMayusculas($this->expedido);

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

            if (mb_strtoupper((string) $this->pais_documento, 'UTF-8') === 'BOLIVIA' && $this->tipo_documento === 'CI') {
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
            $this->normalizarDatosFormulario();

            $rulesPaso2 = [
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
                'departamento_domicilio' => ['required', 'string'],
                'municipio_domicilio' => ['required', 'string'],
                'referencia_domicilio' => ['nullable', 'string', 'max:255'],
                'contacto_emergencia' => ['required', 'string', 'min:2', 'max:150', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
                'ap_paterno_emergencia' => ['required_without:ap_materno_emergencia', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
                'ap_materno_emergencia' => ['required_without:ap_paterno_emergencia', 'nullable', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-]+$/u'],
                'parentesco_emergencia' => ['required', 'string', 'max:100'],
                'celular_emergencia' => ['required', 'string', 'max:20', 'different:telefono', 'regex:/^\d+$/'],
            ];

            $rulesPaso2 = array_merge($rulesPaso2, $this->reglasDireccion());

            $this->validate($rulesPaso2, array_merge($this->messages(), [
                'contacto_emergencia.regex' => 'El nombre del contacto solo puede contener letras.',
                'ap_paterno_emergencia.regex' => 'El apellido del contacto solo puede contener letras.',
                'ap_materno_emergencia.regex' => 'El apellido del contacto solo puede contener letras.',
                'celular_emergencia.different' => 'El celular de emergencia no puede ser igual al celular del usuario.',
                'celular_emergencia.regex' => 'El celular de emergencia solo puede contener números.',
            ], $this->mensajesDireccion()));

            $this->validarTelefono(function($err) {
                $this->addError('telefono', $err);
            });
            if ($this->getErrorBag()->has('telefono')) {
                return;
            }
        } elseif ($this->pasoFormulario === 3) {
            $this->tipo_vinculacion = match(true) {
                in_array($this->rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA']) => 'CONTRATO',
                in_array($this->rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR']) => 'CONTRATO',
                $this->rol === 'VOLUNTARIO' => 'VOLUNTARIADO',
                $this->rol === 'FAMILIAR' => 'FAMILIAR',
                default => 'OTRO'
            };

            if (!$this->isEdit) {
                $this->fecha_ingreso = now()->format('Y-m-d');
                $this->estado = 'ACTIVO';
                $this->acceso_sistema = 'HABILITADO';
            }

            $this->normalizarDatosFormulario();

            $rules = [
                'rol' => ['required', 'exists:roles,name'],
            ];

            if ($this->isEdit) {
                $rules['estado'] = ['required', 'in:ACTIVO,INACTIVO,ARCHIVADO'];
                $rules['acceso_sistema'] = ['required', 'in:HABILITADO,BLOQUEADO'];
            }

            if (in_array($this->rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
                $rules['especialidad_salud'] = ['required', 'string'];
                $rules['institucion_formacion'] = ['nullable', 'string', 'max:255'];
                $rules['fecha_ingreso'] = ['required', 'date'];
            }

            if (in_array($this->rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR'])) {
                $rules['cargo_administrativo'] = ['required', 'string'];
                $rules['fecha_ingreso'] = ['required', 'date'];
            }

            if ($this->rol === 'VOLUNTARIO') {
                $rules['disponibilidad_inicial'] = ['nullable', 'string', 'max:150'];
                $rules['area_apoyo_preferente'] = ['nullable', 'string', 'max:150'];
                $rules['fecha_ingreso'] = ['required', 'date'];
            }

            if ($this->rol === 'FAMILIAR') {
                if (!empty($this->busquedaAdultoMayor) && empty($this->selected_cod_am) && count($this->vinculosFamiliar) === 0) {
                    $this->addError('selected_cod_am', 'Seleccione un adulto mayor válido de la lista.');
                    return;
                }

                if (!empty($this->selected_cod_am) && !collect($this->vinculosFamiliar)->contains(fn ($v) => ($v['cod_am'] ?? null) === $this->selected_cod_am)) {
                    $this->vincularAdultoMayor();
                    if ($this->getErrorBag()->has('selected_cod_am') || $this->getErrorBag()->has('selected_parentesco')) {
                        return;
                    }
                }

                $rules['observacion_vinculo'] = ['nullable', 'string', 'max:255'];
                $rules['vinculosFamiliar'] = ['required', 'array', 'min:1'];
                $rules['vinculosFamiliar.*.parentesco_vinculo'] = ['required', 'string', 'max:100'];
            }

            $this->validate($rules, array_merge($this->messages(), [
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

        // Ejecutar normalización completa
        $this->normalizarDatosFormulario();

        if ($this->rol === 'FAMILIAR') {
            if (!empty($this->busquedaAdultoMayor) && empty($this->selected_cod_am) && count($this->vinculosFamiliar) === 0) {
                $this->addError('selected_cod_am', 'Seleccione un adulto mayor válido de la lista.');
                return;
            }

            if (!empty($this->selected_cod_am) && !collect($this->vinculosFamiliar)->contains(fn ($v) => ($v['cod_am'] ?? null) === $this->selected_cod_am)) {
                $this->vincularAdultoMayor();
                if ($this->getErrorBag()->has('selected_cod_am') || $this->getErrorBag()->has('selected_parentesco')) {
                    return;
                }
            }
        }

        $rules = $this->rules();
        $this->validate($rules);

        if (empty($this->ap_paterno) && empty($this->ap_materno)) {
            $this->addError('ap_paterno', 'Debe registrar al menos un apellido (paterno o materno).');
            $this->addError('ap_materno', 'Debe registrar al menos un apellido (paterno o materno).');
            return;
        }

        if (mb_strtoupper((string) $this->pais_documento, 'UTF-8') === 'BOLIVIA' && $this->tipo_documento === 'CI') {
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
            $this->rol = 'admin';
            $this->correo = 'admincasaamandita@gmail.com';
        }

        $this->armarDireccionCompleta();

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

        // Manejar archivo cargado fuera de la transacción
        $oldPhotoToDelete = null;
        $newPhotoPath = null;
        if ($this->foto_de_perfil_upload) {
            if ($this->isEdit && $this->usuarioId) {
                $oldUser = User::find($this->usuarioId);
                if ($oldUser && $oldUser->foto_de_perfil && \Storage::disk('public')->exists($oldUser->foto_de_perfil)) {
                    $oldPhotoToDelete = $oldUser->foto_de_perfil;
                }
            }
            $newPhotoPath = $this->foto_de_perfil_upload->store('usuarios/fotos', 'public');
            $userData['foto_de_perfil'] = $newPhotoPath;
        }

        $mensaje = '';
        $passwordTemporal = null;
        $usuario = null;

        try {
            DB::beginTransaction();

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
                    // Para otros usuarios, la contraseña no se cambia mediante el formulario general
                    unset($userData['password']);
                }

                if ($usuario->hasRole('SUPERADMINISTRADOR') && $this->rol !== 'SUPERADMINISTRADOR') {
                    $superadmins = User::role('SUPERADMINISTRADOR')->where('estado', 'ACTIVO')->count();
                    if ($superadmins <= 1) {
                        throw new \Exception('No puedes quitar el rol de superadministrador al único superadministrador activo.');
                    }
                }

                $usuario->update($userData);
                $usuario->syncRoles([$this->rol]);

                // Guardar sub-modelos de forma unificada en edición
                $this->guardarSubModelos($usuario);

            } else {
                $userData['estado'] = 'ACTIVO';
                $userData['acceso_sistema'] = 'HABILITADO';
                $passwordTemporal = $this->passwordTemporalVisual ?: $this->generarPasswordTemporal();
                $userData['password'] = $passwordTemporal;
                $userData['debe_cambiar_password'] = true;

                $usuario = User::create($userData);
                $usuario->assignRole($this->rol);

                // Guardar sub-modelos de forma unificada en creación
                $this->guardarSubModelos($usuario);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            // Revertir físicamente el nuevo archivo en caso de fallo
            if ($newPhotoPath && \Storage::disk('public')->exists($newPhotoPath)) {
                \Storage::disk('public')->delete($newPhotoPath);
            }

            Log::error("Error crítico durante el guardado de usuario: " . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $this->usuarioId ?? 'nuevo',
                'rol' => $this->rol
            ]);

            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error al guardar',
                'text' => $e->getMessage() === 'No puedes quitar el rol de superadministrador al único superadministrador activo.'
                    ? $e->getMessage()
                    : 'Ocurrió un error inesperado al procesar los datos en el servidor.'
            ]);
            return;
        }

        // --- OPERACIONES DE ESCRITURA EN DISCO / MAILING / LOGGING FUERA DE LA TRANSACCIÓN (POST-COMMIT EXITOSO) ---

        $this->procesarPostGuardado($usuario, $passwordTemporal, $oldPhotoToDelete);
    }

    private function guardarSubModelos(User $usuario)
    {
        if ($this->rol === 'VOLUNTARIO') {
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
            // TODO: definir flujo administrativo para archivar perfiles previos al cambiar de rol.
        } elseif ($this->rol === 'FAMILIAR') {
            $hayResponsable = collect($this->vinculosFamiliar)->contains(fn($v) => $v['es_responsable'] === 'SI');
            $fam = \App\Models\Familiar::updateOrCreate(
                ['cod_usu' => $usuario->cod_usu],
                [
                    'parentesco' => $this->parentesco_emergencia ?? 'Familiar',
                    'direccion' => $this->direccion,
                    'es_responsable' => $hayResponsable ? 'SI' : 'NO',
                    'observaciones' => $this->observacion_vinculo,
                ]
            );
            
            $this->guardarVinculosFamiliar($fam);
            // TODO: definir flujo administrativo para archivar perfiles previos al cambiar de rol.
        }
    }

    // ══════════════════════════════════════════════
    // VISTA COMPLETA (modal/panel expandido)
    // ══════════════════════════════════════════════

    private function procesarPostGuardado(User $usuario, ?string $passwordTemporal, ?string $oldPhotoToDelete)
    {
        // 1. Eliminar la foto antigua físicamente de forma segura
        if ($oldPhotoToDelete) {
            try {
                \Storage::disk('public')->delete($oldPhotoToDelete);
            } catch (\Exception $ex) {
                Log::error("No se pudo eliminar la foto antigua del storage: " . $ex->getMessage());
            }
        }

        if ($this->isEdit) {
            // Notificación por correo de clave actualizada
            $correoNotifEnviado = false;
            $passwordModificada = ($usuario->cod_usu === auth()->id() && !empty($this->password));
            if ($passwordModificada) {
                try {
                    \Mail::to($usuario->correo)->send(new \App\Mail\UsuarioPasswordActualizadaMail($usuario, $this->password));
                    $correoNotifEnviado = true;
                } catch (\Exception $e) {
                    Log::error("Error al enviar correo de actualización de clave a {$usuario->correo}: " . $e->getMessage());
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
                Log::error("Error al enviar correo de bienvenida a {$usuario->correo}: " . $e->getMessage());
            }

            // Enviar credenciales
            $credencialesEnviadas = false;
            try {
                \Mail::to($usuario->correo)->send(new \App\Mail\UsuarioCredencialesInicialesMail($usuario, $passwordTemporal));
                $credencialesEnviadas = true;
            } catch (\Exception $e) {
                Log::error("Error al enviar credenciales a {$usuario->correo}: " . $e->getMessage());
            }

            if (function_exists('activity')) {
                $logMsg = "Se registró un nuevo usuario: {$usuario->name} con rol {$this->rol}. " . ($credencialesEnviadas ? "Correo de credenciales enviado con éxito." : "Error al enviar correo de credenciales.");
                activity('Usuarios')
                    ->causedBy(auth()->user())
                    ->performedOn($usuario)
                    ->event('registro')
                    ->log($logMsg);
            }

            $rolGuardado = $this->rol;
            $this->resetFormulario();
            $this->mostrarFormulario = false;
            $this->resetPage();

            $this->usuarioPostRegistro = $usuario;
            $this->documentosRequeridos = app(\App\Services\Documentos\DocumentosUsuarioService::class)->documentosRequeridosPorRol($rolGuardado);
            $this->fechaLimiteDocumentacion = $usuario->created_at ? $usuario->created_at->addHours(48)->format('d/m/Y H:i') : now()->addHours(48)->format('d/m/Y H:i');
            $this->passwordTemporalPostRegistro = $passwordTemporal;
            $this->mostrarPostRegistro = true;

            // Enviar correo de requisitos de forma automática
            $this->enviarCorreoRequisitosAction();
        }
    }

    public function abrirVistaCompleta($codUsu)
    {
        $this->verUsuario($codUsu);
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
        $this->verUsuario($codUsu);
    }

    public function cerrarFichaRapida(): void
    {
        $this->mostrarFichaRapida = false;
        $this->usuarioFichaId = null;
    }

    public function verUsuario($codUsu)
    {
        $this->usuarioDetalle = User::with([
            'roles',
            'documentos',
            'familiares.adultosMayores',
            'voluntarios'
        ])->findOrFail($codUsu);
        $this->modoVista = 'detalle';
        $this->mostrarVistaCompleta = false;
        $this->mostrarFichaRapida = false;
        $this->seccionActivaDetalle = 'resumen';
    }

    public function cambiarSeccionDetalle(string $seccion): void
    {
        $this->seccionActivaDetalle = $seccion;
    }

    public function volverAlListadoUsuarios()
    {
        $this->modoVista = 'listado';
        $this->usuarioDetalle = null;
    }

    public function abrirEdicionDesdeDetalle()
    {
        if ($this->usuarioDetalle) {
            $this->editarUsuario($this->usuarioDetalle->cod_usu);
        }
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

        // Proteger último admin
        if ($usuario->estado === 'ACTIVO' && $usuario->hasRole('SUPERADMINISTRADOR')) {
            $superadmins = User::role('SUPERADMINISTRADOR')->where('estado', 'ACTIVO')->count();
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
            $query = User::query()->with(['roles']);

            if (!empty($this->search)) {
                $query->where(function ($q) {
                    $q->whereLike('nombres', '%' . $this->search . '%')
                      ->orWhereLike('ap_paterno', '%' . $this->search . '%')
                      ->orWhereLike('ap_materno', '%' . $this->search . '%')
                      ->orWhereLike('correo', '%' . $this->search . '%');
                });
            }

            if (!empty($this->filtroEstado)) {
                $query->where('estado', $this->filtroEstado);
            }

            if (!empty($this->filtroRol)) {
                $query->role($this->filtroRol);
            }

            if (!empty($this->filtroArea)) {
                $rolesArea = AreaInstitucional::rolesPorArea($this->filtroArea);
                $query->whereHas('roles', fn ($q) => $q->whereIn('name', $rolesArea));
            }

            $usuarios = $query->orderBy('ap_paterno')->orderBy('nombres')->get();

            $viewData = [
                'usuarios' => $usuarios,
                'fecha' => date('d/m/Y H:i'),
                'usuario' => auth()->user()->name,
            ];

            $fileNameService = app(\App\Services\Reportes\ReportFileNameService::class);
            $filename = $fileNameService->generate('usuarios_remembermind', 'pdf');

            $exportService = app(\App\Services\Reportes\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->log('Se exportó el reporte general de usuarios en formato PDF.');

            return $exportService->exportPdf('pdf.exports.usuarios.general', $viewData, $filename);
        } catch (\Throwable $e) {
            Log::error("Error al exportar PDF de usuarios: " . $e->getMessage());
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
            $fileNameService = app(\App\Services\Reportes\ReportFileNameService::class);
            $filename = $fileNameService->generate('usuarios_remembermind', 'xlsx');

            $exportService = app(\App\Services\Reportes\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->log('Se exportó el listado de usuarios en formato Excel.');

            return $exportService->exportExcel(new \App\Exports\UsuariosExport, $filename);
        } catch (\Throwable $e) {
            Log::error("Error al exportar Excel de usuarios: " . $e->getMessage());
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
            $fileNameService = app(\App\Services\Reportes\ReportFileNameService::class);
            $filename = $fileNameService->generate('usuarios_remembermind', 'csv');

            $exportService = app(\App\Services\Reportes\ReportExportService::class);

            activity('Usuarios')
                ->causedBy(auth()->user())
                ->log('Se exportó el listado de usuarios en formato CSV.');

            return $exportService->exportCsv(new \App\Exports\UsuariosExport, $filename);
        } catch (\Throwable $e) {
            Log::error("Error al exportar CSV de usuarios: " . $e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el reporte en CSV: ' . $e->getMessage()
            ]);
        }
    }



    public function obtenerNombreRolLegible($rol): string
    {
        switch ($rol) {
            case 'SUPERADMINISTRADOR':
            case 'ADMINISTRADOR':
                return 'Personal Administrativo';
            case 'ENFERMEROS':
            case 'MEDICO GENERAL/GERIATRA':
            case 'PSICOLOGO/A':
            case 'PEDAGOGO':
            case 'NUTRICIONISTA':
            case 'FISIOTERAPEUTA':
                return 'Personal de Salud';
            case 'VOLUNTARIO':
                return 'Voluntario';
            case 'FAMILIAR':
                return 'Familiar';
            case 'admin':
                return 'Administrador';
            case 'super_admin':
                return 'Super Administrador';
            default:
                return ucfirst(str_replace('_', ' ', $rol));
        }
    }

    public function enviarCorreoRequisitosAction($usuarioId = null)
    {
        try {
            $usuario = $usuarioId ? User::find($usuarioId) : $this->usuarioPostRegistro;
            if (!$usuario) {
                throw new \Exception("Usuario no encontrado.");
            }

            $rol = $usuario->roles->first()?->name;
            $documentos = app(\App\Services\Documentos\DocumentosUsuarioService::class)->documentosRequeridosPorRol($rol);
            $rolLegible = $this->obtenerNombreRolLegible($rol);
            $fechaLimite = $usuario->created_at ? $usuario->created_at->addHours(48)->format('d/m/Y H:i') : now()->addHours(48)->format('d/m/Y H:i');
            $fechaRegistro = $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i');

            // Generar PDF para adjuntarlo
            $viewData = [
                'usuario_reg' => $usuario,
                'rol_legible' => $rolLegible,
                'fecha_registro' => $fechaRegistro,
                'fecha_limite' => $fechaLimite,
                'documentos' => $documentos,
                'fecha' => now()->format('d/m/Y H:i'),
                'usuario' => auth()->check() ? auth()->user()->name : 'Sistema',
            ];

            $pdfContent = null;
            try {
                $pdfContent = \Spatie\LaravelPdf\Facades\Pdf::view('pdf.exports.usuarios.solicitud_documental_pdf', $viewData)->output();
            } catch (\Throwable $e) {
                Log::warning("Spatie PDF falló al generar adjunto en panel: {$e->getMessage()}. Usando DomPDF.");
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.exports.usuarios.solicitud_documental_pdf', $viewData)
                    ->setPaper('a4', 'portrait')
                    ->setWarnings(false);
                $pdfContent = $pdf->output();
            }

            $filename = 'solicitud_documental_' . $usuario->cod_usu . '.pdf';
            $tempPath = 'temp/' . uniqid() . '_' . $filename;
            \Illuminate\Support\Facades\Storage::disk('public')->put($tempPath, $pdfContent);
            $fullPath = storage_path('app/public/' . $tempPath);

            \Illuminate\Support\Facades\Mail::to($usuario->correo)
                ->send(new \App\Mail\UsuarioRequisitosDocumentalesMail($usuario, $rolLegible, $documentos, $fechaLimite, $fullPath, $filename));

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            $this->correoRequisitosEnviado = true;
            $this->mensajeCorreoRequisitos = 'Correo enviado correctamente al correo registrado.';
            
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Correo Enviado',
                'text' => 'El correo de requisitos documentales ha sido enviado a ' . $usuario->correo
            ]);
        } catch (\Exception $e) {
            Log::error("Error al enviar correo de requisitos para usuario " . ($usuario->cod_usu ?? 'unknown') . ": " . $e->getMessage());
            $this->correoRequisitosEnviado = false;
            $this->mensajeCorreoRequisitos = 'Usuario registrado, pero no se pudo enviar el correo de requisitos.';
            
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Advertencia de Correo',
                'text' => $this->mensajeCorreoRequisitos
            ]);
        }
    }

    public function reenviarCorreoRequisitos()
    {
        if ($this->usuarioPostRegistro) {
            $this->enviarCorreoRequisitosAction($this->usuarioPostRegistro->cod_usu);
        }
    }

    public function cerrarPostRegistro()
    {
        $this->mostrarPostRegistro = false;
        $this->usuarioPostRegistro = null;
        $this->documentosRequeridos = [];
        $this->fechaLimiteDocumentacion = null;
        $this->passwordTemporalPostRegistro = null;
        $this->correoRequisitosEnviado = false;
        $this->mensajeCorreoRequisitos = null;
    }

    public function registrarOtroUsuario()
    {
        $this->cerrarPostRegistro();
        $this->crearUsuario();
    }

    public function verFichaPostRegistro()
    {
        if ($this->usuarioPostRegistro) {
            $codUsu = $this->usuarioPostRegistro->cod_usu;
            $this->cerrarPostRegistro();
            $this->verUsuario($codUsu);
        }
    }

    // ══════════════════════════════════════════════
    // GESTIÓN DOCUMENTAL (SUBIDA DE ARCHIVOS)
    // ══════════════════════════════════════════════

    public function abrirModalSubirDoc($codTipoDoc)
    {
        $tipo = \App\Models\TipoDocumentoUsuario::findOrFail($codTipoDoc);
        $this->tipoDocSeleccionado = $codTipoDoc;
        $this->tipoDocNombre = $tipo->nombre;
        $this->archivoTemporal = null;
        $this->fechaEmisionDoc = null;
        $this->fechaVencimientoDoc = null;
        $this->observacionesDoc = '';
        $this->mostrarModalSubirDoc = true;
    }

    public function cerrarModalSubirDoc()
    {
        $this->mostrarModalSubirDoc = false;
        $this->tipoDocSeleccionado = null;
        $this->tipoDocNombre = null;
        $this->archivoTemporal = null;
        $this->fechaEmisionDoc = null;
        $this->fechaVencimientoDoc = null;
        $this->observacionesDoc = '';
    }

    public function guardarDocumento()
    {
        $this->validate([
            'archivoTemporal' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'fechaEmisionDoc' => 'nullable|date',
            'fechaVencimientoDoc' => 'nullable|date|after_or_equal:fechaEmisionDoc',
            'observacionesDoc' => 'nullable|string|max:1000',
        ], [
            'archivoTemporal.required' => 'Debe seleccionar un archivo.',
            'archivoTemporal.file' => 'El archivo no es válido.',
            'archivoTemporal.mimes' => 'Solo se permiten archivos PDF, JPG, JPEG o PNG.',
            'archivoTemporal.max' => 'El tamaño máximo del archivo es de 10 MB.',
            'fechaVencimientoDoc.after_or_equal' => 'La fecha de vencimiento debe ser posterior o igual a la de emisión.',
        ]);

        $usuario = $this->usuarioDetalle;
        if (!$usuario) {
            $this->cerrarModalSubirDoc();
            return;
        }

        $service = app(\App\Services\Documentos\DocumentacionUsuarioService::class);
        
        try {
            $service->subirDocumento(
                $usuario,
                $this->tipoDocSeleccionado,
                $this->archivoTemporal,
                $this->fechaEmisionDoc,
                $this->fechaVencimientoDoc,
                $this->observacionesDoc
            );

            // Refrescar el usuario para reflejar el estado cargado
            $this->usuarioDetalle = User::with([
                'roles',
                'documentos',
                'familiares.adultosMayores',
                'voluntarios'
            ])->findOrFail($usuario->cod_usu);

            $this->cerrarModalSubirDoc();

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Documento cargado',
                'text' => 'El archivo ha sido registrado correctamente.',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error al subir',
                'text' => $e->getMessage(),
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
        ]);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->whereLike('nombres', '%' . $this->search . '%')
                  ->orWhereLike('ap_paterno', '%' . $this->search . '%')
                  ->orWhereLike('ap_materno', '%' . $this->search . '%')
                  ->orWhereLike('correo', '%' . $this->search . '%')
                  ->orWhereLike('cod_usu', '%' . $this->search . '%');
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
            $rolesArea = AreaInstitucional::rolesPorArea($this->filtroArea);
            $query->whereHas('roles', fn ($q) => $q->whereIn('name', $rolesArea));
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
                'documentos',
                'familiares.adultosMayores',
                'voluntarios'
            ])->where('cod_usu', $this->usuarioFichaId)->first();
        }

        return view('livewire.identidad.usuarios-panel', [
            'usuarios' => $query->paginate(12),
            'roles' => Role::all(),
            'especialidades' => collect(),
            'cargosAdmin' => collect(),
            'areas' => collect(User::$areasEstaticas)->map(fn ($nombre, $codigo) => (object) [
                'cod_area' => $codigo,
                'nombre' => $nombre,
            ]),
            'usuarioFicha' => $usuarioFichaModel,
        ]);
    }
}


