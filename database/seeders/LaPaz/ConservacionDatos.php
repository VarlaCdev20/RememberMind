<?php

namespace Database\Seeders\LaPaz;

use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Huellas efímeras: nunca guarda el contenido ni las identidades de la instalación en Git. */
final class ConservacionDatos
{
    private const GENERADAS = ['residentes', 'admisiones', 'residentes_contactos', 'ocupaciones_cama',
        'historial_estados_residente', 'consentimientos', 'seguros_residente', 'medicamentos'];

    private array $originales = [];

    private array $secuencias = [];

    public function capturar(): array
    {
        $this->secuencias = DB::table('secuencias_codigos')->pluck('ultimo_valor', 'entidad')->all();
        $conteos = [];
        foreach (DB::getSchemaBuilder()->getTables() as $tabla) {
            $nombre = $tabla['name'];
            // Estas tablas técnicas pueden cambiar por otras sesiones de la aplicación.
            if (in_array($nombre, ['cache', 'cache_locks', 'sessions', 'jobs', 'failed_jobs', 'job_batches'], true)) {
                continue;
            }
            $primaria = collect(DB::getSchemaBuilder()->getIndexes($nombre))->firstWhere('primary', true);
            if (! $primaria) {
                continue;
            }
            $columnas = $primaria['columns'];
            $consulta = DB::table($nombre);
            foreach ($columnas as $columna) {
                $consulta->orderBy($columna);
            }
            $filas = [];
            foreach ($consulta->cursor() as $fila) {
                $datos = (array) $fila;
                $clave = array_intersect_key($datos, array_flip($columnas));
                $filas[] = ['clave' => $clave, 'sha256' => self::huella($datos)];
            }
            $this->originales[$nombre] = $filas;
            $conteos[$nombre] = count($filas);
        }

        return $conteos;
    }

    public function comprobar(): int
    {
        $conservadas = 0;
        foreach ($this->originales as $tabla => $filas) {
            if ($tabla === 'secuencias_codigos') {
                $this->comprobarSecuencias();

                continue;
            }
            foreach (array_chunk($filas, 150) as $grupo) {
                $actuales = DB::table($tabla)->where(function ($q) use ($grupo) {
                    foreach ($grupo as $fila) {
                        $q->orWhere($fila['clave']);
                    }
                })->get()->mapWithKeys(function ($fila) use ($grupo) {
                    $datos = (array) $fila;
                    $clave = array_intersect_key($datos, $grupo[0]['clave']);

                    return [json_encode($clave, JSON_THROW_ON_ERROR) => self::huella($datos)];
                });
                foreach ($grupo as $fila) {
                    if (($actuales[json_encode($fila['clave'], JSON_THROW_ON_ERROR)] ?? null) !== $fila['sha256']) {
                        throw new RuntimeException('Una fila preexistente cambió en '.$tabla.'. La carga se revierte.');
                    }
                    $conservadas++;
                }
            }
        }

        return $conservadas;
    }

    /** El contador técnico puede avanzar solo por códigos nuevos existentes; nunca retroceder. */
    private function comprobarSecuencias(): void
    {
        $actuales = DB::table('secuencias_codigos')->pluck('ultimo_valor', 'entidad')->all();
        if (array_diff_key($this->secuencias, $actuales)) {
            throw new RuntimeException('Una secuencia preexistente desapareció.');
        }
        foreach ($actuales as $entidad => $valor) {
            $antes = (int) ($this->secuencias[$entidad] ?? 0);
            $despues = (int) $valor;
            if ($despues === $antes && array_key_exists($entidad, $this->secuencias)) {
                continue;
            }
            if (! in_array($entidad, self::GENERADAS, true) || $despues <= $antes) {
                throw new RuntimeException('Cambio no permitido en una secuencia de códigos.');
            }
            [$pk] = CatalogoCodigos::ENTIDADES[$entidad];
            $previas = array_fill_keys(array_column(array_column($this->originales[$entidad] ?? [], 'clave'), $pk), true);
            for ($inicio = $antes + 1; $inicio <= $despues; $inicio += 150) {
                $codigos = array_map(fn ($n) => CatalogoCodigos::formatear($entidad, $n), range($inicio, min($inicio + 149, $despues)));
                if (array_intersect_key($previas, array_fill_keys($codigos, true))
                    || DB::table($entidad)->whereIn($pk, $codigos)->count() !== count($codigos)) {
                    throw new RuntimeException('Avance de secuencia sin registros nuevos que lo justifiquen.');
                }
            }
        }
    }

    private static function huella(array $fila): string
    {
        ksort($fila);

        return hash('sha256', json_encode($fila, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
    }
}
