<?php

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Models\AplicacionInstrumento;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class SaludEvaluacionesGeriatricasPanel extends Component
{
    use WithPagination;

    #[Locked]
    public ?Residente $adulto = null;

    public function mount(?Residente $adulto = null)
    {
        $this->adulto = $adulto?->exists ? $adulto : null;
        $this->autorizar();
    }

    private function autorizar(): void
    {
        $usuario = auth()->user() ? User::query()->find(auth()->id()) : null;
        abort_unless($usuario?->estado === 'ACTIVO'
            && ! $usuario->hasRole('FAMILIAR')
            && $usuario->checkPermissionTo('aplicaciones_instrumento.ver', 'web')
            && ($usuario->hasRole('SUPERADMINISTRADOR') || $usuario->personal()->where('estado', 'ACTIVO')->exists()), 403);
        if ($this->adulto !== null) {
            app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($this->adulto->getKey(), $usuario);
        }
    }

    #[On('evaluacion-geriatrica-guardada')]
    public function actualizarCapturas(): void
    {
        $this->autorizar();
        $this->resetPage();
    }

    public function render()
    {
        $this->autorizar();
        $evaluaciones = $this->adulto === null ? null : AplicacionInstrumento::query()
            ->with('instrumento')
            ->where('cod_residente', $this->adulto->cod_residente)
            ->orderByDesc('fecha_hora')
            ->orderByDesc('cod_aplicacion')->paginate(15);

        return view('livewire.valoraciones.salud-evaluaciones-geriatricas-panel', compact('evaluaciones'))
            ->layout('layouts.sistema');
    }
}
