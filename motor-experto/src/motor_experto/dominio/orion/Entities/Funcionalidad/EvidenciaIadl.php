<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Funcionalidad;

use MotorExperto\dominio\orion\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoIadlEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\OportunidadIadlEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de hechos (Actividades instrumentales de la vida diaria).
 *
 * Describe el desempeño de una actividad junto con la oportunidad, el nivel previo y la causa de ayuda.
 * Ese contexto permite distinguir limitaciones institucionales o motoras de una atribución cognitiva simulada.
 *
 * TÍTULO: Evidencia contextual de una actividad instrumental.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de hechos.
 *
 * RESPONSABILIDAD:
 * Separar actividad, desempeño, oportunidad, nivel previo y causa de ayuda.
 *
 * ENTRADAS:
 * Hecho original, actividad, estados tipados y revisión causal ficticia opcional.
 *
 * SALIDAS:
 * Una evidencia inmutable cuya interpretabilidad contextual puede consultarse.
 *
 * RESTRICCIONES:
 * La atribución nullable es propuesta técnica de la PoC, nunca una inferencia
 * clínica. Ayuda motora o sustitución institucional no demuestran deterioro.
 * La ausencia de revisión permanece null; no se convierte en causa cognitiva.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R4/R5/R9; Documento Maestro §3.13.2 y DCL-C03 experimental.
 */
final readonly class EvidenciaIadl
{
    /**
     * Conserva una observación contextual y, si existe, una revisión sintética.
     *
     * @param HechoExperto $hecho Fuente original y contexto temporal.
     * @param string $actividadId Actividad documentada, sin imponer un catálogo nuevo.
     * @param EstadoIadlEnum $estado Desempeño observado.
     * @param OportunidadIadlEnum $oportunidad Oportunidad real o limitación.
     * @param EstadoIadlEnum|null $nivelPrevio Desempeño previo conocido, si existe.
     * @param string|null $causaAyuda Causa descrita; no se interpreta texto automáticamente.
     * @param bool|null $atribucionCognitivaSimulada Declaración externa ficticia; null significa no revisada.
     * @param string|null $revisorSinteticoId Revisor ficticio explícito.
     * @throws DomainException Por datos incoherentes o atribución sin revisión sintética.
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
     *
     * @return bool Hay ejecución contextualizada y revisión ficticia explícita.
     *
     * No recibe parámetros ni lanza excepciones. No acredita por sí misma C03.
     */
    public function esInterpretable(): bool
    {
        return $this->hecho->valor !== null && $this->oportunidad === OportunidadIadlEnum::REAL_Y_ADECUADA
            && in_array($this->estado, [EstadoIadlEnum::INDEPENDIENTE, EstadoIadlEnum::CON_AYUDA], true)
            && in_array($this->nivelPrevio, [EstadoIadlEnum::INDEPENDIENTE, EstadoIadlEnum::CON_AYUDA], true)
            && $this->atribucionCognitivaSimulada !== null && $this->revisorSinteticoId !== null;
    }

}
