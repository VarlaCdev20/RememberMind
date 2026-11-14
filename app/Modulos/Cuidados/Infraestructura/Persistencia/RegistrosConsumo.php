<?php

namespace App\Modulos\Cuidados\Infraestructura\Persistencia;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class RegistrosConsumo
{
    public const TABLA = 'registros_consumo';

    public const CODIGO = 'cod_registro_consumo';

    public const ORIGENES = [
        'registros_ingesta' => [self::TABLA, self::CODIGO, 'INGESTA', 'cod_ingesta'],
        'registros_hidratacion' => [self::TABLA, self::CODIGO, 'HIDRATACION', 'cod_hidratacion'],
        'registros_eliminacion' => ['registros_consumo', 'cod_registro_consumo', 'ELIMINACION', 'cod_eliminacion'],
        'registros_movilidad' => ['registros_observaciones_cuidado', 'cod_observacion_cuidado', 'MOVILIDAD', 'cod_movilidad'],
        'registros_conductuales' => ['registros_observaciones_cuidado', 'cod_observacion_cuidado', 'CONDUCTA', 'cod_registro_conductual'],
    ];

    public static function codigo(string $entidad): ?string
    {
        return self::ORIGENES[$entidad][1] ?? null;
    }

    public static function consulta(string $entidad): Builder
    {
        return isset(self::ORIGENES[$entidad])
            ? DB::table(self::ORIGENES[$entidad][0])->where('tipo_evento', self::ORIGENES[$entidad][2])
            : DB::table($entidad);
    }

    public static function insertar(string $entidad, array $filas): void
    {
        if (isset(self::ORIGENES[$entidad])) {
            [$tabla, $codigo, $tipo, $campo] = self::ORIGENES[$entidad];
            foreach ($filas as &$fila) {
                $fila[$codigo] = $fila[$campo];
                $fila['tipo_evento'] = $tipo;
                unset($fila[$campo]);
            }
            unset($fila);
            DB::table($tabla)->insert($filas);

            return;
        }

        DB::table($entidad)->insert($filas);
    }
}
