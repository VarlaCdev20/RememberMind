<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\CohorteCognitivaLocal;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/** Entrada por residente a la consulta autorizada de resultados del sistema experto. */
final class ResidentesSistemaExperto extends Component
{
    use WithPagination;

    public string $busqueda = '';

    public function mount(): void
    {
        $this->autorizar();
        $buscar = request()->query('buscar', '');
        $this->busqueda = is_string($buscar) ? mb_substr(trim($buscar), 0, 100) : '';
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    private function autorizar(): User
    {
        $usuario = auth()->user() ? User::query()->findOrFail(auth()->id()) : null;
        abort_unless($usuario?->estado === 'ACTIVO'
            && session(RolePreviewService::SESSION_KEY) === null
            && ($usuario->hasRole('SUPERADMINISTRADOR') || ($usuario->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'])
            && $usuario->personal()->where('estado', 'ACTIVO')->whereIn('profesion', array_filter([
                $usuario->hasRole('MEDICO GENERAL/GERIATRA') ? 'MEDICINA' : null,
                $usuario->hasRole('PSICOLOGO/A') ? 'PSICOLOGIA' : null,
            ]))->exists())), 403);
        Gate::forUser($usuario)->authorize('viewAny', Residente::class);

        return $usuario;
    }

    public function render()
    {
        $usuario = $this->autorizar();
        $busqueda = trim($this->busqueda);
        $terminos = preg_split('/\s+/u', $busqueda, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $residentes = Residente::query()->where('estado', 'ADMITIDO')
            ->when($terminos !== [], static function ($query) use ($terminos): void {
                foreach ($terminos as $termino) {
                    if (mb_strtolower($termino) === 'entrenamiento' && CohorteCognitivaLocal::habilitada()) {
                        CohorteCognitivaLocal::limitarLote($query);

                        continue;
                    }
                    $query->where(static function ($q) use ($termino): void {
                        $patron = "%{$termino}%";
                        $q->whereLike('cod_residente', $patron)
                            ->orWhereLike('nombres', $patron)
                            ->orWhereLike('apellido_paterno', $patron)
                            ->orWhereLike('apellido_materno', $patron);
                    });
                }
            })
            ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombres')
            ->paginate(12);

        $accesos = [];
        foreach ($residentes as $residente) {
            $accesos[$residente->getKey()] = [
                'consultar' => Gate::forUser($usuario)->allows('consultarResultados', [EvaluacionExperta::class, $residente]),
            ];
        }

        return view('apoyodecision::livewire.experto.residentes', compact('residentes', 'accesos'))
            ->layout('layouts.sistema');
    }
}
