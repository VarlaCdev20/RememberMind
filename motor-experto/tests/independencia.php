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
foreach (['dificultad', 'preservacion', 'mixto', 'contexto'] as $case) {
    $result = $demo->ejecutar($case);
    if ($result['uso'] !== 'PRUEBA_TECNICA' || empty($result['traza'])) {
        throw new RuntimeException('Resultado tecnico incompatible.');
    }
}
try {
    $demo->ejecutar('cobertura');
    throw new RuntimeException('Se acepto cobertura inconsistente.');
} catch (DomainException $error) {
    if (! str_contains($error->getMessage(), 'inconsistencia de cobertura')) {
        throw $error;
    }
}
foreach (get_declared_classes() as $class) {
    if (str_starts_with($class, 'Illuminate\\') || str_starts_with($class, 'App\\')) {
        throw new RuntimeException('El motor cargo clases de Laravel.');
    }
}
echo "PASS: cinco casos tecnicos sin Laravel ni Eloquent.\n";
