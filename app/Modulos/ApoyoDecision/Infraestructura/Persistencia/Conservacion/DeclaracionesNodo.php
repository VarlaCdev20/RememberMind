<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use DomainException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Dos clases cerradas de declaración; mantiene los contratos de conocimiento existentes. */
final class DeclaracionesNodo
{
    public const TABLA = 'declaraciones_nodo';

    private const CAMPOS = [
        'variables_expertas' => ['cod_variable_experta' => 'cod_declaracion', 'cod_nodo_semantico' => 'cod_nodo_semantico',
            'cod_nodo_propietario_primario' => 'cod_nodo_propietario_primario', 'cod_dominio_valores' => 'cod_dominio_valores',
            'tipo_semantico' => 'tipo_semantico', 'papel_inferencial' => 'papel_inferencial', 'estado' => 'estado',
            'fecha_hora_creacion' => 'fecha_hora_creacion', 'cod_usuario_creacion' => 'cod_usuario_creacion', 'observacion' => 'observacion'],
        'criterios_dominios_resultado' => ['cod_criterio_dominio_resultado' => 'cod_declaracion', 'cod_nodo_criterio' => 'cod_nodo_semantico',
            'cod_dominio_valores' => 'cod_dominio_valores', 'estado' => 'estado', 'fecha_hora_creacion' => 'fecha_hora_creacion',
            'cod_usuario_creacion' => 'cod_usuario_creacion', 'observacion' => 'observacion'],
    ];

    public static function admite(string $entidad): bool { return isset(self::CAMPOS[$entidad]); }

    public static function columnas(string $entidad): array
    {
        $campos = array_keys(self::CAMPOS[$entidad]);
        if ($entidad === 'variables_expertas') array_splice($campos, 1, 0, ['cod_version_modelo']);
        return $campos;
    }

    public static function consulta(string $entidad, ?string $conexion = null, ?string $alias = null): Builder
    {
        $db = DB::connection($conexion);
        $campos = array_map(fn($fisico,$logico)=>'d.'.$fisico.' as '.$logico, self::CAMPOS[$entidad], array_keys(self::CAMPOS[$entidad]));
        if ($entidad === 'variables_expertas') array_splice($campos,1,0,['n.cod_version_modelo']);
        $q = $db->table(self::TABLA.' as d')->join('nodos_semanticos as n','n.cod_nodo_semantico','=','d.cod_nodo_semantico')
            ->where('d.tipo_declaracion',$entidad === 'variables_expertas' ? 'VARIABLE' : 'CRITERIO')->select($campos);
        return $db->query()->fromSub($q,$alias ?? $entidad);
    }

    public static function insertar(string $entidad, array $filas, ?string $conexion = null): void
    {
        $db = DB::connection($conexion);
        $db->transaction(function () use ($db,$entidad,$filas): void {
            foreach ($filas as $fila) {
                $campos = self::CAMPOS[$entidad];
                $id = array_key_first($campos);
                $propietario = $entidad === 'variables_expertas' ? 'cod_nodo_semantico' : 'cod_nodo_criterio';
                if (array_diff(array_keys($fila),self::columnas($entidad)) || ! isset($fila[$id],$fila[$propietario])) throw new DomainException('La declaración requiere identidad, nodo y campos declarados.');
                $nodos = $db->table('nodos_semanticos')->whereIn('cod_nodo_semantico',array_values(array_filter([$fila[$propietario],$fila['cod_nodo_propietario_primario'] ?? null])))
                    ->orderBy('cod_nodo_semantico')->sharedLock()->get(['cod_nodo_semantico','cod_version_modelo']);
                $version = $nodos->firstWhere('cod_nodo_semantico',$fila[$propietario])?->cod_version_modelo;
                if ($version === null || (isset($fila['cod_version_modelo']) && $fila['cod_version_modelo'] !== $version)) throw new DomainException('La versión de la declaración no corresponde a su nodo.');
                $datos = ['tipo_declaracion' => $entidad === 'variables_expertas' ? 'VARIABLE' : 'CRITERIO'];
                foreach ($campos as $logico => $fisico) if (array_key_exists($logico,$fila)) $datos[$fisico] = $fila[$logico];
                $db->table(self::TABLA)->insert($datos);
            }
        });
    }
}
