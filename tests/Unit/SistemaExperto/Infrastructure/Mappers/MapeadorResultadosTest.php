<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Infrastructure\Mappers;

use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorResultados;
use DateTimeZone;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Lectura de resultados literales con soportes originales.
 * CAPA: Infrastructure - Mappers (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Verifica estados separados, evaluabilidad, contexto y suficiencia.
 * ¿QUÉ INFORMACIÓN RECIBE? Filas sintéticas que declaran literalmente el contrato de Domain.
 * ¿QUÉ HACE? Reconstruye resultados y rechaza catálogos o referencias incompatibles.
 * ¿QUÉ DEVUELVE? Aserciones sobre el perfil y sus evidencias.
 * ¿QUÉ NO DEBE HACER? No declara que A/P esté instalado en D-137 ni valida clínicamente los estados.
 */
final class MapeadorResultadosTest extends TestCase
{
    /** Conserva los cinco estados sin índice ni inferencia desde evaluabilidad; sin parámetros ni excepciones esperadas. */
    public function test_reconstruye_cinco_dominios_con_sus_soportes_originales(): void
    {
        [$modelo, $memoria] = EscenarioMapeadores::resultados();
        $antes = $modelo->getAttributes();
        $vector = self::mapper()->aVector($modelo, $memoria);

        self::assertSame($memoria, $vector->memoria);
        self::assertSame(['A', 'P', 'U', 'I', 'X'], array_map(static fn ($r) => $r->estado->value, array_values($vector->resultados)));
        self::assertSame($memoria->evidencia('SIM-EV-COG-MEM-0'), $vector->resultado(DominioCognitivoEnum::MEMORIA)->evidencias[0]);
        self::assertSame(EstadoDominioEnum::DESCONOCIDO, $vector->resultado(DominioCognitivoEnum::FUNCION_EJECUTIVA)->estado);
        self::assertStringContainsString('SIM-RES-COG-MEM', $vector->resultado(DominioCognitivoEnum::MEMORIA)->justificacion);
        self::assertSame($antes, $modelo->getAttributes());
        self::assertFalse(property_exists($vector, 'puntuacionGlobal'));
    }

    /**
     * No convierte los literales descriptivos de G09 en alteración o preservación.
     *
     * @param  string  $codigo  Código semántico sin equivalencia literal en Domain.
     *
     * @throws DomainException Rechazo explícito esperado.
     */
    #[DataProvider('resultadosG09')]
    public function test_resultados_descriptivos_no_se_convierten_en_apx(string $codigo): void
    {
        [$modelo, $memoria] = EscenarioMapeadores::resultados();
        $fila = $modelo->getRelation('evaluacionCriterios')[0]->getRelation('resultado');
        $valor = $fila->getRelation('valor');
        $valor->setRawAttributes(array_replace($valor->getAttributes(), ['codigo_valor' => $codigo]));

        $this->expectException(DomainException::class);
        self::mapper()->aDominio($fila, $memoria);
    }

    /** @return iterable<string, array{string}> Literales del piloto descriptivo; sin parámetros ni excepciones. */
    public static function resultadosG09(): iterable
    {
        yield 'dificultad' => ['DIFICULTAD_EVIDENCIADA'];
        yield 'sin dificultad' => ['SIN_DIFICULTAD_EVIDENCIADA'];
        yield 'mixto' => ['HALLAZGOS_MIXTOS'];
    }

    /** @throws DomainException Rechazo esperado de una evaluación cargada que no coincide con la FK física. */
    public function test_lectura_individual_rechaza_criterio_de_otra_evaluacion(): void
    {
        [$modelo, $memoria] = EscenarioMapeadores::resultados();
        $criterio = $modelo->getRelation('evaluacionCriterios')[0];
        $criterio->setRawAttributes(array_replace($criterio->getAttributes(), ['cod_evaluacion_experta' => 'SIM-OTRA']));

        $this->expectException(DomainException::class);
        self::mapper()->aDominio($criterio->getRelation('resultado'), $memoria);
    }

    /**
     * Impide reconstruir conclusiones sin referencias y contexto compatibles.
     *
     * @param  string  $caso  Manipulación independiente de las filas.
     *
     * @throws DomainException Rechazo esperado sin cambiar Domain.
     */
    #[DataProvider('resultadosInvalidos')]
    public function test_rechaza_resultados_sin_contrato_coherente(string $caso): void
    {
        [$modelo, $memoria] = EscenarioMapeadores::resultados();
        $criterios = $modelo->getRelation('evaluacionCriterios');
        $ec = $criterios[0];
        $fila = $ec->getRelation('resultado');
        $vinculos = $ec->getRelation('evidenciasCriterioEvaluacion');
        switch ($caso) {
            case 'residente ajeno': $modelo->setRawAttributes(array_replace($modelo->getAttributes(), ['cod_residente' => 'SINTETICO-OTRO']));
                break;
            case 'versión ajena': $modelo->setRawAttributes(array_replace($modelo->getAttributes(), ['cod_version_modelo' => 'SIM-V2']));
                break;
            case 'modo asistencial': $modelo->setRawAttributes(array_replace($modelo->getAttributes(), ['origen_activacion' => 'ASISTENCIAL']));
                break;
            case 'catálogo ajeno': $fila->getRelation('valor')->getRelation('dominio')->setRawAttributes(array_replace($fila->getRelation('valor')->getRelation('dominio')->getAttributes(), ['cod_version_modelo' => 'SIM-V2']));
                break;
            case 'sin soportes A': $ec->setRelation('evidenciasCriterioEvaluacion', new Collection);
                break;
            case 'soporte contextual A': $vinculos[0]->setRawAttributes(array_replace($vinculos[0]->getAttributes(), ['rol_en_criterio' => 'CONTEXTUAL']));
                break;
            case 'rol no autorizado': $vinculos[0]->setRawAttributes(array_replace($vinculos[0]->getAttributes(), ['rol_en_criterio' => 'SIM-NO-AUTORIZADO']));
                break;
            case 'soporte duplicado': $ec->setRelation('evidenciasCriterioEvaluacion', new Collection([$vinculos[0], clone $vinculos[0]]));
                break;
            case 'soporte ajeno': $vinculos[0]->getRelation('evidencia')->setRawAttributes(array_replace($vinculos[0]->getRelation('evidencia')->getAttributes(), ['cod_evaluacion_experta' => 'SIM-OTRA']));
                break;
            case 'evaluabilidad de otro dominio': $ec->setRawAttributes(array_replace($ec->getAttributes(), ['estado_evaluabilidad' => 'EV-CA-2']));
                break;
            case 'modificador no representable': $ec->setRawAttributes(array_replace($ec->getAttributes(), ['codigo_modificador_interpretacion' => 'SIM-MODIFICADOR']));
                break;
            case 'fecha inválida': $fila->setRawAttributes(array_replace($fila->getAttributes(), ['fecha_hora_determinacion' => '2026-02-30 12:00:00']));
                break;
            case 'sin cargar valor': $fila->unsetRelation('valor');
                break;
            case 'dominio faltante': $modelo->setRelation('evaluacionCriterios', new Collection(array_slice($criterios->all(), 0, 4)));
                break;
            case 'dominio duplicado': $modelo->setRelation('evaluacionCriterios', new Collection([$criterios[0], $criterios[0], $criterios[2], $criterios[3], $criterios[4]]));
                break;
            case 'sin resultado': $ec->setRelation('resultado', null);
                break;
        }
        $this->expectException(DomainException::class);
        self::mapper()->aVector($modelo, $memoria);
    }

    /** @return iterable<string, array{string}> Incompatibilidades de reconstrucción; sin parámetros ni excepciones. */
    public static function resultadosInvalidos(): iterable
    {
        foreach (['residente ajeno', 'versión ajena', 'modo asistencial', 'catálogo ajeno', 'sin soportes A', 'soporte contextual A',
            'rol no autorizado', 'soporte duplicado', 'soporte ajeno', 'evaluabilidad de otro dominio', 'modificador no representable',
            'fecha inválida', 'sin cargar valor', 'dominio faltante', 'dominio duplicado', 'sin resultado'] as $caso) {
            yield $caso => [$caso];
        }
    }

    /** Mantiene desconocimiento con cero evidencias, sin inventar normalidad; sin parámetros ni excepciones esperadas. */
    public function test_perfil_desconocido_no_inventa_soportes_ni_resultados_favorables(): void
    {
        [$modelo, $memoria] = EscenarioMapeadores::resultados(['U', 'U', 'U', 'U', 'U']);
        $vector = self::mapper()->aVector($modelo, $memoria);

        foreach ($vector->resultados as $resultado) {
            self::assertSame(EstadoDominioEnum::DESCONOCIDO, $resultado->estado);
            self::assertSame([], $resultado->evidencias);
        }
    }

    /** Evita cambiar permisos de soporte mediante referencias PHP externas; sin parámetros ni excepciones esperadas. */
    public function test_copia_roles_del_contrato_sin_referencias_mutables(): void
    {
        $rol = 'SIM-SOPORTE';
        $mapper = new MapeadorResultados(new DateTimeZone('America/La_Paz'), [&$rol]);
        $rol = 'CONTEXTUAL';
        [$modelo, $memoria] = EscenarioMapeadores::resultados();

        self::assertCount(5, $mapper->aVector($modelo, $memoria)->resultados);
    }

    /** @throws DomainException Rechazo esperado de un contrato que atribuya soporte al contexto. */
    public function test_contextual_no_puede_autorizarse_como_rol_de_soporte(): void
    {
        $this->expectException(DomainException::class);
        new MapeadorResultados(new DateTimeZone('America/La_Paz'), ['CONTEXTUAL']);
    }

    /** @return MapeadorResultados Mapper con contrato de roles ficticio explícito; sin parámetros ni excepciones. */
    private static function mapper(): MapeadorResultados
    {
        return new MapeadorResultados(new DateTimeZone('America/La_Paz'), ['SIM-SOPORTE']);
    }
}
