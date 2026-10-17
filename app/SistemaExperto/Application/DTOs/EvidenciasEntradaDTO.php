<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\DTOs;

use App\SistemaExperto\Domain\Entities\Hechos\EvidenciaCognitiva;
use App\SistemaExperto\Domain\Entities\Hechos\HechoExperto;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use DateTimeImmutable;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (Transporte de evidencias).
 *
 * TÍTULO:
 * Conjunto de evidencias para la inferencia.
 *
 * CAPA:
 * Application - Objetos de Transferencia de Datos (DTOs).
 *
 * ¿PARA QUÉ SIRVE?
 * Transporta los hechos y evidencias recuperados junto con su procedencia
 * y las ausencias informadas por la fuente.
 *
 * ¿QUÉ RECIBE?
 * Solicitud original, memoria validada de Domain y mapas de datos faltantes
 * o fuentes no disponibles, con una razón explícita por identificador.
 *
 * ¿QUÉ HACE?
 * Contrasta el contexto, conserva los objetos originales y copia las referencias
 * de procedencia. Conserva una memoria vacía sin inventar resultados negativos.
 *
 * ¿QUÉ DEVUELVE?
 * Evidencias tipadas, hechos originales y metadatos inmutables de disponibilidad.
 *
 * ¿QUÉ NO DEBE HACER?
 * No consulta repositorios, filtra silenciosamente otro caso, califica pruebas
 * ni duplica las validaciones de integridad que ya realiza MemoriaTrabajo.
 *
 * RELACIÓN CON O.R.I.O.N.:
 * Conecta CU-01 con CU-02. El repositorio actual entrega MemoriaTrabajo;
 * conservarla evita perder hechos de contexto que no son evidencia cognitiva.
 */
final readonly class EvidenciasEntradaDTO
{
    /** @var array<string, HechoExperto> Hechos originales, incluidos los de contexto. */
    public array $hechos;

    /** @var array<string, EvidenciaCognitiva> Evidencias originales por identidad del hecho. */
    public array $evidencias;

    /** @var array<string, array{fuenteId: string, origenId: string, episodioId: string, fecha: DateTimeImmutable, versionConocimiento: string}> Procedencia por hecho. */
    public array $referenciasProcedencia;

    /** @var array<string, string> Identificador del dato faltante y razón declarada. */
    public array $datosFaltantes;

    /** @var array<string, string> Identificador de fuente no disponible y razón declarada. */
    public array $fuentesNoDisponibles;

    /**
     * Conserva la respuesta de Domain sin sustituir evidencias ni completar ausencias.
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Petición original y contexto sintético.
     * @param  MemoriaTrabajo  $memoria  Respuesta íntegra del repositorio de Domain.
     * @param  array<string, string>  $datosFaltantes  Ausencias declaradas; vacío significa ninguna informada.
     * @param  array<string, string>  $fuentesNoDisponibles  Indisponibilidades declaradas, sin suponer disponibilidad.
     *
     * @throws DomainException Por contexto ajeno o metadatos estructuralmente inválidos.
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
     *
     * @param  array<string, string>  $ausencias  Identificadores y razones recibidos.
     * @return array<string, string> Copia independiente que conserva los textos exactos.
     *
     * @throws DomainException Por identificador inválido o razón vacía/no textual.
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
