<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Simulacion;

use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarLaboratorioExperimental;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/** Instantáneas temporales del actor. No sustituye el archivo institucional/consola. */
final class SesionLaboratorioExperimental
{
    public function ejecutar(User $autor, string $escenario): string
    {
        $clave = $this->clave($autor);
        $salida = app(ConsultarLaboratorioExperimental::class)->ejecutar($autor, $escenario);
        $id = (string) Str::uuid();
        $ejecuciones = session()->get($clave, []);
        $ejecuciones[$id] = ['id' => $id, 'escenario' => $escenario, 'fecha' => now()->toIso8601String(), 'salida' => $salida];
        session()->put($clave, array_slice($ejecuciones, -6, null, true));

        return $id;
    }

    public function historial(User $autor): array
    {
        return array_reverse(session()->get($this->clave($autor), []), true);
    }

    public function consultar(User $autor, string $id): array
    {
        $ejecuciones = $this->historial($autor);
        abort_unless(isset($ejecuciones[$id]), 404);

        return $ejecuciones[$id];
    }

    private function clave(User $autor): string
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        $actual = User::query()->findOrFail($autor->getKey());
        Gate::forUser($actual)->authorize('consultar', VersionModeloExperto::class);

        return 'laboratorio_experimental.'.hash('sha256', (string) $actual->getKey());
    }
}
