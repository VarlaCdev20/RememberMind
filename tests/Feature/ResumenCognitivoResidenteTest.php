<?php

namespace Tests\Feature;

use App\Models\AsignacionResidenteJornada;
use App\Models\ControlCognitivo;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Consultas\ConsultarResumenCognitivoResidente;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\FichaPaciente;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Lectura real con datos sintéticos: hechos, límites, publicación conservada y aislamiento. */
class ResumenCognitivoResidenteTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;

    private Personal $personal;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 17)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->medico = User::factory()->create(['estado' => 'ACTIVO']);
        $this->medico->assignRole('MEDICO GENERAL/GERIATRA');
        $this->personal = Personal::create(['cod_usuario' => $this->medico->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'COG-FASE3', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create();
        $this->actingAs($this->medico);
    }

    /** Crea historia de prueba con valores explícitos, sin ejecutar motores o acciones de diagnóstico. */
    private function control(array $datos = []): ControlCognitivo
    {
        return ControlCognitivo::create($datos + ['cod_residente' => $this->residente->getKey(),
            'cod_personal' => $this->personal->getKey(), 'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE']);
    }

    private function resumen(string $periodo = '30d', ?Residente $residente = null): array
    {
        return app(ConsultarResumenCognitivoResidente::class)->consultar($residente ?? $this->residente, $periodo);
    }

    /** Exportación opcional del Blade real para QA visual local, fuera del repositorio y sin sesión operativa. */
    private function exportar(string $estado, ?array $resumen): void
    {
        if ($directorio = getenv('RM_RESUMEN_COGNITIVO_QA_DIR')) {
            if (! is_dir($directorio)) {
                mkdir($directorio, 0700, true);
            }
            file_put_contents($directorio.'/'.$estado.'.html', view('clinica::livewire.cuidados.ficha.resumen.memoria-estado-cognitivo',
                ['resumenCognitivo' => $resumen, 'adultoMayor' => $this->residente, 'periodoEstado' => '30d'])->render());
        }
    }

    public function test_sin_registros_no_afirma_normalidad_y_se_integra_a_la_ficha(): void
    {
        $resumen = $this->resumen();
        $this->assertSame('Sin observaciones registradas', $resumen['estado']);
        $this->assertNull($resumen['principal']);
        $this->assertNull($resumen['experto']);
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])
            ->assertSee('Memoria y estado cognitivo')->assertSee('Sin observaciones registradas')
            ->assertSee('Análisis cognitivo')->assertDontSee('Sin cambio observado');
        $this->exportar('vacio', $resumen);
    }

    public function test_repeticion_de_preguntas_es_hecho_preliminar_con_fecha_y_autor(): void
    {
        $control = $this->control(['repite_preguntas' => true]);
        $resumen = $this->resumen();
        $this->assertSame('Señal preliminar', $resumen['estado']);
        $this->assertSame('Se documentó repetición de preguntas en un control cognitivo.', $resumen['observacion']);
        $this->assertSame('17/10/2026 11:00', $resumen['principal']['fecha']);
        $this->assertSame('Profesional Sintético', $resumen['principal']['autor']);
        $this->assertSame('Sí', $resumen['principal']['campos']['Repite preguntas']);
        $this->assertSame('No observado', $resumen['principal']['campos']['Olvida indicaciones']);
        $this->assertStringContainsString('no acreditan por sí solas una alteración cognitiva objetiva', $resumen['alcance']);
        $this->assertNull($resumen['experto']);
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])
            ->assertSee('Consultar control original')->assertDontSee($control->getKey());
        $this->exportar('preliminar', $resumen);
    }

    public function test_olvido_de_indicaciones_no_se_atribuye_automaticamente_a_memoria(): void
    {
        $this->control(['olvida_indicaciones' => true, 'comprension' => 'PARCIAL']);
        $resumen = $this->resumen();
        $this->assertSame('Se documentó olvido de indicaciones en un control cognitivo.', $resumen['observacion']);
        $this->assertStringContainsString('no determinan por sí solos su causa', $resumen['alcance']);
        $this->assertSame('Parcial', $resumen['principal']['campos']['Comprensión']);
    }

    public function test_varios_registros_del_mismo_episodio_no_crean_confirmacion_ni_perdida_de_memoria(): void
    {
        foreach (range(1, 3) as $i) {
            $this->control(['repite_preguntas' => true, 'olvida_indicaciones' => true, 'observacion' => 'Mismo episodio sintético.']);
        }
        $resumen = $this->resumen();
        $this->assertCount(3, $resumen['registros']);
        $this->assertSame('Señal preliminar', $resumen['estado']);
        $this->assertSame([], $resumen['contradicciones']);
        $this->assertStringContainsString('su cantidad no demuestra pérdida de memoria ni evidencia independiente', $resumen['por_que']);
        $this->assertNull($resumen['experto']);
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
        $this->assertDatabaseCount('diagnosticos', 0);
        $this->assertDatabaseCount('alertas', 0);
    }

    public function test_fechas_diferentes_conservan_hechos_separados_sin_inferir_progresion(): void
    {
        $this->control(['fecha_hora' => now()->subDays(2), 'repite_preguntas' => true]);
        $this->control(['fecha_hora' => now()->subDay(), 'olvida_indicaciones' => true, 'repite_preguntas' => false]);
        $resumen = $this->resumen();
        $this->assertCount(2, $resumen['registros']);
        $this->assertSame('16/10/2026 12:00', $resumen['principal']['fecha']);
        $this->assertSame('Se documentó olvido de indicaciones en un control cognitivo.', $resumen['observacion']);
        $this->assertSame([], $resumen['contradicciones']);
        $this->assertSame('Sí', $resumen['registros'][1]['campos']['Repite preguntas']);
    }

    public function test_no_valorable_y_vacios_son_informacion_insuficiente(): void
    {
        $this->control(['memoria_reciente' => 'NO_VALORABLE', 'cambio_cognitivo' => null]);
        $resumen = $this->resumen();
        $this->assertSame('Información insuficiente', $resumen['estado']);
        $this->assertNull($resumen['experto']);
        $this->exportar('insuficiente', $resumen);
    }

    public function test_conservada_y_false_solo_son_observacion_documentada(): void
    {
        $this->control(['memoria_reciente' => 'CONSERVADA', 'repite_preguntas' => false, 'olvida_indicaciones' => false]);
        $resumen = $this->resumen();
        $this->assertSame('Observación documentada', $resumen['estado']);
        $this->assertSame('Conservada', $resumen['principal']['campos']['Memoria reciente']);
        $this->assertSame('No', $resumen['principal']['campos']['Repite preguntas']);
        $this->assertNull($resumen['experto']);
        $this->exportar('documentada', $resumen);
    }

    public function test_respuestas_opuestas_del_mismo_campo_y_momento_se_exponen_como_contradiccion(): void
    {
        $this->control(['repite_preguntas' => true]);
        $this->control(['repite_preguntas' => false]);
        $resumen = $this->resumen();
        $this->assertSame('Contradicción', $resumen['estado']);
        $this->assertSame(['Repite preguntas · 17/10/2026 11:00'], $resumen['contradicciones']);
        $this->assertCount(2, $resumen['registros']);
        $this->assertNull($resumen['experto']);
        $this->exportar('contradiccion', $resumen);
    }

    public function test_descripciones_distintas_no_son_reglas_de_contradiccion_clinica(): void
    {
        $this->control(['memoria_reciente' => 'CONSERVADA', 'repite_preguntas' => true]);
        $resumen = $this->resumen();
        $this->assertSame('Señal preliminar', $resumen['estado']);
        $this->assertSame([], $resumen['contradicciones']);
        $this->assertSame('Conservada', $resumen['principal']['campos']['Memoria reciente']);
    }

    public function test_valor_desconocido_no_inventa_etiqueta_ni_normalidad(): void
    {
        $this->control(['memoria_reciente' => 'COD_DESCONOCIDO']);
        $resumen = $this->resumen();
        $this->assertSame('Información insuficiente', $resumen['estado']);
        $this->assertSame('Valor sin descripción disponible', $resumen['principal']['campos']['Memoria reciente']);
    }

    public function test_cuenta_inactiva_y_familiar_no_pueden_consultar_el_resumen(): void
    {
        $this->control(['observacion' => 'Contenido privado de prueba']);
        $this->medico->update(['estado' => 'INACTIVO']);
        $this->assertFalse(app(ConsultarResumenCognitivoResidente::class)->disponible($this->residente));
        try {
            $this->resumen();
            $this->fail('Una cuenta inactiva no recibe el resumen.');
        } catch (AuthorizationException $e) {
            $this->assertTrue($e->hasStatus());
        }
        $familiar = User::factory()->create(['estado' => 'ACTIVO']);
        $familiar->assignRole('FAMILIAR');
        $this->actingAs($familiar);
        $this->expectException(AuthorizationException::class);
        $this->resumen();
    }

    public function test_texto_libre_se_conserva_sin_extraer_inferencias_y_se_escapa(): void
    {
        $this->control(['observacion' => '<script>dato_sintetico</script> Repite preguntas según nota.']);
        $resumen = $this->resumen();
        $this->assertSame('Observación documentada', $resumen['estado']);
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])
            ->assertSeeHtml('&lt;script&gt;dato_sintetico&lt;/script&gt;')->assertDontSeeHtml('<script>dato_sintetico</script>');
    }

    public function test_periodo_excluye_anulados_futuros_ajenos_y_fuera_de_ventana(): void
    {
        foreach ([['estado' => 'ANULADO'], ['fecha_hora' => now()->addMinute()],
            ['fecha_hora' => now()->subDays(31)], ['cod_residente' => Residente::factory()->create()->getKey()]] as $datos) {
            $this->control($datos + ['repite_preguntas' => true]);
        }
        $this->assertSame([], $this->resumen()['registros']);
        $this->control(['fecha_hora' => now()->subDays(20), 'repite_preguntas' => true]);
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])
            ->assertSee('Se documentó repetición de preguntas en un control cognitivo.')
            ->call('setPeriodoEstado', '7d')->assertSee('Sin observaciones registradas')
            ->call('setPeriodoEstado', '90d')->assertSee('Señal preliminar')
            ->call('setPeriodoEstado', 'todos')->assertHasErrors('periodoEstado');
    }

    public function test_residente_sin_permiso_no_entrega_datos_por_consulta_directa_ni_por_ficha(): void
    {
        $this->control(['observacion' => 'Contenido cognitivo restringido']);
        Role::findByName('MEDICO GENERAL/GERIATRA')->revokePermissionTo('controles_cognitivos.ver');
        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])
            ->assertSee('No dispone de autorización')->assertDontSee('Contenido cognitivo restringido');
        $this->exportar('restringido', null);
        $this->expectException(AuthorizationException::class);
        $this->resumen();
    }

    public function test_reautoriza_permiso_revocado_despues_de_abrir_ficha(): void
    {
        $this->control(['observacion' => 'Dato cognitivo privado']);
        $ficha = Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->getKey()])->assertSee('Dato cognitivo privado');
        Role::findByName('MEDICO GENERAL/GERIATRA')->revokePermissionTo('controles_cognitivos.ver');
        $ficha->call('setPeriodoEstado', '7d')->assertDontSee('Dato cognitivo privado')->assertDontSee('Análisis cognitivo');
    }

    public function test_enfermeria_solo_consulta_residentes_asignados_y_revoca_al_cerrar_jornada(): void
    {
        $enfermera = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermera->assignRole('ENFERMEROS');
        $personal = Personal::create(['cod_usuario' => $enfermera->getKey(), 'nombres' => 'Enfermera',
            'apellido_paterno' => 'Sintética', 'numero_documento' => 'COG-ENF', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '07:00', 'hora_cierre' => '19:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionResidenteJornada::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $personal->getKey(),
            'cod_jornada' => $jornada->getKey(), 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $this->control(['repite_preguntas' => true]);
        $this->actingAs($enfermera);
        $this->assertSame('Señal preliminar', $this->resumen()['estado']);
        $this->assertStringContainsString('/enfermeria/pacientes/', $this->resumen()['analisis_url']);
        $otro = Residente::factory()->create();
        try {
            $this->resumen(residente: $otro);
            $this->fail('Enfermería no puede consultar al residente ajeno.');
        } catch (AuthorizationException $e) {
            $this->assertTrue($e->hasStatus());
        }
        $jornada->update(['estado' => 'CERRADA']);
        $this->expectException(AuthorizationException::class);
        $this->resumen();
    }

    /** Conserva una traza completamente artificial para probar la lectura, sin ejecutar motor alguno. */
    private function evaluacionConservada(): void
    {
        $this->insertar('versiones_modelo_experto', ['cod_version_modelo' => 'VER_TEST', 'codigo_version' => 'INFORME-LECTURA-02',
            'estado' => 'INACTIVO', 'cod_usuario_creacion' => $this->medico->getKey()]);
        foreach (EjemploTecnicoCOGMEM::tablas() as $tabla => $filas) {
            foreach ($filas as $fila) {
                if (isset($fila['cod_usuario_creacion'])) {
                    $fila['cod_usuario_creacion'] = $this->medico->getKey();
                }
                if ($tabla === 'dominios_valores_expertos') {
                    $fila['codigo_dominio'] = $fila['cod_dominio_valores'] === 'DOM_RES' ? 'DOM-RESULTADO-COG-MEM' : 'DOM-ARTIFICIAL';
                }
                $this->insertar($tabla, $fila);
            }
        }
        $this->insertar('evaluaciones_expertas', ['cod_evaluacion_experta' => 'EVAL_F3', 'cod_residente' => $this->residente->getKey(),
            'cod_version_modelo' => 'VER_TEST', 'cod_personal_solicitante' => $this->personal->getKey(), 'origen_activacion' => 'MANUAL', 'estado_ejecucion' => 'FINALIZADA']);
        $this->insertar('evaluacion_criterios', ['cod_evaluacion_criterio' => 'EC_F3', 'cod_evaluacion_experta' => 'EVAL_F3',
            'cod_nodo_criterio' => 'MEM', 'estado_evaluabilidad' => 'EV-CM-2', 'tipo_registro' => 'D137']);
        $this->insertar('resultados_criterio', ['cod_resultado_criterio' => 'RC_F3', 'cod_evaluacion_criterio' => 'EC_F3', 'cod_valor_semantico' => 'RES_D']);
        $this->insertar('trazas_inferencia', ['cod_traza_inferencia' => 'TR_F3', 'cod_evaluacion_criterio' => 'EC_F3', 'estado_inferencia' => 'FINALIZADA']);
        $this->insertar('evidencias_evaluacion', ['cod_evidencia_evaluacion' => 'EV_F3', 'cod_evaluacion_experta' => 'EVAL_F3',
            'cod_mapeo_variable_fuente' => 'MAP_INS', 'cod_registro_fuente' => 'APP_SINTETICA', 'estado_representacion' => 'MAPEADO',
            'cod_valor_semantico' => 'VAL_A', 'estado_admisibilidad' => 'ADMISIBLE']);
        $this->insertar('evidencias_criterio_evaluacion', ['cod_evidencia_criterio' => 'EP_F3', 'cod_evaluacion_criterio' => 'EC_F3',
            'cod_evidencia_evaluacion' => 'EV_F3', 'rol_en_criterio' => 'INSTRUMENTAL', 'estado_participacion' => 'UTILIZADA',
            'fecha_hora_vinculacion' => now()->subDay()->format('Y-m-d H:i:s')]);
        $this->insertar('evaluaciones_reglas', ['cod_evaluacion_regla' => 'ER_F3', 'cod_traza_inferencia' => 'TR_F3', 'cod_regla_experta' => 'RULE_A', 'estado_regla' => 'CUMPLE']);
        $this->insertar('evaluaciones_condiciones_regla', ['cod_evaluacion_condicion' => 'CV_F3', 'cod_evaluacion_regla' => 'ER_F3', 'cod_condicion_regla' => 'COND_A', 'estado_condicion' => 'CUMPLE']);
        $this->insertar('evidencias_soporte_condicion', ['cod_evidencia_soporte_condicion' => 'SUP_F3', 'cod_evaluacion_condicion' => 'CV_F3', 'cod_evidencia_evaluacion' => 'EV_F3']);
        config(['sistema_experto.lectura_clinica' => ['VER_TEST' => ['criterios' => ['COG-MEM'], 'estados_ejecucion' => ['FINALIZADA'],
            'componentes_memoria' => ['MAP_INS'], 'estados_inferencia' => ['FINALIZADA'], 'roles_soporte' => ['INSTRUMENTAL'],
            'estados_participacion_soporte' => ['UTILIZADA']]]]);
    }

    /** Completa solo metadatos obligatorios de la fixture en la base aislada, nunca datos de producto. */
    private function insertar(string $tabla, array $fila): void
    {
        foreach (Schema::getColumns($tabla) as $columna) {
            $nombre = $columna['name'];
            if (array_key_exists($nombre, $fila) || $columna['default'] !== null) {
                continue;
            }
            $fila[$nombre] = $columna['nullable'] ? null : ($nombre === 'cod_usuario_creacion' ? $this->medico->getKey()
                : (str_contains($columna['type_name'], 'time') || str_contains($columna['type_name'], 'date') ? now()->subDay()->format('Y-m-d H:i:s') : 'F3_'.reset($fila)));
        }
        DB::table($tabla)->insert($fila);
    }

    public function test_hallazgo_sustentado_reutiliza_integridad_y_lenguaje_del_lector_sin_mutaciones(): void
    {
        $this->evaluacionConservada();
        $antes = DB::table('evaluaciones_expertas')->get()->toJson();
        $resumen = $this->resumen();
        $this->assertSame('Hallazgo sustentado', $resumen['experto']['estado']);
        $this->assertSame('Dificultad evidenciada', $resumen['experto']['observacion']);
        $this->assertStringContainsString('no establece por sí sola un diagnóstico clínico', $resumen['experto']['alcance']);
        $this->assertSame($antes, DB::table('evaluaciones_expertas')->get()->toJson());
        $this->assertDatabaseCount('diagnosticos', 0);
        $this->assertDatabaseCount('alertas', 0);
        $this->exportar('sustentado', $resumen);
    }

    public function test_ausencia_de_evidencia_instrumental_suprime_resultado_aunque_exista_valor(): void
    {
        $this->evaluacionConservada();
        DB::table('evidencias_criterio_evaluacion')->update(['rol_en_criterio' => 'CONTEXTUAL']);
        $resumen = $this->resumen();
        $this->assertSame('Información insuficiente', $resumen['experto']['estado']);
        $this->assertStringContainsString('requiere revisar su integridad', $resumen['experto']['alcance']);
        $this->assertNotSame('Dificultad evidenciada', $resumen['experto']['observacion']);
    }

    public function test_contrato_vacio_y_pruebas_tecnicas_nunca_publican_hallazgos(): void
    {
        $this->evaluacionConservada();
        config(['sistema_experto.lectura_clinica' => []]);
        $this->assertNull($this->resumen()['experto']);
        config(['sistema_experto.lectura_clinica' => ['VER_TEST' => ['criterios' => ['COG-MEM']]]]);
        DB::table('evaluaciones_expertas')->update(['origen_activacion' => 'PRUEBA_TECNICA', 'estado_ejecucion' => 'PRUEBA_FINALIZADA']);
        $this->assertNull($this->resumen()['experto']);
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
    }
}
