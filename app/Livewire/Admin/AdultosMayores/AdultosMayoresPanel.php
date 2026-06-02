<?php

namespace App\Livewire\Admin\AdultosMayores;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Admin\AdultoMayorService;
use App\Models\AdultoMayor;

class AdultosMayoresPanel extends Component
{
    use WithPagination;

    public $buscar = '';
    public $estado = '';
    public $genero = '';
    public $permanencia = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';
    public $ciudad_municipio = '';
    public $rango_edad = '';

    protected $listeners = [
        'adulto-mayor-guardado' => '$refresh',
    ];

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['buscar', 'estado', 'genero', 'permanencia', 'fecha_desde', 'fecha_hasta', 'ciudad_municipio', 'rango_edad'])) {
            $this->resetPage();
        }
    }

    public function crearAdultoMayor()
    {
        $this->dispatch('adulto-mayor-form-abrir');
    }

    public function editarAdultoMayor($cod_am)
    {
        $this->dispatch('adulto-mayor-form-abrir', adultoId: $cod_am);
    }

    public function render()
    {
        $service = app(AdultoMayorService::class);
        $filtros = [
            'buscar' => $this->buscar,
            'estado' => $this->estado,
            'genero' => $this->genero,
            'permanencia' => $this->permanencia,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'ciudad_municipio' => $this->ciudad_municipio,
            'rango_edad' => $this->rango_edad,
        ];

        $adultos = $service->obtenerListado($filtros);
        $adultosBase = AdultoMayor::query()->visiblesClinicamentePara(auth()->user());

        $totales = [
            'total' => (clone $adultosBase)->count(),
            'activos' => (clone $adultosBase)->whereHas('estado', fn($q) => $q->whereRaw('UPPER(estado) = ?', ['ACTIVO']))->count(),
            'archivados' => (clone $adultosBase)->whereHas('estado', fn($q) => $q->whereRaw('UPPER(estado) IN (?, ?)', ['ARCHIVADO', 'INACTIVO']))->count(),
            'seguimiento' => (clone $adultosBase)->whereHas('estado', fn($q) => $q->whereRaw('UPPER(estado) LIKE ?', ['%SEGUIMIENTO%']))->count(),
            'sin_evaluacion' => (clone $adultosBase)->doesntHave('evaluacionesCognitivas')->count(),
            'docs_pendientes' => (clone $adultosBase)->doesntHave('documentos')->count(),
        ];

        return view('livewire.admin.adultos-mayores.adultos-mayores-panel', [
            'adultos' => $adultos,
            'totales' => $totales,
            'estadosAdulto' => $service->obtenerEstados(),
        ]);
    }
}
