<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Tests\Support\EntornoPruebas;

/** Ensayo relacional B02 en un PostgreSQL desechable; no crea tablas institucionales. */
final class ConservacionB02PostgresTest extends TestCase
{
    public function test_preguntas_sin_respuesta_compartidas_conservan_identidad_y_rechazan_escrituras_invalidas(): void
    {
        if (getenv('RM_TEST_PROFILE') !== 'postgres') {
            self::markTestSkipped('La prueba de FK y triggers requiere PostgreSQL desechable.');
        }
        $database = (string) getenv('DB_DATABASE');
        $username = (string) getenv('DB_USERNAME');
        $host = (string) getenv('DB_HOST');
        self::assertTrue(EntornoPruebas::postgresDesechable($database, $username, $host));
        $pdo = new PDO('pgsql:host='.$host.';port='.getenv('DB_PORT').';dbname='.$database,
            $username, (string) getenv('DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $identidad = $pdo->query('SELECT current_database() AS base, current_user AS usuario,
            pg_get_userbyid(datdba) AS propietario FROM pg_database WHERE datname=current_database()')
            ->fetch(PDO::FETCH_ASSOC);
        self::assertSame($database, $identidad['base']);
        self::assertSame($username, $identidad['usuario']);
        self::assertSame($username, $identidad['propietario']);

        require_once __DIR__.'/InstantaneaEvaluacionInstitucionalTest.php';
        $fixture = new InstantaneaEvaluacionInstitucionalTest('test_preguntas_originales_sin_respuesta_se_conservan_en_ambas_ramas');
        $dto = (new \ReflectionMethod($fixture, 'crear'))->invoke($fixture, ['COG-MEM'], false, false, true);
        $instrumental = $dto->contenido['instrumental']['contenido'];
        $petersen = $dto->contenido['petersen']['contenido'];
        $aplicaciones = array_column($instrumental['capturas']['capturas'], null, 'cod_aplicacion');
        $referencias = [];
        foreach ($petersen['valoraciones'] as $valoracion) {
            foreach ($valoracion['soportes'] as $soporte) {
                $original = $soporte['datos_originales']['captura_original'] ?? null;
                if ($original === null) {
                    continue;
                }
                self::assertSame($aplicaciones[$original['cod_aplicacion']]['preguntas_declarativas_originales'],
                    $original['preguntas_declarativas_originales']);
                $referencias[] = [$valoracion['elemento'], $original['cod_aplicacion']];
            }
        }
        self::assertCount(5, $aplicaciones);
        self::assertCount(5, $referencias);

        $pdo->beginTransaction();
        try {
            $pdo->exec(<<<'SQL'
                CREATE TEMP TABLE orion_b02_evaluaciones (
                  codigo text PRIMARY KEY, residente text NOT NULL, cerrada boolean NOT NULL DEFAULT false,
                  UNIQUE(codigo,residente)
                ) ON COMMIT DROP;
                CREATE TEMP TABLE orion_b02_aplicaciones (
                  evaluacion text NOT NULL REFERENCES orion_b02_evaluaciones(codigo),
                  residente text NOT NULL, aplicacion text NOT NULL, instrumento text NOT NULL,
                  PRIMARY KEY(evaluacion,aplicacion), UNIQUE(evaluacion,residente,aplicacion),
                  UNIQUE(evaluacion,aplicacion,instrumento),
                  FOREIGN KEY(evaluacion,residente)
                    REFERENCES orion_b02_evaluaciones(codigo,residente)
                ) ON COMMIT DROP;
                CREATE TEMP TABLE orion_b02_referencias (
                  evaluacion text NOT NULL, residente text NOT NULL,
                  rama text NOT NULL CHECK(rama IN ('INSTRUMENTAL','PETERSEN')),
                  juicio text NOT NULL, aplicacion text NOT NULL,
                  PRIMARY KEY(evaluacion,rama,juicio,aplicacion),
                  FOREIGN KEY(evaluacion,residente,aplicacion)
                    REFERENCES orion_b02_aplicaciones(evaluacion,residente,aplicacion)
                ) ON COMMIT DROP;
                CREATE TEMP TABLE orion_b02_preguntas (
                  pregunta text NOT NULL, instrumento text NOT NULL,
                  PRIMARY KEY(pregunta,instrumento)
                ) ON COMMIT DROP;
                CREATE TEMP TABLE orion_b02_entradas (
                  evaluacion text NOT NULL, aplicacion text NOT NULL, instrumento text NOT NULL,
                  orden integer NOT NULL CHECK(orden>=0),
                  clave_tipo text NOT NULL CHECK(clave_tipo IN ('TEXTO','ENTERO')),
                  clave_mapa text NOT NULL, pregunta text NOT NULL,
                  tipo_respuesta text NOT NULL,
                  enunciado_presente boolean NOT NULL, enunciado text,
                  PRIMARY KEY(evaluacion,aplicacion,clave_tipo,clave_mapa),
                  UNIQUE(evaluacion,aplicacion,orden),
                  FOREIGN KEY(evaluacion,aplicacion,instrumento)
                    REFERENCES orion_b02_aplicaciones(evaluacion,aplicacion,instrumento),
                  FOREIGN KEY(pregunta,instrumento) REFERENCES orion_b02_preguntas(pregunta,instrumento),
                  CHECK(enunciado_presente OR enunciado IS NULL)
                ) ON COMMIT DROP;
                CREATE FUNCTION pg_temp.orion_b02_cierre() RETURNS trigger LANGUAGE plpgsql AS $$
                DECLARE estado boolean;
                BEGIN
                  IF TG_OP <> 'INSERT' THEN RAISE EXCEPTION 'historia inmutable'; END IF;
                  SELECT cerrada INTO estado FROM orion_b02_evaluaciones
                    WHERE codigo=NEW.evaluacion FOR UPDATE;
                  IF estado THEN
                    RAISE EXCEPTION 'evaluacion cerrada';
                  END IF;
                  RETURN NEW;
                END $$;
                CREATE TRIGGER orion_b02_guardar_referencia
                  BEFORE INSERT OR UPDATE OR DELETE ON orion_b02_referencias
                  FOR EACH ROW EXECUTE FUNCTION pg_temp.orion_b02_cierre();
                CREATE TRIGGER orion_b02_guardar_entrada
                  BEFORE INSERT OR UPDATE OR DELETE ON orion_b02_entradas
                  FOR EACH ROW EXECUTE FUNCTION pg_temp.orion_b02_cierre();
                SQL);
            $evaluacion = 'E-B02-PG';
            $residente = $instrumental['contexto']['residente'];
            $pdo->prepare('INSERT INTO orion_b02_evaluaciones(codigo,residente) VALUES (?,?)')
                ->execute([$evaluacion,$residente]);
            $guardarAplicacion = $pdo->prepare('INSERT INTO orion_b02_aplicaciones VALUES (?,?,?,?)');
            $guardarReferencia = $pdo->prepare('INSERT INTO orion_b02_referencias VALUES (?,?,?,?,?)');
            $guardarPregunta = $pdo->prepare('INSERT INTO orion_b02_preguntas VALUES (?,?)');
            $guardarEntrada = $pdo->prepare('INSERT INTO orion_b02_entradas VALUES (?,?,?,?,?,?,?,?,?,?)');
            foreach ($aplicaciones as $aplicacion => $captura) {
                $instrumento = $captura['cod_instrumento'];
                $guardarAplicacion->execute([$evaluacion,$residente,$aplicacion,$instrumento]);
                $guardarReferencia->execute([$evaluacion,$residente,'INSTRUMENTAL','CAPTURA-'.$aplicacion,$aplicacion]);
                $orden = 0;
                foreach ($captura['preguntas_declarativas_originales'] as $clave => $pregunta) {
                    $guardarPregunta->execute([$pregunta['cod_pregunta'],$pregunta['cod_instrumento']]);
                    $guardarEntrada->execute([$evaluacion,$aplicacion,$instrumento,$orden++,
                        is_int($clave) ? 'ENTERO' : 'TEXTO',(string) $clave,$pregunta['cod_pregunta'],
                        $pregunta['tipo_respuesta'],array_key_exists('enunciado',$pregunta) ? 1 : 0,
                        $pregunta['enunciado'] ?? null]);
                }
            }
            foreach ($referencias as [$juicio,$aplicacion]) {
                $guardarReferencia->execute([$evaluacion,$residente,'PETERSEN',$juicio,$aplicacion]);
            }

            $consulta = $pdo->prepare('SELECT e.* FROM orion_b02_entradas e
                JOIN orion_b02_referencias r ON r.evaluacion=e.evaluacion AND r.aplicacion=e.aplicacion
                WHERE r.evaluacion=? AND r.rama=? AND r.aplicacion=? ORDER BY e.orden');
            foreach ($aplicaciones as $aplicacion => $captura) {
                foreach (['INSTRUMENTAL','PETERSEN'] as $rama) {
                    $consulta->execute([$evaluacion,$rama,$aplicacion]);
                    $recuperadas = [];
                    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                        $clave = $fila['clave_tipo'] === 'ENTERO' ? (int) $fila['clave_mapa'] : $fila['clave_mapa'];
                        $pregunta = ['cod_pregunta'=>$fila['pregunta'], 'cod_instrumento'=>$fila['instrumento'],
                            'tipo_respuesta'=>$fila['tipo_respuesta']];
                        if (in_array($fila['enunciado_presente'], [true, 't', '1', 1], true)) {
                            $pregunta['enunciado'] = $fila['enunciado'];
                        }
                        $recuperadas[$clave] = $pregunta;
                    }
                    self::assertSame(CapturaInstrumentalCanonica::ordenar($captura['preguntas_declarativas_originales']),
                        CapturaInstrumentalCanonica::ordenar($recuperadas));
                }
            }
            self::assertSame(5, (int) $pdo->query('SELECT count(*) FROM orion_b02_entradas')->fetchColumn());
            $primera = array_key_first($aplicaciones);
            $pregunta = array_values($aplicaciones[$primera]['preguntas_declarativas_originales'])[0]['cod_pregunta'];
            $segunda = array_keys($aplicaciones)[1];
            $preguntaAjena = array_values($aplicaciones[$segunda]['preguntas_declarativas_originales'])[0]['cod_pregunta'];
            $this->rechaza($pdo, "INSERT INTO orion_b02_aplicaciones VALUES ('$evaluacion','OTRO','AP-AJENA','INST-AJENO')", '23503');
            $this->rechaza($pdo, "INSERT INTO orion_b02_referencias VALUES ('$evaluacion','OTRO','PETERSEN','AJENO','$primera')", '23503');
            $this->rechaza($pdo, "INSERT INTO orion_b02_entradas VALUES ('$evaluacion','$primera','AJENO',99,'TEXTO','AJENA','$pregunta','TEXTO',false,NULL)", '23503');
            $this->rechaza($pdo, "INSERT INTO orion_b02_entradas VALUES ('$evaluacion','$primera','{$aplicaciones[$primera]['cod_instrumento']}',99,'TEXTO','OTRA','$preguntaAjena','TEXTO',false,NULL)", '23503');
            $this->rechaza($pdo, "INSERT INTO orion_b02_entradas SELECT * FROM orion_b02_entradas WHERE evaluacion='$evaluacion' AND aplicacion='$primera'", '23505');
            $pdo->prepare('UPDATE orion_b02_evaluaciones SET cerrada=true WHERE codigo=?')->execute([$evaluacion]);
            $this->rechaza($pdo, "INSERT INTO orion_b02_entradas VALUES ('$evaluacion','$primera','{$aplicaciones[$primera]['cod_instrumento']}',99,'TEXTO','TARDE','$pregunta','TEXTO',false,NULL)", 'P0001');
            $this->rechaza($pdo, "INSERT INTO orion_b02_referencias VALUES ('$evaluacion','$residente','PETERSEN','TARDE','$primera')", 'P0001');
            $this->rechaza($pdo, "UPDATE orion_b02_entradas SET enunciado='ALTERADO'", 'P0001');
            $this->rechaza($pdo, 'DELETE FROM orion_b02_entradas', 'P0001');
            self::assertSame(5, (int) $pdo->query('SELECT count(*) FROM orion_b02_entradas')->fetchColumn());
        } finally {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }

    private function rechaza(PDO $pdo, string $sql, string $sqlstate): void
    {
        $pdo->exec('SAVEPOINT orion_b02_negativo');
        try {
            $pdo->exec($sql);
            self::fail('PostgreSQL aceptó una escritura inválida: '.$sql);
        } catch (PDOException $error) {
            self::assertSame($sqlstate, $error->getCode());
        } finally {
            $pdo->exec('ROLLBACK TO SAVEPOINT orion_b02_negativo');
            $pdo->exec('RELEASE SAVEPOINT orion_b02_negativo');
        }
    }
}
