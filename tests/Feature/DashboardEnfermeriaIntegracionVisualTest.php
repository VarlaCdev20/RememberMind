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

    public function test_dashboard_de_enfermeria_conserva_su_acento_en_usuarios_con_varios_roles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('MEDICO GENERAL/GERIATRA', 'ENFERMEROS');

        $this->actingAs($usuario)->get(route('admin.enfermeria.dashboard'))
            ->assertOk()
            ->assertSee('data-role="nursing"', false)
            ->assertSee('data-accent="nursing"', false);
    }

    public function test_dashboard_tiene_jerarquia_unica_sin_utilidades_ficticias(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ENFERMEROS');

        $html = $this->actingAs($usuario)->get(route('admin.enfermeria.dashboard'))
            ->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertMatchesRegularExpression('~<header[^>]*class="[^"]*rm-nursing-dashboard__welcome[^\"]*"~', $html);
        $this->assertStringNotContainsString('rm-dashboard-header--compact', $html);
        $this->assertStringContainsString('class="rm-dashboard-header__visual"', $html);
        $this->assertStringContainsString('489963938_1158744422930145_8442506970304201426_n.jpg', $html);
        $this->assertStringContainsString('rm-dashboard-divider', $html);
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

        foreach (['Residentes del turno', 'Alertas prioritarias', 'Evolución de incidentes',
            'Estado de seguimiento', 'Cuidados pendientes', 'Agenda de medicación y cuidados',
            'Tareas del turno', 'Ubicación de residentes', 'Conducta y estado emocional', 'Controles por residente'] as $titulo) {
            $this->assertStringContainsString($titulo, $html);
        }
        $this->assertStringNotContainsString('Ocupación de camas', $html);
        $this->assertStringNotContainsString('Tendencia de ocupación', $html);
        $anterior = -1;
        foreach (['aria-label="Alertas prioritarias"', 'id="nursing-schedule-title"',
            'id="nursing-tasks-title"', 'id="nursing-controls-title"', 'id="nursing-notes-title"',
            'aria-label="Residentes del turno"'] as $seccion) {
            $posicion = strpos($html, $seccion);
            $this->assertNotFalse($posicion, $seccion);
            $this->assertGreaterThan($anterior, $posicion, 'El orden de lectura sigue la prioridad operacional.');
            $anterior = $posicion;
        }
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
