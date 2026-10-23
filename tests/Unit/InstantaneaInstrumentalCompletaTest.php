<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ArchivoConocimientoOrion;
use DateTimeImmutable;
use DomainException;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\instrumentos\NormalizadorHvltBolivia2015 as Hvlt;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use MotorExperto\soporte\conservacion\InstantaneaInstrumental;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use PHPUnit\Framework\TestCase;
use ZipArchive;

/** Fixtures sintéticas originales: el replay no recibe un z ni una clasificación esperados como entrada. */
final class InstantaneaInstrumentalCompletaTest extends TestCase
{
    private function crear(?array $cambiar = null): InstantaneaInstrumental
    {
        $d = ReproduccionFuenteNominalTest::ausenciaHistorica()->contenido;
        $k = $d['conocimiento'];
        $p = new PaqueteConocimiento($k['version'], $k['tablas'], $k['contratos_relaciones'], false, $k['tipo_regla_resultado'], $k['contratos_extraccion']);
        $c = new ContextoEjecucion($d['contexto']['evaluacion'], $d['contexto']['residente'], $d['contexto']['version'],
            new DateTimeImmutable('2026-10-19T12:00:00.123456-04:00'));
        $a = ['cod_aplicacion' => 'APP-NUMERICA', 'cod_residente' => $c->residente, 'cod_instrumento' => 'INSTR-NUMERICO',
            'codigo_instrumento' => 'MEDIDAS-TEST', 'nombre_instrumento' => 'Protocolo sintético', 'version_instrumento' => 'TEST-1',
            'cod_personal' => 'AUTOR-TEST', 'autor' => 'Autor sintético', 'fecha_hora' => '2026-10-18T10:00:00.000000-04:00',
            'estado_original' => 'COMPLETA', 'edad_en_aplicacion' => 70, 'fuente_edad' => [], 'procedencia' => [], 'motivos' => [],
            'extraccion' => null, 'aplicacion_actual' => [], 'instrumento_actual' => ['cod_instrumento' => 'INSTR-NUMERICO', 'tipo' => 'COGNITIVO', 'version' => 'TEST-1'],
            'respuestas_originales' => [], 'vinculos_preguntas_actuales' => [], 'preguntas_declarativas_originales' => []];
        $selectores = [];
        foreach (Hvlt::ENTRADAS as $entrada => $tipo) {
            $campo = $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero';
            $id = 'P-'.count($selectores);
            $valor = match ($entrada) {
                'ensayo_1' => 6, 'ensayo_2' => 7, 'ensayo_3' => 8, 'recuerdo_diferido' => 4,
                'demora_minutos' => 22, 'escolaridad_anios' => 4, default => true,
            };
            $selectores[$entrada] = ['cod_pregunta' => $id, 'campo' => $campo];
            $a['respuestas_originales'][] = ['cod_respuesta' => 'R-'.$id, 'cod_aplicacion' => $a['cod_aplicacion'], 'cod_pregunta' => $id,
                'cod_opcion' => null, 'valor_numero' => null, 'valor_logico' => null, 'valor_texto' => null,
                'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO', 'coherente' => true,
                'pregunta_actual' => null, 'opcion_actual' => null];
            $a['respuestas_originales'][array_key_last($a['respuestas_originales'])][$campo] = $valor;
        }
        $a['perfil_captura_original'] = ['id' => 'PERFIL-TEST', 'cod_instrumento' => $a['cod_instrumento'],
            'codigo_instrumento' => $a['codigo_instrumento'], 'version_instrumento' => $a['version_instrumento'],
            'metodo' => Hvlt::METODO, 'identidad_instrumento' => ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es'], 'selectores' => $selectores];
        $d['capturas']['capturas'][] = $cambiar === null ? $a : array_replace($a, $cambiar);

        return InstantaneaInstrumental::crear($p, $c, $d['capturas'], $d['vinculos'], 'USUARIO-TEST', $d['cabecera_conocimiento'],
            'INSTRUMENTAL-DECLARATIVO-3', (new ArchivoConocimientoOrion)->leer());
    }

    public function test_recalcula_brutos_normalizacion_red_y_cinco_dominios_desde_un_solo_dto(): void
    {
        $dto = $this->crear();
        $r = InstantaneaInstrumental::verificar($dto->contenido, $dto->huella);
        self::assertSame($dto->contenido, $r->contenido);
        $perfil = $r->contenido['salida']['perfil_instrumental'];
        self::assertCount(5, $perfil['dominios']);
        self::assertCount(19, $perfil['red']['relaciones']);
        self::assertSame(2, $perfil['metricas_60_o_mas']['componentes_con_comparacion']);
        $m = $perfil['dominios']['COG-MEM']['componentes']['MEM-S01']['mediciones'][0]['resultado'];
        self::assertSame(21, $m['puntuacion_bruta']);
        self::assertEqualsWithDelta(16.713, $m['esperado'], 0.0000001);
        self::assertEqualsWithDelta(1.01756468075, $m['z'], 0.0000001);
        self::assertNull($perfil['diagnostico']);
        self::assertFalse($perfil['persistido']);
    }

    public function test_rechaza_normalizacion_alterada_aunque_se_recalcule_la_huella_del_contenedor(): void
    {
        $d = $this->crear()->contenido;
        $d['capturas']['capturas'][1]['extraccion']['normalizacion']['componentes']['MEM-S01']['z'] = 100.0;
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('normalización');
        InstantaneaInstrumental::verificar($d, CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($d)));
    }

