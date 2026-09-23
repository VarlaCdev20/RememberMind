<?php

namespace App\Livewire\Residentes;

use App\Models\Residente;
use App\Services\Residentes\AdultoMayorService;
use Livewire\Component;
use Livewire\WithPagination;

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

        $totales = [
            'total' => Residente::count(),
            'activos' => Residente::whereIn('estado', ['ACTIVO', 'ADMITIDO'])->count(),
            'archivados' => Residente::whereIn('estado', ['ARCHIVADO', 'INACTIVO'])->count(),
            'seguimiento' => Residente::where('estado', 'like', '%SEGUIMIENTO%')->count(),
            'sin_evaluacion' => Residente::doesntHave('evaluacionesGeriatricas')->count(),
            'docs_pendientes' => Residente::doesntHave('documentos')->count(),
        ];

        return view('livewire.residentes.adultos-mayores-panel', [
            'adultos' => $adultos,
            'totales' => $totales,
            'estadosAdulto' => $service->obtenerEstados(),
        ]);
    }
}
