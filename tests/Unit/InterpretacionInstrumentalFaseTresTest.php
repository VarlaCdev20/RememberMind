<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ArchivoConocimientoOrion;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\instrumentos\CalculadorReconocimientoHvlt;
use MotorExperto\dominio\instrumentos\CatalogoCapturaCognitiva;
use MotorExperto\dominio\instrumentos\ExplicacionMatematicaInstrumental;
use MotorExperto\dominio\instrumentos\NormalizadorHvltBolivia2015;
use MotorExperto\dominio\instrumentos\NormalizadorReferenciaColombia2019;
use MotorExperto\dominio\instrumentos\PerfilInstrumentalCognitivo;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Casos sintéticos independientes de los algoritmos de normalización. */
final class InterpretacionInstrumentalFaseTresTest extends TestCase
{
    /** Predichos transcritos de las tablas; z recalculados por separado con aritmética decimal. */
    public static function referenciasIndependientes(): array
    {
        return [
            [NormalizadorHvltBolivia2015::METODO, 'MEM-S01', null, 21, 16.713, 1.0175646807500593],
            [NormalizadorHvltBolivia2015::METODO, 'MEM-S02', null, 4, 5.222, -0.5041254125412541],
            ['BTA-BO-2015-NL', 'ATE-S03', null, 13, 10.812, 0.5779186476492340],
            ['TMT-B-BO-2015', 'EJE-S02', null, 200, 228.979, -0.4160003445256313],
            ['BNT-BO-2015-60', 'LEN-S01', null, 45, 45.102, -0.0116811726981219],
            ['ROCF-BO-2015-COPIA-A', 'VIS-S02', null, 22.5, 22.833, -0.0494946492271106],
            ['FLUIDEZ-BO-2015-FAS-ANIMALES', 'LEN-S02', 'f', 8, 7.380, 0.1908866995073892],
            ['FLUIDEZ-BO-2015-FAS-ANIMALES', 'LEN-S02', 'a', 9, 7.151, 0.5373437954083115],
            ['FLUIDEZ-BO-2015-FAS-ANIMALES', 'LEN-S02', 's', 10, 6.233, 1.1063142437591777],
            ['FLUIDEZ-BO-2015-FAS-ANIMALES', 'LEN-S02', 'animales', 12, 11.013, 0.2168277680140598],
        ];
    }

    #[DataProvider('referenciasIndependientes')]
    public function test_regresiones_con_oraculo_decimal_y_sin_reutilizar_formula_del_motor(string $metodo, string $componente, ?string $condicion, int|float $bruto, float $predicho, float $z): void
    {
        $registro = new RegistroMetodosInstrumentales;
        $c = $registro->metodos()[$metodo]['identidad'] + ['protocolo' => $metodo, 'edad_anios' => 70,
            'escolaridad_anios' => 4, 'espanol_nativo' => true, 'nacimiento_bolivia' => true,
            'residencia_bolivia' => true, 'lectoescritura' => true, 'administracion_estandarizada' => true,
            'audicion_compatible' => true, 'condiciones_sensoriales_motoras_compatibles' => true,
            'ensayos' => [6, 7, 8], 'recuerdo_diferido' => 4, 'demora_minutos' => 22,
            'forma_n_aciertos' => 6, 'forma_l_aciertos' => 7, 'segundos_parte_b' => 200,
            'errores_observados' => 1, 'finalizada' => true, 'aciertos_espontaneos' => 40,
            'aciertos_clave_semantica' => 5, 'puntaje_copia' => 22.5, 'elementos_revisados' => 18,
            'f_validas' => 8, 'a_validas' => 9, 's_validas' => 10, 'animales_validas' => 12,
            'f_segundos' => 60, 'a_segundos' => 60, 's_segundos' => 60, 'animales_segundos' => 60];
        $r = $registro->ejecutar($metodo, $c)['componentes'][$componente];
        $m = $condicion === null ? $r : $r['medidas'][$condicion];
        self::assertSame($bruto, $m['puntuacion_bruta']);
        self::assertEqualsWithDelta($predicho, $m['esperado'], 1e-10);
        self::assertEqualsWithDelta($z, $m['z'], 1e-12);
        self::assertSame('COMPARACION_CON_PREDICHO', $m['interpretacion_matematica']['estado']);
        self::assertNull($m['clasificacion_clinica']);
    }

