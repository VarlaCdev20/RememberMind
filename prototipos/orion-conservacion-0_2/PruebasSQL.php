<?php

declare(strict_types=1);

namespace OrionFase02;

use PDO;
use PDOException;
use RuntimeException;

final class PruebasSQL
{
    public static function ejecutar(PDO $pdo, array $entrada): void
    {
        $mapa = require __DIR__.'/mapa-cerrado.php';
        $proyeccion = new ProyeccionTipada($pdo, $mapa);
        $negativo = static function (callable $accion, string $estado) use ($pdo, $proyeccion, $entrada): array {
            $pdo->beginTransaction();
            try {
                $pdo->exec("INSERT INTO orion_f02.evaluaciones_compuestas(cod_evaluacion_experta,id_evaluacion_dominio,id_evaluacion_petersen,cod_residente,cod_usuario_ejecucion,cod_release_motor,huella_compuesta,huella_instrumental,huella_petersen,contrato_proyeccion)
                    SELECT 'NEGATIVO','DOMINIO-NEGATIVO',id_evaluacion_petersen,cod_residente,cod_usuario_ejecucion,cod_release_motor,huella_compuesta,huella_instrumental,huella_petersen,contrato_proyeccion FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta='EVAL-F02'");
                $proyeccion->insertar($proyeccion->proyectar($entrada,'NEGATIVO'));
                $accion($pdo);
                throw new RuntimeException('SQL inválido fue aceptado.');
            } catch (PDOException $e) {
                \exigir($e->getCode()===$estado,'SQLSTATE inesperado '.$e->getCode().' (esperado '.$estado.'): '.$e->getMessage());
                return ['sqlstate'=>$e->getCode(),'rechazado'=>true];
            } finally {
                if ($pdo->inTransaction()) $pdo->rollBack();
                \exigir((int)$pdo->query("SELECT count(*) FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta='NEGATIVO'")->fetchColumn()===0,'Quedó evaluación parcial.');
            }
        };
        $modeloCaptura = array_values(array_filter($mapa['modelos'],fn($m)=>$m['ruta']==='/instrumental/capturas/capturas'))[0];
        $tCaptura=$modeloCaptura['tabla'];
        \probar('sql_residente_ajeno',fn()=>$negativo(fn($db)=>$db->exec("UPDATE orion_f02.$tCaptura SET cod_residente_texto='RES-AJENO' WHERE cod_evaluacion_experta='NEGATIVO'"),'23503'));
        \probar('sql_residente_tipo_alternativo_no_elude_fk',fn()=>$negativo(fn($db)=>$db->exec("UPDATE orion_f02.$tCaptura SET cod_residente_tipo='INT',cod_residente_texto=NULL,cod_residente_entero=1 WHERE cod_evaluacion_experta='NEGATIVO'"),'23514'));
        \probar('sql_fk_regla_tipo_alternativo',fn()=>$negativo(fn($db)=>$db->exec("UPDATE orion_f02.copia_condiciones_regla_experta SET cod_regla_experta_tipo='INT',cod_regla_experta_texto=NULL,cod_regla_experta_entero=1 WHERE cod_evaluacion_experta='NEGATIVO'"),'23514'));
        \probar('sql_padre_de_otra_evaluacion',fn()=>$negativo(fn($db)=>$db->exec("UPDATE orion_f02.$tCaptura SET cod_propietario='EVAL-F02',orden=orden+999 WHERE cod_evaluacion_experta='NEGATIVO'"),'23503'));
        \probar('sql_raiz_duplicada',fn()=>$negativo(function($db) use($proyeccion,$entrada) {
            $fila=$proyeccion->proyectar($entrada,'NEGATIVO')[0];$fila['fila']['cod_fila']='OTRA-RAIZ';$proyeccion->insertar([$fila]);
        },'23505'));
        // Los constraints diferidos FK se hacen inmediatos sin disparar el constraint de cierre parcial.
        $inmediatas=static function(PDO $db): void {
            foreach($db->query("SELECT c.conname,t.relname FROM pg_constraint c JOIN pg_class t ON t.oid=c.conrelid JOIN pg_namespace n ON n.oid=t.relnamespace WHERE n.nspname='orion_f02' AND c.contype='f' AND c.condeferrable")->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $db->exec('SET CONSTRAINTS orion_f02."'.$f['conname'].'" IMMEDIATE');
            }
        };
        \probar('sql_regla_de_otra_rama',fn()=>$negativo(function($db) use($inmediatas) {
            $p=$db->query("SELECT cod_regla_experta_texto FROM orion_f02.copia_reglas_expertas WHERE cod_evaluacion_experta='NEGATIVO' AND coleccion='P' LIMIT 1")->fetchColumn();
            $db->prepare("UPDATE orion_f02.copia_condiciones_regla_experta SET cod_regla_experta_texto=? WHERE cod_evaluacion_experta='NEGATIVO' AND coleccion='I'")->execute([$p]);$inmediatas($db);
        },'23503'));
        \probar('sql_condicion_no_pertenece_a_regla',fn()=>$negativo(function($db) use($inmediatas) {
            $r=$db->query("SELECT c.cod_condicion_regla_texto,c.cod_regla_experta_texto,(SELECT r.cod_regla_experta_texto FROM orion_f02.copia_reglas_expertas r WHERE r.cod_evaluacion_experta=c.cod_evaluacion_experta AND r.coleccion=c.coleccion AND r.cod_regla_experta_texto<>c.cod_regla_experta_texto LIMIT 1) AS otra FROM orion_f02.copia_condiciones_regla_experta c WHERE c.cod_evaluacion_experta='NEGATIVO' AND c.coleccion='P' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $db->prepare("INSERT INTO orion_f02.anclajes_condiciones_adquiridas VALUES('ANCLA-CRUZADA','NEGATIVO','P',?,?)")->execute([$r['otra'],$r['cod_condicion_regla_texto']]);$inmediatas($db);
        },'23503'));
        \probar('sql_version_cruzada_rechazada_al_cerrar',fn()=>$negativo(function($db) {
            // Alinear sólo contexto de la evaluación de ensayo; todo el grafo sigue en la misma rama.
            $map=require __DIR__.'/mapa-cerrado.php';
            $col=$map['columnas']['/instrumental/contexto/evaluacion'];
            $db->exec("UPDATE orion_f02.entradas_compuestas SET {$col}_texto='DOMINIO-NEGATIVO' WHERE cod_evaluacion_experta='NEGATIVO'");
            $db->exec("INSERT INTO orion_f02.anclajes_condiciones_adquiridas SELECT substr(md5(cod_fila),1,20),cod_evaluacion_experta,coleccion,cod_regla_experta_texto,cod_condicion_regla_texto FROM orion_f02.copia_condiciones_regla_experta WHERE cod_evaluacion_experta='NEGATIVO'");
            $db->exec("UPDATE orion_f02.copia_reglas_expertas SET cod_version_modelo_texto='OTRA-VERSION' WHERE cod_evaluacion_experta='NEGATIVO'");
            $db->exec("UPDATE orion_f02.evaluaciones_compuestas SET estado='CERRADA' WHERE cod_evaluacion_experta='NEGATIVO'");
        },'23514'));
        \probar('sql_no_confirma_cabecera_parcial',function() use($pdo) {
            $pdo->beginTransaction();
            try {
                $pdo->exec("INSERT INTO orion_f02.evaluaciones_compuestas SELECT 'PARCIAL','PARCIAL',id_evaluacion_petersen,cod_residente,cod_usuario_ejecucion,cod_release_motor,huella_compuesta,huella_instrumental,huella_petersen,contrato_proyeccion,clock_timestamp(),'ABIERTA' FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta='EVAL-F02'");
                $pdo->commit();throw new RuntimeException('Confirmó parcial.');
            }catch(PDOException $e){\exigir($e->getCode()==='23514','SQLSTATE de cierre parcial incorrecto.');}
            finally{if($pdo->inTransaction())$pdo->rollBack();}
            \exigir((int)$pdo->query("SELECT count(*) FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta='PARCIAL'")->fetchColumn()===0,'Quedó cabecera parcial.');
        });
        foreach($mapa['modelos'] as $m) {
            $tabla=$m['tabla'];
            if((int)$pdo->query("SELECT count(*) FROM orion_f02.$tabla")->fetchColumn()===0) continue;
            foreach(['UPDATE','DELETE','INSERT'] as $op) {
                \probar('inmutable_'.$tabla.'_'.$op,function() use($pdo,$tabla,$op) {
                    $pdo->beginTransaction();
                    try {
                        $sql=match($op){'UPDATE'=>"UPDATE orion_f02.$tabla SET cod_fila=cod_fila",'DELETE'=>"DELETE FROM orion_f02.$tabla",'INSERT'=>"INSERT INTO orion_f02.$tabla SELECT * FROM orion_f02.$tabla LIMIT 1"};
                        $pdo->exec($sql);throw new RuntimeException('Mutación histórica aceptada.');
                    }catch(PDOException $e){\exigir($e->getCode()==='55000','Inmutabilidad no impidió '.$op.': '.$e->getMessage());}
                    finally{if($pdo->inTransaction())$pdo->rollBack();}
                });
            }
        }
        \probar('inmutable_truncate_y_rollback_con_historia',function()use($pdo) {
            foreach(['TRUNCATE orion_f02.entradas_compuestas CASCADE',file_get_contents(__DIR__.'/esquema.down.sql')] as $sql) {
                $pdo->beginTransaction();try{$pdo->exec($sql);throw new RuntimeException('Destrucción aceptada.');}
                catch(PDOException $e){\exigir($e->getCode()==='55000','Código de rechazo de destrucción incorrecto.');}
                finally{if($pdo->inTransaction())$pdo->rollBack();}
            }
        });
        \probar('concurrencia_insercion_tardia_bloquea_y_revalida_cierre',function()use($pdo,$tCaptura) {
            $fila=$pdo->query("SELECT * FROM orion_f02.$tCaptura WHERE cod_evaluacion_experta='EVAL-F02' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $fila['cod_fila']='TARDIA-CONCURRENTE';$fila['orden']=999;
            $pdo->beginTransaction();
            $pdo->exec("SELECT cod_evaluacion_experta FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta='EVAL-F02' FOR UPDATE");
            $proceso=proc_open([PHP_BINARY,__DIR__.'/worker-sql.php'],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
            if(!is_resource($proceso))throw new RuntimeException('No arrancó conexión independiente.');
            try {
                fwrite($pipes[0],serialize(['tabla'=>$tCaptura,'fila'=>$fila])."\n");fclose($pipes[0]);
                $pid=(int)trim(fgets($pipes[1]));\exigir($pid>0,'Worker no identificó sesión.');
                $limite=microtime(true)+4;$bloqueado=false;
                $consulta=$pdo->prepare('SELECT cardinality(pg_blocking_pids(?))>0');
                do{$consulta->execute([$pid]);$bloqueado=$consulta->fetchColumn()===true;}while(!$bloqueado&&microtime(true)<$limite);
                \exigir($bloqueado,'No se demostró espera SQL real.');
                $pdo->commit();
                $estado=trim(stream_get_contents($pipes[1]));$error=stream_get_contents($pipes[2]);
                fclose($pipes[1]);fclose($pipes[2]);
                \exigir(proc_close($proceso)===0,'Worker falló: '.$error);$proceso=null;
                \exigir($estado==='55000','La inserción no revalidó cierre después del bloqueo: '.$estado);
                \exigir((int)$pdo->query("SELECT count(*) FROM orion_f02.$tCaptura WHERE cod_fila='TARDIA-CONCURRENTE'")->fetchColumn()===0,'Inserción tardía persistida.');
                return ['conexiones_independientes'=>true,'espera_pg_blocking_pids'=>true,'sqlstate'=>'55000'];
            }finally{
                if($pdo->inTransaction())$pdo->rollBack();
                if(is_resource($proceso)){proc_terminate($proceso);proc_close($proceso);}
            }
        });
    }
}
