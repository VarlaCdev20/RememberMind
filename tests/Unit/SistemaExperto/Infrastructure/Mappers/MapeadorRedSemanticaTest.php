<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Infrastructure\Mappers;

use MotorExperto\dominio\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorRedSemantica;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Verifica PK, códigos, dirección de aristas y rechazos. */
final class MapeadorRedSemanticaTest extends TestCase
{
    /** Conserva las PK, códigos y extremos sin inventar la inversa */
    public function test_reconstruye_nodos_y_aristas_con_identidad_compartida(): void
    {
        $modelo = EscenarioMapeadores::red();
        $antes = $modelo->getAttributes();
        $red = (new MapeadorRedSemantica)->aDominio($modelo);

        self::assertSame('SIM-V1', $red->versionConocimiento);
        self::assertSame('MEM-S01', $red->nodo('SIM-N-SUB')->codigo);
        self::assertSame('COG-MEM', $red->nodo('SIM-N-MEM')->codigo);
        self::assertSame($red->nodo('SIM-N-SUB'), $red->relaciones['SIM-REL']->origen);
        self::assertSame($red->nodo('SIM-N-MEM'), $red->relaciones['SIM-REL']->destino);
        self::assertSame(TipoRelacionSemanticaEnum::SUBCOMPONENTE_DE, $red->relaciones['SIM-REL']->tipo);
        self::assertSame([], $red->salientes('SIM-N-MEM'));
        self::assertSame($antes, $modelo->getAttributes());
        self::assertFalse($modelo->exists);
    }

    /**
     * Rechaza entradas cuya semántica o identidad no puede conservar Domain.
     * @param string $caso
     * @throws DomainException
     */
    #[DataProvider('grafosInvalidos')]
    public function test_rechaza_grafos_incompatibles(string $caso): void
    {
        $version = EscenarioMapeadores::red();
        $nodos = $version->getRelation('nodosSemanticos');
        $aristas = $version->getRelation('relacionesSemanticas');
        switch ($caso) {
            case 'nodo ajeno':
                $nodos[0]->setRawAttributes(array_replace($nodos[0]->getAttributes(), ['cod_version_modelo' => 'SIM-V2']));
                break;
            case 'extremo ausente':
                $aristas[0]->setRawAttributes(array_replace($aristas[0]->getAttributes(), ['cod_nodo_origen' => 'SIM-AUSENTE']));
                break;
            case 'arista ajena':
                $aristas[0]->setRawAttributes(array_replace($aristas[0]->getAttributes(), ['cod_version_modelo' => 'SIM-V2']));
                break;
            case 'predicado desconocido':
                $aristas[0]->setRawAttributes(array_replace($aristas[0]->getAttributes(), ['tipo_relacion' => 'IS_A']));
                break;
            case 'modalidad no representable':
                $aristas[0]->setRawAttributes(array_replace($aristas[0]->getAttributes(), ['modalidad_relacion' => 'CANDIDATA']));
                break;
            case 'nodo inactivo':
                $nodos[0]->setRawAttributes(array_replace($nodos[0]->getAttributes(), ['estado' => 'INACTIVO']));
                break;
            case 'nodo duplicado':
                $version->setRelation('nodosSemanticos', new Collection([$nodos[0], $nodos[0]]));
                break;
            case 'arista duplicada':
                $version->setRelation('relacionesSemanticas', new Collection([$aristas[0], $aristas[0]]));
                break;
            case 'colección no cargada':
                $version->unsetRelation('nodosSemanticos');
                break;
        }
        $this->expectException(DomainException::class);
        (new MapeadorRedSemantica)->aDominio($version);
    }

    /** @return iterable<string, array{string}> */
    public static function grafosInvalidos(): iterable
    {
        foreach (['nodo ajeno', 'extremo ausente', 'arista ajena', 'predicado desconocido', 'modalidad no representable',
            'nodo inactivo', 'nodo duplicado', 'arista duplicada', 'colección no cargada'] as $caso) {
            yield $caso => [$caso];
        }
    }

    /**
     * Impide convertir tipos falsy en códigos o textos de un nodo.
     * @param mixed $valor
     * @throws DomainException
     */
    #[DataProvider('codigosInvalidos')]
    public function test_no_convierte_null_false_o_cero_en_codigo_semantico(mixed $valor): void
    {
        $nodo = EscenarioMapeadores::nodo('SIM-N', 'COG-MEM');
        $nodo->setRawAttributes(array_replace($nodo->getAttributes(), ['codigo_semantico' => $valor]));
        $this->expectException(DomainException::class);
        (new MapeadorRedSemantica)->aNodo($nodo);
    }

    /** @return iterable<string, array{mixed}> */
    public static function codigosInvalidos(): iterable
    {
        yield 'null' => [null];
        yield 'false' => [false];
        yield 'entero' => [0];
        yield 'vacío' => [''];
    }
}
