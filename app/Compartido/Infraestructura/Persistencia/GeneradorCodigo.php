<?php

namespace App\Compartido\Infraestructura\Persistencia;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use Illuminate\Support\Facades\DB;

final class GeneradorCodigo implements CodigosEntidad
{
    public function registrarExplicito(string $tabla, string $codigo, ?string $conexion = null): void
    {
        $prefijo = CatalogoCodigos::ENTIDADES[$tabla][1] ?? CatalogoCodigos::ALIASES[$tabla][1] ?? throw new \InvalidArgumentException('Entidad desconocida.');
        if (! preg_match('/^COD_'.preg_quote($prefijo, '/').'_([0-9]{6,12})$/D', $codigo, $m)) {
            if (! app()->environment('testing')) {
                throw new \InvalidArgumentException('La identidad nueva debe usar el código correlativo de su entidad.');
            }

            return;
        }
        $numero = (int) $m[1];
        if ($numero < 1 || CatalogoCodigos::formatear($tabla, $numero) !== $codigo) {
            throw new \InvalidArgumentException('Código correlativo inválido.');
        }
        DB::connection($conexion)->statement('INSERT INTO secuencias_codigos (entidad, ultimo_valor) VALUES (?, ?) '
            .'ON CONFLICT (entidad) DO UPDATE SET ultimo_valor = CASE WHEN secuencias_codigos.ultimo_valor < excluded.ultimo_valor '
            .'THEN excluded.ultimo_valor ELSE secuencias_codigos.ultimo_valor END', [$tabla, $numero]);
    }

    /** Serializa cargas idempotentes sin consumir un número adicional. */
    public function serializar(string $tabla, callable $operacion): mixed
    {
        CatalogoCodigos::clave($tabla);

        return DB::transaction(function () use ($tabla, $operacion) {
            DB::statement('INSERT INTO secuencias_codigos (entidad, ultimo_valor) VALUES (?, 0) '
                .'ON CONFLICT (entidad) DO UPDATE SET ultimo_valor = secuencias_codigos.ultimo_valor', [$tabla]);

            return $operacion();
        }, 5);
    }

    /** Una sola sentencia bloquea/incrementa; funciona en PostgreSQL y SQLite. */
    public function siguiente(string $tabla, ?string $conexion = null): string
    {
        CatalogoCodigos::clave($tabla);
        $db = DB::connection($conexion);
        if (! in_array($db->getDriverName(), ['pgsql', 'sqlite'], true)) {
            throw new \LogicException('El generador requiere PostgreSQL o SQLite.');
        }

        $reservar = function () use ($db, $tabla): string {
            $fila = $db->selectOne(
                'INSERT INTO secuencias_codigos (entidad, ultimo_valor) VALUES (?, 1) '
                .'ON CONFLICT (entidad) DO UPDATE SET ultimo_valor = secuencias_codigos.ultimo_valor + 1 '
                .'RETURNING ultimo_valor', [$tabla], false,
            );

            return CatalogoCodigos::formatear($tabla, (int) $fila->ultimo_valor);
        };

        // La sentencia ya es atómica; la operación exterior controla su rollback/reintento.
        return $db->transactionLevel() > 0 ? $reservar() : $db->transaction($reservar, 5);
    }
}
