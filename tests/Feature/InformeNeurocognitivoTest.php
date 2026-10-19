<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarAutonomiaDescriptiva;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\LenguajeResultadosExperto;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarInformeCognitivo;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarPatronExperimental;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use MotorExperto\dominio\orion\Services\Dcl\ClasificadorPetersenService;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum as C;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum as D;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\InformeCognitivoFixture as F;
use Tests\TestCase;
use Tests\Unit\PresentacionNeurocognitivaTest;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

/** Integración del informe, fuente funcional y auditoría; todas las filas son efímeras. */
final class InformeNeurocognitivoTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;

    private Residente $residente;

    private Personal $personal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 17)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->medico = User::factory()->create(['estado' => 'ACTIVO']);
        $this->medico->assignRole('MEDICO GENERAL/GERIATRA');
        $this->personal = Personal::create(['cod_usuario' => $this->medico->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Prueba', 'numero_documento' => 'NARRATIVA-TEST', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create();
        $this->actingAs($this->medico);
        F::conocimiento($this->medico);
    }

    private function informe(): array
    {
        $l = app(LecturaResultadosExperto::class)->consultar($this->medico->fresh(), $this->residente, null, 'COG-MEM');

        return app(PresentarInformeCognitivo::class)->presentar($l, 'COG-MEM')['narrativa'];
    }

    private function iadl(array $cambios = []): ControlFuncionalIadl
    {
        return ControlFuncionalIadl::create(array_replace(['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->personal->getKey(), 'cod_usuario_registro' => $this->medico->getKey(),
            'fecha_hora' => now()->subDay(), 'registrado_en' => now()->subHour(), 'actividad' => 'MEDICACION',
            'oportunidad' => 'OBSERVADA', 'desempeno' => 'CON_APOYO', 'tipo_ayuda' => 'SUSTITUCION',
            'causa_ayuda' => 'INSTITUCIONAL', 'nivel_previo' => 'DESCONOCIDO', 'fuente' => 'EQUIPO_CUIDADOS',
            'estado' => 'REGISTRADO'], $cambios));
    }

    private function permitirIadl(): void
    {
        // Permiso exclusivo de esta fixture: la tarea no instala ni concede permisos en la base local.
        Permission::findOrCreate('controles_funcionales_iadl.ver', 'web');
        $this->medico->givePermissionTo('controles_funcionales_iadl.ver');
    }

    public function test_estado_vacio_explica_faltantes_sin_inventar_normalidad(): void
    {
        $n = $this->informe();
        self::assertFalse($n['completa']);
        self::assertCount(5, $n['funciones']);
        self::assertCount(7, $n['faltantes']);
        self::assertStringContainsString('Todavía no podemos', $n['conclusion']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSeeInOrder(['Resultado principal', 'Valoración de las funciones cognitivas', '¿Por qué llegamos a este resultado?',
                '¿Qué información nos falta?', '¿Qué significa el resultado?', '¿Qué corresponde revisar?'])
            ->assertDontSee('función normal')->assertDontSee('PET-')->assertDontSee('SIM-ORION');
        Livewire::withQueryParams(['simulacion' => '0'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Pendiente de una valoración disponible')
            ->assertDontSee('Salir de la vista previa');
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_cambiar_area_no_cambia_la_conclusion_global_ni_los_antecedentes(): void
    {
        ControlCognitivo::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'fecha_hora' => now()->subDay(), 'estado' => 'VIGENTE', 'repite_preguntas' => true]);
        $n = $this->informe();
        $lectura = app(LecturaResultadosExperto::class)->consultar($this->medico, $this->residente, null, 'COG-ATE');
        self::assertSame($n, app(PresentarInformeCognitivo::class)->presentar($lectura, 'COG-ATE')['narrativa']);
    }

    public function test_pantalla_medica_no_expone_version_ids_reglas_ni_auditoria(): void
    {
        F::evaluacion($this->residente, $this->personal, 'V');
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $visible = html_entity_decode(strip_tags($componente->html()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        foreach (['INFORME-LECTURA-01', 'RULE_A', 'TR_V', 'MAP_INS', 'CUMPLE', 'FINALIZADA', 'DIFICULTAD_EVIDENCIADA', 'Consultar auditoría técnica'] as $token) {
            self::assertStringNotContainsString($token, $visible);
        }
        $componente->call('verTrazabilidad')->assertNotFound();
    }

    public function test_auditor_autorizado_abre_traza_y_revocacion_la_cierra_con_denegacion(): void
    {
        F::evaluacion($this->residente, $this->personal, 'AUD');
        $auditor = User::factory()->create(['estado' => 'ACTIVO']);
        $auditor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($auditor);
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Consultar auditoría técnica')->assertDontSee('Variable exigida:')
            ->call('verTrazabilidad')->assertSee('Variable exigida:')->assertSee('RULE_A')
            ->call('cerrarTrazabilidad')->assertDontSee('Variable exigida:')
            ->call('verTrazabilidad');
        $auditor->revokePermissionTo('auditoria.ver');
        // Revocar el permiso de rol también elimina cualquier autorización heredada.
        Role::findByName('SUPERADMINISTRADOR')->revokePermissionTo('auditoria.ver');
        $c->call('reintentar')->assertNotFound();
    }

    public function test_iadl_requiere_permiso_especifico_y_no_consulta_filas_sin_el(): void
    {
        $this->iadl(['observacion' => 'Contenido funcional reservado']);
        $consultas = [];
        DB::listen(static function ($q) use (&$consultas): void {
            $consultas[] = $q->sql;
        });
        $a = app(ConsultarAutonomiaDescriptiva::class)->consultar($this->medico, $this->residente);
        self::assertFalse($a['disponible']);
        self::assertSame([], $a['actividades']);
        self::assertFalse(collect($consultas)->contains(fn ($sql) => str_contains($sql, 'controles_funcionales_iadl')));
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertDontSee('Contenido funcional reservado');
    }

    public function test_iadl_describe_ayuda_institucional_sin_acreditar_independencia(): void
    {
        $this->permitirIadl();
        $this->iadl();
        $n = $this->informe();
        self::assertCount(1, $n['autonomia']['actividades']);
        self::assertStringContainsString('no demuestra incapacidad', $n['autonomia']['actividades'][0]['texto']);
        self::assertFalse($n['completa']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertSee('Organización de medicación')
            ->assertSee('No se conoce suficientemente su capacidad anterior.')->assertDontSee('PET-');
    }

    public function test_iadl_rectificacion_futuro_ajenos_y_lotes_no_alteran_sintesis(): void
    {
        $this->permitirIadl();
        $original = $this->iadl(['observacion' => 'Original rectificado']);
        $this->iadl(['cod_control_anterior' => $original->getKey(), 'estado' => 'RECTIFICACION', 'observacion' => 'Corrección vigente',
            'fecha_hora' => now()->subHour(), 'registrado_en' => now()->subMinute()]);
        $this->iadl(['fecha_hora' => now()->addDay(), 'observacion' => 'Futuro privado']);
        $this->iadl(['cod_residente' => Residente::factory()->create()->getKey(), 'observacion' => 'Ajeno privado']);
        $this->iadl(['actividad' => 'FINANZAS', 'observacion' => '[SIM-IADL-20261017:caso]']);
        $a = app(ConsultarAutonomiaDescriptiva::class)->consultar($this->medico, $this->residente);
        self::assertCount(1, $a['actividades']);
        self::assertSame('Corrección vigente', $a['actividades'][0]['observacion']);
        self::assertFalse($a['acredita_independencia']);
        self::assertDatabaseCount('controles_funcionales_iadl', 5);
    }

    public function test_iadl_posterior_no_completa_una_evaluacion_historica(): void
    {
        $this->permitirIadl();
        F::evaluacion($this->residente, $this->personal, 'HIST');
        $antes = $this->informe();
        $this->iadl(['fecha_hora' => now()->subMinute(), 'observacion' => 'Observación funcional posterior']);
        self::assertSame($antes, $this->informe());
        self::assertStringContainsString('no está conservada dentro de esta evaluación', $antes['autonomia']['resumen']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertDontSee('Observación funcional posterior');
    }

    public function test_iadl_simultanea_discrepante_conserva_ambas_descripciones_sin_elegir_por_pk(): void
    {
        $this->permitirIadl();
        $this->iadl(['tipo_ayuda' => 'RECORDATORIO', 'causa_ayuda' => 'COGNITIVA', 'observacion' => 'Descripción A']);
        $this->iadl(['tipo_ayuda' => 'SUSTITUCION', 'causa_ayuda' => 'COGNITIVA', 'nivel_previo' => 'INDEPENDIENTE', 'observacion' => 'Descripción B']);
        $a = $this->informe()['autonomia']['actividades'][0];
        self::assertStringContainsString('Ninguno se elige como conclusión', $a['texto']);
        self::assertStringNotContainsString('Necesita recordatorios', $a['texto']);
        self::assertCount(2, $a['variantes']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Descripción A')->assertSee('Descripción B')->assertSee('Necesita recordatorios')->assertSee('Otra persona realiza la actividad');
    }

    public function test_fuente_excluida_explica_motivo_y_soportes_se_agrupan_por_episodio(): void
    {
        F::evaluacion($this->residente, $this->personal, 'MIX', valor: 'RES_M');
        DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'EV_MIX_A')->update([
            'estado_admisibilidad' => 'NO_ADMISIBLE', 'motivo_admisibilidad' => 'La aplicación quedó interrumpida y no permite interpretar el resultado.']);
        $n = $this->informe();
        self::assertStringContainsString('La aplicación quedó interrumpida', json_encode($n['faltantes'], JSON_UNESCAPED_UNICODE));
        self::assertStringNotContainsString('Se identificaron dificultades', $n['conclusion']);
        DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'EV_MIX_A')->update(['estado_admisibilidad' => 'ADMISIBLE']);
        F::insertar('relaciones_evidencias_evaluacion', ['cod_relacion_evidencia' => 'RE_EP', 'cod_evidencia_origen' => 'EV_MIX_A',
            'cod_evidencia_destino' => 'EV_MIX_B', 'tipo_relacion' => 'MISMO_EPISODIO_CLINICO']);
        $n = $this->informe();
        self::assertCount(1, $n['fuentes']);
        self::assertFalse($n['funciones']['COG-MEM']['evaluada']);
    }

    public static function escenariosDePresentacion(): array
    {
        return ['completo' => [['A', 'A', 'P', 'P', 'P'], [], D::COMPLETADO_SIN_IMPEDIMENTO, C::ACREDITADO, 'La información requerida para este análisis está completa.'],
            'completo sin patron' => [['P', 'P', 'P', 'P', 'P'], ['DCL-C02' => C::NO_ACREDITADO], D::COMPLETADO_SIN_IMPEDIMENTO, C::ACREDITADO, 'No se trata de ausencia de datos'],
            ...PresentacionNeurocognitivaTest::incompletos()];
    }

    /** El Blade describe fuentes, datos y fechas sin publicar identificadores de tablas ni literales técnicos. */
    #[DataProvider('escenariosDePresentacion')]
    public function test_blade_compartido_presenta_completos_y_abstenciones_con_fuentes_explicitas(array $estados, array $criterios, D $d, C $independencia, string $esperado): void
    {
        $r = (new ClasificadorPetersenService)->clasificar(E::perfil($estados, $criterios, $d, $independencia,
            $estados[0] === 'I' ? ['COG-MEM' => 'La aplicación se interrumpió por fatiga.'] : []));
        $narrativa = (new PresentarPatronExperimental)->presentar($r);
        $html = view('apoyodecision::livewire.medico.resultados-experto.informe-narrativo', [
            'narrativa' => $narrativa, 'informe' => ['areas' => LenguajeResultadosExperto::CRITERIOS],
        ])->render();
        $visible = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        self::assertStringContainsString('SIMULACIÓN ACADÉMICA', $visible);
        self::assertStringContainsString($esperado, $visible);
        self::assertStringContainsString('¿Qué información nos falta?', $visible);
        self::assertStringNotContainsString('PET-02', $visible);
        self::assertStringNotContainsString('DCL-C03', $visible);
        foreach ($narrativa['funciones'] as $funcion) {
            foreach ($funcion['referencias'] as $referencia) {
                self::assertStringNotContainsString($referencia['registro'], $visible);
                self::assertStringNotContainsString($referencia['edicion'] ?? 'SIM-EDICION', $visible);
                self::assertStringContainsString($referencia['fecha_visible'], $visible);
                self::assertStringContainsString($referencia['descripcion'], $visible);
            }
        }
        self::assertDoesNotMatchRegularExpression('/COG-|COD_|SIM-|RAW:|controles_cognitivos|representacion_literal/', $visible);
        self::assertStringNotContainsString('wire:click', $html);
        if ($estados[0] === 'I') {
            self::assertStringContainsString('La aplicación se interrumpió por fatiga.', $visible);
        }
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }
}
