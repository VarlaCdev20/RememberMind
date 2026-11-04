<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Services\Inferencia;

use MotorExperto\dominio\orion\Entities\Cognicion\ResultadoDominio;
use MotorExperto\dominio\orion\Entities\Cognicion\ResultadoSubcomponente;
use MotorExperto\dominio\orion\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\orion\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Services\Compuertas\EvaluadorEvaluabilidadService;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EvaluabilidadEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;

/** Integra evidencias admisibles por dominio; conserva incertidumbres y contradicciones. */
final readonly class IntegradorDominiosService
{
    /**
     * Conecta la compuerta pura; no realiza consultas ni acredita revisiones.
     * @param EvaluadorEvaluabilidadService $evaluabilidad
     */
    public function __construct(
        /** Evaluación independiente del estado cognitivo. */
        private EvaluadorEvaluabilidadService $evaluabilidad = new EvaluadorEvaluabilidadService,
    ) {}

    /**
     * Integra únicamente soportes con procedencia acreditada en esta memoria.
     * @param MemoriaTrabajo $memoria
     * @param DominioCognitivoEnum $dominio
     * @param list<EvidenciaCognitiva> $candidatas
     * @param list<HechoExperto> $procedencias
     * @param list<HechoExperto> $contexto
     * @return array{resultado: ResultadoDominio, compuertas: array}
     * @throws \DomainException
     */
    public function integrar(MemoriaTrabajo $memoria, DominioCognitivoEnum $dominio, array $candidatas,
        array $procedencias = [], array $contexto = []): array
    {
        $compuertas = $this->evaluabilidad->evaluar($memoria, $dominio, $candidatas, $procedencias, $contexto);
        $verificacion = $compuertas['verificacion'];
        $elegibles = [];
        foreach ($candidatas as $e) {
            $procedencia = $verificacion['procedencias'][$e->hecho->id];
            if ($e->dominio === $dominio && $procedencia['valor'] === true && ! $procedencia['contradictoria']
                && $e->hecho->valor !== null) {
                $elegibles[$e->hecho->id] = $e;
            }
        }
        ksort($elegibles, SORT_STRING);
        $completas = array_filter($elegibles, static fn (EvidenciaCognitiva $e): bool => $e->subcomponente === null);
        $hallazgoCompleto = $this->resolverHallazgo(array_values($completas));
        $estado = match (true) {
            $hallazgoCompleto === HallazgoInstrumentalEnum::CONTRADICTORIO => EstadoDominioEnum::CONTRADICTORIO,
            $hallazgoCompleto === HallazgoInstrumentalEnum::NO_INTERPRETABLE => EstadoDominioEnum::NO_INTERPRETABLE,
            $compuertas['estado'] !== EvaluabilidadEnum::EVALUACION_INTEGRADA => EstadoDominioEnum::DESCONOCIDO,
            $hallazgoCompleto === HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA => EstadoDominioEnum::ALTERACION,
            $hallazgoCompleto === HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO => EstadoDominioEnum::RENDIMIENTO_ESPERADO,
            default => EstadoDominioEnum::DESCONOCIDO,
        };
        $subresultados = [];
        foreach (SubcomponenteCognitivoEnum::cases() as $subcomponente) {
            if ($subcomponente->dominio() !== $dominio) {
                continue;
            }
            $soportes = array_values(array_filter($elegibles, static fn (EvidenciaCognitiva $e): bool => $e->subcomponente === $subcomponente));
            $subresultados[] = new ResultadoSubcomponente($subcomponente, $this->resolverHallazgo($soportes),
                $memoria, array_map(static fn (EvidenciaCognitiva $e): string => $e->hecho->id, $soportes),
                'Hallazgos explícitos del subcomponente; no acreditan por sí solos el dominio.');
        }
        $razon = 'Estado '.$estado->value.'; compuerta '.$compuertas['codigo'].'. '
            .($compuertas['motivos'] === [] ? 'Referencia sintética completa verificada.' : implode('; ', $compuertas['motivos']));

        return ['resultado' => new ResultadoDominio($dominio, $estado, $compuertas['estado'], $memoria,
            $subresultados, array_map(static fn (EvidenciaCognitiva $e): string => $e->hecho->id, array_values($elegibles)), $razon), 'compuertas' => $compuertas];
    }

    /**
     * Conserva conflictos y límites antes de elegir un descriptor documentado.
     * @param list<EvidenciaCognitiva> $soportes
     * @return HallazgoInstrumentalEnum
     */
    private function resolverHallazgo(array $soportes): HallazgoInstrumentalEnum
    {
        $hallazgos = array_map(static fn (EvidenciaCognitiva $e): HallazgoInstrumentalEnum => $e->hallazgo, $soportes);
        if (in_array(HallazgoInstrumentalEnum::CONTRADICTORIO, $hallazgos, true)
            || (in_array(HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA, $hallazgos, true)
                && in_array(HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO, $hallazgos, true))) {
            return HallazgoInstrumentalEnum::CONTRADICTORIO;
        }
        foreach ([HallazgoInstrumentalEnum::NO_INTERPRETABLE, HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA,
            HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO, HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO] as $hallazgo) {
            if (in_array($hallazgo, $hallazgos, true)) {
                return $hallazgo;
            }
        }

        return HallazgoInstrumentalEnum::DESCONOCIDO;
    }
}
