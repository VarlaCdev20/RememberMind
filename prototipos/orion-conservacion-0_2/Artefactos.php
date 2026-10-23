<?php

declare(strict_types=1);

namespace OrionFase02;

use DomainException;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use ZipArchive;

/** Laboratorio de retención con copia primaria y respaldo independiente. Nunca incluye DTO. */
final class Artefactos
{
    public function __construct(private string $base) {}

    public function retener(string $zip, string $huella): array
    {
        $m = (new ReleaseMotorInstrumental)->verificarEjecutorActual($zip, $huella);
        $adapter = file_get_contents(__DIR__.'/EntradasCompuestas.php');
        if(!is_dir($this->base))mkdir($this->base,0700,true);
        $runtime=RuntimeRetenido::crear($this->base.'/runtime-origen.zip');
        foreach (['primario', 'respaldo'] as $copy) {
            $dir = $this->base.'/'.$copy;
            if (! is_dir($dir) && ! mkdir($dir, 0700, true)) {
                throw new DomainException('No se pudo preparar la retención.');
            }
            foreach (['motor.zip' => file_get_contents($zip), 'EntradasCompuestas.php' => $adapter, 'runtime.zip'=>file_get_contents($this->base.'/runtime-origen.zip')] as $name => $bytes) {
                $f = fopen($dir.'/'.$name, 'xb');
                if ($f === false || fwrite($f, $bytes) !== strlen($bytes)) {
                    throw new DomainException('No se confirmó la copia del artefacto.');
                }
                fflush($f);
                fclose($f);
            }
        }

        return ['cod_release_motor' => 'RELEASE-F02', 'huella_manifiesto' => $huella, 'huella_zip' => hash_file('sha256', $zip),
            'huella_adaptador' => hash('sha256', $adapter), 'revision_base' => $m['revision_base'], 'php_version' => PHP_VERSION,
            'cantidad_archivos' => ReleaseMotorInstrumental::identidadEjecutor($m['archivos'])['cantidad_archivos'],
            'huella_src' => ReleaseMotorInstrumental::identidadEjecutor($m['archivos'])['huella_src']] + $runtime;
    }

    public function restaurar(array $registro, string $copia, string $destino): string
    {
        if (! in_array($copia, ['primario', 'respaldo'], true) || file_exists($destino)) {
            throw new DomainException('Restauración requiere destino nuevo y copia conocida.');
        }
        $dir = $this->base.'/'.$copia;
        $zipPath = $dir.'/motor.zip';
        if (! is_file($zipPath) || ! is_file($dir.'/EntradasCompuestas.php')
            || hash_file('sha256', $zipPath) !== $registro['huella_zip']
            || hash_file('sha256', $dir.'/EntradasCompuestas.php') !== $registro['huella_adaptador']) {
            throw new DomainException('Artefacto perdido o alterado: restauración denegada.');
        }
        $m = (new ReleaseMotorInstrumental)->verificar($zipPath, $registro['huella_manifiesto']);
        if (ReleaseMotorInstrumental::identidadEjecutor($m['archivos'])['huella_src'] !== $registro['huella_src']) {
            throw new DomainException('Ejecutor distinto del registrado.');
        }
        mkdir($destino, 0700, true);
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::RDONLY) !== true) {
            throw new DomainException('ZIP no legible.');
        }
        try {
            if (! $zip->extractTo($destino, array_keys($m['archivos']))) {
                throw new DomainException('Restauración incompleta.');
            }
        } finally {
            $zip->close();
        }
        mkdir($destino.'/adaptador', 0700);
        copy($dir.'/EntradasCompuestas.php', $destino.'/adaptador/EntradasCompuestas.php');

        return $zipPath;
    }

    public function reproducir(array $entrada, array $registro, string $copia, string $destino): array
    {
        $zip = $this->restaurar($registro, $copia, $destino);
        $php=RuntimeRetenido::restaurar($this->base.'/'.$copia.'/runtime.zip',$destino.'/runtime',$registro);
        // El parser INI de PHP requiere comillas cuando el directorio temporal de Windows contiene ~ o espacios.
        $extensionDir = str_replace('\\', '/', $destino.'/runtime/ext');
        $process = proc_open([$php, '-n', '-d','extension_dir="'.$extensionDir.'"','-d','extension=zip','-d','display_errors=stderr',$destino.'/runtime/replay-aislado.php', $destino, $zip, $registro['huella_manifiesto']],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $destino);
        if (! is_resource($process)) {
            throw new DomainException('No se inició el ejecutor restaurado.');
        }
        fwrite($pipes[0], serialize($entrada));
        fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $estado = proc_close($process);
        if ($estado !== 0) {
            throw new DomainException('Replay aislado falló (salida '.$estado.', stdout '.strlen($out).' bytes): '.$error);
        }
        $resultado = unserialize($out, ['allowed_classes' => false]);
        if (! is_array($resultado)) {
            throw new DomainException('Resultado del ejecutor inválido.');
        }

        return $resultado;
    }
}
