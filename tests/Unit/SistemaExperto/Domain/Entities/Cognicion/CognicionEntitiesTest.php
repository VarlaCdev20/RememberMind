<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Entities\Cognicion;

use App\SistemaExperto\Domain\Entities\Cognicion\ResultadoDominio;
use App\SistemaExperto\Domain\Entities\Cognicion\ResultadoSubcomponente;
use App\SistemaExperto\Domain\Entities\Cognicion\VectorCognitivo;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum as D;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EstadoDominioEnum as E;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\EvaluabilidadEnum as V;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\HallazgoInstrumentalEnum as H;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\SubcomponenteCognitivoEnum as S;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Perfiles sin agregación y resultados con alcance positivo.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Hechos y resultados cognitivos.
 *
 * RESPONSABILIDAD:
 * Verificar conservación, contexto y rechazo de conclusiones sin soporte.
 *
 * ENTRADAS:
 * Hechos y referencias totalmente ficticios.
 *
 * SALIDAS:
 * Aserciones sobre invariantes constructivas, sin interpretación clínica.
 *
 * RESTRICCIONES:
 * No inicia Laravel, no consulta BDD ni implementa servicios de la Fase D.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase B e invariantes R1–R10.
 */
final class CognicionEntitiesTest extends TestCase
{
    /**
     * Construye desconocimiento explícito con cero soportes.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_subcomponente_desconocido_permite_ausencia_sin_normalizar(): void
    {
        $r = new ResultadoSubcomponente(S::MEM_RECONOCIMIENTO, H::DESCONOCIDO, F::memoria(), [], 'Sin información.');
        self::assertSame(H::DESCONOCIDO, $r->hallazgo);
        self::assertSame([], $r->evidencias);
    }
    /**
     * Impide que un hallazgo de atención se presente como memoria.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_subcomponente_rechaza_evidencia_de_otro_eje(): void
    {
        $e = F::evidencia(subcomponente: S::ATE_SOSTENIDA, dominio: D::ATENCION);
        $this->expectException(DomainException::class);
        new ResultadoSubcomponente(S::MEM_RECONOCIMIENTO, H::HALLAZGO_DESCRIPTIVO, F::memoria([$e]), [$e->hecho->id], 'Prueba negativa.');
    }
    /**
     * Conserva un hallazgo de tarea sin elevarlo automáticamente a A.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_subcomponente_preserva_fuente_y_no_crea_estado_de_dominio(): void
    {
        $e = F::evidencia(subcomponente: S::MEM_RECONOCIMIENTO);
        $r = new ResultadoSubcomponente(S::MEM_RECONOCIMIENTO, H::HALLAZGO_DESCRIPTIVO, F::memoria([$e]), [$e->hecho->id], 'Hallazgo aislado.');
        self::assertSame($e, $r->evidencias[0]);
        self::assertSame(D::MEMORIA, $r->dominio);
    }
    /**
     * Rechaza P a partir del solo nivel de evaluabilidad 2.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_integrada_no_acredita_rendimiento_esperado_sin_soporte(): void
    {
        $this->expectException(DomainException::class);
        new ResultadoDominio(D::MEMORIA, E::RENDIMIENTO_ESPERADO, V::EVALUACION_INTEGRADA, F::memoria(), [], [], 'Sin soportes.');
    }
    /**
     * Rechaza A aun cuando haya un hallazgo descriptivo del mismo dominio.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_hallazgo_g09_no_acredita_alteracion_completa(): void
    {
        $e = F::evidencia();
        $this->expectException(DomainException::class);
        new ResultadoDominio(D::MEMORIA, E::ALTERACION, V::EVALUACION_INTEGRADA, F::memoria([$e]), [], [$e->hecho->id], 'Solo observación.');
    }
    /**
     * Rechaza convertir una referencia de subcomponente en estado global A.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_tarea_aislada_no_sustituye_referencia_del_dominio(): void
    {
        $e = F::evidencia(hallazgo: H::ALTERACION_OBJETIVA_SIMULADA, subcomponente: S::MEM_RECONOCIMIENTO);
        $this->expectException(DomainException::class);
        new ResultadoDominio(D::MEMORIA, E::ALTERACION, V::EVALUACION_INTEGRADA, F::memoria([$e]), [], [$e->hecho->id], 'Solo una tarea.');
    }
    /**
     * Rechaza A cuando la compuerta declara únicamente pre-evaluación.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_senal_no_habilita_alteracion_aunque_exista_referencia(): void
    {
        $e = F::evidencia(hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $this->expectException(DomainException::class);
        new ResultadoDominio(D::MEMORIA, E::ALTERACION, V::PRE_EVALUACION_SENAL, F::memoria([$e]), [], [$e->hecho->id], 'Solo señal.');
    }
    /**
     * Impide reutilizar evidencia de atención para memoria.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_resultado_rechaza_soporte_de_otro_dominio(): void
    {
        $e = F::evidencia(dominio: D::ATENCION, hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $this->expectException(DomainException::class);
        new ResultadoDominio(D::MEMORIA, E::ALTERACION, V::EVALUACION_INTEGRADA, F::memoria([$e]), [], [$e->hecho->id], 'Caso inválido.');
    }
    /**
     * Admite una declaración sintética completa sin introducir un requisito basal universal.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_referencia_positiva_no_requiere_linea_basal_inventada(): void
    {
        $e = F::evidencia(hallazgo: H::RENDIMIENTO_ESPERADO_SIMULADO);
        $r = new ResultadoDominio(D::MEMORIA, E::RENDIMIENTO_ESPERADO, V::EVALUACION_INTEGRADA, F::memoria([$e]), [], [$e->hecho->id], 'Referencia externa ficticia.');
        self::assertSame([$e], $r->soportesPositivos());
    }
    /**
     * Rechaza A cuando existe también una referencia completa P sin adjudicar.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_referencias_opuestas_no_admiten_estado_concluyente(): void
    {
        $a = F::evidencia('SIM-A', hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $p = F::evidencia('SIM-P', hallazgo: H::RENDIMIENTO_ESPERADO_SIMULADO);
        $this->expectException(DomainException::class);
        new ResultadoDominio(D::MEMORIA, E::ALTERACION, V::EVALUACION_INTEGRADA, F::memoria([$a, $p]), [],
            [$a->hecho->id, $p->hecho->id], 'Contradicción.');
    }

    /**
     * Rechaza concluir cuando una evidencia completa declara falta de interpretabilidad.
     *
     * @return void
     *
     * No recibe parámetros; se espera DomainException por contexto no interpretable.
     */
    public function test_referencia_positiva_no_oculta_evidencia_no_interpretable(): void
    {
        $a = F::evidencia('SIM-A', hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $i = F::evidencia('SIM-I', hallazgo: H::NO_INTERPRETABLE);
        $this->expectException(DomainException::class);
        new ResultadoDominio(D::MEMORIA, E::ALTERACION, V::EVALUACION_INTEGRADA, F::memoria([$a, $i]), [],
            [$a->hecho->id, $i->hecho->id], 'La interpretación permanece impedida.');
    }
    /**
     * Conserva cinco estados U explícitos y su memoria sin generar normalidad.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_vector_conserva_cinco_desconocidos_sin_indice_global(): void
    {
        $vector = F::perfil(['U', 'U', 'U', 'U', 'U'])->cognicion;
        self::assertCount(5, $vector->resultados);
        foreach (D::cases() as $d) {
            self::assertSame(E::DESCONOCIDO, $vector->resultado($d)->estado);
            self::assertSame([], $vector->resultado($d)->soportesPositivos());
        }
        self::assertFalse(property_exists($vector, 'puntuacionGlobal'));
    }
    /**
     * Rechaza cuatro dominios en lugar de completar silenciosamente el quinto.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_vector_rechaza_eje_faltante(): void
    {
        $resultados = array_values(F::perfil()->cognicion->resultados);
        array_pop($resultados);
        $this->expectException(DomainException::class);
        new VectorCognitivo($resultados);
    }
    /**
     * Rechaza dos resultados del mismo eje aunque la colección tenga cinco elementos.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_vector_rechaza_dominio_duplicado(): void
    {
        $resultados = array_values(F::perfil()->cognicion->resultados);
        $resultados[4] = $resultados[0];
        $this->expectException(DomainException::class);
        new VectorCognitivo($resultados);
    }
    /**
     * Impide mezclar dos memorias construidas por separado.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_vector_rechaza_instantaneas_distintas(): void
    {
        $resultados = array_values(F::perfil()->cognicion->resultados);
        $resultados[4] = F::perfil()->cognicion->resultado(D::VISUOESPACIAL);
        $this->expectException(DomainException::class);
        new VectorCognitivo($resultados);
    }
    /**
     * Impide presentar un episodio común como dos soportes independientes A/P.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_vector_rechaza_episodio_reutilizado_entre_dominios(): void
    {
        $a = F::evidencia('SIM-M', D::MEMORIA, H::ALTERACION_OBJETIVA_SIMULADA, cambiosHecho: ['episodioId' => 'SIM-COMPARTIDO']);
        $b = F::evidencia('SIM-A', D::ATENCION, H::RENDIMIENTO_ESPERADO_SIMULADO, cambiosHecho: ['episodioId' => 'SIM-COMPARTIDO']);
        $memoria = F::memoria([$a, $b]);
        $resultados = [
            new ResultadoDominio(D::MEMORIA, E::ALTERACION, V::EVALUACION_INTEGRADA, $memoria, [], [$a->hecho->id], 'Ficticio.'),
            new ResultadoDominio(D::ATENCION, E::RENDIMIENTO_ESPERADO, V::EVALUACION_INTEGRADA, $memoria, [], [$b->hecho->id], 'Ficticio.'),
        ];
        foreach ([D::FUNCION_EJECUTIVA, D::LENGUAJE, D::VISUOESPACIAL] as $d) {
            $resultados[] = new ResultadoDominio($d, E::DESCONOCIDO, V::NO_EVALUABLE, $memoria, [], [], 'Sin datos.');
        }
        $this->expectException(DomainException::class);
        new VectorCognitivo($resultados);
    }

    /**
     * Conserva las dos referencias opuestas al declarar un subcomponente contradictorio.
     *
     * @return void
     *
     * No recibe parámetros; las referencias son manuales y exclusivamente sintéticas.
     */
    public function test_subcomponente_contradictorio_preserva_ambos_soportes(): void
    {
        $a = F::evidencia('SIM-A', hallazgo: H::ALTERACION_OBJETIVA_SIMULADA, subcomponente: S::MEM_RECONOCIMIENTO);
        $p = F::evidencia('SIM-P', hallazgo: H::RENDIMIENTO_ESPERADO_SIMULADO, subcomponente: S::MEM_RECONOCIMIENTO);
        $memoria = F::memoria([$a, $p]);
        $resultado = new ResultadoSubcomponente(S::MEM_RECONOCIMIENTO, H::CONTRADICTORIO,
            $memoria, [$a->hecho->id, $p->hecho->id], 'Referencias ficticias discordantes.');

        self::assertSame(H::CONTRADICTORIO, $resultado->hallazgo);
        self::assertSame([$a, $p], $resultado->evidencias);
        self::assertSame($memoria, $resultado->memoria);
    }

    /**
     * Impide seleccionar una conclusión favorable u opuesta ocultando el conflicto.
     *
     * @param H $hallazgo Conclusión incompatible con los dos soportes recibidos.
     * @return void
     * @throws DomainException Excepción esperada por contradicción no preservada.
     */
    #[DataProvider('hallazgosConcluyentes')]
    public function test_subcomponente_no_oculta_referencias_opuestas(H $hallazgo): void
    {
        $a = F::evidencia('SIM-A', hallazgo: H::ALTERACION_OBJETIVA_SIMULADA, subcomponente: S::MEM_RECONOCIMIENTO);
        $p = F::evidencia('SIM-P', hallazgo: H::RENDIMIENTO_ESPERADO_SIMULADO, subcomponente: S::MEM_RECONOCIMIENTO);
        $memoria = F::memoria([$a, $p]);

        $this->expectException(DomainException::class);
        new ResultadoSubcomponente(S::MEM_RECONOCIMIENTO, $hallazgo,
            $memoria, [$a->hecho->id, $p->hecho->id], 'Conclusión incompatible.');
    }

    /**
     * Aporta los dos hallazgos sintéticos que el conflicto impide declarar.
     *
     * @return iterable<string, array{H}> Conclusiones explícitas incompatibles.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function hallazgosConcluyentes(): iterable
    {
        yield 'alteración simulada' => [H::ALTERACION_OBJETIVA_SIMULADA];
        yield 'rendimiento esperado simulado' => [H::RENDIMIENTO_ESPERADO_SIMULADO];
    }

}
