<?php

declare(strict_types=1);

/** Impide un CI verde cuando un filtro deja fuera una clase del laboratorio integrado. */
if ($argc !== 3 || ! in_array($argv[2], ['sqlite', 'postgres'], true) || ! is_file($argv[1])) {
    fwrite(STDERR, "Uso: php scripts/verificar-pruebas-laboratorio.php listado-phpunit.xml sqlite|postgres\n");
    exit(2);
}

$integracion = ['Tests\\Feature\\AdquisicionPerfilExperimentalTest', 'Tests\\Feature\\ArchivoPerfilExperimentalTest',
    'Tests\\Feature\\GestionConocimientoExperimentalTest', 'Tests\\Feature\\ConsolaPerfilExperimentalTest'];
$esperadas = $argv[2] === 'postgres' ? $integracion : [...$integracion,
    'Tests\\Feature\\LaboratorioExpertoFrontendTest', 'Tests\\Feature\\LaboratorioPerfilInterfazTest',
    'Tests\\Unit\\CatalogoPerfilCincoDominiosTest', 'Tests\\Unit\\ComparadorLongitudinalExperimentalTest',
    'Tests\\Unit\\ContratoG09ExperimentalTest', 'Tests\\Unit\\ControlesTareaG09ExperimentalTest',
    'Tests\\Unit\\EditorPaqueteExperimentalTest', 'Tests\\Unit\\PerfilExperimentalTest',
    'Tests\\Unit\\ReglasDeclarativasPerfilExperimentalTest', 'Tests\\Unit\\ResultadoPerfilExperimentalTest'];

$xml = new DOMDocument;
$erroresAnteriores = libxml_use_internal_errors(true);
$cargado = $xml->load($argv[1], LIBXML_NONET);
libxml_clear_errors();
libxml_use_internal_errors($erroresAnteriores);
if (! $cargado || $xml->documentElement?->localName !== 'testSuite') {
    fwrite(STDERR, "El archivo no es un listado XML de pruebas PHPUnit.\n");
    exit(1);
}
$xpath = new DOMXPath($xml);
$clases = [];
foreach ($xpath->query('/*[local-name()="testSuite"]/*[local-name()="tests"]/*[local-name()="testClass"]') as $clase) {
    if (is_file($clase->getAttribute('file')) && $xpath->query('./*[local-name()="testMethod"]', $clase)->length > 0) {
        $clases[$clase->getAttribute('name')] = true;
    }
}
$ausentes = array_values(array_diff($esperadas, array_keys($clases)));
if ($ausentes !== []) {
    fwrite(STDERR, 'Faltan clases ejecutables del laboratorio: '.implode(', ', $ausentes)."\n");
    exit(1);
}
fwrite(STDOUT, 'Descubrimiento '.$argv[2].': '.count($esperadas)." clases del laboratorio verificadas.\n");
