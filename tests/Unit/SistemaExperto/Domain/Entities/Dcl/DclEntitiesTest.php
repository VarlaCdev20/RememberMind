<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Entities\Dcl;

use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum as C;
use MotorExperto\dominio\orion\ValueObjects\Dcl\MotivoAbstencionEnum as M;
use MotorExperto\dominio\orion\ValueObjects\Dcl\SubtipoDclEnum as P;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum as D;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Resultados candidatos y abstenciones con requisitos explícitos.
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
final class DclEntitiesTest extends TestCase
{

    /**
     * Admite cuatro candidatos previamente declarados con distribución compatible.
     *
     * No prueba un clasificador: el patrón es entrada explícita del constructor.
     *
     * @param list<string> $estados Perfil cognitivo manual.
     * @param P $patron Patrón candidato declarado.
     * @return void
     *
     * No se esperan excepciones con los casos ficticios coherentes.
     */
    #[DataProvider('patrones')]
    public function test_cuatro_patrones_declarados_conservan_perfil_y_soportes(array $estados, P $patron): void
    {
        $perfil = F::perfil($estados);
        $r = new ResultadoClasificacionDcl($perfil, $patron, [], 'Candidato ficticio, no diagnóstico.');
        self::assertSame($patron, $r->patronCandidato);
        self::assertSame($perfil, $r->perfil);
        self::assertSame([], $r->motivos);
        self::assertSame('SIMULACION_ACADEMICA_NO_CLINICA', $r::USO);
        self::assertSame('DCL-PET-20261017-v0.1', $r->perfil->versionContrato);
        self::assertCount(5, $r->perfil->soportes);
    }

    /**
     * Declara los cuatro perfiles de referencia sin puntajes ni instrumentos reales.
     *
     * @return iterable<string, array{list<string>, P}> Distribuciones documentales.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function patrones(): iterable
    {
        yield 'PET-01' => [['A', 'P', 'P', 'P', 'P'], P::AMNESICO_DOMINIO_UNICO];
        yield 'PET-02' => [['A', 'A', 'P', 'P', 'P'], P::AMNESICO_MULTIDOMINIO];
        yield 'PET-03' => [['P', 'A', 'P', 'P', 'P'], P::NO_AMNESICO_DOMINIO_UNICO];
        yield 'PET-04' => [['P', 'A', 'A', 'P', 'P'], P::NO_AMNESICO_MULTIDOMINIO];
    }
    /**
     * Rechaza una estructura de cuatro criterios sin completar el quinto por defecto.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_perfil_rechaza_requisito_faltante(): void
    {
        $p = F::perfil();
        $criterios = $p->criterios;
        unset($criterios['DCL-C05']);
        $this->expectException(DomainException::class);
        new PerfilRequisitosDcl($p->cognicion, $p->funcionalidad, $p->diferencial, $criterios, $p->soportes, $p->justificaciones);
    }
    /**
     * Rechaza ACREDITADO si falta la referencia positiva del requisito.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_perfil_rechaza_acreditacion_sin_soporte(): void
    {
        $p = F::perfil();
        $soportes = $p->soportes;
        $soportes['DCL-C01'] = [];
        $this->expectException(DomainException::class);
        new PerfilRequisitosDcl($p->cognicion, $p->funcionalidad, $p->diferencial, $p->criterios, $soportes, $p->justificaciones);
    }
    /**
     * Impide declarar C03 acreditado mientras el perfil funcional sigue pendiente.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_perfil_rechaza_c03_sin_revision_funcional_acreditada(): void
    {
        $this->expectException(DomainException::class);
        F::perfil(independencia: C::PENDIENTE);
    }
    /**
     * Rechaza una acreditación incompatible con la revisión diferencial explícita.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_perfil_rechaza_c04_acreditado_con_diferencial_impedido(): void
    {
        $this->expectException(DomainException::class);
        F::perfil(diferencial: D::IMPEDIMENTO_NO_RESUELTO);
    }
    /**
     * Rechaza PET aunque los dominios estén A/P si falta C01.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_requisito_pendiente_impide_patron(): void
    {
        $p = F::perfil(cambiosCriterios: ['DCL-C01' => C::PENDIENTE]);
        $this->expectException(DomainException::class);
        new ResultadoClasificacionDcl($p, P::AMNESICO_DOMINIO_UNICO, [], 'Caso negativo.');
    }
    /**
     * Rechaza PET con una condición general expresamente incumplida.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_requisito_no_acreditado_impide_patron(): void
    {
        $p = F::perfil(cambiosCriterios: ['DCL-C01' => C::NO_ACREDITADO]);
        $this->expectException(DomainException::class);
        new ResultadoClasificacionDcl($p, P::AMNESICO_DOMINIO_UNICO, [], 'Caso negativo.');
    }

    /**
     * Rechaza candidatos con un dominio desconocido, no interpretable o contradictorio.
     *
     * @param string $estado Estado bloqueante de memoria.
     * @return void
     * @throws DomainException Excepción exigida para impedir conclusión.
     */
    #[DataProvider('noConcluyentes')]
    public function test_ui_x_impiden_patron(string $estado): void
    {
        $p = F::perfil([$estado, 'P', 'P', 'P', 'P']);
        $this->expectException(DomainException::class);
        new ResultadoClasificacionDcl($p, P::AMNESICO_DOMINIO_UNICO, [], 'Caso negativo.');
    }

