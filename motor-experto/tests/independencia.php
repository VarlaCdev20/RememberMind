<?php

use MotorExperto\adaptadores\entrada\DemostracionCOGMEM;

// Ejecutable con PHP 8.3 sin bootstrap Laravel, Composer ni base de datos.
spl_autoload_register(function (string $class): void {
    $prefix = 'MotorExperto\\';
    if (str_starts_with($class, $prefix)) {
        require dirname(__DIR__).'/src/motor_experto/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
    }
});

$demo = new DemostracionCOGMEM;
$esperados = [
    'dificultad' => ['estado' => 'EV-CM-2', 'resultado' => 'DIFICULTAD_EVIDENCIADA'],
    'preservacion' => ['estado' => 'EV-CM-2', 'resultado' => 'SIN_DIFICULTAD_EVIDENCIADA'],
    'mixto' => ['estado' => 'EV-CM-2', 'resultado' => 'HALLAZGOS_MIXTOS'],
    'contexto' => ['estado' => 'EV-CM-1', 'resultado' => null],
];
foreach ($esperados as $caso => $esperado) {
    $resultado = $demo->ejecutar($caso);
    if (($resultado['uso'] ?? null) !== 'PRUEBA_TECNICA' || empty($resultado['traza'])) {
        throw new RuntimeException("Caso {$caso}: falta traza técnica o uso esperado.");
    }
    if (($resultado['evaluabilidad']['estado'] ?? null) !== $esperado['estado']) {
        throw new RuntimeException("Caso {$caso}: estado de evaluabilidad inesperado.");
    }
    if (($resultado['resultado']['codigo'] ?? null) !== $esperado['resultado']) {
        throw new RuntimeException("Caso {$caso}: resultado semántico inesperado.");
    }
    if (($resultado['traza']['compuerta']['estado'] ?? null) !== $esperado['estado']) {
        throw new RuntimeException("Caso {$caso}: evaluabilidad inconsistente con la traza.");
    }
    if (($resultado['traza']['resultado']['codigo'] ?? null) !== $esperado['resultado']) {
        throw new RuntimeException("Caso {$caso}: resultado inconsistente con la traza.");
    }
    if ($esperado['resultado'] === null && ($resultado['evaluabilidad']['habilita_resultado'] ?? null) !== false) {
        throw new RuntimeException("Caso {$caso}: se habilitaron reglas sin evaluabilidad suficiente.");
    }
}
try {
    $demo->ejecutar('cobertura');
    throw new RuntimeException('Se aceptó cobertura inconsistente.');
} catch (DomainException $error) {
    if (! str_contains($error->getMessage(), 'inconsistencia de cobertura')) {
        throw $error;
    }
}
foreach (get_declared_classes() as $class) {
    if (str_starts_with($class, 'Illuminate\\') || str_starts_with($class, 'App\\')) {
        throw new RuntimeException('El motor cargó clases de Laravel.');
    }
}
echo "PASS: cinco casos técnicos con resultados, evaluabilidad, traza y aislamiento de Laravel.\n";
