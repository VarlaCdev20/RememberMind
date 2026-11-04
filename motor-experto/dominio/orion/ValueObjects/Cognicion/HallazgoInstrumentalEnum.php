<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Cognicion;

/** Distingue hallazgos descriptivos, desconocimiento y resultados no interpretables. */
enum HallazgoInstrumentalEnum: string
{
    /** Hallazgo descriptivo aislado; no acredita por sí mismo un estado A/P. */
    case HALLAZGO_DESCRIPTIVO = 'HALLAZGO_DESCRIPTIVO';

    /** Referencia externa ficticia de alteración; no es diagnóstico ni resultado global. */
    case ALTERACION_OBJETIVA_SIMULADA = 'ALTERACION_OBJETIVA_SIMULADA';

    /** Referencia externa ficticia positiva de rendimiento esperado, no falta de hallazgo. */
    case RENDIMIENTO_ESPERADO_SIMULADO = 'RENDIMIENTO_ESPERADO_SIMULADO';

    /** No hay información que permita caracterizar el hallazgo. */
    case DESCONOCIDO = 'DESCONOCIDO';

    /** Existe una entrada, pero sus condiciones impiden interpretarla. */
    case NO_INTERPRETABLE = 'NO_INTERPRETABLE';

    /** Se conservan interpretaciones incompatibles pendientes de adjudicación. */
    case CONTRADICTORIO = 'CONTRADICTORIO';

}
