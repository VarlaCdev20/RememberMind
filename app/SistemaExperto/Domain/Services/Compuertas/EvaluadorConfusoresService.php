<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Services\Compuertas;

use App\SistemaExperto\Domain\Entities\Hechos\HechoExperto;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum;
use DomainException;

/**
 * TÍTULO: EvaluadorConfusoresService.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Compuertas de contexto.
 *
 * RESPONSABILIDAD:
 * Evaluar revisión, invalidación y advertencia declaradas, sin diagnosticar causas.
 *
 * ENTRADAS:
 * Hechos de revisión contextual del mismo caso, dominio, versión y corte.
 *
 * SALIDAS:
 * Estado de revisión, declaraciones originales, requisitos pendientes y advertencia.
 *
 * RESTRICCIONES:
 * No deduce causalidad desde medicación, síntomas, edad o dependencia física.
 * Los conceptos COG-*:campo son envolturas técnicas internas de la PoC.
 * La extensión a otros dominios es experimental, no una formalización clínica
 * aprobada. La línea basal y la advertencia ausentes permanecen desconocidas.
 *
 * TRAZABILIDAD:
 * CTX-MEM-01 y reglamento maestro R1/R2/R4/R5/R9.
 */
final readonly class EvaluadorConfusoresService
{
    /** Campos documentados de CTX-MEM-01; su representación no los acredita. */
    public const CAMPOS_CONTEXTO = [
        // Declaración de pertinencia del componente; no valida baremos automáticamente.
        'componente_validado',
        // Condiciones de aplicación conocidas por la revisión externa.
        'condiciones_aplicacion_conocidas',
        // Revisión explícita de confusores; no se deduce de su ausencia en registros.
        'confusores_revisados',
        // Impedimento interpretativo declarado, separado de su causa clínica.
        'invalidacion_necesaria',
        // Advertencia ortogonal que no implica invalidación ni resta puntos.
        'limitacion_no_invalidante',
        // Disponibilidad de basal opcional; no es requisito universal del nivel 2.
        'basal_disponible',
    ];

    /**
     * Comparte la verificación de procedencia y contexto de las declaraciones.
     *
     * @param VerificadorEvidenciaService $verificador Verificador puro.
     *
     * No lanza excepciones ni realiza efectos externos.
     */
    public function __construct(
        /** Colaborador puro, sin estado operativo. */
        private VerificadorEvidenciaService $verificador = new VerificadorEvidenciaService(),
    ) {}

    /**
     * Evalúa exclusivamente declaraciones explícitas de revisión contextual.
     *
     * Prioridad técnica: contradicción, invalidación, pendiente y revisión
     * acreditada. No se transforma una declaración ausente en false.
     * El modificador MOD-CM-1 se conserva únicamente para COG-MEM.
     *
     * @param MemoriaTrabajo $memoria Contexto y fuentes originales.
     * @param DominioCognitivoEnum $dominio Dominio al que aplica la revisión.
     * @param list<HechoExperto> $revisiones Declaraciones COG-*:campo originales.
     * @return array{
     *   estado: EstadoCriterioDclEnum, faltantes: list<string>,
     *   declaraciones: array<string, array{valor: ?bool, contradictoria: bool, soportes: list<HechoExperto>}>,
     *   invalidacion: ?bool, advertencia: ?bool, modificador: ?string
     * } Compuerta contextual y fuentes; no equivale a acreditar DCL-C04.
     * @throws DomainException Por declaración incoherente con la memoria o el dominio.
     */
    public function evaluar(MemoriaTrabajo $memoria, DominioCognitivoEnum $dominio, array $revisiones = []): array
    {
        $conceptos = array_map(static fn (string $campo): string => $dominio->value.':'.$campo, self::CAMPOS_CONTEXTO);
        $leidas = $this->verificador->leerDeclaraciones($memoria, $revisiones, $conceptos);
        $declaraciones = [];
        foreach (self::CAMPOS_CONTEXTO as $campo) {
            $declaraciones[$campo] = $leidas[$dominio->value.':'.$campo];
        }
        $revision = $declaraciones['confusores_revisados'];
        $invalidacion = $declaraciones['invalidacion_necesaria'];
        $advertencia = $declaraciones['limitacion_no_invalidante'];
        $faltantes = [];
        if ($revision['valor'] !== true) {
            $faltantes[] = 'confusores_revisados';
        }
        if ($invalidacion['valor'] === null) {
            $faltantes[] = 'invalidacion_necesaria';
        }
        $estado = match (true) {
            $revision['contradictoria'] || $invalidacion['contradictoria'] || $advertencia['contradictoria'] => EstadoCriterioDclEnum::CONTRADICTORIO,
            $invalidacion['valor'] === true => EstadoCriterioDclEnum::NO_ACREDITADO,
            $faltantes !== [] => EstadoCriterioDclEnum::PENDIENTE,
            default => EstadoCriterioDclEnum::ACREDITADO,
        };

        return [
            'estado' => $estado, 'faltantes' => $faltantes, 'declaraciones' => $declaraciones,
            'invalidacion' => $invalidacion['valor'], 'advertencia' => $advertencia['valor'],
            'modificador' => $dominio === DominioCognitivoEnum::MEMORIA && $advertencia['valor'] === true ? 'MOD-CM-1' : null,
        ];
    }
}
