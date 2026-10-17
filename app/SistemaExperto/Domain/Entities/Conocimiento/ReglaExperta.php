<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Entities\Conocimiento;

use App\SistemaExperto\Domain\ValueObjects\Conocimiento\OperadorCondicionEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de conocimiento (Reglas declarativas).
 *
 * Describe qué condiciones y consecuencia pertenecen a una versión del conocimiento.
 * Conserva la regla como datos inmutables para que un servicio pueda evaluarla.
 *
 * TÍTULO: Regla versionada expresada como datos tipados.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de conocimiento.
 *
 * RESPONSABILIDAD:
 * Separar identidad, condiciones, consecuencia, fuente y habilitación técnica.
 *
 * ENTRADAS:
 * Árbol de condiciones de la gramática interna, consecuencia escalar y versión.
 *
 * SALIDAS:
 * Conocimiento inmutable preparado para un evaluador posterior.
 *
 * RESTRICCIONES:
 * No ejecuta cadenas, no contiene reglas G09 fijas ni activa uso clínico.
 * La gramática es propuesta interna de la PoC y no cambia D-130 vigente.
 * Profundidad máxima 32: límite técnico contra estructuras cíclicas o abusivas.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R8/R10; operadores provisionales de la Fase A.
 */
final readonly class ReglaExperta
{
    /** @var array<string, mixed> Árbol validado y copiado sin referencias externas. */
    public array $condiciones;

    /** @var array{concepto: string, valor: string|int|float|bool} Consecuencia declarativa. */
    public array $consecuencia;

    /**
     * Construye conocimiento declarativo sin ejecutar sus condiciones.
     *
     * @param string $id Identidad de regla.
     * @param string $versionConocimiento Versión propietaria.
     * @param array<string, mixed> $condiciones Árbol tipado de condiciones.
     * @param array{concepto: string, valor: string|int|float|bool} $consecuencia Salida declarada.
     * @param bool $habilitada Habilitación técnica explícita, nunca autorización clínica.
     * @param string $fuente Fuente documental identificable.
     * @throws DomainException Por forma, literal, identidad o profundidad inválidos.
     */
    public function __construct(
        /** Identidad única de la regla. */ public string $id,
        /** Versión del conocimiento. */ public string $versionConocimiento,
        array $condiciones,
        array $consecuencia,
        /** Habilitación técnica explícita. */ public bool $habilitada,
        /** Procedencia documental. */ public string $fuente,
    ) {
        foreach ([$id, $versionConocimiento] as $token) {
            if ($token === '' || preg_match('/[\x00-\x20\x7F]/', $token) === 1) {
                throw new DomainException('Identidad o versión de regla inválida.');
            }
        }
        if (trim($fuente) === '' || ! self::tieneClaves($consecuencia, ['concepto', 'valor'])
            || ! is_string($consecuencia['concepto']) || $consecuencia['concepto'] === ''
            || preg_match('/[\x00-\x20\x7F]/', $consecuencia['concepto']) === 1) {
            throw new DomainException('La regla requiere fuente y consecuencia explícitas.');
        }
        $this->condiciones = self::copiarCondicion($condiciones, 0);
        $this->consecuencia = ['concepto' => $consecuencia['concepto'], 'valor' => self::literal($consecuencia['valor'])];
    }

    /**
     * Valida y reconstruye el árbol para romper referencias externas de arrays.
     *
     * IGUAL_A/DISTINTO_DE: operador, variable y valor conocido.
     * Y/O: operador y lista no vacía de condiciones. NO: operador y condición.
     * Ninguna rama evalúa valores ni completa ausencias.
     *
     * @param array<string, mixed> $condicion Nodo declarativo.
     * @param int $profundidad Profundidad técnica acumulada.
     * @return array<string, mixed> Copia validada del nodo.
     * @throws DomainException Por operador, forma, literal o profundidad inválidos.
     */
    private static function copiarCondicion(array $condicion, int $profundidad): array
    {
        $operador = $condicion['operador'] ?? null;
        if ($profundidad > 32 || ! $operador instanceof OperadorCondicionEnum) {
            throw new DomainException('Condición sin operador tipado o demasiado profunda.');
        }
        if (in_array($operador, [OperadorCondicionEnum::IGUAL_A, OperadorCondicionEnum::DISTINTO_DE], true)) {
            if (! self::tieneClaves($condicion, ['operador', 'variable', 'valor'])
                || ! is_string($condicion['variable']) || $condicion['variable'] === ''
                || preg_match('/[\x00-\x20\x7F]/', $condicion['variable']) === 1) {
                throw new DomainException('Comparación declarativa incompleta.');
            }

            return ['operador' => $operador, 'variable' => $condicion['variable'], 'valor' => self::literal($condicion['valor'])];
        }
        if ($operador === OperadorCondicionEnum::NO) {
            if (! self::tieneClaves($condicion, ['operador', 'condicion']) || ! is_array($condicion['condicion'])) {
                throw new DomainException('Negación declarativa inválida.');
            }

            return ['operador' => $operador, 'condicion' => self::copiarCondicion($condicion['condicion'], $profundidad + 1)];
        }
        if (! self::tieneClaves($condicion, ['operador', 'condiciones']) || ! is_array($condicion['condiciones'])
            || ! array_is_list($condicion['condiciones']) || $condicion['condiciones'] === []) {
            throw new DomainException('Composición lógica sin condiciones explícitas.');
        }
        $hijas = [];
        foreach ($condicion['condiciones'] as $hija) {
            if (! is_array($hija)) {
                throw new DomainException('Una condición hija no puede ser texto ejecutable.');
            }
            $hijas[] = self::copiarCondicion($hija, $profundidad + 1);
        }

        return ['operador' => $operador, 'condiciones' => $hijas];
    }

    /**
     * Admite solamente valores escalares conocidos, sin coerción.
     *
     * @param mixed $valor Literal recibido de la estructura declarativa.
     * @return string|int|float|bool Literal original.
     * @throws DomainException Por null, objeto, array o número no finito.
     */
    private static function literal(mixed $valor): string|int|float|bool
    {
        if (! is_scalar($valor) || (is_float($valor) && ! is_finite($valor))) {
            throw new DomainException('El literal de una regla debe ser conocido y escalar.');
        }

        return $valor;
    }

    /**
     * Verifica forma cerrada sin depender del orden de las claves.
     *
     * @param array<mixed> $datos Estructura candidata.
     * @param list<string> $claves Claves exactas exigidas.
     * @return bool Coincidencia estructural, sin interpretación del contenido.
     *
     * No lanza excepciones.
     */
    private static function tieneClaves(array $datos, array $claves): bool
    {
        $actuales = array_keys($datos);
        sort($actuales);
        sort($claves);

        return $actuales === $claves;
    }

}
