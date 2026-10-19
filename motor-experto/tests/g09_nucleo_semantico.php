<?php
declare(strict_types=1);

// Verifica G09 usando el motor real D-130, sin Laravel, BDD ni residentes.
spl_autoload_register(static function (string $clase): void {
    $prefix = 'MotorExperto\\';
    if (str_starts_with($clase, $prefix)) {
        require dirname(__DIR__).'/src/motor_experto/'.str_replace('\\','/',substr($clase,strlen($prefix))).'.php';
    }
});

use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental as Catalogo;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental as Paquete;
use MotorExperto\investigacion\g09\EvaluadorG09Experimental as Evaluador;
use MotorExperto\investigacion\g09\VerificadorNucleoG09Experimental as Verificador;

function exigeG09(bool $ok, string $mensaje): void {
    if (!$ok) throw new RuntimeException($mensaje);
}
function entradaG09(string $regla): array {
    return [
        'uso' => 'TEST_ONLY', 'aislado' => true,
        'residente_ficticio' => 'SINTETICO-1',
        'evaluacion' => 'SIM-EV-01',
        'version' => Catalogo::VERSION,
        'criterio' => Catalogo::obtener($regla)['criterio'],
        'fecha_corte' => '2026-10-17',
        'contexto_suficiente' => true,
        'senal_contextual' => true,
        'evidencias' => [[
            'id' => 'E-'.substr($regla,2),
            'regla' => $regla,
            'residente_ficticio' => 'SINTETICO-1',
            'evaluacion' => 'SIM-EV-01',
            'version' => Catalogo::VERSION,
            'fecha' => '2026-10-16',
            'fuente' => 'SIM-APLICACION',
            'episodio' => 'EPISODIO-SIM',
            'procedencia_verificada' => true,
            'instrumento_test_only' => true,
            'tarea_atribuible' => true,
            'confusor_invalidante' => false,
            'dificultad_especifica_test_only' => true,
            'atribucion_test_only' => true,
            'contexto_regla_suficiente' => true,
        ]],
    ];
}

$paquete = Paquete::crear();
exigeG09($paquete->soloPruebasTecnicas, 'Paquete G09 no está segregado');
exigeG09(count($paquete->tabla('reglas_expertas')) === 15, 'No hay 15 reglas en el paquete');
exigeG09(count($paquete->tabla('condiciones_regla_experta')) === 45, 'No hay exactamente 45 condiciones');
exigeG09(count($paquete->tabla('consecuencias_regla_experta')) === 15, 'Consecuencias incompletas');
$pruebas = 4;
$eval = new Evaluador;
$verificador = new Verificador;
foreach (array_keys(Catalogo::todas()) as $regla) {
    $entrada = entradaG09($regla);
    $res = $eval->evaluar($entrada);
    $com = $verificador->cotejar($res);
    exigeG09($com['coincidencia'] === 'COINCIDE', $regla.' no coincide');
    exigeG09(count($com['consecuencias_nucleo']) === 1, $regla.' falta salida D-130');
    $pruebas += 2;

    foreach ([
        'dificultad_especifica_test_only',
        'atribucion_test_only',
        'contexto_regla_suficiente',
    ] as $condicion) {
        $cambio = entradaG09($regla);
        $cambio['evidencias'][0][$condicion] = false;
        $resultado = $eval->evaluar($cambio);
        $cotejo = $verificador->cotejar($resultado);
        exigeG09($cotejo['coincidencia'] === 'COINCIDE', $regla.' discrepancia con '.$condicion);
        exigeG09($cotejo['consecuencias_nucleo'] === [], $regla.' dio soporte sin '.$condicion);
        $pruebas += 2;
    }

    $ausente = entradaG09($regla);
    $ausente['evidencias'] = [];
    $sin = $verificador->cotejar($eval->evaluar($ausente));
    exigeG09($sin['consecuencias_nucleo'] === [], $regla.' infirió por ausencia');
    $pruebas++;

    $invalida = entradaG09($regla);
    $invalida['evidencias'][0]['confusor_invalidante'] = true;
    $sin = $verificador->cotejar($eval->evaluar($invalida));
    exigeG09($sin['consecuencias_nucleo'] === [], $regla.' admitió confusor');
    $pruebas++;
}
$porCriterio = [];
foreach (array_keys(Catalogo::todas()) as $id) {
    $porCriterio[Catalogo::obtener($id)['criterio']][] = $id;
}
foreach ($porCriterio as $criterio => $reglas) {
    $primero = entradaG09($reglas[0]);
    $segundo = entradaG09($reglas[1])['evidencias'][0];
    $segundo['id'] .= '-SEGUNDO';
    $segundo['episodio'] .= '-SEGUNDO';
    $primero['evidencias'][] = $segundo;
    $res = $eval->evaluar($primero);
    exigeG09($res['estado_tecnico'] === 'MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION', $criterio.' perdió segundo soporte');
    $nucleo = $verificador->cotejar($res);
    exigeG09(count($nucleo['consecuencias_nucleo']) === 2, $criterio.' no obtuvo 2 consecuencias del núcleo');
    $pruebas += 2;
}

$conflicto = entradaG09('R-ATE-S01-P01');
$conflicto['contradiccion_documentada'] = true;
$inconcluso = $verificador->cotejar($eval->evaluar($conflicto));
exigeG09($inconcluso['coincidencia'] === 'NO_COMPARABLE_POR_CONTRADICCION', 'No preservó abstención en contradicción');
$pruebas++;

try {
    $c = entradaG09('R-ATE-S01-P01');
    $c['uso'] = 'ASISTENCIAL';
    $verificador->cotejar($c);
    throw new RuntimeException('Verificador admitió uso clínico');
} catch (DomainException $esperada) {
    $pruebas++;
}
echo 'PASS: '.$pruebas." aserciones contra núcleo D-130, 15 reglas/4 criterios TEST_ONLY.\n";
