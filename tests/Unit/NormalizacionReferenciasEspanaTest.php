<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\dominio\instrumentos\CatalogoCapturaCognitiva;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;
use MotorExperto\dominio\instrumentos\NormalizadorReferenciasEspana as Norma;
use MotorExperto\dominio\instrumentos\PerfilInstrumentalCognitivo;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Comprobaciones matemáticas independientes y abstenciones de las cinco referencias. */
final class NormalizacionReferenciasEspanaTest extends TestCase
{
    private function captura(string $metodo): array
    {
        $meta = (new Norma)->metodos()[$metodo];
        $c = $meta['identidad'] + ['protocolo' => $metodo, 'edad_anios' => 81,
            'escolaridad_anios' => $metodo === Norma::IMAGENES ? 6 : 12, 'sexo_norma' => 1,
            'longitud_inversa' => 3, 'longitud_letras_numeros' => 4, 'aciertos' => 18,
            'elementos_puntuados' => 30, 'libre_1' => 6, 'adicional_claves_1' => 7,
            'libre_2' => 8, 'adicional_claves_2' => 6, 'libre_3' => 10, 'adicional_claves_3' => 5];
        foreach ($meta['entradas'] as $campo => $tipo) {
            if ($tipo === 'LOGICO') {
                $c[$campo] = true;
            }
        }
        if ($metodo === Norma::IMAGENES) {
            $c = array_replace($c, ['libre_1' => 3, 'libre_2' => 8, 'libre_3' => 9]);
        }

        return $c;
    }

    public static function ejemplos(): array
    {
        // Valores literales obtenidos con Decimal, sin invocar la implementación PHP.
        return [
            [Norma::DSB, 'EJE-S03', 3, 3.938, .854, -1.098360655737705],
            [Norma::LN, 'EJE-S03', 4, 3.766, 1.075, .217674418604651],
            [Norma::JLO, 'VIS-S01', 18, 19.123, 4.446, -.252586594691858],
            [Norma::FCSRT, 'MEM-S03', 42, 44.788, 3.865, -.721345407503234],
            [Norma::IMAGENES, 'MEM-S01', 20, 23.817, 6.791660327195405, -.562012794532117],
        ];
    }

    #[DataProvider('ejemplos')]
    public function test_normaliza_sin_redondear_ni_clasificar(string $metodo, string $componente, int $bruto, float $predicho, float $de, float $z): void
    {
        $c = $this->captura($metodo);
        $r = (new RegistroMetodosInstrumentales)->ejecutar($metodo, $c);
        $m = $r['componentes'][$componente];
        self::assertSame('NORMALIZADO', $m['estado']);
        self::assertSame($bruto, $m['puntuacion_bruta']);
        self::assertEqualsWithDelta($predicho, $m['esperado'], 1e-10);
        self::assertEqualsWithDelta($de, $m['de_residual'], 1e-10);
        self::assertEqualsWithDelta($z, $m['z'], 1e-10);
        self::assertNull($m['clasificacion_clinica']);
        self::assertNull($r['diagnostico']);
        self::assertSame('PENDIENTE', $r['revision_profesional']);
        self::assertSame('NO_CONFIGURADA', $r['adopcion_institucional']);
        self::assertSame($c, $r['contexto_aplicacion']);
        self::assertSame('COMPARACION_CON_PREDICHO', $m['interpretacion_matematica']['estado']);
    }

    public static function limites(): array
    {
        return [[Norma::DSB, 55, 3, true], [Norma::LN, 87, 22, true],
            [Norma::JLO, 54, 12, false], [Norma::FCSRT, 88, 12, false],
            [Norma::DSB, 81, 2, false], [Norma::LN, 81, 23, false],
            [Norma::IMAGENES, 50, 0, true], [Norma::IMAGENES, 88, 30, true],
            [Norma::IMAGENES, 49, 6, false], [Norma::IMAGENES, 89, 6, false],
            [Norma::IMAGENES, 81, 31, false], [Norma::IMAGENES, 81, -1, false]];
    }

    #[DataProvider('limites')]
    public function test_respeta_limites_exactos_del_estudio(string $metodo, int $edad, int $educacion, bool $normaliza): void
    {
        $r = (new Norma)->normalizar($metodo, array_replace($this->captura($metodo),
            ['edad_anios' => $edad, 'escolaridad_anios' => $educacion]));
        $m = array_values($r['componentes'])[0];
        self::assertSame($normaliza ? 'NORMALIZADO' : 'NO_NORMALIZABLE', $m['estado']);
        self::assertSame($normaliza, $m['z'] !== null);
        if (! $normaliza) {
            self::assertNull($m['esperado']);
            self::assertNull($m['de_residual']);
        }
    }

