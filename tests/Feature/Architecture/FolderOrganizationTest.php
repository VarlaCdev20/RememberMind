<?php

namespace Tests\Feature\Architecture;

use Illuminate\Support\Facades\File;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Tests\TestCase;

class FolderOrganizationTest extends TestCase
{
    public function test_la_estructura_canonica_no_tiene_raices_duplicadas(): void
    {
        $this->assertCount(12, File::directories(app_path('Modulos')));
        foreach (File::directories(app_path('Modulos')) as $modulo) {
            foreach (['Dominio', 'Aplicacion', 'Infraestructura', 'Presentacion'] as $capa) {
                $this->assertDirectoryExists($modulo.'/'.$capa);
            }
            $this->assertFileExists($modulo.'/Presentacion/Rutas/web.php');
        }
        $this->assertDirectoryDoesNotExist(app_path('Backend'));
        $this->assertDirectoryDoesNotExist(app_path('Frontend'));
        $this->assertDirectoryExists(app_path('Http/Controllers'));

        $this->assertDirectoryDoesNotExist(app_path('Actions'));
        $this->assertDirectoryDoesNotExist(app_path('Livewire'));
        $this->assertDirectoryDoesNotExist(app_path('Services'));
        $this->assertDirectoryDoesNotExist(app_path('Backend/ApoyoDecision'));
    }

    public function test_los_namespaces_coinciden_con_su_ruta_fisica(): void
    {
        $this->assertNamespacesMatchPath(app_path('Modulos'), 'App\\Modulos');
        $this->assertNamespacesMatchPath(app_path('Compartido'), 'App\\Compartido');
        $this->assertNamespacesMatchPath(base_path('motor-experto/src/motor_experto'), 'MotorExperto');
        $this->assertNamespacesMatchPath(app_path('Http/Controllers'), 'App\\Http\\Controllers');
    }

    public function test_las_capas_no_dependen_hacia_presentacion_y_el_dominio_es_puro(): void
    {
        $dominio = $aplicacion = 0;
        foreach (File::allFiles(app_path('Modulos')) as $file) {
            if ($file->getExtension() !== 'php' || str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $path = str_replace('\\', '/', $file->getPathname());
            $content = File::get($path);
            if (str_contains($path, '/Aplicacion/')) {
                $aplicacion++;
                $this->assertDoesNotMatchRegularExpression('/^use\s+.*\\\\Presentacion\\\\/m', $content, $path);
            }
            if (str_contains($path, '/Dominio/')) {
                $dominio++;
                $this->assertDoesNotMatchRegularExpression('/^use\s+(?:Illuminate|Livewire|App\\\\Models)\\\\/m', $content, $path);
                $this->assertDoesNotMatchRegularExpression('/\b(?:config|app|auth|request|view|now)\s*\(/', $content, $path);
            }
            $this->assertStringNotContainsString('App\\Backend\\', $content, $path);
            $this->assertStringNotContainsString('App\\Frontend\\', $content, $path);
        }
        $this->assertGreaterThan(0, $dominio);
        $this->assertGreaterThan(0, $aplicacion);
        foreach (File::allFiles(base_path('motor-experto/src')) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $this->assertDoesNotMatchRegularExpression('/^use\s+(?:App|Illuminate|Livewire)\\\\/m', File::get($file->getPathname()), $file->getPathname());
        }
        foreach (['app', 'database', 'routes', 'config', 'bootstrap'] as $directory) {
            foreach (File::allFiles(base_path($directory)) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                $content = File::get($file->getPathname());
                $this->assertStringNotContainsString('App\\Backend\\', $content, $file->getPathname());
                $this->assertStringNotContainsString('App\\Frontend\\', $content, $file->getPathname());
            }
        }
    }

    public function test_el_contrato_ordenado_de_rutas_permanece_identico(): void
    {
        $actuales = [];
        foreach (app('router')->getRoutes() as $ruta) {
            $actuales[] = ['methods' => $ruta->methods(), 'uri' => $ruta->uri(), 'name' => $ruta->getName(),
                'action' => $ruta->getActionName(), 'middleware' => $ruta->gatherMiddleware(),
                'domain' => $ruta->getDomain(), 'defaults' => $ruta->defaults,
                'wheres' => $ruta->wheres, 'bindingFields' => $ruta->bindingFields()];
        }
        $esperadas = json_decode(File::get(base_path('tests/Fixtures/Architecture/routes.json')), true, 512, JSON_THROW_ON_ERROR);
        // Incluye consulta/gobierno y los dos puertos aprobados de conciliación/transcripción.
        $this->assertCount(304, $actuales);
        $this->assertSame(
            $this->normalizarPrefijosLivewire($esperadas),
            $this->normalizarPrefijosLivewire($actuales, ltrim(EndpointResolver::prefix(), '/')),
        );
    }

    private function normalizarPrefijosLivewire(array $rutas, ?string $prefijoActual = null): array
    {
        // El prefijo depende de APP_KEY; los contratos y sufijos permanecen exactos.
        $endpoints = [
            'update', 'livewire.min.js', 'livewire.min.js.map', 'livewire.csp.min.js.map',
            'upload-file', 'preview-file/{filename}', 'js/{component}.js',
            'css/{component}.css', 'css/{component}.global.css',
        ];

        $normalizadas = 0;
        foreach ($rutas as &$ruta) {
            if (preg_match('/^livewire-(?:[a-f0-9]{8}|\{hash\})\/(.+)$/D', $ruta['uri'], $coincidencia)
                && in_array($coincidencia[1], $endpoints, true)) {
                if ($prefijoActual !== null) {
                    $this->assertSame($prefijoActual, explode('/', $ruta['uri'], 2)[0]);
                }
                $ruta['uri'] = 'livewire-{hash}/'.$coincidencia[1];
                $normalizadas++;
            }
        }
        unset($ruta);

        $this->assertSame(9, $normalizadas);

        return $rutas;
    }

    private function assertNamespacesMatchPath(string $root, string $rootNamespace): void
    {
        foreach (File::allFiles($root) as $file) {
            if ($file->getExtension() !== 'php' || str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            if (! preg_match('/\b(?:class|interface|enum|trait)\s+\w+/', File::get($file->getPathname()))) {
                continue;
            }

            $relativeDirectory = str_replace(
                ['/', DIRECTORY_SEPARATOR],
                '\\',
                trim($file->getRelativePath(), '/\\'),
            );
            $expectedNamespace = $rootNamespace
                .($relativeDirectory === '' ? '' : '\\'.$relativeDirectory);

            $this->assertMatchesRegularExpression(
                '/^namespace\s+'.preg_quote($expectedNamespace, '/').';/m',
                File::get($file->getPathname()),
                "El namespace de {$file->getRelativePathname()} no coincide con su carpeta.",
            );
        }
    }
}
