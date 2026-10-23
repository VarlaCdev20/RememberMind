<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
<<<<<<< HEAD
use App\Models\Instrumento;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPsicologica;
=======
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
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

<<<<<<< HEAD
=======
        $area = Area::create(['cod_area' => 'ARE_AFE', 'nombre' => 'Afectiva sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $this->psicologo->personal->getKey(), 'cod_jornada' => $jornada->getKey(), 'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);

>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
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
<<<<<<< HEAD
        $response->assertSee('Ver resultados cognitivos');
        $response->assertSee('El expediente de un vistazo');
        $response->assertSee('Últimos registros del expediente');
        $response->assertSee('Secciones de la ficha psicológica');
        $response->assertSee('aria-pressed="true"', false);
        $response->assertDontSee('CI:');
        $response->assertDontSee('Informe O.R.I.O.N.');
        $this->assertLessThan(strpos($response->getContent(), 'Trayectoria y tendencias longitudinales'), strpos($response->getContent(), 'Últimos registros del expediente'));
        if ($directorio = getenv('RM_FICHA_PSICOLOGICA_QA_DIR')) {
            if (! is_dir($directorio)) {
                mkdir($directorio, 0700, true);
            }
            file_put_contents($directorio.'/ficha-psicologica.html', $response->getContent());
        }
=======
        $response->assertSee('Informe Apoyo a la decisión');
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
    }

    public function test_enfermero_o_usuario_no_autorizado_es_rechazado_con_403(): void
    {
        $this->actingAs($this->enfermero);

        $response = $this->get(route('admin.psicologia.residente.ficha', $this->residente));
        $response->assertForbidden();
    }

<<<<<<< HEAD
=======
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

>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
    public function test_componente_livewire_muestra_pestanas_didacticas_y_permite_navegar_entre_ellas(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->assertSet('tab', 'resumen')
            ->assertSee('Resumen')
            ->assertSee('Cognitiva')
            ->assertSee('Afectiva y emocional')
<<<<<<< HEAD
            ->assertSee('Autonomía cotidiana')
=======
            ->assertSee('Funcionamiento (IADL)')
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            ->assertSee('Entorno y red social')
            ->assertSee('Sesiones y seguimiento')
            ->assertSee('Informes y resultados')
            // Navegar a Cognitiva
            ->call('cambiarTab', 'cognitiva')
            ->assertSet('tab', 'cognitiva')
<<<<<<< HEAD
            ->assertSee('Evaluación y seguimiento cognitivo')
            ->assertSee('Aplicar instrumento cognitivo')
=======
            ->assertSee('Baterías cognitivas y vinculación con Apoyo a la decisión')
            ->assertSee('Explorar instrumentos cognitivos')
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            // Navegar a Afectiva
            ->call('cambiarTab', 'afectiva')
            ->assertSet('tab', 'afectiva')
            ->assertSee('Evaluación afectiva, del ánimo y descarte diferencial')
<<<<<<< HEAD
            ->assertSee('Aplicar escala afectiva')
=======
            ->assertSee('Consultar recursos del área')
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
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
<<<<<<< HEAD
=======
            ->set('formAfectiva.cod_atencion', 'ATE_MANIPULADA')
            ->set('formAfectiva.cod_personal', 'PER_MANIPULADO')
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            ->call('guardarValoracionAfectiva')
            ->assertHasNoErrors()
            ->assertSee('Valoración afectiva registrada correctamente');

        $this->assertDatabaseHas('valoraciones_psicologicas', [
            'cod_residente' => $this->residente->getKey(),
            'estado_animo' => 'EUTIMICO',
            'ansiedad' => 'AUSENTE',
            'estado' => 'VIGENTE',
<<<<<<< HEAD
=======
            'cod_personal' => $this->psicologo->personal->getKey(),
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
        ]);

        $this->assertDatabaseHas('atenciones', [
            'cod_residente' => $this->residente->getKey(),
            'tipo_atencion' => 'VALORACION_AFECTIVA',
            'estado' => 'FINALIZADA',
        ]);
    }

