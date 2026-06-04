<?php

namespace App\Livewire\Admin\PersonalInstitucional;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;

class PersonalInstitucionalPanel extends Component
{
    public $tabSeleccionada = 'todos';
    public $busqueda = '';

    public function render()
    {
        $query = User::with(['personalSalud', 'personalAdmin', 'areaInstitucional', 'roles'])
            ->where(function($q) {
                $q->where('nombres', 'ilike', '%' . $this->busqueda . '%')
                  ->orWhere('ap_paterno', 'ilike', '%' . $this->busqueda . '%')
                  ->orWhere('correo', 'ilike', '%' . $this->busqueda . '%')
                  ->orWhere('cod_usu', 'ilike', '%' . $this->busqueda . '%');
            });

        if ($this->tabSeleccionada === 'salud') {
            $query->has('personalSalud');
        } elseif ($this->tabSeleccionada === 'admin') {
            $query->has('personalAdmin');
        }

        // TODO: In the future we will use the string states. 
        // For now, it might be 1/0 or strings if migrated.
        $usuarios = $query->orderBy('created_at', 'desc')->get();

        $estadisticas = [
            'total' => User::count(),
            'salud' => User::has('personalSalud')->count(),
            'admin' => User::has('personalAdmin')->count(),
            'activos' => User::where('estado', 1)->orWhere('estado', 'ACTIVO')->count(),
        ];

        return view('livewire.admin.personal-institucional.personal-institucional-panel', [
            'usuarios' => $usuarios,
            'estadisticas' => $estadisticas
        ])->layout('layouts.sistema');
    }
}
