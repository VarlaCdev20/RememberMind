<?php

namespace App\Livewire\Admin\Admisiones;

use App\Models\Preadmision;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class PreadmisionesPanel extends Component
{
    use WithPagination;

    public string $search = '';
    public string $estado = '';
    public string $prioridad = '';
    public string $enfermero_id = '';
    public string $fecha_inicio = '';
    public string $fecha_fin = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'prioridad' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function updatingPrioridad(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['search', 'estado', 'prioridad', 'enfermero_id', 'fecha_inicio', 'fecha_fin']);
        $this->resetPage();
    }

    public function exportarReportePdf(): void
    {
        if (! auth()->user()->can('admisiones.ver_dashboard') && ! auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            abort(403);
        }

        activity('Admisiones')
            ->causedBy(auth()->user())
            ->log('Solicito reporte de preadmisiones.');

        $this->dispatch('swal', [
            'title' => 'Reporte en preparacion',
            'text' => 'La consulta de preadmisiones esta lista. La plantilla PDF queda pendiente de integracion.',
            'icon' => 'info',
        ]);
    }

    public function render()
    {
        $query = Preadmision::query()
            ->with(['enfermero', 'documentos'])
            ->withCount('documentos');

        if ($this->search !== '') {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('cod_pre', 'like', "%{$search}%")
                    ->orWhere('nombres', 'like', "%{$search}%")
                    ->orWhere('ap_paterno', 'like', "%{$search}%")
                    ->orWhere('ap_materno', 'like', "%{$search}%")
                    ->orWhere('ci', 'like', "%{$search}%")
                    ->orWhere('familiar_nombres', 'like', "%{$search}%")
                    ->orWhere('familiar_ap_paterno', 'like', "%{$search}%");
            });
        }

        if ($this->estado !== '') {
            $query->where('estado', $this->estado);
        }

        if ($this->prioridad !== '') {
            $query->where('prioridad', $this->prioridad);
        }

        if ($this->enfermero_id !== '') {
            $query->where('enfermero_asignado', $this->enfermero_id);
        }

        if ($this->fecha_inicio !== '') {
            $query->whereDate('fecha_solicitud', '>=', $this->fecha_inicio);
        }

        if ($this->fecha_fin !== '') {
            $query->whereDate('fecha_solicitud', '<=', $this->fecha_fin);
        }

        $metricas = [
            'total' => Preadmision::count(),
            'asignadas' => Preadmision::where('estado', 'PREADMISION_ASIGNADA')->count(),
            'alta_prioridad' => Preadmision::whereIn('prioridad', ['ALTA', 'CRITICA'])->count(),
            'con_documentos' => Preadmision::where('documentos_iniciales_completos', true)->count(),
            'sin_enfermero' => Preadmision::whereNull('enfermero_asignado')->count(),
        ];

        return view('livewire.admin.admisiones.preadmisiones-panel', [
            'preadmisiones' => $query->latest('fecha_asignacion')->paginate(10),
            'metricas' => $metricas,
            'enfermeros' => User::role('ENFERMEROS')
                ->where('estado', 'ACTIVO')
                ->orderBy('ap_paterno')
                ->get(['cod_usu', 'nombres', 'ap_paterno', 'ap_materno']),
        ])->layout('layouts.sistema');
    }
}
