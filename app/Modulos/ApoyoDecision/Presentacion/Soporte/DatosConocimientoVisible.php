<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Soporte;

use DomainException;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;

/** Relaciones de lectura por identidad exacta en una instantánea ya autorizada. */
final class DatosConocimientoVisible
{
    public const CATEGORIAS = [
        'nodos_semanticos' => ['Conceptos', 'ph-graph'],
        'reglas_expertas' => ['Reglas', 'ph-gear'],
        'condiciones_regla_experta' => ['Condiciones', 'ph-list-checks'],
        'consecuencias_regla_experta' => ['Consecuencias', 'ph-git-branch'],
        'variables_expertas' => ['Variables', 'ph-sliders-horizontal'],
        'fuentes_datos_expertas' => ['Fuentes', 'ph-books'],
        'relaciones_semanticas' => ['Relaciones', 'ph-share-network'],
        'mapeos_variables_fuente' => ['Mapeos de variables', 'ph-link'],
        'mapeos_valores_fuente' => ['Mapeos de valores', 'ph-arrows-left-right'],
        'dominios_valores_expertos' => ['Dominios de valores', 'ph-stack'],
        'valores_semanticos' => ['Valores', 'ph-tag'],
        'criterios_dominios_resultado' => ['Criterios', 'ph-target'],
    ];

    private const ALIAS = [
        'cod_nodo_origen' => 'cod_nodo_semantico', 'cod_nodo_destino' => 'cod_nodo_semantico',
        'cod_nodo_criterio' => 'cod_nodo_semantico', 'cod_nodo_propietario_primario' => 'cod_nodo_semantico',
        'cod_regla_anterior' => 'cod_regla_experta',
    ];

    public function nombre(array $fila, string $id): string
    {
        foreach (['nombre', 'codigo_regla', 'codigo_semantico', 'codigo_valor', 'codigo_dominio', 'tipo_relacion'] as $campo) {
            if (is_string($fila[$campo] ?? null) && trim($fila[$campo]) !== '') {
                return $fila[$campo];
            }
        }

        return $id;
    }

    public function categorias(array $tablas): array
    {
        $salida = [];
        foreach (self::CATEGORIAS as $tabla => [$nombre, $icono]) {
            $salida[$tabla] = compact('nombre', 'icono') + ['cantidad' => count($tablas[$tabla] ?? [])];
        }

        return $salida;
    }

    public function detalle(array $tablas, string $tabla, string $id): array
    {
        if (! isset(self::CATEGORIAS[$tabla])) {
            throw new DomainException('Categoría de conocimiento desconocida.');
        }
        $indice = [];
        foreach (PaqueteConocimiento::CLAVES as $coleccion => $pk) {
            $indice[$coleccion] = array_column($tablas[$coleccion] ?? [], null, $pk);
        }
        $fila = $indice[$tabla][$id] ?? throw new DomainException('Registro ausente en la versión seleccionada.');
        $enlaces = [];
        foreach ($indice as $origen => $filas) {
            foreach ($filas as $origenId => $registro) {
                foreach ($registro as $campo => $valor) {
                    if ($campo === PaqueteConocimiento::CLAVES[$origen] || ! is_string($valor) || $valor === '') {
                        continue;
                    }
                    $destino = array_search(self::ALIAS[$campo] ?? $campo, PaqueteConocimiento::CLAVES, true);
                    if ($destino === false || ! isset($indice[$destino][$valor])) {
                        continue;
                    }
                    $saliente = $origen === $tabla && $origenId === $id;
                    $entrante = $destino === $tabla && $valor === $id;
                    if (! $saliente && ! $entrante) {
                        continue;
                    }
                    $otraTabla = $saliente ? $destino : $origen;
                    $otroId = $saliente ? $valor : (string) $origenId;
                    if ($otraTabla === $tabla && $otroId === $id) {
                        continue;
                    }
                    $otraFila = $indice[$otraTabla][$otroId];
                    $clave = $origen.':'.$origenId.':'.$campo;
                    $enlaces[$clave] = ['tabla' => $otraTabla, 'id' => $otroId,
                        'nombre' => $this->nombre($otraFila, $otroId), 'categoria' => self::CATEGORIAS[$otraTabla][0],
                        'campo' => $campo, 'direccion' => $saliente ? 'Referencia a' : 'Utilizado por'];
                }
            }
        }
        $nombre = $this->nombre($fila, $id);
        $nodos = [['id' => 'actual', 'nombre' => $nombre, 'tipo' => self::CATEGORIAS[$tabla][0],
            'estado' => $fila['estado'] ?? 'Registro declarado', 'columna' => 1, 'tabla' => $tabla, 'registro' => $id]];
        $aristas = [];
        $vistos = [];
        foreach (array_slice(array_values($enlaces), 0, 18) as $enlace) {
            $nid = hash('sha256', $enlace['tabla'].':'.$enlace['id']);
            if (! isset($vistos[$nid])) {
                $nodos[] = ['id' => $nid, 'nombre' => $enlace['nombre'], 'tipo' => $enlace['categoria'],
                    'estado' => $enlace['direccion'], 'columna' => $enlace['direccion'] === 'Referencia a' ? 2 : 0,
                    'tabla' => $enlace['tabla'], 'registro' => $enlace['id']];
                $vistos[$nid] = true;
            }
            $aristas[] = ['origen' => $enlace['direccion'] === 'Referencia a' ? 'actual' : $nid,
                'destino' => $enlace['direccion'] === 'Referencia a' ? $nid : 'actual',
                'relacion' => str_replace('cod_', '', $enlace['campo'])];
        }

        return ['tabla' => $tabla, 'id' => $id, 'nombre' => $nombre, 'categoria' => self::CATEGORIAS[$tabla][0],
            'icono' => self::CATEGORIAS[$tabla][1], 'campos' => $fila, 'enlaces' => array_values($enlaces),
            'grafo' => ['nodos' => $nodos, 'aristas' => $aristas]];
    }
}
