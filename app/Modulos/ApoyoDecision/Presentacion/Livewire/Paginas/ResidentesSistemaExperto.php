<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/** Entrada por residente a la consulta y captura ya autorizadas del sistema experto. */
final class ResidentesSistemaExperto extends Component
{
    use WithPagination;

    public string $busqueda = '';

    public function mount(): void
    {
        $this->autorizar();
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
            && $usuario->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'])
            && $usuario->personal()->where('estado', 'ACTIVO')->whereIn('profesion', array_filter([
                $usuario->hasRole('MEDICO GENERAL/GERIATRA') ? 'MEDICINA' : null,
                $usuario->hasRole('PSICOLOGO/A') ? 'PSICOLOGIA' : null,
            ]))->exists(), 403);
        Gate::forUser($usuario)->authorize('viewAny', Residente::class);

        return $usuario;
    }

    public function render()
    {
        $usuario = $this->autorizar();
        $busqueda = trim($this->busqueda);
        $residentes = Residente::query()->where('estado', 'ADMITIDO')
            ->when($busqueda !== '', static fn ($query) => $query->where(static function ($q) use ($busqueda): void {
                $q->where('cod_residente', 'like', "%{$busqueda}%")
                    ->orWhere('nombres', 'like', "%{$busqueda}%")
                    ->orWhere('apellido_paterno', 'like', "%{$busqueda}%")
                    ->orWhere('apellido_materno', 'like', "%{$busqueda}%");
            }))
            ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombres')
            ->paginate(12);

        $accesos = [];
        foreach ($residentes as $residente) {
            $puedeConsultar = Gate::forUser($usuario)->allows('consultarResultados', [EvaluacionExperta::class, $residente]);
            $accesos[$residente->getKey()] = [
                'consultar' => $puedeConsultar,
                'valorar' => $puedeConsultar && Gate::forUser($usuario)->allows('crearOriginal', [ValoracionPetersenOriginal::class, $residente]),
            ];
        }

        return view('apoyodecision::livewire.experto.residentes', compact('residentes', 'accesos'))
            ->layout('layouts.sistema');
    }
}
