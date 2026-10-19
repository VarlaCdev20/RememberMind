<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\UseCases;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\EvidenciasEntradaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\SolicitudInferenciaDTO;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\ExtraerHechosUseCase;
use App\Modulos\ApoyoDecision\Aplicacion\CasosDeUso\PrepararMemoriaTrabajoUseCase;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Repositories\Hechos\EvidenciaRepositoryInterface;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/**
 * TÍTULO: Preparación de memoria con los objetos recuperados.
 * CAPA: Application (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Comprueba aislamiento de peticiones y conservación de originales.
 * ¿QUÉ RECIBE? Solicitudes y DTOs de evidencia exclusivamente sintéticos.
 * ¿QUÉ HACE? Ejecuta CU-02 y recorre también la conexión real CU-01 → CU-02.
 * ¿QUÉ DEVUELVE? Aserciones sobre memoria, contexto y ausencia de mutaciones.
 * ¿QUÉ NO DEBE HACER? No evalúa compuertas, reglas o requisitos clínicos.
 * RELACIÓN CON O.R.I.O.N.: Reglamento de Application, CU-02 y etapa C.
 */
final class PrepararMemoriaTrabajoUseCaseTest extends TestCase
{
    /** Construye otra memoria conservando los originales; sin parámetros ni excepciones esperadas. */
    public function test_construye_memoria_nueva_con_hechos_y_evidencias_originales(): void
    {
        $solicitud = self::solicitud();
        $a = F::evidencia('SIM-A', cambiosHecho: ['episodioId' => 'SIM-EP-COMPARTIDO']);
        $b = F::evidencia('SIM-B', cambiosHecho: ['episodioId' => 'SIM-EP-COMPARTIDO']);
        $contexto = F::hecho(['id' => 'SIM-CONTEXTO', 'origenId' => 'SIM-OR-CONTEXTO', 'valor' => 0]);
        $original = F::memoria([$a, $b], [$contexto]);
        $entrada = new EvidenciasEntradaDTO($solicitud, $original);

        $memoria = (new PrepararMemoriaTrabajoUseCase)->ejecutar($solicitud, $entrada);

        self::assertNotSame($original, $memoria);
        self::assertSame($original->hechos, $memoria->hechos);
        self::assertSame($original->evidencias, $memoria->evidencias);
        self::assertSame($a, $memoria->evidencia($a->hecho->id));
        self::assertSame($b, $memoria->evidencia($b->hecho->id));
        self::assertSame($contexto, $memoria->hechos[$contexto->id]);
        self::assertSame(0, $memoria->hechos[$contexto->id]->valor);
        self::assertSame($solicitud->fechaCorte, $memoria->fechaCorte);
        self::assertSame($solicitud->versionConocimiento, $memoria->versionConocimiento);
        self::assertSame($original, $entrada->memoria);
        self::assertFalse($a->esIndependienteDe($b));
    }

    /** Conserva una entrada vacía sin generar resultados; sin parámetros ni excepciones esperadas. */
    public function test_ausencia_de_evidencias_no_produce_hechos_de_normalidad(): void
    {
        $solicitud = self::solicitud();
        $entrada = new EvidenciasEntradaDTO($solicitud, F::memoria(), ['SIM-DATO' => 'No informado.']);

        $memoria = (new PrepararMemoriaTrabajoUseCase)->ejecutar($solicitud, $entrada);

        self::assertSame([], $memoria->hechos);
        self::assertSame([], $memoria->evidencias);
        self::assertSame('SIM-EVAL', $memoria->evaluacionId);
        self::assertSame('SINTETICO-RES', $memoria->residenteId);
        self::assertSame(['SIM-DATO' => 'No informado.'], $entrada->datosFaltantes);
    }

    /**
     * Una entrada válida para otra petición no puede contaminar la memoria objetivo.
     *
     * @param  string  $campo  Dimensión alterada de la solicitud de entrada.
     * @param  string|DateTimeImmutable  $valor  Identidad o corte ajenos.
     *
     * @throws DomainException Rechazo esperado antes de construir la memoria objetivo.
     */
    #[DataProvider('solicitudesAjenas')]
    public function test_rechaza_entrada_de_otra_peticion(string $campo, string|DateTimeImmutable $valor): void
    {
        $otra = self::solicitud([$campo => $valor]);
        $memoriaAjena = new MemoriaTrabajo($otra->evaluacionId, $otra->casoSinteticoId,
            $otra->versionConocimiento, $otra->fechaCorte);
        $entrada = new EvidenciasEntradaDTO($otra, $memoriaAjena);

        $this->expectException(DomainException::class);
        (new PrepararMemoriaTrabajoUseCase)->ejecutar(self::solicitud(), $entrada);
    }

    /** @return iterable<string, array{string, string|DateTimeImmutable}> Peticiones ajenas; sin parámetros ni excepciones. */
    public static function solicitudesAjenas(): iterable
    {
        yield 'solicitud' => ['solicitudId', 'SIM-OTRA-SOL'];
        yield 'evaluación' => ['evaluacionId', 'SIM-OTRA-EVAL'];
        yield 'caso' => ['casoSinteticoId', 'SINTETICO-OTRO'];
        yield 'conocimiento' => ['versionConocimiento', 'SIM-V2'];
        yield 'corte' => ['fechaCorte', F::fecha()->modify('-1 second')];
    }

