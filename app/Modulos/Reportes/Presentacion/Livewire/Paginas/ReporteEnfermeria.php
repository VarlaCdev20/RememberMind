<?php

namespace App\Modulos\Reportes\Presentacion\Livewire\Paginas;

use App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReporteEnfermeria;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ReporteEnfermeria extends Component
{
    use WithPagination;

    public string $desde = '';

    public string $hasta = '';

    public string $codResidente = '';

    public string $tipo = 'TODOS';

    #[Locked]
    public array $aplicados = [];

    public function mount(): void
    {
        foreach (['desde', 'hasta', 'tipo', 'codResidente'] as $campo) {
            abort_if(request()->has($campo) && ! is_string(request()->query($campo)), 422, 'El filtro del reporte no es válido.');
        }
        $this->desde = (string) request()->query('desde', now()->subDays(6)->toDateString());
        $this->hasta = (string) request()->query('hasta', today()->toDateString());
        $this->tipo = (string) request()->query('tipo', 'TODOS');
        $this->codResidente = (string) request()->query('codResidente', '');
        $this->aplicar();
    }

    public function aplicar(): void
    {
        $this->aplicados = app(ConsultarReporteEnfermeria::class)->validar(auth()->user(), [
            'desde' => $this->desde, 'hasta' => $this->hasta, 'codResidente' => $this->codResidente, 'tipo' => $this->tipo,
        ]);
        $this->resetPage();
    }

    public function seleccionarSeccion(string $tipo): void
    {
        $this->tipo = $tipo;
        $this->aplicar();
    }

    public function render()
    {
        $reporte = app(ConsultarReporteEnfermeria::class)->consultar(auth()->user(), $this->aplicados);
        $pagina = $this->getPage();
        $registros = new LengthAwarePaginator($reporte['filas']->forPage($pagina, 20)->values(), $reporte['filas']->count(), 20, $pagina);

        return view('livewire.cuidados.reporte-enfermeria', compact('reporte', 'registros'))
            ->layout(request()->routeIs('admin.enfermeria.*') ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
