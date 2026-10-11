<?php

namespace Tests\Unit\SistemaExperto;

use App\Models\EvidenciaSoporteCondicion;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HistoriaExpertaTest extends TestCase
{
    public static function mutaciones(): array
    {
        return [['delete', []], ['forceDelete', []], ['update', [['cod_evidencia_evaluacion' => 'OTRA']]],
            ['upsert', [[['cod_evidencia_evaluacion' => 'OTRA']], 'cod_evidencia_soporte_condicion']],
            ['touch', ['fecha_hora_fin']], ['increment', ['campo']], ['decrement', ['campo']],
            ['incrementEach', [['campo' => 1]]], ['decrementEach', [['campo' => 1]]]];
    }

    #[DataProvider('mutaciones')]
    public function test_la_mutacion_masiva_se_rechaza_antes_de_acceder_a_una_conexion(string $metodo, array $argumentos): void
    {
        // Connection sin PDO: cualquier ejecución accidental no puede llegar a una BDD.
        $query = new Builder(new Connection(null));
        $builder = (new EvidenciaSoporteCondicion)->newEloquentBuilder($query);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('La historia experta');
        $builder->$metodo(...$argumentos);
    }
}
