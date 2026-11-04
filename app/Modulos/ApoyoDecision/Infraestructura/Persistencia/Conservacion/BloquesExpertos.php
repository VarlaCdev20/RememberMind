<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use DomainException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Proyección de cuatro entidades singulares sobre su propietario físico; no crea vistas ni tablas paralelas. */
final class BloquesExpertos
{
    public const DEFINICIONES = [
        'resultados_criterio' => ['tabla' => 'evaluacion_criterios', 'propietario' => 'cod_evaluacion_criterio', 'id' => 'cod_resultado_criterio',
            'campos' => ['cod_resultado_criterio' => 'cod_resultado_criterio', 'cod_evaluacion_criterio' => 'cod_evaluacion_criterio',
                'cod_valor_semantico' => 'resultado_cod_valor_semantico', 'fecha_hora_determinacion' => 'resultado_fecha_hora_determinacion',
                'justificacion_original' => 'resultado_justificacion_original']],
        'trazas_inferencia' => ['tabla' => 'evaluacion_criterios', 'propietario' => 'cod_evaluacion_criterio', 'id' => 'cod_traza_inferencia',
            'campos' => ['cod_traza_inferencia' => 'cod_traza_inferencia', 'cod_evaluacion_criterio' => 'cod_evaluacion_criterio',
                'estado_inferencia' => 'traza_estado_inferencia', 'fecha_hora_inicio' => 'traza_fecha_hora_inicio', 'fecha_hora_fin' => 'traza_fecha_hora_fin']],
        'variables_expertas' => ['tabla' => 'nodos_semanticos', 'propietario' => 'cod_nodo_semantico', 'id' => 'cod_variable_experta',
            'campos' => ['cod_variable_experta' => 'cod_variable_experta', 'cod_version_modelo' => 'cod_version_modelo', 'cod_nodo_semantico' => 'cod_nodo_semantico',
                'cod_nodo_propietario_primario' => 'cod_nodo_propietario_primario', 'cod_dominio_valores' => 'cod_dominio_valores',
                'tipo_semantico' => 'tipo_semantico', 'papel_inferencial' => 'papel_inferencial', 'estado' => 'estado_variable',
                'fecha_hora_creacion' => 'fecha_hora_creacion_variable', 'cod_usuario_creacion' => 'cod_usuario_creacion_variable', 'observacion' => 'observacion_variable']],
        'clasificaciones_dcl_instantanea' => ['tabla' => 'evaluaciones_expertas', 'propietario' => 'cod_evaluacion_experta', 'id' => 'cod_clasificacion_dcl_instantanea',
            'campos' => ['cod_clasificacion_dcl_instantanea' => 'cod_clasificacion_dcl_instantanea', 'cod_evaluacion_experta' => 'cod_evaluacion_experta',
                'cod_funcionalidad_instantanea' => 'cod_funcionalidad_instantanea', 'version_contrato' => 'dcl_version_contrato',
                'diferencial' => 'dcl_diferencial', 'patron_candidato' => 'dcl_patron_candidato', 'justificacion' => 'dcl_justificacion',
                'uso' => 'dcl_uso', 'huella_clasificacion' => 'dcl_huella_clasificacion',
                'funcionalidad_independencia_relativa' => 'funcionalidad_independencia_relativa',
                'funcionalidad_revisor_sintetico_id' => 'funcionalidad_revisor_sintetico_id', 'funcionalidad_justificacion' => 'funcionalidad_justificacion']],
    ];

    public static function consulta(string $entidad, ?string $conexion = null): Builder
    {
        $db = DB::connection($conexion);
        if (! isset(self::DEFINICIONES[$entidad])) {
            return $db->table($entidad);
        }
        $d = self::DEFINICIONES[$entidad];
        $columnas = array_map(static fn ($col, $nombre) => $col.' as '.$nombre, $d['campos'], array_keys($d['campos']));
        $origen = $db->table($d['tabla'])->select($columnas)->whereNotNull($d['id']);

        return $db->query()->fromSub($origen, $entidad);
    }

    public static function insertar(string $entidad, array $filas, ?string $conexion = null): void
    {
        if ($filas === []) {
            return;
        }
        $filas = array_is_list($filas) ? $filas : [$filas];
        $db = DB::connection($conexion);
        if (! isset(self::DEFINICIONES[$entidad])) {
            $db->table($entidad)->insert($filas);

            return;
        }
        $d = self::DEFINICIONES[$entidad];
        $db->transaction(function () use ($db, $d, $filas): void {
            foreach ($filas as $fila) {
                if (array_diff(array_keys($fila), array_keys($d['campos'])) || ! isset($fila[$d['id']], $fila[$d['propietario']])) {
                    throw new DomainException('El bloque experto requiere identidad, propietario y columnas declaradas.');
                }
                $q = $db->table($d['tabla'])->where($d['propietario'], $fila[$d['propietario']])->whereNull($d['id']);
                if (isset($fila['cod_version_modelo'])) {
                    $q->where('cod_version_modelo', $fila['cod_version_modelo']);
                }
                $cambios = [];
                foreach ($fila as $campo => $valor) {
                    if ($campo !== $d['propietario'] && $campo !== 'cod_version_modelo') {
                        $cambios[$d['campos'][$campo]] = $valor;
                    }
                }
                if ($q->update($cambios) !== 1) {
                    throw new DomainException('El bloque ya existe o su propietario/version no corresponde.');
                }
            }
        });
    }

    /** Datos lógicos exactos del nodo original; excluye atributos del bloque de variable. */
    public static function nodoOriginal(array $fila): array
    {
        return array_intersect_key($fila, array_flip(['cod_nodo_semantico', 'cod_version_modelo', 'codigo_semantico', 'nombre',
            'tipo_nodo', 'definicion', 'estado', 'fecha_hora_creacion', 'cod_usuario_creacion', 'observacion']));
    }
}
