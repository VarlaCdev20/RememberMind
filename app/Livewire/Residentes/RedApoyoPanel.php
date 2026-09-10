<?php

namespace App\Livewire\Residentes;

use App\Models\AdultoMayor;
use App\Models\Familiar;
use App\Models\FamiliarAdulto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RedApoyoPanel extends Component
{
    public string $adultoSeleccionado = '';
    public string $buscarAdulto = '';
    public string $buscarPersona = '';
    public string $filtroEstado = '';
    public string $filtroTipo = '';

    public bool $mostrarFormulario = false;
    public bool $modoEdicion = false;
    public ?int $vinculoId = null;
    public ?array $detalleVinculo = null;
    public bool $modalDetalleVinculo = false;

    public array $form = [
        'cod_fam' => '',
        'nombres' => '',
        'ap_paterno' => '',
        'ap_materno' => '',
        'correo' => '',
        'telefono' => '',
        'direccion' => '',
        'parentesco_vinculo' => '',
        'es_responsable' => false,
        'es_contacto_emergencia' => false,
        'estado' => 'ACTIVO',
        'observaciones' => '',
    ];

    protected $queryString = [
        'adultoSeleccionado' => ['as' => 'adulto', 'except' => ''],
        'buscarAdulto' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (request('adulto')) {
            $this->adultoSeleccionado = (string) request('adulto');
        }
    }

    public function updatedAdultoSeleccionado(): void
    {
        $this->detalleVinculo = null;
        $this->buscarPersona = '';
        $this->filtroEstado = '';
        $this->filtroTipo = '';
    }

    public function limpiarSeleccion(): void
    {
        $this->adultoSeleccionado = '';
        $this->detalleVinculo = null;
        $this->resetForm();
    }

    public function actualizarRed(): void
    {
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Red actualizada',
            'text' => 'La informacion de la red de apoyo fue recargada correctamente.',
        ]);
    }

    public function abrirVincular(): void
    {
        abort_unless(auth()->user()->can('familiares.crear'), 403);

        if ($this->adultoSeleccionado === '') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Seleccione un adulto mayor',
                'text' => 'Debe seleccionar un adulto mayor antes de vincular un familiar o contacto.',
            ]);
            return;
        }

        $this->resetForm();
        $this->modoEdicion = false;
        $this->mostrarFormulario = true;
    }

    public function editarVinculo(int $id): void
    {
        abort_unless(auth()->user()->can('familiares.editar'), 403);

        $vinculo = FamiliarAdulto::with(['familiar.usuario'])
            ->where('id', $id)
            ->where('cod_am', $this->adultoSeleccionado)
            ->first();

        if (! $vinculo || ! $vinculo->familiar) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Vinculo no encontrado',
                'text' => 'No se encontro el vinculo familiar seleccionado.',
            ]);
            return;
        }

        $usuario = $vinculo->familiar->usuario;

        $this->vinculoId = $vinculo->id;
        $this->modoEdicion = true;
        $this->form = [
            'cod_fam' => (string) $vinculo->cod_fam,
            'nombres' => (string) ($usuario?->nombres ?? ''),
            'ap_paterno' => (string) ($usuario?->ap_paterno ?? ''),
            'ap_materno' => (string) ($usuario?->ap_materno ?? ''),
            'correo' => (string) ($usuario?->correo ?? ''),
            'telefono' => (string) ($usuario?->telefono ?? ''),
            'direccion' => (string) ($vinculo->familiar->direccion ?? $usuario?->direccion ?? ''),
            'parentesco_vinculo' => (string) ($vinculo->parentesco_vinculo ?? $vinculo->familiar->parentesco ?? ''),
            'es_responsable' => $this->boolValue($vinculo->es_responsable),
            'es_contacto_emergencia' => $this->esContactoEmergencia($this->adultoActual(), $this->nombreUsuario($usuario), $usuario?->telefono),
            'estado' => (string) ($vinculo->estado ?? 'ACTIVO'),
            'observaciones' => (string) ($vinculo->observaciones ?? $vinculo->familiar->observaciones ?? ''),
        ];

        $this->mostrarFormulario = true;
    }

    public function updatedFormCodFam(): void
    {
        if ($this->modoEdicion || empty($this->form['cod_fam'])) {
            return;
        }

        $familiar = Familiar::with('usuario')->find($this->form['cod_fam']);

        if (! $familiar) {
            return;
        }

        $usuario = $familiar->usuario;

        $this->form['nombres'] = (string) ($usuario?->nombres ?? '');
        $this->form['ap_paterno'] = (string) ($usuario?->ap_paterno ?? '');
        $this->form['ap_materno'] = (string) ($usuario?->ap_materno ?? '');
        $this->form['correo'] = (string) ($usuario?->correo ?? '');
        $this->form['telefono'] = (string) ($usuario?->telefono ?? '');
        $this->form['direccion'] = (string) ($familiar->direccion ?? $usuario?->direccion ?? '');
        $this->form['parentesco_vinculo'] = (string) ($familiar->parentesco ?? '');
    }

    public function cerrarFormulario(): void
    {
        $this->mostrarFormulario = false;
        $this->resetForm();
    }

    public function guardarVinculo(): void
    {
        abort_unless(auth()->user()->can($this->modoEdicion ? 'familiares.editar' : 'familiares.crear'), 403);

        $this->validate($this->rules(), $this->messages());

        DB::transaction(function () {
            $familiar = $this->resolverFamiliar();

            $duplicado = FamiliarAdulto::query()
                ->where('cod_am', $this->adultoSeleccionado)
                ->where('cod_fam', $familiar->cod_fam)
                ->when($this->vinculoId, fn ($query) => $query->where('id', '!=', $this->vinculoId))
                ->when(Schema::hasColumn('familiar_adulto', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
                ->exists();

            if ($duplicado) {
                $this->addError('form.cod_fam', 'Esta persona ya esta vinculada al adulto mayor seleccionado.');
                return;
            }

            if ((bool) $this->form['es_responsable']) {
                FamiliarAdulto::query()
                    ->where('cod_am', $this->adultoSeleccionado)
                    ->when($this->vinculoId, fn ($query) => $query->where('id', '!=', $this->vinculoId))
                    ->update(['es_responsable' => false]);
            }

            $payload = [
                'cod_fam' => $familiar->cod_fam,
                'cod_am' => $this->adultoSeleccionado,
                'parentesco_vinculo' => $this->form['parentesco_vinculo'],
                'es_responsable' => (bool) $this->form['es_responsable'],
                'estado' => $this->form['estado'],
                'observaciones' => $this->form['observaciones'] ?: null,
            ];

            if ($this->modoEdicion && $this->vinculoId) {
                FamiliarAdulto::where('id', $this->vinculoId)->update($payload);
            } else {
                FamiliarAdulto::create($payload);
            }

            if ((bool) $this->form['es_contacto_emergencia']) {
                $this->actualizarContactoEmergencia($familiar);
            }
        });

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->mostrarFormulario = false;
        $this->detalleVinculo = null;
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->modoEdicion ? 'Vinculo actualizado' : 'Vinculo creado',
            'text' => $this->modoEdicion
                ? 'La informacion del vinculo fue actualizada correctamente.'
                : 'La persona quedo vinculada a la red de apoyo.',
        ]);
        $this->resetForm();
    }

    public function abrirDetalleVinculo(string $tipo, string|int $id): void
    {
        $this->detalleVinculo = [];
        $nodoOriginal = null;

        if ($tipo === 'adulto') {
            $adulto = $this->adultoActual();
            $nodoOriginal = $adulto ? $this->detalleAdulto($adulto) : null;
        } elseif ($tipo === 'familiar') {
            $nodoOriginal = $this->familiaresColeccion()->firstWhere('cod_fam', $id);
        } elseif ($tipo === 'voluntario') {
            $nodoOriginal = $this->voluntariosColeccion()->firstWhere('cod_vol', $id);
        }

        if ($nodoOriginal) {
            $this->detalleVinculo = [
                'tipo' => $tipo,
                'nombre_completo' => $nodoOriginal['nombre'] ?? 'Información no disponible',
                'iniciales' => $nodoOriginal['iniciales'] ?? 'NA',
                'parentesco' => $nodoOriginal['parentesco'] ?? 'No registrado',
                'tipo_vinculo' => $tipo === 'voluntario' ? 'Voluntario' : ($tipo === 'adulto' ? 'Adulto Mayor' : 'Familiar'),
                'celular' => $nodoOriginal['celular'] ?? 'No registrado',
                'correo' => $nodoOriginal['correo'] ?? 'No registrado',
                'direccion' => $nodoOriginal['direccion'] ?? 'No registrada',
                'estado' => $nodoOriginal['estado'] ?? 'No registrado',
                'responsable_principal' => $nodoOriginal['responsable'] ?? false,
                'contacto_emergencia' => $nodoOriginal['emergencia'] ?? false,
                'adulto_mayor' => $nodoOriginal['adulto_nombre'] ?? 'No registrado',
                'observacion' => !empty($nodoOriginal['observaciones']) ? $nodoOriginal['observaciones'] : 'Sin observaciones registradas.',
                'ultima_actualizacion' => $nodoOriginal['fecha_registro'] ?? ($nodoOriginal['actualizado'] ?? 'No registrada'),
                'vinculo_id' => $nodoOriginal['vinculo_id'] ?? null,
                'incompleto' => $nodoOriginal['incompleto'] ?? false,
                'asignaciones' => $nodoOriginal['asignaciones'] ?? 0,
                'ultima_participacion' => $nodoOriginal['ultima_participacion'] ?? 'Sin registro',
            ];
            $this->modalDetalleVinculo = true;
        } else {
            $this->detalleVinculo = [
                'tipo' => $tipo,
                'nombre_completo' => 'Información no disponible',
                'iniciales' => 'NA',
                'parentesco' => 'No registrado',
                'tipo_vinculo' => 'No registrado',
                'celular' => 'No registrado',
                'correo' => 'No registrado',
                'direccion' => 'No registrada',
                'estado' => 'No registrado',
                'responsable_principal' => false,
                'contacto_emergencia' => false,
                'adulto_mayor' => 'No registrado',
                'observacion' => 'Sin observaciones registradas.',
                'ultima_actualizacion' => 'No registrada',
            ];
            $this->modalDetalleVinculo = true;
        }
    }

    public function cerrarDetalleVinculo(): void
    {
        $this->modalDetalleVinculo = false;
        $this->detalleVinculo = null;
    }

    public function marcarResponsable(int $vinculoId): void
    {
        abort_unless(auth()->user()->can('familiares.editar'), 403);

        $vinculo = FamiliarAdulto::where('id', $vinculoId)
            ->where('cod_am', $this->adultoSeleccionado)
            ->first();

        if (! $vinculo) {
            return;
        }

        FamiliarAdulto::where('cod_am', $this->adultoSeleccionado)->update(['es_responsable' => false]);
        $vinculo->update(['es_responsable' => true, 'estado' => 'ACTIVO']);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Responsable principal actualizado',
            'text' => 'Esta persona quedo marcada como contacto principal de apoyo.',
        ]);
    }

    public function marcarContactoEmergencia(int $vinculoId): void
    {
        abort_unless(auth()->user()->can('familiares.editar'), 403);

        $vinculo = FamiliarAdulto::with('familiar.usuario')
            ->where('id', $vinculoId)
            ->where('cod_am', $this->adultoSeleccionado)
            ->first();

        if (! $vinculo?->familiar) {
            return;
        }

        $this->actualizarContactoEmergencia($vinculo->familiar);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Contacto de emergencia actualizado',
            'text' => 'La persona quedo registrada como contacto de emergencia del adulto mayor.',
        ]);
    }

    public function desactivarVinculo(int $vinculoId): void
    {
        abort_unless(auth()->user()->can('familiares.anular'), 403);

        FamiliarAdulto::where('id', $vinculoId)
            ->where('cod_am', $this->adultoSeleccionado)
            ->update(['estado' => 'INACTIVO']);

        $this->detalleVinculo = null;
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Vinculo desactivado',
            'text' => 'El vinculo no fue eliminado; quedo conservado en el historial institucional.',
        ]);
    }

    public function activarVinculo(int $vinculoId): void
    {
        abort_unless(auth()->user()->can('familiares.editar'), 403);

        FamiliarAdulto::where('id', $vinculoId)
            ->where('cod_am', $this->adultoSeleccionado)
            ->update(['estado' => 'ACTIVO']);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Vinculo reactivado',
            'text' => 'La persona vuelve a formar parte activa de la red de apoyo.',
        ]);
    }

    public function render()
    {
        $adulto = $this->adultoActual();
        $familiares = $adulto ? $this->familiaresColeccion() : collect();
        $voluntarios = $adulto ? $this->voluntariosColeccion() : collect();

        return view('livewire.residentes.red-apoyo-panel', [
            'adultos' => $this->adultosDisponibles(),
            'adulto' => $adulto,
            'familiares' => $familiares,
            'voluntarios' => $voluntarios,
            'gruposFamiliares' => $this->agruparFamiliares($familiares),
            'voluntariosMapa' => $voluntarios->take(8)->values(),
            'personasListado' => $this->personasListado($familiares, $voluntarios),
            'metricas' => $this->metricas($adulto, $familiares, $voluntarios),
            'familiaresDisponibles' => $this->familiaresDisponibles(),
            'detalleVinculo' => $this->detalleVinculo,
            'mostrarFormulario' => $this->mostrarFormulario,
            'modoEdicion' => $this->modoEdicion,
            'form' => $this->form,
            'rutas' => [
                'resumen' => Route::has('admin.familia-social.resumen') ? route('admin.familia-social.resumen') : null,
            ],
        ])->layout('layouts.sistema');
    }

    protected function rules(): array
    {
        $requiereNuevo = $this->requiereNuevoFamiliar();
        $usuarioId = null;

        if ($this->form['cod_fam']) {
            $usuarioId = Familiar::with('usuario')->find($this->form['cod_fam'])?->usuario?->cod_usu;
        }

        $correoRules = ['nullable', 'email', 'max:120'];
        if (! empty($this->form['correo'])) {
            $correoRules[] = Rule::unique('users', 'correo')->ignore($usuarioId, 'cod_usu');
        }

        return [
            'adultoSeleccionado' => ['required', 'exists:adulto_mayor,cod_am'],
            'form.cod_fam' => ['nullable', 'exists:familiares,cod_fam'],
            'form.nombres' => [$requiereNuevo ? 'required' : 'nullable', 'string', 'max:100'],
            'form.ap_paterno' => [$requiereNuevo ? 'required' : 'nullable', 'string', 'max:80'],
            'form.ap_materno' => ['nullable', 'string', 'max:80'],
            'form.correo' => $correoRules,
            'form.telefono' => ['nullable', 'regex:/^[0-9+\\-\\s()]{6,20}$/'],
            'form.direccion' => ['nullable', 'string', 'max:150'],
            'form.parentesco_vinculo' => ['required', 'string', 'max:100'],
            'form.es_responsable' => ['boolean'],
            'form.es_contacto_emergencia' => ['boolean'],
            'form.estado' => ['required', 'in:ACTIVO,INACTIVO'],
            'form.observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'adultoSeleccionado.required' => 'Seleccione un adulto mayor para gestionar su red de apoyo.',
            'adultoSeleccionado.exists' => 'El adulto mayor seleccionado no se encuentra disponible.',
            'form.nombres.required' => 'Ingrese los nombres del familiar o contacto.',
            'form.ap_paterno.required' => 'Ingrese al menos el apellido paterno.',
            'form.correo.email' => 'Ingrese un correo valido.',
            'form.correo.unique' => 'El correo ya esta registrado por otra persona.',
            'form.telefono.regex' => 'Ingrese un telefono valido.',
            'form.parentesco_vinculo.required' => 'Seleccione o escriba el parentesco o rol de apoyo.',
            'form.estado.required' => 'Seleccione el estado del vinculo.',
        ];
    }

    private function adultoActual(): ?AdultoMayor
    {
        if ($this->adultoSeleccionado === '') {
            return null;
        }

        return AdultoMayor::with('estado')->find($this->adultoSeleccionado);
    }

    private function adultosDisponibles(): Collection
    {
        $query = AdultoMayor::query()->with('estado');

        if (Schema::hasColumn('adulto_mayor', 'archivado_en')) {
            $query->whereNull('archivado_en');
        }

        if ($this->buscarAdulto !== '') {
            $buscar = '%' . mb_strtolower(trim($this->buscarAdulto)) . '%';
            $query->whereRaw("LOWER(CONCAT(nombres, ' ', ap_paterno, ' ', COALESCE(ap_materno, ''))) LIKE ?", [$buscar]);
        }

        return $query
            ->orderBy('ap_paterno')
            ->limit(15)
            ->get()
            ->map(fn (AdultoMayor $adulto) => [
                'cod_am' => (string) $adulto->cod_am,
                'nombre' => $this->nombreAdulto($adulto),
                'edad' => $this->edad($adulto->fecha_nac),
                'estado' => $adulto->estado?->estado ?? 'Sin estado',
            ]);
    }

    private function familiaresColeccion(): Collection
    {
        if ($this->adultoSeleccionado === '' || ! Schema::hasTable('familiar_adulto')) {
            return collect();
        }

        $adulto = $this->adultoActual();

        return DB::table('familiar_adulto as fa')
            ->join('familiares as f', 'fa.cod_fam', '=', 'f.cod_fam')
            ->leftJoin('users as u', 'f.cod_usu', '=', 'u.cod_usu')
            ->where('fa.cod_am', $this->adultoSeleccionado)
            ->when(Schema::hasColumn('familiar_adulto', 'deleted_at'), fn ($query) => $query->whereNull('fa.deleted_at'))
            ->select([
                'fa.id as vinculo_id',
                'fa.cod_fam',
                'fa.parentesco_vinculo',
                'fa.es_responsable',
                'fa.estado as estado_vinculo',
                'fa.observaciones as observaciones_vinculo',
                'fa.updated_at as vinculo_actualizado',
                'fa.created_at as vinculo_creado',
                'f.parentesco',
                'f.direccion',
                'f.ocupacion',
                'f.observaciones as familiar_observaciones',
                'u.nombres',
                'u.ap_paterno',
                'u.ap_materno',
                'u.correo',
                'u.telefono',
                'u.direccion as usuario_direccion',
                'u.estado as usuario_estado',
            ])
            ->orderByDesc('fa.es_responsable')
            ->orderBy('u.ap_paterno')
            ->get()
            ->map(function ($row) use ($adulto) {
                $nombre = trim(implode(' ', array_filter([$row->nombres, $row->ap_paterno, $row->ap_materno])));
                $telefono = $row->telefono ?: 'No registrado';
                $emergencia = $this->esContactoEmergencia($adulto, $nombre, $telefono);
                $responsable = $this->boolValue($row->es_responsable);
                $estado = strtoupper((string) ($row->estado_vinculo ?? 'ACTIVO'));

                return [
                    'tipo' => 'familiar',
                    'id' => 'familiar-' . $row->cod_fam,
                    'vinculo_id' => (int) $row->vinculo_id,
                    'cod_fam' => (int) $row->cod_fam,
                    'nombre' => $nombre !== '' ? $nombre : 'Familiar sin nombre',
                    'iniciales' => $this->iniciales($nombre),
                    'parentesco' => $row->parentesco_vinculo ?: $row->parentesco ?: 'Apoyo familiar',
                    'rol' => $responsable ? 'Responsable principal' : 'Familiar/contacto',
                    'celular' => $telefono,
                    'correo' => $row->correo ?: 'No registrado',
                    'direccion' => $row->direccion ?: $row->usuario_direccion ?: 'No registrada',
                    'responsable' => $responsable,
                    'emergencia' => $emergencia,
                    'estado' => $estado,
                    'estado_badge' => $estado === 'ACTIVO' ? 'Activo' : 'Inactivo',
                    'observaciones' => $row->observaciones_vinculo ?: $row->familiar_observaciones ?: 'Sin observaciones registradas.',
                    'actualizado' => $this->formatoFecha($row->vinculo_actualizado),
                    'fecha_registro' => $this->formatoFecha($row->vinculo_creado ?? $row->vinculo_actualizado),
                    'incompleto' => $telefono === 'No registrado' || ! $row->parentesco_vinculo,
                    'adulto_nombre' => $this->nombreAdulto($adulto),
                ];
            })
            ->values();
    }

    private function voluntariosColeccion(): Collection
    {
        if ($this->adultoSeleccionado === '' || ! Schema::hasTable('asignacion_voluntarios') || ! Schema::hasTable('voluntarios')) {
            return collect();
        }

        return DB::table('asignacion_voluntarios as av')
            ->join('voluntarios as v', 'av.cod_vol', '=', 'v.cod_vol')
            ->leftJoin('users as u', 'v.cod_usu', '=', 'u.cod_usu')
            ->where('av.cod_am', $this->adultoSeleccionado)
            ->select([
                'v.cod_vol',
                'v.area_apoyo',
                'v.area_apoyo_preferente',
                'v.estado as estado_voluntario',
                'v.observaciones',
                'u.nombres',
                'u.ap_paterno',
                'u.ap_materno',
                'u.telefono',
                'u.correo',
                'av.fecha_asig',
                'av.fecha_fin',
                'av.estado as estado_asignacion',
                'av.obser',
            ])
            ->orderByDesc('av.fecha_asig')
            ->get()
            ->groupBy('cod_vol')
            ->map(function (Collection $items) {
                $row = $items->first();
                $nombre = trim(implode(' ', array_filter([$row->nombres, $row->ap_paterno, $row->ap_materno])));

                return [
                    'tipo' => 'voluntario',
                    'id' => 'voluntario-' . $row->cod_vol,
                    'cod_vol' => (int) $row->cod_vol,
                    'nombre' => $nombre !== '' ? $nombre : 'Voluntario sin nombre',
                    'iniciales' => $this->iniciales($nombre),
                    'parentesco' => 'Apoyo institucional',
                    'rol' => $row->area_apoyo ?: $row->area_apoyo_preferente ?: 'Voluntariado',
                    'celular' => $row->telefono ?: 'No registrado',
                    'correo' => $row->correo ?: 'No registrado',
                    'direccion' => 'Apoyo institucional',
                    'responsable' => false,
                    'emergencia' => false,
                    'estado' => strtoupper((string) ($row->estado_voluntario ?? 'ACTIVO')),
                    'estado_badge' => $row->estado_asignacion ?: 'Relacionado',
                    'observaciones' => $row->obser ?: $row->observaciones ?: 'Sin observacion registrada',
                    'actualizado' => $row->fecha_asig ? Carbon::parse($row->fecha_asig)->format('d/m/Y') : 'Sin fecha',
                    'asignaciones' => $items->count(),
                    'ultima_participacion' => $row->fecha_asig ? Carbon::parse($row->fecha_asig)->format('d/m/Y') : 'Sin registro',
                    'fecha_registro' => $row->fecha_asig ? Carbon::parse($row->fecha_asig)->format('d/m/Y') : 'Sin registro',
                    'incompleto' => false,
                    'adulto_nombre' => $this->nombreAdulto($this->adultoActual()),
                ];
            })
            ->values();
    }

    private function personasListado(Collection $familiares, Collection $voluntarios): Collection
    {
        $personas = $familiares->merge($voluntarios);

        if ($this->buscarPersona !== '') {
            $buscar = mb_strtolower(trim($this->buscarPersona));
            $personas = $personas->filter(function (array $persona) use ($buscar) {
                return str_contains(mb_strtolower($persona['nombre']), $buscar)
                    || str_contains(mb_strtolower($persona['parentesco']), $buscar)
                    || str_contains(mb_strtolower($persona['celular']), $buscar);
            });
        }

        if ($this->filtroEstado !== '') {
            $estado = strtoupper($this->filtroEstado);
            $personas = $personas->filter(fn (array $persona) => strtoupper($persona['estado']) === $estado);
        }

        if ($this->filtroTipo !== '') {
            $personas = $personas->filter(function (array $persona) {
                return match ($this->filtroTipo) {
                    'responsable' => $persona['responsable'],
                    'emergencia' => $persona['emergencia'],
                    'voluntario' => $persona['tipo'] === 'voluntario',
                    'familiar' => $persona['tipo'] === 'familiar',
                    'incompleto' => $persona['incompleto'],
                    default => true,
                };
            });
        }

        return $personas->values();
    }

    private function familiaresDisponibles(): Collection
    {
        if (! Schema::hasTable('familiares')) {
            return collect();
        }

        $vinculados = [];
        if ($this->adultoSeleccionado !== '') {
            $vinculados = FamiliarAdulto::where('cod_am', $this->adultoSeleccionado)
                ->when(Schema::hasColumn('familiar_adulto', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
                ->pluck('cod_fam')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return Familiar::with('usuario')
            ->whereNotIn('cod_fam', $vinculados)
            ->limit(40)
            ->get()
            ->map(fn (Familiar $familiar) => [
                'cod_fam' => (string) $familiar->cod_fam,
                'nombre' => $this->nombreUsuario($familiar->usuario) ?: 'Familiar sin nombre',
                'parentesco' => $familiar->parentesco ?: 'Sin parentesco',
            ]);
    }

    private function agruparFamiliares(Collection $familiares): array
    {
        $grupos = [
            'conyuge' => [],
            'hijos' => [],
            'nietos' => [],
            'hermanos' => [],
            'sobrinos' => [],
            'otros' => [],
        ];

        foreach ($familiares as $f) {
            $parentesco = mb_strtolower(trim($f['parentesco']));
            
            if (preg_match('/espos[oa]|c[oó]nyuge|pareja|marido|mujer/u', $parentesco)) {
                $grupos['conyuge'][] = $f;
            } elseif (preg_match('/hij[oa]/u', $parentesco)) {
                $grupos['hijos'][] = $f;
            } elseif (preg_match('/niet[oa]/u', $parentesco)) {
                $grupos['nietos'][] = $f;
            } elseif (preg_match('/herman[oa]/u', $parentesco)) {
                $grupos['hermanos'][] = $f;
            } elseif (preg_match('/sobrin[oa]/u', $parentesco)) {
                $grupos['sobrinos'][] = $f;
            } else {
                $grupos['otros'][] = $f;
            }
        }

        return $grupos;
    }

    private function metricas(?AdultoMayor $adulto, Collection $familiares, Collection $voluntarios): array
    {
        if (! $adulto) {
            return [
                'familiares' => 0,
                'responsable' => 'No',
                'emergencias' => 0,
                'voluntarios' => 0,
                'red_incompleta' => 'Pendiente',
                'ultima' => 'Sin seleccion',
            ];
        }

        $activos = $familiares->where('estado', 'ACTIVO');
        $tieneResponsable = $activos->contains('responsable', true);
        $contactosEmergencia = $activos->where('emergencia', true)->count() + ($this->adultoTieneContactoEmergencia($adulto) ? 1 : 0);
        $ultima = $familiares
            ->pluck('actualizado')
            ->reject(fn ($fecha) => $fecha === 'Sin fecha')
            ->first() ?: $this->formatoFecha($adulto->updated_at);

        return [
            'familiares' => $activos->count(),
            'responsable' => $tieneResponsable ? 'Si' : 'No',
            'emergencias' => $contactosEmergencia,
            'voluntarios' => $voluntarios->count(),
            'red_incompleta' => ($activos->isEmpty() || ! $tieneResponsable || $contactosEmergencia === 0) ? 'Incompleta' : 'Completa',
            'ultima' => $ultima,
        ];
    }

    private function resolverFamiliar(): Familiar
    {
        if (! empty($this->form['cod_fam'])) {
            $familiar = Familiar::with('usuario')->findOrFail($this->form['cod_fam']);
            $this->actualizarDatosFamiliar($familiar);

            return $familiar;
        }

        $correo = $this->form['correo'] ?: $this->generarCorreoTemporal();
        $password = $this->generarPasswordTemporal();

        $usuarioPayload = [
            'nombres' => $this->form['nombres'],
            'ap_paterno' => $this->form['ap_paterno'],
            'ap_materno' => $this->form['ap_materno'] ?: null,
            'correo' => $correo,
            'password' => Hash::make($password),
            'telefono' => $this->form['telefono'] ?: null,
            'estado' => 'ACTIVO',
        ];

        if (Schema::hasColumn('users', 'acceso_sistema')) {
            $usuarioPayload['acceso_sistema'] = 'HABILITADO';
        }

        if (Schema::hasColumn('users', 'direccion')) {
            $usuarioPayload['direccion'] = $this->form['direccion'] ?: null;
        }

        $usuario = User::create($usuarioPayload);

        if (Schema::hasTable('roles') && Role::where('name', 'familiar')->exists()) {
            $usuario->assignRole('familiar');
        }

        return Familiar::create([
            'parentesco' => $this->form['parentesco_vinculo'],
            'direccion' => $this->form['direccion'] ?: null,
            'es_responsable' => (bool) $this->form['es_responsable'] ? 'SI' : 'NO',
            'estado' => 'ACTIVO',
            'observaciones' => $this->form['observaciones'] ?: null,
            'cod_usu' => $usuario->cod_usu,
        ]);
    }

    private function actualizarDatosFamiliar(Familiar $familiar): void
    {
        $usuario = $familiar->usuario;

        if ($usuario) {
            $usuarioPayload = [
                'nombres' => $this->form['nombres'] ?: $usuario->nombres,
                'ap_paterno' => $this->form['ap_paterno'] ?: $usuario->ap_paterno,
                'ap_materno' => $this->form['ap_materno'] ?: null,
                'telefono' => $this->form['telefono'] ?: null,
            ];

            if (! empty($this->form['correo'])) {
                $usuarioPayload['correo'] = $this->form['correo'];
            }

            if (Schema::hasColumn('users', 'direccion')) {
                $usuarioPayload['direccion'] = $this->form['direccion'] ?: null;
            }

            $usuario->update($usuarioPayload);
        }

        $familiar->update([
            'parentesco' => $this->form['parentesco_vinculo'],
            'direccion' => $this->form['direccion'] ?: null,
            'es_responsable' => (bool) $this->form['es_responsable'] ? 'SI' : 'NO',
            'estado' => $this->form['estado'],
            'observaciones' => $this->form['observaciones'] ?: null,
        ]);
    }

    private function actualizarContactoEmergencia(Familiar $familiar): void
    {
        $adulto = $this->adultoActual();
        $usuario = $familiar->usuario;

        if (! $adulto || ! $usuario) {
            return;
        }

        $payload = [];

        if (Schema::hasColumn('adulto_mayor', 'contacto_emergencia_nombre')) {
            $payload['contacto_emergencia_nombre'] = $this->nombreUsuario($usuario);
        }

        if (Schema::hasColumn('adulto_mayor', 'contacto_emergencia_parentesco')) {
            $payload['contacto_emergencia_parentesco'] = $this->form['parentesco_vinculo'];
        }

        if (Schema::hasColumn('adulto_mayor', 'contacto_emergencia_celular')) {
            $payload['contacto_emergencia_celular'] = $usuario->telefono;
        }

        if (Schema::hasColumn('adulto_mayor', 'contacto_emergencia_direccion')) {
            $payload['contacto_emergencia_direccion'] = $familiar->direccion;
        }

        if (! empty($payload)) {
            $adulto->update($payload);
        }
    }

    private function detalleAdulto(AdultoMayor $adulto): array
    {
        return [
            'tipo' => 'adulto',
            'nombre' => $this->nombreAdulto($adulto),
            'parentesco' => 'Nodo central',
            'rol' => 'Adulto mayor',
            'celular' => $adulto->celular ?: $adulto->telefono ?: 'No registrado',
            'correo' => 'No aplica',
            'direccion' => trim(($adulto->zona ? $adulto->zona . ' ' : '') . ($adulto->calle ?: '')) ?: 'No registrada',
            'estado' => $adulto->estado?->estado ?? 'Sin estado',
            'estado_badge' => $adulto->estado?->estado ?? 'Sin estado',
            'observaciones' => $adulto->observaciones ?: 'Sin observacion registrada',
            'actualizado' => $this->formatoFecha($adulto->updated_at),
            'responsable' => false,
            'emergencia' => $this->adultoTieneContactoEmergencia($adulto),
        ];
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->vinculoId = null;
        $this->modoEdicion = false;
        $this->form = [
            'cod_fam' => '',
            'nombres' => '',
            'ap_paterno' => '',
            'ap_materno' => '',
            'correo' => '',
            'telefono' => '',
            'direccion' => '',
            'parentesco_vinculo' => '',
            'es_responsable' => false,
            'es_contacto_emergencia' => false,
            'estado' => 'ACTIVO',
            'observaciones' => '',
        ];
    }

    private function requiereNuevoFamiliar(): bool
    {
        return ! $this->modoEdicion && empty($this->form['cod_fam']);
    }

    private function nombreAdulto(AdultoMayor $adulto): string
    {
        return trim(implode(' ', array_filter([$adulto->nombres, $adulto->ap_paterno, $adulto->ap_materno])));
    }

    private function nombreUsuario(?User $usuario): string
    {
        if (! $usuario) {
            return '';
        }

        return trim(implode(' ', array_filter([$usuario->nombres, $usuario->ap_paterno, $usuario->ap_materno])));
    }

    private function iniciales(string $nombre): string
    {
        $partes = preg_split('/\s+/', trim($nombre));
        $iniciales = collect($partes)
            ->filter()
            ->take(2)
            ->map(fn ($parte) => mb_substr($parte, 0, 1))
            ->implode('');

        return mb_strtoupper($iniciales ?: 'NA');
    }

    private function edad($fecha): string
    {
        if (! $fecha) {
            return 'Edad no registrada';
        }

        return Carbon::parse($fecha)->age . ' años';
    }

    private function formatoFecha($fecha): string
    {
        if (! $fecha) {
            return 'Sin fecha';
        }

        return Carbon::parse($fecha)->format('d/m/Y');
    }

    private function boolValue(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'SI', 'si', 'true', 'TRUE'], true);
    }

    private function adultoTieneContactoEmergencia(?AdultoMayor $adulto): bool
    {
        return (bool) ($adulto?->contacto_emergencia_nombre || $adulto?->contacto_emergencia_celular);
    }

    private function esContactoEmergencia(?AdultoMayor $adulto, string $nombre, ?string $telefono): bool
    {
        if (! $adulto) {
            return false;
        }

        $nombreEmergencia = mb_strtolower(trim((string) $adulto->contacto_emergencia_nombre));
        $telefonoEmergencia = preg_replace('/\D+/', '', (string) $adulto->contacto_emergencia_celular);
        $nombrePersona = mb_strtolower(trim($nombre));
        $telefonoPersona = preg_replace('/\D+/', '', (string) $telefono);

        return ($nombreEmergencia !== '' && $nombrePersona !== '' && str_contains($nombrePersona, $nombreEmergencia))
            || ($telefonoEmergencia !== '' && $telefonoPersona !== '' && $telefonoEmergencia === $telefonoPersona);
    }

    private function generarPasswordTemporal(): string
    {
        return 'FAM_' . Str::random(8);
    }

    private function generarCorreoTemporal(): string
    {
        do {
            $correo = 'familiar_' . Str::lower(Str::random(10)) . '@casaamandita.temporal';
        } while (User::where('correo', $correo)->exists());

        return $correo;
    }
}