<<<<<<< HEAD
    public function test_psicologo_puede_guardar_control_funcional_iadl_soporte_c03(): void
    {
        $this->actingAs($this->psicologo);

        Livewire::test(FichaPsicologicaResidente::class, ['residente' => $this->residente])
            ->call('cambiarTab', 'funcionamiento')
            ->set('formIadl.actividad', 'MEDICACION')
            ->set('formIadl.oportunidad', 'OBSERVADA')
            ->set('formIadl.desempeno', 'INDEPENDIENTE')
            ->set('formIadl.tipo_ayuda', 'NINGUNA')
            ->set('formIadl.causa_ayuda', 'NINGUNA')
            ->set('formIadl.nivel_previo', 'INDEPENDIENTE')
            ->set('formIadl.fuente', 'OBSERVACION_DIRECTA')
            ->call('guardarControlIadl')
            ->assertHasNoErrors()
            ->assertSee('Observación funcional IADL guardada');

        $this->assertDatabaseHas('controles_funcionales_iadl', [
            'cod_residente' => $this->residente->getKey(),
            'actividad' => 'MEDICACION',
            'desempeno' => 'INDEPENDIENTE',
            'estado' => 'REGISTRADO',
        ]);
=======
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
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
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

<<<<<<< HEAD
        $inst = \App\Models\Instrumento::firstOrCreate(
=======
        $inst = Instrumento::firstOrCreate(
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            ['cod_instrumento' => 'INS_COG_MMSE_TEST'],
            ['codigo' => 'MMSE_TEST', 'nombre' => 'Minimental Test', 'tipo' => 'COGNITIVO', 'estado' => 'ACTIVO']
        );

        $personal = $this->psicologo->personal;
<<<<<<< HEAD
        $area = \App\Models\Area::firstOrCreate(['cod_area' => 'ARE_COG'], ['nombre' => 'Cognitiva', 'estado' => 'ACTIVA']);

        $atencion1 = \App\Models\Atencion::create([
=======
        $area = Area::firstOrCreate(['cod_area' => 'ARE_COG'], ['nombre' => 'Cognitiva', 'estado' => 'ACTIVA']);

        $atencion1 = Atencion::create([
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            'cod_atencion' => 'ATE_TEND_01',
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_area' => $area->getKey(),
            'tipo_atencion' => 'VALORACION',
            'fecha_hora' => now()->subMonths(2),
            'estado' => 'FINALIZADA',
        ]);

<<<<<<< HEAD
        \App\Models\AplicacionInstrumento::create([
=======
        AplicacionInstrumento::create([
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
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

<<<<<<< HEAD
        $atencion2 = \App\Models\Atencion::create([
=======
        $atencion2 = Atencion::create([
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            'cod_atencion' => 'ATE_TEND_02',
            'cod_residente' => $this->residente->getKey(),
            'cod_personal' => $personal->getKey(),
            'cod_area' => $area->getKey(),
            'tipo_atencion' => 'VALORACION',
            'fecha_hora' => now()->subMonths(1),
            'estado' => 'FINALIZADA',
        ]);

<<<<<<< HEAD
        \App\Models\AplicacionInstrumento::create([
=======
        AplicacionInstrumento::create([
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
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
<<<<<<< HEAD
            ->assertSee('Resultados cognitivos')
            ->call('cambiarTab', 'orion')
            ->assertSet('tab', 'orion')
            ->assertSee('Resultados del sistema experto')
=======
            ->assertSee('Resultados Apoyo a la decisión')
            ->call('cambiarTab', 'orion')
            ->assertSet('tab', 'orion')
            ->assertSee('Motor Experto Apoyo a la decisión')
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            ->assertSee('Criterios diagnósticos Petersen')
            ->assertSee('C01 · Queja cognitiva confirmada')
            ->assertSee('C02 · Deterioro cognitivo objetivo')
            ->assertSee('C03 · Preservación de actividades funcionales (IADL)')
            ->assertSee('C04 · Ausencia de demencia / Criterio diferencial')
            ->assertSee('C05 · Juicio clínico global interdisciplinario')
<<<<<<< HEAD
            ->assertSee('El cálculo actual todavía no se guarda como una evaluación histórica completa.')
            ->assertDontSee('persistido = false');
    }

    public function test_tarjetas_del_resumen_abren_la_seccion_del_mismo_residente(): void
    {
        $this->actingAs($this->psicologo);
        $respuesta = $this->get(route('admin.psicologia.residente.ficha', $this->residente))->assertOk();
        foreach (['cognitiva', 'afectiva', 'funcionamiento', 'seguimiento'] as $tab) {
            $ruta = route('admin.psicologia.residente.ficha', ['residente' => $this->residente, 'tab' => $tab]);
            $respuesta->assertSee($ruta, false);
            $this->get($ruta)->assertOk()->assertSee($this->residente->nombre_completo);
        }
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
        $this->assertDatabaseCount('valoraciones_psicologicas', 0);
    }

    public function test_resumen_distingue_observacion_sin_puntuacion_y_muestra_autoria_y_fecha(): void
    {
        $this->actingAs($this->psicologo);
        $this->residente->update(['nombres' => 'María del Carmen Alejandra', 'apellido_paterno' => 'Fernández de Córdova']);
        $instrumento = Instrumento::create(['codigo' => 'OBS-FICHA-QA', 'nombre' => 'Observación cognitiva de seguimiento',
            'tipo' => 'OBSERVACIONAL', 'version' => 'QA-1', 'estado' => 'ACTIVO']);
        AplicacionInstrumento::create(['cod_residente' => $this->residente->getKey(), 'cod_instrumento' => $instrumento->getKey(),
            'cod_personal' => $this->psicologo->personal->getKey(), 'fecha_hora' => now()->subDay(),
            'estado' => 'COMPLETA', 'puntaje_total' => null, 'puntaje_maximo' => null]);
        $area = \App\Models\Area::create(['nombre' => 'Área afectiva de prueba', 'estado' => 'ACTIVA']);
        $atencion = \App\Models\Atencion::create(['cod_residente' => $this->residente->getKey(),
            'cod_area' => $area->getKey(), 'cod_personal' => $this->psicologo->personal->getKey(),
            'fecha_hora' => now()->subDay(), 'tipo_atencion' => 'VALORACION_AFECTIVA',
            'motivo' => 'Verificación de la presentación del expediente', 'estado' => 'FINALIZADA']);
        ValoracionPsicologica::create(['cod_residente' => $this->residente->getKey(),
            'cod_atencion' => $atencion->getKey(),
            'cod_personal' => $this->psicologo->personal->getKey(), 'fecha_hora' => now()->subDay(),
            'estado_animo' => 'EUTIMICO', 'ansiedad' => 'AUSENTE', 'estado' => 'VIGENTE',
            'conclusion' => 'Valoración sintética utilizada únicamente para verificar la presentación del expediente.']);

        $respuesta = $this->get(route('admin.psicologia.residente.ficha', $this->residente))->assertOk()
            ->assertSee($this->residente->nombre_completo)->assertSee('Observación clínica')
            ->assertSee('Sin puntuación registrada')->assertSee('Eutimico')->assertSee('Ausente')
            ->assertSee('Licenciada Psicología')->assertSee('20/10/2026 · 12:00');
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
        if ($directorio = getenv('RM_FICHA_PSICOLOGICA_QA_DIR')) {
            file_put_contents($directorio.'/ficha-psicologica-con-datos.html', $respuesta->getContent());
        }
    }
}

=======
            ->assertSee('persistido = false');
    }
}
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
