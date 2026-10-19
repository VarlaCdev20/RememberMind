<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\dominio\instrumentos\CalculadorLenguajeWabCastellano2000 as Wab;
use MotorExperto\dominio\instrumentos\CatalogoCapturaCognitiva;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;
use MotorExperto\dominio\instrumentos\NormalizadorReferenciaBarcelona2011;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Cálculos sintéticos contrastados con la página impresa 18 de la edición castellana de 2000. */
final class LenguajeWabCastellanoInvestigadoTest extends TestCase
{
    private function captura(): array
    {
        return ['instrumento' => 'WAB', 'edicion_protocolo' => 'Nau-Llibres-2000-Castellana', 'idioma' => 'es',
            'protocolo' => Wab::METODO, 'repeticion_total' => 98, 'contenido_informacion' => 8, 'fluidez' => 7,
            'audicion_compatible' => true, 'muestra_discurso_documentada' => true, 'administracion_estandarizada' => true];
    }

    public static function puntuaciones(): array
    {
        // Los resultados esperados son literales independientes; no se calculan con código productivo.
        return [[98, 8, 7, 9.8, 15], [0, 0, 0, 0, 0], [100, 10, 10, 10, 20], [37, 4, 6, 3.7, 10]];
    }

    #[DataProvider('puntuaciones')]
    public function test_calcula_escala_y_suma_sin_generar_norma_ni_cociente_de_afasia(int $total, int $contenido, int $fluidez, int|float $escala, int $discurso): void
    {
        $c = array_replace($this->captura(), ['repeticion_total' => $total, 'contenido_informacion' => $contenido, 'fluidez' => $fluidez]);
        $r = (new RegistroMetodosInstrumentales)->ejecutar(Wab::METODO, $c);
        self::assertEquals($escala, $r['componentes']['LEN-S04']['puntuacion_bruta']);
        self::assertSame($discurso, $r['componentes']['LEN-S05']['puntuacion_bruta']);
        self::assertSame($contenido, $r['componentes']['LEN-S05']['contenido_informacion']);
        self::assertSame($fluidez, $r['componentes']['LEN-S05']['fluidez']);
        self::assertSame($c, $r['contexto_aplicacion']);
        self::assertStringContainsString('18', $r['localizador']);
        foreach ($r['componentes'] as $m) {
            self::assertSame('CALCULADO_SIN_NORMA', $m['estado']);
            self::assertSame('DESCRIPTIVO_SIN_COMPARACION', $m['interpretacion_matematica']['estado']);
            self::assertStringNotContainsString('falsas alarmas', $m['interpretacion_matematica']['texto']);
            self::assertNull($m['z']);
            self::assertNull($m['esperado']);
            self::assertNull($m['clasificacion_clinica']);
        }
        self::assertNull($r['diagnostico']);
        self::assertArrayNotHasKey('cociente_afasia', $r);
    }

    public static function incompatibles(): array
    {
        return [['instrumento', 'WAB-R'], ['edicion_protocolo', 'WAB-R-2006'], ['edicion_protocolo', 'Nau-Llibres-1990'],
            ['idioma', 'en'], ['protocolo', 'BEDside'], ['administracion_estandarizada', null]];
    }

    #[DataProvider('incompatibles')]
    public function test_no_intercambia_ediciones_ni_da_por_acreditada_la_administracion(string $campo, mixed $valor): void
    {
        $r = (new Wab)->calcular(array_replace($this->captura(), [$campo => $valor]));
        foreach ($r['componentes'] as $m) {
            self::assertSame('NO_CALCULABLE', $m['estado']);
            self::assertNull($m['puntuacion_bruta']);
            self::assertNotEmpty($m['motivos']);
        }
        self::assertSame($valor, $r['contexto_aplicacion'][$campo]);
    }

    public static function repeticionInvalida(): array
    {
        return [[null], ['98'], [true], [-1], [101], [98.5], [INF], [NAN]];
    }

    #[DataProvider('repeticionInvalida')]
    public function test_repeticion_invalida_no_borra_discurso_compatible(mixed $valor): void
    {
        $r = (new Wab)->calcular(array_replace($this->captura(), ['repeticion_total' => $valor]));
        self::assertSame('NO_CALCULABLE', $r['componentes']['LEN-S04']['estado']);
        self::assertNull($r['componentes']['LEN-S04']['puntuacion_bruta']);
        self::assertSame(15, $r['componentes']['LEN-S05']['puntuacion_bruta']);
        if (is_float($valor) && is_nan($valor)) {
            self::assertNan($r['bruto']['repeticion_total']);
        } else {
            self::assertSame($valor, $r['bruto']['repeticion_total']);
        }
    }

