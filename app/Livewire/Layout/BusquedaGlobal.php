<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use App\Services\Busqueda\BusquedaGlobalService;
use Illuminate\Support\Facades\Auth;

class BusquedaGlobal extends Component
{
    public $query = '';
    public $resultados = [];
    public $isOpen = false;

    public function updatedQuery()
    {
        if (strlen(trim($this->query)) >= 2) {
            $service = app(BusquedaGlobalService::class);
            $this->resultados = $service->buscar($this->query, Auth::user());
            $this->isOpen = true;
        } else {
            $this->resultados = [];
            $this->isOpen = false;
        }
    }

    public function clear()
    {
        $this->query = '';
        $this->resultados = [];
        $this->isOpen = false;
    }

    public function render()
    {
        return view('livewire.layout.busqueda-global');
    }
}