    /** Acepta una solicitud equivalente por valor, sin exigir el mismo objeto; sin parámetros ni excepciones esperadas. */
    public function test_acepta_solicitud_inmutable_equivalente(): void
    {
        $solicitud = self::solicitud();
        $entrada = new EvidenciasEntradaDTO(clone $solicitud, F::memoria());

        $memoria = (new PrepararMemoriaTrabajoUseCase)->ejecutar($solicitud, $entrada);

        self::assertSame($solicitud->fechaCorte, $memoria->fechaCorte);
        self::assertSame($solicitud->evaluacionId, $memoria->evaluacionId);
    }

    /** Conecta extracción y preparación con una sola consulta; sin parámetros ni excepciones esperadas. */
    public function test_extraccion_y_preparacion_comparten_hechos_sin_reconsultar(): void
    {
        $solicitud = self::solicitud();
        $e = F::evidencia();
        $original = F::memoria([$e]);
        $repositorio = $this->createMock(EvidenciaRepositoryInterface::class);
        $repositorio->expects(self::once())->method('paraEvaluacion')->willReturn($original);

        $entrada = (new ExtraerHechosUseCase($repositorio))->ejecutar($solicitud);
        $memoria = (new PrepararMemoriaTrabajoUseCase)->ejecutar($solicitud, $entrada);

        self::assertSame($original->hechos[$e->hecho->id], $memoria->hechos[$e->hecho->id]);
        self::assertSame($e, $memoria->evidencia($e->hecho->id));
    }

    /** Repetir la preparación no acumula evidencias ni reconsulta; sin parámetros ni excepciones esperadas. */
    public function test_preparar_dos_veces_la_misma_entrada_conserva_originales_sin_duplicarlos(): void
    {
        $solicitud = self::solicitud();
        $evidencia = F::evidencia();
        $original = F::memoria([$evidencia]);
        $repositorio = $this->createMock(EvidenciaRepositoryInterface::class);
        $repositorio->expects(self::once())->method('paraEvaluacion')->willReturn($original);
        $entrada = (new ExtraerHechosUseCase($repositorio))->ejecutar($solicitud);
        $preparar = new PrepararMemoriaTrabajoUseCase;

        $primera = $preparar->ejecutar($solicitud, $entrada);
        $segunda = $preparar->ejecutar($solicitud, $entrada);

        self::assertNotSame($primera, $segunda);
        self::assertNotSame($original, $primera);
        self::assertNotSame($original, $segunda);
        self::assertSame($original->hechos, $primera->hechos);
        self::assertSame($original->hechos, $segunda->hechos);
        self::assertSame($original->evidencias, $primera->evidencias);
        self::assertSame($original->evidencias, $segunda->evidencias);
        self::assertCount(1, $segunda->evidencias);
        self::assertSame($original, $entrada->memoria);
    }

    /** Conserva una evidencia en el corte aunque la fuente use UTC; sin parámetros ni excepciones esperadas. */
    public function test_preparacion_preserva_fecha_original_y_corte_equivalente_en_otra_zona(): void
    {
        $solicitud = self::solicitud();
        $instanteUtc = new DateTimeImmutable('2026-10-17T16:00:00+00:00');
        $evidencia = F::evidencia(cambiosHecho: ['fecha' => $instanteUtc]);
        $original = new MemoriaTrabajo('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1',
            $instanteUtc, [$evidencia->hecho], [$evidencia]);
        $repositorio = $this->createMock(EvidenciaRepositoryInterface::class);
        $repositorio->expects(self::once())->method('paraEvaluacion')
            ->with('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', self::identicalTo($solicitud->fechaCorte))
            ->willReturn($original);

        $entrada = (new ExtraerHechosUseCase($repositorio))->ejecutar($solicitud);
        $memoria = (new PrepararMemoriaTrabajoUseCase)->ejecutar($solicitud, $entrada);

        self::assertSame($solicitud->fechaCorte, $memoria->fechaCorte);
        self::assertSame($instanteUtc, $memoria->hechos[$evidencia->hecho->id]->fecha);
        self::assertSame($evidencia, $memoria->evidencia($evidencia->hecho->id));
        self::assertSame($instanteUtc, $entrada->referenciasProcedencia[$evidencia->hecho->id]['fecha']);
    }

    /**
     * Construye una solicitud ficticia con cambios explícitos de contexto.
     *
     * @param  array<string, mixed>  $cambios  Parámetros nombrados a sustituir.
     * @return SolicitudInferenciaDTO Solicitud real del escenario.
     *                                Sin excepciones esperadas con los valores de los proveedores.
     */
    private static function solicitud(array $cambios = []): SolicitudInferenciaDTO
    {
        return new SolicitudInferenciaDTO(...array_replace([
            'solicitudId' => 'SIM-SOL', 'evaluacionId' => 'SIM-EVAL', 'casoSinteticoId' => 'SINTETICO-RES',
            'fechaCorte' => F::fecha(), 'versionConocimiento' => 'SIM-V1',
        ], $cambios));
    }
}