    public static function discursoInvalido(): array
    {
        return [['contenido_informacion', null], ['contenido_informacion', 11], ['contenido_informacion', -1],
            ['fluidez', '7'], ['fluidez', false], ['fluidez', 7.5], ['muestra_discurso_documentada', false]];
    }

    #[DataProvider('discursoInvalido')]
    public function test_discurso_invalido_no_inventa_contenido_y_conserva_repeticion(string $campo, mixed $valor): void
    {
        $r = (new Wab)->calcular(array_replace($this->captura(), [$campo => $valor]));
        self::assertSame('NO_CALCULABLE', $r['componentes']['LEN-S05']['estado']);
        self::assertNull($r['componentes']['LEN-S05']['puntuacion_bruta']);
        self::assertSame(9.8, $r['componentes']['LEN-S04']['puntuacion_bruta']);
        self::assertSame($valor, $r['bruto'][$campo]);
    }

    public function test_falta_de_audicion_afecta_repeticion_y_no_sustituye_su_dato_por_cero(): void
    {
        $r = (new Wab)->calcular(array_replace($this->captura(), ['audicion_compatible' => false]));
        self::assertNull($r['componentes']['LEN-S04']['puntuacion_bruta']);
        self::assertSame(98, $r['bruto']['repeticion_total']);
        self::assertSame(15, $r['componentes']['LEN-S05']['puntuacion_bruta']);
    }

    public function test_wab_conserva_brutos_y_no_recibe_la_referencia_de_otro_instrumento(): void
    {
        $c = (new CatalogoCapturaCognitiva)->componentes();
        self::assertCount(19, $c);
        self::assertCount(8, array_filter($c, fn ($m) => $m['metodos_disponibles'] !== []));
        foreach (['LEN-S04', 'LEN-S05'] as $id) {
            self::assertSame([], $c[$id]['metodos_disponibles']);
            self::assertSame([Wab::METODO], $c[$id]['calculos_descriptivos_disponibles']);
            // La referencia TB-A identificada no convierte las puntuaciones de WAB.
            self::assertSame('NORMALIZADOR_REFERENCIA_CON_CONTEXTO_EXPLICITO', $c[$id]['estado_metodo']);
            self::assertSame([NormalizadorReferenciaBarcelona2011::METODO], $c[$id]['normalizadores_referencia_disponibles']);
        }
    }

    public function test_extractor_reutiliza_mediciones_tipadas_y_preserva_fuentes_de_ambos_componentes(): void
    {
        $meta = (new Wab)->metadatos();
        $c = $this->captura();
        $a = ['cod_aplicacion' => 'AP-WAB-SINTETICA', 'cod_instrumento' => 'INST-WAB-SINTETICO',
            'codigo_instrumento' => 'WAB-SINTETICO', 'version_instrumento' => 'EDICION-SINTETICA'];
        $perfil = $a + ['id' => 'PERFIL-WAB-SINTETICO', 'metodo' => Wab::METODO,
            'identidad_instrumento' => $meta['identidad'], 'selectores' => []];
        $respuestas = [];
        foreach ($meta['entradas'] as $campo => $tipo) {
            $pregunta = 'PREGUNTA-SINTETICA-'.count($respuestas);
            $columna = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
            $perfil['selectores'][$campo] = ['cod_pregunta' => $pregunta, 'campo' => $columna];
            $respuestas[] = ['cod_aplicacion' => $a['cod_aplicacion'], 'cod_pregunta' => $pregunta,
                'cod_respuesta' => 'RESPUESTA-SINTETICA-'.count($respuestas), 'coherente' => true,
                'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO',
                $columna => $tipo === 'LOGICO' ? '1' : $c[$campo].'.0000'];
        }
        $r = (new ExtractorCapturaInstrumental)->extraer($perfil, $a, $respuestas, 81);
        self::assertSame([], $r['motivos_captura']);
        self::assertSame('98.0000', $r['fuentes_entradas']['repeticion_total']['bruto']);
        self::assertSame(98, $r['captura']['repeticion_total']);
        self::assertSame(9.8, $r['normalizacion']['componentes']['LEN-S04']['puntuacion_bruta']);
        self::assertSame(15, $r['normalizacion']['componentes']['LEN-S05']['puntuacion_bruta']);
        foreach ($r['fuentes_entradas'] as $fuente) {
            self::assertSame('AP-WAB-SINTETICA', $fuente['aplicacion']);
        }
        // Una identidad WAB-R declarada no puede obtener estas conversiones castellanas.
        $perfil['identidad_instrumento']['instrumento'] = 'WAB-R';
        $r = (new ExtractorCapturaInstrumental)->extraer($perfil, $a, $respuestas, 81);
        foreach ($r['normalizacion']['componentes'] as $m) {
            self::assertSame('NO_CALCULABLE', $m['estado']);
            self::assertNull($m['puntuacion_bruta']);
        }
    }
}
