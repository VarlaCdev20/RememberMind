<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

use MotorExperto\dominio\orion\Entities\Conocimiento\RelacionSemantica;
use DomainException;

/** Transporta los tres niveles del informe: resumen, interpretación y fundamento, junto con los resultados originales que permiten revisar su trazabilidad. */
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
     * @param ResultadoInferenciaDTO $inferencia
     * @param ResultadoDclDTO|null $dcl
     * @param string $resumen
     * @param string $interpretacion
     * @param string $fundamento
     * @param list<RelacionSemantica> $relacionesUtilizadas
     * @param array<string, string> $exclusiones
     * @throws DomainException
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