    public function test_octavo_componente_stroop_con_oraculo_percentilar_sin_z(): void
    {
        $metodo = 'STROOP-BO-2015-GOLDEN';
        $c = (new RegistroMetodosInstrumentales)->metodos()[$metodo]['identidad'] + [
            'protocolo' => $metodo, 'edad_anios' => 70, 'escolaridad_anios' => 4,
            'espanol_nativo' => true, 'nacimiento_bolivia' => true, 'residencia_bolivia' => true,
            'lectoescritura' => true, 'administracion_estandarizada' => true,
            'condiciones_sensoriales_motoras_compatibles' => true,
            'palabras_correctas' => 60, 'colores_correctos' => 30, 'palabra_color_correctas' => 21,
            'segundos_palabras' => 45, 'segundos_colores' => 45, 'segundos_palabra_color' => 45];
        $m = (new RegistroMetodosInstrumentales)->ejecutar($metodo, $c)['componentes']['EJE-S01'];
        // 21 − (1800 / 90) = 1; entre 0 (P40) y 2.4 (P50), tabla A35.
        self::assertSame(1, $m['puntuacion_bruta']);
        self::assertSame(['inferior' => 40, 'superior' => 50, 'tipo' => 'APROXIMACION_TABLA_REDONDEADA_A35'], $m['percentil_intervalo']);
        self::assertNull($m['z']);
        self::assertSame('INTERVALO_PERCENTILAR', $m['interpretacion_matematica']['estado']);
    }

    public function test_bta_usa_coeficientes_publicados_y_no_el_ejemplo_aritmetico_discrepante(): void
    {
        $metodo = 'BTA-BO-2015-NL';
        $c = (new RegistroMetodosInstrumentales)->metodos()[$metodo]['identidad'] + [
            'protocolo' => $metodo, 'edad_anios' => 60, 'escolaridad_anios' => 7,
            'espanol_nativo' => true, 'nacimiento_bolivia' => true, 'residencia_bolivia' => true,
            'lectoescritura' => true, 'administracion_estandarizada' => true, 'audicion_compatible' => true,
            'forma_n_aciertos' => 6, 'forma_l_aciertos' => 6];
        $m = (new RegistroMetodosInstrumentales)->ejecutar($metodo, $c)['componentes']['ATE-S03'];
        self::assertEqualsWithDelta(11.402, $m['esperado'], 1e-12);
        self::assertNotEqualsWithDelta(11.424, $m['esperado'], 1e-12);
    }

