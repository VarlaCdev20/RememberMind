<?php

use MotorExperto\adaptadores\entrada\DemostracionCOGMEM;
use MotorExperto\adaptadores\entrada\AdaptadorHechosCOGMEM;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM as E;
use MotorExperto\aplicacion\casos_uso\MotorExperto;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;

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
// Regresión D-099/D-131: grupos opuestos que se solapan no prueban
// independencia, aunque uno de los lados tenga un episodio extra.
foreach ([
    'solo_un_lado_exclusivo' => ['literales' => ['literal_A', 'literal_B', 'literal_A'], 'mixto' => false],
    'ambos_lados_exclusivos' => ['literales' => ['literal_A', 'literal_B', 'literal_A', 'literal_B'], 'mixto' => true],
] as $caso => $config) {
    $paquete = E::paquete();
    $memoria = new MemoriaTrabajo(E::contexto());
    $ids = [];
    foreach ($config['literales'] as $i => $literal) {
        $fuente = E::fuente($literal, registro: 'APP_EPISODIO_'.$caso.'_'.$i);
        $e = (new AdaptadorHechosCOGMEM)->adaptar($paquete, $memoria, $fuente, 'MAP_INS');
        $memoria->vincular('MEM', $e->id, 'INSTRUMENTAL');
        $ids[] = $e->id;
    }
    $memoria->relacionar($ids[0], $ids[1], 'MISMO_EPISODIO_CLINICO', 'Un mismo episodio');
    try {
        $evaluacion = (new MotorExperto)->evaluarCOGMEM($paquete, $memoria, 'MEM', E::contextoClinico());
        if (! $config['mixto'] || ($evaluacion['resultado']['codigo'] ?? null) !== 'HALLAZGOS_MIXTOS') {
            throw new RuntimeException("Caso {$caso}: se emitió un resultado contradictorio o inesperado.");
        }
    } catch (DomainException $error) {
        if ($config['mixto'] || ! str_contains($error->getMessage(), 'Soportes opuestos no independientes')) {
            throw $error;
        }
    }
}

foreach (get_declared_classes() as $class) {
    if (str_starts_with($class, 'Illuminate\\') || str_starts_with($class, 'App\\')) {
        throw new RuntimeException('El motor cargó clases de Laravel.');
    }
}
echo "PASS: siete casos técnicos con regresión de episodios, resultados y trazabilidad.\n";
