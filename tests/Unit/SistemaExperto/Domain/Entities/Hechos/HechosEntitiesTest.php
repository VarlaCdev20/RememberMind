<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Entities\Hechos;

use App\SistemaExperto\Domain\Entities\Hechos\EvidenciaCognitiva;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Identidad, temporalidad, deduplicación y memoria inmutable.
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
final class HechosEntitiesTest extends TestCase
{
    /**
     * Conserva cada valor original sin convertir ausencia en normalidad.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_ausencia_false_y_cero_se_conservan_sin_inferencias(): void
    {
        foreach ([null, false, 0, '0'] as $valor) {
            self::assertSame($valor, F::hecho(['valor' => $valor])->valor);
        }
    }

    /**
     * Rechaza tokens de identidad vacíos, con espacios o con controles.
     *
     * @param string $id Identidad inválida artificial.
     * @return void
     * @throws DomainException Excepción exigida por la entrada inválida.
     */
    #[DataProvider('identidadesInvalidas')]
    public function test_identidad_invalida_se_rechaza(string $id): void
    {
        $this->expectException(DomainException::class);
        F::hecho(['id' => $id]);
    }

    /**
     * Proporciona tokens inválidos sin datos reales.
     *
     * @return iterable<string, array{string}> Identificadores negativos.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function identidadesInvalidas(): iterable
    {
        yield 'vacío' => [''];
        yield 'espacios' => ['   '];
        yield 'interno' => ['SIM ID'];
        yield 'control' => ["SIM\nID"];
    }
    /**
     * Impide NaN como dato aparentemente interpretable.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_numero_no_finito_se_rechaza(): void
    {
        $this->expectException(DomainException::class);
        F::hecho(['valor' => NAN]);
    }
    /**
     * Rechaza vincular atención a memoria por manipulación de entrada.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_evidencia_no_admite_subcomponente_de_otro_dominio(): void
    {
        $this->expectException(DomainException::class);
        F::evidencia(subcomponente: SubcomponenteCognitivoEnum::ATE_SOSTENIDA);
    }
    /**
     * Admite null como desconocido sin fabricar una conclusión.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_dato_ausente_solo_conserva_desconocimiento(): void
    {
        $e = F::evidencia(hallazgo: HallazgoInstrumentalEnum::DESCONOCIDO, cambiosHecho: ['valor' => null]);
        self::assertNull($e->hecho->valor);
        self::assertSame(HallazgoInstrumentalEnum::DESCONOCIDO, $e->hallazgo);
    }
    /**
     * Rechaza interpretar una fuente ausente.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_null_no_sostiene_hallazgo_descriptivo(): void
    {
        $this->expectException(DomainException::class);
        F::evidencia(cambiosHecho: ['valor' => null]);
    }
    /**
     * Rechaza A/P simulado cuya procedencia es un hallazgo del propio motor.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_g09_no_puede_hacerse_pasar_por_referencia_independiente(): void
    {
        $this->expectException(DomainException::class);
        F::evidencia(hallazgo: HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA,
            cambiosHecho: ['fuenteId' => 'G09_DESCRIPTIVO']);
    }
    /**
     * Rechaza una referencia A/P con metadatos incompletos.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_referencia_sin_norma_ficticia_se_rechaza(): void
    {
        $e = F::evidencia(hallazgo: HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA);
        $ref = $e->referenciaSintetica;
        unset($ref['norma']);
        $this->expectException(DomainException::class);
        new EvidenciaCognitiva($e->hecho, $e->dominio, null, $e->hallazgo, $e->instrumentoId, $e->justificacion, $ref);
    }
    /**
     * Comprueba que incorporar evidencia no modifica la memoria anterior.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_memoria_incorpora_en_otra_instantanea_y_preserva_original(): void
    {
        $original = F::memoria();
        $e = F::evidencia();
        $nueva = $original->conEvidencia($e);
        self::assertSame([], $original->hechos);
        self::assertSame([], $original->evidencias);
        self::assertSame($e, $nueva->evidencia($e->hecho->id));
        self::assertSame($e->hecho, $nueva->hechos[$e->hecho->id]);
    }

    /**
     * Rechaza hechos de otra evaluación, sujeto, versión o posteriores al corte.
     *
     * @param string $campo Parámetro del contexto manipulado.
     * @param string|DateTimeImmutable $valor Valor incompatible.
     * @return void
     * @throws DomainException Excepción exigida por el contexto ajeno.
     */
    #[DataProvider('contextosAjenos')]
    public function test_memoria_rechaza_contexto_ajeno(string $campo, string|DateTimeImmutable $valor): void
    {
        $this->expectException(DomainException::class);
        F::memoria()->conEvidencia(F::evidencia(cambiosHecho: [$campo => $valor]));
    }

