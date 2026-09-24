<?php

namespace App\Livewire\Residentes;

use App\Models\Residente;
use App\Models\Contacto;
use App\Models\ResidenteContacto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Component;

class RedApoyoPanel extends Component
{
    public string $adultoSeleccionado = '';
    public string $buscarAdulto = '';
    public string $buscarPersona = '';
    public string $filtroEstado = '';
    public string $filtroTipo = '';

    public bool $mostrarFormulario = false;
    public bool $modoEdicion = false;
    public ?string $vinculoId = null;
    public ?array $detalleVinculo = null;
    public bool $modalDetalleVinculo = false;

    public array $form = [
        'cod_contacto' => '',
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
            'text' => 'La información de la red de apoyo fue recargada correctamente.',
        ]);
    }

    public function abrirVincular(): void
    {
        abort_unless(auth()->user()->can('familiares.crear'), 403);

        if ($this->adultoSeleccionado === '') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Seleccione un residente',
                'text' => 'Debe seleccionar un residente antes de vincular un familiar o contacto.',
            ]);
            return;
        }

        $this->resetForm();
        $this->modoEdicion = false;
        $this->mostrarFormulario = true;
    }

    public function editarVinculo(string $id): void
    {
        abort_unless(auth()->user()->can('familiares.editar'), 403);

        $vinculo = ResidenteContacto::with('contacto')
            ->where('cod_residente_contacto', $id)
            ->where('cod_residente', $this->adultoSeleccionado)
            ->first();

        if (! $vinculo || ! $vinculo->contacto) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Vínculo no encontrado',
                'text' => 'No se encontró el vínculo familiar seleccionado.',
            ]);
            return;
        }

        $c = $vinculo->contacto;
        $this->vinculoId = $vinculo->cod_residente_contacto;
        $this->modoEdicion = true;
        $this->form = [
            'cod_contacto' => $c->cod_contacto,
            'nombres' => $c->nombres,
            'ap_paterno' => $c->apellido_paterno,
            'ap_materno' => $c->apellido_materno ?? '',
            'correo' => $c->correo ?? '',
            'telefono' => $c->celular ?? $c->telefono ?? '',
            'direccion' => $c->direccion ?? '',
            'parentesco_vinculo' => $vinculo->parentesco,
            'es_responsable' => (bool) $vinculo->responsable_principal,
            'es_contacto_emergencia' => (bool) $vinculo->contacto_emergencia,
            'estado' => $vinculo->estado,
            'observaciones' => $vinculo->observacion ?? '',
        ];

        $this->mostrarFormulario = true;
    }

    public function updatedFormCodContacto(): void
    {
        if ($this->modoEdicion || empty($this->form['cod_contacto'])) {
            return;
        }

        $contacto = Contacto::find($this->form['cod_contacto']);
        if (! $contacto) return;

        $this->form['nombres'] = (string) $contacto->nombres;
        $this->form['ap_paterno'] = (string) $contacto->apellido_paterno;
        $this->form['ap_materno'] = (string) ($contacto->apellido_materno ?? '');
        $this->form['correo'] = (string) ($contacto->correo ?? '');
        $this->form['telefono'] = (string) ($contacto->celular ?? $contacto->telefono ?? '');
        $this->form['direccion'] = (string) ($contacto->direccion ?? '');
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
            $contacto = $this->resolverContacto();

            if ((bool) $this->form['es_responsable']) {
                ResidenteContacto::where('cod_residente', $this->adultoSeleccionado)
                    ->when($this->vinculoId, fn ($q) => $q->where('cod_residente_contacto', '!=', $this->vinculoId))
                    ->update(['responsable_principal' => false]);
            }

            if ((bool) $this->form['es_contacto_emergencia']) {
                ResidenteContacto::where('cod_residente', $this->adultoSeleccionado)
                    ->when($this->vinculoId, fn ($q) => $q->where('cod_residente_contacto', '!=', $this->vinculoId))
                    ->update(['contacto_emergencia' => false]);
            }

            $payload = [
                'cod_contacto' => $contacto->cod_contacto,
                'cod_residente' => $this->adultoSeleccionado,
                'parentesco' => $this->form['parentesco_vinculo'],
                'responsable_principal' => (bool) $this->form['es_responsable'],
                'contacto_emergencia' => (bool) $this->form['es_contacto_emergencia'],
                'estado' => $this->form['estado'],
                'observacion' => $this->form['observaciones'] ?: null,
            ];

            if ($this->modoEdicion && $this->vinculoId) {
                ResidenteContacto::where('cod_residente_contacto', $this->vinculoId)->update($payload);
            } else {
                $payload['cod_residente_contacto'] = 'RC_' . strtoupper(Str::random(10));
                ResidenteContacto::create($payload);
            }
        });

        $this->cerrarFormulario();
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $this->modoEdicion ? 'Vínculo actualizado' : 'Familiar vinculado',
            'text' => 'La red de apoyo del residente fue actualizada con éxito.',
        ]);
    }

    public function verDetalle(string $vinculoId): void
    {
        $vinculo = ResidenteContacto::with(['contacto', 'residente'])
            ->where('cod_residente_contacto', $vinculoId)
            ->where('cod_residente', $this->adultoSeleccionado)
            ->first();

        if (! $vinculo || ! $vinculo->contacto) {
            return;
        }

        $c = $vinculo->contacto;
        $r = $vinculo->residente;

        $this->detalleVinculo = [
            'tipo' => 'FAMILIAR',
            'id' => $vinculo->cod_residente_contacto,
            'vinculo_id' => $vinculo->cod_residente_contacto,
            'cod_fam' => $c->cod_contacto,
            'nombre_completo' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
            'parentesco' => $vinculo->parentesco,
            'rol' => $vinculo->parentesco,
            'celular' => $c->celular ?? $c->telefono ?? 'No registrado',
            'correo' => $c->correo ?? 'No registrado',
            'direccion' => $c->direccion ?? 'No registrada',
            'estado' => $vinculo->estado,
            'estado_badge' => $vinculo->estado,
            'observacion' => $vinculo->observacion ?: 'Sin observaciones',
            'responsable_principal' => (bool) $vinculo->responsable_principal,
            'contacto_emergencia' => (bool) $vinculo->contacto_emergencia,
            'adulto_mayor' => $r ? $r->nombre_completo : '',
            'ultima_actualizacion' => 'Sin fecha registrada',
            'incompleto' => blank($c->celular ?? $c->telefono) || blank($c->correo),
        ];

        $this->modalDetalleVinculo = true;
    }

    public function verDetalleAdulto(): void
    {
        $adulto = $this->adultoActual();
        if (! $adulto) return;

        $this->detalleVinculo = $this->detalleAdulto($adulto);
        $this->modalDetalleVinculo = true;
    }

    public function cerrarDetalleVinculo(): void
    {
        $this->modalDetalleVinculo = false;
        $this->detalleVinculo = null;
    }

    public function marcarResponsable(string $vinculoId): void
    {
        abort_unless(auth()->user()->can('familiares.editar'), 403);

        ResidenteContacto::where('cod_residente', $this->adultoSeleccionado)->update(['responsable_principal' => false]);
        ResidenteContacto::where('cod_residente_contacto', $vinculoId)->update(['responsable_principal' => true, 'estado' => 'ACTIVO']);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Responsable principal actualizado',
            'text' => 'Esta persona quedó marcada como contacto principal de apoyo.',
        ]);
    }

    public function marcarContactoEmergencia(string $vinculoId): void
    {
        abort_unless(auth()->user()->can('familiares.editar'), 403);

        ResidenteContacto::where('cod_residente', $this->adultoSeleccionado)->update(['contacto_emergencia' => false]);
        ResidenteContacto::where('cod_residente_contacto', $vinculoId)->update(['contacto_emergencia' => true, 'estado' => 'ACTIVO']);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Contacto de emergencia actualizado',
            'text' => 'La persona quedó registrada como contacto de emergencia del residente.',
        ]);
    }

    public function desactivarVinculo(string $vinculoId): void
    {
        abort_unless(auth()->user()->can('familiares.anular'), 403);

        ResidenteContacto::where('cod_residente_contacto', $vinculoId)
            ->where('cod_residente', $this->adultoSeleccionado)
            ->update(['estado' => 'INACTIVO']);

        $this->detalleVinculo = null;
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Vínculo desactivado',
            'text' => 'El vínculo no fue eliminado; quedó conservado en el historial institucional.',
        ]);
    }

    public function activarVinculo(string $vinculoId): void
    {
        abort_unless(auth()->user()->can('familiares.editar'), 403);

        ResidenteContacto::where('cod_residente_contacto', $vinculoId)
            ->where('cod_residente', $this->adultoSeleccionado)
            ->update(['estado' => 'ACTIVO']);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Vínculo reactivado',
            'text' => 'La persona vuelve a formar parte activa de la red de apoyo.',
        ]);
    }

    public function render()
    {
        $adulto = $this->adultoActual();
        $familiares = $adulto ? $this->familiaresColeccion() : collect();

        return view('livewire.residentes.red-apoyo-panel', [
            'adultos' => $this->adultosDisponibles(),
            'adulto' => $adulto,
            'familiares' => $familiares,
            'gruposFamiliares' => $this->agruparFamiliares($familiares),
            'personasListado' => $this->personasListado($familiares),
            'metricas' => $this->metricas($adulto, $familiares),
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
        $requiereNuevo = ! $this->modoEdicion && empty($this->form['cod_contacto']);

        return [
            'adultoSeleccionado' => ['required', 'exists:residentes,cod_residente'],
            'form.cod_contacto' => ['nullable', 'exists:contactos,cod_contacto'],
            'form.nombres' => [$requiereNuevo ? 'required' : 'nullable', 'string', 'max:100'],
            'form.ap_paterno' => [$requiereNuevo ? 'required' : 'nullable', 'string', 'max:80'],
            'form.ap_materno' => ['nullable', 'string', 'max:80'],
            'form.correo' => ['nullable', 'email', 'max:120'],
            'form.telefono' => ['nullable'],
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
            'adultoSeleccionado.required' => 'Seleccione un residente para gestionar su red de apoyo.',
            'adultoSeleccionado.exists' => 'El residente seleccionado no se encuentra disponible.',
            'form.nombres.required' => 'Ingrese los nombres del familiar o contacto.',
            'form.ap_paterno.required' => 'Ingrese al menos el apellido paterno.',
            'form.correo.email' => 'Ingrese un correo válido.',
            'form.parentesco_vinculo.required' => 'Seleccione o escriba el parentesco o rol de apoyo.',
            'form.estado.required' => 'Seleccione el estado del vínculo.',
        ];
    }

    private function adultoActual(): ?Residente
    {
        if ($this->adultoSeleccionado === '') return null;
        return Residente::find($this->adultoSeleccionado);
    }

    private function adultosDisponibles(): Collection
    {
        $query = Residente::query();

        if ($this->buscarAdulto !== '') {
            $buscar = '%' . mb_strtolower(trim($this->buscarAdulto)) . '%';
            $query->whereRaw("LOWER(CONCAT(nombres, ' ', apellido_paterno, ' ', COALESCE(apellido_materno, ''))) LIKE ?", [$buscar]);
        }

        return $query
            ->orderBy('apellido_paterno')
            ->limit(15)
            ->get()
            ->map(fn (Residente $r) => [
                'cod_am' => (string) $r->cod_residente,
                'nombre' => $r->nombre_completo,
                'edad' => ($r->fecha_nacimiento ? Carbon::parse($r->fecha_nacimiento)->age : '-') . ' años',
                'estado' => $r->estado ?? 'ACTIVO',
            ]);
    }

    private function familiaresColeccion(): Collection
    {
        if ($this->adultoSeleccionado === '') {
            return collect();
        }

        return DB::table('residentes_contactos as rc')
            ->join('contactos as c', 'rc.cod_contacto', '=', 'c.cod_contacto')
            ->where('rc.cod_residente', $this->adultoSeleccionado)
            ->select([
                'rc.cod_residente_contacto as vinculo_id',
                'rc.cod_contacto as cod_fam',
                'rc.parentesco as parentesco_vinculo',
                'rc.responsable_principal as es_responsable',
                'rc.contacto_emergencia as es_contacto_emergencia',
                'rc.estado as estado_vinculo',
                'rc.observacion as observaciones_vinculo',
                'c.nombres',
                'c.apellido_paterno as ap_paterno',
                'c.apellido_materno as ap_materno',
                'c.correo',
                'c.celular as telefono',
                'c.direccion',
                'c.estado as usuario_estado',
            ])
            ->orderByDesc('rc.responsable_principal')
            ->orderBy('c.apellido_paterno')
            ->get();
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
            $parentesco = Str::upper(Str::ascii((string) $f->parentesco_vinculo));
            $grupo = match (true) {
                str_contains($parentesco, 'CONYUGE'), str_contains($parentesco, 'ESPOS') => 'conyuge',
                str_contains($parentesco, 'NIET') => 'nietos',
                str_contains($parentesco, 'HIJ') => 'hijos',
                str_contains($parentesco, 'HERMAN') => 'hermanos',
                str_contains($parentesco, 'SOBRIN') => 'sobrinos',
                default => 'otros',
            };
            $grupos[$grupo][] = $this->presentarFamiliar($f);
        }

        return $grupos;
    }

    private function personasListado(Collection $familiares): Collection
    {
        if ($this->filtroEstado !== '') {
            $familiares = $familiares->where('estado_vinculo', $this->filtroEstado);
        }
        if ($this->buscarPersona !== '') {
            $b = mb_strtolower(trim($this->buscarPersona));
            $familiares = $familiares->filter(fn ($f) => str_contains(mb_strtolower("{$f->nombres} {$f->ap_paterno} {$f->ap_materno}"), $b));
        }
        if ($this->filtroTipo === 'responsable') {
            $familiares = $familiares->where('es_responsable', true);
        } elseif ($this->filtroTipo === 'emergencia') {
            $familiares = $familiares->where('es_contacto_emergencia', true);
        } elseif ($this->filtroTipo === 'incompleto') {
            $familiares = $familiares->filter(fn ($f) => blank($f->telefono) || blank($f->correo));
        }

        return $familiares->map(fn ($f) => $this->presentarFamiliar($f))->values();
    }

    private function presentarFamiliar(object $f): array
    {
        $nombre = trim("{$f->nombres} {$f->ap_paterno} {$f->ap_materno}");

        return [
            'tipo' => 'FAMILIAR',
            'vinculo_id' => (string) $f->vinculo_id,
            'cod_fam' => (string) $f->cod_fam,
            'nombre' => $nombre,
            'iniciales' => Str::upper(mb_substr((string) $f->nombres, 0, 1) . mb_substr((string) $f->ap_paterno, 0, 1)),
            'correo' => $f->correo ?: 'No registrado',
            'celular' => $f->telefono ?: 'No registrado',
            'parentesco' => $f->parentesco_vinculo ?: 'Contacto',
            'responsable' => (bool) $f->es_responsable,
            'emergencia' => (bool) $f->es_contacto_emergencia,
            'estado' => $f->estado_vinculo ?: 'INACTIVO',
            'estado_badge' => $f->estado_vinculo ?: 'INACTIVO',
            'actualizado' => 'Sin fecha registrada',
        ];
    }

    private function metricas(?Residente $adulto, Collection $familiares): array
    {
        $responsable = $familiares->firstWhere('es_responsable', true);

        return [
            'total' => $familiares->count(),
            'responsables' => $familiares->where('es_responsable', true)->count(),
            'activos' => $familiares->where('estado_vinculo', 'ACTIVO')->count(),
            'inactivos' => $familiares->where('estado_vinculo', '!=', 'ACTIVO')->count(),
            'familiares' => $familiares->count(),
            'emergencias' => $familiares->where('es_contacto_emergencia', true)->count(),
            'responsable' => $responsable
                ? trim("{$responsable->nombres} {$responsable->ap_paterno} {$responsable->ap_materno}")
                : 'No definido',
        ];
    }

    private function familiaresDisponibles(): Collection
    {
        return Contacto::where('estado', 'ACTIVO')
            ->orderBy('apellido_paterno')
            ->get()
            ->map(fn ($c) => [
                'cod_fam' => $c->cod_contacto,
                'nombre' => trim("{$c->nombres} {$c->apellido_paterno} {$c->apellido_materno}"),
                'parentesco' => 'Contacto',
            ]);
    }

    private function resolverContacto(): Contacto
    {
        if (! empty($this->form['cod_contacto'])) {
            $contacto = Contacto::findOrFail($this->form['cod_contacto']);
            $contacto->update([
                'nombres' => $this->form['nombres'] ?: $contacto->nombres,
                'apellido_paterno' => $this->form['ap_paterno'] ?: $contacto->apellido_paterno,
                'apellido_materno' => $this->form['ap_materno'] ?: null,
                'celular' => $this->form['telefono'] ?: null,
                'correo' => $this->form['correo'] ?: null,
                'direccion' => $this->form['direccion'] ?: null,
            ]);
            return $contacto;
        }

        return Contacto::create([
            'cod_contacto' => 'CON_' . strtoupper(Str::random(10)),
            'nombres' => $this->form['nombres'],
            'apellido_paterno' => $this->form['ap_paterno'],
            'apellido_materno' => $this->form['ap_materno'] ?: null,
            'celular' => $this->form['telefono'] ?: null,
            'correo' => $this->form['correo'] ?: null,
            'direccion' => $this->form['direccion'] ?: null,
            'estado' => 'ACTIVO',
        ]);
    }

    private function detalleAdulto(Residente $adulto): array
    {
        return [
            'tipo' => 'adulto',
            'nombre_completo' => $adulto->nombre_completo,
            'parentesco' => 'Nodo central',
            'rol' => 'Residente',
            'celular' => $adulto->celular ?: $adulto->telefono ?: 'No registrado',
            'correo' => 'No aplica',
            'direccion' => $adulto->direccion ?: 'No registrada',
            'edad' => $adulto->fecha_nacimiento ? Carbon::parse($adulto->fecha_nacimiento)->age . ' años' : 'Edad no registrada',
            'estado' => $adulto->estado ?? 'ACTIVO',
            'estado_badge' => $adulto->estado ?? 'ACTIVO',
            'observacion' => $adulto->observacion ?: 'Sin observación registrada',
            'responsable_principal' => false,
            'contacto_emergencia' => false,
        ];
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->vinculoId = null;
        $this->modoEdicion = false;
        $this->form = [
            'cod_contacto' => '',
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
}
