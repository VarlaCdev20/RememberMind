<?php

declare(strict_types=1);

/** Ensayo de unión tipada de vínculos: SQLite en memoria, datos sintéticos, sin Laravel. */

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys = ON');

function exigirVinculos(bool $ok, string $mensaje): void
{
    if (! $ok) {
        throw new RuntimeException($mensaje);
    }
}

function leerVinculos(PDO $db, string $tabla, string $orden): array
{
    return $db->query("SELECT * FROM $tabla ORDER BY $orden")->fetchAll(PDO::FETCH_ASSOC);
}

function rechazarVinculo(PDO $db, string $sql): void
{
    try {
        $db->exec($sql);
    } catch (Throwable) {
        return;
    }

    throw new RuntimeException('Se aceptó un vínculo inválido.');
}

$db->exec(<<<'SQL'
CREATE TABLE evaluacion_criterios (cod_evaluacion_criterio TEXT PRIMARY KEY);
CREATE TABLE evaluaciones_condiciones_regla (cod_evaluacion_condicion TEXT PRIMARY KEY);
CREATE TABLE evidencias_evaluacion (cod_evidencia_evaluacion TEXT PRIMARY KEY);
CREATE TABLE hechos_instantanea_experta (cod_hecho_instantanea TEXT PRIMARY KEY);
CREATE TABLE soportes_sinteticos (cod_soporte_sintetico TEXT PRIMARY KEY);
CREATE TABLE evidencias_criterio_evaluacion (
    cod_evidencia_criterio TEXT PRIMARY KEY,
    cod_evaluacion_criterio TEXT NOT NULL REFERENCES evaluacion_criterios(cod_evaluacion_criterio) ON DELETE RESTRICT,
    cod_evidencia_evaluacion TEXT REFERENCES evidencias_evaluacion(cod_evidencia_evaluacion) ON DELETE RESTRICT,
    rol_en_criterio TEXT NOT NULL,
    estado_participacion TEXT NOT NULL,
    justificacion TEXT,
    fecha_hora_vinculacion TEXT NOT NULL,
    orden_conservacion INTEGER,
    cod_hecho_instantanea TEXT REFERENCES hechos_instantanea_experta(cod_hecho_instantanea) ON DELETE RESTRICT,
    cod_soporte_sintetico TEXT REFERENCES soportes_sinteticos(cod_soporte_sintetico) ON DELETE RESTRICT,
    CHECK ((cod_evidencia_evaluacion IS NOT NULL AND cod_hecho_instantanea IS NULL AND cod_soporte_sintetico IS NULL)
        OR (cod_evidencia_evaluacion IS NULL AND cod_hecho_instantanea IS NOT NULL AND cod_soporte_sintetico IS NOT NULL))
);
CREATE UNIQUE INDEX ec_evidencia ON evidencias_criterio_evaluacion(cod_evaluacion_criterio,cod_evidencia_evaluacion);
CREATE TABLE evidencias_soporte_condicion (
    cod_evidencia_soporte_condicion TEXT PRIMARY KEY,
    cod_evaluacion_condicion TEXT NOT NULL REFERENCES evaluaciones_condiciones_regla(cod_evaluacion_condicion) ON DELETE RESTRICT,
    cod_evidencia_evaluacion TEXT NOT NULL REFERENCES evidencias_evaluacion(cod_evidencia_evaluacion) ON DELETE RESTRICT,
    UNIQUE(cod_evaluacion_condicion,cod_evidencia_evaluacion)
);
INSERT INTO evaluacion_criterios VALUES ('CR-1'),('CR-2');
INSERT INTO evaluaciones_condiciones_regla VALUES ('CD-1'),('CD-2');
INSERT INTO evidencias_evaluacion VALUES ('EV-1'),('EV-2');
INSERT INTO hechos_instantanea_experta VALUES ('HE-1');
INSERT INTO soportes_sinteticos VALUES ('SS-1');
INSERT INTO evidencias_criterio_evaluacion VALUES
 ('EC-1','CR-1','EV-1','ORIGINAL','ADMITIDA',NULL,'2026-01-01 10:00:00',0,NULL,NULL),
 ('EC-2','CR-2',NULL,'SINTETICO','ADMITIDA','motivo','2026-01-01 10:01:00',1,'HE-1','SS-1');
INSERT INTO evidencias_soporte_condicion VALUES
 ('ESC-1','CD-1','EV-2'),('ESC-2','CD-2','EV-2');
SQL);