    /**
     * Proporciona cuatro alteraciones independientes del contexto.
     *
     * @return iterable<string, array{string, string|DateTimeImmutable}> Manipulaciones.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function contextosAjenos(): iterable
    {
        yield 'evaluación' => ['evaluacionId', 'SIM-OTRA'];
        yield 'sujeto' => ['residenteId', 'SINTETICO-OTRO'];
        yield 'versión' => ['versionConocimiento', 'SIM-V2'];
        yield 'futuro' => ['fecha', F::fecha()->modify('+1 second')];
    }
    /**
     * Conserva un hecho situado exactamente en la fecha de corte.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_corte_inclusivo_admite_fecha_exacta(): void
    {
        $e = F::evidencia();
        self::assertSame($e, F::memoria([$e])->evidencia($e->hecho->id));
    }
    /**
     * Impide duplicar una fuente atómica renombrando el hecho.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_misma_fuente_con_otro_id_se_rechaza(): void
    {
        $a = F::hecho();
        $b = F::hecho(['id' => 'SIM-H-2']);
        $this->expectException(DomainException::class);
        F::memoria([], [$a, $b]);
    }
    /**
     * Rechaza la misma evidencia incorporada dos veces.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_evidencia_reincorporada_no_se_cuenta_dos_veces(): void
    {
        $e = F::evidencia();
        $memoria = F::memoria([$e]);
        $this->expectException(DomainException::class);
        $memoria->conEvidencia($e);
    }
    /**
     * Conserva la dependencia aunque los registros e instrumentos tengan IDs distintos.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_mismo_episodio_no_es_corroboracion_independiente(): void
    {
        $a = F::evidencia('SIM-A', cambiosHecho: ['episodioId' => 'SIM-COMPARTIDO']);
        $b = F::evidencia('SIM-B', DominioCognitivoEnum::ATENCION, cambiosHecho: ['episodioId' => 'SIM-COMPARTIDO']);
        self::assertFalse($a->esIndependienteDe($b));
        self::assertTrue($a->esIndependienteDe(F::evidencia('SIM-C')));
    }
    /**
     * Impide renombrar episodio y hecho para reutilizar un instrumento como soporte independiente.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_instrumento_reutilizado_no_es_corroboracion_independiente(): void
    {
        $a = F::evidencia('SIM-A');
        $b = F::evidencia('SIM-B');
        $reutilizada = new EvidenciaCognitiva($b->hecho, $b->dominio, null, $b->hallazgo,
            $a->instrumentoId, $b->justificacion);
        self::assertFalse($a->esIndependienteDe($reutilizada));
    }

    /**
     * Rechaza sustituir el hecho original por un objeto idéntico copiado.
     *
     * @return void
     * @throws DomainException Excepción esperada al perder la identidad de origen.
     */
    public function test_memoria_rechaza_evidencia_ligada_a_una_copia_del_hecho(): void
    {
        $e = F::evidencia();
        $copia = new EvidenciaCognitiva(clone $e->hecho, $e->dominio, $e->subcomponente,
            $e->hallazgo, $e->instrumentoId, $e->justificacion);
        $original = F::memoria([], [$e->hecho]);

        $this->expectException(DomainException::class);
        $original->conEvidencia($copia);
    }

    /**
     * Evita incorporar una descripción sin su hecho original en la instantánea.
     *
     * @return void
     * @throws DomainException Excepción esperada por soporte ausente.
     */
    public function test_constructor_de_memoria_rechaza_evidencia_sin_hecho(): void
    {
        $this->expectException(DomainException::class);
        new MemoriaTrabajo('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', F::fecha(), [], [F::evidencia()]);
    }

    /**
     * Una consulta fallida no fabrica una evidencia desconocida ni preservada.
     *
     * @return void
     * @throws DomainException Excepción esperada por identidad no registrada.
     */
    public function test_consulta_de_evidencia_ausente_falla_explicitamente(): void
    {
        $this->expectException(DomainException::class);
        F::memoria()->evidencia('SIM-INEXISTENTE');
    }

    /**
     * Rechaza tratar otro caso como corroboración aunque sus fuentes difieran.
     *
     * @param string $campo Dimensión del contexto alterada.
     * @param string $valor Identificador artificial incompatible.
     * @return void
     *
     * No se esperan excepciones; se contrasta la independencia en ambos sentidos.
     */
    #[DataProvider('contextosDeIndependenciaAjenos')]
    public function test_independencia_exige_la_misma_evaluacion_residente_y_version(string $campo, string $valor): void
    {
        $a = F::evidencia('SIM-A');
        $b = F::evidencia('SIM-B', cambiosHecho: [$campo => $valor]);

        self::assertFalse($a->esIndependienteDe($b));
        self::assertFalse($b->esIndependienteDe($a));
    }

    /**
     * Separa alteraciones de evaluación, residente y versión del conocimiento.
     *
     * @return iterable<string, array{string, string}> Contextos incompatibles.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function contextosDeIndependenciaAjenos(): iterable
    {
        yield 'evaluación' => ['evaluacionId', 'SIM-OTRA'];
        yield 'residente' => ['residenteId', 'SINTETICO-OTRO'];
        yield 'versión' => ['versionConocimiento', 'SIM-V2'];
    }

    /**
     * La igualdad temporal depende del instante, no de la zona que lo representa.
     *
     * @return void
     *
     * No recibe parámetros; no se esperan excepciones en el corte inclusivo.
     */
    public function test_corte_inclusivo_admite_el_mismo_instante_en_otra_zona(): void
    {
        $e = F::evidencia(cambiosHecho: ['fecha' => new DateTimeImmutable('2026-10-17T16:00:00+00:00')]);

        self::assertSame($e, F::memoria([$e])->evidencia($e->hecho->id));
    }

    /**
     * Congela metadatos de referencia aunque el llamador mantenga una referencia PHP.
     *
     * @return void
     *
     * No recibe parámetros; las normas y revisores son exclusivamente ficticios.
     */
    public function test_metadatos_sinteticos_no_cambian_por_referencias_externas(): void
    {
        $e = F::evidencia(hallazgo: HallazgoInstrumentalEnum::ALTERACION_OBJETIVA_SIMULADA);
        $norma = 'SIM-NORMA-ORIGINAL';
        $ref = $e->referenciaSintetica;
        $ref['norma'] = &$norma;
        $congelada = new EvidenciaCognitiva($e->hecho, $e->dominio, null, $e->hallazgo,
            $e->instrumentoId, $e->justificacion, $ref);
        $norma = 'SIM-NORMA-CAMBIADA';

        self::assertSame('SIM-NORMA-ORIGINAL', $congelada->referenciaSintetica['norma']);
        self::assertSame($e->hecho, $congelada->hecho);
    }

}
