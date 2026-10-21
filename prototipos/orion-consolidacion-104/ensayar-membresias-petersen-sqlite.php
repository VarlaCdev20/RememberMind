<?php

declare(strict_types=1);

/** Ensayo B03 aislado: soportes Petersen como filas independientes, sin introducir JSON/EAV. */
$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys = ON');

function exigirMembresia(bool $ok, string $mensaje): void
{
    if (! $ok) {
        throw new RuntimeException($mensaje);
    }
}

function rechazarMembresia(PDO $db, string $sql): void
{
    try {
        $db->exec($sql);
    } catch (PDOException) {
        return;
    }

    throw new RuntimeException('Se admitió una membresía incompatible.');
}

$db->exec(<<<'SQL'
CREATE TABLE evaluaciones_expertas (cod_evaluacion_experta TEXT PRIMARY KEY,
 estado TEXT NOT NULL CHECK(estado IN ('ABIERTA','CERRADA')));
CREATE TABLE valoraciones_petersen_originales (cod_valoracion_petersen TEXT PRIMARY KEY);
CREATE TABLE soportes_valoracion_petersen (cod_soporte_petersen TEXT PRIMARY KEY,
 cod_valoracion_petersen TEXT NOT NULL,
 UNIQUE(cod_soporte_petersen,cod_valoracion_petersen),
 FOREIGN KEY(cod_valoracion_petersen) REFERENCES valoraciones_petersen_originales(cod_valoracion_petersen));
CREATE TABLE juicios_adquiridos (cod_juicio_adquirido TEXT PRIMARY KEY,
 cod_evaluacion_experta TEXT NOT NULL, cod_valoracion_petersen TEXT NOT NULL,
 UNIQUE(cod_juicio_adquirido,cod_evaluacion_experta,cod_valoracion_petersen),
 UNIQUE(cod_evaluacion_experta,cod_valoracion_petersen),
 FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_expertas(cod_evaluacion_experta),
 FOREIGN KEY(cod_valoracion_petersen) REFERENCES valoraciones_petersen_originales(cod_valoracion_petersen));
CREATE TABLE criterios_evaluacion (cod_evaluacion_criterio TEXT PRIMARY KEY,
 cod_evaluacion_experta TEXT NOT NULL,
 UNIQUE(cod_evaluacion_criterio,cod_evaluacion_experta));
CREATE TABLE condiciones_evaluacion (cod_condicion TEXT PRIMARY KEY,
 cod_evaluacion_experta TEXT NOT NULL,
 UNIQUE(cod_condicion,cod_evaluacion_experta));
CREATE TABLE evidencias_evaluacion (cod_evidencia TEXT PRIMARY KEY,
 cod_evaluacion_experta TEXT NOT NULL,
 UNIQUE(cod_evidencia,cod_evaluacion_experta));
INSERT INTO evaluaciones_expertas VALUES ('E-1','ABIERTA');
INSERT INTO valoraciones_petersen_originales VALUES ('V-1'),('V-2');
INSERT INTO soportes_valoracion_petersen VALUES ('S-1','V-1'),('S-2','V-1'),('S-3','V-2');
INSERT INTO juicios_adquiridos VALUES ('J-1','E-1','V-1'),('J-2','E-1','V-2');
INSERT INTO criterios_evaluacion VALUES ('CR-1','E-1');
INSERT INTO condiciones_evaluacion VALUES ('CD-1','E-1');
INSERT INTO evidencias_evaluacion VALUES ('EV-1','E-1');
SQL);

