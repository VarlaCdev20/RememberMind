<?php

namespace Tests\Feature;

use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\SignosVitalesService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Identidad\Aplicacion\Contratos\EstadoPrevisualizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DesacoplamientoContextoTest extends TestCase
{
    use RefreshDatabase;

    public function test_adaptador_lee_la_peticion_vigente_aunque_se_resolvio_antes(): void
    {
        $this->app->instance('request', Request::create('/consulta', 'GET'));
        $contexto = app(ContextoEjecucion::class);
        $this->assertTrue($contexto->esLectura());
        foreach (['POST', 'HEAD', 'PUT', 'DELETE'] as $metodo) {
            $this->app->instance('request', Request::create('/consulta', $metodo));
            $this->assertFalse($contexto->esLectura());
        }
        $this->app->instance('request', Request::create('/otra', 'GET'));
        $this->assertTrue($contexto->esLectura());
    }

    public function test_contexto_no_retiene_la_identidad_de_una_cuenta_anterior(): void
    {
        $contexto = app(ContextoEjecucion::class);
        $this->assertNull($contexto->usuario());
        $uno = User::factory()->create();
        $dos = User::factory()->create();
        $this->actingAs($uno);
        $this->assertSame($uno->getKey(), $contexto->usuario()->getKey());
        $this->actingAs($dos);
        $this->assertSame($dos->getKey(), $contexto->usuario()->getKey());
        auth()->forgetGuards();
        $this->assertNull($contexto->usuario());
    }

    public function test_aplicacion_resuelve_actor_mediante_contrato_sin_sesion_http(): void
    {
        Role::findOrCreate('SUPERADMINISTRADOR', 'web');
        $usuario = User::factory()->create();
        $usuario->assignRole('SUPERADMINISTRADOR');
        $contexto = $this->contexto($usuario);
        $this->assertNull(auth()->user());
        $this->assertTrue((new TurnoEnfermeriaService($contexto))->esSuperAdmin());
    }

    public function test_identidad_recibida_no_sustituye_actor_confiable_y_no_persiste_signos(): void
    {
        $autor = User::factory()->create();
        $otro = User::factory()->create();
        foreach ([null, $otro] as $actorConfiable) {
            $this->app->instance(ContextoEjecucion::class, $this->contexto($actorConfiable));
            try {
                app(SignosVitalesService::class)->registrarConEvaluacion('RES_INEXISTENTE', [], $autor);
                $this->fail('Debe rechazarse la atribución a otra identidad.');
            } catch (HttpException $error) {
                $this->assertSame(403, $error->getStatusCode());
            }
            $this->assertDatabaseCount('signos_vitales', 0);
            $this->assertDatabaseCount('alertas', 0);
        }
    }

    public function test_preview_con_estado_en_memoria_conserva_roles_y_limpia_cambio_de_actor(): void
    {
        Role::findOrCreate('SUPERADMINISTRADOR', 'web');
        $super = User::factory()->create();
        $super->assignRole('SUPERADMINISTRADOR');
        $otro = User::factory()->create();
        $estado = new class implements EstadoPrevisualizacion
        {
            public mixed $rol = null;

            public function obtener(): mixed
            {
                return $this->rol;
            }

            public function guardar(string $rol): void
            {
                $this->rol = $rol;
            }

            public function limpiar(): void
            {
                $this->rol = null;
            }
        };
        $preview = new RolePreviewService($estado);
        $preview->activate($super, 'ENFERMEROS');
        $this->assertSame('ENFERMEROS', $preview->activeRole($super));
        $this->assertSame(['SUPERADMINISTRADOR'], $super->fresh()->getRoleNames()->all());
        $this->assertNull($preview->activeRole($otro));
        $this->assertNull($estado->rol);
        $estado->rol = 'ROL_INVENTADO';
        $this->assertNull($preview->activeRole($super));
        $this->assertNull($estado->rol);
    }

    private function contexto(?User $usuario): ContextoEjecucion
    {
        return new class($usuario) implements ContextoEjecucion
        {
            public function __construct(private readonly ?User $actor) {}

            public function usuario(): ?User
            {
                return $this->actor;
            }

            public function esLectura(): bool
            {
                return false;
            }
        };
    }
}
