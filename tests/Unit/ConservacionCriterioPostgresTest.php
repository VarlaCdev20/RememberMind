<?php

declare(strict_types=1);

namespace Tests\Unit;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Tests\Support\EntornoPruebas;

/** Ensayo de transición de dos relaciones 0..1 en PostgreSQL desechable. No instala DDL institucional. */
final class ConservacionCriterioPostgresTest extends TestCase
{
    public function test_resultado_y_traza_se_conservan_sin_perder_identidad_ni_relaciones(): void
    {
        if (getenv('RM_TEST_PROFILE') !== 'postgres') {
            self::markTestSkipped('La transición se ensaya únicamente en PostgreSQL desechable.');
        }

        $base = (string) getenv('DB_DATABASE');
        $usuario = (string) getenv('DB_USERNAME');
        $host = (string) getenv('DB_HOST');
        self::assertTrue(EntornoPruebas::postgresDesechable($base, $usuario, $host));
        $db = new PDO('pgsql:host='.$host.';port='.getenv('DB_PORT').';dbname='.$base,
            $usuario, (string) getenv('DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $identidad = $db->query('SELECT current_database() AS base, current_user AS usuario,
            pg_get_userbyid(datdba) AS propietario FROM pg_database WHERE datname=current_database()')
            ->fetch(PDO::FETCH_ASSOC);
        self::assertSame([$base, $usuario, $usuario], array_values($identidad));

        $db->beginTransaction();
        try {
            $db->exec(<<<'SQL'
                CREATE TEMP TABLE orion_cp_valores (codigo text PRIMARY KEY) ON COMMIT DROP;
                CREATE TEMP TABLE orion_cp_evaluaciones (codigo text PRIMARY KEY) ON COMMIT DROP;
                CREATE TEMP TABLE orion_cp_nodos (codigo text PRIMARY KEY) ON COMMIT DROP;
                CREATE TEMP TABLE orion_cp_reglas (codigo text PRIMARY KEY) ON COMMIT DROP;
                CREATE TEMP TABLE orion_cp_criterios (
                    codigo text PRIMARY KEY, evaluacion text NOT NULL REFERENCES orion_cp_evaluaciones(codigo),
                    nodo text NOT NULL REFERENCES orion_cp_nodos(codigo), estado text NOT NULL,
                    modificador text, motivo text, fecha timestamp NOT NULL, UNIQUE(evaluacion,nodo)
                ) ON COMMIT DROP;
                CREATE TEMP TABLE orion_cp_resultados (
                    codigo text PRIMARY KEY, criterio text NOT NULL UNIQUE REFERENCES orion_cp_criterios(codigo),
                    valor text NOT NULL REFERENCES orion_cp_valores(codigo), fecha timestamp NOT NULL,
                    justificacion text
                ) ON COMMIT DROP;
                CREATE TEMP TABLE orion_cp_trazas (
                    codigo text PRIMARY KEY, criterio text NOT NULL UNIQUE REFERENCES orion_cp_criterios(codigo),
                    estado text NOT NULL, inicio timestamp NOT NULL, fin timestamp
                ) ON COMMIT DROP;
                CREATE TEMP TABLE orion_cp_evaluaciones_reglas (
                    codigo text PRIMARY KEY, traza text NOT NULL REFERENCES orion_cp_trazas(codigo),
                    regla text NOT NULL REFERENCES orion_cp_reglas(codigo), UNIQUE(traza,regla)
                ) ON COMMIT DROP;
                CREATE TEMP TABLE orion_cp_condiciones (
                    codigo text PRIMARY KEY, evaluacion_regla text NOT NULL REFERENCES orion_cp_evaluaciones_reglas(codigo)
                ) ON COMMIT DROP;
                INSERT INTO orion_cp_valores VALUES ('VAL-1');
                INSERT INTO orion_cp_evaluaciones VALUES ('E-1');
                INSERT INTO orion_cp_nodos VALUES ('N-1'),('N-2'),('N-3'),('N-4');
                INSERT INTO orion_cp_reglas VALUES ('R-1');
                INSERT INTO orion_cp_criterios VALUES
                    ('C-1','E-1','N-1','INSUFICIENTE',NULL,NULL,'2026-01-01 10:00:00'),
                    ('C-2','E-1','N-2','EVALUABLE',NULL,'motivo literal','2026-01-01 10:01:00'),
                    ('C-3','E-1','N-3','EVALUABLE','MOD-1',NULL,'2026-01-01 10:02:00'),
                    ('C-4','E-1','N-4','EVALUABLE',NULL,NULL,'2026-01-01 10:03:00');
                INSERT INTO orion_cp_resultados VALUES
                    ('RES-2','C-2','VAL-1','2026-01-01 10:11:00',NULL),
                    ('RES-4','C-4','VAL-1','2026-01-01 10:13:00','justificación original');
                INSERT INTO orion_cp_trazas VALUES
                    ('T-3','C-3','EN_CURSO','2026-01-01 09:55:00',NULL),
                    ('T-4','C-4','FINALIZADA','2026-01-01 09:56:00','2026-01-01 10:14:00');
                INSERT INTO orion_cp_evaluaciones_reglas VALUES ('ER-4','T-4','R-1');
                INSERT INTO orion_cp_condiciones VALUES ('COND-4','ER-4');
                SQL);

            $originales = [];
            foreach (['criterios', 'resultados', 'trazas', 'evaluaciones_reglas', 'condiciones'] as $tabla) {
                $originales[$tabla] = $db->query("SELECT * FROM orion_cp_$tabla ORDER BY codigo")->fetchAll(PDO::FETCH_ASSOC);
            }

            $definicion = <<<'SQL'
                CREATE TEMP TABLE orion_cp_criterios_nuevos (
                    codigo text PRIMARY KEY, evaluacion text NOT NULL REFERENCES orion_cp_evaluaciones(codigo),
                    nodo text NOT NULL REFERENCES orion_cp_nodos(codigo), estado text NOT NULL,
                    modificador text, motivo text, fecha timestamp NOT NULL,
                    resultado text UNIQUE, valor text REFERENCES orion_cp_valores(codigo),
                    fecha_resultado timestamp, justificacion text,
                    traza text UNIQUE, estado_traza text, inicio_traza timestamp, fin_traza timestamp,
                    UNIQUE(evaluacion,nodo),
                    CHECK ((resultado IS NULL AND valor IS NULL AND fecha_resultado IS NULL AND justificacion IS NULL)
                        OR (resultado IS NOT NULL AND valor IS NOT NULL AND fecha_resultado IS NOT NULL)),
                    CHECK ((traza IS NULL AND estado_traza IS NULL AND inicio_traza IS NULL AND fin_traza IS NULL)
                        OR (traza IS NOT NULL AND estado_traza IS NOT NULL AND inicio_traza IS NOT NULL))
                ) ON COMMIT DROP;
                INSERT INTO orion_cp_criterios_nuevos
                SELECT c.codigo,c.evaluacion,c.nodo,c.estado,c.modificador,c.motivo,c.fecha,
                    r.codigo,r.valor,r.fecha,r.justificacion,t.codigo,t.estado,t.inicio,t.fin
                FROM orion_cp_criterios c
                LEFT JOIN orion_cp_resultados r ON r.criterio=c.codigo
                LEFT JOIN orion_cp_trazas t ON t.criterio=c.codigo;
                SQL;
            $db->exec('SAVEPOINT orion_cp_fallo');
            $db->exec($definicion);
            $db->exec('ROLLBACK TO SAVEPOINT orion_cp_fallo');
            self::assertSame(0, (int) $db->query("SELECT count(*) FROM pg_class WHERE relname='orion_cp_criterios_nuevos'")->fetchColumn());
            self::assertSame($originales['resultados'], $db->query('SELECT * FROM orion_cp_resultados ORDER BY codigo')->fetchAll(PDO::FETCH_ASSOC));

            $db->exec($definicion);
            $db->exec(<<<'SQL'
                CREATE TEMP TABLE orion_cp_reglas_nuevas (
                    codigo text PRIMARY KEY, traza text NOT NULL REFERENCES orion_cp_criterios_nuevos(traza),
                    regla text NOT NULL REFERENCES orion_cp_reglas(codigo), UNIQUE(traza,regla)
                ) ON COMMIT DROP;
                INSERT INTO orion_cp_reglas_nuevas SELECT * FROM orion_cp_evaluaciones_reglas;
                CREATE TEMP TABLE orion_cp_condiciones_nuevas (
                    codigo text PRIMARY KEY, evaluacion_regla text NOT NULL REFERENCES orion_cp_reglas_nuevas(codigo)
                ) ON COMMIT DROP;
                INSERT INTO orion_cp_condiciones_nuevas SELECT * FROM orion_cp_condiciones;
                SQL);

            $reconstruidos = ['criterios' => [], 'resultados' => [], 'trazas' => [],
                'evaluaciones_reglas' => $db->query('SELECT * FROM orion_cp_reglas_nuevas ORDER BY codigo')->fetchAll(PDO::FETCH_ASSOC),
                'condiciones' => $db->query('SELECT * FROM orion_cp_condiciones_nuevas ORDER BY codigo')->fetchAll(PDO::FETCH_ASSOC)];
            foreach ($db->query('SELECT * FROM orion_cp_criterios_nuevos ORDER BY codigo')->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $reconstruidos['criterios'][] = array_intersect_key($fila, array_flip(['codigo', 'evaluacion', 'nodo', 'estado', 'modificador', 'motivo', 'fecha']));
                if ($fila['resultado'] !== null) {
                    $reconstruidos['resultados'][] = ['codigo' => $fila['resultado'], 'criterio' => $fila['codigo'],
                        'valor' => $fila['valor'], 'fecha' => $fila['fecha_resultado'], 'justificacion' => $fila['justificacion']];
                }
                if ($fila['traza'] !== null) {
                    $reconstruidos['trazas'][] = ['codigo' => $fila['traza'], 'criterio' => $fila['codigo'],
                        'estado' => $fila['estado_traza'], 'inicio' => $fila['inicio_traza'], 'fin' => $fila['fin_traza']];
                }
            }
            foreach ($originales as $tabla => $filas) {
                self::assertSame($filas, $reconstruidos[$tabla], "La proyección alteró $tabla.");
            }

            $db->exec('SAVEPOINT orion_cp_rechazo');
            try {
                $db->exec("INSERT INTO orion_cp_criterios_nuevos(codigo,evaluacion,nodo,estado,fecha,resultado)
                    VALUES ('C-MAL','E-1','N-4','EVALUABLE','2026-01-01','RES-MAL')");
                self::fail('El resultado incompleto pasó el CHECK.');
            } catch (PDOException) {
                $db->exec('ROLLBACK TO SAVEPOINT orion_cp_rechazo');
            }
            $db->exec('SAVEPOINT orion_cp_fk');
            try {
                $db->exec("INSERT INTO orion_cp_reglas_nuevas VALUES ('ER-MAL','T-AJENA','R-1')");
                self::fail('La regla aceptó una traza inexistente.');
            } catch (PDOException) {
                $db->exec('ROLLBACK TO SAVEPOINT orion_cp_fk');
            }
            self::assertSame(4, (int) $db->query('SELECT count(*) FROM orion_cp_criterios_nuevos')->fetchColumn());
        } finally {
            $db->rollBack();
        }
    }
}
