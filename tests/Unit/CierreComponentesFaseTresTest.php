<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\dominio\instrumentos\CatalogoCapturaCognitiva;
use MotorExperto\dominio\instrumentos\NormalizadorReferenciaBarcelona2011 as Barcelona;
use MotorExperto\dominio\instrumentos\NormalizadorReferenciaCancelacionScand2023 as Scand;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Ejemplos externos y fronteras de las tres comparaciones que completan la matriz técnica. */
final class CierreComponentesFaseTresTest extends TestCase
{
    /** Entradas sintéticas; identidad y contexto se documentan expresamente para cada método. */
    private function captura(string $metodo, array $cambios = []): array
    {
        $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];
        $c = $meta['identidad'] + ['protocolo' => $metodo, 'edad_anios' => 81,
            'repeticion_palabras_aciertos' => 9, 'repeticion_elementos_administrados' => 10, 'fluencia_gramatica' => 10,
            'aciertos' => 20, 'omisiones_corregidas' => 5, 'comisiones' => 3, 'objetivos_del_protocolo' => 65,
            'filas_del_protocolo' => 26, 'columnas_del_protocolo' => 30, 'segundos' => 60, 'nivel_educativo_scand' => 2];
        foreach ($meta['entradas'] as $campo => $tipo) {
            if ($tipo === 'LOGICO') {
                $c[$campo] = true;
            }
        }

