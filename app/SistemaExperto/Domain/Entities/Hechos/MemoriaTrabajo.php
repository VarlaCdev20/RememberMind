<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Entities\Hechos;

use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use DateTimeImmutable;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Memoria de trabajo (Contexto de inferencia).
 *
 * Reúne los hechos y evidencias admitidos para una evaluación y fecha de corte.
 * Cada incorporación crea otra instantánea y conserva intactas las evidencias originales.
 *
 * TÍTULO: Memoria inmutable de una evaluación.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Memoria de trabajo.
 *
 * RESPONSABILIDAD:
 * Retener hechos originales y evidencias del mismo caso hasta una fecha de corte.
 *
 * ENTRADAS:
 * Identidad de evaluación, residente, versión, corte y listas explícitas tipadas.
 *
 * SALIDAS:
 * Mapas de hechos y evidencias; incorporaciones producen una memoria nueva.
 *
 * RESTRICCIONES:
 * No mezcla casos ni versiones, no sobrescribe hechos, no duplica fuentes.
 * Los episodios compartidos pueden conservarse, pero nunca se cuentan aquí
 * como corroboraciones: la independencia se consulta en EvidenciaCognitiva.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R3/R4 y memoria del motor D-124/D-137.
 */
final readonly class MemoriaTrabajo
{
    /** @var array<string, HechoExperto> Hechos originales por ID. */
    public array $hechos;

    /** @var array<string, EvidenciaCognitiva> Evidencias por ID del hecho que documentan. */
    public array $evidencias;

    /**
     * Valida la instantánea completa antes de publicarla.
     *
     * @param string $evaluacionId Evaluación propietaria.
     * @param string $residenteId Sujeto propietario.
     * @param string $versionConocimiento Versión única.
     * @param DateTimeImmutable $fechaCorte Corte inclusivo.
     * @param list<HechoExperto> $hechos Hechos originales.
     * @param list<EvidenciaCognitiva> $evidencias Evidencias ligadas a esos mismos objetos.
     * @throws DomainException Por identidad, duplicación, contexto o sustitución del original.
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
     *
     * @param HechoExperto $hecho Hecho original a incorporar.
     * @return self Memoria nueva.
     * @throws DomainException Si el constructor detecta duplicación o contexto ajeno.
     */
    public function conHecho(HechoExperto $hecho): self
    {
        return new self($this->evaluacionId, $this->residenteId, $this->versionConocimiento, $this->fechaCorte,
            [...array_values($this->hechos), $hecho], array_values($this->evidencias));
    }

    /**
     * Incorpora evidencia y, si falta, su hecho original en una memoria nueva.
     *
     * @param EvidenciaCognitiva $evidencia Evidencia a conservar.
     * @return self Instantánea nueva con la incorporación validada.
     * @throws DomainException Por duplicación, contexto ajeno o reemplazo de origen.
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
     *
     * @param string $id Identidad del hecho documentado.
     * @return EvidenciaCognitiva Evidencia original.
     * @throws DomainException Si está ausente.
     */
    public function evidencia(string $id): EvidenciaCognitiva
    {
        return $this->evidencias[$id] ?? throw new DomainException('Evidencia ausente de esta memoria.');
    }

    /**
     * Conserva el filtro de dominio sin ponderar ni agrupar hallazgos.
     *
     * @param DominioCognitivoEnum $dominio Dominio consultado.
     * @return list<EvidenciaCognitiva> Evidencias originales vinculadas.
     *
     * No lanza excepciones.
     */
    public function evidenciasParaDominio(DominioCognitivoEnum $dominio): array
    {
        return array_values(array_filter($this->evidencias, static fn (EvidenciaCognitiva $e): bool => $e->dominio === $dominio));
    }

}
