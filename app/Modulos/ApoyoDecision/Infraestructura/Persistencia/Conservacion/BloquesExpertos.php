<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use DomainException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/** Proyección de entidades singulares sobre su propietario físico. */
final class BloquesExpertos
{
    private const TIPADOS = [
        'evaluaciones_reglas' => ['evaluaciones_reglas', 'cod_elemento_evaluacion', 'REGLA', 'cod_evaluacion_regla', ['cod_traza_inferencia', 'cod_regla_experta', 'estado_regla', 'fecha_hora_evaluacion']],
        'evaluaciones_condiciones_regla' => ['evaluaciones_reglas', 'cod_elemento_evaluacion', 'CONDICION', 'cod_evaluacion_condicion', ['cod_evaluacion_regla_padre as cod_evaluacion_regla', 'cod_condicion_regla', 'estado_condicion', 'fecha_hora_evaluacion']],
        'reglas_aplicadas_dcl_instantanea' => ['reglas_aplicadas_dcl_instantanea', 'cod_elemento_regla_dcl', 'REGLA', 'cod_regla_aplicada_dcl', ['cod_clasificacion_dcl_instantanea', 'cod_regla_experta', 'id_regla_dominio', 'fuente', 'habilitada', 'concepto_consecuencia', 'consecuencia_tipo', 'consecuencia_texto', 'consecuencia_entero', 'consecuencia_real_ieee754', 'consecuencia_logico', 'orden']],
        'condiciones_regla_instantanea' => ['reglas_aplicadas_dcl_instantanea', 'cod_elemento_regla_dcl', 'CONDICION', 'cod_condicion_instantanea', ['cod_regla_propietaria as cod_regla_aplicada_dcl', 'cod_condicion_padre', 'operador', 'variable', 'literal_tipo', 'literal_texto', 'literal_entero', 'literal_real_ieee754', 'literal_logico', 'orden']],
        'condiciones_regla_experta' => ['componentes_regla_experta', 'cod_componente_regla', 'CONDICION',
            'cod_condicion_regla', ['cod_regla_experta', 'cod_variable_experta', 'cod_valor_semantico']],
        'consecuencias_regla_experta' => ['componentes_regla_experta', 'cod_componente_regla', 'CONSECUENCIA',
            'cod_consecuencia_regla', ['cod_regla_experta', 'cod_criterio_dominio_resultado', 'cod_valor_semantico']],
        'evidencias_criterio_evaluacion' => ['vinculos_evidencia_experta', 'cod_vinculo_evidencia', 'CRITERIO', 'cod_evidencia_criterio', ['cod_evaluacion_criterio',
            'cod_evidencia_evaluacion', 'rol_en_criterio', 'estado_participacion', 'justificacion', 'fecha_hora_vinculacion',
            'orden_conservacion', 'cod_hecho_instantanea', 'cod_soporte_sintetico']],
        'evidencias_soporte_condicion' => ['vinculos_evidencia_experta', 'cod_vinculo_evidencia', 'CONDICION', 'cod_evidencia_soporte_condicion', ['cod_evaluacion_condicion',
            'cod_evidencia_evaluacion']],
    ];

    private const PARENTESCOS = ['evaluaciones_condiciones_regla' => ['cod_evaluacion_regla' => 'cod_evaluacion_regla_padre'],
        'condiciones_regla_instantanea' => ['cod_regla_aplicada_dcl' => 'cod_regla_propietaria']];

    public const DEFINICIONES = [
        'resultados_criterio' => ['tabla' => 'evaluacion_criterios', 'propietario' => 'cod_evaluacion_criterio', 'id' => 'cod_resultado_criterio',
            'campos' => ['cod_resultado_criterio' => 'cod_resultado_criterio', 'cod_evaluacion_criterio' => 'cod_evaluacion_criterio',
                'cod_valor_semantico' => 'resultado_cod_valor_semantico', 'fecha_hora_determinacion' => 'resultado_fecha_hora_determinacion',
                'justificacion_original' => 'resultado_justificacion_original']],
        'trazas_inferencia' => ['tabla' => 'evaluacion_criterios', 'propietario' => 'cod_evaluacion_criterio', 'id' => 'cod_traza_inferencia',
            'campos' => ['cod_traza_inferencia' => 'cod_traza_inferencia', 'cod_evaluacion_criterio' => 'cod_evaluacion_criterio',
                'estado_inferencia' => 'traza_estado_inferencia', 'fecha_hora_inicio' => 'traza_fecha_hora_inicio', 'fecha_hora_fin' => 'traza_fecha_hora_fin']],
        'clasificaciones_dcl_instantanea' => ['tabla' => 'evaluaciones_expertas', 'propietario' => 'cod_evaluacion_experta', 'id' => 'cod_clasificacion_dcl_instantanea',
            'campos' => ['cod_clasificacion_dcl_instantanea' => 'cod_clasificacion_dcl_instantanea', 'cod_evaluacion_experta' => 'cod_evaluacion_experta',
                'cod_funcionalidad_instantanea' => 'cod_funcionalidad_instantanea', 'version_contrato' => 'dcl_version_contrato',
                'diferencial' => 'dcl_diferencial', 'patron_candidato' => 'dcl_patron_candidato', 'justificacion' => 'dcl_justificacion',
                'uso' => 'dcl_uso', 'huella_clasificacion' => 'dcl_huella_clasificacion',
                'funcionalidad_independencia_relativa' => 'funcionalidad_independencia_relativa',
                'funcionalidad_revisor_sintetico_id' => 'funcionalidad_revisor_sintetico_id', 'funcionalidad_justificacion' => 'funcionalidad_justificacion']],
    ];

