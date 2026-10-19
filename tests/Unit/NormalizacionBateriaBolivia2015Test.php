<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use MotorExperto\dominio\instrumentos\CatalogoCapturaCognitiva;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;
use MotorExperto\dominio\instrumentos\NormalizadorBateriaBolivia2015;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NormalizacionBateriaBolivia2015Test extends TestCase
{
    private function captura(string $metodo): array
    {
        $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];

        return $meta['identidad'] + ['protocolo' => $metodo, 'edad_anios' => 70, 'escolaridad_anios' => 4,
            'espanol_nativo' => true, 'nacimiento_bolivia' => true, 'residencia_bolivia' => true,
            'lectoescritura' => true, 'administracion_estandarizada' => true, 'audicion_compatible' => true,
            'condiciones_sensoriales_motoras_compatibles' => true, 'forma_n_aciertos' => 6, 'forma_l_aciertos' => 7,
            'segundos_parte_b' => 200, 'errores_observados' => 1, 'finalizada' => true,
            'aciertos_espontaneos' => 40, 'aciertos_clave_semantica' => 5, 'puntaje_copia' => 22.5, 'elementos_revisados' => 18,
            'f_validas' => 8, 'a_validas' => 9, 's_validas' => 10, 'animales_validas' => 12,
            'f_segundos' => 60, 'a_segundos' => 60, 's_segundos' => 60, 'animales_segundos' => 60,
            'palabras_correctas' => 60, 'colores_correctos' => 30, 'palabra_color_correctas' => 20,
            'segundos_palabras' => 45, 'segundos_colores' => 45, 'segundos_palabra_color' => 45];
    }

    public static function regresiones(): array
    {
        // Valores de referencia del modelo publicado para edad 70 y escolaridad 4; cálculos no redondeados en producción.
        return [['BTA-BO-2015-NL', 'ATE-S03', 13, 10.812, 3.786],
            ['TMT-B-BO-2015', 'EJE-S02', 200, 228.979, 69.661],
            ['BNT-BO-2015-60', 'LEN-S01', 45, 45.102, 8.732],
            ['ROCF-BO-2015-COPIA-A', 'VIS-S02', 22.5, 22.833, 6.728]];
    }

    #[DataProvider('regresiones')]
    public function test_modelos_especificos_y_brutos_no_generan_diagnosticos(string $metodo, string $componente, int|float $bruto, float $esperado, float $de): void
    {
        $salida = (new RegistroMetodosInstrumentales)->ejecutar($metodo, $this->captura($metodo));
        $r = $salida['componentes'][$componente];
        self::assertSame('NORMALIZADO', $r['estado']);
        self::assertSame($bruto, $r['puntuacion_bruta']);
        self::assertEqualsWithDelta($esperado, $r['esperado'], 1e-10);
        self::assertSame($de, $r['de_residual']);
        self::assertNull($r['clasificacion_clinica']);
        self::assertNull($salida['diagnostico']);
        self::assertSame('PENDIENTE', $salida['revision_profesional']);
        self::assertNotEmpty($salida['localizador']);
    }

    public function test_fluidez_son_cuatro_normas_separadas_sin_promedio_y_comparten_una_aplicacion(): void
    {
        $metodo = 'FLUIDEZ-BO-2015-FAS-ANIMALES';
        $r = (new RegistroMetodosInstrumentales)->ejecutar($metodo, $this->captura($metodo))['componentes']['LEN-S02'];
        self::assertCount(4, $r['medidas']);
        foreach (['f' => 7.380, 'a' => 7.151, 's' => 6.233, 'animales' => 11.013] as $condicion => $predicho) {
            self::assertEqualsWithDelta($predicho, $r['medidas'][$condicion]['esperado'], 1e-10);
        }
        self::assertNull($r['z']);
        self::assertNull($r['puntuacion_bruta']);
        $c = $this->captura($metodo);
        $c['s_validas'] = null;
        $c['animales_segundos'] = 59;
        $r = (new RegistroMetodosInstrumentales)->ejecutar($metodo, $c)['componentes']['LEN-S02'];
        self::assertSame('PARCIALMENTE_NORMALIZADO', $r['estado']);
        self::assertSame('NORMALIZADO', $r['medidas']['f']['estado']);
        self::assertSame('NORMALIZADO', $r['medidas']['a']['estado']);
        self::assertNull($r['medidas']['s']['z']);
        self::assertNull($r['medidas']['animales']['z']);
    }

    public function test_bnt_no_hereda_ajuste_etario_y_escolaridad_cambia_desde_trece(): void
    {
        $m = 'BNT-BO-2015-60';
        $c = $this->captura($m);
        $registro = new RegistroMetodosInstrumentales;
        $base = $registro->ejecutar($m, $c)['componentes']['LEN-S01'];
        $otro = $registro->ejecutar($m, array_replace($c, ['edad_anios' => 95, 'escolaridad_anios' => 12]))['componentes']['LEN-S01'];
        self::assertSame($base['esperado'], $otro['esperado']);
        $alto = $registro->ejecutar($m, array_replace($c, ['escolaridad_anios' => 13]))['componentes']['LEN-S01'];
        self::assertEqualsWithDelta(48.848, $alto['esperado'], 1e-10);
    }

    public function test_segundo_caso_de_referencia_con_escolaridad_mayor_de_doce(): void
    {
        $registro = new RegistroMetodosInstrumentales;
        foreach (['BTA-BO-2015-NL' => ['ATE-S03', 13.345], 'TMT-B-BO-2015' => ['EJE-S02', 149.788],
            'BNT-BO-2015-60' => ['LEN-S01', 48.848], 'ROCF-BO-2015-COPIA-A' => ['VIS-S02', 29.515]] as $metodo => [$componente, $esperado]) {
            $r = $registro->ejecutar($metodo, array_replace($this->captura($metodo), ['edad_anios' => 60, 'escolaridad_anios' => 13]));
            self::assertEqualsWithDelta($esperado, $r['componentes'][$componente]['esperado'], 1e-10);
        }
        $m = 'FLUIDEZ-BO-2015-FAS-ANIMALES';
        $r = $registro->ejecutar($m, array_replace($this->captura($m), ['edad_anios' => 60, 'escolaridad_anios' => 13]));
        foreach (['f' => 10.470, 'a' => 11.086, 's' => 10.097, 'animales' => 16.411] as $letra => $esperado) {
            self::assertEqualsWithDelta($esperado, $r['componentes']['LEN-S02']['medidas'][$letra]['esperado'], 1e-10);
        }
    }

    public function test_tmt_conserva_direccion_de_tiempo_y_no_normaliza_finalizacion_censurada(): void
    {
        $m = 'TMT-B-BO-2015';
        $c = $this->captura($m);
        $registro = new RegistroMetodosInstrumentales;
        $r = $registro->ejecutar($m, array_replace($c, ['segundos_parte_b' => 250]))['componentes']['EJE-S02'];
        self::assertGreaterThan(0, $r['z']);
        self::assertSame('MENOR_ES_MEJOR', $r['direccion']);
        $r = $registro->ejecutar($m, array_replace($c, ['finalizada' => false, 'segundos_parte_b' => 300]))['componentes']['EJE-S02'];
        self::assertSame(300, $r['puntuacion_bruta']);
        self::assertNull($r['z']);
        self::assertContains('TAREA_NO_FINALIZADA_NO_EQUIVALE_A_TIEMPO_EXACTO', $r['motivos']);
    }

    public function test_stroop_aplica_interferencia_y_tabla_a35_sin_fabricar_z_ni_interpolar(): void
    {
        $m = 'STROOP-BO-2015-GOLDEN';
        $c = $this->captura($m);
        $registro = new RegistroMetodosInstrumentales;
        $r = $registro->ejecutar($m, $c)['componentes']['EJE-S01'];
        self::assertSame(0, $r['puntuacion_bruta']);
        self::assertSame(40, $r['percentil_intervalo']['inferior']);
        self::assertSame(40, $r['percentil_intervalo']['superior']);
        self::assertNull($r['z']);
        self::assertNull($r['esperado']);
        foreach ([21 => [40, 50], 0 => [null, 5], 50 => [95, null]] as $wc => [$inferior, $superior]) {
            $r = $registro->ejecutar($m, array_replace($c, ['palabra_color_correctas' => $wc]))['componentes']['EJE-S01'];
            self::assertSame($inferior, $r['percentil_intervalo']['inferior']);
            self::assertSame($superior, $r['percentil_intervalo']['superior']);
        }
        $r = $registro->ejecutar($m, array_replace($c, ['palabras_correctas' => 0, 'colores_correctos' => 0]))['componentes']['EJE-S01'];
        self::assertNull($r['percentil_intervalo']);
        self::assertSame('NO_NORMALIZABLE', $r['estado']);
    }

    public static function invalidas(): array
    {
        return [['BTA-BO-2015-NL', 'forma_n_aciertos', null], ['BTA-BO-2015-NL', 'forma_l_aciertos', 11],
            ['BTA-BO-2015-NL', 'forma_n_aciertos', '6'], ['BTA-BO-2015-NL', 'audicion_compatible', false],
            ['TMT-B-BO-2015', 'segundos_parte_b', 301], ['TMT-B-BO-2015', 'segundos_parte_b', 0],
            ['TMT-B-BO-2015', 'segundos_parte_b', INF], ['TMT-B-BO-2015', 'errores_observados', null],
            ['STROOP-BO-2015-GOLDEN', 'segundos_colores', 44], ['STROOP-BO-2015-GOLDEN', 'palabras_correctas', NAN],
            ['BNT-BO-2015-60', 'aciertos_espontaneos', 60], ['ROCF-BO-2015-COPIA-A', 'puntaje_copia', 22.25],
            ['ROCF-BO-2015-COPIA-A', 'elementos_revisados', 17], ['ROCF-BO-2015-COPIA-A', 'puntaje_copia', 37]];
    }

    #[DataProvider('invalidas')]
    public function test_no_normaliza_medida_incompatible_y_conserva_su_original(string $m, string $campo, mixed $valor): void
    {
        $c = array_replace($this->captura($m), [$campo => $valor]);
        $r = (new RegistroMetodosInstrumentales)->ejecutar($m, $c);
        foreach ($r['componentes'] as $componente) {
            self::assertSame('NO_NORMALIZABLE', $componente['estado']);
            self::assertNull($componente['z']);
            self::assertNotEmpty($componente['motivos']);
        }
        if (is_float($valor) && is_nan($valor)) {
            self::assertNan($r['bruto'][$campo]);
        } else {
            self::assertSame($valor, $r['bruto'][$campo]);
        }
    }

    public function test_aplicabilidad_no_se_sustituye_con_defaults_en_ningun_metodo(): void
    {
        foreach ((new NormalizadorBateriaBolivia2015)->metodos() as $m => $meta) {
            foreach (['edicion_protocolo' => 'OTRA', 'edad_anios' => 96, 'escolaridad_anios' => 0, 'lectoescritura' => null,
                'espanol_nativo' => false, 'nacimiento_bolivia' => null, 'residencia_bolivia' => false, 'administracion_estandarizada' => null] as $campo => $valor) {
                $c = array_replace($this->captura($m), [$campo => $valor]);
                $r = (new RegistroMetodosInstrumentales)->ejecutar($m, $c)['componentes'][$meta['componentes'][0]];
                foreach ($r['medidas'] ?? [$r] as $medida) {
                    self::assertSame('NO_NORMALIZABLE', $medida['estado'], $m.':'.$campo);
                    self::assertNull($medida['z']);
                    self::assertNull($medida['clasificacion_clinica']);
                }
            }
        }
        $m = 'BTA-BO-2015-NL';
        $r = (new RegistroMetodosInstrumentales)->ejecutar($m, array_replace($this->captura($m), ['forma_n_aciertos' => 0, 'forma_l_aciertos' => 0]));
        self::assertSame(0, $r['componentes']['ATE-S03']['puntuacion_bruta']);
        self::assertSame('NORMALIZADO', $r['componentes']['ATE-S03']['estado']);
    }

    public function test_diccionario_cubre_19_componentes_con_8_normalizables_sin_rellenar_residentes(): void
    {
        $catalogo = (new CatalogoCapturaCognitiva)->componentes();
        self::assertCount(19, $catalogo);
        self::assertCount(8, array_filter($catalogo, fn ($c) => $c['metodos_disponibles'] !== []));
        foreach ($catalogo as $componente) {
            self::assertNotEmpty($componente['campos']);
            self::assertNotEmpty($componente['limite']);
            self::assertStringStartsWith('https://', $componente['fuente']);
            self::assertNull($componente['instrumento_administrado']);
            self::assertNull($componente['mediciones_residente']);
        }
        self::assertSame([], $catalogo['EJE-S05']['metodos_disponibles']);
        self::assertSame([], $catalogo['MEM-S04']['metodos_disponibles']);
    }

    public function test_extractor_no_convierte_un_logico_nominal_y_rechaza_selectores_ambiguos(): void
    {
        $m = 'BTA-BO-2015-NL';
        $meta = (new RegistroMetodosInstrumentales)->metodos()[$m];
        $c = $this->captura($m);
        $a = ['cod_aplicacion' => 'AP-SINTETICA', 'cod_instrumento' => 'INST-SINTETICO', 'codigo_instrumento' => 'COD-SINTETICO', 'version_instrumento' => 'TEST-1'];
        $perfil = ['id' => 'PERFIL-SINTETICO', 'metodo' => $m, 'selectores' => [], 'identidad_instrumento' => $meta['identidad']] + $a;
        $respuestas = [];
        foreach ($meta['entradas'] as $campo => $tipo) {
            $id = 'Q-'.count($respuestas);
            $columna = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
            $perfil['selectores'][$campo] = ['cod_pregunta' => $id, 'campo' => $columna];
            $respuestas[] = ['cod_aplicacion' => $a['cod_aplicacion'], 'cod_pregunta' => $id, 'cod_respuesta' => 'R-'.count($respuestas), 'coherente' => true,
                'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO', $columna => $campo === 'lectoescritura' ? 'Sí' : $c[$campo]];
        }
        $r = (new ExtractorCapturaInstrumental)->extraer($perfil, $a, $respuestas, 70);
        self::assertNull($r['captura']['lectoescritura']);
        self::assertSame('Sí', $r['fuentes_entradas']['lectoescritura']['bruto']);
        self::assertNull($r['normalizacion']['componentes']['ATE-S03']['z']);
        $respuestas[0]['cod_aplicacion'] = 'AP-AJENA';
        $ajena = (new ExtractorCapturaInstrumental)->extraer($perfil, $a, $respuestas, 70);
        self::assertArrayNotHasKey('forma_n_aciertos', $ajena['fuentes_entradas']);
        self::assertNull($ajena['normalizacion']['componentes']['ATE-S03']['z']);
        $perfil['selectores']['forma_l_aciertos'] = $perfil['selectores']['forma_n_aciertos'];
        $this->expectException(DomainException::class);
        (new ExtractorCapturaInstrumental)->extraer($perfil, $a, $respuestas, 70);
    }
}
