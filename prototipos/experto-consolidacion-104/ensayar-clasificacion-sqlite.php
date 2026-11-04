<?php

declare(strict_types=1);

/** Ensayo 0..1 de clasificación DCL: solo SQLite :memory: con identidades sintéticas. */

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys = ON');

function exigirClasificacion(bool $ok, string $mensaje): void
{
    if (! $ok) {
        throw new RuntimeException($mensaje);
    }
}

function leerClasificacion(PDO $db, string $tabla, string $orden): array
{
    return $db->query("SELECT * FROM $tabla ORDER BY $orden")->fetchAll(PDO::FETCH_ASSOC);
}

function rechazarClasificacion(PDO $db, string $sql): void
{
    try {
        $db->exec($sql);
    } catch (Throwable) {
        return;
    }

    throw new RuntimeException('Aceptó clasificación o vínculo inválido.');
}

$db->exec(<<<'SQL'
CREATE TABLE funcionalidades_instantanea (cod_funcionalidad_instantanea TEXT PRIMARY KEY);
CREATE TABLE evaluaciones_expertas (
    cod_evaluacion_experta TEXT PRIMARY KEY,
    cod_residente TEXT NOT NULL,
    estado_ejecucion TEXT NOT NULL,
    fecha_hora_inicio TEXT NOT NULL,
    fecha_hora_fin TEXT
);
CREATE TABLE clasificaciones_dcl_instantanea (
    cod_clasificacion_dcl_instantanea TEXT PRIMARY KEY,
    cod_evaluacion_experta TEXT NOT NULL UNIQUE REFERENCES evaluaciones_expertas(cod_evaluacion_experta) ON DELETE RESTRICT,
    cod_funcionalidad_instantanea TEXT NOT NULL UNIQUE REFERENCES funcionalidades_instantanea(cod_funcionalidad_instantanea) ON DELETE RESTRICT,
    version_contrato TEXT NOT NULL,
    diferencial TEXT NOT NULL,
    patron_candidato TEXT,
    justificacion TEXT NOT NULL,
    uso TEXT NOT NULL,
    huella_clasificacion TEXT NOT NULL
);
CREATE TABLE motivos_dcl_instantanea (
    cod_motivo_dcl_instantanea TEXT PRIMARY KEY,
    cod_clasificacion_dcl_instantanea TEXT NOT NULL REFERENCES clasificaciones_dcl_instantanea(cod_clasificacion_dcl_instantanea) ON DELETE RESTRICT,
    motivo TEXT NOT NULL,
    orden INTEGER NOT NULL,
    UNIQUE(cod_clasificacion_dcl_instantanea,motivo),
    UNIQUE(cod_clasificacion_dcl_instantanea,orden)
);
CREATE TABLE reglas_aplicadas_dcl (
    cod_regla_aplicada_dcl TEXT PRIMARY KEY,
    cod_clasificacion_dcl_instantanea TEXT NOT NULL REFERENCES clasificaciones_dcl_instantanea(cod_clasificacion_dcl_instantanea) ON DELETE RESTRICT,
    id_regla_dominio TEXT NOT NULL,
    orden INTEGER NOT NULL,
    UNIQUE(cod_clasificacion_dcl_instantanea,id_regla_dominio),
    UNIQUE(cod_clasificacion_dcl_instantanea,orden)
);
INSERT INTO funcionalidades_instantanea VALUES ('F-1'),('F-2');
INSERT INTO evaluaciones_expertas VALUES
 ('E-1','RES-1','FINALIZADA','2026-01-01 09:00:00','2026-01-01 10:00:00'),
 ('E-2','RES-2','FINALIZADA','2026-01-02 09:00:00',NULL),
 ('E-3','RES-3','PENDIENTE','2026-01-03 09:00:00',NULL);
INSERT INTO clasificaciones_dcl_instantanea VALUES
 ('CL-1','E-1','F-1','PETERSEN-1','REVISADO',NULL,'abstención por falta original','ORIENTATIVO','H-1'),
 ('CL-2','E-2','F-2','PETERSEN-1','REVISADO','AMN','perfil sustentado','ORIENTATIVO','H-2');
INSERT INTO motivos_dcl_instantanea VALUES
 ('MO-1','CL-1','EVIDENCIA_INSUFICIENTE',0),('MO-2','CL-1','CONTEXTO_PENDIENTE',1);
INSERT INTO reglas_aplicadas_dcl VALUES
 ('RA-1','CL-1','PET-01',0),('RA-2','CL-2','PET-02',0);
SQL);

