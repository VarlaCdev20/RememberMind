<?php

declare(strict_types=1);

namespace MotorExperto\conservacion;

/** Identificadores ya conservados; cambiarlos rompería versiones, huellas o registros anteriores. */
final class IdentificadoresHistoricos
{
    public const PERFIL_COGNITIVO = 'ORION-COGNITIVO-1';

    public const PETERSEN = 'ORION-PETERSEN-1';

    public const CONSERVACION = 'ORION-SNAP-2';

    public const DOCUMENTAL_INICIAL = 'ORION-V1-20261008';

    public const PREFIJO_DOCUMENTAL = 'ORION-V1-';

    public const CONTRATO_INSTITUCIONAL = 'ORION-AD-20261019';

    public const MOTOR_PRUEBAS = 'ORION-DOMAIN-EXPERIMENTAL-1';

    public const ARCHIVOS_RELEASE = [
        'motor-experto/tests/orion_independencia.php',
        'app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json',
    ];
}
