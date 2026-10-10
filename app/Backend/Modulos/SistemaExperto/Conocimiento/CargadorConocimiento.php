<?php

namespace App\Backend\Modulos\SistemaExperto\Conocimiento;

use App\Models\CondicionReglaExperta;
use App\Models\ConsecuenciaReglaExperta;
use App\Models\CriterioDominioResultado;
use App\Models\DominioValoresExperto;
use App\Models\FuenteDatoExperta;
use App\Models\MapeoValorFuente;
use App\Models\MapeoVariableFuente;
use App\Models\NodoSemantico;
use App\Models\ReglaExperta;
use App\Models\RelacionSemantica;
use App\Models\ValorSemantico;
use App\Models\VariableExperta;
use App\Models\VersionModeloExperto;
use DateTimeImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Acceso Eloquent por versión concreta; no ejecuta ni activa conocimiento. */
final class CargadorConocimiento
{
    private const MODELOS = [
        'nodos_semanticos' => [NodoSemantico::class, null],
        'relaciones_semanticas' => [RelacionSemantica::class, null],
        'dominios_valores_expertos' => [DominioValoresExperto::class, null],
        'valores_semanticos' => [ValorSemantico::class, 'dominio'],
        'variables_expertas' => [VariableExperta::class, null],
        'fuentes_datos_expertas' => [FuenteDatoExperta::class, null],
        'mapeos_variables_fuente' => [MapeoVariableFuente::class, null],
        'mapeos_valores_fuente' => [MapeoValorFuente::class, 'mapeoVariableFuente'],
        'criterios_dominios_resultado' => [CriterioDominioResultado::class, 'criterio'],
        'reglas_expertas' => [ReglaExperta::class, null],
        'condiciones_regla_experta' => [CondicionReglaExperta::class, 'regla'],
        'consecuencias_regla_experta' => [ConsecuenciaReglaExperta::class, 'regla'],
    ];

    public function cargar(string $version, array $contratosRelaciones, array $contratosExtraccion = []): PaqueteConocimiento
    {
        return new PaqueteConocimiento($version, $this->instantanea($version), $contratosRelaciones,
            soloPruebasTecnicas: false, contratosExtraccion: $contratosExtraccion);
    }

    /** Consulta administrativa: conserva filas y estados sin certificarlas ni ejecutar reglas. */
    public function instantanea(string $version): array
    {
        return DB::transaction(function () use ($version): array {
            $this->validarCadenaVersiones($version);
            $tablas = [];
            foreach (self::MODELOS as $tabla => [$clase, $relacion]) {
                $q = $clase::query();
                if ($relacion === null) {
                    $q->where('cod_version_modelo', $version);
                } else {
                    $q->whereHas($relacion, fn ($r) => $r->where('cod_version_modelo', $version));
                }
                $tablas[$tabla] = $q->orderBy((new $clase)->getKeyName())->get()->map(fn ($modelo) => $modelo->getAttributes())->all();
            }

            return $tablas;
        });
    }

    private function validarCadenaVersiones(string $id): void
    {
        $vistos = [];
        $posterior = null;
        do {
            if (isset($vistos[$id])) {
                throw new DomainException('Ciclo entre versiones del conocimiento.');
            }
            $vistos[$id] = true;
            $v = VersionModeloExperto::query()->find($id) ?? throw new DomainException('Versión de conocimiento inexistente.');
            $creacion = new DateTimeImmutable($v->getRawOriginal('fecha_hora_creacion'));
            if ($posterior !== null && $creacion > $posterior) {
                throw new DomainException('Antecesora creada después de su sucesora.');
            }
            $desde = $v->getRawOriginal('fecha_hora_vigencia');
            $hasta = $v->getRawOriginal('fecha_hora_retiro');
            if ($desde !== null && new DateTimeImmutable($desde) < $creacion) {
                throw new DomainException('Vigencia anterior a la creación.');
            }
            if ($desde !== null && $hasta !== null && new DateTimeImmutable($hasta) < new DateTimeImmutable($desde)) {
                throw new DomainException('Retiro anterior a la vigencia.');
            }
            $posterior = $creacion;
            $id = $v->cod_version_anterior;
        } while ($id !== null);
    }
}
