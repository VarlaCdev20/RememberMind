<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class FiltrosDisenoUnificadoTest extends TestCase
{
    public function test_las_vistas_con_filtros_usan_la_barra_canonica(): void
    {
        $directorio = resource_path('views');
        $archivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio));
        $sinBarraCanonica = [];

        foreach ($archivos as $archivo) {
            if (! $archivo->isFile() || ! str_ends_with($archivo->getFilename(), '.blade.php')) {
                continue;
            }

            $contenido = file_get_contents($archivo->getPathname());
            $rutaRelativa = str_replace('\\', '/', str_replace($directorio.DIRECTORY_SEPARATOR, '', $archivo->getPathname()));

            // La búsqueda global del encabezado no es una barra de filtros de pantalla.
            if ($rutaRelativa === 'components/layout/topbar-enfermeria.blade.php') {
                continue;
            }

            $tieneFiltroReactivo = preg_match(
                '/wire:model[^\r\n]*(?:filtro|buscar|search)|name=["\'][^"\']*(?:buscar|filtro|search)/i',
                $contenido
            ) === 1;

            $usaBarraCanonica = str_contains($contenido, '<x-ui.filter-bar')
                || str_contains($contenido, 'rm-filter-bar');

            if ($tieneFiltroReactivo && ! $usaBarraCanonica) {
                $sinBarraCanonica[] = $rutaRelativa;
            }
        }

        $this->assertSame(
            [],
            $sinBarraCanonica,
            'Estas vistas todavía tienen filtros fuera del diseño unificado: '.implode(', ', $sinBarraCanonica)
        );
    }

    public function test_el_estilo_canonico_usa_tokens_semanticos_y_no_colores_fijos(): void
    {
        $css = file_get_contents(resource_path('frontend/styles/design-system/components/filters.css'));
        $componente = file_get_contents(resource_path('views/components/ui/filter-bar.blade.php'));

        $this->assertStringContainsString('.rm-filter-bar.rm-filter-bar', $css);
        $this->assertStringContainsString('var(--rm-input-bg)', $css);
        $this->assertStringContainsString('var(--rm-primary)', $css);
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-f]{3,8}\b/i', $css);
        $this->assertStringContainsString('rm-filter-bar', $componente);
    }
}
