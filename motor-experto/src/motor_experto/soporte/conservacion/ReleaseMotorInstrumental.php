<?php

declare(strict_types=1);

namespace MotorExperto\soporte\conservacion;

use DomainException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/** Artefacto recuperable de código y red documental; nunca incorpora capturas, historia, configuración local o .env. */
final class ReleaseMotorInstrumental
{
    public function crear(string $raiz, string $destino, string $revisionBase): array
    {
        if (! preg_match('/^[a-f0-9]{40}$/D', $revisionBase) || file_exists($destino) || ! is_dir(dirname($destino))) {
            throw new DomainException('Indique revisión Git completa y un archivo nuevo en una carpeta existente.');
        }
        $raiz = realpath($raiz) ?: throw new DomainException('Raíz de código inexistente.');
        $archivos = ['motor-experto/composer.json', 'composer.lock', 'motor-experto/tests/independencia.php', 'motor-experto/tests/orion_independencia.php',
            'app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json'];
        $directorio = $raiz.'/motor-experto/src';
        if (! is_dir($directorio)) {
            throw new DomainException('Falta el código del motor.');
        }
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio, \FilesystemIterator::SKIP_DOTS)) as $archivo) {
            if ($archivo->isFile() && $archivo->getExtension() === 'php') {
                $archivos[] = str_replace('\\', '/', substr($archivo->getPathname(), strlen($raiz) + 1));
            }
        }
        sort($archivos, SORT_STRING);
        $contenido = $huellas = [];
        foreach ($archivos as $relativo) {
            $ruta = realpath($raiz.'/'.$relativo);
            if ($ruta === false || ! str_starts_with($ruta, $raiz.DIRECTORY_SEPARATOR) || is_link($raiz.'/'.$relativo)) {
                throw new DomainException('Archivo de release ausente o fuera de la raíz.');
            }
            $contenido[$relativo] = file_get_contents($ruta);
            if ($contenido[$relativo] === false) {
                throw new DomainException('No se pudo leer un archivo del release.');
            }
            $huellas[$relativo] = hash('sha256', $contenido[$relativo]);
        }
        $manifiesto = ['contrato' => 'ARTEFACTO-MOTOR-2', 'revision_base' => $revisionBase,
            'php_requerido' => '^8.3', 'incluye_datos_clinicos' => false, 'archivos' => $huellas];
        $manifiesto['huella'] = CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($manifiesto));
        $zip = new ZipArchive;
        if ($zip->open($destino, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            throw new DomainException('No se pudo crear un archivo nuevo de release.');
        }
        try {
            foreach ($contenido as $nombre => $texto) {
                if (! $zip->addFromString($nombre, $texto)) {
                    throw new DomainException('Falló la incorporación de código al release.');
                }
            }
            if (! $zip->addFromString('MANIFIESTO-MOTOR.json', json_encode($manifiesto, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
                throw new DomainException('No se pudo conservar el manifiesto de software.');
            }
        } finally {
            if (! $zip->close()) {
                throw new DomainException('No se confirmó el cierre del archivo de release.');
            }
        }

        return $this->verificar($destino, $manifiesto['huella']);
    }

    /** Comprueba bytes y conjunto exacto de archivos sin extraer ni ejecutar código del ZIP. */
    public function verificar(string $ruta, string $huellaEsperada): array
    {
        $zip = new ZipArchive;
        if (! is_file($ruta) || $zip->open($ruta, ZipArchive::RDONLY) !== true) {
            throw new DomainException('Artefacto de software ausente o ilegible.');
        }
        try {
            $texto = $zip->getFromName('MANIFIESTO-MOTOR.json');
            $m = $texto === false ? null : json_decode($texto, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($m) || ! in_array($m['contrato'] ?? null, ['ARTEFACTO-MOTOR-1', 'ARTEFACTO-MOTOR-2'], true) || ($m['incluye_datos_clinicos'] ?? null) !== false) {
                throw new DomainException('Manifiesto de software incompatible.');
            }
            $huella = $m['huella'] ?? '';
            unset($m['huella']);
            if (! is_string($huella) || ! hash_equals($huellaEsperada, $huella)
                || ! hash_equals(CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($m)), $huella)
                || $zip->numFiles !== count($m['archivos']) + 1) {
                throw new DomainException('Huella o conjunto de archivos del release incompatible.');
            }
            foreach ($m['archivos'] as $nombre => $esperada) {
                $bytes = $zip->getFromName($nombre);
                if ($bytes === false || ! hash_equals($esperada, hash('sha256', $bytes))) {
                    throw new DomainException('El código conservado fue alterado o está incompleto.');
                }
            }

            return $m + ['huella' => $huella];
        } finally {
            $zip->close();
        }
    }
}
