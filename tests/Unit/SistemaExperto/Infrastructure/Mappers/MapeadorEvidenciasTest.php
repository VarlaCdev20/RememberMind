<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Infrastructure\Mappers;

use App\Models\AplicacionInstrumento;
use App\Models\SeguimientoPedagogico;
use App\Models\ValoracionFuncional;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorEvidencias;
use DateTimeZone;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;

/**
 * TÍTULO: Evidencias originales con contexto y fecha clínica.
 * CAPA: Infrastructure - Mappers (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Verifica conservación de tipos, fuentes, fechas y alcance.
 * ¿QUÉ INFORMACIÓN RECIBE? Filas Eloquent ficticias y valores del extractor declarados manualmente.
 * ¿QUÉ HACE? Convierte los modelos admitidos y prueba los rechazos del contrato.
 * ¿QUÉ DEVUELVE? Aserciones de procedencia, desconocimiento y descripción.
 * ¿QUÉ NO DEBE HACER? No extrae datos de BDD, interpreta instrumentos ni inventa IADL.
 */
final class MapeadorEvidenciasTest extends TestCase
{
    /**
     * Conserva cada valor tipado sin equiparar ausencia, false, cero y decimal.
     *
     * @param  string|int|float|bool|null  $valor  Dato declarado por el extractor de prueba.
     *                                             Sin excepciones esperadas con las referencias sintéticas.
     */
    #[DataProvider('valoresOriginales')]
    public function test_preserva_valor_y_fecha_del_original_sin_interpretar(string|int|float|bool|null $valor): void
    {
        [$fila, $original] = EscenarioMapeadores::evidencia();
        $antes = $original->getAttributes();
        $evidencia = self::mapper()->aDominio($fila, $original, F::fecha(), $valor);

        self::assertSame($valor, $evidencia->hecho->valor);
        self::assertSame('SIM-EVID', $evidencia->hecho->id);
        self::assertSame('SIM-CTRL', $evidencia->hecho->origenId);
        self::assertSame('SIM-AT', $evidencia->hecho->episodioId);
        self::assertSame('SIM-CONTROLES', $evidencia->hecho->fuenteId);
        self::assertSame('VAR-MEM-OBS-01', $evidencia->hecho->concepto);
        self::assertEquals(F::fecha(), $evidencia->hecho->fecha);
        self::assertSame($valor === null ? HallazgoInstrumentalEnum::DESCONOCIDO : HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO, $evidencia->hallazgo);
        self::assertSame(DominioCognitivoEnum::MEMORIA, $evidencia->dominio);
        self::assertSame([], $evidencia->referenciaSintetica);
        self::assertSame($antes, $original->getAttributes());
    }

    /** @return iterable<string, array{string|int|float|bool|null}> Literales originales; sin parámetros ni excepciones. */
    public static function valoresOriginales(): iterable
    {
        yield 'ausencia' => [null];
        yield 'booleano' => [false];
        yield 'entero' => [0];
        yield 'cadena' => ['0'];
        yield 'decimal' => ['12.30'];
        yield 'flotante cero original' => [0.0];
        yield 'texto que no acredita A' => ['ALTERACION_OBJETIVA_SIMULADA'];
    }

    /** Conserva el subcomponente expresamente vinculado, sin ampliarlo; sin parámetros ni excepciones esperadas. */
    public function test_propietario_documentado_conserva_alcance_de_subcomponente(): void
    {
        [$fila, $original] = EscenarioMapeadores::evidencia();
        $propietario = $fila->getRelation('mapeoVariableFuente')->getRelation('variable')->getRelation('propietarioPrimario');
        $propietario->setRawAttributes(array_replace($propietario->getAttributes(), ['codigo_semantico' => 'MEM-S01', 'tipo_nodo' => 'SUBCOMPONENTE']));

        $evidencia = self::mapper()->aDominio($fila, $original, F::fecha(), 'OBSERVACION_FICTICIA');

        self::assertSame(SubcomponenteCognitivoEnum::MEM_APRENDIZAJE_ADQUISICION, $evidencia->subcomponente);
        self::assertSame(HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO, $evidencia->hallazgo);
    }

    /**
     * Conserva atención/actividad originales de las demás fuentes soportadas.
     *
     * @param  class-string  $tipo  Modelo operativo existente.
     * @param  string  $pk  PK física.
     * @param  array<string, string>  $contexto  Episodio e instrumento originales.
     *                                           Sin excepciones esperadas.
     */
    #[DataProvider('fuentesOriginales')]
    public function test_modelos_operativos_preservan_episodio_sin_crear_iadl(string $tipo, string $pk, array $contexto): void
    {
        [$fila] = EscenarioMapeadores::evidencia();
        $original = EscenarioMapeadores::modelo($tipo, [$pk => 'SIM-ORIGINAL', 'cod_residente' => 'SINTETICO-RES',
            'fecha_hora' => '2026-10-17 12:00:00', 'estado' => $tipo === AplicacionInstrumento::class ? 'COMPLETA' : 'VIGENTE'] + $contexto);
        $fila->setRawAttributes(array_replace($fila->getAttributes(), ['cod_registro_fuente' => 'SIM-ORIGINAL']));
        $fuente = $fila->getRelation('mapeoVariableFuente')->getRelation('fuente');
        $fuente->setRawAttributes(array_replace($fuente->getAttributes(), ['tabla_raiz' => $original->getTable(), 'campo_pk_raiz' => $pk]));

        $hecho = self::mapper()->aHecho($fila, $original, F::fecha(), null);

        self::assertSame('SIM-ORIGINAL', $hecho->origenId);
        self::assertSame($contexto['cod_atencion'] ?? $contexto['cod_actividad'], $hecho->episodioId);
        self::assertNull($hecho->valor);
    }