$crear = <<<'SQL'
CREATE TABLE evidencias_criterio_evaluacion (
 cod_evidencia_criterio TEXT PRIMARY KEY CHECK(length(cod_evidencia_criterio) BETWEEN 1 AND 20),
 cod_evaluacion_experta TEXT NOT NULL REFERENCES evaluaciones_expertas(cod_evaluacion_experta),
 tipo_vinculo TEXT NOT NULL CHECK(tipo_vinculo IN ('CRITERIO','CONDICION','MEMBRESIA_PETERSEN')),
 cod_evaluacion_criterio TEXT,
 cod_condicion TEXT,
 cod_evidencia TEXT,
 cod_juicio_adquirido TEXT,
 cod_valoracion_petersen TEXT,
 cod_soporte_petersen TEXT,
 orden_soporte INTEGER,
 FOREIGN KEY(cod_evaluacion_criterio,cod_evaluacion_experta)
   REFERENCES criterios_evaluacion(cod_evaluacion_criterio,cod_evaluacion_experta) ON DELETE RESTRICT,
 FOREIGN KEY(cod_condicion,cod_evaluacion_experta)
   REFERENCES condiciones_evaluacion(cod_condicion,cod_evaluacion_experta) ON DELETE RESTRICT,
 FOREIGN KEY(cod_evidencia,cod_evaluacion_experta)
   REFERENCES evidencias_evaluacion(cod_evidencia,cod_evaluacion_experta) ON DELETE RESTRICT,
 FOREIGN KEY(cod_juicio_adquirido,cod_evaluacion_experta,cod_valoracion_petersen)
   REFERENCES juicios_adquiridos(cod_juicio_adquirido,cod_evaluacion_experta,cod_valoracion_petersen) ON DELETE RESTRICT,
 FOREIGN KEY(cod_soporte_petersen,cod_valoracion_petersen)
   REFERENCES soportes_valoracion_petersen(cod_soporte_petersen,cod_valoracion_petersen) ON DELETE RESTRICT,
 CHECK ((tipo_vinculo='CRITERIO' AND cod_evaluacion_criterio IS NOT NULL AND cod_evidencia IS NOT NULL
    AND cod_condicion IS NULL AND cod_juicio_adquirido IS NULL AND cod_valoracion_petersen IS NULL
    AND cod_soporte_petersen IS NULL AND orden_soporte IS NULL)
  OR (tipo_vinculo='CONDICION' AND cod_condicion IS NOT NULL AND cod_evidencia IS NOT NULL
    AND cod_evaluacion_criterio IS NULL AND cod_juicio_adquirido IS NULL AND cod_valoracion_petersen IS NULL
    AND cod_soporte_petersen IS NULL AND orden_soporte IS NULL)
  OR (tipo_vinculo='MEMBRESIA_PETERSEN' AND cod_juicio_adquirido IS NOT NULL
    AND cod_valoracion_petersen IS NOT NULL AND cod_soporte_petersen IS NOT NULL AND orden_soporte>=0
    AND cod_evaluacion_criterio IS NULL AND cod_condicion IS NULL AND cod_evidencia IS NULL))
);
CREATE UNIQUE INDEX vinculo_criterio ON evidencias_criterio_evaluacion(cod_evaluacion_criterio,cod_evidencia)
 WHERE tipo_vinculo='CRITERIO';
CREATE UNIQUE INDEX vinculo_condicion ON evidencias_criterio_evaluacion(cod_condicion,cod_evidencia)
 WHERE tipo_vinculo='CONDICION';
CREATE UNIQUE INDEX membresia_soporte ON evidencias_criterio_evaluacion(cod_juicio_adquirido,cod_soporte_petersen)
 WHERE tipo_vinculo='MEMBRESIA_PETERSEN';
CREATE UNIQUE INDEX membresia_orden ON evidencias_criterio_evaluacion(cod_juicio_adquirido,orden_soporte)
 WHERE tipo_vinculo='MEMBRESIA_PETERSEN';
CREATE TRIGGER vinculo_cerrado BEFORE INSERT ON evidencias_criterio_evaluacion
 WHEN EXISTS(SELECT 1 FROM evaluaciones_expertas e
  WHERE e.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND e.estado='CERRADA')
 BEGIN SELECT RAISE(ABORT,'Colección de soportes cerrada'); END;
CREATE TRIGGER vinculo_inmutable_update BEFORE UPDATE ON evidencias_criterio_evaluacion
 BEGIN SELECT RAISE(ABORT,'Vínculo histórico inmutable'); END;