$antes = [
    'evaluaciones_expertas' => leerClasificacion($db, 'evaluaciones_expertas', 'cod_evaluacion_experta'),
    'clasificaciones_dcl_instantanea' => leerClasificacion($db, 'clasificaciones_dcl_instantanea', 'cod_clasificacion_dcl_instantanea'),
    'motivos_dcl_instantanea' => leerClasificacion($db, 'motivos_dcl_instantanea', 'cod_motivo_dcl_instantanea'),
    'reglas_aplicadas_dcl' => leerClasificacion($db, 'reglas_aplicadas_dcl', 'cod_regla_aplicada_dcl'),
];

$crear = <<<'SQL'
CREATE TABLE evaluaciones_expertas_v2 (
    cod_evaluacion_experta TEXT PRIMARY KEY,
    cod_residente TEXT NOT NULL,
    estado_ejecucion TEXT NOT NULL,
    fecha_hora_inicio TEXT NOT NULL,
    fecha_hora_fin TEXT,
    cod_clasificacion_dcl_instantanea TEXT UNIQUE,
    cod_funcionalidad_instantanea TEXT UNIQUE REFERENCES funcionalidades_instantanea(cod_funcionalidad_instantanea) ON DELETE RESTRICT,
    version_contrato_dcl TEXT,
    diferencial_dcl TEXT,
    patron_candidato_dcl TEXT,
    justificacion_dcl TEXT,
    uso_dcl TEXT,
    huella_clasificacion_dcl TEXT,
    CHECK ((cod_clasificacion_dcl_instantanea IS NULL AND cod_funcionalidad_instantanea IS NULL
        AND version_contrato_dcl IS NULL AND diferencial_dcl IS NULL AND patron_candidato_dcl IS NULL
        AND justificacion_dcl IS NULL AND uso_dcl IS NULL AND huella_clasificacion_dcl IS NULL)
        OR (cod_clasificacion_dcl_instantanea IS NOT NULL AND cod_funcionalidad_instantanea IS NOT NULL
        AND version_contrato_dcl IS NOT NULL AND diferencial_dcl IS NOT NULL
        AND justificacion_dcl IS NOT NULL AND uso_dcl IS NOT NULL AND huella_clasificacion_dcl IS NOT NULL))
);
SQL;

$copiar = <<<'SQL'
INSERT INTO evaluaciones_expertas_v2
SELECT e.cod_evaluacion_experta,e.cod_residente,e.estado_ejecucion,e.fecha_hora_inicio,e.fecha_hora_fin,
 c.cod_clasificacion_dcl_instantanea,c.cod_funcionalidad_instantanea,c.version_contrato,c.diferencial,
 c.patron_candidato,c.justificacion,c.uso,c.huella_clasificacion
FROM evaluaciones_expertas e LEFT JOIN clasificaciones_dcl_instantanea c
 ON c.cod_evaluacion_experta=e.cod_evaluacion_experta;
SQL;

$db->beginTransaction();
try {
    $db->exec($crear);
    $db->exec($copiar);
    throw new RuntimeException('FALLO_INYECTADO');
} catch (RuntimeException $error) {
    exigirClasificacion($error->getMessage() === 'FALLO_INYECTADO', 'Error inesperado en rollback.');
    $db->rollBack();
}
exigirClasificacion(leerClasificacion($db, 'clasificaciones_dcl_instantanea', 'cod_clasificacion_dcl_instantanea') === $antes['clasificaciones_dcl_instantanea'], 'Rollback alteró la clasificación.');
exigirClasificacion((int) $db->query("SELECT count(*) FROM sqlite_master WHERE name='evaluaciones_expertas_v2'")->fetchColumn() === 0, 'Rollback dejó tabla parcial.');

