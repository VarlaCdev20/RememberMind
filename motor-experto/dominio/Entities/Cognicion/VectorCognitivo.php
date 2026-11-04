<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Entities\Cognicion;

use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\ValueObjects\Cognicion\DominioCognitivoEnum;
use DomainException;

/** Reúne los cinco dominios del mismo caso, sin puntuación global ni soportes duplicados. */
final readonly class VectorCognitivo
{
    /** Contexto común, incluidos evaluación, sujeto, versión y fecha de corte. */
    public MemoriaTrabajo $memoria;

    /** @var array<string, ResultadoDominio> Cinco resultados en orden documental. */
    public array $resultados;

    /**
     * Construye un perfil completo sin inventar resultados faltantes.
     * @param list<ResultadoDominio> $resultados
     * @throws DomainException
     */
    public function __construct(array $resultados)
    {
        if (! array_is_list($resultados) || count($resultados) !== count(DominioCognitivoEnum::cases())) {
            throw new DomainException('El vector requiere cinco resultados explícitos.');
        }
        $mapa = $positivos = [];
        $memoria = null;
        foreach ($resultados as $resultado) {
            if (! $resultado instanceof ResultadoDominio || isset($mapa[$resultado->dominio->value])) {
                throw new DomainException('Dominio duplicado o resultado mal tipado.');
            }
            $memoria ??= $resultado->memoria;
            if ($resultado->memoria !== $memoria) {
                throw new DomainException('El vector no puede mezclar instantáneas de memoria.');
            }
            foreach ($resultado->soportesPositivos() as $soporte) {
                foreach ($positivos as [$dominio, $anterior]) {
                    if ($dominio !== $resultado->dominio && ! $soporte->esIndependienteDe($anterior)) {
                        throw new DomainException('Soporte o episodio reutilizado entre dominios A/P.');
                    }
                }
                $positivos[] = [$resultado->dominio, $soporte];
            }
            $mapa[$resultado->dominio->value] = $resultado;
        }
        $ordenados = [];
        foreach (DominioCognitivoEnum::cases() as $dominio) {
            $ordenados[$dominio->value] = $mapa[$dominio->value]
                ?? throw new DomainException('Dominio requerido ausente del perfil.');
        }
        $this->memoria = $memoria;
        $this->resultados = $ordenados;
    }

    /**
     * Recupera un eje existente sin sintetizar un estado global.
     * @param DominioCognitivoEnum $dominio
     * @return ResultadoDominio
     */
    public function resultado(DominioCognitivoEnum $dominio): ResultadoDominio
    {
        return $this->resultados[$dominio->value];
    }

}
