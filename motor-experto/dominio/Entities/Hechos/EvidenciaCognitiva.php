<?php

declare(strict_types=1);

namespace MotorExperto\dominio\Entities\Hechos;

use MotorExperto\dominio\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use DomainException;

/** Vincula cada evidencia a su fuente y alcance; permite detectar fuentes reutilizadas. */
final readonly class EvidenciaCognitiva
{
    /** @var array<string, string> Metadatos sintéticos copiados; vacíos para hallazgos descriptivos. */
    public array $referenciaSintetica;

    /**
     * Construye una evidencia sin interpretar puntuaciones.
     * @param HechoExperto $hecho
     * @param DominioCognitivoEnum $dominio
     * @param SubcomponenteCognitivoEnum|null $subcomponente
     * @param HallazgoInstrumentalEnum $hallazgo
     * @param string|null $instrumentoId
     * @param string $justificacion
     * @param array<string, string> $referenciaSintetica
     * @throws DomainException
     */
    public function __construct(
        /** Hecho original inmutable. */ public HechoExperto $hecho,
        /** Dominio propietario. */ public DominioCognitivoEnum $dominio,
        /** Subcomponente específico o alcance de dominio. */ public ?SubcomponenteCognitivoEnum $subcomponente,
        /** Hallazgo explícito. */ public HallazgoInstrumentalEnum $hallazgo,
        /** Instrumento documentado, si existe. */ public ?string $instrumentoId,
        /** Explicación del descriptor. */ public string $justificacion,
        array $referenciaSintetica = [],
    ) {
        if (($subcomponente !== null && $subcomponente->dominio() !== $dominio) || trim($justificacion) === '') {
            throw new DomainException('La evidencia requiere dominio coherente y justificación.');
        }
        if ($instrumentoId !== null && ($instrumentoId === '' || preg_match('/[\x00-\x20\x7F]/', $instrumentoId) === 1)) {
            throw new DomainException('Identidad instrumental inválida.');
        }
        if ($hecho->valor === null && $hallazgo !== HallazgoInstrumentalEnum::DESCONOCIDO) {
            throw new DomainException('La ausencia de dato solo puede conservarse como desconocimiento.');
        }
        $simulada = in_array($hallazgo, [HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA, HallazgoInstrumentalEnum::RENDIMIENTO_ESPERADO_SIMULADO], true);
        $copia = [];
        if ($simulada) {
            $claves = ['instrumento', 'edicion', 'subprueba', 'norma', 'idioma', 'poblacion', 'revisor'];
            $actuales = array_keys($referenciaSintetica);
            sort($actuales);
            $esperadas = $claves;
            sort($esperadas);
            if ($actuales !== $esperadas || $hecho->fuenteId !== 'REFERENCIA_EXTERNA_SINTETICA'
                || ! str_starts_with($hecho->evaluacionId, 'SIM-') || ! str_starts_with($hecho->residenteId, 'SINTETICO-')
                || $hecho->concepto !== ($subcomponente?->value ?? $dominio->value) || $hecho->valor !== $hallazgo->value) {
                throw new DomainException('La interpretación requiere una referencia externa sintética coherente.');
            }
            foreach ($claves as $clave) {
                $valor = $referenciaSintetica[$clave];
                if (! is_string($valor) || ! str_starts_with($valor, 'SIM-') || strlen($valor) <= 4
                    || preg_match('/[\x00-\x20\x7F]/', $valor) === 1) {
                    throw new DomainException('Metadato ficticio incompleto o no identificable.');
                }
                $copia[$clave] = $valor;
            }
            if ($instrumentoId !== $copia['instrumento']) {
                throw new DomainException('Instrumento incompatible con la referencia sintética.');
            }
        } elseif ($referenciaSintetica !== []) {
            throw new DomainException('Un hallazgo descriptivo no puede ocultar una referencia A/P.');
        }
        $this->referenciaSintetica = $copia;
    }

    /**
     * Contrasta independencia de fuentes dentro del mismo caso y versión.
     * @param self $otra
     * @return bool
     */
    public function esIndependienteDe(self $otra): bool
    {
        return $this->hecho->evaluacionId === $otra->hecho->evaluacionId
            && $this->hecho->residenteId === $otra->hecho->residenteId
            && $this->hecho->versionConocimiento === $otra->hecho->versionConocimiento
            && $this->hecho->id !== $otra->hecho->id
            && $this->hecho->claveOrigen() !== $otra->hecho->claveOrigen()
            && ($this->instrumentoId === null || $otra->instrumentoId === null || $this->instrumentoId !== $otra->instrumentoId)
            && $this->hecho->episodioId !== $otra->hecho->episodioId;
    }

}
