<?php

namespace App\Livewire\Admin\Actividades;

use App\Models\TipoActividadAdulto;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class TiposActividadPanel extends Component
{
    use WithPagination;

    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $search    = '';
    public string $filtroUso = '';

    // ── Modales ───────────────────────────────────────────────────────────────
    public bool $modalRegistrar = false;
    public bool $modalEditar    = false;
    public bool $modalDetalle   = false;

    // ── Campos del formulario ─────────────────────────────────────────────────
    public string $tipo        = '';
    public string $descripcion = '';

    // ── Tracking ──────────────────────────────────────────────────────────────
    public ?string $editandoId = null;
    public ?string $detalleId  = null;

    protected function rules(): array
    {
        $editandoId = $this->editandoId;
        return [
            'tipo' => [
                'required', 'string', 'max:50',
                function ($attribute, $value, $fail) use ($editandoId) {
                    $q = TipoActividadAdulto::whereRaw('LOWER(nombre) = LOWER(?)', [trim($value)]);
                    if ($editandoId) {
                        $q->where('cod_tipo_act', '!=', $editandoId);
                    }
                    if ($q->exists()) {
                        $fail('Ya existe un tipo de actividad con ese nombre.');
                    }
                },
            ],
            'descripcion' => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'tipo.required'      => 'El nombre del tipo es obligatorio.',
            'tipo.max'           => 'El nombre no puede superar los 50 caracteres.',
            'descripcion.max'    => 'La descripción no puede superar los 1000 caracteres.',
        ];
    }

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingFiltroUso(): void { $this->resetPage(); }

    public function limpiarFiltros(): void
    {
        $this->search    = '';
        $this->filtroUso = '';
        $this->resetPage();
    }

    // ── Apertura de modales ───────────────────────────────────────────────────
    public function abrirRegistrar(): void
    {
        $this->resetForm();
        $this->modalRegistrar = true;
    }

    public function abrirEditar(string $id): void
    {
        $t = TipoActividadAdulto::findOrFail($id);
        $this->editandoId  = $id;
        $this->tipo        = $t->tipo;
        $this->descripcion = $t->descripcion ?? '';
        $this->resetValidation();
        $this->modalEditar = true;
    }

    public function abrirDetalle(string $id): void
    {
        $this->detalleId    = $id;
        $this->modalDetalle = true;
    }

    public function cerrarModales(): void
    {
        $this->modalRegistrar = false;
        $this->modalEditar    = false;
        $this->modalDetalle   = false;
        $this->resetForm();
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────
    public function guardarTipo(): void
    {
        $this->validate();
        TipoActividadAdulto::create([
            'nombre'      => trim($this->tipo),
            'descripcion' => $this->descripcion ?: null,
        ]);
        $this->modalRegistrar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tipo de actividad registrado correctamente.']);
    }

    public function actualizarTipo(): void
    {
        $this->validate();
        $t = TipoActividadAdulto::findOrFail($this->editandoId);
        $t->update([
            'nombre'      => trim($this->tipo),
            'descripcion' => $this->descripcion ?: null,
        ]);
        $this->modalEditar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tipo de actividad actualizado correctamente.']);
    }

    public function eliminarTipo(string $id): void
    {
        $t = TipoActividadAdulto::withCount('actividades')->findOrFail($id);
        if ($t->actividades_count > 0) {
            $this->dispatch('swal', [
                'icon'  => 'warning',
                'title' => 'No es posible eliminar',
                'text'  => "Este tipo tiene {$t->actividades_count} actividad(es) asociada(s). Para eliminarlo del catálogo, primero reasigne o elimine las actividades vinculadas.",
            ]);
            return;
        }
        $t->delete();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Tipo eliminado del catálogo.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function resetForm(): void
    {
        $this->tipo        = '';
        $this->descripcion = '';
        $this->editandoId  = null;
        $this->detalleId   = null;
        $this->resetValidation();
    }

    private function tablaExiste(): bool
    {
        return Schema::hasTable('tipo_actividades_adulto');
    }

    private function getStats(): array
    {
        $zero = ['total' => 0, 'con_actividades' => 0, 'sin_actividades' => 0,
                 'total_actividades' => 0, 'mas_nombre' => '—', 'mas_count' => 0, 'promedio' => '0.0'];
        if (! $this->tablaExiste()) {
            return $zero;
        }
        $todos    = TipoActividadAdulto::withCount('actividades')->get();
        $total    = $todos->count();
        $totalAct = $todos->sum('actividades_count');
        $mas      = $todos->sortByDesc('actividades_count')->first();
        return [
            'total'            => $total,
            'con_actividades'  => $todos->filter(fn($t) => $t->actividades_count > 0)->count(),
            'sin_actividades'  => $todos->filter(fn($t) => $t->actividades_count === 0)->count(),
            'total_actividades'=> $totalAct,
            'mas_nombre'       => $mas?->tipo ?? '—',
            'mas_count'        => $mas?->actividades_count ?? 0,
            'promedio'         => $total > 0 ? number_format($totalAct / $total, 1) : '0.0',
        ];
    }

    private function getTiposFiltrados()
    {
        if (! $this->tablaExiste()) {
            return TipoActividadAdulto::paginate(12);
        }
        return TipoActividadAdulto::withCount('actividades')
            ->when($this->search, fn($q) =>
                $q->whereLike('nombre', '%' . $this->search . '%')
                  ->orWhereLike('descripcion', '%' . $this->search . '%')
            )
            ->when($this->filtroUso === 'con', fn($q) => $q->has('actividades'))
            ->when($this->filtroUso === 'sin', fn($q) => $q->doesntHave('actividades'))
            ->orderBy('nombre')
            ->paginate(12);
    }

    private function getDetalle(): ?TipoActividadAdulto
    {
        return $this->detalleId
            ? TipoActividadAdulto::withCount('actividades')
                ->with(['actividades' => fn($q) =>
                    $q->with('adultoMayor')->orderByDesc('fecha')->limit(5)
                ])
                ->find($this->detalleId)
            : null;
    }

    public function render()
    {
        return view('livewire.actividades.tipos-actividad-panel', [
            'stats'  => $this->getStats(),
            'tipos'  => $this->getTiposFiltrados(),
            'detalle'=> $this->getDetalle(),
        ])->layout('layouts.sistema');
    }
}
