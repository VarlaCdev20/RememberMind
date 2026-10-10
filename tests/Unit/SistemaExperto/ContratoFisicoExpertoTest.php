<?php

namespace Tests\Unit\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Conocimiento\InventarioExperto;
use App\Models\EvaluacionExperta;
use Illuminate\Database\Eloquent\MassAssignmentException;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

/** Lee AST; no incluye archivos de migración ni llama up/down/Schema. */
class ContratoFisicoExpertoTest extends TestCase
{
    private function contrato(): array
    {
        return json_decode(file_get_contents(dirname(__DIR__, 2).'/Fixtures/SistemaExperto/contrato-d137.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    private function literal(Node $n): mixed
    {
        if ($n instanceof Node\Scalar\String_ || $n instanceof Node\Scalar\Int_) {
            return $n->value;
        }
        if ($n instanceof Node\Expr\Array_) {
            return array_map(fn ($i) => $this->literal($i->value), $n->items);
        }
        $this->fail('Argumento no literal en contrato físico: '.$n->getType());
    }

    private function cadena(Node\Expr\MethodCall $n): array
    {
        $calls = [];
        do {
            $calls[$n->name->toString()] = array_map(fn ($a) => $this->literal($a->value), $n->args);
            $n = $n->var;
        } while ($n instanceof Node\Expr\MethodCall);
        $this->assertInstanceOf(Node\Expr\Variable::class, $n);
        $this->assertSame('table', $n->name);

        return $calls;
    }

    public function test_los_archivos_materializan_exactamente_d137_sin_ejecutarlos(): void
    {
        $root = dirname(__DIR__, 3);
        $contract = $this->contrato();
        $this->assertSame(InventarioExperto::TABLAS, array_column($contract['tables'], 'name'));
        $files = glob($root.'/database/migrations/2026_10_08_1200*_create_*_table.php');
        $this->assertSame(array_map(fn ($t) => $root.'/'.$t['migration'], $contract['tables']), $files);
        $finder = new NodeFinder;
        $parser = (new ParserFactory)->createForHostVersion();
        $counts = ['columns' => 0, 'foreign_keys' => 0, 'composite' => 0, 'unique' => 0, 'indexes' => 0];

        foreach ($contract['tables'] as $table) {
            $ast = $parser->parse(file_get_contents($root.'/'.$table['migration']));
            $schemaCalls = $finder->findInstanceOf($ast, Node\Expr\StaticCall::class);
            $this->assertCount(2, $schemaCalls);
            $this->assertSame(['create', 'dropIfExists'], array_map(fn ($n) => $n->name->toString(), $schemaCalls));
            $this->assertSame($table['name'], $this->literal($schemaCalls[0]->args[0]->value));
            $this->assertSame($table['name'], $this->literal($schemaCalls[1]->args[0]->value));
            $closure = $schemaCalls[0]->args[1]->value;
            $this->assertInstanceOf(Node\Expr\Closure::class, $closure);
            $columns = $fks = $unique = $indexes = [];

            foreach ($closure->stmts as $stmt) {
                $chain = $this->cadena($stmt->expr);
                $type = array_key_last($chain);
                if (in_array($type, ['string', 'text', 'dateTime'], true)) {
                    $this->assertEmpty(array_diff(array_keys($chain), [$type, 'nullable', 'primary']));
                    $columns[] = ['name' => $chain[$type][0], 'type' => $type,
                        'length' => $chain[$type][1] ?? null, 'nullable' => isset($chain['nullable']),
                        'primary_key' => isset($chain['primary'])];
                } elseif ($type === 'primary') {
                    $this->assertCount(1, $chain['primary'][0]);
                    $this->assertLessThanOrEqual(63, strlen($chain['primary'][1]));
                    foreach ($columns as &$columna) {
                        if ($columna['name'] === $chain['primary'][0][0]) {
                            $columna['primary_key'] = true;
                        }
                    }
                    unset($columna);
                } elseif ($type === 'foreign') {
                    $this->assertArrayHasKey('restrictOnDelete', $chain);
                    $this->assertArrayHasKey('noActionOnUpdate', $chain);
                    $this->assertLessThanOrEqual(63, strlen($chain['foreign'][1]));
                    $fks[] = ['columns' => $chain['foreign'][0], 'references_table' => $chain['on'][0],
                        'references_columns' => $chain['references'][0]];
                } elseif ($type === 'unique' || $type === 'index') {
                    $this->assertLessThanOrEqual(63, strlen($chain[$type][1]));
                    if ($type === 'unique') {
                        $unique[] = $chain[$type][0];
                    } else {
                        $indexes[] = $chain[$type][0];
                    }
                } else {
                    $this->fail('Operación estructural fuera de D-137: '.$type);
                }
            }
            $this->assertSame($table['columns'], $columns, $table['name']);
            $this->assertSame($table['foreign_keys'], $fks, $table['name']);
            $this->assertSame($table['unique'], $unique, $table['name']);
            $this->assertSame($table['indexes'], $indexes, $table['name']);
            $counts['columns'] += count($columns);
            $counts['foreign_keys'] += count($fks);
            $counts['composite'] += count(array_filter($fks, fn ($f) => count($f['columns']) > 1));
            $counts['unique'] += count($unique);
            $counts['indexes'] += count($indexes);
        }
        $this->assertSame(['columns' => 190, 'foreign_keys' => 65, 'composite' => 7, 'unique' => 24, 'indexes' => 52], $counts);
    }

    public function test_los_23_modelos_preservan_claves_y_protegen_asignacion_masiva(): void
    {
        foreach ($this->contrato()['tables'] as $table) {
            $class = 'App\\Models\\'.$table['model'];
            $model = new $class;
            $this->assertSame($table['name'], $model->getTable());
            $pk = array_values(array_filter($table['columns'], fn ($c) => $c['primary_key']))[0]['name'];
            $this->assertSame($pk, $model->getKeyName());
            $this->assertSame('string', $model->getKeyType());
            $this->assertFalse($model->getIncrementing());
            $this->assertFalse($model->usesTimestamps());
            $this->assertSame(['*'], $model->getGuarded());
            foreach ($table['columns'] as $col) {
                if ($col['type'] === 'dateTime') {
                    $this->assertSame('immutable_datetime', $model->getCasts()[$col['name']]);
                }
            }
        }
        $this->expectException(MassAssignmentException::class);
        (new EvaluacionExperta)->fill(['cod_residente' => 'RES_MANIPULADO']);
    }
}
