<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\UseCases;

use App\SistemaExperto\Application\DTOs\InformeExpertoDTO;
use App\SistemaExperto\Application\DTOs\SolicitudInferenciaDTO;
use App\SistemaExperto\Application\UseCases\AnalizarCasoExperimentalUseCase;
use App\SistemaExperto\Application\UseCases\ClasificarDclUseCase;
use App\SistemaExperto\Application\UseCases\EjecutarInferenciaUseCase;
use App\SistemaExperto\Application\UseCases\ExtraerHechosUseCase;
use App\SistemaExperto\Application\UseCases\GenerarInformeUseCase;
use App\SistemaExperto\Application\UseCases\PrepararMemoriaTrabajoUseCase;
use App\SistemaExperto\Domain\Entities\Conocimiento\RedSemantica;
use App\SistemaExperto\Domain\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use App\SistemaExperto\Domain\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use App\SistemaExperto\Domain\Repositories\Hechos\EvidenciaRepositoryInterface;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum as C;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoDiferencialEnum as D;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;
use Tests\Unit\SistemaExperto\Domain\Services\Inferencia\EscenarioInferencia;

/**
 * TÍTULO: Recorrido real de los cinco casos con puertos de repositorio simulados.
 * CAPA: Application (pruebas).
 * ¿PARA QUÉ SIRVE? Verifica orquestación, identidad, informe automático y propagación de fallos.
 * ¿QUÉ RECIBE? Casos sintéticos y mocks únicamente en los puertos de persistencia.
 * ¿QUÉ HACE? Ejecuta todos los servicios Domain reales, sin simular inferencia ni clasificación.
 * ¿QUÉ DEVUELVE? Aserciones de resultados y ausencia de ejecución posterior a un fallo.
 * ¿QUÉ NO DEBE HACER? No confunde mocks con prueba SQL ni activa uso clínico.
 * RELACIÓN CON O.R.I.O.N.: CU-01..CU-06 y R1–R10.
 */
final class AnalizarCasoExperimentalUseCaseTest extends TestCase
{
    /** @return iterable<string, array{array, string}> Distribuciones sintéticas independientes para los cuatro patrones. */
    public static function patrones(): iterable
    {
        yield 'amnésico único' => [['A', 'P', 'P', 'P', 'P'], 'PET-01'];
        yield 'amnésico múltiple' => [['A', 'A', 'P', 'P', 'P'], 'PET-02'];
        yield 'no amnésico único' => [['P', 'A', 'P', 'P', 'P'], 'PET-03'];
        yield 'no amnésico múltiple' => [['P', 'A', 'P', 'A', 'P'], 'PET-04'];
    }

    /** @param array $estados Referencias explícitas. @param string $esperado Patrón esperado del contrato. */
    #[DataProvider('patrones')]
    public function test_cinco_pasos_conservan_originales_y_generan_informe_automatico(array $estados, string $esperado): void
    {
        $e = EscenarioInferencia::completo($estados);
        $orden = [];
        $evidencias = $this->createMock(EvidenciaRepositoryInterface::class);
        $evidencias->expects(self::once())->method('paraEvaluacion')->with('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', E::fecha())
            ->willReturnCallback(static function () use ($e, &$orden) {
                $orden[] = 'extraer';

                return $e['memoria'];
            });
        $reglas = $this->createMock(ReglaExpertaRepositoryInterface::class);
        $reglas->expects(self::once())->method('porVersion')->with('SIM-V1')
            ->willReturnCallback(static function () use (&$orden): array {
                $orden[] = 'reglas';

                return [];
            });
        $red = $this->createMock(RedSemanticaRepositoryInterface::class);
        $red->expects(self::once())->method('porVersion')->with('SIM-V1')
            ->willReturnCallback(static function () use (&$orden): RedSemantica {
                $orden[] = 'red';

                return new RedSemantica('SIM-V1', [], []);
            });
        $coordinador = $this->coordinador($evidencias, $reglas, $red);
        $informe = $coordinador->ejecutar(self::solicitud(), $e['entradaDcl'], [], procedencias: $e['procedencias'], contexto: $e['contexto']);

        self::assertSame(['extraer', 'reglas', 'red'], $orden);
        self::assertSame($esperado, $informe->dcl->patronCandidato->value);
        self::assertSame($informe->inferencia->perfilCognitivo, $informe->dcl->resultado->perfil->cognicion);
        foreach ($e['memoria']->hechos as $id => $hecho) {
            self::assertSame($hecho, $informe->inferencia->perfilCognitivo->memoria->hechos[$id]);
        }
        self::assertStringContainsString($esperado, $informe->interpretacion);
        self::assertStringContainsString('DCL-C03=ACREDITADO', $informe->fundamento);
        self::assertStringContainsString('episodio=', $informe->fundamento);
        self::assertStringContainsString('COMPUERTA:COG-MEM', $informe->fundamento);
        self::assertSame(InformeExpertoDTO::ADVERTENCIA_EXPERIMENTAL, $informe->advertencia);
    }

