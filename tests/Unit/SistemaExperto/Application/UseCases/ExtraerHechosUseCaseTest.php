<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\UseCases;

use App\SistemaExperto\Application\DTOs\SolicitudInferenciaDTO;
use App\SistemaExperto\Application\UseCases\ExtraerHechosUseCase;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\Repositories\Hechos\EvidenciaRepositoryInterface;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/**
 * TÍTULO: Orquestación de la extracción por contrato de Domain.
 * CAPA: Application (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Comprueba parámetros, procedencia y propagación de errores de CU-01.
 * ¿QUÉ RECIBE? Solicitudes sintéticas y un repositorio simulado en memoria.
 * ¿QUÉ HACE? Ejecuta el caso de uso con entidades reales y respuestas controladas.
 * ¿QUÉ DEVUELVE? Aserciones sobre la consulta y el DTO obtenido.
 * ¿QUÉ NO DEBE HACER? No conecta fuentes clínicas ni sustituye el motor de Domain.
 * RELACIÓN CON O.R.I.O.N.: Reglamento de Application, CU-01 y etapa C.
 */
final class ExtraerHechosUseCaseTest extends TestCase
{
    /** Conserva hechos y evidencias con una única consulta exacta; sin parámetros ni excepciones esperadas. */
    public function test_consulta_contexto_exacto_y_preserva_objetos_y_procedencia(): void
    {
        $solicitud = self::solicitud();
        $evidencia = F::evidencia(hallazgo: HallazgoInstrumentalEnum::DESCONOCIDO, cambiosHecho: ['valor' => null]);
        $contexto = F::hecho(['id' => 'SIM-CONTEXTO', 'origenId' => 'SIM-OR-CONTEXTO', 'valor' => false]);
        $memoria = F::memoria([$evidencia], [$contexto]);
        $caso = new ExtraerHechosUseCase($this->repositorio($solicitud, $memoria));

        $dto = $caso->ejecutar($solicitud);

        self::assertSame($solicitud, $dto->solicitud);
        self::assertSame($memoria, $dto->memoria);
        self::assertSame($evidencia, $dto->evidencias[$evidencia->hecho->id]);
        self::assertSame($contexto, $dto->hechos[$contexto->id]);
        self::assertNull($dto->evidencias[$evidencia->hecho->id]->hecho->valor);
        self::assertFalse($dto->hechos[$contexto->id]->valor);
        self::assertSame($evidencia->hecho->fecha, $dto->referenciasProcedencia[$evidencia->hecho->id]['fecha']);
        self::assertSame($evidencia->hecho->origenId, $dto->referenciasProcedencia[$evidencia->hecho->id]['origenId']);
        self::assertSame([], $dto->datosFaltantes);
        self::assertSame([], $dto->fuentesNoDisponibles);
    }

    /** Expresa una colección ausente sin inferir un resultado o disponibilidad; sin parámetros ni excepciones esperadas. */
    public function test_memoria_vacia_informa_ausencia_sin_fabricar_evidencias(): void
    {
        $solicitud = self::solicitud();
        $memoria = F::memoria();
        $caso = new ExtraerHechosUseCase($this->repositorio($solicitud, $memoria));

        $dto = $caso->ejecutar($solicitud);

        self::assertSame($memoria, $dto->memoria);
        self::assertSame([], $dto->hechos);
        self::assertSame([], $dto->evidencias);
        self::assertArrayHasKey('EVIDENCIAS_COGNITIVAS', $dto->datosFaltantes);
        self::assertNotSame('', $dto->datosFaltantes['EVIDENCIAS_COGNITIVAS']);
        self::assertSame([], $dto->fuentesNoDisponibles);
    }

    /**
     * Rechaza una respuesta coherente internamente que pertenece a otro contexto.
     *
     * @param  string  $campo  Dimensión alterada de la memoria retornada.
     * @param  string|DateTimeImmutable  $valor  Valor incompatible con la petición.
     *
     * @throws DomainException Rechazo esperado al construir el DTO de entrada.
     */
    #[DataProvider('respuestasAjenas')]
    public function test_rechaza_respuesta_ajena_o_con_corte_posterior(string $campo, string|DateTimeImmutable $valor): void
    {
        $solicitud = self::solicitud();
        $memoria = new MemoriaTrabajo(...array_replace([
            'evaluacionId' => 'SIM-EVAL', 'residenteId' => 'SINTETICO-RES',
            'versionConocimiento' => 'SIM-V1', 'fechaCorte' => F::fecha(),
        ], [$campo => $valor]));
        $caso = new ExtraerHechosUseCase($this->repositorio($solicitud, $memoria));

        $this->expectException(DomainException::class);
        $caso->ejecutar($solicitud);
    }

