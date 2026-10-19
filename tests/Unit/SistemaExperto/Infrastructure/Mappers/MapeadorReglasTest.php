<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Infrastructure\Mappers;

use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorReglas;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Conversión de reglas D-130 sin ejecución.
 * CAPA: Infrastructure - Mappers (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Comprueba conjunción positiva, referencias y habilitación original.
 * ¿QUÉ INFORMACIÓN RECIBE? Modelos sintéticos normalizados con sus dependencias.
 * ¿QUÉ HACE? Mapea conocimiento y prueba rechazos estructurales.
 * ¿QUÉ DEVUELVE? Aserciones de conservación y fallo explícito.
 * ¿QUÉ NO DEBE HACER? No evalúa condiciones ni activa catálogos.
 */
final class MapeadorReglasTest extends TestCase
{
    /** Conserva PK, códigos y la cadena cero sin ejecutarlos; sin parámetros ni excepciones esperadas. */
    public function test_traduce_conjuncion_positiva_y_consecuencia_sin_coercion(): void
    {
        $modelo = EscenarioMapeadores::regla();
        $original = $modelo->getAttributes();
        $regla = (new MapeadorReglas('RESULTADO'))->aDominio($modelo);

        self::assertSame('SIM-R', $regla->id);
        self::assertSame('SIM-V1', $regla->versionConocimiento);
        self::assertTrue($regla->habilitada);
        self::assertSame(OperadorCondicionEnum::Y, $regla->condiciones['operador']);
        self::assertCount(2, $regla->condiciones['condiciones']);
        self::assertSame(['operador' => OperadorCondicionEnum::IGUAL_A, 'variable' => 'VAR-SIM-1', 'valor' => '0'],
            $regla->condiciones['condiciones'][0]);
        self::assertSame(['concepto' => 'COG-MEM', 'valor' => 'DIFICULTAD_EVIDENCIADA'], $regla->consecuencia);
        self::assertSame('reglas_expertas:SIM-R', $regla->fuente);
        self::assertSame($original, $modelo->getAttributes());
    }

    /** Conserva inhabilitación documental, sin promover la versión; sin parámetros ni excepciones esperadas. */
    public function test_regla_inactiva_permanece_inhabilitada(): void
    {
        $modelo = EscenarioMapeadores::regla();
        $modelo->setRawAttributes(array_replace($modelo->getAttributes(), ['estado' => 'INACTIVO']));
        $version = $modelo->getRelation('version');
        $version->setRawAttributes(array_replace($version->getAttributes(), ['estado' => 'INACTIVO']));

        self::assertFalse((new MapeadorReglas('RESULTADO'))->aDominio($modelo)->habilitada);
        self::assertSame('INACTIVO', $modelo->getAttributes()['estado']);
    }

    /**
     * Rechaza conocimiento que no puede transformarse sin alterar el contrato.
     *
     * @param  string  $caso  Manipulación de una dependencia del escenario.
     *
     * @throws DomainException Rechazo explícito esperado.
     */
    #[DataProvider('reglasInvalidas')]
    public function test_rechaza_regla_incoherente_o_no_representable(string $caso): void
    {
        $regla = EscenarioMapeadores::regla();
        $condiciones = $regla->getRelation('condicionesReglaExperta');
        $consecuencia = $regla->getRelation('consecuenciasReglaExperta')[0];
        $valor = $condiciones[0]->getRelation('valor');
        switch ($caso) {
            case 'sin condiciones': $regla->setRelation('condicionesReglaExperta', new Collection);
                break;
            case 'sin consecuencia': $regla->setRelation('consecuenciasReglaExperta', new Collection);
                break;
            case 'varias consecuencias': $regla->setRelation('consecuenciasReglaExperta', new Collection([$consecuencia, clone $consecuencia]));
                break;
            case 'condición ajena': $condiciones[0]->setRawAttributes(array_replace($condiciones[0]->getAttributes(), ['cod_regla_experta' => 'SIM-OTRA']));
                break;
            case 'condición duplicada': $regla->setRelation('condicionesReglaExperta', new Collection([$condiciones[0], clone $condiciones[0]]));
                break;
            case 'versión del valor': $valor->getRelation('dominio')->setRawAttributes(['cod_dominio_valores' => 'SIM-DV-VAR', 'cod_version_modelo' => 'SIM-V2', 'estado' => 'ACTIVO']);
                break;
            case 'valor pendiente': $valor->setRawAttributes(array_replace($valor->getAttributes(), ['estado_aprobacion' => 'PENDIENTE']));
                break;
            case 'tipo de regla': $regla->setRawAttributes(array_replace($regla->getAttributes(), ['tipo_regla' => 'OTRO']));
                break;
            case 'versión inactiva': $regla->getRelation('version')->setRawAttributes(['cod_version_modelo' => 'SIM-V1', 'estado' => 'INACTIVO']);
                break;
            case 'relación sin cargar': $condiciones[0]->unsetRelation('variable');
                break;
            case 'consecuencia reservada': $consecuencia->getRelation('valor')->setRawAttributes(array_replace($consecuencia->getRelation('valor')->getAttributes(), ['codigo_valor' => 'HALLAZGOS_MIXTOS']));
                break;
            case 'dominio ajeno': $consecuencia->getRelation('criterioDominioResultado')->setRawAttributes(array_replace($consecuencia->getRelation('criterioDominioResultado')->getAttributes(), ['cod_dominio_valores' => 'SIM-OTRO']));
                break;
        }
        $this->expectException(DomainException::class);
        (new MapeadorReglas('RESULTADO'))->aDominio($regla);
    }

    /** @return iterable<string, array{string}> Errores de contrato; sin parámetros ni excepciones. */
    public static function reglasInvalidas(): iterable
    {
        foreach (['sin condiciones', 'sin consecuencia', 'varias consecuencias', 'condición ajena',
            'condición duplicada', 'versión del valor', 'valor pendiente', 'tipo de regla', 'versión inactiva',
            'relación sin cargar', 'consecuencia reservada', 'dominio ajeno'] as $caso) {
            yield $caso => [$caso];
        }
    }
}
