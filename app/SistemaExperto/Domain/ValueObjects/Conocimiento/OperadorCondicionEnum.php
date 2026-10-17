<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\ValueObjects\Conocimiento;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Motor de inferencia (Operadores declarativos).
 *
 * Define los símbolos de comparación y composición usados por las reglas de la PoC.
 * Cada símbolo describe una operación; no ejecuta expresiones ni resuelve datos ausentes.
 *
 * TÍTULO: Vocabulario técnico de condiciones.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Motor de inferencia.
 *
 * RESPONSABILIDAD:
 * Nombrar operaciones de comparación y composición lógica sin ejecutar texto.
 *
 * ENTRADAS:
 * Un literal técnico explícito de esta propuesta interna de la PoC.
 *
 * SALIDAS:
 * Un operador tipado que un evaluador futuro deberá interpretar y validar.
 *
 * RESTRICCIONES:
 * No contiene eval(), expresiones arbitrarias, umbrales ni reglas clínicas.
 * IGUAL_A y DISTINTO_DE requieren comparación estricta sin coerción de tipos.
 * Y, O y NO deberán conservar el desconocimiento cuando falte evidencia;
 * la negación de un dato ausente nunca demuestra un estado contrario.
 * Declarar un operador no habilita su ejecución en el motor existente.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, R1, R8 y R10. D-130 del motor vigente solo admite
 * condiciones positivas, existenciales y conjuntivas, sin operador genérico.
 * Los cinco literales siguientes son propuestas internas, no códigos oficiales
 * O.R.I.O.N. ni una ampliación autorizada del contrato ejecutable V1.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
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
