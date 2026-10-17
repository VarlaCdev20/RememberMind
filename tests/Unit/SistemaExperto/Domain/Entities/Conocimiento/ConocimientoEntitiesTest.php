<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Entities\Conocimiento;

use App\SistemaExperto\Domain\Entities\Conocimiento\NodoSemantico;
use App\SistemaExperto\Domain\Entities\Conocimiento\RedSemantica;
use App\SistemaExperto\Domain\Entities\Conocimiento\ReglaExperta;
use App\SistemaExperto\Domain\Entities\Conocimiento\RelacionSemantica;
use App\SistemaExperto\Domain\ValueObjects\Conocimiento\OperadorCondicionEnum;
use App\SistemaExperto\Domain\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Identidad versionada, grafo explícito y condiciones declarativas.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Entidades y sus invariantes.
 *
 * RESPONSABILIDAD:
 * Verificar constructores y comportamiento local de la Fase B.
 *
 * ENTRADAS:
 * Escenarios artificiales sin datos de residentes ni instrumentos reales.
 *
 * SALIDAS:
 * Aserciones de conservación, rechazo e inmutabilidad.
 *
 * RESTRICCIONES:
 * No inicia Laravel, no accede a BDD ni acredita validación clínica.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase B, documentación obligatoria e invariantes R1–R10.
 */
