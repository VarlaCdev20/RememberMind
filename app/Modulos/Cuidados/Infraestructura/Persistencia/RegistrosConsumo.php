<?php

namespace App\Modulos\Cuidados\Infraestructura\Persistencia;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class RegistrosConsumo
{
    public const TABLA = 'registros_consumo';

    public const CODIGO = 'cod_registro_consumo';

    public const TIPOS = ['registros_ingesta' => 'INGESTA', 'registros_hidratacion' => 'HIDRATACION'];

    public static function consulta(string $entidad): Builder
    {
        return isset(self::TIPOS[$entidad])
            ? DB::table(self::TABLA)->where('tipo_evento', self::TIPOS[$entidad])
            : DB::table($entidad);
    }

    public static function insertar(string $entidad, array $filas): void
    {
        if (isset(self::TIPOS[$entidad])) {
            $campo = $entidad === 'registros_ingesta' ? 'cod_ingesta' : 'cod_hidratacion';
            foreach ($filas as &$fila) {
                $fila[self::CODIGO] = $fila[$campo];
                $fila['tipo_evento'] = self::TIPOS[$entidad];
                unset($fila[$campo]);
            }
            unset($fila);
            DB::table(self::TABLA)->insert($filas);

            return;
        }

        DB::table($entidad)->insert($filas);
    }
}
