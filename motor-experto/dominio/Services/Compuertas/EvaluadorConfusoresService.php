<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Services\Compuertas;

use MotorExperto\dominio\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\ValueObjects\Dcl\EstadoCriterioDclEnum;
use DomainException;

/** Comprueba la revisión contextual declarada; la ausencia de datos permanece desconocida. */
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
     * @param VerificadorEvidenciaService $verificador
     */
    public function __construct(
        /** Colaborador puro, sin estado operativo. */
        private VerificadorEvidenciaService $verificador = new VerificadorEvidenciaService(),
    ) {}

    /**
     * Evalúa exclusivamente declaraciones explícitas de revisión contextual.
     * @param MemoriaTrabajo $memoria
     * @param DominioCognitivoEnum $dominio
     * @param list<HechoExperto> $revisiones
     * @return array{ estado: EstadoCriterioDclEnum, faltantes: list<string>, declaraciones: array<string, array{valor: ?bool, contradictoria: bool, soportes: list<HechoExperto>}>, invalidacion: ?bool, advertencia: ?bool, modificador: ?string }
     * @throws DomainException
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
