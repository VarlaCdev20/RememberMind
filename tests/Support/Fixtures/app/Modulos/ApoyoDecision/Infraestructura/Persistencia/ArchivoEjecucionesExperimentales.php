<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use App\Modulos\ApoyoDecision\Aplicacion\Contratos\AlmacenEjecucionesExperimentales;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use DomainException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use RuntimeException;

/**
 * Archivo privado cifrado de evidencia de pruebas, permitido por §14 del Prompt Maestro.
 * Conserva entradas/salidas técnicas; no sustituye las tablas expertas, historia clínica
 * ni el contrato pendiente de conservación institucional del manifiesto compuesto.
 */
final class ArchivoEjecucionesExperimentales implements AlmacenEjecucionesExperimentales
{
    public const VERSION = 'ARCHIVO-PERFIL-TEST-1';

    private const RAIZ = 'laboratorio-experto';

    public function guardar(array $entrada, array $resultado, string $autor, string $motor): array
    {
        $caso = $resultado['caso'];
        $evaluacion = $resultado['evaluacion'];
        $ruta = $this->ruta($caso, $evaluacion);
        $disk = $this->disco();
        $dir = dirname($ruta);
        if (! $disk->makeDirectory($dir)) {
            throw new RuntimeException('No se pudo preparar el archivo técnico privado.');
        }
        $lock = fopen($disk->path($dir.'/.archivo.lock'), 'c');
        if ($lock === false) {
            throw new RuntimeException('No se pudo bloquear el archivo técnico.');
        }
        $temporal = null;
        try {
            if (! flock($lock, LOCK_EX)) {
                throw new RuntimeException('No se obtuvo exclusión para conservar la ejecución.');
            }
            $huella = CodecInstantanea::huella($entrada);
            if ($disk->exists($ruta)) {
                $guardada = $this->leer($caso, $evaluacion);
                if ($guardada['huella_entrada'] !== $huella || $guardada['huella_resultado'] !== CodecInstantanea::huella($resultado)
                    || $guardada['motor_sha256'] !== $motor) {
                    throw new DomainException('La identidad ya conserva otra ejecución; no se sobrescribe historia.');
                }

                return $guardada;
            }
            $documento = ['contrato_archivo' => self::VERSION, 'uso' => 'EVIDENCIA_DE_INGENIERIA_NO_INSTITUCIONAL',
                'caso' => $caso, 'evaluacion' => $evaluacion, 'autor_tecnico' => $autor,
                'fecha_archivo' => now()->format('Y-m-d\TH:i:s.uP'), 'motor_sha256' => $motor,
                'huella_entrada' => $huella, 'huella_resultado' => CodecInstantanea::huella($resultado),
                'entrada' => $entrada, 'resultado' => $resultado, 'conservacion_bdd_institucional' => false];
            $json = json_encode($documento, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
            $relectura = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            if (CodecInstantanea::huella($relectura['entrada']) !== $huella
                || CodecInstantanea::huella($relectura['resultado']) !== $documento['huella_resultado']) {
                throw new DomainException('El formato de archivo no conserva exactamente los tipos originales.');
            }
            $temporal = $dir.'/.pendiente-'.bin2hex(random_bytes(12));
            if (! $disk->put($temporal, Crypt::encryptString($json)) || ! $disk->move($temporal, $ruta)) {
                throw new RuntimeException('No se completó la conservación técnica atómica.');
            }
            $temporal = null;

            return $documento;
        } finally {
            if ($temporal !== null) {
                $disk->delete($temporal); // Solo el archivo temporal no publicado creado por esta invocación.
            }
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function leer(string $caso, string $evaluacion): array
    {
        $ruta = $this->ruta($caso, $evaluacion);
        $disk = $this->disco();
        if (! $disk->exists($ruta)) {
            throw new DomainException('No existe una ejecución técnica conservada con esa identidad.');
        }
        $contenido = $disk->get($ruta);
        $r = json_decode(Crypt::decryptString($contenido), true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($r) || ($r['contrato_archivo'] ?? null) !== self::VERSION
            || ($r['caso'] ?? null) !== $caso || ($r['evaluacion'] ?? null) !== $evaluacion
            || ! is_array($r['entrada'] ?? null) || ! is_array($r['resultado'] ?? null)
            || ($r['huella_entrada'] ?? null) !== CodecInstantanea::huella($r['entrada'])
            || ($r['huella_resultado'] ?? null) !== CodecInstantanea::huella($r['resultado'])) {
            throw new DomainException('El archivo técnico no conserva identidad o contenido íntegros.');
        }

        return $r;
    }

    public function listar(string $caso): array
    {
        $this->ruta($caso, 'SIM-CONSULTA');
        $rutas = $this->disco()->files(self::RAIZ.'/'.$caso);
        $resultado = [];
        foreach ($rutas as $ruta) {
            if (preg_match('/\/(SIM-[A-Za-z0-9-]+)\.enc$/D', $ruta, $m)) {
                $r = $this->leer($caso, $m[1]);
                $resultado[] = array_intersect_key($r, array_flip(['caso', 'evaluacion', 'fecha_archivo', 'autor_tecnico', 'motor_sha256']));
            }
        }
        usort($resultado, static fn ($a, $b) => strcmp($a['evaluacion'], $b['evaluacion']));

        return $resultado;
    }

    private function ruta(string $caso, string $evaluacion): string
    {
        if (! preg_match('/^SINTETICO-[A-Za-z0-9-]{1,30}$/D', $caso) || ! preg_match('/^SIM-[A-Za-z0-9-]{1,30}$/D', $evaluacion)) {
            throw new DomainException('Identidad no permitida en el archivo técnico.');
        }

        return self::RAIZ.'/'.$caso.'/'.$evaluacion.'.enc';
    }

    private function disco(): FilesystemAdapter
    {
        $db = DB::connection();
        if (! app()->environment('testing') || ! app()->runningInConsole()
            || ! BaseTecnicaDesechable::permite($db->getDriverName(), $db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))
            || config('filesystems.disks.local.driver') !== 'local') {
            throw new DomainException('El archivo experimental requiere almacenamiento privado local.');
        }

        return Storage::disk('local');
    }
}
