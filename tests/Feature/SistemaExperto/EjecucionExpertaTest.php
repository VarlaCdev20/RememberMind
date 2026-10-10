<?php

namespace Tests\Feature\SistemaExperto;

use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Backend\Modulos\SistemaExperto\Acciones\PersistirEvaluacionTecnica;
use App\Backend\Modulos\SistemaExperto\Adaptadores\AdaptadorFuentesCOGMEM;
use App\Backend\Modulos\SistemaExperto\Conocimiento\CargadorConocimiento;
use App\Backend\Modulos\SistemaExperto\Conocimiento\EjemploTecnicoCOGMEM as F;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\DTO\ContextoEjecucion;
use App\Backend\Modulos\SistemaExperto\DTO\EvidenciaExperta;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use App\Models\AplicacionInstrumento;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\ResultadoCriterio;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\SistemaExperto\PruebaConBaseDesechable;

class EjecucionExpertaTest extends PruebaConBaseDesechable
{
    private array $componentesInstrumentales = [];

    private function escenario(): array
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $autor = User::create(['cod_usuario' => 'USR_TEST', 'correo' => 'prueba-experto@example.test', 'contrasena' => Hash::make(bin2hex(random_bytes(20))), 'estado' => 'ACTIVO']);
        $autor->assignRole('ADMINISTRADOR');
        Personal::create(['cod_personal' => 'PER_TEST', 'cod_usuario' => 'USR_TEST', 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético', 'numero_documento' => 'TEST-PER', 'profesion' => 'PRUEBA_TECNICA', 'estado' => 'ACTIVO']);
        Habitacion::create(['cod_habitacion' => 'HAB_TEST', 'codigo' => 'TEST', 'capacidad' => 1, 'estado' => 'ACTIVA']);
        Cama::create(['cod_cama' => 'CAM_TEST', 'cod_habitacion' => 'HAB_TEST', 'codigo' => 'TEST', 'estado' => 'ACTIVA']);
        $pre = Preadmision::create(['cod_preadmision' => 'PRE_TEST', 'cod_usuario_registro' => 'USR_TEST', 'nombres' => 'Residente', 'apellido_paterno' => 'Sintético',
            'fecha_nacimiento' => '1940-02-03', 'motivo_ingreso' => 'Prueba de integración experta', 'fecha_solicitud' => '2026-10-08 09:00:00', 'estado' => 'APROBADA']);
        $this->travelTo(now()->setDate(2026, 10, 8)->setTime(10, 0));
        $residente = (new FormalizarAdmision)->ejecutar($pre, ['cod_cama' => 'CAM_TEST', 'contacto' => ['nombres' => 'Contacto', 'apellido_paterno' => 'Sintético']], $autor);
        $this->travelBack();
        DB::table('versiones_modelo_experto')->insert(['cod_version_modelo' => 'VER_TEST', 'codigo_version' => 'TEST_1', 'nombre' => 'Paquete artificial', 'estado' => 'INACTIVO',
            'fecha_hora_creacion' => '2026-10-08 10:00:00', 'cod_usuario_creacion' => 'USR_TEST', 'observacion' => 'Fixture exclusivo de BDD desechable.']);
        $contrato = json_decode(file_get_contents(base_path('tests/Fixtures/SistemaExperto/contrato-d137.json')), true, 512, JSON_THROW_ON_ERROR);
        $tablas = F::tablas();
        foreach ($contrato['tables'] as $t) {
            if (! isset(PaqueteConocimiento::CLAVES[$t['name']])) {
                continue;
            }
            foreach ($tablas[$t['name']] ?? [] as $fila) {
                foreach ($t['columns'] as $col) {
                    if (array_key_exists($col['name'], $fila)) {
                        continue;
                    }
                    if ($col['nullable']) {
                        $fila[$col['name']] = null;
                    } elseif ($col['name'] === 'cod_usuario_creacion') {
                        $fila[$col['name']] = 'USR_TEST';
                    } elseif ($col['type'] === 'dateTime') {
                        $fila[$col['name']] = '2026-10-08 12:00:00';
                    } else {
                        $fila[$col['name']] = 'TEST_'.$fila[PaqueteConocimiento::CLAVES[$t['name']]];
                    }
                }
                DB::table($t['name'])->insert($fila);
            }
        }
        DB::table('instrumentos')->insert(['cod_instrumento' => 'INST_TEST', 'codigo' => 'TEST', 'nombre' => 'Instrumento artificial sin contenido clínico',
            'tipo' => 'PRUEBA_TECNICA', 'version' => 'TECH_1', 'estado' => 'INACTIVO']);
        DB::table('preguntas_instrumento')->insert(['cod_pregunta' => 'Q1', 'cod_instrumento' => 'INST_TEST', 'codigo' => 'Q1', 'enunciado' => 'Literal técnico',
            'tipo_respuesta' => 'TEXTO', 'orden' => 1, 'estado' => 'INACTIVO']);
        DB::table('aplicaciones_instrumento')->insert(['cod_aplicacion' => 'APP_TEST', 'cod_instrumento' => 'INST_TEST', 'cod_residente' => $residente->cod_residente,
            'cod_personal' => 'PER_TEST', 'fecha_hora' => '2026-10-08 11:00:00', 'estado' => 'COMPLETA']);
        DB::table('respuestas_instrumento')->insert(['cod_respuesta' => 'RESP_TEST', 'cod_aplicacion' => 'APP_TEST', 'cod_pregunta' => 'Q1', 'valor_texto' => 'A']);
        $fixture = F::paquete();
        $cargado = (new CargadorConocimiento)->cargar('VER_TEST', $fixture->contratosRelaciones, $fixture->contratosExtraccion);
        $datos = [];
        foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
            $datos[$tabla] = $cargado->tabla($tabla);
        }
        $paquete = new PaqueteConocimiento('VER_TEST', $datos, $fixture->contratosRelaciones, true, 'RESULTADO', $fixture->contratosExtraccion);
        $memoria = new MemoriaTrabajo(new ContextoEjecucion('EVAL_TEST', $residente->cod_residente, 'VER_TEST', F::contexto()->fechaCorte));
        $componente = ['solo_pruebas_tecnicas' => true, 'instrumento' => 'INST_TEST', 'version_instrumento' => 'TECH_1', 'componente' => 'COMP_TEST',
            'mapeo_variable' => 'MAP_INS', 'metodo' => 'TEST_EXACTO', 'preguntas_requeridas' => ['Q1' => ['campo' => 'valor_texto', 'tipo' => 'string', 'tipo_respuesta' => 'TEXTO', 'valores_permitidos' => ['A']]],
            'patrones' => [['respuestas' => ['Q1' => 'A'], 'valor_fuente' => 'literal_A']]];
        $app = AplicacionInstrumento::query()->with(['instrumento', 'evaluador', 'respuestas'])->findOrFail('APP_TEST');
        $this->componentesInstrumentales = ['MAP_INS' => $componente];
        $adaptacion = (new AdaptadorFuentesCOGMEM)->extraerAplicacion($app, $paquete, $memoria, $componente);
        $this->assertTrue($adaptacion['inspeccion']['verificado']);
        $this->assertSame('A', $adaptacion['evidencia']->fuente->valorOriginal[0]['valor_original']);
        $memoria->vincular('MEM', $adaptacion['evidencia']->id, 'INSTRUMENTAL');

        return [$paquete, $memoria, $autor];
    }

