<?php
declare(strict_types=1);

/**
 * Prueba de cinco dominios, datos ficticios únicamente.
 * Reutiliza el contrato de tablas instrumentales en SQLite :memory:.
 * DCL debe abstenerse ante cualquier combinación de salidas sintéticas.
 */
spl_autoload_register(static function (string $clase): void {
    $prefix = 'MotorExperto\\';
    if (str_starts_with($clase, $prefix)) {
        require dirname(__DIR__).'/src/motor_experto/'.str_replace('\\','/',substr($clase,strlen($prefix))).'.php';
    }
});

use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental as Catalogo;
use MotorExperto\investigacion\g09\PerfilInstrumentalG08Experimental as PerfilG08;
use MotorExperto\investigacion\g09\LectorSQLiteG08Experimental as Lector;
use MotorExperto\investigacion\g09\EjecutorPerfilG09Experimental as G09;
use MotorExperto\investigacion\dcl\OrquestadorCincoDominiosExperimental as Orquestador;

function exigeCinco(bool $si, string $porQue): void {
    if (!$si) throw new RuntimeException($porQue);
}
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
foreach ([
    'CREATE TABLE instrumentos (cod_instrumento TEXT PRIMARY KEY, version TEXT)',
    'CREATE TABLE aplicaciones_instrumento (cod_aplicacion TEXT PRIMARY KEY,
     cod_instrumento TEXT, cod_residente TEXT, fecha_hora TEXT, estado TEXT)',
    'CREATE TABLE preguntas_instrumento (cod_pregunta TEXT PRIMARY KEY,
     cod_instrumento TEXT, tipo_respuesta TEXT)',
    'CREATE TABLE opciones_pregunta (cod_opcion TEXT PRIMARY KEY, cod_pregunta TEXT)',
    'CREATE TABLE respuestas_instrumento (cod_respuesta TEXT PRIMARY KEY,
     cod_aplicacion TEXT, cod_pregunta TEXT, valor_texto TEXT)',
] as $sql) $db->exec($sql);
$tareas = [];
$insert = [
    $db->prepare('INSERT INTO instrumentos (cod_instrumento,version) VALUES (?,?)'),
    $db->prepare('INSERT INTO aplicaciones_instrumento
        (cod_aplicacion,cod_instrumento,cod_residente,fecha_hora,estado) VALUES (?,?,?,?,?)'),
    $db->prepare('INSERT INTO preguntas_instrumento
        (cod_pregunta,cod_instrumento,tipo_respuesta) VALUES (?,?,?)'),
    $db->prepare('INSERT INTO respuestas_instrumento
        (cod_respuesta,cod_aplicacion,cod_pregunta,valor_texto) VALUES (?,?,?,?)'),
];
foreach (Catalogo::todas() as $regla => $meta) {
    $perfil = PerfilG08::paraRegla($regla);
    $q = array_key_first($perfil['preguntas_requeridas']);
    $app = 'SIM-'.$meta['componente'];
    $insert[0]->execute([$perfil['instrumento'],$perfil['version_instrumento']]);
    $insert[1]->execute([$app,$perfil['instrumento'],'SINTETICO-01','2026-10-16 11:00:00','COMPLETA']);
    $insert[2]->execute([$q,$perfil['instrumento'],'TEXTO']);
    $insert[3]->execute(['SIM-R-'.$meta['componente'],$app,$q,'FALLO_SIMULADO']);
    $tareas[] = ['regla'=>$regla, 'aplicacion'=>$app];
}
$contexto = [
    'uso'=>'TEST_ONLY','aislado'=>true,
    'evaluacion'=>'SIM-EV-01','residente_ficticio'=>'SINTETICO-01',
    'fecha_corte'=>'2026-10-17', 'version'=>Catalogo::VERSION,
    'contexto_suficiente'=>true,
];
$integrador = new Orquestador(new G09(new Lector($db)));
$n=0;
foreach (['literal_A','literal_B','SIN_DATO'] as $memoriaCaso) {
    $resultado = $integrador->ejecutar($contexto,$tareas,$memoriaCaso);
    exigeCinco(count($resultado['perfil'])===5,'No hay cinco dominios');
    exigeCinco(array_keys($resultado['perfil'])===['COG-MEM','COG-ATE','COG-EJE','COG-LEN','COG-VIS'],
        'Orden o identidad de dominios inesperada');
    exigeCinco($resultado['evaluacion']===$contexto['evaluacion'] &&
        $resultado['residente_ficticio']===$contexto['residente_ficticio'] &&
        $resultado['fecha_corte']===$contexto['fecha_corte'],'Identidad/corte no conservados');
    exigeCinco($resultado['perfil']['COG-MEM']['version']==='VER_TEST','Se falseó versión Memoria');
    exigeCinco($resultado['versiones']['G09']===Catalogo::VERSION,'Se falseó versión G09');
    exigeCinco($resultado['clasificacion_dcl']['estado']==='ABSTENCION_INFORMACION_INSUFICIENTE',
        'Se forzó patrón Petersen a partir de motores sintéticos');
    exigeCinco($resultado['clasificacion_dcl']['patron_candidato']===null,
        'Se atribuyó falsamente subtipo DCL');
    exigeCinco($resultado['informe_dcl']['uso']==='PRUEBA_TECNICA_NO_CLINICA',
        'Informe DCL no está rotulado');
    exigeCinco(count($resultado['clasificacion_dcl']['traza']['dominios'])===5,'DCL no recibió 5 entradas');
    foreach ($resultado['perfil'] as $criterio=>$dato) {
        exigeCinco($dato['estado_dcl']==='U',
            'Se convirtió un resultado técnico en A o P para '.$criterio);
        $n++;
    }
    $memResultado=$resultado['perfil']['COG-MEM']['salida']['resultado']['codigo']??null;
    if ($memoriaCaso==='literal_A') exigeCinco($memResultado==='DIFICULTAD_EVIDENCIADA','Memoria A falla');
    if ($memoriaCaso==='literal_B') exigeCinco($memResultado==='SIN_DIFICULTAD_EVIDENCIADA','Memoria B falla');
    if ($memoriaCaso==='SIN_DATO') exigeCinco($memResultado===null,'Ausencia Memoria generó resultado');
    $n+=10;
}

$vacio=$integrador->ejecutar($contexto,[],'SIN_DATO');
exigeCinco($vacio['clasificacion_dcl']['patron_candidato']===null,'Ausencia produjo DCL');
$n++;

$alterado=$contexto;$alterado['uso']='ASISTENCIAL';
try {$integrador->ejecutar($alterado,$tareas,'literal_A');throw new RuntimeException('Admitió caso asistencial');}
catch (DomainException $esperada) {$n++;}

$alterado=$contexto;$alterado['residente_ficticio']='RESIDENTE-REAL';
try {$integrador->ejecutar($alterado,$tareas,'literal_A');throw new RuntimeException('Admitió identidad no ficticia');}
catch (DomainException $esperada) {$n++;}

$alterado=$contexto;$alterado['evaluacion']='SIM-EV-UNA-CADENA-MUY-LARGA';
try {$integrador->ejecutar($alterado,$tareas,'literal_A');throw new RuntimeException('Admitió identificador excesivo');}
catch (DomainException $esperada) {$n++;}

echo 'PASS: '.$n." comprobaciones de 5 dominios + abstención DCL; solo TEST_ONLY.\n";