    public function test_documento_posterior_no_puede_respaldar_una_normalizacion_en_replay(): void
    {
        $d = $this->crear()->contenido;
        $d['capturas']['capturas'][1]['procedencia']['cod_documento_clinico'] = 'DOC-TEST';
        $d['capturas']['capturas'][1]['procedencia']['estado_documento'] = 'VERIFICADO';
        $d['capturas']['capturas'][1]['documento_original'] = ['fecha_hora' => '2026-10-20T12:00:00.000000-04:00'];
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('normalización');
        InstantaneaInstrumental::verificar($d, CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($d)));
    }

    public function test_rechaza_salida_de_red_alterada_aunque_se_recalcule_su_huella(): void
    {
        $d = $this->crear()->contenido;
        $d['salida']['perfil_instrumental']['red']['dominios']['COG-MEM']['nombre'] = 'Resultado alterado';
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('no se reproduce');
        InstantaneaInstrumental::verificar($d, CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($d)));
    }

    public function test_no_reconstruye_un_perfil_ausente_desde_la_configuracion_actual(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('perfil de captura utilizado');
        $this->crear(['perfil_captura_original' => null]);
    }

    public function test_metadatos_locales_ajenos_no_entran_en_el_contrato_conservado(): void
    {
        $this->expectException(DomainException::class);
        $this->crear(['perfil_captura_original' => ['id' => 'TEST', 'password' => 'dato-sintetico-prohibido']]);
    }

    public function test_contrato_v2_sigue_reproduciendo_episodios_sin_exigir_la_fuente_v3(): void
    {
        $d = ReproduccionFuenteNominalTest::ausenciaHistorica()->contenido;
        $k = $d['conocimiento'];
        $p = new PaqueteConocimiento($k['version'], $k['tablas'], $k['contratos_relaciones'], false, $k['tipo_regla_resultado'], $k['contratos_extraccion']);
        $c = new ContextoEjecucion('EVAL-HISTORICA', 'RES-SINTETICO', $p->version, new DateTimeImmutable('2026-10-19T12:00:00.123456-04:00'));
        $dto = InstantaneaInstrumental::crear($p, $c, $d['capturas'], $d['vinculos'], 'USUARIO-SINTETICO', $d['cabecera_conocimiento'], 'INSTRUMENTAL-DECLARATIVO-2');
        self::assertArrayNotHasKey('fuente_conceptual', $dto->contenido);
        self::assertArrayNotHasKey('perfil_instrumental', $dto->contenido['salida']);
        self::assertSame($dto->contenido, InstantaneaInstrumental::verificar($dto->contenido, $dto->huella)->contenido);
    }

    public function test_replay_completo_se_ejecuta_en_otro_proceso_usando_solo_el_codigo_del_zip(): void
    {
        $dto = $this->crear();
        $directorio = sys_get_temp_dir().'/orion-release-replay-'.bin2hex(random_bytes(8));
        self::assertTrue(mkdir($directorio));
        try {
            $archivo = $directorio.'/motor.zip';
            $manifiesto = (new ReleaseMotorInstrumental)->crear(dirname(__DIR__, 2), $archivo, str_repeat('a', 40));
            $zip = new ZipArchive;
            self::assertTrue($zip->open($archivo, ZipArchive::RDONLY));
            self::assertTrue($zip->extractTo($directorio.'/release', array_keys($manifiesto['archivos'])));
            self::assertTrue($zip->close());
            $programa = <<<'PHP'
                <?php
                declare(strict_types=1);
                $raiz = __DIR__.'/release';
                spl_autoload_register(static function (string $clase) use ($raiz): void {
                    $prefijo = 'MotorExperto\\';
                    if (str_starts_with($clase, $prefijo)) {
                        require $raiz.'/motor-experto/src/motor_experto/'.str_replace('\\', '/', substr($clase, strlen($prefijo))).'.php';
                    }
                });
                $entrada = unserialize(stream_get_contents(STDIN), ['allowed_classes' => false]);
                $dto = MotorExperto\soporte\conservacion\InstantaneaInstrumental::verificar($entrada['contenido'], $entrada['huella']);
                foreach (get_included_files() as $incluido) {
                    if ($incluido !== __FILE__ && ! str_starts_with(str_replace('\\', '/', $incluido), str_replace('\\', '/', $raiz).'/')) {
                        throw new RuntimeException('El replay cargó código fuera del release.');
                    }
                }
                echo $dto->huella;
                PHP;
            self::assertNotFalse(file_put_contents($directorio.'/reproducir.php', $programa));
            $proceso = proc_open([PHP_BINARY, $directorio.'/reproducir.php'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $tuberias, $directorio);
            self::assertIsResource($proceso);
            fwrite($tuberias[0], serialize(['contenido' => $dto->contenido, 'huella' => $dto->huella]));
            fclose($tuberias[0]);
            $salida = stream_get_contents($tuberias[1]);
            $error = stream_get_contents($tuberias[2]);
            fclose($tuberias[1]);
            fclose($tuberias[2]);
            self::assertSame(0, proc_close($proceso), $error);
            self::assertSame($dto->huella, $salida);
        } finally {
            // Solo el directorio temporal creado en esta prueba, comprobado antes de recorrerlo.
            $raiz = realpath($directorio);
            if ($raiz !== false && str_starts_with(basename($raiz), 'orion-release-replay-')
                && realpath(dirname($raiz)) === realpath(sys_get_temp_dir())) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($raiz, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $f) {
                    $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
                }
                rmdir($raiz);
            }
        }
    }
}
