<?php

declare(strict_types=1);

namespace Tests\Unit;

use DomainException;
use OrionNominal\ContratoNominal;
use OrionNominal\EsquemaNominal;
use OrionNominal\ProyeccionNominal;
use PDO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** Representación física; estos valores no son medidas clínicas ni casos diagnósticos. */
final class ContratoNominalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['ContratoNominal', 'EsquemaNominal', 'ProyeccionNominal'] as $clase) {
            require_once dirname(__DIR__, 2).'/prototipos/orion-conservacion-nominal/'.$clase.'.php';
        }
    }

    public function test_el_ddl_y_la_cobertura_de_783_rutas_coinciden_con_el_codigo(): void
    {
        $raiz = dirname(__DIR__, 2);
        $proceso = new Process([PHP_BINARY, $raiz.'/prototipos/orion-conservacion-nominal/generar-diccionario.php', '--check'], $raiz, timeout: 20);
        $proceso->mustRun();
        $resumen = json_decode($proceso->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        self::assertTrue($resumen['check']);
        self::assertSame(34, $resumen['tablas']);
        self::assertSame(138, $resumen['total']);
        $auditoria = json_decode(file_get_contents($raiz.'/prototipos/orion-conservacion-nominal/resumen.json'), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(783, $auditoria['rutas_originales_mapeadas']);
        self::assertFalse($auditoria['instalable']);
    }

    public function test_sqlite_distingue_texto_precision_tipo_cero_negativo_null_y_ausencia(): void
    {
        $mapa = ['raiz' => 'm001', 'modelos' => ['m001' => ['tabla' => 'entradas_institucionales', 'padre' => null,
            'fk_propietario' => 'cod_propietario', 'estructura' => ['clase' => 'objeto', 'campos' => array_fill_keys(
                ['valor_numero', 'puntaje', 'forma', 'valor_logico', 'edad_en_aplicacion'], ['clase' => 'escalar'])]]]];
        $db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $db->exec('PRAGMA foreign_keys=ON');
        $db->exec("CREATE TABLE residentes(cod_residente VARCHAR(20) PRIMARY KEY); CREATE TABLE usuarios(cod_usuario VARCHAR(20) PRIMARY KEY);
            INSERT INTO residentes VALUES('RES-TEST'); INSERT INTO usuarios VALUES('USUARIO-TEST')");
        $db->exec(EsquemaNominal::ddl($mapa, 'sqlite'));
        $db->prepare('INSERT INTO releases_nominales VALUES(?,?,?,?,?,?,?,?,?,?)')->execute([
            str_repeat('a', 64), 'RELEASE-TEST', str_repeat('b', 64), str_repeat('c', 64), str_repeat('d', 40),
            '8.3', 1, str_repeat('e', 64), str_repeat('f', 64), str_repeat('0', 64)]);
        $casos = [
            ['valor_numero' => PHP_INT_MAX, 'puntaje' => '1.00', 'forma' => 1, 'valor_logico' => false, 'edad_en_aplicacion' => null],
            ['valor_numero' => -0.0, 'puntaje' => 0.0, 'forma' => '01', 'valor_logico' => 0],
            ['valor_numero' => PHP_FLOAT_MAX, 'puntaje' => '000', 'forma' => '', 'valor_logico' => true],
            ['puntaje' => null], [],
        ];
        $proyeccion = new ProyeccionNominal($db, $mapa);
        foreach ($casos as $i => $entrada) {
            $id = 'VALORES-'.$i;
            $db->prepare("INSERT INTO evaluaciones_nominales VALUES(?,?,?,?,?,?,?,?,'ABIERTA')")
                ->execute([$id, 'SOLICITUD-'.$i, 'RES-TEST', 'USUARIO-TEST', str_repeat('a', 64), str_repeat('b', 64), str_repeat('a', 64), ContratoNominal::VERSION]);
            $proyeccion->insertar($proyeccion->proyectar($entrada, $id));
            $recuperada = $proyeccion->recuperar($id);
            self::assertSame($entrada, $recuperada);
            if ($i === 1) {
                self::assertSame(pack('E', -0.0), pack('E', $recuperada['valor_numero']));
            }
        }
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Tipo fuera del contrato nominal');
        $proyeccion->proyectar(['edad_en_aplicacion' => '80'], 'NO-ACEPTAR');
    }
}
