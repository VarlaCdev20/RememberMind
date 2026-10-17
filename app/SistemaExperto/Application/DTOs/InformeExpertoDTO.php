<?php

declare(strict_types=1);

namespace App\SistemaExperto\Application\DTOs;

use App\SistemaExperto\Domain\Entities\Conocimiento\RelacionSemantica;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Application (Informe estructurado).
 *
 * TÍTULO:
 * Informe final estructurado de O.R.I.O.N.
 *
 * CAPA:
 * Application - Objetos de Transferencia de Datos (DTOs).
 *
 * ¿PARA QUÉ SIRVE?
 * Transporta los tres niveles del informe: resumen, interpretación y fundamento,
 * junto con los resultados originales que permiten revisar su trazabilidad.
 *
 * ¿QUÉ RECIBE?
 * Resultado cognitivo, resultado DCL opcional, textos explicativos suministrados
 * por Domain, relaciones utilizadas y exclusiones justificadas.
 *
 * ¿QUÉ HACE?
 * Verifica la coherencia del contexto, conserva el texto recibido y adjunta
 * versiones y la advertencia experimental obligatoria.
 *
 * ¿QUÉ DEVUELVE?
 * Un informe inmutable con sus tres secciones y referencias originales.
 *
 * ¿QUÉ NO DEBE HACER?
 * No redacta conclusiones nuevas, interpreta instrumentos, modifica estados,
 * diagnostica ni genera PDF, HTML o vistas. No sustituye al servicio de explicación.
 *
 * RELACIÓN CON O.R.I.O.N.:
 * Es la salida prevista de CU-05/CU-06. Evidencias, reglas, trazas y motivos
 * permanecen accesibles en los DTOs originales, sin producir copias divergentes.
 */
final readonly class InformeExpertoDTO
{
    /** Advertencia obligatoria del reglamento; ningún constructor puede reemplazarla. */
    public const ADVERTENCIA_EXPERIMENTAL = 'SIMULACIÓN ACADÉMICA — SIN VALIDEZ DIAGNÓSTICA — NO UTILIZAR PARA DECISIONES ASISTENCIALES.';

    /** Solicitud original, obtenida del resultado cognitivo. */
    public SolicitudInferenciaDTO $solicitud;

    /** Advertencia que debe conservar toda presentación posterior del informe. */
    public string $advertencia;

    /** @var list<RelacionSemantica> Relaciones originales declaradas en la explicación. */
    public array $relacionesUtilizadas;

    /** @var array<string, string> Referencia excluida y razón original de exclusión. */
    public array $exclusiones;

    /** @var array<string, string> Versiones efectivamente recibidas, sin completar las ausentes. */
    public array $versiones;

    /**
     * Conserva un informe explicado previamente, sin generar interpretación adicional.
     *
     * @param  ResultadoInferenciaDTO  $inferencia  Resultados, evidencias, reglas y trazas originales.
     * @param  ResultadoDclDTO|null  $dcl  Resultado opcional del mismo perfil cognitivo.
     * @param  string  $resumen  Nivel 1: contexto, evaluabilidad y hallazgos o limitaciones.
     * @param  string  $interpretacion  Nivel 2: perfil y patrón candidato o abstención.
     * @param  string  $fundamento  Nivel 3: explicación respaldada por las referencias conservadas.
     * @param  list<RelacionSemantica>  $relacionesUtilizadas  Relaciones citadas por Domain, de la misma versión.
     * @param  array<string, string>  $exclusiones  Exclusiones y razones suministradas por Domain.
     *
     * @throws DomainException Por secciones vacías, mezcla de perfiles o referencias inválidas.
     */
    public function __construct(
        /** Perfil cognitivo y trazabilidad originales. */ public ResultadoInferenciaDTO $inferencia,
        /** Salida de Petersen opcional, sin inventar un resultado cuando no existe. */ public ?ResultadoDclDTO $dcl,
        /** Nivel 1 recibido del productor de la explicación. */ public string $resumen,
        /** Nivel 2 recibido del productor de la explicación. */ public string $interpretacion,
        /** Nivel 3 recibido del productor de la explicación. */ public string $fundamento,
        array $relacionesUtilizadas = [],
        array $exclusiones = [],
    ) {
        foreach ([$resumen, $interpretacion, $fundamento] as $seccion) {
            if (trim($seccion) === '') {
                throw new DomainException('El informe requiere resumen, interpretación y fundamento explícitos.');
            }
        }
        if ($dcl !== null && ($dcl->solicitud != $inferencia->solicitud
            || $dcl->resultado->perfil->cognicion !== $inferencia->perfilCognitivo)) {
            throw new DomainException('El informe no puede mezclar solicitudes o perfiles cognitivos distintos.');
        }
        if (! array_is_list($relacionesUtilizadas)) {
            throw new DomainException('Las relaciones utilizadas deben entregarse como lista.');
        }
        $relaciones = $ids = [];
        foreach ($relacionesUtilizadas as $relacion) {
            if (! $relacion instanceof RelacionSemantica
                || $relacion->versionConocimiento !== $inferencia->solicitud->versionConocimiento || isset($ids[$relacion->id])) {
                throw new DomainException('Relación utilizada mal tipada, duplicada o de otra versión.');
            }
            $ids[$relacion->id] = true;
            $relaciones[] = $relacion;
        }
        $copiaExclusiones = [];
        foreach ($exclusiones as $id => $razon) {
            if (! is_string($id) || $id === '' || preg_match('/[\x00-\x20\x7F]/', $id) === 1
                || ! is_string($razon) || trim($razon) === '') {
                throw new DomainException('Cada exclusión requiere una referencia y una razón textual.');
            }
            $copiaExclusiones[$id] = $razon;
        }
        $versiones = ['conocimiento' => $inferencia->solicitud->versionConocimiento, 'motor' => $inferencia->versionMotor];
        if ($dcl !== null) {
            $versiones['contratoPetersen'] = $dcl->versionContrato;
        }
        $this->solicitud = $inferencia->solicitud;
        $this->advertencia = self::ADVERTENCIA_EXPERIMENTAL;
        $this->relacionesUtilizadas = $relaciones;
        $this->exclusiones = $copiaExclusiones;
        $this->versiones = $versiones;
    }
}
