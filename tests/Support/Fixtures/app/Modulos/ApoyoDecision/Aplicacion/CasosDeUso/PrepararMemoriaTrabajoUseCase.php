<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\EvidenciasEntradaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use DomainException;
use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;

/** Prepara la instantánea que recibirá el motor conservando la identidad de los hechos y evidencias recuperados para una única solicitud experimental. */
final readonly class PrepararMemoriaTrabajoUseCase
{
    /**
     * Organiza las colecciones recibidas mediante el constructor real de Domain.
     * @param SolicitudInferenciaDTO $solicitud
     * @param EvidenciasEntradaDTO $entrada
     * @return MemoriaTrabajo
     * @throws DomainException
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