$antes = [
    'criterios' => leerVinculos($db, 'evidencias_criterio_evaluacion', 'cod_evidencia_criterio'),
    'soportes' => leerVinculos($db, 'evidencias_soporte_condicion', 'cod_evidencia_soporte_condicion'),
];

$crear = <<<'SQL'
CREATE TABLE evidencias_criterio_evaluacion_v2 (
    cod_evidencia_criterio TEXT PRIMARY KEY CHECK(length(cod_evidencia_criterio) <= 20),
    cod_evidencia_soporte_condicion TEXT UNIQUE,
    cod_evaluacion_criterio TEXT REFERENCES evaluacion_criterios(cod_evaluacion_criterio) ON DELETE RESTRICT,
    cod_evaluacion_condicion TEXT REFERENCES evaluaciones_condiciones_regla(cod_evaluacion_condicion) ON DELETE RESTRICT,
    cod_evidencia_evaluacion TEXT REFERENCES evidencias_evaluacion(cod_evidencia_evaluacion) ON DELETE RESTRICT,
    rol_en_criterio TEXT,
    estado_participacion TEXT,
    justificacion TEXT,
    fecha_hora_vinculacion TEXT,
    orden_conservacion INTEGER,
    cod_hecho_instantanea TEXT REFERENCES hechos_instantanea_experta(cod_hecho_instantanea) ON DELETE RESTRICT,
    cod_soporte_sintetico TEXT REFERENCES soportes_sinteticos(cod_soporte_sintetico) ON DELETE RESTRICT,
    CHECK ((cod_evaluacion_criterio IS NOT NULL AND cod_evaluacion_condicion IS NULL
        AND cod_evidencia_soporte_condicion IS NULL AND rol_en_criterio IS NOT NULL
        AND estado_participacion IS NOT NULL AND fecha_hora_vinculacion IS NOT NULL
        AND ((cod_evidencia_evaluacion IS NOT NULL AND cod_hecho_instantanea IS NULL AND cod_soporte_sintetico IS NULL)
          OR (cod_evidencia_evaluacion IS NULL AND cod_hecho_instantanea IS NOT NULL AND cod_soporte_sintetico IS NOT NULL)))
        OR (cod_evaluacion_criterio IS NULL AND cod_evaluacion_condicion IS NOT NULL
        AND cod_evidencia_soporte_condicion IS NOT NULL AND cod_evidencia_evaluacion IS NOT NULL
        AND rol_en_criterio IS NULL AND estado_participacion IS NULL AND justificacion IS NULL
        AND fecha_hora_vinculacion IS NULL AND orden_conservacion IS NULL
        AND cod_hecho_instantanea IS NULL AND cod_soporte_sintetico IS NULL))
);
CREATE UNIQUE INDEX ec_v2_criterio_evidencia ON evidencias_criterio_evaluacion_v2(cod_evaluacion_criterio,cod_evidencia_evaluacion);
CREATE UNIQUE INDEX ec_v2_condicion_evidencia ON evidencias_criterio_evaluacion_v2(cod_evaluacion_condicion,cod_evidencia_evaluacion);
SQL;

$copiarParticipaciones = <<<'SQL'
INSERT INTO evidencias_criterio_evaluacion_v2
SELECT cod_evidencia_criterio,NULL,cod_evaluacion_criterio,NULL,cod_evidencia_evaluacion,
 rol_en_criterio,estado_participacion,justificacion,fecha_hora_vinculacion,
 orden_conservacion,cod_hecho_instantanea,cod_soporte_sintetico
FROM evidencias_criterio_evaluacion;
SQL;

