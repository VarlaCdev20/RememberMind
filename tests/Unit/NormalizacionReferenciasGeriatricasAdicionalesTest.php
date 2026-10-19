<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\dominio\instrumentos\CatalogoCapturaCognitiva;
use MotorExperto\dominio\instrumentos\NormalizadorReferenciaColombia2019 as Colombia;
use MotorExperto\dominio\instrumentos\NormalizadorReferenciaTolF2019 as Tol;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Aritmética con resultados externos, selección de estratos y abstenciones geriátricas. */
final class NormalizacionReferenciasGeriatricasAdicionalesTest extends TestCase
{
    /** Entradas sintéticas: documentar un contexto no lo infiere del nombre de la persona. */
    private function captura(string $metodo): array
    {
        $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];
        $c = $meta['identidad'] + ['protocolo' => $metodo, 'edad_anios' => 70,
            'escolaridad_anios' => 6, 'sexo_norma' => 1, 'nivel_educativo_ghs' => 4,
            'aciertos_minimos_movimientos' => 12, 'problemas_del_protocolo' => 24,
            'aciertos' => $metodo === Colombia::RAVEN ? 7 : 12, 'elementos_puntuados' => 12,
            'objetivos_presentados' => $metodo === Colombia::CANCELACION ? 16 : 10,
            'distractores_presentados' => 10, 'aciertos_objetivos' => 9, 'falsas_alarmas' => 2,
            'demora_minutos' => 7, 'tiempo_segundos' => 92.5];
        foreach ($meta['entradas'] as $campo => $tipo) {
            if ($tipo === 'LOGICO') {
                $c[$campo] = true;
            }
        }

