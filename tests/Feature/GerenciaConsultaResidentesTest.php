<?php

namespace Tests\Feature;

use App\Models\Residente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GerenciaConsultaResidentesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $gerente = User::factory()->create();
        $gerente->assignRole('GERENTE');
        $this->actingAs($gerente);
    }

    public function test_tarjetas_y_tabla_consultan_el_mismo_conjunto_sin_identificadores_visibles(): void
    {
        $residente = Residente::factory()->create(['nombres' => 'Persona', 'apellido_paterno' => 'Sintética', 'apellido_materno' => 'Prueba', 'fecha_nacimiento' => '1940-05-01']);
        $tarjetas = $this->get(route('admin.residentes.index'))->assertOk()->assertSeeText('Persona Sintética Prueba')
            ->assertSeeText('Ver ficha')->assertSeeText('Sin ubicación asignada')->assertDontSee('<th scope="col">Documento', false);
        $this->assertSame('tarjetas', $tarjetas->viewData('vista'));
        $tabla = $this->get(route('admin.residentes.index', ['vista'=>'tabla', 'buscar'=>'Persona Prueba']))
            ->assertOk()->assertSee('<table', false)->assertSeeText('Persona Sintética Prueba');
        $this->assertSame($tarjetas->viewData('residentes')->modelKeys(), $tabla->viewData('residentes')->modelKeys());
        $texto = html_entity_decode(strip_tags($tabla->getContent()));
        $this->assertStringNotContainsString($residente->getKey(), $texto);
        $this->get(route('admin.residentes.index', ['buscar'=>'Sin coincidencia']))->assertOk()->assertSeeText('No hay residentes en esta consulta');
        $this->assertDatabaseCount('residentes', 1);
    }

    public function test_ficha_gerencial_no_carga_ni_ofrece_secciones_clinicas(): void
    {
        $residente = Residente::factory()->create();
        $respuesta = $this->get(route('admin.residentes.show', $residente))->assertOk()
            ->assertSeeText('Ficha administrativa del residente')->assertSeeText('Historia administrativa')
            ->assertDontSee('Prescripciones')->assertDontSee('Registrar diagnóstico');
        $this->assertFalse($respuesta->viewData('persona')->relationLoaded('atenciones'));
        foreach (['documentacion', 'prescripciones'] as $tab) {
            $this->get(route('admin.residentes.show', [$residente, 'tab'=>$tab]))->assertForbidden();
        }
        $this->get(route('admin.residentes.show', [$residente, 'tab'=>'contactos']))->assertOk()->assertSeeText('Contactos autorizados');
        Role::findByName('GERENTE', 'web')->revokePermissionTo('residentes_contactos.ver');
        $this->get(route('admin.residentes.show', [$residente, 'tab'=>'contactos']))->assertForbidden();
        $this->get(route('admin.residentes.show', [$residente, 'tab'=>'ubicacion']))->assertOk()->assertSeeText('Sin registros en esta sección');
        $this->assertDatabaseCount('prescripciones', 0);
    }

    public function test_permiso_revocado_no_se_transforma_en_cero_o_acceso_a_fuentes_restringidas(): void
    {
        $residente = Residente::factory()->create();
        Role::findByName('GERENTE', 'web')->revokePermissionTo('ocupaciones_cama.ver');
        $lista = $this->get(route('admin.residentes.index'))->assertOk()->assertSeeText('Consulta restringida');
        $this->assertNull($lista->viewData('resumen')['con_cama']);
        $this->assertFalse($lista->viewData('residentes')->first()->relationLoaded('ocupacionActiva'));
        $this->get(route('admin.residentes.index', ['alojamiento'=>'con_cama']))->assertForbidden();
        $this->get(route('admin.residentes.show', [$residente, 'tab'=>'ubicacion']))->assertForbidden();
        Role::findByName('GERENTE', 'web')->revokePermissionTo('residentes.ver');
        $this->get(route('admin.residentes.index'))->assertForbidden();
        $this->get(route('admin.residentes.show', $residente))->assertForbidden();
    }
}
