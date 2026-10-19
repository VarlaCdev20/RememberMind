<?php

declare(strict_types=1);

// Sin Composer, Laravel, conexión SQL ni fixtures de la aplicación.
spl_autoload_register(static function (string $clase): void {
    $prefijo = 'MotorExperto\\';
    if (str_starts_with($clase, $prefijo)) {
        require dirname(__DIR__).'/src/motor_experto/'.str_replace('\\', '/', substr($clase, strlen($prefijo))).'.php';
    }
});

use MotorExperto\dominio\orion\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use MotorExperto\soporte\conservacion\CodecInstantanea;

$fecha = new DateTimeImmutable('2026-10-18T12:00:00-04:00');
$hecho = new HechoExperto('SIM-H', 'SIM-EVAL', 'SINTETICO', 'SIM-EP', 'SIM-SRC', 'SIM-OR', 'SIM-V1', 'OBS', $fecha, null);
$memoria = new MemoriaTrabajo('SIM-EVAL', 'SINTETICO', 'SIM-V1', $fecha, [$hecho]);
if (count(DominioCognitivoEnum::cases()) !== 5 || count(SubcomponenteCognitivoEnum::cases()) !== 19
    || ! preg_match('/^[a-f0-9]{64}$/D', CodecInstantanea::huella($memoria))) {
    throw new RuntimeException('Contrato puro O.R.I.O.N. incompatible.');
}
foreach (get_declared_classes() as $clase) {
    if (str_starts_with($clase, 'Illuminate\\') || str_starts_with($clase, 'App\\')) {
        throw new RuntimeException('El dominio puro cargó una clase de la aplicación.');
    }
}
echo "PASS: dominio O.R.I.O.N. y huellas sin Laravel, Composer o SQL.\n";
