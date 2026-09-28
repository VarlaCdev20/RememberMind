<?php

namespace Tests\Feature\Architecture;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FolderOrganizationTest extends TestCase
{
    public function test_la_estructura_canonica_no_tiene_raices_duplicadas(): void
    {
        $this->assertDirectoryExists(app_path('Backend/Modulos'));
        $this->assertDirectoryExists(app_path('Frontend/Livewire'));
        $this->assertDirectoryExists(app_path('Http/Controllers'));

        $this->assertDirectoryDoesNotExist(app_path('Actions'));
        $this->assertDirectoryDoesNotExist(app_path('Livewire'));
        $this->assertDirectoryDoesNotExist(app_path('Services'));
        $this->assertDirectoryDoesNotExist(app_path('Backend/ApoyoDecision'));
    }

    public function test_los_namespaces_coinciden_con_su_ruta_fisica(): void
    {
        $this->assertNamespacesMatchPath(app_path('Backend'), 'App\\Backend');
        $this->assertNamespacesMatchPath(app_path('Frontend'), 'App\\Frontend');
        $this->assertNamespacesMatchPath(app_path('Http/Controllers'), 'App\\Http\\Controllers');
    }

    private function assertNamespacesMatchPath(string $root, string $rootNamespace): void
    {
        foreach (File::allFiles($root) as $file) {
            if ($file->getExtension() !== 'php') {
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
