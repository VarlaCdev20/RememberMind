<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\ValueObjects\Dcl;

use MotorExperto\dominio\orion\ValueObjects\Dcl\CriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\MotivoAbstencionEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\SubtipoDclEnum;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Requisitos y salidas del contrato experimental Petersen.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Contratos de ValueObjects.
 *
 * RESPONSABILIDAD:
 * Comprobar los límites semánticos del vocabulario aislado de esta fase.
 *
 * ENTRADAS:
 * Códigos documentales y propuestas técnicas, sin información clínica real.
 *
 * SALIDAS:
 * Aserciones sobre representación y rechazo, sin resultados de inferencia.
 *
 * RESTRICCIONES:
 * No inicia Laravel, no consulta BDD, no clasifica ni acredita validación clínica.
 *
 * TRAZABILIDAD:
 * DCL-PET-20261017-v0.1, secciones 2–6 y cierre 03A; R1/R7/R9.
 */
final class VocabularioDclTest extends TestCase
{
    /**
     * Verifica los cinco requisitos y su identidad semántica documental.
     *
     * @return void
     *
     * No recibe parámetros ni evalúa los requisitos.
     */
    public function test_requisitos_c01_a_c05_conservan_su_significado(): void
    {
        self::assertSame(['DCL-C01', 'DCL-C02', 'DCL-C03', 'DCL-C04', 'DCL-C05'], array_column(CriterioDclEnum::cases(), 'value'));
        self::assertSame(CriterioDclEnum::CAMBIO_COGNITIVO_CORROBORADO, CriterioDclEnum::from('DCL-C01'));
        self::assertSame(CriterioDclEnum::ALTERACION_COGNITIVA_OBJETIVA, CriterioDclEnum::from('DCL-C02'));
        self::assertSame(CriterioDclEnum::INDEPENDENCIA_FUNCIONAL_RELATIVA, CriterioDclEnum::from('DCL-C03'));
        self::assertSame(CriterioDclEnum::EVALUACION_DIFERENCIAL, CriterioDclEnum::from('DCL-C04'));
        self::assertSame(CriterioDclEnum::INTERPRETABILIDAD_Y_CALIDAD, CriterioDclEnum::from('DCL-C05'));
        self::assertNull(CriterioDclEnum::tryFrom('DCL-C06'));
    }

    /**
     * Usa los estados textuales del documento, sin confundirlos con A/P/U/I/X.
     *
     * @return void
     *
     * No recibe parámetros ni interpreta los alias del código experimental previo.
     */
    public function test_estados_criterio_preservan_pendiente_y_no_acreditado(): void
    {
        self::assertSame(['ACREDITADO', 'NO_ACREDITADO', 'PENDIENTE', 'CONTRADICTORIO'], array_column(EstadoCriterioDclEnum::cases(), 'value'));
        self::assertNull(EstadoCriterioDclEnum::tryFrom('U'));
        self::assertNull(EstadoCriterioDclEnum::tryFrom('P'));
        self::assertNull(EstadoCriterioDclEnum::tryFrom('S'));
        self::assertNull(EstadoCriterioDclEnum::tryFrom(''));
    }

    /**
     * Verifica exactamente los cuatro patrones candidatos, sin quinto subtipo.
     *
     * @return void
     *
     * No recibe parámetros ni realiza una clasificación.
     */
    public function test_patrones_conservan_codigos_y_significados(): void
    {
        self::assertSame(['PET-01', 'PET-02', 'PET-03', 'PET-04'], array_column(SubtipoDclEnum::cases(), 'value'));
        self::assertSame(SubtipoDclEnum::AMNESICO_DOMINIO_UNICO, SubtipoDclEnum::from('PET-01'));
        self::assertSame(SubtipoDclEnum::AMNESICO_MULTIDOMINIO, SubtipoDclEnum::from('PET-02'));
        self::assertSame(SubtipoDclEnum::NO_AMNESICO_DOMINIO_UNICO, SubtipoDclEnum::from('PET-03'));
        self::assertSame(SubtipoDclEnum::NO_AMNESICO_MULTIDOMINIO, SubtipoDclEnum::from('PET-04'));
        self::assertNull(SubtipoDclEnum::tryFrom('PET-05'));
    }

    /**
     * Exige que ninguna abstención pueda construirse como un patrón candidato.
     *
     * @return void
     *
     * No recibe parámetros; se verifican los códigos exactos del contrato.
     */
    public function test_abstenciones_son_salidas_separadas_de_patrones(): void
    {
        self::assertSame([
            'ABSTENCION_CONTRADICCION',
            'ABSTENCION_EVALUACION_DIFERENCIAL',
            'ABSTENCION_INFORMACION_INSUFICIENTE',
            'SIN_PATRON_CLASIFICABLE_POR_REQUISITO',
            'REQUIERE_ADJUDICACION_CONTRATO',
            'SOLO_LABORATORIO_NO_EMITIR',
        ], array_column(MotivoAbstencionEnum::cases(), 'value'));

        foreach (MotivoAbstencionEnum::cases() as $motivo) {
            self::assertNull(SubtipoDclEnum::tryFrom($motivo->value));
        }
        foreach (SubtipoDclEnum::cases() as $patron) {
            self::assertNull(MotivoAbstencionEnum::tryFrom($patron->value));
        }
    }

    /**
     * Conserva el nombre conciliado de adjudicación y rechaza el literal histórico.
     *
     * @return void
     *
     * No recibe parámetros ni modifica la matriz documental de origen.
     */
    public function test_adjudicacion_respeta_conciliacion_documental_03a(): void
    {
        self::assertSame(MotivoAbstencionEnum::REQUIERE_ADJUDICACION_CONTRATO, MotivoAbstencionEnum::from('REQUIERE_ADJUDICACION_CONTRATO'));
        self::assertNull(MotivoAbstencionEnum::tryFrom('REQUIERE_ADJUDICACION_C02_SIN_SOPORTE'));
    }

}
