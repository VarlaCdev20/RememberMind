<?php

namespace Tests\Feature;

use App\Models\Contacto;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use App\Modulos\Social\Aplicacion\Acciones\GestionarRedApoyo;
use App\Modulos\Social\Presentacion\Livewire\Paginas\RedApoyoPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GerenciaContactosTest extends TestCase
{
    use RefreshDatabase;

    private User $gerente;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->gerente = User::factory()->create();
        $this->gerente->assignRole('GERENTE');
        $this->actingAs($this->gerente);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
    }

    private function contacto(): Contacto
    {
        return app(GestionarRedApoyo::class)->guardarContacto($this->gerente, ['nombres' => 'Persona sintética', 'apellido_paterno' => 'Prueba', 'celular' => '77712345']);
    }

    private function datos(): array
    {
        return ['parentesco' => 'Hija', 'responsable_principal' => true, 'contacto_emergencia' => false, 'observacion' => 'Vínculo de prueba'];
    }

    public function test_contacto_separado_no_crea_cuenta_personal_residente_o_autorizaciones(): void
    {
        $antes = User::count();
        $c = $this->contacto();
        $this->assertNull($c->cod_usuario);
        $this->assertSame($antes, User::count());
        $this->assertDatabaseCount('personal', 0);
        $this->assertDatabaseCount('residentes_contactos', 0);
        $this->assertDatabaseCount('residentes', 1);
        try {
            app(GestionarRedApoyo::class)->guardarContacto($this->gerente, ['nombres' => 'Solo nombre']);
            $this->fail('Falta identidad.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('apellido_paterno', $e->errors());
        }
    }

    public function test_vinculo_no_concede_informacion_salida_ni_emergencia_por_parentesco(): void
    {
        $c = $this->contacto();
        $v = app(GestionarRedApoyo::class)->vincular($this->gerente, $this->residente->getKey(), $c->getKey(), $this->datos());
        $this->assertFalse($v->autoriza_informacion);
        $this->assertFalse($v->autoriza_salida);
        $this->assertFalse($v->contacto_emergencia);
        $this->assertTrue($v->responsable_principal);
        try {
            app(GestionarRedApoyo::class)->vincular($this->gerente, $this->residente->getKey(), $c->getKey(), $this->datos());
            $this->fail('No puede duplicar.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('contactoId', $e->errors());
        }
        $this->assertDatabaseCount('residentes_contactos', 1);
    }

    public function test_corregir_vinculo_no_reatribuye_extremos_ni_cambia_identidad_o_autorizacion_previa(): void
    {
        $a = app(GestionarRedApoyo::class);
        $c = $this->contacto();
        $v = $a->vincular($this->gerente, $this->residente->getKey(), $c->getKey(), $this->datos());
        $v->update(['autoriza_informacion' => true]);
        $a->vincular($this->gerente, $this->residente->getKey(), $c->getKey(), [...$this->datos(), 'parentesco' => 'Hijo'], $v->getKey());
        $this->assertTrue($v->fresh()->autoriza_informacion);
        $this->assertSame('Persona sintética', $c->fresh()->nombres);
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->expectException(ModelNotFoundException::class);
        try {
            $a->vincular($this->gerente, $otro->getKey(), $c->getKey(), $this->datos(), $v->getKey());
        } finally {
            $this->assertSame($this->residente->getKey(), $v->fresh()->cod_residente);
            $this->assertDatabaseCount('residentes_contactos', 1);
        }
    }

    public function test_telefono_compartido_y_correo_de_cuenta_son_datos_distintos(): void
    {
        $c = $this->contacto();
        $cuenta = User::factory()->create(['correo' => 'acceso@example.test']);
        $cuenta->assignRole('FAMILIAR');
        $c->update(['cod_usuario' => $cuenta->getKey()]);
        app(GestionarRedApoyo::class)->guardarContacto($this->gerente, ['nombres' => $c->nombres, 'apellido_paterno' => $c->apellido_paterno, 'correo' => 'contacto@example.test', 'telefono' => '2222222'], $c->getKey());
        $this->assertSame('acceso@example.test', $cuenta->fresh()->correo);
        $this->assertSame('contacto@example.test', $c->fresh()->correo);
    }

    public function test_vigencia_requiere_motivo_y_preserva_otros_vinculos_y_cuenta(): void
    {
        $a = app(GestionarRedApoyo::class);
        $c = $this->contacto();
        $v = $a->vincular($this->gerente, $this->residente->getKey(), $c->getKey(), $this->datos());
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $v2 = $a->vincular($this->gerente, $otro->getKey(), $c->getKey(), $this->datos());
        $a->cambiarVigencia($this->gerente, $this->residente->getKey(), $v->getKey(), 'INACTIVO', 'Cambio solicitado de prueba');
        $this->assertSame('INACTIVO', $v->fresh()->estado);
        $this->assertSame('ACTIVO', $v2->fresh()->estado);
        $this->assertSame('ACTIVO', $c->fresh()->estado);
        $this->assertDatabaseHas('activity_log', ['event' => 'vigencia_vinculo', 'causer_id' => $this->gerente->getKey()]);
    }

    public function test_directorio_dos_presentaciones_y_alta_desde_formulario(): void
    {
        $c = $this->contacto();
        Livewire::test(RedApoyoPanel::class)->assertSee('Familiares y contactos')->call('cambiarVista', 'personas')->assertSee($c->nombre_completo)
            ->call('nuevoContacto')->set('contacto.nombres', 'Otra persona')->set('contacto.apellido_paterno', 'Sintética')->call('guardarContacto')->assertHasNoErrors()->assertSet('modalContacto', false);
        $this->assertDatabaseCount('contactos', 2);
        $this->assertDatabaseCount('personal', 0);
        $this->assertFalse($this->gerente->can('usuarios.crear'));
    }

    public function test_actor_inactivo_familiar_y_permiso_revocado_no_guardan(): void
    {
        $panel = Livewire::test(RedApoyoPanel::class);
        $this->gerente->removeRole('GERENTE');
        $panel->call('nuevoContacto')->assertForbidden();
        $this->assertDatabaseCount('contactos', 0);
        $familiar = User::factory()->create();
        $familiar->assignRole('FAMILIAR');
        $this->actingAs($familiar);
        $this->get(route('admin.familia-social.red-apoyo'))->assertForbidden();
    }

    public function test_sin_historia_nominal_correccion_y_vigencia_se_denegan_sin_efectos(): void
    {
        $c = $this->contacto();
        $v = ResidenteContacto::create(['cod_residente_contacto' => 'QA_VINCULO_SIN_HIST', 'cod_residente' => $this->residente->getKey(), 'cod_contacto' => $c->getKey(), 'parentesco' => 'Hija', 'estado' => 'ACTIVO', 'responsable_principal' => false, 'contacto_emergencia' => false, 'autoriza_informacion' => false, 'autoriza_salida' => false]);
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->down();
        foreach (['corregir', 'vigencia'] as $operacion) {
            try {
                $a = app(GestionarRedApoyo::class);
                $operacion === 'corregir' ? $a->vincular($this->gerente, $this->residente->getKey(), $c->getKey(), $this->datos(), $v->getKey()) : $a->cambiarVigencia($this->gerente, $this->residente->getKey(), $v->getKey(), 'INACTIVO', 'Motivo registrado');
                $this->fail('No puede sobrescribir sin historia.');
            } catch (HttpException $error) {
                $this->assertSame(409, $error->getStatusCode());
            }
        }
        $this->assertSame('ACTIVO', $v->fresh()->estado);
        $this->assertSame('Hija', $v->fresh()->parentesco);
    }

    public function test_lectura_de_vinculos_no_expone_otras_relaciones_o_cuenta_sin_permiso_de_contactos(): void
    {
        $a = app(GestionarRedApoyo::class);
        $c = $this->contacto();
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $a->vincular($this->gerente, $this->residente->getKey(), $c->getKey(), $this->datos());
        $a->vincular($this->gerente, $otro->getKey(), $c->getKey(), $this->datos());
        $lector = User::factory()->create();
        $lector->givePermissionTo(['residentes.ver', 'residentes_contactos.ver']);
        $this->actingAs($lector);
        $panel = Livewire::test(RedApoyoPanel::class)->set('residenteSeleccionado', $this->residente->getKey());
        $fila = $panel->viewData('contactos')->first();
        $this->assertCount(1, $fila->vinculos);
        $this->assertSame($this->residente->getKey(), $fila->vinculos->first()->cod_residente);
        $this->assertNull($fila->usuario);
        $panel->call('cambiarVista','personas')->assertForbidden();
    }

    public function test_longitudes_estado_residente_y_autorizaciones_manipuladas_se_rechazan_sin_filas(): void
    {
        $a=app(GestionarRedApoyo::class);$c=$this->contacto();
        foreach([['parentesco'=>str_repeat('a',41)],['autoriza_informacion'=>true],['autoriza_salida'=>true]] as $alteracion) {
            try {$a->vincular($this->gerente,$this->residente->getKey(),$c->getKey(),[...$this->datos(),...$alteracion]);$this->fail('No debe admitir datos fuera de contrato.');}
            catch(ValidationException $error){$this->assertNotEmpty($error->errors());}
        }
        $this->residente->update(['estado'=>'EGRESADO']);
        try {$a->vincular($this->gerente,$this->residente->getKey(),$c->getKey(),$this->datos());$this->fail('Requiere residente admitido.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $error){$this->assertSame(422,$error->getStatusCode());}
        $this->assertDatabaseCount('residentes_contactos',0);$this->assertDatabaseCount('versiones_vinculo_contacto',0);
    }
}
