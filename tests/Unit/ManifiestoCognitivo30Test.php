<?php

declare(strict_types=1);

namespace Tests\Unit;

use Database\Seeders\CohorteCognitivaInvestigacionSeeder;
use Database\Seeders\Datos\ManifiestoCognitivo30;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;
use PHPUnit\Framework\TestCase;

final class ManifiestoCognitivo30Test extends TestCase
{
    public function test_cobertura_versionada_y_metodos_existentes_sin_afirmar_datos_individuales_importados(): void
    {
        $m = CohorteCognitivaInvestigacionSeeder::manifiesto();
        self::assertCount(30, ManifiestoCognitivo30::NOMBRES);
        self::assertCount(30, array_unique(array_map(fn ($n) => implode(' ', $n), ManifiestoCognitivo30::NOMBRES)));
        self::assertCount(30, $m['aceptacion']);
        self::assertCount(7, $m['referencias']);
        self::assertFalse($m['datos_individuales_importados']);
        self::assertFalse($m['aprobacion_clinica']);
        self::assertSame(ManifiestoCognitivo30::VERSION, $m['version']);
        self::assertSame(['PET-01' => 3, 'PET-02' => 3, 'PET-03' => 3, 'PET-04' => 3],
            array_count_values(array_filter(array_column($m['aceptacion'], 'patron_esperado'))));
        $registro = (new RegistroMetodosInstrumentales)->metodos();
        foreach ($m['metodos'] as $metodo) {
            self::assertArrayHasKey($metodo['metodo'], $registro);
            self::assertArrayHasKey($metodo['referencia'], $m['referencias']);
            self::assertFalse($metodo['reactivos_reproducidos']);
            self::assertSame('PENDIENTE', $registro[$metodo['metodo']]['revision_profesional']);
        }
        foreach (CohorteCognitivaInvestigacionSeeder::casos() as $caso) {
            self::assertTrue($caso['sintetico']);
            self::assertGreaterThan(60, $caso['edad']);
            self::assertLessThanOrEqual(95, $caso['edad']);
            self::assertArrayNotHasKey('patron_esperado', $caso);
        }
    }

    public function test_retroalimentacion_detecta_discrepancia_dominio_salida_omitida_duplicacion_y_caso_no_ejecutado(): void
    {
        $m = CohorteCognitivaInvestigacionSeeder::manifiesto();
        // Informe externo, sin motor ni BDD; las salidas reales se prueban en la aceptación Feature.
        $resultados = [];
        foreach ($m['aceptacion'] as $id => $e) {
            $resultados[] = ['caso' => $id, 'patron_calculado' => $e['patron_esperado'], 'estado' => $e['estado_esperado'],
                'requisitos' => ['C02' => $e['c02_esperado']], 'dominios' => $e['dominios_esperados']];
        }
        $base = ManifiestoCognitivo30::comparar($m, $resultados);
        self::assertTrue($base['satisfactorio']);
        self::assertFalse($base['validez_clinica_demostrada']);
        self::assertFalse($base['reglas_modificadas']);

        $incorrectos = $resultados;
        $incorrectos[0]['dominios']['COG-MEM'] = 'P'; // Mismo patrón declarado: debe detectar la discrepancia interna.
        unset($incorrectos[12]['patron_calculado']); // NULL esperado no debe ocultar una clave ausente.
        array_pop($incorrectos);
        $incorrectos[] = $resultados[0];
        $r = ManifiestoCognitivo30::comparar($m, $incorrectos);
        self::assertFalse($r['satisfactorio']);
        self::assertContains('dominios.COG-MEM', array_column($r['discrepancias'], 'campo'));
        self::assertContains('SALIDA_AUSENTE', array_column($r['discrepancias'], 'motivo'));
        self::assertContains('CASO_DESCONOCIDO_O_REPETIDO', array_column($r['discrepancias'], 'motivo'));
        self::assertContains('CASO_NO_EJECUTADO', array_column($r['discrepancias'], 'motivo'));
        self::assertSame('A', $m['aceptacion']['S01']['dominios_esperados']['COG-MEM']);
        self::assertSame('A', $resultados[0]['dominios']['COG-MEM']);
    }
}
