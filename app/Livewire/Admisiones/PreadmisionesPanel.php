<?php

namespace App\Livewire\Admisiones;

use App\Models\Preadmision;
use Livewire\Component;

class PreadmisionesPanel extends Component
{
    public string $estado = '';

    public function render()
    {
        abort_unless(auth()->user()?->can('preadmisiones.ver'), 403);
        $consulta = Preadmision::query()->when($this->estado !== '', fn ($q) => $q->where('estado', $this->estado))->latest('fecha_solicitud');
        return view('livewire.admisiones.preadmisiones-panel', ['preadmisiones' => $consulta->limit(30)->get()]);
    }
}
