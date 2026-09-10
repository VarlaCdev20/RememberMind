<?php

namespace App\Livewire\Cuidados;

use App\Models\TurnoEnfermeria;
use Livewire\Component;

class TurnosEnfermeriaPanel extends Component
{
    public bool   $modalTurno    = false;
    public ?int   $editandoId    = null;
    public string $nombre        = '';
    public string $horaInicio    = '';
    public string $horaFin       = '';
    public string $orden         = '';
    public string $estado        = 'ACTIVO';
    public string $observacion   = '';

    public function abrirCrear(): void
    {
        $this->reset('editandoId','nombre','horaInicio','horaFin','orden','estado','observacion');
        $this->estado = 'ACTIVO';
        $this->modalTurno = true;
    }

    public function abrirEditar(int $id): void
    {
        $t = TurnoEnfermeria::findOrFail($id);
        $this->editandoId  = $id;
        $this->nombre      = $t->nombre;
        $this->horaInicio  = substr($t->hora_inicio, 0, 5);
        $this->horaFin     = substr($t->hora_fin, 0, 5);
        $this->orden       = (string) $t->orden;
        $this->estado      = $t->estado;
        $this->observacion = $t->observacion ?? '';
        $this->resetValidation();
        $this->modalTurno  = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre'     => 'required|string|max:50',
            'horaInicio' => 'required|date_format:H:i',
            'horaFin'    => 'required|date_format:H:i',
            'orden'      => 'required|integer|min:1|max:10',
            'estado'     => 'required|in:ACTIVO,INACTIVO',
        ], [
            'nombre.required'     => 'El nombre es obligatorio.',
            'horaInicio.required' => 'La hora de inicio es obligatoria.',
            'horaFin.required'    => 'La hora de fin es obligatoria.',
            'orden.required'      => 'El orden es obligatorio.',
        ]);

        $datos = [
            'nombre'      => strtoupper(trim($this->nombre)),
            'hora_inicio' => $this->horaInicio . ':00',
            'hora_fin'    => $this->horaFin . ':00',
            'orden'       => (int) $this->orden,
            'estado'      => $this->estado,
            'observacion' => $this->observacion ?: null,
        ];

        if ($this->editandoId) {
            TurnoEnfermeria::findOrFail($this->editandoId)->update($datos);
            $msg = 'Turno actualizado correctamente.';
        } else {
            TurnoEnfermeria::create($datos);
            $msg = 'Turno registrado correctamente.';
        }

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
            'turnos' => TurnoEnfermeria::orderBy('orden')->get(),
        ])->layout('layouts.sistema');
    }
}
