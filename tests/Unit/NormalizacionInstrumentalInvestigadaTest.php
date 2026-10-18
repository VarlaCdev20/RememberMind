<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use MotorExperto\dominio\instrumentos\NormalizadorHvltBolivia2015 as Hvlt;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NormalizacionInstrumentalInvestigadaTest extends TestCase
{
    private function captura(): array
    {
        return ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es', 'protocolo' => Hvlt::METODO,
            'espanol_nativo' => true, 'nacimiento_bolivia' => true, 'residencia_bolivia' => true,
            'lectoescritura' => true, 'administracion_estandarizada' => true,
            'edad_anios' => 70, 'escolaridad_anios' => 4, 'ensayos' => [6, 7, 8],
            'recuerdo_diferido' => 4, 'demora_minutos' => 22];
    }

    public function test_aplica_las_dos_regresiones_publicadas_sin_inventar_diagnostico(): void
    {
        $r = (new RegistroMetodosInstrumentales)->ejecutar(Hvlt::METODO, $this->captura());
        self::assertSame(21, $r['componentes']['MEM-S01']['puntuacion_bruta']);
        self::assertEqualsWithDelta(16.713, $r['componentes']['MEM-S01']['esperado'], 0.0000001);
        self::assertEqualsWithDelta(1.01756468075, $r['componentes']['MEM-S01']['z'], 0.0000001);
        self::assertEqualsWithDelta(5.222, $r['componentes']['MEM-S02']['esperado'], 0.0000001);
        self::assertEqualsWithDelta(-0.50412541254, $r['componentes']['MEM-S02']['z'], 0.0000001);
        self::assertNull($r['diagnostico']);
        self::assertNull($r['componentes']['MEM-S01']['clasificacion_clinica']);
        self::assertSame('PENDIENTE', $r['revision_profesional']);
        self::assertArrayNotHasKey('MEM-S03', $r['componentes']);
        self::assertArrayNotHasKey('MEM-S04', $r['componentes']);
        self::assertSame(70, $r['contexto_aplicacion']['edad_anios']);
        self::assertSame(4, $r['contexto_aplicacion']['escolaridad_anios']);
    }

    public function test_educacion_mayor_de_doce_afecta_total_y_no_el_modelo_boliviano_diferido(): void
    {
        $a = (new Hvlt)->normalizar($this->captura());
        $b = (new Hvlt)->normalizar(array_replace($this->captura(), ['escolaridad_anios' => 13]));
        self::assertEqualsWithDelta(3.264, $b['componentes']['MEM-S01']['esperado'] - $a['componentes']['MEM-S01']['esperado'], 0.0000001);
        self::assertSame($a['componentes']['MEM-S02']['esperado'], $b['componentes']['MEM-S02']['esperado']);
    }

    public static function incompatibles(): array
    {
        return [['forma', 1], ['idioma', 'ay'], ['edad_anios', 17], ['edad_anios', 96],
            ['escolaridad_anios', 0], ['lectoescritura', false], ['espanol_nativo', null],
            ['nacimiento_bolivia', null], ['residencia_bolivia', false], ['administracion_estandarizada', false]];
    }

    #[DataProvider('incompatibles')]
    public function test_no_extrapola_ni_completa_contexto_ausente(string $campo, mixed $valor): void
    {
        $captura = array_replace($this->captura(), [$campo => $valor]);
        $r = (new Hvlt)->normalizar($captura);
        foreach ($r['componentes'] as $c) {
            self::assertSame('NO_NORMALIZABLE', $c['estado']);
            self::assertNotEmpty($c['motivos']);
            self::assertNull($c['z']);
        }
        self::assertSame($captura['ensayos'], $r['bruto']['ensayos']);
        self::assertSame($valor, $r['contexto_aplicacion'][$campo]);
    }

    public function test_valor_nominal_no_se_convierte_en_ensayos_y_dato_nulo_no_es_cero(): void
    {
        foreach (['DIFICULTAD', true, null, [1, 2, 13], ['1', '2', '3']] as $ensayos) {
            $r = (new Hvlt)->normalizar(array_replace($this->captura(), ['ensayos' => $ensayos]));
            self::assertNull($r['componentes']['MEM-S01']['z']);
            self::assertSame($ensayos, $r['bruto']['ensayos']);
            self::assertSame('NORMALIZADO', $r['componentes']['MEM-S02']['estado']);
        }
        $r = (new Hvlt)->normalizar(array_replace($this->captura(), ['recuerdo_diferido' => null]));
        self::assertNull($r['componentes']['MEM-S02']['z']);
        $r = (new Hvlt)->normalizar(array_replace($this->captura(), ['recuerdo_diferido' => 0]));
        self::assertSame('NORMALIZADO', $r['componentes']['MEM-S02']['estado']);
        self::assertSame(0, $r['componentes']['MEM-S02']['puntuacion_bruta']);
    }

    public function test_demora_incompatible_invalida_diferido_y_no_aprendizaje(): void
    {
        foreach ([null, 19, 26, INF, NAN, '22'] as $demora) {
            $r = (new Hvlt)->normalizar(array_replace($this->captura(), ['demora_minutos' => $demora]));
            self::assertSame('NORMALIZADO', $r['componentes']['MEM-S01']['estado']);
            self::assertSame('NO_NORMALIZABLE', $r['componentes']['MEM-S02']['estado']);
        }
    }

    public function test_rechaza_metodo_inexistente(): void
    {
        $this->expectException(DomainException::class);
        (new RegistroMetodosInstrumentales)->ejecutar('METODO-DESCONOCIDO', $this->captura());
    }
}
