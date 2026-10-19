<?php
declare(strict_types=1);

spl_autoload_register(static function(string $class): void {
    $pre = 'MotorExperto\\';
    if (str_starts_with($class, $pre)) {
        require dirname(__DIR__).'/src/motor_experto/'.str_replace('\\','/',substr($class,strlen($pre))).'.php';
    }
});

use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental as Catalogo;
use MotorExperto\investigacion\g09\EvaluadorG09Experimental as Evaluador;

function verificar(bool $ok, string $mensaje): void {
    if (!$ok) throw new RuntimeException($mensaje);
}
function caso(string $regla, array $cambios = []): array {
    $r = Catalogo::obtener($regla);
    return array_replace_recursive([
        'uso'=>'TEST_ONLY','aislado'=>true,'residente_ficticio'=>'SINTETICO-1',
        'evaluacion'=>'EXP-001','version'=>Catalogo::VERSION,
        'criterio'=>$r['criterio'],'fecha_corte'=>'2026-10-17',
        'contexto_suficiente'=>true,'senal_contextual'=>true,
        'evidencias'=>[[
            'id'=>'e-01','regla'=>$regla,'residente_ficticio'=>'SINTETICO-1',
            'evaluacion'=>'EXP-001','version'=>Catalogo::VERSION,'fecha'=>'2026-10-16',
            'fuente'=>'FIXTURE-001','episodio'=>'episodio-1',
            'procedencia_verificada'=>true,'instrumento_test_only'=>true,
            'tarea_atribuible'=>true,'confusor_invalidante'=>false,
            'dificultad_especifica_test_only'=>true,'atribucion_test_only'=>true,
            'contexto_regla_suficiente'=>true,
        ]],
    ],$cambios);
}
$e = new Evaluador;
$n=0;
foreach (Catalogo::todas() as $id=>$r) {
    $c=caso($id);
    $res=$e->evaluar($c);
    verificar($res['estado_tecnico']==='HALLAZGO_ACOTADO',$id.' no cumple');
    verificar(isset($res['consecuencias_candidatas'][$r['consecuencia']]),$id.' sin consecuencia');
    $n++;
    foreach (['dificultad_especifica_test_only','atribucion_test_only','contexto_regla_suficiente'] as $campo) {
        $no=caso($id);
        $no['evidencias'][0][$campo]=false;
        verificar($e->evaluar($no)['estado_tecnico']==='EVALUABLE_SIN_SOPORTE',$id.' aceptó '.$campo.' falsa');
        $n++;
    }
    $wrong=caso($id);
    $wrong['evidencias'][0]['version']='OTRA-VERSION';
    verificar($e->evaluar($wrong)['estado_tecnico']==='SOLO_SENAL_CONTEXTO',$id.' permitió otra versión');
    $n++;
    $conf=caso($id);
    $conf['evidencias'][0]['confusor_invalidante']=true;
    verificar($e->evaluar($conf)['estado_tecnico']==='SOLO_SENAL_CONTEXTO',$id.' permitió confusor invalidante');
    $n++;
}
foreach (['COG-ATE','COG-EJE','COG-LEN','COG-VIS'] as $dom) {
    $rs=array_keys(array_filter(Catalogo::todas(),fn($x)=>$x['criterio']===$dom));
    $combo=caso($rs[0]);
    $otra=caso($rs[1])['evidencias'][0];
    $otra['id']='e-02'; $otra['episodio']='episodio-2';
    $combo['evidencias'][]=$otra;
    verificar($e->evaluar($combo)['estado_tecnico']==='MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION',$dom.' seleccionó un componente arbitrario');
    $n++;
}
$c=caso('R-ATE-S01-P01');
$c['uso']='ASISTENCIAL';
try { $e->evaluar($c); throw new RuntimeException('Se admitió uso asistencial'); } catch (DomainException $exc) { $n++; }
$c=caso('R-ATE-S01-P01');
$c['evidencias'][0]['fecha']='2027-01-01';
verificar($e->evaluar($c)['estado_tecnico']==='SOLO_SENAL_CONTEXTO','Se aceptó fuente posterior al corte');$n++;
$c=caso('R-ATE-S01-P01');
$a=$e->evaluar($c);$b=$e->evaluar($c);
verificar($a===$b,'Ejecución no determinista');$n++;
echo "PASS: ".$n." casos experimentales; 15 reglas; sin Laravel/BBDD/clinica.\n";
