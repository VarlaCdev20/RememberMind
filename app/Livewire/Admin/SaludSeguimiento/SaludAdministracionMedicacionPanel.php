<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use App\Models\AdultoMayor;

class SaludAdministracionMedicacionPanel extends Component
{
    public AdultoMayor $adulto;

    public function mount(AdultoMayor $adulto)
    {
        $this->adulto = $adulto;
    }

    public function render()
    {
        return view('livewire.admin.salud-seguimiento.salud-panel-individual', [
            'titulo' => 'Administración de Medicación',
            'icono' => 'ph-prescription'
        ])->layout('layouts.sistema');
    }
}
