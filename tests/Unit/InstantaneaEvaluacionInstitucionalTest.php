<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ArchivoConocimientoOrion;
use DomainException;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\conocimiento\versiones\PaqueteCognitivoInstitucional;
use MotorExperto\dominio\conocimiento\versiones\PaquetePetersenInstitucional;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;
use MotorExperto\dominio\petersen\FuentePetersenInstitucional as F;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen as V;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;
use MotorExperto\soporte\conservacion\CodecInstantanea as Codec;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional as Compuesta;
use MotorExperto\soporte\conservacion\InstantaneaInstrumental;
use MotorExperto\soporte\conservacion\InstantaneaPetersen;
use MotorExperto\soporte\conservacion\ReleaseMotorInstrumental;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\Support\ConstruyeCasoPetersen;
use ZipArchive;

/** Contrato compuesto sin Laravel ni SQL durante el replay; datos exclusivamente sintéticos. */
final class InstantaneaEvaluacionInstitucionalTest extends TestCase
{
    use ConstruyeCasoPetersen;

    private function crear(array $alterados = ['COG-MEM'], bool $sinC03 = false, bool $sinGobierno = false,
        bool $conPreguntasSinRespuesta = false): Compuesta
    {
        $c = $this->contexto();
        $v = $this->caso($alterados);
        $capturas = [];
        foreach ($v as $i => $valoracion) {
            if (! str_starts_with($valoracion->elemento, 'COG-')) {
                continue;
            }
            $s = $valoracion->soportes[0];
            $a = $s->datos['captura_original'] + ['vinculos_preguntas_actuales' => [], 'preguntas_declarativas_originales' => []];
            if ($conPreguntasSinRespuesta) {
                $codigoPregunta = 'Q-SIN-RESPUESTA-'.$valoracion->elemento;
                $clavePregunta = $valoracion->elemento === 'COG-ATE' ? 7 : 'MAP-'.$valoracion->elemento;
                $pregunta = [
                    'cod_pregunta' => $codigoPregunta, 'cod_instrumento' => $a['cod_instrumento'],
                    'tipo_respuesta' => 'TEXTO',
                ];
                if ($valoracion->elemento === 'COG-MEM') {
                    $pregunta['enunciado'] = null;
                }
                $a['preguntas_declarativas_originales'] = [$clavePregunta => $pregunta];
            }
            foreach ($a['respuestas_originales'] as &$r) {
                $r += ['pregunta_actual' => null, 'opcion_actual' => null];
            }
            unset($r);
            $a['extraccion'] = (new ExtractorCapturaInstrumental)->extraer($a['perfil_captura_original'], $a, $a['respuestas_originales'], $a['edad_en_aplicacion']);
            $capturas[] = $a;
            $v[$i] = new V($valoracion->elemento, $valoracion->estado, $valoracion->justificacion, $valoracion->origen,
                [new F($s->tabla, $s->registro, $s->residente, $s->autor, $s->fecha, $s->episodio, $s->tipo, $s->vigente,
                    ['dominio' => $valoracion->elemento, 'captura_original' => $a])], $valoracion->comprobaciones);
        }
        if ($sinC03) {
            $v = array_values(array_filter($v, fn ($j) => $j->elemento !== 'C03'));
        }
        $fuente = (new ArchivoConocimientoOrion)->leer();
        $p = (new PaqueteCognitivoInstitucional)->construir((new PerfilConceptualCognitivo)->construir($fuente), $c->version, 'AUTOR-TEST', '2026-10-19 10:00:00');
        $i = InstantaneaInstrumental::crear($p, $c, ['disponible' => true, 'fecha_corte' => $c->fechaCorte->format('Y-m-d\TH:i:s.uP'),
            'capturas' => $capturas], [], 'USUARIO-TEST', ['cod_version_modelo' => $c->version], 'INSTRUMENTAL-DECLARATIVO-3', $fuente);
        $g = $sinGobierno ? null : (new PaquetePetersenInstitucional)->construir('GOBIERNO-TEST', 'AUTOR-TEST', '2026-10-19 10:00:00');
        $cabecera = $sinGobierno ? null : ['cod_version_modelo' => $g->version, 'codigo_version' => PaquetePetersenInstitucional::CODIGO,
            'nombre' => 'Gobierno sintético', 'descripcion' => PaquetePetersenInstitucional::FUNDAMENTO, 'estado' => 'ACTIVO',
            'fecha_hora_creacion' => '2026-10-19T10:00:00.000000-04:00', 'fecha_hora_vigencia' => '2026-10-19T10:00:00.000000-04:00',
            'fecha_hora_retiro' => null, 'motivo_cambio' => 'Ensayo de conservación', 'cod_version_anterior' => null,
            'cod_usuario_creacion' => 'AUTOR-TEST', 'observacion' => PaquetePetersenInstitucional::FUNDAMENTO];
        $procedencia = array_map(fn ($j) => ['cod_valoracion_petersen' => $j->origen->registro, 'cod_residente' => $c->residente,
            'cod_personal' => $j->origen->autor, 'cod_usuario_registro' => 'USUARIO-TEST', 'registrado_en' => '2026-10-19T10:00:00.000000-04:00',
            'fecha_hora' => $j->origen->fecha->format('Y-m-d\TH:i:s.uP'), 'elemento' => $j->elemento, 'tipo_registro' => 'REGISTRO',
            'cod_valoracion_anterior' => null, 'cod_documento_clinico' => null, 'localizador_fuente' => null], $v);
        $membresias = [];
        foreach ($v as $indice => $j) {
            $cantidad = count($j->soportes) + count($j->dominiosRevisados);
            $membresias[] = ['valoracion' => $j->origen->registro,
                'soportes' => $cantidad === 0 ? [] : array_map(fn ($n) => 'SV-'.$indice.'-'.$n, range(1, $cantidad))];
        }
        $petersen = InstantaneaPetersen::crear($c, $v, $g, $cabecera, 'USUARIO-TEST', ['valoraciones_al_corte' => count($v),
            'exclusiones' => [], 'fuentes_originales_mutables' => true, 'conservacion_completa' => false], procedencia: $procedencia,
            membresias: $membresias);

        return Compuesta::crear($i, $petersen);
    }