    public function test_toda_condicion_logica_desconocida_falsa_o_textual_impide_normalizar(): void
    {
        foreach ((new Norma)->metodos() as $id => $meta) {
            foreach ($meta['entradas'] as $campo => $tipo) {
                if ($tipo !== 'LOGICO') {
                    continue;
                }
                foreach ([null, false, '1', 1] as $valor) {
                    $r = (new Norma)->normalizar($id, array_replace($this->captura($id), [$campo => $valor]));
                    $m = array_values($r['componentes'])[0];
                    foreach ($m['medidas'] ?? [$m] as $medida) {
                        self::assertSame('NO_NORMALIZABLE', $medida['estado'], $id.':'.$campo);
                        self::assertNull($medida['z']);
                        self::assertNull($medida['esperado']);
                    }
                }
            }
        }
    }

    public static function invalidos(): array
    {
        return [[null], ['81'], [81.0], [true], [INF], [NAN]];
    }

    #[DataProvider('invalidos')]
    public function test_edad_y_escolaridad_no_admiten_conversiones_ni_relleno(mixed $valor): void
    {
        foreach (['edad_anios', 'escolaridad_anios'] as $campo) {
            $r = (new Norma)->normalizar(Norma::DSB, array_replace($this->captura(Norma::DSB), [$campo => $valor]));
            self::assertSame('NO_NORMALIZABLE', $r['componentes']['EJE-S03']['estado']);
            self::assertNull($r['componentes']['EJE-S03']['z']);
        }
    }

    public function test_no_intercambia_idioma_edicion_condicion_o_protocolo(): void
    {
        foreach (array_keys((new Norma)->metodos()) as $id) {
            foreach (['idioma' => 'en', 'instrumento' => 'OTRO', 'edicion_protocolo' => 'WAIS-IV', 'protocolo' => 'OTRO'] as $campo => $valor) {
                $r = (new Norma)->normalizar($id, array_replace($this->captura($id), [$campo => $valor]));
                $componente = array_values($r['componentes'])[0];
                foreach ($componente['medidas'] ?? [$componente] as $medida) {
                    self::assertSame('NO_NORMALIZABLE', $medida['estado']);
                }
            }
        }
    }

    public function test_modelos_que_usan_sexo_rechazan_ausencia_y_aceptan_cero_documentado(): void
    {
        foreach ([Norma::JLO, Norma::FCSRT, Norma::IMAGENES] as $id) {
            foreach ([null, '0', false, 2] as $sexo) {
                $r = (new Norma)->normalizar($id, array_replace($this->captura($id), ['sexo_norma' => $sexo]));
                self::assertNull(array_values($r['componentes'])[0]['z']);
            }
            $r = (new Norma)->normalizar($id, array_replace($this->captura($id), ['sexo_norma' => 0]));
            self::assertSame('NORMALIZADO', array_values($r['componentes'])[0]['estado']);
            self::assertSame(0, $r['bruto']['sexo_norma']);
        }
        $c = $this->captura(Norma::DSB);
        unset($c['sexo_norma']);
        self::assertSame('NORMALIZADO', (new Norma)->normalizar(Norma::DSB, $c)['componentes']['EJE-S03']['estado']);
    }

    public function test_no_confunde_longitud_con_total_jlo_corto_o_doble_conteo(): void
    {
        foreach ([[Norma::DSB, 'longitud_inversa', 9], [Norma::LN, 'longitud_letras_numeros', 20],
            [Norma::JLO, 'elementos_puntuados', 15], [Norma::JLO, 'aciertos', 31],
            [Norma::FCSRT, 'adicional_claves_1', 11], [Norma::IMAGENES, 'libre_1', 17]] as [$id, $campo, $valor]) {
            $r = (new Norma)->normalizar($id, array_replace($this->captura($id), [$campo => $valor]));
            self::assertSame('NO_NORMALIZABLE', array_values($r['componentes'])[0]['estado']);
        }
        foreach ([[Norma::DSB, 'longitud_inversa'], [Norma::LN, 'longitud_letras_numeros'], [Norma::JLO, 'aciertos']] as [$id, $campo]) {
            foreach ([null, '3', true, 2.5, -1, INF, NAN] as $valor) {
                $r = (new Norma)->normalizar($id, array_replace($this->captura($id), [$campo => $valor]));
                self::assertNull(array_values($r['componentes'])[0]['z']);
            }
            $r = (new Norma)->normalizar($id, array_replace($this->captura($id), [$campo => 0]));
            self::assertSame('NORMALIZADO', array_values($r['componentes'])[0]['estado']);
            self::assertSame(0, array_values($r['componentes'])[0]['puntuacion_bruta']);
        }
    }

