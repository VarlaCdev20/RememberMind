<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class FuentesSinConflictosIntegracionTest extends TestCase
{
    public function test_fuentes_ejecutables_no_contienen_marcadores_de_merge(): void
    {
        $root = dirname(__DIR__, 2);
        $conflictos = [];
        foreach (['app', 'resources', 'routes', 'config', 'bootstrap', 'database', 'motor-experto', 'tests'] as $directorio) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/'.$directorio, \FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if (! $file->isFile() || ! in_array($file->getExtension(), ['php', 'js', 'css'], true)) {
                    continue;
                }
                if (preg_match('/^(?:<{7} |={7}\s*$|>{7} )/m', file_get_contents($file->getPathname()))) {
                    $conflictos[] = substr($file->getPathname(), strlen($root) + 1);
                }
            }
        }
        self::assertSame([], $conflictos, 'Hay conflictos de integración sin resolver.');
    }
}
