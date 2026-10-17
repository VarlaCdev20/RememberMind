<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Entities\Funcionalidad;

use App\SistemaExperto\Domain\Entities\Funcionalidad\VectorFuncionalidad;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum as C;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoIadlEnum as I;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\OportunidadIadlEnum as O;
use DomainException;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: IADL con oportunidad, nivel previo y causa de ayuda separados.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Funcionalidad y clasificación experimental.
 *
 * RESPONSABILIDAD:
 * Verificar las precondiciones de los objetos, sin ejecutar clasificación.
 *
 * ENTRADAS:
 * Casos y referencias totalmente ficticios.
 *
 * SALIDAS:
 * Aserciones de conservación y rechazo por invariantes.
 *
 * RESTRICCIONES:
 * No accede a BDD, no inicia Laravel ni acredita validación clínica.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1–R10; contrato DCL-PET-20261017-v0.1.
 */
final class FuncionalidadEntitiesTest extends TestCase
{
    /**
     * Conserva una no ejecución institucional sin convertirla en atribución cognitiva.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_rutina_institucional_no_demuestra_dependencia_cognitiva(): void
    {
        $e = F::iadl(['estado' => I::NO_REALIZADA, 'oportunidad' => O::SUSTITUIDA_POR_INSTITUCION,
            'causaAyuda' => 'La institución realiza la actividad.', 'atribucionCognitivaSimulada' => null, 'revisorSinteticoId' => null]);
        self::assertFalse($e->esInterpretable());
        self::assertNull($e->atribucionCognitivaSimulada);
        self::assertSame(I::NO_REALIZADA, $e->estado);
    }
    /**
     * Rechaza atribuir pérdida cognitiva a una actividad no ofrecida.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_atribucion_cognitiva_sin_oportunidad_se_rechaza(): void
    {
        $this->expectException(DomainException::class);
        F::iadl(['estado' => I::CON_AYUDA, 'oportunidad' => O::SIN_OPORTUNIDAD,
            'causaAyuda' => 'Sin oportunidad.', 'atribucionCognitivaSimulada' => true]);
    }
    /**
     * Rechaza NO_ACREDITADO sustentado únicamente por ayuda no cognitiva.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_ayuda_motora_no_acredita_perdida_de_independencia_cognitiva(): void
    {
        $e = F::iadl(['estado' => I::CON_AYUDA, 'causaAyuda' => 'Limitación motora ficticia.', 'atribucionCognitivaSimulada' => false]);
        $memoria = F::memoria([], [$e->hecho]);
        $this->expectException(DomainException::class);
        new VectorFuncionalidad($memoria, C::NO_ACREDITADO, [$e], 'SIM-REVISOR', 'Caso negativo.');
    }
    /**
     * Rechaza acreditar independencia cuando falta el contexto previo requerido.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_nivel_previo_ausente_no_sostiene_declaracion_concluyente(): void
    {
        $e = F::iadl(['nivelPrevio' => null]);
        $this->expectException(DomainException::class);
        new VectorFuncionalidad(F::memoria([], [$e->hecho]), C::ACREDITADO, [$e], 'SIM-REVISOR', 'Sin nivel previo.');
    }
    /**
     * Representa información funcional pendiente sin crear observaciones.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_ausencia_de_iadl_conserva_pendiente(): void
    {
        $r = new VectorFuncionalidad(F::memoria(), C::PENDIENTE, [], null, 'Falta información IADL.');
        self::assertSame(C::PENDIENTE, $r->independenciaRelativa);
        self::assertSame([], $r->evidencias);
    }
    /**
     * Rechaza usar una fuente ausente como desempeño independiente.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_iadl_con_valor_null_no_demuestra_independencia(): void
    {
        $this->expectException(DomainException::class);
        F::iadl(['hecho' => F::hecho(['valor' => null])]);
    }
    /**
     * Rechaza presentar una revisión sintética como perteneciente a un residente real.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_revision_causal_no_admite_contexto_real(): void
    {
        $this->expectException(DomainException::class);
        F::iadl(['hecho' => F::hecho(['residenteId' => 'COD_RES_000001', 'fuenteId' => 'REFERENCIA_IADL_SINTETICA'])]);
    }
    /**
     * Impide contar dos veces una IADL bajo una misma declaración.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_vector_funcional_rechaza_reutilizacion_de_la_misma_evidencia(): void
    {
        $e = F::iadl();
        $this->expectException(DomainException::class);
        new VectorFuncionalidad(F::memoria([], [$e->hecho]), C::ACREDITADO, [$e, $e], 'SIM-REVISOR', 'Duplicado.');
    }
    /**
     * Rechaza una actividad documentada para otro sujeto.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_vector_funcional_rechaza_contexto_ajeno(): void
    {
        $e = F::iadl(['hecho' => F::hecho(['residenteId' => 'SINTETICO-OTRO', 'fuenteId' => 'REFERENCIA_IADL_SINTETICA'])]);
        $this->expectException(DomainException::class);
        new VectorFuncionalidad(F::memoria(), C::PENDIENTE, [$e], null, 'Sujeto ajeno.');
    }

}
