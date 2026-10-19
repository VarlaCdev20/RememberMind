<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Explicacion;

use MotorExperto\dominio\orion\Entities\Cognicion\ResultadoDominio;
use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Services\Dcl\ClasificadorPetersenService;
use MotorExperto\dominio\orion\Services\Explicacion\GeneradorExplicacionService;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum as D;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum as S;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EvaluabilidadEnum as V;
use DomainException;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as E;

/**
 * TÍTULO: Explicación reconstruible desde los resultados originales.
 * CAPA: Domain (pruebas).
 * SUBSISTEMA: Explicación.
 * RESPONSABILIDAD: Verificar procedencia, abstenciones y frontera sintética directa.
 * ENTRADAS: Perfiles ficticios reales de Domain, sin servicios simulados.
 * SALIDAS: Tres niveles reproducibles o rechazo de entradas incompatibles.
 * RESTRICCIONES: No acredita validación profesional ni genera formatos de presentación.
 * TRAZABILIDAD: R1/R3/R4/R7/R9; Documento 04 y contrato DCL-PET.
 */
final class GeneradorExplicacionServiceTest extends TestCase
{
    /** Preserva exclusiones, fuentes, referencias SIM y razones de abstención. */
    public function test_explicacion_conserva_tres_niveles_y_antecedentes_incluso_excluidos(): void
    {
        $p = E::perfil(['X', 'P', 'P', 'P', 'P']);
        $dcl = (new ClasificadorPetersenService)->clasificar($p);
        $generador = new GeneradorExplicacionService;
        $reglas = array_fill_keys(array_keys($p->cognicion->resultados), []);
        $a = $generador->generar($p->cognicion, $reglas, ['SIM-TRAZA' => 'Condición desconocida: valor=null'],
            ['SIM-AVISO' => 'Revisión pendiente'], $dcl, [], ['SIM-EXCLUIDA' => 'EVIDENCIA_NO_INTERPRETABLE']);
        self::assertSame(['resumen', 'interpretacion', 'fundamento'], array_keys($a));
        self::assertStringContainsString('COG-MEM=X', $a['resumen']);
        self::assertStringContainsString('ABSTENCION_CONTRADICCION', $a['interpretacion']);
        foreach (['episodio=', 'fecha=', 'versión=SIM-V1', 'SIM-NORMA', 'SIM-EXCLUIDA', 'DCL-C03', 'IADL', 'valor=null'] as $texto) {
            self::assertStringContainsString($texto, $a['fundamento']);
        }
        self::assertSame($a, $generador->generar($p->cognicion, $reglas, ['SIM-TRAZA' => 'Condición desconocida: valor=null'],
            ['SIM-AVISO' => 'Revisión pendiente'], $dcl, [], ['SIM-EXCLUIDA' => 'EVIDENCIA_NO_INTERPRETABLE']));
    }

    /** Invocación directa de Domain: nunca rotula datos reales como una simulación. */
    public function test_rechaza_memoria_real_aunque_todos_los_dominios_sean_desconocidos(): void
    {
        $m = new MemoriaTrabajo('EVAL-REAL', 'RES-REAL', 'V1', E::fecha());
        $resultados = [];
        foreach (D::cases() as $d) {
            $resultados[] = new ResultadoDominio($d, S::DESCONOCIDO, V::NO_EVALUABLE, $m, [], [], 'Sin datos.');
        }
        $v = new VectorCognitivo($resultados);
        $this->expectException(DomainException::class);
        (new GeneradorExplicacionService)->generar($v, array_fill_keys(array_keys($v->resultados), []), []);
    }

    /** Dos perfiles equivalentes pero distintos no pueden mezclarse en una explicación. */
    public function test_rechaza_dcl_que_no_utiliza_el_perfil_original(): void
    {
        $p = E::perfil();
        $otro = E::perfil();
        $this->expectException(DomainException::class);
        (new GeneradorExplicacionService)->generar($p->cognicion, array_fill_keys(array_keys($p->cognicion->resultados), []), [],
            dcl: (new ClasificadorPetersenService)->clasificar($otro));
    }
}
