<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ReferenciasFormulariosTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogo_exige_sesion_y_permiso_existente(): void
    {
        $this->get('/admin/referencias-formularios')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/referencias-formularios')->assertForbidden();
    }

    public function test_gerencia_ve_los_19_apartados_sin_crear_registros(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $before = User::count();
        $this->actingAs($actor)->get('/admin/referencias-formularios')
            ->assertOk()->assertSee('Galería local de referencia visual')
            ->assertSee('data-reference-step="adulto-6"', false)
            ->assertSee('data-reference-step="personal-pagina-6"', false)
            ->assertSee('data-reference-step="personal-modal-7"', false);
        $this->assertSame($before, User::count());
    }

    public function test_la_galeria_no_se_expone_en_produccion(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->app->instance('env', 'production');
        $this->actingAs($actor)->get('/admin/referencias-formularios')->assertNotFound();
    }

    public function test_fragmentos_historicos_son_estaticos_y_todos_los_controles_inertes(): void
    {
        $html = File::get(base_path('app/Modulos/Identidad/Presentacion/Vistas/referencias/formularios-historicos.blade.php'));
        $this->assertSame(19, substr_count($html, 'data-reference-step='));
        $this->assertDoesNotMatchRegularExpression('/<script|<form|<iframe|wire:|@click|x-on:|<\?php|\{!!/i', $html);
        preg_match_all('/<(?:input|select|textarea|button)\b[^>]*>/i', $html, $controls);
        $this->assertNotEmpty($controls[0]);
        foreach ($controls[0] as $control) $this->assertMatchesRegularExpression('/\sdisabled(?:\s|=|>)/', $control);
    }
}
