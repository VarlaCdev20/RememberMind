<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'MotorExperto\\';
    if (str_starts_with($class, $prefix)) {
        require dirname(__DIR__).'/src/motor_experto/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
    }
});

use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental as Catalogo;
use MotorExperto\investigacion\g09\PerfilInstrumentalG08Experimental as G08;
use MotorExperto\investigacion\g09\AdaptadorInstrumentalG08G09Experimental as Adaptador;
use MotorExperto\investigacion\g09\EvaluadorG09Experimental as Motor;

function exige(bool $ok, string $motivo): void {
    if (!$ok) {
        throw new RuntimeException($motivo);
    }
}

function fixture(string $id): array {
    $r = Catalogo::obtener($id);
    $p = G08::paraRegla($id);
    $q = array_key_first($p['preguntas_requeridas']);
    $aplicacion = 'SIM-AP-'.$r['componente'];
    return [
        'contexto' => [
            'uso' => 'TEST_ONLY', 'aislado' => true,
            'residente_ficticio' => 'SINTETICO-INDIVIDUO-01',
            'evaluacion' => 'SINTETICO-EV-01',
            'version' => Catalogo::VERSION, 'criterio' => $r['criterio'],
            'fecha_corte' => '2026-10-17', 'contexto_suficiente' => true,
            'senal_contextual' => true, 'evidencias' => [
                ['id' => 'ATAQUE_INYECCION', 'regla' => $id],
            ],
        ],
        'captura' => [
            'fixture_sintetico' => true,
            'aplicacion' => [
                'cod_aplicacion' => $aplicacion,
                'cod_instrumento' => $p['instrumento'],
                'cod_residente' => 'SINTETICO-INDIVIDUO-01',
                'fecha_aplicacion' => '2026-10-16',
                'estado' => 'COMPLETA',
            ],
            'instrumento' => [
                'cod_instrumento' => $p['instrumento'],
                'version' => $p['version_instrumento'],
            ],
            'preguntas' => [
                $q => ['cod_instrumento' => $p['instrumento'], 'tipo_respuesta' => 'TEXTO'],
            ],
            'opciones' => [],
            'respuestas' => [
                ['cod_respuesta' => 'SIM-R-01', 'cod_aplicacion' => $aplicacion,
                 'cod_pregunta' => $q, 'valor_texto' => 'FALLO_SIMULADO'],
            ],
        ],
    ];
}

$adaptador = new Adaptador();
$motor = new Motor();
$pruebas = 0;
foreach (array_keys(Catalogo::todas()) as $regla) {
    $f = fixture($regla);
    $c = $adaptador->preparar($regla, $f['contexto'], $f['captura']);
    exige(count($c['evidencias']) === 1, $regla.': no construyó evidencia');
    exige($c['evidencias'][0]['id'] !== 'ATAQUE_INYECCION', $regla.': aceptó evidencia del llamador');
    $r = $motor->evaluar($c);
    exige($r['estado_tecnico'] === 'HALLAZGO_ACOTADO', $regla.': fallo puente');
    exige(str_starts_with($r['evaluabilidad'],'EV-'), $regla.': compuerta ausente');
    $pruebas += 3;

    $neg = fixture($regla);
    $neg['captura']['respuestas'][0]['valor_texto'] = 'SIN_HALLAZGO_SIMULADO';
    $r = $motor->evaluar($adaptador->preparar($regla, $neg['contexto'], $neg['captura']));
    exige($r['estado_tecnico'] === 'SIN_EVIDENCIA_ADMISIBLE', $regla.': inferencia por ausencia');
    $pruebas++;

    $sinRespuesta = fixture($regla);
    $sinRespuesta['captura']['respuestas'] = [];
    $r = $motor->evaluar($adaptador->preparar($regla,$sinRespuesta['contexto'],$sinRespuesta['captura']));
    exige($r['estado_tecnico'] === 'SIN_EVIDENCIA_ADMISIBLE', $regla.': aceptó respuesta faltante');
    $pruebas++;

    $version = fixture($regla);
    $version['captura']['instrumento']['version'] = 'EDICION_REAL_PROHIBIDA';
    $r = $motor->evaluar($adaptador->preparar($regla,$version['contexto'],$version['captura']));
    exige($r['estado_tecnico'] === 'SIN_EVIDENCIA_ADMISIBLE', $regla.': aceptó edición ajena');
    $pruebas++;

    $fecha = fixture($regla);
    $fecha['captura']['aplicacion']['fecha_aplicacion'] = '2026-10-18';
    $r = $motor->evaluar($adaptador->preparar($regla,$fecha['contexto'],$fecha['captura']));
    exige($r['estado_tecnico'] === 'SIN_EVIDENCIA_ADMISIBLE', $regla.': aceptó futuro');
    $pruebas++;

    $contexto = fixture($regla);
    $contexto['contexto']['contexto_suficiente'] = false;
    $r = $motor->evaluar($adaptador->preparar($regla,$contexto['contexto'],$contexto['captura']));
    exige($r['estado_tecnico'] === 'SOLO_SENAL_CONTEXTO', $regla.': ignoró falta de contexto');
    $pruebas++;

    $noFixture = fixture($regla);
    $noFixture['captura']['fixture_sintetico'] = false;
    try {
        $adaptador->preparar($regla,$noFixture['contexto'],$noFixture['captura']);
        throw new RuntimeException($regla.': aceptó fuente no sintética');
    } catch (DomainException $esperada) { $pruebas++; }

    try {
        G08::perfilClinico($regla);
        throw new RuntimeException($regla.': inventó perfil clínico');
    } catch (DomainException $esperada) { $pruebas++; }
}
echo "PASS G08-G09: ".$pruebas." aserciones de 15 reglas con entrada instrumental sintética; sin perfiles clínicos.\n";
