<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\dominio\instrumentos\CircuitoInstrumentalInicial as C;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CircuitoInstrumentalInicialTest extends TestCase
{
    public static function captura(string $metodo, bool $maximos = false): array
    {
        $d = (new C)->definiciones()[$metodo];
        $r = $d['identidad'] + ['protocolo' => $metodo];
        foreach ($d['campos'] as $nombre => $meta) {
            $r[$nombre] = $meta['tipo'] === 'LOGICO' ? true : ($maximos ? $meta['maximo'] : 0);
        }
        // Las claves no se cuentan otra vez cuando todos los recuerdos fueron libres.
        if ($metodo === C::MOCA && $maximos) {
            $r['recuerdo_categoria'] = $r['recuerdo_eleccion'] = 0;
        }

        return $r;
    }

    public static function metodos(): array
    {
        return [[C::MOCA], [C::RUDAS], [C::FAQ]];
    }

    #[DataProvider('metodos')]
    public function test_maximos_y_ausencias_no_crean_dominios_ni_diagnosticos(string $metodo): void
    {
        $r = (new RegistroMetodosInstrumentales)->ejecutar($metodo, self::captura($metodo, true));
        self::assertSame(30, $r['resumen_inicial']['total']);
        self::assertSame([], $r['componentes']);
        self::assertNull($r['diagnostico']);
        self::assertSame([], $r['resumen_inicial']['invalidos']);
        $d = (new C)->definiciones()[$metodo];
        $campo = array_key_first(array_filter($d['campos'], static fn ($m) => $m['puntuable']));
        $r = (new C)->calcular($metodo, array_replace(self::captura($metodo), [$campo => null]));
        self::assertNull($r['resumen_inicial']['total']);
        self::assertContains($campo, $r['resumen_inicial']['ausentes']);
    }

    public function test_moca_separa_aprendizaje_ajuste_y_mis_sin_doble_conteo(): void
    {
        $c = array_replace(self::captura(C::MOCA), ['recuerdo_libre' => 2, 'recuerdo_categoria' => 2,
            'recuerdo_eleccion' => 1, 'aprendizaje_1' => 5, 'aprendizaje_2' => 5]);
        $r = (new C)->calcular(C::MOCA, $c)['resumen_inicial'];
        self::assertSame(2, $r['total_bruto']);
        self::assertSame(3, $r['total']);
        self::assertSame(11, $r['indice_memoria']);
        $r = (new C)->calcular(C::MOCA, array_replace($c, ['ajuste_educativo' => null]))['resumen_inicial'];
        self::assertSame(2, $r['total_bruto']);
        self::assertNull($r['total']);
        self::assertContains('AJUSTE_EDUCATIVO_SIN_CONFIRMAR', $r['motivos']);
        $r = (new C)->calcular(C::MOCA, array_replace($c, ['recuerdo_categoria' => 4]))['resumen_inicial'];
        self::assertContains('RECUERDOS_SOLAPADOS', $r['invalidos']);
        self::assertNull($r['indice_memoria']);
        self::assertNull($r['total']);
    }

    public function test_rudas_sin_registro_no_asume_administracion_y_recuerdo_es_par(): void
    {
        $r = (new C)->calcular(C::RUDAS, array_replace(self::captura(C::RUDAS), ['registro_aprendizaje' => false]))['resumen_inicial'];
        self::assertSame(0, $r['total_bruto']);
        self::assertNull($r['total']);
        self::assertContains('REGISTRO_INICIAL_NO_CONFIRMADO', $r['motivos']);
        $r = (new C)->calcular(C::RUDAS, array_replace(self::captura(C::RUDAS), ['recuerdo' => 3]))['resumen_inicial'];
        self::assertContains('recuerdo', $r['invalidos']);
        self::assertNull($r['total']);
    }

    public function test_faq_no_aplicable_no_es_cero_ni_suma_ni_prorrateo(): void
    {
        $r = (new C)->calcular(C::FAQ, self::captura(C::FAQ))['resumen_inicial'];
        self::assertSame(0, $r['total']);
        $r = (new C)->calcular(C::FAQ, array_replace(self::captura(C::FAQ), ['actividad_1' => 8]))['resumen_inicial'];
        self::assertSame(['actividad_1'], $r['no_aplicables']);
        self::assertNull($r['total_bruto']);
        self::assertNull($r['total']);
        $r = (new C)->calcular(C::FAQ, array_replace(self::captura(C::FAQ), ['informante_participo' => false]))['resumen_inicial'];
        self::assertNull($r['total']);
        self::assertContains('INFORMANTE_NO_CONFIRMADO', $r['motivos']);
    }

    public static function invalidos(): array
    {
        return [[C::MOCA, 'copia', 2], [C::MOCA, 'copia', 0.5], [C::RUDAS, 'praxias', -1],
            [C::RUDAS, 'lenguaje', INF], [C::FAQ, 'actividad_1', 9], [C::FAQ, 'actividad_1', true],
            [C::MOCA, 'ajuste_educativo', 'false'], [C::RUDAS, 'idioma', 'en']];
    }

    #[DataProvider('invalidos')]
    public function test_datos_invalidos_o_identidad_distinta_no_producen_total(string $metodo, string $campo, mixed $valor): void
    {
        $r = (new C)->calcular($metodo, array_replace(self::captura($metodo), [$campo => $valor]))['resumen_inicial'];
        self::assertNotEmpty($r['invalidos']);
        self::assertNull($r['total']);
    }
}
