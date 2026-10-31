<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\Residente;
use App\Models\User;
use Carbon\CarbonImmutable;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;

/** Preparación backend autorizada; no es una operación de guardado ni un endpoint para descargar historia. */
final readonly class PrepararEvaluacionInstitucional
{
    public function __construct(private ConsultarConocimientoCognitivo $instrumental, private ConsultarPetersenInstitucional $petersen,
        private LecturaCognitivaConsistente $lectura) {}

    public function preparar(User $usuario, Residente $residente, CarbonImmutable $corte): InstantaneaEvaluacionInstitucional
    {
        return $this->lectura->consultar($usuario, $residente, function (User $usuario, Residente $residente) use ($corte): InstantaneaEvaluacionInstitucional {
            $instrumental = $this->instrumental->preparar($usuario, $residente, $corte);
            $petersen = $this->petersen->preparar($usuario, $residente, $corte, $instrumental->contenido['contexto']['version'], $instrumental);

            return InstantaneaEvaluacionInstitucional::crear($instrumental, $petersen);
        }, exigirOriginales: true);
    }
}
