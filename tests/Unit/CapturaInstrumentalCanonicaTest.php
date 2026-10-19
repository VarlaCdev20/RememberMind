<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CapturaInstrumentalCanonicaTest extends TestCase
{
    public static function decimales(): array
    {
        return [[0, 2, '0.00'], ['000.00', 2, '0.00'], ['-0.0000', 4, '0.0000'],
            ['1234.5678', 4, '1234.5678'], [1234.5678, 4, '1234.5678'], ['2.10000', 4, '2.1000']];
    }

    #[DataProvider('decimales')]
    public function test_decimal_sql_conserva_escala_sin_float_en_dto(int|float|string $original, int $escala, string $esperado): void
    {
        self::assertSame($esperado, C::decimal($original, $escala, 8));
    }

    public static function invalidos(): array
    {
        return [[0.12345], ['0.12345'], [INF], ['1e2'], ['999999999.0000']];
    }

    #[DataProvider('invalidos')]
    public function test_rechaza_perdida_y_precision_ajena(mixed $valor): void
    {
        $this->expectException(DomainException::class);
        C::decimal($valor, 4, 8);
    }

    public function test_orden_de_mapas_no_altera_huella_pero_null_false_cero_y_ausencia_si(): void
    {
        self::assertSame(CodecInstantanea::huella(C::ordenar(['b' => 1, 'a' => ['z' => 2, 'x' => 3]])),
            CodecInstantanea::huella(C::ordenar(['a' => ['x' => 3, 'z' => 2], 'b' => 1])));
        $huellas = array_map(static fn ($v) => CodecInstantanea::huella(C::ordenar($v)), [[], ['a' => null], ['a' => false], ['a' => 0], ['a' => '0']]);
        self::assertCount(5, array_unique($huellas));
        self::assertNotSame(CodecInstantanea::huella(C::ordenar(['a', 'b'])), CodecInstantanea::huella(C::ordenar(['b', 'a'])));
    }

    public function test_materializacion_sqlite_y_postgres_producen_el_mismo_original_canonico(): void
    {
        $a = ['cod_aplicacion' => 'SIM-APP', 'fecha_hora' => '2026-10-18T09:00:00.000000-04:00',
            'instrumento_actual' => ['cod_instrumento' => 'SIM-I', 'puntaje_maximo' => 10],
            'aplicacion_actual' => ['puntaje_total' => 0], 'vinculos_preguntas_actuales' => [],
            'extraccion' => null, 'respuestas_originales' => [['cod_respuesta' => 'SIM-R', 'valor_numero' => 1234.5678,
                'valor_logico' => 0, 'puntaje' => 2, 'pregunta_actual' => ['orden' => 1], 'opcion_actual' => null]]];
        $pg = $a;
        $pg['instrumento_actual']['puntaje_maximo'] = '10.00';
        $pg['aplicacion_actual']['puntaje_total'] = '0.00';
        $pg['respuestas_originales'][0]['valor_numero'] = '1234.5678';
        $pg['respuestas_originales'][0]['valor_logico'] = false;
        $pg['respuestas_originales'][0]['puntaje'] = '2.00';
        $pg['respuestas_originales'][0]['pregunta_actual']['orden'] = '1';
        $fuente = ['disponible' => true, 'fecha_corte' => '2026-10-19T12:00:00.123456-04:00', 'capturas' => [$a]];
        $otra = array_replace($fuente, ['capturas' => [$pg]]);
        self::assertSame((new C)->construir($fuente), (new C)->construir($otra));
    }
}
