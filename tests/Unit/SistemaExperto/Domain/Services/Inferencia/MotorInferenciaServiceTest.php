<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Inferencia;

use App\SistemaExperto\Domain\Entities\Conocimiento\NodoSemantico;
use App\SistemaExperto\Domain\Entities\Conocimiento\RedSemantica;
use App\SistemaExperto\Domain\Entities\Conocimiento\ReglaExperta;
use App\SistemaExperto\Domain\Entities\Conocimiento\RelacionSemantica;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\Services\Inferencia\MotorInferenciaService;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum as D;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EstadoDominioEnum as S;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EvaluabilidadEnum;
use App\SistemaExperto\Domain\ValueObjects\Conocimiento\EstadoReglaEnum as R;
use App\SistemaExperto\Domain\ValueObjects\Conocimiento\OperadorCondicionEnum as O;
use App\SistemaExperto\Domain\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum as T;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

/**
 * TÍTULO: Integración real de compuertas, semántica y reglas.
 * CAPA: Domain (pruebas).
 * SUBSISTEMA: Inferencia.
 * RESPONSABILIDAD: Probar perfil completo, ausencia, conflicto y separación G09/A-P.
 * ENTRADAS: Memorias y grafo ficticios; colaboradores reales sin mocks.
 * SALIDAS: Cinco dominios, trazas y exclusiones conservadas.
 * RESTRICCIONES: No activa uso clínico ni genera datos institucionales.
 * TRAZABILIDAD: R1–R10 y puente de referencias DCL experimental.
 */
final class MotorInferenciaServiceTest extends TestCase
{
    /** Referencias completas y contexto acreditado permiten A/P sin exigir basal previa. */
    public function test_integra_cinco_dominios_con_soportes_positivos_independientes(): void
    {
        $e = EscenarioInferencia::completo();
        $salida = (new MotorInferenciaService)->inferir($e['memoria'], [], new RedSemantica('SIM-V1', [], []), [],
            procedencias: $e['procedencias'], contexto: $e['contexto']);
        self::assertSame(['A', 'P', 'P', 'P', 'P'], array_map(static fn ($r): string => $r->estado->value, array_values($salida['perfil']->resultados)));
        self::assertSame($e['memoria'], $salida['perfil']->memoria);
        foreach ($salida['perfil']->resultados as $resultado) {
            self::assertSame(EvaluabilidadEnum::EVALUACION_INTEGRADA, $resultado->evaluabilidad);
            self::assertNotEmpty($resultado->soportesPositivos());
        }
        self::assertCount(19, array_merge(...array_map(static fn ($r): array => array_values($r->subcomponentes), array_values($salida['perfil']->resultados))));
    }

    /** @return iterable<string, array{array, string}> Casos que conservan I y X aunque el verificador los excluya. */
    public static function conflictos(): iterable
    {
        yield 'contradicción' => [['X', 'P', 'P', 'P', 'P'], 'X'];
        yield 'no interpretable' => [['I', 'P', 'P', 'P', 'P'], 'I'];
    }

    /** @param array $estados Referencias del caso. @param string $esperado Estado que debe conservarse. */
    #[DataProvider('conflictos')]
    public function test_preserva_evidencias_excluidas_de_contradiccion_y_no_interpretabilidad(array $estados, string $esperado): void
    {
        $e = EscenarioInferencia::completo($estados);
        $s = (new MotorInferenciaService)->inferir($e['memoria'], [], new RedSemantica('SIM-V1', [], []), [],
            procedencias: $e['procedencias'], contexto: $e['contexto']);
        self::assertSame($esperado, $s['perfil']->resultado(D::MEMORIA)->estado->value);
        self::assertNotEmpty($s['perfil']->resultado(D::MEMORIA)->evidencias);
        if ($esperado === 'I') {
            self::assertContains('EVIDENCIA_NO_INTERPRETABLE', array_values($s['exclusiones']));
        }
    }

    /** Sin procedencia las referencias permanecen en memoria, sin autorizar A/P. */
    public function test_procedencia_ausente_no_acredita_una_referencia(): void
    {
        $e = EscenarioInferencia::completo();
        $s = (new MotorInferenciaService)->inferir($e['memoria'], [], new RedSemantica('SIM-V1', [], []), [], contexto: $e['contexto']);
        foreach ($s['perfil']->resultados as $r) {
            self::assertSame(S::DESCONOCIDO, $r->estado);
        }
        self::assertCount(5, $s['exclusiones']);
    }

