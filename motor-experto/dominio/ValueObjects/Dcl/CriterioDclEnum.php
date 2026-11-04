<?php

declare(strict_types=1);

namespace MotorExperto\dominio\ValueObjects\Dcl;

/** Identifica los cinco requisitos; su existencia no demuestra su cumplimiento. */
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
