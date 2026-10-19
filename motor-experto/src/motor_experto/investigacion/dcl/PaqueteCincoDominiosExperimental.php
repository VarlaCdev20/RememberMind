<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\dcl;

use DomainException;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM;
use MotorExperto\dominio\conocimiento\CompiladorPerfilCognitivo;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;

/**
 * Revisión integrada de ingeniería: D-131 y G09 comparten grafo y versión.
 * Conserva el hash y la versión de cada fuente; rechaza colisiones en vez de sobrescribirlas.
 * Los conceptos MEM sin contrato de tarea siguen inactivos. No se publica conocimiento institucional.
 */
final class PaqueteCincoDominiosExperimental
{
    public function integrar(PaqueteConocimiento $g09): array
    {
        $mem = EjemploTecnicoCOGMEM::paquete();
        if (! $g09->soloPruebasTecnicas || $g09->tipoReglaResultado !== $mem->tipoReglaResultado) {
            throw new DomainException('La integración exige contratos de resultado compatibles y uso TEST_ONLY.');
        }
        if (in_array('COG-MEM', array_column($g09->tabla('nodos_semanticos'), 'codigo_semantico'), true)) {
            $origenes = [];
            foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
                foreach ($g09->tabla($tabla) as $fila) {
                    $origenes[$tabla][$fila[$pk]] = ['familia' => 'PERFIL', 'version' => $g09->version, 'id_original' => $fila[$pk]];
                }
            }

            // Un perfil completo recibido es su propia fuente declarada. No se atribuyen versiones ancestrales desconocidas.
            return ['paquete' => $g09, 'red' => (new CompiladorPerfilCognitivo)->compilar($g09),
                'fuentes' => ['PERFIL' => ['version' => $g09->version, 'sha256' => hash('sha256', serialize($g09->declaracion()))]], 'origenes' => $origenes];
        }
        $tablas = $contratos = $extracciones = $origenes = $fuentes = [];
        foreach (['COG-MEM' => $mem, 'G09' => $g09] as $familia => $p) {
            $declaracion = $p->declaracion();
            foreach (PaqueteConocimiento::CLAVES as $tabla => $pk) {
                usort($declaracion['tablas'][$tabla], static fn ($a, $b) => strcmp($a[$pk], $b[$pk]));
                foreach ($declaracion['tablas'][$tabla] as $fila) {
                    $id = $fila[$pk];
                    if (isset($tablas[$tabla][$id])) {
                        throw new DomainException('Colisión de identidades entre las fuentes del perfil.');
                    }
                    $tablas[$tabla][$id] = $fila;
                    $origenes[$tabla][$id] = ['familia' => $familia, 'version' => $p->version, 'id_original' => $id];
                }
            }
            $fuentes[$familia] = ['version' => $p->version, 'sha256' => hash('sha256', serialize($declaracion))];
            foreach ($p->contratosRelaciones as $contrato) {
                $clave = serialize([$contrato['tipo_relacion'], $contrato['tipo_origen'], $contrato['tipo_destino']]);
                if (isset($contratos[$clave]) && $contratos[$clave] !== $contrato) {
                    throw new DomainException('Contratos semánticos incompatibles entre familias.');
                }
                $contratos[$clave] = $contrato;
            }
            foreach ($p->contratosExtraccion as $tipo => $modo) {
                if (isset($extracciones[$tipo]) && $extracciones[$tipo] !== $modo) {
                    throw new DomainException('Contratos de extracción incompatibles entre familias.');
                }
                $extracciones[$tipo] = $modo;
            }
        }
        $catalogo = CatalogoPerfilCincoDominios::construir();
        $porCodigo = array_column($tablas['nodos_semanticos'], 'cod_nodo_semantico', 'codigo_semantico');
        // Completa únicamente la topología documental; no fabrica cuatro reglas de Memoria.
        foreach ($catalogo['nodos'] as $nodo) {
            if (isset($porCodigo[$nodo['codigo_semantico']])) {
                continue;
            }
            $id = $nodo['cod_nodo_semantico'];
            if (isset($tablas['nodos_semanticos'][$id])) {
                throw new DomainException('Colisión de identidad del catálogo conceptual.');
            }
            $tablas['nodos_semanticos'][$id] = $nodo;
            $porCodigo[$nodo['codigo_semantico']] = $id;
            $origenes['nodos_semanticos'][$id] = ['familia' => 'CONCEPTOS', 'version' => $catalogo['version'], 'id_original' => $id];
            $relacion = ['cod_relacion_semantica' => 'PERFIL-'.$id, 'cod_version_modelo' => $catalogo['version'],
                'cod_nodo_origen' => $id, 'cod_nodo_destino' => $porCodigo['COG-MEM'],
                'tipo_relacion' => 'SUBCOMPONENTE_DE', 'modalidad_relacion' => null, 'estado' => 'INACTIVO'];
            $tablas['relaciones_semanticas'][$relacion['cod_relacion_semantica']] = $relacion;
            $origenes['relaciones_semanticas'][$relacion['cod_relacion_semantica']] = ['familia' => 'CONCEPTOS',
                'version' => $catalogo['version'], 'id_original' => 'REL-'.$id];
        }
        $fuentes['CONCEPTOS'] = ['version' => $catalogo['version'], 'sha256' => hash('sha256', serialize($catalogo))];
        $version = 'SIM-5-'.substr(hash('sha256', serialize($fuentes)), 0, 14);
        foreach ($tablas as &$filas) {
            foreach ($filas as &$fila) {
                if (array_key_exists('cod_version_modelo', $fila)) {
                    $fila['cod_version_modelo'] = $version;
                }
            }
            unset($fila);
            $filas = array_values($filas);
        }
        unset($filas);
        $p = new PaqueteConocimiento($version, $tablas, array_values($contratos), true, $mem->tipoReglaResultado, $extracciones);
        $red = (new CompiladorPerfilCognitivo)->compilar($p);

        return ['paquete' => $p, 'red' => $red, 'fuentes' => $fuentes, 'origenes' => $origenes];
    }
}
