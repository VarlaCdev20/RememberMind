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
        $this->desde = now()->subDays(6)->toDateString();
        $this->hasta = today()->toDateString();
        $this->aplicar();
    }

    public function aplicar(): void
    {
        $this->aplicados = app(ConsultarReporteEnfermeria::class)->validar(auth()->user(), [
            'desde' => $this->desde, 'hasta' => $this->hasta, 'codResidente' => $this->codResidente, 'tipo' => $this->tipo,
        ]);
        $this->resetPage();
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
