<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\dominio\instrumentos\CalculadorRecuerdoConClavesFcsrt as Fcsrt;
use MotorExperto\dominio\instrumentos\CatalogoCapturaCognitiva;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;
use MotorExperto\dominio\instrumentos\PerfilInstrumentalCognitivo;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Casos sintéticos independientes de las instrucciones y estímulos protegidos de la prueba. */
final class RecuerdoConClavesFcsrtInvestigadoTest extends TestCase
{
    private function captura(): array
    {
        return ['instrumento' => 'FCSRT', 'edicion_protocolo' => 'NEURONORMA-2009-PALABRAS',
            'idioma' => 'es', 'protocolo' => Fcsrt::METODO, 'codificacion_verificada' => true,
            'administracion_estandarizada' => true, 'libre_1' => 6, 'adicional_claves_1' => 7,
            'libre_2' => 8, 'adicional_claves_2' => 6, 'libre_3' => 10, 'adicional_claves_3' => 5,
            'libre_diferido' => 9, 'adicional_claves_diferido' => 6, 'demora_minutos' => 30];
    }

    public function test_sumas_independientes_preservan_libres_adicionales_y_fuente(): void
    {
        $c = $this->captura();
        $r = (new RegistroMetodosInstrumentales)->ejecutar(Fcsrt::METODO, $c);
        $medidas = $r['componentes']['MEM-S03']['medidas'];
        // 7+6+5=18; libres 6+8+10=24; total 13+14+15=42; diferido 9+6=15.
        self::assertSame([18, 42, 15], array_column($medidas, 'puntuacion_bruta'));
        self::assertSame(24, $medidas[1]['libre_total']);
        self::assertSame($c, $r['contexto_aplicacion']);
        self::assertSame(Fcsrt::FUENTE, $r['referencia']);
        foreach ($medidas as $m) {
            self::assertSame('CALCULADO_SIN_NORMA', $m['estado']);
            self::assertNull($m['z']);
            self::assertNull($m['esperado']);
            self::assertNull($m['clasificacion_clinica']);
            self::assertSame('DESCRIPTIVO_SIN_COMPARACION', $m['interpretacion_matematica']['estado']);
        }
        self::assertNull($r['diagnostico']);
    }

    public function test_cero_y_maximos_son_datos_distintos_de_la_ausencia(): void
    {
        foreach ([[0, 0, [0, 0, 0]], [16, 0, [0, 48, 16]], [0, 16, [48, 48, 16]]] as [$libre, $claves, $esperados]) {
            $c = $this->captura();
            foreach ([1, 2, 3, 'diferido'] as $ensayo) {
                $c['libre_'.$ensayo] = $libre;
                $c['adicional_claves_'.$ensayo] = $claves;
            }
            $r = (new Fcsrt)->calcular($c);
            self::assertSame($esperados, array_column($r['componentes']['MEM-S03']['medidas'], 'puntuacion_bruta'));
        }
    }

    public static function incompatibles(): array
    {
        return [['instrumento', 'HVLT-R'], ['edicion_protocolo', 'pFCSRT+IR-IMAGENES-A'],
            ['idioma', 'en'], ['protocolo', 'OTRO'], ['codificacion_verificada', null],
            ['administracion_estandarizada', false]];
    }

    #[DataProvider('incompatibles')]
    public function test_no_intercambia_versiones_ni_completa_condiciones(string $campo, mixed $valor): void
    {
        $r = (new Fcsrt)->calcular(array_replace($this->captura(), [$campo => $valor]));
        foreach ($r['componentes']['MEM-S03']['medidas'] as $m) {
            self::assertSame('NO_CALCULABLE', $m['estado']);
            self::assertNull($m['puntuacion_bruta']);
            self::assertNotEmpty($m['motivos']);
        }
    }

    public static function invalidos(): array
    {
        return [[null], ['6'], [true], [6.5], [-1], [17], [INF], [NAN]];
    }

    #[DataProvider('invalidos')]
    public function test_ensayo_invalido_no_produce_total_parcial_ni_borra_diferido(mixed $valor): void
    {
        $r = (new Fcsrt)->calcular(array_replace($this->captura(), ['libre_2' => $valor]));
        self::assertSame([null, null, 15], array_column($r['componentes']['MEM-S03']['medidas'], 'puntuacion_bruta'));
        self::assertSame('NO_CALCULABLE', $r['componentes']['MEM-S03']['medidas'][0]['estado']);
    }

