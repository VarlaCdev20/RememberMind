<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\dominio\instrumentos\NormalizadorReferenciaTokenColombia2017 as Token;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Cotejo de tablas originales, corrección escolar y rechazo de celdas ambiguas. */
final class NormalizacionTokenColombiaInvestigadaTest extends TestCase
{
    /** Captura sintética de un puntaje ya corregido; no reproduce consignas del instrumento. */
    private function captura(array $cambios = []): array
    {
        $meta = (new Token)->metadatos();
        $c = $meta['identidad'] + ['protocolo' => Token::METODO, 'edad_anios' => 81,
            'escolaridad_anios' => 6, 'puntaje_total' => 33, 'instrucciones_del_protocolo' => 36];
        foreach ($meta['entradas'] as $campo => $tipo) {
            if ($tipo === 'LOGICO') {
                $c[$campo] = true;
            }
        }

        return array_replace($c, $cambios);
    }

    /** Puntos visualmente contrastados en tablas 23–30 y 32, no calculados desde constantes PHP. */
    public static function ejemplos(): array
    {
        return [[60, 6, 34, 11, 12, 23], [65, 6, 34, 11, 12, 24],
            [66, 18, 34, 12, 11, 25], [71, 0, 34, 12, 14, 26],
            [74, 12, 34, 13, 13, 27], [77, 4, 34, 13, 14, 28],
            [80, 15, 34, 14, 13, 29], [90, 8, 34, 14, 14, 30],
            [81, 0, 36, 18, 20, 30], [81, 18, 0, 4, 3, 30]];
    }

    #[DataProvider('ejemplos')]
    public function test_consulta_tablas_sin_extrapolar_percentil_final(int $edad, int $educacion, int $bruto, int $a, int $ae, int $tabla): void
    {
        $c = $this->captura(['edad_anios' => $edad, 'escolaridad_anios' => $educacion, 'puntaje_total' => $bruto]);
        $r = (new RegistroMetodosInstrumentales)->ejecutar(Token::METODO, $c);
        $m = $r['componentes']['LEN-S03'];
        self::assertSame('NORMALIZADO', $m['estado']);
        self::assertSame($a, $m['escalar_edad']);
        self::assertSame($ae, $m['escalar_edad_escolaridad']);
        self::assertSame($tabla, $m['tabla_edad']);
        self::assertSame(32, $m['tabla_escolaridad']);
        self::assertNull($m['z']);
        self::assertNull($m['percentil']);
        self::assertNull($m['clasificacion_clinica']);
        self::assertSame('ESCALAR_TABULADO_EDAD_ESCOLARIDAD', $m['interpretacion_matematica']['estado']);
        self::assertSame('TESIS_MAESTRIA_ORIGINAL', $r['nivel_evidencia']);
        self::assertSame($c, $r['contexto_aplicacion']);
        self::assertNull($r['diagnostico']);
    }

    public function test_no_infiere_redondeo_y_conserva_celda_literal_con_escolaridad_doce(): void
    {
        $r = (new Token)->normalizar($this->captura(['escolaridad_anios' => 12, 'puntaje_total' => 30]));
        self::assertSame(10, $r['componentes']['LEN-S03']['escalar_edad_escolaridad']);
        self::assertSame('41–59', $r['componentes']['LEN-S03']['rango_percentil_edad']);
        // Floor del resultado continuo daría 9: no se sustituye la celda publicada 10 por esa elección.
        self::assertSame(9.0, floor(10 - .251 * (12 - 11)));
    }

    public function test_recorrido_de_medios_puntos_conserva_lagunas_y_orden(): void
    {
        $normalizadas = $rechazadas = 0;
        foreach ([60, 63, 66, 69, 72, 75, 78, 81] as $edad) {
            $anterior = 0;
            for ($doble = 0; $doble <= 72; $doble++) {
                $r = (new Token)->normalizar($this->captura(['edad_anios' => $edad, 'puntaje_total' => $doble / 2]));
                $m = $r['componentes']['LEN-S03'];
                $laguna = ($edad === 66 && $doble <= 44) || ($edad === 63 && $doble === 55);
                self::assertSame($laguna ? 'NO_NORMALIZABLE' : 'NORMALIZADO', $m['estado']);
                if ($laguna) {
                    $rechazadas++;
                    self::assertNull($m['escalar_edad_escolaridad']);
                    self::assertContains($edad === 66 ? 'CELDA_PUBLICADA_AMBIGUA_TABLA_25' : 'BRUTO_SIN_CORRESPONDENCIA_UNIVOCA_PUBLICADA', $m['motivos']);
                } else {
                    $normalizadas++;
                    self::assertGreaterThanOrEqual($anterior, $m['escalar_edad']);
                    $anterior = $m['escalar_edad'];
                }
            }
        }
        self::assertSame(538, $normalizadas);
        self::assertSame(46, $rechazadas);
    }

    public static function invalidos(): array
    {
        return [['edad_anios', 59], ['edad_anios', 91], ['edad_anios', '81'],
            ['escolaridad_anios', -1], ['escolaridad_anios', 19], ['escolaridad_anios', null],
            ['puntaje_total', -0.5], ['puntaje_total', 36.5], ['puntaje_total', 30.25],
            ['puntaje_total', '33'], ['puntaje_total', false], ['puntaje_total', NAN], ['puntaje_total', INF],
            ['instrucciones_del_protocolo', 16], ['instrucciones_del_protocolo', 36.0],
            ['instrumento', 'WAB'], ['idioma', 'pt'], ['protocolo', 'TOKEN-CHILE-2024']];
    }

    #[DataProvider('invalidos')]
    public function test_entrada_incompatible_se_conserva_sin_convertirse_en_resultado(string $campo, mixed $valor): void
    {
        $c = $this->captura([$campo => $valor]);
        $r = (new Token)->normalizar($c);
        $m = $r['componentes']['LEN-S03'];
        self::assertSame('NO_NORMALIZABLE', $m['estado']);
        self::assertNull($m['escalar_edad_escolaridad']);
        self::assertNull($m['percentil']);
        self::assertNotEmpty($m['motivos']);
        // Comparar NaN con identidad no tiene semántica de igualdad: verificar contexto por claves.
        self::assertSame(array_keys($c), array_keys($r['contexto_aplicacion']));
    }

    public function test_ningun_contexto_ausente_o_falso_se_presume_acreditado(): void
    {
        foreach ((new Token)->metadatos()['entradas'] as $campo => $tipo) {
            if ($tipo !== 'LOGICO') {
                continue;
            }
            foreach ([null, false, '1', 1] as $valor) {
                $r = (new Token)->normalizar($this->captura([$campo => $valor]));
                self::assertSame('NO_NORMALIZABLE', $r['componentes']['LEN-S03']['estado']);
            }
        }
        $r = (new Token)->normalizar([]);
        self::assertSame('NO_NORMALIZABLE', $r['componentes']['LEN-S03']['estado']);
        self::assertNull($r['componentes']['LEN-S03']['puntuacion_bruta']);
    }
}
