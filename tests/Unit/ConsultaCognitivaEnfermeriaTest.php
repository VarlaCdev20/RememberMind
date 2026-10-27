<?php

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ConsultaCognitivaEnfermeria;
use PHPUnit\Framework\TestCase;

class ConsultaCognitivaEnfermeriaTest extends TestCase
{
    private function captura(string $id, string $version = '1', mixed $z = 0): array
    {
        return ['cod_aplicacion' => $id, 'cod_instrumento' => 'INSTRUMENTO_SINTETICO', 'version_instrumento' => $version, 'nombre_instrumento' => 'Prueba sintética', 'autor' => 'Profesional de prueba', 'fecha_hora' => '2026-10-20T09:00:00-04:00', 'motivos' => [],
            'extraccion' => ['perfil' => 'PERFIL_SINTETICO', 'metodo' => 'METODO_SINTETICO', 'normalizacion' => ['referencia' => 'Referencia de prueba', 'componentes' => ['MEM-S01' => ['estado' => 'NORMALIZADO', 'z' => $z, 'puntuacion_bruta' => 10]]]]];
    }

    public function test_series_no_mezclan_versiones_ni_fabrican_valores_ausentes(): void
    {
        $entrada = ['capturas' => [$this->captura('a'), $this->captura('b'), $this->captura('c', '2'), $this->captura('d', '1', null)]];
        $antes = $entrada;
        $s = (new ConsultaCognitivaEnfermeria)->series($entrada, 'COG-MEM');
        self::assertCount(2, $s);
        self::assertCount(2, $s[0]['puntos']);
        self::assertSame(0.0, $s[0]['puntos'][0]['valor']);
        self::assertSame($antes, $entrada);
        self::assertSame([], (new ConsultaCognitivaEnfermeria)->series($entrada, 'COG-ATE'));
        self::assertSame([], (new ConsultaCognitivaEnfermeria)->series(null, 'COG-MEM'));
    }

    public function test_series_solo_admiten_fuentes_validas_y_referencia_identica(): void
    {
        $a = $this->captura('a');
        $b = $this->captura('b');
        $b['extraccion']['normalizacion']['referencia'] = 'Otra referencia';
        $c = $this->captura('c');
        $c['motivos'] = ['ORIGINAL_NO_VERIFICABLE'];
        $d = $this->captura('d', '1', INF);
        $s = (new ConsultaCognitivaEnfermeria)->series(['capturas' => [$a, $b, $c, $d]], 'COG-MEM');
        self::assertCount(2, $s);
        self::assertCount(1, $s[0]['puntos']);
        self::assertCount(1, $s[1]['puntos']);
    }

    public function test_detalle_conserva_procedencia_actual_sin_publicar_identificadores_internos(): void
    {
        $perfil = ['capturas_originales' => ['capturas' => [$this->captura('a')]], 'dominios' => ['COG-MEM' => ['componentes' => ['MEM-S01' => [
            'nombre' => 'Aprendizaje de lista', 'estado_medicion' => 'MEDICIONES_DISPONIBLES', 'concepto' => [], 'mediciones' => [[
                'aplicacion' => 'a', 'fecha' => '2026-10-20', 'metodo' => 'Método sintético', 'referencia' => 'Referencia sintética',
                'resultado' => ['estado' => 'NORMALIZADO', 'z' => 0, 'puntuacion_bruta' => 10],
            ]],
        ]]]]];
        $informe = ['actual' => ['estado' => 'Sin evaluación'], 'areas' => [], 'narrativa' => ['conclusion' => '', 'resumen' => '']];
        $dato = (new ConsultaCognitivaEnfermeria)->presentar(['evaluacion' => null], $informe, $perfil);
        $medicion = $dato['componentes']['COG-MEM'][0]['mediciones'][0];
        self::assertSame('Prueba sintética', $medicion['instrumento']);
        self::assertSame('Profesional de prueba', $medicion['autor']);
        self::assertSame(10, $medicion['valor_bruto']);
        self::assertSame(0, $medicion['valor']);
        self::assertArrayNotHasKey('aplicacion', $medicion);
        self::assertSame('Sin evaluación clínica conservada', $dato['titulo']);
    }
}
