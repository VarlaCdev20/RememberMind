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
    private const ARCHIVOS_FIJOS = ['motor-experto/composer.json', 'composer.lock', 'motor-experto/tests/independencia.php',
        'motor-experto/tests/orion_independencia.php',
        'app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json'];

    public function crear(string $raiz, string $destino, string $revisionBase): array
    {
        if (! preg_match('/^[a-f0-9]{40}$/D', $revisionBase) || file_exists($destino) || ! is_dir(dirname($destino))) {
            throw new DomainException('Indique revisión Git completa y un archivo nuevo en una carpeta existente.');
        }
        $raiz = realpath($raiz) ?: throw new DomainException('Raíz de código inexistente.');
        $archivos = self::ARCHIVOS_FIJOS;
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
            try {
                $m = $texto === false ? null : json_decode($texto, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new DomainException('Manifiesto de software ilegible.', previous: $e);
            }
            if (! is_array($m) || ! in_array($m['contrato'] ?? null, ['ARTEFACTO-MOTOR-1', 'ARTEFACTO-MOTOR-2'], true) || ($m['incluye_datos_clinicos'] ?? null) !== false) {
                throw new DomainException('Manifiesto de software incompatible.');
            }
            if (! is_array($m['archivos'] ?? null) || $m['archivos'] === []) {
                throw new DomainException('El manifiesto no enumera archivos de software.');
            }
            $huella = $m['huella'] ?? '';
            unset($m['huella']);
            if (! is_string($huella) || ! hash_equals($huellaEsperada, $huella)
                || ! hash_equals(CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($m)), $huella)
                || $zip->numFiles !== count($m['archivos']) + 1) {
                throw new DomainException('Huella o conjunto de archivos del release incompatible.');
            }
            if ($m['contrato'] === 'ARTEFACTO-MOTOR-2') {
                foreach (self::ARCHIVOS_FIJOS as $nombre) {
                    if (! array_key_exists($nombre, $m['archivos'])) {
                        throw new DomainException('Falta un archivo obligatorio del release.');
                    }
                }
                foreach (array_keys($m['archivos']) as $nombre) {
                    if (! in_array($nombre, self::ARCHIVOS_FIJOS, true)
                        && ! preg_match('~^motor-experto/src/(?:[A-Za-z0-9_]+/)*[A-Za-z0-9_]+\.php$~D', $nombre)) {
                        throw new DomainException('El release contiene una ruta fuera del contrato de software.');
                    }
                }
            }
            foreach ($m['archivos'] as $nombre => $esperada) {
                if (! is_string($nombre) || ! is_string($esperada) || ! preg_match('/^[a-f0-9]{64}$/D', $esperada)) {
                    throw new DomainException('Una entrada del manifiesto de software es inválida.');
                }
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

    /** Vincular una preparación recién calculada exige los mismos bytes de motor que la ejecutaron. */
    public function verificarEjecutorActual(string $ruta, string $huellaEsperada): array
    {
        $manifiesto = $this->verificar($ruta, $huellaEsperada);
        if ($manifiesto['contrato'] !== 'ARTEFACTO-MOTOR-2') {
            throw new DomainException('La preparación compuesta exige un release completo del ejecutor utilizado.');
        }
        if ($this->identidadEjecutorActual() !== self::identidadEjecutor($manifiesto['archivos'])) {
            throw new DomainException('El release no contiene el mismo ejecutor completo que calculó la preparación.');
        }

        return $manifiesto;
    }

    /** Identidad de software capturada al crear el DTO; nunca lee fuentes clínicas. */
    public function identidadEjecutorActual(): array
    {
        $raiz = dirname(__DIR__, 5);
        $actuales = [];
        foreach (self::ARCHIVOS_FIJOS as $nombre) {
            $ruta = $raiz.'/'.$nombre;
            if (! is_file($ruta) || ! is_readable($ruta)) {
                throw new DomainException('Falta un archivo fijo del ejecutor actual.');
            }
            $huella = hash_file('sha256', $ruta);
            if ($huella === false) {
                throw new DomainException('Falta un archivo fijo del ejecutor actual.');
            }
            $actuales[$nombre] = $huella;
        }
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz.'/motor-experto/src', \FilesystemIterator::SKIP_DOTS)) as $archivo) {
            if ($archivo->isFile() && $archivo->getExtension() === 'php') {
                $nombre = str_replace('\\', '/', substr($archivo->getPathname(), strlen($raiz) + 1));
                if (! $archivo->isReadable()) {
                    throw new DomainException('No se pudo identificar un archivo del ejecutor actual.');
                }
                $huella = hash_file('sha256', $archivo->getPathname());
                if ($huella === false) {
                    throw new DomainException('No se pudo identificar un archivo del ejecutor actual.');
                }
                $actuales[$nombre] = $huella;
            }
        }

        return self::identidadEjecutor($actuales);
    }

    public static function identidadEjecutor(array $archivos): array
    {
        $fuentes = array_filter($archivos, static fn ($nombre) => str_starts_with($nombre, 'motor-experto/src/'), ARRAY_FILTER_USE_KEY);
        $identidad = array_intersect_key($archivos, array_fill_keys(self::ARCHIVOS_FIJOS, true)) + $fuentes;

        // huella_src conserva el nombre del contrato V1; incluye también los archivos fijos del release.
        return ['cantidad_archivos' => count($fuentes), 'huella_src' => CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($identidad))];
    }
}
