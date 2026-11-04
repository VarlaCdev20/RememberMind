<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Entities\Funcionalidad;

use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\ValueObjects\Dcl\EstadoCriterioDclEnum;
use DomainException;

/** Conserva la independencia funcional declarada y las actividades que la sustentan. */
final readonly class VectorFuncionalidad
{
    /** @var list<EvidenciaIadl> Fuentes funcionales originales, sin duplicados. */
    public array $evidencias;

    /**
     * Comprueba coherencia de una declaración funcional ficticia.
     * @param MemoriaTrabajo $memoria
     * @param EstadoCriterioDclEnum $independenciaRelativa
     * @param list<EvidenciaIadl> $evidencias
     * @param string|null $revisorSinteticoId
     * @param string $justificacion
     * @throws DomainException
     */
    public function __construct(
        /** Contexto de evaluación. */ public MemoriaTrabajo $memoria,
        /** Declaración externa de independencia relativa. */ public EstadoCriterioDclEnum $independenciaRelativa,
        array $evidencias,
        /** Revisor ficticio de la declaración. */ public ?string $revisorSinteticoId,
        /** Fundamento o limitación. */ public string $justificacion,
    ) {
        if (! array_is_list($evidencias) || trim($justificacion) === '') {
            throw new DomainException('Perfil funcional sin evidencias explícitas o justificación.');
        }
        $copia = $ids = [];
        $interpretable = $atribucionCognitiva = false;
        foreach ($evidencias as $e) {
            if (! $e instanceof EvidenciaIadl) {
                throw new DomainException('Evidencia funcional mal tipada.');
            }
            $e->hecho->exigirContexto($memoria->evaluacionId, $memoria->residenteId, $memoria->versionConocimiento, $memoria->fechaCorte);
            if (($memoria->hechos[$e->hecho->id] ?? null) !== $e->hecho || isset($ids[$e->hecho->id])) {
                throw new DomainException('IADL duplicada o sin hecho original en memoria.');
            }
            $ids[$e->hecho->id] = true;
            $copia[] = $e;
            $interpretable = $interpretable || $e->esInterpretable();
            $atribucionCognitiva = $atribucionCognitiva || ($e->esInterpretable() && $e->atribucionCognitivaSimulada === true);
        }
        if ($revisorSinteticoId !== null && (! str_starts_with($revisorSinteticoId, 'SIM-')
            || strlen($revisorSinteticoId) <= 4 || preg_match('/[\x00-\x20\x7F]/', $revisorSinteticoId) === 1)) {
            throw new DomainException('Revisor funcional ficticio inválido.');
        }
        if (in_array($independenciaRelativa, [EstadoCriterioDclEnum::ACREDITADO, EstadoCriterioDclEnum::NO_ACREDITADO], true)) {
            if (! $interpretable || $revisorSinteticoId === null
                || ! str_starts_with($memoria->evaluacionId, 'SIM-') || ! str_starts_with($memoria->residenteId, 'SINTETICO-')) {
                throw new DomainException('La declaración funcional requiere revisión sintética contextualizada.');
            }
            if ($independenciaRelativa === EstadoCriterioDclEnum::NO_ACREDITADO && ! $atribucionCognitiva) {
                throw new DomainException('Ayuda motora o institucional aislada no demuestra pérdida cognitiva de independencia.');
            }
        }
        if ($independenciaRelativa === EstadoCriterioDclEnum::CONTRADICTORIO && $copia === []) {
            throw new DomainException('La contradicción funcional debe conservar sus fuentes.');
        }
        $this->evidencias = $copia;
    }

}
