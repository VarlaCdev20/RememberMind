<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarInformeCognitivo;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarPatronExperimental;
use App\SistemaExperto\Application\DTOs\InformeExpertoDTO;
use App\SistemaExperto\Domain\Services\Dcl\ClasificadorPetersenService;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum as C;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoDiferencialEnum as D;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

/** Casos hipotéticos con objetos y clasificador reales, sin fuentes ni aprobaciones clínicas. */
final class PresentacionNeurocognitivaTest extends TestCase
{
    public function test_revision_ausente_o_parcial_no_produce_offsets_nulos_ni_inventa_firmas(): void
    {
        $p = new PresentarInformeCognitivo;
        self::assertSame('Pendiente de una valoración disponible', $p->resumirRevision(null, false, false));
        self::assertSame('No habilitada para esta valoración', $p->resumirRevision(null, true, false));
        self::assertSame('Resultado conservado', $p->resumirRevision(null, true, true));
        self::assertSame('Psicología: estado no disponible. Medicina: estado no disponible.',
            $p->resumirRevision(['PSICOLOGIA' => null, 'MEDICINA' => null], true, false));
        self::assertSame('Psicología: confirmada. Medicina: estado no disponible.',
            $p->resumirRevision(['PSICOLOGIA' => ['estado' => 'CONFIRMADA']], true, false));
        self::assertSame('Psicología: estado no disponible. Medicina: estado no disponible.',
            $p->resumirRevision(['PSICOLOGIA' => ['estado' => 'INVALIDO']], true, false));
    }

    public function test_caso_completo_explica_cinco_dominios_y_patron_sin_codigos_medicos(): void
    {
        $resultado = (new ClasificadorPetersenService)->clasificar(E::perfil(['A', 'A', 'P', 'P', 'P']));
        $antes = serialize($resultado);
        $p = new PresentarPatronExperimental;
        $n = $p->presentar($resultado);
        self::assertSame($n, $p->presentar($resultado));
        self::assertSame($antes, serialize($resultado));
        self::assertCount(5, $n['funciones']);
        self::assertSame([], $n['faltantes']);
        self::assertTrue($n['completa']);
        self::assertStringContainsString('memoria, atención', $n['conclusion']);
        self::assertStringContainsString('amnésico multidominio', $n['resumen']);
        self::assertStringContainsString('autonomía relativamente conservada', $n['autonomia']['resumen']);
        self::assertSame(InformeExpertoDTO::ADVERTENCIA_EXPERIMENTAL, $n['advertencia']);
        self::assertFalse($n['publicable_clinicamente']);
        self::assertNotEmpty($n['auditoria']);
        self::assertStringNotContainsString('PET-02', $n['resumen']);
        self::assertStringNotContainsString('SIM-', $n['conclusion']);
    }

    public static function incompletos(): array
    {
        return [
            'sin memoria' => [['U', 'P', 'P', 'P', 'P'], [], D::COMPLETADO_SIN_IMPEDIMENTO, C::ACREDITADO, 'evaluación específica interpretable de memoria'],
            'sin autonomia' => [['A', 'P', 'P', 'P', 'P'], ['DCL-C03' => C::PENDIENTE], D::COMPLETADO_SIN_IMPEDIMENTO, C::PENDIENTE, 'No se asume independencia'],
            'contradiccion' => [['X', 'P', 'P', 'P', 'P'], [], D::COMPLETADO_SIN_IMPEDIMENTO, C::ACREDITADO, 'conclusiones diferentes'],
            'contradiccion funcional' => [['A', 'P', 'P', 'P', 'P'], ['DCL-C03' => C::PENDIENTE], D::COMPLETADO_SIN_IMPEDIMENTO, C::CONTRADICTORIO, 'No se asume independencia'],
            'factores medicos' => [['A', 'P', 'P', 'P', 'P'], ['DCL-C04' => C::NO_ACREDITADO], D::IMPEDIMENTO_NO_RESUELTO, C::ACREDITADO, 'Debe resolverse antes'],
            'sin registros interpretables' => [['U', 'U', 'U', 'U', 'U'], ['DCL-C01' => C::PENDIENTE, 'DCL-C03' => C::PENDIENTE, 'DCL-C04' => C::PENDIENTE, 'DCL-C05' => C::PENDIENTE], D::PENDIENTE, C::PENDIENTE, 'Falta una evaluación'],
            'no interpretable' => [['I', 'P', 'P', 'P', 'P'], [], D::COMPLETADO_SIN_IMPEDIMENTO, C::ACREDITADO, 'no es interpretable'],
            'requisito incoherente' => [['P', 'P', 'P', 'P', 'P'], [], D::COMPLETADO_SIN_IMPEDIMENTO, C::ACREDITADO, 'no concuerda'],
        ];
    }

    #[DataProvider('incompletos')]
    public function test_abstenciones_conservan_razones_y_no_inventan_un_patron(array $estados, array $criterios, D $d, C $independencia, string $esperado): void
    {
        $resultado = (new ClasificadorPetersenService)->clasificar(E::perfil($estados, $criterios, $d, $independencia));
        $n = (new PresentarPatronExperimental)->presentar($resultado);
        self::assertNull($resultado->patronCandidato);
        self::assertSame($estados === ['P', 'P', 'P', 'P', 'P'], $n['completa']);
        self::assertFalse($n['publicable_clinicamente']);
        self::assertNotEmpty($n['faltantes']);
        self::assertStringContainsString($esperado, json_encode([$n['faltantes'], $n['funciones']], JSON_UNESCAPED_UNICODE));
        self::assertStringNotContainsString('patrón candidato de DCL', $n['resumen']);
        self::assertStringNotContainsString('función normal', json_encode($n, JSON_UNESCAPED_UNICODE));
        self::assertSame(InformeExpertoDTO::ADVERTENCIA_EXPERIMENTAL, $n['advertencia']);
        if ($independencia === C::CONTRADICTORIO) {
            self::assertTrue($n['contradiccion']);
            self::assertStringContainsString('discrepancias', $n['conclusion']);
        }
    }

    public function test_cinco_dominios_sin_alteracion_y_c02_no_acreditado_no_equivalen_a_falta_de_datos(): void
    {
        $r = (new ClasificadorPetersenService)->clasificar(E::perfil(['P', 'P', 'P', 'P', 'P'], ['DCL-C02' => C::NO_ACREDITADO]));
        $n = (new PresentarPatronExperimental)->presentar($r);
        self::assertTrue($n['completa']);
        self::assertNull($r->patronCandidato);
        self::assertStringContainsString('No se trata de ausencia de datos', $n['resumen']);
        self::assertStringNotContainsString('Falta acreditar', json_encode($n['faltantes'], JSON_UNESCAPED_UNICODE));
        self::assertStringNotContainsString('fuentes que faltan', $n['significado']);
    }

    public function test_no_interpretable_conserva_el_motivo_y_fecha_legibles_de_la_fuente(): void
    {
        $r = (new ClasificadorPetersenService)->clasificar(E::perfil(['I', 'P', 'P', 'P', 'P'],
            razonesEvidencia: ['COG-MEM' => 'La aplicación se interrumpió por fatiga.']));
        $n = (new PresentarPatronExperimental)->presentar($r);
        self::assertStringContainsString('La aplicación se interrumpió por fatiga.', $n['fundamentos'][0]['fuentes'][0]);
        self::assertStringContainsString('Fuente de memoria del 17/10/2026', $n['faltantes'][0]['texto']);
        self::assertStringNotContainsString('SIM-EV', $n['faltantes'][0]['texto']);
    }
}
