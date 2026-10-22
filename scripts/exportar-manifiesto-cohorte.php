<?php

declare(strict_types=1);

use Database\Seeders\CohorteCognitivaInvestigacionSeeder;

require dirname(__DIR__).'/vendor/autoload.php';

// Exporta únicamente definiciones de ingeniería. No arranca Laravel ni conecta una BDD.
if ($argc !== 2 || trim($argv[1]) === '') {
    throw new RuntimeException('Uso: php scripts/exportar-manifiesto-cohorte.php directorio-de-evidencias');
}
$destino = $argv[1];
if (! is_dir($destino) && ! mkdir($destino, 0770, true) && ! is_dir($destino)) {
    throw new RuntimeException('No se pudo crear el directorio de evidencias.');
}
$manifiesto = CohorteCognitivaInvestigacionSeeder::manifiesto();
if (file_put_contents($destino.'/manifiesto.json', json_encode($manifiesto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL) === false) {
    throw new RuntimeException('No se pudo guardar el manifiesto.');
}
$csv = fopen($destino.'/matriz-casos.csv', 'wb');
if ($csv === false) {
    throw new RuntimeException('No se pudo abrir la matriz.');
}
try {
    $escribir = static function (array $fila) use ($csv): void {
        if (fputcsv($csv, $fila, ';', '"', '') === false) {
            throw new RuntimeException('No se pudo completar la matriz.');
        }
    };
    $escribir(['caso', 'nombres', 'apellido_paterno', 'apellido_materno', 'procedencia', 'edad', 'escolaridad_anios', 'caida_contextual', 'diagnostico_contextual',
        'variante', 'memoria', 'atencion', 'ejecutiva', 'lenguaje', 'visuoespacial', 'patron_esperado',
        'estado_esperado', 'c02_esperado', 'referencias']);
    foreach (CohorteCognitivaInvestigacionSeeder::casos() as $caso) {
        $e = $manifiesto['aceptacion'][$caso['caso']];
        $escribir([$caso['caso'], ...array_values($caso['identidad']), $manifiesto['procedencia'], $caso['edad'], $caso['escolaridad_anios'],
            $caso['caida'] ? 'SI' : 'NO', $caso['diagnostico_contextual'], $caso['variante'],
            ...array_values($e['dominios_esperados']), $e['patron_esperado'], $e['estado_esperado'],
            $e['c02_esperado'], implode(' | ', $e['referencias'])]);
    }
} finally {
    fclose($csv);
}
echo "Manifiesto y matriz exportados: 30 casos sintéticos; cero datos clínicos consultados.\n";
