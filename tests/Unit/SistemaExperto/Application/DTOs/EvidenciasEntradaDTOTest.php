<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\DTOs;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\EvidenciasEntradaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/** Comprueba conservación de hechos, procedencia y ausencias. */
final class EvidenciasEntradaDTOTest extends TestCase
{
    /**
 * Conserva objetos y referencias de procedencia
 * @return void
 */
    public function test_transporta_hechos_de_contexto_y_evidencias_originales(): void
    {
        $e = F::evidencia(hallazgo: HallazgoInstrumentalEnum::DESCONOCIDO, cambiosHecho: ['valor' => null]);
        $contexto = F::hecho(['id' => 'SIM-CONTEXTO', 'origenId' => 'SIM-OR-CONTEXTO', 'valor' => false]);
        $memoria = F::memoria([$e], [$contexto]);
        $dto = new EvidenciasEntradaDTO(self::solicitud(), $memoria);

        self::assertSame($memoria, $dto->memoria);
        self::assertSame($e, $dto->evidencias[$e->hecho->id]);
        self::assertSame($contexto, $dto->hechos[$contexto->id]);
        self::assertNull($dto->evidencias[$e->hecho->id]->hecho->valor);
        self::assertFalse($dto->hechos[$contexto->id]->valor);
        self::assertSame([
            'fuenteId' => $e->hecho->fuenteId, 'origenId' => $e->hecho->origenId,
            'episodioId' => $e->hecho->episodioId, 'fecha' => $e->hecho->fecha,
            'versionConocimiento' => $e->hecho->versionConocimiento,
        ], $dto->referenciasProcedencia[$e->hecho->id]);
    }

    /**
 * Una memoria vacía conserva las ausencias
 * @return void
 */
    public function test_sin_evidencias_no_fabrica_normalidad(): void
    {
        $dto = new EvidenciasEntradaDTO(self::solicitud(), F::memoria(),
            ['SIM-DATO' => 'Dato no informado.'], ['SIM-FUENTE' => 'Fuente no disponible.']);

        self::assertSame([], $dto->hechos);
        self::assertSame([], $dto->evidencias);
        self::assertSame([], $dto->referenciasProcedencia);
        self::assertSame(['SIM-DATO' => 'Dato no informado.'], $dto->datosFaltantes);
        self::assertSame(['SIM-FUENTE' => 'Fuente no disponible.'], $dto->fuentesNoDisponibles);
    }

    /** @return void @throws DomainException Rechazo esperado de una respuesta con otro corte. */
    public function test_rechaza_respuesta_con_corte_posterior_al_solicitado(): void
    {
        $memoria = new MemoriaTrabajo('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', F::fecha()->modify('+1 second'));
        $this->expectException(DomainException::class);
        new EvidenciasEntradaDTO(self::solicitud(), $memoria);
    }

    /**
 * Rompe referencias mutables de ambos mapas
 * @return void
 */
    public function test_metadatos_no_cambian_al_mutar_las_referencias_de_entrada(): void
    {
        $dato = 'Ausencia original.';
        $fuente = 'Indisponibilidad original.';
        $dto = new EvidenciasEntradaDTO(self::solicitud(), F::memoria(),
            ['SIM-DATO' => &$dato], ['SIM-FUENTE' => &$fuente]);
        $dato = 'Modificado.';
        $fuente = 'Modificada.';

        self::assertSame('Ausencia original.', $dto->datosFaltantes['SIM-DATO']);
        self::assertSame('Indisponibilidad original.', $dto->fuentesNoDisponibles['SIM-FUENTE']);
    }

    /**
     * Rechaza metadatos ambiguos en cualquiera de los dos mapas de disponibilidad.
     * @param string $campo
     * @param array<mixed> $datos
     * @throws DomainException
     */
    #[DataProvider('metadatosInvalidos')]
    public function test_rechaza_metadatos_sin_identificador_o_razon(string $campo, array $datos): void
    {
        $this->expectException(DomainException::class);
        new EvidenciasEntradaDTO(self::solicitud(), F::memoria(), ...[$campo => $datos]);
    }

    /** @return iterable<string, array{string, array<mixed>}> */
    public static function metadatosInvalidos(): iterable
    {
        foreach (['datosFaltantes', 'fuentesNoDisponibles'] as $campo) {
            foreach (['sin identidad' => ['' => 'Razón.'], 'lista sin identidad' => ['Razón.'],
                'sin razón' => ['SIM-ID' => '  '], 'razón no textual' => ['SIM-ID' => false]] as $caso => $datos) {
                yield $campo.' '.$caso => [$campo, $datos];
            }
        }
    }

    /** @return SolicitudInferenciaDTO */
    private static function solicitud(): SolicitudInferenciaDTO
    {
        return new SolicitudInferenciaDTO('SIM-SOL', 'SIM-EVAL', 'SINTETICO-RES', F::fecha(), 'SIM-V1');
    }
}
