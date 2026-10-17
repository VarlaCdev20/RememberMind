<?php

declare(strict_types=1);

namespace App\SistemaExperto\Domain\Entities\Hechos;

use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Base de hechos (Evidencia cognitiva).
 *
 * Sitúa un hecho original en un dominio o subcomponente y documenta su alcance.
 * Permite reconocer fuentes reutilizadas antes de tratarlas como corroboración independiente.
 *
 * TÍTULO: Evidencia cognitiva con alcance y procedencia.
 *
 * CAPA: Domain.
 *
 * SUBSISTEMA: Base de hechos.
 *
 * RESPONSABILIDAD:
 * Vincular un hecho original a un dominio o subcomponente sin alterar su origen.
 *
 * ENTRADAS:
 * Hecho, dominio, subcomponente opcional, hallazgo, instrumento y justificación.
 * Las referencias externas ficticias incluyen metadatos SIM explícitos.
 *
 * SALIDAS:
 * Evidencia inmutable y comprobación de independencia respecto a otra evidencia.
 *
 * RESTRICCIONES:
 * No deduce A/P de G09 ni de un puntaje; los literales SIM son referencias
 * externas predeterminadas. Los metadatos SIM no representan normas aprobadas.
 * Un mismo episodio o resultado reutilizado no es corroboración independiente.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R3/R4/R8/R9; puente PTE-DCL-G08-TEST-01 experimental.
 */
final readonly class EvidenciaCognitiva
{
    /** @var array<string, string> Metadatos sintéticos copiados; vacíos para hallazgos descriptivos. */
    public array $referenciaSintetica;

    /**
     * Construye una evidencia sin interpretar puntuaciones.
     *
     * @param HechoExperto $hecho Instantánea original preservada.
     * @param DominioCognitivoEnum $dominio Dominio propietario.
     * @param SubcomponenteCognitivoEnum|null $subcomponente Alcance específico; null identifica alcance de dominio.
     * @param HallazgoInstrumentalEnum $hallazgo Descriptor explícito, no resultado integrado.
     * @param string|null $instrumentoId Identidad de instrumento, cuando existe.
     * @param string $justificacion Razón de la descripción o limitación.
     * @param array<string, string> $referenciaSintetica Instrumento, edición, subprueba, norma, idioma, población y revisor ficticios.
     * @throws DomainException Por dominio, identidad, ausencia o referencia incoherentes.
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
     *
     * @param self $otra Evidencia a contrastar.
     * @return bool False para otra ejecución o reutilización de resultado/episodio.
     *
     * No lanza excepciones ni cuenta contribuciones.
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