    /** Una consecuencia literal A de regla cumplida no se convierte en referencia externa de dominio. */
    public function test_regla_cumplida_tiene_traza_pero_no_promueve_salida_descriptiva_a_estado_a(): void
    {
        [$m, $regla, $red, $contratos, $p, $relacion] = self::casoRegla();
        $s = (new MotorInferenciaService)->inferir($m, [$regla], $red, [$regla->id => D::MEMORIA],
            $contratos, [$relacion->id => true], [$p]);
        self::assertSame(R::CUMPLE, $s['evaluacionesReglas'][$regla->id]['estado']);
        self::assertSame(S::DESCONOCIDO, $s['perfil']->resultado(D::MEMORIA)->estado);
        self::assertSame([$regla], $s['reglasPorDominio'][D::MEMORIA->value]);
        self::assertSame([$relacion], $s['relacionesUtilizadas']);
        self::assertStringContainsString('SIM-VAR', $s['trazas']['REGLA:'.$regla->id]);
        self::assertStringContainsString('episodio=', $s['trazas']['REGLA:'.$regla->id]);
        self::assertStringContainsString('no acredita A/P', $s['trazas']['CONSECUENCIA:'.$regla->id]);
        self::assertCount(2, $m->hechos, 'No se incorpora la consecuencia a la memoria.');
    }

    /** La participación ausente no se habilita por existir una arista en el grafo. */
    public function test_arista_sin_habilitacion_bloquea_regla_sin_evaluar_condiciones(): void
    {
        [$m, $regla, $red, $contratos, $p] = self::casoRegla();
        $s = (new MotorInferenciaService)->inferir($m, [$regla], $red, [$regla->id => D::MEMORIA], $contratos, [], [$p]);
        self::assertSame(R::BLOQUEADA, $s['evaluacionesReglas'][$regla->id]['estado']);
        self::assertNull($s['evaluacionesReglas'][$regla->id]['condiciones']);
        self::assertSame([], $s['reglasPorDominio'][D::MEMORIA->value]);
    }

    /** Una entrada vacía produce cinco U explícitos; no cinco P. */
    public function test_memoria_vacia_conserva_desconocimiento_en_todos_los_dominios(): void
    {
        $s = (new MotorInferenciaService)->inferir(E::memoria(), [], new RedSemantica('SIM-V1', [], []), []);
        self::assertCount(5, $s['perfil']->resultados);
        foreach ($s['perfil']->resultados as $r) {
            self::assertSame(S::DESCONOCIDO, $r->estado);
            self::assertSame(EvaluabilidadEnum::NO_EVALUABLE, $r->evaluabilidad);
        }
    }

    /** Reordenar declaraciones no cambia perfil ni trazas. */
    public function test_ejecucion_es_determinista_sin_mutar_las_fuentes(): void
    {
        $e = EscenarioInferencia::completo();
        $motor = new MotorInferenciaService;
        $red = new RedSemantica('SIM-V1', [], []);
        $a = $motor->inferir($e['memoria'], [], $red, [], procedencias: $e['procedencias'], contexto: $e['contexto']);
        $b = $motor->inferir($e['memoria'], [], $red, [], procedencias: array_reverse($e['procedencias']), contexto: array_reverse($e['contexto']));
        self::assertSame($a['trazas'], $b['trazas']);
        self::assertEquals($a['perfil'], $b['perfil']);
    }

    /** El punto de entrada de Domain rechaza identidades de residentes reales. */
    public function test_no_admite_ejecucion_asistencial(): void
    {
        $m = new MemoriaTrabajo('EVAL-REAL', 'RES-REAL', 'SIM-V1', E::fecha());
        $this->expectException(DomainException::class);
        (new MotorInferenciaService)->inferir($m, [], new RedSemantica('SIM-V1', [], []), []);
    }

    /** @return array Memoria y regla sintéticas con relación explícita, sin baremos ni interpretación instrumental. */
    public static function casoRegla(): array
    {
        $e = E::evidencia(cambiosHecho: ['concepto' => 'SIM-VAR', 'valor' => true]);
        $p = E::hecho(['id' => 'PROC', 'origenId' => 'PROC', 'concepto' => 'VERIFICACION_PROCEDENCIA:'.$e->hecho->id, 'valor' => true]);
        $m = E::memoria([$e], [$p]);
        $r = new ReglaExperta('SIM-R1', 'SIM-V1', ['operador' => O::IGUAL_A, 'variable' => 'SIM-VAR', 'valor' => true],
            ['concepto' => 'SIM-CRITERIO', 'valor' => 'A'], true, 'SIM-FUENTE');
        $variable = new NodoSemantico('N-V', 'SIM-V1', 'SIM-VAR', 'Variable ficticia', 'VARIABLE', 'Dato de prueba');
        $criterio = new NodoSemantico('N-C', 'SIM-V1', 'SIM-CRITERIO', 'Criterio ficticio', 'CRITERIO', 'Salida descriptiva');
        $relacion = new RelacionSemantica('REL', $variable, T::EVIDENCIA_DE, $criterio);
        $red = new RedSemantica('SIM-V1', [$variable, $criterio], [$relacion]);
        $contratos = [['versionConocimiento' => 'SIM-V1', 'tipo' => T::EVIDENCIA_DE,
            'tipoOrigen' => 'VARIABLE', 'tipoDestino' => 'CRITERIO', 'habilitaParticipacion' => true]];

        return [$m, $r, $red, $contratos, $p, $relacion];
    }
}
