<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use MotorExperto\dominio\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use DateTimeImmutable;
use DomainException;

/** Identifica una petición experimental y fija el contexto que debe conservarse durante extracción, inferencia y elaboración del informe. */
final readonly class SolicitudInferenciaDTO
{
    /** Único modo admitido; coincide con el alcance declarado por Domain. */
    public const MODO_EXPERIMENTAL = ResultadoClasificacionDcl::USO;

    /**
     * Valida una solicitud sin asignar datos ausentes ni normalizar identidades.
     * @param string $solicitudId
     * @param string $evaluacionId
     * @param string $casoSinteticoId
     * @param DateTimeImmutable $fechaCorte
     * @param string $versionConocimiento
     * @param string $modoEjecucion
     * @throws DomainException
     * @throws \TypeError
     */
    public function __construct(
        /** Identidad de la solicitud original. */ public string $solicitudId,
        /** Identidad de la evaluación ficticia. */ public string $evaluacionId,
        /** Identidad del caso, nunca una referencia Eloquent. */ public string $casoSinteticoId,
        /** Corte inclusivo proporcionado por quien solicita el análisis. */ public DateTimeImmutable $fechaCorte,
        /** Versión exacta del conocimiento requerido. */ public string $versionConocimiento,
        /** Alcance técnico de la ejecución. */ public string $modoEjecucion = self::MODO_EXPERIMENTAL,
    ) {
        foreach ([$solicitudId, $evaluacionId, $casoSinteticoId, $versionConocimiento] as $identificador) {
            if ($identificador === '' || preg_match('/[\x00-\x20\x7F]/', $identificador) === 1) {
                throw new DomainException('La solicitud requiere identificadores y versión sin espacios ni controles.');
            }
        }
        if (! str_starts_with($evaluacionId, 'SIM-') || strlen($evaluacionId) <= 4
            || ! str_starts_with($casoSinteticoId, 'SINTETICO-') || strlen($casoSinteticoId) <= 10
            || $modoEjecucion !== self::MODO_EXPERIMENTAL) {
            throw new DomainException('La solicitud solo admite evaluaciones y casos inequívocamente sintéticos.');
        }
        // DateTimeImmutable aporta una fecha tipada; el DTO no analiza cadenas
        // ambiguas ni impone límites temporales clínicos inexistentes.
    }

    /**
     * Contrasta la identidad de una memoria ya validada por Domain.
     * @param MemoriaTrabajo $memoria
     * @throws DomainException
     */
    public function exigirContexto(MemoriaTrabajo $memoria): void
    {
        if ($memoria->evaluacionId !== $this->evaluacionId || $memoria->residenteId !== $this->casoSinteticoId
            || $memoria->versionConocimiento !== $this->versionConocimiento || $memoria->fechaCorte != $this->fechaCorte) {
            throw new DomainException('La memoria no corresponde al contexto y corte de la solicitud.');
        }
    }
}
