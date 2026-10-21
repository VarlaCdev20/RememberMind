<?php

namespace Database\Seeders\LaPaz;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Huellas efímeras: nunca guarda el contenido ni las identidades de la instalación en Git. */
final class ConservacionDatos
{
    private array $originales = [];

    public function capturar(): array
    {
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

    private static function huella(array $fila): string
    {
        ksort($fila);

        return hash('sha256', json_encode($fila, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
    }
}
