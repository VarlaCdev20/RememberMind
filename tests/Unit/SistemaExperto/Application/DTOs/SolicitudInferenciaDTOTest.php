<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\DTOs;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use MotorExperto\dominio\Entities\Hechos\MemoriaTrabajo;
use DateTime;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;
use TypeError;

/** Comprueba identidades, modo sintético y contexto temporal. */
final class SolicitudInferenciaDTOTest extends TestCase
{
    /**
 * Conserva la petición original
 * @return void
 */
    public function test_conserva_identidades_fecha_version_y_modo(): void
    {
        $fecha = F::fecha();
        $dto = new SolicitudInferenciaDTO('SIM-SOL', 'SIM-EVAL', 'SINTETICO-RES', $fecha, 'SIM-V1');

        self::assertSame('SIM-SOL', $dto->solicitudId);
        self::assertSame('SIM-EVAL', $dto->evaluacionId);
        self::assertSame('SINTETICO-RES', $dto->casoSinteticoId);
        self::assertSame($fecha, $dto->fechaCorte);
        self::assertSame('SIM-V1', $dto->versionConocimiento);
        self::assertSame('SIMULACION_ACADEMICA_NO_CLINICA', $dto->modoEjecucion);
        $dto->exigirContexto(F::memoria());
    }

    /**
     * Rechaza entradas que no identifican inequívocamente una simulación.
     * @param string $campo
     * @param string $valor
     * @throws DomainException
     */
    #[DataProvider('identidadesInvalidas')]
    public function test_rechaza_identidades_y_modos_invalidos(string $campo, string $valor): void
    {
        $this->expectException(DomainException::class);
        self::solicitud([$campo => $valor]);
    }

    /**
     * Proporciona límites de identidad y los modos expresamente excluidos.
     * @return iterable<string, array{string, string}>
     */
    public static function identidadesInvalidas(): iterable
    {
        foreach (['solicitudId', 'evaluacionId', 'casoSinteticoId', 'versionConocimiento'] as $campo) {
            yield $campo.' vacío' => [$campo, ''];
        }
        yield 'espacios' => ['solicitudId', 'SIM SOL'];
        yield 'control' => ['versionConocimiento', "SIM-V1\n"];
        yield 'evaluación ajena' => ['evaluacionId', 'EVAL-REAL'];
        yield 'caso ajeno' => ['casoSinteticoId', 'RES-REAL'];
        yield 'solo prefijo de evaluación' => ['evaluacionId', 'SIM-'];
        yield 'solo prefijo de caso' => ['casoSinteticoId', 'SINTETICO-'];
        yield 'modo clínico' => ['modoEjecucion', 'CLINICO'];
        yield 'modo asistencial' => ['modoEjecucion', 'ASISTENCIAL'];
    }

    /**
     * Rechaza cadenas y fechas mutables; no analiza fechas ambiguas dentro del DTO.
     * @param mixed $fecha
     * @throws TypeError
     */
    #[DataProvider('fechasInvalidas')]
    public function test_fecha_debe_ser_un_instante_inmutable(mixed $fecha): void
    {
        $this->expectException(TypeError::class);
        self::solicitud(['fechaCorte' => $fecha]);
    }

    /** @return iterable<string, array{mixed}> */
    public static function fechasInvalidas(): iterable
    {
        yield 'cadena inválida' => ['fecha-inexistente'];
        yield 'ausencia' => [null];
        yield 'fecha mutable' => [new DateTime('2026-10-17T12:00:00-04:00')];
    }

    /**
     * Contrasta cada dimensión del contexto sin reconstruir hechos.
     * @param string $campo
     * @param string|DateTimeImmutable $valor
     * @throws DomainException
     */
    #[DataProvider('contextosAjenos')]
    public function test_memoria_debe_coincidir_con_todo_el_contexto(string $campo, string|DateTimeImmutable $valor): void
    {
        $memoria = new MemoriaTrabajo(...array_replace([
            'evaluacionId' => 'SIM-EVAL', 'residenteId' => 'SINTETICO-RES',
            'versionConocimiento' => 'SIM-V1', 'fechaCorte' => F::fecha(),
        ], [$campo => $valor]));
        $solicitud = self::solicitud();

        $this->expectException(DomainException::class);
        $solicitud->exigirContexto($memoria);
    }

    /** @return iterable<string, array{string, string|DateTimeImmutable}> */
    public static function contextosAjenos(): iterable
    {
        yield 'evaluación' => ['evaluacionId', 'SIM-OTRA'];
        yield 'caso' => ['residenteId', 'SINTETICO-OTRO'];
        yield 'conocimiento' => ['versionConocimiento', 'SIM-V2'];
        yield 'corte posterior' => ['fechaCorte', F::fecha()->modify('+1 second')];
    }

    /**
 * Admite el mismo instante en otra zona
 * @return void
 */
    public function test_contexto_compara_instantes_y_no_cadenas_de_zona(): void
    {
        $solicitud = self::solicitud(['fechaCorte' => new DateTimeImmutable('2026-10-17T16:00:00+00:00')]);
        $solicitud->exigirContexto(F::memoria());

        self::assertEquals(F::fecha(), $solicitud->fechaCorte);
    }

    /**
     * Construye una solicitud ficticia con cambios controlados para negativos.
     * @param array<string, mixed> $cambios
     * @return SolicitudInferenciaDTO
     * @throws DomainException|TypeError
     */
    private static function solicitud(array $cambios = []): SolicitudInferenciaDTO
    {
        return new SolicitudInferenciaDTO(...array_replace([
            'solicitudId' => 'SIM-SOL', 'evaluacionId' => 'SIM-EVAL', 'casoSinteticoId' => 'SINTETICO-RES',
            'fechaCorte' => F::fecha(), 'versionConocimiento' => 'SIM-V1',
        ], $cambios));
    }
}
