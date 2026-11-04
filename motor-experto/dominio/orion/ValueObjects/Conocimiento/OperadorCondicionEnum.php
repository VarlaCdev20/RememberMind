<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Conocimiento;

/** Nombra comparaciones y operaciones lógicas; no ejecuta texto. */
enum OperadorCondicionEnum: string
{
    /** Igualdad estricta entre valores conocidos y semánticamente compatibles. */
    case IGUAL_A = 'IGUAL_A';

    /** Diferencia estricta; requiere valores conocidos, no ausencia de igualdad. */
    case DISTINTO_DE = 'DISTINTO_DE';

    /** Conjunción lógica de condiciones; no equivale a sumar hallazgos. */
    case Y = 'Y';

    /** Disyunción lógica explícita; no define prioridades entre reglas. */
    case O = 'O';

    /** Negación lógica de una condición evaluable, nunca de datos ausentes. */
    case NO = 'NO';

}