    /** @return iterable<string, array{string, string|DateTimeImmutable}> Respuestas ajenas; sin parámetros ni excepciones. */
    public static function respuestasAjenas(): iterable
    {
        yield 'evaluación' => ['evaluacionId', 'SIM-OTRA'];
        yield 'caso' => ['residenteId', 'SINTETICO-OTRO'];
        yield 'versión' => ['versionConocimiento', 'SIM-V2'];
        yield 'corte posterior' => ['fechaCorte', F::fecha()->modify('+1 second')];
    }

    /** @throws RuntimeException Error original exigido por el escenario, sin devolver una memoria vacía. */
    public function test_fallo_tecnico_se_propaga_sin_convertirse_en_ausencia(): void
    {
        $error = new RuntimeException('Fallo ficticio de recuperación.');
        $repositorio = $this->createMock(EvidenciaRepositoryInterface::class);
        $repositorio->expects(self::once())->method('paraEvaluacion')->willThrowException($error);
        $caso = new ExtraerHechosUseCase($repositorio);

        $this->expectExceptionObject($error);
        $caso->ejecutar(self::solicitud());
    }

    /** @throws DomainException Error original del repositorio, sin captura ni transformación. */
    public function test_incoherencia_detectada_por_el_repositorio_no_se_oculta(): void
    {
        $error = new DomainException('Fuente ficticia duplicada.');
        $repositorio = $this->createMock(EvidenciaRepositoryInterface::class);
        $repositorio->expects(self::once())->method('paraEvaluacion')->willThrowException($error);
        $caso = new ExtraerHechosUseCase($repositorio);

        $this->expectExceptionObject($error);
        $caso->ejecutar(self::solicitud());
    }

    /** Conserva hechos de contexto aunque no haya evidencia cognitiva; sin parámetros ni excepciones esperadas. */
    public function test_respuesta_sin_evidencia_cognitiva_conserva_hechos_de_contexto(): void
    {
        $solicitud = self::solicitud();
        $hecho = F::hecho(['valor' => 0]);
        $memoria = F::memoria([], [$hecho]);
        $caso = new ExtraerHechosUseCase($this->repositorio($solicitud, $memoria));

        $dto = $caso->ejecutar($solicitud);

        self::assertSame($hecho, $dto->hechos[$hecho->id]);
        self::assertSame(0, $dto->hechos[$hecho->id]->valor);
        self::assertSame([], $dto->evidencias);
        self::assertSame($hecho->fuenteId, $dto->referenciasProcedencia[$hecho->id]['fuenteId']);
        self::assertArrayHasKey('EVIDENCIAS_COGNITIVAS', $dto->datosFaltantes);
        self::assertSame([], $dto->fuentesNoDisponibles);
    }