    public function test_persistencia_normalizada_y_reconstruccion_resultado_hasta_fuente(): void
    {
        [$p, $m, $autor] = $this->escenario();
        $id = (new PersistirEvaluacionTecnica)->ejecutar($p, $m, 'MEM', F::contextoClinico(), $autor, $this->componentesInstrumentales);
        $this->assertSame('EVAL_TEST', $id);
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
        $this->assertDatabaseCount('evidencias_evaluacion', 1);
        $this->assertDatabaseCount('resultados_criterio', 1);
        $this->assertDatabaseCount('trazas_inferencia', 1);
        $this->assertDatabaseCount('evaluaciones_reglas', 2);
        $this->assertDatabaseCount('evaluaciones_condiciones_regla', 2);
        $this->assertDatabaseCount('evidencias_soporte_condicion', 1);
        $resultado = ResultadoCriterio::query()->with('evaluacionCriterio.traza.evaluacionesReglas.evaluacionesCondicionesRegla.evidenciasSoporteCondicion.evidencia.mapeoVariableFuente.fuente')->firstOrFail();
        $this->assertSame('RES_D', $resultado->cod_valor_semantico);
        $this->assertSame('VER_TEST', $resultado->evaluacionCriterio->evaluacion->cod_version_modelo);
        $cumple = $resultado->evaluacionCriterio->traza->evaluacionesReglas->firstWhere('estado_regla', 'CUMPLE');
        $e = $cumple->evaluacionesCondicionesRegla->first()->evidenciasSoporteCondicion->first()->evidencia;
        $this->assertSame('APP_TEST', $e->cod_registro_fuente);
        $this->assertSame('aplicaciones_instrumento', $e->mapeoVariableFuente->fuente->tabla_raiz);
        $this->assertDatabaseHas('aplicaciones_instrumento', ['cod_aplicacion' => 'APP_TEST', 'estado' => 'COMPLETA']);
    }

    public function test_ev1_conserva_evidencias_y_traza_sin_resultado_ni_reglas(): void
    {
        [$p, $m, $autor] = $this->escenario();
        (new PersistirEvaluacionTecnica)->ejecutar($p, $m, 'MEM', [...F::contextoClinico(), 'confusores_revisados' => false], $autor, $this->componentesInstrumentales);
        $this->assertDatabaseCount('evidencias_evaluacion', 1);
        $this->assertDatabaseCount('resultados_criterio', 0);
        $this->assertDatabaseCount('evaluaciones_reglas', 0);
        $this->assertDatabaseHas('evaluacion_criterios', ['estado_evaluabilidad' => 'EV-CM-1']);
    }

