<?php

declare(strict_types=1);
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;

// Solo código y composer.lock. No inicia Laravel, SQL ni usa .env.
spl_autoload_register(static function (string $clase): void {
    if (str_starts_with($clase, 'MotorExperto\\')) {
        require dirname(__DIR__).'/motor-experto/src/motor_experto/'.str_replace('\\', '/', substr($clase, strlen('MotorExperto\\'))).'.php';
    }
});

if ($argc !== 3) {
    fwrite(STDERR, "Uso: php scripts/preparar-release-orion.php ARCHIVO_NUEVO.zip REVISION_GIT_BASE_COMPLETA\n");
    exit(2);
}
$manifiesto = (new ReleaseMotorInstrumental)->crear(dirname(__DIR__), $argv[1], $argv[2]);
echo 'Release verificado: '.$manifiesto['huella'].'; '.count($manifiesto['archivos'])." archivos de software; sin capturas clínicas.\n";