    /** Una memoria sin datos atraviesa el flujo como desconocida y produce abstención explícita. */
    public function test_falta_de_evidencias_y_requisitos_no_se_convierte_en_normalidad(): void
    {
        $evidencias = $this->createMock(EvidenciaRepositoryInterface::class);
        $evidencias->expects(self::once())->method('paraEvaluacion')->willReturn(E::memoria());
        $reglas = $this->createMock(ReglaExpertaRepositoryInterface::class);
        $reglas->expects(self::once())->method('porVersion')->willReturn([]);
        $red = $this->createMock(RedSemanticaRepositoryInterface::class);
        $red->expects(self::once())->method('porVersion')->willReturn(new RedSemantica('SIM-V1', [], []));
        $criterios = $razones = [];
        for ($i = 1; $i <= 5; $i++) {
            $criterios['DCL-C0'.$i] = C::PENDIENTE;
            $razones['DCL-C0'.$i] = 'No hay revisión original.';
        }
        $informe = $this->coordinador($evidencias, $reglas, $red)->ejecutar(self::solicitud(), [
            'criterios' => $criterios, 'soportes' => [], 'justificaciones' => $razones, 'diferencial' => D::PENDIENTE,
            'independenciaRelativa' => C::PENDIENTE, 'evidenciasIadl' => [], 'revisorSinteticoId' => null,
            'justificacionFuncional' => 'Sin revisión IADL.'], []);
        self::assertTrue($informe->dcl->abstencion);
        self::assertSame('ABSTENCION_INFORMACION_INSUFICIENTE', $informe->dcl->motivos[0]->value);
        foreach ($informe->inferencia->dominios as $r) {
            self::assertSame('U', $r->estado->value);
        }
    }

    /** Un fallo al extraer detiene el flujo antes de consultar conocimiento. */
    public function test_fallo_del_repositorio_no_se_oculta_y_no_ejecuta_etapas_posteriores(): void
    {
        $e = EscenarioInferencia::completo();
        $evidencias = $this->createMock(EvidenciaRepositoryInterface::class);
        $evidencias->expects(self::once())->method('paraEvaluacion')->willThrowException(new RuntimeException('Fallo sintético de almacenamiento'));
        $reglas = $this->createMock(ReglaExpertaRepositoryInterface::class);
        $reglas->expects(self::never())->method('porVersion');
        $red = $this->createMock(RedSemanticaRepositoryInterface::class);
        $red->expects(self::never())->method('porVersion');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fallo sintético');
        $this->coordinador($evidencias, $reglas, $red)->ejecutar(self::solicitud(), $e['entradaDcl'], []);
    }

    /** Una versión sin red no se reemplaza por conocimiento de otra versión. */
    public function test_version_sin_red_falla_explicitamente(): void
    {
        $reglas = $this->createMock(ReglaExpertaRepositoryInterface::class);
        $reglas->expects(self::once())->method('porVersion')->willReturn([]);
        $red = $this->createMock(RedSemanticaRepositoryInterface::class);
        $red->expects(self::once())->method('porVersion')->willReturn(null);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No existe una red');
        (new EjecutarInferenciaUseCase($reglas, $red))->ejecutar(self::solicitud(), E::memoria(), []);
    }

    /** El constructor real de Domain rechaza acreditar un requisito sin su soporte. */
    public function test_clasificar_no_inventa_soportes_para_requisitos_acreditados(): void
    {
        $e = EscenarioInferencia::completo();
        $reglas = $this->createMock(ReglaExpertaRepositoryInterface::class);
        $reglas->expects(self::once())->method('porVersion')->willReturn([]);
        $red = $this->createMock(RedSemanticaRepositoryInterface::class);
        $red->expects(self::once())->method('porVersion')->willReturn(new RedSemantica('SIM-V1', [], []));
        $inferencia = (new EjecutarInferenciaUseCase($reglas, $red))->ejecutar(self::solicitud(), $e['memoria'], [],
            procedencias: $e['procedencias'], contexto: $e['contexto']);
        $entrada = array_replace($e['entradaDcl'], ['soportes' => []]);
        $this->expectException(DomainException::class);
        (new ClasificarDclUseCase)->ejecutar(self::solicitud(), $inferencia, ...$entrada);
    }

    /** @return SolicitudInferenciaDTO Petición fija y sintética, sin reloj ni acceso externo. */
    private static function solicitud(): SolicitudInferenciaDTO
    {
        return new SolicitudInferenciaDTO('SIM-SOLICITUD', 'SIM-EVAL', 'SINTETICO-RES', E::fecha(), 'SIM-V1');
    }

    /** @param EvidenciaRepositoryInterface $evidencias Puerto simulado. @param ReglaExpertaRepositoryInterface $reglas Puerto simulado. @param RedSemanticaRepositoryInterface $red Puerto simulado. @return AnalizarCasoExperimentalUseCase Flujo real sin sustitutos del Domain. */
    private function coordinador(EvidenciaRepositoryInterface $evidencias, ReglaExpertaRepositoryInterface $reglas,
        RedSemanticaRepositoryInterface $red): AnalizarCasoExperimentalUseCase
    {
        return new AnalizarCasoExperimentalUseCase(new ExtraerHechosUseCase($evidencias), new PrepararMemoriaTrabajoUseCase,
            new EjecutarInferenciaUseCase($reglas, $red), new ClasificarDclUseCase, new GenerarInformeUseCase);
    }
}