    public function test_reintento_no_sobrescribe_ni_duplica_ejecucion(): void
    {
        [$p, $m, $autor] = $this->escenario();
        $accion = new PersistirEvaluacionTecnica;
        $accion->ejecutar($p, $m, 'MEM', F::contextoClinico(), $autor, $this->componentesInstrumentales);
        try {
            $accion->ejecutar($p, $m, 'MEM', F::contextoClinico(), $autor, $this->componentesInstrumentales);
            $this->fail('La ejecución histórica no debe sobrescribirse.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('ya fue conservada', $e->getMessage());
        }
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
        $this->assertDatabaseCount('evidencias_evaluacion', 1);
        $this->assertDatabaseCount('resultados_criterio', 1);
    }

    public function test_inconsistencia_de_version_persistida_no_crea_filas_parciales(): void
    {
        [$p, $m, $autor] = $this->escenario();
        DB::table('mapeos_valores_fuente')->where('cod_mapeo_valor_fuente', 'MV_INS_A')->update(['valor_fuente_exacto' => 'otro_literal']);
        try {
            (new PersistirEvaluacionTecnica)->ejecutar($p, $m, 'MEM', F::contextoClinico(), $autor, $this->componentesInstrumentales);
            $this->fail('La instantánea incompatible debe fallar.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('instantánea', $e->getMessage());
        }
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
        $this->assertDatabaseCount('evidencias_evaluacion', 0);
        $this->assertDatabaseCount('trazas_inferencia', 0);
    }

    public function test_respuesta_modificada_despues_de_extraer_rechaza_la_instantanea(): void
    {
        [$p, $m, $autor] = $this->escenario();
        DB::table('respuestas_instrumento')->where('cod_respuesta', 'RESP_TEST')->update(['valor_texto' => 'B']);
        $this->rechazarSinPersistir($p, $m, $autor);
    }

    public function test_version_instrumental_modificada_despues_de_extraer_rechaza_la_instantanea(): void
    {
        [$p, $m, $autor] = $this->escenario();
        DB::table('instrumentos')->where('cod_instrumento', 'INST_TEST')->update(['version' => 'TECH_2']);
        $this->rechazarSinPersistir($p, $m, $autor);
    }

    public function test_dto_semantico_manipulado_no_reemplaza_el_mapeo_real(): void
    {
        [$p, $m, $autor] = $this->escenario();
        $e = $m->todas()[0];
        $manipulada = new EvidenciaExperta(
            $e->id, $e->ejecucion, $e->fuente, $e->mapeoVariable, 'MV_INS_B',
            $e->variable, 'VAL_B', $e->representacion, $e->admisibilidad, $e->motivos,
        );
        $falsa = new MemoriaTrabajo($m->contexto);
        $falsa->incorporar($manipulada);
        $falsa->vincular('MEM', $manipulada->id, 'INSTRUMENTAL');
        $this->rechazarSinPersistir($p, $falsa, $autor);
    }

    public function test_fechas_tecnicas_registran_ejecucion_y_no_fecha_de_corte(): void
    {
        [$p, $m, $autor] = $this->escenario();
        $instante = now()->setDate(2026, 10, 9)->setTime(15, 30, 0);
        $this->travelTo($instante);
        try {
            (new PersistirEvaluacionTecnica)->ejecutar($p, $m, 'MEM', F::contextoClinico(), $autor, $this->componentesInstrumentales);
            $this->assertDatabaseHas('evaluaciones_expertas', ['fecha_hora_inicio' => '2026-10-09 15:30:00', 'fecha_hora_fin' => '2026-10-09 15:30:00']);
            $this->assertDatabaseHas('evidencias_evaluacion', ['fecha_hora_incorporacion' => '2026-10-09 15:30:00']);
            $this->assertDatabaseHas('trazas_inferencia', ['fecha_hora_inicio' => '2026-10-09 15:30:00']);
            $this->assertNotSame($instante->format('Y-m-d H:i:s'), $m->contexto->fechaCorte->format('Y-m-d H:i:s'));
        } finally {
            $this->travelBack();
        }
    }

    private function rechazarSinPersistir(PaqueteConocimiento $p, MemoriaTrabajo $m, User $autor): void
    {
        try {
            (new PersistirEvaluacionTecnica)->ejecutar($p, $m, 'MEM', F::contextoClinico(), $autor, $this->componentesInstrumentales);
            $this->fail('La fuente o representación incompatible debe rechazarse.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('instantánea', $e->getMessage());
        }
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
        $this->assertDatabaseCount('evidencias_evaluacion', 0);
        $this->assertDatabaseCount('resultados_criterio', 0);
        $this->assertDatabaseCount('trazas_inferencia', 0);
    }
}
