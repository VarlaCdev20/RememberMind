<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use MotorExperto\dominio\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use DateTimeImmutable;
use DomainException;

/** Transporta los hechos y evidencias recuperados junto con su procedencia y las ausencias informadas por la fuente. */
final readonly class EvidenciasEntradaDTO
{
    /** @var array<string, HechoExperto> Hechos originales, incluidos los de contexto. */
    public array $hechos;

    /** @var array<string, EvidenciaCognitiva> Evidencias originales por identidad del hecho. */
    public array $evidencias;

    /** @var array<string, array{fuenteId: string, origenId: string, episodioId: string, fecha: DateTimeImmutable, versionConocimiento: string}> */
    public array $referenciasProcedencia;

    /** @var array<string, string> Identificador del dato faltante y razón declarada. */
    public array $datosFaltantes;

    /** @var array<string, string> Identificador de fuente no disponible y razón declarada. */
    public array $fuentesNoDisponibles;

    /**
     * Conserva la respuesta de Domain sin sustituir evidencias ni completar ausencias.
     * @param SolicitudInferenciaDTO $solicitud
     * @param MemoriaTrabajo $memoria
     * @param array<string, string> $datosFaltantes
     * @param array<string, string> $fuentesNoDisponibles
     * @throws DomainException
     */
    public function __construct(
        /** Solicitud a la que pertenecen las evidencias. */ public SolicitudInferenciaDTO $solicitud,
        /** Instantánea original validada por Domain. */ public MemoriaTrabajo $memoria,
        array $datosFaltantes = [],
        array $fuentesNoDisponibles = [],
    ) {
        $solicitud->exigirContexto($memoria);
        $this->datosFaltantes = self::copiarAusencias($datosFaltantes);
        $this->fuentesNoDisponibles = self::copiarAusencias($fuentesNoDisponibles);
        $this->hechos = $memoria->hechos;
        $this->evidencias = $memoria->evidencias;
        $procedencia = [];
        foreach ($memoria->hechos as $id => $hecho) {
            $procedencia[$id] = [
                'fuenteId' => $hecho->fuenteId, 'origenId' => $hecho->origenId,
                'episodioId' => $hecho->episodioId, 'fecha' => $hecho->fecha,
                'versionConocimiento' => $hecho->versionConocimiento,
            ];
        }
        $this->referenciasProcedencia = $procedencia;
    }

    /**
     * Reconstruye metadatos para romper referencias externas de los arrays PHP.
     * @param array<string, string> $ausencias
     * @return array<string, string>
     * @throws DomainException
     */
    private static function copiarAusencias(array $ausencias): array
    {
        $copia = [];
        foreach ($ausencias as $id => $razon) {
            if (! is_string($id) || $id === '' || preg_match('/[\x00-\x20\x7F]/', $id) === 1
                || ! is_string($razon) || trim($razon) === '') {
                throw new DomainException('Cada ausencia requiere un identificador y una razón textual explícita.');
            }
            $copia[$id] = $razon;
        }

        return $copia;
    }
}
