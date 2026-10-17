<?php
declare(strict_types=1);

/**
 * Ensayo de extremo a extremo: fuentes sintéticas de cinco dominios → puente
 * de interpretación ficticia → Petersen → explicación; ningún paciente real.
 */
require_once __DIR__.'/orion_cinco_dominios_experimental.php';
require_once __DIR__.'/dcl_puente_referencias_experimental.php';

use MotorExperto\investigacion\dcl\IntegradorReferenciasDCLExperimental;
use MotorExperto\investigacion\dcl\ClasificadorPetersenExperimental as Clasificador;

function ajustarReferencias(array $lote, array $contexto): array {
    foreach (['dominios','criterios'] as $categoria) {
        foreach ($lote[$categoria] as &$entradas) {
            if ($categoria === 'dominios') {
                foreach ($entradas as &$ref) {
                    $ref['id_evaluacion'] = $contexto['evaluacion'];
                    $ref['residente_ficticio'] = $contexto['residente_ficticio'];
                }
                unset($ref);
            } else {
                $entradas['id_evaluacion'] = $contexto['evaluacion'];
                $entradas['residente_ficticio'] = $contexto['residente_ficticio'];
            }
        }
        unset($entradas);
    }
    return $lote;
}

function verificarFlujo(bool $ok, string $motivo): void {
    if (!$ok) throw new RuntimeException($motivo);
}

$flujo = new IntegradorReferenciasDCLExperimental($integrador);
$plan = $tareas; // Ejecución de los 15 subcomponentes sintéticos en cada recorrido.
$evaluados = 0;
$pruebasPatrones = [
    ['PET-01',['COG-MEM'=>'A','COG-ATE'=>'P','COG-EJE'=>'P','COG-LEN'=>'P','COG-VIS'=>'P']],
    ['PET-02',['COG-MEM'=>'A','COG-ATE'=>'A','COG-EJE'=>'P','COG-LEN'=>'P','COG-VIS'=>'P']],
    ['PET-03',['COG-MEM'=>'P','COG-ATE'=>'P','COG-EJE'=>'A','COG-LEN'=>'P','COG-VIS'=>'P']],
    ['PET-04',['COG-MEM'=>'P','COG-ATE'=>'A','COG-EJE'=>'A','COG-LEN'=>'P','COG-VIS'=>'P']],
];
foreach ($pruebasPatrones as [$esperado,$estados]) {
    $refs = ajustarReferencias(loteRefs($estados), $contexto);
    $salida = $flujo->evaluar($contexto, $plan, 'literal_A', $refs);
    verificarFlujo(count($salida['perfil']) === 5, 'Perfil no tiene cinco dominios');
    verificarFlujo($salida['clasificacion_dcl']['estado'] === $esperado, $esperado.': salida incorrecta');
    verificarFlujo($salida['clasificacion_dcl']['patron_candidato'] === $esperado, $esperado.': falta patrón');
    verificarFlujo($salida['informe_dcl']['uso'] === 'PRUEBA_TECNICA_NO_CLINICA', 'Informe clínico impropio');
    verificarFlujo($salida['versiones']['PUENTE_DCL'] ===
        \MotorExperto\investigacion\dcl\AdaptadorEstadosPetersenExperimental::VERSION,
        'No registró versión del puente');
    foreach (Clasificador::DOMINIOS as $dominio) {
        verificarFlujo($salida['perfil'][$dominio]['estado_dcl'] === 'U',
            'El puente alteró el resultado descriptivo de '.$dominio);
    }
    verificarFlujo(count($salida['puente_dcl']['auditoria']['dominios']) === 5, 'Trazabilidad incompleta');
    $evaluados += 11;
}

$sinRefs = ajustarReferencias(loteRefs([],false), $contexto);
$abstencion = $flujo->evaluar($contexto, $plan, 'literal_A', $sinRefs);
verificarFlujo($abstencion['clasificacion_dcl']['estado'] === 'ABSTENCION_INFORMACION_INSUFICIENTE',
    'G09 o Memoria generaron DCL sin referencias');
verificarFlujo($abstencion['clasificacion_dcl']['patron_candidato'] === null, 'Falso patrón sin G08');
$evaluados += 2;

$refs = ajustarReferencias(loteRefs($pruebasPatrones[0][1]), $contexto);
$refs['criterios']['DCL-C03']['iadl_observadas'] = false;
$abstencion = $flujo->evaluar($contexto, $plan, 'literal_A', $refs);
verificarFlujo($abstencion['clasificacion_dcl']['patron_candidato'] === null, 'Sin IADL se clasificó');
$evaluados++;

$refs = ajustarReferencias(loteRefs($pruebasPatrones[0][1]), $contexto);
$refs['dominios']['COG-MEM'][] = $refs['dominios']['COG-MEM'][0];
$refs['dominios']['COG-MEM'][1]['id'] = 'SIM-REF-COG-MEM-CONTRARIA';
$refs['dominios']['COG-MEM'][1]['episodio'] = 'SIM-EP-COG-MEM-CONTRARIA';
$refs['dominios']['COG-MEM'][1]['interpretacion'] = 'RENDIMIENTO_ESPERADO_SIMULADO';
$conflicto = $flujo->evaluar($contexto, $plan, 'literal_A', $refs);
verificarFlujo($conflicto['clasificacion_dcl']['estado'] === 'ABSTENCION_CONTRADICCION',
    'Resultados opuestos no obligaron abstención');
$evaluados++;

$refs = ajustarReferencias(loteRefs($pruebasPatrones[0][1]), $contexto);
$refs['dominios']['COG-LEN'][0]['version_conocimiento'] = 'SIM-OTRA-VERSION';
try {
    $flujo->evaluar($contexto, $plan, 'literal_A', $refs);
    throw new RuntimeException('Se mezclaron versiones instrumentales');
} catch (DomainException $esperada) {
    $evaluados++;
}
echo 'PASS: '.$evaluados." comprobaciones de puente completo con 5 dominios, patrones PET y abstenciones TEST_ONLY.\n";
