<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\Services\Dcl;

use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\SubtipoDclEnum;

/** Selecciona un patrón candidato solo con requisitos cumplidos; conserva las abstenciones. */
final readonly class ClasificadorPetersenService
{
    /**
     * Recibe la comprobación de requisitos sin activar capacidades asistenciales.
     * @param EvaluadorRequisitosDclService $requisitos
     */
    public function __construct(
        /** Evaluador puro de condiciones y coherencia. */
        private EvaluadorRequisitosDclService $requisitos = new EvaluadorRequisitosDclService,
    ) {}

    /**
     * Clasifica la distribución sustentada; no calcula estados de dominio.
     * @param PerfilRequisitosDcl $perfil
     * @return ResultadoClasificacionDcl
     * @throws \DomainException
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
