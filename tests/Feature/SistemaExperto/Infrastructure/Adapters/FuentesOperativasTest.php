<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Adapters;

use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

/**
 * TÍTULO:
 * Pruebas integradas de controles, seguimiento y BADL.
 *
 * CAPA:
 * Infrastructure - Adapters.
 *
 * ¿PARA QUÉ SIRVE?
 * Recupera datos brutos autorizados sin interpretación clínica.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Registros sintéticos y mapeos reales de la versión SIM-V1.
 *
 * ¿QUÉ HACE?
 * Recorre repositorio, extractor, adaptadores y mapper reales.
 *
 * ¿QUÉ DEVUELVE?
 * Aserciones de NULL, tipos, procedencia, corte y ausencia de efectos.
 *
 * ¿QUÉ NO DEBE HACER?
 * No interpreta puntuaciones, reemplaza IADL ni simula la BDD central.
 */
final class FuentesOperativasTest extends AdaptadoresTestCase
{
    /** NULL sigue desconocido, sin accessors ni escrituras; sin parámetros ni excepciones esperadas. */
    public function test_control_null_permanece_null_hasta_el_dominio(): void
    {
        $this->prepararControl(['memoria_reciente' => null]);
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $memoria = $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertNull($memoria->hechos['SIM-EVID']->valor);
        self::assertSame(HallazgoInstrumentalEnum::DESCONOCIDO, $memoria->evidencias['SIM-EVID']->hallazgo);
        self::assertSame('SIM-AT', $memoria->hechos['SIM-EVID']->episodioId);
        $this->exigirSoloLecturas();
    }

    /**
     * Un booleano conocido nunca se confunde con ausencia.
     *
     * @param  bool|null  $valor  Valor físico del escenario.
     * @return void Sin excepciones esperadas.
     */
    #[DataProvider('booleanos')]
    public function test_control_conserva_booleano_y_null_sin_inventar_normalidad(?bool $valor): void
    {
        $this->prepararControl(['repite_preguntas' => $valor]);
        DB::table('mapeos_variables_fuente')->update(['campo_valor' => 'repite_preguntas']);
        $memoria = $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame($valor, $memoria->hechos['SIM-EVID']->valor);
    }

    /** @return iterable<string, array{bool|null}> Valores independientes; sin parámetros ni excepciones. */
    public static function booleanos(): iterable
    {
        yield 'NULL' => [null];
        yield 'false' => [false];
        yield 'true' => [true];
    }

    /** No selecciona la columna clínica de un registro futuro; sin parámetros ni excepciones esperadas. */
    public function test_fuente_futura_solo_transporta_metadatos_y_no_genera_hechos(): void
    {
        $this->prepararControl(['fecha_hora' => '2026-10-17 12:00:01', 'memoria_reciente' => 'NO-DEBE-LEERSE']);
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $memoria = $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame([], $memoria->hechos);
        foreach (DB::connection()->getQueryLog() as $consulta) {
            if (str_contains($consulta['query'], 'from "controles_cognitivos"')) {
                self::assertStringNotContainsString('memoria_reciente', $consulta['query']);
            }
        }
    }

    /** Un campo textual cero se conserva literalmente; sin parámetros ni excepciones esperadas. */
    public function test_seguimiento_transporta_valor_y_actividad_original(): void
    {
        $this->prepararControl();
        $this->cambiarFuente('seguimientos_pedagogicos', 'cod_seguimiento_pedagogico', 'SIM-PEDAGOGIA', 'SIM-SEG', 'atencion');
        DB::table('actividades')->insert(['cod_actividad' => 'SIM-ACT', 'cod_area' => 'SIM-AREA', 'cod_personal' => 'SIM-PER',
            'tipo' => 'PRUEBA_TECNICA', 'nombre' => 'Actividad ficticia', 'fecha_hora' => '2026-10-17 12:00:00', 'estado' => 'VIGENTE']);
        DB::table('seguimientos_pedagogicos')->insert(['cod_seguimiento_pedagogico' => 'SIM-SEG', 'cod_residente' => 'SINTETICO-RES',
            'cod_personal' => 'SIM-PER', 'cod_actividad' => 'SIM-ACT', 'fecha_hora' => '2026-10-17 12:00:00', 'atencion' => '0', 'estado' => 'VIGENTE']);
        $memoria = $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame('0', $memoria->hechos['SIM-EVID']->valor);
        self::assertSame('SIM-ACT', $memoria->hechos['SIM-EVID']->episodioId);
        self::assertSame(HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO, $memoria->evidencias['SIM-EVID']->hallazgo);
    }

