<?php

namespace App\Livewire\Medicacion;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;
use App\Models\MedicacionAdulto;
use Carbon\Carbon;

class SaludMedicacionPanel extends Component
{
    use WithPagination;

    public ?AdultoMayor $adulto = null;
    public $cod_am = '';

    // Filtros de búsqueda para medicamentos
    public $search = '';
    public $filtroEstado = '';
    public $filtroVia = '';

    protected $listeners = [
        'medicacion-actualizada' => '$refresh',
        'administracion-actualizada' => '$refresh',
    ];

    public function mount()
    {
        // Se monta sin adulto por defecto para forzar la selección
    }

    public function updatedCodAm($value)
    {
        if ($value) {
            $this->adulto = AdultoMayor::where('cod_am', $value)->first();
            $this->resetPage();
        } else {
            $this->adulto = null;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroVia()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filtroEstado', 'filtroVia']);
        $this->resetPage();
    }

    public function render()
    {
        // Lista de adultos mayores para el selector
        $adultosDisponibles = AdultoMayor::whereHas('estado', function ($q) {
            $q->whereIn('estado', ['ACTIVO', 'ACTIVA']);
        })->orderBy('nombres')->get();

        // Estadísticas globales y panel (solo si hay un adulto seleccionado)
        $stats = [
            'activas' => 0,
            'suspendidas' => 0,
            'finalizadas' => 0,
            'archivadas' => 0,
        ];
        
        $medicaciones = collect();
        $viasDisponibles = collect();
        $tomasPendientesHoy = 0; // Próxima implementación
        $tomasOmitidas = 0; // Próxima implementación

        if ($this->adulto) {
            $allMedicaciones = MedicacionAdulto::where('cod_am', $this->adulto->cod_am)->get();
            $stats = [
                'activas' => $allMedicaciones->where('estado', 'ACTIVO')->count(),
                'suspendidas' => $allMedicaciones->where('estado', 'SUSPENDIDO')->count(),
                'finalizadas' => $allMedicaciones->where('estado', 'FINALIZADO')->count(),
                'archivadas' => $allMedicaciones->where('estado', 'ARCHIVADO')->count(),
            ];

            // Consulta base con filtros
            $query = MedicacionAdulto::with(['registrador', 'administraciones' => function($q) {
                $q->whereDate('fecha_toma', Carbon::today());
            }])->where('cod_am', $this->adulto->cod_am);

            if (!empty($this->search)) {
                $query->where('nombre_medicamento', 'like', '%' . $this->search . '%');
            }

            if (!empty($this->filtroEstado)) {
                $query->where('estado', $this->filtroEstado);
            }

            if (!empty($this->filtroVia)) {
                $query->where('via_administracion', $this->filtroVia);
            }

            $medicaciones = $query->latest()->paginate(10);

            $viasDisponibles = MedicacionAdulto::where('cod_am', $this->adulto->cod_am)
                ->whereNotNull('via_administracion')
                ->select('via_administracion')
                ->distinct()
                ->pluck('via_administracion');
        } else {
            // Si no hay adulto, pero queremos mostrar estadísticas generales de TODO el módulo
            $allGlobal = MedicacionAdulto::all();
            $stats = [
                'activas' => $allGlobal->where('estado', 'ACTIVO')->count(),
                'suspendidas' => $allGlobal->where('estado', 'SUSPENDIDO')->count(),
                'finalizadas' => $allGlobal->where('estado', 'FINALIZADO')->count(),
                'archivadas' => $allGlobal->where('estado', 'ARCHIVADO')->count(),
            ];
            
            // Para la tabla general sin adulto seleccionado, se muestran todos los medicamentos activos por defecto
            $query = MedicacionAdulto::with(['registrador', 'adultoMayor']);
            
            if (!empty($this->search)) {
                $query->where('nombre_medicamento', 'like', '%' . $this->search . '%');
            }
            if (!empty($this->filtroEstado)) {
                $query->where('estado', $this->filtroEstado);
            } else {
                $query->where('estado', 'ACTIVO'); // Por defecto activos si no hay filtro
            }
            if (!empty($this->filtroVia)) {
                $query->where('via_administracion', $this->filtroVia);
            }
            
            $medicaciones = $query->latest()->paginate(10);
            
            $viasDisponibles = MedicacionAdulto::whereNotNull('via_administracion')
                ->select('via_administracion')
                ->distinct()
                ->pluck('via_administracion');
        }

        return view('livewire.admin.salud-seguimiento.salud-medicacion', [
            'adultosDisponibles' => $adultosDisponibles,
            'medicaciones' => $medicaciones,
            'stats' => $stats,
            'viasDisponibles' => $viasDisponibles,
        ]);
    }
}