    #[DataProviderExternal(PetersenInstitucionalTest::class, 'patrones')]
    public function test_reproduce_los_cuatro_patrones_desde_un_contrato_compuesto(array $alterados, string $patron): void
    {
        $d = $this->crear($alterados);
        self::assertSame($d->contenido, Compuesta::verificar($d->contenido, $d->huella)->contenido);
        self::assertSame($patron, $d->contenido['petersen']['contenido']['salida']['patron']);
        self::assertFalse($d->contenido['persistido']);
        self::assertFalse($d->contenido['release_retenido']);
        self::assertNotSame($d->contenido['instrumental']['contenido']['contexto']['version'], $d->contenido['petersen']['contenido']['gobierno']['conocimiento']['version']);
    }

    public function test_ausencias_no_se_rellenan_durante_el_replay(): void
    {
        foreach ([$this->crear(sinC03: true), $this->crear(sinGobierno: true)] as $d) {
            $r = Compuesta::verificar($d->contenido, $d->huella);
            self::assertNull($r->contenido['petersen']['contenido']['salida']['patron']);
            self::assertNotEmpty($r->contenido['petersen']['contenido']['salida']['impedimentos']);
        }
    }

    public function test_preguntas_originales_sin_respuesta_se_conservan_en_ambas_ramas(): void
    {
        $dto = $this->crear(conPreguntasSinRespuesta: true);
        $recuperada = Compuesta::verificar($dto->contenido, $dto->huella);
        self::assertSame($dto->contenido, $recuperada->contenido);
        $capturas = $dto->contenido['instrumental']['contenido']['capturas']['capturas'];
        $porAplicacion = array_column($capturas, null, 'cod_aplicacion');
        self::assertCount(5, $porAplicacion);
        foreach ($dto->contenido['petersen']['contenido']['valoraciones'] as $valoracion) {
            foreach ($valoracion['soportes'] as $soporte) {
                $original = $soporte['datos_originales']['captura_original'] ?? null;
                if ($original === null) {
                    continue;
                }
                $captura = $porAplicacion[$original['cod_aplicacion']];
                self::assertCount(1, $captura['preguntas_declarativas_originales']);
                self::assertSame($captura['preguntas_declarativas_originales'], $original['preguntas_declarativas_originales']);
                $pregunta = array_values($original['preguntas_declarativas_originales'])[0];
                if ($valoracion['elemento'] === 'COG-MEM') {
                    self::assertArrayHasKey('enunciado', $pregunta);
                    self::assertNull($pregunta['enunciado']);
                } else {
                    self::assertArrayNotHasKey('enunciado', $pregunta);
                }
                if ($valoracion['elemento'] === 'COG-ATE') {
                    self::assertArrayHasKey(7, $original['preguntas_declarativas_originales']);
                }
                self::assertNotContains($pregunta['cod_pregunta'], array_column($captura['respuestas_originales'], 'cod_pregunta'));
            }
        }
    }

