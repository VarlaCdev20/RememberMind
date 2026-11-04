<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Hechos;

use DateTimeImmutable;
use DomainException;

/** Conserva el dato original, su fuente y su contexto; distingue ausencia, falso y cero. */
final readonly class HechoExperto
{
    /**
     * Construye un hecho sin normalizar ni completar su contenido.
     * @param string $id
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $episodioId
     * @param string $fuenteId
     * @param string $origenId
     * @param string $versionConocimiento
     * @param string $concepto
     * @param DateTimeImmutable $fecha
     * @param string|int|float|bool|null $valor
     * @throws DomainException
     */
    public function __construct(
        /** Identificador del hecho. */ public string $id,
        /** Evaluación propietaria. */ public string $evaluacionId,
        /** Sujeto propietario. */ public string $residenteId,
        /** Episodio de procedencia. */ public string $episodioId,
        /** Fuente identificable. */ public string $fuenteId,
        /** Registro atómico original. */ public string $origenId,
        /** Versión del conocimiento. */ public string $versionConocimiento,
        /** Concepto representado. */ public string $concepto,
        /** Fecha original inmutable. */ public DateTimeImmutable $fecha,
        /** Valor original, incluido null. */ public string|int|float|bool|null $valor,
    ) {
        foreach ([$id, $evaluacionId, $residenteId, $episodioId, $fuenteId, $origenId, $versionConocimiento, $concepto] as $token) {
            if ($token === '' || preg_match('/[\x00-\x20\x7F]/', $token) === 1) {
                throw new DomainException('El hecho requiere identificadores explícitos sin espacios ni controles.');
            }
        }
        if (is_float($valor) && ! is_finite($valor)) {
            throw new DomainException('El hecho no admite números no finitos.');
        }
    }

    /**
     * Comprueba pertenencia y corte sin interpretar el valor.
     * @param string $evaluacionId
     * @param string $residenteId
     * @param string $versionConocimiento
     * @param DateTimeImmutable $fechaCorte
     * @return void
     * @throws DomainException
     */
    public function exigirContexto(string $evaluacionId, string $residenteId, string $versionConocimiento, DateTimeImmutable $fechaCorte): void
    {
        if ($this->evaluacionId !== $evaluacionId || $this->residenteId !== $residenteId
            || $this->versionConocimiento !== $versionConocimiento || $this->fecha > $fechaCorte) {
            throw new DomainException('Hecho ajeno al caso, versión o fecha de corte.');
        }
    }

    /**
     * Identifica una fuente atómica aunque se cambie el ID local del hecho.
     * @return non-empty-string
     */
    public function claveOrigen(): string
    {
        return serialize([$this->fuenteId, $this->origenId]);
    }

}
