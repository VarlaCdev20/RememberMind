<?php

namespace Tests\Feature\SistemaExperto;

use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Backend\Modulos\SistemaExperto\Conocimiento\InventarioExperto;
use App\Backend\Modulos\SistemaExperto\Servicios\{LecturaResultadosExperto, LecturaFuenteEvidencia};
use App\Frontend\Livewire\Medico\Clinica\ResultadosExpertoResidente;
use App\Frontend\Livewire\Enfermeria\Cuidados\MisPacientes;
use App\Models\{Residente, User};
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\SistemaExperto\{PruebaConBaseDesechable, FixtureLecturaExperta as F};

class ResultadosExpertoResidenteTest extends PruebaConBaseDesechable
{
    private User $medico;
    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->medico, $this->residente] = F::crear();
        $this->actingAs($this->medico);
    }

    private function leer(string $id = 'EVAL_D', string $criterio = 'COG-MEM'): array
    {
        return app(LecturaResultadosExperto::class)->consultar($this->medico, $this->residente, $id, $criterio);
    }

    private function huella(): array
    {
        $filas = [];
        foreach (array_merge(InventarioExperto::TABLAS, ['alertas', 'notas_clinicas', 'activity_log']) as $tabla) {
            $filas[$tabla] = hash('sha256', json_encode(DB::table($tabla)->get()->all(), JSON_THROW_ON_ERROR));
        }
        return $filas;
    }

    private function asignarEnfermeria(): void
    {
        $this->medico->syncRoles(['ENFERMEROS']);
        DB::table('personal')->where('cod_personal', 'PER_MED')->update(['profesion' => 'ENFERMERIA']);
        \App\Models\Turno::create(['cod_turno' => 'TUR_LECTURA', 'nombre' => 'Turno sintético', 'orden' => 1, 'hora_inicio' => '00:00:00', 'hora_cierre' => '23:59:59', 'estado' => 'ACTIVO']);
        \App\Models\Jornada::create(['cod_jornada' => 'JOR_LECTURA', 'cod_turno' => 'TUR_LECTURA', 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        \App\Models\AsignacionResidenteJornada::create(['cod_asignacion' => 'ARJ_LECTURA', 'cod_jornada' => 'JOR_LECTURA', 'cod_personal' => 'PER_MED', 'cod_residente' => $this->residente->getKey(), 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $this->assertFalse($this->medico->checkPermissionTo('valoracion_medica.ver', 'web'));
    }

    public function test_enfermeria_consulta_resultados_sin_validaciones_ni_escrituras(): void
    {
        $this->asignarEnfermeria();
        $antes = $this->huella();
        $url = route('admin.enfermeria.pacientes.resultados-experto', $this->residente);
        $this->get($url)->assertOk()->assertSee('Consulta de Enfermería')->assertSee('Volver a Mis residentes')->assertDontSee('Confirmar concordancia');
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $c->assertSet('enfermeria', true)->call('seleccionarEvaluacion', 'EVAL_D')->assertSee('Dificultad evidenciada')
            ->call('seleccionarCriterio', 'COG-ATE')->assertSee('Sin evaluación disponible')
            ->call('seleccionarCriterio', 'COG-MEM')->call('verEvidencia', 'EV_D_A')->assertSee('Acceso al dato fuente restringido');
        $this->assertSame($antes, $this->huella());
        $this->get(route('admin.medico.residente.resultados-experto', $this->residente))->assertForbidden();
        config()->set('sistema_experto.lectura_clinica', []);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertSee('Publicación clínica no habilitada')->assertDontSee('Dificultad evidenciada');
    }

    public function test_entradas_de_enfermeria_en_resumen_selector_y_ficha(): void
    {
        $this->asignarEnfermeria();
        $url = route('admin.enfermeria.pacientes.resultados-experto', $this->residente);
        $this->get(route('admin.enfermeria.pacientes', ['residente' => $this->residente->getKey()]))->assertOk()->assertSee($url, false);
        Livewire::test(MisPacientes::class)->call('seleccionarResidente', $this->residente->getKey())
            ->call('mostrarSelectorRegistro')->assertSee('Consulta clínica')->assertSee($url, false);
        $this->get(route('admin.enfermeria.pacientes.ficha', ['adulto' => $this->residente->getKey()]))->assertOk()->assertSee($url, false);
    }

    public function test_enfermeria_no_consulta_otro_residente_y_reautoriza_asignacion(): void
    {
        $this->asignarEnfermeria();
        $otro = F::residenteAdicional('ENF_OTRO');
        $this->get(route('admin.enfermeria.pacientes.resultados-experto', $otro))->assertNotFound();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $otro])->assertNotFound();
        $this->medico->assignRole('PSICOLOGO/A');
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $otro])->assertNotFound();
        $this->medico->removeRole('PSICOLOGO/A');
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        DB::table('asignaciones_residente_jornada')->where('cod_asignacion', 'ARJ_LECTURA')->update(['estado' => 'INACTIVA']);
        $c->call('seleccionarCriterio', 'COG-MEM')->assertNotFound();
        $this->get(route('admin.enfermeria.pacientes.resultados-experto', $this->residente))->assertNotFound();
    }

    public function test_enfermeria_sin_permiso_personal_activo_o_jornada_no_lee(): void
    {
        $this->asignarEnfermeria();
        DB::table('jornadas')->where('cod_jornada', 'JOR_LECTURA')->update(['estado' => 'CERRADA']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertNotFound();
        DB::table('jornadas')->where('cod_jornada', 'JOR_LECTURA')->update(['estado' => 'ABIERTA']);
        DB::table('personal')->where('cod_personal', 'PER_MED')->update(['estado' => 'INACTIVO']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertNotFound();
        DB::table('personal')->where('cod_personal', 'PER_MED')->update(['estado' => 'ACTIVO']);
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $this->medico->roles->first()->revokePermissionTo('controles_cognitivos.ver');
        $this->medico->unsetRelation('roles');
        $c->call('seleccionarCriterio', 'COG-MEM')->assertNotFound();
    }

    public function test_ruta_real_selectores_fundamento_y_revision_no_escriben(): void
    {
        $antes = $this->huella();
        $this->get(route('admin.medico.residente.resultados-experto', $this->residente))->assertOk()->assertSee('Resultados del sistema experto');
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $c->call('seleccionarEvaluacion', 'EVAL_D')->assertSee('Dificultad evidenciada')->assertSee('No preservado en la traza.')->assertSee('disabled', false);
        foreach (['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS', 'COG-MEM'] as $criterio) { $c->call('seleccionarCriterio', $criterio)->assertSet('criterio', $criterio); }
        $c->call('verEvidencia', 'EV_D_A')->assertSee('Registro fuente no disponible')->call('verEvidencia', 'EV_D_A')->assertSet('evidencia', null);
        $c->call('seleccionarEvaluacion', 'EVAL_S')->assertSee('Sin dificultad evidenciada')->call('seleccionarEvaluacion', 'EVAL_M')->assertSee('Hallazgos mixtos');
        $this->assertSame($antes, $this->huella());
    }

    public function test_visor_consulta_traza_sin_mutar_y_reautoriza_al_abrir(): void
    {
        $antes = $this->huella();
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $c->call('seleccionarEvaluacion', 'EVAL_D')->call('verTrazabilidad')
            ->assertSet('trazabilidadAbierta', true)->assertSee('Trazabilidad completa')
            ->assertSee('EVAL_D')->assertSee('RULE_A')
            ->call('cerrarTrazabilidad')->assertSet('trazabilidadAbierta', false)
            ->call('verTrazabilidad')->call('seleccionarCriterio', 'COG-ATE')
            ->assertSet('trazabilidadAbierta', false);
        $this->assertSame($antes, $this->huella());
        $this->medico->update(['estado' => 'INACTIVO']);
        $c->call('verTrazabilidad')->assertNotFound();
    }

    public function test_registros_existentes_sin_evaluacion_no_generan_resultados(): void
    {
        $this->asignarEnfermeria();
        $responsableCarga = User::create(['cod_usuario' => 'USR_CARGA_TEST', 'correo' => 'carga@example.test',
            'contrasena' => bin2hex(random_bytes(24)), 'estado' => 'ACTIVO']);
        $responsableCarga->assignRole('SUPERADMINISTRADOR');
        (new \App\Backend\Modulos\SistemaExperto\Acciones\CargarConocimientoOrion)->ejecutar($responsableCarga);
        $otro = F::residenteAdicional('CONTROL_AJENO');
        DB::table('evaluaciones_expertas')->update(['origen_activacion' => 'PRUEBA_TECNICA']);
        foreach (range(1, 7) as $i) {
            DB::table('controles_cognitivos')->insert(['cod_control_cognitivo' => 'CC_HIST_'.$i,
                'cod_residente' => $this->residente->getKey(), 'cod_personal' => 'PER_MED',
                'fecha_hora' => now()->subDays($i), 'estado' => 'VIGENTE', 'memoria_reciente' => 'CONSERVADA',
                'memoria_remota' => null, 'repite_preguntas' => false, 'observacion' => 'Observación guardada '.$i]);
        }
        foreach (['AJENO' => [$otro->getKey(), now(), 'VIGENTE'],
            'ANULADO' => [$this->residente->getKey(), now(), 'ANULADO'],
            'FUTURO' => [$this->residente->getKey(), now()->addDay(), 'VIGENTE']] as $id => [$r, $fecha, $estado]) {
            DB::table('controles_cognitivos')->insert(['cod_control_cognitivo' => 'CC_'.$id,
                'cod_residente' => $r, 'cod_personal' => 'PER_MED', 'fecha_hora' => $fecha,
                'estado' => $estado, 'observacion' => 'NO_MOSTRAR_'.$id]);
        }
        $antes = $this->huella();
        $fuentesAntes = DB::table('controles_cognitivos')->get()->toJson();
        $d = app(LecturaResultadosExperto::class)->consultar($this->medico, $this->residente, null, 'COG-MEM');
        $this->assertNull($d['evaluacion']);
        $this->assertNull($d['detalle']['resultado']);
        $this->assertCount(5, $d['registros_cognitivos']);
        $this->assertSame('CC_HIST_1', $d['registros_cognitivos'][0]['codigo']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Conocimiento disponible')->assertSee('ORION-V1-20261008')
            ->assertSee('Registros cognitivos disponibles')->assertSee('Observación guardada 1')
            ->assertSee('Conservada')->assertSee('No registrado')->assertSee('No marcado en el registro')
            ->assertDontSee('Observación guardada 6')->assertDontSee('NO_MOSTRAR_AJENO')
            ->assertDontSee('NO_MOSTRAR_ANULADO')->assertDontSee('NO_MOSTRAR_FUTURO')
            ->assertDontSee('Sin dificultad evidenciada')->call('seleccionarCriterio', 'COG-ATE')
            ->assertSee('Conocimiento disponible')->assertSee('Capacidad de seleccionar y mantener el foco')
            ->assertDontSee('Registros cognitivos disponibles');
        $this->assertSame($antes, $this->huella());
        $this->assertSame($fuentesAntes, DB::table('controles_cognitivos')->get()->toJson());
    }

    public function test_cinco_criterios_resultados_limitados_y_version_historica(): void
    {
        $d = $this->leer();
        $this->assertCount(5, $d['perfil']);
        $this->assertSame('DIFICULTAD_EVIDENCIADA', $d['detalle']['resultado']['codigo']);
        $this->assertStringContainsString('no establece por sí sola un diagnóstico', $d['detalle']['interpretacion']);
        $this->assertSame('SIN_DIFICULTAD_EVIDENCIADA', $this->leer('EVAL_S')['detalle']['resultado']['codigo']);
        $this->assertStringContainsString('no equivale a normalidad cognitiva global', $this->leer('EVAL_S')['detalle']['interpretacion']);
        $this->assertSame('HALLAZGOS_MIXTOS', $this->leer('EVAL_M')['detalle']['resultado']['codigo']);
        $this->assertSame('ARTIFICIAL_1', $d['evaluacion']['version']);
        $this->assertDatabaseHas('versiones_modelo_experto', ['cod_version_modelo' => 'VER_TEST', 'estado' => 'INACTIVO']);
        $this->assertSame('Sin evaluación disponible', $this->leer(criterio: 'COG-ATE')['detalle']['estado']);
    }

    public function test_ausencia_es_distinta_de_insuficiencia_e_inactividad(): void
    {
        $this->assertSame('Información insuficiente', $this->leer('EVAL_I')['detalle']['estado']);
        $this->assertSame('Información preliminar', $this->leer('EVAL_P')['detalle']['estado']);
        $this->assertNull($this->leer('EVAL_I')['detalle']['resultado']);
        config()->set('sistema_experto.lectura_clinica', []);
        $d = $this->leer();
        $this->assertNull($d['detalle']['resultado']);
        $this->assertSame('Publicación clínica no habilitada', $d['detalle']['estado']);
        $this->assertStringNotContainsString('evidencia válida e interpretable', $d['detalle']['interpretacion']);
        DB::table('evaluaciones_expertas')->update(['origen_activacion' => 'PRUEBA_TECNICA']);
        $d = app(LecturaResultadosExperto::class)->consultar($this->medico, $this->residente, null, 'COG-MEM');
        $this->assertNull($d['evaluacion']);
        $this->assertSame(0, $d['historial']->total());
        $this->assertSame('Sin evaluación disponible', $d['detalle']['estado']);
    }

    public function test_contexto_no_sostiene_un_resultado(): void
    {
        DB::table('evidencias_criterio_evaluacion')->where('cod_evidencia_criterio', 'EP_D_A')->update(['rol_en_criterio' => 'CONTEXTUAL']);
        $d = $this->leer()['detalle'];
        $this->assertNull($d['resultado']);
        $this->assertSame('Contexto', $d['evidencias']['EV_D_A']['papel']);
        $this->assertSame('Error de inferencia', $d['estado']);
    }

    public function test_evidencia_excluida_no_respalda_resultado(): void
    {
        DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'EV_D_A')->update(['estado_admisibilidad' => 'NO_ADMISIBLE']);
        $this->assertNull($this->leer()['detalle']['resultado']);
        $this->assertSame('Error de inferencia', $this->leer()['detalle']['estado']);
    }

    public function test_soporte_ausente_y_resultado_discordante_se_ocultan(): void
    {
        DB::table('evidencias_soporte_condicion')->where('cod_evidencia_soporte_condicion', 'SUP_D_A')->delete();
        $this->assertNull($this->leer()['detalle']['resultado']);
        DB::table('resultados_criterio')->where('cod_resultado_criterio', 'RC_S')->update(['cod_valor_semantico' => 'RES_D']);
        $this->assertNull($this->leer('EVAL_S')['detalle']['resultado']);
    }

    public function test_regla_mixta_directa_y_resultado_sin_ev2_se_rechazan(): void
    {
        DB::table('consecuencias_regla_experta')->where('cod_consecuencia_regla', 'CONS_A')->update(['cod_valor_semantico' => 'RES_M']);
        DB::table('resultados_criterio')->where('cod_resultado_criterio', 'RC_D')->update(['cod_valor_semantico' => 'RES_M']);
        $this->assertNull($this->leer()['detalle']['resultado']);
        DB::table('evaluacion_criterios')->where('cod_evaluacion_criterio', 'EC_S')->update(['estado_evaluabilidad' => 'EV-CM-0']);
        $this->assertNull($this->leer('EVAL_S')['detalle']['resultado']);
    }

    public function test_mismo_episodio_no_es_soporte_mixto_independiente(): void
    {
        F::insertar('relaciones_evidencias_evaluacion', ['cod_relacion_evidencia' => 'REL_EP', 'cod_evidencia_origen' => 'EV_M_A', 'cod_evidencia_destino' => 'EV_M_B', 'tipo_relacion' => 'MISMO_EPISODIO_CLINICO', 'estado' => 'ACTIVO']);
        $this->assertNull($this->leer('EVAL_M')['detalle']['resultado']);
        $this->assertStringContainsString('independiente', implode(' ', $this->leer('EVAL_M')['detalle']['integridad']));
    }

    public function test_dominios_homonimos_no_reemplazan_la_asociacion_exacta(): void
    {
        F::insertar('dominios_valores_expertos', ['cod_dominio_valores' => 'DOM_OTRO', 'cod_version_modelo' => 'VER_TEST', 'codigo_dominio' => 'OTRO', 'estado' => 'ACTIVO']);
        F::insertar('valores_semanticos', ['cod_valor_semantico' => 'RES_OTRO', 'cod_dominio_valores' => 'DOM_OTRO', 'codigo_valor' => 'DIFICULTAD_EVIDENCIADA', 'estado' => 'ACTIVO', 'estado_aprobacion' => 'APROBADO']);
        DB::table('criterios_dominios_resultado')->where('cod_criterio_dominio_resultado', 'CR_DOM')->update(['cod_dominio_valores' => 'DOM_OTRO']);
        DB::table('consecuencias_regla_experta')->where('cod_consecuencia_regla', 'CONS_A')->update(['cod_valor_semantico' => 'RES_OTRO']);
        $this->assertNull($this->leer()['detalle']['resultado']);
    }

    public function test_sin_resultado_ev2_y_estado_desconocido_no_inventan_conclusion(): void
    {
        DB::table('resultados_criterio')->where('cod_resultado_criterio', 'RC_D')->delete();
        $this->assertNull($this->leer()['detalle']['resultado']);
        DB::table('evaluacion_criterios')->where('cod_evaluacion_criterio', 'EC_P')->update(['estado_evaluabilidad' => 'DESCONOCIDO']);
        $this->assertSame('Error de inferencia', $this->leer('EVAL_P')['detalle']['estado']);
    }

    public function test_autorizacion_roles_cuenta_personal_y_preview(): void
    {
        $antes = $this->huella();
        foreach (['FAMILIAR', 'ENFERMEROS', 'GERENTE', 'ADMINISTRADOR', 'PSICOLOGO/A'] as $rol) {
            $this->medico->syncRoles([$rol]);
            $this->medico->givePermissionTo(['residentes.ver', 'controles_cognitivos.ver', 'valoracion_medica.ver']);
            Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertNotFound();
        }
        $this->medico->syncRoles(['MEDICO GENERAL/GERIATRA']);
        DB::table('usuarios')->where('cod_usuario', $this->medico->getKey())->update(['estado' => 'INACTIVO']); $this->medico->refresh();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertNotFound();
        DB::table('usuarios')->where('cod_usuario', $this->medico->getKey())->update(['estado' => 'ACTIVO']); $this->medico->refresh();
        DB::table('personal')->where('cod_personal', 'PER_MED')->update(['estado' => 'INACTIVO']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertNotFound();
        DB::table('personal')->where('cod_personal', 'PER_MED')->update(['estado' => 'ACTIVO']);
        session()->put(RolePreviewService::SESSION_KEY, 'MEDICO GENERAL/GERIATRA');
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertNotFound();
        $this->assertSame($antes, $this->huella());
    }

    public function test_revocacion_en_accion_y_bypass_superadmin_no_dan_acceso(): void
    {
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $this->medico->roles->first()->revokePermissionTo('valoracion_medica.ver'); $this->medico->unsetRelation('roles');
        $c->call('seleccionarCriterio', 'COG-MEM')->assertNotFound();
        $this->medico->roles->first()->givePermissionTo('valoracion_medica.ver'); $this->medico->unsetRelation('roles');
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $this->medico->roles->first()->revokePermissionTo('controles_cognitivos.ver'); $this->medico->unsetRelation('roles');
        $c->call('seleccionarCriterio', 'COG-MEM')->assertNotFound();
        $this->medico->syncRoles(['SUPERADMINISTRADOR']);
        $this->medico->roles->first()->revokePermissionTo('controles_cognitivos.ver'); $this->medico->unsetRelation('roles');
        $this->assertTrue($this->medico->can('controles_cognitivos.ver'));
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertNotFound();
    }

    public function test_ids_cruzados_y_propiedades_locked_no_permiten_idor(): void
    {
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $c->call('seleccionarEvaluacion', 'NO_EXISTE')->assertNotFound();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->call('seleccionarEvaluacion', 'EVAL_D')->call('verEvidencia', 'EV_S_B')->assertNotFound();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->call('seleccionarCriterio', 'NO_EXISTE')->assertStatus(422);
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->set('codResidente', 'OTRO');
    }

    public function test_fuente_instrumental_restringida_y_fuente_cognitiva_actual(): void
    {
        $this->medico->roles->first()->revokePermissionTo('aplicaciones_instrumento.ver'); $this->medico->unsetRelation('roles');
        $s = app(LecturaFuenteEvidencia::class);
        $this->assertStringContainsString('restringido', $s->consultar($this->medico, $this->residente, 'EVAL_D', 'EV_D_A')['estado']);
        DB::table('controles_cognitivos')->insert(['cod_control_cognitivo' => 'CC_QA', 'cod_residente' => $this->residente->getKey(), 'cod_personal' => 'PER_MED', 'fecha_hora' => '2026-10-08 11:00:00', 'estado' => 'ACTIVO', 'memoria_reciente' => 'literal_fuente_actual']);
        DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'EV_D_A')->update(['cod_mapeo_variable_fuente' => 'MAP_OBS', 'cod_registro_fuente' => 'CC_QA']);
        $dato = $s->consultar($this->medico, $this->residente, 'EVAL_D', 'EV_D_A');
        $this->assertSame('literal_fuente_actual', $dato['valor']);
        $this->assertStringContainsString('no es la instantánea', $dato['estado']);
        DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'EV_D_A')->update(['cod_registro_fuente' => 'FUENTE_OTRO']);
        $this->assertArrayNotHasKey('valor', $s->consultar($this->medico, $this->residente, 'EVAL_D', 'EV_D_A'));
    }

    public function test_evaluacion_y_registro_de_otro_residente_no_se_exponen(): void
    {
        $otro = F::residenteAdicional('DOS');
        F::insertar('evaluaciones_expertas', ['cod_evaluacion_experta' => 'EVAL_OTRO', 'cod_residente' => $otro->getKey(), 'cod_version_modelo' => 'VER_TEST', 'origen_activacion' => 'SINTETICA', 'estado_ejecucion' => 'SINTETICA_FINAL']);
        $antes = $this->huella();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->call('seleccionarEvaluacion', 'EVAL_OTRO')->assertNotFound();
        DB::table('controles_cognitivos')->insert(['cod_control_cognitivo' => 'CC_OTRO', 'cod_residente' => $otro->getKey(), 'cod_personal' => 'PER_MED', 'fecha_hora' => '2026-10-08 11:00:00', 'estado' => 'ACTIVO', 'memoria_reciente' => 'NO_REVELAR']);
        DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'EV_D_A')->update(['cod_mapeo_variable_fuente' => 'MAP_OBS', 'cod_registro_fuente' => 'CC_OTRO']);
        $dato = app(LecturaFuenteEvidencia::class)->consultar($this->medico, $this->residente, 'EVAL_D', 'EV_D_A');
        $this->assertArrayNotHasKey('valor', $dato);
        $despues = $this->huella();
        $this->leer();
        $this->assertSame($despues, $this->huella());
    }

    public function test_dependencia_transitiva_y_corroboracion_se_distinguen(): void
    {
        F::insertar('evidencias_evaluacion', ['cod_evidencia_evaluacion' => 'EV_PUENTE', 'cod_evaluacion_experta' => 'EVAL_M', 'cod_mapeo_variable_fuente' => 'MAP_OBS', 'cod_registro_fuente' => 'CC_PUENTE', 'estado_representacion' => 'MAPEADO', 'cod_valor_semantico' => 'VAL_A', 'estado_admisibilidad' => 'ADMISIBLE']);
        F::insertar('relaciones_evidencias_evaluacion', ['cod_relacion_evidencia' => 'REL_COR', 'cod_evidencia_origen' => 'EV_M_A', 'cod_evidencia_destino' => 'EV_M_B', 'tipo_relacion' => 'CORROBORACION_CLINICA', 'estado' => 'ACTIVO']);
        $this->assertSame('HALLAZGOS_MIXTOS', $this->leer('EVAL_M')['detalle']['resultado']['codigo']);
        foreach (['A' => ['EV_M_A', 'EV_PUENTE'], 'B' => ['EV_PUENTE', 'EV_M_B']] as $id => [$a, $b]) {
            F::insertar('relaciones_evidencias_evaluacion', ['cod_relacion_evidencia' => 'REL_'.$id, 'cod_evidencia_origen' => $a, 'cod_evidencia_destino' => $b, 'tipo_relacion' => 'MISMO_EPISODIO_CLINICO', 'estado' => 'ACTIVO']);
        }
        $this->assertNull($this->leer('EVAL_M')['detalle']['resultado']);
    }

    public function test_fuente_solo_corroborativa_y_traza_incoherente_no_publican(): void
    {
        DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'EV_D_A')->update(['cod_mapeo_variable_fuente' => 'MAP_COR']);
        DB::table('condiciones_regla_experta')->where('cod_condicion_regla', 'COND_A')->update(['cod_variable_experta' => 'VAR_COR']);
        $this->assertNull($this->leer()['detalle']['resultado']);
        DB::table('evaluaciones_condiciones_regla')->where('cod_evaluacion_condicion', 'CV_S_B')->update(['estado_condicion' => 'NO_CUMPLE']);
        $this->assertNull($this->leer('EVAL_S')['detalle']['resultado']);
    }

    public function test_consulta_invitada_denegada_y_sin_n_mas_uno(): void
    {
        auth()->logout();
        $this->get(route('admin.medico.residente.resultados-experto', $this->residente))->assertRedirect(route('login'));
        $this->actingAs($this->medico);
        DB::enableQueryLog(); DB::flushQueryLog(); $this->leer(); $una = count(DB::getQueryLog());
        DB::flushQueryLog(); $this->leer('EVAL_M'); $dos = count(DB::getQueryLog()); DB::disableQueryLog();
        $this->assertLessThanOrEqual($una + 1, $dos);
    }
    public function test_ficha_y_carpeta_cognitiva_contienen_entrada_contextual(): void
    {
        $url = route('admin.medico.residente.resultados-experto', $this->residente);
        $this->get(route('admin.medico.residente.ficha', ['adulto' => $this->residente->getKey()]))->assertOk()->assertSee($url, false);
        $this->get(route('admin.adultos-mayores.show', ['adulto_mayor' => $this->residente->getKey(), 'tab' => 'cognitivo']))->assertOk()->assertSee($url, false)->assertDontSee('Deterioro Detectado');
    }

    public function test_componente_nulo_contextual_o_sin_participacion_no_acredita_ev2(): void
    {
        DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'EV_D_A')->update(['cod_mapeo_variable_fuente' => 'MAP_OBS']);
        DB::table('condiciones_regla_experta')->where('cod_condicion_regla', 'COND_A')->update(['cod_variable_experta' => 'VAR_OBS']);
        F::insertar('evidencias_evaluacion', ['cod_evidencia_evaluacion' => 'EV_COMP_EXTRA', 'cod_evaluacion_experta' => 'EVAL_D', 'cod_mapeo_variable_fuente' => 'MAP_INS', 'cod_registro_fuente' => 'APP_EXTRA', 'estado_representacion' => 'MAPEADO', 'cod_valor_semantico' => null, 'estado_admisibilidad' => 'ADMISIBLE']);
        F::insertar('evidencias_criterio_evaluacion', ['cod_evidencia_criterio' => 'EP_COMP_EXTRA', 'cod_evaluacion_criterio' => 'EC_D', 'cod_evidencia_evaluacion' => 'EV_COMP_EXTRA', 'rol_en_criterio' => 'INSTRUMENTAL', 'estado_participacion' => 'SINTETICA_USADA']);
        $this->assertNull($this->leer()['detalle']['resultado']);
        DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'EV_COMP_EXTRA')->update(['cod_valor_semantico' => 'VAL_A']);
        $this->assertSame('DIFICULTAD_EVIDENCIADA', $this->leer()['detalle']['resultado']['codigo']);
        DB::table('evidencias_criterio_evaluacion')->where('cod_evidencia_criterio', 'EP_COMP_EXTRA')->update(['rol_en_criterio' => 'CONTEXTUAL']);
        $this->assertNull($this->leer()['detalle']['resultado']);
        DB::table('evidencias_criterio_evaluacion')->where('cod_evidencia_criterio', 'EP_COMP_EXTRA')->update(['rol_en_criterio' => 'INSTRUMENTAL', 'estado_participacion' => 'NO_USADA']);
        $this->assertNull($this->leer()['detalle']['resultado']);
        DB::table('evidencias_criterio_evaluacion')->where('cod_evidencia_criterio', 'EP_S_B')->update(['estado_participacion' => 'NO_USADA']);
        $this->assertNull($this->leer('EVAL_S')['detalle']['resultado']);
        DB::table('evidencias_criterio_evaluacion')->where('cod_evidencia_criterio', 'EP_M_A')->update(['rol_en_criterio' => 'CONTEXTUAL']);
        $this->assertNull($this->leer('EVAL_M')['detalle']['resultado']);
    }

    public function test_historial_publica_solo_estados_verificados_y_paginar_no_muta(): void
    {
        DB::table('resultados_criterio')->where('cod_resultado_criterio', 'RC_D')->update(['cod_valor_semantico' => 'RES_S']);
        $h = collect($this->leer('EVAL_S')['historial']->items())->firstWhere('id', 'EVAL_D');
        $this->assertSame('Error de inferencia', $h['criterios'][0]['estado']);
        foreach (range(1, 8) as $i) { F::insertar('evaluaciones_expertas', ['cod_evaluacion_experta' => 'EVAL_EXTRA_'.$i, 'cod_residente' => $this->residente->getKey(), 'cod_version_modelo' => 'VER_TEST', 'origen_activacion' => 'SINTETICA', 'estado_ejecucion' => 'SINTETICA_FINAL']); }
        $antes = $this->huella();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->call('setPage', 2, 'historia')->assertSee('Historial de evaluaciones');
        $this->assertSame($antes, $this->huella());
        config()->set('sistema_experto.lectura_clinica', []);
        $h = collect(app(LecturaResultadosExperto::class)->consultar($this->medico, $this->residente, 'EVAL_S', 'COG-MEM', 1)['historial']->items())->firstWhere('id', 'EVAL_S');
        $this->assertSame('Publicación clínica no habilitada', $h['criterios'][0]['estado']);
    }

    public function test_aplicacion_actual_solo_muestra_procedencia_sin_respuestas(): void
    {
        DB::table('instrumentos')->insert(['cod_instrumento' => 'INST_QA', 'codigo' => 'QA', 'nombre' => 'Instrumento artificial de lectura', 'tipo' => 'SINTETICO', 'version' => 'QA1', 'estado' => 'ACTIVO']);
        DB::table('aplicaciones_instrumento')->insert(['cod_aplicacion' => 'APP_D_A', 'cod_instrumento' => 'INST_QA', 'cod_residente' => $this->residente->getKey(), 'cod_personal' => 'PER_MED', 'fecha_hora' => '2026-10-08 11:00:00', 'estado' => 'COMPLETA']);
        $dato = app(LecturaFuenteEvidencia::class)->consultar($this->medico, $this->residente, 'EVAL_D', 'EV_D_A');
        $this->assertSame('Instrumento artificial de lectura', $dato['instrumento']);
        $this->assertArrayNotHasKey('respuestas', $dato);
        $this->assertArrayNotHasKey('valor', $dato);
    }
}
