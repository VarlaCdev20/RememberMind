<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\FichaPsicologicaResidente;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

final class FichaPsicologicaResidenteTest extends TestCase
{
    use RefreshDatabase;

    private User $psicologo;

    private User $enfermero;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-21T12:00:00-04:00'));
        $this->seed(RolesAndPermissionsSeeder::class);

        // Crear psicólogo
        $this->psicologo = User::factory()->create(['estado' => 'ACTIVO']);
        $this->psicologo->assignRole('PSICOLOGO/A');
        Personal::create([
            'cod_usuario' => $this->psicologo->getKey(),
            'nombres' => 'Licenciada',
            'apellido_paterno' => 'Psicología',
            'numero_documento' => 'DOC-PSI-'.Str::random(6),
            'profesion' => 'PSICOLOGIA',
            'estado' => 'ACTIVO',
        ]);
        $this->psicologo->givePermissionTo([
            'residentes.ver',
            'valoraciones_psicologicas.ver',
            'aplicaciones_instrumento.ver',
            'aplicaciones_instrumento.crear',
            'instrumentos.ver',
        ]);

        $area = Area::create(['cod_area' => 'ARE_AFE', 'nombre' => 'Afectiva sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $this->psicologo->personal->getKey(), 'cod_jornada' => $jornada->getKey(), 'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);

        // Crear enfermero (sin rol de psicología)
        $this->enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermero->assignRole('ENFERMEROS');
        Personal::create([
            'cod_usuario' => $this->enfermero->getKey(),
            'nombres' => 'Licenciado',
            'apellido_paterno' => 'Enfermería',
            'numero_documento' => 'DOC-ENF-'.Str::random(6),
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVO',
        ]);

        $this->residente = Residente::factory()->create([
            'cod_residente' => 'RES_PSI_TEST_01',
            'nombres' => 'Carmelo',
            'apellido_paterno' => 'Quispe',
            'apellido_materno' => 'Vargas',
            'fecha_nacimiento' => '1952-04-15',
            'estado' => 'ADMITIDO',
        ]);
    }

    public function test_psicologo_activo_puede_ver_ficha_psicologica_del_residente(): void
    {
        $this->actingAs($this->psicologo);

        $response = $this->get(route('admin.psicologia.residente.ficha', $this->residente));
        $response->assertOk();
        $response->assertSee('Ficha psicológica del residente');
        $response->assertSee('Carmelo Quispe Vargas');
        $response->assertSee('Informe Apoyo a la decisión');
    }

    public function test_enfermero_o_usuario_no_autorizado_es_rechazado_con_403(): void
    {
        $this->actingAs($this->enfermero);

        $response = $this->get(route('admin.psicologia.residente.ficha', $this->residente));
        $response->assertForbidden();
    }

    public function test_fuentes_restringidas_no_exponen_contactos_ni_metricas_iadl(): void
    {
        $this->psicologo->roles()->first()->revokePermissionTo('controles_funcionales_iadl.ver');
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertViewHas('totalIadl', null)
            ->call('cambiarMetricaTendencia', 'iadl')
            ->assertViewHas('datosTendencia', null)
            ->assertSee('No tienes permiso para consultar esta fuente')
            ->call('cambiarTab', 'entorno')
            ->assertViewHas('contactosFamiliares', fn ($contactos) => $contactos->isEmpty())
            ->assertSee('Tu cuenta no tiene permiso para consultar contactos');
    }

