<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\InventarioExperto;
use DomainException;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ContratoEntradasSinResidentesTest extends TestCase
{
    use RefreshDatabase;

    public function test_prepara_el_mapa_con_base_vacia_sin_leer_ni_escribir_filas_clinicas(): void
    {
        self::assertDatabaseCount('residentes', 0);
        $consultas = [];
        $capturando = true;
        DB::listen(static function ($consulta) use (&$consultas, &$capturando): void {
            if ($capturando) {
                $consultas[] = $consulta->sql;
            }
        });
        $r = InventarioExperto::entradas(DB::connection());
        $capturando = false;
        self::assertSame(ContratoFuentesPetersen::mapa(), $r['mapa_fuente_a_criterio']);
        self::assertFalse($r['requiere_residentes']);
        self::assertFalse($r['certifica_preparacion_clinica']);
        self::assertSame(array_keys(ContratoFuentesPetersen::FUENTES), array_keys($r['soportes_admitidos']));
        self::assertCount(8, $r['soportes_admitidos']);
        self::assertCount(6, $r['captura_original']);
        self::assertCount(9, $r['captura_original']['soportes_valoracion_petersen']['relaciones']);
        foreach ($r['soportes_admitidos'] as $clave => $fuente) {
            self::assertSame(ContratoFuentesPetersen::FUENTES[$clave][1], $fuente['permiso_lectura']);
        }
        foreach ([...array_values($r['soportes_admitidos']), ...array_values($r['captura_original'])] as $fuente) {
            self::assertTrue($fuente['estructura_verificada'], $fuente['tabla']);
            self::assertSame([], $fuente['campos_base_faltantes']);
            self::assertContains($fuente['clave_primaria'], $fuente['campos_existentes']);
            foreach ($fuente['relaciones'] as $relacion) {
                self::assertTrue($relacion['verificada']);
            }
        }
        self::assertSame('estado_original', $r['soportes_admitidos']['cod_valoracion_revisada']['campo_estado']);
        self::assertSame('fecha_referencia', $r['soportes_admitidos']['cod_antecedente']['campo_fecha']);
        $tablas = array_column([...array_values($r['soportes_admitidos']), ...array_values($r['captura_original'])], 'tabla');
        $tablas[] = 'evaluaciones_expertas';
        foreach ($consultas as $sql) {
            self::assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|alter|create|drop|truncate)\b/i', $sql);
            self::assertDoesNotMatchRegularExpression('/\b(?:from|join)\s+(?:["`]?\w+["`]?\.)?["`]?(?:'.implode('|', $tablas).')["`]?(?=\s|$)/i', $sql);
        }
        self::assertDatabaseCount('residentes', 0);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_sigue_funcionando_con_conexion_sqlite_de_solo_lectura(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('La protección PRAGMA corresponde a SQLite.');
        }
        DB::statement('PRAGMA query_only = ON');
        try {
            $r = InventarioExperto::entradas(DB::connection());
            self::assertTrue($r['captura_original']['respuestas_instrumento']['estructura_verificada']);
            self::assertSame(1, (int) DB::selectOne('PRAGMA query_only')->query_only);
        } finally {
            DB::statement('PRAGMA query_only = OFF');
        }
    }

    public function test_una_base_sin_esquema_informa_faltantes_y_no_los_crea(): void
    {
        config(['database.connections.entradas_vacia' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
        $db = DB::connection('entradas_vacia');
        try {
            $r = InventarioExperto::entradas($db);
            foreach ([...array_values($r['soportes_admitidos']), ...array_values($r['captura_original'])] as $fuente) {
                self::assertFalse($fuente['tabla_presente']);
                self::assertFalse($fuente['estructura_verificada']);
                self::assertNotEmpty($fuente['campos_base_faltantes']);
                self::assertSame([], $fuente['campos_existentes']);
            }
            self::assertSame([], $db->getSchemaBuilder()->getTableListing());
        } finally {
            DB::purge('entradas_vacia');
        }
    }

    public function test_un_motor_no_verificado_se_rechaza_antes_de_inspeccionar_el_esquema(): void
    {
        $db = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->onlyMethods(['getDriverName', 'getSchemaBuilder'])->getMock();
        $db->method('getDriverName')->willReturn('mysql');
        $db->expects(self::never())->method('getSchemaBuilder');
        $this->expectException(DomainException::class);
        InventarioExperto::entradas($db);
    }
}
