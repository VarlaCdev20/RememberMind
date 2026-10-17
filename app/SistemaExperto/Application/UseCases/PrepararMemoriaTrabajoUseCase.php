<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\UseCases;

use App\SistemaExperto\Application\DTOs\EvidenciasEntradaDTO;
use App\SistemaExperto\Application\DTOs\SolicitudInferenciaDTO;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (CU-02, memoria de trabajo).
 *
 * TÍTULO:
 * Preparación de la memoria de trabajo.
 *
 * CAPA:
 * Application - Casos de Uso.
 *
 * ¿PARA QUÉ SIRVE?
 * Prepara la instantánea que recibirá el motor conservando la identidad de
 * los hechos y evidencias recuperados para una única solicitud experimental.
 *
 * ¿QUÉ RECIBE?
 * SolicitudInferenciaDTO y EvidenciasEntradaDTO de la misma petición.
 *
 * ¿QUÉ HACE?
 * 1. Contrasta la petición original, incluyendo su identificador de solicitud.
 * 2. Delega la comprobación de contexto al DTO de solicitud.
 * 3. Construye MemoriaTrabajo y delega en Domain sus invariantes constructivas.
 *
 * ¿QUÉ DEVUELVE?
 * Una nueva MemoriaTrabajo con las mismas evidencias y hechos originales.
 *
 * ¿QUÉ NO DEBE HACER?
 * No recupera datos nuevamente, aplica reglas cognitivas, acredita requisitos
 * ni completa una memoria vacía con hechos o resultados ficticios.
 *
 * RELACIÓN CON O.R.I.O.N.:
 * Implementa CU-02. Domain conserva la responsabilidad por duplicación,
 * procedencia, versiones y corte; Application solo coordina la construcción.
 */
final readonly class PrepararMemoriaTrabajoUseCase
{
    /**
     * Organiza las colecciones recibidas mediante el constructor real de Domain.
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Petición propietaria de la memoria.
     * @param  EvidenciasEntradaDTO  $entrada  Evidencias extraídas para esa petición.
     * @return MemoriaTrabajo Instantánea validada, posiblemente vacía, sin alterar la entrada.
     *
     * @throws DomainException Por mezcla de peticiones o incumplimiento de invariantes de Domain.
     */
    public function ejecutar(SolicitudInferenciaDTO $solicitud, EvidenciasEntradaDTO $entrada): MemoriaTrabajo
    {
        if ($entrada->solicitud != $solicitud) {
            throw new DomainException('Las evidencias pertenecen a una solicitud diferente.');
        }
        $solicitud->exigirContexto($entrada->memoria);

        // Los mapas del DTO se convierten en listas sin copiar ni reemplazar los
        // objetos originales. La validación del contenido pertenece a Domain.
        return new MemoriaTrabajo(
            $solicitud->evaluacionId,
            $solicitud->casoSinteticoId,
            $solicitud->versionConocimiento,
            $solicitud->fechaCorte,
            array_values($entrada->hechos),
            array_values($entrada->evidencias),
        );
    }
}
