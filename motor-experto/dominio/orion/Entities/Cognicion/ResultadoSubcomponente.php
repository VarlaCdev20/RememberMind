<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Entities\Cognicion;

use MotorExperto\dominio\orion\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use DomainException;

/** Conserva un hallazgo de tarea y sus fuentes; no concluye el estado del dominio. */
final readonly class ResultadoSubcomponente
{
    /** Dominio primario documental, no resultado cognitivo. */
    public DominioCognitivoEnum $dominio;

    /** @var list<EvidenciaCognitiva> Soportes originales, sin repetición atómica. */
    public array $evidencias;

    /**
     * Valida coherencia del resultado declarado sin inferirlo.
     * @param SubcomponenteCognitivoEnum $subcomponente
     * @param HallazgoInstrumentalEnum $hallazgo
     * @param MemoriaTrabajo $memoria
     * @param list<string> $evidenciasIds
     * @param string $justificacion
     * @throws DomainException
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