CREATE TRIGGER vinculo_inmutable_delete BEFORE DELETE ON evidencias_criterio_evaluacion
 BEGIN SELECT RAISE(ABORT,'Vínculo histórico inmutable'); END;
SQL;

$db->beginTransaction();
try {
    $db->exec($crear);
    $db->exec("INSERT INTO evidencias_criterio_evaluacion VALUES ('EC-0','E-1','MEMBRESIA_PETERSEN',NULL,NULL,NULL,'J-1','V-1','S-1',0)");
    throw new RuntimeException('FALLO_INYECTADO');
} catch (RuntimeException $error) {
    exigirMembresia($error->getMessage() === 'FALLO_INYECTADO', 'Fallo de prueba inesperado.');
    $db->rollBack();
}
exigirMembresia((int) $db->query("SELECT COUNT(*) FROM sqlite_master WHERE name='evidencias_criterio_evaluacion'")->fetchColumn() === 0,
    'La reversión dejó estructura parcial.');

$db->exec($crear);
$db->exec(<<<'SQL'
INSERT INTO evidencias_criterio_evaluacion VALUES
 ('EC-1','E-1','CRITERIO','CR-1',NULL,'EV-1',NULL,NULL,NULL,NULL),
 ('EC-2','E-1','CONDICION',NULL,'CD-1','EV-1',NULL,NULL,NULL,NULL),
 ('EC-3','E-1','MEMBRESIA_PETERSEN',NULL,NULL,NULL,'J-1','V-1','S-2',0),
 ('EC-4','E-1','MEMBRESIA_PETERSEN',NULL,NULL,NULL,'J-1','V-1','S-1',1);
SQL);

$recuperado = [];
foreach ($db->query('SELECT cod_juicio_adquirido FROM juicios_adquiridos ORDER BY cod_juicio_adquirido')->fetchAll(PDO::FETCH_COLUMN) as $juicio) {
    $recuperado[$juicio] = [];
}
foreach ($db->query("SELECT cod_juicio_adquirido,cod_soporte_petersen FROM evidencias_criterio_evaluacion
    WHERE tipo_vinculo='MEMBRESIA_PETERSEN' ORDER BY cod_juicio_adquirido,orden_soporte")->fetchAll(PDO::FETCH_ASSOC) as $fila) {
    $recuperado[$fila['cod_juicio_adquirido']][] = $fila['cod_soporte_petersen'];
}
exigirMembresia($recuperado === ['J-1' => ['S-2', 'S-1'], 'J-2' => []],
    'Se perdió el orden o la colección vacía del juicio.');
rechazarMembresia($db, "INSERT INTO evidencias_criterio_evaluacion VALUES ('EC-X','E-1','MEMBRESIA_PETERSEN',NULL,NULL,NULL,'J-1','V-1','S-3',2)");
rechazarMembresia($db, "INSERT INTO evidencias_criterio_evaluacion VALUES ('EC-X','E-1','MEMBRESIA_PETERSEN',NULL,NULL,NULL,'J-1','V-1','S-1',2)");
rechazarMembresia($db, "INSERT INTO evidencias_criterio_evaluacion VALUES ('EC-X','E-1','MEMBRESIA_PETERSEN',NULL,NULL,NULL,'J-1','V-1','S-3',0)");
$db->exec("UPDATE evaluaciones_expertas SET estado='CERRADA' WHERE cod_evaluacion_experta='E-1'");
rechazarMembresia($db, "INSERT INTO evidencias_criterio_evaluacion VALUES ('EC-X','E-1','MEMBRESIA_PETERSEN',NULL,NULL,NULL,'J-2','V-2','S-3',0)");
rechazarMembresia($db, "UPDATE evidencias_criterio_evaluacion SET orden_soporte=5 WHERE cod_evidencia_criterio='EC-3'");
exigirMembresia($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'FK inválida.');

echo "PASS B03 SQLite desechable: dos juicios, soporte doble, lista vacía, orden, FK de valoración, rollback y cierre.\n";
echo "LÍMITE: esquema sintético parcial; falta compatibilidad con la unión previa, PostgreSQL, concurrencia y 4FN independiente.\n";
