<?php
declare(strict_types=1);

spl_autoload_register(static function(string $clase): void {
    $prefix = 'MotorExperto\\';
    if (str_starts_with($clase, $prefix)) {
        require dirname(__DIR__).'/src/motor_experto/'.str_replace('\\', '/', substr($clase, strlen($prefix))).'.php';
    }
});

use MotorExperto\investigacion\dcl\AdaptadorEstadosPetersenExperimental as Puente;
use MotorExperto\investigacion\dcl\ClasificadorPetersenExperimental as Petersen;
use MotorExperto\investigacion\dcl\OrquestadorCincoDominiosExperimental as Orquestador;

function comprobarPuente(bool $estado, string $mensaje): void {
    if (!$estado) throw new RuntimeException($mensaje);
}

function perfilSim(): array {
    $dominios = [];
    foreach (Petersen::DOMINIOS as $dominio) {
        $dominios[$dominio] = ['estado_dcl'=>'U', 'salida'=>['uso'=>'PRUEBA_TECNICA_NO_CLINICA']];
    }
    // Este hallazgo ficticio del motor jamás puede convertirse por sí solo en A.
    $dominios['COG-ATE']['salida']['estado_tecnico'] = 'HALLAZGO_ACOTADO';
    return [
        'uso'=>'PRUEBA_TECNICA_NO_CLINICA',
        'version_orquestacion'=>Orquestador::VERSION_PERFIL,
        'evaluacion'=>'SIM-PUENTE-01',
        'residente_ficticio'=>'SINTETICO-01',
        'fecha_corte'=>'2026-10-17',
        'perfil'=>$dominios,
        'versiones'=>[
            'COG-MEM'=>'VER_TEST',
            'G09'=>'G09-EXP-20261017',
            'DCL'=>Petersen::VERSION,
        ],
    ];
}

function refDominio(string $codigo, string $estado, int $numero=1): array {
    $perfil = perfilSim();
    return [
        'uso'=>'REFERENCIA_INDEPENDIENTE_SINTETICA',
        'id'=>'SIM-REF-'.$codigo.'-'.$numero,
        'episodio'=>'SIM-EP-'.$codigo.'-'.$numero,
        'codigo'=>$codigo,
        'id_evaluacion'=>$perfil['evaluacion'],
        'residente_ficticio'=>$perfil['residente_ficticio'],
        'version_conocimiento'=>$codigo==='COG-MEM'?'VER_TEST':'G09-EXP-20261017',
        'fecha'=>'2026-10-16',
        'origen'=>'REFERENCIA_EXTERNA_SINTETICA_PREDEFINIDA',
        'interpretacion'=>$estado==='A'?'ALTERACION_OBJETIVA_SIMULADA':'RENDIMIENTO_ESPERADO_SIMULADO',
        'evaluabilidad'=>'INTERPRETABLE',
        'confusores'=>'REVISADOS',
        'procedencia'=>'VERIFICADA',
        'norma_aplicable'=>'SIMULADA_VERIFICABLE',
        'instrumento'=>'SIM-PRUEBA-'.$codigo,
        'edicion'=>'SIM-EDICION-01',
        'subprueba'=>'SIM-SUBPRUEBA-'.$codigo,
        'norma_id'=>'SIM-BAREMO-SINTETICO',
        'idioma'=>'SIM-ES',
        'poblacion'=>'SIM-POBLACION-SINTETICA',
        'revisor_independiente'=>'SIM-REVISOR-01',
    ];
}
function refCriterio(string $codigo, string $estado='S'): array {
    $perfil = perfilSim();
    $tipos = [
        'DCL-C01'=>'CAMBIO_LONGITUDINAL_SIMULADO',
        'DCL-C02'=>'OBJETIVIDAD_SIMULADA',
        'DCL-C03'=>'IADL_FUNCIONAL_SIMULADA',
        'DCL-C04'=>'DIFERENCIAL_SIMULADO',
        'DCL-C05'=>'EVALUABILIDAD_SIMULADA',
    ];
    return [
        'uso'=>'REFERENCIA_INDEPENDIENTE_SINTETICA',
        'id'=>'SIM-REF-'.$codigo,
        'episodio'=>'SIM-EP-'.$codigo,
        'codigo'=>$codigo,
        'id_evaluacion'=>$perfil['evaluacion'],
        'residente_ficticio'=>$perfil['residente_ficticio'],
        'version_conocimiento'=>Petersen::VERSION,
        'fecha'=>'2026-10-16',
        'origen'=>'REFERENCIA_EXTERNA_SINTETICA_PREDEFINIDA',
        'tipo_revision'=>$tipos[$codigo],
        'procedencia'=>'VERIFICADA',
        'revisor_independiente'=>'SIM-REVISOR-02',
        'estado'=>$estado,
        'iadl_observadas'=>$codigo==='DCL-C03',
    ];
}
function loteRefs(array $estados, bool $generales=true): array {
    $lote = ['uso'=>'REFERENCIAS_SINTETICAS_INDEPENDIENTES',
        'version_puente'=>Puente::VERSION,'dominios'=>[],'criterios'=>[]];
    foreach ($estados as $codigo=>$estado) {
        if ($estado === 'U') $lote['dominios'][$codigo] = [];
        else $lote['dominios'][$codigo] = [refDominio($codigo,$estado)];
    }
    if ($generales) foreach (Petersen::CRITERIOS as $codigo) {
        $lote['criterios'][$codigo] = refCriterio($codigo);
    }
    return $lote;
}

