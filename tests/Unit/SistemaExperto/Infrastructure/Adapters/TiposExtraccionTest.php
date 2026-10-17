<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Infrastructure\Adapters;

use App\Models\ControlCognitivo;
use App\Models\MapeoVariableFuente;
use App\Models\ValoracionFuncional;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorControlesCognitivos;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorSeguimientosPedagogicos;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorValoracionesFuncionales;
use App\SistemaExperto\Infrastructure\Adapters\MapeoFuentes\ExtractorVariableMapeada;
use DateTimeZone;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO:
 * Tipos físicos y conservación explícita de ausencias.
 *
 * CAPA:
 * Infrastructure - Adapters.
 *
 * ¿PARA QUÉ SIRVE?
 * Recupera datos brutos autorizados sin interpretación clínica.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Atributos originales en modelos Eloquent sin BDD.
 *
 * ¿QUÉ HACE?
 * Verifica NULL, booleanos, textos y decimales sin accessors ni baremos.
 *
 * ¿QUÉ DEVUELVE?
 * Valores tipados exactos y rechazos de datos incompatibles.
 *
 * ¿QUÉ NO DEBE HACER?
 * No ejecuta reglas ni supone normalidad desde NULL o falso.
 */
final class TiposExtraccionTest extends TestCase
{
    /**
     * Conserva NULL y valores conocidos sin defaults.
     *
     * @param  mixed  $valor  Dato físico explícito.
     * @param  string  $tipo  Tipo autorizado del adaptador.
     * @param  mixed  $esperado  Representación tipada.
     * @return void Sin excepciones esperadas.
     */
    #[DataProvider('valoresValidos')]
    public function test_conserva_el_tipo_de_los_valores_fisicos(mixed $valor, string $tipo, mixed $esperado): void
    {
        $modelo = (new ControlCognitivo)->setRawAttributes(['memoria_reciente' => $valor], true);

        self::assertSame($esperado, ExtractorVariableMapeada::valorFisico($modelo, 'memoria_reciente', $tipo));
    }

    /** @return iterable<string, array{mixed, string, mixed}> Valores independientes; sin parámetros ni excepciones. */
    public static function valoresValidos(): iterable
    {
        yield 'texto NULL' => [null, 'texto', null];
        yield 'booleano NULL' => [null, 'booleano', null];
        yield 'decimal NULL' => [null, 'decimal', null];
        yield 'texto cero' => ['0', 'texto', '0'];
        yield 'texto vacío existente' => ['', 'texto', ''];
        yield 'texto literal false' => ['false', 'texto', 'false'];
        yield 'booleano false' => [false, 'booleano', false];
        yield 'booleano true' => [true, 'booleano', true];
        yield 'SQLite cero' => [0, 'booleano', false];
        yield 'SQLite uno' => [1, 'booleano', true];
        yield 'cero textual booleano' => ['0', 'booleano', false];
        yield 'uno textual booleano' => ['1', 'booleano', true];
        yield 'decimal PostgreSQL' => ['12.3400', 'decimal', '12.3400'];
        yield 'decimal SQLite entero' => [0, 'decimal', '0'];
        yield 'decimal SQLite real' => [12.3456, 'decimal', '12.3456'];
    }

    /**
     * Rechaza coerciones que cambiarían el significado.
     *
     * @param  mixed  $valor  Dato incompatible.
     * @param  string  $tipo  Tipo esperado.
     *
     * @throws DomainException Rechazo esperado.
     */
    #[DataProvider('valoresInvalidos')]
    public function test_rechaza_coerciones_ambiguas(mixed $valor, string $tipo): void
    {
        $modelo = (new ControlCognitivo)->setRawAttributes(['memoria_reciente' => $valor], true);
        $this->expectException(DomainException::class);
        ExtractorVariableMapeada::valorFisico($modelo, 'memoria_reciente', $tipo);
    }