    public function test_imagenes_recalcula_varianza_y_conserva_interaccion_de_sexo(): void
    {
        $n = new Norma;
        $c = $this->captura(Norma::IMAGENES);
        $mujer = $n->normalizar(Norma::IMAGENES, $c)['componentes']['MEM-S01'];
        $varon = $n->normalizar(Norma::IMAGENES, array_replace($c, ['sexo_norma' => 0]))['componentes']['MEM-S01'];
        self::assertEqualsWithDelta(25.972, $varon['esperado'], 1e-10);
        self::assertEqualsWithDelta(40.6314, $varon['varianza_residual'], 1e-10);
        self::assertEqualsWithDelta(46.12665, $mujer['varianza_residual'], 1e-10);
        self::assertNotEquals($mujer['de_residual'], $varon['de_residual']);
        self::assertArrayNotHasKey('percentil', $mujer);
        self::assertArrayNotHasKey('MEM-S03', $n->normalizar(Norma::IMAGENES, $c)['componentes']);
    }

    public function test_fuentes_tipadas_llegan_al_perfil_y_no_crean_diagnostico(): void
    {
        $id = Norma::JLO;
        $meta = (new Norma)->metodos()[$id];
        $c = array_replace($this->captura($id), ['sexo_norma' => 0]);
        $a = ['cod_aplicacion' => 'AP-NORMA-SINTETICA', 'cod_instrumento' => 'INST-SINTETICO',
            'codigo_instrumento' => 'CODIGO-SINTETICO', 'version_instrumento' => 'VERSION-SINTETICA'];
        $perfil = $a + ['id' => 'PERFIL-NORMA-SINTETICO', 'metodo' => $id,
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
        $e = (new ExtractorCapturaInstrumental)->extraer($perfil, $a, $respuestas, 81);
        self::assertSame([], $e['motivos_captura']);
        self::assertSame('0.0000', $e['fuentes_entradas']['sexo_norma']['bruto']);
        self::assertSame(0, $e['captura']['sexo_norma']);
        $capturas = ['disponible' => true, 'capturas' => [$a + ['fecha_hora' => '2026-10-19T09:00:00-04:00',
            'cod_personal' => 'PERSONAL-SINTETICO', 'extraccion' => $e]]];
        $red = ['dominios' => ['VIS' => ['nombre' => 'Percepción', 'componentes' => ['VIS-S01' => ['nombre' => 'Orientación de líneas']]]]];
        $r = (new PerfilInstrumentalCognitivo)->construir($red, $capturas);
        $m = $r['dominios']['VIS']['componentes']['VIS-S01'];
        self::assertSame('MEDICIONES_DISPONIBLES', $m['estado_medicion']);
        self::assertSame($e['fuentes_entradas'], $m['mediciones'][0]['fuentes_entradas']);
        self::assertNull($m['resultado_clinico']);
        self::assertNull($r['diagnostico']);
        self::assertFalse($r['persistido']);
    }

    public function test_catalogo_separa_referencias_de_base_y_brutos_sin_inflar_componentes(): void
    {
        $c = (new CatalogoCapturaCognitiva)->componentes();
        self::assertCount(19, $c);
        self::assertCount(8, array_filter($c, fn ($m) => $m['metodos_disponibles'] !== []));
        self::assertCount(4, array_filter($c, fn ($m) => $m['normalizadores_referencia_disponibles'] !== []));
        self::assertSame([Norma::DSB, Norma::LN, Norma::SPAN], $c['EJE-S03']['normalizadores_referencia_disponibles']);
        self::assertSame([], $c['EJE-S03']['metodos_disponibles']);
        self::assertSame('NORMALIZADOR_REFERENCIA_CON_CONTEXTO_EXPLICITO', $c['EJE-S03']['estado_metodo']);
        self::assertSame([], $c['MEM-S04']['normalizadores_referencia_disponibles']);
        self::assertSame([], $c['LEN-S03']['normalizadores_referencia_disponibles']);
        self::assertArrayNotHasKey('FCSRT-ES-SABIEX-2021-DIFERIDO', (new RegistroMetodosInstrumentales)->metodos());
    }

    public function test_ambas_longitudes_pueden_pertenecer_a_un_solo_perfil_sin_borrar_la_condicion_valida(): void
    {
        $c = $this->captura(Norma::SPAN);
        $registro = new RegistroMetodosInstrumentales;
        $r = $registro->ejecutar(Norma::SPAN, $c);
        $m = $r['componentes']['EJE-S03']['medidas'];
        self::assertCount(2, $m);
        self::assertEqualsWithDelta(-1.098360655737705, $m[0]['z'], 1e-10);
        self::assertEqualsWithDelta(.217674418604651, $m[1]['z'], 1e-10);
        self::assertSame($c, $r['contexto_aplicacion']);
        $r = $registro->ejecutar(Norma::SPAN, array_replace($c, ['longitud_inversa' => null]));
        self::assertSame('NO_NORMALIZABLE', $r['componentes']['EJE-S03']['medidas'][0]['estado']);
        self::assertSame('NORMALIZADO', $r['componentes']['EJE-S03']['medidas'][1]['estado']);
        self::assertNull($r['componentes']['EJE-S03']['medidas'][0]['z']);
        self::assertSame('COMPARACION_CON_PREDICHO', $r['componentes']['EJE-S03']['medidas'][1]['interpretacion_matematica']['estado']);
    }
}
