<?php

namespace Tests\Feature;

use App\Compartido\Presentacion\Componentes\DashboardWelcomeHeader;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarConocimientoAdministrativo;
use App\Modulos\Cuidados\Aplicacion\Consultas\ConsultarCuidadosFicha;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ConsultasPorCapaTest extends TestCase
{
    use RefreshDatabase;

    public function test_signos_no_prepara_consultas_de_otras_pestanas(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('ADMINISTRADOR');
        $residente = Residente::factory()->create();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $datos = app(ConsultarCuidadosFicha::class)->consultar($residente, 'signos', $actor);
        $this->assertSame([], $datos);
        $sql = implode(' ', array_column(DB::getQueryLog(), 'query'));
        foreach (['ejecuciones_cuidado', 'intervenciones_cuidado', 'planes_cuidado', 'pases_turno'] as $tabla) {
            $this->assertStringNotContainsString($tabla, $sql);
        }
        DB::disableQueryLog();
    }

    public function test_consulta_de_cuidados_rechaza_residente_fuera_del_alcance_de_enfermeria(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('ENFERMEROS');
        $residente = Residente::factory()->create();
        $this->expectException(HttpException::class);
        app(ConsultarCuidadosFicha::class)->consultar($residente, 'cuidado', $actor);
    }

    public function test_consulta_de_conocimiento_exige_autorizacion_sin_la_ui(): void
    {
        $this->actingAs(User::factory()->create());
        $this->expectException(AuthorizationException::class);
        app(ConsultarConocimientoAdministrativo::class)->versiones();
    }

    public function test_vista_del_encabezado_consume_proyeccion_sin_consultar_personas(): void
    {
        $usuario = User::factory()->create();
        $this->actingAs($usuario);
        $componente = new DashboardWelcomeHeader(usuario: $usuario);
        $vista = $componente->render()->with($componente->data());
        DB::enableQueryLog();
        DB::flushQueryLog();
        $html = $vista->render();
        $this->assertStringContainsString('rm-dashboard-header', $html);
        $this->assertSame([], DB::getQueryLog());
        DB::disableQueryLog();
    }
}
