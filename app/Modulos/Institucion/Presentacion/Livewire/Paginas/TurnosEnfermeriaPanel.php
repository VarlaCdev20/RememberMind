<?php

namespace App\Modulos\Institucion\Presentacion\Livewire\Paginas;

use App\Models\Turno;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarTurnosInstitucionales;
use Livewire\Component;

class TurnosEnfermeriaPanel extends Component
{
    public bool $modalTurno = false;

    public ?string $editandoId = null;

    public string $nombre = '';

    public string $horaInicio = '';

    public string $horaFin = '';

    public string $orden = '';

    public string $estado = 'ACTIVO';

    public string $observacion = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('turnos.ver'), 403);
    }

    public function abrirCrear(): void
    {
        abort_unless(auth()->user()?->can('turnos.gestionar'), 403);
        $this->reset('editandoId', 'nombre', 'horaInicio', 'horaFin', 'orden', 'estado', 'observacion');
        $this->estado = 'ACTIVO';
        $this->modalTurno = true;
    }

    public function abrirEditar(string $id): void
    {
        abort_unless(auth()->user()?->can('turnos.gestionar'), 403);
        $t = Turno::findOrFail($id);
        $this->editandoId = $id;
        $this->nombre = $t->nombre;
        $this->horaInicio = substr($t->hora_inicio, 0, 5);
        $this->horaFin = substr($t->hora_fin, 0, 5);
        $this->orden = (string) $t->orden;
        $this->estado = $t->estado;
        $this->observacion = $t->observacion ?? '';
        $this->resetValidation();
        $this->modalTurno = true;
    }

    public function guardar(): void
    {
        $msg = app(GestionarTurnosInstitucionales::class)->guardar(auth()->user(), [
            'editandoId' => $this->editandoId,
            'nombre' => $this->nombre,
            'horaInicio' => $this->horaInicio,
            'horaFin' => $this->horaFin,
            'orden' => $this->orden,
            'estado' => $this->estado,
            'observacion' => $this->observacion,
        ]);

        $this->modalTurno = false;
        $this->dispatch('swal', ['icon' => 'success', 'title' => $msg]);
    }

    public function cerrarModales(): void
    {
        $this->modalTurno = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.cuidados.turnos-enfermeria-panel', [
            'turnos' => Turno::orderBy('orden')->get(),
        ])->layout('layouts.sistema');
    }
}
