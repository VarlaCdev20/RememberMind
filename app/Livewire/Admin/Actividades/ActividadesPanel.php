<?php

namespace App\Livewire\Admin\Actividades;

use App\Models\ActividadAdulto;
use App\Models\AdultoMayor;
use App\Models\TipoActividadAdulto;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class ActividadesPanel extends Component
{
    use WithPagination;

    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $search           = '';
    public string $filtroTipo       = '';
    public string $filtroEstado     = '';
    public string $filtroFechaDesde = '';
    public string $filtroFechaHasta = '';

    // ── Modales ───────────────────────────────────────────────────────────────
    public bool $modalRegistrar = false;
    public bool $modalEditar    = false;
    public bool $modalDetalle   = false;

    // ── Campos del formulario ─────────────────────────────────────────────────
    public string $codAm      = '';
    public string $codTipoAct = '';
    public string $fecha      = '';
    public string $hora       = '';
    public string $obs        = '';
    public string $estado     = 'PROGRAMADA';

    // ── Tracking ──────────────────────────────────────────────────────────────
    public ?string $editandoId = null;
    public ?string $detalleId  = null;

    protected function rules(): array
    {
        return [
            'codAm'      => 'required|exists:adulto_mayor,cod_am',
            'codTipoAct' => 'required|exists:tipo_actividades_adulto,cod_tipo_act',
            'fecha'      => 'required|date',
            'hora'       => 'required',
            'obs'        => 'nullable|string|max:2000',
            'estado'     => 'required|string|max:50',
        ];
    }

    protected function messages(): array
    {
        return [
            'codAm.required'      => 'Seleccione un adulto mayor.',
            'codAm.exists'        => 'El adulto mayor seleccionado no es válido.',
            'codTipoAct.required' => 'Seleccione el tipo de actividad.',
            'codTipoAct.exists'   => 'El tipo de actividad no es válido.',
            'fecha.required'      => 'La fecha es obligatoria.',
            'hora.required'       => 'La hora es obligatoria.',
            'estado.required'     => 'El estado es obligatorio.',
        ];
    }

    public function updatingSearch(): void           { $this->resetPage(); }
    public function updatingFiltroTipo(): void        { $this->resetPage(); }
    public function updatingFiltroEstado(): void      { $this->resetPage(); }
    public function updatingFiltroFechaDesde(): void  { $this->resetPage(); }
    public function updatingFiltroFechaHasta(): void  { $this->resetPage(); }

    // ── Apertura de modales ───────────────────────────────────────────────────
    public function abrirRegistrar(): void
    {
        $this->resetForm();
        $this->fecha  = today()->format('Y-m-d');
        $this->estado = 'PROGRAMADA';
        $this->modalRegistrar = true;
    }

    public function abrirEditar(string $id): void
    {
        $a = ActividadAdulto::findOrFail($id);
        $this->editandoId  = $id;
        $this->codAm       = $a->cod_am;
        $this->codTipoAct  = (string) $a->cod_tipo_act;
        $this->fecha       = $a->fecha->format('Y-m-d');
        $this->hora        = substr($a->hora ?? '', 0, 5);
        $this->obs         = $a->obs ?? '';
        $this->estado      = $a->estado;
        $this->resetValidation();
        $this->modalEditar = true;
    }

    public function abrirDetalle(int $id): void
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
    public function guardarActividad(): void
    {
        $this->validate();
        ActividadAdulto::create([
            'cod_am'       => $this->codAm,
            'cod_tipo_act' => $this->codTipoAct,
            'fecha'        => $this->fecha,
            'hora'         => strlen(trim($this->hora)) === 5 ? $this->hora . ':00' : $this->hora,
            'obs'          => $this->obs ?: null,
            'estado'       => strtoupper(trim($this->estado)),
        ]);
        $this->modalRegistrar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad registrada correctamente.']);
    }

    public function actualizarActividad(): void
    {
        $this->validate();
        $a = ActividadAdulto::findOrFail($this->editandoId);
        $a->update([
            'cod_am'       => $this->codAm,
            'cod_tipo_act' => $this->codTipoAct,
            'fecha'        => $this->fecha,
            'hora'         => strlen(trim($this->hora)) === 5 ? $this->hora . ':00' : $this->hora,
            'obs'          => $this->obs ?: null,
            'estado'       => strtoupper(trim($this->estado)),
        ]);
        $this->modalEditar = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad actualizada correctamente.']);
    }

    public function cancelarActividad(string $id): void
    {
        $a = ActividadAdulto::findOrFail($id);
        $a->update(['estado' => 'CANCELADA']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Actividad cancelada.']);
    }

    public function limpiarFiltros(): void
    {
        $this->search           = '';
        $this->filtroTipo       = '';
        $this->filtroEstado     = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
        $this->resetPage();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function resetForm(): void
    {
        $this->codAm      = '';
        $this->codTipoAct = '';
        $this->fecha      = '';
        $this->hora       = '';
        $this->obs        = '';
        $this->estado     = 'PROGRAMADA';
        $this->editandoId = null;
        $this->detalleId  = null;
        $this->resetValidation();
    }

    private function tablaExiste(): bool
    {
        return Schema::hasTable('actividades_adulto');
    }

    private function getStats(): array
    {
        if (! $this->tablaExiste()) {
            return array_fill_keys(
                ['total', 'programadas', 'realizadas', 'canceladas', 'reprogramadas', 'hoy', 'proximas7', 'adultos_distintos'],
                0
            );
        }
        return [
            'total'            => ActividadAdulto::count(),
            'programadas'      => ActividadAdulto::where('estado', 'PROGRAMADA')->count(),
            'realizadas'       => ActividadAdulto::whereIn('estado', ['COMPLETADA', 'REALIZADA'])->count(),
            'canceladas'       => ActividadAdulto::where('estado', 'CANCELADA')->count(),
            'reprogramadas'    => ActividadAdulto::where('estado', 'REPROGRAMADA')->count(),
            'hoy'              => ActividadAdulto::whereDate('fecha', today())->count(),
            'proximas7'        => ActividadAdulto::where('estado', 'PROGRAMADA')
                                    ->whereBetween('fecha', [today(), today()->addDays(7)])
                                    ->count(),
            'adultos_distintos'=> ActividadAdulto::distinct('cod_am')->count('cod_am'),
        ];
    }

    private function getActividades()
    {
        if (! $this->tablaExiste()) {
            return ActividadAdulto::paginate(12);
        }
        return ActividadAdulto::with(['tipoActividad', 'adultoMayor'])
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres', 'ilike', '%' . $this->search . '%')
                      ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                      ->orWhere('ap_materno', 'ilike', '%' . $this->search . '%')
                )
            )
            ->when($this->filtroTipo,       fn($q) => $q->where('cod_tipo_act', $this->filtroTipo))
            ->when($this->filtroEstado,     fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroFechaDesde, fn($q) => $q->where('fecha', '>=', $this->filtroFechaDesde))
            ->when($this->filtroFechaHasta, fn($q) => $q->where('fecha', '<=', $this->filtroFechaHasta))
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->paginate(12);
    }

    private function getTipos()
    {
        return TipoActividadAdulto::orderBy('nombre')->get();
    }

    private function getAdultos()
    {
        return AdultoMayor::select('cod_am', 'nombres', 'ap_paterno', 'ap_materno')
            ->whereNull('archivado_en')
            ->orderBy('ap_paterno')
            ->orderBy('nombres')
            ->get();
    }

    private function getDetalle(): ?ActividadAdulto
    {
        return $this->detalleId
            ? ActividadAdulto::with(['tipoActividad', 'adultoMayor'])->find($this->detalleId)
            : null;
    }

    public function render()
    {
        return view('livewire.admin.actividades.actividades-panel', [
            'stats'       => $this->getStats(),
            'actividades' => $this->getActividades(),
            'tipos'       => $this->getTipos(),
            'adultos'     => $this->getAdultos(),
            'detalle'     => $this->getDetalle(),
        ])->layout('layouts.sistema');
    }
}
