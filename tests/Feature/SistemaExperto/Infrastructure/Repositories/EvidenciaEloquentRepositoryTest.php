<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Repositories;

use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

/**
 * TÍTULO: Lectura de evidencias y corte clínico inclusivo.
 * CAPA: Infrastructure - Repositories (pruebas de integración).
 * ¿PARA QUÉ SIRVE? Prueba la coordinación de Eloquent, extracción por lote y mapper.
 * ¿QUÉ INFORMACIÓN RECIBE? Fixtures sintéticas sobre las migraciones reales.
 * ¿QUÉ HACE? Conserva procedencia y distingue ausencia, falta técnica y duplicación.
 * ¿QUÉ DEVUELVE? Aserciones sobre entidades, contexto y consultas.
 * ¿QUÉ NO DEBE HACER? No infiere A/P, interpreta instrumentos ni reemplaza IADL.
 */
final class EvidenciaEloquentRepositoryTest extends RepositoriosTestCase
{
    /** Preserva el original aunque su incorporación técnica sea posterior; sin parámetros ni excepciones esperadas. */
    public function test_recupera_observacion_y_procedencia_originales_sin_escribir(): void
    {
        EscenarioRepositorios::evidencia();
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();

        $memoria = $this->evidencias()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertCount(1, $memoria->hechos);
        self::assertCount(1, $memoria->evidencias);
        $hecho = $memoria->hechos['SIM-EVID'];
        self::assertSame('SIM-CTRL', $hecho->origenId);
        self::assertSame('SIM-AT', $hecho->episodioId);
        self::assertSame('OBSERVACION_FICTICIA', $hecho->valor);
        self::assertEquals(self::corte(), $hecho->fecha);
        self::assertSame($hecho, $memoria->evidencias['SIM-EVID']->hecho);
        self::assertSame(HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO,
            $memoria->evidencias['SIM-EVID']->hallazgo);
        $this->exigirSoloLecturas();
    }

    /**
     * Conserva NULL y cero textual sin convertirlos en false.
     *
     * @param  string|null  $valor  Dato físico artificial.
     * @return void Sin excepciones esperadas.
     */
    #[DataProvider('valoresBrutos')]
    public function test_conserva_valores_brutos_sin_default_clinico(?string $valor): void
    {
        EscenarioRepositorios::evidencia(['memoria_reciente' => $valor]);
        $memoria = $this->evidencias()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame($valor, $memoria->hechos['SIM-EVID']->valor);
    }

    /** @return iterable<string, array{string|null}> Valores sin equivalencia clínica; sin parámetros ni excepciones. */
    public static function valoresBrutos(): iterable
    {
        yield 'NULL' => [null];
        yield 'cero textual' => ['0'];
    }

    /** Excluye un registro un segundo posterior al corte; sin parámetros ni excepciones esperadas. */
    public function test_excluye_fuentes_posteriores_al_corte_sin_usar_fecha_de_incorporacion(): void
    {
        EscenarioRepositorios::evidencia(['fecha_hora' => '2026-10-17 12:00:01']);
        $memoria = $this->evidencias()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame([], $memoria->hechos);
        self::assertSame([], $memoria->evidencias);
        self::assertEquals(self::corte(), $memoria->fechaCorte);
    }

    /** Un destino técnico HECHO conserva contexto sin acreditar evidencia cognitiva; sin parámetros ni excepciones esperadas. */
    public function test_conserva_hechos_de_contexto_sin_crear_evidencia_cognitiva(): void
    {
        EscenarioRepositorios::evidencia();
        $memoria = $this->evidencias(self::extraccion('HECHO'))->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertCount(1, $memoria->hechos);
        self::assertSame([], $memoria->evidencias);
    }

    /** La cantidad de consultas permanece constante al añadir otro origen; sin parámetros ni excepciones esperadas. */
    public function test_extrae_los_origenes_por_lote_sin_una_consulta_por_evidencia(): void
    {
        EscenarioRepositorios::evidencia();
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $this->evidencias()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
        $cantidad = count(DB::connection()->getQueryLog());

        $control = (array) DB::table('controles_cognitivos')->where('cod_control_cognitivo', 'SIM-CTRL')->first();
        $control['cod_control_cognitivo'] = 'SIM-CTRL-2';
        DB::table('controles_cognitivos')->insert($control);
        $evidencia = (array) DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'SIM-EVID')->first();
        $evidencia['cod_evidencia_evaluacion'] = 'SIM-EVID-2';
        $evidencia['cod_registro_fuente'] = 'SIM-CTRL-2';
        DB::table('evidencias_evaluacion')->insert($evidencia);
        DB::connection()->flushQueryLog();

