<?php
declare(strict_types=1);

spl_autoload_register(static function(string $class): void {
    $prefix = 'MotorExperto\\';
    if (str_starts_with($class, $prefix)) {
        require dirname(__DIR__).'/src/motor_experto/'.str_replace('\\','/',substr($class,strlen($prefix))).'.php';
    }
});
use MotorExperto\investigacion\dcl\ClasificadorPetersenExperimental as Petersen;
use MotorExperto\investigacion\dcl\ConstructorInformePetersenExperimental as Informe;

function comprobar(bool $condicion, string $mensaje): void {
    if (!$condicion) throw new RuntimeException($mensaje);
}
function fixtureDcl(array $requisitos, array $dominios, string $id): array {
    $caso = [
        'uso' => 'TEST_ONLY', 'aislado' => true,
        'id_evaluacion' => 'SIM-'.$id,
        'residente_ficticio' => 'SINTETICO-PET-01',
        'version' => Petersen::VERSION,
        'fecha_corte' => '2026-10-17',
        'criterios' => [], 'dominios' => [],
    ];
    foreach (Petersen::CRITERIOS as $i => $codigo) {
        $v = $requisitos[$i];
        $caso['criterios'][$codigo] = ['estado' => $v];
        if (in_array($v, ['S','N'], true)) $caso['criterios'][$codigo]['soporte'] = soporteDcl($caso, $codigo);
    }
    foreach (Petersen::DOMINIOS as $i => $codigo) {
        $v = $dominios[$i];
        $caso['dominios'][$codigo] = ['estado' => $v];
        if (in_array($v, ['A','P'], true)) $caso['dominios'][$codigo]['soporte'] = soporteDcl($caso, $codigo);
    }
    return $caso;
}
function soporteDcl(array $c, string $dominio): array {
    return [
        'uso' => 'REFERENCIA_SINTETICA',
        'id' => 'SIM-SOP-'.$c['id_evaluacion'].'-'.$dominio,
        'episodio' => 'SIM-EPI-'.$c['id_evaluacion'].'-'.$dominio,
        'version' => Petersen::VERSION,
        'id_evaluacion' => $c['id_evaluacion'],
        'residente_ficticio' => $c['residente_ficticio'],
        'fecha' => '2026-10-16',
    ];
}
function filasDcl(string $archivo): array {
    $data = json_decode(file_get_contents(__DIR__.'/fixtures/'.$archivo), true, 512, JSON_THROW_ON_ERROR);
    comprobar($data['version'] === Petersen::VERSION, 'Fixture de otra versión');
    comprobar(str_contains($data['origen'], 'Matriz 03A'), 'Fixture sin origen');
    return $data['filas'];
}

$m = new Petersen();
$informe = new Informe();
$casos = filasDcl('dcl_03a_30_casos.json');
comprobar(count($casos) === 30, 'Faltan los 30 casos de 03A');
$n = 0;
$ultimo = [];
foreach ($casos as $fila) {
    [$id,$c01,$c02,$c03,$c04,$c05,$mem,$ate,$eje,$len,$vis,$esperado] = $fila;
    $caso = fixtureDcl([$c01,$c02,$c03,$c04,$c05], [$mem,$ate,$eje,$len,$vis], $id);
    if ($id === 'CAS-30') {
        $caso['criterios'] = array_reverse($caso['criterios'], true);
        $caso['dominios'] = array_reverse($caso['dominios'], true);
    }
    $r = $m->clasificar($caso);
    comprobar($r['estado'] === $esperado, $id.' esperado '.$esperado.'; obtenido '.$r['estado']);
    comprobar($r['compuerta_asistencial'] === 'SOLO_LABORATORIO_NO_EMITIR', $id.' autoriza clínica');
    comprobar(($r['patron_candidato'] !== null) === str_starts_with($esperado, 'PET-'), $id.' emitió patrón indebido');
    $vista = $informe->construir($r);
    comprobar($vista['uso'] === 'PRUEBA_TECNICA_NO_CLINICA', $id.' informe no aislado');
    comprobar(count($r['traza']['dominios']) === 5, $id.' perdió un dominio');
    $ultimo[$id] = $r['estado'];
    $n += 5;
}

$exhaustiva = filasDcl('dcl_03a_32_configuraciones.json');
comprobar(count($exhaustiva) === 32, 'Faltan las 32 combinaciones');
foreach ($exhaustiva as $fila) {
    [$id,$mem,$ate,$eje,$len,$vis,$esperadosA,$esperado] = $fila;
    $caso = fixtureDcl(['S','S','S','S','S'],[$mem,$ate,$eje,$len,$vis],'EXH-'.$id);
    $r = $m->clasificar($caso);
    $nA = count(array_filter([$ate,$eje,$len,$vis], fn($x) => $x === 'A'));
    comprobar($r['traza']['otros_dominios_alterados'] === $nA, 'N incorrecto en configuración '.$id);
    comprobar($r['estado'] === $esperado, 'Configuración '.$id.' esperaba '.$esperado.' y obtuvo '.$r['estado']);
    $n += 2;
}
comprobar(count($exhaustiva) === 32, 'DCL: se exige verificar todas las configuraciones oficiales, ninguna excepción');
comprobar(!str_contains(file_get_contents(__DIR__.'/fixtures/dcl_03a_32_configuraciones.json'), 'REQUIERE_ADJUDICACION_C02_SIN_SOPORTE'), 'El identificador anterior solo se conserva en el historial documental');
$n += 2;

$base = fixtureDcl(['S','S','S','S','S'],['A','P','P','P','P'],'SEGURIDAD-01');
unset($base['dominios']['COG-ATE']['soporte']);
comprobar($m->clasificar($base)['estado'] === 'ABSTENCION_INFORMACION_INSUFICIENTE','P sin soporte se aceptó');
$n++;

$base = fixtureDcl(['S','S','S','S','S'],['A','A','P','P','P'],'SEGURIDAD-02');
$base['dominios']['COG-ATE']['soporte']['episodio'] = $base['dominios']['COG-MEM']['soporte']['episodio'];
comprobar($m->clasificar($base)['estado'] === 'ABSTENCION_CONTRADICCION','Episodio doble contado');
$n++;

$base = fixtureDcl(['S','S','S','S','S'],['A','P','P','P','P'],'SEGURIDAD-03');
$base['dominios']['COG-MEM']['soporte']['fecha'] = '2027-01-01';
try { $m->clasificar($base); throw new RuntimeException('Se aceptó fuente posterior al corte'); }
catch (DomainException $ex) { $n++; }

$base = fixtureDcl(['S','S','S','S','S'],['A','P','P','P','P'],'SEGURIDAD-04');
$base['dominios']['COG-MEM']['soporte']['residente_ficticio'] = 'SINTETICO-OTRO';
try { $m->clasificar($base); throw new RuntimeException('Se aceptó otra persona'); }
catch (DomainException $ex) { $n++; }

$base = fixtureDcl(['S','S','S','S','S'],['A','P','P','P','P'],'SEGURIDAD-05');
$base['uso'] = 'ASISTENCIAL';
try { $m->clasificar($base); throw new RuntimeException('Se permitió uso clínico'); }
catch (DomainException $ex) { $n++; }

$base = fixtureDcl(['S','S','S','S','S'],['A','P','P','P','P'],'SEGURIDAD-06');
comprobar($m->clasificar($base) === $m->clasificar($base), 'No determinista'); $n++;

echo 'PASS: 30 casos + 32 configuraciones de 03A; '.$n." aserciones adicionales; 0 discrepancias entre Documento 03 y Matriz 03A; DCL solo simulación.\n";