    /** @return iterable<string, array{mixed, string}> Incompatibilidades; sin parámetros ni excepciones. */
    public static function valoresInvalidos(): iterable
    {
        yield 'false a texto' => [false, 'texto'];
        yield 'número a texto' => [0, 'texto'];
        yield 'cadena false a booleano' => ['false', 'booleano'];
        yield 'cadena vacía a booleano' => ['', 'booleano'];
        yield 'dos a booleano' => [2, 'booleano'];
        yield 'booleano a decimal' => [false, 'decimal'];
        yield 'cadena vacía a decimal' => ['', 'decimal'];
        yield 'exponente no declarado' => ['1e2', 'decimal'];
        yield 'float infinito' => [INF, 'decimal'];
        yield 'estructura a texto' => [[], 'texto'];
    }

    /** @throws DomainException Una columna no recuperada no representa un NULL físico. */
    public function test_columna_ausente_no_se_sustituye_por_null(): void
    {
        $this->expectException(DomainException::class);
        ExtractorVariableMapeada::valorFisico(new ControlCognitivo, 'memoria_reciente', 'texto');
    }

    /** Evita defaults MEDIO/REGULAR y otros de presentación en funcionalidad; sin parámetros ni excepciones esperadas. */
    public function test_los_adaptadores_no_usan_accessors_de_presentacion(): void
    {
        $original = (new ValoracionFuncional)->setRawAttributes(['nivel_dependencia' => null], true);
        $mapeo = (new MapeoVariableFuente)->setRawAttributes([
            'tipo_extraccion' => 'CAMPO_DIRECTO', 'campo_valor' => 'nivel_dependencia', 'clave_selector' => null,
        ], true);

        self::assertNull((new AdaptadorValoracionesFuncionales)->extraer($original, $mapeo));
        self::assertSame('HECHO', (new AdaptadorValoracionesFuncionales)->tipo($mapeo));
    }

    /**
     * Rechaza cualquier campo/selector fuera de una lista cerrada.
     *
     * @param  string  $modo  Modo declarado.
     * @param  string|null  $campo  Campo declarado.
     * @param  string|null  $selector  Selector declarado.
     *
     * @throws DomainException Rechazo esperado.
     */
    #[DataProvider('mapeosInvalidos')]
    public function test_mapeos_no_soportados_no_ejecutan_lecturas(string $modo, ?string $campo, ?string $selector): void
    {
        $mapeo = (new MapeoVariableFuente)->setRawAttributes([
            'tipo_extraccion' => $modo, 'campo_valor' => $campo, 'clave_selector' => $selector,
        ], true);
        $this->expectException(DomainException::class);
        (new AdaptadorControlesCognitivos)->validarMapeo($mapeo);
    }

    /** @return iterable<string, array{string, string|null, string|null}> Modos sin contrato; sin parámetros ni excepciones. */
    public static function mapeosInvalidos(): iterable
    {
        yield 'campo desconocido' => ['CAMPO_DIRECTO', 'cod_usuario', null];
        yield 'campo SQL' => ['CAMPO_DIRECTO', 'memoria_reciente; SELECT 1', null];
        yield 'selector en campo directo' => ['CAMPO_DIRECTO', 'memoria_reciente', 'SIM-SEL'];
        yield 'modo arbitrario' => ['EVAL', 'memoria_reciente', null];
        yield 'sin campo' => ['CAMPO_DIRECTO', null, null];
        yield 'selector sin soporte' => ['SELECTOR_COMPONENTE', null, 'SIM-SEL'];
    }

    /** @throws DomainException Un seguimiento sin mapeo permitido no recibe un significado por defecto. */
    public function test_seguimiento_no_acepta_columna_clinica_inventada(): void
    {
        $mapeo = (new MapeoVariableFuente)->setRawAttributes([
            'tipo_extraccion' => 'CAMPO_DIRECTO', 'campo_valor' => 'diagnostico_dcl', 'clave_selector' => null,
        ], true);
        $this->expectException(DomainException::class);
        (new AdaptadorSeguimientosPedagogicos)->validarMapeo($mapeo);
    }

    /** @throws DomainException Una fecha imposible no se normaliza al siguiente mes. */
    public function test_fecha_invalida_no_se_normaliza_silenciosamente(): void
    {
        $original = (new ControlCognitivo)->setRawAttributes(['fecha_hora' => '2026-02-30 12:00:00'], true);
        $this->expectException(DomainException::class);
        ExtractorVariableMapeada::fechaOriginal($original, new DateTimeZone('America/La_Paz'));
    }
}
