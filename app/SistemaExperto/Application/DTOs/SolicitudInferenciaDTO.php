<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\DTOs;

use App\SistemaExperto\Domain\Entities\Dcl\ResultadoClasificacionDcl;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use DateTimeImmutable;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (Solicitud experimental).
 *
 * TÍTULO:
 * Solicitud de análisis del sistema experto.
 *
 * CAPA:
 * Application - Objetos de Transferencia de Datos (DTOs).
 *
 * ¿PARA QUÉ SIRVE?
 * Identifica una petición experimental y fija el contexto que debe conservarse
 * durante extracción, inferencia y elaboración del informe.
 *
 * ¿QUÉ RECIBE?
 * Identificadores de solicitud, evaluación y caso sintético, fecha de corte,
 * versión de conocimiento y modo de ejecución explícito.
 *
 * ¿QUÉ HACE?
 * Valida identidades y modo sintético. Permite contrastar una memoria existente
 * con el contexto solicitado sin reconstruirla ni cambiar sus hechos.
 *
 * ¿QUÉ DEVUELVE?
 * Una solicitud inmutable con fecha y versión originales.
 *
 * ¿QUÉ NO DEBE HACER?
 * No admite ejecución clínica, consulta fuentes, interpreta instrumentos
 * ni garantiza idempotencia: conserva el identificador para comprobarla después.
 *
 * RELACIÓN CON O.R.I.O.N.:
 * Es la entrada de CU-01 a CU-06. Reutiliza el marcador experimental de Domain
 * y los prefijos SIM-/SINTETICO- utilizados por sus referencias ficticias.
 */
final readonly class SolicitudInferenciaDTO
{
    /** Único modo admitido; coincide con el alcance declarado por Domain. */
    public const MODO_EXPERIMENTAL = ResultadoClasificacionDcl::USO;

    /**
     * Valida una solicitud sin asignar datos ausentes ni normalizar identidades.
     *
     * @param  string  $solicitudId  Identidad de petición, conservada para trazabilidad.
     * @param  string  $evaluacionId  Evaluación ficticia con prefijo SIM-.
     * @param  string  $casoSinteticoId  Caso ficticio con prefijo SINTETICO-.
     * @param  DateTimeImmutable  $fechaCorte  Instante válido e inmutable, con su zona.
     * @param  string  $versionConocimiento  Versión o manifiesto requerido, sin resolverlo.
     * @param  string  $modoEjecucion  Marcador de simulación académica no clínica.
     *
     * @throws DomainException Por identidad vacía, prefijo incompleto o modo no admitido.
     * @throws \TypeError Si la fecha u otro argumento incumple el tipo declarado.
     */
    public function __construct(
        /** Identidad de la solicitud original. */ public string $solicitudId,
        /** Identidad de la evaluación ficticia. */ public string $evaluacionId,
        /** Identidad del caso, nunca una referencia Eloquent. */ public string $casoSinteticoId,
        /** Corte inclusivo proporcionado por quien solicita el análisis. */ public DateTimeImmutable $fechaCorte,
        /** Versión exacta del conocimiento requerido. */ public string $versionConocimiento,
        /** Alcance técnico de la ejecución. */ public string $modoEjecucion = self::MODO_EXPERIMENTAL,
    ) {
        foreach ([$solicitudId, $evaluacionId, $casoSinteticoId, $versionConocimiento] as $identificador) {
            if ($identificador === '' || preg_match('/[\x00-\x20\x7F]/', $identificador) === 1) {
                throw new DomainException('La solicitud requiere identificadores y versión sin espacios ni controles.');
            }
        }
        if (! str_starts_with($evaluacionId, 'SIM-') || strlen($evaluacionId) <= 4
            || ! str_starts_with($casoSinteticoId, 'SINTETICO-') || strlen($casoSinteticoId) <= 10
            || $modoEjecucion !== self::MODO_EXPERIMENTAL) {
            throw new DomainException('La solicitud solo admite evaluaciones y casos inequívocamente sintéticos.');
        }
        // DateTimeImmutable aporta una fecha tipada; el DTO no analiza cadenas
        // ambiguas ni impone límites temporales clínicos inexistentes.
    }

    /**
     * Contrasta la identidad de una memoria ya validada por Domain.
     * No devuelve datos ni modifica la instantánea recibida.
     *
     * @param  MemoriaTrabajo  $memoria  Instantánea recibida de un contrato de dominio.
     *
     * @throws DomainException Si evaluación, caso, versión o corte no coinciden.
     *
     * El residenteId de esta memoria identifica exclusivamente el caso sintético.
     * La comparación temporal usa el instante, aunque las zonas sean distintas.
     */
    public function exigirContexto(MemoriaTrabajo $memoria): void
    {
        if ($memoria->evaluacionId !== $this->evaluacionId || $memoria->residenteId !== $this->casoSinteticoId
            || $memoria->versionConocimiento !== $this->versionConocimiento || $memoria->fechaCorte != $this->fechaCorte) {
            throw new DomainException('La memoria no corresponde al contexto y corte de la solicitud.');
        }
    }
}
