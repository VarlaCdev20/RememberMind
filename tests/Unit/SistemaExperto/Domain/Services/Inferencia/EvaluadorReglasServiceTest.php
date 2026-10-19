<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Inferencia;

use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\Services\Inferencia\EvaluadorReglasService;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\EstadoReglaEnum as R;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum as O;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

/**
 * TÍTULO: Lógica trivalente y soportes originales de reglas.
 * CAPA: Domain (pruebas).
 * SUBSISTEMA: Inferencia.
 * RESPONSABILIDAD: Probar ausencia, comparación estricta, composición y habilitación.
 * ENTRADAS: Hechos sintéticos con null, false, cero, conflicto y valores conocidos.
 * SALIDAS: Estados y árboles realmente producidos por el evaluador.
 * RESTRICCIONES: No ejecuta texto, SQL ni pruebas clínicas.
 * TRAZABILIDAD: R1/R3/R4/R8/R10; gramática de ReglaExperta.
 */
final class EvaluadorReglasServiceTest extends TestCase
{
    /** @return iterable<string, array> Casos que diferencian ausencia, tipo y valor. */
    public static function comparaciones(): iterable
    {
        yield 'null no es false' => [null, false, R::DESCONOCIDO];
        yield 'false conocido' => [false, false, R::CUMPLE];
        yield 'cero conocido' => [0, 0, R::CUMPLE];
        yield 'cero no es false' => [0, false, R::NO_CUMPLE];
        yield 'texto no es entero' => ['1', 1, R::NO_CUMPLE];
        yield 'valor desigual conocido' => ['NO', 'SI', R::NO_CUMPLE];
    }

    /** @param mixed $valor Original. @param mixed $esperado Literal declarado. @param R $estado Oráculo. */
    #[DataProvider('comparaciones')]
    public function test_comparacion_estricta_conserva_valor_y_origen(mixed $valor, mixed $esperado, R $estado): void
    {
        $h = E::hecho(['concepto' => 'V', 'valor' => $valor]);
        $m = E::memoria([], [$h]);
        $salida = (new EvaluadorReglasService)->evaluar($this->regla(['operador' => O::IGUAL_A, 'variable' => 'V', 'valor' => $esperado]), $m, [$h]);
        self::assertSame($estado, $salida['estado']);
        self::assertSame([$h], $salida['condiciones']['soportes']);
        self::assertSame($valor, $h->valor);
        self::assertSame($estado === R::CUMPLE ? ['concepto' => 'SALIDA', 'valor' => 'DESCRIPTIVO'] : null, $salida['consecuencia']);
    }

    /** @return iterable<string, array{O, ?bool, ?bool, R}> Tabla externa de composición trivalente. */
    public static function composiciones(): iterable
    {
        yield 'Y verdadero desconocido' => [O::Y, true, null, R::DESCONOCIDO];
        yield 'Y falso desconocido' => [O::Y, false, null, R::NO_CUMPLE];
        yield 'Y conocidos' => [O::Y, true, true, R::CUMPLE];
        yield 'O verdadero desconocido' => [O::O, true, null, R::CUMPLE];
        yield 'O falso desconocido' => [O::O, false, null, R::DESCONOCIDO];
        yield 'O conocidos negativos' => [O::O, false, false, R::NO_CUMPLE];
    }

    /** @param O $op Composición. @param ?bool $a Primer original. @param ?bool $b Segundo original. @param R $estado Resultado esperado. */
    #[DataProvider('composiciones')]
    public function test_composicion_no_convierte_desconocido_en_false(O $op, ?bool $a, ?bool $b, R $estado): void
    {
        $ha = E::hecho(['id' => 'A', 'origenId' => 'A', 'concepto' => 'A', 'valor' => $a]);
        $hb = E::hecho(['id' => 'B', 'origenId' => 'B', 'concepto' => 'B', 'valor' => $b]);
        $m = E::memoria([], [$ha, $hb]);
        $regla = $this->regla(['operador' => $op, 'condiciones' => [
            ['operador' => O::IGUAL_A, 'variable' => 'A', 'valor' => true],
            ['operador' => O::IGUAL_A, 'variable' => 'B', 'valor' => true]]]);
        $salida = (new EvaluadorReglasService)->evaluar($regla, $m, [$ha, $hb]);
        self::assertSame($estado, $salida['estado']);
        self::assertCount(2, $salida['condiciones']['ramas']);
    }

    /** Negación de ausencia y desigualdad no prueban el hecho contrario por omisión. */
    public function test_negaciones_sin_dato_se_abstienen_y_valor_conocido_es_evaluable(): void
    {
        $regla = $this->regla(['operador' => O::NO, 'condicion' => ['operador' => O::DISTINTO_DE, 'variable' => 'V', 'valor' => 0]]);
        $evaluador = new EvaluadorReglasService;
        self::assertSame(R::DESCONOCIDO, $evaluador->evaluar($regla, E::memoria(), [])['estado']);
        $h = E::hecho(['concepto' => 'V', 'valor' => 0]);
        self::assertSame(R::CUMPLE, $evaluador->evaluar($regla, E::memoria([], [$h]), [$h])['estado']);
    }

    /** Conflictos explícitos de una variable permanecen desconocidos, sin prioridad por orden. */
    public function test_declaraciones_competidoras_no_se_resuelven_por_primera_coincidencia(): void
    {
        $a = E::hecho(['id' => 'A', 'origenId' => 'A', 'concepto' => 'V', 'valor' => true]);
        $b = E::hecho(['id' => 'B', 'origenId' => 'B', 'concepto' => 'V', 'valor' => false]);
        $m = E::memoria([], [$a, $b]);
        $salida = (new EvaluadorReglasService)->evaluar($this->regla(['operador' => O::IGUAL_A, 'variable' => 'V', 'valor' => true]), $m, [$b, $a]);
        self::assertSame(R::DESCONOCIDO, $salida['estado']);
        self::assertSame('DECLARACIONES_CONTRADICTORIAS', $salida['condiciones']['motivo']);
        self::assertSame([$a, $b], $salida['soportes']);
    }

    /** Una regla deshabilitada no ejecuta condiciones ni atribuye consecuencia. */
    public function test_regla_inactiva_permanece_bloqueada(): void
    {
        $r = new ReglaExperta('R', 'SIM-V1', ['operador' => O::IGUAL_A, 'variable' => 'V', 'valor' => true],
            ['concepto' => 'SALIDA', 'valor' => 'DESCRIPTIVO'], false, 'Fuente artificial');
        $salida = (new EvaluadorReglasService)->evaluar($r, E::memoria(), []);
        self::assertSame(R::BLOQUEADA, $salida['estado']);
        self::assertNull($salida['condiciones']);
        self::assertNull($salida['consecuencia']);
    }

    /** Rechaza un objeto equivalente que sustituye al original de memoria. */
    public function test_no_admite_sustituir_un_original(): void
    {
        $h = E::hecho();
        $this->expectException(DomainException::class);
        (new EvaluadorReglasService)->evaluar($this->regla(['operador' => O::IGUAL_A, 'variable' => 'OBS', 'valor' => true]), E::memoria([], [$h]), [clone $h]);
    }

    /** @param array $condicion Árbol explícito. @return ReglaExperta Regla ficticia válida, sin efectos. */
    private function regla(array $condicion): ReglaExperta
    {
        return new ReglaExperta('SIM-R', 'SIM-V1', $condicion, ['concepto' => 'SALIDA', 'valor' => 'DESCRIPTIVO'], true, 'SIM-FUENTE');
    }
}
