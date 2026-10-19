<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Repositories;

use App\Models\EvaluacionExperta;
use App\Models\HechoInstantaneaExperta;
use MotorExperto\dominio\orion\Entities\Cognicion\ResultadoDominio;
use MotorExperto\dominio\orion\Entities\Cognicion\ResultadoSubcomponente;
use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Conocimiento\RedSemantica;
use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\orion\Entities\Funcionalidad\VectorFuncionalidad;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Services\Dcl\ClasificadorPetersenService;
use MotorExperto\dominio\orion\Services\Inferencia\MotorInferenciaService;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum as D;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum as S;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EvaluabilidadEnum as V;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum as H;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum as Sub;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum as Op;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\CatalogoConservacionExperta as Catalogo;
use MotorExperto\soporte\conservacion\CodecInstantanea as Codec;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorResultados;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvaluacionExpertaEloquentRepository;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;
use Tests\Unit\SistemaExperto\Domain\Services\Inferencia\EscenarioInferencia;
use Tests\Unit\SistemaExperto\Infrastructure\Mappers\EscenarioMapeadores;

/**
 * TÍTULO: Conservación exacta sobre esquema real y base desechable.
 * CAPA: Infrastructure — pruebas de integración.
 * ¿PARA QUÉ SIRVE? Demuestra recuperación durable, atomicidad e inmutabilidad.
 * ¿QUÉ RECIBE? Objetos y referencias institucionales completamente ficticios.
 * ¿QUÉ HACE? Guarda y recupera con nuevas instancias del repositorio real.
 * ¿QUÉ DEVUELVE? Evidencia de tipos, identidad compartida, orden, FK y rollback.
 * ¿QUÉ NO DEBE HACER? No simula SQL ni declara validación clínica.
 */
