<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\ReglaExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarReglasExpertas as Gestor;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarPetersenInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratosConocimiento;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\GobiernoReglasPanel;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\versiones\PaquetePetersenInstitucional as P;
use Tests\TestCase;

/** Gobierno relacional real; los estados de la prueba administrativa no se convierten en historia clínica. */
final class GobiernoPetersenInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-20T12:00:00-04:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->actor->assignRole('PSICOLOGO/A');
        Personal::create(['cod_usuario' => $this->actor->getKey(), 'nombres' => 'Psicología', 'apellido_paterno' => 'Sintética',
            'numero_documento' => 'GOB-PET-TEST', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $this->actingAs($this->actor);
    }

    private function paquete(string $version): PaqueteConocimiento
    {
        $c = P::contratos();

        return new PaqueteConocimiento($version, app(ConocimientoVersionado::class)->instantanea($version), $c['relaciones'], false, 'RESULTADO', []);
    }

    private function estados(): array
    {
        return ['C01' => 'S', 'C02' => 'S', 'C03' => 'S', 'C04' => 'S', 'C05' => 'S',
            'PERFIL' => 'INTERPRETABLE', 'MEMORIA' => 'A', 'CANTIDAD' => 'UNICO'];
    }

    public function test_registro_idempotente_preserva_familia_ad_y_no_crea_datos_clinicos(): void
    {
        $ad = app(RegistrarConocimientoInstitucional::class)->ejecutar($this->actor);
        $original = app(ConocimientoVersionado::class)->instantanea($ad);
        $id = app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor);
        self::assertSame($id, app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor));
        self::assertSame($original, app(ConocimientoVersionado::class)->instantanea($ad));
        self::assertSame(17, ReglaExperta::query()->where('cod_version_modelo', $ad)->count());
        self::assertSame(4, ReglaExperta::query()->where('cod_version_modelo', $id)->count());
        self::assertSame(4, ReglaExperta::query()->where('cod_version_modelo', $id)->where('estado_revision', 'PENDIENTE')->count());
        self::assertSame($ad, app(ContratosConocimiento::class)->versionInstitucionalVigente(CarbonImmutable::now()));
        self::assertSame($id, app(ContratosConocimiento::class)->versionPetersenVigente(CarbonImmutable::now()));
        self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
        self::assertSame(1, DB::table('activity_log')->where('event', 'contrato_petersen_registrado')->count());
    }

    public function test_archivo_y_sucesora_afectan_nuevas_ejecuciones_y_conservan_el_paquete_anterior(): void
    {
        $id = app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor);
        $antes = $this->paquete($id);
        $regla = ReglaExperta::query()->where('cod_version_modelo', $id)->where('codigo_regla', 'PET-01')->sole();
        self::assertSame('PET-01', (new P)->evaluar($antes, $this->estados())['patron']);
        $sucesora = app(Gestor::class)->crearRevision($this->actor, $id, 'ORION-PET-ARCHIVO-2', 'Archivo de patrón conservando sus condiciones originales.', retiradas: [$regla->getKey()]);
        self::assertSame($sucesora, app(ContratosConocimiento::class)->versionPetersenVigente(CarbonImmutable::now()));
        self::assertNull((new P)->evaluar($this->paquete($sucesora), $this->estados())['patron']);
        self::assertSame('PET-01', (new P)->evaluar($antes, $this->estados())['patron']);
        self::assertSame($antes->declaracion(), $this->paquete($id)->declaracion());
        self::assertSame('INACTIVO', ReglaExperta::query()->where('cod_version_modelo', $sucesora)->where('cod_regla_anterior', $regla->getKey())->sole()->estado);
    }

    public function test_no_se_puede_saltar_un_requisito_desde_el_editor_ni_por_llamada_directa(): void
    {
        $id = app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor);
        $p = $this->paquete($id);
        $condicion = $p->tabla('condiciones_regla_experta')[0];
        $definicion = ['codigo' => 'PET-SIN-C03', 'nombre' => 'Intento de quitar requisitos', 'descripcion' => 'Negativo de ingeniería.',
            'fundamento' => 'No debe publicarse una clínica distinta del contrato.',
            'condiciones' => [['variable' => $condicion['cod_variable_experta'], 'valor' => $condicion['cod_valor_semantico']]],
            'criterio_dominio' => $p->tabla('criterios_dominios_resultado')[0]['cod_criterio_dominio_resultado'],
            'valor_consecuencia' => $p->tabla('consecuencias_regla_experta')[0]['cod_valor_semantico']];
        try {
            app(Gestor::class)->crearRevision($this->actor, $id, 'ORION-PET-INSEGURO', 'No debe omitir los antecedentes originales de Petersen.', $definicion);
            self::fail('El editor publicó otra semántica clínica.');
        } catch (DomainException $e) {
            self::assertStringContainsString('cuatro definiciones', $e->getMessage());
        }
        self::assertDatabaseCount('versiones_modelo_experto', 1);
        self::assertNull(VersionModeloExperto::findOrFail($id)->fecha_hora_retiro);
        Livewire::test(GobiernoReglasPanel::class)->set('versionSeleccionada', $id)->assertDontSee('Incorporar una regla nueva o sustituta')
            ->set('agregarRegla', true)->set('nueva', $definicion)->set('codigoVersion', 'ORION-PET-INSEGURO')
            ->set('motivoCambio', 'No debe omitir los antecedentes originales de Petersen.')->call('crearSucesora')->assertHasErrors('gobierno');
        self::assertDatabaseCount('versiones_modelo_experto', 1);
    }

    public function test_ausencia_y_retiro_del_gobierno_producen_abstencion_explicita(): void
    {
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $lector = app(ConsultarPetersenInstitucional::class);
        $r = $lector->consultar($this->actor, $residente, CarbonImmutable::now(), 'INSTRUMENTAL-TEST');
        self::assertNull($r['patron']);
        self::assertContains('ABSTENCION_SIN_GOBIERNO_PETERSEN_VIGENTE', $r['impedimentos']);
        $id = app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor);
        DB::table('versiones_modelo_experto')->where('cod_version_modelo', $id)->update(['fecha_hora_retiro' => now()]);
        self::assertNull(app(ContratosConocimiento::class)->versionPetersenVigente(CarbonImmutable::now()));
        self::assertContains('ABSTENCION_SIN_GOBIERNO_PETERSEN_VIGENTE', $lector->consultar($this->actor, $residente, CarbonImmutable::now(), 'INSTRUMENTAL-TEST')['impedimentos']);
    }

    public function test_dos_ramas_vigentes_no_se_resuelven_eligiendo_una_fila(): void
    {
        $id = app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor);
        $sucesora = app(Gestor::class)->crearRevision($this->actor, $id, 'ORION-PET-RAMA-2', 'Sucesión usada para comprobar ambigüedad de versiones.');
        $cabecera = VersionModeloExperto::findOrFail($sucesora)->getAttributes();
        $cabecera['cod_version_modelo'] = 'VER-PET-AMBIGUA';
        $cabecera['codigo_version'] = 'ORION-PET-OTRA-RAMA';
        DB::table('versiones_modelo_experto')->insert($cabecera);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('varias revisiones institucionales vigentes');
        app(ContratosConocimiento::class)->versionPetersenVigente(CarbonImmutable::now());
    }

    public function test_corte_anterior_a_sucesora_conserva_la_version_de_cada_familia(): void
    {
        $petersen = app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor);
        $cognitivo = app(RegistrarConocimientoInstitucional::class)->ejecutar($this->actor);
        $corte = CarbonImmutable::now();
        $this->travel(1)->minutes();
        $p2 = app(Gestor::class)->crearRevision($this->actor, $petersen, 'ORION-PET-TEMPORAL-2', 'Comprobar la selección temporal independiente del gobierno.');
        $c2 = app(Gestor::class)->crearRevision($this->actor, $cognitivo, 'ORION-COG-TEMPORAL-2', 'Comprobar la selección temporal independiente del conocimiento.');
        $contratos = app(ContratosConocimiento::class);
        self::assertSame($petersen, $contratos->versionPetersenVigente($corte));
        self::assertSame($cognitivo, $contratos->versionInstitucionalVigente($corte));
        self::assertSame($p2, $contratos->versionPetersenVigente(CarbonImmutable::now()));
        self::assertSame($c2, $contratos->versionInstitucionalVigente(CarbonImmutable::now()));
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $r = app(ConsultarPetersenInstitucional::class)->consultar($this->actor, $residente, $corte, $cognitivo);
        self::assertSame($petersen, $r['gobierno']['version']);
    }

    public function test_superadministracion_consulta_sin_registrar_ni_revisar(): void
    {
        $super = User::factory()->create(['estado' => 'ACTIVO']);
        $super->assignRole('SUPERADMINISTRADOR');
        Livewire::actingAs($super)->test(GobiernoReglasPanel::class)->assertDontSee('Registrar patrones de Petersen adoptados');
        $this->expectException(AuthorizationException::class);
        app(RegistrarConocimientoPetersen::class)->ejecutar($super);
    }

    public function test_panel_registra_prueba_y_revisa_sin_confundir_entradas_con_datos_clinicos(): void
    {
        $panel = Livewire::test(GobiernoReglasPanel::class)->call('registrarPetersen')->assertHasNoErrors()->assertSee('Amnésico de dominio único');
        $id = $panel->get('versionSeleccionada');
        $regla = ReglaExperta::query()->where('cod_version_modelo', $id)->where('codigo_regla', 'PET-01')->sole();
        $entradas = $regla->condicionesReglaExperta()->pluck('cod_valor_semantico', 'cod_variable_experta')->all();
        $panel->call('prepararPrueba', $regla->getKey())->set('entradaPrueba', $entradas)->call('probarCondiciones')->assertSet('resultadoPrueba.cumple', true);
        $panel->call('prepararRevision', $regla->getKey())->set('decision', 'RECHAZADA')->set('justificacion', 'Ensayo de revisión de conocimiento, sin evaluar residentes.')->call('guardarRevision')->assertHasNoErrors();
        self::assertNull((new P)->evaluar($this->paquete($id), $this->estados())['patron']);
        self::assertDatabaseCount('valoraciones_petersen_originales', 0);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }
}