    private static function definicion(string $entidad): ?array
    {
        return self::DEFINICIONES[$entidad] ?? null;
    }

    public static function tablaFisica(string $entidad): string
    {
        if (DeclaracionesNodo::admite($entidad)) return DeclaracionesNodo::TABLA;
        if (isset(self::TIPADOS[$entidad])) {
            return self::TIPADOS[$entidad][0];
        }

        return self::definicion($entidad)['tabla'] ?? $entidad;
    }

    public static function columnasLogicas(string $entidad): ?array
    {
        if (DeclaracionesNodo::admite($entidad)) return DeclaracionesNodo::columnas($entidad);
        if (isset(self::TIPADOS[$entidad])) {
            [, , , $clave, $columnas] = self::TIPADOS[$entidad];

            return [$clave, ...$columnas];
        }
        $definicion = self::definicion($entidad);

        return $definicion === null ? null : array_keys($definicion['campos']);
    }

    public static function consulta(string $entidad, ?string $conexion = null, ?string $alias = null): Builder
    {
        if (DeclaracionesNodo::admite($entidad)) return DeclaracionesNodo::consulta($entidad,$conexion,$alias);
        $db = DB::connection($conexion);
        if (isset(self::TIPADOS[$entidad])) {
            [$tabla, $codigo, $tipo, $clave, $columnas] = self::TIPADOS[$entidad];
            $origen = $db->table($tabla)->where('tipo_evento', $tipo)
                ->select([$codigo.' as '.$clave, ...$columnas]);

            return $db->query()->fromSub($origen, $alias ?? $entidad);
        }
        if (($d = self::definicion($entidad)) === null) {
            return isset(\App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas::CONTRATOS[$entidad])
                ? \App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas::consulta($db, $entidad, $alias)
                : $db->table($entidad, $alias);
        }
        $columnas = array_map(static fn ($col, $nombre) => $col.' as '.$nombre, $d['campos'], array_keys($d['campos']));
        $origen = $db->table($d['tabla'])->select($columnas)->whereNotNull($d['id']);

        return $db->query()->fromSub($origen, $alias ?? $entidad);
    }

    public static function insertar(string $entidad, array $filas, ?string $conexion = null): void
    {
        if ($filas === []) {
            return;
        }
        $filas = array_is_list($filas) ? $filas : [$filas];
        if (DeclaracionesNodo::admite($entidad)) { DeclaracionesNodo::insertar($entidad,$filas,$conexion); return; }
        $db = DB::connection($conexion);
        if (isset(self::TIPADOS[$entidad])) {
            [$tabla, $codigo, $tipo, $clave] = self::TIPADOS[$entidad];
            $db->table($tabla)->insert(array_map(static function (array $fila) use ($entidad, $codigo, $tipo, $clave): array {
                $datos = array_diff_key($fila, [$clave => true]);
                foreach (self::PARENTESCOS[$entidad] ?? [] as $logico => $fisico) {
                    if (array_key_exists($logico, $datos)) {
                        $datos[$fisico] = $datos[$logico];
                        unset($datos[$logico]);
                    }
                }

                return ['tipo_evento' => $tipo, $codigo => $fila[$clave]] + $datos;
            }, $filas));

            return;
        }
        if (($d = self::definicion($entidad)) === null) {
            $db->table($entidad)->insert(array_map(fn ($fila) => \App\Compartido\Infraestructura\Persistencia\ColumnasRelacionadas::preparar($db, $entidad, $fila), $filas));

            return;
        }
        $db->transaction(function () use ($db, $d, $filas): void {
            foreach ($filas as $fila) {
                if (array_diff(array_keys($fila), array_keys($d['campos'])) || ! isset($fila[$d['id']], $fila[$d['propietario']])) {
                    throw new DomainException('El bloque experto requiere identidad, propietario y columnas declaradas.');
                }
                $q = $db->table($d['tabla'])->where($d['campos'][$d['propietario']], $fila[$d['propietario']])->whereNull($d['id']);
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
