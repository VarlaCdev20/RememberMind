<?php

namespace Tests\Feature\Architecture;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FrontendArchitectureTest extends TestCase
{
    /**
     * Protege que los componentes genéricos en components/ui no conozcan modelos Eloquent ni ejecuten consultas.
     */
    public function test_components_ui_no_importan_modelos(): void
    {
        $uiPath = resource_path('views/components/ui');
        $this->assertDirectoryExists($uiPath);

        $violaciones = [];

        foreach (File::files($uiPath) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contenido = File::get($file->getPathname());

            if (preg_match('/App\\\\Models\\\\\w+/i', $contenido)
                || preg_match('/(?:Residente|User|AdultoMayor|Alerta|TurnoEnfermeria|Atencion)::(?:query|where|find|all)/i', $contenido)) {
                $violaciones[] = $file->getFilename();
            }
        }

        $this->assertEmpty(
            $violaciones,
            'Los siguientes componentes en components/ui no deben importar ni consultar modelos Eloquent: ' . implode(', ', $violaciones)
        );
    }

    /**
     * Protege que los patrones visuales en components/patterns no conozcan modelos Eloquent ni ejecuten consultas.
     */
    public function test_components_patterns_no_importan_modelos(): void
    {
        $patternsPath = resource_path('views/components/patterns');
        $this->assertDirectoryExists($patternsPath, 'El directorio components/patterns debe existir.');

        $violaciones = [];

        foreach (File::files($patternsPath) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contenido = File::get($file->getPathname());

            if (preg_match('/App\\\\Models\\\\\w+/i', $contenido)
                || preg_match('/(?:Residente|User|AdultoMayor|Alerta|TurnoEnfermeria|Atencion)::(?:query|where|find|all)/i', $contenido)) {
                $violaciones[] = $file->getFilename();
            }
        }

        $this->assertEmpty(
            $violaciones,
            'Los siguientes componentes en components/patterns no deben importar ni consultar modelos Eloquent: ' . implode(', ', $violaciones)
        );
    }

    /**
     * Protege que las nuevas Features Livewire ubiquen sus vistas bajo resources/views/livewire/features/.
     */
    public function test_nuevas_features_vistas_bajo_livewire_features(): void
    {
        $featuresPhpPath = app_path('Frontend/Livewire/Features');
        $this->assertDirectoryExists($featuresPhpPath);

        $featuresViewsPath = resource_path('views/livewire/features');
        $this->assertDirectoryExists($featuresViewsPath);

        $violaciones = [];

        foreach (File::allFiles($featuresPhpPath) as $phpFile) {
            if (! str_ends_with($phpFile->getFilename(), '.php')) {
                continue;
            }

            $contenido = File::get($phpFile->getPathname());

            // Extraer la vista devuelta en el método render: view('...')
            if (preg_match("/view\(['\"]([^'\"]+)['\"]/i", $contenido, $coincidencias)) {
                $vistaDeclarada = $coincidencias[1];
                if (! str_starts_with($vistaDeclarada, 'livewire.features.')) {
                    $violaciones[] = "{$phpFile->getFilename()} declara la vista '{$vistaDeclarada}' fuera de livewire.features.*";
                }
            }
        }

        $this->assertEmpty(
            $violaciones,
            'Las Features Livewire deben usar vistas bajo resources/views/livewire/features/: ' . implode(', ', $violaciones)
        );
    }

    /**
     * Protege que el Design System canónico mantenga sus tokens y entrypoint importado en app.css.
     */
    public function test_design_system_mantiene_entrypoint_y_tokens_validos(): void
    {
        $dsIndex = resource_path('frontend/styles/design-system/index.css');
        $appCss = resource_path('frontend/styles/app.css');

        $this->assertFileExists($dsIndex, 'El entrypoint canónico design-system/index.css debe existir.');
        $this->assertFileExists($appCss, 'El archivo app.css debe existir.');

        $appCssContent = File::get($appCss);
        $this->assertStringContainsString(
            'design-system/index.css',
            $appCssContent,
            'app.css debe importar ./design-system/index.css como parte de la cascada oficial.'
        );

        $tokensRequeridos = [
            'colors.css',
            'typography.css',
            'spacing.css',
            'radius.css',
            'shadows.css',
            'states.css',
            'motion.css',
            'accessibility.css',
            'chart-colors.css',
        ];

        foreach ($tokensRequeridos as $tokenFile) {
            $tokenPath = resource_path("frontend/styles/design-system/tokens/{$tokenFile}");
            $this->assertFileExists($tokenPath, "El token canónico {$tokenFile} debe existir en el Design System.");
        }
    }
}
