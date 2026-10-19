<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ArchivoConocimientoOrion;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\instrumentos\AplicabilidadGeriatrica;
use MotorExperto\dominio\instrumentos\PerfilInstrumentalCognitivo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AplicabilidadGeriatricaTest extends TestCase
{
    public static function edades(): array
    {
        return [[59, 18, 95, false, false], [60, 18, 95, true, true], [79, 60, 79, true, true],
            [80, 60, 79, true, false], [95, 18, 95, true, true], [96, 18, 95, true, false], [null, 18, 95, null, false]];
    }

    #[DataProvider('edades')]
    public function test_cohorte_y_limite_normativo_son_comprobaciones_distintas(?int $edad, int $minima, int $maxima, ?bool $cohorte, bool $compatible): void
    {
        $r = (new AplicabilidadGeriatrica)->evaluar(['edad_minima' => $minima, 'edad_maxima' => $maxima], ['edad_anios' => $edad]);
        self::assertSame($cohorte, $r['pertenece_a_poblacion_objetivo']);
        self::assertSame($compatible, $r['alcance_etario_compatible']);
        self::assertFalse($r['autoriza_interpretacion_clinica']);
    }

    public function test_no_ignora_celda_ambigua_de_edad_65_o_norma_sin_rango(): void
    {
        $r = (new AplicabilidadGeriatrica)->evaluar(['edad_minima' => 61, 'edad_maxima' => 86, 'edades_excluidas_por_conflicto' => [65]], ['edad_anios' => 65]);
        self::assertContains('EDAD_CON_AMBIGUEDAD_EN_FUENTE', $r['motivos']);
        self::assertFalse($r['alcance_etario_compatible']);
        $r = (new AplicabilidadGeriatrica)->evaluar([], ['edad_anios' => 100]);
        self::assertFalse($r['alcance_etario_compatible']);
        self::assertSame(['SIN_RANGO_ETARIO_NORMATIVO_IDENTIFICADO'], $r['motivos']);
    }

    public function test_metrica_cuenta_componentes_una_vez_y_no_incluye_aplicaciones_menores_60(): void
    {
        $red = (new PerfilConceptualCognitivo)->construir((new ArchivoConocimientoOrion)->leer());
        $app = ['cod_aplicacion' => 'SIM-A', 'fecha_hora' => '2026-10-18', 'cod_personal' => 'SIM-P', 'edad_en_aplicacion' => 70,
            'extraccion' => ['perfil' => 'SIM-PERFIL', 'metodo' => 'SIM-METODO', 'fuentes_entradas' => [], 'captura' => [],
                'normalizacion' => ['referencia' => 'SIM-REFERENCIA', 'componentes' => ['MEM-S01' => ['estado' => 'NORMALIZADO'], 'MEM-S02' => ['estado' => 'NORMALIZADO']]]]];
        $repetida = array_replace($app, ['cod_aplicacion' => 'SIM-B']);
        $menor = array_replace($app, ['cod_aplicacion' => 'SIM-C', 'edad_en_aplicacion' => 59]);
        $menor['extraccion']['normalizacion']['componentes'] = ['EJE-S03' => ['estado' => 'NORMALIZADO']];
        $desconocida = ['cod_aplicacion' => 'SIM-D', 'edad_en_aplicacion' => null, 'extraccion' => null];
        $r = (new PerfilInstrumentalCognitivo)->construir($red, ['disponible' => true, 'capturas' => [$app, $repetida, $menor, $desconocida]]);
        self::assertSame(['aplicaciones_examinadas' => 4, 'aplicaciones_60_o_mas' => 2, 'aplicaciones_menores_60' => 1,
            'aplicaciones_edad_desconocida' => 1, 'componentes_con_comparacion' => 2, 'componentes_del_perfil' => 19,
            'porcentaje_cobertura_comparacion' => 10.53, 'denominador' => 'COMPONENTES_DEL_PERFIL',
            'tipo_metrica' => 'COBERTURA_DE_DATOS_NO_VALIDEZ_DIAGNOSTICA'], $r['metricas_60_o_mas']);
        self::assertNull($r['diagnostico']);
    }
}
