<?php

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos as C;
use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Traduce identidades internas del paquete puro hacia las PK de almacenamiento. */
final class IdentidadesConocimiento implements \App\Modulos\ApoyoDecision\Aplicacion\Contratos\IdentidadesConocimiento
{
    private const NATURALES = [
        'versiones_modelo_experto' => ['codigo_version'],
        'nodos_semanticos' => ['codigo_semantico'],
        'dominios_valores_expertos' => ['codigo_dominio'],
        'valores_semanticos' => ['cod_dominio_valores', 'codigo_valor'],
        'fuentes_datos_expertas' => ['codigo_fuente'],
        'variables_expertas' => ['cod_nodo_semantico'],
        'mapeos_variables_fuente' => ['cod_variable_experta', 'cod_fuente_dato_experta', 'tipo_extraccion', 'campo_valor', 'clave_selector'],
        'mapeos_valores_fuente' => ['cod_mapeo_variable_fuente', 'tipo_valor_fuente', 'valor_fuente_exacto'],
        'criterios_dominios_resultado' => ['cod_nodo_criterio'],
        'relaciones_semanticas' => ['cod_nodo_origen', 'tipo_relacion', 'cod_nodo_destino'],
    ];

    public function traducir(array $plantilla, string $versionPlantilla, string $versionReal, bool $existente): array
    {
        $mapa = ['versiones_modelo_experto' => [$versionPlantilla => $versionReal]];
        $referencias = [];
        foreach ($plantilla as $tabla => $filas) {
            foreach (DB::getSchemaBuilder()->getForeignKeys($tabla) as $fk) {
                foreach ($fk['columns'] as $i => $campo) {
                    if ((C::ENTIDADES[$fk['foreign_table']][0] ?? null) === $fk['foreign_columns'][$i]) {
                        $referencias[$tabla][$campo] = $fk['foreign_table'];
                    }
                }
            }
        }
        if (! $existente) {
            foreach ($plantilla as $tabla => $filas) {
                $pk = C::clave($tabla);
                foreach ($filas as $fila) {
                    $mapa[$tabla][$fila[$pk]] ??= app(GeneradorCodigo::class)->siguiente($tabla);
                }
            }
        }
        $salida = [];
        foreach ($plantilla as $tabla => $filas) {
            $pk = C::clave($tabla);
            foreach ($filas as $fila) {
                if ($tabla === 'reglas_expertas') {
                    // Las plantillas anteriores y las reglas nuevas comparten el contrato nullable aprobado.
                    $fila = array_replace(['cod_regla_anterior' => null, 'estado_revision' => null, 'cod_usuario_revision' => null,
                        'fecha_hora_revision' => null, 'justificacion_revision' => null], $fila);
                }
                $anterior = $fila[$pk];
                foreach ($referencias[$tabla] ?? [] as $campo => $destino) {
                    if (($fila[$campo] ?? null) !== null && isset($mapa[$destino][$fila[$campo]])) {
                        $fila[$campo] = $mapa[$destino][$fila[$campo]];
                    }
                }
                if ($existente && $tabla !== 'versiones_modelo_experto') {
                    $q = DB::table($tabla);
                    if (array_key_exists('cod_version_modelo', $fila)) {
                        $q->where('cod_version_modelo', $versionReal);
                    }
                    foreach (self::NATURALES[$tabla] ?? throw new DomainException('Tabla sin identidad semántica de carga.') as $campo) {
                        if (! array_key_exists($campo, $fila)) {
                            throw new DomainException('Contrato semántico incompleto: '.$tabla.'.'.$campo);
                        }
                        $q->where($campo, $fila[$campo]);
                    }
                    $coincidencias = $q->get([$pk]);
                    if ($coincidencias->count() !== 1) {
                        throw new DomainException('El conocimiento conservado tiene una identidad semántica ausente o ambigua.');
                    }
                    $mapa[$tabla][$anterior] = $coincidencias->sole()->$pk;
                }
                $fila[$pk] = $mapa[$tabla][$anterior];
                $salida[$tabla][] = $fila;
            }
            $salida[$tabla] ??= [];
        }

        return $salida;
    }
}
