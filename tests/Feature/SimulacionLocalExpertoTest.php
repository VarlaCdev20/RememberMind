<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AsignacionResidenteJornada;
use App\Models\ControlFuncionalIadl;
use App\Models\Residente;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\ContextoSimulacionLocal;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\EjecutorSimulacionLocal;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\FuenteCasoSimulado;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\LectorSimulacionLocal;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\PaqueteSimulacionLocal;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\InformeExpertoDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\ClasificarDclUseCase;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\EjecutarInferenciaUseCase;
use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\AlmacenInstantaneas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorRedSemantica;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorReglas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\RedSemanticaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\ReglaExpertaEloquentRepository;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\CohorteSimulacionLocal;
use Tests\Support\InformeCognitivoFixture;
use Tests\TestCase;

/** Integración real: fuentes, ocho reglas, cinco casos de uso, clasificación y reconstrucción SQL. */
final class SimulacionLocalExpertoTest extends TestCase
{
    use CohorteSimulacionLocal, RefreshDatabase;

    public function test_cinco_casos_reales_guardan_y_recuperan_sin_instancia_previa(): void
    {
        $datos = $this->prepararCohorte();
        $scope = new ContextoSimulacionLocal(config('database.default'));
        $cut = new DateTimeImmutable('2026-10-17T12:00:00-04:00');
        $resultado = (new EjecutorSimulacionLocal($scope))->ejecutar($datos['autor']->getKey(), $datos['personal']->getKey(), $cut);
        self::assertCount(5, $resultado);
        self::assertSame(8, DB::table('reglas_expertas')->count());
        self::assertSame(160, DB::table('controles_funcionales_iadl')->count());
        $esperados = ['CAS-01' => 'PET-01', 'CAS-02' => 'PET-02', 'CAS-08' => 'PET-02', 'CAS-10' => null, 'CAS-20' => null];
        foreach ($resultado as $r) {
            self::assertSame($esperados[$r['caso']], $r['patron']);
            $cab = DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $r['evaluacion'])->sole();
            self::assertSame($r['residente'], $cab->cod_residente);
            $almacen = new AlmacenInstantaneas($scope->conexion, null, new DateTimeZone('America/La_Paz'), $scope);
            $dcl = $almacen->obtenerClasificacion($cab->id_evaluacion_dominio, $scope->alias($r['residente']), $cab->cod_version_modelo, $cut,
                PerfilRequisitosDcl::VERSION_CONTRATO);
            self::assertSame($r['patron'], $dcl->patronCandidato?->value);
            self::assertCount(5, $dcl->perfil->criterios);
            self::assertCount(5, $dcl->perfil->cognicion->resultados);
        }
        $secuencias = DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson();
        $repeticion = (new EjecutorSimulacionLocal(new ContextoSimulacionLocal($scope->conexion)))
            ->ejecutar($datos['autor']->getKey(), $datos['personal']->getKey(), $cut);
        self::assertFalse(collect($repeticion)->contains('nueva', true));
        self::assertSame($secuencias, DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson());
        self::assertSame(5, DB::table('evaluaciones_expertas')->count());
        self::assertSame([], config('sistema_experto.lectura_clinica'));
        self::assertSame(0, DB::table('diagnosticos')->count());
        self::assertSame(0, DB::table('alertas')->count());
        $r = $datos['residentes']['CAS-08'];
        $lectura = app(LectorSimulacionLocal::class)
            ->consultar($datos['nurse'], $r, null, 'COG-MEM');
        self::assertTrue($lectura['simulacion_local']['conservacion_completa']);
        self::assertSame('PET-02', $lectura['simulacion_local']['patron']);
        $this->actingAs($datos['nurse']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $r])
            ->assertSee('Evaluación neurocognitiva')
            ->assertSee('amnésico multidominio')->assertSee('Entorno de pruebas · Resultado de demostración · Sin uso asistencial.')
            ->assertDontSee('PET-02')->assertDontSee('RAW:memoria_reciente')
            ->call('seleccionarCriterio', 'COG-ATE')->assertDontSee('SIM-ATE-01')
            ->call('verTrazabilidad')->assertNotFound();
        $operativa = app(LecturaResultadosExperto::class)
            ->consultar($datos['nurse'], $r, null, 'COG-MEM');
        self::assertNull($operativa['evaluacion']);
        self::assertCount(0, $operativa['historial']);
        self::assertSame([], $operativa['registros_cognitivos']);
        self::assertArrayNotHasKey('simulacion_local', $operativa);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $r])
            ->call('seleccionarEvaluacion', $lectura['evaluacion']['id'])->assertSee('amnésico multidominio');
    }

    public function test_lectura_no_escribe_y_conserva_null_y_false_sin_consultar_fuentes(): void
    {
        [$d, $scope, $cut] = $this->cargar();
        $r = $d['residentes']['CAS-20'];
        $consultas = [];
        DB::listen(static function ($q) use (&$consultas): void {
            $consultas[] = $q->sql;
        });
        $l = app(LectorSimulacionLocal::class)->consultar($d['nurse'], $r, null, 'COG-MEM');
        $raw = collect($l['simulacion_local']['hechos'])->keyBy('concepto');
        self::assertNull($raw['RAW:memoria_reciente']['valor']);
        self::assertNull($l['simulacion_local']['patron']);
        self::assertContains('ABSTENCION_INFORMACION_INSUFICIENTE', $l['simulacion_local']['motivos']);
        self::assertFalse(collect($consultas)->contains(fn ($q) => preg_match('/^\s*(insert|update|delete|alter|create)\b/i', $q) === 1));
        self::assertFalse(collect($consultas)->contains(fn ($q) => str_contains($q, 'from "controles_cognitivos"') || str_contains($q, 'from "controles_funcionales_iadl"')));
        $gloria = app(LectorSimulacionLocal::class)->consultar($d['nurse'], $d['residentes']['CAS-10'], null, 'COG-MEM');
        self::assertFalse(collect($gloria['simulacion_local']['hechos'])->firstWhere('concepto', 'RAW:repite_preguntas')['valor']);
    }

    public function test_snapshot_historica_no_se_recalcula_y_reintento_divergente_no_sobrescribe(): void
    {
        [$d, $scope, $cut] = $this->cargar();
        $r = $d['residentes']['CAS-08'];
        $lector = app(LectorSimulacionLocal::class);
        $original = $lector->consultar($d['nurse'], $r, null, 'COG-MEM');
        DB::table('controles_cognitivos')->where('cod_residente', $r->getKey())->update(['memoria_reciente' => null]);
        $secuencias = DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson();
        try {
            (new EjecutorSimulacionLocal($scope))->ejecutar($d['autor']->getKey(), $d['personal']->getKey(), $cut);
            self::fail('No puede sustituir una evaluación con fuentes divergentes.');
        } catch (\DomainException $e) {
            self::assertStringContainsString('No se sustituye', $e->getMessage());
        }
        $leida = $lector->consultar($d['nurse'], $r, $original['evaluacion']['id'], 'COG-MEM');
        self::assertSame($original['simulacion_local'], $leida['simulacion_local']);
        self::assertSame($secuencias, DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson());
        self::assertSame(5, DB::table('evaluaciones_expertas')->count());
    }

    public function test_rollback_integral_si_falla_la_traza_despues_de_guardar_memoria_y_dcl(): void
    {
        $d = $this->prepararCohorte();
        DB::listen(static function ($q): void {
            if (preg_match('/^insert into ["`]?trazas_inferencia/i', $q->sql)) {
                throw new \DomainException('Fallo tardío de prueba.');
            }
        });
        try {
            (new EjecutorSimulacionLocal(new ContextoSimulacionLocal(config('database.default'))))->ejecutar(
                $d['autor']->getKey(), $d['personal']->getKey(), new DateTimeImmutable('2026-10-17T12:00:00-04:00'));
            self::fail('Debía fallar después de la conservación.');
        } catch (\DomainException $e) {
            self::assertSame('Fallo tardío de prueba.', $e->getMessage());
        }
        foreach (['versiones_modelo_experto', 'reglas_expertas', 'controles_funcionales_iadl', 'evaluaciones_expertas',
            'clasificaciones_dcl_instantanea', 'hechos_instantanea_experta', 'trazas_inferencia'] as $tabla) {
            self::assertSame(0, DB::table($tabla)->count(), $tabla);
        }
    }

    public function test_no_admite_fuente_iadl_alterada_ni_rectificada(): void
    {
        [$d, $scope, $cut] = $this->cargar();
        DB::table('controles_funcionales_iadl')->where('cod_residente', $d['residentes']['CAS-01']->getKey())->update(['causa_ayuda' => 'FISICA']);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('IADL del lote alterada');
        (new EjecutorSimulacionLocal($scope))->ejecutar($d['autor']->getKey(), $d['personal']->getKey(), $cut);
    }

    public function test_admision_reciente_impide_todo_el_lote_sin_registrar_antecedentes_previos(): void
    {
        $d = $this->prepararCohorte();
        DB::table('admisiones')->where('cod_residente', $d['residentes']['CAS-01']->getKey())->update(['fecha_hora_admision' => '2026-10-01 10:00:00']);
        try {
            (new EjecutorSimulacionLocal(new ContextoSimulacionLocal(config('database.default'))))->ejecutar(
                $d['autor']->getKey(), $d['personal']->getKey(), new DateTimeImmutable('2026-10-17T12:00:00-04:00'));
            self::fail('No admite observaciones preadmisión.');
        } catch (\DomainException $e) {
            self::assertStringContainsString('admisión previa', $e->getMessage());
        }
        self::assertSame(0, DB::table('controles_funcionales_iadl')->count());
        self::assertSame(0, DB::table('versiones_modelo_experto')->count());
    }

    public function test_deshabilitada_y_produccion_permanecen_cerradas(): void
    {
        $this->prepararCohorte();
        $scope = new ContextoSimulacionLocal(config('database.default'));
        config(['sistema_experto.simulacion_local.habilitada' => false]);
        try {
            $scope->db();
            self::fail('Falta habilitación.');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('no está habilitada', $e->getMessage());
        }
        config(['sistema_experto.simulacion_local.habilitada' => true]);
        $this->app['env'] = 'production';
        try {
            $scope->db();
            self::fail('Producción cerrada.');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('no autorizado', $e->getMessage());
        } finally {
            $this->app['env'] = 'testing';
        }
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
    }

    public function test_sin_cuenta_activa_o_competencia_no_carga_nada(): void
    {
        $d = $this->prepararCohorte();
        $d['personal']->update(['profesion' => 'FISIOTERAPIA']);
        try {
            (new EjecutorSimulacionLocal(new ContextoSimulacionLocal(config('database.default'))))->ejecutar(
                $d['autor']->getKey(), $d['personal']->getKey(), new DateTimeImmutable('2026-10-17T12:00:00-04:00'));
            self::fail('Enfermería exige competencia explícita.');
        } catch (\DomainException $e) {
            self::assertStringContainsString('sin competencia', $e->getMessage());
        }
        self::assertSame(0, DB::table('versiones_modelo_experto')->count());
        self::assertSame(0, DB::table('controles_funcionales_iadl')->count());
    }

    public function test_evaluacion_de_otro_residente_y_asignacion_revocada_se_denegan(): void
    {
        [$d] = $this->cargar();
        $lector = app(LectorSimulacionLocal::class);
        $r = $d['residentes']['CAS-08'];
        $idAjeno = DB::table('evaluaciones_expertas')->where('cod_residente', $d['residentes']['CAS-01']->getKey())->value('cod_evaluacion_experta');
        try {
            $lector->consultar($d['nurse'], $r, $idAjeno, 'COG-MEM');
            self::fail('IDOR.');
        } catch (HttpException $e) {
            self::assertSame(404, $e->getStatusCode());
        }
        AsignacionResidenteJornada::query()->where('cod_residente', $r->getKey())->update(['estado' => 'INACTIVA']);
        $this->actingAs($d['nurse'])->get('/admin/enfermeria/pacientes/'.$r->getKey().'/resultados-experto')->assertNotFound();
        self::assertSame(5, DB::table('evaluaciones_expertas')->count());
    }

    public function test_corrupcion_de_traza_retiro_total_sin_reinferir(): void
    {
        [$d] = $this->cargar();
        DB::table('evaluaciones_reglas')->update(['estado_regla' => 'ALTERADO']);
        $l = app(LectorSimulacionLocal::class)
            ->consultar($d['nurse'], $d['residentes']['CAS-08'], null, 'COG-MEM');
        self::assertFalse($l['simulacion_local']['conservacion_completa']);
        self::assertNull($l['simulacion_local']['patron']);
        self::assertNotEmpty($l['detalle']['integridad']);
        foreach ($l['perfil'] as $area) {
            self::assertNull($area['resultado']);
        }
    }

    public function test_traza_conservada_antes_de_ampliar_gobierno_se_recupera_sin_reescribirla(): void
    {
        [$d, $scope] = $this->cargar();
        $criterio = DB::table('evaluacion_criterios')->where('motivo_evaluabilidad', 'like', 'SIM-TRACE-1%')->value('cod_evaluacion_criterio');
        $traza = new \App\Modulos\ApoyoDecision\Infraestructura\Simulacion\TrazaSimulacionLocal($scope);
        $payload = $traza->leer($criterio)['payload'];
        foreach ($payload as &$fila) {
            $fila[3][0] = (array) DB::table('reglas_expertas')->where('cod_regla_experta', $fila[0])->sole();
        }
        unset($fila);
        DB::table('evaluacion_criterios')->where('cod_evaluacion_criterio', $criterio)->update(['motivo_evaluabilidad' => 'SIM-TRACE-1 sha256='.
            \MotorExperto\soporte\conservacion\CodecInstantanea::huella($payload).'; forma posterior a la ampliación estructural.']);
        $huellas = DB::table('evaluacion_criterios')->orderBy('cod_evaluacion_criterio')->pluck('motivo_evaluabilidad')->all();
        $l = app(LectorSimulacionLocal::class)->consultar($d['nurse'], $d['residentes']['CAS-08'], null, 'COG-MEM');
        self::assertTrue($l['simulacion_local']['conservacion_completa']);
        self::assertSame($huellas, DB::table('evaluacion_criterios')->orderBy('cod_evaluacion_criterio')->pluck('motivo_evaluabilidad')->all());
        DB::table('reglas_expertas')->where('codigo_regla', 'SIM-MEM-01')->update(['descripcion' => 'Cambio semántico no conservado.']);
        $l = app(LectorSimulacionLocal::class)->consultar($d['nurse'], $d['residentes']['CAS-08'], null, 'COG-MEM');
        self::assertFalse($l['simulacion_local']['conservacion_completa']);
    }

    public function test_cabecera_o_significado_de_regla_alterados_impiden_publicar_la_simulacion(): void
    {
        [$d] = $this->cargar();
        $lector = app(LectorSimulacionLocal::class);
        DB::table('trazas_inferencia')->update(['estado_inferencia' => 'ERROR']);
        $l = $lector->consultar($d['nurse'], $d['residentes']['CAS-08'], null, 'COG-MEM');
        self::assertFalse($l['simulacion_local']['conservacion_completa']);
        self::assertNull($l['simulacion_local']['patron']);
        DB::table('trazas_inferencia')->update(['estado_inferencia' => 'PRUEBA_FINALIZADA']);
        DB::table('reglas_expertas')->where('codigo_regla', 'SIM-MEM-01')->update(['descripcion' => 'Definición sustituida.']);
        $l = $lector->consultar($d['nurse'], $d['residentes']['CAS-08'], null, 'COG-MEM');
        self::assertFalse($l['simulacion_local']['conservacion_completa']);
        self::assertNull($l['simulacion_local']['patron']);
    }

    public function test_iadl_rectificada_no_reutiliza_el_original_y_la_instantanea_previa_se_conserva(): void
    {
        [$d, $scope, $cut] = $this->cargar();
        $original = ControlFuncionalIadl::query()->firstOrFail();
        $sucesora = $original->replicate();
        $sucesora->cod_control_anterior = $original->getKey();
        $sucesora->estado = 'RECTIFICACION';
        $sucesora->observacion = 'Rectificación sintética explícita.';
        $sucesora->save();
        try {
            (new EjecutorSimulacionLocal($scope))->ejecutar($d['autor']->getKey(), $d['personal']->getKey(), $cut);
            self::fail('No se reutiliza el original rectificado.');
        } catch (\DomainException $e) {
            self::assertStringContainsString('sucesora', $e->getMessage());
        }
        $r = Residente::findOrFail($original->cod_residente);
        $l = app(LectorSimulacionLocal::class)->consultar($d['nurse'], $r, null, 'COG-MEM');
        self::assertTrue($l['simulacion_local']['conservacion_completa']);
        self::assertSame(5, DB::table('evaluaciones_expertas')->count());
    }

    public function test_reglas_descriptivas_sin_referencias_objetivas_no_producen_a_p_ni_pet(): void
    {
        [$d, $scope, $cut] = $this->cargar();
        $version = DB::table('versiones_modelo_experto')->where('codigo_version', ContextoSimulacionLocal::VERSION)->value('cod_version_modelo');
        $r = $d['residentes']['CAS-08'];
        $s = new SolicitudInferenciaDTO('SIM-NEG-SOL', 'SIM-NEG-EVAL', $scope->alias($r->getKey()), $cut, $version);
        $fuente = new FuenteCasoSimulado($scope, $s);
        $m = $fuente->memoria;
        $soloDescriptivas = new MemoriaTrabajo($m->evaluacionId, $m->residenteId, $m->versionConocimiento, $m->fechaCorte,
            array_values($m->hechos), array_values(array_filter($m->evidencias, fn ($e) => ! str_starts_with($e->hecho->origenId, 'SIM-03A-'))));
        $reglas = new ReglaExpertaEloquentRepository(
            new MapeadorReglas(PaqueteSimulacionLocal::TIPO_REGLA), $scope->conexion, $scope);
        $redes = new RedSemanticaEloquentRepository(
            new MapeadorRedSemantica, $scope->conexion, $scope);
        $adscripcion = [];
        foreach ($reglas->porVersion($version) as $regla) {
            $adscripcion[$regla->id] = DominioCognitivoEnum::from($regla->consecuencia['concepto']);
        }
        $inf = (new EjecutarInferenciaUseCase($reglas, $redes))->ejecutar($s, $soloDescriptivas, $adscripcion,
            PaqueteSimulacionLocal::contratoRed($version),
            array_fill_keys(array_keys($redes->porVersion($version)->relaciones), true),
            array_values(array_filter($fuente->procedencias, fn ($h) => isset($soloDescriptivas->evidencias[substr($h->concepto, strlen('VERIFICACION_PROCEDENCIA:'))]))), $fuente->contexto);
        foreach ($inf->perfilCognitivo->resultados as $resultado) {
            self::assertSame('U', $resultado->estado->value);
        }
        $dcl = (new ClasificarDclUseCase)->ejecutar($s, $inf, ...$fuente->entradaDcl);
        self::assertNull($dcl->resultado->patronCandidato);
    }

    private function cargar(): array
    {
        $d = $this->prepararCohorte();
        $scope = new ContextoSimulacionLocal(config('database.default'));
        $cut = new DateTimeImmutable('2026-10-17T12:00:00-04:00');
        (new EjecutorSimulacionLocal($scope))->ejecutar($d['autor']->getKey(), $d['personal']->getKey(), $cut);

        return [$d, $scope, $cut];
    }

    /** Identifica cada dato por campo, registro y fecha original sin sustituir la instantánea por el control actual. */
    public function test_referencias_de_memoria_explicitas_conservan_datos_y_fechas_originales(): void
    {
        [$d] = $this->cargar();
        $residente = $d['residentes']['CAS-01'];
        $control = DB::table('controles_cognitivos')->where('cod_residente', $residente->getKey())->sole();
        DB::table('controles_cognitivos')->where('cod_control_cognitivo', $control->cod_control_cognitivo)
            ->update(['memoria_reciente' => 'CONSERVADA', 'fecha_hora' => '2026-10-17 11:59:00']);
        $escrituras = [];
        DB::listen(static function ($consulta) use (&$escrituras): void {
            if (preg_match('/^\s*(insert|update|delete|alter|create|drop)\b/i', $consulta->sql)) {
                $escrituras[] = $consulta->sql;
            }
        });
        $lectura = app(LectorSimulacionLocal::class)->consultar($d['nurse'], $residente, null, 'COG-MEM');
        $referencias = $lectura['simulacion_local']['narrativa']['funciones']['COG-MEM']['referencias'];
        self::assertCount(4, $referencias);
        $observadas = array_values(array_filter($referencias, fn ($r) => str_starts_with($r['titulo'], 'Control cognitivo')));
        self::assertCount(3, $observadas);
        self::assertSame(['Memoria reciente: Alteración leve', 'Repite preguntas: Sí', 'Olvida indicaciones: Sí'], array_column($observadas, 'dato'));
        self::assertSame([$control->cod_control_cognitivo], array_values(array_unique(array_column($observadas, 'registro'))));
        self::assertSame(['16/10/2026 09:40:00 (UTC-04:00)'], array_values(array_unique(array_column($observadas, 'fecha'))));
        $simulada = array_values(array_filter($referencias, fn ($r) => str_starts_with($r['titulo'], 'Referencia de simulación')));
        self::assertCount(1, $simulada);
        self::assertSame('SIM-03A-CAS-01-COG-MEM', $simulada[0]['registro']);
        self::assertSame('17/10/2026 12:00:00 (UTC-04:00)', $simulada[0]['fecha']);
        self::assertSame('Alteración objetiva simulada', $simulada[0]['dato']);
        self::assertSame('SIM-03A-v1', $simulada[0]['edicion']);
        $fuentes = $lectura['simulacion_local']['narrativa']['funciones']['COG-MEM']['fuentes_legibles'];
        self::assertCount(2, $fuentes);
        self::assertCount(3, $fuentes[0]['datos']);
        self::assertSame('Control cognitivo', $fuentes[0]['titulo']);
        $this->actingAs($d['nurse']);
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $residente])
            ->assertSee('Información utilizada')->assertSee('Memoria reciente: Alteración leve')
            ->assertSee('Repite preguntas: Sí')->assertSee('Olvida indicaciones: Sí')
            ->assertSee('Control cognitivo')->assertSee('16/10/2026 a las 09:40')
            ->assertSee('Referencia de simulación')->assertSee('17/10/2026 a las 12:00')
            ->assertSee('No corresponde a un examen aplicado al residente.')
            ->assertDontSee('Referencia de memoria del')->assertDontSee('17/10/2026 11:59');
        $visible = html_entity_decode(strip_tags($componente->html()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        self::assertDoesNotMatchRegularExpression('/COG-|COD_|SIM-|RAW:|controles_cognitivos|representacion_literal|Edición:/', $visible);
        if ($directorio = getenv('RM_REFERENCIAS_EXPERTO_QA_DIR')) {
            if (! is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
            file_put_contents($directorio.'/referencias.html', $componente->html());
        }
        self::assertSame([], $escrituras);
    }

    /** Distingue ausencia, respuesta negativa y literal histórico sin convertirlos en normalidad ni completar datos. */
    public function test_referencias_descriptivas_no_confunden_ausencia_negativa_o_literal_desconocido(): void
    {
        $d = $this->prepararCohorte();
        DB::table('controles_cognitivos')->where('cod_residente', $d['residentes']['CAS-01']->getKey())
            ->update(['memoria_reciente' => null, 'repite_preguntas' => false]);
        DB::table('controles_cognitivos')->where('cod_residente', $d['residentes']['CAS-02']->getKey())
            ->update(['memoria_reciente' => 'LITERAL_HISTORICO']);
        (new EjecutorSimulacionLocal(new ContextoSimulacionLocal(config('database.default'))))->ejecutar(
            $d['autor']->getKey(), $d['personal']->getKey(), new DateTimeImmutable('2026-10-17T12:00:00-04:00'));
        $lector = app(LectorSimulacionLocal::class);
        $primero = $lector->consultar($d['nurse'], $d['residentes']['CAS-01'], null, 'COG-MEM');
        $datos = array_column($primero['simulacion_local']['narrativa']['funciones']['COG-MEM']['referencias'], 'dato');
        // El perfil original excluye el dato ausente: la presentación no debe añadirlo ni declararlo conservado.
        self::assertFalse(collect($datos)->contains(fn ($dato) => str_starts_with($dato, 'Memoria reciente:')));
        self::assertContains('Repite preguntas: No', $datos);
        self::assertContains('Olvida indicaciones: Sí', $datos);
        $segundo = $lector->consultar($d['nurse'], $d['residentes']['CAS-02'], null, 'COG-MEM');
        self::assertContains('Memoria reciente: LITERAL_HISTORICO', array_column(
            $segundo['simulacion_local']['narrativa']['funciones']['COG-MEM']['referencias'], 'dato'));
        $this->actingAs($d['nurse']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-02']])
            ->assertDontSee('LITERAL_HISTORICO')->assertSee('El dato guardado necesita revisión para disponer de una descripción comprensible.');
    }

    /** Muestra la narrativa conservada sin escribir, ejecutar el motor ni añadir un selector de modos. */
    public function test_vista_previa_explicita_muestra_caso_y_conserva_el_expediente(): void
    {
        [$d] = $this->cargar();
        $this->actingAs($d['nurse']);
        $escrituras = [];
        DB::listen(static function ($q) use (&$escrituras): void {
            if (preg_match('/^\s*(insert|update|delete|alter|create|drop)\b/i', $q->sql)) {
                $escrituras[] = $q->sql;
            }
        });
        $componente = Livewire::withQueryParams(['simulacion' => '1'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->assertSet('simulacion', true)
            ->assertSee('Entorno de pruebas · Resultado de demostración · Sin uso asistencial.')
            ->assertSee('amnésico de dominio único')
            ->assertDontSee('Salir de la vista previa')
            ->assertSee('Requisitos considerados en la evaluación')
            ->assertSee('Cambio respecto al funcionamiento anterior')
            ->assertSee('Evidencia objetiva y coherencia del perfil')
            ->assertSee('Autonomía cotidiana anterior y actual')
            ->assertSee('Factores médicos y revisión diferencial')
            ->assertSee('Condiciones de interpretación')
            ->assertSee('Información considerada hasta:')
            ->assertDontSee('Confirmar valoración')
            ->call('seleccionarCriterio', 'COG-ATE')
            ->assertSee('amnésico de dominio único');
        $id = DB::table('evaluaciones_expertas')->where('cod_residente', $d['residentes']['CAS-01']->getKey())->value('cod_evaluacion_experta');
        $componente->call('seleccionarEvaluacion', $id)->assertSee('amnésico de dominio único');
        $this->get(route('admin.enfermeria.pacientes.resultados-experto', ['residente' => $d['residentes']['CAS-01'], 'simulacion' => 1]))
            ->assertOk()->assertSee('Entorno de pruebas · Resultado de demostración · Sin uso asistencial.')->assertSee('amnésico de dominio único');
        self::assertSame([], $escrituras);
        self::assertSame([], config('sistema_experto.lectura_clinica'));
    }

    /** Los flags y el entorno se comprueban al abrir y también en las interacciones posteriores. */
    public function test_vista_previa_rechaza_flag_desactivado_y_produccion(): void
    {
        [$d] = $this->cargar();
        $this->actingAs($d['nurse']);
        config(['sistema_experto.simulacion_local.habilitada' => false]);
        Livewire::withQueryParams(['simulacion' => '1'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->assertSet('simulacion', false)->assertDontSee('patrón candidato de DCL');
        config(['sistema_experto.simulacion_local.habilitada' => true]);
        $this->app->instance('env', 'production');
        Livewire::withQueryParams(['simulacion' => '1'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->assertSet('simulacion', false)->assertDontSee('patrón candidato de DCL');
        $this->app->instance('env', 'testing');
        $componente = Livewire::withQueryParams(['simulacion' => '1'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']]);
        config(['sistema_experto.simulacion_local.habilitada' => false]);
        $componente->call('reintentar')->assertNotFound();
    }

    /** Mantiene el alcance del residente y la asignación vigente aun dentro de la vista previa. */
    public function test_vista_previa_denega_evaluacion_ajena_y_asignacion_revocada(): void
    {
        [$d] = $this->cargar();
        $this->actingAs($d['nurse']);
        $id = DB::table('evaluaciones_expertas')->where('cod_residente', $d['residentes']['CAS-02']->getKey())->value('cod_evaluacion_experta');
        Livewire::withQueryParams(['simulacion' => '1'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->call('seleccionarEvaluacion', $id)->assertNotFound();
        $componente = Livewire::withQueryParams(['simulacion' => '1'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']]);
        AsignacionResidenteJornada::query()->update(['estado' => 'INACTIVA']);
        $componente->call('reintentar')->assertNotFound();
    }

    /** Ninguna simulación habilita una firma o un acceso a una fuente clínica como sustitución. */
    public function test_vista_previa_no_admite_revision_ni_fuente_operativa(): void
    {
        [$d] = $this->cargar();
        $this->actingAs($d['nurse']);
        Livewire::withQueryParams(['simulacion' => '1'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->call('prepararRevision', 'MEDICINA')->assertNotFound();
        Livewire::withQueryParams(['simulacion' => '1'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->call('verEvidencia', 'FUENTE')->assertNotFound();
        self::assertSame(0, DB::table('revisiones_evaluacion_experta')->count());
    }

    /** Una instantánea corrupta retira toda conclusión en vez de publicar una narrativa parcial. */
    public function test_vista_previa_corrupta_muestra_error_seguro_sin_patron(): void
    {
        [$d] = $this->cargar();
        DB::table('evaluaciones_expertas')->update(['fecha_corte_zona' => 'INVALID/TZ']);
        $this->actingAs($d['nurse']);
        Livewire::withQueryParams(['simulacion' => '1'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->assertSee('No se pudo consultar la evaluación')
            ->assertDontSee('amnésico de dominio único');
    }

    /** La auditoría autorizada abre los valores y decisiones de la instantánea, no un fundamento clínico. */
    public function test_vista_previa_auditoria_conservada_y_cuenta_inactiva(): void
    {
        [$d] = $this->cargar();
        $this->actingAs($d['autor']);
        $componente = Livewire::withQueryParams(['simulacion' => '1'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->call('verTrazabilidad')
            ->assertSee('Decisiones de reglas conservadas')->assertSee('SIM-MEM-03')
            ->assertSee('Valores utilizados de la instantánea original');
        $d['autor']->update(['estado' => 'INACTIVO']);
        $componente->call('reintentar')->assertNotFound();
    }

    /** La ruta habitual muestra la valoración conservada del laboratorio y un único aviso de origen. */
    public function test_vista_previa_predeterminada_muestra_valoracion_y_por_que_sin_estado_vacio(): void
    {
        [$d] = $this->cargar();
        $this->actingAs($d['nurse']);
        $escrituras = [];
        DB::listen(static function ($q) use (&$escrituras): void {
            if (preg_match('/^\s*(insert|update|delete|alter|create|drop)\b/i', $q->sql)) {
                $escrituras[] = $q->sql;
            }
        });
        $respuesta = $this->get(route('admin.enfermeria.pacientes.resultados-experto', ['residente' => $d['residentes']['CAS-01']]))
            ->assertOk()->assertSee('Evaluación neurocognitiva')
            ->assertDontSee('Consulta de Enfermería · Residente asignado.')
            ->assertDontSee('rm-expert-test-note')->assertSee('Se identifican dificultades en memoria.')
            ->assertSee('patrón candidato de DCL amnésico de dominio único')
            ->assertSee('¿Por qué llegamos a este resultado?')->assertSee('Autonomía cotidiana')
            ->assertSee('Resultado conservado')->assertSee('Entorno de pruebas · Resultado de demostración · Sin uso asistencial.')
            ->assertDontSee('Sin evaluación disponible')->assertDontSee('Pendiente de una valoración disponible')
            ->assertDontSee('ficticio')->assertDontSee('ficticia');
        self::assertSame(1, substr_count($respuesta->getContent(), 'Entorno de pruebas · Resultado de demostración · Sin uso asistencial.'));
        $texto = html_entity_decode(strip_tags($respuesta->getContent()));
        self::assertStringNotContainsString('SIMULACIÓN ACADÉMICA', $texto);
        self::assertStringNotContainsString('Las simulaciones', $texto);
        self::assertSame([], $escrituras);
        Livewire::withQueryParams(['simulacion' => '0'])
            ->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->assertSet('simulacion', true)->assertDontSee('Sin evaluación disponible')
            ->assertSee('patrón candidato de DCL amnésico de dominio único')
            ->assertDontSee('Salir de la vista previa');
        $this->get(route('admin.enfermeria.pacientes.resultados-experto', ['residente' => $d['residentes']['CAS-01'], 'simulacion' => 0]))
            ->assertOk()->assertSee('patrón candidato de DCL amnésico de dominio único')
            ->assertSee('Requisitos considerados en la evaluación')->assertDontSee('Salir de la vista previa');
    }

    /** Deshabilitar el laboratorio o usar producción impide mostrar sus resultados por defecto. */
    public function test_vista_previa_predeterminada_se_cierra_fuera_del_laboratorio(): void
    {
        [$d] = $this->cargar();
        $this->actingAs($d['nurse']);
        config(['sistema_experto.simulacion_local.habilitada' => false]);
        Livewire::withQueryParams([])->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->assertSet('simulacion', false)->assertDontSee('patrón candidato de DCL');
        config(['sistema_experto.simulacion_local.habilitada' => true]);
        $this->app->instance('env', 'production');
        Livewire::withQueryParams([])->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->assertSet('simulacion', false)->assertDontSee('patrón candidato de DCL');
        $this->app->instance('env', 'testing');
    }

    /** Una evaluación operativa existente conserva prioridad; la vista previa no la sustituye. */
    public function test_vista_previa_predeterminada_no_reemplaza_evaluacion_operativa(): void
    {
        [$d] = $this->cargar();
        $this->actingAs($d['nurse']);
        InformeCognitivoFixture::conocimiento($d['autor']);
        InformeCognitivoFixture::evaluacion($d['residentes']['CAS-01'], $d['personal'], 'OPERATIVA');
        Livewire::withQueryParams([])->test(ResultadosExpertoResidente::class, ['residente' => $d['residentes']['CAS-01']])
            ->assertSet('simulacion', false)->assertSee('Se identificaron dificultades en los componentes evaluados de memoria.')
            ->assertDontSee(InformeExpertoDTO::ADVERTENCIA_EXPERIMENTAL);
    }

    public function test_zona_conservada_corrupta_retira_resultados_con_mensaje_seguro(): void
    {
        [$d] = $this->cargar();
        DB::table('evaluaciones_expertas')->update(['fecha_corte_zona' => 'INVALID/TZ']);
        $l = app(LectorSimulacionLocal::class)
            ->consultar($d['nurse'], $d['residentes']['CAS-08'], null, 'COG-MEM');
        self::assertFalse($l['simulacion_local']['conservacion_completa']);
        self::assertNull($l['simulacion_local']['patron']);
        self::assertNotEmpty($l['detalle']['integridad']);
    }
}
