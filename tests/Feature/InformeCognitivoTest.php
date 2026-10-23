<?php

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\AsignacionResidenteJornada;
use App\Models\ControlCognitivo;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaFuenteEvidencia;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PreparacionFuentesCOGMEM;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\LenguajeResultadosExperto;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarInformeCognitivo;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\Support\InformeCognitivoFixture as F;
use Tests\TestCase;

/** Comprueba el informe real sobre filas artificiales; no ejecuta motores ni atribuye aprobación clínica. */
class InformeCognitivoTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Support\CorromperConservacionParaPrueba;

    private User $medico;

    private Personal $personal;

    private Residente $residente;

    /** Prepara el contexto autorizado y conocimiento exclusivamente sintético en la base efímera. */
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 17)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->medico = User::factory()->create(['estado' => 'ACTIVO']);
        $this->medico->assignRole('MEDICO GENERAL/GERIATRA');
        $this->personal = Personal::create(['cod_usuario' => $this->medico->getKey(), 'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético', 'numero_documento' => 'INFORME-F4', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create();
        $this->actingAs($this->medico);
        F::conocimiento($this->medico);
    }

    /** Lee mediante Application y devuelve ambos contratos para comprobar que la presentación no los modifica. */
    private function informe(?string $id = null, string $criterio = 'COG-MEM'): array
    {
        $lectura = app(LecturaResultadosExperto::class)->consultar($this->medico->fresh(), $this->residente, $id, $criterio);

        return [app(PresentarInformeCognitivo::class)->presentar($lectura, $criterio), $lectura];
    }

    /** Exporta el HTML real para QA local con datos sintéticos, sin sesión institucional. */
    private function exportar(string $estado, $componente): void
    {
        if ($directorio = getenv('RM_INFORME_COGNITIVO_QA_DIR')) {
            if (! is_dir($directorio)) {
                mkdir($directorio, 0700, true);
            }
            file_put_contents($directorio.'/'.$estado.'.html', $componente->html());
        }
    }

    /** Define los tres resultados de memoria ya reconocidos por el lector, sin añadir reglas. */
    public static function resultadosMemoria(): array
    {
        return [
            'dificultad' => ['RES_D', 'DIFICULTAD_EVIDENCIADA'],
            'sin dificultad' => ['RES_S', 'SIN_DIFICULTAD_EVIDENCIADA'],
            'mixtos' => ['RES_M', 'HALLAZGOS_MIXTOS'],
        ];
    }

    /** Los mensajes dependen de resultados autorizados, con soportes y traza originales intactos. */
    #[DataProvider('resultadosMemoria')]
    public function test_resultados_de_memoria_conservan_codigo_y_alcance(string $valor, string $codigo): void
    {
        F::evaluacion($this->residente, $this->personal, 'M', valor: $valor);
        $antes = \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('resultados_criterio')->get()->toJson();
        [$informe, $lectura] = $this->informe();
        $this->assertSame($codigo, $lectura['detalle']['resultado']['codigo']);
        $this->assertSame(LenguajeResultadosExperto::conclusionMemoria($codigo), $informe['actual']['conclusion']);
        $this->assertSame(LenguajeResultadosExperto::RESULTADOS[$codigo]['interpretacion'], $informe['actual']['significado']);
        $this->assertTrue($informe['actual']['evaluada']);
        $this->assertFalse($informe['areas']['COG-ATE']['evaluada']);
        $this->assertSame('Sin evaluación disponible', $informe['areas']['COG-ATE']['estado']);
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Evaluación cognitiva y funcional')->assertSee('Ver evidencias')->assertSee('Ver historial')->assertSee('Resultado principal')->assertSee('Valoración de las funciones cognitivas')
            ->assertSee('Disponibilidad por dominio')->assertSee('Áreas cognitivas')
            ->assertSee('Cómo llega la información a la evaluación')
            ->assertSee('Hechos observados')
            ->assertSee('0 aplicaciones registradas')
            ->assertSee('Aún no hay una aplicación instrumental cognitiva en esta consulta')
            ->assertSee('Ver hallazgos y reglas')
            ->assertSee('Ver aplicaciones y respuestas')
            ->assertSee('Ver desglose cognitivo')
            ->assertSee('Autonomía y contexto funcional')
            ->assertSee('Ver inventario por instrumento y edición')
            ->assertSee('Resumen de fuentes registradas por instrumento y edición')
            ->assertSee('Cambios en mediciones comparables')
            ->assertSee('¿Qué significa el resultado?')->assertSee('¿Por qué llegamos a este resultado?')->assertSee('Historial de valoraciones')
            ->assertSee(LenguajeResultadosExperto::conclusionMemoria($codigo))
            ->assertSee('¿Cómo se obtuvo este resultado?')
            ->assertDontSee('¿Cómo razonó O.R.I.O.N.?')->assertDontSee('Fecha y versión')
            ->assertDontSee('Nueva evaluación')->assertDontSee('Exportar reporte')->assertDontSee('Confirmar concordancia')
            ->call('irASeccion', 'fuentes-disponibles')
            ->assertDispatched('orion-navigate', seccion: 'fuentes-disponibles')
            ->call('irASeccion', 'hallazgos-observacionales')
            ->assertDispatched('orion-navigate', seccion: 'hallazgos-observacionales')
            ->call('irASeccion', 'aplicaciones-instrumentales')
            ->assertDispatched('orion-navigate', seccion: 'aplicaciones-instrumentales')
            ->call('irASeccion', 'orion-desglose')
            ->assertDispatched('orion-navigate', seccion: 'orion-desglose');
        $this->assertSame($antes, \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('resultados_criterio')->get()->toJson());
        $this->assertDatabaseCount('diagnosticos', 0);
        $this->assertDatabaseCount('alertas', 0);
        $this->exportar($codigo, $componente);
    }

    public function test_informe_extenso_se_abre_y_cierra_sin_modificar_el_resultado(): void
    {
        F::evaluacion($this->residente, $this->personal, 'INFORME');
        $antes = \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('resultados_criterio')->get()->toJson();

        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Lectura clínica y fundamentos')
            ->assertSee('Leer informe completo')
            ->assertSet('informeCompletoAbierto', false)
            ->call('abrirInformeCompleto')
            ->assertSet('informeCompletoAbierto', true)
            ->assertSee('Informe clínico completo')
            ->assertSee('¿Por qué llegamos a este resultado?')
            ->call('cerrarInformeCompleto')
            ->assertSet('informeCompletoAbierto', false);

        $this->assertSame($antes, \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('resultados_criterio')->get()->toJson());
    }

    /** No se transforma una observación operativa en evaluación integrada. */
    public function test_sin_evaluacion_conserva_observaciones_generales_y_cinco_areas(): void
    {
        ControlCognitivo::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'fecha_hora' => now()->subDay(), 'estado' => 'VIGENTE', 'repite_preguntas' => true]);
        [$informe] = $this->informe();
        $this->assertCount(5, $informe['areas']);
        $this->assertSame('Observaciones generales disponibles', $informe['actual']['estado']);
        $this->assertFalse($informe['actual']['evaluada']);
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('La información disponible todavía no permite establecer una interpretación cognitiva integrada.')
            ->call('seleccionarCriterio', 'COG-ATE')->assertSet('criterio', 'COG-ATE')->assertSee('Atención');
        $this->exportar('SIN_EVALUACION', $componente);
    }

    /** Expone insuficiencia y preliminar como estados distintos, sin conclusión favorable por defecto. */
    public function test_evaluabilidad_insuficiente_y_preliminar_no_generan_resultado(): void
    {
        foreach (['I' => 'EV-CM-0', 'P' => 'EV-CM-1'] as $id => $ev) {
            F::evaluacion($this->residente, $this->personal, $id, $ev, null);
            [$informe, $lectura] = $this->informe('E_'.$id);
            $this->assertNull($lectura['detalle']['resultado']);
            $this->assertFalse($informe['actual']['evaluada']);
            $this->assertSame($id === 'I' ? 'Información insuficiente' : 'Información preliminar', $informe['actual']['estado']);
            $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->call('seleccionarEvaluacion', 'E_'.$id)
                ->assertSee($informe['actual']['conclusion']);
            $this->exportar($id === 'I' ? 'INSUFICIENTE' : 'PRELIMINAR', $componente);
        }
    }

    /** Una cabecera sin contrato nunca publica el valor, aunque su traza parezca completa. */
    public function test_publicacion_no_habilitada_no_muestra_conclusion_clinica(): void
    {
        F::evaluacion($this->residente, $this->personal, 'B');
        config(['sistema_experto.lectura_clinica' => []]);
        [$informe] = $this->informe();
        $this->assertSame('Publicación clínica no habilitada', $informe['actual']['estado']);
        $this->assertFalse($informe['actual']['evaluada']);
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertDontSee(LenguajeResultadosExperto::conclusionMemoria('DIFICULTAD_EVIDENCIADA'));
        $this->exportar('BLOQUEADA', $componente);
    }

    /** Manipulaciones sintéticas de integridad, independientes y reproducibles. */
    public static function incompatibilidades(): array
    {
        return [['contextual'], ['valor ajeno'], ['sin resultado'], ['sin traza'], ['version incompatible']];
    }

    /** Cada incompatibilidad suprime el hallazgo en Application y en el render de Blade. */
    #[DataProvider('incompatibilidades')]
    public function test_integridad_impide_publicar_hallazgos(string $caso): void
    {
        F::evaluacion($this->residente, $this->personal, 'X');
        match ($caso) {
            'contextual' => DB::table('evidencias_criterio_evaluacion')->update(['rol_en_criterio' => 'CONTEXTUAL']),
            'valor ajeno' => $this->corromperConservacion(fn () => DB::table('evaluacion_criterios')->whereNotNull('cod_resultado_criterio')->update(['resultado_cod_valor_semantico' => 'VAL_A'])),
            'sin resultado' => DB::table('evaluacion_criterios')->update(['estado_evaluabilidad' => 'EV-CM-0']),
            'sin traza' => $this->corromperConservacion(fn () => DB::table('evaluacion_criterios')->whereNotNull('cod_traza_inferencia')->update(['traza_estado_inferencia' => 'INCOMPATIBLE'])),
            'version incompatible' => config(['sistema_experto.lectura_clinica.VER_TEST.estados_inferencia' => []]),
        };
        [$informe, $lectura] = $this->informe();
        $this->assertNotEmpty($lectura['detalle']['integridad']);
        $this->assertNull($lectura['detalle']['resultado']);
        $this->assertSame('Integridad pendiente', $informe['actual']['estado']);
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertDontSee(LenguajeResultadosExperto::conclusionMemoria('DIFICULTAD_EVIDENCIADA'));
        $this->exportar('INTEGRIDAD', $componente);
    }

    /** Historial y selección consultan evaluaciones exactas sin aplicar una versión posterior al resultado anterior. */
    public function test_historial_seleccion_y_fuente_conservan_contexto_y_no_escriben(): void
    {
        F::evaluacion($this->residente, $this->personal, 'A', valor: 'RES_D');
        F::evaluacion($this->residente, $this->personal, 'B', valor: 'RES_S');
        $instrumento = Instrumento::create(['codigo' => 'F4_FUENTE', 'nombre' => 'Instrumento de consulta',
            'tipo' => 'COGNITIVO', 'version' => '1', 'estado' => 'ACTIVO']);
        AplicacionInstrumento::create(['cod_aplicacion' => 'APP_A_A', 'cod_instrumento' => $instrumento->getKey(),
            'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(), 'fecha_hora' => now()->subDay(), 'estado' => 'COMPLETA']);
        $antes = DB::table('evidencias_evaluacion')->get()->toJson();
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('seleccionarEvaluacion', 'E_A')->assertSet('evaluacion', 'E_A')
            ->call('verEvidencia', 'EV_A_A')->assertSet('evidencia', 'EV_A_A')
            ->assertSee('Instrumento de consulta')->assertSee('Procedencia actual de la aplicación.')
            ->call('seleccionarEvaluacion', 'E_B')->assertSee(LenguajeResultadosExperto::conclusionMemoria('SIN_DIFICULTAD_EVIDENCIADA'))
            ->call('seleccionarCriterio', 'COG-LEN')->assertSet('criterio', 'COG-LEN');
        $this->assertSame($antes, DB::table('evidencias_evaluacion')->get()->toJson());
        $this->exportar('HISTORIAL', $componente);
    }

    /** No se aceptan IDs de evaluaciones o evidencias de otro residente. */
    public function test_idor_evaluacion_y_evidencia_se_rechazan(): void
    {
        F::evaluacion(Residente::factory()->create(), $this->personal, 'AJENA');
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('seleccionarEvaluacion', 'E_AJENA')->assertNotFound();
        F::evaluacion($this->residente, $this->personal, 'PROPIA');
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('verEvidencia', 'EV_AJENA_A')->assertNotFound();
    }

    /** La cuenta, permiso y residente se reautorizan después de montar la pantalla. */
    public function test_revocacion_permiso_y_cuenta_inactiva_no_filtran_resultados(): void
    {
        F::evaluacion($this->residente, $this->personal, 'P');
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        Role::findByName('MEDICO GENERAL/GERIATRA')->revokePermissionTo('controles_cognitivos.ver');
        $componente->call('seleccionarCriterio', 'COG-ATE')->assertNotFound();
        Role::findByName('MEDICO GENERAL/GERIATRA')->givePermissionTo('controles_cognitivos.ver');
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);
        $this->medico->update(['estado' => 'INACTIVO']);
        $componente->call('verTrazabilidad')->assertNotFound();
    }

    /** Las rutas reales continúan exigiendo permisos y autenticación para el recurso. */
    public function test_ruta_medica_y_enfermeria_sin_asignacion_y_familiar_denegados(): void
    {
        $ruta = route('admin.medico.residente.resultados-experto', ['residente' => $this->residente->getKey()]);
        $this->get($ruta)->assertOk()->assertSee('Evaluación cognitiva y funcional');
        foreach (['ENFERMEROS', 'FAMILIAR'] as $rol) {
            $usuario = User::factory()->create(['estado' => 'ACTIVO']);
            $usuario->assignRole($rol);
            $this->actingAs($usuario);
            Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertNotFound();
        }
    }

    /** TEST_ONLY y G09 experimental no aparecen en el historial de una consulta asistencial. */
    public function test_ejecucion_experimental_no_se_publica_en_el_historial_clinico(): void
    {
        F::evaluacion($this->residente, $this->personal, 'EXP');
        DB::table('evaluaciones_expertas')->update(['origen_activacion' => 'TEST_ONLY']);
        [$informe, $lectura] = $this->informe();
        $this->assertNull($lectura['evaluacion']);
        $this->assertSame([], $informe['historial']);
        $this->assertFalse($informe['actual']['evaluada']);
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
    }

    /** La consulta propia fuera de turno conserva lectura; revocar la asignación retira el acceso. */
    public function test_enfermeria_asignada_consulta_informe_y_pierde_acceso_al_revocar_asignacion(): void
    {
        $enfermera = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermera->assignRole('ENFERMEROS');
        $personal = Personal::create(['cod_usuario' => $enfermera->getKey(), 'nombres' => 'Enfermera',
            'apellido_paterno' => 'Sintética', 'numero_documento' => 'INFORME-ENF', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '07:00', 'hora_cierre' => '19:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionResidenteJornada::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $personal->getKey(),
            'cod_jornada' => $jornada->getKey(), 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        F::evaluacion($this->residente, $this->personal, 'ENF');
        ControlCognitivo::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $personal->getKey(),
            'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE', 'repite_preguntas' => true]);
        $this->actingAs($enfermera);
        // La cabecera compartida conserva el contexto de navegación sin la franja retirada.
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Evaluación cognitiva y cuidados')->assertDontSee('Consulta de Enfermería')
            ->assertSee('Volver a Mis residentes')
<<<<<<< HEAD
            ->assertSee('Consulta del residente asignado')
            ->assertSee('Últimos registros cognitivos')
            ->assertSee('Continuidad de cuidados')
            ->assertSee('Hallazgos del seguimiento cognitivo')
            ->assertSee('Repetición de preguntas')
            ->assertSee('Se documentó un episodio de repetición de preguntas.')
            ->assertSee('Consulta actual de 1 de 1 control')
            ->assertSee('Áreas cognitivas, resultado y fundamento disponibles')
            ->assertSee('expert-nursing-today', false)
            ->assertSee('Consultar fuentes y fundamento')
=======
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            ->assertSee('Cobertura del resultado publicado')
            ->assertSee('Sin resultado publicado')
            ->assertDontSee('Versión utilizada')
            ->assertSet('enfermeria', true);
        $ruta = route('admin.enfermeria.pacientes.resultados-experto', ['residente' => $this->residente->getKey()]);
        $this->get($ruta)->assertOk()->assertSee('Evaluación cognitiva y cuidados');
        $this->exportar('ENFERMERIA', $componente);
        $jornada->update(['estado' => 'CERRADA']);
        $componente->call('seleccionarCriterio', 'COG-ATE')->assertOk()->assertSet('criterio', 'COG-ATE');
        AsignacionResidenteJornada::query()->where('cod_residente', $this->residente->getKey())
            ->where('cod_personal', $personal->getKey())->update(['estado' => 'INACTIVA']);
        $componente->call('seleccionarCriterio', 'COG-ATE')->assertNotFound();
    }

    /** Un resultado técnico G09 no se promueve a hallazgo clínico aunque se manipule el DTO de entrada. */
    public function test_presentador_no_traduce_g09_como_memoria_clinica(): void
    {
        F::evaluacion($this->residente, $this->personal, 'G');
        [, $lectura] = $this->informe();
        $lectura['perfil']['COG-MEM']['resultado']['codigo'] = 'HALLAZGO_ACOTADO';
        $original = $lectura;
        $informe = app(PresentarInformeCognitivo::class)->presentar($lectura, 'COG-MEM');
        $this->assertFalse($informe['actual']['evaluada']);
        $this->assertSame('No hay un resultado verificable disponible para esta área.', $informe['actual']['conclusion']);
        $this->assertSame($original, $lectura);
    }

    /** La habilitación del laboratorio no incorpora resultados de prueba en la pantalla operativa. */
    public function test_lectura_tecnica_no_se_incluye_en_la_valoracion_operativa(): void
    {
        F::evaluacion($this->residente, $this->personal, 'T');
        DB::table('evaluaciones_expertas')->update(['origen_activacion' => 'PRUEBA_TECNICA', 'estado_ejecucion' => 'PRUEBA_FINALIZADA']);
        $this->corromperConservacion(fn () => DB::table('evaluacion_criterios')->whereNotNull('cod_traza_inferencia')->update(['traza_estado_inferencia' => 'PRUEBA_FINALIZADA']));
        DB::table('evidencias_criterio_evaluacion')->update(['estado_participacion' => 'PRUEBA_TECNICA']);
        config(['sistema_experto.lectura_tecnica_pruebas.habilitada' => true]);
        [$informe, $lectura] = $this->informe();
        $this->assertFalse($informe['actual']['evaluada']);
        $this->assertNull($lectura['evaluacion']);
        $this->assertSame([], $informe['historial']);
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Evaluación cognitiva y funcional')
            ->assertDontSee('resultados artificiales')->assertDontSee('Prueba técnica')
            ->assertDontSee(LenguajeResultadosExperto::conclusionMemoria('DIFICULTAD_EVIDENCIADA'));
        $this->exportar('TECNICA', $componente);
    }

    /** Mantiene preview excluido y no permite inferir acceso por el rol visualizado. */
    public function test_previsualizacion_de_rol_no_autoriza_el_informe(): void
    {
        session(['preview_role' => 'MEDICO GENERAL/GERIATRA']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->assertNotFound();
    }

    /** Simula un fallo SQL en el límite de lectura y comprueba recuperación sin exponer el diagnóstico técnico. */
    public function test_error_de_lectura_es_seguro_y_reintentable(): void
    {
        $fallar = true;
        DB::connection()->beforeExecuting(static function (string $consulta, array $bindings, $conexion) use (&$fallar): void {
            if ($fallar && str_contains($consulta, 'evaluaciones_expertas') && in_array('D137', $bindings, true)) {
                $fallar = false;
                throw new QueryException($conexion->getName(), $consulta, $bindings, new \RuntimeException('Detalle SQL privado de prueba'));
            }
        });
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('No se pudo consultar la evaluación')->assertSee('Reintentar')->assertDontSee('Detalle SQL privado de prueba');
        $this->exportar('ERROR', $componente);
        $componente->call('reintentar')->assertSee('Resultado principal')->assertDontSee('No se pudo consultar la evaluación');
    }

    /** Marcadores independientes de laboratorio, aun si se habilitan flags y un contrato, no llegan a la UI. */
    #[DataProvider('marcadoresLaboratorio')]
    public function test_marcadores_de_prueba_excluyen_evaluacion_y_seleccion_manual(string $tabla, string $campo, string $valor): void
    {
        F::evaluacion($this->residente, $this->personal, 'LAB');
        DB::table($tabla)->update([$campo => $valor]);
        config(['sistema_experto.simulacion_local.habilitada' => true, 'sistema_experto.lectura_tecnica_pruebas.habilitada' => true]);
        [, $lectura] = $this->informe();
        $this->assertNull($lectura['evaluacion']);
        $this->assertCount(0, $lectura['historial']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Evaluación cognitiva y funcional')->assertDontSee('Simulación')->assertDontSee('simulado')
            ->call('seleccionarEvaluacion', 'E_LAB')->assertNotFound();
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
    }

    public static function marcadoresLaboratorio(): array
    {
        return [
            ['evaluaciones_expertas', 'origen_activacion', 'SIMULACION_LOCAL'],
            ['evaluaciones_expertas', 'estado_ejecucion', 'PRUEBA_FINALIZADA'],
            ['evaluaciones_expertas', 'id_evaluacion_dominio', 'sim-cas-10'],
            ['versiones_modelo_experto', 'codigo_version', 'SIM-ORION-LOCAL-1'],
            ['versiones_modelo_experto', 'codigo_version', 'ARTIFICIAL_INFORME'],
            ['versiones_modelo_experto', 'codigo_version', 'G09-EXP-20261017'],
        ];
    }

    /** El lote ficticio queda fuera de listas y cantidades; NULL conserva el registro ordinario. */
    public function test_registros_del_lote_no_aparecen_como_fuentes_operativas(): void
    {
        F::insertar('versiones_modelo_experto', ['cod_version_modelo' => 'VER_PREP', 'codigo_version' => 'MEM-INV-20261008',
            'estado' => 'INACTIVO', 'cod_usuario_creacion' => $this->medico->getKey()]);
        foreach ([null, 'Control registrado en el expediente.', '[SIM-SE-20261017-V1:caso]', '[COR-SIM-SE-20261017-V1:caso]', '[SIM-IADL-20261017:caso]', '[sim-se-20261017-v1:caso]'] as $nota) {
            ControlCognitivo::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
                'fecha_hora' => now()->subDay(), 'estado' => 'VIGENTE', 'observacion' => $nota]);
        }
        [, $lectura] = $this->informe();
        $this->assertCount(2, $lectura['registros_cognitivos']);
        $this->assertContains(null, array_column($lectura['registros_cognitivos'], 'observacion'));
        $this->assertSame(2, $lectura['preparacion_memoria']['controles']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Control registrado en el expediente.')->assertDontSee('SIM-SE')->assertDontSee('SIM-IADL');
        $this->assertDatabaseCount('controles_cognitivos', 6);
    }

    /** El instrumento reservado de QA no se abre desde una fuente normal aunque se manipulen flags. */
    public function test_instrumento_de_qa_no_se_muestra_en_fuentes_ni_cantidades(): void
    {
        F::evaluacion($this->residente, $this->personal, 'QA');
        Instrumento::create(['cod_instrumento' => 'INST_COMPLETO', 'codigo' => 'QA', 'nombre' => 'Instrumento artificial QA',
            'tipo' => 'COGNITIVO', 'version' => 'TECNICA_1', 'estado' => 'ACTIVO']);
        AplicacionInstrumento::create(['cod_aplicacion' => 'APP_QA_A', 'cod_instrumento' => 'INST_COMPLETO',
            'cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'fecha_hora' => now()->subDay(), 'estado' => 'VIGENTE', 'observacion' => null]);
        config(['sistema_experto.lectura_tecnica_pruebas.habilitada' => true]);
        $fuente = app(LecturaFuenteEvidencia::class)->consultar($this->medico, $this->residente, 'E_QA', 'EV_QA_A');
        $this->assertSame(['estado' => 'Registro fuente no disponible para este residente.'], $fuente);
        F::insertar('versiones_modelo_experto', ['cod_version_modelo' => 'VER_PREP', 'codigo_version' => 'MEM-INV-20261008',
            'estado' => 'INACTIVO', 'cod_usuario_creacion' => $this->medico->getKey()]);
        $preparacion = app(PreparacionFuentesCOGMEM::class)->consultar($this->medico, $this->residente);
        $this->assertSame(0, $preparacion['aplicaciones']);
        $this->assertSame([], $preparacion['instrumentos']);
        $this->assertDatabaseCount('aplicaciones_instrumento', 1);
    }

    /** El aislamiento conserva una valoración legítima en un historial que también contiene pruebas. */
    public function test_historial_mixto_conserva_solo_la_valoracion_operativa(): void
    {
        F::evaluacion($this->residente, $this->personal, 'OPERATIVA');
        F::evaluacion($this->residente, $this->personal, 'LAB');
        DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', 'E_LAB')->update(['origen_activacion' => 'SIMULACION_LOCAL']);
        config(['sistema_experto.simulacion_local.habilitada' => true, 'sistema_experto.lectura_tecnica_pruebas.habilitada' => true]);
        [$informe, $lectura] = $this->informe();
        $this->assertSame('E_OPERATIVA', $lectura['evaluacion']['id']);
        $this->assertTrue($informe['actual']['evaluada']);
        $this->assertSame(['E_OPERATIVA'], $lectura['historial']->pluck('id')->all());
        $this->assertDatabaseCount('evaluaciones_expertas', 2);
    }

    /** El historial conserva dos versiones sin aplicar el contrato nuevo a una evaluación incompatible. */
    public function test_version_incompatible_no_sustituye_resultado_historico_verificable(): void
    {
        F::evaluacion($this->residente, $this->personal, 'VALIDA');
        F::evaluacion($this->residente, $this->personal, 'INCOMP');
        F::insertar('versiones_modelo_experto', ['cod_version_modelo' => 'VER_OTRA', 'codigo_version' => 'LECTURA-OTRA-01',
            'estado' => 'INACTIVO', 'cod_usuario_creacion' => $this->medico->getKey()]);
        DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', 'E_INCOMP')->update(['cod_version_modelo' => 'VER_OTRA']);
        [$informe, $lectura] = $this->informe('E_INCOMP');
        $this->assertSame('LECTURA-OTRA-01', $lectura['evaluacion']['version']);
        $this->assertSame('Integridad pendiente', $informe['actual']['estado']);
        $this->assertNull($lectura['detalle']['resultado']);
        [$valida] = $this->informe('E_VALIDA');
        $this->assertTrue($valida['actual']['evaluada']);
        $this->assertCount(2, $informe['historial']);
    }
}
