<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\InformeExpertoDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoDclDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResultadoInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use DomainException;
use MotorExperto\dominio\orion\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\orion\Services\Explicacion\GeneradorExplicacionService;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (CU-05, ensamblaje del informe).
 *
 * TÍTULO:
 * Construcción del informe explicativo.
 *
 * CAPA:
 * Application - Casos de Uso.
 *
 * ¿PARA QUÉ SIRVE?
 * Organiza los resultados y las explicaciones recibidas en los tres niveles
 * del InformeExpertoDTO, manteniendo su trazabilidad experimental.
 *
 * ¿QUÉ RECIBE?
 * Solicitud original, resultados de inferencia y DCL opcional, textos de resumen,
 * interpretación y fundamento, relaciones utilizadas y exclusiones justificadas.
 *
 * ¿QUÉ HACE?
 * 1. Verifica que el resultado corresponda a la petición recibida.
 * 2. Entrega los resultados y explicaciones originales al DTO de informe.
 * 3. Delega sus controles estructurales y la advertencia obligatoria al DTO.
 *
 * ¿QUÉ DEVUELVE?
 * Un InformeExpertoDTO con resumen, interpretación, fundamento y antecedentes.
 *
 * ¿QUÉ NO DEBE HACER?
 * No redacta explicaciones propias, genera diagnósticos, recalcula estados ni emite
 * PDF/HTML. La redacción automática se delega al generador real de Domain.
 *
 * RELACIÓN CON O.R.I.O.N.:
 * Implementa la modalidad «recibe explicaciones» permitida por CU-05.
 * desdeResultados utiliza GeneradorExplicacionService y conserva sus trazas.
 * ejecutar mantiene la modalidad de textos recibidos para los consumidores existentes.
 */
final readonly class GenerarInformeUseCase
{
    /**
     * Recibe el generador puro de Domain sin construir contenido al inicializar.
     *
     * @param  GeneradorExplicacionService  $explicacion  Productor de los tres niveles.
     *                                                    No genera contenido ni lanza excepciones durante la construcción.
     */
    public function __construct(
        /** Productor real de la explicación, independiente de Laravel. */
        private GeneradorExplicacionService $explicacion = new GeneradorExplicacionService,
    ) {}

    /**
     * Solicita a Domain los tres niveles desde resultados originales del mismo caso.
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Petición original.
     * @param  ResultadoInferenciaDTO  $inferencia  Perfil, trazas, relaciones y exclusiones reales.
     * @param  ResultadoDclDTO|null  $dcl  Clasificación del mismo perfil cuando se solicitó.
     * @return InformeExpertoDTO Informe con la advertencia experimental obligatoria.
     *
     * @throws DomainException Por mezcla de peticiones o antecedentes inválidos.
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
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Petición original del análisis.
     * @param  ResultadoInferenciaDTO  $inferencia  Perfil y trazas originales de esa petición.
     * @param  ResultadoDclDTO|null  $dcl  Patrón candidato o abstención del mismo perfil, si existe.
     * @param  string  $resumen  Nivel 1 ya elaborado por el productor de explicaciones.
     * @param  string  $interpretacion  Nivel 2 recibido, sin conclusiones añadidas.
     * @param  string  $fundamento  Nivel 3 recibido con respaldo en las trazas originales.
     * @param  list<RelacionSemantica>  $relacionesUtilizadas  Relaciones originales citadas en la explicación.
     * @param  array<string, string>  $exclusiones  Referencias excluidas y sus razones originales.
     * @return InformeExpertoDTO Informe con objetos, textos y advertencia conservados.
     *
     * @throws DomainException Por mezcla de solicitudes, perfiles o explicaciones estructuralmente inválidas.
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