    /** Aísla dos respuestas sucesivas del mismo repositorio; sin parámetros ni excepciones esperadas. */
    public function test_dos_solicitudes_consecutivas_no_comparten_hechos_ni_contexto(): void
    {
        $primera = self::solicitud();
        $segunda = new SolicitudInferenciaDTO('SIM-SOL-2', 'SIM-EVAL-2', 'SINTETICO-RES-2', F::fecha(), 'SIM-V2');
        $a = F::hecho(['id' => 'SIM-H-A']);
        $b = F::hecho(['id' => 'SIM-H-B', 'evaluacionId' => $segunda->evaluacionId,
            'residenteId' => $segunda->casoSinteticoId, 'versionConocimiento' => $segunda->versionConocimiento]);
        $memoriaA = F::memoria([], [$a]);
        $memoriaB = new MemoriaTrabajo($segunda->evaluacionId, $segunda->casoSinteticoId,
            $segunda->versionConocimiento, $segunda->fechaCorte, [$b]);
        $llamadas = [];
        $respuestas = [$memoriaA, $memoriaB];
        $repositorio = $this->createMock(EvidenciaRepositoryInterface::class);
        $repositorio->expects(self::exactly(2))->method('paraEvaluacion')->willReturnCallback(
            static function (string $evaluacionId, string $residenteId, string $version, DateTimeImmutable $corte) use (&$llamadas, &$respuestas): MemoriaTrabajo {
                $llamadas[] = [$evaluacionId, $residenteId, $version, $corte];

                return array_shift($respuestas);
            },
        );
        $caso = new ExtraerHechosUseCase($repositorio);

        $dtoA = $caso->ejecutar($primera);
        $dtoB = $caso->ejecutar($segunda);

        self::assertSame([
            [$primera->evaluacionId, $primera->casoSinteticoId, $primera->versionConocimiento, $primera->fechaCorte],
            [$segunda->evaluacionId, $segunda->casoSinteticoId, $segunda->versionConocimiento, $segunda->fechaCorte],
        ], $llamadas);
        self::assertSame($memoriaA, $dtoA->memoria);
        self::assertSame($memoriaB, $dtoB->memoria);
        self::assertSame([$a->id => $a], $dtoA->hechos);
        self::assertSame([$b->id => $b], $dtoB->hechos);
        self::assertArrayNotHasKey($a->id, $dtoB->hechos);
        self::assertArrayNotHasKey($b->id, $dtoA->hechos);
    }

    /** Un fallo previo no se transforma en caché de ausencia; sin parámetros, con primer error capturado explícitamente. */
    public function test_una_nueva_llamada_recupera_la_respuesta_tras_un_fallo_del_repositorio(): void
    {
        $solicitud = self::solicitud();
        $error = new RuntimeException('Fallo transitorio exclusivamente ficticio.');
        $evidencia = F::evidencia();
        $memoria = F::memoria([$evidencia]);
        $intentos = 0;
        $repositorio = $this->createMock(EvidenciaRepositoryInterface::class);
        $repositorio->expects(self::exactly(2))->method('paraEvaluacion')
            ->with($solicitud->evaluacionId, $solicitud->casoSinteticoId,
                $solicitud->versionConocimiento, self::identicalTo($solicitud->fechaCorte))
            ->willReturnCallback(static function () use (&$intentos, $error, $memoria): MemoriaTrabajo {
                if (++$intentos === 1) {
                    throw $error;
                }

                return $memoria;
            });
        $caso = new ExtraerHechosUseCase($repositorio);

        try {
            $caso->ejecutar($solicitud);
            self::fail('La primera llamada debe propagar el error original.');
        } catch (RuntimeException $recibido) {
            self::assertSame($error, $recibido);
        }
        // El reintento lo solicita la prueba: el caso de uso no lo ejecuta solo.
        $dto = $caso->ejecutar($solicitud);

        self::assertSame($memoria, $dto->memoria);
        self::assertSame($evidencia, $dto->evidencias[$evidencia->hecho->id]);
        self::assertSame([], $dto->datosFaltantes);
    }

    /**
     * Configura una respuesta real de Domain para una única llamada al repositorio.
     *
     * @param  SolicitudInferenciaDTO  $solicitud  Parámetros exactos que debe recibir el contrato.
     * @param  MemoriaTrabajo  $respuesta  Instantánea real del escenario.
     * @return EvidenciaRepositoryInterface Doble exclusivo de la fuente, sin Eloquent.
     *                                      Sin excepciones esperadas durante su configuración.
     */
    private function repositorio(SolicitudInferenciaDTO $solicitud, MemoriaTrabajo $respuesta): EvidenciaRepositoryInterface
    {
        $repositorio = $this->createMock(EvidenciaRepositoryInterface::class);
        $repositorio->expects(self::once())->method('paraEvaluacion')
            ->with($solicitud->evaluacionId, $solicitud->casoSinteticoId, $solicitud->versionConocimiento,
                self::identicalTo($solicitud->fechaCorte))
            ->willReturn($respuesta);

        return $repositorio;
    }

    /** @return SolicitudInferenciaDTO Petición ficticia; sin parámetros ni excepciones esperadas. */
    private static function solicitud(): SolicitudInferenciaDTO
    {
        return new SolicitudInferenciaDTO('SIM-SOL', 'SIM-EVAL', 'SINTETICO-RES', F::fecha(), 'SIM-V1');
    }
}
