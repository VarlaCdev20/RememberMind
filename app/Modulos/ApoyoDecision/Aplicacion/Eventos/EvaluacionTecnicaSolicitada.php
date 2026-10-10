<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Eventos;

use App\Modulos\ApoyoDecision\Aplicacion\Contratos\SolicitudEvaluacionTecnicaV1;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

/** Preparación de transporte. Ningún flujo clínico publica este evento. */
final readonly class EvaluacionTecnicaSolicitada implements ShouldDispatchAfterCommit
{
    public function __construct(public SolicitudEvaluacionTecnicaV1 $solicitud) {}
}