    public function test_rechaza_pregunta_sin_respuesta_discrepante_entre_ramas(): void
    {
        $d = $this->crear(conPreguntasSinRespuesta: true)->contenido;
        $p = &$d['petersen']['contenido'];
        $p['valoraciones'][0]['soportes'][0]['datos_originales']['captura_original']
            ['preguntas_declarativas_originales']['MAP-COG-MEM']['enunciado'] = 'Texto sintético alterado';
        $d['petersen']['huella'] = Codec::huella(C::ordenar($p));
        $this->expectException(DomainException::class);
        Compuesta::verificar($d, Codec::huella(C::ordenar($d)));
    }

    public static function corrupciones(): array
    {
        return array_map(fn ($c) => [$c], ['salida', 'iadl', 'c05', 'gobierno', 'corte', 'residente', 'actor', 'version', 'procedencia', 'campo_ajeno', 'medicion', 'membresias']);
    }

    #[DataProvider('corrupciones')]
    public function test_corrupcion_y_cruces_se_rechazan_aunque_se_recalculen_las_huellas(string $cambio): void
    {
        $d = $this->crear()->contenido;
        $p = &$d['petersen']['contenido'];
        match ($cambio) {
            'salida' => $p['salida']['patron'] = 'PET-04',
            'iadl' => $p['valoraciones'][6]['soportes'][0]['datos_originales']['desempeno'] = 'CON_APOYO',
            'c05' => $p['valoraciones'][8]['dominios_revisados']['COG-MEM'] = 'OTRO:JUICIO',
            'gobierno' => $p['gobierno']['conocimiento']['tablas']['reglas_expertas'][0]['estado'] = 'INACTIVO',
            'corte' => $p['contexto']['fecha_corte']['corte_offset'] = 0,
            'residente' => $p['contexto']['residente'] = 'OTRO-RESIDENTE',
            'actor' => $p['contexto']['usuario_ejecucion'] = 'OTRA-CUENTA',
            'version' => $p['contexto']['version'] = 'OTRA-VERSION',
            'procedencia' => $p['procedencia_valoraciones'][0]['registrado_en'] = '2027-01-01T10:00:00.000000-04:00',
            'membresias' => $p['membresias_soportes'] = [],
            'campo_ajeno' => $p['valoraciones'][0]['password'] = 'prohibido-sintetico',
            'medicion' => $p['valoraciones'][0]['soportes'][0]['datos_originales']['captura_original']['respuestas_originales'][0]['valor_numero'] = 0,
        };
        $d['petersen']['huella'] = Codec::huella(C::ordenar($p));
        $this->expectException(DomainException::class);
        Compuesta::verificar($d, Codec::huella(C::ordenar($d)));
    }