    /**
     * Proporciona los tres estados no concluyentes del contrato.
     *
     * @return iterable<string, array{string}> U, I y X.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function noConcluyentes(): iterable
    {
        yield 'desconocido' => ['U'];
        yield 'no interpretable' => ['I'];
        yield 'contradictorio' => ['X'];
    }
    /**
     * Conserva la no conclusión sin inventar un quinto subtipo.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_abstencion_preserva_motivos_y_perfil_pendiente(): void
    {
        $p = F::perfil(cambiosCriterios: ['DCL-C01' => C::PENDIENTE]);
        $r = new ResultadoClasificacionDcl($p, null, [M::ABSTENCION_INFORMACION_INSUFICIENTE], 'C01 pendiente.');
        self::assertNull($r->patronCandidato);
        self::assertSame([M::ABSTENCION_INFORMACION_INSUFICIENTE], $r->motivos);
        self::assertSame(C::PENDIENTE, $r->perfil->criterios['DCL-C01']);
    }
    /**
     * Representa un diferencial no resuelto sin diagnosticar ni emitir PET.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_diferencial_pendiente_conserva_abstencion(): void
    {
        $p = F::perfil(cambiosCriterios: ['DCL-C04' => C::PENDIENTE], diferencial: D::PENDIENTE);
        $r = new ResultadoClasificacionDcl($p, null, [M::ABSTENCION_EVALUACION_DIFERENCIAL], 'Revisión pendiente.');
        self::assertNull($r->patronCandidato);
        self::assertSame(D::PENDIENTE, $r->perfil->diferencial);
    }
    /**
     * Conserva explícitamente C02 acreditado con todos los dominios P como no conclusión.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_c02_incoherente_puede_conservarse_para_adjudicacion(): void
    {
        $p = F::perfil(['P', 'P', 'P', 'P', 'P']);
        $r = new ResultadoClasificacionDcl($p, null, [M::REQUIERE_ADJUDICACION_CONTRATO], 'C02 incompatible con la distribución.');
        self::assertNull($r->patronCandidato);
        self::assertSame(C::ACREDITADO, $p->criterios['DCL-C02']);
    }
    /**
     * Impide declarar PET-04 para un perfil amnésico de dominio único.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_patron_incompatible_con_distribucion_se_rechaza(): void
    {
        $this->expectException(DomainException::class);
        new ResultadoClasificacionDcl(F::perfil(), P::NO_AMNESICO_MULTIDOMINIO, [], 'Distribución incorrecta.');
    }
    /**
     * Impide una salida que afirme simultáneamente patrón y abstención.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_patron_y_abstencion_no_pueden_coexistir(): void
    {
        $this->expectException(DomainException::class);
        new ResultadoClasificacionDcl(F::perfil(), P::AMNESICO_DOMINIO_UNICO, [M::ABSTENCION_CONTRADICCION], 'Salida ambigua.');
    }
    /**
     * Impide una no conclusión sin explicación estructurada.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_abstencion_sin_motivos_se_rechaza(): void
    {
        $this->expectException(DomainException::class);
        new ResultadoClasificacionDcl(F::perfil(), null, [], 'Sin motivo.');
    }
    /**
     * Conserva las reglas originales sin ejecutar ni copiar su lógica.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_reglas_declaradas_preservan_identidad_y_version(): void
    {
        $regla = new ReglaExperta('SIM-R', 'SIM-V1',
            ['operador' => OperadorCondicionEnum::IGUAL_A, 'variable' => 'SIM-VAR', 'valor' => true],
            ['concepto' => 'SIM-HALLAZGO', 'valor' => true], true, 'SIM-FUENTE');
        $r = new ResultadoClasificacionDcl(F::perfil(), P::AMNESICO_DOMINIO_UNICO, [], 'Declaración ficticia.', [$regla]);
        self::assertSame([$regla], $r->reglasAplicadas);
    }
    /**
     * Rechaza trazabilidad que mezcle una regla ajena al conocimiento del caso.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_regla_aplicada_de_otra_version_se_rechaza(): void
    {
        $regla = new ReglaExperta('SIM-R', 'SIM-V2',
            ['operador' => OperadorCondicionEnum::IGUAL_A, 'variable' => 'SIM-VAR', 'valor' => true],
            ['concepto' => 'SIM-HALLAZGO', 'valor' => true], true, 'SIM-FUENTE');
        $this->expectException(DomainException::class);
        new ResultadoClasificacionDcl(F::perfil(), P::AMNESICO_DOMINIO_UNICO, [], 'Versión ajena.', [$regla]);
    }

    /**
     * Evita sustituir la procedencia de un requisito mediante una copia equivalente.
     *
     * @return void
     * @throws DomainException Excepción esperada por pérdida del soporte original.
     */
    public function test_perfil_rechaza_copia_del_soporte_original(): void
    {
        $p = F::perfil();
        $soportes = $p->soportes;
        $soportes['DCL-C01'] = [clone $soportes['DCL-C01'][0]];

        $this->expectException(DomainException::class);
        new PerfilRequisitosDcl($p->cognicion, $p->funcionalidad, $p->diferencial,
            $p->criterios, $soportes, $p->justificaciones);
    }

    /**
     * Un soporte acreditado de C02 no demuestra el cambio corroborado exigido en C01.
     *
     * @return void
     * @throws DomainException Excepción esperada por alcance semántico incompatible.
     */
    public function test_perfil_rechaza_soporte_de_otro_requisito(): void
    {
        $p = F::perfil();
        $soportes = $p->soportes;
        $soportes['DCL-C01'] = $soportes['DCL-C02'];

        $this->expectException(DomainException::class);
        new PerfilRequisitosDcl($p->cognicion, $p->funcionalidad, $p->diferencial,
            $p->criterios, $soportes, $p->justificaciones);
    }

    /**
     * Rechaza aplicar las invariantes actuales a una versión de contrato desconocida.
     *
     * @return void
     * @throws DomainException Excepción esperada sin adaptación silenciosa de versiones.
     */
    public function test_perfil_rechaza_version_de_contrato_no_implementada(): void
    {
        $p = F::perfil();

        $this->expectException(DomainException::class);
        new PerfilRequisitosDcl($p->cognicion, $p->funcionalidad, $p->diferencial,
            $p->criterios, $p->soportes, $p->justificaciones, 'SIM-CONTRATO-NO-IMPLEMENTADO');
    }

}