    public function test_permiso_de_fuente_revocado_impide_consultar_la_ficha(): void
    {
        $this->psicologo->revokePermissionTo('aplicaciones_instrumento.ver');
        $this->psicologo->roles()->first()->revokePermissionTo('aplicaciones_instrumento.ver');
        $this->actingAs($this->psicologo);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])->assertForbidden();
    }

    public function test_componente_livewire_muestra_pestanas_didacticas_y_permite_navegar_entre_ellas(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSet('tab', 'resumen')
            ->assertSee('Resumen')
            ->assertSee('Cognitiva')
            ->assertSee('Afectiva y emocional')
            ->assertSee('Funcionamiento (IADL)')
            ->assertSee('Entorno y red social')
            ->assertSee('Sesiones y seguimiento')
            ->assertSee('Informes y resultados')
            // Navegar a Cognitiva
            ->call('cambiarTab', 'cognitiva')
            ->assertSet('tab', 'cognitiva')
            ->assertSee('Baterías cognitivas y vinculación con Apoyo a la decisión')
            ->assertSee('Explorar instrumentos cognitivos')
            // Navegar a Afectiva
            ->call('cambiarTab', 'afectiva')
            ->assertSet('tab', 'afectiva')
            ->assertSee('Evaluación afectiva, del ánimo y descarte diferencial')
            ->assertSee('Consultar recursos del área')
            // Navegar a Funcionamiento
            ->call('cambiarTab', 'funcionamiento')
            ->assertSet('tab', 'funcionamiento')
            ->assertSee('Autonomía instrumental y actividades cotidianas (IADL)')
            // Navegar a Entorno
            ->call('cambiarTab', 'entorno')
            ->assertSet('tab', 'entorno')
            ->assertSee('Red social, vinculación familiar y adaptación institucional');
    }

    public function test_disparar_abrir_evaluacion_emite_evento_hacia_modal_de_area(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('abrirEvaluacion', 'ARE_COG')
            ->assertDispatched('evaluacion-geriatrica-area-abrir', [
                'cod_residente' => $this->residente->getKey(),
                'cod_area' => 'ARE_COG',
            ]);
    }

    public function test_psicologo_puede_guardar_valoracion_afectiva_vinculada_a_atencion_y_soporte_c04(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'afectiva')
            ->set('formAfectiva.estado_animo', 'EUTIMICO')
            ->set('formAfectiva.afecto', 'ADECUADO')
            ->set('formAfectiva.ansiedad', 'AUSENTE')
            ->set('formAfectiva.apatia', 'AUSENTE')
            ->set('formAfectiva.conducta', 'Colaboradora y orientada')
            ->set('formAfectiva.conclusion', 'Se descarta sintomatología depresiva mayor o ansiedad que interfiera con pruebas.')
            ->set('formAfectiva.cod_atencion', 'ATE_MANIPULADA')
            ->set('formAfectiva.cod_personal', 'PER_MANIPULADO')
            ->call('guardarValoracionAfectiva')
            ->assertHasNoErrors()
            ->assertSee('Valoración afectiva registrada correctamente');

        $this->assertDatabaseHas('valoraciones_psicologicas', [
            'cod_residente' => $this->residente->getKey(),
            'estado_animo' => 'EUTIMICO',
            'ansiedad' => 'AUSENTE',
            'estado' => 'VIGENTE',
            'cod_personal' => $this->psicologo->personal->getKey(),
        ]);

        $this->assertDatabaseHas('atenciones', [
            'cod_residente' => $this->residente->getKey(),
            'tipo_atencion' => 'VALORACION_AFECTIVA',
            'estado' => 'FINALIZADA',
        ]);
    }

    public function test_psicologia_solo_consulta_iadl_sin_exponer_captura(): void
    {
        $this->actingAs($this->psicologo);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'funcionamiento')->assertDontSee('Nueva observación IADL');
        self::assertFalse(method_exists(FichaPsicologicaResidente::class, 'guardarControlIadl'));
        self::assertDatabaseCount('controles_funcionales_iadl', 0);
    }

    public function test_formulario_afectivo_no_precarga_hallazgos_y_conserva_cambios_al_cancelar(): void
    {
        $this->actingAs($this->psicologo);
        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSet('formAfectiva.estado_animo', '')->assertSet('formAfectiva.ansiedad', '')
            ->call('alternarFormularioAfectivo')->set('formAfectiva.conducta', 'Observación sintética pendiente')
            ->call('alternarFormularioAfectivo')->assertSet('confirmarDescarteAfectiva', true)
            ->call('conservarValoracionAfectiva')->assertSet('formAfectiva.conducta', 'Observación sintética pendiente')
            ->call('alternarFormularioAfectivo')->call('descartarValoracionAfectiva')
            ->assertSet('mostrarFormAfectiva', false)->assertSet('formAfectiva.conducta', '');
        self::assertDatabaseCount('valoraciones_psicologicas', 0);
    }

    public function test_psicologo_puede_registrar_sesion_de_atencion(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'sesiones')
            ->set('formSesion.tipo_atencion', 'SEGUIMIENTO')
            ->set('formSesion.motivo', 'Seguimiento de adaptación residencial')
            ->set('formSesion.observacion', 'Residente refiere buena integración con compañeros de habitación.')
            ->call('guardarSesion')
            ->assertHasNoErrors()
            ->assertSee('Sesión de atención psicológica registrada exitosamente');

        $this->assertDatabaseHas('atenciones', [
            'cod_residente' => $this->residente->getKey(),
            'tipo_atencion' => 'SEGUIMIENTO',
            'motivo' => 'Seguimiento de adaptación residencial',
            'estado' => 'FINALIZADA',
        ]);
    }

    public function test_cambiar_periodo_y_metrica_de_tendencia_longitudinal(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSee('Trayectoria y tendencias longitudinales')
            ->call('cambiarPeriodoTendencia', 12)
            ->assertSet('periodoMeses', 12)
            ->call('cambiarMetricaTendencia', 'afectivo')
            ->assertSet('metricaTendencia', 'afectivo')
            ->call('toggleTablaTendencia')
            ->assertSet('mostrarTablaTendencia', true);
    }

    public function test_tendencia_cognitiva_con_multiples_puntos_calcula_resumen_y_datasets(): void
    {
        $this->actingAs($this->psicologo);

        $inst = Instrumento::firstOrCreate(
            ['cod_instrumento' => 'INS_COG_MMSE_TEST'],
            ['codigo' => 'MMSE_TEST', 'nombre' => 'Minimental Test', 'tipo' => 'COGNITIVO', 'estado' => 'ACTIVO']
        );

        $personal = $this->psicologo->personal;
        $area = Area::firstOrCreate(['cod_area' => 'ARE_COG'], ['nombre' => 'Cognitiva', 'estado' => 'ACTIVA']);

        $atencion1 = Atencion::create([
            'cod_atencion' => 'ATE_TEND_01',
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_area' => $area->getKey(),
            'tipo_atencion' => 'VALORACION',
            'fecha_hora' => now()->subMonths(2),
            'estado' => 'FINALIZADA',
        ]);

        AplicacionInstrumento::create([
            'cod_aplicacion' => 'APP_TEND_01',
            'cod_residente' => $this->residente->getKey(),
            'cod_instrumento' => $inst->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_usuario_registro' => $this->psicologo->getKey(),
            'registrado_en' => now()->subMonths(2),
            'cod_atencion' => $atencion1->getKey(),
            'fecha_hora' => now()->subMonths(2),
            'puntaje_total' => 24.0,
            'puntaje_maximo' => 30.0,
            'estado' => 'APLICADO',
        ]);

        $atencion2 = Atencion::create([
            'cod_atencion' => 'ATE_TEND_02',
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_area' => $area->getKey(),
            'tipo_atencion' => 'VALORACION',
            'fecha_hora' => now()->subMonths(1),
            'estado' => 'FINALIZADA',
        ]);

        AplicacionInstrumento::create([
            'cod_aplicacion' => 'APP_TEND_02',
            'cod_residente' => $this->residente->getKey(),
            'cod_instrumento' => $inst->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_usuario_registro' => $this->psicologo->getKey(),
            'registrado_en' => now()->subMonths(1),
            'cod_atencion' => $atencion2->getKey(),
            'fecha_hora' => now()->subMonths(1),
            'puntaje_total' => 27.0,
            'puntaje_maximo' => 30.0,
            'estado' => 'APLICADO',
        ]);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSee('Trayectoria y tendencias longitudinales')
            ->assertSee('Minimental Test')
            ->assertSee('Evolución favorable');
    }

    public function test_psicologo_puede_navegar_a_pestana_orion_y_consultar_criterios_petersen(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSee('Resultados Apoyo a la decisión')
            ->call('cambiarTab', 'orion')
            ->assertSet('tab', 'orion')
            ->assertSee('Motor Experto Apoyo a la decisión')
            ->assertSee('Criterios diagnósticos Petersen')
            ->assertSee('C01 · Queja cognitiva confirmada')
            ->assertSee('C02 · Deterioro cognitivo objetivo')
            ->assertSee('C03 · Preservación de actividades funcionales (IADL)')
            ->assertSee('C04 · Ausencia de demencia / Criterio diferencial')
            ->assertSee('C05 · Juicio clínico global interdisciplinario')
            ->assertSee('persistido = false');
    }
}
