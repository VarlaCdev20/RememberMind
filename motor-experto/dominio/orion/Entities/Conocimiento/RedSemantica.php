<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Conocimiento;

use MotorExperto\dominio\orion\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use DomainException;

/** Conserva los conceptos y vínculos explícitos de una versión; no añade conexiones. */
final readonly class RedSemantica
{
    /** @var array<string, NodoSemantico> Nodos por identidad única. */
    public array $nodos;

    /** @var array<string, RelacionSemantica> Aristas por identidad única. */
    public array $relaciones;

    /**
     * Construye una instantánea consistente, incluso cuando está vacía.
     * @param string $versionConocimiento
     * @param list<NodoSemantico> $nodos
     * @param list<RelacionSemantica> $relaciones
     * @throws DomainException
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
     * @param string $id
     * @return NodoSemantico
     * @throws DomainException
     */
    public function nodo(string $id): NodoSemantico
    {
        return $this->nodos[$id] ?? throw new DomainException('Nodo ausente de la red.');
    }

    /**
     * Consulta exclusivamente aristas salientes directas.
     * @param string $origenId
     * @param TipoRelacionSemanticaEnum|null $tipo
     * @return list<RelacionSemantica>
     * @throws DomainException
     */
    public function salientes(string $origenId, ?TipoRelacionSemanticaEnum $tipo = null): array
    {
        $this->nodo($origenId);

        return array_values(array_filter($this->relaciones, static fn (RelacionSemantica $r): bool =>
            $r->origen->id === $origenId && ($tipo === null || $r->tipo === $tipo)));
    }

}