        $memoria = $this->evidencias()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertCount(2, $memoria->evidencias);
        self::assertCount($cantidad, DB::connection()->getQueryLog());
        self::assertFalse($memoria->evidencias['SIM-EVID']->esIndependienteDe($memoria->evidencias['SIM-EVID-2']));
        $this->exigirSoloLecturas();
    }

    /** No solicita fuentes cuando la cabecera existe sin filas admitidas; sin parámetros ni excepciones esperadas. */
    public function test_una_memoria_vacia_conserva_el_contexto_sin_invocar_extraccion(): void
    {
        EscenarioRepositorios::evidencia();
        DB::table('evidencias_evaluacion')->update(['estado_admisibilidad' => 'NO_ADMISIBLE']);
        $repo = $this->evidencias(static function (): never {
            throw new RuntimeException('No debe invocarse.');
        });
        $memoria = $repo->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame('SIM-EVAL', $memoria->evaluacionId);
        self::assertSame('SINTETICO-RES', $memoria->residenteId);
        self::assertSame('SIM-V1', $memoria->versionConocimiento);
        self::assertSame([], $memoria->hechos);
    }

    /** @throws DomainException Rechazo esperado; una evaluación inexistente no produce éxito vacío. */
    public function test_rechaza_evaluacion_inexistente(): void
    {
        $this->expectException(DomainException::class);
        $this->evidencias()->paraEvaluacion('SIM-NO-EXISTE', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException Rechazo esperado antes de invocar la extracción. */
    public function test_rechaza_residente_ajeno_en_la_cabecera(): void
    {
        EscenarioRepositorios::evidencia();
        $this->expectException(DomainException::class);
        $this->evidencias(static function (): never {
            throw new RuntimeException('No debe invocarse.');
        })
            ->paraEvaluacion('SIM-EVAL', 'SINTETICO-OTRO', 'SIM-V1', self::corte());
    }

    /** @throws DomainException Mapeos inactivos no autorizan consultar una fuente. */
    public function test_rechaza_mapeos_inactivos_antes_de_extraer(): void
    {
        EscenarioRepositorios::evidencia();
        DB::table('mapeos_variables_fuente')->update(['estado' => 'INACTIVO']);
        $this->expectException(DomainException::class);
        $this->evidencias(static function (): never {
            throw new RuntimeException('No debe invocarse.');
        })
            ->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException IADL aprobada no admite PK/metadatos de otra fuente ni sustitución BADL. */
    public function test_no_consulta_una_fuente_iadl_con_metadatos_de_otra_tabla(): void
    {
        EscenarioRepositorios::evidencia();
        self::assertTrue(Schema::hasTable('controles_funcionales_iadl'));
        DB::table('fuentes_datos_expertas')->update(['tabla_raiz' => 'controles_funcionales_iadl']);
        $this->expectException(DomainException::class);
        $this->evidencias(static function (): never {
            throw new RuntimeException('No debe invocarse.');
        })
            ->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws RuntimeException Una fuente no disponible no se convierte en memoria vacía. */
    public function test_no_oculta_una_extraccion_sin_origenes(): void
    {
        EscenarioRepositorios::evidencia();
        $this->expectException(RuntimeException::class);
        $this->evidencias(static fn (): array => [])->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException Dos mapeos del mismo registro no constituyen dos fuentes independientes. */
    public function test_rechaza_el_mismo_origen_bajo_dos_mapeos_distintos(): void
    {
        EscenarioRepositorios::evidencia();
        $mapeo = (array) DB::table('mapeos_variables_fuente')->first();
        $mapeo['cod_mapeo_variable_fuente'] = 'SIM-MAP-2';
        DB::table('mapeos_variables_fuente')->insert($mapeo);
        $evidencia = (array) DB::table('evidencias_evaluacion')->first();
        $evidencia['cod_evidencia_evaluacion'] = 'SIM-EVID-2';
        $evidencia['cod_mapeo_variable_fuente'] = 'SIM-MAP-2';
        DB::table('evidencias_evaluacion')->insert($evidencia);

        $this->expectException(DomainException::class);
        $this->evidencias()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException No acepta un original recibido desde otra conexión. */
    public function test_rechaza_original_fuera_de_la_conexion_segregada(): void
    {
        $origen = EscenarioRepositorios::evidencia();
        $origen->setConnection('SIM-OTRA-CONEXION');
        $this->expectException(DomainException::class);
        $this->evidencias(static fn (): array => ['SIM-EVID' => ['origen' => $origen, 'valor' => null, 'tipo' => 'HECHO']])
            ->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }
}