    private function reconocimiento(): array
    {
        return ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es',
            'protocolo' => CalculadorReconocimientoHvlt::METODO, 'administracion_estandarizada' => true,
            'aciertos_reconocimiento' => 10, 'falsas_alarmas_relacionadas' => 2,
            'falsas_alarmas_no_relacionadas' => 1, 'demora_minutos' => 22];
    }

    public function test_reconocimiento_calcula_diferencia_sin_importar_normas_de_recuerdo(): void
    {
        $r = (new RegistroMetodosInstrumentales)->ejecutar(CalculadorReconocimientoHvlt::METODO, $this->reconocimiento());
        $m = $r['componentes']['MEM-S04'];
        self::assertSame(7, $m['puntuacion_bruta']);
        self::assertSame(3, $m['falsas_alarmas_total']);
        self::assertSame('CALCULADO_SIN_NORMA', $m['estado']);
        self::assertNull($m['z']);
        self::assertNull($m['esperado']);
        self::assertNull($m['clasificacion_clinica']);
        self::assertNull($r['diagnostico']);
        self::assertSame('DESCRIPTIVO_SIN_COMPARACION', $m['interpretacion_matematica']['estado']);
        self::assertSame('PENDIENTE', $r['revision_profesional']);
        self::assertSame($this->reconocimiento(), $r['contexto_aplicacion']);
    }

    public function test_diferencia_negativa_no_se_trunca_ni_se_acepta_como_indice_normativo(): void
    {
        $c = array_replace($this->reconocimiento(), ['aciertos_reconocimiento' => 0,
            'falsas_alarmas_relacionadas' => 6, 'falsas_alarmas_no_relacionadas' => 6]);
        $r = (new CalculadorReconocimientoHvlt)->calcular($c)['componentes']['MEM-S04'];
        self::assertSame(-12, $r['puntuacion_bruta']);
        self::assertTrue($r['fuera_rango_declarado_fuente']);
        self::assertContains('CONFLICTO_RANGO_DECLARADO_FITBIR', $r['advertencias']);
        self::assertNull($r['z']);
        $c = array_replace($c, ['aciertos_reconocimiento' => 12, 'falsas_alarmas_relacionadas' => 0, 'falsas_alarmas_no_relacionadas' => 0]);
        $r = (new CalculadorReconocimientoHvlt)->calcular($c)['componentes']['MEM-S04'];
        self::assertSame(12, $r['puntuacion_bruta']);
        self::assertFalse($r['fuera_rango_declarado_fuente']);
    }

    public static function reconocimientoInvalido(): array
    {
        return [['aciertos_reconocimiento', null], ['aciertos_reconocimiento', '10'], ['aciertos_reconocimiento', true],
            ['aciertos_reconocimiento', 13], ['falsas_alarmas_relacionadas', -1], ['falsas_alarmas_no_relacionadas', 7],
            ['demora_minutos', NAN], ['demora_minutos', INF], ['demora_minutos', 19], ['demora_minutos', 26],
            ['demora_minutos', '22'], ['administracion_estandarizada', null], ['forma', 1], ['idioma', 'ay'],
            ['protocolo', NormalizadorHvltBolivia2015::METODO]];
    }

    #[DataProvider('reconocimientoInvalido')]
    public function test_no_completa_contexto_ni_recuentos_invalidos(string $campo, mixed $valor): void
    {
        $r = (new CalculadorReconocimientoHvlt)->calcular(array_replace($this->reconocimiento(), [$campo => $valor]));
        self::assertSame('NO_CALCULABLE', $r['componentes']['MEM-S04']['estado']);
        self::assertNull($r['componentes']['MEM-S04']['puntuacion_bruta']);
        self::assertNotEmpty($r['componentes']['MEM-S04']['motivos']);
        if (is_float($valor) && is_nan($valor)) {
            self::assertNan($r['contexto_aplicacion'][$campo]);
        } else {
            self::assertSame($valor, $r['contexto_aplicacion'][$campo]);
        }
    }

    public function test_cero_valido_y_campo_ausente_no_tienen_el_mismo_resultado(): void
    {
        $c = array_replace($this->reconocimiento(), ['aciertos_reconocimiento' => 0,
            'falsas_alarmas_relacionadas' => 0, 'falsas_alarmas_no_relacionadas' => 0]);
        self::assertSame(0, (new CalculadorReconocimientoHvlt)->calcular($c)['componentes']['MEM-S04']['puntuacion_bruta']);
        unset($c['falsas_alarmas_relacionadas']);
        self::assertNull((new CalculadorReconocimientoHvlt)->calcular($c)['componentes']['MEM-S04']['puntuacion_bruta']);
    }

    public function test_catalogo_no_cuenta_calculo_descriptivo_como_noveno_normalizador(): void
    {
        $c = (new CatalogoCapturaCognitiva)->componentes();
        self::assertCount(19, $c);
        self::assertCount(8, array_filter($c, fn ($m) => $m['metodos_disponibles'] !== []));
        self::assertSame([], $c['MEM-S04']['metodos_disponibles']);
        self::assertSame([CalculadorReconocimientoHvlt::METODO], $c['MEM-S04']['calculos_descriptivos_disponibles']);
        // CERAD aporta otra referencia; el bruto HVLT sigue sin normalizarse con ella.
        self::assertSame('NORMALIZADOR_REFERENCIA_CON_CONTEXTO_EXPLICITO', $c['MEM-S04']['estado_metodo']);
        self::assertSame([NormalizadorReferenciaColombia2019::CERAD], $c['MEM-S04']['normalizadores_referencia_disponibles']);
    }

    public static function direcciones(): array
    {
        return [[-1.2, 'MAYOR_ES_MEJOR', 'INFERIOR_AL_PREDICHO', 'menor que'],
            [1.2, 'MAYOR_ES_MEJOR', 'SUPERIOR_AL_PREDICHO', 'mayor que'],
            [0.0, 'MAYOR_ES_MEJOR', 'IGUAL_AL_PREDICHO', 'coincide'],
            [-1.2, 'MENOR_ES_MEJOR', 'INFERIOR_AL_PREDICHO', 'más rápida'],
            [1.2, 'MENOR_ES_MEJOR', 'SUPERIOR_AL_PREDICHO', 'más lenta'],
            [0.0, 'MENOR_ES_MEJOR', 'IGUAL_AL_PREDICHO', 'coincide']];
    }

    #[DataProvider('direcciones')]
    public function test_explicacion_respeta_signo_y_direccion_sin_crear_cortes(float $z, string $direccion, string $posicion, string $texto): void
    {
        $original = ['estado' => 'NORMALIZADO', 'z' => $z, 'direccion' => $direccion,
            'clasificacion_clinica' => null, 'puntuacion_bruta' => 1, 'esperado' => 2];
        $r = (new ExplicacionMatematicaInstrumental)->agregar(['componentes' => ['TEST' => $original]]);
        $explicacion = $r['componentes']['TEST']['interpretacion_matematica'];
        self::assertSame($posicion, $explicacion['posicion']);
        self::assertStringContainsString($texto, $explicacion['texto']);
        self::assertNull($explicacion['clasificacion_clinica']);
        unset($r['componentes']['TEST']['interpretacion_matematica']);
        self::assertSame($original, $r['componentes']['TEST']);
    }

    public function test_explicacion_parcial_por_medida_no_produce_promedio_ni_normalidad(): void
    {
        $r = (new ExplicacionMatematicaInstrumental)->agregar(['componentes' => ['TEST' => [
            'estado' => 'PARCIALMENTE_NORMALIZADO', 'medidas' => [
                'ausente' => ['estado' => 'NO_NORMALIZABLE', 'z' => null],
                'indefinida' => ['estado' => 'NORMALIZADO', 'z' => INF, 'direccion' => 'MAYOR_ES_MEJOR'],
                'sin_direccion' => ['estado' => 'NORMALIZADO', 'z' => -1],
                'percentil' => ['estado' => 'NORMALIZADO', 'z' => null, 'percentil_intervalo' => ['inferior' => null, 'superior' => 5]],
            ]]]]);
        $m = $r['componentes']['TEST']['medidas'];
        foreach (['ausente', 'indefinida', 'sin_direccion'] as $id) {
            self::assertSame('INFORMACION_INSUFICIENTE', $m[$id]['interpretacion_matematica']['estado']);
        }
        self::assertSame('INTERVALO_PERCENTILAR', $m['percentil']['interpretacion_matematica']['estado']);
        self::assertArrayNotHasKey('interpretacion_matematica', $r['componentes']['TEST']);
    }

    public function test_perfil_conserva_reconocimiento_sin_convertirlo_en_medicion_normalizada(): void
    {
        $red = (new PerfilConceptualCognitivo)->construir((new ArchivoConocimientoOrion)->leer());
        $resultado = (new RegistroMetodosInstrumentales)->ejecutar(CalculadorReconocimientoHvlt::METODO, $this->reconocimiento());
        $e = ['perfil' => 'SINTETICO', 'metodo' => CalculadorReconocimientoHvlt::METODO,
            'fuentes_entradas' => [], 'captura' => $this->reconocimiento(), 'normalizacion' => $resultado];
        $r = (new PerfilInstrumentalCognitivo)->construir($red, ['disponible' => true, 'capturas' => [
            ['cod_aplicacion' => 'SINTETICA', 'fecha_hora' => '2026-10-19T10:00:00-04:00', 'cod_personal' => 'AUTOR-SINTETICO', 'extraccion' => $e]]]);
        $c = $r['dominios']['COG-MEM']['componentes']['MEM-S04'];
        self::assertSame('SIN_MEDICION_NORMALIZADA', $c['estado_medicion']);
        self::assertSame($resultado['componentes']['MEM-S04'], $c['mediciones'][0]['resultado']);
        self::assertNull($c['resultado_clinico']);
        self::assertFalse($r['persistido']);
        self::assertNull($r['diagnostico']);
    }
}
