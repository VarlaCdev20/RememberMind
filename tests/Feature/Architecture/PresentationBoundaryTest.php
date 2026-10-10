<?php

namespace Tests\Feature\Architecture;

use Illuminate\Support\Facades\File;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Tests\TestCase;

class PresentationBoundaryTest extends TestCase
{
    public function test_presentacion_delega_transacciones_y_escrituras_eloquent(): void
    {
        $parser = (new ParserFactory)->createForHostVersion();
        $finder = new NodeFinder;
        $violaciones = [];
        $mutadores = ['create', 'update', 'save', 'delete', 'forceDelete', 'insert', 'upsert', 'increment', 'decrement', 'sync', 'attach', 'detach', 'syncRoles', 'syncPermissions'];
        foreach (File::allFiles(app_path('Modulos')) as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            if (! str_contains($path, '/Presentacion/') || $file->getExtension() !== 'php' || str_ends_with($path, '.blade.php')) {
                continue;
            }
            $traverser = new NodeTraverser(new NameResolver);
            $ast = $traverser->traverse($parser->parse(File::get($path)));
            foreach ($finder->findInstanceOf($ast, Node\Stmt\ClassMethod::class) as $method) {
                $modelos = [];
                $esModelo = function (Node $node) use (&$esModelo, &$modelos): bool {
                    if ($node instanceof Node\Expr\MethodCall) {
                        return $esModelo($node->var);
                    }
                    if ($node instanceof Node\Expr\StaticCall && $node->class instanceof Node\Name) {
                        return str_starts_with($node->class->toString(), 'App\\Models\\') || str_starts_with($node->class->toString(), 'Spatie\\Permission\\Models\\');
                    }
                    if ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Name) {
                        return str_starts_with($node->class->toString(), 'App\\Models\\');
                    }

                    return $node instanceof Node\Expr\Variable && is_string($node->name) && isset($modelos[$node->name]);
                };
                foreach ($method->params as $param) {
                    if ($param->type instanceof Node\Name && str_starts_with($param->type->toString(), 'App\\Models\\')) {
                        $modelos[$param->var->name] = true;
                    }
                }
                foreach ($finder->findInstanceOf($method->stmts ?? [], Node\Expr\Assign::class) as $assign) {
                    if ($assign->var instanceof Node\Expr\Variable && is_string($assign->var->name) && $esModelo($assign->expr)) {
                        $modelos[$assign->var->name] = true;
                    }
                }
                foreach ($finder->find($method->stmts ?? [], fn (Node $n) => $n instanceof Node\Expr\StaticCall || $n instanceof Node\Expr\MethodCall) as $call) {
                    if (! $call->name instanceof Node\Identifier) {
                        continue;
                    }
                    $nombre = $call->name->toString();
                    $transaccion = $call instanceof Node\Expr\StaticCall && $call->class instanceof Node\Name && in_array($call->class->toString(), ['DB', 'Illuminate\\Support\\Facades\\DB'], true) && in_array($nombre, ['transaction', 'beginTransaction', 'commit', 'rollBack'], true);
                    if ($transaccion || (in_array($nombre, $mutadores, true) && $esModelo($call))) {
                        $violaciones[] = $file->getRelativePathname().':'.$call->getStartLine().' '.$nombre;
                    }
                }
            }
        }
        $this->assertEmpty($violaciones, implode("\n", $violaciones));
    }

    public function test_blade_no_ejecuta_consultas_eloquent(): void
    {
        foreach (File::allFiles(app_path('Modulos')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $this->assertDoesNotMatchRegularExpression('/\\\\App\\\\Models\\\\\w+::(?:query|where\w*|find\w*|all|role|with\w*|join|leftJoin|select)\s*\(/', File::get($file->getPathname()), $file->getPathname());
        }
    }
}
