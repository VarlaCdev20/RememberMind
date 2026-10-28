<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;

/** Proyección administrativa; no interpreta, activa ni modifica conocimiento. */
final class ConsultarConocimientoAdministrativo
{
    /** Excluye paquetes técnicos y sus sucesoras sin modificar su historia. */
    public static function institucionales(): Builder
    {
        $tecnicas = VersionModeloExperto::query()->get(['cod_version_modelo', 'codigo_version', 'cod_version_anterior']);
        $excluidas = [];
        foreach ($tecnicas as $version) {
            if (preg_match('/SIM|TEST|EXPERIMENTAL|^MEM-INV-|^ORION-V1-/i', $version->codigo_version)) {
                $excluidas[$version->getKey()] = true;
            }
        }
        do {
            $cantidad = count($excluidas);
            foreach ($tecnicas as $version) {
                if (isset($excluidas[$version->cod_version_anterior])) {
                    $excluidas[$version->getKey()] = true;
                }
            }
        } while (count($excluidas) !== $cantidad);

        return VersionModeloExperto::query()->whereNotIn('cod_version_modelo', array_keys($excluidas));
    }
    public function __construct(private readonly ConocimientoVersionado $conocimiento) {}

    public function existeVersion(string $version): bool
    {
        Gate::authorize('consultar', VersionModeloExperto::class);

        return strlen($version) <= 20 && self::institucionales()->whereKey($version)->exists();
    }

    public function versiones(): LengthAwarePaginator
    {
        Gate::authorize('consultar', VersionModeloExperto::class);

        return self::institucionales()->orderByDesc('fecha_hora_creacion')->paginate(12);
    }

    public function instantanea(string $version): array
    {
        Gate::authorize('consultar', VersionModeloExperto::class);

        abort_unless($this->existeVersion($version), 404);

        return $this->conocimiento->instantanea($version);
    }
}
