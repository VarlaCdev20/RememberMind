<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;

class SaludSeguimientoListPanel extends Component
{
    use WithPagination;

    public $search = '';

    protected $queryString = [
        'search' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getContext()
    {
        $context = [
            'titulo' => 'Resumen de salud',
            'descripcion' => 'Seleccione un adulto mayor para consultar su expediente de salud y seguimiento.',
            'boton' => 'Ver resumen de salud',
            'ruta_destino' => 'admin.salud-seguimiento.resumen',
            'icono' => 'ph-heartbeat',
        ];

        if (request()->routeIs('admin.salud-seguimiento.ficha.index')) {
            $context = [
                'titulo' => 'Ficha médica',
                'descripcion' => 'Seleccione un adulto mayor para registrar, actualizar o consultar su ficha médica.',
                'boton' => 'Gestionar ficha médica',
                'ruta_destino' => 'admin.salud-seguimiento.ficha',
                'icono' => 'ph-file-text',
            ];
        } elseif (request()->routeIs('admin.salud-seguimiento.medicacion.index')) {
            $context = [
                'titulo' => 'Medicación',
                'descripcion' => 'Seleccione un adulto mayor para gestionar su medicación registrada.',
                'boton' => 'Gestionar medicación',
                'ruta_destino' => 'admin.salud-seguimiento.medicacion',
                'icono' => 'ph-pill',
            ];
        } elseif (request()->routeIs('admin.salud-seguimiento.administracion.index')) {
            $context = [
                'titulo' => 'Administración de medicación',
                'descripcion' => 'Seleccione un adulto mayor para registrar o consultar administraciones de medicación.',
                'boton' => 'Registrar administración',
                'ruta_destino' => 'admin.salud-seguimiento.administracion',
                'icono' => 'ph-prescription',
            ];
        } elseif (request()->routeIs('admin.salud-seguimiento.signos.index')) {
            $context = [
                'titulo' => 'Signos vitales',
                'descripcion' => 'Seleccione un adulto mayor para registrar o revisar controles de signos vitales.',
                'boton' => 'Registrar signos vitales',
                'ruta_destino' => 'admin.salud-seguimiento.signos',
                'icono' => 'ph-activity',
            ];
        } elseif (request()->routeIs('admin.salud-seguimiento.valoracion.index')) {
            $context = [
                'titulo' => 'Valoración funcional',
                'descripcion' => 'Seleccione un adulto mayor para registrar o consultar su autonomía, dependencia y riesgo funcional.',
                'boton' => 'Gestionar valoración funcional',
                'ruta_destino' => 'admin.salud-seguimiento.valoracion',
                'icono' => 'ph-person-simple-walk',
            ];
        }

        return $context;
    }

    public function render()
    {
        $query = AdultoMayor::query()
            ->with([
                'estado', 
                'fichasMedicas' => function($q) { $q->latest()->limit(1); },
                'medicaciones' => function($q) { $q->where('estado', 'ACTIVA'); },
                'valoracionesFuncionales' => function($q) { $q->latest('fecha')->limit(1); }
            ])
            ->where(function ($q) {
                $q->where('nombres', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('cod_am', 'ilike', '%' . $this->search . '%');
            });

        $adultos = $query->orderBy('ap_paterno')->paginate(12);
        
        return view('livewire.admin.salud-seguimiento.salud-seguimiento-list-panel', [
            'adultos' => $adultos,
            'contexto' => $this->getContext()
        ])->layout('layouts.sistema');
    }
}