        return array_replace($c, $cambios);
    }

    /** Cálculo manual y celdas del original SCAND, tablas 7–8; no usan constantes del normalizador. */
    public static function ejemplosScand(): array
    {
        return [[60, 1, 20, 5, 3, 12, 10, 1, 11], [60, 4, 20, 5, 3, 12, 10, -2, 8],
            [65, 3, 20, 5, 3, 12, 10, 0, 10], [66, 2, 20, 5, 3, 12, 10, 1, 11],
            [75, 3, 20, 5, 3, 12, 10, 1, 11], [81, 2, 20, 5, 3, 12, 10, 2, 12],
            [94, 4, 20, 5, 3, 12, 10, 2, 12], [98, 1, 20, 5, 3, 12, 10, 5, 15],
            [81, 2, 3, 11, 2, -10, 5, 2, 7], [81, 2, 0, 0, 0, 0, 6, 2, 8],
            [81, 2, 28, 0, 0, 28, 18, 2, 20], [81, 2, 0, 40, 2, -42, 2, 2, 4]];
    }

    #[DataProvider('ejemplosScand')]
    public function test_eficiencia_conserva_signo_y_consulta_correccion_exacta(int $edad, int $nivel, int $a, int $o, int $c, int $bruto, int $base, int $ajuste, int $final): void
    {
        $captura = $this->captura(Scand::METODO, ['edad_anios' => $edad, 'nivel_educativo_scand' => $nivel,
            'aciertos' => $a, 'omisiones_corregidas' => $o, 'comisiones' => $c]);
        $r = (new RegistroMetodosInstrumentales)->ejecutar(Scand::METODO, $captura);
        $m = $r['componentes']['ATE-S02'];
        self::assertSame('NORMALIZADO', $m['estado']);
        self::assertSame($bruto, $m['puntuacion_bruta']);
        self::assertSame($base, $m['escalar_base']);
        self::assertSame($ajuste, $m['ajuste_demografico']);
        self::assertSame($final, $m['escalar_ajustado']);
        self::assertSame('ESCALAR_AJUSTADO_TABULADO', $m['interpretacion_matematica']['estado']);
        self::assertNull($m['percentil']);
        self::assertNull($m['z']);
        self::assertNull($m['clasificacion_clinica']);
        self::assertNull($r['diagnostico']);
        self::assertSame($captura, $r['contexto_aplicacion']);
        self::assertSame(['ATE-S02'], array_keys($r['componentes']));
    }

    /** Las fronteras se enumeran desde los intervalos publicados, incluyendo eficiencia cero. */
    public static function fronterasScand(): array
    {
        return [[-42, 2], [-31, 2], [-30, 3], [-25, 3], [-24, 4], [-15, 4], [-14, 5], [-6, 5],
            [-5, 6], [0, 6], [1, 7], [3, 7], [4, 8], [6, 8], [7, 9], [9, 9], [10, 10], [12, 10],
            [13, 11], [14, 11], [15, 12], [16, 12], [17, 13], [18, 13], [19, 14], [20, 14],
            [21, 15], [22, 15], [23, 16], [24, 17], [25, 18], [28, 18]];
    }

    #[DataProvider('fronterasScand')]
    public function test_fronteras_eficiencia_sin_interpolar(int $bruto, int $base): void
    {
        $c = $this->captura(Scand::METODO, ['aciertos' => max(0, $bruto), 'omisiones_corregidas' => max(0, -$bruto), 'comisiones' => 0]);
        $m = (new Scand)->normalizar($c)['componentes']['ATE-S02'];
        self::assertSame($bruto, $m['puntuacion_bruta']);
        self::assertSame($base, $m['escalar_base']);
        self::assertSame($base + 2, $m['escalar_ajustado']);
        if ($base === 2) {
            self::assertNull($m['rango_percentil_sin_ajuste']);
        }
    }

    public function test_todas_las_edades_y_categorias_escolares_disponen_de_celda_sin_efecto_sexo(): void
    {
        foreach (range(60, 98) as $edad) {
            foreach (range(1, 4) as $nivel) {
                $c = $this->captura(Scand::METODO, ['edad_anios' => $edad, 'nivel_educativo_scand' => $nivel]);
                $r = (new Scand)->normalizar($c);
                self::assertSame('NORMALIZADO', $r['componentes']['ATE-S02']['estado']);
                self::assertSame($r['componentes'], (new Scand)->normalizar($c + ['sexo_norma' => 1])['componentes']);
            }
        }
    }

    public function test_barcelona_conserva_regla_publicada_sin_simular_z_y_sin_extrapolar_a_wab(): void
    {
        foreach (range(0, 10) as $bruto) {
            $c = $this->captura(Barcelona::METODO, ['repeticion_palabras_aciertos' => $bruto, 'fluencia_gramatica' => $bruto]);
            $r = (new RegistroMetodosInstrumentales)->ejecutar(Barcelona::METODO, $c);
            foreach ($r['componentes'] as $m) {
                self::assertSame('NORMALIZADO', $m['estado']);
                self::assertSame($bruto === 10 ? 18 : 2, $m['escalar_publicado']);
                self::assertSame($bruto === 10 ? '>99' : '<1', $m['rango_percentil_publicado']);
                self::assertSame('CONVERSION_PUBLICADA_CON_EFECTO_TECHO', $m['interpretacion_matematica']['estado']);
                self::assertNull($m['z']);
                self::assertNull($m['clasificacion_clinica']);
                self::assertContains('NO_INTERPRETAR_MAXIMO_COMO_CAPACIDAD_SUPERIOR', $m['advertencias']);
            }
            self::assertSame($c, $r['contexto_aplicacion']);
            self::assertNull($r['diagnostico']);
        }
    }

    public function test_barcelona_limita_edad_preserva_subprueba_valida_y_no_aplica_ajuste_inexistente(): void
    {
        foreach ([50, 60, 81, 90] as $edad) {
            $r = (new Barcelona)->normalizar($this->captura(Barcelona::METODO, ['edad_anios' => $edad]));
            self::assertSame(18, $r['componentes']['LEN-S05']['escalar_publicado']);
            self::assertSame(2, $r['componentes']['LEN-S04']['escalar_publicado']);
        }
        $c = $this->captura(Barcelona::METODO, ['muestra_discurso_documentada' => null]);
        $r = (new Barcelona)->normalizar($c);
        self::assertSame('NORMALIZADO', $r['componentes']['LEN-S04']['estado']);
        self::assertSame('NO_NORMALIZABLE', $r['componentes']['LEN-S05']['estado']);
        $c = $this->captura(Barcelona::METODO, ['repeticion_elementos_administrados' => 9]);
        $r = (new Barcelona)->normalizar($c);
        self::assertSame('NO_NORMALIZABLE', $r['componentes']['LEN-S04']['estado']);
        self::assertSame('NORMALIZADO', $r['componentes']['LEN-S05']['estado']);
        self::assertSame($r['componentes'], (new Barcelona)->normalizar($c + ['escolaridad_anios' => 0, 'sexo_norma' => 0])['componentes']);
    }

    public static function invalidos(): array
    {
        return [[Scand::METODO, 'edad_anios', 59], [Scand::METODO, 'edad_anios', 99],
            [Scand::METODO, 'edad_anios', '81'], [Scand::METODO, 'nivel_educativo_scand', 0],
            [Scand::METODO, 'nivel_educativo_scand', 5], [Scand::METODO, 'segundos', 59],
            [Scand::METODO, 'segundos', '60'], [Scand::METODO, 'segundos', INF],
            [Scand::METODO, 'aciertos', 29], [Scand::METODO, 'comisiones', 59],
            [Scand::METODO, 'omisiones_corregidas', 65], [Scand::METODO, 'aciertos', -1],
            [Scand::METODO, 'aciertos', 66], [Scand::METODO, 'comisiones', 716],
            [Scand::METODO, 'filas_del_protocolo', 20], [Scand::METODO, 'columnas_del_protocolo', 20],
            [Scand::METODO, 'objetivos_del_protocolo', 16], [Scand::METODO, 'instrumento', 'Ruff'],
            [Scand::METODO, 'idioma', 'en'], [Barcelona::METODO, 'edad_anios', 49],
            [Barcelona::METODO, 'edad_anios', 91], [Barcelona::METODO, 'edad_anios', null],
            [Barcelona::METODO, 'instrumento', 'WAB'], [Barcelona::METODO, 'edicion_protocolo', 'TB-R'],
            [Barcelona::METODO, 'idioma', 'pt']];
    }

    #[DataProvider('invalidos')]
    public function test_contexto_incompatible_no_genera_comparacion(string $metodo, string $campo, mixed $valor): void
    {
        // 29 aciertos y ocho errores producen 21: para probar fuera de soporte eliminar errores.
        $c = $this->captura($metodo, [$campo => $valor]);
        if ($metodo === Scand::METODO && $campo === 'aciertos' && $valor === 29) {
            $c['omisiones_corregidas'] = $c['comisiones'] = 0;
        }
        $r = (new RegistroMetodosInstrumentales)->ejecutar($metodo, $c);
        foreach ($r['componentes'] as $m) {
            self::assertSame('NO_NORMALIZABLE', $m['estado']);
            self::assertNotEmpty($m['motivos']);
            self::assertSame('INFORMACION_INSUFICIENTE', $m['interpretacion_matematica']['estado']);
            self::assertNull($m['z']);
        }
    }

    public function test_contexto_desconocido_y_recaptura_no_fabrican_normalidad(): void
    {
        foreach ([Scand::METODO, Barcelona::METODO] as $metodo) {
            $meta = (new RegistroMetodosInstrumentales)->metodos()[$metodo];
            foreach ($meta['entradas'] as $campo => $tipo) {
                if ($tipo !== 'LOGICO') {
                    continue;
                }
                foreach ([null, false, '1', 1] as $valor) {
                    $r = (new RegistroMetodosInstrumentales)->ejecutar($metodo, $this->captura($metodo, [$campo => $valor]));
                    $componentes = $metodo === Barcelona::METODO && in_array($campo, ['muestra_discurso_documentada', 'tareas_habla_espontanea_completadas'], true)
                        ? [$r['componentes']['LEN-S05']] : $r['componentes'];
                    foreach ($componentes as $m) {
                        self::assertSame('NO_NORMALIZABLE', $m['estado']);
                    }
                }
            }
            $r = (new RegistroMetodosInstrumentales)->ejecutar($metodo, []);
            foreach ($r['componentes'] as $m) {
                self::assertSame('NO_NORMALIZABLE', $m['estado']);
                self::assertNull($m['puntuacion_bruta']);
            }
        }
        foreach (['repeticion_palabras_aciertos', 'fluencia_gramatica'] as $campo) {
            foreach ([null, true, '10', 9.5, -1, 11, NAN] as $valor) {
                $r = (new Barcelona)->normalizar($this->captura(Barcelona::METODO, [$campo => $valor]));
                self::assertSame('NO_NORMALIZABLE', $r['componentes'][$campo === 'fluencia_gramatica' ? 'LEN-S05' : 'LEN-S04']['estado']);
            }
        }
    }

    public function test_diecinueve_componentes_tienen_comparacion_identificada_sin_contar_brutos(): void
    {
        $c = (new CatalogoCapturaCognitiva)->componentes();
        self::assertCount(19, $c);
        self::assertCount(19, array_filter($c, fn ($v) => $v['metodos_disponibles'] !== [] || $v['normalizadores_referencia_disponibles'] !== []));
        self::assertSame([Scand::METODO], $c['ATE-S02']['normalizadores_referencia_disponibles']);
        self::assertSame([Barcelona::METODO], $c['LEN-S04']['normalizadores_referencia_disponibles']);
        self::assertSame([Barcelona::METODO], $c['LEN-S05']['normalizadores_referencia_disponibles']);
        foreach ([Scand::METODO, Barcelona::METODO] as $id) {
            $meta = (new RegistroMetodosInstrumentales)->metodos()[$id];
            self::assertSame('NO_CONFIGURADA', $meta['adopcion_institucional']);
            self::assertFalse($meta['reproduce_reactivos']);
        }
    }
}
