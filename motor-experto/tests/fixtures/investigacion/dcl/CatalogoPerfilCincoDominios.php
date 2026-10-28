<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\dcl;

use MotorExperto\dominio\conocimiento\red_semantica\RedSemantica;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental;

/**
 * Catálogo conceptual D-108/D-135: cinco dominios y diecinueve subcomponentes.
 * Es una red documental inactiva; no es una versión ejecutable que mezcle paquetes.
 * Conserva la frontera EJE-S03/Memoria y no crea reglas para los cuatro conceptos MEM.
 */
final class CatalogoPerfilCincoDominios
{
    public const VERSION = 'PERFIL-CONCEP-1';

    public const DOMINIOS = ['COG-MEM' => 'Aprendizaje y memoria', 'COG-ATE' => 'Atención',
        'COG-EJE' => 'Función ejecutiva', 'COG-LEN' => 'Lenguaje', 'COG-VIS' => 'Función visuoespacial'];

    public static function subcomponentes(): array
    {
        $subcomponentes = [];
        foreach (['MEM-S01' => 'Aprendizaje y adquisición', 'MEM-S02' => 'Recuerdo diferido libre',
            'MEM-S03' => 'Recuerdo con claves', 'MEM-S04' => 'Reconocimiento'] as $codigo => $nombre) {
            $subcomponentes[$codigo] = ['dominio' => 'COG-MEM', 'nombre' => $nombre, 'reglas' => []];
        }
        foreach (CatalogoReglasG09Experimental::todas() as $regla => $meta) {
            $subcomponentes[$meta['componente']] = ['dominio' => $meta['criterio'], 'nombre' => $meta['descripcion'], 'reglas' => [$regla]];
        }

        return $subcomponentes;
    }

    public static function construir(): array
    {
        $nodos = $relaciones = [];
        foreach (self::DOMINIOS as $codigo => $nombre) {
            $nodos[] = ['cod_nodo_semantico' => $codigo, 'codigo_semantico' => $codigo, 'nombre' => $nombre,
                'tipo_nodo' => 'CRITERIO', 'cod_version_modelo' => self::VERSION, 'estado' => 'INACTIVO'];
        }
        foreach (self::subcomponentes() as $codigo => $meta) {
            $nodos[] = ['cod_nodo_semantico' => $codigo, 'codigo_semantico' => $codigo, 'nombre' => $meta['nombre'],
                'tipo_nodo' => 'SUBCOMPONENTE', 'cod_version_modelo' => self::VERSION, 'estado' => 'INACTIVO'];
            $relaciones[] = ['cod_relacion_semantica' => 'REL-'.$codigo, 'cod_nodo_origen' => $codigo,
                'cod_nodo_destino' => $meta['dominio'], 'tipo_relacion' => 'SUBCOMPONENTE_DE',
                'modalidad_relacion' => null, 'cod_version_modelo' => self::VERSION, 'estado' => 'INACTIVO'];
        }
        $contratos = [['tipo_relacion' => 'SUBCOMPONENTE_DE', 'tipo_origen' => 'SUBCOMPONENTE',
            'tipo_destino' => 'CRITERIO', 'modalidades' => [null]]];
        new RedSemantica(self::VERSION, $nodos, $relaciones, $contratos);

        return ['version' => self::VERSION, 'uso' => 'CATALOGO_CONCEPTUAL_NO_EJECUTABLE',
            'fuente' => 'Documento Maestro D-108/D-135 y catálogo conceptual del proyecto; cotejo 18/10/2026',
            'nodos' => $nodos, 'relaciones' => $relaciones, 'contratos' => $contratos];
    }
}
