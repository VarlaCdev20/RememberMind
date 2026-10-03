<?php

namespace App\Frontend\Livewire\Medico\Clinica;

use App\Backend\Modulos\Reportes\Servicios\RoleDashboardDataService;
use App\Models\Residente;
use Livewire\Component;

class DashboardMedico extends Component
{
    public string $seccion = 'dashboard';

    protected $queryString = ['seccion'];

    protected $listeners = [
        'valoracionMedicaCompletada' => '$refresh',
        'nota-evolucion-guardada' => '$refresh',
        'signos-actualizados' => '$refresh',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']), 403);
        $routeName = request()->route()?->getName() ?? '';
        if ($routeName === 'admin.medico.valoraciones') {
            $this->seccion = 'valoraciones';
        }
        if ($routeName === 'admin.medico.decisiones') {
            $this->seccion = 'decisiones';
        }
    }

    public function render()
    {
        $data = app(RoleDashboardDataService::class)->forRole(auth()->user(), 'MEDICO GENERAL/GERIATRA');
        $dashboardMetrics = $data['metrics'];
        $dashboardPanels = $data['panels'];

        // Se conserva la cola que ya alimenta las acciones de valoración y dictamen.
        $valoracionesPendientes = Residente::query()
            ->whereIn('estado', ['VALORACION_MEDICA', 'DECISION_ADMISION'])
            ->orderBy('apellido_paterno')->limit(12)->get();

        return view('livewire.clinica.dashboard-medico', compact('dashboardMetrics', 'dashboardPanels', 'valoracionesPendientes'))
            ->layout('layouts.sistema');
    }

    public function iniciarValoracionMedica(string $cod_residente): void
    {
        $this->dispatch('abrirValoracionMedica', $cod_residente);
    }

    public function abrirDecisionAdmision(string $cod_residente): void
    {
        $this->dispatch('abrirDecisionAdmision', $cod_residente);
    }
}
