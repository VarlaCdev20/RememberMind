<?php
namespace Tests\Feature;

use App\Models\{AdultoMayor, User, MedicacionAdulto, AdministracionMedicacion, AtencionAdulto, TipoAtencionAdulto};
use Database\Seeders\{EstadoAdultoSeeder, RolesAndPermissionsSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrosClinicosTest extends TestCase
{
    use RefreshDatabase;

    private function contexto(): array
    {
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);
        return [$user, AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001'])];
    }

    public function test_administracion_acepta_codigo_real_y_rechaza_medicacion_de_otro_adulto(): void
    {
        [, $adulto] = $this->contexto();
        $otro = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
        $med = MedicacionAdulto::create(['cod_am' => $adulto->cod_am, 'nombre_medicamento' => 'Orden de prueba', 'dosis' => 'Según orden', 'frecuencia' => 'Diaria', 'fecha_inicio' => today()]);
        $datos = ['cod_med_adulto' => $med->cod_med_adulto, 'fecha' => today()->format('Y-m-d'), 'hora_programada' => '08:00', 'administrado' => false, 'motivo_omision' => 'Rechazo registrado'];
        $this->post(route('admin.adultos-mayores.administracion-medicacion.store', $otro), $datos)->assertSessionHasErrors('cod_med_adulto');
        $this->assertDatabaseCount('administracion_medicacion', 0);
        $this->post(route('admin.adultos-mayores.administracion-medicacion.store', $adulto), $datos)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertDatabaseHas('administracion_medicacion', ['cod_am' => $adulto->cod_am, 'cod_med_adulto' => $med->cod_med_adulto, 'administrado' => false]);
        $this->assertDatabaseHas('alertas_adulto', ['cod_am' => $adulto->cod_am, 'origen' => 'MEDICACION']);
    }

    public function test_anular_atencion_conserva_registro_y_no_permite_modificar_otro_residente(): void
    {
        [, $adulto] = $this->contexto();
        $otro = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
        $tipo = TipoAtencionAdulto::create(['nombre' => 'Seguimiento', 'estado' => 'ACTIVO']);
        $this->post(route('admin.adultos-mayores.atenciones.store', $adulto), [
            'fecha' => today()->format('Y-m-d'), 'hora' => '08:00', 'cod_tipo_aten' => $tipo->cod_tipo_aten,
            'estado' => 'REALIZADA', 'obs' => 'Atención de seguimiento registrada.',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');
        $atencion = AtencionAdulto::sole();
        $this->get(route('admin.adultos-mayores.atenciones.index', [$adulto, 'buscar' => 'seguimiento']))
            ->assertOk()->assertSee('Atención de seguimiento registrada.')->assertSee('Seguimiento');
        $this->delete(route('admin.adultos-mayores.atenciones.destroy', [$otro, $atencion]))->assertNotFound();
        $this->delete(route('admin.adultos-mayores.atenciones.destroy', [$adulto, $atencion]))->assertRedirect();
        $this->assertDatabaseHas('atenciones_adulto', ['cod_aten_adul' => $atencion->cod_aten_adul, 'estado' => 'ANULADO']);
        $this->patch(route('admin.adultos-mayores.atenciones.restore', [$adulto, $atencion]))->assertRedirect();
        $this->assertDatabaseCount('atenciones_adulto', 1);
    }

    public function test_notas_permiten_registrar_buscar_corregir_anular_y_restaurar_desde_sus_rutas(): void
    {
        [, $adulto] = $this->contexto();
        $datos = ['fecha' => today()->format('Y-m-d'), 'tipo_obs' => 'GENERAL',
            'descripcion' => 'Evolución registrada para la prueba.', 'cod_est_adul' => $adulto->cod_est_adul];
        $this->post(route('admin.adultos-mayores.observaciones.store', $adulto), $datos)
            ->assertSessionHasNoErrors();
        $nota = \App\Models\ObsAdulto::sole();
        $this->get(route('admin.adultos-mayores.observaciones.index', [$adulto, 'buscar' => 'Evolución']))
            ->assertOk()->assertSee($datos['descripcion'])->assertSee('Corregir nota');
        $this->patch(route('admin.adultos-mayores.observaciones.update', [$adulto, $nota]), [
            'descripcion' => 'Evolución corregida y conservada.', 'tipo_obs' => 'GENERAL',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Evolución corregida y conservada.', $nota->fresh()->descripcion);
        $this->delete(route('admin.adultos-mayores.observaciones.destroy', [$adulto, $nota]))->assertRedirect();
        $this->assertSoftDeleted('obs_adulto', ['cod_obs_adul' => $nota->cod_obs_adul]);
        $this->get(route('admin.adultos-mayores.observaciones.index', $adulto))->assertOk()->assertSee('ANULADA');
        $this->patch(route('admin.adultos-mayores.observaciones.restore', [$adulto, $nota->cod_obs_adul]))->assertRedirect();
        $this->assertFalse($nota->fresh()->trashed());
    }
}
