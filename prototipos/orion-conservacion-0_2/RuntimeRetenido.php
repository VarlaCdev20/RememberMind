<?php

declare(strict_types=1);

namespace OrionFase02;

use DomainException;
use ZipArchive;

/** Retención del ejecutable usado por el ensayo Windows; sin php.ini local ni archivos de usuario. */
final class RuntimeRetenido
{
    public static function crear(string $destino): array
    {
        $base=dirname(PHP_BINARY);
        $paths=[PHP_BINARY,$base.'/license.txt',$base.'/ext/php_zip.dll'];
        foreach(glob($base.'/*.dll') as $path) $paths[]=$path;
        $paths=array_unique($paths);
        $zip=new ZipArchive;
        if($zip->open($destino,ZipArchive::CREATE|ZipArchive::EXCL)!==true) throw new DomainException('Runtime requiere ZIP nuevo.');
        $hashes=[];
        try{
            foreach($paths as $path){
                if(!is_file($path)||is_link($path))throw new DomainException('Falta dependencia binaria de runtime.');
                $name=str_replace('\\','/',substr($path,strlen($base)+1));
                $hashes[$name]=hash_file('sha256',$path);
                if(!$zip->addFile($path,$name))throw new DomainException('No se retuvo dependencia runtime.');
            }
            // El punto de entrada forma parte del ejecutor retenido, no del checkout futuro.
            $lanzador = __DIR__.'/replay-aislado.php';
            if (!is_file($lanzador) || is_link($lanzador)) throw new DomainException('Falta el punto de entrada del replay.');
            $hashes['replay-aislado.php'] = hash_file('sha256', $lanzador);
            if (!$zip->addFile($lanzador, 'replay-aislado.php')) throw new DomainException('No se retuvo el punto de entrada.');
            ksort($hashes);
            $m=['contrato'=>'RUNTIME-PHP-WINDOWS-F02-1','php_version'=>PHP_VERSION,'plataforma'=>PHP_OS_FAMILY,'arquitectura'=>php_uname('m'),'archivos'=>$hashes];
            $m['huella']=hash('sha256',json_encode($m,JSON_THROW_ON_ERROR));
            $zip->addFromString('MANIFIESTO-RUNTIME.json',json_encode($m,JSON_THROW_ON_ERROR|JSON_PRETTY_PRINT));
        }finally{$zip->close();}
        return ['huella_runtime_zip'=>hash_file('sha256',$destino),'huella_php_ejecutable'=>hash_file('sha256',PHP_BINARY)];
    }

    public static function restaurar(string $zipPath, string $destino, array $identidad): string
    {
        if(!is_file($zipPath)||hash_file('sha256',$zipPath)!==$identidad['huella_runtime_zip']||file_exists($destino))throw new DomainException('Runtime perdido, alterado o destino ocupado.');
        $zip=new ZipArchive;
        if($zip->open($zipPath,ZipArchive::RDONLY)!==true)throw new DomainException('Runtime no legible.');
        try{
            $m=json_decode($zip->getFromName('MANIFIESTO-RUNTIME.json'),true,flags:JSON_THROW_ON_ERROR);
            $h=$m['huella'];unset($m['huella']);
            if($h!==hash('sha256',json_encode($m,JSON_THROW_ON_ERROR))||$m['contrato']!=='RUNTIME-PHP-WINDOWS-F02-1'||$zip->numFiles!==count($m['archivos'])+1
                || !isset($m['archivos']['replay-aislado.php']))throw new DomainException('Manifiesto de runtime inválido.');
            foreach($m['archivos'] as $name=>$hash){
                if(!preg_match('~^(?:[A-Za-z0-9_-]+\.(?:exe|dll|txt)|ext/php_zip\.dll|replay-aislado\.php)$~D',$name)||hash('sha256',$zip->getFromName($name))!==$hash)throw new DomainException('Runtime incompleto o ruta inválida.');
            }
            if($m['archivos']['php.exe']!==$identidad['huella_php_ejecutable'])throw new DomainException('Ejecutable distinto del adquirido.');
            mkdir($destino,0700,true);
            if(!$zip->extractTo($destino,array_keys($m['archivos'])))throw new DomainException('No se restauró runtime.');
        }finally{$zip->close();}
        return $destino.'/php.exe';
    }
}
