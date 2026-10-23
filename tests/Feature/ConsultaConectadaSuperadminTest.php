<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ExplorarConocimientoExperto;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ConocimientoPanel;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResidentesConocimientoPanel;
use Database\Seeders\PermisosConsultaFuentesExpertasSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Support\InformeCognitivoFixture;
use Tests\TestCase;

final class ConsultaConectadaSuperadminTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($this->usuario);
    }

    public function test_recorrido_regla_condicion_retorno_y_cambio_de_version_no_escribe_historia(): void
    {
        $conocimiento = app(ExplorarConocimientoExperto::class)->obtener(null);
        $regla = array_values($conocimiento['reglas'])[0];
        $condicion = $regla['condiciones'][0]['cod_condicion_regla'];
        $antes = DB::table('evaluaciones_expertas')->count();
        $c = Livewire::test(ConocimientoPanel::class)->call('abrirDato', 'reglas_expertas', $regla['id'])
            ->assertSee('Todos los campos')->assertSee('Conexiones')
            ->call('abrirDato', 'condiciones_regla_experta', $condicion)->assertSet('registroCatalogo', $condicion)
            ->call('volverDato')->assertSet('registroCatalogo', $regla['id']);
        InformeCognitivoFixture::conocimiento($this->usuario);
        $c->call('seleccionarVersion', 'VER_TEST')->assertSet('recorridoDatos', [])->assertSet('detalleAbierto', false);
        self::assertSame($antes, DB::table('evaluaciones_expertas')->count());
    }

    public function test_categorias_arbitrarias_y_registros_ajenos_no_se_exponen(): void
    {
        Livewire::test(ConocimientoPanel::class)->call('abrirDato', 'usuarios', 'ID')->assertStatus(422);
        Livewire::test(ConocimientoPanel::class)->call('abrirDato', 'reglas_expertas', 'AJENO')->assertNotFound();
    }

    public function test_superadmin_selecciona_expediente_y_no_crea_resultados_por_consultar(): void
    {
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO', 'nombres' => 'Elena Consulta Sintética']);
        $antes = DB::table('evaluaciones_expertas')->count();
        Livewire::test(ResidentesConocimientoPanel::class)->call('seleccionar', $residente->getKey())
            ->assertSee('Elena Consulta Sintética')->assertSee('Sin aplicaciones registradas')
            ->assertSee('Sin evaluación histórica publicable')->assertDontSee('Analizar evidencia');
        self::assertSame($antes, DB::table('evaluaciones_expertas')->count());
        Livewire::test(ResidentesConocimientoPanel::class)->call('seleccionar', $residente->getKey())
            ->call('abrirFuente', 'FUENTE_DE_OTRO_RESIDENTE')->assertNotFound();
    }

    public function test_cuenta_inactiva_y_familiar_no_consultan_el_directorio_tecnico(): void
    {
        $this->usuario->update(['estado' => 'INACTIVO']);
        Livewire::test(ResidentesConocimientoPanel::class)->assertForbidden();
        $familiar = User::factory()->create(['estado' => 'ACTIVO']);
        $familiar->assignRole('FAMILIAR');
        $this->actingAs($familiar);
        Livewire::test(ResidentesConocimientoPanel::class)->assertForbidden();
    }

    public function test_reparacion_de_lecturas_es_aditiva_idempotente_y_no_da_firma_clinica(): void
    {
        $medicina = Role::findByName('MEDICO GENERAL/GERIATRA', 'web');
        $super = Role::findByName('SUPERADMINISTRADOR', 'web');
        $medicina->revokePermissionTo('respuestas_instrumento.ver');
        $antes = $medicina->permissions()->pluck('name')->all();
        $this->seed(PermisosConsultaFuentesExpertasSeeder::class);
        self::assertTrue($medicina->fresh()->hasPermissionTo('respuestas_instrumento.ver'));
        self::assertSame([], array_diff($antes, $medicina->fresh()->permissions()->pluck('name')->all()));
        self::assertFalse($super->fresh()->hasPermissionTo('valoraciones_petersen.crear'));
        self::assertFalse($super->fresh()->hasPermissionTo('evaluaciones_expertas.ejecutar_observaciones'));
        $actividades = DB::table('activity_log')->count();
        $this->seed(PermisosConsultaFuentesExpertasSeeder::class);
        self::assertSame($actividades, DB::table('activity_log')->count());
    }
}
