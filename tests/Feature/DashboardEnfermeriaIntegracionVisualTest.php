<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardEnfermeriaIntegracionVisualTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_tiene_jerarquia_unica_sin_utilidades_ficticias(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ENFERMEROS');

        $html = $this->actingAs($usuario)->get(route('admin.enfermeria.dashboard'))
            ->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertMatchesRegularExpression('~class="rm-dashboard-header__visual">\s*<img src="[^"]*/images/FOTOS CENTRO DE ADULTOS MAYORES/[^"]+"~', $html);
        $this->assertSame(1, substr_count($html, 'ph-bell text-lg'));
        $this->assertStringNotContainsString('nursing-welcome-search', $html);
        $this->assertStringNotContainsString('Buscar residente, habitación o diagnóstico...', $html);
        $this->assertStringNotContainsString('Indicadores de alertas existentes', $html);
        $this->assertStringNotContainsString('graficoCumplimientoTurno', $html);
        $this->assertStringNotContainsString('graficoDistribucionTurno', $html);
        $this->assertStringNotContainsString('href="#"', $html);
        $this->assertStringContainsString('Asignaciones de Enfermería', $html);
        $this->assertStringNotContainsString('Turno activo del equipo · solo lectura', $html);
        $this->assertStringNotContainsString('images/LOGO.png', $html);
        $this->assertStringContainsString('Sin contexto del turno', $html);
        $this->assertStringNotContainsString('No hay situaciones que requieran atención inmediata.', $html);

        foreach (['Pacientes del turno', 'Alertas prioritarias', 'Evolución de incidentes',
            'Estado de seguimiento', 'Ocupación de camas', 'Agenda de medicación y cuidados',
            'Tareas del turno', 'Ubicación de pacientes', 'Conducta y estado emocional'] as $titulo) {
            $this->assertStringContainsString($titulo, $html);
        }
    }
}