final class ConocimientoEntitiesTest extends TestCase
{
    /**
     * Consulta exclusivamente la arista existente y preserva sus nodos.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_red_conserva_aristas_directas_sin_inventar_la_inversa(): void
    {
        $a = new NodoSemantico('N-A', 'V-1', 'MEM-S01', 'Aprendizaje', 'SUBCOMPONENTE', 'Concepto documental.');
        $b = new NodoSemantico('N-B', 'V-1', 'COG-MEM', 'Memoria', 'CRITERIO', 'Concepto documental.');
        $r = new RelacionSemantica('R-1', $a, TipoRelacionSemanticaEnum::SUBCOMPONENTE_DE, $b);
        $red = new RedSemantica('V-1', [$a, $b], [$r]);
        self::assertSame($a, $red->nodo('N-A'));
        self::assertSame([$r], $red->salientes('N-A'));
        self::assertSame([], $red->salientes('N-B'));
        self::assertSame([], $red->salientes('N-A', TipoRelacionSemanticaEnum::PARTE_DE));
    }

    /**
     * Rechaza una arista entre versiones sin crear compatibilidad implícita.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_relacion_rechaza_versiones_diferentes(): void
    {
        $a = new NodoSemantico('A', 'V-1', 'C-A', 'A', 'CONCEPTO', 'Definición.');
        $b = new NodoSemantico('B', 'V-2', 'C-B', 'B', 'CONCEPTO', 'Definición.');
        $this->expectException(DomainException::class);
        new RelacionSemantica('R', $a, TipoRelacionSemanticaEnum::PARTE_DE, $b);
    }

    /**
     * Rechaza un nodo incompleto antes de incorporarlo a una red.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_nodo_rechaza_identidad_vacia(): void
    {
        $this->expectException(DomainException::class);
        new NodoSemantico('', 'V-1', 'C-A', 'A', 'CONCEPTO', 'Definición.');
    }

    /**
     * Impide dos identidades locales para el mismo concepto de una versión.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_red_rechaza_codigo_semantico_duplicado(): void
    {
        $a = new NodoSemantico('A', 'V-1', 'CODIGO', 'A', 'CONCEPTO', 'Definición.');
        $b = new NodoSemantico('B', 'V-1', 'CODIGO', 'B', 'CONCEPTO', 'Definición.');
        $this->expectException(DomainException::class);
        new RedSemantica('V-1', [$a, $b], []);
    }

    /**
     * Impide recontar la misma arista bajo otro ID.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_red_rechaza_arista_duplicada(): void
    {
        $a = new NodoSemantico('A', 'V-1', 'C-A', 'A', 'CONCEPTO', 'Definición.');
        $b = new NodoSemantico('B', 'V-1', 'C-B', 'B', 'CONCEPTO', 'Definición.');
        $r = new RelacionSemantica('R1', $a, TipoRelacionSemanticaEnum::PARTE_DE, $b);
        $otra = new RelacionSemantica('R2', $a, TipoRelacionSemanticaEnum::PARTE_DE, $b);
        $this->expectException(DomainException::class);
        new RedSemantica('V-1', [$a, $b], [$r, $otra]);
    }

    /**
     * Impide que la relación introduzca nodos ausentes de la instantánea.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_red_rechaza_extremo_no_incorporado(): void
    {
        $a = new NodoSemantico('A', 'V-1', 'C-A', 'A', 'CONCEPTO', 'Definición.');
        $b = new NodoSemantico('B', 'V-1', 'C-B', 'B', 'CONCEPTO', 'Definición.');
        $r = new RelacionSemantica('R', $a, TipoRelacionSemanticaEnum::PARTE_DE, $b);
        $this->expectException(DomainException::class);
        new RedSemantica('V-1', [$a], [$r]);
    }

    /**
     * Conserva datos declarativos aunque el array original cambie por referencia.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_regla_conserva_version_y_rompe_referencias_externas(): void
    {
        $valor = 'LITERAL-ORIGINAL';
        $comparacion = ['operador' => OperadorCondicionEnum::IGUAL_A, 'variable' => 'VAR-A', 'valor' => &$valor];
        $arbol = ['operador' => OperadorCondicionEnum::Y, 'condiciones' => [$comparacion]];
        $regla = new ReglaExperta('R-1', 'V-1', $arbol, ['concepto' => 'HALLAZGO', 'valor' => 'DESCRIPTIVO'], false, 'SIM-FUENTE');
        $valor = 'LITERAL-CAMBIADO';
        self::assertSame('LITERAL-ORIGINAL', $regla->condiciones['condiciones'][0]['valor']);
        self::assertSame('V-1', $regla->versionConocimiento);
        self::assertFalse($regla->habilitada);
    }

    /**
     * Rechaza expresiones en cadenas sin ejecutar código.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_regla_rechaza_texto_como_condicion_hija(): void
    {
        $this->expectException(DomainException::class);
        new ReglaExperta('R', 'V-1', ['operador' => OperadorCondicionEnum::Y, 'condiciones' => ['expresion()']],
            ['concepto' => 'H', 'valor' => true], false, 'SIM-FUENTE');
    }

    /**
     * Evita usar ausencia como condición positiva o consecuencia.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_regla_rechaza_literal_null(): void
    {
        $this->expectException(DomainException::class);
        new ReglaExperta('R', 'V-1', ['operador' => OperadorCondicionEnum::IGUAL_A, 'variable' => 'VAR', 'valor' => null],
            ['concepto' => 'H', 'valor' => true], false, 'SIM-FUENTE');
    }

    /**
     * Rechaza un ciclo de referencias mediante el límite técnico de profundidad.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_regla_rechaza_arbol_ciclico(): void
    {
        $arbol = ['operador' => OperadorCondicionEnum::NO];
        $arbol['condicion'] = &$arbol;
        $this->expectException(DomainException::class);
        new ReglaExperta('R', 'V-1', $arbol, ['concepto' => 'H', 'valor' => true], false, 'SIM-FUENTE');
    }

    /**
     * Impide que conocimiento incompleto o ambiguo entre al evaluador posterior.
     *
     * @param array<string, mixed> $condicion Árbol artificial mal formado.
     * @return void
     * @throws DomainException Rechazo esperado, sin evaluar el árbol recibido.
     */
    #[DataProvider('condicionesInvalidas')]
    public function test_regla_rechaza_gramatica_incompleta_o_ambigua(array $condicion): void
    {
        $this->expectException(DomainException::class);
        new ReglaExperta('SIM-R', 'SIM-V1', $condicion,
            ['concepto' => 'SIM-HALLAZGO', 'valor' => true], true, 'SIM-FUENTE');
    }

