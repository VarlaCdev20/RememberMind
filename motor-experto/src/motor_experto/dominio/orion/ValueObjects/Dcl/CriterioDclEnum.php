<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Dcl;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Clasificación experimental (Vocabulario de requisitos).
 *
 * Identifica DCL-C01 a DCL-C05 y mantiene separados sus significados.
 * La identidad de un requisito permite referenciarlo; su acreditación exige soportes explícitos.
 *
 * TÍTULO: Identidad de los cinco requisitos generales DCL.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Clasificación experimental.
 *
 * RESPONSABILIDAD:
 * Identificar C01–C05 sin confundir su significado con su acreditación.
 *
 * ENTRADAS:
 * Uno de los códigos DCL-C01 a DCL-C05 del contrato experimental.
 *
 * SALIDAS:
 * La identidad tipada de un requisito que deberá examinarse separadamente.
 *
 * RESTRICCIONES:
 * La existencia del código no acredita el requisito. C05 es un control de
 * ingeniería de interpretabilidad, no un criterio clínico universal.
 * C03 considera IADL, nivel previo y causas no cognitivas; C04 no es
 * una conclusión etiológica. No se evalúan personas reales en esta fase.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase A y R7/R9; contrato experimental
 * DCL-PET-20261017-v0.1, sección 2.2. Su estado es investigación
 * y formalización para dictamen clínico, sin ejecución clínica.
 *
 * Las propiedades nativas e inmutables name y value conservan, respectivamente,
 * el identificador PHP del caso y su literal de intercambio. from() rechaza
 * valores ajenos mediante ValueError; tryFrom() devuelve null sin sustituirlo
 * por un estado favorable. Estas operaciones no ejecutan inferencias.
 */
enum CriterioDclEnum: string
{
    /** DCL-C01: cambio cognitivo e historia corroborada. */
    case CAMBIO_COGNITIVO_CORROBORADO = 'DCL-C01';

    /** DCL-C02: alteración cognitiva objetiva de uno o más dominios. */
    case ALTERACION_COGNITIVA_OBJETIVA = 'DCL-C02';

    /** DCL-C03: independencia funcional relativa, IADL y causas de ayuda examinadas. */
    case INDEPENDENCIA_FUNCIONAL_RELATIVA = 'DCL-C03';

    /** DCL-C04: revisión diferencial completada sin impedimento no resuelto. */
    case EVALUACION_DIFERENCIAL = 'DCL-C04';

    /** DCL-C05: interpretabilidad y calidad suficiente para la tarea. */
    case INTERPRETABILIDAD_Y_CALIDAD = 'DCL-C05';

}
