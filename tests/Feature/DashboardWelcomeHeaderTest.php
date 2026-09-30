<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardWelcomeHeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public');
    }

    public function test_muestra_nombre_rol_y_reloj_sin_utilidades_duplicadas(): void
    {
        $usuario = User::factory()->create([
            'nombres' => 'Ana',
            'ap_paterno' => 'Torres',
            'ap_materno' => 'Mamani',
        ]);
        $usuario->assignRole('ENFERMEROS');

        $html = Blade::render('<x-ui.dashboard-welcome-header :usuario="$usuario" />', compact('usuario'));

        $this->assertStringContainsString('Bienvenido, Ana Torres Mamani', $html);
        $this->assertStringContainsString('Enfermeros', $html);
        $this->assertStringNotContainsString('ph-bell', $html);
        $this->assertStringNotContainsString('<input', $html);
        $this->assertStringNotContainsString('rm-nursing-welcome__profile', $html);
        $this->assertStringContainsString('data-time-zone="'.config('app.timezone').'"', $html);
        $this->assertStringContainsString('setInterval(() => this.actualizar(), 60000)', $html);
        $this->assertSame(2, substr_count($html, '<img'));
        $this->assertStringContainsString('621786801_1404497435021508_7880315777607437580_n.jpg', $html);
        $this->assertStringContainsString('558487013_1337134818424437_2282337776297854403_n.jpg', $html);
    }

    public function test_usa_fotos_institucionales_y_soporta_nombre_largo(): void
    {
        Storage::disk('public')->put('usuarios/fotos/ana.jpg', 'foto-de-prueba');
        $usuario = User::factory()->create([
            'nombres' => 'María Fernanda de los Ángeles',
            'ap_paterno' => 'Gutiérrez',
            'ap_materno' => 'Quispe',
            'foto' => 'usuarios/fotos/ana.jpg',
        ]);

        $html = Blade::render('<x-ui.dashboard-welcome-header :usuario="$usuario" />', compact('usuario'));

        $this->assertStringContainsString('Bienvenido, María Fernanda de los Ángeles Gutiérrez Quispe', $html);
        $this->assertStringNotContainsString('usuarios/fotos/ana.jpg', $html);
        $this->assertSame(2, substr_count($html, '<img'));
    }

    public function test_sin_personal_usa_fallback_neutro(): void
    {
        $usuario = User::factory()->create();

        $html = Blade::render('<x-ui.dashboard-welcome-header :usuario="$usuario" />', compact('usuario'));

        $this->assertStringContainsString('Bienvenido, equipo de Enfermería', $html);
        $this->assertStringNotContainsString('ROSA MAMANI', $html);
    }

    public function test_normaliza_nombre_en_mayusculas_sin_perder_particulas(): void
    {
        $usuario = User::factory()->create([
            'nombres' => 'ROSA MARÍA',
            'ap_paterno' => 'DE LOS ÁNGELES',
            'ap_materno' => 'CHOQUE',
        ]);

        $html = Blade::render('<x-ui.dashboard-welcome-header :usuario="$usuario" />', compact('usuario'));

        $this->assertStringContainsString('Bienvenido, Rosa María de los Ángeles Choque', $html);
    }
}
