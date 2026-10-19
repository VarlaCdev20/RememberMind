<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Adapters;

use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorInstrumentos;
use DateTimeZone;
use DomainException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

/**
 * TÍTULO:
 * Integración instrumental sin baremos inventados.
 *
 * CAPA:
 * Infrastructure - Adapters.
 *
 * ¿PARA QUÉ SIRVE?
 * Recupera datos brutos autorizados sin interpretación clínica.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Aplicaciones y reactivos completamente ficticios, con versiones y mapeos explícitos.
 *
 * ¿QUÉ HACE?
 * Prueba campo directo, selector y todas sus referencias físicas con Eloquent real.
 *
 * ¿QUÉ DEVUELVE?
 * Valores brutos, NULL y procedencia original; fallos explícitos ante incompatibilidad.
 *
 * ¿QUÉ NO DEBE HACER?
 * No reproduce instrumentos reales ni atribuye dominios desde totales o preguntas.dominio.
 */
final class AdaptadorInstrumentosTest extends AdaptadoresTestCase
{
    /** El total cero es un hecho bruto, sin puntuación cognitiva; sin parámetros ni excepciones esperadas. */
    public function test_total_cero_se_transporta_como_hecho_sin_interpretacion(): void
    {
        $this->prepararInstrumento();
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $memoria = $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame((string) DB::table('aplicaciones_instrumento')->where('cod_aplicacion', 'SIM-APL')->value('puntaje_total'),
            $memoria->hechos['SIM-EVID']->valor);
        self::assertSame([], $memoria->evidencias);
        self::assertSame('SIM-APL', $memoria->hechos['SIM-EVID']->origenId);
        $this->exigirSoloLecturas();
    }

