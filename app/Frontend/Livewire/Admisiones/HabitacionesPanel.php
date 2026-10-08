<?php

namespace App\Frontend\Livewire\Admisiones;

use App\Backend\Modulos\Admisiones\Acciones\GuardarEspacioResidencial;
use App\Models\Cama;
use App\Models\Habitacion;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/** Mantenimiento físico embebido; el directorio vive en el explorador. */
class HabitacionesPanel extends Component
{
    public bool $modalHabitacion = false;

    public bool $modalCama = false;

    #[Locked]
    public ?string $editandoHabitacionId = null;

    #[Locked]
    public ?string $editandoCamaId = null;

    #[Locked]
    public ?string $habitacionParaCama = null;

    public string $codigo = '';

    public string $nombre = '';

    public string $tipo = '';

    public string $piso = '';

    public string $capacidad = '1';

    public string $estado = 'ACTIVA';

    public string $observacion = '';

    #[On('crear-habitacion')]
    public function abrirCrearHabitacion(): void
    {
        Gate::authorize('create', Habitacion::class);
        $this->limpiar();
        $this->modalHabitacion = true;
    }

    #[On('editar-habitacion')]
    public function abrirEditarHabitacion(string $codHabitacion): void
    {
        $habitacion = Habitacion::findOrFail($codHabitacion);
        Gate::authorize('update', $habitacion);
        $this->limpiar();
        $this->editandoHabitacionId = $habitacion->getKey();
        $this->cargar($habitacion);
        $this->piso = $habitacion->piso ?? '';
        $this->nombre = $habitacion->nombre ?? '';
        $this->capacidad = (string) $habitacion->capacidad;
        $this->modalHabitacion = true;
    }

    #[On('crear-cama')]
    public function abrirCrearCama(string $codHabitacion): void
    {
        Gate::authorize('create', Cama::class);
        Habitacion::findOrFail($codHabitacion);
        $this->limpiar();
        $this->habitacionParaCama = $codHabitacion;
        $this->modalCama = true;
    }

    #[On('editar-cama')]
    public function editarCama(string $codCama): void
    {
        $cama = Cama::findOrFail($codCama);
        Gate::authorize('update', $cama);
        $this->limpiar();
        $this->editandoCamaId = $cama->getKey();
        $this->habitacionParaCama = $cama->cod_habitacion;
        $this->cargar($cama);
        $this->modalCama = true;
    }

    public function guardarHabitacion(GuardarEspacioResidencial $guardar): void
    {
        abort_unless($this->modalHabitacion, 403);
        $guardar->habitacion($this->datos() + ['nombre' => $this->nombre, 'piso' => $this->piso, 'capacidad' => $this->capacidad], auth()->user(), $this->editandoHabitacionId);
        $this->terminar('Habitación guardada correctamente.');
    }

    public function guardarCama(GuardarEspacioResidencial $guardar): void
    {
        abort_unless($this->modalCama && $this->habitacionParaCama, 403);
        $guardar->cama($this->datos(), auth()->user(), $this->habitacionParaCama, $this->editandoCamaId);
        $this->terminar('Cama guardada correctamente.');
    }

    public function cerrarModales(): void
    {
        $this->limpiar();
        $this->dispatch('editor-espacios-cerrado');
    }

    private function limpiar(): void
    {
        $this->reset(['modalHabitacion', 'modalCama', 'editandoHabitacionId', 'editandoCamaId', 'habitacionParaCama', 'codigo', 'nombre', 'tipo', 'piso', 'capacidad', 'estado', 'observacion']);
        $this->resetValidation();
    }

    private function cargar(Habitacion|Cama $espacio): void
    {
        $this->codigo = $espacio->codigo;
        $this->tipo = $espacio->tipo ?? '';
        $this->estado = $espacio->estado;
        $this->observacion = $espacio->observacion ?? '';
    }

    private function datos(): array
    {
        return ['codigo' => $this->codigo, 'tipo' => $this->tipo, 'estado' => $this->estado, 'observacion' => $this->observacion];
    }

    private function terminar(string $mensaje): void
    {
        $this->cerrarModales();
        $this->dispatch('espacios-actualizados');
        $this->dispatch('swal', ['icon' => 'success', 'title' => $mensaje]);
    }

    public function render()
    {
        if ($this->modalHabitacion) {
            Gate::authorize($this->editandoHabitacionId ? 'update' : 'create', $this->editandoHabitacionId ? Habitacion::findOrFail($this->editandoHabitacionId) : Habitacion::class);
        }
        if ($this->modalCama) {
            Gate::authorize($this->editandoCamaId ? 'update' : 'create', $this->editandoCamaId ? Cama::findOrFail($this->editandoCamaId) : Cama::class);
        }

        return view('livewire.admisiones.habitaciones-panel', ['habitacionContexto' => $this->modalCama ? Habitacion::withCount('camas')->findOrFail($this->habitacionParaCama) : null]);
    }
}
