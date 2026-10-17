<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Entities\Conocimiento;

use App\SistemaExperto\Domain\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de conocimiento (Red semántica).
 *
 * Reúne los conceptos y relaciones explícitos de una versión del conocimiento.
 * Permite consultar el grafo conservando la identidad de sus nodos y aristas.
 *
 * TÍTULO: Instantánea inmutable del grafo semántico.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de conocimiento.
 *
 * RESPONSABILIDAD:
 * Validar unicidad de nodos, códigos y aristas y pertenencia de sus extremos.
 *
 * ENTRADAS:
 * Versión explícita, lista de nodos y lista de relaciones ya construidas.
 *
 * SALIDAS:
 * Mapas canónicos y consultas de conexiones directas existentes.
 *
 * RESTRICCIONES:
 * No completa caminos, no invierte relaciones ni calcula inferencias clínicas.
 * Conserva exactamente los nodos recibidos; las versiones nunca se fusionan.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R4/R10 y red versionada del motor D-123/D-137.
 */
final readonly class RedSemantica
{
    /** @var array<string, NodoSemantico> Nodos por identidad única. */
    public array $nodos;

    /** @var array<string, RelacionSemantica> Aristas por identidad única. */
    public array $relaciones;

    /**
     * Construye una instantánea consistente, incluso cuando está vacía.
     *
     * @param string $versionConocimiento Versión única del grafo.
     * @param list<NodoSemantico> $nodos Nodos explícitos.
     * @param list<RelacionSemantica> $relaciones Relaciones explícitas.
     * @throws DomainException Por colección mal tipada, duplicación o extremo ajeno.
     */
    public function __construct(
        /** Versión del grafo. */ public string $versionConocimiento,
        array $nodos,
        array $relaciones,
    ) {
        if ($versionConocimiento === '' || preg_match('/[\x00-\x20\x7F]/', $versionConocimiento) === 1
            || ! array_is_list($nodos) || ! array_is_list($relaciones)) {
            throw new DomainException('La red requiere una versión y listas explícitas.');
        }
        $mapa = $codigos = [];
        foreach ($nodos as $nodo) {
            if (! $nodo instanceof NodoSemantico || $nodo->versionConocimiento !== $versionConocimiento
                || isset($mapa[$nodo->id]) || isset($codigos[$nodo->codigo])) {
                throw new DomainException('Nodo duplicado, mal tipado o de otra versión.');
            }
            $mapa[$nodo->id] = $nodo;
            $codigos[$nodo->codigo] = true;
        }
        $aristas = $pares = [];
        foreach ($relaciones as $relacion) {
            if (! $relacion instanceof RelacionSemantica || $relacion->versionConocimiento !== $versionConocimiento
                || ($mapa[$relacion->origen->id] ?? null) !== $relacion->origen
                || ($mapa[$relacion->destino->id] ?? null) !== $relacion->destino) {
                throw new DomainException('Arista ajena a la instantánea del grafo.');
            }
            $clave = serialize([$relacion->origen->id, $relacion->tipo->value, $relacion->destino->id]);
            if (isset($aristas[$relacion->id]) || isset($pares[$clave])) {
                throw new DomainException('Arista semántica duplicada.');
            }
            $aristas[$relacion->id] = $relacion;
            $pares[$clave] = true;
        }
        $this->nodos = $mapa;
        $this->relaciones = $aristas;
    }

    /**
     * Recupera un nodo existente, sin fabricar un reemplazo.
     *
     * @param string $id Identidad solicitada.
     * @return NodoSemantico Nodo original.
     * @throws DomainException Si no pertenece al grafo.
     */
    public function nodo(string $id): NodoSemantico
    {
        return $this->nodos[$id] ?? throw new DomainException('Nodo ausente de la red.');
    }

    /**
     * Consulta exclusivamente aristas salientes directas.
     *
     * @param string $origenId Nodo de origen existente.
     * @param TipoRelacionSemanticaEnum|null $tipo Predicado opcional de filtro.
     * @return list<RelacionSemantica> Aristas originales, nunca inferidas.
     * @throws DomainException Si el origen no pertenece al grafo.
     */
    public function salientes(string $origenId, ?TipoRelacionSemanticaEnum $tipo = null): array
    {
        $this->nodo($origenId);

        return array_values(array_filter($this->relaciones, static fn (RelacionSemantica $r): bool =>
            $r->origen->id === $origenId && ($tipo === null || $r->tipo === $tipo)));
    }

}
