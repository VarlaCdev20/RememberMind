<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarConocimientoCognitivo;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarPerfilInstrumental;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PrepararEvaluacionInstitucional;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use MotorExperto\conservacion\InstantaneaInstrumental;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

final class EjecucionConsultaCognitivaTest extends TestCase
{
    use EsquemaSinTransaccion;

    private User $actor;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-22T12:00:00-04:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->actor->assignRole('PSICOLOGO/A');
        Personal::create(['cod_usuario' => $this->actor->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'EJECUCION-TEST',
            'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->actingAs($this->actor);
        app(RegistrarConocimientoInstitucional::class)->ejecutar($this->actor);
        app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor);
    }

    public function test_petersen_recibe_la_instantanea_instrumental_de_la_misma_consulta(): void
    {
        $consulta = app(ConsultarConocimientoCognitivo::class)->adquirir($this->actor, $this->residente, CarbonImmutable::now());
        self::assertInstanceOf(InstantaneaInstrumental::class, $consulta['_instantanea']);
        $perfil = app(ConsultarPerfilInstrumental::class)->consultar($this->actor, $this->residente, CarbonImmutable::now());
        self::assertArrayHasKey('petersen_institucional', $perfil);
        self::assertSame($consulta['capturas']['fecha_corte'], $perfil['petersen_institucional']['fecha_corte']);
        self::assertFalse($perfil['petersen_institucional']['persistido']);
        self::assertSame('PLAN-CAPTURA-DCL-1', $perfil['plan_captura']['contrato']);
        self::assertCount(5, $perfil['plan_captura']['pendientes']);
        self::assertArrayNotHasKey('_instantanea', $perfil['inferencia_declarativa']);
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_analizar_presenta_la_adquisicion_ejecutada_una_sola_vez(): void
    {
        $pagina = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $adquisiciones = 0;
        DB::listen(static function (QueryExecuted $evento) use (&$adquisiciones): void {
            if (str_contains($evento->sql, 'from "aplicaciones_instrumento"')) {
                $adquisiciones++;
            }
        });
        $pagina->call('analizarEvidencia')->assertHasNoErrors()
            ->assertSet('analisisEjecutado', true)
            ->assertSet('fechaAnalisis', CarbonImmutable::now()->format('Y-m-d\TH:i:s.uP'))
            ->assertSee('Resultado calculado para esta consulta:');
        $pagina->assertSee('Qué información conviene completar o revisar')
            ->assertSee('Guía de registro y fundamento');
        self::assertSame(1, $adquisiciones, 'La acción y su render deben compartir una sola adquisición de medidas.');
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_lectura_sin_permiso_petersen_conserva_mediciones_sin_ampliar_acceso(): void
    {
        $this->actor->roles()->first()->revokePermissionTo('valoraciones_petersen.ver');
        $perfil = app(ConsultarPerfilInstrumental::class)->consultar($this->actor, $this->residente, CarbonImmutable::now());
        self::assertArrayHasKey('capturas_originales', $perfil);
        self::assertArrayNotHasKey('petersen_institucional', $perfil);
        self::assertFalse($perfil['inferencia_declarativa']['persistido']);
        self::assertSame(0, DB::transactionLevel());
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_lectura_sin_version_conserva_originales_sin_afirmar_inferencia(): void
    {
        DB::table('versiones_modelo_experto')->update(['estado' => 'INACTIVO']);
        $perfil = app(ConsultarPerfilInstrumental::class)->consultar($this->actor, $this->residente, CarbonImmutable::now());
        self::assertArrayHasKey('capturas_originales', $perfil);
        self::assertFalse($perfil['inferencia_declarativa']['disponible']);
        self::assertSame('SIN_VERSION_COGNITIVA_VINCULADA', $perfil['inferencia_declarativa']['motivo']);
        self::assertArrayNotHasKey('petersen_institucional', $perfil);
        self::assertSame(0, DB::transactionLevel());
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_render_consulta_una_sola_vez_el_historial_observacional(): void
    {
        $lecturas = 0;
        DB::listen(static function (QueryExecuted $evento) use (&$lecturas): void {
            if (preg_match('/count\s*\(\s*\*\s*\)\s+as\s+"?aggregate/i', $evento->sql) && str_contains($evento->sql, '"evaluaciones_expertas"')
                && in_array('OBSERVACIONAL', $evento->bindings, true) && in_array('OBS-SNAP-1', $evento->bindings, true)) {
                $lecturas++;
            }
        });
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertOk();
        self::assertSame(1, $lecturas);
    }

    public function test_fallo_observacional_no_borra_un_analisis_instrumental_correcto(): void
    {
        $pagina = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        DB::connection()->beforeExecuting(static function (string $sql, array $bindings): void {
            if (str_contains($sql, '"evaluaciones_expertas"') && in_array('OBSERVACIONAL', $bindings, true)
                && in_array('OBS-SNAP-1', $bindings, true)) {
                throw new DomainException('DETALLE-OBS-INTERNO');
            }
        });
        $pagina->call('analizarEvidencia')->assertHasNoErrors()
            ->assertSet('analisisEjecutado', true)
            ->assertSee('Resultado calculado para esta consulta:')
            ->assertSee('No se pudieron consultar los hallazgos observacionales')
            ->assertDontSee('No se pudieron consultar las mediciones instrumentales')
            ->assertDontSee('DETALLE-OBS-INTERNO');
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_la_presentacion_conserva_ambas_salidas_y_la_huella_sin_consultar_datos_actuales(): void
    {
        $dto = app(PrepararEvaluacionInstitucional::class)->preparar($this->actor, $this->residente, CarbonImmutable::now());
        $consultas = 0;
        DB::listen(static function () use (&$consultas): void {
            $consultas++;
        });
        $perfil = app(ConsultarPerfilInstrumental::class)->desdePreparacion($dto);
        self::assertSame(0, $consultas);
        self::assertSame($dto->contenido['petersen']['contenido']['salida'], $perfil['petersen_institucional']);
        self::assertSame($dto->contenido['instrumental']['contenido']['salida']['resultado'], $perfil['inferencia_declarativa']['resultado']);
        self::assertSame($dto->huella, $perfil['evaluacion_institucional']['huella']);
        self::assertFalse($perfil['evaluacion_institucional']['persistido']);
        self::assertSame('PLAN-CAPTURA-DCL-1', $perfil['plan_captura']['contrato']);
    }

    public function test_un_fallo_petersen_no_anuncia_exito_y_no_expone_el_error_interno(): void
    {
        $pagina = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $this->fallarAdquisicionPetersen('DETALLE-INTERNO-SENSIBLE');
        $pagina->call('analizarEvidencia')->assertHasErrors('analisis')
            ->assertSet('analisisEjecutado', false)->assertSet('fechaAnalisis', null)
            ->assertDontSee('Resultado calculado para esta consulta:')
            ->assertDontSee('DETALLE-INTERNO-SENSIBLE');
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_un_segundo_intento_fallido_retira_el_aviso_de_exito_anterior(): void
    {
        $pagina = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')->assertHasNoErrors()->assertSet('analisisEjecutado', true);
        $this->fallarAdquisicionPetersen('Adquisición interrumpida.');
        $pagina->call('analizarEvidencia')->assertHasErrors('analisis')
            ->assertSet('analisisEjecutado', false)->assertSet('mensajeAnalisis', null)
            ->assertSet('fechaAnalisis', null)->assertDontSee('Resultado calculado para esta consulta:');
    }

    public function test_una_revocacion_de_fuente_no_se_convierte_en_confirmacion_de_analisis(): void
    {
        $pagina = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $this->actor->roles()->first()->revokePermissionTo('respuestas_instrumento.ver');
        $pagina->call('analizarEvidencia')->assertForbidden();
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_otra_peticion_no_presenta_una_nueva_lectura_como_el_analisis_previo(): void
    {
        $pagina = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('analizarEvidencia')->assertHasNoErrors()->assertSet('analisisEjecutado', true);
        $this->travel(1)->minutes();
        $pagina->call('$refresh')->assertHasNoErrors()
            ->assertSet('analisisEjecutado', false)->assertSet('fechaAnalisis', null)
            ->assertSet('mensajeAnalisis', null)->assertDontSee('Resultado calculado para esta consulta:');
    }

    public function test_sin_version_de_conocimiento_no_afirma_una_ejecucion_completa(): void
    {
        $pagina = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        DB::table('versiones_modelo_experto')->update(['estado' => 'INACTIVO']);
        $pagina->call('analizarEvidencia')->assertHasErrors('analisis')
            ->assertSet('analisisEjecutado', false)->assertSet('fechaAnalisis', null)
            ->assertDontSee('Resultado calculado para esta consulta:');
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    private function fallarAdquisicionPetersen(string $mensaje): void
    {
        $pendiente = true;
        DB::listen(static function (QueryExecuted $evento) use (&$pendiente, $mensaje): void {
            if ($pendiente && str_contains($evento->sql, 'from "valoraciones_petersen_originales"')) {
                $pendiente = false;
                throw new DomainException($mensaje);
            }
        });
    }
}
