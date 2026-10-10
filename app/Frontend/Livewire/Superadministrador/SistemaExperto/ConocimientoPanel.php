<?php

namespace App\Frontend\Livewire\Superadministrador\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Conocimiento\CargadorConocimiento;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaquetesCandidatosCognitivos;
use App\Backend\Modulos\SistemaExperto\Servicios\DemostracionCOGMEM;
use App\Models\VersionModeloExperto;
use DomainException;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

final class ConocimientoPanel extends Component
{
    use WithPagination;

    #[Locked]
    public ?string $version = null;

    #[Locked]
    public string $caso = 'dificultad';

    public function mount(): void
    {
        $this->autorizar();
    }

    public function seleccionarVersion(string $id): void
    {
        $this->autorizar();
        abort_unless(strlen($id) <= 20 && VersionModeloExperto::query()->whereKey($id)->exists(), 404);
        $this->version = $id;
    }

    public function seleccionarCaso(string $caso): void
    {
        $this->autorizar();
        abort_unless(array_key_exists($caso, DemostracionCOGMEM::CASOS), 422);
        $this->caso = $caso;
    }

    public function render()
    {
        $this->autorizar();
        $versiones = VersionModeloExperto::query()->orderByDesc('fecha_hora_creacion')->paginate(12);
        $filas = [];
        $errorConocimiento = null;
        if ($this->version !== null) {
            try {
                $filas = (new CargadorConocimiento)->instantanea($this->version);
            } catch (DomainException $e) {
                $errorConocimiento = $e->getMessage();
            }
        }
        $demo = null;
        $errorDemo = null;
        try {
            $demo = (new DemostracionCOGMEM)->ejecutar($this->caso);
        } catch (DomainException $e) {
            $errorDemo = $e->getMessage();
        }
        $candidatos = (new PaquetesCandidatosCognitivos)->todos();

        return view('livewire.superadministrador.sistema-experto.conocimiento-panel', compact('versiones', 'filas', 'errorConocimiento', 'demo', 'errorDemo', 'candidatos'))
            ->layout('layouts.sistema');
    }

    private function autorizar(): void
    {
        Gate::authorize('consultar', VersionModeloExperto::class);
    }
}
