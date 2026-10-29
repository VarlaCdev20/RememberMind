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

    public function test_comando_integra_preparacion_del_circuito_en_base_sqlite_vacia_y_solo_lectura(): void
    {
        $proceso = new \Symfony\Component\Process\Process([PHP_BINARY, base_path('scripts/inventariar-experto.php')], base_path(), [
            'APP_ENV' => 'testing', 'APP_DEBUG' => 'false',
            'APP_CONFIG_CACHE' => sys_get_temp_dir().'/remembermind-config-no-existente-'.bin2hex(random_bytes(8)).'.php',
            'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DB_URL' => '', 'DATABASE_URL' => '',
            'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array',
        ]);
        $proceso->setTimeout(45);
        self::assertSame(0, $proceso->run(), $proceso->getErrorOutput());
        $r = json_decode($proceso->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        self::assertTrue($r['transaccion_solo_lectura']);
        self::assertSame([], $r['instrumentos']);
        self::assertFalse($r['contrato_entradas']['requiere_residentes']);
        self::assertFalse($r['preparacion_circuito_inicial']['habilita_perfiles']);
        self::assertFalse($r['preparacion_circuito_inicial']['consulta_datos_clinicos']);
        self::assertCount(3, $r['preparacion_circuito_inicial']['familias']);
        self::assertSame(0, $r['preparacion_circuito_inicial']['observacion_tecnica']['perfiles_configurados_total']);
    }

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
        array_push($tablas, 'atenciones', 'residentes_contactos', 'contactos', 'notas_clinicas');
        foreach ($consultas as $sql) {
            self::assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|alter|create|drop|truncate)\b/i', $sql);
            self::assertDoesNotMatchRegularExpression('/\b(?:from|join)\s+(?:["`]?\w+["`]?\.)?["`]?(?:'.implode('|', $tablas).')["`]?(?=\s|$)/i', $sql);
        }
        self::assertDatabaseCount('residentes', 0);
        self::assertDatabaseCount('aplicaciones_instrumento', 0);
        self::assertDatabaseCount('respuestas_instrumento', 0);
        self::assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_contexto_distingue_escolaridad_estructural_de_idioma_e_informantes_del_caso(): void
    {
        $contexto = InventarioExperto::entradas(DB::connection())['contexto_evaluacion'];
        self::assertFalse($contexto['datos_clinicos_consultados']);
        self::assertFalse($contexto['certifica_4fn_global']);
        self::assertTrue($contexto['escolaridad']['estructura_de_referencia_verificada']);
        self::assertSame('atenciones', $contexto['escolaridad']['fuente_por_episodio']['tabla']);
        foreach (['escolaridad_anios', 'puede_leer', 'puede_escribir'] as $campo) {
            self::assertContains($campo, $contexto['escolaridad']['fuente_por_episodio']['campos_existentes']);
        }
        self::assertTrue($contexto['escolaridad']['vinculo_con_aplicacion']['estructura_verificada']);
        self::assertFalse($contexto['escolaridad']['se_copia_a_respuestas']);
        self::assertFalse($contexto['escolaridad']['acredita_aplicabilidad_instrumental']);
        self::assertTrue($contexto['idioma']['contrato_de_captura_episodica_definido']);
        self::assertTrue($contexto['idioma']['fuente_por_episodio']['estructura_verificada']);
        self::assertTrue($contexto['informantes']['fuente_por_episodio']['estructura_verificada']);
        self::assertFalse($contexto['idioma']['idioma_del_material_acredita_idioma_del_caso']);
        self::assertTrue($contexto['informantes']['vinculo_residente_contacto']['estructura_verificada']);
        self::assertFalse($contexto['informantes']['vinculo_maestro_acredita_participacion']);
        self::assertFalse($contexto['informantes']['vinculo_maestro_acredita_conocimiento_previo']);
        self::assertFalse($contexto['informantes']['responsable_principal_se_elige_por_defecto']);
        self::assertDatabaseCount('residentes', 0);
        self::assertDatabaseCount('atenciones', 0);
        self::assertDatabaseCount('residentes_contactos', 0);
    }

    public function test_una_atencion_sin_la_extension_no_se_presenta_como_contexto_disponible(): void
    {
        $migracion = require base_path('database/migrations/2026_10_28_010000_add_contexto_escolaridad_atenciones.php');
        $migracion->down();
        try {
            $contexto = InventarioExperto::entradas(DB::connection())['contexto_evaluacion'];
            self::assertTrue($contexto['escolaridad']['fuente_por_episodio']['tabla_presente']);
            self::assertFalse($contexto['escolaridad']['estructura_de_referencia_verificada']);
            self::assertSame(['escolaridad_anios', 'puede_leer', 'puede_escribir'],
                $contexto['escolaridad']['fuente_por_episodio']['campos_base_faltantes']);
            self::assertTrue($contexto['escolaridad']['vinculo_con_aplicacion']['estructura_verificada']);
            self::assertFalse(DB::getSchemaBuilder()->hasColumn('atenciones', 'escolaridad_anios'));
            self::assertFalse($contexto['informantes']['vinculo_maestro_acredita_participacion']);
        } finally {
            $migracion->up();
        }
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
            $contexto = $r['contexto_evaluacion'];
            self::assertFalse($contexto['escolaridad']['estructura_de_referencia_verificada']);
            self::assertFalse($contexto['informantes']['vinculo_residente_contacto']['estructura_verificada']);
            self::assertTrue($contexto['idioma']['contrato_de_captura_episodica_definido']);
            self::assertFalse($contexto['idioma']['fuente_por_episodio']['estructura_verificada']);
            self::assertFalse($contexto['informantes']['vinculo_maestro_acredita_participacion']);
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
