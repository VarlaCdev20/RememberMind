<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\ValueObjects\Cognicion;

use MotorExperto\dominio\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\EvaluabilidadEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use TypeError;
use ValueError;
use PHPUnit\Framework\TestCase;

/** Verificar los vocabularios y límites de la Fase A sin implementar inferencias. */
final class VocabularioCognicionTest extends TestCase
{
    /**
     * Comprueba códigos de dominio y estados sin introducir un sexto eje.
     * @return void
     */
    public function test_cinco_dominios_y_cinco_estados_documentales(): void
    {
        self::assertSame(['COG-MEM', 'COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], array_column(DominioCognitivoEnum::cases(), 'value'));
        self::assertSame(['A', 'P', 'U', 'I', 'X'], array_column(EstadoDominioEnum::cases(), 'value'));
        self::assertNull(DominioCognitivoEnum::tryFrom('COG-ORI'));
        self::assertNull(DominioCognitivoEnum::tryFrom('COG-VEL'));
        self::assertNull(EstadoDominioEnum::tryFrom('EV-CM-2'));
        self::assertNull(EstadoDominioEnum::tryFrom(HallazgoInstrumentalEnum::HALLAZGO_DESCRIPTIVO->value));
    }

    /**
     * Verifica la pertenencia primaria de cada uno de los 19 subcomponentes.
     * @param string $codigo
     * @param string $dominio
     * @return void
     */
    #[DataProvider('subcomponentes')]
    public function test_subcomponente_conserva_su_dominio_primario(string $codigo, string $dominio): void
    {
        self::assertSame($dominio, SubcomponenteCognitivoEnum::from($codigo)->dominio()->value);
    }

    /**
     * Proporciona las 19 correspondencias del catálogo documental.
     * @return iterable<string, array{string, string}>
     */
    public static function subcomponentes(): iterable
    {
        foreach ([
            'MEM-S01' => 'COG-MEM', 'MEM-S02' => 'COG-MEM',
            'MEM-S03' => 'COG-MEM', 'MEM-S04' => 'COG-MEM',
            'ATE-S01' => 'COG-ATE', 'ATE-S02' => 'COG-ATE', 'ATE-S03' => 'COG-ATE',
            'EJE-S01' => 'COG-EJE', 'EJE-S02' => 'COG-EJE', 'EJE-S03' => 'COG-EJE',
            'EJE-S04' => 'COG-EJE', 'EJE-S05' => 'COG-EJE',
            'LEN-S01' => 'COG-LEN', 'LEN-S02' => 'COG-LEN', 'LEN-S03' => 'COG-LEN',
            'LEN-S04' => 'COG-LEN', 'LEN-S05' => 'COG-LEN',
            'VIS-S01' => 'COG-VIS', 'VIS-S02' => 'COG-VIS',
        ] as $codigo => $dominio) {
            yield $codigo => [$codigo, $dominio];
        }
    }

    /**
     * Comprueba las quince combinaciones de nivel y prefijo documental.
     * @param string $dominio
     * @param int $nivel
     * @param string $esperado
     * @return void
     */
    #[DataProvider('codigosEvaluabilidad')]
    public function test_codigo_evaluabilidad_respeta_dominio(string $dominio, int $nivel, string $esperado): void
    {
        self::assertSame($esperado, EvaluabilidadEnum::from($nivel)->codigo(DominioCognitivoEnum::from($dominio)));
    }

    /**
     * Declara ejemplos completos sin depender del mapeo implementado.
     * @return iterable<string, array{string, int, string}>
     */
    public static function codigosEvaluabilidad(): iterable
    {
        foreach ([
            ['COG-MEM', 0, 'EV-CM-0'], ['COG-MEM', 1, 'EV-CM-1'], ['COG-MEM', 2, 'EV-CM-2'],
            ['COG-ATE', 0, 'EV-CA-0'], ['COG-ATE', 1, 'EV-CA-1'], ['COG-ATE', 2, 'EV-CA-2'],
            ['COG-EJE', 0, 'EV-CE-0'], ['COG-EJE', 1, 'EV-CE-1'], ['COG-EJE', 2, 'EV-CE-2'],
            ['COG-LEN', 0, 'EV-CL-0'], ['COG-LEN', 1, 'EV-CL-1'], ['COG-LEN', 2, 'EV-CL-2'],
            ['COG-VIS', 0, 'EV-CV-0'], ['COG-VIS', 1, 'EV-CV-1'], ['COG-VIS', 2, 'EV-CV-2'],
        ] as $caso) {
            yield $caso[2] => $caso;
        }
    }

    /**
     * Demuestra que el nivel 1 nombra señal, sin exigir una referencia basal.
     * @return void
     */
    public function test_niveles_no_son_resultados_ni_requisitos_de_linea_basal(): void
    {
        self::assertSame(EvaluabilidadEnum::PRE_EVALUACION_SENAL, EvaluabilidadEnum::from(1));
        self::assertSame('EV-CM-2', EvaluabilidadEnum::EVALUACION_INTEGRADA->codigo(DominioCognitivoEnum::MEMORIA));
        self::assertNull(EstadoDominioEnum::tryFrom('2'));
        self::assertNull(SubcomponenteCognitivoEnum::tryFrom('EJE-S06'));
        self::assertCount(19, SubcomponenteCognitivoEnum::cases());
    }

    /**
     * Comprueba que un nivel fuera de contrato falla, sin redondeo ni default.
     * @return void
     */
    public function test_rechaza_nivel_no_documentado(): void
    {
        $this->expectException(ValueError::class);
        EvaluabilidadEnum::from(3);
    }

    /**
     * Comprueba que la ausencia no se convierte en el nivel cero.
     * @return void
     */
    public function test_null_no_se_convierte_en_nivel_cero(): void
    {
        $this->expectException(TypeError::class);
        EvaluabilidadEnum::from(null);
    }

    /**
     * Comprueba que la ausencia no se convierte en rendimiento esperado.
     * @return void
     */
    public function test_null_no_se_convierte_en_rendimiento_esperado(): void
    {
        $this->expectException(TypeError::class);
        EstadoDominioEnum::from(null);
    }

}