$puente = new Puente;
$clasificador = new Petersen;
$base = perfilSim();
$n = 0;
$patrones = [
    ['PET-01',['COG-MEM'=>'A','COG-ATE'=>'P','COG-EJE'=>'P','COG-LEN'=>'P','COG-VIS'=>'P']],
    ['PET-02',['COG-MEM'=>'A','COG-ATE'=>'A','COG-EJE'=>'P','COG-LEN'=>'P','COG-VIS'=>'P']],
    ['PET-03',['COG-MEM'=>'P','COG-ATE'=>'P','COG-EJE'=>'A','COG-LEN'=>'P','COG-VIS'=>'P']],
    ['PET-04',['COG-MEM'=>'P','COG-ATE'=>'A','COG-EJE'=>'P','COG-LEN'=>'P','COG-VIS'=>'A']],
];
foreach ($patrones as [$esperado,$estados]) {
    $t = $puente->preparar($base,loteRefs($estados));
    comprobarPuente($t['uso']==='PRUEBA_TECNICA_NO_CLINICA','Puente fuera del laboratorio');
    comprobarPuente($clasificador->clasificar($t['caso'])['estado']===$esperado,$esperado.' falla');
    comprobarPuente(count($t['auditoria']['dominios'])===5,'Falta trazabilidad de cinco dominios');
    $n += 3;
}
$sin = $puente->preparar($base,loteRefs([],false));
comprobarPuente($clasificador->clasificar($sin['caso'])['estado']==='ABSTENCION_INFORMACION_INSUFICIENTE','Inferencia por ausencia');
$n++;

$fallo = loteRefs($patrones[0][1]);
$fallo['dominios']['COG-ATE'][0]['norma_id']='';
$t = $puente->preparar($base,$fallo);
comprobarPuente($t['caso']['dominios']['COG-ATE']['estado']==='I','G08 incompleto no produjo I');
comprobarPuente($clasificador->clasificar($t['caso'])['patron_candidato']===null,'I resultó clasificable');
$n += 2;

$conflicto = loteRefs($patrones[0][1]);
$conflicto['dominios']['COG-MEM'][] = refDominio('COG-MEM','P',2);
$t = $puente->preparar($base,$conflicto);
comprobarPuente($t['caso']['dominios']['COG-MEM']['estado']==='X','No identificó conflicto A/P');
comprobarPuente($clasificador->clasificar($t['caso'])['estado']==='ABSTENCION_CONTRADICCION','X sin abstención');
$n += 2;

$iadl = loteRefs($patrones[0][1]);
$iadl['criterios']['DCL-C03']['iadl_observadas']=false;
$t = $puente->preparar($base,$iadl);
comprobarPuente($t['caso']['criterios']['DCL-C03']['estado']==='U','IADL ausente acreditó C03');
comprobarPuente($clasificador->clasificar($t['caso'])['patron_candidato']===null,'IADL ausente generó PET');
$n += 2;

$diferencial = loteRefs($patrones[0][1]);
$diferencial['criterios']['DCL-C04']['estado']='B';
$t = $puente->preparar($base,$diferencial);
comprobarPuente($clasificador->clasificar($t['caso'])['estado']==='ABSTENCION_EVALUACION_DIFERENCIAL','C04 bloqueado resultó clasificable');
$n++;

$reutilizada = loteRefs($patrones[1][1]);
$reutilizada['dominios']['COG-ATE'][0]['episodio'] = $reutilizada['dominios']['COG-MEM'][0]['episodio'];
$t = $puente->preparar($base,$reutilizada);
comprobarPuente($clasificador->clasificar($t['caso'])['estado']==='ABSTENCION_CONTRADICCION','Episodio duplicado clasificado');
$n++;

$ajena = loteRefs($patrones[0][1]);
$ajena['dominios']['COG-MEM'][0]['residente_ficticio']='SINTETICO-OTRO';
try {
    $puente->preparar($base,$ajena);throw new RuntimeException('Se permitió otro residente');
} catch (DomainException $esperada) { $n++; }

$fecha = loteRefs($patrones[0][1]);
$fecha['dominios']['COG-MEM'][0]['fecha']='2027-10-16';
try {
    $puente->preparar($base,$fecha);throw new RuntimeException('Se permitió fuente posterior');
} catch (DomainException $esperada) { $n++; }

$preasignada = $base;
$preasignada['perfil']['COG-ATE']['estado_dcl']='A';
try {
    $puente->preparar($preasignada,loteRefs($patrones[0][1]));
    throw new RuntimeException('Se permitió A/P preasignado');
} catch (DomainException $esperada) { $n++; }

$clinica = $base; $clinica['uso']='ASISTENCIAL';
try {
    $puente->preparar($clinica,loteRefs($patrones[0][1]));
    throw new RuntimeException('Se permitió uso asistencial');
} catch (DomainException $esperada) { $n++; }

echo 'PASS: '.$n." comprobaciones A/P/U/I/X y compuertas con referencias únicamente sintéticas.\n";
