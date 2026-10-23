<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\CohorteCognitivaLocal;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

final class MisResidentesPsicologia extends Component
{
    use WithPagination;

    public string $search = '';

    public string $orden = 'NOMBRE_ASC';

    public string $vistaModo = 'tarjetas';

    public function mount(): void
    {
        $this->autorizar();
        $busqueda = request()->query('buscar', '');
        $this->search = is_string($busqueda) ? mb_substr(trim($busqueda), 0, 100) : '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingOrden(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->autorizar();

        $query = Residente::query()->where('estado', 'ADMITIDO');
        $total = (clone $query)->count();

        $terms = preg_split('/\s+/u', trim($this->search), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach (array_slice($terms, 0, 5) as $term) {
            $term = mb_strtolower($term);
            if ($term === 'entrenamiento' && CohorteCognitivaLocal::habilitada()) {
                CohorteCognitivaLocal::limitarLote($query);

                continue;
            }
            $query->where(static function ($residentes) use ($term): void {
                $residentes->whereRaw('LOWER(nombres) LIKE ?', ['%'.$term.'%'])
                    ->orWhereRaw('LOWER(apellido_paterno) LIKE ?', ['%'.$term.'%'])
                    ->orWhereRaw('LOWER(apellido_materno) LIKE ?', ['%'.$term.'%'])
                    ->orWhereHas('ocupacionActiva.cama.habitacion', static fn ($habitaciones) => $habitaciones->whereRaw('LOWER(codigo) LIKE ?', ['%'.$term.'%']));
            });
        }

        $direction = $this->orden === 'NOMBRE_DESC' ? 'desc' : 'asc';
        $residentes = $query
            ->with('ocupacionActiva.cama.habitacion')
            ->withMax(['atenciones as ultima_atencion_psicologia' => static fn ($atenciones) => $atenciones
                ->where('estado', 'FINALIZADA')
                ->whereHas('personal', static fn ($personal) => $personal->where('profesion', 'PSICOLOGIA'))], 'fecha_hora')
            ->orderBy('apellido_paterno', $direction)
            ->orderBy('apellido_materno', $direction)
            ->orderBy('nombres', $direction)
            ->orderBy('cod_residente')
            ->paginate(12);

        return view('valoraciones::livewire.valoraciones.mis-residentes-psicologia', compact('residentes', 'total'))
            ->layout('layouts.sistema');
    }

    private function autorizar(): void
    {
        $usuario = auth()->user() ? User::query()->find(auth()->id()) : null;
        abort_unless($usuario?->estado === 'ACTIVO'
            && $usuario->hasAnyRole(['PSICOLOGO/A', 'SUPERADMINISTRADOR'])
            && ! $usuario->hasRole('FAMILIAR')
            && Gate::forUser($usuario)->allows('viewAny', Residente::class)
            && ($usuario->hasRole('SUPERADMINISTRADOR')
                || $usuario->personal()->where('estado', 'ACTIVO')->where('profesion', 'PSICOLOGIA')->exists()), 403);
    }
}