function copiarVinculos(PDO $db, string $participaciones): void
{
    $db->exec($participaciones);
    $insertar = $db->prepare('INSERT INTO evidencias_criterio_evaluacion_v2
        (cod_evidencia_criterio,cod_evidencia_soporte_condicion,cod_evaluacion_condicion,cod_evidencia_evaluacion)
        VALUES (?,?,?,?)');
    foreach (leerVinculos($db, 'evidencias_soporte_condicion', 'cod_evidencia_soporte_condicion') as $soporte) {
        $insertar->execute([
            bin2hex(random_bytes(10)),
            $soporte['cod_evidencia_soporte_condicion'],
            $soporte['cod_evaluacion_condicion'],
            $soporte['cod_evidencia_evaluacion'],
        ]);
    }
}

$db->beginTransaction();
try {
    $db->exec($crear);
    copiarVinculos($db, $copiarParticipaciones);
    throw new RuntimeException('FALLO_INYECTADO');
} catch (RuntimeException $error) {
    exigirVinculos($error->getMessage() === 'FALLO_INYECTADO', 'Error inesperado en ensayo de rollback.');
    $db->rollBack();
}
exigirVinculos(leerVinculos($db, 'evidencias_soporte_condicion', 'cod_evidencia_soporte_condicion') === $antes['soportes'], 'Rollback cambió soportes.');
exigirVinculos((int) $db->query("SELECT count(*) FROM sqlite_master WHERE name='evidencias_criterio_evaluacion_v2'")->fetchColumn() === 0, 'Rollback dejó tabla parcial.');

$db->exec('PRAGMA foreign_keys = OFF');
$db->beginTransaction();
try {
    $db->exec($crear);
    copiarVinculos($db, $copiarParticipaciones);
    $db->exec('DROP TABLE evidencias_soporte_condicion');
    $db->exec('DROP TABLE evidencias_criterio_evaluacion');
    $db->exec('ALTER TABLE evidencias_criterio_evaluacion_v2 RENAME TO evidencias_criterio_evaluacion');
    exigirVinculos($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'FK inválida después de la transición.');
    $db->commit();
} catch (Throwable $error) {
    $db->rollBack();
    throw $error;
} finally {
    $db->exec('PRAGMA foreign_keys = ON');
}

$criterios = [];
$soportes = [];
foreach (leerVinculos($db, 'evidencias_criterio_evaluacion', 'cod_evidencia_criterio') as $fila) {
    if ($fila['cod_evaluacion_criterio'] !== null) {
        $criterios[] = array_intersect_key($fila, array_flip(array_keys($antes['criterios'][0])));
    } else {
        $soportes[] = [
            'cod_evidencia_soporte_condicion' => $fila['cod_evidencia_soporte_condicion'],
            'cod_evaluacion_condicion' => $fila['cod_evaluacion_condicion'],
            'cod_evidencia_evaluacion' => $fila['cod_evidencia_evaluacion'],
        ];
    }
}
usort($soportes, static fn (array $a, array $b): int => strcmp($a['cod_evidencia_soporte_condicion'], $b['cod_evidencia_soporte_condicion']));
exigirVinculos($criterios === $antes['criterios'], 'No reconstruyó participaciones exactas.');
exigirVinculos($soportes === $antes['soportes'], 'No reconstruyó soportes exactos.');
exigirVinculos(count(leerVinculos($db, 'evidencias_criterio_evaluacion', 'cod_evidencia_criterio')) === 4, 'Perdió un vínculo independiente.');

rechazarVinculo($db, "INSERT INTO evidencias_criterio_evaluacion(cod_evidencia_criterio,cod_evaluacion_criterio,cod_evaluacion_condicion,cod_evidencia_evaluacion,rol_en_criterio,estado_participacion,fecha_hora_vinculacion) VALUES ('EC-MAL','CR-1','CD-1','EV-1','ORIGINAL','ADMITIDA','2026-01-01')");
rechazarVinculo($db, "INSERT INTO evidencias_criterio_evaluacion(cod_evidencia_criterio,cod_evidencia_soporte_condicion,cod_evaluacion_condicion,cod_evidencia_evaluacion) VALUES ('EC-MAL','ESC-1','CD-1','EV-1')");
rechazarVinculo($db, "INSERT INTO evidencias_criterio_evaluacion(cod_evidencia_criterio,cod_evidencia_soporte_condicion,cod_evaluacion_condicion,cod_evidencia_evaluacion) VALUES ('EC-MAL','ESC-MAL','CD-AJENA','EV-1')");
exigirVinculos($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'FK inválida después de pruebas negativas.');

echo "PASS SQLite desechable: rollback, 2 participaciones, 2 soportes independientes, reconstrucción exacta y FK/checks.\n";
echo "LÍMITE: no prueba PostgreSQL, concurrencia, 4FN independiente ni uso institucional. No autoriza DDL.\n";
