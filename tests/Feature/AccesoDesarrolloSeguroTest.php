<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class AccesoDesarrolloSeguroTest extends TestCase
{
    public function test_el_acceso_automatico_no_se_registra_ni_en_local(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        Route::setRoutes(new RouteCollection);
        require base_path('routes/web.php');

        $this->expectException(NotFoundHttpException::class);
        Route::getRoutes()->match(Request::create('/local-login-enfermeria', 'GET'));
    }

    public function test_las_pruebas_arrancan_con_base_en_memoria_y_caches_aisladas(): void
    {
        $this->assertSame('testing', $this->app->environment());
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertFalse($this->app->configurationIsCached());
        $this->assertFalse($this->app->routesAreCached());
        $this->assertNotSame(base_path('bootstrap/cache/config.php'), $this->app->getCachedConfigPath());
    }
}