        return $c;
    }

    /** Literales calculados con Decimal desde las parejas publicadas en tablas 10–11. */
    public static function ejemplosColombia(): array
    {
        return [[Colombia::RAVEN, 61, 4, 7, 8.43, 1.94, -.737113402061856],
            [Colombia::RAVEN, 64, 5, 7, 8.16, 2.00, -.58],
            [Colombia::RAVEN, 66, 4, 7, 8.06, 2.11, -.502369668246445],
            [Colombia::RAVEN, 86, 25, 7, 7.71, 2.26, -.314159292035398],
            [Colombia::CERAD, 61, 4, 7, 9.76, .54, -5.111111111111111],
            [Colombia::CERAD, 64, 5, 7, 9.45, 1.09, -2.247706422018349],
            [Colombia::CERAD, 66, 1, 7, 9.35, 1.17, -2.008547008547009],
            [Colombia::CERAD, 86, 25, 7, 9.37, 1.30, -1.823076923076923],
            [Colombia::CANCELACION, 61, 4, 12, 12.00, 2.83, 0.0],
            [Colombia::CANCELACION, 64, 5, 12, 13.67, 2.31, -.722943722943723],
            [Colombia::CANCELACION, 66, 5, 12, 13.83, 1.94, -.943298969072165]];
    }

    #[DataProvider('ejemplosColombia')]
    public function test_compara_con_media_sin_suponer_percentiles_normales(string $metodo, int $edad, int $educacion, int $bruto, float $media, float $de, float $z): void
    {
        $c = array_replace($this->captura($metodo), ['edad_anios' => $edad, 'escolaridad_anios' => $educacion]);
        $r = (new RegistroMetodosInstrumentales)->ejecutar($metodo, $c);
        $m = array_values($r['componentes'])[0];
        self::assertSame('NORMALIZADO', $m['estado']);
        self::assertSame($bruto, $m['puntuacion_bruta']);
        self::assertEqualsWithDelta($media, $m['media_referencia'], 1e-10);
        self::assertEqualsWithDelta($de, $m['de_referencia'], 1e-10);
        self::assertEqualsWithDelta($z, $m['z'], 1e-10);
        self::assertNull($m['de_residual']);
        self::assertNull($m['percentil']);
        self::assertNull($m['clasificacion_clinica']);
        self::assertSame('COMPARACION_CON_MEDIA', $m['interpretacion_matematica']['estado']);
        self::assertSame($c, $r['contexto_aplicacion']);
        self::assertNull($r['diagnostico']);
        self::assertSame('NO_CONFIGURADA', $r['adopcion_institucional']);
    }

    public function test_celda_vacia_y_edad_solapada_no_se_rellenan_con_otra_referencia(): void
    {
        $c = array_replace($this->captura(Colombia::CANCELACION), ['escolaridad_anios' => 4]);
        $m = array_values((new Colombia)->normalizar(Colombia::CANCELACION, $c)['componentes'])[0];
        self::assertSame('NO_NORMALIZABLE', $m['estado']);
        self::assertContains('CELDA_NORMATIVA_SIN_DATOS_PUBLICADOS', $m['motivos']);
        self::assertSame(17, $m['estrato']['n_grupo_educativo']);
        self::assertNull($m['z']);
        foreach (array_keys((new Colombia)->metodos()) as $id) {
            $c = array_replace($this->captura($id), ['edad_anios' => 65]);
            $m = array_values((new Colombia)->normalizar($id, $c)['componentes'])[0];
            self::assertContains('SOLAPAMIENTO_PUBLICADO_ENTRE_TABLAS_A_LOS_65_ANIOS', $m['motivos']);
            self::assertNull($m['z']);
        }
    }

    public function test_reconocimiento_cerad_trunca_solo_segun_su_procedimiento(): void
    {
        $c = array_replace($this->captura(Colombia::CERAD), ['aciertos_objetivos' => 2, 'falsas_alarmas' => 9]);
        $r = (new Colombia)->normalizar(Colombia::CERAD, $c);
        self::assertSame(0, $r['componentes']['MEM-S04']['puntuacion_bruta']);
        self::assertSame(2, $r['bruto']['aciertos_objetivos']);
        self::assertSame(9, $r['bruto']['falsas_alarmas']);
    }

    /** Puntos cotejados visualmente en p. 527–528; no se obtienen de la constante PHP. */
    public static function estratosTol(): array
    {
        return [[0, 2, 60, 35, 313], [0, 3, 65, 46, 312], [0, 2, 70, 59, 357], [0, 3, 79, 63, 247],
            [0, 4, 60, 24, 251], [0, 5, 65, 32, 216], [0, 4, 70, 49, 175], [0, 5, 79, 63, 140],
            [1, 2, 60, 55, 398], [1, 3, 65, 58, 395], [1, 2, 70, 74, 413], [1, 3, 79, 81, 254],
            [1, 4, 60, 41, 123], [1, 5, 65, 49, 104], [1, 4, 70, 52, 83], [1, 5, 79, 70, 50]];
    }

    #[DataProvider('estratosTol')]
    public function test_consulta_percentil_y_n_en_el_estrato_exactamente_publicado(int $sexo, int $nivel, int $edad, int $p, int $n): void
    {
        $c = array_replace($this->captura(Tol::METODO), ['sexo_norma' => $sexo, 'nivel_educativo_ghs' => $nivel, 'edad_anios' => $edad]);
        $r = (new RegistroMetodosInstrumentales)->ejecutar(Tol::METODO, $c);
        $m = $r['componentes']['EJE-S04'];
        self::assertSame('NORMALIZADO', $m['estado']);
        self::assertSame($p, $m['percentil_publicado']);
        self::assertSame($n, $m['estrato']['n']);
        self::assertSame('PERCENTIL_PUBLICADO', $m['interpretacion_matematica']['estado']);
        self::assertSame($n < 100, in_array('CELDA_CON_MENOS_DE_CIEN_PARTICIPANTES', $m['advertencias'], true));
        self::assertNull($m['z']);
        self::assertNull($m['clasificacion_clinica']);
        self::assertNull($r['diagnostico']);
        self::assertSame($c, $r['contexto_aplicacion']);
    }

    public function test_todas_las_celdas_tol_son_monotonas_y_no_invierten_cero_o_cien_en_z(): void
    {
        foreach (self::estratosTol() as [$sexo, $nivel, $edad]) {
            $anterior = -1;
            for ($bruto = 1; $bruto <= 24; $bruto++) {
                $c = array_replace($this->captura(Tol::METODO), ['sexo_norma' => $sexo, 'nivel_educativo_ghs' => $nivel,
                    'edad_anios' => $edad, 'aciertos_minimos_movimientos' => $bruto]);
                $m = (new Tol)->normalizar($c)['componentes']['EJE-S04'];
                self::assertSame('NORMALIZADO', $m['estado']);
                self::assertGreaterThanOrEqual($anterior, $m['percentil_publicado']);
                self::assertLessThanOrEqual(100, $m['percentil_publicado']);
                self::assertNull($m['z']);
                $anterior = $m['percentil_publicado'];
            }
        }
    }

    public static function entradasInvalidas(): array
    {
        return [[Tol::METODO, 'edad_anios', 59], [Tol::METODO, 'edad_anios', 80],
            [Tol::METODO, 'nivel_educativo_ghs', 1], [Tol::METODO, 'nivel_educativo_ghs', 6],
            [Tol::METODO, 'sexo_norma', null], [Tol::METODO, 'problemas_del_protocolo', 10],
            [Tol::METODO, 'aciertos_minimos_movimientos', 0], [Tol::METODO, 'aciertos_minimos_movimientos', 25],
            [Colombia::RAVEN, 'edad_anios', 60], [Colombia::RAVEN, 'edad_anios', 87],
            [Colombia::RAVEN, 'escolaridad_anios', 0], [Colombia::RAVEN, 'escolaridad_anios', 26],
            [Colombia::RAVEN, 'elementos_puntuados', 60], [Colombia::RAVEN, 'aciertos', 13],
            [Colombia::CERAD, 'demora_minutos', 20], [Colombia::CERAD, 'distractores_presentados', 12],
            [Colombia::CERAD, 'objetivos_presentados', 12], [Colombia::CERAD, 'falsas_alarmas', 11],
            [Colombia::CERAD, 'aciertos_objetivos', -1], [Colombia::CANCELACION, 'aciertos', 17],
            [Colombia::CANCELACION, 'tiempo_segundos', 0], [Colombia::CANCELACION, 'tiempo_segundos', INF],
            [Colombia::CANCELACION, 'tiempo_segundos', '92.5'], [Colombia::CANCELACION, 'objetivos_presentados', 15]];
    }

    #[DataProvider('entradasInvalidas')]
    public function test_abstencion_no_fabrica_z_percentil_ni_clasificacion(string $metodo, string $campo, mixed $valor): void
    {
        $r = (new RegistroMetodosInstrumentales)->ejecutar($metodo, array_replace($this->captura($metodo), [$campo => $valor]));
        $m = array_values($r['componentes'])[0];
        self::assertSame('NO_NORMALIZABLE', $m['estado']);
        self::assertNotEmpty($m['motivos']);
        self::assertNull($m['z']);
        self::assertNull($m['percentil_publicado'] ?? null);
        self::assertNull($m['clasificacion_clinica']);
        self::assertSame('INFORMACION_INSUFICIENTE', $m['interpretacion_matematica']['estado']);
    }

    public function test_identidad_contexto_tipos_y_ausencia_no_se_inventan(): void
    {
        foreach ([Tol::METODO, Colombia::RAVEN, Colombia::CERAD, Colombia::CANCELACION] as $metodo) {
            $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];
            $campos = array_keys($meta['identidad'] + ['protocolo' => $metodo, 'edad_anios' => 70]);
            foreach ($meta['entradas'] as $campo => $tipo) {
                if (in_array($tipo, ['LOGICO', 'ENTERO'], true)) {
                    $campos[] = $campo;
                }
            }
            foreach ($campos as $campo) {
                foreach ([null, false, '1', 1.5, NAN] as $invalido) {
                    $r = (new RegistroMetodosInstrumentales)->ejecutar($metodo, array_replace($this->captura($metodo), [$campo => $invalido]));
                    $m = array_values($r['componentes'])[0];
                    self::assertSame('NO_NORMALIZABLE', $m['estado'], $metodo.':'.$campo);
                    self::assertNull($m['z']);
                    self::assertNull($m['percentil_publicado'] ?? null);
                }
            }
            $vacio = (new RegistroMetodosInstrumentales)->ejecutar($metodo, []);
            self::assertSame('NO_NORMALIZABLE', array_values($vacio['componentes'])[0]['estado']);
        }
    }

    public function test_catalogo_cuenta_componentes_y_no_perfiles_ni_otro_instrumento(): void
    {
        $c = (new CatalogoCapturaCognitiva)->componentes();
        self::assertCount(19, $c);
        self::assertCount(16, array_filter($c, fn ($v) => $v['metodos_disponibles'] !== [] || $v['normalizadores_referencia_disponibles'] !== []));
        foreach (['EJE-S04' => Tol::METODO, 'EJE-S05' => Colombia::RAVEN,
            'MEM-S04' => Colombia::CERAD, 'ATE-S01' => Colombia::CANCELACION] as $componente => $metodo) {
            self::assertContains($metodo, $c[$componente]['normalizadores_referencia_disponibles']);
            self::assertNotContains($metodo, $c['ATE-S02']['normalizadores_referencia_disponibles']);
        }
    }
}