$db->exec('PRAGMA foreign_keys = OFF');
$db->beginTransaction();
try {
    $db->exec($crear);
    $db->exec($copiar);
    $db->exec(<<<'SQL'
CREATE TABLE motivos_dcl_instantanea_v2 (
    cod_motivo_dcl_instantanea TEXT PRIMARY KEY,
    cod_clasificacion_dcl_instantanea TEXT NOT NULL REFERENCES evaluaciones_expertas_v2(cod_clasificacion_dcl_instantanea) ON DELETE RESTRICT,
    motivo TEXT NOT NULL,
    orden INTEGER NOT NULL,
    UNIQUE(cod_clasificacion_dcl_instantanea,motivo),
    UNIQUE(cod_clasificacion_dcl_instantanea,orden)
);
INSERT INTO motivos_dcl_instantanea_v2 SELECT * FROM motivos_dcl_instantanea;
CREATE TABLE reglas_aplicadas_dcl_v2 (
    cod_regla_aplicada_dcl TEXT PRIMARY KEY,
    cod_clasificacion_dcl_instantanea TEXT NOT NULL REFERENCES evaluaciones_expertas_v2(cod_clasificacion_dcl_instantanea) ON DELETE RESTRICT,
    id_regla_dominio TEXT NOT NULL,
    orden INTEGER NOT NULL,
    UNIQUE(cod_clasificacion_dcl_instantanea,id_regla_dominio),
    UNIQUE(cod_clasificacion_dcl_instantanea,orden)
);
INSERT INTO reglas_aplicadas_dcl_v2 SELECT * FROM reglas_aplicadas_dcl;
DROP TABLE motivos_dcl_instantanea;
DROP TABLE reglas_aplicadas_dcl;
DROP TABLE clasificaciones_dcl_instantanea;
DROP TABLE evaluaciones_expertas;
ALTER TABLE evaluaciones_expertas_v2 RENAME TO evaluaciones_expertas;
ALTER TABLE motivos_dcl_instantanea_v2 RENAME TO motivos_dcl_instantanea;
ALTER TABLE reglas_aplicadas_dcl_v2 RENAME TO reglas_aplicadas_dcl;
SQL);
    exigirClasificacion($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'FK rota tras transición.');
    $db->commit();
} catch (Throwable $error) {
    $db->rollBack();
    throw $error;
} finally {
    $db->exec('PRAGMA foreign_keys = ON');
}

$reconstruidas = [
    'evaluaciones_expertas' => [],
    'clasificaciones_dcl_instantanea' => [],
    'motivos_dcl_instantanea' => leerClasificacion($db, 'motivos_dcl_instantanea', 'cod_motivo_dcl_instantanea'),
    'reglas_aplicadas_dcl' => leerClasificacion($db, 'reglas_aplicadas_dcl', 'cod_regla_aplicada_dcl'),
];
foreach (leerClasificacion($db, 'evaluaciones_expertas', 'cod_evaluacion_experta') as $fila) {
    $reconstruidas['evaluaciones_expertas'][] = array_slice($fila, 0, 5, true);
    if ($fila['cod_clasificacion_dcl_instantanea'] !== null) {
        $reconstruidas['clasificaciones_dcl_instantanea'][] = [
            'cod_clasificacion_dcl_instantanea' => $fila['cod_clasificacion_dcl_instantanea'],
            'cod_evaluacion_experta' => $fila['cod_evaluacion_experta'],
            'cod_funcionalidad_instantanea' => $fila['cod_funcionalidad_instantanea'],
            'version_contrato' => $fila['version_contrato_dcl'],
            'diferencial' => $fila['diferencial_dcl'],
            'patron_candidato' => $fila['patron_candidato_dcl'],
            'justificacion' => $fila['justificacion_dcl'],
            'uso' => $fila['uso_dcl'],
            'huella_clasificacion' => $fila['huella_clasificacion_dcl'],
        ];
    }
}
foreach (array_keys($antes) as $tabla) {
    exigirClasificacion($reconstruidas[$tabla] === $antes[$tabla], "No reconstruyó $tabla.");
}

rechazarClasificacion($db, "INSERT INTO evaluaciones_expertas(cod_evaluacion_experta,cod_residente,estado_ejecucion,fecha_hora_inicio,cod_clasificacion_dcl_instantanea) VALUES ('E-MAL','RES-4','FINALIZADA','2026-01-04','CL-MAL')");
rechazarClasificacion($db, "INSERT INTO evaluaciones_expertas(cod_evaluacion_experta,cod_residente,estado_ejecucion,fecha_hora_inicio,cod_clasificacion_dcl_instantanea,cod_funcionalidad_instantanea,version_contrato_dcl,diferencial_dcl,justificacion_dcl,uso_dcl,huella_clasificacion_dcl) VALUES ('E-MAL','RES-4','FINALIZADA','2026-01-04','CL-1','F-1','PETERSEN-1','REVISADO','j','ORIENTATIVO','H-3')");
rechazarClasificacion($db, "INSERT INTO motivos_dcl_instantanea VALUES ('MO-MAL','CL-AJENA','OTRO',0)");
exigirClasificacion($db->query('PRAGMA foreign_key_check')->fetchAll() === [], 'FK rota tras casos negativos.');

echo "PASS SQLite desechable: rollback, 3 evaluaciones, 2 clasificaciones, motivos/reglas, reconstrucción exacta y FK/checks.\n";
echo "LÍMITE: no prueba PostgreSQL, historia poblada, concurrencia, 4FN independiente ni DTO compuesto. No autoriza DDL.\n";
