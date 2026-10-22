<?php

declare(strict_types=1);

// Proceso de prueba aislado: recibe exclusivamente un fixture sintético por stdin.
require dirname(__DIR__, 2).'/vendor/autoload.php';

use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;

$intentos = [];
spl_autoload_register(static function (string $clase) use (&$intentos): void {
    foreach (['MotorExperto\\aplicacion\\', 'MotorExperto\\dominio\\', 'MotorExperto\\adaptadores\\'] as $prefijo) {
        if (str_starts_with($clase, $prefijo)) {
            $intentos[] = $clase;
            throw new RuntimeException('INFERENCIA_BLOQUEADA_EN_LECTURA');
        }
    }
}, true, true);
$entrada = unserialize(stream_get_contents(STDIN), ['allowed_classes' => false]);
try {
    $salida = $entrada['modo'] === 'REPLAY'
        ? InstantaneaEvaluacionInstitucional::verificar($entrada['contenido'], $entrada['huella'])
        : InstantaneaEvaluacionInstitucional::verificarIntegridad($entrada['contenido'], $entrada['huella']);
    echo json_encode(['contenido_identico' => $entrada['contenido'] === $salida->contenido,
        'huella' => $salida->huella, 'persistido' => $salida->contenido['persistido'], 'intentos_inferencia' => $intentos], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    echo json_encode(['error' => get_class($e), 'intentos_inferencia' => $intentos], JSON_THROW_ON_ERROR);
    exit(12);
}
