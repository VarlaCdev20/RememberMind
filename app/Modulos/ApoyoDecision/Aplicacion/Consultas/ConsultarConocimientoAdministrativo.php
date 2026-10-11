<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Eloquent\CargadorConocimiento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/** Proyección administrativa; no interpreta, activa ni modifica conocimiento. */
final class ConsultarConocimientoAdministrativo
{
    public function __construct(private readonly CargadorConocimiento $conocimiento) {}

    public function existeVersion(string $version): bool
    {
        Gate::authorize('consultar', VersionModeloExperto::class);

        return strlen($version) <= 20 && VersionModeloExperto::query()->whereKey($version)->exists();
    }

    public function versiones(): LengthAwarePaginator
    {
        Gate::authorize('consultar', VersionModeloExperto::class);

        return VersionModeloExperto::query()->orderByDesc('fecha_hora_creacion')->paginate(12);
    }

    public function instantanea(string $version): array
    {
        Gate::authorize('consultar', VersionModeloExperto::class);

        return $this->conocimiento->instantanea($version);
    }
}