    /** @return iterable<string, array{class-string, string, array<string, string>}> Fuentes físicas; sin parámetros ni excepciones. */
    public static function fuentesOriginales(): iterable
    {
        yield 'BADL' => [ValoracionFuncional::class, 'cod_valoracion_funcional', ['cod_atencion' => 'SIM-AT-FUNC']];
        yield 'pedagogía' => [SeguimientoPedagogico::class, 'cod_seguimiento_pedagogico', ['cod_actividad' => 'SIM-ACT']];
        yield 'instrumento' => [AplicacionInstrumento::class, 'cod_aplicacion', ['cod_atencion' => 'SIM-AT-INST', 'cod_instrumento' => 'SIM-INST']];
    }

    /**
     * Rechaza procedencia, identidad o fechas que no acreditan el contexto original.
     *
     * @param  string  $caso  Manipulación independiente del escenario.
     *
     * @throws DomainException Rechazo esperado sin inventar datos sustitutos.
     */
    #[DataProvider('evidenciasInvalidas')]
    public function test_rechaza_evidencia_ajena_o_incompleta(string $caso): void
    {
        [$fila, $original] = EscenarioMapeadores::evidencia();
        $mapeo = $fila->getRelation('mapeoVariableFuente');
        $fuente = $mapeo->getRelation('fuente');
        switch ($caso) {
            case 'otro residente': $original->setRawAttributes(array_replace($original->getAttributes(), ['cod_residente' => 'SINTETICO-OTRO']));
                break;
            case 'evaluación ajena': $fila->setRawAttributes(array_replace($fila->getAttributes(), ['cod_evaluacion_experta' => 'SIM-OTRA']));
                break;
            case 'caso real': $fila->getRelation('evaluacion')->setRawAttributes(array_replace($fila->getRelation('evaluacion')->getAttributes(), ['cod_residente' => 'RES-REAL']));
                break;
            case 'mapeo de otra versión': $mapeo->setRawAttributes(array_replace($mapeo->getAttributes(), ['cod_version_modelo' => 'SIM-V2']));
                break;
            case 'fuente inactiva': $fuente->setRawAttributes(array_replace($fuente->getAttributes(), ['estado' => 'INACTIVO']));
                break;
            case 'tabla incorrecta': $fuente->setRawAttributes(array_replace($fuente->getAttributes(), ['tabla_raiz' => 'usuarios']));
                break;
            case 'fecha futura': $original->setRawAttributes(array_replace($original->getAttributes(), ['fecha_hora' => '2026-10-17 12:00:01']));
                break;
            case 'fecha inválida': $original->setRawAttributes(array_replace($original->getAttributes(), ['fecha_hora' => '2026-02-30 12:00:00']));
                break;
            case 'sin fecha original': $original->setRawAttributes(array_replace($original->getAttributes(), ['fecha_hora' => null]));
                break;
            case 'sin episodio': $original->setRawAttributes(array_replace($original->getAttributes(), ['cod_atencion' => null]));
                break;
            case 'anulada': $original->setRawAttributes(array_replace($original->getAttributes(), ['estado' => 'ANULADA']));
                break;
            case 'sin cargar fuente': $mapeo->unsetRelation('fuente');
                break;
            case 'propietario no cognitivo': $mapeo->getRelation('variable')->getRelation('propietarioPrimario')->setRawAttributes(array_replace($mapeo->getRelation('variable')->getRelation('propietarioPrimario')->getAttributes(), ['codigo_semantico' => 'BL-LON']));
                break;
        }
        $this->expectException(DomainException::class);
        self::mapper()->aDominio($fila, $original, F::fecha(), false);
    }

    /** @return iterable<string, array{string}> Casos incompatibles; sin parámetros ni excepciones. */
    public static function evidenciasInvalidas(): iterable
    {
        foreach (['otro residente', 'evaluación ajena', 'caso real', 'mapeo de otra versión', 'fuente inactiva',
            'tabla incorrecta', 'fecha futura', 'fecha inválida', 'sin fecha original', 'sin episodio', 'anulada',
            'sin cargar fuente', 'propietario no cognitivo'] as $caso) {
            yield $caso => [$caso];
        }
    }

    /** @throws DomainException Rechazo esperado de un registro fuente duplicado aunque cambie su PK de evidencia. */
    public function test_no_fragmenta_un_registro_para_contarlo_como_fuentes_independientes(): void
    {
        [$fila, $original] = EscenarioMapeadores::evidencia();
        $otra = clone $fila;
        $otra->setRawAttributes(array_replace($otra->getAttributes(), ['cod_evidencia_evaluacion' => 'SIM-EVID-2']));
        $a = self::mapper()->aHecho($fila, $original, F::fecha(), false);
        $b = self::mapper()->aHecho($otra, $original, F::fecha(), false);
        self::assertSame($a->claveOrigen(), $b->claveOrigen());

        $this->expectException(DomainException::class);
        new MemoriaTrabajo('SIM-EVAL', 'SINTETICO-RES', 'SIM-V1', F::fecha(), [$a, $b]);
    }

    /** @return MapeadorEvidencias Mapper con zona de almacenamiento explícita; sin parámetros ni excepciones. */
    private static function mapper(): MapeadorEvidencias
    {
        return new MapeadorEvidencias(new DateTimeZone('America/La_Paz'));
    }
}
