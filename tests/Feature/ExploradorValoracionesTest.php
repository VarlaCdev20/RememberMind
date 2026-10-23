<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Identidad\Presentacion\Navegacion\SidebarService;
use App\Modulos\Valoraciones\Aplicacion\Consultas\ExploradorInstrumentos;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaAreaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\EvaluacionesAreaPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ExploradorValoracionesTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private Instrumento $instrumento;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        config(['remembermind.superadmin_clinical_write' => false]);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('PSICOLOGO/A');
        $personal = Personal::create(['cod_usuario' => $this->usuario->getKey(), 'nombres' => 'Profesional sintético', 'apellido_paterno' => 'Explorador', 'numero_documento' => 'EXP-001', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $area = Area::create(['cod_area' => 'ARE_COG', 'nombre' => 'Área cognitiva sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $personal->getKey(), 'cod_jornada' => $jornada->getKey(), 'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        $this->instrumento = Instrumento::create(['codigo' => 'EXP-OBS', 'nombre' => 'Observación sintética', 'version' => 'TEST-1', 'tipo' => 'OBSERVACIONAL', 'estado' => 'ACTIVO']);
        PreguntaInstrumento::create(['cod_instrumento' => $this->instrumento->getKey(), 'codigo' => 'TEST-LOG', 'enunciado' => 'Dato sintético', 'tipo_respuesta' => 'LOGICO', 'orden' => 1, 'estado' => 'ACTIVA']);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->actingAs($this->usuario);
    }

    public function test_biblioteca_filtra_dominios_y_no_escribe_catalogo_ni_resultados(): void
    {
        $panel = Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])
            ->assertSee('EXP-OBS')->set('biblioteca', 'candidatos')->set('dominio', 'MEM');
        $panel->assertViewHas('tarjetas', fn ($tarjetas) => $tarjetas->isNotEmpty() && $tarjetas->every(fn ($t) => in_array('MEM', $t['dominios'], true) && $t['cod_instrumento'] === null));
        $id = app(ExploradorInstrumentos::class)->tarjetas('ARE_COG')->firstWhere('estado', 'CANDIDATO_INVESTIGADO')['id'];
        $panel->call('abrirGuia', $id)->assertSet('mostrarGuia', true)->assertSee('REFERENCIA_NO_CLINICA')->call('cerrarGuia')->assertSet('mostrarGuia', false);
        self::assertDatabaseCount('instrumentos', 1);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_tarjeta_abre_captura_y_conserva_seleccion_al_elegir_residente(): void
    {
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])
            ->call('nuevaEvaluacion', null, $this->instrumento->getKey())
            ->assertDispatched('evaluacion-geriatrica-area-abrir', ['cod_residente' => null, 'cod_area' => 'ARE_COG', 'cod_instrumento' => $this->instrumento->getKey()]);
        Livewire::test(EvaluacionGeriatricaAreaModal::class)
            ->call('abrir', ['cod_area' => 'ARE_COG', 'cod_instrumento' => $this->instrumento->getKey()])
            ->set('cod_residente', $this->residente->getKey())
            ->assertSet('cod_instrumento', $this->instrumento->getKey())
            ->assertSet('preguntas.0.enunciado', 'Dato sintético');
    }

    public function test_inactivo_y_sin_preguntas_no_admiten_nueva_aplicacion(): void
    {
        $this->instrumento->update(['estado' => 'INACTIVO']);
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->assertSee('Histórico · inactivo')
            ->call('nuevaEvaluacion', null, $this->instrumento->getKey())->assertStatus(422);
        $this->instrumento->update(['estado' => 'ACTIVO']);
        $this->instrumento->preguntas()->update(['estado' => 'INACTIVA']);
        Livewire::test(EvaluacionGeriatricaAreaModal::class)->call('abrir', ['cod_area' => 'ARE_COG', 'cod_instrumento' => $this->instrumento->getKey()])->assertStatus(422);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
    }

    public function test_parametros_manipulados_no_cruzan_area_ni_candidatos(): void
    {
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_FUN'])->call('nuevaEvaluacion', null, $this->instrumento->getKey())->assertStatus(422);
        Livewire::test(EvaluacionGeriatricaAreaModal::class)->call('abrir', ['cod_area' => 'ARE_FUN', 'cod_instrumento' => $this->instrumento->getKey()])->assertStatus(422);
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->call('nuevaEvaluacion', null, 'HVLT-R-BO-2015-F5')->assertStatus(422);
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_FUN'])->call('abrirGuia', 'catalogo:'.$this->instrumento->getKey())->assertNotFound();
    }

    public function test_roles_y_profesion_real_no_se_sustituyen_por_permisos(): void
    {
        foreach (['MEDICO GENERAL/GERIATRA', 'NUTRICIONISTA', 'FAMILIAR', 'ENFERMEROS'] as $rol) {
            $this->usuario->syncRoles([$rol]);
            Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->assertForbidden();
        }
        $this->usuario->syncRoles(['SUPERADMINISTRADOR']);
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->assertSee('Solo consulta')->call('nuevaEvaluacion')->assertForbidden();
        $this->usuario->syncRoles(['PSICOLOGO/A']);
        $this->usuario->personal->update(['estado' => 'INACTIVO']);
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->assertForbidden();
    }

    public function test_sin_permiso_del_residente_historial_y_conteos_no_exponen_datos(): void
    {
        AplicacionInstrumento::create(['cod_instrumento' => $this->instrumento->getKey(), 'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->usuario->personal->getKey(), 'fecha_hora' => now(), 'estado' => 'COMPLETA', 'clasificacion' => 'CRITICO']);
        Role::findByName('PSICOLOGO/A')->revokePermissionTo('residentes.ver');
        $this->usuario->unsetRelation('roles')->unsetRelation('permissions');
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])
            ->assertForbidden();
        self::assertDatabaseCount('aplicaciones_instrumento', 1);
    }

    public function test_vacios_afectivo_y_social_derivan_a_ficha_sin_inventar_catalogo(): void
    {
        foreach (['ARE_AFE', 'ARE_SOC'] as $area) {
            Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => $area])->assertSee('Abrir ficha')
                ->assertDontSee('Registrar aplicación')->assertViewHas('tarjetas', fn ($t) => $t->isEmpty());
        }
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_NUT'])->assertForbidden();
        self::assertDatabaseCount('instrumentos', 1);
    }

    public function test_superadmin_encuentra_el_avatar_desde_su_entrada_habitual_y_el_menu(): void
    {
        $this->usuario->syncRoles(['SUPERADMINISTRADOR']);
        $this->get(route('admin.salud-seguimiento.valoracion.index'))
            ->assertOk()->assertSee('Explorar instrumentos y guías')
            ->assertSee('Guía de RememberMind')->assertSee(route('admin.psicologia.evaluacion.cognitiva'));
        $enlaces = collect(app(SidebarService::class)->getSidebar())->flatMap(fn ($seccion) => $seccion['items'] ?? []);
        self::assertSame('admin.psicologia.evaluacion.cognitiva', $enlaces->firstWhere('label', 'Instrumentos y guías')['route']);
        $this->getJson(route('admin.instrumentos.index'))->assertOk()->assertJsonFragment(['codigo' => 'EXP-OBS']);
    }

    public function test_acceso_visual_no_ofrece_el_flujo_de_psicologia_a_otros_roles(): void
    {
        foreach (['MEDICO GENERAL/GERIATRA', 'NUTRICIONISTA', 'FAMILIAR'] as $rol) {
            $this->usuario->syncRoles([$rol]);
            self::assertStringNotContainsString('Explorar instrumentos y guías', Blade::render('<x-ui.acceso-valoraciones />'));
        }
    }

    public function test_dominios_proceden_de_preguntas_vigentes_y_no_del_nombre(): void
    {
        $this->instrumento->preguntas()->update(['dominio' => 'COG-MEM']);
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->set('dominio', 'MEM')
            ->assertViewHas('tarjetas', fn ($tarjetas) => $tarjetas->count() === 1 && $tarjetas->first()['dominios'] === ['MEM']);
        $this->instrumento->preguntas()->update(['estado' => 'INACTIVA']);
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])->set('dominio', 'MEM')
            ->assertViewHas('tarjetas', fn ($tarjetas) => $tarjetas->isEmpty());
    }
}
