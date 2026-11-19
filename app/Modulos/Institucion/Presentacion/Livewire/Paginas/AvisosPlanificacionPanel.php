<?php

namespace App\Modulos\Institucion\Presentacion\Livewire\Paginas;

use App\Modulos\Institucion\Aplicacion\Acciones\GestionarAvisosPlanificacion;
use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarAvisosPlanificacion;
use Livewire\Component;
use Livewire\WithPagination;

class AvisosPlanificacionPanel extends Component
{
    use WithPagination;

    public string $mensaje = '';

    public function mount(): void
    {
        abort_unless(app(ConsultarAvisosPlanificacion::class)->puedeConsultar(auth()->user()), 403);
    }

    public function reintentar(string $codigo): void
    {
        $estado = app(GestionarAvisosPlanificacion::class)->reintentar(auth()->user(), $codigo);
        $this->mensaje = $estado === 'DISPONIBLE' ? 'El aviso está disponible para las cuentas autorizadas.'
            : 'El cambio sigue guardado. El aviso no pudo prepararse; puedes reintentarlo.';
        $this->dispatch('avisos-planificacion-actualizados');
    }

    public function render()
    {
        $consulta = app(ConsultarAvisosPlanificacion::class);
        $actor = auth()->user()?->fresh(['personal', 'roles']);
        abort_unless($consulta->puedeConsultar($actor), 403);
        $horarios = $consulta->propio($actor) ? $consulta->horariosPropios($actor) : null;
        $disponible = $consulta->disponible();
        $eventos = $disponible ? $consulta->consulta($actor)->paginate(15) : null;
        $avisos = $eventos ? $eventos->getCollection()->map(fn ($e) => $consulta->presentar($e)) : collect();
        return view('institucion::livewire.avisos-planificacion-panel', compact('disponible', 'eventos', 'avisos', 'horarios'))
            ->layout('layouts.sistema');
    }
}
