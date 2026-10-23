<?php

declare(strict_types=1);
use OrionFase02\GuardiaDesechable;
use OrionNominal\RepositorioNominal;

require dirname(__DIR__, 2).'/vendor/autoload.php';
require dirname(__DIR__).'/orion-conservacion-0_2/GuardiaDesechable.php';
foreach (['ContratoNominal', 'EsquemaNominal', 'ProyeccionNominal', 'ContenidoCompleto', 'RepositorioNominal'] as $clase) {
    require __DIR__.'/'.$clase.'.php';
}
$intentos = [];
spl_autoload_register(static function (string $clase) use (&$intentos): void {
    foreach (['MotorExperto\\aplicacion\\', 'MotorExperto\\dominio\\', 'MotorExperto\\adaptadores\\'] as $prefijo) {
        if (str_starts_with($clase, $prefijo)) {
            $intentos[] = $clase;
            throw new RuntimeException('No se permite inferencia al recuperar SQL.');
        }
    }
}, true, true);
$codigo = $argv[1] ?? '';
if (! preg_match('/^VARIANTE-[0-9]{1,8}$/D', $codigo)) {
    throw new RuntimeException('El worker sólo lee identidades del corpus sintético.');
}
$db = GuardiaDesechable::conectar();
$mapa = require __DIR__.'/mapa-completo.php';
$dto = (new RepositorioNominal($db, $mapa))->recuperarCompleta($codigo);
echo json_encode(['contenido' => $dto->contenido, 'huella' => $dto->huella, 'intentos_inferencia' => $intentos],
    JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