    public function test_rechaza_doble_conteo_en_pareja_y_conserva_original(): void
    {
        $c = array_replace($this->captura(), ['adicional_claves_1' => 11]);
        $r = (new Fcsrt)->calcular($c);
        self::assertSame('NO_CALCULABLE', $r['componentes']['MEM-S03']['medidas'][1]['estado']);
        self::assertContains('PAREJA_SUPERA_16:libre_1', $r['componentes']['MEM-S03']['medidas'][1]['motivos']);
        self::assertSame(11, $r['bruto']['adicional_claves_1']);
    }

    public static function demoras(): array
    {
        return [[null], ['30'], [true], [20], [29.9], [30.1], [INF], [NAN]];
    }

    #[DataProvider('demoras')]
    public function test_demora_incompatible_no_invalida_ensayos_de_aprendizaje(mixed $demora): void
    {
        $r = (new Fcsrt)->calcular(array_replace($this->captura(), ['demora_minutos' => $demora]));
        self::assertSame([18, 42, null], array_column($r['componentes']['MEM-S03']['medidas'], 'puntuacion_bruta'));
        self::assertSame('NO_CALCULABLE', $r['componentes']['MEM-S03']['medidas'][2]['estado']);
    }

    public function test_diferido_invalido_o_con_doble_conteo_no_borra_aprendizaje(): void
    {
        foreach ([null, 8] as $claves) {
            $r = (new Fcsrt)->calcular(array_replace($this->captura(), ['adicional_claves_diferido' => $claves]));
            self::assertSame([18, 42, null], array_column($r['componentes']['MEM-S03']['medidas'], 'puntuacion_bruta'));
        }
        $r = (new Fcsrt)->calcular(array_replace($this->captura(), ['demora_minutos' => 30.0]));
        self::assertSame(15, $r['componentes']['MEM-S03']['medidas'][2]['puntuacion_bruta']);
    }

    public function test_extraccion_e_integracion_preservan_procedencia_sin_marcar_normalizado(): void
    {
        $c = $this->captura();
        $meta = (new Fcsrt)->metadatos();
        $a = ['cod_aplicacion' => 'AP-FCSRT-SINTETICA', 'cod_instrumento' => 'INST-FCSRT-SINTETICO',
            'codigo_instrumento' => 'FCSRT-SINTETICO', 'version_instrumento' => 'EDICION-SINTETICA'];
        $perfil = $a + ['id' => 'PERFIL-SINTETICO', 'metodo' => Fcsrt::METODO,
            'identidad_instrumento' => $meta['identidad'], 'selectores' => []];
        $respuestas = [];
        foreach ($meta['entradas'] as $campo => $tipo) {
            $id = 'PREGUNTA-SINTETICA-'.count($respuestas);
            $columna = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
            $perfil['selectores'][$campo] = ['cod_pregunta' => $id, 'campo' => $columna];
            $respuestas[] = ['cod_aplicacion' => $a['cod_aplicacion'], 'cod_pregunta' => $id,
                'cod_respuesta' => 'RESPUESTA-SINTETICA-'.count($respuestas), 'coherente' => true,
                'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO',
                $columna => $tipo === 'LOGICO' ? '1' : $c[$campo].'.0000'];
        }
        $e = (new ExtractorCapturaInstrumental)->extraer($perfil, $a, $respuestas, 81);
        self::assertSame([], $e['motivos_captura']);
        self::assertSame('6.0000', $e['fuentes_entradas']['libre_1']['bruto']);
        $captura = ['disponible' => true, 'capturas' => [$a + ['fecha_hora' => '2026-10-19T09:00:00-04:00',
            'cod_personal' => 'PERSONAL-SINTETICO', 'extraccion' => $e]]];
        $red = ['dominios' => ['MEM' => ['nombre' => 'Memoria', 'componentes' => ['MEM-S03' => ['nombre' => 'Recuerdo con claves']]]]];
        $r = (new PerfilInstrumentalCognitivo)->construir($red, $captura);
        $componente = $r['dominios']['MEM']['componentes']['MEM-S03'];
        self::assertCount(3, $componente['mediciones']);
        self::assertSame('SIN_MEDICION_NORMALIZADA', $componente['estado_medicion']);
        self::assertSame('PERSONAL-SINTETICO', $componente['mediciones'][0]['autor']);
        self::assertSame($e['fuentes_entradas'], $componente['mediciones'][2]['fuentes_entradas']);
        self::assertNull($r['diagnostico']);
        self::assertFalse($r['persistido']);
        $catalogo = (new CatalogoCapturaCognitiva)->componentes();
        self::assertSame([], $catalogo['MEM-S03']['metodos_disponibles']);
        self::assertSame([Fcsrt::METODO], $catalogo['MEM-S03']['calculos_descriptivos_disponibles']);
        self::assertCount(8, array_filter($catalogo, fn ($m) => $m['metodos_disponibles'] !== []));
    }
}
