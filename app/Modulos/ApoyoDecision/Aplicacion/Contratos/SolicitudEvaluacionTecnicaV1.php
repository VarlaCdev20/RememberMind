<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Contratos;

use DateTimeImmutable;
use DomainException;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;

/** Sobre técnico del contexto ya existente; no autoriza una evaluación clínica. */
final readonly class SolicitudEvaluacionTecnicaV1
{
    public const VERSION = 1;

    public function __construct(public ContextoEjecucion $contexto, public string $criterio)
    {
        if ($criterio === '' || strlen($criterio) > 20) {
            throw new DomainException('Identificador de criterio experto inválido.');
        }
    }

    public static function desdeArray(array $datos): self
    {
        $claves = ['contrato', 'uso', 'evaluacion', 'residente', 'version_conocimiento', 'fecha_corte', 'criterio'];
        if (array_diff(array_keys($datos), $claves) || array_diff($claves, array_keys($datos))
            || $datos['contrato'] !== self::VERSION || $datos['uso'] !== 'PRUEBA_TECNICA') {
            throw new DomainException('Contrato de solicitud técnica incompatible.');
        }
        foreach (array_diff($claves, ['contrato']) as $clave) {
            if (! is_string($datos[$clave])) {
                throw new DomainException('El contrato requiere identificadores y fecha textuales.');
            }
        }
        $fecha = DateTimeImmutable::createFromFormat(DATE_ATOM, $datos['fecha_corte']);
        if ($fecha === false || $fecha->format(DATE_ATOM) !== $datos['fecha_corte']) {
            throw new DomainException('La fecha de corte requiere ISO 8601 con zona horaria.');
        }

        return new self(new ContextoEjecucion($datos['evaluacion'], $datos['residente'], $datos['version_conocimiento'], $fecha), $datos['criterio']);
    }

    public function paraArray(): array
    {
        return ['contrato' => self::VERSION, 'uso' => 'PRUEBA_TECNICA', 'evaluacion' => $this->contexto->evaluacion,
            'residente' => $this->contexto->residente, 'version_conocimiento' => $this->contexto->version,
            'fecha_corte' => $this->contexto->fechaCorte->format(DATE_ATOM), 'criterio' => $this->criterio];
    }

    public function claveIdempotencia(): string
    {
        return hash('sha256', json_encode($this->paraArray(), JSON_THROW_ON_ERROR));
    }
}
