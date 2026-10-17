<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Compuertas;

use App\SistemaExperto\Domain\Entities\Hechos\EvidenciaCognitiva;
use App\SistemaExperto\Domain\Entities\Hechos\HechoExperto;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades;

/**
 * TÍTULO: Preparación manual de declaraciones sintéticas.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Repositorios y compuertas.
 *
 * RESPONSABILIDAD:
 * Verificar contratos explícitos con fuentes artificiales y objetos reales.
 *
 * ENTRADAS:
 * Identidades, declaraciones y referencias ficticias de prueba.
 *
 * SALIDAS:
 * Objetos sintéticos o aserciones sobre preservación y rechazo.
 *
 * RESTRICCIONES:
 * No inicia Laravel, no consulta persistencia ni acredita validación clínica.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fases C/D e invariantes R1–R10.
 */
final class EscenarioCompuertas
{
    /**
     * Construye un escenario con declaraciones explícitas, sin defaults de aprobación.
     *
     * @param list<EvidenciaCognitiva> $evidencias Evidencias originales únicas.
     * @param array<string, mixed> $contexto Valores declarados para campos de contexto.
     * @param array<string, mixed> $procedencias Verificaciones declaradas por ID de evidencia.
     * @param list<HechoExperto> $contextoExtra Revisiones adicionales para probar contradicciones.
     * @param list<HechoExperto> $procedenciaExtra Verificaciones adicionales.
     * @param DominioCognitivoEnum $dominio Eje de las declaraciones de contexto.
     * @return array{memoria: MemoriaTrabajo, candidatas: list<EvidenciaCognitiva>, contexto: list<HechoExperto>, procedencias: list<HechoExperto>} Escenario real del Domain.
     * @throws \DomainException Si los hechos artificiales violan el contexto de memoria.
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
     *
     * La basal y la advertencia no se inventan: no se incluyen salvo override.
     *
     * @param array<string, mixed> $cambios Variantes del escenario positivo.
     * @return array<string, mixed> Declaraciones ficticias, nunca valores calculados.
     *
     * No lanza excepciones.
     */
    public static function contextoCompleto(array $cambios = []): array
    {
        return array_replace(['componente_validado' => true, 'condiciones_aplicacion_conocidas' => true,
            'confusores_revisados' => true, 'invalidacion_necesaria' => false], $cambios);
    }

}
