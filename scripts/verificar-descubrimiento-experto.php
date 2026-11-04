<?php

declare(strict_types=1);

namespace RememberMind\Herramientas;

use RuntimeException;

/** Exige pruebas descubribles del circuito experto vigente. */
function verificarDescubrimientoExperto(string $contenido): array
{
    if (str_contains($contenido, '<!DOCTYPE')) {
        throw new RuntimeException('El inventario de pruebas no admite DTD.');
    }
    $anterior = libxml_use_internal_errors(true);
    try {
        $xml = simplexml_load_string($contenido, options: LIBXML_NONET | LIBXML_NOBLANKS);
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($anterior);
    }
    if ($xml === false || ($xml->getDocNamespaces()[''] ?? null) !== 'https://xml.phpunit.de/testSuite') {
        throw new RuntimeException('El descubrimiento no produjo un inventario PHPUnit válido.');
    }
    $xml->registerXPathNamespace('p', 'https://xml.phpunit.de/testSuite');
    $descubiertas = [];
    foreach ($xml->xpath('/p:testSuite/p:tests/p:testClass') as $clase) {
        $descubiertas[(string) $clase['name']] = count($clase->children('https://xml.phpunit.de/testSuite')->testMethod);
    }
    $requeridas = ['CircuitoInstrumentalInicialTest', 'RetiradaLaboratorioExpertoTest', 'ExploradorExpertoTest',
        'GobiernoReglasExpertasTest', 'ConocimientoCognitivoInstitucionalTest',
        'CicloObservacionalConservadoTest', 'ConservacionCompletaSQLTest'];
    $resultado = [];
    foreach ($requeridas as $nombre) {
        $cantidad = $descubiertas['Tests\\Feature\\'.$nombre] ?? 0;
        if ($cantidad === 0) {
            throw new RuntimeException('Faltan pruebas ejecutables del circuito experto: '.$nombre);
        }
        $resultado[$nombre] = $cantidad;
    }

    return $resultado;
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    try {
        if (count($argv) !== 3 || ! in_array($argv[2], ['sqlite', 'postgres'], true) || ! is_file($argv[1])) {
            throw new RuntimeException('Uso: php scripts/verificar-descubrimiento-experto.php inventario.xml sqlite|postgres');
        }
        $resultado = verificarDescubrimientoExperto((string) file_get_contents($argv[1]));
        echo 'PASS: '.count($resultado).' grupos expertos y '.array_sum($resultado).' casos descubribles en '.$argv[2].".\n";
    } catch (\Throwable $error) {
        fwrite(STDERR, $error->getMessage()."\n");
        exit(1);
    }
}
