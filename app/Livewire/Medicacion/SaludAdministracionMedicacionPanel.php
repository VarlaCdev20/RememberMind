<?php

namespace App\Livewire\Medicacion;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\AdministracionMedicacion;
use App\Models\MedicacionAdulto;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Medicacion\AgendaMedicacionService;

class SaludAdministracionMedicacionPanel extends Component
{
    public AdultoMayor $adulto;

    protected $listeners = [
        'administracion-actualizada' => '$refresh',
        'medicacion-actualizada' => '$refresh',
    ];

    public function mount(AdultoMayor $adulto)
    {
        $user = auth()->user();
        abort_unless($user, 401);
        abort_unless($user->hasRole('SUPERADMINISTRADOR') || $user->canAny([
            'medicacion.ver', 'salud.medicacion.ver', 'administracion_medicacion.registrar',
        ]), 403);
        if ($user->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($adulto, $user);
        }
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
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
            ->orderBy('hora_programada')
            ->get();

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_am);
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
            'pendientes' => $agenda->whereIn('estado', ['PENDIENTE', 'PROXIMA', 'VENCIDA'])->count(),
        ];

        return view('livewire.medicacion.salud-administracion-medicacion', [
            'administraciones' => $administraciones,
            'medicacionesActivas' => $medicacionesActivas,
            'stats' => $stats,
            'agenda' => $agenda,
        ])
            ->layout('layouts.sistema');
    }
}