final class ConservacionExpertaTest extends RepositoriosTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Catalogo::preparar($this->conexionPruebas(), 'SIM-V1', 'SIM-USU');
    }

    private function repo(): EvaluacionExpertaEloquentRepository
    {
        return new EvaluacionExpertaEloquentRepository(new MapeadorResultados(new DateTimeZone('America/La_Paz')), null,
            $this->conexionPruebas(), self::extraccion(), new DateTimeZone('America/La_Paz'));
    }

    public static function patrones(): iterable
    {
        yield [['A', 'P', 'P', 'P', 'P'], 'PET-01'];
        yield [['A', 'A', 'P', 'P', 'P'], 'PET-02'];
        yield [['P', 'A', 'P', 'P', 'P'], 'PET-03'];
        yield [['P', 'A', 'P', 'A', 'P'], 'PET-04'];
        yield [['U', 'U', 'U', 'U', 'U'], null];
    }

    #[DataProvider('patrones')]
    public function test_recupera_perfil_y_dcl_sin_recuperador_anterior(array $estados, ?string $patron): void
    {
        $perfil = E::perfil($estados);
        $r = (new ClasificadorPetersenService)->clasificar($perfil);
        $this->repo()->guardar($perfil->cognicion);
        $this->repo()->guardarClasificacion($r);
        $recuperado = $this->repo()->obtenerClasificacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha(), $perfil->versionContrato);
        self::assertSame($patron, $recuperado->patronCandidato?->value);
        self::assertSame(Codec::huella($r), Codec::huella($recuperado));
        self::assertNotSame($r, $recuperado);
        $memoria = $recuperado->perfil->cognicion->memoria;
        self::assertSame($memoria, $recuperado->perfil->funcionalidad->memoria);
        foreach ($recuperado->perfil->soportes as $soportes) {
            foreach ($soportes as $h) {
                self::assertSame($memoria->hechos[$h->id], $h);
            }
        }
        self::assertSame($memoria->hechos['SIM-IADL'], $recuperado->perfil->funcionalidad->evidencias[0]->hecho);
        self::assertSame(10, DB::table('evaluacion_criterios')->whereIn('tipo_registro', ['DOMINIO', 'DCL'])->count());
        self::assertSame(1, DB::table('evaluaciones_expertas')->count());
    }

    public function test_preserva_tipos_fechas_ids_largos_y_19_subcomponentes(): void
    {
        $corte = new DateTimeImmutable('2026-10-17 12:00:00.123456', new DateTimeZone('America/La_Paz'));
        $id = 'SIM-EVALUACION-CON-IDENTIDAD-ORIGINAL-MUY-LARGA';
        $hechos = [];
        foreach ([null, false, true, 0, '0', '0.00', 0.0, -0.0, PHP_INT_MAX, 1.2345678901234567] as $i => $valor) {
            $hechos[] = E::hecho(['id' => 'SIM-TIPO-'.$i, 'evaluacionId' => $id, 'origenId' => 'SIM-OR-'.$i,
                'fuenteId' => 'REFERENCIA_IADL_SINTETICA', 'fecha' => $corte, 'valor' => $valor]);
        }
        $m = new MemoriaTrabajo($id, 'SINTETICO-RES', 'SIM-V1', $corte, $hechos);
        $resultados = [];
        foreach (D::cases() as $dominio) {
            $partes = [];
            foreach (Sub::cases() as $sub) {
                if ($sub->dominio() === $dominio) {
                    $partes[] = new ResultadoSubcomponente($sub, H::DESCONOCIDO, $m, [], 'Sin datos originales.');
                }
            }
            $resultados[] = new ResultadoDominio($dominio, S::DESCONOCIDO, V::NO_EVALUABLE, $m, $partes, [], 'Ausencia documentada.');
        }
        $vector = new VectorCognitivo($resultados);
        $this->repo()->guardar($vector);
        $r = $this->repo()->obtener($id, 'SINTETICO-RES', 'SIM-V1', $corte);
        self::assertSame(Codec::huella($vector), Codec::huella($r));
        self::assertSame(19, DB::table('evaluacion_criterios')->where('tipo_registro', 'SUBCOMPONENTE')->count());
        self::assertLessThanOrEqual(20, strlen(DB::table('evaluaciones_expertas')->value('cod_evaluacion_experta')));
        foreach ($hechos as $h) {
            self::assertSame(Codec::escalar('v', $h->valor), Codec::escalar('v', $r->memoria->hechos[$h->id]->valor));
        }
    }

    public function test_reintentos_no_escriben_y_contenido_divergente_se_rechaza(): void
    {
        $perfil = E::perfil();
        $r = (new ClasificadorPetersenService)->clasificar($perfil);
        $this->repo()->guardar($perfil->cognicion);
        $this->repo()->guardarClasificacion($r);
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $this->repo()->guardar($perfil->cognicion);
        $this->repo()->guardarClasificacion($r);
        $this->exigirSoloLecturas();
        $cambio = new ResultadoClasificacionDcl($r->perfil, $r->patronCandidato, [], 'Otra justificación.');
        $this->expectException(DomainException::class);
        $this->repo()->guardarClasificacion($cambio);
    }

    public function test_fallo_intermedio_revierte_instantanea_y_correlativos(): void
    {
        $antes = DB::table('secuencias_codigos')->orderBy('entidad')->get()->all();
        $disparado = false;
        DB::listen(function ($consulta) use (&$disparado): void {
            if (! $disparado && str_contains($consulta->sql, 'insert into "resultados_criterio"')) {
                $disparado = true;
                throw new RuntimeException('Fallo de almacenamiento simulado.');
            }
        });
        try {
            $this->repo()->guardar(E::perfil()->cognicion);
            self::fail('Debe fallar dentro de la transacción.');
        } catch (RuntimeException $e) {
            self::assertSame('Fallo de almacenamiento simulado.', $e->getMessage());
        }
        self::assertTrue($disparado);
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        self::assertSame(0, DB::table('hechos_instantanea_experta')->count());
        self::assertEquals($antes, DB::table('secuencias_codigos')->orderBy('entidad')->get()->all());
    }

    public function test_recupera_arbol_de_reglas_compuesto(): void
    {
        $perfil = E::perfil();
        $base = (new ClasificadorPetersenService)->clasificar($perfil);
        $regla = new ReglaExperta('SIM-REGLA-ARBOL', 'SIM-V1', ['operador' => Op::Y, 'condiciones' => [
            ['operador' => Op::IGUAL_A, 'variable' => 'SIM-X', 'valor' => false],
            ['operador' => Op::NO, 'condicion' => ['operador' => Op::DISTINTO_DE, 'variable' => 'SIM-Y', 'valor' => 0]],
            ['operador' => Op::O, 'condiciones' => [['operador' => Op::IGUAL_A, 'variable' => 'SIM-Z', 'valor' => -0.0]]],
        ]], ['concepto' => 'SIM-CONSECUENCIA', 'valor' => '0'], true, 'SIM-FUENTE');
        $r = new ResultadoClasificacionDcl($perfil, $base->patronCandidato, [], $base->justificacion, [$regla]);
        $this->repo()->guardar($perfil->cognicion);
        $this->repo()->guardarClasificacion($r);
        $nuevo = $this->repo()->obtenerClasificacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha(), $perfil->versionContrato);
        self::assertSame(Codec::huella($r), Codec::huella($nuevo));
        self::assertCount(1, $nuevo->reglasAplicadas);
    }

    public function test_rechaza_corte_con_otra_zona_aunque_sea_el_mismo_instante(): void
    {
        $this->repo()->guardar(E::perfil()->cognicion);
        $this->expectException(DomainException::class);
        $this->repo()->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha()->setTimezone(new DateTimeZone('UTC')));
    }

    public function test_detecta_snapshot_alterada_sin_fallback(): void
    {
        $this->repo()->guardar(E::perfil()->cognicion);
        DB::table('resultados_criterio')->update(['justificacion_original' => 'Cambio físico indebido.']);
        $this->expectException(DomainException::class);
        $this->repo()->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha());
    }

    public function test_snapshot_institucional_no_reextrae_datos_al_recuperar(): void
    {
        EscenarioRepositorios::evidencia();
        $m = $this->evidencias()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha());
        $vector = new VectorCognitivo(array_map(fn ($d) => new ResultadoDominio($d, S::DESCONOCIDO, V::NO_EVALUABLE, $m, [], [], 'Sin conclusión.'), D::cases()));
        $this->repo()->guardar($vector);
        DB::table('controles_cognitivos')->update(['memoria_reciente' => 'FUENTE_MODIFICADA_DESPUES']);
        $r = $this->repo()->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha());
        self::assertSame(Codec::huella($vector), Codec::huella($r));
        self::assertNotSame('FUENTE_MODIFICADA_DESPUES', $r->memoria->hechos['SIM-EVID']->valor);
    }

    public function test_constraints_rechazan_dos_valores_escalares_simultaneos(): void
    {
        $this->repo()->guardar(E::perfil()->cognicion);
        $this->expectException(QueryException::class);
        DB::transaction(fn () => DB::table('hechos_instantanea_experta')->update(['valor_tipo' => 'NULL', 'valor_texto' => 'Indebido']));
    }

    public function test_nuevos_modelos_no_admiten_sobrescritura_masiva(): void
    {
        $this->expectException(LogicException::class);
        HechoInstantaneaExperta::query()->update(['valor_texto' => 'Cambio']);
    }

    public function test_migracion_down_up_preserva_d137_preexistente(): void
    {
        [$modelo] = EscenarioMapeadores::resultados(['U', 'U', 'U', 'U', 'U']);
        EscenarioRepositorios::guardarGrafo($modelo);
        $antes = DB::table('resultados_criterio')->orderBy('cod_resultado_criterio')->get()->all();
        $migracion = require base_path('database/migrations/2026_10_17_200000_incorporate_conservacion_experta_v2.php');
        $migracion->down();
        self::assertFalse(DB::getSchemaBuilder()->hasTable('hechos_instantanea_experta'));
        $migracion->up();
        self::assertEquals($antes, DB::table('resultados_criterio')->orderBy('cod_resultado_criterio')->get()->all());
        self::assertSame(5, DB::table('evaluacion_criterios')->where('tipo_registro', 'D137')->count());
    }

    public function test_down_rechaza_historia_incluso_si_la_cabecera_fue_desmarcada(): void
    {
        $this->repo()->guardar(E::perfil()->cognicion);
        DB::table('evaluaciones_expertas')->update(['version_conservacion' => null]);
        $migracion = require base_path('database/migrations/2026_10_17_200000_incorporate_conservacion_experta_v2.php');
        try {
            $migracion->down();
            self::fail('No debe borrar una historia parcialmente representada.');
        } catch (RuntimeException) {
            self::assertTrue(DB::getSchemaBuilder()->hasTable('hechos_instantanea_experta'));
            self::assertGreaterThan(0, DB::table('hechos_instantanea_experta')->count());
        }
    }

    public function test_criterio_dcl_no_admite_version_nula(): void
    {
        $perfil = E::perfil();
        $this->repo()->guardar($perfil->cognicion);
        $this->repo()->guardarClasificacion((new ClasificadorPetersenService)->clasificar($perfil));
        $this->expectException(QueryException::class);
        DB::transaction(fn () => DB::table('evaluacion_criterios')->where('tipo_registro', 'DCL')->update(['version_conservacion' => null]));
    }

    public function test_catalogo_se_prepara_idempotentemente_sin_generar_mas_filas(): void
    {
        $antes = DB::table('secuencias_codigos')->orderBy('entidad')->get()->all();
        Catalogo::preparar($this->conexionPruebas(), 'SIM-V1', 'SIM-USU');
        self::assertEquals($antes, DB::table('secuencias_codigos')->orderBy('entidad')->get()->all());
        self::assertSame(29, DB::table('nodos_semanticos')->where('definicion', Catalogo::VERSION)->count());
    }

    public function test_cognicion_divergente_no_sustituye_el_original(): void
    {
        $this->repo()->guardar(E::perfil()->cognicion);
        try {
            $this->repo()->guardar(E::perfil(['P', 'A', 'P', 'P', 'P'])->cognicion);
            self::fail('La identidad no permite sustituir su contenido.');
        } catch (DomainException) {
            self::assertSame('A', $this->repo()->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha())->resultados['COG-MEM']->estado->value);
        }
    }

    public function test_fallo_al_guardar_dcl_no_deja_funcionalidad_ni_requisitos_parciales(): void
    {
        $perfil = E::perfil();
        $this->repo()->guardar($perfil->cognicion);
        $disparado = false;
        DB::listen(function ($q) use (&$disparado): void {
            if (! $disparado && str_contains($q->sql, 'insert into "resultados_criterio"')) {
                $disparado = true;
                throw new RuntimeException('Fallo DCL simulado.');
            }
        });
        try {
            $this->repo()->guardarClasificacion((new ClasificadorPetersenService)->clasificar($perfil));
            self::fail('Debe revertir DCL.');
        } catch (RuntimeException $e) {
            self::assertSame('Fallo DCL simulado.', $e->getMessage());
        }
        self::assertTrue($disparado);
        self::assertSame(0, DB::table('funcionalidad_instantanea_experta')->count());
        self::assertSame(0, DB::table('evidencias_iadl_instantanea')->count());
        self::assertSame(0, DB::table('clasificaciones_dcl_instantanea')->count());
        self::assertSame(0, DB::table('evaluacion_criterios')->where('tipo_registro', 'DCL')->count());
        self::assertNotNull($this->repo()->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha()));
    }

    public function test_rechaza_campo_contextual_no_autorizado_y_revierte_memoria(): void
    {
        $h = E::hecho(['concepto' => 'COG-MEM:campo_inventado', 'valor' => true]);
        $m = E::memoria([], [$h]);
        $vector = new VectorCognitivo(array_map(fn ($d) => new ResultadoDominio($d, S::DESCONOCIDO, V::NO_EVALUABLE, $m, [], [], 'Sin datos.'), D::cases()));
        try {
            $this->repo()->guardar($vector);
            self::fail('No admite conceptos clínicos libres.');
        } catch (DomainException) {
            self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        }
    }

    public function test_recupera_regla_con_profundidad_maxima_32(): void
    {
        $perfil = E::perfil();
        $base = (new ClasificadorPetersenService)->clasificar($perfil);
        $ast = ['operador' => Op::IGUAL_A, 'variable' => 'SIM-X', 'valor' => 0];
        for ($i = 0; $i < 32; $i++) {
            $ast = ['operador' => Op::NO, 'condicion' => $ast];
        }
        $regla = new ReglaExperta('SIM-PROFUNDA', 'SIM-V1', $ast, ['concepto' => 'SIM-SALIDA', 'valor' => true], true, 'SIM-FUENTE');
        $r = new ResultadoClasificacionDcl($perfil, $base->patronCandidato, [], $base->justificacion, [$regla]);
        $this->repo()->guardar($perfil->cognicion);
        $this->repo()->guardarClasificacion($r);
        self::assertSame(Codec::huella($r), Codec::huella($this->repo()->obtenerClasificacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha(), $perfil->versionContrato)));
    }

    public function test_fuente_institucional_manipulada_no_se_conserva_como_original(): void
    {
        EscenarioRepositorios::evidencia();
        $m = $this->evidencias()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha());
        DB::table('controles_cognitivos')->update(['memoria_reciente' => 'CAMBIO_ANTES_DE_GUARDAR']);
        $vector = new VectorCognitivo(array_map(fn ($d) => new ResultadoDominio($d, S::DESCONOCIDO, V::NO_EVALUABLE, $m, [], [], 'Sin conclusión.'), D::cases()));
        try {
            $this->repo()->guardar($vector);
            self::fail('El valor original ya no coincide.');
        } catch (DomainException) {
            self::assertNull(DB::table('evaluaciones_expertas')->value('version_conservacion'));
            self::assertSame(0, DB::table('hechos_instantanea_experta')->count());
        }
    }

    public function test_resultado_no_admite_otro_valor_con_mismo_codigo(): void
    {
        $this->repo()->guardar(E::perfil(['U', 'U', 'U', 'U', 'U'])->cognicion);
        EscenarioRepositorios::version('SIM-V2');
        Catalogo::preparar($this->conexionPruebas(), 'SIM-V2', 'SIM-USU');
        $otro = Catalogo::leer($this->conexionPruebas(), 'SIM-V2')['SNAP:DOMINIO:COG-MEM']['valores']['U'];
        DB::table('resultados_criterio')->update(['cod_valor_semantico' => $otro]);
        $this->expectException(DomainException::class);
        $this->repo()->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha());
    }

    public function test_entero_corrupto_no_se_convierte_a_cero_en_sqlite(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            $this->expectException(QueryException::class);
        } else {
            $this->expectException(DomainException::class);
        }
        $h = E::hecho(['fuenteId' => 'REFERENCIA_IADL_SINTETICA', 'valor' => 0]);
        $m = E::memoria([], [$h]);
        $v = new VectorCognitivo(array_map(fn ($d) => new ResultadoDominio($d, S::DESCONOCIDO, V::NO_EVALUABLE, $m, [], [], 'Sin conclusión.'), D::cases()));
        $this->repo()->guardar($v);
        DB::transaction(fn () => DB::table('hechos_instantanea_experta')->update(['valor_entero' => 'garbage']));
        $this->repo()->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha());
    }

    public function test_regla_d137_adulterada_revierte_toda_la_clasificacion(): void
    {
        $f = EscenarioMapeadores::regla();
        EscenarioRepositorios::guardarGrafo($f);
        $original = $this->reglas()->obtener('SIM-R', 'SIM-V1');
        $regla = new ReglaExperta($original->id, $original->versionConocimiento, $original->condiciones,
            ['concepto' => $original->consecuencia['concepto'], 'valor' => 'VALOR_INVENTADO'], true, $original->fuente);
        $perfil = E::perfil();
        $base = (new ClasificadorPetersenService)->clasificar($perfil);
        $this->repo()->guardar($perfil->cognicion);
        try {
            $this->repo()->guardarClasificacion(new ResultadoClasificacionDcl($perfil, $base->patronCandidato, [], $base->justificacion, [$regla]));
            self::fail('Una PK real no acredita contenido inventado.');
        } catch (DomainException) {
            self::assertSame(0, DB::table('funcionalidad_instantanea_experta')->count());
            self::assertSame(0, DB::table('clasificaciones_dcl_instantanea')->count());
            self::assertSame(0, DB::table('evaluacion_criterios')->where('tipo_registro', 'DCL')->count());
        }
    }

    public function test_rechaza_fk_de_hecho_que_apunta_a_otra_evaluacion(): void
    {
        EscenarioRepositorios::evidencia();
        $m = $this->evidencias()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha());
        $vector = new VectorCognitivo(array_map(fn ($d) => new ResultadoDominio($d, S::DESCONOCIDO, V::NO_EVALUABLE, $m, [], [], 'Sin conclusión.'), D::cases()));
        $this->repo()->guardar($vector);
        $cab = (array) DB::table('evaluaciones_expertas')->first();
        $cab['cod_evaluacion_experta'] = 'SIM-OTRA';
        foreach (['id_evaluacion_dominio', 'version_conservacion', 'huella_memoria_cognicion', 'fecha_corte_utc', 'fecha_corte_zona', 'fecha_corte_offset'] as $c) {
            $cab[$c] = null;
        }
        DB::table('evaluaciones_expertas')->insert($cab);
        $evidencia = (array) DB::table('evidencias_evaluacion')->first();
        $evidencia['cod_evidencia_evaluacion'] = 'SIM-OTRA-EVID';
        $evidencia['cod_evaluacion_experta'] = 'SIM-OTRA';
        DB::table('evidencias_evaluacion')->insert($evidencia);
        DB::table('hechos_instantanea_experta')->update(['cod_evidencia_evaluacion' => 'SIM-OTRA-EVID']);
        $this->expectException(DomainException::class);
        $this->repo()->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha());
    }

    public function test_no_guarda_fuera_de_testing(): void
    {
        $this->app->instance('env', 'local');
        $this->expectException(RuntimeException::class);
        $this->repo()->guardar(E::perfil()->cognicion);
    }

    public function test_conserva_la_salida_del_motor_real_con_revisiones_y_subcomponentes(): void
    {
        $e = EscenarioInferencia::completo();
        $vector = (new MotorInferenciaService)->inferir($e['memoria'], [], new RedSemantica('SIM-V1', [], []), [],
            procedencias: $e['procedencias'], contexto: $e['contexto'])['perfil'];
        $entrada = $e['entradaDcl'];
        $funcional = new VectorFuncionalidad($vector->memoria, $entrada['independenciaRelativa'], $entrada['evidenciasIadl'],
            $entrada['revisorSinteticoId'], $entrada['justificacionFuncional']);
        $perfil = new PerfilRequisitosDcl($vector, $funcional, $entrada['diferencial'], $entrada['criterios'],
            $entrada['soportes'], $entrada['justificaciones']);
        $r = (new ClasificadorPetersenService)->clasificar($perfil);
        $this->repo()->guardar($vector);
        $this->repo()->guardarClasificacion($r);
        $nuevo = $this->repo()->obtenerClasificacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha(), $perfil->versionContrato);
        self::assertSame(Codec::huella($r), Codec::huella($nuevo));
        self::assertSame('PET-01', $nuevo->patronCandidato->value);
        self::assertSame(19, DB::table('evaluacion_criterios')->where('tipo_registro', 'SUBCOMPONENTE')->count());
        self::assertGreaterThan(30, count($nuevo->perfil->cognicion->memoria->hechos));
    }

    public function test_anexar_snapshot_no_oculta_historia_d137_ni_publica_evaluaciones_nuevas(): void
    {
        [$modelo] = EscenarioMapeadores::resultados(['U', 'U', 'U', 'U', 'U']);
        EscenarioRepositorios::guardarGrafo($modelo);
        self::assertTrue(EvaluacionExperta::query()->paraLecturaD137()->whereKey('SIM-EVAL')->exists());
        $this->repo()->guardar(E::perfil(['U', 'U', 'U', 'U', 'U'])->cognicion);
        self::assertTrue(EvaluacionExperta::query()->paraLecturaD137()->whereKey('SIM-EVAL')->exists());
        $m = new MemoriaTrabajo('SIM-NUEVA', 'SINTETICO-RES', 'SIM-V1', E::fecha());
        $nuevo = new VectorCognitivo(array_map(fn ($d) => new ResultadoDominio($d, S::DESCONOCIDO, V::NO_EVALUABLE, $m, [], [], 'Sin datos.'), D::cases()));
        $this->repo()->guardar($nuevo);
        self::assertSame(['SIM-EVAL'], EvaluacionExperta::query()->paraLecturaD137()->pluck('cod_evaluacion_experta')->all());
        self::assertSame(2, EvaluacionExperta::query()->count());
    }

    public function test_orden_corrupto_no_se_convierte_a_cero(): void
    {
        $this->repo()->guardar(E::perfil()->cognicion);
        $this->expectException(DB::getDriverName() === 'pgsql' ? QueryException::class : DomainException::class);
        DB::transaction(fn () => DB::table('hechos_instantanea_experta')->where('orden', 0)->update(['orden' => 'garbage']));
        $this->repo()->obtener('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha());
    }
}