    /** BADL queda como hecho y no recibe los defaults de presentación; sin parámetros ni excepciones esperadas. */
    public function test_badl_null_no_se_convierte_en_autonomia_ni_iadl(): void
    {
        $this->prepararControl();
        $this->cambiarFuente('valoraciones_funcionales', 'cod_valoracion_funcional', 'SIM-BADL', 'SIM-FUNC', 'nivel_dependencia');
        DB::table('valoraciones_funcionales')->insert(['cod_valoracion_funcional' => 'SIM-FUNC', 'cod_residente' => 'SINTETICO-RES',
            'cod_personal' => 'SIM-PER', 'cod_atencion' => 'SIM-AT', 'fecha_hora' => '2026-10-17 12:00:00', 'estado' => 'ACTIVA']);
        $memoria = $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertNull($memoria->hechos['SIM-EVID']->valor);
        self::assertSame([], $memoria->evidencias);
        self::assertArrayNotHasKey('DCL-C03', $memoria->hechos);
    }

    /** @throws DomainException Un accessor ficticio no es una columna autorizada. */
    public function test_rechaza_columnas_derivadas_de_presentacion(): void
    {
        $this->prepararControl();
        $this->cambiarFuente('valoraciones_funcionales', 'cod_valoracion_funcional', 'SIM-BADL', 'SIM-FUNC', 'estado_general');
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException Las columnas configuradas no permiten interpolar SQL. */
    public function test_rechaza_columna_arbitraria_antes_de_consultar_la_fuente(): void
    {
        $this->prepararControl();
        DB::table('mapeos_variables_fuente')->update(['campo_valor' => 'memoria_reciente) OR 1=1']);
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws RuntimeException Una fila fuente ausente no se representa como un valor NULL existente. */
    public function test_origen_ausente_es_error_y_no_dato_nulo(): void
    {
        $this->prepararControl();
        DB::table('evidencias_evaluacion')->update(['cod_registro_fuente' => 'SIM-NO-EXISTE']);
        $this->expectException(RuntimeException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException El estado anulado no habilita transportar sus datos. */
    public function test_rechaza_registro_anulado(): void
    {
        $this->prepararControl(['estado' => 'ANULADA']);
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException SQLite alcanza la guarda del adaptador; PostgreSQL impide antes el estado mediante su FK compuesta. */
    public function test_rechaza_atencion_de_otro_residente(): void
    {
        $this->prepararControl();
        DB::table('residentes')->insert(['cod_residente' => 'SINTETICO-OTRO', 'nombres' => 'Otro caso ficticio',
            'apellido_paterno' => 'Prueba', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        if (DB::getDriverName() === 'pgsql') {
            // El savepoint permite comprobar el estado posterior al rechazo SQL
            // sin dejar abortada la transacción de aislamiento de la prueba.
            try {
                DB::transaction(static fn () => DB::table('atenciones')->where('cod_atencion', 'SIM-AT')
                    ->update(['cod_residente' => 'SINTETICO-OTRO']));
                self::fail('La FK compuesta debe impedir mezclar residentes.');
            } catch (QueryException $error) {
                self::assertSame('23503', $error->errorInfo[0]);
            }
            self::assertSame('SINTETICO-RES', DB::table('atenciones')->where('cod_atencion', 'SIM-AT')->value('cod_residente'));

            return;
        }
        DB::table('atenciones')->where('cod_atencion', 'SIM-AT')->update(['cod_residente' => 'SINTETICO-OTRO']);
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** Dos fuentes del mismo episodio se conservan sin tratarlas como independientes; sin parámetros ni excepciones esperadas. */
    public function test_consultas_por_lote_y_episodio_compartido_no_crean_corroboracion(): void
    {
        $this->prepararControl();
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
        $cantidad = count(DB::connection()->getQueryLog());
        $control = (array) DB::table('controles_cognitivos')->where('cod_control_cognitivo', 'SIM-CTRL')->first();
        $control['cod_control_cognitivo'] = 'SIM-CTRL-2';
        DB::table('controles_cognitivos')->insert($control);
        $evidencia = (array) DB::table('evidencias_evaluacion')->where('cod_evidencia_evaluacion', 'SIM-EVID')->first();
        $evidencia['cod_evidencia_evaluacion'] = 'SIM-EVID-2';
        $evidencia['cod_registro_fuente'] = 'SIM-CTRL-2';
        DB::table('evidencias_evaluacion')->insert($evidencia);
        DB::connection()->flushQueryLog();
        $memoria = $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertCount(2, $memoria->evidencias);
        self::assertCount($cantidad, DB::connection()->getQueryLog());
        self::assertFalse($memoria->evidencias['SIM-EVID']->esIndependienteDe($memoria->evidencias['SIM-EVID-2']));
        $this->exigirSoloLecturas();
    }
}
