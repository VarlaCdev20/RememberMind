<?php

declare(strict_types=1);

require __DIR__.'/GuardiaDesechable.php';
$pdo=OrionFase02\GuardiaDesechable::conectar();
$pdo->exec("SET lock_timeout='8s'");
$dato=unserialize(trim(fgets(STDIN)),['allowed_classes'=>false]);
echo $pdo->query('SELECT pg_backend_pid()')->fetchColumn()."\n";
flush();
try{
    $pdo->beginTransaction();
    $f=$dato['fila'];
    $s=$pdo->prepare('INSERT INTO orion_f02.'.$dato['tabla'].'('.implode(',',array_keys($f)).') VALUES('.implode(',',array_fill(0,count($f),'?')).')');
    foreach(array_values($f) as $i=>$v)$s->bindValue($i+1,$v,is_bool($v)?PDO::PARAM_BOOL:($v===null?PDO::PARAM_NULL:(is_int($v)?PDO::PARAM_INT:PDO::PARAM_STR)));
    $s->execute();$pdo->commit();echo "ACEPTADO\n";
}catch(PDOException $e){if($pdo->inTransaction())$pdo->rollBack();echo $e->getCode()."\n";}
