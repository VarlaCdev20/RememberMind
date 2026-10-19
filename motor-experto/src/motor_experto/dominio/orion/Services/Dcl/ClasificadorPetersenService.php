<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Services\Dcl;

use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\SubtipoDclEnum;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Clasificación experimental (Patrones candidatos).
 * TÍTULO: Clasificador Petersen exclusivamente sintético.
 * CAPA: Domain.
 * SUBSISTEMA: DCL experimental.
 * RESPONSABILIDAD: Seleccionar PET-01..04 solo después de todas las compuertas.
 * ENTRADAS: Perfil original C01–C05 de un caso ficticio.
 * SALIDAS: Patrón candidato sustentado o abstención con todos sus motivos.
 * RESTRICCIONES: No diagnostica, acredita C03/C04, convierte G09 a A/P ni crea reglas aplicadas ficticias.
 * TRAZABILIDAD: R7/R9; distribución explícita DCL-PET-20261017-v0.1.
 */
final readonly class ClasificadorPetersenService
{
    /**
     * Recibe la comprobación de requisitos sin activar capacidades asistenciales.
     *
     * @param  EvaluadorRequisitosDclService  $requisitos  Evaluador puro de impedimentos.
     *                                                     No clasifica ni lanza excepciones durante la construcción.
     */
    public function __construct(
        /** Evaluador puro de condiciones y coherencia. */
        private EvaluadorRequisitosDclService $requisitos = new EvaluadorRequisitosDclService,
    ) {}

    /**
     * Clasifica la distribución sustentada; no calcula estados de dominio.
     *
     * @param  PerfilRequisitosDcl  $perfil  Requisitos y cinco dominios explícitos.
     * @return ResultadoClasificacionDcl Resultado inmutable que retiene el perfil original.
     *
     * @throws \DomainException Si la salida viola las invariantes del resultado.
     */
    public function clasificar(PerfilRequisitosDcl $perfil): ResultadoClasificacionDcl
    {
        $revision = $this->requisitos->evaluar($perfil);
        if (! $revision['habilitado']) {
            return new ResultadoClasificacionDcl($perfil, null, $revision['motivos'],
                'Abstención experimental: '.implode('; ', array_map(
                    static fn ($motivo): string => $motivo->value, $revision['motivos']))
                .'. '.implode('; ', $revision['razones']));
        }
        $memoriaAlterada = $perfil->cognicion->resultado(DominioCognitivoEnum::MEMORIA)->estado === EstadoDominioEnum::ALTERACION;
        $otrosAlterados = 0;
        foreach ($perfil->cognicion->resultados as $resultado) {
            if ($resultado->dominio !== DominioCognitivoEnum::MEMORIA && $resultado->estado === EstadoDominioEnum::ALTERACION) {
                $otrosAlterados++;
            }
        }
        $patron = match (true) {
            $memoriaAlterada && $otrosAlterados === 0 => SubtipoDclEnum::AMNESICO_DOMINIO_UNICO,
            $memoriaAlterada => SubtipoDclEnum::AMNESICO_MULTIDOMINIO,
            $otrosAlterados === 1 => SubtipoDclEnum::NO_AMNESICO_DOMINIO_UNICO,
            default => SubtipoDclEnum::NO_AMNESICO_MULTIDOMINIO,
        };

        return new ResultadoClasificacionDcl($perfil, $patron, [],
            'Patrón candidato '.$patron->value.' bajo '.$perfil->versionContrato
            .'; cinco dominios A/P sustentados y C01–C05 acreditados explícitamente. Solo simulación académica.');
    }
}
