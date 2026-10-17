<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\UseCases;

use App\SistemaExperto\Application\DTOs\InformeExpertoDTO;
use App\SistemaExperto\Application\DTOs\ResultadoDclDTO;
use App\SistemaExperto\Application\DTOs\ResultadoInferenciaDTO;
use App\SistemaExperto\Application\DTOs\SolicitudInferenciaDTO;
use App\SistemaExperto\Domain\Entities\Conocimiento\RelacionSemantica;
use DomainException;

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
 * No redacta explicaciones, genera diagnósticos, recalcula estados ni emite
 * PDF/HTML. Recibir textos no demuestra que exista un generador de Domain.
 *
 * RELACIÓN CON O.R.I.O.N.:
 * Implementa la modalidad «recibe explicaciones» permitida por CU-05.
 * El productor debe proporcionar explicaciones respaldadas por las trazas;
 * invocar automáticamente GeneradorExplicacionService requiere su implementación
 * posterior en Domain y no se simula en este caso de uso.
 */
final readonly class GenerarInformeUseCase
{
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
