<?php

declare(strict_types=1);

namespace MotorExperto\dominio\conocimiento;

use DomainException;
use MotorExperto\dominio\conocimiento\red_semantica\RedSemantica;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de conocimiento, red conceptual cognitiva.
 * Proyecta la fuente documental vigente con cinco dominios y diecinueve componentes.
 * Las relaciones describen pertenencia; no activan reglas, pesos ni interpretaciones clínicas.
 */
final class PerfilConceptualCognitivo
{
    public const CANTIDADES = ['COG-MEM' => 4, 'COG-ATE' => 3, 'COG-EJE' => 5, 'COG-LEN' => 5, 'COG-VIS' => 2];

    public function construir(array $fuente): array
    {
        if (! preg_match('/^[a-f0-9]{64}$/D', $fuente['documento_maestro_sha256'] ?? '')
            || ! preg_match('/^[a-f0-9]{64}$/D', $fuente['matrices_sha256'] ?? '')) {
            throw new DomainException('La fuente conceptual debe identificar sus dos documentos.');
        }
        $version = 'CONCEPTOS-'.$fuente['fecha_consulta'];
        $nodos = $relaciones = $conceptos = $componentes = [];
        foreach ($fuente['nodos'] as $nodo) {
            $codigo = $nodo['codigo'];
            if (! isset(self::CANTIDADES[$codigo]) && ! preg_match('/^(MEM|ATE|EJE|LEN|VIS)-S0[1-5]$/D', $codigo)) {
                continue;
            }
            $tipo = isset(self::CANTIDADES[$codigo]) ? 'CRITERIO' : 'SUBCOMPONENTE';
            if ($nodo['tipo'] !== $tipo || isset($conceptos[$codigo])) {
                throw new DomainException('Concepto duplicado o de tipo incompatible.');
            }
            $conceptos[$codigo] = $nodo;
            $nodos[] = ['cod_nodo_semantico' => $codigo, 'codigo_semantico' => $codigo, 'nombre' => $nodo['nombre'],
                'tipo_nodo' => $tipo, 'cod_version_modelo' => $version, 'estado' => 'INACTIVO'];
        }
        foreach ($fuente['relaciones'] as $r) {
            if (! isset($conceptos[$r['origen']]) || ! in_array($r['relacion'], ['SUBCOMPONENTE_DE', 'SUBCOMPONENTE_CANDIDATO_DE'], true)) {
                continue;
            }
            if (($conceptos[$r['origen']]['tipo'] ?? null) !== 'SUBCOMPONENTE' || ! isset(self::CANTIDADES[$r['destino']]) || isset($componentes[$r['origen']])) {
                throw new DomainException('Un componente debe tener un único dominio primario.');
            }
            $componentes[$r['origen']] = ['dominio' => $r['destino'], 'nombre' => $conceptos[$r['origen']]['nombre'],
                'concepto' => $conceptos[$r['origen']], 'relacion_original' => $r];
            $relaciones[] = ['cod_relacion_semantica' => $r['codigo'], 'cod_nodo_origen' => $r['origen'],
                'cod_nodo_destino' => $r['destino'], 'tipo_relacion' => 'SUBCOMPONENTE_DE', 'modalidad_relacion' => null,
                'cod_version_modelo' => $version, 'estado' => 'INACTIVO'];
        }
        if (count($conceptos) !== 24 || count($componentes) !== 19 || ($componentes['EJE-S03']['dominio'] ?? null) !== 'COG-EJE') {
            throw new DomainException('La fuente no conserva el perfil cognitivo completo.');
        }
        $dominios = [];
        foreach (self::CANTIDADES as $codigo => $cantidad) {
            $propios = array_filter($componentes, fn ($c) => $c['dominio'] === $codigo);
            $prefijo = substr($codigo, 4);
            $esperados = array_map(fn ($i) => $prefijo.'-S0'.$i, range(1, $cantidad));
            if (count($propios) !== $cantidad || array_diff($esperados, array_keys($propios))) {
                throw new DomainException('Componentes ausentes o asignados a otro dominio.');
            }
            $dominios[$codigo] = ['nombre' => $conceptos[$codigo]['nombre'], 'concepto' => $conceptos[$codigo], 'componentes' => $propios];
        }
        $contratos = [['tipo_relacion' => 'SUBCOMPONENTE_DE', 'tipo_origen' => 'SUBCOMPONENTE', 'tipo_destino' => 'CRITERIO', 'modalidades' => [null]]];
        new RedSemantica($version, $nodos, $relaciones, $contratos);

        return ['version' => $version, 'documento_sha256' => $fuente['documento_maestro_sha256'], 'matrices_sha256' => $fuente['matrices_sha256'],
            'dominios' => $dominios, 'nodos' => $nodos, 'relaciones' => $relaciones, 'contratos' => $contratos, 'ejecutable_clinico' => false];
    }
}