    public function test_ramas_validas_con_medicion_discrepante_no_se_pueden_componer(): void
    {
        $d = $this->crear()->contenido;
        $p = $d['petersen']['contenido'];
        // El puntaje cambia dentro de un intervalo interpretable: Petersen conserva su patrón.
        $p['valoraciones'][0]['soportes'][0]['datos_originales']['captura_original']['respuestas_originales'][0]['valor_numero'] = 5;
        $p['salida']['dominios']['COG-MEM']['valoraciones'][0]['soportes'][0]['datos_originales']['captura_original']['respuestas_originales'][0]['valor_numero'] = 5;
        InstantaneaPetersen::verificar($p, Codec::huella(C::ordenar($p)));
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ramas discrepan');
        Compuesta::verificar(array_replace($d, ['petersen' => ['contenido' => $p, 'huella' => Codec::huella(C::ordenar($p))]]),
            Codec::huella(C::ordenar(array_replace($d, ['petersen' => ['contenido' => $p, 'huella' => Codec::huella(C::ordenar($p))]]))));
    }

    public function test_release_ausente_no_se_sustituye_por_el_ejecutor_actual(): void
    {
        $this->expectException(DomainException::class);
        $this->crear()->vincularRelease(sys_get_temp_dir().'/release-inexistente-'.bin2hex(random_bytes(8)).'.zip', str_repeat('a', 64));
    }

    public function test_manifiesto_v1_no_se_acepta_en_compuesto_aunque_huellas_y_codigo_coincidan(): void
    {
        $d = $this->crear()->contenido;
        $archivo = sys_get_temp_dir().'/orion-compuesto-v1-'.bin2hex(random_bytes(8)).'.zip';
        try {
            $m = (new ReleaseMotorInstrumental)->crear(dirname(__DIR__, 2), $archivo, str_repeat('a', 40));
            $m['contrato'] = 'ARTEFACTO-MOTOR-1';
            unset($m['huella']);
            $m['huella'] = Codec::huella(C::ordenar($m));
            $d['release'] = $m;
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('manifiesto');
            Compuesta::verificar($d, Codec::huella(C::ordenar($d)));
        } finally {
            if (is_file($archivo)) {
                unlink($archivo);
            }
        }
    }

    public function test_release_vinculado_reproduce_ambas_ramas_en_otro_proceso_sin_laravel(): void
    {
        $dto = $this->crear();
        $directorio = sys_get_temp_dir().'/orion-compuesto-replay-'.bin2hex(random_bytes(8));
        self::assertTrue(mkdir($directorio));
        try {
            $archivo = $directorio.'/motor.zip';
            $m = (new ReleaseMotorInstrumental)->crear(dirname(__DIR__, 2), $archivo, str_repeat('a', 40));
            $dto = $dto->vincularRelease($archivo, $m['huella']);
            self::assertFalse($dto->contenido['release_retenido']);
            self::assertSame(C::ordenar($m), $dto->contenido['release']);
            $zip = new ZipArchive;
            self::assertTrue($zip->open($archivo, ZipArchive::RDONLY));
            self::assertTrue($zip->extractTo($directorio.'/release', array_keys($m['archivos'])));
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
                $dto = MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional::verificar($entrada['contenido'], $entrada['huella']);
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

            // Un ZIP internamente válido con otro conjunto src sirve para verificar su historia,
            // pero no se acepta como el ejecutor de esta preparación.
            self::assertTrue($zip->open($archivo));
            $nombre = 'motor-experto/src/motor_experto/soporte/conservacion/InstantaneaPetersen.php';
            $bytes = $zip->getFromName($nombre)."\n/* Variante sintética de release */\n";
            self::assertTrue($zip->addFromString($nombre, $bytes));
            $m['archivos'][$nombre] = hash('sha256', $bytes);
            unset($m['huella']);
            $m['huella'] = Codec::huella(C::ordenar($m));
            self::assertTrue($zip->addFromString('MANIFIESTO-MOTOR.json', json_encode($m, JSON_THROW_ON_ERROR)));
            self::assertTrue($zip->close());
            self::assertSame($m, (new ReleaseMotorInstrumental)->verificar($archivo, $m['huella']));
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('mismo ejecutor');
            $dto->vincularRelease($archivo, $m['huella']);
        } finally {
            $raiz = realpath($directorio);
            if ($raiz !== false && str_starts_with(basename($raiz), 'orion-compuesto-replay-')
                && realpath(dirname($raiz)) === realpath(sys_get_temp_dir())) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($raiz, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $f) {
                    $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
                }
                rmdir($raiz);
            }
        }
    }
}
