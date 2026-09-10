<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;
use App\Models\NotaEvolucionMedica;
use App\Models\SignosVitalesAdulto;
use App\Models\MedicacionAdulto;

class PacientesSeguimientoPanel extends Component
{
    use WithPagination;

    public string $busqueda     = '';
    public string $filtroEstado = '';
    public string $tab          = 'activos'; // activos | pendientes | historial | interconsultas

    protected $listeners = [
        'nota-evolucion-guardada'  => '$refresh',
        'signos-actualizados'      => '$refresh',
        'valoracion-barthel-guardada' => '$refresh',
    ];

    protected $queryString = ['busqueda', 'filtroEstado', 'tab'];

    public function mount(): void
    {
        if (!auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA'])) {
            abort(403);
        }

        // Activar tab según la ruta de acceso
        $routeName = request()->route()?->getName() ?? '';
        if ($routeName === 'admin.medico.pacientes.historial') {
            $this->tab = 'historial';
        } elseif ($routeName === 'admin.medico.interconsultas') {
            $this->tab = 'interconsultas';
        }
    }

    public function updatingBusqueda(): void { $this->resetPage(); }
    public function updatingTab(): void      { $this->resetPage(); }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function abrirFicha(string $codAm): void
    {
        $this->redirect(route('admin.medico.paciente.ficha', $codAm));
    }

    public function nuevaNota(string $codAm): void
    {
        $this->dispatch('abrir-nota-evolucion', cod_am: $codAm);
    }

    public function nuevosSignos(string $codAm): void
    {
        $this->dispatch('abrir-signos-vitales-medico', cod_am: $codAm);
    }

    public function render()
    {
        $estadosActivos   = ['ACTIVO', 'ADMITIDO', 'ASIGNADO', 'EN_SEGUIMIENTO_ACTIVO', 'OBSERVADO', 'SEGUIMIENTO_ESPECIAL'];
        $estadosPendientes = ['PENDIENTE_VALORACION_MEDICA', 'VALORACION_MEDICA', 'DECISION_ADMISION'];

        $base = AdultoMayor::with(['estado'])
            ->whereNull('archivado_en');

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $base->where(fn($q) =>
                $q->where('nombres', 'like', "%{$busq}%")
                  ->orWhere('ap_paterno', 'like', "%{$busq}%")
                  ->orWhere('ci', 'like', "%{$busq}%")
            );
        }

        if ($this->tab === 'pendientes') {
            $base->whereHas('estado', fn($q) => $q->whereIn('estado', $estadosPendientes));
        } elseif ($this->tab === 'interconsultas') {
            // Pacientes activos que tienen al menos 1 nota de tipo INTERCONSULTA
            $base->whereHas('estado', fn($q) => $q->whereIn('estado', $estadosActivos))
                 ->whereHas('notas', fn($q) => $q->where('tipo_nota', 'INTERCONSULTA')->where('estado', 'ACTIVO'));
        } elseif ($this->tab === 'historial') {
            // Todos los pacientes (sin filtro de estado), incluidos dados de alta/derivados
        } else {
            // activos (default)
            $base->whereHas('estado', fn($q) => $q->whereIn('estado', $estadosActivos));
        }

        $pacientes = $base->orderBy('nombres')->paginate(20);

        // Counts for tab badges
        $cntActivos    = AdultoMayor::whereNull('archivado_en')
            ->whereHas('estado', fn($q) => $q->whereIn('estado', $estadosActivos))->count();
        $cntPendientes = AdultoMayor::whereNull('archivado_en')
            ->whereHas('estado', fn($q) => $q->whereIn('estado', $estadosPendientes))->count();
        $cntInterconsultas = AdultoMayor::whereNull('archivado_en')
            ->whereHas('estado', fn($q) => $q->whereIn('estado', $estadosActivos))
            ->whereHas('notas', fn($q) => $q->where('tipo_nota', 'INTERCONSULTA')->where('estado', 'ACTIVO'))
            ->count();

        // Last vitals and notes per patient (keyed by cod_am)
        $codAms = $pacientes->pluck('cod_am')->toArray();

        $ultimosSignos = SignosVitalesAdulto::whereIn('cod_am', $codAms)
            ->where('estado', 'VIGENTE')
            ->orderByDesc('fecha')
            ->get()
            ->keyBy('cod_am');

        $ultimasNotas = NotaEvolucionMedica::whereIn('cod_am', $codAms)
            ->where('estado', 'ACTIVO')
            ->orderByDesc('fecha')
            ->get()
            ->keyBy('cod_am');

        $cntMedicacion = MedicacionAdulto::whereIn('cod_am', $codAms)
            ->where('estado', 'ACTIVO')
            ->selectRaw('cod_am, COUNT(*) as total')
            ->groupBy('cod_am')
            ->pluck('total', 'cod_am');

        return view('livewire.clinica.pacientes-seguimiento-panel', compact(
            'pacientes', 'cntActivos', 'cntPendientes', 'cntInterconsultas',
            'ultimosSignos', 'ultimasNotas', 'cntMedicacion'
        ))->layout('layouts.sistema');
    }
}
