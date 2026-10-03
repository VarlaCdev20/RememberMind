<?php

namespace Tests\Feature;

use App\Models\User;
use App\Frontend\Livewire\Enfermeria\Cuidados\DashboardTurno;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        $this->assertMatchesRegularExpression('~<header[^>]*class="[^"]*rm-nursing-dashboard__welcome[^\"]*"~', $html);
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
            'Estado de seguimiento', 'Cuidados pendientes', 'Agenda de medicación y cuidados',
            'Tareas del turno', 'Ubicación de pacientes', 'Conducta y estado emocional'] as $titulo) {
            $this->assertStringContainsString($titulo, $html);
        }
        $this->assertStringNotContainsString('Ocupación de camas', $html);
        $this->assertStringNotContainsString('Tendencia de ocupación', $html);
    }

    public function test_actualizacion_del_turno_cambia_la_fecha_al_iniciar_un_nuevo_dia(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ENFERMEROS');

        $this->travelTo(Carbon::parse('2026-10-03 23:59:00'));
        $dashboard = Livewire::actingAs($usuario)->test(DashboardTurno::class)
            ->assertSet('filtroFecha', '2026-10-03');

        $this->travelTo(Carbon::parse('2026-10-04 00:01:00'));
        $dashboard->call('refrescarTurno')->assertSet('filtroFecha', '2026-10-04');
    }
}
