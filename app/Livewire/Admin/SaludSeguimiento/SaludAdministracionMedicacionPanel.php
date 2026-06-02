<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\AdministracionMedicacion;
use App\Models\MedicacionAdulto;
use Illuminate\Support\Facades\Gate;

class SaludAdministracionMedicacionPanel extends Component
{
    public AdultoMayor $adulto;

    protected $listeners = [
        'administracion-actualizada' => '$refresh',
        'medicacion-actualizada' => '$refresh',
    ];

    public function mount(AdultoMayor $adulto)
    {
        Gate::authorize('viewClinicalData', $adulto);
        $this->adulto = $adulto;
    }

    public function render()
    {
        $administraciones = AdministracionMedicacion::with(['medicacion', 'registrador'])
            ->where('cod_am', $this->adulto->cod_am)
            ->orderByDesc('fecha')
            ->orderByDesc('hora_programada')
            ->get();

        $medicacionesActivas = MedicacionAdulto::where('cod_am', $this->adulto->cod_am)
            ->where('estado', 'ACTIVO')
            ->orderBy('hora_programada')
            ->get();

        $hoy = today();
        $administracionesHoy = $administraciones->filter(fn ($admin) => $admin->fecha && $admin->fecha->isSameDay($hoy));

        $stats = [
            'administradas_hoy' => $administracionesHoy
                ->where('administrado', true)
                ->count(),
            'omitidas_hoy' => $administracionesHoy
                ->where('administrado', false)
                ->count(),
            'total_hoy' => $administracionesHoy->count(),
            'pendientes' => max($medicacionesActivas->count() - $administracionesHoy->count(), 0),
        ];

        return view('livewire.admin.salud-seguimiento.salud-administracion-medicacion', [
            'administraciones' => $administraciones,
            'medicacionesActivas' => $medicacionesActivas,
            'stats' => $stats,
        ])
            ->layout('layouts.sistema');
    }
}
