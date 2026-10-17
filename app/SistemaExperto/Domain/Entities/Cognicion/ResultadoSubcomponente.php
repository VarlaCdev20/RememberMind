<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Entities\Cognicion;

use App\SistemaExperto\Domain\Entities\Hechos\EvidenciaCognitiva;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Motor de inferencia (Resultados por subcomponente).
 *
 * Conserva un hallazgo declarado y las evidencias originales que lo sustentan.
 * El alcance de una tarea permanece separado del resultado integrado de su dominio.
 *
 * TÍTULO: Resultado explícito de un subcomponente.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Motor de inferencia.
 *
 * RESPONSABILIDAD:
 * Conservar un hallazgo declarado con sus soportes originales y alcance específico.
 *
 * ENTRADAS:
 * Subcomponente, hallazgo, memoria, IDs de evidencia y justificación.
 *
 * SALIDAS:
 * Un resultado inmutable vinculado a una única instantánea de evaluación.
 *
 * RESTRICCIONES:
 * No convierte un resultado de tarea en el estado de todo un dominio.
 * Una ausencia permanece DESCONOCIDO; no se calculan baremos ni umbrales.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R3/R4/R6/R8 y subcomponentes de la Fase A.
 */
final readonly class ResultadoSubcomponente
{
    /** Dominio primario documental, no resultado cognitivo. */
    public DominioCognitivoEnum $dominio;

    /** @var list<EvidenciaCognitiva> Soportes originales, sin repetición atómica. */
    public array $evidencias;

    /**
     * Valida coherencia del resultado declarado sin inferirlo.
     *
     * @param SubcomponenteCognitivoEnum $subcomponente Alcance del resultado.
     * @param HallazgoInstrumentalEnum $hallazgo Descriptor declarado.
     * @param MemoriaTrabajo $memoria Instantánea original.
     * @param list<string> $evidenciasIds IDs de soportes en esa memoria.
     * @param string $justificacion Fundamentación o limitación explícita.
     * @throws DomainException Por soporte ausente, duplicado, de otro alcance o incompatible.
     */
    public function __construct(
        /** Subcomponente propietario. */ public SubcomponenteCognitivoEnum $subcomponente,
        /** Descriptor declarado. */ public HallazgoInstrumentalEnum $hallazgo,
        /** Contexto y hechos originales. */ public MemoriaTrabajo $memoria,
        array $evidenciasIds,
        /** Fundamentación del resultado. */ public string $justificacion,
    ) {
        if (! array_is_list($evidenciasIds) || trim($justificacion) === '') {
            throw new DomainException('Resultado sin lista de soportes o justificación.');
        }
        $seleccion = $ids = $hallazgos = [];
        foreach ($evidenciasIds as $id) {
            if (! is_string($id) || isset($ids[$id])) {
                throw new DomainException('Soporte mal identificado o duplicado.');
            }
            $e = $memoria->evidencia($id);
            if ($e->subcomponente !== $subcomponente) {
                throw new DomainException('La evidencia pertenece a otro subcomponente.');
            }
            $ids[$id] = true;
            $seleccion[] = $e;
            $hallazgos[$e->hallazgo->value] = true;
        }
        $contradiccion = isset($hallazgos[HallazgoInstrumentalEnum::CONTRADICTORIO->value])
            || (isset($hallazgos[HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA->value])
                && isset($hallazgos[HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO->value]));
        if ($hallazgo === HallazgoInstrumentalEnum::CONTRADICTORIO ? ! $contradiccion
            : ($contradiccion && $hallazgo !== HallazgoInstrumentalEnum::DESCONOCIDO)) {
            throw new DomainException('El resultado no conserva la contradicción documentada.');
        }
        if ($hallazgo !== HallazgoInstrumentalEnum::DESCONOCIDO
            && $hallazgo !== HallazgoInstrumentalEnum::CONTRADICTORIO && ! isset($hallazgos[$hallazgo->value])) {
            throw new DomainException('Hallazgo sin soporte positivo del mismo alcance.');
        }
        $this->dominio = $subcomponente->dominio();
        $this->evidencias = $seleccion;
    }

}
