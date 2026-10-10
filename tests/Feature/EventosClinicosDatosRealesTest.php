<?php

namespace Tests\Feature;

use App\Backend\Modulos\Clinica\Servicios\EventosClinicosService;
use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Models\Residente;
use App\Models\Incidente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EventosClinicosDatosRealesTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->usuario = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Elena',
            'ap_paterno' => 'Rojas',
        ]);
        $this->usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($this->usuario);
    }

    public function test_residente_sin_incidentes_no_recibe_eventos_de_demostracion(): void
    {
        $residente = Residente::factory()->create();

        $eventos = app(EventosClinicosService::class)->obtenerEventos($residente);

        $this->assertCount(0, $eventos);

        $componente = Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->call('cambiarTab', 'eventos')
            ->assertSee('No se encontraron eventos clínicos')
            ->assertDontSee('Laura González')
            ->assertDontSee('Habitación 101')
            ->assertDontSee('Hipotensión matutina');

        $this->assertSame([
            'activos' => 0,
            'en_seguimiento' => 0,
            'resueltos' => 0,
            'criticos' => 0,
        ], $componente->instance()->metricasEventos);
    }

    public function test_incidente_real_conserva_solo_los_datos_persistidos(): void
    {
        $residente = Residente::factory()->create();

        Incidente::create([
            'cod_incidente' => 'INC_REAL_001',
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $this->usuario->personal->cod_personal,
            'tipo_incidente' => 'CAIDA',
            'gravedad' => 'GRAVE',
            'lugar' => 'Patio norte',
            'fecha_hora' => now()->subHour(),
            'descripcion' => 'Pérdida de equilibrio observada durante la marcha.',
            'medida_inmediata' => 'Se suspendió la marcha y se notificó al médico.',
            'requiere_medico' => true,
            'requiere_derivacion' => false,
            'estado' => 'EN_SEGUIMIENTO',
            'observacion' => null,
        ]);

        $evento = app(EventosClinicosService::class)->obtenerEventos($residente)->sole();

        $this->assertSame('INC_REAL_001', $evento['id']);
        $this->assertSame('Patio norte', $evento['lugar']);
        $this->assertSame('Pérdida de equilibrio observada durante la marcha.', $evento['descripcion_completa']);
        $this->assertSame('No registrados en este incidente', $evento['valoracion']['signos_vitales']);
        $this->assertSame('No registrada', $evento['proxima_evaluacion_fecha']);
        $this->assertCount(1, $evento['intervenciones']);
        $this->assertEmpty($evento['documentos']);
    }
}
