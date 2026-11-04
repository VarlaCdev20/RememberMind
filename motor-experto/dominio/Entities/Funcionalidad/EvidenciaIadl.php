<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Entities\Funcionalidad;

use MotorExperto\dominio\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\ValueObjects\Funcionalidad\EstadoIadlEnum;
use MotorExperto\dominio\ValueObjects\Funcionalidad\OportunidadIadlEnum;
use DomainException;

/** Conserva desempeño, oportunidad, nivel previo y causa de ayuda en una actividad instrumental. */
final readonly class EvidenciaIadl
{
    /**
     * Conserva una observación contextual y, si existe, una revisión sintética.
     * @param HechoExperto $hecho
     * @param string $actividadId
     * @param EstadoIadlEnum $estado
     * @param OportunidadIadlEnum $oportunidad
     * @param EstadoIadlEnum|null $nivelPrevio
     * @param string|null $causaAyuda
     * @param bool|null $atribucionCognitivaSimulada
     * @param string|null $revisorSinteticoId
     * @throws DomainException
     */
    public function __construct(
        /** Hecho original. */ public HechoExperto $hecho,
        /** Actividad identificable. */ public string $actividadId,
        /** Desempeño observado. */ public EstadoIadlEnum $estado,
        /** Oportunidad de ejecución. */ public OportunidadIadlEnum $oportunidad,
        /** Desempeño previo, si se conoce. */ public ?EstadoIadlEnum $nivelPrevio,
        /** Explicación de la ayuda o no ejecución. */ public ?string $causaAyuda,
        /** Atribución externa exclusivamente ficticia. */ public ?bool $atribucionCognitivaSimulada,
        /** Identidad del revisor ficticio. */ public ?string $revisorSinteticoId,
    ) {
        if ($actividadId === '' || preg_match('/[\x00-\x20\x7F]/', $actividadId) === 1
            || ($causaAyuda !== null && trim($causaAyuda) === '')) {
            throw new DomainException('La actividad y la causa declarada deben ser identificables.');
        }
        if (in_array($estado, [EstadoIadlEnum::CON_AYUDA, EstadoIadlEnum::NO_REALIZADA], true) && $causaAyuda === null) {
            throw new DomainException('La ayuda o no ejecución requiere contexto explícito.');
        }
        if ($hecho->valor === null && $estado !== EstadoIadlEnum::DESCONOCIDO) {
            throw new DomainException('Una fuente ausente no acredita desempeño funcional.');
        }
        if ($atribucionCognitivaSimulada !== null) {
            if (! str_starts_with($hecho->evaluacionId, 'SIM-') || ! str_starts_with($hecho->residenteId, 'SINTETICO-')
                || $hecho->fuenteId !== 'REFERENCIA_IADL_SINTETICA' || $revisorSinteticoId === null
                || ! str_starts_with($revisorSinteticoId, 'SIM-') || strlen($revisorSinteticoId) <= 4
                || preg_match('/[\x00-\x20\x7F]/', $revisorSinteticoId) === 1) {
                throw new DomainException('La atribución requiere caso y revisión totalmente ficticios.');
            }
            if ($atribucionCognitivaSimulada && ($estado !== EstadoIadlEnum::CON_AYUDA || ! $this->esInterpretable())) {
                throw new DomainException('No se atribuye deterioro desde falta de oportunidad o desempeño no interpretable.');
            }
        } elseif ($revisorSinteticoId !== null) {
            throw new DomainException('El revisor causal no puede ocultar una atribución sin declarar.');
        }
    }

    /**
     * Comprueba disponibilidad de contexto, sin resolver independencia funcional.
     * @return bool
     */
    public function esInterpretable(): bool
    {
        return $this->hecho->valor !== null && $this->oportunidad === OportunidadIadlEnum::REAL_Y_ADECUADA
            && in_array($this->estado, [EstadoIadlEnum::INDEPENDIENTE, EstadoIadlEnum::CON_AYUDA], true)
            && in_array($this->nivelPrevio, [EstadoIadlEnum::INDEPENDIENTE, EstadoIadlEnum::CON_AYUDA], true)
            && $this->atribucionCognitivaSimulada !== null && $this->revisorSinteticoId !== null;
    }

}
