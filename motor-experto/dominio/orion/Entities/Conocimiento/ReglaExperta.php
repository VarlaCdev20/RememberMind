<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Conocimiento;

use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum;
use DomainException;

/** Define condiciones y consecuencia como datos; no ejecuta expresiones de texto. */
final readonly class ReglaExperta
{
    /** @var array<string, mixed> Árbol validado y copiado sin referencias externas. */
    public array $condiciones;

    /** @var array{concepto: string, valor: string|int|float|bool} Consecuencia declarativa. */
    public array $consecuencia;

    /**
     * Construye conocimiento declarativo sin ejecutar sus condiciones.
     * @param string $id
     * @param string $versionConocimiento
     * @param array<string, mixed> $condiciones
     * @param array{concepto: string, valor: string|int|float|bool} $consecuencia
     * @param bool $habilitada
     * @param string $fuente
     * @throws DomainException
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
     * @param array<string, mixed> $condicion
     * @param int $profundidad
     * @return array<string, mixed>
     * @throws DomainException
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
     * @param mixed $valor
     * @return string|int|float|bool
     * @throws DomainException
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
     * @param array<mixed> $datos
     * @param list<string> $claves
     * @return bool
     */
    private static function tieneClaves(array $datos, array $claves): bool
    {
        $actuales = array_keys($datos);
        sort($actuales);
        sort($claves);

        return $actuales === $claves;
    }

}
