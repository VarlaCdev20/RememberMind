<?php

declare(strict_types=1);

// Único punto de entrada del subproceso. No carga Composer, Laravel ni SQL.
$raiz = $argv[1] ?? throw new RuntimeException('Falta raíz restaurada.');
$zip = $argv[2] ?? throw new RuntimeException('Falta ZIP restaurado.');
$hash = $argv[3] ?? throw new RuntimeException('Falta identidad del release.');
spl_autoload_register(static function (string $clase) use ($raiz): void {
    if (str_starts_with($clase, 'MotorExperto\\')) {
        require $raiz.'/motor-experto/src/motor_experto/'.str_replace('\\', '/', substr($clase, strlen('MotorExperto\\'))).'.php';
    }
});
require $raiz.'/adaptador/EntradasCompuestas.php';
$fuente = json_decode(file_get_contents($raiz.'/app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json'), true, flags: JSON_THROW_ON_ERROR);
$entrada = unserialize(stream_get_contents(STDIN), ['allowed_classes' => false]);
$dto = OrionFase02\EntradasCompuestas::reproducir($entrada, $fuente, $zip, $hash);
if ($argc !== 4 && $argc !== 7) {
    throw new RuntimeException('El replay requiere todas las huellas esperadas o ninguna en el ensayo interno.');
}
if ($argc === 7) {
    foreach ([$dto->huella, $dto->contenido['instrumental']['huella'], $dto->contenido['petersen']['huella']] as $i => $actual) {
        if (!preg_match('/^[a-f0-9]{64}$/D', $argv[4+$i]) || !hash_equals($argv[4+$i], $actual)) {
            throw new RuntimeException('El replay no coincide con las tres huellas históricas esperadas.');
        }
    }
}
foreach (get_included_files() as $file) {
    if (! str_starts_with(str_replace('\\', '/', $file), str_replace('\\', '/', $raiz).'/')) {
        throw new RuntimeException('Replay cargó código ajeno al artefacto restaurado.');
    }
}
echo serialize(['contenido' => $dto->contenido, 'huella' => $dto->huella]);
