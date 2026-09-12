<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\FichaPaciente;
use App\Models\AdultoMayor;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\User;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FichaCuidadosTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private AdultoMayor $adulto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Laura',
            'ap_paterno' => 'González',
        ]);
        $this->enfermero->assignRole('SUPERADMINISTRADOR');

        $hab = Habitacion::create([
            'codigo' => 'H-102',
            'nombre' => 'Habitacion 102',
            'piso' => '1',
            'tipo' => 'DOBLE',
            'estado' => 'DISPONIBLE',
        ]);
        $cama = Cama::create([
            'cod_habitacion' => $hab->cod_habitacion,
            'numero' => 'A',
            'codigo' => 'C-102-A',
            'estado' => 'DISPONIBLE',
        ]);

        $this->adulto = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres' => 'Rosa María',
            'ap_paterno' => 'Gómez',
            'cod_habitacion' => $hab->cod_habitacion,
            'cod_cama' => $cama->cod_cama,
        ]);

        $this->actingAs($this->enfermero);
    }

    public function test_pestana_cuidados_contiene_encabezado_y_6_kpis(): void
    {
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('cambiarTab', 'cuidados')
            ->assertSee('Plan de cuidados')
            ->assertSee('Cuidados asistenciales, confort y seguimiento diario')
            ->assertSee('Enfermería ejecuta y registra cuidados programados')
            ->assertSee('Ver indicaciones generales')
            ->assertSee('Cuidados activos')
            ->assertSee('Realizados hoy')
            ->assertSee('Pendientes')
            ->assertSee('Incidencias')
            ->assertSee('Cuidados PRN')
            ->assertSee('Cumplimiento')
            ->assertSee('83%')
            ->assertSee('5 de 6 cuidados');
    }

    public function test_pestana_cuidados_contiene_filtros_tabla_y_bloques_prn_e_historico(): void
    {
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('cambiarTab', 'cuidados')
            ->assertSee('Todos')
            ->assertSee('Por turno')
            ->assertSee('Pendientes')
            ->assertSee('Realizados')
            ->assertSee('PRN')
            ->assertSee('Hoy, 12 de septiembre de 2026')
            // Tabla de cuidados
            ->assertSee('Higiene y aseo')
            ->assertSee('Movilización asistida')
            ->assertSee('Cambio de posición')
            ->assertSee('Hidratación asistida')
            ->assertSee('Control de eliminación')
            ->assertSee('Vigilancia de piel')
            ->assertSee('Registrar')
            // Bloque PRN
            ->assertSee('Cuidados PRN')
            ->assertSee('Cuidados a demanda según necesidad del residente')
            ->assertSee('Medidas de confort por dolor leve')
            ->assertSee('Registrar cuidado')
            // Histórico
            ->assertSee('Histórico de cuidados')
            ->assertSee('Ver todos');
    }

    public function test_drawer_de_detalle_de_cuidado_y_registro_directo(): void
    {
        $component = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('cambiarTab', 'cuidados')
            ->assertSee('PANEL LATERAL DE CONSULTA')
            ->assertSee('DETALLE DE CUIDADO')
            ->assertSee('Información del cuidado')
            ->assertSee('Último registro')
            ->assertSee('Próximo cuidado')
            ->assertSee('Estado actual')
            ->assertSee('Alertas y precauciones')
            ->assertSee('Documentos relacionados');

        // Probar registro directo mediante Livewire
        $component->call('registrarCuidadoDirecto', 'Movilización asistida', 'REALIZADA', 'Toleró deambulación asistida sin mareos')
            ->assertDispatched('swal');
    }
}
