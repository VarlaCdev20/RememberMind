<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarLaboratorioExperimental;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\EscenariosPerfilExperimental;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use Tests\TestCase;

final class LaboratorioPerfilInterfazTest extends TestCase
{
    use RefreshDatabase;

    private function tecnico(): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['nombres' => 'Técnica', 'apellido_paterno' => 'Prueba']);
        $usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($usuario);

        return $usuario;
    }

    public function test_ruta_existente_presenta_laboratorio_sin_ejecutar_el_motor_al_ingresar(): void
    {
        $this->tecnico();
        $this->get(route('admin.sistema-experto.index'))->assertOk()
            ->assertSee('Laboratorio del sistema experto')
            ->assertSee('SIMULACIÓN TÉCNICA')->assertDontSee('Patrón técnico candidato:');
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
    }

    public function test_catalogo_completo_recalcula_la_misma_salida_de_consola_sin_escrituras(): void
    {
        $autor = $this->tecnico();
        $escrituras = [];
        DB::listen(static function ($consulta) use (&$escrituras): void {
            if (preg_match('/^\s*(insert|update|delete|alter|create|drop)\b/i', $consulta->sql)) {
                $escrituras[] = $consulta->sql;
            }
        });
        foreach (array_keys(EscenariosPerfilExperimental::NOMBRES) as $escenario) {
            $esperado = (new EjecutarPerfilExperimental)->ejecutar(...EscenariosPerfilExperimental::entrada($escenario));
            $vista = app(ConsultarLaboratorioExperimental::class)->ejecutar($autor, $escenario);
            self::assertSame($esperado, $vista['resultado']);
            self::assertCount(5, $vista['dominios']);
            self::assertSame(EscenariosPerfilExperimental::NOMBRES[$escenario], $vista['escenario']);
        }
        self::assertSame([], $escrituras);
    }

    public function test_consulta_rechaza_identificador_de_residente_como_caso_experimental(): void
    {
        $autor = $this->tecnico();
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        $this->expectException(DomainException::class);
        app(ConsultarLaboratorioExperimental::class)->ejecutar($autor, 'COD_RES_000030');
    }

    public function test_otros_roles_y_previsualizacion_no_obtienen_acceso_tecnico(): void
    {
        $autor = $this->tecnico();
        foreach (['GERENTE', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'FAMILIAR'] as $rol) {
            $usuario = User::factory()->create();
            $usuario->assignRole($rol);
            $this->actingAs($usuario)->get(route('admin.sistema-experto.index'))->assertForbidden();
        }
        $this->actingAs($autor)->withSession([RolePreviewService::SESSION_KEY => 'GERENTE'])
            ->get(route('admin.sistema-experto.index'))->assertForbidden();
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
    }

    public function test_consulta_revalida_estado_actual_y_permiso_del_actor(): void
    {
        $autor = $this->tecnico();
        $autor->update(['estado' => 'INACTIVO']);
        $this->expectException(AuthorizationException::class);
        app(ConsultarLaboratorioExperimental::class)->ejecutar($autor, 'memoria');
    }

    public function test_consulta_rechaza_ejecucion_experimental_en_produccion(): void
    {
        $autor = $this->tecnico();
        $this->app->instance('env', 'production');
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        $this->expectException(DomainException::class);
        app(ConsultarLaboratorioExperimental::class)->ejecutar($autor, 'memoria');
    }
}
