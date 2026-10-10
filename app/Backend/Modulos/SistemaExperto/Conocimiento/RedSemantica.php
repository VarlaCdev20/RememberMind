<?php

namespace App\Backend\Modulos\SistemaExperto\Conocimiento;

use DomainException;

/** Grafo dirigido, sin jerarquía ni inferencia clínica implícitas. */
final class RedSemantica
{
    private array $nodos = [];

    private array $relaciones = [];

    /** Los contratos de predicado/tipos/modalidades pertenecen al paquete versionado. */
    public function __construct(public readonly string $version, array $nodos, array $relaciones, array $contratos)
    {
        if ($version === '') {
            throw new DomainException('La red requiere una versión identificable.');
        }
        $codigos = [];
        foreach ($nodos as $nodo) {
            $id = $nodo['cod_nodo_semantico'];
            if ($nodo['cod_version_modelo'] !== $version || isset($this->nodos[$id]) || isset($codigos[$nodo['codigo_semantico']])) {
                throw new DomainException('Nodo duplicado o de otra versión.');
            }
            $this->nodos[$id] = $nodo;
            $codigos[$nodo['codigo_semantico']] = true;
        }
        $pares = [];
        foreach ($relaciones as $relacion) {
            $origen = $this->nodo($relacion['cod_nodo_origen']);
            $destino = $this->nodo($relacion['cod_nodo_destino']);
            if ($relacion['cod_version_modelo'] !== $version) {
                throw new DomainException('La relación pertenece a otra versión.');
            }
            $validos = array_filter($contratos, fn ($c) => $c['tipo_relacion'] === $relacion['tipo_relacion']
                && $c['tipo_origen'] === $origen['tipo_nodo'] && $c['tipo_destino'] === $destino['tipo_nodo']
                && in_array($relacion['modalidad_relacion'] ?? null, $c['modalidades'], true));
            if (count($validos) !== 1) {
                throw new DomainException('Relación sin contrato único compatible de tipos y modalidad.');
            }
            $clave = serialize([$relacion['cod_nodo_origen'], $relacion['tipo_relacion'], $relacion['cod_nodo_destino']]);
            $id = $relacion['cod_relacion_semantica'];
            if (isset($pares[$clave]) || isset($this->relaciones[$id])) {
                throw new DomainException('Relación semántica duplicada.');
            }
            $pares[$clave] = true;
            $this->relaciones[$id] = $relacion;
        }
    }

    public function nodo(string $id): array
    {
        return $this->nodos[$id] ?? throw new DomainException('Nodo ausente de la versión solicitada.');
    }

    public function nodos(): array
    {
        return array_values($this->nodos);
    }

    public function entrantes(string $id, ?string $tipo = null): array
    {
        $this->nodo($id);

        return array_values(array_filter($this->relaciones, fn ($r) => $r['cod_nodo_destino'] === $id && ($tipo === null || $r['tipo_relacion'] === $tipo)));
    }

    public function salientes(string $id, ?string $tipo = null): array
    {
        $this->nodo($id);

        return array_values(array_filter($this->relaciones, fn ($r) => $r['cod_nodo_origen'] === $id && ($tipo === null || $r['tipo_relacion'] === $tipo)));
    }

    public function conexiones(string $id, string $tipo, bool $entrantes = true): array
    {
        $relaciones = $entrantes ? $this->entrantes($id, $tipo) : $this->salientes($id, $tipo);

        return array_map(fn ($r) => ['nodo' => $this->nodo($r[$entrantes ? 'cod_nodo_origen' : 'cod_nodo_destino']), 'relacion' => $r], $relaciones);
    }
}
