<?php

namespace App\Modulos\Institucion\Infraestructura\Persistencia;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class HistoriaLaboral
{
    public const TABLA = 'historial_laboral_personal';

    public const CODIGO = 'cod_historial_laboral';

    public const TIPOS = [
        'movimientos_laborales_personal' => ['LABORAL', 'cod_movimiento_laboral'],
        'eventos_asignacion_personal' => ['ASIGNACION', 'cod_evento_asignacion'],
    ];

    public static function consulta(string $entidad, ?string $alias = null): Builder
    {
        [$tipo] = self::TIPOS[$entidad] ?? throw new \InvalidArgumentException('Tipo de historia laboral desconocido.');

        return DB::table(self::TABLA, $alias)->where(($alias ? $alias.'.' : '').'clase_acto', $tipo);
    }

    public static function insertar(string $entidad, array $filas): void
    {
        [$tipo, $clave] = self::TIPOS[$entidad] ?? throw new \InvalidArgumentException('Tipo de historia laboral desconocido.');
        foreach ($filas as &$fila) {
            $codigo = $fila[$clave];
            unset($fila['cod_movimiento_laboral'], $fila['cod_evento_asignacion']);
            $fila[self::CODIGO] = $codigo;
            $fila['clase_acto'] = $tipo;
        }
        unset($fila);
        DB::table(self::TABLA)->insert($filas);
    }
}
