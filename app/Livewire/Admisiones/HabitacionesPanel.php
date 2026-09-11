<?php

namespace App\Livewire\Admisiones;

use App\Models\AsignacionAdultoMayor;
use App\Models\Cama;
use App\Models\Habitacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class HabitacionesPanel extends Component
{
    use WithPagination;

    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $search = '';

    public string $filtroTipo = '';

    public string $filtroEstado = '';

    // ── Modales ───────────────────────────────────────────────────────────────
    public bool $modalHabitacion = false;

    public bool $modalCama = false;

    public bool $modalDetalle = false;

    // ── Formulario habitación ──────────────────────────────────────────────────
    public ?string $editandoHabitacionId = null;

    public string $codigo = '';

    public string $nombre = '';

    public string $tipoHabitacion = '';

    public string $ubicacion = '';

    public string $capacidad = '';

    public string $estadoHab = 'DISPONIBLE';

    public string $observacion = '';

    // ── Formulario cama ────────────────────────────────────────────────────────
    public ?string $editandoCamaId = null;

    public ?string $habitacionParaCama = null;

    public string $codigoCama = '';

    public string $estadoCama = 'DISPONIBLE';

    public string $observacionCama = '';

    // ── Detalle ────────────────────────────────────────────────────────────────
    public ?string $detalleId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('habitaciones.ver'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    // ── CRUD Habitación ────────────────────────────────────────────────────────

    public function abrirCrearHabitacion(): void
    {
        abort_unless(auth()->user()?->can('habitaciones.crear'), 403);
        $this->resetHabitacion();
        $this->modalHabitacion = true;
    }

    public function abrirEditarHabitacion(string $id): void
    {
        abort_unless(auth()->user()?->can('habitaciones.editar'), 403);
        $h = Habitacion::findOrFail($id);
        $this->editandoHabitacionId = $id;
        $this->codigo = $h->codigo;
        $this->nombre = $h->nombre ?? '';
        $this->tipoHabitacion = $h->tipo_habitacion;
        $this->ubicacion = $h->ubicacion ?? '';
        $this->capacidad = (string) $h->capacidad;
        $this->estadoHab = $h->estado === 'OCUPADA' ? 'DISPONIBLE' : $h->estado;
        $this->observacion = $h->observacion ?? '';
        $this->resetValidation();
        $this->modalHabitacion = true;
    }

    public function guardarHabitacion(): void
    {
        abort_unless(auth()->user()?->can($this->editandoHabitacionId ? 'habitaciones.editar' : 'habitaciones.crear'), 403);
        $this->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('habitaciones', 'codigo')->ignore($this->editandoHabitacionId, 'cod_habitacion')],
            'nombre' => 'required|string|max:100',
            'tipoHabitacion' => 'required|in:INDIVIDUAL,COMPARTIDA,UCI,OBSERVACION',
            'capacidad' => 'required|integer|min:1|max:50',
            'estadoHab' => 'required|in:DISPONIBLE,MANTENIMIENTO,BLOQUEADA',
            'observacion' => 'nullable|string|max:500',
        ], [
            'codigo.required' => 'Ingrese el número o referencia de la habitación.',
            'codigo.unique' => 'Ya existe una habitación con esta referencia.',
            'nombre.required' => 'Ingrese un nombre claro para la habitación.',
            'tipoHabitacion.required' => 'Seleccione el tipo de habitación.',
            'tipoHabitacion.in' => 'Tipo de habitación no válido.',
            'capacidad.required' => 'La capacidad es obligatoria.',
            'estadoHab.required' => 'El estado es obligatorio.',
        ]);

        $habitacionActual = $this->editandoHabitacionId ? Habitacion::withCount(['camas', 'asignacionesActivas'])->findOrFail($this->editandoHabitacionId) : null;
        if ($habitacionActual && (int) $this->capacidad < $habitacionActual->camas_count) {
            throw ValidationException::withMessages(['capacidad' => "Ya existen {$habitacionActual->camas_count} camas registradas. Retire camas sin uso antes de reducir la capacidad."]);
        }
        if ($habitacionActual && in_array($this->estadoHab, ['MANTENIMIENTO', 'BLOQUEADA'], true) && $habitacionActual->asignaciones_activas_count > 0) {
            throw ValidationException::withMessages(['estadoHab' => 'No puede bloquear la habitación ni enviarla a mantenimiento mientras tenga residentes asignados.']);
        }

        $datos = [
            'codigo' => strtoupper(trim($this->codigo)),
            'nombre' => $this->nombre ?: null,
            'tipo_habitacion' => $this->tipoHabitacion,
            'ubicacion' => $this->ubicacion ?: null,
            'capacidad' => (int) $this->capacidad,
            'estado' => $this->estadoHab,
            'observacion' => $this->observacion ?: null,
        ];

        if ($this->editandoHabitacionId) {
            $guardada = Habitacion::findOrFail($this->editandoHabitacionId);
            $guardada->update($datos);
            $msg = 'Habitación actualizada correctamente.';
        } else {
            $guardada = Habitacion::create($datos);
            $msg = 'Habitación registrada correctamente.';
        }

        if ($this->estadoHab === 'DISPONIBLE' && $guardada->camas()->exists()) {
            $guardada->updateQuietly(['estado' => $guardada->camasDisponibles()->exists() ? 'DISPONIBLE' : 'OCUPADA']);
        }

        $this->modalHabitacion = false;
        $this->resetHabitacion();
        $this->dispatch('swal', ['icon' => 'success', 'title' => $msg]);
    }

    public function eliminarHabitacion(string $id): void
    {
        abort_unless(auth()->user()?->can('habitaciones.editar'), 403);
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
        abort_unless(auth()->user()?->can('camas.crear'), 403);
        $habitacion = Habitacion::withCount('camas')->findOrFail($habitacionId);
        if ($habitacion->camas_count >= $habitacion->capacidad) {
            $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Capacidad completa', 'text' => 'Esta habitación ya tiene registradas todas las camas permitidas.']);
        }
        $this->resetCama();
        $this->habitacionParaCama = $habitacionId;
        $this->estadoCama = in_array($habitacion->estado, ['MANTENIMIENTO', 'BLOQUEADA'], true) ? 'BLOQUEADA' : 'DISPONIBLE';
        $this->modalCama = true;
    }

    public function verCamas(string $id): void
    {
        abort_unless(auth()->user()?->can('camas.ver'), 403);
        $this->detalleId = $id;
        $this->modalDetalle = true;
    }

    public function editarCama(string $id): void
    {
        abort_unless(auth()->user()?->can('camas.editar'), 403);
        $cama = Cama::with('asignacionesActivas')->findOrFail($id);
        $this->editandoCamaId = $id;
        $this->habitacionParaCama = $cama->cod_habitacion;
        $this->codigoCama = $cama->codigo;
        $this->estadoCama = $cama->asignacionesActivas->isNotEmpty() ? 'OCUPADA' : $cama->estado;
        $this->observacionCama = $cama->observacion ?? '';
        $this->modalDetalle = false;
        $this->modalCama = true;
    }

    public function guardarCama(): void
    {
        abort_unless(auth()->user()?->can($this->editandoCamaId ? 'camas.editar' : 'camas.crear'), 403);
        $this->validate([
            'codigoCama' => ['required', 'string', 'max:20', Rule::unique('camas', 'codigo')->ignore($this->editandoCamaId, 'cod_cama')],
            'estadoCama' => 'required|in:DISPONIBLE,OCUPADA,MANTENIMIENTO,BLOQUEADA',
            'habitacionParaCama' => 'required|exists:habitaciones,cod_habitacion',
        ], [
            'codigoCama.required' => 'Ingrese el número o referencia visible de la cama.',
            'codigoCama.unique' => 'Ya existe una cama con esta referencia.',
            'estadoCama.required' => 'El estado es obligatorio.',
        ]);

        DB::transaction(function (): void {
            $habitacion = Habitacion::lockForUpdate()->findOrFail($this->habitacionParaCama);
            if (! $this->editandoCamaId && $habitacion->camas()->count() >= $habitacion->capacidad) {
                throw ValidationException::withMessages(['codigoCama' => 'La habitación alcanzó su capacidad y no admite otra cama.']);
            }
            $cama = $this->editandoCamaId ? Cama::lockForUpdate()->findOrFail($this->editandoCamaId) : new Cama;
            $ocupada = $cama->exists && $cama->asignacionesActivas()->exists();
            if ($ocupada && $this->estadoCama !== 'OCUPADA') {
                throw ValidationException::withMessages(['estadoCama' => 'La cama tiene un residente. Finalice o traslade su asignación antes de cambiarla.']);
            }
            if (! $ocupada && $this->estadoCama === 'OCUPADA') {
                throw ValidationException::withMessages(['estadoCama' => 'El estado ocupado se genera al asignar un residente.']);
            }
            if (in_array($habitacion->estado, ['MANTENIMIENTO', 'BLOQUEADA'], true) && $this->estadoCama === 'DISPONIBLE') {
                throw ValidationException::withMessages(['estadoCama' => 'La cama no puede quedar disponible mientras la habitación esté fuera de servicio.']);
            }
            $cama->fill(['cod_habitacion' => $habitacion->cod_habitacion, 'codigo' => strtoupper(trim($this->codigoCama)), 'estado' => $this->estadoCama, 'observacion' => $this->observacionCama ?: null])->save();
            if (! in_array($habitacion->estado, ['MANTENIMIENTO', 'BLOQUEADA'], true)) {
                $habitacion->updateQuietly(['estado' => $habitacion->camasDisponibles()->exists() ? 'DISPONIBLE' : 'OCUPADA']);
            }
        });

        $mensaje = $this->editandoCamaId ? 'Disponibilidad de cama actualizada.' : 'Cama registrada correctamente.';
        $this->modalCama = false;
        $this->resetCama();
        $this->dispatch('swal', ['icon' => 'success', 'title' => $mensaje]);
    }

    public function cerrarModales(): void
    {
        $this->modalHabitacion = false;
        $this->modalCama = false;
        $this->modalDetalle = false;
        $this->resetHabitacion();
        $this->resetCama();
        $this->resetValidation();
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function resetHabitacion(): void
    {
        $this->editandoHabitacionId = null;
        $this->codigo = '';
        $this->nombre = '';
        $this->tipoHabitacion = '';
        $this->ubicacion = '';
        $this->capacidad = '';
        $this->estadoHab = 'DISPONIBLE';
        $this->observacion = '';
        $this->resetValidation();
    }

    private function resetCama(): void
    {
        $this->editandoCamaId = null;
        $this->habitacionParaCama = null;
        $this->codigoCama = '';
        $this->estadoCama = 'DISPONIBLE';
        $this->observacionCama = '';
        $this->resetValidation();
    }

    private function getStats(): array
    {
        return [
            'total' => Habitacion::count(),
            'disponibles' => Habitacion::where('estado', 'DISPONIBLE')->count(),
            'ocupadas' => Habitacion::where('estado', 'OCUPADA')->count(),
            'mantenimiento' => Habitacion::where('estado', 'MANTENIMIENTO')->count(),
            'camas_total' => Cama::count(),
            'camas_libres' => Cama::disponibles()->whereHas('habitacion', fn ($q) => $q->whereNotIn('estado', ['MANTENIMIENTO', 'BLOQUEADA']))->count(),
            'capacidad_total' => (int) Habitacion::sum('capacidad'),
            'camas_ocupadas' => AsignacionAdultoMayor::whereIn('estado', ['ACTIVO', 'ACTIVA'])->distinct()->count('cod_cama'),
            'fuera_servicio' => Cama::whereIn('estado', ['MANTENIMIENTO', 'BLOQUEADA'])->count(),
        ];
    }

    public function render()
    {
        $habitaciones = Habitacion::withCount([
            'camas', 'camasDisponibles as camas_disponibles_count',
            'asignacionesActivas as camas_ocupadas_count',
            'camas as camas_mantenimiento_count' => fn ($q) => $q->where('estado', 'MANTENIMIENTO'),
            'camas as camas_bloqueadas_count' => fn ($q) => $q->where('estado', 'BLOQUEADA'),
        ])
            ->when($this->search, fn ($q) => $q->where(fn ($busqueda) => $busqueda
                ->where('codigo', 'like', '%'.$this->search.'%')
                ->orWhere('nombre', 'like', '%'.$this->search.'%')
                ->orWhere('ubicacion', 'like', '%'.$this->search.'%')
            ))
            ->when($this->filtroTipo, fn ($q) => $q->where('tipo_habitacion', $this->filtroTipo))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->orderBy('codigo')
            ->paginate(10);

        return view('livewire.admisiones.habitaciones-panel', [
            'habitaciones' => $habitaciones,
            'stats' => $this->getStats(),
            'detalleHabitacion' => $this->modalDetalle ? Habitacion::with(['camas' => fn ($q) => $q->with('asignacionesActivas.adultoMayor')->orderBy('codigo')])->withCount(['camas', 'camasDisponibles as camas_disponibles_count', 'asignacionesActivas as camas_ocupadas_count'])->find($this->detalleId) : null,
            'habitacionFormularioCama' => $this->habitacionParaCama ? Habitacion::find($this->habitacionParaCama) : null,
        ])->layout('layouts.sistema');
    }
}
