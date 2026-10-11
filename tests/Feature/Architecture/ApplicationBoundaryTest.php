<?php

namespace Tests\Feature\Architecture;

use Illuminate\Support\Facades\File;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Tests\TestCase;

class ApplicationBoundaryTest extends TestCase
{
    public function test_aplicacion_no_depende_de_http_sesion_ni_implementaciones_externas(): void
    {
        $parser = (new ParserFactory)->createForHostVersion();
        $finder = new NodeFinder;
        $violaciones = [];
        foreach ([app_path('Modulos'), app_path('Compartido')] as $root) {
            foreach (File::allFiles($root) as $file) {
                $ruta = str_replace('\\', '/', $file->getPathname());
                if (! str_contains($ruta, '/Aplicacion/') || $file->getExtension() !== 'php') {
                    continue;
                }
                $ast = (new NodeTraverser(new NameResolver))->traverse($parser->parse(File::get($ruta)));
                foreach ($finder->findInstanceOf($ast, Node\Name::class) as $name) {
                    $clase = $name->toString();
                    if (preg_match('~^App\\\\(?:Modulos\\\\[^\\\\]+|Compartido)\\\\(?:Infraestructura|Presentacion)\\\\~', $clase)
                        || preg_match('~^Illuminate\\\\(?:Http|Session|Contracts\\\\Session)\\\\~', $clase)
                        || str_starts_with($clase, 'App\\Mail\\')
                        || in_array($clase, ['Illuminate\\Support\\Facades\\Auth', 'Illuminate\\Support\\Facades\\Session', 'Illuminate\\Support\\Facades\\Storage', 'Illuminate\\Support\\Facades\\Mail', 'Barryvdh\\DomPDF\\Facade\\Pdf', 'Auth', 'Session', 'Storage', 'Mail', 'Pdf'], true)) {
                        $violaciones[] = $file->getRelativePathname().':'.$name->getStartLine().' '.$clase;
                    }
                }
                foreach ($finder->findInstanceOf($ast, Node\Expr\FuncCall::class) as $call) {
                    if ($call->name instanceof Node\Name && in_array($call->name->toString(), ['auth', 'request', 'session', 'response', 'redirect', 'view', 'route', 'url', 'cookie'], true)) {
                        $violaciones[] = $file->getRelativePathname().':'.$call->getStartLine().' '.$call->name;
                    }
                }
            }
        }
        $this->assertEmpty($violaciones, implode("\n", $violaciones));
    }
}
