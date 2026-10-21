<?php

declare(strict_types=1);

namespace Tests\Unit;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Tests\Support\EntornoPruebas;

/** Ensayo de dos conexiones sobre una base PostgreSQL desechable, sin migración institucional. */
final class ConservacionCierrePostgresTest extends TestCase
{
    public function test_insercion_y_cierre_se_serializan_en_ambos_ordenes(): void
    {
        if (getenv('RM_TEST_PROFILE') !== 'postgres') {
            self::markTestSkipped('La concurrencia requiere PostgreSQL desechable.');
        }

        $database = (string) getenv('DB_DATABASE');
        $username = (string) getenv('DB_USERNAME');
        $host = (string) getenv('DB_HOST');
        self::assertTrue(EntornoPruebas::postgresDesechable($database, $username, $host));
        $dsn = 'pgsql:host='.$host.';port='.getenv('DB_PORT').';dbname='.$database;
        $opciones = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        $cierre = new PDO($dsn, $username, (string) getenv('DB_PASSWORD'), $opciones);
        $insercion = new PDO($dsn, $username, (string) getenv('DB_PASSWORD'), $opciones);
        $identidad = $cierre->query('SELECT current_database() AS base, current_user AS usuario,
            pg_get_userbyid(datdba) AS propietario FROM pg_database WHERE datname=current_database()')
            ->fetch(PDO::FETCH_ASSOC);
        self::assertSame($database, $identidad['base']);
        self::assertSame($username, $identidad['usuario']);
        self::assertSame($username, $identidad['propietario']);

        $esquema = 'orion_cierre_'.bin2hex(random_bytes(6));
        $cierre->exec('CREATE SCHEMA '.$esquema);
        try {
            $cierre->exec(str_replace('ESQUEMA', $esquema, <<<'SQL'
                CREATE TABLE ESQUEMA.evaluaciones (
                    codigo text PRIMARY KEY, cerrada boolean NOT NULL DEFAULT false
                );
                CREATE TABLE ESQUEMA.entradas (
                    codigo text PRIMARY KEY,
                    evaluacion text NOT NULL REFERENCES ESQUEMA.evaluaciones(codigo)
                );
                CREATE FUNCTION ESQUEMA.guardar_entrada() RETURNS trigger LANGUAGE plpgsql AS $$
                DECLARE estado boolean;
                BEGIN
                    IF TG_OP <> 'INSERT' THEN
                        RAISE EXCEPTION 'historia inmutable';
                    END IF;
                    SELECT e.cerrada INTO estado FROM ESQUEMA.evaluaciones e
                    WHERE e.codigo=NEW.evaluacion FOR UPDATE;
                    IF estado THEN
                        RAISE EXCEPTION 'evaluacion cerrada';
                    END IF;
                    RETURN NEW;
                END $$;
                CREATE TRIGGER guardar_entrada BEFORE INSERT OR UPDATE OR DELETE
                    ON ESQUEMA.entradas FOR EACH ROW EXECUTE FUNCTION ESQUEMA.guardar_entrada();
                CREATE FUNCTION ESQUEMA.no_reabrir() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    IF OLD.cerrada AND NOT NEW.cerrada THEN
                        RAISE EXCEPTION 'cierre irreversible';
                    END IF;
                    RETURN NEW;
                END $$;
                CREATE TRIGGER no_reabrir BEFORE UPDATE OF cerrada
                    ON ESQUEMA.evaluaciones FOR EACH ROW EXECUTE FUNCTION ESQUEMA.no_reabrir();
                INSERT INTO ESQUEMA.evaluaciones(codigo) VALUES ('E-1'),('E-2');
                SQL));

            $cierre->exec("SET lock_timeout TO '250ms'");
            $insercion->exec("SET lock_timeout TO '250ms'");

            // El cierre obtiene primero el bloqueo. La inserción debe esperar y luego abstenerse.
            $cierre->beginTransaction();
            $cierre->exec("UPDATE $esquema.evaluaciones SET cerrada=true WHERE codigo='E-1'");
            $insercion->beginTransaction();
            $this->rechaza($insercion, "INSERT INTO $esquema.entradas VALUES ('I-TARDE','E-1')", '55P03');
            $insercion->rollBack();
            $cierre->commit();
            $this->rechaza($insercion, "INSERT INTO $esquema.entradas VALUES ('I-TARDE','E-1')", 'P0001');

            // La inserción obtiene primero el bloqueo. El cierre espera y respeta esa entrada.
            $insercion->beginTransaction();
            $insercion->exec("INSERT INTO $esquema.entradas VALUES ('I-PRIMERA','E-2')");
            $cierre->beginTransaction();
            $this->rechaza($cierre, "UPDATE $esquema.evaluaciones SET cerrada=true WHERE codigo='E-2'", '55P03');
            $cierre->rollBack();
            $insercion->commit();
            $cierre->exec("UPDATE $esquema.evaluaciones SET cerrada=true WHERE codigo='E-2'");
            $this->rechaza($insercion, "INSERT INTO $esquema.entradas VALUES ('I-POSTERIOR','E-2')", 'P0001');
            $this->rechaza($cierre, "UPDATE $esquema.evaluaciones SET cerrada=false WHERE codigo='E-2'", 'P0001');
            self::assertSame(1, (int) $cierre->query("SELECT count(*) FROM $esquema.entradas")->fetchColumn());
            self::assertSame(2, (int) $cierre->query("SELECT count(*) FROM $esquema.evaluaciones WHERE cerrada")->fetchColumn());
        } finally {
            foreach ([$cierre, $insercion] as $conexion) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
            }
            $cierre->exec('DROP SCHEMA '.$esquema.' CASCADE');
        }
    }

    private function rechaza(PDO $pdo, string $sql, string $sqlstate): void
    {
        try {
            $pdo->exec($sql);
            self::fail('PostgreSQL aceptó una escritura incompatible con el cierre.');
        } catch (PDOException $error) {
            self::assertSame($sqlstate, $error->getCode());
        }
    }
}
