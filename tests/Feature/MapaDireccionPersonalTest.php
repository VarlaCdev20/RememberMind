<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\Identidad\Infraestructura\Geografia\GeocodificadorDireccionPersonal;
use App\Modulos\Identidad\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalForm;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class MapaDireccionPersonalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default'=>'array']);
        Cache::flush();
        Http::preventStrayRequests();
        $this->seed(RolesAndPermissionsSeeder::class);
        $gerente = User::factory()->create(); $gerente->assignRole('GERENTE'); $this->actingAs($gerente);
    }

    private function respuesta(): array
    {
        return ['address'=>['country_code'=>'bo', 'country'=>'Bolivia', 'state'=>'La Paz', 'city'=>'La Paz',
            'suburb'=>'Barrio sintético', 'road'=>'Calle de prueba', 'house_number'=>'120']];
    }

    private function formulario()
    {
        return Livewire::test(PersonalInstitucionalForm::class)->set('ciudad_id','LPZ')->set('municipio_id','MUN_LPZ');
    }

    public function test_centra_solo_por_region_sin_enviar_identidad_y_reutiliza_cache(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*'=>Http::response([['lat'=>'-16.5','lon'=>'-68.1']])]);
        $form = $this->formulario()->set('nombres','NO ENVIAR NOMBRE')->set('numero_documento','87654321')->set('direccion','NO ENVIAR DOMICILIO');
        $form->call('centrarMapaDireccion')->assertHasNoErrors()->assertSet('direccion','NO ENVIAR DOMICILIO');
        $form->call('centrarMapaDireccion')->assertHasNoErrors();
        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => $r['q']==='LA PAZ, LA PAZ, Bolivia' && $r['countrycodes']==='bo'
            && str_contains($r->header('User-Agent')[0], 'RememberMind') && !str_contains($r->url(),'NO%20ENVIAR'));
    }

    public function test_consulta_punto_no_cambia_direccion_hasta_confirmar_y_completa_componentes(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*'=>Http::response($this->respuesta())]);
        $this->formulario()->set('direccion','DIRECCIÓN ANTERIOR')->call('consultarDireccionMapa',-16.5,-68.1)
            ->assertHasNoErrors()->assertSet('direccion','DIRECCIÓN ANTERIOR')->assertSet('direccionMapa.zona','BARRIO SINTÉTICO')
            ->call('usarDireccionMapa','calle de prueba 120, barrio sintético, la paz, bolivia')->assertHasNoErrors()
            ->assertSet('direccion','CALLE DE PRUEBA 120, BARRIO SINTÉTICO, LA PAZ, BOLIVIA')
            ->assertSet('ciudad_id','LPZ')->assertSet('municipio_id','MUN_LPZ')->assertSet('zona_id','OTRA')
            ->assertSet('otra_zona','BARRIO SINTÉTICO')->assertSet('otra_calle','CALLE DE PRUEBA')->assertSet('direccionMapa',null)
            ->assertDispatched('direccion-personal-completada');
        $this->assertDatabaseCount('personal',0);
    }

    public function test_no_consulta_sin_permiso_o_cuenta_activa(): void
    {
        $form = $this->formulario();
        $actor = auth()->user();
        $actor->forceFill(['estado'=>'SUSPENDIDO'])->save();
        $form->call('consultarDireccionMapa',-16.5,-68.1)->assertForbidden();
        $actor->forceFill(['estado'=>'ACTIVO'])->save();
        $otroFormulario = $this->formulario();
        $this->actingAs(User::factory()->create());
        $otroFormulario->call('consultarDireccionMapa',-16.5,-68.1)->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_coordenadas_invalidas_y_departamento_faltante_no_consultan(): void
    {
        $this->formulario()->call('consultarDireccionMapa',100,-68.1)->assertHasErrors(['latitud']);
        Livewire::test(PersonalInstitucionalForm::class)->call('centrarMapaDireccion')->assertHasErrors(['ciudad_id']);
        Http::assertNothingSent();
    }

    public function test_no_aplica_sugerencia_anterior_tras_cambiar_departamento_ni_texto_invalido(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*'=>Http::response($this->respuesta())]);
        $this->formulario()->call('consultarDireccionMapa',-16.5,-68.1)->call('usarDireccionMapa',str_repeat('A',256))->assertHasErrors(['direccion']);
        $this->formulario()->call('consultarDireccionMapa',-16.5,-68.1)->set('ciudad_id','CBB')
            ->call('usarDireccionMapa','DIRECCIÓN MANIPULADA')->assertHasErrors(['mapa_direccion'])->assertSet('direccion',null);
    }

    public function test_limiter_global_y_cache_evitan_consultas_continuas(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*'=>Http::response($this->respuesta())]);
        $servicio = app(GeocodificadorDireccionPersonal::class);
        $servicio->resolver(-16.5,-68.1,'BO'); $servicio->resolver(-16.5,-68.1,'BO');
        try { $servicio->resolver(-16.6,-68.2,'BO'); $this->fail('Debe limitar la consulta global.'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('mapa_direccion',$e->errors()); }
        Http::assertSentCount(1);
    }

    public function test_red_sin_cobertura_o_pais_distinto_no_fingen_direccion(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*'=>Http::response(['address'=>['country_code'=>'pe']],200)]);
        $this->formulario()->call('consultarDireccionMapa',-16.5,-68.1)->assertHasErrors(['mapa_direccion'])->assertSet('direccionMapa',null);
        Cache::flush(); Http::fake(['nominatim.openstreetmap.org/*'=>Http::response([],503)]);
        $this->formulario()->call('centrarMapaDireccion')->assertHasErrors(['mapa_direccion'])->assertSet('direccion',null);
        Cache::flush(); Http::fake(['nominatim.openstreetmap.org/*'=>Http::failedConnection()]);
        $this->formulario()->call('centrarMapaDireccion')->assertHasErrors(['mapa_direccion']);
    }
}
