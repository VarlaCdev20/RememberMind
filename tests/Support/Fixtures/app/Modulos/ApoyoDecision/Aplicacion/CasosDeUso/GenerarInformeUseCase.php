<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\InformeExpertoDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoDclDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use DomainException;
use MotorExperto\dominio\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\Services\Explicacion\GeneradorExplicacionService;

/** Organiza los resultados y las explicaciones recibidas en los tres niveles del InformeExpertoDTO, manteniendo su trazabilidad experimental. */
final readonly class GenerarInformeUseCase
{
    /**
     * Recibe el generador puro de Domain sin construir contenido al inicializar.
     * @param GeneradorExplicacionService $explicacion
     */
    public function __construct(
        /** Productor real de la explicación, independiente de Laravel. */
        private GeneradorExplicacionService $explicacion = new GeneradorExplicacionService,
    ) {}

    /**
     * Solicita a Domain los tres niveles desde resultados originales del mismo caso.
     * @param SolicitudInferenciaDTO $solicitud
     * @param ResultadoInferenciaDTO $inferencia
     * @param ResultadoDclDTO|null $dcl
     * @return InformeExpertoDTO
     * @throws DomainException
     */
    public function desdeResultados(SolicitudInferenciaDTO $solicitud, ResultadoInferenciaDTO $inferencia,
        ?ResultadoDclDTO $dcl = null): InformeExpertoDTO
    {
        if ($inferencia->solicitud != $solicitud || ($dcl !== null && $dcl->solicitud != $solicitud)) {
            throw new DomainException('Los resultados del informe pertenecen a otra solicitud.');
        }
        $textos = $this->explicacion->generar($inferencia->perfilCognitivo, $inferencia->reglasPorDominio,
            $inferencia->trazas, $inferencia->advertencias, $dcl?->resultado,
            $inferencia->relacionesUtilizadas, $inferencia->exclusiones);

        return $this->ejecutar($solicitud, $inferencia, $dcl, $textos['resumen'], $textos['interpretacion'],
            $textos['fundamento'], $inferencia->relacionesUtilizadas, $inferencia->exclusiones);
    }

    /**
     * Ensambla explicaciones previamente elaboradas sin cambiar su contenido.
     * @param SolicitudInferenciaDTO $solicitud
     * @param ResultadoInferenciaDTO $inferencia
     * @param ResultadoDclDTO|null $dcl
     * @param string $resumen
     * @param string $interpretacion
     * @param string $fundamento
     * @param list<RelacionSemantica> $relacionesUtilizadas
     * @param array<string, string> $exclusiones
     * @return InformeExpertoDTO
     * @throws DomainException
     */
    public function ejecutar(
        SolicitudInferenciaDTO $solicitud,
        ResultadoInferenciaDTO $inferencia,
        ?ResultadoDclDTO $dcl,
        string $resumen,
        string $interpretacion,
        string $fundamento,
        array $relacionesUtilizadas = [],
        array $exclusiones = [],
    ): InformeExpertoDTO {
        if ($inferencia->solicitud != $solicitud) {
            throw new DomainException('El resultado de inferencia pertenece a una solicitud diferente.');
        }

        return new InformeExpertoDTO(
            $inferencia,
            $dcl,
            $resumen,
            $interpretacion,
            $fundamento,
            $relacionesUtilizadas,
            $exclusiones,
        );
    }
}
