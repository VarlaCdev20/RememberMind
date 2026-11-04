<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Hechos;

use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use DateTimeImmutable;
use DomainException;

/** Agrupa hechos y evidencias del mismo caso hasta una fecha de corte, sin sobrescribirlos. */
final readonly class MemoriaTrabajo
{
    /** @var array<string, HechoExperto> Hechos originales por ID. */
    public array $hechos;

    /** @var array<string, EvidenciaCognitiva> Evidencias por ID del hecho que documentan. */
    public array $evidencias;

    /**
     * Valida la instantánea completa antes de publicarla.
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $versionConocimiento
     * @param DateTimeImmutable $fechaCorte
     * @param list<HechoExperto> $hechos
     * @param list<EvidenciaCognitiva> $evidencias
     * @throws DomainException
     */
    public function __construct(
        /** Evaluación propietaria. */ public string $evaluacionId,
        /** Sujeto propietario. */ public string $residenteId,
        /** Versión única del conocimiento. */ public string $versionConocimiento,
        /** Corte temporal inclusivo. */ public DateTimeImmutable $fechaCorte,
        array $hechos = [],
        array $evidencias = [],
    ) {
        foreach ([$evaluacionId, $residenteId, $versionConocimiento] as $token) {
            if ($token === '' || preg_match('/[\x00-\x20\x7F]/', $token) === 1) {
                throw new DomainException('La memoria requiere identidad y versión explícitas.');
            }
        }
        if (! array_is_list($hechos) || ! array_is_list($evidencias)) {
            throw new DomainException('La memoria recibe listas explícitas.');
        }
        $mapa = $origenes = [];
        foreach ($hechos as $hecho) {
            if (! $hecho instanceof HechoExperto) {
                throw new DomainException('Hecho mal tipado en memoria.');
            }
            $hecho->exigirContexto($evaluacionId, $residenteId, $versionConocimiento, $fechaCorte);
            if (isset($mapa[$hecho->id]) || isset($origenes[$hecho->claveOrigen()])) {
                throw new DomainException('Hecho o fuente atómica duplicados.');
            }
            $mapa[$hecho->id] = $hecho;
            $origenes[$hecho->claveOrigen()] = true;
        }
        $documentadas = [];
        foreach ($evidencias as $evidencia) {
            if (! $evidencia instanceof EvidenciaCognitiva
                || ($mapa[$evidencia->hecho->id] ?? null) !== $evidencia->hecho
                || isset($documentadas[$evidencia->hecho->id])) {
                throw new DomainException('Evidencia duplicada o sin su hecho original en memoria.');
            }
            $documentadas[$evidencia->hecho->id] = $evidencia;
        }
        $this->hechos = $mapa;
        $this->evidencias = $documentadas;
    }

    /**
     * Incorpora un hecho en otra instantánea sin modificar esta memoria.
     * @param HechoExperto $hecho
     * @return self
     * @throws DomainException
     */
    public function conHecho(HechoExperto $hecho): self
    {
        return new self($this->evaluacionId, $this->residenteId, $this->versionConocimiento, $this->fechaCorte,
            [...array_values($this->hechos), $hecho], array_values($this->evidencias));
    }

    /**
     * Incorpora evidencia y, si falta, su hecho original en una memoria nueva.
     * @param EvidenciaCognitiva $evidencia
     * @return self
     * @throws DomainException
     */
    public function conEvidencia(EvidenciaCognitiva $evidencia): self
    {
        $hechos = array_values($this->hechos);
        if (! isset($this->hechos[$evidencia->hecho->id])) {
            $hechos[] = $evidencia->hecho;
        }

        return new self($this->evaluacionId, $this->residenteId, $this->versionConocimiento, $this->fechaCorte,
            $hechos, [...array_values($this->evidencias), $evidencia]);
    }

    /**
     * Consulta una evidencia existente, sin crear una evidencia desconocida.
     * @param string $id
     * @return EvidenciaCognitiva
     * @throws DomainException
     */
    public function evidencia(string $id): EvidenciaCognitiva
    {
        return $this->evidencias[$id] ?? throw new DomainException('Evidencia ausente de esta memoria.');
    }

    /**
     * Conserva el filtro de dominio sin ponderar ni agrupar hallazgos.
     * @param DominioCognitivoEnum $dominio
     * @return list<EvidenciaCognitiva>
     */
    public function evidenciasParaDominio(DominioCognitivoEnum $dominio): array
    {
        return array_values(array_filter($this->evidencias, static fn (EvidenciaCognitiva $e): bool => $e->dominio === $dominio));
    }

}