    /**
     * Aporta errores estructurales que no deben recibir defaults inferenciales.
     *
     * @return iterable<string, array{array<string, mixed>}> Árboles negativos.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function condicionesInvalidas(): iterable
    {
        $atomo = ['operador' => OperadorCondicionEnum::IGUAL_A, 'variable' => 'SIM-VAR', 'valor' => true];
        yield 'sin operador' => [[]];
        yield 'operador sin tipar' => [array_replace($atomo, ['operador' => 'IGUAL_A'])];
        yield 'variable inválida' => [array_replace($atomo, ['variable' => 'SIM VAR'])];
        yield 'clave desconocida' => [$atomo + ['normalizarAusencia' => true]];
        yield 'conjunción vacía' => [['operador' => OperadorCondicionEnum::Y, 'condiciones' => []]];
        yield 'disyunción vacía' => [['operador' => OperadorCondicionEnum::O, 'condiciones' => []]];
        yield 'hijas sin lista' => [['operador' => OperadorCondicionEnum::Y, 'condiciones' => ['primera' => $atomo]]];
        yield 'negación sin hija' => [['operador' => OperadorCondicionEnum::NO]];
        yield 'negación de texto' => [['operador' => OperadorCondicionEnum::NO, 'condicion' => 'SIM-VAR']];
    }

    /**
     * Conserva literales falsy en una regla compuesta sin tratarlos como ausencias.
     *
     * @param string|int|float|bool $valor Literal conocido con tipo explícito.
     * @return void
     *
     * No se esperan excepciones; construir la regla no ejecuta sus condiciones.
     */
    #[DataProvider('literalesConocidos')]
    public function test_regla_conserva_tipos_en_composiciones_y_consecuencias(string|int|float|bool $valor): void
    {
        $arbol = ['operador' => OperadorCondicionEnum::O, 'condiciones' => [
            ['operador' => OperadorCondicionEnum::NO, 'condicion' => [
                'operador' => OperadorCondicionEnum::DISTINTO_DE, 'variable' => 'SIM-VAR', 'valor' => $valor,
            ]],
            ['operador' => OperadorCondicionEnum::IGUAL_A, 'variable' => 'SIM-OTRA', 'valor' => $valor],
        ]];
        $regla = new ReglaExperta('SIM-R', 'SIM-V1', $arbol,
            ['concepto' => 'SIM-HALLAZGO', 'valor' => $valor], false, 'SIM-FUENTE');

        self::assertSame($valor, $regla->condiciones['condiciones'][0]['condicion']['valor']);
        self::assertSame($valor, $regla->condiciones['condiciones'][1]['valor']);
        self::assertSame($valor, $regla->consecuencia['valor']);
        self::assertFalse($regla->habilitada);
    }

    /**
     * Define datos conocidos que una comprobación de truthiness perdería.
     *
     * @return iterable<string, array{string|int|float|bool}> Literales distintos.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function literalesConocidos(): iterable
    {
        yield 'booleano false' => [false];
        yield 'entero cero' => [0];
        yield 'decimal cero' => [0.0];
        yield 'cadena cero' => ['0'];
    }

    /**
     * Evita que una consecuencia mal formada publique una conclusión aparente.
     *
     * @param array<string, mixed> $consecuencia Salida declarativa inválida.
     * @return void
     * @throws DomainException Rechazo esperado antes de publicar conocimiento.
     */
    #[DataProvider('consecuenciasInvalidas')]
    public function test_regla_rechaza_consecuencia_desconocida_o_no_finita(array $consecuencia): void
    {
        $this->expectException(DomainException::class);
        new ReglaExperta('SIM-R', 'SIM-V1',
            ['operador' => OperadorCondicionEnum::IGUAL_A, 'variable' => 'SIM-VAR', 'valor' => true],
            $consecuencia, false, 'SIM-FUENTE');
    }

    /**
     * Proporciona salidas ausentes, no escalares y ambiguas.
     *
     * @return iterable<string, array{array<string, mixed>}> Consecuencias negativas.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function consecuenciasInvalidas(): iterable
    {
        yield 'valor ausente' => [['concepto' => 'SIM-H', 'valor' => null]];
        yield 'número infinito' => [['concepto' => 'SIM-H', 'valor' => INF]];
        yield 'colección como valor' => [['concepto' => 'SIM-H', 'valor' => []]];
        yield 'clave desconocida' => [['concepto' => 'SIM-H', 'valor' => true, 'diagnostico' => true]];
    }

    /**
     * Protege la consecuencia de cambios posteriores en referencias del llamador.
     *
     * @return void
     *
     * No recibe parámetros; no se esperan excepciones con los datos ficticios.
     */
    public function test_consecuencia_no_cambia_al_mutar_su_array_de_origen(): void
    {
        $concepto = 'SIM-ORIGINAL';
        $valor = false;
        $regla = new ReglaExperta('SIM-R', 'SIM-V1',
            ['operador' => OperadorCondicionEnum::IGUAL_A, 'variable' => 'SIM-VAR', 'valor' => true],
            ['concepto' => &$concepto, 'valor' => &$valor], false, 'SIM-FUENTE');
        $concepto = 'SIM-CAMBIADO';
        $valor = true;

        self::assertSame(['concepto' => 'SIM-ORIGINAL', 'valor' => false], $regla->consecuencia);
    }

}
