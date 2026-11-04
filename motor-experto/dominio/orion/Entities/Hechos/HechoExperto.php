<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Hechos;

use DateTimeImmutable;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Memoria de trabajo (Hechos originales).
 *
 * Conserva un dato junto con su evaluación, residente, episodio, origen y fecha.
 * Mantiene distintos la ausencia, el valor false y el cero para evitar conclusiones implícitas.
 *
 * TÍTULO: Hecho atómico con procedencia y contexto.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Memoria de trabajo.
 *
 * RESPONSABILIDAD:
 * Conservar un valor original y la identidad temporal de la fuente que lo produjo.
 *
 * ENTRADAS:
 * Identificadores explícitos de hecho, evaluación, residente, episodio, fuente,
 * registro original, versión, concepto, fecha y valor escalar o ausente.
 *
 * SALIDAS:
 * Una instantánea inmutable y comprobaciones de pertenencia al caso.
 *
 * RESTRICCIONES:
 * No consulta persistencia ni transforma null, false o cero en normalidad.
 * Los identificadores son tokens técnicos no vacíos; no se imponen PK de BDD.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R3/R4 y contrato de procedencia D-124/D-137.
 */
final readonly class HechoExperto
{
    /**
     * Construye un hecho sin normalizar ni completar su contenido.
     *
     * @param string $id Identidad única de este hecho.
     * @param string $evaluacionId Evaluación propietaria.
     * @param string $residenteId Sujeto propietario; no se busca en una BDD.
     * @param string $episodioId Episodio original, conservado entre relecturas.
     * @param string $fuenteId Tipo o sistema de origen identificable.
     * @param string $origenId Identidad atómica del registro o resultado fuente.
     * @param string $versionConocimiento Versión de interpretación de la instantánea.
     * @param string $concepto Concepto o variable representado.
     * @param DateTimeImmutable $fecha Fecha del hecho, nunca sustituida por ahora.
     * @param string|int|float|bool|null $valor Dato original, con ausencia explícita.
     * @throws DomainException Por identidad vacía, espacios de control o número no finito.
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
     *
     * @param string $evaluacionId Evaluación esperada.
     * @param string $residenteId Sujeto esperado.
     * @param string $versionConocimiento Versión esperada.
     * @param DateTimeImmutable $fechaCorte Última fecha admitida, inclusiva.
     * @return void
     * @throws DomainException Si el hecho es ajeno o posterior al corte.
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
     *
     * @return non-empty-string Tupla serializada, no dato persistido ni expresión.
     *
     * No recibe parámetros ni lanza excepciones para el estado construido.
     */
    public function claveOrigen(): string
    {
        return serialize([$this->fuenteId, $this->origenId]);
    }

}
