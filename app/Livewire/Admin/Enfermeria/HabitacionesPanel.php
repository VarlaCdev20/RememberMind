<?php

namespace App\Livewire\Admin\Enfermeria;

use App\Models\Habitacion;
use App\Models\Cama;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Facades\CauserResolver;

class HabitacionesPanel extends Component
{
    use WithPagination;

    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $search        = '';
    public string $filtroTipo    = '';
    public string $filtroEstado  = '';

    // ── Modales ───────────────────────────────────────────────────────────────
    public bool $modalHabitacion = false;
    public bool $modalCama       = false;
    public bool $modalDetalle    = false;

    // ── Formulario habitación ──────────────────────────────────────────────────
    public ?string $editandoHabitacionId = null;
    public string $codigo        = '';
    public string $nombre        = '';
    public string $tipoHabitacion= '';
    public string $ubicacion     = '';
    public string $capacidad     = '';
    public string $estadoHab     = 'DISPONIBLE';
    public string $observacion   = '';

    // ── Formulario cama ────────────────────────────────────────────────────────
    public ?string $editandoCamaId     = null;
    public ?string $habitacionParaCama = null;
    public string $codigoCama         = '';
    public string $estadoCama         = 'DISPONIBLE';
    public string $observacionCama    = '';

    // ── Detalle ────────────────────────────────────────────────────────────────
    public ?string $detalleId = null;

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingFiltroTipo(): void   { $this->resetPage(); }
    public function updatingFiltroEstado(): void { $this->resetPage(); }

    // ── CRUD Habitación ────────────────────────────────────────────────────────

    public function abrirCrearHabitacion(): void
    {
        $this->resetHabitacion();
        $this->modalHabitacion = true;
    }

    public function abrirEditarHabitacion(string $id): void
    {
        $h = Habitacion::findOrFail($id);
        $this->editandoHabitacionId = $id;
        $this->codigo         = $h->codigo;
        $this->nombre         = $h->nombre ?? '';
        $this->tipoHabitacion = $h->tipo_habitacion;
        $this->ubicacion      = $h->ubicacion ?? '';
        $this->capacidad      = (string) $h->capacidad;
        $this->estadoHab      = $h->estado;
        $this->observacion    = $h->observacion ?? '';
        $this->resetValidation();
        $this->modalHabitacion = true;
    }

    public function guardarHabitacion(): void
    {
        $this->validate([
            'codigo'         => 'required|string|max:20',
            'tipoHabitacion' => 'required|in:INDIVIDUAL,COMPARTIDA,UCI,OBSERVACION',
            'capacidad'      => 'required|integer|min:1|max:50',
            'estadoHab'      => 'required|in:DISPONIBLE,OCUPADA,MANTENIMIENTO,BLOQUEADA',
        ], [
            'codigo.required'         => 'El código es obligatorio.',
            'tipoHabitacion.required' => 'Seleccione el tipo de habitación.',
            'tipoHabitacion.in'       => 'Tipo de habitación no válido.',
            'capacidad.required'      => 'La capacidad es obligatoria.',
            'estadoHab.required'      => 'El estado es obligatorio.',
        ]);

        $datos = [
            'codigo'          => strtoupper(trim($this->codigo)),
            'nombre'          => $this->nombre ?: null,
            'tipo_habitacion' => $this->tipoHabitacion,
            'ubicacion'       => $this->ubicacion ?: null,
            'capacidad'       => (int) $this->capacidad,
            'estado'          => $this->estadoHab,
            'observacion'     => $this->observacion ?: null,
        ];

        if ($this->editandoHabitacionId) {
            Habitacion::findOrFail($this->editandoHabitacionId)->update($datos);
            $msg = 'Habitación actualizada correctamente.';
        } else {
            Habitacion::create($datos);
            $msg = 'Habitación registrada correctamente.';
        }

        $this->modalHabitacion = false;
        $this->resetHabitacion();
        $this->dispatch('swal', ['icon' => 'success', 'title' => $msg]);
    }

    public function eliminarHabitacion(string $id): void
    {
        $h = Habitacion::withCount('camas')->findOrFail($id);
        if ($h->camas_count > 0) {
            $this->dispatch('swal', ['icon' => 'error',
                'title' => 'No se puede eliminar', 'text' => 'La habitación tiene camas registradas.']);
            return;
        }
        $h->delete();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Habitación eliminada.']);
    }

    // ── CRUD Cama ──────────────────────────────────────────────────────────────

    public function abrirCrearCama(string $habitacionId): void
    {
        $this->resetCama();
        $this->habitacionParaCama = $habitacionId;
        $this->modalCama = true;
    }

    public function guardarCama(): void
    {
        $this->validate([
            'codigoCama'       => 'required|string|max:20',
            'estadoCama'       => 'required|in:DISPONIBLE,OCUPADA,MANTENIMIENTO,BLOQUEADA',
            'habitacionParaCama'=> 'required|exists:habitaciones,cod_habitacion',
        ], [
            'codigoCama.required' => 'El código de cama es obligatorio.',
            'estadoCama.required' => 'El estado es obligatorio.',
        ]);

        Cama::create([
            'cod_habitacion' => $this->habitacionParaCama,
            'codigo'         => strtoupper(trim($this->codigoCama)),
            'estado'         => $this->estadoCama,
            'observacion'    => $this->observacionCama ?: null,
        ]);

        $this->modalCama = false;
        $this->resetCama();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Cama registrada correctamente.']);
    }

    public function cerrarModales(): void
    {
        $this->modalHabitacion = false;
        $this->modalCama       = false;
        $this->modalDetalle    = false;
        $this->resetHabitacion();
        $this->resetCama();
        $this->resetValidation();
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function resetHabitacion(): void
    {
        $this->editandoHabitacionId = null;
        $this->codigo         = '';
        $this->nombre         = '';
        $this->tipoHabitacion = '';
        $this->ubicacion      = '';
        $this->capacidad      = '';
        $this->estadoHab      = 'DISPONIBLE';
        $this->observacion    = '';
        $this->resetValidation();
    }

    private function resetCama(): void
    {
        $this->editandoCamaId     = null;
        $this->habitacionParaCama = null;
        $this->codigoCama         = '';
        $this->estadoCama         = 'DISPONIBLE';
        $this->observacionCama    = '';
        $this->resetValidation();
    }

    private function getStats(): array
    {
        return [
            'total'         => Habitacion::count(),
            'disponibles'   => Habitacion::where('estado', 'DISPONIBLE')->count(),
            'ocupadas'      => Habitacion::where('estado', 'OCUPADA')->count(),
            'mantenimiento' => Habitacion::where('estado', 'MANTENIMIENTO')->count(),
            'camas_total'   => Cama::count(),
            'camas_libres'  => Cama::where('estado', 'DISPONIBLE')->count(),
        ];
    }

    public function render()
    {
        $habitaciones = Habitacion::withCount(['camas', 'camasDisponibles as camas_disponibles_count'])
            ->when($this->search, fn($q) =>
                $q->where('codigo', 'ilike', '%' . $this->search . '%')
                  ->orWhere('nombre', 'ilike', '%' . $this->search . '%')
            )
            ->when($this->filtroTipo,   fn($q) => $q->where('tipo_habitacion', $this->filtroTipo))
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->orderBy('codigo')
            ->paginate(10);

        return view('livewire.admin.enfermeria.habitaciones-panel', [
            'habitaciones' => $habitaciones,
            'stats'        => $this->getStats(),
        ])->layout('layouts.sistema');
    }
}
