<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\DTOs;

use App\SistemaExperto\Application\DTOs\ResultadoDclDTO;
use App\SistemaExperto\Application\DTOs\SolicitudInferenciaDTO;
use App\SistemaExperto\Domain\Entities\Dcl\ResultadoClasificacionDcl;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum as C;
use App\SistemaExperto\Domain\ValueObjects\Dcl\MotivoAbstencionEnum as M;
use App\SistemaExperto\Domain\ValueObjects\Dcl\SubtipoDclEnum as P;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoDiferencialEnum as D;
use DomainException;
use Error;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/**
 * TÍTULO: Transporte de patrones candidatos y abstenciones.
 * CAPA: Application (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Comprueba que Application no cambie la salida de Domain.
 * ¿QUÉ RECIBE? Resultados declarados con perfiles sintéticos compatibles.
 * ¿QUÉ HACE? Envuelve resultados originales y verifica sus referencias.
 * ¿QUÉ DEVUELVE? Aserciones sobre candidatos, abstenciones y requisitos.
 * ¿QUÉ NO DEBE HACER? No ejecuta ni acredita un clasificador todavía pendiente.
 * RELACIÓN CON O.R.I.O.N.: Reglamento de Application, DTO 04 y etapa B.
 */
final class ResultadoDclDTOTest extends TestCase
{
    /**
     * Conserva cada candidato declarado y sus soportes originales.
     *
     * @param  list<string>  $estados  Cinco estados sintéticos de entrada.
     * @param  P  $patron  Candidato explícito, no calculado por el DTO.
     * @return void
     *              Sin excepciones esperadas con la distribución compatible.
     */
    #[DataProvider('patrones')]
    public function test_transporta_los_cuatro_patrones_sin_reclasificar(array $estados, P $patron): void
    {
        $perfil = F::perfil($estados);
        $original = new ResultadoClasificacionDcl($perfil, $patron, [], 'Candidato ficticio declarado.');
        $dto = new ResultadoDclDTO(self::solicitud(), $original);

        self::assertSame($original, $dto->resultado);
        self::assertSame($patron, $dto->patronCandidato);
        self::assertFalse($dto->abstencion);
        self::assertSame([], $dto->motivos);
        self::assertSame($estados, array_map(static fn ($estado) => $estado->value, array_values($dto->estadosDominios)));
        self::assertSame($perfil->criterios, $dto->criterios);
        self::assertSame($perfil->soportes, $dto->soportes);
        self::assertSame($perfil->soportes['DCL-C01'][0], $dto->soportes['DCL-C01'][0]);
        self::assertSame('SIM-EVAL', $dto->analisisId);
        self::assertSame($perfil->versionContrato, $dto->versionContrato);
    }

    /** @return iterable<string, array{list<string>, P}> Distribuciones manuales; sin parámetros ni excepciones. */
    public static function patrones(): iterable
    {
        yield 'PET-01' => [['A', 'P', 'P', 'P', 'P'], P::AMNESICO_DOMINIO_UNICO];
        yield 'PET-02' => [['A', 'A', 'P', 'P', 'P'], P::AMNESICO_MULTIDOMINIO];
        yield 'PET-03' => [['P', 'A', 'P', 'P', 'P'], P::NO_AMNESICO_DOMINIO_UNICO];
        yield 'PET-04' => [['P', 'A', 'A', 'P', 'P'], P::NO_AMNESICO_MULTIDOMINIO];
    }

    /**
     * Conserva motivos de no conclusión sin introducir un patrón favorable.
     *
     * @param  string  $caso  Tipo de limitación sintética.
     * @param  M  $motivo  Salida declarada del Domain.
     * @return void
     *              Sin excepciones esperadas; el DTO no decide el motivo de abstención.
     */
    #[DataProvider('abstenciones')]
    public function test_abstencion_se_transporta_sin_convertirla_en_pet(string $caso, M $motivo): void
    {
        $perfil = match ($caso) {
            'insuficiente' => F::perfil(['U', 'P', 'P', 'P', 'P']),
            'contradicción' => F::perfil(['X', 'P', 'P', 'P', 'P']),
            'diferencial' => F::perfil(cambiosCriterios: ['DCL-C04' => C::PENDIENTE], diferencial: D::PENDIENTE),
        };
        $original = new ResultadoClasificacionDcl($perfil, null, [$motivo], 'Abstención ficticia explicada.');
        $dto = new ResultadoDclDTO(self::solicitud(), $original);

        self::assertSame($original, $dto->resultado);
        self::assertNull($dto->patronCandidato);
        self::assertTrue($dto->abstencion);
        self::assertSame([$motivo], $dto->motivos);
        self::assertSame($perfil->criterios, $dto->criterios);
    }

    /** @return iterable<string, array{string, M}> No conclusiones; sin parámetros ni excepciones. */
    public static function abstenciones(): iterable
    {
        yield 'datos insuficientes' => ['insuficiente', M::ABSTENCION_INFORMACION_INSUFICIENTE];
        yield 'contradicción' => ['contradicción', M::ABSTENCION_CONTRADICCION];
        yield 'diferencial pendiente' => ['diferencial', M::ABSTENCION_EVALUACION_DIFERENCIAL];
    }

    /** @return void @throws DomainException Rechazo esperado de una salida de otra evaluación. */
    public function test_rechaza_resultado_de_otro_contexto(): void
    {
        $original = new ResultadoClasificacionDcl(F::perfil(), P::AMNESICO_DOMINIO_UNICO, [], 'Candidato ficticio.');
        $solicitud = new SolicitudInferenciaDTO('SIM-SOL', 'SIM-OTRA', 'SINTETICO-RES', F::fecha(), 'SIM-V1');
        $this->expectException(DomainException::class);
        new ResultadoDclDTO($solicitud, $original);
    }

    /** @return void @throws Error Rechazo esperado de la mutación de un candidato transportado. */
    public function test_no_permite_reemplazar_el_patron_original(): void
    {
        $original = new ResultadoClasificacionDcl(F::perfil(), P::AMNESICO_DOMINIO_UNICO, [], 'Candidato ficticio.');
        $dto = new ResultadoDclDTO(self::solicitud(), $original);
        $this->expectException(Error::class);
        $dto->patronCandidato = P::NO_AMNESICO_MULTIDOMINIO;
    }

    /** @return SolicitudInferenciaDTO Solicitud ficticia; sin parámetros ni excepciones esperadas. */
    private static function solicitud(): SolicitudInferenciaDTO
    {
        return new SolicitudInferenciaDTO('SIM-SOL', 'SIM-EVAL', 'SINTETICO-RES', F::fecha(), 'SIM-V1');
    }
}
