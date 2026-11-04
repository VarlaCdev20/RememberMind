<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Funcionalidad;

use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Clasificación experimental (Perfil funcional).
 *
 * Conserva la declaración de independencia funcional relativa y las IADL que la sustentan.
 * La declaración requiere revisión explícita del contexto; no surge de una lista vacía de actividades.
 *
 * TÍTULO: Perfil funcional revisado de forma explícita.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Compuertas funcionales.
 *
 * RESPONSABILIDAD:
 * Conservar IADL y una declaración externa de independencia relativa sin deducirla.
 *
 * ENTRADAS:
 * Memoria, estado declarado, lista de evidencias, revisor y justificación.
 *
 * SALIDAS:
 * Perfil inmutable con limitaciones y fuentes funcionales originales.
 *
 * RESTRICCIONES:
 * No sustituye IADL por BADL, no calcula promedios ni umbrales.
 * La no acreditación no puede sustentarse solo en ayuda no cognitiva.
 * Una evidencia interpretable es requisito técnico, no suficiencia clínica.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R3/R4/R5/R9; DCL-PET-20261017-v0.1, C03.
 */
final readonly class VectorFuncionalidad
{
    /** @var list<EvidenciaIadl> Fuentes funcionales originales, sin duplicados. */
    public array $evidencias;

    /**
     * Comprueba coherencia de una declaración funcional ficticia.
     *
     * @param MemoriaTrabajo $memoria Contexto propietario y hechos originales.
     * @param EstadoCriterioDclEnum $independenciaRelativa Estado declarado externamente.
     * @param list<EvidenciaIadl> $evidencias IADL contextualizadas.
     * @param string|null $revisorSinteticoId Responsable ficticio de la declaración.
     * @param string $justificacion Fundamento o información pendiente.
     * @throws DomainException Por identidad, contexto, duplicación o declaración sin soporte.
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