    /** El total NULL continúa NULL y la versión ausente no se completa; sin parámetros ni excepciones esperadas. */
    public function test_total_y_version_null_no_reciben_valores_por_defecto(): void
    {
        $this->prepararInstrumento(version: null);
        DB::table('aplicaciones_instrumento')->update(['puntaje_total' => null]);
        $adaptador = new AdaptadorInstrumentos;
        $origen = $adaptador->recuperar($this->evaluacion(), ['SIM-APL'], self::corte(), $this->conexionPruebas(), new DateTimeZone('America/La_Paz'))->first();

        self::assertNull($adaptador->procedencia($origen)['version']);
        self::assertSame('SIM-INST', $adaptador->procedencia($origen)['instrumento']);
        self::assertNull($this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte())->hechos['SIM-EVID']->valor);
    }

    /**
     * Una respuesta existente se conserva con el tipo de su columna configurada.
     *
     * @param  string  $campo  Columna autorizada de la respuesta.
     * @param  string|bool|null  $valor  Valor original.
     * @param  string|bool|null  $esperado  Valor tipado esperado sin baremación.
     * @return void Sin excepciones esperadas.
     */
    #[DataProvider('respuestasBrutas')]
    public function test_selector_conserva_null_false_cero_y_decimal(string $campo, string|bool|null $valor, string|bool|null $esperado): void
    {
        $this->prepararInstrumento([$campo => $valor]);
        $adaptador = $this->selector($campo);
        $memoria = $this->repositorioAdaptado($adaptador)->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame($esperado, $memoria->hechos['SIM-EVID']->valor);
        self::assertSame($valor === null ? HallazgoInstrumentalEnum::DESCONOCIDO : HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO,
            $memoria->evidencias['SIM-EVID']->hallazgo);
        self::assertSame('SIM-INST', $memoria->evidencias['SIM-EVID']->instrumentoId);
    }

    /** @return iterable<string, array{string, string|bool|null, string|bool|null}> Casos físicos independientes; sin parámetros ni excepciones. */
    public static function respuestasBrutas(): iterable
    {
        yield 'texto NULL' => ['valor_texto', null, null];
        yield 'cero textual' => ['valor_texto', '0', '0'];
        yield 'booleano NULL' => ['valor_logico', null, null];
        yield 'false explícito' => ['valor_logico', false, false];
        yield 'true explícito' => ['valor_logico', true, true];
        yield 'decimal' => ['valor_numero', '12.3456', '12.3456'];
    }

    /** Una opción se conserva por su PK y no se puntúa automáticamente; sin parámetros ni excepciones esperadas. */
    public function test_selector_opcion_preserva_fk_y_procedencia_sin_usar_su_puntaje(): void
    {
        $this->prepararInstrumento(['cod_opcion' => 'SIM-OPC']);
        $adaptador = $this->selector('cod_opcion');
        $memoria = $this->repositorioAdaptado($adaptador)->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
        $origen = $adaptador->recuperar($this->evaluacion(), ['SIM-APL'], self::corte(), $this->conexionPruebas(), new DateTimeZone('America/La_Paz'))->first();
        $referencia = $adaptador->procedencia($origen);

        self::assertSame('SIM-OPC', $memoria->hechos['SIM-EVID']->valor);
        self::assertSame('SIM-ED1', $referencia['version']);
        self::assertSame([['respuesta' => 'SIM-RESP', 'pregunta' => 'SIM-PREG', 'opcion' => 'SIM-OPC']], $referencia['respuestas']);
    }

    /** @throws RuntimeException La ausencia de fila es distinta de una respuesta con valor NULL. */
    public function test_selector_sin_respuesta_original_no_devuelve_null_ficticio(): void
    {
        $this->prepararInstrumento();
        $adaptador = $this->selector();
        DB::table('respuestas_instrumento')->delete();
        $this->expectException(RuntimeException::class);
        $this->repositorioAdaptado($adaptador)->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException Un selector no habilitado no se interpreta como uno conocido. */
    public function test_selector_no_registrado_se_rechaza_antes_de_consultar_aplicaciones(): void
    {
        $this->prepararInstrumento();
        $this->selector();
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado(new AdaptadorInstrumentos)->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /**
     * Una versión formal distinta o ausente no habilita el selector.
     *
     * @param  string|null  $version  Versión física no coincidente.
     *
     * @throws DomainException Rechazo esperado sin elegir una versión por defecto.
     */
    #[DataProvider('versionesIncompatibles')]
    public function test_selector_exige_la_version_formal_exacta(?string $version): void
    {
        $this->prepararInstrumento(version: $version);
        $adaptador = $this->selector();
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado($adaptador)->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @return iterable<string, array{string|null}> Versiones incompatibles; sin parámetros ni excepciones. */
    public static function versionesIncompatibles(): iterable
    {
        yield 'ausente' => [null];
        yield 'otra edición' => ['SIM-ED2'];
    }

    /** @throws DomainException La pregunta de otro instrumento no es una respuesta válida de esta aplicación. */
    public function test_rechaza_pregunta_de_otro_instrumento(): void
    {
        $this->prepararInstrumento();
        DB::table('instrumentos')->insert(['cod_instrumento' => 'SIM-INST-2', 'codigo' => 'SIM-INST-2',
            'nombre' => 'Otro instrumento ficticio', 'tipo' => 'PRUEBA_TECNICA', 'estado' => 'ACTIVO']);
        DB::table('preguntas_instrumento')->where('cod_pregunta', 'SIM-PREG')->update(['cod_instrumento' => 'SIM-INST-2']);
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @throws DomainException Una opción no se reutiliza para otra pregunta. */
    public function test_rechaza_opcion_de_otra_pregunta(): void
    {
        $this->prepararInstrumento(['cod_opcion' => 'SIM-OPC']);
        DB::table('preguntas_instrumento')->insert(['cod_pregunta' => 'SIM-PREG-2', 'cod_instrumento' => 'SIM-INST',
            'codigo' => 'SIM-PREG-2', 'enunciado' => 'Otra pregunta artificial.', 'tipo_respuesta' => 'OPCION', 'orden' => 2, 'estado' => 'ACTIVO']);
        DB::table('opciones_pregunta')->where('cod_opcion', 'SIM-OPC')->update(['cod_pregunta' => 'SIM-PREG-2']);
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /**
     * Solo el estado operacional COMPLETA habilita una aplicación.
     *
     * @param  string  $estado  Estado incompatible.
     *
     * @throws DomainException Rechazo esperado tanto del adaptador como del mapper.
     */
    #[DataProvider('estadosInvalidos')]
    public function test_no_cambia_el_estado_de_la_aplicacion_para_habilitarla(string $estado): void
    {
        $this->prepararInstrumento();
        DB::table('aplicaciones_instrumento')->update(['estado' => $estado]);
        $this->expectException(DomainException::class);
        $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());
    }

    /** @return iterable<string, array{string}> Estados no admitidos; sin parámetros ni excepciones. */
    public static function estadosInvalidos(): iterable
    {
        yield 'anulada' => ['ANULADA'];
        yield 'alias de otra fuente' => ['VIGENTE'];
    }

    /** No carga preguntas/respuestas de una aplicación futura; sin parámetros ni excepciones esperadas. */
    public function test_instrumento_posterior_al_corte_no_carga_sus_respuestas(): void
    {
        $this->prepararInstrumento();
        DB::table('aplicaciones_instrumento')->update(['fecha_hora' => '2026-10-17 12:00:01']);
        DB::connection()->enableQueryLog();
        DB::connection()->flushQueryLog();
        $memoria = $this->repositorioAdaptado()->paraEvaluacion('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::corte());

        self::assertSame([], $memoria->hechos);
        foreach (DB::connection()->getQueryLog() as $consulta) {
            self::assertStringNotContainsString('from "respuestas_instrumento"', $consulta['query']);
        }
    }
}
