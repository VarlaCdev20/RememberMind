<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Inferencia;

use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\Services\Compuertas\EvaluadorConfusoresService;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

/**
 * TÍTULO: Caso completo con referencias y revisiones sintéticas independientes.
 * CAPA: Domain (pruebas).
 * SUBSISTEMA: Fixtures de inferencia.
 * RESPONSABILIDAD: Preparar originales compartidos entre servicios y casos de uso.
 * ENTRADAS: Cinco estados explícitos del escenario, nunca puntuaciones reales.
 * SALIDAS: Memoria, revisiones y declaraciones DCL ficticias.
 * RESTRICCIONES: No acredita aptitud clínica, carga Laravel ni consulta bases.
 * TRAZABILIDAD: R1–R10; matriz 03A y referencias SIM del contrato.
 */
final class EscenarioInferencia
{
    /** @param list<string> $estados Estados explícitos. @return array Escenario original sin efectos externos; puede lanzar DomainException por entradas incoherentes. */
    public static function completo(array $estados = ['A', 'P', 'P', 'P', 'P']): array
    {
        $perfil = E::perfil($estados);
        $original = $perfil->cognicion->memoria;
        $hechos = array_values($original->hechos);
        $procedencias = $contexto = [];
        foreach ($original->evidencias as $e) {
            $id = 'PROC-'.$e->hecho->id;
            $h = E::hecho(['id' => $id, 'origenId' => $id, 'episodioId' => $id,
                'concepto' => 'VERIFICACION_PROCEDENCIA:'.$e->hecho->id, 'valor' => true]);
            $hechos[] = $procedencias[] = $h;
        }
        foreach (DominioCognitivoEnum::cases() as $d) {
            foreach (EvaluadorConfusoresService::CAMPOS_CONTEXTO as $campo) {
                $id = $d->value.':'.$campo;
                $h = E::hecho(['id' => $id, 'origenId' => $id, 'episodioId' => $id,
                    'concepto' => $id, 'valor' => ! in_array($campo, ['invalidacion_necesaria', 'limitacion_no_invalidante', 'basal_disponible'], true)]);
                $hechos[] = $contexto[] = $h;
            }
        }
        $memoria = new MemoriaTrabajo($original->evaluacionId, $original->residenteId, $original->versionConocimiento,
            $original->fechaCorte, $hechos, array_values($original->evidencias));

        return ['memoria' => $memoria, 'procedencias' => $procedencias, 'contexto' => $contexto,
            'entradaDcl' => ['criterios' => $perfil->criterios, 'soportes' => $perfil->soportes,
                'justificaciones' => $perfil->justificaciones, 'diferencial' => $perfil->diferencial,
                'independenciaRelativa' => $perfil->funcionalidad->independenciaRelativa,
                'evidenciasIadl' => $perfil->funcionalidad->evidencias,
                'revisorSinteticoId' => $perfil->funcionalidad->revisorSinteticoId,
                'justificacionFuncional' => $perfil->funcionalidad->justificacion]];
    }
}
