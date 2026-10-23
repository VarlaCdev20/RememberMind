<?php

declare(strict_types=1);

require dirname(__DIR__, 2).'/vendor/autoload.php';
foreach (['GuardiaDesechable', 'ProyeccionTipada', 'EntradasCompuestas', 'RepositorioCompuesto', 'CasoCanonico', 'Artefactos', 'RuntimeRetenido','VariantesCanonicas', 'PruebasSQL'] as $clase) {
    require __DIR__.'/'.$clase.'.php';
}

use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use OrionFase02\Artefactos;
use OrionFase02\CasoCanonico;
use OrionFase02\GuardiaDesechable;
use OrionFase02\RepositorioCompuesto;

$salida = $argv[1] ?? throw new RuntimeException('Indique carpeta de evidencia nueva.');
if (file_exists($salida) || ! mkdir($salida, 0700, true)) {
    throw new RuntimeException('La evidencia necesita carpeta nueva.');
}
$pruebas = [];
$aserciones = 0;
function exigir(bool $ok, string $detalle): void
{
    global $aserciones;
    $aserciones++;
    if (! $ok) {
        throw new RuntimeException($detalle);
    }
}
function probar(string $nombre, callable $prueba): void
{
    global $pruebas, $aserciones;
    $t = hrtime(true);
    $antes = $aserciones;
    try {
        $datos = $prueba();
        $pruebas[] = ['caso' => $nombre, 'estado' => 'PASS', 'aserciones' => $aserciones - $antes, 'segundos' => (hrtime(true) - $t) / 1e9, 'evidencia' => $datos];
        echo "PASS $nombre\n";
    } catch (Throwable $e) {
        $pruebas[] = ['caso' => $nombre, 'estado' => 'FAIL', 'aserciones' => $aserciones - $antes, 'segundos' => (hrtime(true) - $t) / 1e9, 'error' => $e->getMessage()];
        echo "FAIL $nombre: ".$e->getMessage()."\n";
        throw $e;
    }
}
$pdo = null;
try {
    probar('instalacion_exclusivamente_desechable', function () use (&$pdo) {
        $pdo = GuardiaDesechable::conectar();
        GuardiaDesechable::instalar($pdo);
        exigir((int) $pdo->query("SELECT count(*) FROM information_schema.tables WHERE table_schema='orion_f02'")->fetchColumn() > 0, 'No se instaló esquema.');

        return ['postgres' => $pdo->query('SHOW server_version')->fetchColumn()];
    });
    $repo = dirname(__DIR__, 2);
    $m = (new ReleaseMotorInstrumental)->crear($repo, $salida.'/motor-origen.zip', trim(shell_exec('git -C '.escapeshellarg($repo).' rev-parse HEAD')));
    $artefactos = new Artefactos($salida.'/artefactos');
    $release = $artefactos->retener($salida.'/motor-origen.zip', $m['huella']);
    $stmt = $pdo->prepare('INSERT INTO orion_f02.releases_motor ('.implode(',', array_keys($release)).') VALUES ('.implode(',', array_fill(0, count($release), '?')).')');
    $stmt->execute(array_values($release));
    $fuente = json_decode(file_get_contents($repo.'/app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json'), true, flags: JSON_THROW_ON_ERROR);
    $repositorio = new RepositorioCompuesto($pdo);
    $dto = (new CasoCanonico)->crear()->vincularRelease($salida.'/motor-origen.zip', $m['huella']);
    probar('guardar_recuperar_replay_dto_completo', function () use ($repositorio, $dto, $release, $fuente, $artefactos, $salida) {
        exigir($repositorio->guardar('EVAL-F02', $dto, $release, $fuente), 'No guardó.');
        $r = $repositorio->recuperar('EVAL-F02');
        $replay = $artefactos->reproducir($r['entradas'], $release, 'primario', $salida.'/restaurado-1');
        exigir($dto->contenido === $replay['contenido'], 'DTO completo distinto después de replay.');
        exigir($dto->huella === $replay['huella'], 'Huella compuesta distinta.');
        exigir($dto->contenido['instrumental']['huella'] === $replay['contenido']['instrumental']['huella'], 'Huella instrumental distinta.');
        exigir($dto->contenido['petersen']['huella'] === $replay['contenido']['petersen']['huella'], 'Huella Petersen distinta.');

        return ['huella_compuesta' => $dto->huella, 'huella_instrumental' => $dto->contenido['instrumental']['huella'],
            'huella_petersen' => $dto->contenido['petersen']['huella'], 'patron' => $replay['contenido']['petersen']['contenido']['salida']['patron'], 'igualdad_estricta' => true];
    });
    foreach (OrionFase02\VariantesCanonicas::casos() as $nombre => $caso) {
        probar('roundtrip_'.$nombre, function () use ($repositorio, $caso, $nombre, $release, $fuente, $artefactos, $salida, $m) {
            $original = $caso->vincularRelease($salida.'/motor-origen.zip', $m['huella']);
            $codigo = 'E-'.substr(hash('sha256',$nombre),0,16);
            exigir($repositorio->guardar($codigo,$original,$release,$fuente),'No guardó variante.');
            $r=$repositorio->recuperar($codigo);
            $replay=$artefactos->reproducir($r['entradas'],$release,'primario',$salida.'/replay-'.$nombre);
            exigir($original->contenido===$replay['contenido'],'DTO de variante difiere.');
            exigir($original->huella===$replay['huella'],'Huella difiere.');
            return ['igualdad_estricta'=>true,'huella'=>$original->huella,'patron'=>$original->contenido['petersen']['contenido']['salida']['patron']];
        });
    }
    probar('reintento_identico_sin_sobrescritura',function()use($repositorio,$dto,$release,$fuente,$pdo) {
        $antes=$pdo->query('SELECT count(*) FROM orion_f02.evaluaciones_compuestas')->fetchColumn();
        exigir(!$repositorio->guardar('EVAL-F02',$dto,$release,$fuente),'Reintento añadió historia.');
        exigir($antes===$pdo->query('SELECT count(*) FROM orion_f02.evaluaciones_compuestas')->fetchColumn(),'Cambió el número de evaluaciones.');
    });
    probar('conflicto_id_no_sobrescribe_historia',function()use($repositorio,$release,$fuente,$pdo,$salida,$m) {
        $otro=(new CasoCanonico)->crear(['COG-ATE'])->vincularRelease($salida.'/motor-origen.zip',$m['huella']);
        $antes=$pdo->query("SELECT huella_compuesta FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta='EVAL-F02'")->fetchColumn();
        try{$repositorio->guardar('EVAL-F02',$otro,$release,$fuente);throw new RuntimeException('Aceptó conflicto.');}
        catch(DomainException $e){exigir(str_contains($e->getMessage(),'otra identidad'),'Error no corresponde al conflicto.');}
        exigir($antes===$pdo->query("SELECT huella_compuesta FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta='EVAL-F02'")->fetchColumn(),'Historia sobrescrita.');
    });
    probar('fallo_intermedio_revierte_todas_las_filas',function()use($repositorio,$release,$fuente,$pdo,$salida,$m) {
        $nuevo=(new CasoCanonico)->crear(identidad:'FALLO-INTERMEDIO')->vincularRelease($salida.'/motor-origen.zip',$m['huella']);
        try{$repositorio->guardar('FALLO',$nuevo,$release,$fuente,fn()=>throw new RuntimeException('FALLO_INYECTADO'));throw new RuntimeException('No falló.');}
        catch(RuntimeException $e){exigir($e->getMessage()==='FALLO_INYECTADO','Fallo inesperado.');}
        exigir((int)$pdo->query("SELECT count(*) FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta='FALLO'")->fetchColumn()===0,'Quedó historia parcial.');
        exigir((int)$pdo->query("SELECT count(*) FROM orion_f02.entradas_compuestas WHERE cod_evaluacion_experta='FALLO'")->fetchColumn()===0,'Quedaron entradas parciales.');
    });
    OrionFase02\PruebasSQL::ejecutar($pdo,$repositorio->recuperar('EVAL-F02')['entradas']);
    probar('release_ausente_corrupcion_y_restauracion_desde_respaldo',function()use($artefactos,$release,$repositorio,$dto,$salida) {
        $primario=$salida.'/artefactos/primario/motor.zip';
        rename($primario,$primario.'.apartado');
        try{$artefactos->restaurar($release,'primario',$salida.'/no-debe-restaurar');throw new RuntimeException('Aceptó artefacto ausente.');}
        catch(DomainException $e){exigir(str_contains($e->getMessage(),'perdido'),'Error inesperado.');}
        file_put_contents($primario,'ZIP ALTERADO');
        try{$artefactos->restaurar($release,'primario',$salida.'/tampoco-restaura');throw new RuntimeException('Aceptó artefacto corrupto.');}
        catch(DomainException $e){exigir(str_contains($e->getMessage(),'alterado'),'Error inesperado.');}
        $replay=$artefactos->reproducir($repositorio->recuperar('EVAL-F02')['entradas'],$release,'respaldo',$salida.'/restaurado-respaldo');
        exigir($dto->contenido===$replay['contenido'],'Respaldo no reproduce DTO exacto.');
        exigir($dto->huella===$replay['huella'],'Huella distinta tras restauración.');
        return ['primario_ausente_rechazado'=>true,'primario_corrupto_rechazado'=>true,'restaurado_desde_respaldo'=>true];
    });
} finally {
    $fallos = count(array_filter($pruebas, fn ($p) => $p['estado'] === 'FAIL'));
    file_put_contents($salida.'/resultados.json', json_encode(['fecha' => '2026-10-20', 'casos' => count($pruebas), 'fallos' => $fallos, 'aserciones' => $aserciones, 'pruebas' => $pruebas], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
