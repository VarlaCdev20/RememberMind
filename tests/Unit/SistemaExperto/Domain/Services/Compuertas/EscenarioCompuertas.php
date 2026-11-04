<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Compuertas;

use MotorExperto\dominio\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\ValueObjects\Cognicion\DominioCognitivoEnum;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades;

/** Verificar contratos explícitos con fuentes artificiales y objetos reales. */
final class EscenarioCompuertas
{
    /**
     * Construye un escenario con declaraciones explícitas, sin defaults de aprobación.
     * @param list<EvidenciaCognitiva> $evidencias
     * @param array<string, mixed> $contexto
     * @param array<string, mixed> $procedencias
     * @param list<HechoExperto> $contextoExtra
     * @param list<HechoExperto> $procedenciaExtra
     * @param DominioCognitivoEnum $dominio
     * @return array{memoria: MemoriaTrabajo, candidatas: list<EvidenciaCognitiva>, contexto: list<HechoExperto>, procedencias: list<HechoExperto>}
     * @throws \DomainException
     */
    public static function preparar(array $evidencias = [], array $contexto = [], array $procedencias = [],
        array $contextoExtra = [], array $procedenciaExtra = [], DominioCognitivoEnum $dominio = DominioCognitivoEnum::MEMORIA): array
    {
        $declaraciones = [];
        foreach ($contexto as $campo => $valor) {
            $id = 'SIM-CTX-'.$dominio->value.'-'.$campo;
            $declaraciones[] = EscenarioEntidades::hecho(['id' => $id, 'origenId' => 'SIM-OR-'.$id,
                'fuenteId' => 'REVISION_CONTEXTO_SINTETICA', 'concepto' => $dominio->value.':'.$campo, 'valor' => $valor]);
        }
        $verificaciones = [];
        foreach ($procedencias as $evidenciaId => $valor) {
            $id = 'SIM-PROC-'.$evidenciaId;
            $verificaciones[] = EscenarioEntidades::hecho(['id' => $id, 'origenId' => 'SIM-OR-'.$id,
                'fuenteId' => 'REVISION_PROCEDENCIA_SINTETICA', 'concepto' => 'VERIFICACION_PROCEDENCIA:'.$evidenciaId, 'valor' => $valor]);
        }
        $declaraciones = [...$declaraciones, ...$contextoExtra];
        $verificaciones = [...$verificaciones, ...$procedenciaExtra];
        $memoria = EscenarioEntidades::memoria($evidencias, [...$declaraciones, ...$verificaciones]);

        return ['memoria' => $memoria, 'candidatas' => $evidencias, 'contexto' => $declaraciones, 'procedencias' => $verificaciones];
    }

    /**
     * Declara manualmente las cuatro condiciones conocidas de un caso positivo.
     * @param array<string, mixed> $cambios
     * @return array<string, mixed>
     */
    public static function contextoCompleto(array $cambios = []): array
    {
        return array_replace(['componente_validado' => true, 'condiciones_aplicacion_conocidas' => true,
            'confusores_revisados' => true, 'invalidacion_necesaria' => false], $cambios);
    }

}
