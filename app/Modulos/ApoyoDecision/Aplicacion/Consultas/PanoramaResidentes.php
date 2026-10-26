<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\Alergia;
use App\Models\Diagnostico;
use App\Models\EvaluacionExperta;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\CohorteCognitivaLocal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

/** Frecuencias descriptivas de hechos vigentes; nunca infiere diagnósticos ni modifica historia. */
final class PanoramaResidentes
{
    public const CAMPOS = ['diagnosticos' => 'Diagnósticos registrados', 'alergias' => 'Alergias registradas'];

    private function autorizar(User $usuario, string $campo): User
    {
        abort_unless(array_key_exists($campo, self::CAMPOS), 422);
        $usuario = User::query()->findOrFail($usuario->getKey());
        abort_if($usuario->hasRole('FAMILIAR'), 403);
        Gate::forUser($usuario)->authorize('consultar', VersionModeloExperto::class);
        Gate::forUser($usuario)->authorize('viewAny', Residente::class);
        foreach (['residentes.ver', 'valoracion_medica.ver', 'controles_cognitivos.ver', $campo.'.ver'] as $permiso) {
            abort_unless($usuario->checkPermissionTo($permiso, 'web'), 403);
        }

        return $usuario;
    }

    private function fuentes(string $campo): Builder
    {
        $query = $campo === 'diagnosticos' ? Diagnostico::query() : Alergia::query();
        $query->whereIn('estado', $campo === 'diagnosticos' ? ['ACTIVO', 'ACTIVA', 'VIGENTE'] : ['ACTIVO', 'ACTIVA'])
            ->where('fecha_hora', '<=', now());
        if ($campo === 'diagnosticos') {
            $query->whereExists(fn ($atencion) => $atencion->selectRaw('1')->from('atenciones')
                ->whereColumn('atenciones.cod_atencion', 'diagnosticos.cod_atencion')
                ->whereColumn('atenciones.cod_residente', 'diagnosticos.cod_residente')
                ->whereNotIn('atenciones.estado', ['ANULADO', 'ANULADA', 'CANCELADO', 'CANCELADA']));
        }

        return $query;
    }

    /** Mantiene el lote de ingeniería fuera de los indicadores institucionales, incluso si se deshabilita su consulta. */
    public static function limitarPoblacion(Builder $query, bool $investigacion): Builder
    {
        if ($investigacion) {
            abort_unless(CohorteCognitivaLocal::habilitada(), 404);

            return CohorteCognitivaLocal::limitarLote($query);
        }

        return $query->whereNotIn('cod_residente', Residente::query()->select('cod_residente')
            ->whereIn('numero_documento', array_map(fn ($i) => sprintf('COH30-S%02d', $i), range(1, 30)))
            ->whereHas('admisiones', fn ($q) => $q->where('observacion', CohorteCognitivaLocal::MARCA_ADMISION)));
    }

    public function consultar(User $usuario, string $buscar, string $campo, ?string $patron, int $pagina = 1, bool $investigacion = false): array
    {
        $this->autorizar($usuario, $campo);
        $base = self::limitarPoblacion(Residente::query()->where('estado', 'ADMITIDO'), $investigacion);
        foreach (preg_split('/\s+/u', mb_substr(trim($buscar), 0, 100), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $termino) {
            $base->where(fn ($q) => $q->whereLike('nombres', "%{$termino}%")->orWhereLike('apellido_paterno', "%{$termino}%")
                ->orWhereLike('apellido_materno', "%{$termino}%")->orWhereLike('cod_residente', "%{$termino}%"));
        }
        $columna = $campo === 'diagnosticos' ? 'nombre' : 'sustancia';
        $fuentes = $this->fuentes($campo)->whereNotNull($columna)->whereRaw("TRIM({$columna}) <> ''")
            ->whereIn('cod_residente', (clone $base)->select('cod_residente'));
        $total = (clone $base)->count();
        $conDatos = (clone $fuentes)->distinct()->count('cod_residente');
        $filas = (clone $fuentes)->select($columna.' as etiqueta')->selectRaw('COUNT(DISTINCT cod_residente) as cantidad')
            ->groupBy($columna)->orderByDesc('cantidad')->orderBy($columna)->get()
            ->map(fn ($fila) => ['id' => hash('sha256', $fila->etiqueta), 'etiqueta' => $fila->etiqueta,
                'cantidad' => (int) $fila->cantidad, 'porcentaje' => $total ? round(100 * $fila->cantidad / $total, 1) : 0])->all();
        $sinDatos = $total - $conDatos;
        $activa = $patron === 'sin-registro' ? ['id' => $patron, 'etiqueta' => 'Sin registro activo de este campo']
            : ($patron ? collect($filas)->firstWhere('id', $patron) : null);
        // Una selección desaparecida tras actualizar datos no se interpreta como otra cohorte.
        $listado = clone $base;
        if ($patron === 'sin-registro') {
            $listado->whereNotIn('cod_residente', (clone $fuentes)->select('cod_residente'));
        } elseif ($patron !== null) {
            $activa ? $listado->whereIn('cod_residente', (clone $fuentes)->where($columna, $activa['etiqueta'])->select('cod_residente'))
                : $listado->whereRaw('1 = 0');
        }

        return ['total' => $total, 'con_datos' => $conDatos, 'sin_datos' => $sinDatos, 'filas' => $filas, 'activa' => $activa,
            'fecha_corte' => now()->format('d/m/Y H:i'), 'campo' => self::CAMPOS[$campo],
            'residentes' => $listado->with('cama.habitacion')->orderBy('apellido_paterno')->orderBy('nombres')->orderBy('cod_residente')
                ->paginate(8, ['*'], 'residentesConocimiento', max(1, $pagina))];
    }

    public function detalle(User $usuario, Residente $residente, string $campo): array
    {
        $usuario = $this->autorizar($usuario, $campo);
        $residente = Residente::query()->findOrFail($residente->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $filas = $this->fuentes($campo)->where('cod_residente', $residente->getKey())->orderByDesc('fecha_hora')
            ->orderBy($campo === 'diagnosticos' ? 'cod_diagnostico' : 'cod_alergia')->get();
        $autores = Personal::query()->whereIn('cod_personal', $filas->pluck('cod_personal')->unique())
            ->get(['cod_personal', 'nombres', 'apellido_paterno', 'apellido_materno'])->keyBy('cod_personal');

        return $filas->map(function ($fila) use ($campo, $autores) {
            $autor = $autores->get($fila->cod_personal);

            return ['nombre' => $campo === 'diagnosticos' ? $fila->nombre : $fila->sustancia,
                'datos' => ['Registro' => $fila->getKey(), 'Origen' => $campo, 'Fecha' => $fila->fecha_hora?->format('d/m/Y H:i'),
                    'Estado' => $fila->estado, 'Profesional' => $autor ? trim("{$autor->nombres} {$autor->apellido_paterno} {$autor->apellido_materno}") : 'Autoría no disponible',
                    ...$fila->only($campo === 'diagnosticos' ? ['codigo_clinico', 'tipo', 'certeza', 'observacion'] : ['tipo', 'reaccion', 'gravedad', 'observacion'])]];
        })->all();
    }
}
