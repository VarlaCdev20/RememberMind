<?php

namespace App\Livewire\Admin\Psicologia;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\EvaluacionGeriatrica;
use App\Models\AreaGeriatrica;

class DashboardPsicologo extends Component
{
    protected $listeners = ['evaluacion-geriatrica-guardada' => '$refresh'];

    public function mount(): void
    {
        if (!auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'PSICOLOGO/A'])) {
            abort(403, 'Acceso denegado. Solo personal de psicología autorizado.');
        }
    }

    public function render()
    {
        $totalPacientes = AdultoMayor::whereNull('archivado_en')->count();
        $totalEvaluaciones = EvaluacionGeriatrica::whereNull('deleted_at')->count();
        $alertasCriticas = EvaluacionGeriatrica::whereNull('deleted_at')
            ->where('nivel_alerta', 'CRITICO')->count();

        $areasConfig = [
            'ARE_COG' => [
                'nombre'    => 'Cognitivo',
                'icono'     => 'ph-brain',
                'color_bg'  => 'bg-boton-acento/10',
                'color_txt' => 'text-boton-acento',
                'ruta'      => 'admin.psicologia.evaluacion.cognitiva',
            ],
            'ARE_AFE' => [
                'nombre'    => 'Afectivo',
                'icono'     => 'ph-heart',
                'color_bg'  => 'bg-estado-peligroBg',
                'color_txt' => 'text-estado-peligro',
                'ruta'      => 'admin.psicologia.evaluacion.afectiva',
            ],
            'ARE_FUN' => [
                'nombre'    => 'Funcionamiento',
                'icono'     => 'ph-person-simple-walk',
                'color_bg'  => 'bg-estado-exitoBg',
                'color_txt' => 'text-estado-exito',
                'ruta'      => 'admin.psicologia.evaluacion.funcionamiento',
            ],
            'ARE_NUT' => [
                'nombre'    => 'Nutricional',
                'icono'     => 'ph-apple-logo',
                'color_bg'  => 'bg-estado-advertenciaBg',
                'color_txt' => 'text-estado-advertencia',
                'ruta'      => 'admin.psicologia.evaluacion.nutricional',
            ],
            'ARE_SOC' => [
                'nombre'    => 'Entorno',
                'icono'     => 'ph-users-four',
                'color_bg'  => 'bg-estado-infoBg',
                'color_txt' => 'text-estado-info',
                'ruta'      => 'admin.psicologia.evaluacion.entorno',
            ],
        ];

        $statsPorArea = [];
        foreach ($areasConfig as $codArea => $config) {
            $total   = EvaluacionGeriatrica::whereNull('deleted_at')
                ->whereHas('instrumento', fn($q) => $q->where('cod_area', $codArea))
                ->count();
            $criticas = EvaluacionGeriatrica::whereNull('deleted_at')
                ->where('nivel_alerta', 'CRITICO')
                ->whereHas('instrumento', fn($q) => $q->where('cod_area', $codArea))
                ->count();
            $statsPorArea[$codArea] = array_merge($config, [
                'total'   => $total,
                'criticas'=> $criticas,
            ]);
        }

        $evaluacionesRecientes = EvaluacionGeriatrica::with(['instrumento.area', 'adulto', 'evaluador'])
            ->whereNull('deleted_at')
            ->orderByDesc('fecha_eval')
            ->limit(10)
            ->get();

        return view('livewire.admin.psicologia.dashboard-psicologo', compact(
            'totalPacientes',
            'totalEvaluaciones',
            'alertasCriticas',
            'evaluacionesRecientes',
            'statsPorArea'
        ))->layout('layouts.sistema');
    }
}
